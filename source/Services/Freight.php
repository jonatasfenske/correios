<?php

namespace JonatasFenske\Correios\Services;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientInterface;
use JonatasFenske\Correios\Config;

class Freight
{

    /**
     * Correios services (Sedex, PAC...).
     *
     * @var array
     */
    protected $services = [];

    /**
     * Default Payload.
     *
     * @var array
     */
    protected $defaultPayload = [
        'nCdEmpresa' => '',
        'sDsSenha' => '',
        'nCdServico' => '',
        'sCepOrigem' => '',
        'sCepDestino' => '',
        'nCdFormato' => Config::PACKAGE_BOX,
        'nVlLargura' => 0,
        'nVlAltura' => 0,
        'nVlPeso' => 0,
        'nVlComprimento' => 0,
        'nVlDiametro' => 0,
        'sCdMaoPropria' => 'N',
        'nVlValorDeclarado' => 0,
        'sCdAvisoRecebimento' => 'N',
    ];
    protected $callback;

    /**
     * Request Payload.
     *
     * @var array
     */
    protected $payload = [];

    /**
     * Delivered Itens.
     *
     * @var array
     */
    protected $items = [];

    /**
     * HTTP Client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $http;

    /**
     * Creates a new class instance.
     *
     * @param \GuzzleHttp\ClientInterface $http
     */
    public function __construct(ClientInterface $http)
    {
        $this->http = $http;
    }

    /**
     * getResponse()
     *
     * @return array
     */
    public function getResponse(): array
    {
        return $this->callback;
    }

    /**
     * getJsonResponse()
     *
     * @return string
     */
    public function getJsonResponse(): string
    {
        return json_encode($this->callback);
    }

    /**
     * Correios Webservices request Payload.
     *
     * @param  string $service Service (Sedex, PAC...)
     *
     * @return array
     */
    public function payload($service): array
    {
        $this->payload['nCdServico'] = $service;

        if ($this->items) {
            $this->payload['nVlLargura'] = $this->width();
            $this->payload['nVlAltura'] = $this->height();
            $this->payload['nVlComprimento'] = $this->length();
            $this->payload['nVlDiametro'] = 0;
            $this->payload['nVlPeso'] = $this->useWeightOrVolume();
        }

        return array_merge($this->defaultPayload, $this->payload);
    }

    /**
     * Origin ZipCode.
     *
     * @param  string $zipCode
     *
     * @return self
     */
    public function origin($zipCode): Freight
    {
        $this->payload['sCepOrigem'] = preg_replace('/[^0-9]/', null, $zipCode);

        return $this;
    }

    /**
     * Destination ZipCode.
     *
     * @param  string $zipCode
     *
     * @return self
     */
    public function destination($zipCode): Freight
    {
        $this->payload['sCepDestino'] = preg_replace('/[^0-9]/', null, $zipCode);

        return $this;
    }

    /**
     * Calculate services.
     *
     * @param  int ...$services
     *
     * @return self
     */
    public function services(...$services): Freight
    {
        $this->services = array_unique($services);

        return $this;
    }

    /**
     * Código administrativo junto à ECT. O código está disponível no
     * corpo do contrato firmado com os Correios.
     *
     * Senha para acesso ao serviço, associada ao seu código administrativo,
     * a senha inicial corresponde aos 8 primeiros dígitos do CNPJ informado no contrato.
     *
     * @param  string $code
     * @param  string $password
     *
     * @return self
     */
    public function credentials($code, $password): Freight
    {
        $this->payload['nCdEmpresa'] = $code;
        $this->payload['sDsSenha'] = $password;

        return $this;
    }

    /**
     * Package format (Caixa, pacote, rolo, prisma ou envelope).
     *
     * @param  int $format
     *
     * @return self
     */
    public function package($format): Freight
    {
        $this->payload['nCdFormato'] = $format;

        return $this;
    }

    /**
     * Use if Own Hand.
     *
     * @param  bool $useOwnHand
     *
     * @return self
     */
    public function useOwnHand($useOwnHand): Freight
    {
        $this->payload['sCdMaoPropria'] = (bool) $useOwnHand ? 'S' : 'N';

        return $this;
    }

    /**
     * Use if declare value, in format BRL.
     *
     * @param  int|float $value
     *
     * @return self
     */
    public function declaredValue($value): Freight
    {
        $this->payload['nVlValorDeclarado'] = floatval($value);

        return $this;
    }

    /**
     * Dimensions, Weight and Quantity of item.
     *
     * @param  int|float $width
     * @param  int|float $height
     * @param  int|float $length
     * @param  int|float $weight
     * @param  int       $quantity
     *
     * @return self
     */
    public function item($width, $height, $length, $weight, $quantity = 1): Freight
    {
        $width = (empty($width) || $width < 16) ? 16 : \number_format($width, 4, '.', '');
        $height = (empty($height) || $height < 2) ? 2 : \number_format($height, 4, '.', '');
        $length = (empty($length) || $length < 11) ? 11 : \number_format($length, 4, '.', '');

        $this->items[] = compact('width', 'height', 'length', 'weight', 'quantity');

        return $this;
    }

    /**
     * Correios prizes and prices calculate.
     *
     * @return array
     */
    public function calculate(): Freight
    {
        $servicesResponses = array_map(function ($service) {
            return $this->http->get(Config::WEBSERVICE_CALC_PRICE, [
                        'query' => $this->payload($service),
            ]);
        }, $this->services);


        $services = array_map([$this, 'fetchCorreiosService'], $servicesResponses);

        $this->callback = array_map([$this, 'transformCorreiosService'], $services);
        return $this;
    }

    /**
     * Width calculate and return for itens.
     *
     * @return int|float
     */
    protected function width(): float
    {
        return max(array_map(function ($item) {
                    return $item['width'];
                }, $this->items));
    }

    /**
     * Height calculate and return for itens.
     *
     * @return int|float
     */
    protected function height(): float
    {
        return array_sum(array_map(function ($item) {
                    return $item['height'] * $item['quantity'];
                }, $this->items));
    }

    /**
     * Length calculate and return for itens.
     *
     * @return int|float
     */
    protected function length(): float
    {
        return max(array_map(function ($item) {
                    return $item['length'];
                }, $this->items));
    }

    /**
     * Weight calculate and return for itens.
     *
     * @return int|float
     */
    protected function weight(): float
    {
        return array_sum(array_map(function ($item) {
                    return $item['weight'] * $item['quantity'];
                }, $this->items));
    }

    /**
     * Freight volume calculate in lenght, widht and height of itens.
     *
     * @return int|float
     */
    protected function volume(): float
    {
        return ($this->length() * $this->width() * $this->height()) / 6000;
    }

    /**
     * Calculate why value use (Volume or Weight) in freight final request.
     *
     * @return int|float
     */
    protected function useWeightOrVolume(): float
    {
        if ($this->volume() < 10 || $this->volume() <= $this->weight()) {
            return $this->weight();
        }
        return $this->volume();
    }

    /**
     * Extract alll response XML of Correios.
     *
     * @param  \GuzzleHttp\Psr7\Response $response
     *
     * @return array
     */
    protected function fetchCorreiosService(Response $response): array
    {
        $xml = simplexml_load_string($response->getBody()->getContents());
        $result = json_decode(json_encode($xml->Servicos));

        return get_object_vars($result->cServico);
    }

    /**
     * Transform Correios service in clean array, pretty and easy for manipulation.
     *
     * @param  array  $service
     *
     * @return array
     */
    protected function transformCorreiosService(array $service): array
    {
        $error = [];

        if ($service['Erro'] != 0) {

            return ['error' => $service['MsgErro']];
        }

        return [
            'name' => $this->friendlyServiceName($service['Codigo']),
            'code' => $service['Codigo'],
            'price' => floatval(str_replace(',', '.', $service['Valor'])),
            'deadline' => intval($service['PrazoEntrega'])
        ];
    }

    /**
     * Services names (Sedex, PAC...) of codes.
     *
     * @param  string $code
     *
     * @return string|null
     */
    protected function friendlyServiceName($code): ?string
    {
        $id = intval($code);
        $services = [
            intval(Config::SERVICE_PAC) => 'PAC',
            intval(Config::SERVICE_PAC_CONTRATO) => 'PAC',
            intval(Config::SERVICE_PAC_CONTRATO_04812) => 'PAC',
            intval(Config::SERVICE_PAC_CONTRATO_41068) => 'PAC',
            intval(Config::SERVICE_PAC_CONTRATO_41211) => 'PAC',
            intval(Config::SERVICE_SEDEX) => 'Sedex',
            intval(Config::SERVICE_SEDEX_CONTRATO) => 'Sedex',
            intval(Config::SERVICE_SEDEX_A_COBRAR) => 'Sedex a Cobrar',
            intval(Config::SERVICE_SEDEX_10) => 'Sedex 10',
            intval(Config::SERVICE_SEDEX_HOJE) => 'Sedex Hoje',
            intval(Config::SERVICE_SEDEX_CONTRATO_04316) => 'Sedex',
            intval(Config::SERVICE_SEDEX_CONTRATO_40096) => 'Sedex',
            intval(Config::SERVICE_SEDEX_CONTRATO_40436) => 'Sedex',
            intval(Config::SERVICE_SEDEX_CONTRATO_40444) => 'Sedex',
            intval(Config::SERVICE_SEDEX_CONTRATO_40568) => 'Sedex',
        ];

        if (array_key_exists($id, $services)) {
            return $services[$id];
        }

        return null;
    }

}
