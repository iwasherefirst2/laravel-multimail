<?php

namespace IWasHereFirst2\LaravelMultiMail;

interface MailSettings
{
    public function setKey($identifier): void;

    public function getDriver(): array;

    public function getFromName(): string|null;

    public function getReplyTo(): array|null;

    public function getEmail(): string;

    public function getReturnPath(): string|null;

    public function getDriverName(): string;
}
