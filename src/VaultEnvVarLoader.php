<?php

namespace Damienfern\VaultSymfonyBundle;

use Damienfern\VaultSymfonyBundle\Factory\TokenClientFactory;
use Damienfern\VaultSymfonyBundle\Factory\VaultClientFactory;
use Symfony\Component\DependencyInjection\EnvVarLoaderInterface;

class VaultEnvVarLoader implements EnvVarLoaderInterface
{
    public function __construct(
        private readonly string $path,
        private readonly VaultClientFactory $factory
    ) {}

    public function loadEnvVars(): array
    {
        $client = $this->factory->create();
        return $client->getSecrets($this->path);
    }
}
