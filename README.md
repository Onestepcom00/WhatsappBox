# 📦 The WhatsApp Box

![PHP Version](https://img.shields.io/badge/PHP-8%2B-blue)
![Composer](https://img.shields.io/badge/Composer-Ready-orange)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Stable-success)
![Made with ❤️](https://img.shields.io/badge/Made%20with-❤️-red)

---

**The WhatsApp Box** is part of the **TheBox** family — a lightweight PHP package that allows you to send WhatsApp messages easily.  
It can be extremely useful in real-world scenarios such as **user registration forms**, where you can send a **verification code** or **confirmation message** directly via WhatsApp once an account is created.

---

## 🚀 Requirements

Before using this package, make sure you have the following:

- PHP **8.0+**
- **Composer** installed
- **cURL** extension enabled
- A **Unipile** account, with an **API key** and **DNS endpoint** → [https://www.unipile.com/](https://www.unipile.com/)

---

## ⚙️ Installation

Install the package via Composer:

```bash
composer install the-box/whatsapp-box
```

---

## 💡 Usage Example

```php
<?php

use BoxWhatsapp\BoxWhatsapp;

$whatsapp = new BoxWhatsapp();

// Configure your Unipile API key and DNS (base URL of your Unipile instance)
$whatsapp->setKey('ENTER_YOUR_API_KEY');
$whatsapp->setDns('https://your-unipile-host/api');

// Optional: Set a default recipient number
$whatsapp->setDest('+2430000000000');

// 1️⃣ Simple message (uses the default number)
$result = $whatsapp->sendMessage('Hello there');

// 2️⃣ Direct message with a specific number
$result2 = $whatsapp->sendMessage('Hi again!', '+2430000000000');

// 3️⃣ Send to multiple recipients
$result3 = $whatsapp->sendMessageGroup('Hello everyone!', ['+2430000000000', '+2431111111111']);

// 4️⃣ (Optional) Send to a WhatsApp group using JID
// Example JID: 123456789-123456@g.us
// $result4 = $whatsapp->sendMessageToGroup('123456789-123456@g.us', 'Hello Group!');
```

---

## 🧠 Notes

- The package automatically retrieves your `account_id` via the `/accounts` endpoint.  
  If needed, you can manually set it with `$whatsapp->setAccountId('YOUR_ACCOUNT_ID');`.
- Sending requests uses `multipart/form-data` on the `/chats` endpoint with `attendees_ids` and `text` fields.
- Numbers are automatically converted to the WhatsApp JID format:  
  `+243...` → `243...@s.whatsapp.net`.

---

## 🧩 Part of **TheBox**
This package is designed to integrate seamlessly with the **TheBox** ecosystem.  
For more modules and utilities, check out other TheBox projects.

> You can Donate Me
<br>

[![Support via Donation](https://img.shields.io/badge/Donate-Here-ff69b4?logo=heart&style=for-the-badge)](https://jxzmkdpz.mychariow.shop/donation)
---

### ✨ Author
Developed with ❤️ by **Exauce Stan Malka -  Exauce Malumba**  
[GitHub](https://github.com/Onestepcom00) • [Website](https://linktr.ee/exaucestan.malka)

