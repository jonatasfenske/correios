<?php

namespace JonatasFenske\Correios\Services;

use Exception;
use Goutte\Client;
use Carbon\Carbon;
use GuzzleHttp\ClientInterface;
use JonatasFenske\Correios\Config;

class Tracking
{

    /**
     * Tracking Code.
     *
     * @var string
     */
    private $trackingCode;

    /**
     * Callback.
     *
     * @var array|string
     */
    protected $callback;

    /**
     * getTrackingCode
     * 
     * @return string
     */
    public function getTrackingCode()
    {
        return $this->trackingCode;
    }

    /**
     * Create new instance of Tracking class.
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
     * Find package by Tracking Code.
     *
     * @param  string $zipcode
     *
     * @return Tracking
     */
    public function find($trackingCode): Tracking
    {
        $client = new Client;

        $crawler = $client->request('GET', Config::WEBSERVICE_TRACKING . '/' . $trackingCode);
        $arr = [];

        $crawler->filter('ul.linha_status')->each(function ($node) use (&$arr) {
            $lastDate = null;

            $date = $node->filter('li')->eq(1)->text();
            $date = str_replace(['Data  : ', 'Data : ', ' | ', 'Hora:'], '', $date);
            $status = str_replace('Status: ', '', $node->filter('li')->eq(0)->text());
            $locale = str_replace(['Local: ', 'Origem: '], '', $node->filter('li')->eq(2)->text());

            $arr[$date] = [
                'date' => $date,
                'status' => $status,
                'locale' => $locale,
            ];
        });

        $tracking = array_values($arr);

        $trackingObject = array_map(function ($key) {
            return [
                'timestamp' => Carbon::createFromFormat('d/m/Y H:i', $key['date'])->timestamp,
                'date' => Carbon::createFromFormat('d/m/Y H:i', $key['date'])->format('Y-m-d H:i'),
                'locale' => rtrim($key['locale']),
                'status' => $key['status'],
                'forwarded' => isset($key['encaminhado']) ? $key['encaminhado'] : null,
                'delivered' => $key['status'] == 'Entrega Efetuada' || $key['status'] == 'Objeto entregue ao destinatário'
            ];
        }, $tracking);

        if (!isset($trackingObject[0])) {
            $this->callback = ['error' => "Objeto não encontrado!"];
            return $this;
        }

        $firstTrackingObject = $trackingObject[0];

        $this->callback = array_merge(
                ['code' => $this->getTrackingCode()],
                ['last_timestamp' => $firstTrackingObject['timestamp']],
                ['last_status' => $firstTrackingObject['status']],
                ['last_date' => $firstTrackingObject['date']],
                ['last_locale' => $firstTrackingObject['locale']],
                ['delivered' => $firstTrackingObject['delivered']],
                ['delivered_at' => ($firstTrackingObject['delivered']) ? $firstTrackingObject['date'] : null],
                ['tracking' => $trackingObject]
        );
        return $this;
    }

}
