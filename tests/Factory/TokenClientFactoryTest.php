<?php

namespace Damienfern\VaultSymfonyBundle\Tests\Factory;

use Damienfern\VaultSymfonyBundle\Factory\TokenClientFactory;
use Damienfern\VaultSymfonyBundle\VaultClient;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenClientFactoryTest extends TestCase
{
    #[AllowMockObjectsWithoutExpectations]
    public function testCreate(): void
    {
        $factory = new TokenClientFactory(
            'http://localhost:8200',
            'dummy_token',
            $this->createMock(HttpClientInterface::class)
        );
        $client = $factory->create();

        $this->assertSame('http://localhost:8200', $client->vaultAddr);
        $this->assertSame('dummy_token', $client->vaultToken);
    }
}
