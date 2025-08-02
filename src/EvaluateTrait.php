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
    private static function generate(string $content): string
    {
        $content = self::inherit($content);
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
    private static function data(string $content): string
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
    private static function simpleReplacing(string $content): string
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
    private static function comments(string $content): string
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
    private static function include(string $content): string
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
     * View inheritance
     *
     * @param string $content
     * @return string
     */
    private static function inherit(string $content): string
    {
        $extends = self::getExtends($content);
        $extendsName = self::$views_path . DIRECTORY_SEPARATOR . $extends . self::$extension;

        if ($extends && file_exists($extendsName)) {
            $extendView = file_get_contents($extendsName);

            while (str_contains($content, "@section")) {
                $breaking = explode("@section", $content, 2);
                $secondBreaking = explode("):", $breaking[1], 2)[1];
                $subContent = explode("@endsection", $secondBreaking)[0];
                $sectionName = self::getParam("@section", "@section" . $breaking[1]);

                $extendView = self::putYield($sectionName, $subContent, $extendView);
                $content = $secondBreaking;
            }

            return self::removeYieldsAndExtends($extendView);
        }
        return self::removeYieldsAndExtends($content);
    }

    /**
     * Finds and puts the respective section content
     *
     * @param string $sectionName
     * @param string $subContent
     * @param string $content
     * @return string
     */
    private static function putYield(string $sectionName, string $subContent, string $content): string
    {
        $scanContent = $content; // the next part to read
        $afterBreaking = ""; // preserves the read content
        while (str_contains($scanContent, "@yield")) {// if the next part has a yield
            $yieldName = self::getParam("@yield", $scanContent);
            $breaking = explode("@yield", $scanContent, 2);
            $afterBreaking .= $breaking[0];
            $secondBreaking = explode(")", $breaking[1], 2)[1];

            if ($yieldName == $sectionName) {
                $newContent = $afterBreaking . $subContent . $secondBreaking;
                return $newContent;
            }
            $afterBreaking .= '@yield("' . $yieldName . '")'; // if not found, recover code
            $scanContent = explode("@yield", $scanContent, 2)[1]; // gets the next part to read
        }
        return $content; // wanted yield not found
    }

    /**
     * Remove not found yields and extends tags
     *
     * @param string $content
     * @return string
     */
    private static function removeYieldsAndExtends(string $content): string
    {
        $fields = ['@yield', '@extends'];

        foreach ($fields as $value) {
            while (str_contains($content, $value)) {
                $breaking = explode($value, $content, 2);
                $secondBreaking = explode(")", $breaking[1], 2);
                $content = $breaking[0] . $secondBreaking[1];
            }
        }
        
        return $content;
    }

    /**
     * Get extends from view
     *
     * @param string $content
     * @return boolean|string
     */
    private static function getExtends(string $content): bool|string
    {
        return self::getParam("@extends", $content);
    }

    /**
     * Return the param set on the view
     *
     * @param string $of
     * @param string $content
     * @return boolean|string
     */
    private static function getParam(string $of, string $content): bool|string
    {
        $breaking = explode($of, $content, 2);
        if (isset($breaking[1])) {
            $secondBreaking = explode(")", $breaking[1], 2);
            $otherBreaking = explode("'", $secondBreaking[0]);
            $param = isset($otherBreaking[1]) ? $otherBreaking[1] : explode('"', $secondBreaking[0])[1];
            return $param;
        }
        return false;
    }

    /**
     * @param string $content
     * @return string
     */
    private static function fixEscapes(string $content): string
    {
        return implode("):", explode(")\:", $content));
    }

    /**
     * Make code better for working with
     *
     * @param string $content
     * @return string
     */
    public static function fixNotation(string $content): string
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
    private static function removeLines(string $content): string
    {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", PHP_EOL, $content);
    }
}
