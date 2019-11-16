<?php
declare(strict_types=1);

namespace HelmVaultInjector;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use HelmVaultInjector\Exception\Exception;
use HelmVaultInjector\Exception\KeypathInvalid;
use HelmVaultInjector\Exception\SecretNotFound;
use Psr\Log\LoggerInterface;

class SecretProvider
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * SecretProvider constructor.
     */
    public function __construct(string $vaultUrl, string $vaultToken, LoggerInterface $log)
    {
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $vaultUrl,
            // You can set any number of default request options.
            'timeout' => 2.0,
            'headers' => ['X-Vault-Token' => $vaultToken],
        ]);
        $this->log = $log;
    }

    public function get(string $keyPath): string
    {
        if (!$keyPath) {
            throw new Exception('Kaypath empty');
        }
        $keyPathParts = explode('.', $keyPath);
        if (!$keyPathParts || count($keyPathParts) < 3 || count($keyPathParts) > 3) {
            throw new KeypathInvalid(sprintf('Keypath %s does not have 3 parts: ', $key));
        }

        list ($path, $secret, $key) = $keyPathParts;
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

        $this->log->info('Secret found: ' . $keyPath);

        return $responseData['data']['data'][$key];
    }
}