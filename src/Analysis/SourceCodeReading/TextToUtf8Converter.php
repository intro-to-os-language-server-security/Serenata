<?php

namespace Serenata\Analysis\SourceCodeReading;

/**
 * Converts text to UTF-8.
 */
final class TextToUtf8Converter implements TextEncodingConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert(string $code): string
    {
        // Passing null does NOT work with 8.1(.7), although based on PHP docs, it must do the same
        // thing as calling mb_detect_order(). It might be an internal bug, but not totally sure.
        $encoding = mb_detect_encoding($code, mb_detect_order(), true);

        if ($encoding === false) {
            $encoding = 'ASCII';
        }

        if (!in_array($encoding, ['UTF-8', 'ASCII'], true)) {
            $code = mb_convert_encoding($code, 'UTF-8', $encoding);
        }

        return $code;
    }
}
