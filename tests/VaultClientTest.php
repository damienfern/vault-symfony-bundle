<?php

namespace Damienfern\VaultSymfonyBundle\Tests;

use Damienfern\VaultSymfonyBundle\VaultClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class VaultClientTest extends TestCase
{
    public function testGetSecrets(): void
    {
        $vaultAddr = 'http://localhost:8200';
        $vaultToken = 'your_test_token';
        $testPath = 'your/test/path';

        $expectedUrl = rtrim($vaultAddr, '/') . '/v1/secret/data/' . ltrim($testPath, '/');

        $mockResponse = new MockResponse(json_encode([
            'data' => [
                'data' => [
                    'KEY1' => 'VALUE1',
                    'KEY2' => 'VALUE2'
                ]
            ]
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]);

        $httpClient = new MockHttpClient($mockResponse);

        $client = new VaultClient($vaultAddr, $vaultToken, $httpClient);
        $secrets = $client->getSecrets($testPath);

        $this->assertSame('GET', $mockResponse->getRequestMethod());
        $this->assertSame($expectedUrl, $mockResponse->getRequestUrl());
        $this->assertContains(
            'X-Vault-Token: your_test_token',
            $mockResponse->getRequestOptions()['headers']
        );

        $this->assertSame([
            'KEY1' => 'VALUE1',
            'KEY2' => 'VALUE2'
        ], $secrets);
    }
}
