<?php

namespace Andrexm\Astatine;

class Engine
{
    private static string $views_path;
    private static string $cache_path;
    private static ?Engine $instance = null;

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
    public static function config(string $views_path, string $cache_path)
    {
        self::$views_path = $views_path;
        self::$cache_path = $cache_path;
    }
}
