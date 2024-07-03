<?php

namespace CongnqNexlesoft\SimpleFirebaseHttpV1\Helpers;

use CongnqNexlesoft\SimpleFirebaseHttpV1\Classes\ValidationObj;

/**
 * (Last updated on July 3, 2024)
 * ### Validation Helper
 */
class ValidationHelper
{
    /**
     * create a new ValidationObj
     * @param string|null $error
     * @param array $data
     * @return ValidationObj
     */
    public static function new(string $error = null, array $data = []): ValidationObj
    {
        return new ValidationObj($error, $data);
    }

    /**
     * create a new ValidationObj and set valid status
     * @return ValidationObj
     */
    public static function valid(): ValidationObj
    {
        return self::new()->clearError();
    }

    /**
     * create a new ValidationObj and set invalid status
     * @param string $errorMessage
     * @return ValidationObj
     */
    public static function invalid(string $errorMessage): ValidationObj
    {
        return self::new()->setError($errorMessage);
    }
}
