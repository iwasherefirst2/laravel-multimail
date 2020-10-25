<?php

namespace IWasHereFirst2\LaravelMultiMail;

interface MailSettings
{
    public function initialize($key);

    /**
     * Check if log driver is currently used.
     *
     * @return boolean
     */
    public function isLogDriver();

    /**
     * Get provider.
     *
     * @return array
     */
    public function getProvider();

    /**
     * Get setting.
     *
     * @return array
     */
    public function getSetting();

    /**
     * Return email of sender.
     *
     * @return string
     */
    public function getFromEmail();

    /**
     * Return name of sender.
     *
     * @return string
     */
    public function getFromName();

    /**
     * Return email of sender.
     *
     * @return string
     */
    public function getReplyEmail();

    /**
     * Return name of sender.
     *
     * @return string
     */
    public function getReplyName();

    /**
     * Return email
     * @return string
     */
    public function getEmail();
}