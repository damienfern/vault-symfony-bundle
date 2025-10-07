<?php

namespace Damienfern\VaultSymfonyBundle\Factory;

use Damienfern\VaultSymfonyBundle\Factory\VaultClientFactory;
use Damienfern\VaultSymfonyBundle\VaultClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenClientFactory extends VaultClientFactory
{
    public function __construct(
        private readonly string $vaultAddr,
        #[\SensitiveParameter]
        private readonly string $vaultToken,
        private readonly HttpClientInterface $httpClient
    )
    {
    }

    public function create(): VaultClient
    {
        return new VaultClient(
            $this->vaultAddr,
            $this->vaultToken,
            $this->httpClient
        );
    }
}
