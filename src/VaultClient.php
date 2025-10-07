<?php

namespace Damienfern\VaultSymfonyBundle;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class VaultClient
{
    public string $vaultAddr;

    private HttpClientInterface $httpClient;

    public function __construct(
        string $vaultAddr,
        #[\SensitiveParameter]
        public string $vaultToken,
        ?HttpClientInterface $httpClient
    )
    {
        $this->vaultAddr = rtrim($vaultAddr, '/');
        $this->vaultToken = $vaultToken;
        $this->httpClient = $httpClient;
    }

    public function getSecrets(string $path): array
    {
        $url = sprintf('%s/v1/secret/data/%s', $this->vaultAddr, ltrim($path, '/'));

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'X-Vault-Token' => $this->vaultToken,
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 10,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw new Exception('Erreur de transport HTTP: ' . $e->getMessage(), previous: $e);
        }

        if ($statusCode !== 200) {
            throw new Exception("Erreur HTTP {$statusCode}: {$content}");
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erreur JSON: ' . json_last_error_msg());
        }

        return $data['data']['data'] ?? [];
    }
}
