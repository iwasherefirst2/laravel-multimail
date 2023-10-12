# Laravel Multimail

![Mail image](https://miro.medium.com/max/640/1*XAhO69eFPH6p32VlylUCaw.png)

[![CircleCI](https://circleci.com/gh/iwasherefirst2/laravel-multimail/tree/master.svg?style=svg)](https://circleci.com/gh/iwasherefirst2/laravel-multimail/tree/master)
[![codecov](https://codecov.io/gh/iwasherefirst2/laravel-multimail/branch/master/graph/badge.svg?token=3X6ZVRR5EQ)](https://codecov.io/gh/iwasherefirst2/laravel-multimail)

This package helps you to send mails from your Laravel application from multiple email accounts.

This package works for all mail drivers supported by [Laravel 9](https://laravel.com/docs/9.x/mail#driver-prerequisites) or [Laravel 10](https://laravel.com/docs/10.x/mail#driver-prerequisites).
Queued mails work aswell.

## Table of Contents

- [Do I need this Package](#do-i-need-this-package)
- [Requirments](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Upgrade guide from 1.* to 2.*](#upgrade-guide-from-1-to-2) 
- [Get Mail From Database](#get-mail-from-database)
- [Testing](#testing) 
- [For Package Developer](#for-package-developer)

## Do I need this Package

### Laravel 

Since [Laravel 7](https://laravel.com/docs/7.x/upgrade) you can define multiple mail driver
and specify the mailer on the [Mail facade](https://laravel.com/docs/7.x/mail#sending-mail):

``` 
Mail::mailer('postmark')
        ->to($request->user())
        ->send(new OrderShipped($order));
```

The `from` mail address can be defined for each mailable: https://laravel.com/docs/7.x/mail#configuring-the-sender

Thus, since Laravel 7, you can send from multiple mail accounts. However, you need to specifiy the mail driver on the mail facade and the 
"from" mail address in the Mailable.

### MultiMail Package

In the MultiMail package, the email and mail driver combination is stored either in `config/multimail.php` or in the database.
Thus, the mail driver does not need to be mentioned explicitly.
When using MultiMail, sending an email may look like this:

``` 
MultiMail::from('info@example.com')
        ->to($request->user())
        ->send(new OrderShipped($order));
```

The "from" does not need to be specified in the Mailable. If you do specify an "from" name in the Mailable, it will
overwrite the MultiMail setting.

### Summary

The following three cases are situations where the package may be beneficial to you:

- You need support to send mails from multiple accounts for a Laravel 5 or 6 application.
- You want to send mails from mail accounts that are stored in a database
- You don't want to define the "from" email in the Mailable and the "driver" on the facade separately.

## Requirements

| Laravel Version | MultiMail Version |
|-----------------|-------------------|
| 5,6,7,8         | 1.3.7             |
| 9, 10           | 2.*               |

These are the instructions for MultiMail 2.*.
If you want to install MultiMail 1.3.7, please refer to the [MultiMail 1.3.7 Readme](https://github.com/iwasherefirst2/laravel-multimail/tree/1.3.7).


## Installation

Install the package into your Laraval application with composer:

    composer require iwasherefirst2/laravel-multimail

Publish the config file:

    php artisan vendor:publish --provider="IWasHereFirst2\LaravelMultiMail\MultiMailServiceProvider" --tag=config

Configure your email clients in `config/multimail.php`:

    'emails'  => [
        'office@example.net' =>
            [
              'from_name'     => "Max Musterman",
            ],
        'contact@example.net'  =>
            [
                'driver' => 'smtp',
                'from_name' => 'Karl Peter'
            ],
    ],

For each mail you may specify multiple columns (no fields are required, you can also have an empty array):

| Attribut        | Functionality                                      |
|-----------------|----------------------------------------------------|
| `from_name`     | The name in front of the email                     |
| `driver`        | The maildriver specified in config('mail.mailers') |
| `reply_to_mail` | email to reply to                                  |
| `reply_to_name` | name to reply to                                   |
| `return_path`   | path of return                                     |

## Usage

You have to call "from" method from MultiMail, providing an email from `config/multimail.php`.

### Basic Examples

This example assumes that `office@example.net` and `contact@example.net` have been specified in `config/multimail.php`.

    // Send mail from office@example.net
    \MultiMail::from('office@example.com')->to($user)->send(new \App\Mail\Invitation($user));

    // Send from malaccount email@gmail.com
    \MultiMail::from('email@example.net')->to($user)->locale('en')->send(new \App\Mail\Invitation($user));

## Upgrade guide from 1.* to 2.*

1. The provider array has been removed from the MultiMail config. Maildriver belong now to config('mail.mailers')
2. You cannot set the `fromMailer` attribute in Mailables anymore. 
3. MultiMail must be followed immediately by "from". `` \MultiMail::to($user)->from('email@example.net')` is not allowed.
4. The setting `use_default_mail_facade_in_tests` has been removed from the config.

## Get Mail From Database

If you want to load your mail account configuration from database
publish the package migrations:

    php artisan vendor:publish --provider="IWasHereFirst2\LaravelMultiMail\MultiMailServiceProvider" --tag=migrations

In your migration folder are now two tabels, email_accounts and email_providers

Instead of adding emails to the config they should be added to the table email_accounts.

Make sure to update your config `config/multimail.php`:

    <?php
    
    return [
        
        'mail_settings_class' => \IWasHereFirst2\LaravelMultiMail\DatabaseConfigMailSettings::class,

        //...
    ];

The emails will now be read from the database instead from the configuration file.
If no provider is provided in email_accounts (column provider is nullable),
then the default profider from `config/multimail.php` will be considerd.

If you want to make customizations, copy the class `\IWasHereFirst2\LaravelMultiMail\DatabaseConfigMailSettings`
somewhere in your application, adjust the namespace, and update the reference `mail_settings_class` in your config file.

## Testing

#### Don't put credentials in local `env`

Do not specify any email accounts in your local `.env`. Otherwise you may risk to send testing mails to actual users.

#### Use one fake mail account or log

Use `log` driver or setup a fake mail SMTP account like [mailtrap](https://mailtrap.io/) or similar services.

It is not needed to specify the same credentials for all your email accounts. Instead, simply provide a default mail account (see above `Default mail account`).

#### Use log mail driver on testing

To avoid latency, I recommend to always use the `log` mail driver when `phpunit` is running. You can set the mail driver in your `phpunit.xml` file like this: `<env name="MAIL_DRIVER" value="log"/>`.

## For Package Developer

If you plan to contribute to this package, please make sure that the unit tests aswell as the integration tests 
all succeed. In order to test the integration tests please create a free mailtraip account, copy `tests/.env.example` 
to `tests/.env` and add your mailtrap API credentials in `tests/.env`. The integration tests will now send 
a test email to your mailtrap account and verify through the API if the mail was successfully send. 

The package ships with a Dockerfile to make it easy to run the tests for you. Simply follow these steps:

    docker-compose up --build 
    docker-compose exec app bash 
    composer install
    ./vendor/bin/phpunit 
