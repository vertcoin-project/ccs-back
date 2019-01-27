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

    public function getNewFiles($merge_request_iid) {
        $url = env('GITLAB_URL') . '/merge_requests/' . $merge_request_iid . '/changes';
        $response = $this->client->request('GET', $url, ['headers' => ['Private-Token' => env('GITLAB_ACCESS_TOKEN')]]);
        $deserialized = collect(json_decode($response->getBody()));

        $result = [];
        foreach ($deserialized['changes'] as $change) {
            if ($change->new_file) {
                $result[] = $change->new_path;
            }
        }

        return $result;
    }


}