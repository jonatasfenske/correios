<?php

namespace JonatasFenske\Correios;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as HttpClient;
use JonatasFenske\Correios\Services\Freight;
use JonatasFenske\Correios\Services\ZipCode;
use JonatasFenske\Correios\Services\Tracking;

class Correios
{
    /**
     * Serviço de frete.
     *
     * @var \Source\Contracts\FreightInterface
     */
    protected $freight;

    /**
     * Serviço de CEP.
     *
     * @var \Source\Contracts\ZipCodeInterface
     */
    protected $zipcode;
    /**
     * Serviço de CEP.
     *
     * @var \Source\Contracts\ZipCodeInterface
     */
    protected $tracking;

    /**
     * Cria uma nova instância da classe Client.
     *
     * @param \GuzzleHttp\ClientInterface|null  $http
     * @param \Source\Contracts\FreightInterface|null $freight
     * @param \Source\Contracts\ZipCodeInterface|null $zipcode
     */
    public function __construct(
        ClientInterface $http = null,
        Freight $freight = null,
        ZipCode $zipcode = null,
        Tracking $tracking = null
    ) {
        $this->http = $http ?: new HttpClient;
        $this->freight = $freight ?: new Freight($this->http);
        $this->zipcode = $zipcode ?: new ZipCode($this->http);
        $this->tracking = $tracking ?: new Tracking($this->http);
    }


    public function freight()
    {
        return $this->freight;
    }


    public function zipcode()
    {
        return $this->zipcode;
    }


    public function tracking()
    {
        return $this->tracking;
    }
    
    public function toJson() {
        return json_encode($this);
    }
}
