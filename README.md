# Bobospay PHP
The Bobospay PHP library provides convenient access to the Bobospay API from applications written in the PHP language. It includes a pre-defined set of classes for API resources that initialize themselves dynamically from API responses which makes it compatible with a wide range of versions of the Bobospay API.

You can sign up for a Bobospay account at https://bobospay.com.

## Requirements

PHP 5.5 and later.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require bobospay/bobospay-php
```

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):

```php
require_once('vendor/autoload.php');
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/bobospay/bobospay-php/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once('/path/to/bobospay-php/init.php');
```

## Dependencies

The bindings require the following extension in order to work properly:

- [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
- [`json`](https://secure.php.net/manual/en/book.json.php)
- [`openssl`](https://secure.php.net/manual/en/book.openssl.php)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Getting Started

Simple usage looks like:

## Documentation

Please see https://docs.bobospay.com for up-to-date documentation.

## Development

Install dependencies:

``` bash
composer install
```
