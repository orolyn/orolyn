<?php
namespace Orolyn;

use function Orolyn\Lang\String;

class Regex
{
    private string $base;
    private string $pattern;

    public function __construct(string $pattern)
    {
        $this->base = $pattern;
        $this->pattern = String('/%s/')->format($pattern);
    }

    public static function create(string $pattern): Regex
    {
        return new Regex($pattern);
    }

    public function match(string $subject, &$matches = null, &...$groups): int
    {
        $num = preg_match($this->pattern, $subject, $matches);

        for ($i = 1; isset($matches[$i]); $i++) {
            $groups[$i-1] = $matches[$i];
        }

        return $num;
    }
}
