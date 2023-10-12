<?php

namespace IWasHereFirst2\LaravelMultiMail;

use IWasHereFirst2\LaravelMultiMail\Models\EmailAccount;

class DatabaseConfigMailSettings implements MailSettings
{
    private EmailAccount $account;
    private array $provider;

    public function __construct(private readonly DefaultLaravelMailDriver $defaultLaravelMailDriver)
    {
    }

    public function setKey($key): void
    {
        try {
            $this->account   = EmailAccount::where('email', '=', $key)->firstOrFail();
        } catch (\Exception $e) {
            throw new Exceptions\EmailNotInConfigException($this->email);
        }

        $this->loadProvider();
    }

    public function getDriver(): array
    {
        return $this->provider;
    }

    private function loadProvider()
    {
        if (!empty($this->account->provider)) {
            $this->provider = $this->account->provider->toArray();
            return;
        }

        $this->provider = config('multimail.provider.default');

        if (!empty($this->provider)) {
            return;
        }

        $this->provider = $this->defaultLaravelMailDriver->getDefaultLaravelConfig();
    }

    public function getFromName(): string|null
    {
        $this->account->from_name;
    }

    public function getReplyTo(): array|null
    {
        $address = $this->account->reply_to_mail;
        $name = $this->account->reply_to_name;

        if ($address === null && $name === null) {
            return null;
        }

        return compact(['address', 'name']);
    }

    public function getEmail(): string
    {
        return $this->account->email;
    }

    public function getReturnPath(): string|null
    {
        return null;
    }

    public function getDriverName(): string
    {
        return $this->provider['driver'] ?? 'unknown';
    }
}
