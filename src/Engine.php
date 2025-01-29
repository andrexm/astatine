<?php

namespace Andrexm\Astatine;

use ErrorException;

class Engine
{
    private static string $views_path;
    private static string $cache_path;
    private static string $extension;
    private static ?Engine $instance = null;
    public static bool|string $errorMessage = false;

    private function __construct()
    {}

    /**
     * Returns an unique Engine instance
     *
     * @return Engine
     */
    public static function getInstance(): Engine
    {
        if (!self::$instance) {
            self::$instance = new Engine;
        }
        return self::$instance;
    }

    /**
     * Set views and cache path
     *
     * @param string $views_path
     * @param string $cache_path
     * @return void
     */
    public static function config(string $views_path, string $cache_path, string $extension = ".php")
    {
        self::$views_path = $views_path;
        self::$cache_path = $cache_path;
        self::$extension = $extension;
    }

    /**
     * Validate views and cache directories
     *
     * @return boolean
     */
    public static function validateDirectories(): bool
    {
        // Verify views directory
        try {
            if (!is_dir(self::$views_path))
                throw new ErrorException("Failed to read views directory!");
        } catch (ErrorException $err) {
            self::$errorMessage = $err->getMessage();
            return false;
        }

        // Verify cache directory
        try {
            if (!is_dir(self::$cache_path))
                throw new ErrorException("Failed to read cache directory!");
        } catch (ErrorException $err) {
            self::$errorMessage = $err->getMessage();
            return false;
        }

        return true;
    }
}
