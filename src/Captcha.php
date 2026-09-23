<?php

declare(strict_types=1);

namespace MiGears\Captcha;

use MiGears\Captcha\Exception\CaptchaException;

class Captcha
{
    public const VERSION = '2.0.0';

    private const DEFAULT_CHARS = 'abCDefGhiJkLmNPQrstUVWXyz23456789';

    /** @var string[] One character per element (multibyte-aware). */
    private array $charSet;

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

        if ($this->length < 1) {
            throw new CaptchaException('Length must be at least 1.');
        }
        if ($this->width < 1 || $this->height < 1) {
            throw new CaptchaException('Width and height must be at least 1.');
        }
        if ($this->noiseLevel < 0 || $this->lineCount < 0) {
            throw new CaptchaException('Noise level and line count cannot be negative.');
        }

        $charSet = preg_split('//u', $this->chars, -1, PREG_SPLIT_NO_EMPTY);
        if ($charSet === false || $charSet === []) {
            throw new CaptchaException('Chars must be a non-empty valid UTF-8 character set.');
        }
        $this->charSet = $charSet;
    }

    /**
     * Generates a captcha image and code
     *
     * @throws CaptchaException
     */
    public function generate(): CaptchaResult
    {
        $characters = $this->generateCharacters();
        $code = implode('', $characters);
        $image = $this->createImage($characters);

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

    /**
     * @return string[]
     */
    private function generateCharacters(): array
    {
        $max = count($this->charSet) - 1;
        $characters = [];
        for ($i = 0; $i < $this->length; $i++) {
            $characters[] = $this->charSet[random_int(0, $max)];
        }
        return $characters;
    }

    /**
     * @param string[] $characters
     */
    private function createImage(array $characters): \GdImage
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        // Light background
        $bg = imagecolorallocate($image, 245, 245, 245);
        imagefilledrectangle($image, 0, 0, $this->width - 1, $this->height - 1, $bg);

        $this->addNoise($image);
        $this->addLines($image);

        $fontSize = (int) ($this->height * 0.55);
        $step = $this->width / count($characters);

        for ($i = 0; $i < count($characters); $i++) {
            $angle = random_int(-20, 20);
            $x = (int) ($i * $step + $step * 0.2);
            $y = (int) ($this->height * 0.7);
            $color = $this->randomDarkColor($image);
            imagettftext($image, $fontSize, $angle, $x, $y, $color, $this->font, $characters[$i]);
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
