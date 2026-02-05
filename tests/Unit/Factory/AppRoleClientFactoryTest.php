<?php

namespace Damienfern\VaultSymfonyBundle\Tests\Unit\Factory;

use Damienfern\VaultSymfonyBundle\Factory\AppRoleClientFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class AppRoleClientFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $vaultAddr = 'http://localhost:8200';
        $expectedUrl = $vaultAddr.'/v1/auth/approle/login';

        $mockResponse = new MockResponse(json_encode([
            'auth' => [
                'client_token' => 'test-token',
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]);

        $httpClient = new MockHttpClient($mockResponse);

        $factory = new AppRoleClientFactory($vaultAddr, 'dummy_role_id', 'dummy_secret_id', $httpClient);
        $client = $factory->create();

        $this->assertSame('POST', $mockResponse->getRequestMethod());
        $this->assertSame($expectedUrl, $mockResponse->getRequestUrl());
        $this->assertSame(
            json_encode(['role_id' => 'dummy_role_id', 'secret_id' => 'dummy_secret_id']),
            $mockResponse->getRequestOptions()['body']
        );

        $this->assertSame('test-token', $client->vaultToken);
    }
}
