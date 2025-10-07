<?php

namespace Damienfern\VaultSymfonyBundle\Tests\Factory;

use Damienfern\VaultSymfonyBundle\Factory\AppRoleClientFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class AppRoleClientFactoryTest extends TestCase
{
    public function testCreate()
    {
        $vaultAddr = 'http://localhost:8200';
        $expectedUrl = rtrim($vaultAddr, '/') . '/v1/auth/approle/login';

        $mockResponse = new MockResponse(json_encode([
            'auth' => [
                'client_token' => 'test-token'
            ]
        ]), ['http_code' => 200]);

        $httpClient = new MockHttpClient(function(string $method, string $url, array $options) use ($expectedUrl, $mockResponse) {
            $this->assertSame('POST', $method);
            $this->assertSame($expectedUrl, $url);
            $payload = $options['json'] ?? json_decode($options['body'] ?? 'null', true);
            $this->assertSame(['role_id' => 'dummy_role_id', 'secret_id' => 'dummy_secret_id'], $payload);
            return $mockResponse;
        });

        $factory = new AppRoleClientFactory($vaultAddr, 'dummy_role_id', 'dummy_secret_id', $httpClient);
        $client = $factory->create();
        $this->assertSame('test-token', $client->vaultToken);
    }
}
