<?php

declare(strict_types=1);

namespace MiGears\Captcha\Tests;

use PHPUnit\Framework\TestCase;
use MiGears\Captcha\Captcha;
use MiGears\Captcha\CaptchaResult;
use MiGears\Captcha\ImageFormat;
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

    public function testImageFormatEnum(): void
    {
        $this->assertSame('image/png', ImageFormat::Png->mimeType());
        $this->assertSame('image/jpeg', ImageFormat::Jpeg->mimeType());
        $this->assertSame('image/gif', ImageFormat::Gif->mimeType());

        $this->assertSame('png', ImageFormat::Png->value);
        $this->assertSame('jpeg', ImageFormat::Jpeg->value);
        $this->assertSame('gif', ImageFormat::Gif->value);
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
