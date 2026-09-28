# migears-captcha — Known Issues / 已知问题

> Summary of this module's issues. The items themselves are in [`issues/`](issues/README.md), one file
> per item: a front-matter header and a thread. This file is generated from them and can be rewritten at
> any time; edit an item, never this file.
>
> 本模块问题的概览。条目本体在 [`issues/`](issues/README.md)，一条目一文件：前置字段加讨论串。
> 本文件由条目生成，随时可以整段重写；请改条目，不要改本文件。
>
> From the miGears Full-Module Code Review Report (4th round, 2026-09-27).

| | |
|---|---|
| Status / 状态 | **Best state / 状态最好** |
| Size / 体量 | src 332 lines (229 net) · 40 tests · 6 src files |

Legend / 图例 — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs
级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## At a glance / 状态一览

| | |
|---|---|
| Items / 条目 | P0 0 · P1 0 · P2 1 · P3 2 · other 1 |
| Answered / 已回复 | 1 of 4 |
| Waiting / 等待回复 | `P2-1`, `P3-1`, `P3-2` |

| id | level | status | title |
|---|---|---|---|
| [`P2-1`](issues/P2-1.md) | P2 | **open** | The README states `length` and `chars` are 'ignored in math mode'; the … |
| [`P3-1`](issues/P3-1.md) | P3 | **open** | The README math example calls `$verifier->verify(...)` without … |
| [`P3-2`](issues/P3-2.md) | P3 | **open** | `Captcha::VERSION` still has zero references while composer and the … |
| [`G2`](issues/G2.md) | - | **fixed** | Strict flags: `phpunit.xml.dist` currently sets none of the five. The … |

## Verdict / 结论

Clean and compact. One documentation contradiction remains — the README still says math mode ignores `length`/`chars` while the constructor validates them unconditionally, so copying the documented math example fails.

干净紧凑。剩一处文档矛盾：README 仍称 math 模式忽略 length/chars，而构造器无条件校验，照抄文档的 math 示例会失败。

## Fixed since the last round / 本轮已修复确认

ext-gd 已从 suggest 提升为 require；多字节折叠改用 mb_strtolower（无 mbstring 时回退）；CI 已补。 

## Test gaps / 测试盲区

CaptchaTest only covers non-math `length:0` / `chars:""`; there is no math-mode case, which is exactly why the contradiction survived. No VERSION test.

CaptchaTest 只测非 math 的 length:0 与 chars:""，没有 math 模式用例——这正是矛盾长期存在的原因。无 VERSION 用例。

## Verification protocol / 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: all four on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。
