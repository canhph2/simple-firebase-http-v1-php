<?php

namespace CongnqNexlesoft\SimpleFirebaseHttpV1\Services\Firebase;

use CongnqNexlesoft\SimpleFirebaseHttpV1\Classes\GoogleAuthToken;
use CongnqNexlesoft\SimpleFirebaseHttpV1\Classes\ValidationObj;
use CongnqNexlesoft\SimpleFirebaseHttpV1\Enum\ResponseCodeEnum;
use CongnqNexlesoft\SimpleFirebaseHttpV1\Helpers\ValidationHelper;
use CongnqNexlesoft\SimpleFirebaseHttpV1\Services\TempCache\TempCacheService;
use Exception;
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
    const MAX_AMOUNT_OF_TOKENS = 500; // for batching
    const TEMP_CACHE_KEY = 'firebase_messaging_cache_1ab';

    /** @var FetchAuthTokenInterface */
    private $credentials;

    /** @var TempCacheService */
    private $tempCacheService;

    public function __construct()
    {
        parent::__construct(['base_uri' => self::END_POINT, 'headers' => []]);
        //
        $this->tempCacheService = new TempCacheService();
    }

    private function generateMessageSendURI(): string
    {
        $credentials = ApplicationDefaultCredentials::getCredentials(self::API_CLIENT_SCOPES);
        return sprintf('v1/projects/%s/messages:send', $credentials->getProjectId());
    }

    private function generateHeaders(): array
    {
        $authToken = $this->getAuthTokenOrCacheAuthToken();
        return [
            'Content-Type' => 'application/json',
            'Authorization' => $authToken ? $authToken->getAuthorization() : '',
        ];
    }

    /**
     * The token duration 1 hour, should cache this token
     * @return GoogleAuthToken|null
     * @throws Exception
     */
    public function getAuthTokenOrCacheAuthToken(): ?GoogleAuthToken
    {
        // handle
        //    cache token
        $cacheData = $this->tempCacheService->getCache(self::TEMP_CACHE_KEY);
        if ($cacheData) {
            $ggAuthTokenCache = GoogleAuthToken::fromJson($cacheData);
            if (!$ggAuthTokenCache->isExpired()) {
                return $ggAuthTokenCache; // END
            }
            // if the cache token is expired, will fall to case below
        }
        //    fresh token
        $credentials = ApplicationDefaultCredentials::getCredentials(self::API_CLIENT_SCOPES);
        if ($credentials) {
            $tokenResponse = $credentials->fetchAuthToken(HttpHandlerFactory::build(
                new Client(['handler' => HandlerStack::create()])));
            $ggAuthToken = new GoogleAuthToken($tokenResponse['token_type'] ?? null,
                $tokenResponse['expires_in'] ?? 0, $tokenResponse['access_token'] ?? null); // END
            $ggAuthToken->setExpiresIn(GoogleAuthToken::AMOUNT_SECONDS_BEFORE_EXPIRED + 15); // todo test
            //        save fresh token to cache
            $this->tempCacheService->setCache(self::TEMP_CACHE_KEY, $ggAuthToken->toJson());
            //
            return $ggAuthToken; // END
        }
        //
        return null; // END
    }

    private function validate(): ValidationObj
    {
        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            return ValidationHelper::invalid('GOOGLE_APPLICATION_CREDENTIALS is not configured'); // END
        }
        if (!is_file(getenv('GOOGLE_APPLICATION_CREDENTIALS'))) {
            return ValidationHelper::invalid('service-account.json file not found'); // END
        }
        $credentialsData = json_decode(file_get_contents(getenv('GOOGLE_APPLICATION_CREDENTIALS')), true);
        if (!($credentialsData['project_id'] ?? null)) {
            return ValidationHelper::invalid('Missing project_id in the service-account file'); // END
        }
        return ValidationHelper::valid(); // END
    }

    public function sendNotificationSingle(string $title, string $body, string $deviceToken, array $data = []): array
    {
        // validate
        if ($this->validate()->fails()) {
            return ['isSuccess' => false, 'error' => $this->validate()->getError()]; // END
        }
        // handle
        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => $data
            ]
        ];
        try {
            $response = $this->post($this->generateMessageSendURI(),
                ['headers' => $this->generateHeaders(), 'json' => $payload]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        return [
            'deviceToken' => substr($deviceToken, 0, 20),
            'isSuccess' => $response->getStatusCode() === ResponseCodeEnum::HTTP_OK,
            'statusCode' => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'body' => json_decode($response->getBody()->getContents(), true),
        ]; // END
    }

    private function sendNotificationMultiple(string $title, string $body, array $deviceTokens, array $data = []): array
    {
        $responses = [];
        foreach ($deviceTokens as $deviceToken) {
            $responses[] = $this->sendNotificationSingle($title, $body, $deviceToken, $data);
        }
        $countSuccess = count(array_filter($responses, function ($response) {
            return $response['isSuccess'] ?? null;
        }));
        return [
            'countSuccess' => $countSuccess,
            'countFailure' => count($responses) - $countSuccess,
            'responses' => $responses,
        ];
    }

}
