<?php

namespace IWasHereFirst2\MultiMail;

use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Mail\MailQueue as MailQueueContract;
use Swift_SmtpTransport;
use Swift_Mailer;
use \Illuminate\Mail\Mailer;

class MultiMailer
{
  protected $mailer;
  protected $locale;


  public function send(MailableContract $mailable, $mailer_name)
  {
    // no mailer given, use default mailer
    if(empty($mailer_name)) return \Mail::send($mailable);

    $this->locale = $mailable->locale;

    $mailer = static::getMailer($mailer_name);

    $mailer->send($mailable);
  }

  public function queue(MailQueueContract $mailable, $mailer)
  {
    // no mailer given, use default mailer
    if(empty($mailer_name)) return \Mail::queue($mailable);

    $this->locale = $mailable->locale;

    SendMailJob::dispatch($mailer_name, $mailable);
  }

  public static function getMailer($name, $timout = null, $frequency = null)
  {
    // if from_name not given, throw exception
    //
    // if user& pass missing throw exception


    //https://stackoverflow.com/a/56965347/2311074
    $transport = new Swift_SmtpTransport('wp10991132.mailout.server-he.de', 465, 'ssl');
    $transport->setUsername($this->getMailUser());
    $transport->setPassword($this->getMailPass());

    $swift_mailer = new Swift_Mailer($transport);

    if(!empty($mails) && !empty($minutes)){
      $swift_mailer->registerPlugin(new \Swift_Plugins_AntiFloodPlugin($mails, $minutes));
    }

    $view = app()->get('view');
    $events = app()->get('events');
    $mailer = new Mailer($view, $swift_mailer, $events);

    // WIe mache ich das mit dem Package :D?
    $mail_name = $this->alias . ' ' . __('list.' . $this->purpose , [], $lang) . ' ' . __('list.words:international', [], $lang);

    $mailer->alwaysFrom($this->getMailAddress(), $mail_name);
    $mailer->alwaysReplyTo($this->getMailAddress(), $mail_name);

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
      return (new PendingMail($this))->to($users);
  }

  /**
   * Begin the process of mailing a mailable class instance.
   *
   * @param  mixed  $users
   * @return \IWasHereFirst2\MultiMail\PendingMail
   */
  public function cc($users)
  {
      return (new PendingMail($this))->cc($users);
  }

  /**
   * Begin the process of mailing a mailable class instance.
   *
   * @param  mixed  $users
   * @return \IWasHereFirst2\MultiMail\PendingMail
   */
  public function bcc($users)
  {
      return (new PendingMail($this))->bcc($users);
  }

  /**
   * Begin the process of mailing a mailable class instance.
   *
   * @param  string locale 2 char
   * @return \IWasHereFirst2\MultiMail\PendingMail
   */
  public function locale($locale)
  {
      return (new PendingMail($this))->locale($locale);
  }

}
