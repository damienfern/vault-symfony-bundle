<?php

namespace Damienfern\VaultSymfonyBundle\Factory;

use Damienfern\VaultSymfonyBundle\VaultClient;
use Exception;
use JsonException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AppRoleClientFactory extends VaultClientFactory
{
    private readonly string $vaultAddr;

    public function __construct(
        string $vaultAddr,
        private readonly string $roleId,
        #[\SensitiveParameter]
        private readonly string $secretId,
        private readonly HttpClientInterface $httpClient
    )
    {
        if (!filter_var($vaultAddr, FILTER_VALIDATE_URL)) {
            throw new Exception("L'adresse de Vault n'est pas une URL valide");
        }
        $this->vaultAddr = rtrim($vaultAddr, '/');
    }

    public function create(): VaultClient
    {
        $url = $this->vaultAddr. '/v1/auth/approle/login';

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'role_id' => $this->roleId,
                    'secret_id' => $this->secretId
                ],
            ]);
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
        } catch (TransportExceptionInterface $e) {
            throw new Exception("Erreur d'authentification (transport): " . $e->getMessage(), previous: $e);
        }

        if ($statusCode !== 200) {
            throw new Exception("Erreur d'authentification: {$content}");
        }

        $data = json_decode($content, true);
        $token = $data['auth']['client_token'] ?? null;

        if (!$token) {
            throw new Exception("Token non reçu");
        }


        try {
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
            $token = $data['auth']['client_token'] ?? throw new Exception("Token non reçu");
        } catch (JsonException $exception) {
            throw new Exception('Erreur JSON: ' . $exception->getMessage(), previous: $exception);
        }

        return new VaultClient($this->vaultAddr, $token, $this->httpClient);
    }
}
