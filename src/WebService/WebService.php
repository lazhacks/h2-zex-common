<?php

namespace Common\WebService;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

class WebService
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param array | ClientInterface $client
     */
    public function __construct($client)
    {
        if (is_array($client)) {
            $parameters = $client;
            $client     = $this->createClient($parameters);
        }

        if (!$client instanceof ClientInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The supplied or instantiated client object does not implement %s',
                ClientInterface::class
            ));
        }

        $this->client = $client;
    }

    /**
     * @param array $parameters
     * @return Client
     */
    public function createClient(array $parameters = array())
    {
        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }

        return new Client($parameters);
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->client, $method), $args);
    }

    /**
     * @param Response $response
     * @return mixed
     */
    public function getData(Response $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}
