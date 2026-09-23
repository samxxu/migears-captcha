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

    private int $angleRange;

    private int $yDrift;

    public function __construct(
        private int $length = 4,
        private ?int $width = null,
        private ?int $height = null,
        private ?string $font = null,
        private ImageFormat $format = ImageFormat::Png,
        private ?int $noiseLevel = null,
        private ?int $lineCount = null,
        private string $chars = self::DEFAULT_CHARS,
        private bool $math = false,
        private ?Difficulty $difficulty = null,
    ) {
        if (!extension_loaded('gd')) {
            throw new CaptchaException('GD extension is required to generate captcha images.');
        }

        $this->font ??= __DIR__ . '/../assets/captcha.ttf';

        if (!is_file($this->font)) {
            throw new CaptchaException(sprintf('Font file not found: %s', $this->font));
        }

        // Math captchas carry more characters (operands + operator + "= ?"), so widen by default
        $this->width ??= $this->math ? 170 : 120;
        $this->height ??= $this->math ? 50 : 40;

        // Difficulty presets tune noise, interference lines and distortion.
        // Explicit noiseLevel/lineCount win; omitting difficulty keeps the
        // original no-drift rendering (drift defaults to 0), so defaults never change.
        [$dNoise, $dLines, $dAngle, $dDrift] = $this->difficulty === null
            ? [50, 3, 20, 0]
            : match ($this->difficulty) {
                Difficulty::Easy   => [20, 1, 12, 3],
                Difficulty::Medium => [50, 3, 22, 6],
                Difficulty::Hard   => [80, 6, 35, 10],
            };
        $this->noiseLevel ??= $dNoise;
        $this->lineCount ??= $dLines;
        $this->angleRange = $dAngle;
        $this->yDrift = $dDrift;

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
        [$characters, $code] = $this->generatePuzzle();
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
     * The expected answer is always the `code`. In char mode the code is the
     * random string shown in the image; in math mode it is the arithmetic result.
     *
     * @return array{0: string[], 1: string}
     */
    private function generatePuzzle(): array
    {
        if ($this->math) {
            $a = random_int(1, 9);
            $b = random_int(1, 9);
            $op = ['+', '-', '*'][random_int(0, 2)];
            if ($op === '-') {
                // keep subtraction positive: 2 <= a <= 9, 1 <= b < a
                $a = random_int(2, 9);
                $b = random_int(1, $a - 1);
            }
            $answer = match ($op) {
                '+' => $a + $b,
                '-' => $a - $b,
                '*' => $a * $b,
            };
            $characters = preg_split(
                '//u',
                sprintf('%d %s %d = ?', $a, $op, $b),
                -1,
                PREG_SPLIT_NO_EMPTY,
            );

            return [$characters, (string) $answer];
        }

        $characters = $this->generateCharacters();

        return [$characters, implode('', $characters)];
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
            $angle = random_int(-$this->angleRange, $this->angleRange);
            $x = (int) ($i * $step + $step * 0.2);
            $y = (int) ($this->height * 0.7) + random_int(-$this->yDrift, $this->yDrift);
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
