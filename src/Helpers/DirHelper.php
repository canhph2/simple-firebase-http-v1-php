<?php

namespace CongnqNexlesoft\SimpleFirebaseHttpV1\Helpers;

/**
 * (Last updated on August 12, 2024)
 * ### A Directory Helper
 * - Required:
 *   - File must place at /src/Helpers/DirHelper.php (Symfony project)
 *   - File must place at /app/Helpers/DirHelper.php (Laravel project)
 */
class DirHelper
{
    /**
     * - Support frameworks: Symfony 4.4, Laravel 5.8 and maybe Laravel higher version
     * ---
     * - [REMOVED] #1.1 The paths are different between local and server (Docker) (dev,stg, prd)
     * .e.g issue
     * - PWD (local) = (no value) ,  PWD (server) = '/srv' (no /public)
     * - DOCUMENT_ROOT (local) =  "/Users/congnqnexlesoft/Desktop/engage-api/public" (have /public)
     * - DOCUMENT_ROOT (server) = "/srv" (no /public)
     * ---
     * - [REMOVED] #1.2 Use a field same part between local and server, like SCRIPT_FILENAME, and replace last part '/public/index.php'
     * - SCRIPT_FILENAME (local)  =  "/srv/legacy/public/index.php"
     * - SCRIPT_FILENAME (server) =  "/Users/congnqnexlesoft/Desktop/engage-api/public/index.php"
     * ---
     * - [REMOVED] #3 Console command
     *   - in Symfony,
     *     - on Mac, SCRIPT_FILENAME will return 'bin/console' , PWD = dir path
     *     - on Docker image, same Mac
     *   - in Laravel,
     *     - on Mac, SCRIPT_FILENAME will return 'artisan' , PWD = dir path
     *     - on Docker image, same Mac
     * - [REMOVED] #3.1 Console command when run outside project with full path, or half path,
     *        the SCRIPT_FILENAME will return <full path>/artisan or <half path>/artisan
     *        the SCRIPT_FILENAME will return <full path>/bin/console or <half path>/bin/console
     * ---
     * - #4 Final solution: Use `__FILE__` to get helper's file path and extract project path
     * ---
     * @param mixed ...$subDirOrFiles
     * @return string
     */
    public static function getWorkingDir(...$subDirOrFiles): string
    {                            // |-Symfony project             |-Laravel project
        $workingDir = str_replace(['/src/Helpers/DirHelper.php', '/app/Helpers/DirHelper.php'], ['', ''], __FILE__);
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

    /**
     * Currently function supports Laravel, Symfony project only.
     * @param mixed ...$subDirOrFiles
     * @return string|null
     */
    public static function getFrameworkCacheDir(...$subDirOrFiles): ?string
    {
        $cacheDir = 'error';
        // Symfony
        if (self::isProjectSubDir('var/cache')) {
            $cacheDir = self::getWorkingDir('var/cache'); // END
        } // Laravel
        else if (self::isProjectSubDir('storage/framework/cache')) {
            $cacheDir = self::getWorkingDir('storage/framework/cache'); // END
        }
        //
        return count($subDirOrFiles) ? self::join($cacheDir, ...$subDirOrFiles) : $cacheDir;
    }
}
