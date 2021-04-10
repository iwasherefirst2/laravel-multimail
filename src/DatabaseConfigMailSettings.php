<?php


namespace IWasHereFirst2\LaravelMultiMail;


use \IWasHereFirst2\LaravelMultiMail\MailSettings;
use IWasHereFirst2\LaravelMultiMail\Models\EmailAccount;

class DatabaseConfigMailSettings implements MailSettings
{
    private $account;

    private $provider;

    /**
     * Name from mail sender.
     *
     * @var string
     */
    private $name;

    /**
     * Email from mail sender.
     * Has to be set in `config/multimail.php`
     *
     * @var string
     */
    private $email;


    /**
     * Email settings.
     * This may include credentials, name, provider.
     *
     * @var array
     */
    private $settings;

    public function initialize($key): DatabaseConfigMailSettings
    {
        $this->parseEmail($key);

        try {
            $this->account   = EmailAccount::where('email', '=', $this->email)->firstOrFail();
        } catch (\Exception $e) {
            throw new Exceptions\EmailNotInConfigException($this->email);
        }

        if (empty($this->name)) {
            $this->name = $this->account->from_mail;
        }

        $this->loadProvider();

        // If credentials are empty, load default values.
        // This makes local testing for many emails
        // very convenient.
        if ($this->isEmpty()) {
            $this->loadDefault();
        }

        return $this;
    }
    /**
     * Check if log driver is used.
     *
     * @return boolean
     */
    public function isLogDriver()
    {
        return (isset($this->provider['driver']) && $this->provider['driver'] == 'log');
    }

    /**
     * Get provider.
     *
     * @return array
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get setting.
     *
     * @return array
     */
    public function getSetting()
    {
        return $this->account->toArray();
    }

    /**
     * Return email of sender.
     *
     * @return string
     */
    public function getFromEmail()
    {
        return $this->account->from_mail ?? $this->email;
    }

    /**
     * Return name of sender.
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->name;
    }

    /**
     * Return email of sender.
     *
     * @return string
     */
    public function getReplyEmail()
    {
        return $this->account->reply_to_mail;
    }

    /**
     * Return name of sender.
     *
     * @return string
     */
    public function getReplyName()
    {
        return $this->account->reply_to_name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Check if email, pass and username are not empty
     *
     * @return boolean
     */
    private function isEmpty()
    {
        return (empty($this->email) || empty($this->account) || empty($this->account->pass) || empty($this->account->username));
    }

    /**
     * Load default setting. If default setting is
     * invalid throw exception
     *
     * @return void
     */
    private function loadDefault()
    {
        $this->account = (object) config('multimail.emails.default');

        $this->loadProvider();

        if ((!isset($this->provider['driver']) || $this->provider['driver'] != 'log') && (empty($this->acount->pass) || empty($this->account->username))) {
            throw new Exceptions\NoDefaultException($this->email);
        }
    }

    /**
     * Parse $key into email and possible from name
     *
     * @param  mixed string/array
     * @return void
     */
    private function parseEmail($key)
    {
        if (!is_array($key)) {
            $this->email = $key;
            return;
        }

        $this->name = $key['name'] ?? null;

        if (empty($key['email'])) {
            throw new Exceptions\InvalidConfigKeyException;
        }

        $this->email = $key['email'];
    }


    private function loadProvider()
    {

        if (!empty($this->account->provider)) {
            $this->provider = $this->account->provider->toArray();
        }

        if (empty($this->provider)) {
            $this->provider = config('multimail.provider.default');
        }
    }

}
