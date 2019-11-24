<?php

/**
 * Source comes from Package: laracasts/Behat-Laravel-Extension
 * Git: https://github.com/laracasts/Behat-Laravel-Extension/
 * File: https://github.com/laracasts/Behat-Laravel-Extension/blob/master/src/Context/Services/MailTrap.php
 *
 * Copyright (c) 2012 Jeffrey Way <jeffrey@laracasts.com>
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace IWasHereFirst2\LaravelMultiMail\Tests\Traits;

use \Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

trait MailTrap
{
    /**
     * The MailTrap configuration.
     *
     * @var integer
     */
    protected $mailTrapInboxId;

    /**
     * The MailTrap API Key.
     *
     * @var string
     */
    protected $mailTrapApiKey;

    /**
     * The Guzzle client.
     *
     * @var Client
     */
    protected $client;

    /**
     *
     * Empty the MailTrap inbox.
     *
     * @AfterScenario @mail
     */
    public function emptyInbox()
    {
        $this->requestClient()->patch($this->getMailTrapCleanUrl());
    }

    /**
     * Get the configuration for MailTrap.
     *
     * @param integer|null $inboxId
     * @throws Exception
     */
    protected function applyMailTrapConfiguration($inboxId = null)
    {
        $this->mailTrapInboxId = env('MAILTRAP_INBOX_ID');
        $this->mailTrapApiKey  = env('MAILTRAP_API_KEY');
    }

    /**
     * Fetch a MailTrap inbox.
     *
     * @param  integer|null $inboxId
     * @return mixed
     * @throws RuntimeException
     */
    protected function fetchInbox($inboxId = null)
    {
        if (! $this->alreadyConfigured()) {
            $this->applyMailTrapConfiguration($inboxId);
        }

        $body = $this->requestClient()
            ->get($this->getMailTrapMessagesUrl())
            ->getBody();

        return $this->parseJson($body);
    }

    /**
     * Get the MailTrap messages endpoint.
     *
     * @return string
     */
    protected function getMailTrapMessagesUrl()
    {
        return "/api/v1/inboxes/{$this->mailTrapInboxId}/messages";
    }

    /**
     * Get the MailTrap "empty inbox" endpoint.
     *
     * @return string
     */
    protected function getMailTrapCleanUrl()
    {
        return "/api/v1/inboxes/{$this->mailTrapInboxId}/clean";
    }

    /**
     * Determine if MailTrap config has been retrieved yet.
     *
     * @return boolean
     */
    protected function alreadyConfigured()
    {
        return $this->mailTrapApiKey;
    }

    /**
     * Request a new Guzzle client.
     *
     * @return Client
     */
    protected function requestClient()
    {
        if (! $this->client) {
            $this->client = new Client([
                'base_uri' => 'https://mailtrap.io',
                'headers'  => ['Api-Token' => $this->mailTrapApiKey],
            ]);
        }

        return $this->client;
    }

    /**
     * @param $body
     * @return array|mixed
     * @throws RuntimeException
     */
    protected function parseJson($body)
    {
        $data = json_decode((string) $body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('Unable to parse response body into JSON: ' . json_last_error());
        }

        return $data === null ? [] : $data;
    }

    /**
     * Search Messages Url
     *
     * @param string $query Search query
     * @return string
     */
    protected function searchInboxMessagesUrl($query)
    {
        return "/api/v1/inboxes/{$this->mailTrapInboxId}/messages?search=" . $query;
    }

    /**
     * Find and fetch a Message By Query.
     *
     * @param  integer $query Query
     * @return mixed
     * @throws RuntimeException
     */
    protected function findMessage($query)
    {
        if (! $this->alreadyConfigured()) {
            $this->applyMailTrapConfiguration();
        }

        $body = $this->requestClient()
            ->get($this->searchInboxMessagesUrl($query))
            ->getBody();

        return $this->parseJson($body);
    }

    /**
     * Check if a message exists based on a string query.
     *
     * @param  string $query Query string
     * @return mixed
     * @throws RuntimeException
     */
    protected function messageExists($query)
    {
        $messages = $this->findMessage($query);

        return count($messages) > 0;
    }
}
