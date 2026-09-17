# migears/captcha

![Version](https://img.shields.io/badge/version-2.0.0-blue)

A lightweight captcha generation library for PHP 8.1+, with zero required dependencies.

## Features

- Generates random captcha strings (numbers + letters, configurable length and character set)
- Generates captcha images (configurable width/height, font, interference lines, noise dots)
- Supports custom TTF fonts
- Supports three output formats: PNG, JPEG, GIF
- `CaptchaResult` is a `readonly` value object
- Zero required dependencies (GD extension is suggested)
- Minimalist API, outputs nothing, only returns data

## Installation

```bash
composer require migears/captcha
```

> Requires PHP 8.1 or higher. GD extension is recommended.

## Quick Start

```php
use MiGears\Captcha\Captcha;
use MiGears\Captcha\ImageFormat;

$captcha = new Captcha(
    length: 4,
    width: 120,
    height: 40,
    format: ImageFormat::Png,
);

$result = $captcha->generate();

// Captcha text
echo $result->code;       // e.g.: a3fK

// Image binary data
echo $result->imageData;

// MIME type
echo $result->mimeType;   // image/png

// Data URI (convenient for embedding in HTML)
echo $result->toDataUri();// data:image/png;base64,...
```

## Configuration Options

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `length` | `int` | `4` | Captcha character length |
| `width` | `int` | `120` | Image width in pixels |
| `height` | `int` | `40` | Image height in pixels |
| `font` | `?string` | Built-in font | Custom TTF font file path |
| `format` | `ImageFormat` | `ImageFormat::Png` | Output image format |
| `noiseLevel` | `int` | `50` | Number of noise dots |
| `lineCount` | `int` | `3` | Number of interference lines |
| `chars` | `string` | See below | Captcha character set |

Default character set (easily confused characters 0/O/1/l/I removed):
```
abCDefGhiJkLmNPQrstUVWXyz23456789
```

## Captcha Storage

This library **does not handle** captcha storage (Session / cache, etc.), it is managed by the user:

```php
// Generate captcha
$result = $captcha->generate();

// Store the code (example: using Session)
$_SESSION['captcha_code'] = $result->code;

// Output the image
header('Content-Type: ' . $result->mimeType);
echo $result->imageData;
```

## Exceptions

On failure, throws `MiGears\Captcha\Exception\CaptchaException`:

```php
use MiGears\Captcha\Exception\CaptchaException;

try {
    $captcha = new Captcha(font: '/path/to/font.ttf');
    $result = $captcha->generate();
} catch (CaptchaException $e) {
    // handle the error
}
```

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT

---

# migears/captcha

![Version](https://img.shields.io/badge/version-2.0.0-blue)

轻量级验证码生成库，PHP 8.1+，零强制依赖。

## 特性

- 生成随机验证码字符串（数字+字母，可配置长度和字符集）
- 生成验证码图片（可配置宽高、字体、干扰线、噪点）
- 支持自定义 TTF 字体
- 支持 PNG、JPEG、GIF 三种输出格式
- `CaptchaResult` 为 `readonly` 值对象
- 零强制依赖（GD 扩展为建议依赖）
- 极简 API，不输出任何内容，仅返回数据

## 安装

```bash
composer require migears/captcha
```

> 需要 PHP 8.1 及以上，建议安装 GD 扩展。

## 快速开始

```php
use MiGears\Captcha\Captcha;
use MiGears\Captcha\ImageFormat;

$captcha = new Captcha(
    length: 4,
    width: 120,
    height: 40,
    format: ImageFormat::Png,
);

$result = $captcha->generate();

// 验证码文本
echo $result->code;       // 例如: a3fK

// 图片二进制数据
echo $result->imageData;

// MIME 类型
echo $result->mimeType;   // image/png

// Data URI（方便嵌入 HTML）
echo $result->toDataUri();// data:image/png;base64,...
```

## 配置选项

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `length` | `int` | `4` | 验证码字符长度 |
| `width` | `int` | `120` | 图片宽度（像素） |
| `height` | `int` | `40` | 图片高度（像素） |
| `font` | `?string` | 内置字体 | 自定义 TTF 字体文件路径 |
| `format` | `ImageFormat` | `ImageFormat::Png` | 输出图片格式 |
| `noiseLevel` | `int` | `50` | 噪点数量 |
| `lineCount` | `int` | `3` | 干扰线数量 |
| `chars` | `string` | 见下 | 验证码字符集 |

默认字符集（已去除易混淆字符 0/O/1/l/I）：
```
abCDefGhiJkLmNPQrstUVWXyz23456789
```

## 验证码存储

本库**不负责**验证码的存储（Session / 缓存等），由使用者自行管理：

```php
// 生成验证码
$result = $captcha->generate();

// 存储 code（示例：使用 Session）
$_SESSION['captcha_code'] = $result->code;

// 输出图片
header('Content-Type: ' . $result->mimeType);
echo $result->imageData;
```

## 异常

失败时抛出 `MiGears\Captcha\Exception\CaptchaException`：

```php
use MiGears\Captcha\Exception\CaptchaException;

try {
    $captcha = new Captcha(font: '/path/to/font.ttf');
    $result = $captcha->generate();
} catch (CaptchaException $e) {
    // 处理错误
}
```

## 测试

```bash
composer install
vendor/bin/phpunit
```

## License

MIT
