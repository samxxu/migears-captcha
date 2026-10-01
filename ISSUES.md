# migears-captcha — Known Issues

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> From the miGears Full-Module Code Review Report (5th round, 2026-09-28).

| | |
|---|---|
| Status | **Best state** |
| Size | src 230 lines (net) · 41 tests · 2 src files |

Legend — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs

## At a glance

| | |
|---|---|
| Unsettled | P0 0 · P1 0 · P2 1 · P3 0 · other 0 |
| Settled | 3 of 4 |
| Waiting on the owner | `P2-1` |
| Waiting on the coordinator | _nothing_ |
| Waiting on the reviewer | _nothing_ |
| Deferred, owing nobody | _nothing_ |

| id | level | status | title |
|---|---|---|---|
| [`P2-1`](issues/P2-1.md) | P2 | **accepted** | The README states `length` and `chars` are 'ignored in math mode'; the … |
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | The README math example calls `$verifier->verify(...)` without … |
| [`P3-2`](issues/P3-2.md) | P3 | **verified** | `Captcha::VERSION` still has zero references while composer and the … |
| [`G2`](issues/G2.md) | - | **verified** | Strict flags: `phpunit.xml.dist` currently sets none of the five. The … |

## Unclosed

What is left to do here: every item whose `status` is not `verified` or `closed`,
highest severity first. `waiting on` is the party who acts next, read from that status.

| | |
|---|---|
| Unclosed | **1** of 4 |
| By status | `accepted` 1 |
| Waiting on | owner 1 |

| level | item | status | waiting on | title |
|---|---|---|---|---|
| **P2** | [`P2-1`](issues/P2-1.md) | `accepted` | owner | The README states `length` and `chars` are 'ignored in math mode'; the … |

## Verdict

A focused captcha generator with image and math modes; all prior findings are resolved and the code now matches the documented contract.

## Fixed since the last round

G2 strict flags confirmed complete; P2-1 (math-mode length/chars validation) fixed — code moved to match the docs, math mode now ignores both parameters as the README always promised.

## Test gaps

No test for GD extension being unavailable (graceful degradation path); no test for very long custom char strings; no test for output quality parameters.

## Verification protocol

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.


---

# migears-captcha — 已知问题

> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> 出自 miGears 全模块代码评审报告（5th round，2026-09-28）。

| | |
|---|---|
| 状态 | **状态最好** |
| 体量 | src 230 行（净）· 41 个用例 · 2 个源文件 |

级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## 状态一览

| | |
|---|---|
| 未了结 | P0 0 · P1 0 · P2 1 · P3 0 · 其他 0 |
| 已了结 | 3 / 4 |
| 等模块主 | `P2-1` |
| 等协调人 | _无_ |
| 等评审方 | _无_ |
| 已暂缓，不欠谁 | _无_ |

| id | 级别 | 状态 | 标题 |
|---|---|---|---|
| [`P2-1`](issues/P2-1.md) | P2 | **accepted** | README 称 length 与 chars「在 math 模式下被忽略」；实现却无条件校验：new Captcha(math: true, … |
| [`P3-1`](issues/P3-1.md) | P3 | **verified** | README 的 math 示例直接调用 $verifier->verify(...)，未先构造 CaptchaVerifier，照抄即失败。 |
| [`P3-2`](issues/P3-2.md) | P3 | **verified** | Captcha::VERSION 仍零引用，而 composer 与 README 徽章各写一份；phpunit.xml.dist … |
| [`G2`](issues/G2.md) | - | **verified** | 严格开关：`phpunit.xml.dist` … |

## 未关闭

本模块还剩什么要做：所有 `status` 不是 `verified` 或 `closed` 的条目，按严重度从高到低。
`waiting on` 是下一步该动手的一方，由其状态读出。

| | |
|---|---|
| 未关闭 | **1** / 4 |
| 按状态 | `accepted` 1 |
| 等在谁 | 模块主 1 |

| 级别 | 条目 | 状态 | 等在谁 | 标题 |
|---|---|---|---|---|
| **P2** | [`P2-1`](issues/P2-1.md) | `accepted` | 模块主 | README 称 length 与 chars「在 math 模式下被忽略」；实现却无条件校验：new Captcha(math: true, … |

## 结论

一个专注的验证码生成器，支持图片与数学模式；所有既有 finding 均已解决，代码现已与文档契约一致。

## 本轮已修复确认

G2 strict flags confirmed complete; P2-1 (math-mode length/chars validation) fixed — code moved to match the docs, math mode now ignores both parameters as the README always promised.

## 测试盲区

无 GD 扩展不可用测试（优雅降级路径）；无超长自定义字符集测试；无输出质量参数测试。

## 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- `phpunit.xml.dist` 中的 warning/notice/deprecation/risky 开关：四个全开
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
