<?php


namespace IWasHereFirst2\LaravelMultiMail;

use \IWasHereFirst2\LaravelMultiMail\MailSettings;

class FileConfigMailSettings implements MailSettings
{
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
     * Driver, Host, Port & Encryption.
     *
     * @var array
     */
    private $provider;

    /**
     * Email settings.
     * This may include credentials, name, provider.
     *
     * @var array
     */
    private $settings;

    public function initialize($key)
    {
        $this->parseEmail($key);

        try {
            $this->settings   = config('multimail.emails')[$this->email];
        } catch (\Exception $e) {
            throw new Exceptions\EmailNotInConfigException($this->email);
        }

        if (empty($this->name)) {
            $this->name = $this->settings['from_name'] ?? null;
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
        return $this->settings;
    }

    /**
     * Return email of sender.
     *
     * @return string
     */
    public function getFromEmail()
    {
        return $this->settings['from_mail'] ?? $this->email;
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
        return $this->settings['reply_to_mail'] ?? null;
    }

    /**
     * Return name of sender.
     *
     * @return string
     */
    public function getReplyName()
    {
        return $this->settings['reply_to_name'] ?? null;
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
        return (empty($this->email) || empty($this->settings) || empty($this->settings['pass']) || empty($this->settings['username']));
    }

    /**
     * Load default setting. If default setting is
     * invalid throw exception
     *
     * @return void
     */
    private function loadDefault()
    {
        $this->settings = config('multimail.emails.default');

        $this->loadProvider();

        if ((!isset($this->provider['driver']) || $this->provider['driver'] != 'log') && (empty($this->settings['pass']) || empty($this->settings['username']))) {
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
        if (!empty($this->settings['provider'])) {
            $this->provider = config('multimail.provider.' . $this->settings['provider']);
        }

        if (empty($this->provider)) {
            $this->provider = config('multimail.provider.default');
        }
    }
}