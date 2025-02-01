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
        $content = self::fixNotation($content);
        $content = self::simpleReplacing($content);
        $content = self::comments($content);
        $content = self::data($content);
        $content = self::fixEscapes($content);
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
        $directivesPair = [
            "):" => "): ?>", ")\:" => "):", "@if" => "<?php if", "@endif" => "<?php endif ?>", "@else:" => "<?php else: ?>", "@elseif" => "<?php elseif",
            "@while" => "<?php while", "@endwhile" => "<?php endwhile ?>", "@for" => "<?php for", "@endfor" => "<?php endfor ?>",
            "@foreach" => "<?php foreach", "@endforeach" => "<?php endforeach ?>", "@default:" => "<?php default: ?>",
            "@switch" => "<?php switch", "@endswitch" => "<?php endswitch ?>", "@case" => "<?php case", "@break" => "<?php break ?>",
            "@continue" => "<?php continue ?>", "@php:" => "<?php", "@endphp" => "?>"
        ];

        foreach ($directivesPair as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }

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
}
