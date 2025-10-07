<?php

namespace Damienfern\VaultSymfonyBundle\Factory;

use Damienfern\VaultSymfonyBundle\VaultClient;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AppRoleClientFactory extends VaultClientFactory
{
    public function __construct(
        private readonly string $vaultAddr,
        private readonly string $roleId,
        #[\SensitiveParameter]
        private readonly string $secretId,
        private readonly HttpClientInterface $httpClient
    )
    {}

    public function create(): VaultClient
    {
        $url = rtrim($this->vaultAddr, '/') . '/v1/auth/approle/login';

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
            $content = $response->getContent(false); // don't throw automatically
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

        // Use base vault address (without login path) for the VaultClient
        return new VaultClient($this->vaultAddr, $token, $this->httpClient);
    }
}
