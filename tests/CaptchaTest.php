<?php

declare(strict_types=1);

namespace MiGears\Captcha\Tests;

use PHPUnit\Framework\TestCase;
use MiGears\Captcha\Captcha;
use MiGears\Captcha\CaptchaResult;
use MiGears\Captcha\ImageFormat;
use MiGears\Captcha\CaptchaVerifier;
use MiGears\Captcha\Difficulty;
use MiGears\Captcha\Exception\CaptchaException;

final class CaptchaTest extends TestCase
{
    public function testGenerateReturnsCaptchaResult(): void
    {
        $captcha = new Captcha();
        $result = $captcha->generate();

        $this->assertInstanceOf(CaptchaResult::class, $result);
    }

    public function testCodeLengthMatchesConfig(): void
    {
        $captcha = new Captcha(length: 6);
        $result = $captcha->generate();

        $this->assertSame(6, strlen($result->code));
    }

    public function testDefaultCodeLength(): void
    {
        $captcha = new Captcha();
        $result = $captcha->generate();

        $this->assertSame(4, strlen($result->code));
    }

    public function testCodeContainsOnlyAllowedChars(): void
    {
        $captcha = new Captcha(length: 20);
        $result = $captcha->generate();

        $this->assertMatchesRegularExpression('/^[abCDefGhiJkLmNPQrstUVWXyz23456789]+$/', $result->code);
    }

    public function testCustomChars(): void
    {
        $captcha = new Captcha(length: 10, chars: '0123456789');
        $result = $captcha->generate();

        $this->assertMatchesRegularExpression('/^[0-9]+$/', $result->code);
        $this->assertSame(10, strlen($result->code));
    }

    public function testDefaultFormatIsPng(): void
    {
        $captcha = new Captcha();
        $result = $captcha->generate();

        $this->assertSame('image/png', $result->mimeType);
        // PNG files start with 89 50 4E 47
        $this->assertStringStartsWith(pack('H*', '89504e47'), $result->imageData);
    }

    public function testJpegFormat(): void
    {
        $captcha = new Captcha(format: ImageFormat::Jpeg);
        $result = $captcha->generate();

        $this->assertSame('image/jpeg', $result->mimeType);
        // JPEG files start with FF D8 FF
        $this->assertStringStartsWith(pack('H*', 'ffd8ff'), $result->imageData);
    }

    public function testGifFormat(): void
    {
        $captcha = new Captcha(format: ImageFormat::Gif);
        $result = $captcha->generate();

        $this->assertSame('image/gif', $result->mimeType);
        // GIF files start with GIF8
        $this->assertStringStartsWith('GIF8', $result->imageData);
    }

    public function testImageDimensions(): void
    {
        $captcha = new Captcha(width: 200, height: 60);
        $result = $captcha->generate();

        $image = imagecreatefromstring($result->imageData);
        $this->assertNotFalse($image);
        $this->assertSame(200, imagesx($image));
        $this->assertSame(60, imagesy($image));
    }

    public function testCaptchaResultIsReadonly(): void
    {
        $captcha = new Captcha();
        $result = $captcha->generate();

        // Verify properties are readable
        $this->assertNotEmpty($result->imageData);
        $this->assertNotEmpty($result->code);
        $this->assertNotEmpty($result->mimeType);

        // Verify CaptchaResult is a readonly class
        $reflection = new \ReflectionClass(CaptchaResult::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testToDataUri(): void
    {
        $captcha = new Captcha();
        $result = $captcha->generate();

        $dataUri = $result->toDataUri();
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);

        // Verify base64 decodes and matches original data
        $base64 = substr($dataUri, strlen('data:image/png;base64,'));
        $decoded = base64_decode($base64, true);
        $this->assertNotFalse($decoded);
        $this->assertSame($result->imageData, $decoded);
    }

    public function testCustomFontFile(): void
    {
        $fontPath = __DIR__ . '/../assets/captcha.ttf';
        $captcha = new Captcha(font: $fontPath);
        $result = $captcha->generate();

        $this->assertNotEmpty($result->imageData);
    }

    public function testInvalidFontThrowsException(): void
    {
        $this->expectException(CaptchaException::class);
        $this->expectExceptionMessage('Font file not found');

        new Captcha(font: '/nonexistent/font.ttf');
    }

    public function testEmptyCharsThrowsException(): void
    {
        $this->expectException(CaptchaException::class);
        $this->expectExceptionMessage('non-empty');

        new Captcha(chars: '');
    }

    public function testInvalidLengthThrowsException(): void
    {
        foreach ([0, -2] as $length) {
            try {
                new Captcha(length: $length);
                $this->fail("length $length should throw");
            } catch (CaptchaException $e) {
                $this->assertSame('Length must be at least 1.', $e->getMessage());
            }
        }
    }

    public function testInvalidDimensionsThrowException(): void
    {
        foreach ([[0, 40], [120, 0]] as [$w, $h]) {
            try {
                new Captcha(width: $w, height: $h);
                $this->fail("width=$w height=$h should throw");
            } catch (CaptchaException $e) {
                $this->assertSame('Width and height must be at least 1.', $e->getMessage());
            }
        }
    }

    public function testInvalidCharsThrowsException(): void
    {
        $this->expectException(CaptchaException::class);
        $this->expectExceptionMessage('valid UTF-8');

        // Invalid UTF-8 byte sequence
        new Captcha(chars: "\xFF\xFE invalid");
    }

    public function testMultibyteCharsProducesValidCode(): void
    {
        $captcha = new Captcha(length: 4, chars: '中文验证码');
        $result = $captcha->generate();

        // Code must be valid UTF-8 and composed solely of the charset characters
        $this->assertTrue(mb_check_encoding($result->code, 'UTF-8'));
        $this->assertSame(4, mb_strlen($result->code));
        foreach (mb_str_split($result->code) as $ch) {
            $this->assertContains($ch, ['中', '文', '验', '证', '码']);
        }
    }

    public function testImageFormatEnum(): void
    {
        $this->assertSame('image/png', ImageFormat::Png->mimeType());
        $this->assertSame('image/jpeg', ImageFormat::Jpeg->mimeType());
        $this->assertSame('image/gif', ImageFormat::Gif->mimeType());

        $this->assertSame('png', ImageFormat::Png->value);
        $this->assertSame('jpeg', ImageFormat::Jpeg->value);
        $this->assertSame('gif', ImageFormat::Gif->value);
    }

    private function internal(string $prop, Captcha $captcha): int|string
    {
        $reflection = new \ReflectionProperty(Captcha::class, $prop);

        return $reflection->getValue($captcha);
    }

    public function testAllDifficultyLevelsGenerate(): void
    {
        foreach (Difficulty::cases() as $difficulty) {
            $result = (new Captcha(difficulty: $difficulty))->generate();

            $this->assertNotEmpty($result->code);
            $this->assertNotEmpty($result->imageData);
            $this->assertSame(4, strlen($result->code));
        }
    }

    public function testDifficultyScalesInternalParameters(): void
    {
        $easy = new Captcha(difficulty: Difficulty::Easy);
        $hard = new Captcha(difficulty: Difficulty::Hard);

        // Hard must increase noise, interference lines, rotation and drift
        $this->assertGreaterThan(
            $this->internal('noiseLevel', $easy),
            $this->internal('noiseLevel', $hard),
        );
        $this->assertGreaterThan(
            $this->internal('lineCount', $easy),
            $this->internal('lineCount', $hard),
        );
        $this->assertGreaterThan(
            $this->internal('angleRange', $easy),
            $this->internal('angleRange', $hard),
        );
        $this->assertGreaterThan(
            $this->internal('yDrift', $easy),
            $this->internal('yDrift', $hard),
        );
    }

    public function testExplicitNoiseOverridesDifficulty(): void
    {
        $captcha = new Captcha(difficulty: Difficulty::Hard, noiseLevel: 5);

        $this->assertSame(5, $this->internal('noiseLevel', $captcha));
    }

    public function testExplicitLineCountOverridesDifficulty(): void
    {
        $captcha = new Captcha(difficulty: Difficulty::Hard, lineCount: 0);

        $this->assertSame(0, $this->internal('lineCount', $captcha));
        // Hard's noise still applies since only lineCount was overridden
        $this->assertSame(80, $this->internal('noiseLevel', $captcha));
    }

    public function testOmittedDifficultyKeepsLegacyDefaults(): void
    {
        $captcha = new Captcha();

        // Original rendering: no drift, noise 50, lines 3
        $this->assertSame(50, $this->internal('noiseLevel', $captcha));
        $this->assertSame(3, $this->internal('lineCount', $captcha));
        $this->assertSame(0, $this->internal('yDrift', $captcha));
    }

    public function testDifficultyComposesWithMath(): void
    {
        $result = (new Captcha(math: true, difficulty: Difficulty::Easy))->generate();

        $this->assertNotEmpty($result->imageData);
        $this->assertMatchesRegularExpression('/^\d+$/', $result->code);
    }

    public function testMathModeProducesNumericAnswerAndImage(): void
    {
        $captcha = new Captcha(math: true);
        $result = $captcha->generate();

        // The answer must be a non-empty integer
        $this->assertMatchesRegularExpression('/^\d+$/', $result->code);
        $this->assertSame('image/png', $result->mimeType);
        $this->assertNotEmpty($result->imageData);
    }

    public function testMathAnswerRangeIsSane(): void
    {
        $captcha = new Captcha(math: true);
        $answers = [];
        for ($i = 0; $i < 100; $i++) {
            $answers[] = (int) $captcha->generate()->code;
        }

        // +: 2..18, -: 1..9, *: 1..81 combined => within 1..81
        foreach ($answers as $a) {
            $this->assertGreaterThanOrEqual(1, $a);
            $this->assertLessThanOrEqual(81, $a);
        }
    }

    public function testMathModeWorksWithVerifier(): void
    {
        $captcha = new Captcha(math: true);
        $verifier = new CaptchaVerifier();

        for ($i = 0; $i < 20; $i++) {
            $result = $captcha->generate();
            $this->assertTrue($verifier->verify($result->code, $result->code));
        }
    }

    public function testMathRenderedImageIsValid(): void
    {
        $captcha = new Captcha(math: true);
        $result = $captcha->generate();

        $image = imagecreatefromstring($result->imageData);
        $this->assertNotFalse($image);
        $this->assertSame(170, imagesx($image));
        $this->assertSame(50, imagesy($image));
    }

    public function testMultipleGeneratesProduceDifferentCodes(): void
    {
        $captcha = new Captcha(length: 8);
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = $captcha->generate()->code;
        }

        // 10 generations should produce at least 2 different codes (almost certain probabilistically)
        $unique = array_unique($codes);
        $this->assertGreaterThan(1, count($unique));
    }

    public function testNoiseAndLineConfig(): void
    {
        // Verify configuration with noise and interference lines generates correctly
        $captcha = new Captcha(
            noiseLevel: 100,
            lineCount: 5,
        );
        $result = $captcha->generate();

        $this->assertNotEmpty($result->imageData);
        $this->assertSame('image/png', $result->mimeType);
    }

    public function testZeroNoiseAndLines(): void
    {
        $captcha = new Captcha(
            noiseLevel: 0,
            lineCount: 0,
        );
        $result = $captcha->generate();

        $this->assertNotEmpty($result->imageData);
    }
}
