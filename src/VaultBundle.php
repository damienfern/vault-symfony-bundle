<?php

namespace Damienfern\VaultSymfonyBundle;

use Damienfern\VaultSymfonyBundle\Factory\AppRoleClientFactory;
use Damienfern\VaultSymfonyBundle\Factory\TokenClientFactory;
use Damienfern\VaultSymfonyBundle\Factory\VaultClientFactory;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference; // ajout pour référencer le service
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class VaultBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (isset($config['app_role']['role_id']) && isset($config['app_role']['secret_id'])) {
            $builder->register('vault.client_factory', AppRoleClientFactory::class)
                ->setArguments([
                    $config['address'],
                    $config['app_role']['role_id'],
                    $config['app_role']['secret_id'],
                    new Reference(HttpClientInterface::class) ?? new Reference(HttpClient::class),
                ])
                ->setPublic(false);
        } else {
            if (empty($config['token'])) {
                throw new \InvalidArgumentException('Vault token must be provided if AppRole authentication is not configured.');
            }
            $builder->register('vault.client_factory', TokenClientFactory::class)
                ->setArguments([
                    $config['address'],
                    $config['token'],
                    new Reference(HttpClientInterface::class) ?? new Reference(HttpClient::class),
                ])
                ->setPublic(false)
            ;
        }

        $builder->register(VaultEnvVarLoader::class)
            ->setArguments([
                $config['path'],
                new Reference('vault.client_factory')
            ])
            ->addTag('container.env_var_loader')
        ;
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->stringNode('path')
                    ->info('Path for your secrets in vault')
                    ->example('data/myapp/')
                    ->defaultValue('')
                ->end()
                ->stringNode('address')
                    ->info('Your vault address')
                    ->example('https://vault.example.com')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->stringNode('token')
                    ->info('Your vault token')
                ->end()
                ->arrayNode('app_role')
                    ->info('AppRole authentication configuration')
                    ->children()
                        ->scalarNode('role_id')
                            ->info('Your Role ID for AppRole authentication')
                        ->end()
                        ->scalarNode('secret_id')
                            ->info('Your Secret ID for AppRole authentication')
                        ->end()
                ->end()
            ->end();
    }
}
