<?php
namespace Orolyn;

use function Orolyn\String;

class Regex
{
    private string $base;
    private string $pattern;

    public function __construct(string $pattern)
    {
        $this->base = $pattern;
        $this->pattern = sprintf('/%s/', $pattern);
    }

    public static function create(string $pattern): Regex
    {
        return new Regex($pattern);
    }

    public function match(string $subject, &$match = null, &...$groups): int
    {
        $num = preg_match($this->pattern, $subject, $matches);

        if ($num > 0) {
            $match = $matches[0];

            for ($i = 1; isset($matches[$i]); $i++) {
                $groups[$i-1] = $matches[$i];
            }
        }

        return $num;
    }
}
