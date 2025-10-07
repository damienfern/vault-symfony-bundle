<?php

namespace Damienfern\VaultSymfonyBundle\Factory;

use Damienfern\VaultSymfonyBundle\VaultClient;

abstract class VaultClientFactory
{
    abstract public function create(): VaultClient;
}
