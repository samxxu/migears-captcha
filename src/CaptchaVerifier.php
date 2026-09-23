<?php

declare(strict_types=1);

namespace MiGears\Captcha;

/**
 * Validates a user-submitted captcha answer against the expected code.
 *
 * Verification is left to this tiny helper so callers don't have to handle
 * timing-safe comparison or case folding themselves. One-time consumption and
 * expiry are the caller's responsibility, keeping storage concerns out.
 */
final class CaptchaVerifier
{
    public function verify(string $submitted, string $expected, bool $caseInsensitive = true): bool
    {
        if ($submitted === '' || $expected === '') {
            return false;
        }

        if ($caseInsensitive) {
            $submitted = strtolower($submitted);
            $expected = strtolower($expected);
        }

        return hash_equals($expected, $submitted);
    }
}