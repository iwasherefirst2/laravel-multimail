# Laravel-MultiMail

This lightweight package helps you to send mails from your Laravel application from multiple email accounts and multipe providers,for example `office@domain.com`, `contact@domain.com`, and `info@test.net`.

The pacakge supports sending queued, localized and bulk mails.

This package works for `SMTP` and `log` drivers.

## Requirments

Laravel 5.6 or above. Also compatible with Laravel 6.0.

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
              'username' => env('first_mail_username'),
              'from'     => "Max Musterman",
            ],
        'contact@example.net'  =>
            [
              'pass'     => env('second_mail_password'),
              'username' => env('second_mail_username'),
              'from'     => "Alice Armania",
            ],
    ],

Make sure to put your credentials in the `.env` file, so they don't get tracked in your repository.

## Usage

One may send a mail using `\MultiMail` instead of `\Mail`. The methods `to`, `cc`, `bcc`, `locale` are exactly the same as provided by the [mail facade](https://laravel.com/docs/5.8/mail#sending-mail).

The `from` method from `MultiMail` needs a string of an email provided in `config/multimail.php`. When using `send` or `queue` the mail will be send from the mail account specified in `cofing/multimail.php`.

### Basic Examples

This example assumes that `office@example.net` and `contact@example.net` have been specified in `config/multimail.php`.

    // Send Mail - send mail from office@example.net, receiver should be specified in mailable
    \MultiMail::from('office@example.com')->send(new \App\Mail\Invitation($user, $form));

    // Send Mail with optional parameters 'to' and 'locale' from malaccount email@gmail.com
    \MultiMail::to('contact@foo.org')->from('email@example.net')->locale('en')->send(new \App\Mail\Invitation($user));

### Queued Mails

Queued mails work exactly the same as for the normal [Mail](https://laravel.com/docs/5.8/mail#queueing-mail) facade,
i.e. they are either send explicitly be the `queue` method or the mailable class implements the `ShouldQueue` contract.

    // Queue Mail
    \MultiMail::from('contact@foo.org')->queue(new \App\Mail\Invitation($user));

It is of course necessary to install a [queue driver](https://laravel.com/docs/5.8/queues#driver-prerequisites).

### Multiple Mail Providers & Drivers

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

When `first_mail_password` and `first_mail_username` are empty, `office@example.net` will use credentials specified by `default`. This is useful for your local development, when you want to send all mains from one mailaccount while testing.

### Advice

#### Production environment

In your production environment simply provide all credentials into your local `.env` file.

#### Local environment

Do not specify any email acounts in your local `.env`. Otherwise you may risk to send testing mails to actual users.
Instead use `log` driver or setup a fake mail SMTP account like [mailtrap](https://mailtrap.io/) or similar services.

If you want to use a fake mail SMPT account for testing, it is not needed to specifiy the same credentials for any email account. Instead, simply provide a default mail account (see above `Default mailaccount`).
