<?php

namespace Damienfern\VaultSymfonyBundle\Tests;

use Damienfern\VaultSymfonyBundle\VaultClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class VaultClientTest extends TestCase
{
    public function testGetSecrets()
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
        ]), ['http_code' => 200]);

        $httpClient = new MockHttpClient(function(string $method, string $url, array $options) use ($expectedUrl, $mockResponse, $vaultToken) {
            $this->assertSame('GET', $method);
            $this->assertSame($expectedUrl, $url);
            $this->assertArrayHasKey('headers', $options);
            $headers = $options['headers'];
            $found = false;
            foreach ($headers as $key => $value) {
                if (is_string($key)) {
                    if (strtolower($key) === 'x-vault-token' && $value === $vaultToken) {
                        $found = true; break;
                    }
                } elseif (is_string($value) && str_starts_with(strtolower($value), 'x-vault-token:')) {
                    if (trim(substr($value, strpos($value, ':') + 1)) === $vaultToken) {
                        $found = true; break;
                    }
                }
            }
            $this->assertTrue($found, 'Header X-Vault-Token non présent ou incorrect');
            return $mockResponse;
        });

        $client = new VaultClient($vaultAddr, $vaultToken, $httpClient);
        $secrets = $client->getSecrets($testPath);
        $this->assertIsArray($secrets);
        $this->assertSame('VALUE1', $secrets['KEY1']);
        $this->assertSame('VALUE2', $secrets['KEY2']);
    }
}
