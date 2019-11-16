<?php
declare(strict_types=1);

namespace HelmVaultInjector;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use HelmVaultInjector\Exception\Exception;

class SecretProvider
{
    /**
     * @var Client
     */
    private $client;

    /**
     * SecretProvider constructor.
     */
    public function __construct(string $vaultUrl, string $vaultToken)
    {
        var_dump(func_get_args());
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $vaultUrl,
            // You can set any number of default request options.
            'timeout' => 2.0,
            'headers' => ['X-Vault-Token' => $vaultToken],
        ]);
    }

    public function get(string $key): string
    {
        if (!$key) {
            throw new Exception('Empty secret kaypath');
        }
        $keyParts = explode('.', $key);
        if (!$keyParts || count($keyParts) < 3 || count($keyParts) > 3) {
            throw new Exception('Keypath does not have 3 parts: ' . $key);
        }
        list ($path, $secret, $key) = $keyParts;

        try {
            $response = $this->client->request('GET', 'v1/' . $path . '/data/' . $secret);
        } catch (GuzzleException $e) {
            throw new Exception('Keypath fetch failed: ' . $key, 0, $e);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!isset($responseData['data']['data'][$key])) {
            throw new Exception(sprintf('Key %s is missing in secret %s', $key, $secret));
        }

        return $responseData['data']['data'][$key];
    }
}