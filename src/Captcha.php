<?php

declare(strict_types=1);

namespace MiGears\Captcha;

use MiGears\Captcha\Exception\CaptchaException;

class Captcha
{
    public const VERSION = '2.0.0';

    private const DEFAULT_CHARS = 'abCDefGhiJkLmNPQrstUVWXyz23456789';

    public function __construct(
        private int $length = 4,
        private int $width = 120,
        private int $height = 40,
        private ?string $font = null,
        private ImageFormat $format = ImageFormat::Png,
        private int $noiseLevel = 50,
        private int $lineCount = 3,
        private string $chars = self::DEFAULT_CHARS,
    ) {
        if (!extension_loaded('gd')) {
            throw new CaptchaException('GD extension is required to generate captcha images.');
        }

        $this->font ??= __DIR__ . '/../assets/captcha.ttf';

        if (!is_file($this->font)) {
            throw new CaptchaException(sprintf('Font file not found: %s', $this->font));
        }
    }

    /**
     * Generates a captcha image and code
     *
     * @throws CaptchaException
     */
    public function generate(): CaptchaResult
    {
        $code = $this->generateCode();
        $image = $this->createImage($code);

        ob_start();
        match ($this->format) {
            ImageFormat::Png  => imagepng($image),
            ImageFormat::Jpeg => imagejpeg($image, quality: 90),
            ImageFormat::Gif  => imagegif($image),
        };
        $imageData = (string) ob_get_clean();

        return new CaptchaResult(
            imageData: $imageData,
            code: $code,
            mimeType: $this->format->mimeType(),
        );
    }

    private function generateCode(): string
    {
        $max = strlen($this->chars) - 1;
        $code = '';
        for ($i = 0; $i < $this->length; $i++) {
            $code .= $this->chars[random_int(0, $max)];
        }
        return $code;
    }

    private function createImage(string $code): \GdImage
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        // Light background
        $bg = imagecolorallocate($image, 245, 245, 245);
        imagefilledrectangle($image, 0, 0, $this->width - 1, $this->height - 1, $bg);

        $this->addNoise($image);
        $this->addLines($image);

        $fontSize = (int) ($this->height * 0.55);
        $step = $this->width / $this->length;

        for ($i = 0; $i < $this->length; $i++) {
            $angle = random_int(-20, 20);
            $x = (int) ($i * $step + $step * 0.2);
            $y = (int) ($this->height * 0.7);
            $color = $this->randomDarkColor($image);
            imagettftext($image, $fontSize, $angle, $x, $y, $color, $this->font, $code[$i]);
        }

        return $image;
    }

    private function addNoise(\GdImage $image): void
    {
        for ($i = 0; $i < $this->noiseLevel; $i++) {
            $color = $this->randomLightColor($image);
            imagesetpixel($image, random_int(0, $this->width - 1), random_int(0, $this->height - 1), $color);
        }
    }

    private function addLines(\GdImage $image): void
    {
        for ($i = 0; $i < $this->lineCount; $i++) {
            $color = $this->randomDarkColor($image);
            imageline(
                $image,
                random_int(0, (int) ($this->width * 0.3)),
                random_int(0, $this->height - 1),
                random_int((int) ($this->width * 0.7), $this->width - 1),
                random_int(0, $this->height - 1),
                $color,
            );
        }
    }

    private function randomLightColor(\GdImage $image): int
    {
        return imagecolorallocate($image, random_int(150, 220), random_int(150, 220), random_int(150, 220));
    }

    private function randomDarkColor(\GdImage $image): int
    {
        return imagecolorallocate($image, random_int(20, 100), random_int(20, 100), random_int(20, 100));
    }
}
