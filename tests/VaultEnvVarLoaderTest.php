<?php

namespace Damienfern\VaultSymfonyBundle\Tests;

use Damienfern\VaultSymfonyBundle\Factory\VaultClientFactory;
use Damienfern\VaultSymfonyBundle\VaultClient;
use Damienfern\VaultSymfonyBundle\VaultEnvVarLoader;
use PHPUnit\Framework\TestCase;

class VaultEnvVarLoaderTest extends TestCase
{
    public function testLoadEnvVars(): void
    {
        $factory = $this->createMock(VaultClientFactory::class);
        $client = $this->createMock(VaultClient::class);
        $path = 'test/path';

        $client
            ->method('getSecrets')
            ->with($path)
            ->willReturn(['TEST_KEY' => 'TEST_VALUE']);
        $factory
            ->method('create')
            ->willReturn($client);

        $loader = new VaultEnvVarLoader('test/path', $factory);
        $envVars = $loader->loadEnvVars();

        $this->assertSame(['TEST_KEY' => 'TEST_VALUE'], $envVars);
    }
}
