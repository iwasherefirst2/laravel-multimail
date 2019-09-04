# Laravel-MultiMail

This lightweight package helps you to send mails from your Laravel application from multiple email accounts, e.g. `office@domain.com`, `contact@domain.com`, `do-not-reply@domain.com`.

It supports sending queued, localized and bulk mails.

In addition, its also possible to send from multiple providers/hosts (its recommended to send bulk and billing mails from different mail servers).

## Requirments

Laravel 5.6 or above. Also compatible with Laravel 6.0.

## Installation

Install the package into your Laraval application with composer:

    composer require iwasherefirst2/laravelmultimail

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
The following three methods from `MultiMail` are different though:

| Method | Desciption|
| ---- |------------|
| `from($sender)` | `$sender` has to be one of the mails provided in `config/multimail.php` |
| `send($mailable)` | Will send the message through the mail account provided by `from`, requires a [mailable](https://laravel.com/docs/5.8/mail#generating-mailables) |
| `queue($mailable)` | Will queue the message from the mail account provided by `from`, requires a [mailable](https://laravel.com/docs/5.8/mail#generating-mailables) |

### Basic Examples

    // Send Mail - minimal example, receiver should be specified in mailable
    \MultiMail::from('office@example.com')->send(new \App\Mail\Invitation($user, $form));

    // Send Mail with optional parameters 'to' and 'locale'
    \MultiMail::to('contact@foo.org')->from('email@gmail.com')->locale('en')->send(new \App\Mail\Invitation($user));

	  // Queue Mail
    \MultiMail::from('contact@foo.org')->queue(new \App\Mail\Invitation($user));

### Queued Mails

Queued mails work exactly the same as for the normal [Mail](https://laravel.com/docs/5.8/mail#queueing-mail) facade,
i.e. they are either send explicitly be the `queue` method or the mailable class implements the `ShouldQueue` contract.

It is of course necessary to install a [queue driver](https://laravel.com/docs/5.8/queues#driver-prerequisites).

### Bulk messages

For bulk messages, you may first require a mailer object. You can define a pause in seconds ($timeout) after a number of mails ($frequency) has been send.

	$mailer = \MultiMail::getMailer('office@example.com' , $timeout, $frequency);

Then you can iterate through your list. The methods of the mailer object are identical to the methods used in the `Mail` facade like `to`,`cc` , `bcc`, `send`, `locale` etc.

	foreach($users as $user){
		$mailer->to($user)->send(new \App\Mail\Invitation($user));
	};

### Mailer Customization

If you wish to add more SwiftMailer methods to your mailer, you can get the mailer object through

    $mailer = \MultiMail::getMailer('office@example.com');

### Multiple Mail Providers

If you wish to send from mails with different hosts, then create another provider in the `provider` array and reference it inside the `emails` array:


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
          'host'      => env('MAIL_HOST'),
          'port'      => env('MAIL_PORT'),
          'encryption' => env('MAIL_ENCRYPTION'),
        ],
      'new_provider' =>
        [
          'host'      => env('MAIL_HOST_PROVIDER_B'),
          'port'      => env('MAIL_PORT_PROVIDER_B'),
          'encryption' => env('MAIL_ENCRYPTION_PROVIDER_B'),
        ]'
    ],
