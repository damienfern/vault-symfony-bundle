<?php

namespace Damienfern\VaultSymfonyBundle\Tests\Factory;

use Damienfern\VaultSymfonyBundle\Factory\TokenClientFactory;
use Damienfern\VaultSymfonyBundle\VaultClient;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenClientFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new TokenClientFactory(
            'http://localhost:8200',
            'dummy_token',
            $this->createMock(HttpClientInterface::class)
        );
        $client = $factory->create();

        $this->assertSame($client->vaultAddr, 'http://localhost:8200');
        $this->assertSame($client->vaultToken, 'dummy_token');
    }
}
