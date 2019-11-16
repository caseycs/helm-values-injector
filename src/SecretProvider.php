<?php
declare(strict_types=1);

namespace HelmVaultInjector;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use HelmVaultInjector\Exception\Exception;
use HelmVaultInjector\Exception\KeypathInvalid;
use HelmVaultInjector\Exception\SecretNotFound;

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
            throw new Exception('Kaypath empty');
        }
        $keyParts = explode('.', $key);
        if (!$keyParts || count($keyParts) < 3 || count($keyParts) > 3) {
            throw new KeypathInvalid(sprintf('Keypath %s does not have 3 parts: ', $key));
        }

        list ($path, $secret, $key) = $keyParts;
        $fullPath = $path . '/data/' . $secret;

        try {
            $response = $this->client->request('GET', 'v1/' . $path . '/data/' . $secret);
        } catch (GuzzleException $e) {
            throw new SecretNotFound(sprintf('Secret %s fetch failed: ', $fullPath), 0, $e);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!isset($responseData['data']['data'][$key])) {
            throw new SecretNotFound(sprintf('Key %s is missing in secret %s', $key, $secret));
        }

        return $responseData['data']['data'][$key];
    }
}