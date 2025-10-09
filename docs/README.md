# VaultBundle

## About

This bundle integrates [Hashicorp Vault](https://www.hashicorp.com/fr/products/vault) secrets into your Symfony application by injecting secrets in environment variables.

## Installation

Add the `damienfern/vault-symfony-bundle` package to your `require` section in the `composer.json` file.

``` bash
$ composer require damienfern/vault-symfony-bundle
```

## Usage

### Using token authentication

To use token authentication, you need to configure the bundle with the `token` obtained from Vault.

Here is an example configuration:

```yaml
# config/packages/vault.yaml
vault:
    address: 'http://localhost:8200' # Vault server address
    path: 'myapp/config' # Path to your secrets in Vault
    token: 'yourtoken' # Your Vault token
```

### Using AppRole authentication

To use AppRole authentication, you need to configure the bundle with the `role_id` and `secret_id` obtained from Vault.

Here is an example configuration:

```yaml
# config/packages/vault.yaml
vault:
    address: 'http://localhost:8200' # Vault server address
    path: 'myapp/config' # Path to your secrets in Vault
    app_role:
        role_id: ''
        secret_id: ''
```
