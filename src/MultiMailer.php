<?php

namespace IWasHereFirst2\LaravelMultiMail;

use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Mail\MailQueue as MailQueueContract;
use Swift_SmtpTransport;
use Swift_Mailer;
use \Illuminate\Mail\Mailer;

class MultiMailer
{
  protected $mailer;
  protected $locale;

  /**
   * Send mail throug mail account form $mailer_name
   * @param  MailableContract $mailable
   * @param  [type]           $mailer_name ]
   * @return [type]                        [description]
   */
  public static function send(MailableContract $mailable, $mailer_name)
  {
    // no mailer given, use default mailer
    if(empty($mailer_name)) return \Mail::send($mailable);

    $mailer = static::getMailer($mailer_name);
    $mailer->send($mailable);
  }

  public static function queue(MailableContract $mailable, $mailer)
  {
    // no mailer given, use default mailer
    if(empty($mailer_name)) return \Mail::queue($mailable);
    Jobs/SendMailJob::dispatch($mailer_name, $mailable);
  }

  public static function getMailer($name, $timout = null, $frequency = null)
  {

    $config = config('multimail.emails')[$name];
    if(empty($name) or empty($config))
    {
      throw new \Exception("Configuration for email: " . $name . ' is missing in config/multimail.php', 1);

    }

    // Allow user to call custom mailer
    if(!empty($config['function_call'])){
      return call_user_func_array($config['function_call'], $config['function_pars']);
    }

    if(empty($config['pass']) || empty($config['username'])){
      throw new \Exception("Username or password is empty for mail provider " . $name, 1);
    }

    $provider = (!empty($config['provider'])) ? $config['provider'] : config('multimail.provider.default');

    //https://stackoverflow.com/a/56965347/2311074
    $transport = new Swift_SmtpTransport($provider['host'], $provider['port'], $provider['encryption']);
    $transport->setUsername($config['username']);
    $transport->setPassword($config['pass']);

    $swift_mailer = new Swift_Mailer($transport);

    if(!empty($mails) && !empty($minutes)){
      $swift_mailer->registerPlugin(new \Swift_Plugins_AntiFloodPlugin($mails, $minutes));
    }

    $view = app()->get('view');
    $events = app()->get('events');
    $mailer = new Mailer($view, $swift_mailer, $events);

    if(!empty($config['from_mail'])){
      $mailer->alwaysFrom($config['from_mail'], $config['from_name'] ?? null);
    }

    if(!empty($config['reply_to_mail'])){
      $mailer->alwaysReplyTo($config['reply_to_mail'], $config['reply_to_name'] ?? null);
    }

    return $mailer;
  }

  /**
   * Begin the process of mailing a mailable class instance.
   *
   * @param  mixed  $users
   * @return \IWasHereFirst2\MultiMail\PendingMail
   */
  public function to($users)
  {
      return (new PendingMail())->to($users);
  }

  /**
   * Begin the process of mailing a mailable class instance.
   *
   * @param  mixed  $users
   * @return \IWasHereFirst2\MultiMail\PendingMail
   */
  public function from($name)
  {
      return (new PendingMail())->from($name);
  }

  /**
   * Begin the process of mailing a mailable class instance.
   *
   * @param  mixed  $users
   * @return \IWasHereFirst2\MultiMail\PendingMail
   */
  public function cc($users)
  {
      return (new PendingMail())->cc($users);
  }

  /**
   * Begin the process of mailing a mailable class instance.
   *
   * @param  mixed  $users
   * @return \IWasHereFirst2\MultiMail\PendingMail
   */
  public function bcc($users)
  {
      return (new PendingMail())->bcc($users);
  }

  /**
   * Begin the process of mailing a mailable class instance.
   *
   * @param  string locale 2 char
   * @return \IWasHereFirst2\MultiMail\PendingMail
   */
  public function locale($locale)
  {
      return (new PendingMail())->locale($locale);
  }

}
