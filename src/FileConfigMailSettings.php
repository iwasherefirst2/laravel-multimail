<?php

namespace IWasHereFirst2\LaravelMultiMail;

use IWasHereFirst2\LaravelMultiMail\Exceptions\InvalidConfigKeyException;

class FileConfigMailSettings implements MailSettings
{
    private array $multimailConfig;
    private $identifier;

    public function __construct(private readonly DefaultLaravelMailDriver $defaultLaravelMailDriver)
    {
    }

    public function setKey($identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getDriverName(): string
    {
        $config = $this->getMultiMailConfig();
        if (!empty($config['emails'][$this->identifier]['driver'])) {
            return $config['emails'][$this->identifier]['driver'];
        }

        $driver = $this->getDefaultDriver();

        if (empty($driver)) {
            throw new \Exception('No driver name specified');
        }

        return $driver;
    }

    public function getDriver(): array
    {
        $config = $this->getMultiMailConfig();

        // Backwards compatibility for MultiMail 1.* config
        if (!empty($config['emails'][$this->identifier]['driver'])) {
            $driver = $config['emails'][$this->identifier]['driver'];

            if (!empty($config['provider'][$driver])) {
                return $config['provider'][$driver];
            }
        }

        // Backwards compatibility for MultiMail 1.* config
        if (!empty($config['provider']['default'])) {
            return $config['provider']['default'];
        }

        if (!isset($driver)) {
            $driver = $this->defaultLaravelMailDriver->getDefaultDriver();
        }

        if ($driver === null) {
            throw new InvalidConfigKeyException('No mail driver found');
        }

        $laravelConfig = $this->defaultLaravelMailDriver->getLaravelConfig($driver);

        if ($laravelConfig === null) {
            throw new \Exception("Mailer [{$driver}] is not defined.");
        }

        return $laravelConfig;
    }

    public function getFromName(): string|null
    {
        return $this->getNullOrKey('from_name');
    }

    public function getReplyTo(): string|null
    {
        return $this->getNullOrKey('reply_to');
    }

    public function getReturnPath(): string|null
    {
        return $this->getNullOrKey('return_path');
    }

    public function getEmail(): string
    {
        return $this->identifier;
    }

    private function getNullOrKey(string $key): string|null
    {
        if (empty($this->getMultiMailConfig($this->identifier)[$key])) {
            return null;
        }

        return $this->getMultiMailConfig($this->identifier)[$key];
    }

    private function getMultiMailConfig(string|null $email = null): array
    {
        if (isset($this->multimailConfig)) {
            $this->multimailConfig = config('multimail');
        }

        if ($email != null) {
            return $this->multimailConfig[$email];
        }

        return $this->multimailConfig;
    }
}
