<?php
namespace Orolyn\Serialization;

use Orolyn\Collection\Dictionary;
use Orolyn\StandardObject;

final class SerializationInfo
{
    private $hasReplacement = false;

    private $replacement;

    private $dictionary;

    public function __construct()
    {
        $this->dictionary = new Dictionary();
    }

    public function hasReplacement(): bool
    {
        return $this->hasReplacement;
    }

    public function getReplacement()
    {
        return $this->replacement;
    }

    public function replaceWith($value): void
    {
        $this->replacement = $value;
        $this->hasReplacement = true;
    }

    public function set($key, $value): void
    {
        $this->dictionary->insert($key, $value);
    }

    public function get($key)
    {
        if ($this->dictionary->try($key, $value)) {
            return $value;
        }

        return null;
    }
}
