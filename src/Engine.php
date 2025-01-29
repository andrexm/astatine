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
     * Build requested view
     *
     * @param string $viewName
     * @return boolean
     */
    public static function buildRequested(string $viewName): bool
    {
        if (!self::validateDirectories()) return false;

        // the informed view
        $file = self::$views_path . DIRECTORY_SEPARATOR . $viewName . self::$extension;

        // Verify specified view
        if (!is_file($file)) {
            self::$errorMessage = "The specified view doesn't exists!";
            return false;
        }

        // Basic view building
        try {
            $content = file_get_contents($file);
            file_put_contents(self::$cache_path . DIRECTORY_SEPARATOR . $viewName . ".php", $content);
        } catch (\Throwable $th) {
            self::$errorMessage = $th->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Validate views and cache directories
     *
     * @return boolean
     */
    private static function validateDirectories(): bool
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
