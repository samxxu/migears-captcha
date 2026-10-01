# migears-captcha — Known Issues

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> From the miGears Full-Module Code Review Report (6th round, 2026-10-01).

| | |
|---|---|
| Status | **Best state** |
| Size | src 232 lines (net) · 41 tests · 6 src files |

Legend — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs

## At a glance

| | |
|---|---|
| Unsettled | P0 0 · P1 0 · P2 0 · P3 0 · other 0 |
| Settled | 4 of 4 |
| Waiting on the owner | _nothing_ |
| Waiting on the coordinator | _nothing_ |
| Waiting on the reviewer | _nothing_ |
| Deferred, owing nobody | _nothing_ |

| id | level | status | title |
|---|---|---|---|
| [`P2-1`](issues/P2-1.md) | P2 | **verified** | The README states `length` and `chars` are 'ignored in math mode'; the … |
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | The README math example calls `$verifier->verify(...)` without … |
| [`P3-2`](issues/P3-2.md) | P3 | **verified** | `Captcha::VERSION` still has zero references while composer and the … |
| [`G2`](issues/G2.md) | - | **verified** | Strict flags: `phpunit.xml.dist` currently sets none of the five. The … |

## Unclosed

_Nothing unclosed — every item in this module is `verified` or `closed`._

## Verdict

The README and the code now tell the same story about math mode, and the guard is load-bearing rather than decorative.

## Fixed since the last round

P2-1 verified by mutation: the README now states in both halves that math mode neither uses nor validates length/chars, matching the guard that was already in the code. Removing the guard turns the module’s own test red.

## Test gaps

generate() never calls imagedestroy(), and no test asserts that ob_get_level() is balanced after a write failure; ImageFormat::Jpeg quality has no assertion of its own; there is no GD-absent negative case.

## Verification protocol

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.


---

# migears-captcha — 已知问题

> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> 出自 miGears 全模块代码评审报告（6th round，2026-10-01）。

| | |
|---|---|
| 状态 | **状态最好** |
| 体量 | src 232 行（净）· 41 个用例 · 6 个源文件 |

级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## 状态一览

| | |
|---|---|
| 未了结 | P0 0 · P1 0 · P2 0 · P3 0 · 其他 0 |
| 已了结 | 4 / 4 |
| 等模块主 | _无_ |
| 等协调人 | _无_ |
| 等评审方 | _无_ |
| 已暂缓，不欠谁 | _无_ |

| id | 级别 | 状态 | 标题 |
|---|---|---|---|
| [`P2-1`](issues/P2-1.md) | P2 | **verified** | README 称 length 与 chars「在 math 模式下被忽略」；实现却无条件校验：new Captcha(math: true, … |
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | README 的 math 示例直接调用 $verifier->verify(...)，未先构造 CaptchaVerifier，照抄即失败。 |
| [`P3-2`](issues/P3-2.md) | P3 | **verified** | Captcha::VERSION 仍零引用，而 composer 与 README 徽章各写一份；phpunit.xml.dist … |
| [`G2`](issues/G2.md) | - | **verified** | 严格开关：`phpunit.xml.dist` … |

## 未关闭

_无未关闭条目——本模块每条都已是 `verified` 或 `closed`。_

## 结论

README 与代码对数学模式的说明现已一致，且该守卫确实承重，不是摆设。

## 本轮已修复确认

P2-1 verified by mutation: the README now states in both halves that math mode neither uses nor validates length/chars, matching the guard that was already in the code. Removing the guard turns the module’s own test red.

## 测试盲区

generate() 从不调用 imagedestroy()，也无「写入失败后 ob_get_level() 仍平衡」的断言；ImageFormat::Jpeg 的 quality 无独立断言；无「GD 未加载」负例。

## 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- `phpunit.xml.dist` 中的 warning/notice/deprecation/risky 开关：四个全开
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
