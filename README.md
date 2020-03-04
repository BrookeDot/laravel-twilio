# Twilio SDK Integration for Laravel [![Build Status](https://travis-ci.com/BabDev/laravel-twilio.svg?branch=master)](https://travis-ci.com/BabDev/laravel-twilio)

Laravel package integrating the Twilio SDK into your Laravel application.

## Installation

To install this package, run the following [Composer](https://getcomposer.org/) command:

```sh
composer require babdev/laravel-twilio
```

If your application is not using package discovery, you will need to add the service provider to your `config/app.php` file:

```sh
BabDev\Twilio\Providers\TwilioProvider::class,
```

Likewise, you will also need to register the facade in your `config/app.php` file if not using package discovery:

```sh
'TwilioClient' => BabDev\Twilio\Facades\TwilioClient::class,
``` 
