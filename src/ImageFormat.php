<?php

declare(strict_types=1);

namespace MiGears\Captcha;

enum ImageFormat: string
{
    case Png = 'png';
    case Jpeg = 'jpeg';
    case Gif = 'gif';

    public function mimeType(): string
    {
        return match ($this) {
            self::Png => 'image/png',
            self::Jpeg => 'image/jpeg',
            self::Gif => 'image/gif',
        };
    }
}
