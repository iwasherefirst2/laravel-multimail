# MultiMail

**MultiMail** helps you to send mails from your Laravel application from multiple email accounts, e.g. `office@domain.com`, `contact@domain.com`, `do-not-reply@domain.com` etc.

Additionally, it offers help for sending queued, translatable or bulk mails and it ships with a helper function for salutation in your emails.

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

One may send a mail using `/iwasherefirst2/MultiMail` instead of `/Mail`. The following methods are supported

| Method | Desciption|
| ---- |------------|
| `to($receiver)` | `$receiver` should either be a email provided as a string, or an object that implements `\iwasherefirst2\Interface\Sendable` |
| `from($sender)` | `$sender` is one of the mails provided in `config/multimail.php` |
| `locale($locale)` | translate blade of mailable in a locale other than the current language, and will even remember this locale if the mail is queued |
| `send($mailable)` | Will send the message directly, requires a [mailable](https://laravel.com/docs/5.8/mail#generating-mailables) |
| `queue($mailable)` | Will send the message out in queue, requires a [mailable](https://laravel.com/docs/5.8/mail#generating-mailables) |

### Basic Examples

    // Send Mail
    /iwasherefirst2/MultiMail::to($to)->from('email@gmail.com')->locale('en')->send(new /App/Mail/Invitation($user, $form));

	// Queue Mail
    /iwasherefirst2/MultiMail::to($to)->from('email2@gmail.com')->locale('de')->queue(new /App/Mail/Invitation($user));

### Bulk messages

For bulk messages, you may first require a mailer object. You can define a pause in seconds ($timeout) after a number of mails ($frequency) has been send.

	$mailer = /iwasherefirst2/MultiMail::getMailer('email@gmail.com' , $timeout, $frequency);

Then you can iterate through your list. The methods of the mailer object are identical to the methods used in the `Mail` facade like `to`,`cc` , `bcc`, `send`, `locale` etc.

	foreach($users as $user){
		$mailer->to($user)->send(new /App/Mail/Invitation($user));
	};
