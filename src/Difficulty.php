<?php

declare(strict_types=1);

namespace MiGears\Captcha;

/**
 * Preset difficulty levels that tune noise, interference lines and the
 * amount of character rotation/vertical drift used to render the image.
 */
enum Difficulty: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';
}