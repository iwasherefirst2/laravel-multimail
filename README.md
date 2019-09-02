# MultiMail 

**MultiMail** helps you to send mails from your Laravel application from multiple different email accounts. It also offers help for sending queued, translatable or bulk mails.

## Requirments

Laravel 5.5 or above.


## Installation 

Install the package into your Laraval application with composer:

    composer require iwasherefirst2/multimail 

Configure your email clients in `config/multimail.php`: 

    'emails'  => [ 'pass' => env('mail_pass_1'),
	               'username' => env('mail_username_1'),
				   'from' => env('mail_from_1'),
				   'email' => env('mail_email_1'),
				  ],


## Usage 


	

	
	