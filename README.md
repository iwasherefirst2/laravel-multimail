# Laravel-MultiMail

This package helps you to send mails from your Laravel application from multiple email accounts and multiple providers,for example `office@domain.com`, `contact@domain.com`, and `info@test.net`.

The package supports sending queued, localized and bulk mails.

This package works for `SMTP` and `log` drivers.

## Table of Contents

- [Requirments](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Basic Examples](#basic-examples)
    - [Queued Mails](#queued-mails)
    - [Specify in Mailable)(#specify-in-mailable)
    - [Multiple Mail Providers](#multiple-mail-providers)
    - [Bulk messages](#bulk-messages)
    - [Default mailaccount](#default-mailaccount)
    - [Advice](#advice)

## Requirements

Laravel 5 or Laravel 6.

## Installation

Install the package into your Laraval application with composer:

    composer require iwasherefirst2/laravel-multimail

Publish the config file:

    php artisan vendor:publish --provider="IWasHereFirst2\LaravelMultiMail\MultiMailServiceProvider"

Configure your email clients in `config/multimail.php`:

    'emails'  => [
        'office@example.net' =>
            [
              'pass'     => env('first_mail_password'),
              'from'     => "Max Musterman",
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
`from` | Name that should appear in front of email | no
`provider` | Provider of email account, only necessary if mail host/encription/port is not default (see [here](#multiple-mail-providers) for more) | no

## Usage

One may send a mail using `\MultiMail` instead of `\Mail`. The methods `to`, `cc`, `bcc`, `locale` are exactly the same as provided by the [mail facade](https://laravel.com/docs/5.8/mail#sending-mail) (note that `locale` is only available since Laravel 5.6).

The `from` method from `MultiMail` needs a string of an email provided in `config/multimail.php`. When using `send` or `queue` the mail will be send from the mail account specified in `cofing/multimail.php`.

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

### Multiple Mail Providers 

If you wish to send from mails with different provider, then you may create another provider in the `provider` array and reference it inside the `emails` array:


    'emails'  => [
        'office@example.net' =>
            [
              'pass'     => env('first_mail_password'),
              'username' => env('first_mail_username'),
              'from'     => "Max Musterman",   
                                                        // <------ no provider given because 'default' provider is used
            ],
        'contact@other_domain.net'  =>
            [
              'pass'     => env('second_mail_password'),
              'username' => env('second_mail_username'),
              'from'     => "Alice Armania",
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
        ]'
    ],


### Bulk messages

For bulk messages, you may first require a mailer object. You can define a pause in seconds ($timeout) after a number of mails ($frequency) has been send.

	$mailer = \MultiMail::getMailer('office@example.com' , $timeout, $frequency);

Then you can iterate through your list.

    foreach($users as $user){
	$mailer->send(new \App\Mail\Invitation($user));
    };


### Default mailaccount

You may provide `default` credentials inside the `email` array from `config/multimail.php`:

    'emails'  => [
        'office@example.net' =>
            [
              'pass'     => env('first_mail_password'),
              'username' => env('first_mail_username'),
              'from'     => "Max Musterman",
            ],
        'contact@example.net'  =>
            [
              'pass'     => env('second_mail_password'),
              'username' => env('second_mail_username'),
              'from'     => "Alice Armania",
            ],
        'default' =>
          [
            'pass'            => env('MAIL_PASSWORD'),
            'username'        => env('MAIL_USERNAME'),
          ]
    ],

When `first_mail_password` and `first_mail_username` are empty, `office@example.net` will use credentials specified by `default`. This is useful for your local development, when you want to send all mails from one mailaccount while testing. This way you only need to specify `MAIL_PASSWORD` and `MAIL_USERNAME` locally.

### Advice

#### Production environment

In your production environment simply provide all credentials into your local `.env` file.

#### Local environment

Do not specify any email accounts in your local `.env`. Otherwise you may risk to send testing mails to actual users.
Instead use `log` driver or setup a fake mail SMTP account like [mailtrap](https://mailtrap.io/) or similar services.

If you want to use a fake mail SMPT account for testing, it is not needed to specify the same credentials for any email account. Instead, simply provide a default mail account (see above `Default mail account`).
