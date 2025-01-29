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
     * Render a view and includes
     *
     * @param string $viewName
     * @param array $data
     * @return boolean
     */
    public static function render(string $viewName, array $data = []): bool
    {
        // view path
        $fileView = self::$views_path . DIRECTORY_SEPARATOR . $viewName . self::$extension;

        // build file path
        $fileBuild = self::$cache_path . DIRECTORY_SEPARATOR . $viewName . ".php";

        // error if the view doesn't exists
        if (!file_exists($fileView)) {
            self::$errorMessage = "The informed view doesn't exists!";
            return false;
        }

        // when the view was updated
        $view_updated_when = filemtime($fileView);
        // when the build file was updated (temp value)
        $build_updated_when = $view_updated_when - 1;

        $builded = false; // if file build exists
        if (file_exists($fileBuild)) {
            $build_updated_when = filemtime($fileBuild); // check when the build was created
            $builded = true;
        }

        // if the builded file is older than the edited view, create a new one according to the view
        if ($view_updated_when > $build_updated_when) {
            $builded = self::buildRequested($viewName);
        }

        // return false because the self::buildRequested had an error
        if (!$builded) {
            return false;
        }

        // includes the file
        include($fileBuild);
        return true;
    }

    /**
     * Build requested view
     *
     * @param string $viewName
     * @return boolean
     */
    private static function buildRequested(string $viewName): bool
    {
        if (!self::validateDirectories()) return false;

        // the informed view
        $file = self::$views_path . DIRECTORY_SEPARATOR . $viewName . self::$extension;

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
