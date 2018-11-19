<?php

namespace GitLab;

use GuzzleHttp\Client;

class Connection
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function mergeRequests($state = 'all') {

        $url = env('GITLAB_URL') . '/merge_requests?scope=all&per_page=50&state='. $state;

        $response = $this->client->request('GET', $url, ['headers' => ['Private-Token' => env('GITLAB_ACCESS_TOKEN')]]);

        return collect(json_decode($response->getBody()));
    }

}