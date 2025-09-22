# Bobospay PHP SDK

The Bobospay PHP SDK enables seamless integration with Bobospay's payment processing API for PHP applications. It provides tools for managing transactions, customers, and currencies, making it easy to process online payments via credit cards and mobile money in your PHP applications. Compatible with popular frameworks such as Laravel, Symfony, and CodeIgniter.

You can sign up for a Bobospay account at [https://bobospay.com](https://bobospay.com).

## Requirements

- PHP 5.6 and later
- cURL extension
- JSON extension  
- OpenSSL extension

## Installation

### Composer (Recommended)

You can install the SDK via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require bobospay/bobospay-php
```

### Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/bobospay/bobospay-php/releases). Then, to use the SDK, include the `init.php` file:

```php
require_once('/path/to/bobospay-php/init.php');
```

## Getting Started

### Basic Configuration

First, configure your Bobospay credentials and environment:

```php
use Bobospay\Bobospay;

// Set your client credentials
Bobospay::setClientId('YOUR_CLIENT_ID');
Bobospay::setClientSecret('YOUR_CLIENT_SECRET');

// Set environment (sandbox for testing, live for production)
Bobospay::setEnvironment('sandbox'); // or 'live'
```

### Use Cases

#### Creating a Customer

```php
use Bobospay\Bobospay;
use Bobospay\Customer;

// Configure Bobospay
Bobospay::setClientId('YOUR_CLIENT_ID');
Bobospay::setClientSecret('YOUR_CLIENT_SECRET');
Bobospay::setEnvironment('sandbox');

// Create a customer
$customer = Customer::create([
    'firstname' => 'John',
    'lastname' => 'Doe',
    'email' => 'john.doe@example.com',
    'phone' => '+22966666600'
]);

echo "Customer created with ID: " . $customer->id;
```

#### Creating a Transaction

```php
use Bobospay\Bobospay;
use Bobospay\Transaction;

// Configure Bobospay
Bobospay::setClientId('YOUR_CLIENT_ID');
Bobospay::setClientSecret('YOUR_CLIENT_SECRET');
Bobospay::setEnvironment('sandbox');

// Create a transaction
$transaction = Transaction::create([
    'description' => 'Payment for order #1234',
    'amount' => 1000, // Amount in smallest currency unit (e.g., cents)
    'currency' => ['iso' => 'XOF'],
    'callback_url' => 'https://example.com/callback',
    'customer' => ['id' => 1] // Customer ID from previous example, you use email, or provide all customer details
    'custom_data' => ['order_id' => '1234']
]);

//$transaction = Transaction::create([
//    'description' => 'Payment for order #1234',
//    'amount' => 1000, // Amount in smallest currency unit (e.g., cents)
//    'currency' => ['iso' => 'XOF'],
//    'callback_url' => 'https://example.com/callback',
//    'customer' => [
//        'firstname' => 'John',
//        'lastname' => 'Doe',
//        'email' => 'john.doe@example.com',
//        'phone' => '+22966666600'
//    ],
//]);

echo "Transaction created with ID: " . $transaction->id;
```

#### Retrieving a Transaction

```php
use Bobospay\Bobospay;
use Bobospay\Transaction;

// Configure Bobospay
Bobospay::setClientId('YOUR_CLIENT_ID');
Bobospay::setClientSecret('YOUR_CLIENT_SECRET');
Bobospay::setEnvironment('sandbox');

// Retrieve a specific transaction
$transaction = Transaction::retrieve('transaction_id_here');
echo "Transaction status: " . $transaction->status;
```

#### Listing All Customers

```php
use Bobospay\Bobospay;
use Bobospay\Customer;

// Configure Bobospay
Bobospay::setClientId('YOUR_CLIENT_ID');
Bobospay::setClientSecret('YOUR_CLIENT_SECRET');
Bobospay::setEnvironment('sandbox');

// Get all customers
$customers = Customer::all();
foreach ($customers->customers as $customer) {
    echo "Customer: " . $customer->firstname . " " . $customer->lastname . "\n";
}
```

#### Managing Currencies

```php
use Bobospay\Bobospay;
use Bobospay\Currency;

// Configure Bobospay
Bobospay::setClientId('YOUR_CLIENT_ID');
Bobospay::setClientSecret('YOUR_CLIENT_SECRET');
Bobospay::setEnvironment('sandbox');

// Get all available currencies
$currencies = Currency::all();
foreach ($currencies->currencies as $currency) {
    echo "Currency: " . $currency->name . " (" . $currency->iso . ")\n";
}

// Get a specific currency
$currency = Currency::retrieve('currency_id_here');
echo "Currency details: " . $currency->name;
```

#### Generating Payment Token

```php
use Bobospay\Bobospay;
use Bobospay\Transaction;

// Configure Bobospay
Bobospay::setClientId('YOUR_CLIENT_ID');
Bobospay::setClientSecret('YOUR_CLIENT_SECRET');
Bobospay::setEnvironment('sandbox');

// Create transaction first
$transaction = Transaction::create([
    'description' => 'Payment for order #1234',
    'amount' => 1000,
    'currency' => ['iso' => 'XOF'],
    'callback_url' => 'https://example.com/callback'
]);

// Generate payment token
$token = $transaction->generateToken();
echo "Payment token: " . $token->token;
```

## Documentation

Please see [https://docs.bobospay.com](https://docs.bobospay.com) for up-to-date API documentation.

## Development

Install dependencies:

```bash
composer install
```

Run tests:

```bash
composer test
```