<?php

namespace App\Services;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;

/**
 * ViacepService class
 */
class ViacepService
{
    /**
     * @var string
     */
    private static $endpoint = 'https://viacep.com.br/';

    /**
     * Returns Brazillian address by zipcode
     * @param  string $raw_zip
     * @return array
     */
    public static function findByZip(string $raw_zip): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => static::$endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = [];

        try {
            $zipcode = substr(preg_replace('/\D/', '', $raw_zip), 0, 8);

            $res = $client->request('GET', "ws/{$zipcode}/json");

            $body = json_decode($res->getBody(), true) ?? [];

            $result = [
                'address'   => $body['logradouro'] ?? null,
                'city'      => $body['localidade'] ?? null,
                'state'     => $body['uf'] ?? null,
                'district'  => $body['bairro'] ?? null,
                'complement' => $body['complemento'] ?? null,
            ];
        } catch (GuzzReqException $ex) {
            logger()->error("Viacep", [
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $ex->hasResponse() ? Psr7\str($ex->getResponse()) : null,
            ]);
        }

        return $result;
    }
}
