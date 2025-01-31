<?php

namespace Andrexm\Astatine;

trait EvaluateTrait
{
    /**
     * Starts processing the view
     *
     * @param string $content
     * @return string
     */
    static private function generate(string $content): string
    {
        return self::data($content);
    }

    /**
     * Showing data
     *
     * @param string $content
     * @return string
     */
    static private function data(string $content): string
    {
        $content = str_replace(["{{", "}}"], ["<?= htmlspecialchars(", ")?>"], $content);
        $content = str_replace(["{!!", "!!}"], ["<?= ", " ?>"], $content);
        return $content;
    }
}
