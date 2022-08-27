<?php

namespace Orolyn\Net\Security\TLS\Structure;

use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;

/**
 * @extends VariableLengthVector<Extension>
 */
class ExtensionList extends VariableLengthVector
{
    protected static string $structureClass = Extension::class;
    protected static VariableLength $variableLength = VariableLength::UInt16;

    /**
     * @param ExtensionType $type
     * @return Extension|null
     */
    public function getExtension(ExtensionType $type): ?Extension
    {
        foreach ($this->source as $extension) {
            if ($type === $extension->extensionType) {
                return $extension;
            }
        }

        return null;
    }
}
