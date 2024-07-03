<?php

namespace CongnqNexlesoft\SimpleFirebaseHttpV1\Classes;

use DateTime;

class GoogleAuthToken
{
    const AMOUNT_SECONDS_BEFORE_EXPIRED = 300; // seconds
    const TYPE_FRESH = 'TYPE_FRESH';
    const TYPE_CACHE = 'TYPE_CACHE';

    /** @var string */
    private $access_token;
    /** @var int */
    private $expires_in;
    /** @var string */
    private $token_type;
    /** @var int */
    private $starting_at;
    /** @var string */
    private $type;

    /**
     * @param string $access_token
     * @param int $expires_in
     * @param string $token_type
     * @param int $starting_at
     */
    public function __construct(string $access_token, int $expires_in, string $token_type, int $starting_at = 0)
    {
        $this->access_token = $access_token;
        $this->expires_in = $expires_in;
        $this->token_type = $token_type;
        //
        $this->starting_at = $starting_at ?: (new DateTime())->getTimestamp();
    }

    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    public function setAccessToken(string $access_token): GoogleAuthToken
    {
        $this->access_token = $access_token;
        return $this;
    }

    public function getExpiresIn(): int
    {
        return $this->expires_in;
    }

    public function setExpiresIn(int $expires_in): GoogleAuthToken
    {
        $this->expires_in = $expires_in;
        return $this;
    }

    public function getTokenType(): string
    {
        return $this->token_type;
    }

    public function setTokenType(string $token_type): GoogleAuthToken
    {
        $this->token_type = $token_type;
        return $this;
    }

    public function getStartingAt(): int
    {
        return $this->starting_at;
    }

    public function setStartingAt(int $starting_at): GoogleAuthToken
    {
        $this->starting_at = $starting_at;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): GoogleAuthToken
    {
        $this->type = $type;
        return $this;
    }

    // === custom functions ====

    public function getAuthorization()
    {
        return "$this->token_type $this->access_token";
    }

    public function isExpired(): bool
    {
        return (new DateTime())->getTimestamp() > $this->starting_at + $this->expires_in - self::AMOUNT_SECONDS_BEFORE_EXPIRED;
    }

    public function toJson(): string
    {
        return json_encode([
            'access_token' => $this->access_token,
            'expires_in' => $this->expires_in,
            'token_type' => $this->token_type,
            'starting_at' => $this->starting_at,
        ]);
    }

    public static function fromJson(string $jsonStr): GoogleAuthToken
    {
        $data = json_decode($jsonStr, true);
        return new GoogleAuthToken($data['access_token'] ?? null, $data['expires_in'] ?? 0,
            $data['token_type'] ?? null, $data['starting_at'] ?? 0);
    }


}
