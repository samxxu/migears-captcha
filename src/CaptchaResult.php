<?php

declare(strict_types=1);

namespace MiGears\Captcha;

/**
 * Captcha result value object
 */
final readonly class CaptchaResult
{
    public function __construct(
        public string $imageData,
        public string $code,
        public string $mimeType,
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
