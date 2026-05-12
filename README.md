# BoxWhatsapp

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Production%20Ready-success)

The WhatsApp Box is part of the TheBox family — a lightweight PHP package that allows you to send WhatsApp messages easily.
It can be extremely useful in real-world scenarios such as user registration forms, where you can send a verification code or confirmation message directly via WhatsApp once an account is created.

---

## Installation

```bash
composer require the-box/whatsapp-box
```

Or include the file directly:

```php
require "src/BoxWhatsapp.php";
```

---

## Configuration

```php
use BoxWhatsapp\BoxWhatsapp;

$whatsapp = new BoxWhatsapp();
$whatsapp->setKey('SET_YOUR_API_UNIPILE');
$whatsapp->setDns('SET_YOUR_DSN');
$whatsapp->setAccountId('YOUR_ACCOUNT_ID');
```

---

## Usage

### Simple Message

```php
$result = $whatsapp->sendMessage('Hello!', '+2430000000000');

if ($result['success']) {
    echo "Message sent successfully. ID: " . $result['message_id'];
} else {
    echo "Error: " . $result['error'];
}
```

### Message with default recipient

```php
$whatsapp->setDest('+2430000000000');
$result = $whatsapp->sendMessage('Automatic message');
```

### Group Message

```php
$recipients = ['+2430000000000', '+2431111111111'];
$results = $whatsapp->sendMessageGroup('Special promotion!', $recipients);
```

### Message to WhatsApp Group

```php
$result = $whatsapp->sendMessageToGroup('123456789-123456@g.us', 'Group message');
```

---

## API Response

All methods return an associative array:

```php
[
    'success' => true|false,
    'message_id' => 'message_id', // if success
    'response' => [...], // full API response
    'error' => 'error_message', // if failure
    'http_code' => 200, // HTTP code
    'details' => '...' // additional details
]
```

---

## Requirements

- PHP 8.0+
- cURL extension (optional - uses file_get_contents by default)
- Unipile account with valid API key

---

## Available Methods

### Configuration
- `setKey(string $key)` - Set API key
- `setDns(string $dns)` - Set API URL
- `setAccountId(string $id)` - Set account ID
- `setDest(string $phone)` - Set default recipient
- `setTimeout(int $seconds)` - Set timeout

### Message Sending
- `sendMessage(string $message, ?string $dest)` - Send a message
- `sendMessageGroup(string $message, array $dests)` - Send to multiple recipients
- `sendMessageToGroup(string $groupJid, string $message)` - Send to a group

---

## Common Errors

**Missing Account ID**: Use `setAccountId()` to configure manually.

**Invalid Number**: Numbers must be in international format with `+`.

**Invalid API Key**: Check your key in the Unipile dashboard.

---

---
## 🧩 Part of **TheBox**
This package is designed to integrate seamlessly with the 
**TheBox** ecosystem.  
For more modules and utilities, check out other TheBox projects.
> You can Donate Me
<br>
[![Support via Donation](https://img.shields.io/badge/
Donate-Here-ff69b4?logo=heart&style=for-the-badge)](https://
jxzmkdpz.mychariow.shop/donation)
---
### ✨ Author
Developed with ❤️ by **Exauce Stan Malka -  Exauce Malumba**  
[GitHub](https://github.com/Onestepcom00) • [Website](https://
linktr.ee/exaucestan.malka)