<?php

namespace Andrexm\Astatine;

use ErrorException;

trait EvaluateTrait
{
    private static string $views_path;
    private static string $extension;

    /**
     * Starts processing the view
     *
     * @param string $content
     * @return string
     */
    static private function generate(string $content): string
    {
        $content = self::include($content);
        $content = self::fixNotation($content);
        $content = self::simpleReplacing($content);
        $content = self::comments($content);
        $content = self::data($content);
        $content = self::fixEscapes($content);
        $content = self::removeLines($content);
        return $content;
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
        $content = str_replace(["{!!", "!!}"], ["<?=", "?>"], $content);
        return $content;
    }

    /**
     * work with directives and others tools that just need a simple replacing to work
     *
     * @param string $content
     * @return string
     */
    static private function simpleReplacing(string $content): string
    {
        $pairs = [
            "):" => "): ?>", ")\:" => "):", "@if" => "<?php if", "@endif" => "<?php endif ?>", "@else:" => "<?php else: ?>", "@elseif" => "<?php elseif",
            "@while" => "<?php while", "@endwhile" => "<?php endwhile ?>", "@for" => "<?php for", "@endfor" => "<?php endfor ?>",
            "@foreach" => "<?php foreach", "@endforeach" => "<?php endforeach ?>", "@default:" => "<?php default: ?>",
            "@switch" => "<?php switch", "@endswitch" => "<?php endswitch ?>", "@case" => "<?php case", "@break" => "<?php break ?>",
            "@continue" => "<?php continue ?>", "@php:" => "<?php", "@endphp" => "?>"
        ];

        foreach ($pairs as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }

    /**
     * Remove comments from the resulting code
     *
     * @param string $content
     * @return string
     */
    static private function comments(string $content): string
    {
        while (str_contains($content, "{{--")) {
            $building = [];
            $breaking = explode("{{--", $content, 2);
            $second = explode("--}}", $breaking[1], 2);

            array_push($building, $breaking[0]);
            array_push($building, $second[1]);

            $content = implode("", $building);
        }
        return $content;
    }

    /**
     * Inclues a subview
     *
     * @param string $content
     * @return string
     */
    static private function include(string $content): string
    {
        while (str_contains($content, "@include(")) {
            $breaking = explode("@include(", $content, 2);
            $secondBreaking = explode(")", $breaking[1], 2);
            $viewName = substr($secondBreaking[0], 1, strlen($secondBreaking[0]) - 2);
            
            $subViewContent = file_get_contents(self::$views_path . DIRECTORY_SEPARATOR . $viewName . self::$extension);
            if (!$subViewContent) {
                throw new ErrorException("Failed to include view " . $viewName);
                return $content;
            }
            $content = implode("", [$breaking[0], $subViewContent, $secondBreaking[1]]);
        }
        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    static private function fixEscapes(string $content): string
    {
        return implode("):", explode(")\:", $content));
    }

    /**
     * Make code better for working with
     *
     * @param string $content
     * @return string
     */
    static function fixNotation(string $content): string
    {
        $content = str_replace(["{{ ", " }}"], ["{{", "}}"], $content); // remove spaces
        $content = str_replace(["('", "')"], ['("', '")'], $content); // remove single quotes
        $content = str_replace(["( '", "' )"], ['( "', '" )'], $content); // remove spaces between quotes and parentheses
        $content = str_replace(['")'], ['"): ?>'], $content);
        return $content;
    }

    /**
     * Removes empty lines
     *
     * @param string $content
     * @return string
     */
    static private function removeLines(string $content): string
    {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", PHP_EOL, $content);
    }
}
