<?php

declare(strict_types=1);

namespace MiGears\Captcha\Tests;

use PHPUnit\Framework\TestCase;
use MiGears\Captcha\CaptchaVerifier;

final class CaptchaVerifierTest extends TestCase
{
    private CaptchaVerifier $verifier;

    protected function setUp(): void
    {
        $this->verifier = new CaptchaVerifier();
    }

    public function testExactMatch(): void
    {
        $this->assertTrue($this->verifier->verify('a3fK', 'a3fK'));
    }

    public function testCaseInsensitiveByDefault(): void
    {
        $this->assertTrue($this->verifier->verify('A3fk', 'a3fK'));
        $this->assertTrue($this->verifier->verify('a3fK', 'A3FK'));
    }

    public function testCaseSensitiveMode(): void
    {
        $this->assertFalse($this->verifier->verify('a3fk', 'a3fK', caseInsensitive: false));
        $this->assertTrue($this->verifier->verify('a3fK', 'a3fK', caseInsensitive: false));
    }

    public function testMismatchReturnsFalse(): void
    {
        $this->assertFalse($this->verifier->verify('wrong', 'a3fK'));
    }

    public function testEmptyInputsReturnFalse(): void
    {
        $this->assertFalse($this->verifier->verify('', 'a3fK'));
        $this->assertFalse($this->verifier->verify('a3fK', ''));
        $this->assertFalse($this->verifier->verify('', ''));
    }

    public function testVerifiesGeneratedCode(): void
    {
        $captcha = new \MiGears\Captcha\Captcha();
        $result = $captcha->generate();

        $this->assertTrue($this->verifier->verify($result->code, $result->code));
        // Changing any single character must fail
        $tampered = ($result->code[0] === 'a') ? 'b' . substr($result->code, 1) : 'a' . substr($result->code, 1);
        $this->assertFalse($this->verifier->verify($tampered, $result->code));
    }

    public function testWhitespaceIsSignificant(): void
    {
        // No trimming is performed; exact input is expected
        $this->assertFalse($this->verifier->verify(' a3fK', 'a3fK'));
    }
}