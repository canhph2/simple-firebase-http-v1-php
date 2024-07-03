<?php

namespace CongnqNexlesoft\SimpleFirebaseHttpV1\Services\TempCache;

use CongnqNexlesoft\SimpleFirebaseHttpV1\Helpers\DirHelper;

/**
 * ### Temp Cache Service
 * - will store some data in <project dir>/temp/cache with obfuscation
 */
class TempCacheService
{
    const CACHE_DIR = 'temp/cache';

    public function __construct()
    {
    }

    private function generateCacheFilePath(string $key): string
    {
        return DirHelper::getWorkingDir(self::CACHE_DIR, $key);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getCache(string $key): ?string
    {
        return is_file($this->generateCacheFilePath($key)) ? file_get_contents($this->generateCacheFilePath($key)) : null;
    }

    /**
     * @param string $key
     * @param string $value
     * @return false|int
     */
    public function setCache(string $key, string $value)
    {
        // validate
        if (!DirHelper::isProjectSubDir(self::CACHE_DIR)) {
            DirHelper::createProjectSubDir(self::CACHE_DIR);
        }
        // handle
        return file_put_contents($this->generateCacheFilePath($key), $value);
    }
}
