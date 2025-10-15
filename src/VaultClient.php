<?php

namespace Damienfern\VaultSymfonyBundle;

use Exception;
use JsonException;
use Stringable;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class VaultClient
{
    public string $vaultAddr;

    public function __construct(
        string $vaultAddr,
        #[\SensitiveParameter]
        public readonly string $vaultToken,
        private readonly HttpClientInterface $httpClient
    )
    {
        if (!filter_var($vaultAddr, FILTER_VALIDATE_URL)) {
            throw new Exception("L'adresse de Vault n'est pas une URL valide");
        }
        $this->vaultAddr = rtrim($vaultAddr, '/');
    }


    /**
     * @param string $path
     * @return array<string, Stringable>
     */
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
        } catch (TransportExceptionInterface $exception) {
            throw new Exception('Erreur de transport HTTP: ' . $exception->getMessage(), previous: $exception);
        }

        if ($statusCode !== 200) {
            throw new Exception("Erreur HTTP {$statusCode}: {$content}");
        }

        try {
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new Exception('Erreur JSON: ' . $exception->getMessage(), previous: $exception);
        }

        return $data['data']['data'] ?? [];
    }
}
