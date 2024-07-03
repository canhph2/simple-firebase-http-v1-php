<?php

namespace CongnqNexlesoft\SimpleFirebaseHttpV1\Services\Firebase;

use CongnqNexlesoft\SimpleFirebaseHttpV1\Classes\ValidationObj;
use CongnqNexlesoft\SimpleFirebaseHttpV1\Enum\ResponseCodeEnum;
use CongnqNexlesoft\SimpleFirebaseHttpV1\Helpers\ValidationHelper;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\FetchAuthTokenInterface;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;

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

    private function validate(): ValidationObj
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            return ValidationHelper::invalid('GOOGLE_APPLICATION_CREDENTIALS is not configured'); // END
        }
        $credentialsData = json_decode(file_get_contents(getenv('GOOGLE_APPLICATION_CREDENTIALS')), true);
        if (!($credentialsData['project_id'] ?? null)) {
            return ValidationHelper::invalid('Missing project_id in the service-account file'); // END
        }
        return ValidationHelper::valid();
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

    public function sendNotification(string $title, string $body, array $deviceTokens, array $data = []): array
    {
        // validate
        if ($this->validate()->fails()) {
            return ['isSuccess' => false, 'error' => $this->validate()->getError()]; // END
        }
        $credentials = ApplicationDefaultCredentials::getCredentials(self::API_CLIENT_SCOPES);

        // handle
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

        try {
            $response = $this->post('v1/projects/' . $credentials->getProjectId() . '/messages:send',
                ['headers' => $headers, 'json' => $fcmData]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return [
            'isSuccess' => $response->getStatusCode() === ResponseCodeEnum::HTTP_OK,
            'statusCode' => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'body' => json_decode($response->getBody()->getContents(), true),
        ]; // END
    }

}
