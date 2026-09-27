<?php

declare(strict_types=1);

namespace MiGears\Captcha;

/**
 * Captcha result value object
 *
 * The properties carry `readonly` one by one rather than the class carrying a
 * `readonly class` modifier, which is PHP 8.2 syntax while this package
 * requires php ^8.1.
 */
final class CaptchaResult
{
    public function __construct(
        public readonly string $imageData,
        public readonly string $code,
        public readonly string $mimeType,
    ) {
    }

    /**
     * Returns the image data as a base64-encoded data URI
     */
    public function toDataUri(): string
    {
        return sprintf('data:%s;base64,%s', $this->mimeType, base64_encode($this->imageData));
    }
}
