<?php

namespace Serenata\Analysis\SourceCodeReading;

use function mb_detect_encoding;
use function mb_detect_order;
use function mb_convert_encoding;

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
        // BUG: https://github.com/php/php-src/issues/9008
        // The workaround is, we suppose the encoding is UTF-8 by default. If it is already, no
        // action will be done, otherwise, it tries to detect and convert the encoding. Notice that
        // the latter case would be a rare situation, so no worries.
        $utf8 = mb_detect_encoding($code, ['UTF-8'], true);

        if ($utf8 !== false) {
            return $code;
        }

        $encoding = mb_detect_encoding($code, mb_detect_order());

        // Safe assumption: $encoding is neither equal to UTF-8 nor ASCII
        // (as every ASCII encoding is also a UTF-8 one)
        return mb_convert_encoding($code, 'UTF-8', $encoding);
    }
}
