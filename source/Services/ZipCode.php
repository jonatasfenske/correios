<?php

namespace JonatasFenske\Correios\Services;

use GuzzleHttp\ClientInterface;
use JonatasFenske\Correios\Config;

class ZipCode
{

    /**
     * HTTP Client 
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $http;

    /**
     * ZipCode
     *
     * @var string
     */
    protected $zipcode;

    /**
     * Request XML.
     *
     * @var string
     */
    protected $body;

    /**
     * Response.
     *
     * @var \GuzzleHttp\Psr7\Response
     */
    protected $response;

    /**
     * Parsed Response XML.
     *
     * @var array
     */
    protected $parsedXML;

    /**
     * Callback.
     *
     * @var array|string
     */
    protected $callback;

    /**
     * Create new instance of ZipCode class.
     *
     * @param ClientInterface $http
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
     * Find address for ZipCode.
     *
     * @param  string $zipcode
     *
     * @return array
     */
    public function find($zipcode): ZipCode
    {
        $this->setZipCode($zipcode)
                ->buildXMLBody()
                ->sendWebServiceRequest()
                ->parseXMLFromResponse();

        if ($this->hasErrorMessage()) {
            $this->callback = $this->fetchErrorMessage();
            return $this;
        }

        $this->callback = $this->fetchZipCodeAddress();

        return $this;
    }

    /**
     * Set ZipCode.
     *
     * @param string $zipcode
     *
     * @return self
     */
    protected function setZipCode($zipcode) : ZipCode
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Build body XML request.
     *
     * @return self
     */
    protected function buildXMLBody() : ZipCode
    {
        $zipcode = preg_replace('/[^0-9]/', null, $this->zipcode);
        $this->body = trim('
            <?xml version="1.0"?>
            <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:cli="http://cliente.bean.master.sigep.bsb.correios.com.br/">
                <soapenv:Header/>
                <soapenv:Body>
                    <cli:consultaCEP>
                        <cep>' . $zipcode . '</cep>
                    </cli:consultaCEP>
                </soapenv:Body>
            </soapenv:Envelope>
        ');

        return $this;
    }

    /**
     * Send Correios webservice request and save response for use
     *
     * @return self
     */
    protected function sendWebServiceRequest() : ZipCode
    {
        $this->response = $this->http->post(Config::WEBSERVICE_SIGEP, [
            'http_errors' => false,
            'body' => $this->body,
            'headers' => [
                'Content-Type' => 'application/xml; charset=utf-8',
                'cache-control' => 'no-cache',
            ],
        ]);

        return $this;
    }

    /**
     * XML body formater
     *
     * @return self
     */
    protected function parseXMLFromResponse() : ZipCode
    {
        $xml = $this->response->getBody()->getContents();
        $parse = simplexml_load_string(str_replace([
            'soap:', 'ns2:',
                        ], null, $xml));

        $this->parsedXML = json_decode(json_encode($parse->Body), true);

        return $this;
    }

    /**
     * Verify error in XML response
     *
     * @return bool
     */
    protected function hasErrorMessage() : bool
    {
        return array_key_exists('Fault', $this->parsedXML);
    }

    /**
     * Recovery XML error message
     *
     * @return array
     */
    protected function fetchErrorMessage() : ZipCode
    {
        return ['error' => 'CEP não encontrado'];
    }

    /**
     * Address complement return.
     *
     * @param  array  $address
     * @return array
     */
    protected function getComplement(array $address) : array
    {
        $complement = [];

        if (array_key_exists('complemento', $address)) {
            $complement[] = $address['complemento'];
        }

        if (array_key_exists('complemento2', $address)) {
            $complement[] = $address['complemento2'];
        }

        return $complement;
    }

    /**
     * Response XML address recovery.
     *
     * @return array
     */
    protected function fetchZipCodeAddress() : array
    {
        $address = $this->parsedXML['consultaCEPResponse']['return'];
        $zipcode = preg_replace('/^([0-9]{5})([0-9]{3})$/', '${1}-${2}', $address['cep']);
        $complement = $this->getComplement($address);

        return [
            'zipcode' => $zipcode,
            'street' => $address['end'],
            'complement' => $complement,
            'district' => $address['bairro'],
            'city' => $address['cidade'],
            'uf' => $address['uf'],
        ];
    }

}
