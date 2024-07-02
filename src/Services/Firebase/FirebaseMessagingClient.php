<?php

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\FetchAuthTokenInterface;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Utils;

/**
 * (Last updated on July 2, 2024)
 * ### Firebase Messaging Client version 2 (HTTP v1 API)
 */
class FirebaseMessagingClient extends Client
{
    const END_POINT = 'https://fcm.googleapis.com';
    const API_CLIENT_SCOPES = [
        'https://www.googleapis.com/auth/iam',
        'https://www.googleapis.com/auth/cloud-platform',
        'https://www.googleapis.com/auth/firebase',
        'https://www.googleapis.com/auth/firebase.database',
        'https://www.googleapis.com/auth/firebase.messaging',
        'https://www.googleapis.com/auth/firebase.remoteconfig',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/securetoken',
    ];
    const MAX_AMOUNT_OF_TOKENS = 500;

    /** @var FetchAuthTokenInterface */
    private $credentials;

    public function __construct()
    {
        parent::__construct(['base_uri' => self::END_POINT, 'headers' => []]);
    }

    public function sendNotification(string $title, string $body, array $deviceTokens, array $data = []): array
    {
        $credentials = ApplicationDefaultCredentials::getCredentials(self::API_CLIENT_SCOPES);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->getAuthToken(),
        ];
        $fcmData = [
            'message' => [
                'token' => $deviceTokens[0],
                'notification' => ['title' => $title, 'body' => $body],
                'data' => $data
            ]
        ];
        $response = $this->post('v1/projects/' . $credentials->getProjectId() . '/messages:send',
            ['headers' => $headers, 'json' => $fcmData]);
        \App\Services\Firebase\dd($response->getBody()->getContents(), $response);
        return [];
    }



    // the token duration 1 hour, should cache this token
    public function getAuthToken() // todo organize to own library
    {
        $credentials = ApplicationDefaultCredentials::getCredentials(self::API_CLIENT_SCOPES);
        if ($credentials) {
            $test4 = $credentials->fetchAuthToken(HttpHandlerFactory::build(
                new Client(['handler' => HandlerStack::create()])));
            return $test4['token_type'] . ' ' . $test4['access_token'];
        }
    }
}
