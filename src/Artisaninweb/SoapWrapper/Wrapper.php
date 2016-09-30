<?php

namespace Artisaninweb\SoapWrapper;

use Exception;
use Closure;

/**
 * Soap Webservice wrapper
 *
 * @package Artisaninweb\SoapWrapper
 * @author Michael van de Rijt
 */
Class Wrapper {

    /**
     * @var array
     */
    protected $clients;

    /**
     * @var array
     */
    protected $services;

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->clients  = [];
        $this->services = [];
    }

    /**
     * Get all added services
     *
     * @return array
     */
    public function services()
    {
        return $this->services;
    }

    /**
     * Get a single service
     *
     * @param $name
     * @param $callback
     * @return mixed
     * @throws \Exception
     */
    public function service($name, Closure $callback)
    {
        if(!empty($this->services[$name]))
        {
            $callback($this->services[$name]);

            return $this->services[$name];
        }
        throw new Exception('Webservice "'.$name.'" is not found.');
    }

    /**
     * Add a service to the wrapper
     *
     * @param $service
     * @return $this
     * @throws \Exception
     */
    public function add(Closure $service)
    {
        $client = new Service();

        $service($client);

        $serviceName = $client->getName();

        if(empty($this->services[$serviceName]))
        {
            $client->createClient();
            
            $this->services[$serviceName] = $client;

            return $this;
        }
        throw new Exception('Service "'.$serviceName.'" already exists, if you want to override it use the override function.');
    }

    /**
     * Override a existing service in the wrapper
     * If service does not exists in the wrapper it will be added
     *
     * @param $service
     * @return $this
     */
    public function override(Closure $service)
    {
        $client = new Service();

        $service($client);

        $serviceName = $client->getName();

        $client->createClient();
        
        $this->services[$serviceName] = $client;

        return $this;
    }

    /**
     * Remove a service from the wrapper
     *
     * @param $name
     * @return $this
     */
    public function remove($name)
    {
        if(!empty($this->services[$name]))
        {
            unset($this->services[$name]);
        }
        return $this;
    }
    
     /**
     * Encodes the data from the request (split in object's data and object's name)
     * to resolve compatibility problem between PHP SoapClient and some .NET SOAP services.
     * Some SOAP services has complex object type on its WSDL and XSD definitions, in some case they are not 
     * automatically understood by PHP SoapClient Library.
     * This SoapClient feature of PHP to obtains a more explicit object definition, and bypass the XDS definitions
     * provided by the WSDL of the service.
     *
     * @param $paramObject
     * @param string $paramObjectName
     * @return array
     */
    public function encodeParamsInSoapVarObject($paramObject, $paramObjectName="")
    {

        if(!is_object($paramObject)){

            if(is_array($paramObject)){

                $result = $paramObject;

            } else {

                $result = [ $paramObjectName => $paramObject ];

            }

        } else {

            $result = [ new SoapVar($paramObject, SOAP_ENC_OBJECT, null, null, $paramObjectName, null) ];

        }

        return $result;
    }

}
