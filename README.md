# BoxWhatsapp

![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Production%20Ready-success)

BoxWhatsapp is a lightweight and developer-focused PHP package designed to simplify WhatsApp message delivery through the Unipile API.

Built for modern PHP environments, BoxWhatsapp helps developers, startups, SaaS platforms, CRMs, automation systems, AI tools, and enterprise applications integrate WhatsApp messaging with a clean and maintainable architecture.

BoxWhatsapp is part of the TheBox ecosystem.

---

# Why BoxWhatsapp?

Working directly with WhatsApp APIs can quickly become complex due to:

- authentication management
- account discovery
- attendee formatting
- multipart requests
- API response normalization
- endpoint changes
- provider-side updates
- error handling
- infrastructure compatibility

BoxWhatsapp abstracts these technical layers and provides a clean PHP-oriented interface focused on simplicity, stability, and production readiness.

---

# Main Features

- Send WhatsApp messages easily
- Send messages to multiple recipients
- Send messages to WhatsApp groups
- Automatic account discovery
- Unified API responses
- Lightweight architecture
- Compatible with modern PHP environments
- Production-ready structure
- PSR-4 autoloading support
- Easy integration into existing PHP projects

---

# Installation

## Composer Installation

```bash
composer require the-box/box-whatsapp
```

---

## Manual Installation

You can also include the package manually:

```php
require "src/BoxWhatsapp.php";
```

---

# Requirements

- PHP 7.4 or higher
- PHP cURL extension enabled
- Valid Unipile API key
- Active Unipile account
- Internet access

---

# Enable cURL Extension

Open your `php.ini` file and enable cURL:

```ini
extension=curl
```

Then restart your terminal or web server.

Verify installation:

```bash
php -m
```

You should see:

```bash
curl
```

---

# Basic Configuration

```php
use BoxWhatsapp\BoxWhatsapp;

$whatsapp = new BoxWhatsapp();

$whatsapp->setKey('YOUR_UNIPILE_API_KEY');
$whatsapp->setDns('https://your-api-host/api/v1');
$whatsapp->setAccountId('YOUR_ACCOUNT_ID');
```

---

# Important About setDns()

The `setDns()` method must contain the complete API base URL.

Correct example:

```php
$whatsapp->setDns('https://your-domain.com/api/v1');
```

Incorrect example:

```php
$whatsapp->setDns('your-domain');
```

A wrong DNS or incomplete API URL is one of the most common causes of:

```text
HTTP 400
HTTP 401
HTTP 404
```

errors.

---

# Quick Start

## Send a Simple Message

```php
<?php

require "src/BoxWhatsapp.php";

use BoxWhatsapp\BoxWhatsapp;

$whatsapp = new BoxWhatsapp();

$whatsapp->setKey('YOUR_API_KEY');
$whatsapp->setDns('https://your-api-host/api/v1');
$whatsapp->setDest('+243977482151');

$result = $whatsapp->sendMessage('Hello there');

if ($result['success']) {
    echo "Message sent successfully";
} else {
    echo "Error: " . $result['error'];
}
```

---

# Usage Examples

## Send Verification Code

```php
$code = rand(100000, 999999);

$message = "Your verification code is: $code";

$whatsapp->sendMessage(
    $message,
    '+243000000000'
);
```

Use cases:

- user registration
- OTP verification
- login confirmation
- security validation

---

## Send Order Confirmation

```php
$message = "Your order #8452 has been confirmed.";

$whatsapp->sendMessage(
    $message,
    '+243000000000'
);
```

Use cases:

- ecommerce
- logistics
- delivery platforms
- payment confirmation

---

## Send Notification to Multiple Users

```php
$users = [
    '+243000000001',
    '+243000000002',
    '+243000000003'
];

$result = $whatsapp->sendMessageGroup(
    'New platform update available.',
    $users
);
```

Use cases:

- marketing campaigns
- maintenance notifications
- internal announcements
- system alerts

---

## Send Message to WhatsApp Group

```php
$result = $whatsapp->sendMessageToGroup(
    '123456789-123456@g.us',
    'Daily team report available.'
);
```

Use cases:

- team collaboration
- enterprise communication
- reporting systems
- operational monitoring

---

# Default Recipient

You can configure a default recipient:

```php
$whatsapp->setDest('+243000000000');

$whatsapp->sendMessage(
    'Automatic notification'
);
```

Useful for:

- scheduled tasks
- bots
- automated systems
- monitoring services

---

# API Response Structure

All methods return a normalized associative array.

## Success Response

```php
[
    'success' => true,
    'message_id' => 'message_id_here',
    'response' => [...],
    'http_code' => 200
]
```

## Error Response

```php
[
    'success' => false,
    'error' => 'Error message',
    'http_code' => 400,
    'details' => 'Additional details'
]
```

---

# Available Methods

## Configuration Methods

| Method | Description |
|---|---|
| `setKey(string $key)` | Define API key |
| `setDns(string $dns)` | Define API base URL |
| `setAccountId(string $id)` | Define account ID manually |
| `setDest(string $phone)` | Define default recipient |
| `setTimeout(int $seconds)` | Define request timeout |
| `setConnectTimeout(int $seconds)` | Define connection timeout |
| `setDebug(bool $debug)` | Enable debug mode |
| `setSslVerification(bool $verify)` | Enable or disable SSL verification |

---

## Messaging Methods

| Method | Description |
|---|---|
| `sendMessage(string $message, ?string $dest)` | Send a message |
| `sendMessageGroup(string $message, array $dests)` | Send to multiple recipients |
| `sendMessageToGroup(string $groupJid, string $message)` | Send to WhatsApp group |

---

# Automatic Account Discovery

If no account ID is defined manually, BoxWhatsapp automatically attempts to retrieve it from the API.

This simplifies integration but requires:

- valid API credentials
- correct DNS configuration
- accessible API endpoint

---

# Common Errors

## Invalid API Key

Example:

```text
HTTP 401
Unauthorized
```

Solution:

Verify your Unipile API key.

---

## Invalid DNS or Endpoint

Example:

```text
HTTP 400
HTTP 404
```

Solution:

Verify your complete API URL.

Example:

```php
https://your-domain.com/api/v1
```

---

## Missing Account ID

Example:

```text
Unable to retrieve account_id
```

Solution:

Either configure the account ID manually or verify API connectivity.

```php
$whatsapp->setAccountId('YOUR_ACCOUNT_ID');
```

---

## Invalid Phone Number

Phone numbers must use international format.

Correct:

```text
+243977000000
```

Incorrect:

```text
0977000000
```

---

# PHP 8.5+ Compatibility

Starting from PHP 8.5:

```php
curl_close()
```

is deprecated.

Because of this, BoxWhatsapp no longer relies on direct `curl_close()` usage by default, ensuring compatibility with modern PHP environments.

A legacy compatibility mode may still be enabled for older infrastructures when necessary.

---

# Why Updates Are Important

Unipile frequently evolves and updates its APIs.

These updates may include:

- endpoint modifications
- authentication changes
- payload structure updates
- attendee formatting changes
- response structure changes
- messaging behavior changes

Keeping BoxWhatsapp updated is strongly recommended.

Update regularly:

```bash
composer update the-box/box-whatsapp
```

Failing to update may lead to:

- HTTP errors
- broken integrations
- authentication failures
- deprecated endpoint usage
- unstable message delivery

---

# Recommended Production Practices

## Store Credentials in Environment Variables

```env
WHATSAPP_API_KEY=your_api_key
WHATSAPP_BASE_URL=https://your-domain.com/api/v1
```

---

## Use try/catch in Production

```php
try {

    $result = $whatsapp->sendMessage(
        'Server started successfully.'
    );

    print_r($result);

} catch (Exception $e) {

    echo $e->getMessage();
}
```

---

## Validate Numbers Before Sending

```php
if (!str_starts_with($number, '+')) {
    throw new Exception(
        'Invalid phone number format'
    );
}
```

---

# Recommended Use Cases

BoxWhatsapp can be used in:

- SaaS platforms
- ERP systems
- CRM systems
- authentication systems
- ecommerce applications
- delivery platforms
- educational platforms
- automation tools
- monitoring systems
- internal enterprise tools
- AI assistants
- chatbot systems

---

# TheBox Ecosystem

BoxWhatsapp is part of the TheBox ecosystem.

TheBox provides lightweight, modular, and production-ready developer tools focused on simplicity, speed, and maintainability.

---

# Support The Project

If this package helps you or your company, you can support the development of the project and future updates.

Your support helps maintain compatibility with evolving APIs such as Unipile and improves long-term ecosystem stability.

## Donation

[![Support via Donation](https://img.shields.io/badge/
Donate-Here-ff69b4?logo=heart&style=for-the-badge)](https://
jxzmkdpz.mychariow.shop/donation)

---

# Author

Developed by Exauce Stan Malka — Exauce Malumba.


- Github : https://github.com/Onestepcom00
- Linktree : https://linktr.ee/exaucestan.malka

