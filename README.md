# Laravel Multimail

![Mail image](https://miro.medium.com/max/640/1*XAhO69eFPH6p32VlylUCaw.png)

[![Build Status](https://travis-ci.com/iwasherefirst2/laravel-multimail.svg?branch=master)](https://travis-ci.com/iwasherefirst2/laravel-multimail)
[![codecov](https://codecov.io/gh/iwasherefirst2/laravel-multimail/branch/master/graph/badge.svg?token=3X6ZVRR5EQ)](https://codecov.io/gh/iwasherefirst2/laravel-multimail)

This package helps you to send mails from your Laravel application from multiple email accounts.

The package supports sending queued, localized and bulk mails.

This package works for `SMTP` and `log` drivers.

## Table of Contents

- [Requirments](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Basic Examples](#basic-examples)
    - [Queued Mails](#queued-mails)
    - [Specify in Mailable](#specify-in-mailable)
    - [Bulk messages](#bulk-messages)
- [Special Settings](#special-settings)
    - [Multiple Mail Providers](#multiple-mail-providers)
    - [Default mailaccount](#default-mailaccount)
    - [Testing](#testing)
    - [Get Mail From Database](#get-mail-from-database)
    - [Troubleshoot](#troubleshoot)
- [For Package Developer](#for-package-developer)

## Requirements

Laravel 5, 6 or 7

## Installation

Install the package into your Laraval application with composer:

    composer require iwasherefirst2/laravel-multimail

Publish the config file:

    php artisan vendor:publish --provider="IWasHereFirst2\LaravelMultiMail\MultiMailServiceProvider" --tag=config

Configure your email clients in `config/multimail.php`:

    'emails'  => [
        'office@example.net' =>
            [
              'pass'     => env('first_mail_password'),
              'from_name'     => "Max Musterman",
            ],
        'contact@example.net'  =>
            [
              'pass'     => env('second_mail_password')
            ],
    ],

Make sure to put your credentials in the `.env` file, so they don't get tracked in your repository.

For each mail you may specify multiple columns:

Attribut | Functionality | required
--- | --- | ---
`pass` | Password of email account | yes
`username` | Username of email account, only neccessary if different from email address | no
`from_name` | Name that should appear in front of email | no
`provider` | Provider of email account, only necessary if mail host/encription/port is not default (see [here](#multiple-mail-providers) for more) | no

## Usage

One may send a mail using `\MultiMail` instead of `\Mail`. The methods `to`, `cc`, `bcc`, `locale` are exactly the same as provided by the [mail facade](https://laravel.com/docs/5.8/mail#sending-mail) (note that `locale` is only available since Laravel 5.6).

The `from` method from `MultiMail` needs a string of an email provided in `config/multimail.php`. You can pass optionaly a second parameter as from name instetad of using the default falue given in the config.
When using `send` or `queue` the mail will be send from the mail account specified in `cofing/multimail.php`.

### Basic Examples

This example assumes that `office@example.net` and `contact@example.net` have been specified in `config/multimail.php`.

    // Send mail from office@example.net
    \MultiMail::to($user)->from('office@example.com')->send(new \App\Mail\Invitation($user));

    // Send from malaccount email@gmail.com
    \MultiMail::to($user)->from('email@example.net')->locale('en')->send(new \App\Mail\Invitation($user));

### Queued Mails

Queued mails work exactly the same as for the normal [Mail](https://laravel.com/docs/5.8/mail#queueing-mail) facade,
i.e. they are either send explicitly be the `queue` method or the mailable class implements the `ShouldQueue` contract.

    // Queue Mail
    \MultiMail::from('contact@foo.org')->queue(new \App\Mail\Invitation($user));

It is of course necessary to install a [queue driver](https://laravel.com/docs/5.8/queues#driver-prerequisites).

### Specify in mailable

You may set `to`, `cc`, `bcc`, `locale` and `from`  in your mailable class. In this case, you could reduce the basic example from above to:

    // Send mail from office@example.net
    \MultiMail::send(new \App\Mail\Invitation($user));

Mailable:

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
      $this->to  = $user;
      $this->fromMailer = 'office@example.com'
      $this->locale('en');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.invitation')
                    ->subject('Invitation mail');
    }
    
    

### Bulk messages

For bulk messages, you may first require a mailer object. You can define a pause in seconds ($timeout) after a number of mails ($frequency) has been send.

	$mailer = \MultiMail::getMailer('office@example.com' , $timeout, $frequency);

Then you can iterate through your list.

    foreach($users as $user){
	$mailer->send(new \App\Mail\Invitation($user));
    };


## Special Settings

### Multiple Mail Providers

If you wish to send from mails with different provider, then you may create another provider in the `provider` array and reference it inside the `emails` array:


    'emails'  => [
        'office@example.net' =>
            [
              'pass'     => env('first_mail_password'),
              'username' => env('first_mail_username'),
              'from_name'     => "Max Musterman",   
                                                        // <------ no provider given because 'default' provider is used
            ],
        'contact@other_domain.net'  =>
            [
              'pass'     => env('second_mail_password'),
              'username' => env('second_mail_username'),
              'from_name'     => "Alice Armania",
              'provider' => 'new_provider',            // <------ specify new provider here
            ],
    ],

    'provider' => [
      'default' =>
        [
          'host'       => env('MAIL_HOST'),
          'port'       => env('MAIL_PORT'),
          'encryption' => env('MAIL_ENCRYPTION'),
          'driver'     => env('MAIL_DRIVER'),
        ],
      'new_provider' =>
        [
          'host'      => env('MAIL_HOST_PROVIDER_B'),
          'port'      => env('MAIL_PORT_PROVIDER_B'),
          'encryption' => env('MAIL_ENCRYPTION_PROVIDER_B'),
	  'driver'     => env('MAIL_DRIVER_B'),
	  // you may also add options like `stream`, `source_ip` and `local_domain`
        ]'
    ],



### Default mailaccount

You may provide `default` credentials inside the `email` array from `config/multimail.php`:

    'emails'  => [
        'office@example.net' =>
            [
              'pass'     => env('first_mail_password'),
              'username' => env('first_mail_username'),
              'from_name'     => "Max Musterman",
            ],
        'contact@example.net'  =>
            [
              'pass'     => env('second_mail_password'),
              'username' => env('second_mail_username'),
              'from_name'     => "Alice Armania",
            ],
        'default' =>
          [
            'pass'            => env('MAIL_PASSWORD'),
            'username'        => env('MAIL_USERNAME'),
          ]
    ],

When `first_mail_password` and `first_mail_username` are empty, `office@example.net` will use credentials specified by `default`. This is useful for your local development, when you want to send all mails from one mailaccount while testing. This way you only need to specify `MAIL_PASSWORD` and `MAIL_USERNAME` locally.

## Testing

#### Don't put credentials in local `env`

Do not specify any email accounts in your local `.env`. Otherwise you may risk to send testing mails to actual users.

#### Use one fake mail account or log

Use `log` driver or setup a fake mail SMTP account like [mailtrap](https://mailtrap.io/) or similar services.

It is not needed to specify the same credentials for all your email accounts. Instead, simply provide a default mail account (see above `Default mail account`).

#### Use log mail driver on testing

To avoid latency, I recommend to always use the `log` mail driver when `phpunit` is running. You can set the mail driver in your `phpunit.xml` file like this: `<env name="MAIL_DRIVER" value="log"/>`.

#### Use Mocking

If you want to use the mocking feature [Mail fake](https://laravel.com/docs/mocking#mail-fake) during your tests, enable `use_default_mail_facade_in_tests`
in your config file `config/multimail.php`. Note that `assertQueued` will never be true, because `queued` mails are actually send through `sent` through a job.

### Get Mail From Database

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

## Troubleshoot

#### Laravel 7 is not working

Please update to version 1.2.2 to support Laravel 7

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
