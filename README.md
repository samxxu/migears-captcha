# migears/captcha

![Version](https://img.shields.io/badge/version-2.0.0-blue)

A lightweight captcha generation library for PHP 8.1+, with zero required dependencies.

## Features

- Generates random captcha strings (numbers + letters, configurable length and character set)
- Generates captcha images (configurable width/height, font, interference lines, noise dots)
- Math captcha mode (`math: true`) — an arithmetic puzzle instead of random characters
- Supports custom TTF fonts
- Supports three output formats: PNG, JPEG, GIF
- `CaptchaResult` is a `readonly` value object
- Multibyte-aware character sets (e.g. CJK)
- `CaptchaVerifier` provides timing-safe, case-optional validation
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

### Math Mode

Pass `math: true` to render an arithmetic puzzle instead of random characters.
The `code` is the arithmetic result, which is what you store and verify:

```php
$captcha = new Captcha(math: true);

$result = $captcha->generate();

// The image shows e.g. "7 + 3 = ?"
echo $result->code;   // "10" — the answer

// Verifying the user's typed answer:
$ok = $verifier->verify($input, $result->code);
```

Uses `+`, `-`, `*` with operands 1–9; subtraction is always positive. When
`width`/`height` are omitted, math mode defaults to 170×50 (wider to fit the
extra characters).

## Configuration Options

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `length` | `int` | `4` | Captcha character length (ignored in math mode) |
| `width` | `?int` | `120` (170 in math mode) | Image width in pixels |
| `height` | `?int` | `40` (50 in math mode) | Image height in pixels |
| `font` | `?string` | Built-in font | Custom TTF font file path |
| `format` | `ImageFormat` | `ImageFormat::Png` | Output image format |
| `noiseLevel` | `int` | `50` | Number of noise dots |
| `lineCount` | `int` | `3` | Number of interference lines |
| `chars` | `string` | See below | Captcha character set (ignored in math mode) |
| `math` | `bool` | `false` | Render an arithmetic puzzle instead of random characters |

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

## Verification

Compare a user-submitted answer against the stored code with `CaptchaVerifier`.
It wraps [timing-safe comparison](https://www.php.net/hash_equals) and optional
case folding, so callers don't have to implement these details themselves.

```php
use MiGears\Captcha\CaptchaVerifier;

$verifier = new CaptchaVerifier();

// Case-insensitive by default
$verifier->verify('a3fk', 'a3fK'); // true

// Case-sensitive
$verifier->verify('a3fk', 'a3fK', caseInsensitive: false); // false

// Empty inputs always fail, no exception is thrown
$verifier->verify('', 'a3fK'); // false
```

Like storage, **expiry and one-time consumption are left to the caller**. The
library does not track when a code was issued or whether it was already used:

```php
$_SESSION['captcha_expires_at'] = time() + 300; // 5 minutes
$_SESSION['captcha_used'] = false;

// On submission:
if (!$_SESSION['captcha_used'] && time() < $_SESSION['captcha_expires_at']) {
    $ok = $verifier->verify($input, $_SESSION['captcha_code']);
    if ($ok) {
        $_SESSION['captcha_used'] = true; // consume
    }
}
```

## Multibyte Note

The character set is multibyte-aware: a code generated from the default ASCII
set or a UTF-8 set (e.g. Chinese) is always valid output. **However, images are
rendered with the bundled `assets/captcha.ttf`, which only contains Latin
glyphs.** To render non-Latin codes (CJK, Cyrillic, etc.), pass a font that
covers those characters via the `font` option.

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
- 数学算式验证码模式（`math: true`）——用算术题替代随机字符
- 支持自定义 TTF 字体
- 支持 PNG、JPEG、GIF 三种输出格式
- `CaptchaResult` 为 `readonly` 值对象
- 字符集支持多字节（如中文）
- `CaptchaVerifier` 提供常时安全、可选忽略大小的校验
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

### 数学算式模式

传入 `math: true` 渲染一道算术题，而非随机字符。`code` 即为算术结果，
用于存储和校验：

```php
$captcha = new Captcha(math: true);

$result = $captcha->generate();

// 图片显示例如 "7 + 3 = ?"
echo $result->code;   // "10" —— 答案

// 校验用户输入的答案：
$ok = $verifier->verify($input, $result->code);
```

使用 `+`、`-`、`*`，操作数为 1–9，减法恒为正。当未显式指定 `width`/`height`
时，数学模式下默认 170×50（更宽以容纳额外字符）。

## 配置选项

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `length` | `int` | `4` | 验证码字符长度（数学模式忽略） |
| `width` | `?int` | `120`（数学模式 170） | 图片宽度（像素） |
| `height` | `?int` | `40`（数学模式 50） | 图片高度（像素） |
| `font` | `?string` | 内置字体 | 自定义 TTF 字体文件路径 |
| `format` | `ImageFormat` | `ImageFormat::Png` | 输出图片格式 |
| `noiseLevel` | `int` | `50` | 噪点数量 |
| `lineCount` | `int` | `3` | 干扰线数量 |
| `chars` | `string` | 见下 | 验证码字符集（数学模式忽略） |
| `math` | `bool` | `false` | 用算术题替代随机字符 |

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

## 验证码校验

使用 `CaptchaVerifier` 将用户提交的答案与已存储的 code 进行比较。它封装了
[常时比较](https://www.php.net/hash_equals) 和可选的大小写折叠，调用方无需
自己实现这些细节。

```php
use MiGears\Captcha\CaptchaVerifier;

$verifier = new CaptchaVerifier();

// 默认忽略大小写
$verifier->verify('a3fk', 'a3fK'); // true

// 区分大小写
$verifier->verify('a3fk', 'a3fK', caseInsensitive: false); // false

// 空输入恒为 false，不抛异常
$verifier->verify('', 'a3fK'); // false
```

与存储一样，**过期与一次性消费由调用方负责**。本库不追踪 code 的签发时间，
也不记录是否已被用过：

```php
$_SESSION['captcha_expires_at'] = time() + 300; // 5 分钟过期
$_SESSION['captcha_used'] = false;

// 用户提交时：
if (!$_SESSION['captcha_used'] && time() < $_SESSION['captcha_expires_at']) {
    $ok = $verifier->verify($input, $_SESSION['captcha_code']);
    if ($ok) {
        $_SESSION['captcha_used'] = true; // 消费
    }
}
```

## 多字节说明

字符集支持多字节：无论用默认 ASCII 字符集还是 UTF-8 字符集（如中文），生成
的 code 都是合法的。**但图片使用内置 `assets/captcha.ttf` 渲染，该字体仅含
拉丁字形。** 若要渲染非拉丁字符（中文、西里尔等），请通过 `font` 选项传入
支持这些字符的字体。

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
