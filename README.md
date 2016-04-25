# SMSFly plugin for CakePHP
CakePHP SMSFly Plugin for [SMS Fly service](http://sms-fly.com/)


## Installation
### Step 1: Install plugin
You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require Sensetivity/SMSFly
```

### Step 2: Load plugin
```php
// your config/bootstrap.php file

Plugin::load('SMSFly', ['bootstrap' => true, 'routes' => false]);

```

### Step 3: Configure plugin
```php
// your plugins/SMSFly/config/bootstrap.php file

Configure::write('SMSFly.API.username', 'Your_username');
Configure::write('SMSFly.API.password', 'Your_password');
Configure::write('SMSFly.API.price', 0.247);
Configure::write('SMSFly.API.source', 'InfoCentr');

```

### Step 4: Usage
```php
// your controller

$this->loadComponent('SMSFly.SMSFly');

//Then use for one:
$this->SMSFly->sendSMS('380930001100', SMS body message');

//or many:
$this->SMSFly->sendSMSToMany([
//        380930001100,
//        380970001100
//   ], 'Тестовий текст для багатьох номерів');

```