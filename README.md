# MultiMail

**MultiMail** helps you to send mails from your Laravel application from multiple email accounts, e.g. `office@domain.com`, `contact@domain.com`, `do-not-reply@domain.com` or 'gandalf@shire.org' etc.

Additionally, it offers help for sending queued, translatable or bulk mails.

## Requirments

Laravel 5.6 or above.

## Installation

Install the package into your Laraval application with composer:

    composer require iwasherefirst2/multimail

Publish the config file:

    php artisan vendor:publish --provider="IWasHereFirst2\MultiMail\MultiMailServiceProvider"

Configure your email clients in `config/multimail.php`:

    'emails'  => [
			'email@gmail.com' =>
			       [
					'pass' => env('mail_pass_1'),
	               'username' => env('mail_username_1'),
				   'from' => "Max Musterman",
				   ],
		  'email2@gmail.com' =>
			       [
					'pass' => env('mail_pass_2'),
	               'username' => env('mail_username_2'),
				   'from' => "Alice Tagarien",
				   ],

If you want to send out queued emails please install a [queue driver](https://laravel.com/docs/5.8/queues#driver-prerequisites).

## Usage

One may send a mail using `/MultiMail` instead of `/Mail`. The methods `to`, `cc`, `bcc`, `locale` are exactly the same as provided by the [mail facade](https://laravel.com/docs/5.8/mail#sending-mail).
The following three methods from `MultiMail` are different though:

| Method | Desciption|
| ---- |------------|
| `from($sender)` | `$sender` has to be one of the mails provided in `config/multimail.php` |
| `send($mailable)` | Will send the message through the mail account provided by `from`, requires a [mailable](https://laravel.com/docs/5.8/mail#generating-mailables) |
| `queue($mailable)` | Will queue the message from the mail account provided by `from`, requires a [mailable](https://laravel.com/docs/5.8/mail#generating-mailables) |

### Basic Examples

    // Send Mail - minimal example, receiver should be specified in mailable
    /MultiMail::from('email@gmail.com')->send(new /App/Mail/Invitation($user, $form));

    // Send Mail with optional parameters "to" and "locale"
    /MultiMail::to('example@example.com)->from('email@gmail.com')->locale('en')->send(new /App/Mail/Invitation($user));

	  // Queue Mail
    /MultiMail::from('email2@gmail.com')->queue(new /App/Mail/Invitation($user));

### Bulk messages

For bulk messages, you may first require a mailer object. You can define a pause in seconds ($timeout) after a number of mails ($frequency) has been send.

	$mailer = /MultiMail::getMailer('email@gmail.com' , $timeout, $frequency);

Then you can iterate through your list. The methods of the mailer object are identical to the methods used in the `Mail` facade like `to`,`cc` , `bcc`, `send`, `locale` etc.

	foreach($users as $user){
		$mailer->to($user)->send(new /App/Mail/Invitation($user));
	};
