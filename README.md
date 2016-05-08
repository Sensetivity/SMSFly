# SMSFly plugin for CakePHP
CakePHP SMSFly Plugin for [SMS Fly service](http://sms-fly.com/)


## Installation
### Step 1: Install plugin
You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require sensetivity/sms-fly": "^1.0.3"
```

### Step 2: Load plugin
```php
// your config/bootstrap.php file

Plugin::load('SMSFly', ['bootstrap' => false, 'routes' => false]);

```

### Step 3: Usage & Configure
To use this plugin just load it on your controller
```php
// your controller

$this->loadComponent('SMSFly.SMSFly', [
    'username' => 'YOUR_USERNAME',
    'password' => 'YOUR_PASSWORD',
]);
```

After that you can use plugin. It`s easy,
```php
// your controller


// Send for one:
$this->SMSFly->sendSMS(380930001100, 'Some SMS-body message');

// Send SMS to many:
$this->SMSFly->sendSMSToMany([
        380930001100,
        380970001100
   ], 'Тестовий текст для багатьох номерів'); // Cyrillic also working.
```

### Method Examples

```php
// Send SMS for one user.
$this->SMSFly->sendSMS(380930001100, 'Some SMS-body message');

// Send SMS with same text for many users.
$this->SMSFly->sendSMSToMany([
        380930001100,
        380970001100
   ], 'Тестовий текст для багатьох номерів'); // Cyrillic also working.

// Check balance on your account.
$this->SMSFly->getBalance();

// Check the SMS count that left on your account.
$this->SMSFly->getSMSCount();
```