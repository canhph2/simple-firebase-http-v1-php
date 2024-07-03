<?php

namespace CongnqNexlesoft\SimpleFirebaseHttpV1\Helpers;

/**
 * (Last updated on July 3, 2024)
 * ### A Directory Helper
 */
class DirHelper
{
    /**
     * - Support frameworks: Symfony 4.4, Laravel 5.8 and maybe Laravel higher version
     * ---
     * - #1.1 The paths are different between local and server (Docker) (dev,stg, prd)
     * .e.g issue
     * - PWD (local) = (no value) ,  PWD (server) = '/srv' (no /public)
     * - DOCUMENT_ROOT (local) =  "/Users/congnqnexlesoft/Desktop/engage-api/public" (have /public)
     * - DOCUMENT_ROOT (server) = "/srv" (no /public)
     * ---
     * - #1.2 Use a field same part between local and server, like SCRIPT_FILENAME, and replace last part '/public/index.php'
     * - SCRIPT_FILENAME (local)  =  "/srv/legacy/public/index.php"
     * - SCRIPT_FILENAME (server) =  "/Users/congnqnexlesoft/Desktop/engage-api/public/index.php"
     * ---
     * - #3 Console command
     *    - in Symfony,
     *        - in Mac, SCRIPT_FILENAME will return 'bin/console' , PWD = dir path
     *        - in Docker image, same Mac
     *    - in Laravel,
     *        - in Mac, SCRIPT_FILENAME will return 'artisan' , PWD = dir path
     *        - in Docker image, same Mac
     * ---
     * @param mixed ...$subDirOrFiles
     * @return string
     */
    public static function getWorkingDir(...$subDirOrFiles): string
    {
        $workingDir = in_array($_SERVER['SCRIPT_FILENAME'], ['bin/console', 'artisan']) ? $_SERVER['PWD'] // case: Console command
            : str_replace('/public/index.php', '', $_SERVER['SCRIPT_FILENAME']); // case: PHP request
        return count($subDirOrFiles) ? self::join($workingDir, ...$subDirOrFiles) : $workingDir;
    }

    /**
     * To check if a subdirectory is existing in the root project directory
     * @param string $subDir
     * @return bool
     */
    public static function isProjectSubDir(string $subDir): bool
    {
        return is_dir(self::getWorkingDir($subDir));
    }

    /**
     * @param string $subDir
     * @return bool TRUE if create a dir success, otherwise FALSE
     */
    public static function createProjectSubDir(string $subDir): bool
    {
        if (!is_dir(self::getWorkingDir($subDir))) {
            mkdir(self::getWorkingDir($subDir), 755, true);
            return is_dir(self::getWorkingDir($subDir));
        }
        return false;
    }

    /**
     * usage: <name>::join($part1, $part2, $morePart) -> "$part1/$part2/$morePart"
     * @param ...$dirOrFileParts
     * @return string|null
     */
    public static function join(...$dirOrFileParts): ?string
    {
        return join('/', array_filter($dirOrFileParts, function ($item) {
            return $item; // filter null or empty parts
        }));
    }
}
