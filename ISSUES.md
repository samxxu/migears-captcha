# migears-captcha — Known Issues / 已知问题

> Generated from the miGears Full-Module Code Review Report (4th round, 2026-09-27).
> This file has two regions. Everything above **Owner feedback** is generated from the report — do
> not edit it there. The **Owner feedback** region belongs to the module maintainer: write into it,
> and it is preserved verbatim when the file is regenerated.
> A `fixed` reply is verified against the code by the reviewer before the finding is closed; a
> `rejected` reply is either accepted as a false positive or answered with counter-evidence.
>
> 本文件分两个区域。**「负责人反馈」之前的全部内容**由评审报告生成，请勿在该区修改；
> **「负责人反馈」区**归模块负责人所有，重新生成时会原样保留。
> 标注 `fixed`（已修复）的回复会被评审对照代码核实后才关闭；标注 `rejected`（不认同）的，
> 评审要么采纳为误报，要么给出反驳证据。
>
> 摘自 miGears 全模块代码评审报告（第四轮，2026-09-27）。

| | |
|---|---|
| Status / 状态 | **Best state / 状态最好** |
| Findings / 问题 | P0 0 · P1 0 · P2 1 · P3 2 |
| Size / 体量 | src 332 lines (229 net) · 40 tests · 6 src files |

Legend / 图例 — **P0** functional or security · **P1** documentation that fails when copied · **P2** robustness · **P3** metadata and docs
级别说明 — **P0** 功能性或安全级 · **P1** 文档照抄即错 · **P2** 健壮性 · **P3** 元数据与文档

## Verdict / 结论

Clean and compact. One documentation contradiction remains — the README still says math mode ignores `length`/`chars` while the constructor validates them unconditionally, so copying the documented math example fails.

干净紧凑。剩一处文档矛盾：README 仍称 math 模式忽略 length/chars，而构造器无条件校验，照抄文档的 math 示例会失败。

## Fixed since the last round / 本轮已修复确认

ext-gd 已从 suggest 提升为 require；多字节折叠改用 mb_strtolower（无 mbstring 时回退）；CI 已补。 

## Open findings / 未修问题


### P2

**P2-1** — `src/Captcha.php:63-77 vs README:108,349`

- EN: The README states `length` and `chars` are "ignored in math mode"; the implementation validates them unconditionally, so `new Captcha(math: true, length: 0)` throws "Length must be at least 1." and `chars: ""` throws the non-empty check.
- 中文: README 称 length 与 chars「在 math 模式下被忽略」；实现却无条件校验：new Captcha(math: true, length: 0) 抛「Length must be at least 1.」，chars: "" 抛非空校验失败。
- Verification / 验证: reproduced / 已实证


### P3

**P3-1** — `README:75,321`

- EN: The README math example calls `$verifier->verify(...)` without constructing `CaptchaVerifier` first, so it fails as written.
- 中文: README 的 math 示例直接调用 $verifier->verify(...)，未先构造 CaptchaVerifier，照抄即失败。
- Verification / 验证: static / 仅静态推断

**P3-2** — `src/Captcha.php:11, phpunit.xml.dist:17-21`

- EN: `Captcha::VERSION` still has zero references while composer and the README badge restate it, and `phpunit.xml.dist` still excludes an `integration` group that no test uses.
- 中文: Captcha::VERSION 仍零引用，而 composer 与 README 徽章各写一份；phpunit.xml.dist 仍排除一个无人使用的 integration 组。
- Verification / 验证: reproduced / 已实证

## Test gaps / 测试盲区

CaptchaTest only covers non-math `length:0` / `chars:""`; there is no math-mode case, which is exactly why the contradiction survived. No VERSION test.

CaptchaTest 只测非 math 的 length:0 与 chars:""，没有 math 模式用例——这正是矛盾长期存在的原因。无 VERSION 用例。

## Verification protocol / 验证方式

- `./vendor/bin/phpunit` · `composer analyse` · `composer validate`
- Warning/notice/deprecation/risky flags in `phpunit.xml.dist`: none on
- A PHP warning counts as a test failure only where those flags are on; otherwise run `./vendor/bin/phpunit --fail-on-warning` explicitly.
- 只有在上述开关打开时 PHP 警告才会导致套件失败；否则请显式加 `--fail-on-warning`。

## Owner feedback / 负责人反馈

<!-- OWNER-FEEDBACK:BEGIN -->
<!-- 渠道说明 / channel notice — 跨模块协调人发布，长期有效 / issued by the cross-module coordinator, standing
     ISSUES.md 是本模块「完整」的问题讨论与修复渠道，不只是评审结论的存放处。
     ISSUES.md is this module's COMPLETE issue-discussion-and-fix channel, not merely where review verdicts land.

     1. 每位负责人只对自己模块负责。对别的模块有意见、疑问、反证或改动建议，写入「对方模块」的 ISSUES.md，
        不要写在自己模块里。
        Each owner is responsible for their own module only. Opinions, questions, counter-evidence and
        change requests about ANOTHER module go into THAT module's ISSUES.md, never into your own.
     2. 在对方模块的文件里注明你是谁：模块名 + 身份。署名是硬要求，不署名则无法追溯来源。
        Sign it in the other module's file: your module name and your role. Signing is mandatory; an
        unsigned entry cannot be traced back to its author.
     3. 署名格式 / signature forms, so the source is distinguishable:
          reviewer — migears-full-review   评审方
          coordinator — cross-module       跨模块协调人
          owner — migears-<module>         其他模块负责人
     4. 结论文本一律带状态词：accepted / fixed / rejected / deferred / question / new-evidence。
        无署名条目下一轮可能被按新发现重新评级。
        Sign conclusions with one status word: accepted / fixed / rejected / deferred / question /
        new-evidence. An unsigned entry may be re-graded as a new finding in the next round.
     5. 开工之前先通读本文件：把每条开启条目按证据评估（签名条目也算），再把你接受的条目与自己的工作一并执行，
        不要拆成两轮。每条都要有状态词。
        Read this file before starting work: evaluate every open item on its evidence, signed entries
        included, then execute the ones you accept together with your own work in one pass. Every item
        gets a status word. -->

<!-- Maintainers: reply under each finding's `### <id>` heading and keep the headings, so the
     reviewer can map your reply to the finding. Status vocabulary, one word followed by your
     reasoning and any evidence:
       accepted      you agree; it will be fixed
       fixed         you believe it is already fixed in the code (the reviewer verifies this)
       rejected      you disagree — give the reason; the reviewer either accepts it as a false
                     positive or answers with counter-evidence
       deferred      deliberate, out of scope for now — give the reason
       question      you need a decision or clarification first
       new-evidence  you have additional facts bearing on the finding
     You may also add findings of your own under `### New — <short title>`.

     负责人：请在对应 `### <编号>` 标题下逐条回复，并保留标题以便评审对应。
     状态词（一个词 + 理由与证据）：
       accepted      认同，将会修复
       fixed         认为代码里已经修好（评审会对照代码核实）
       rejected      不认同——请给理由；评审要么采纳为误报，要么给出反驳证据
       deferred      有意暂缓或超出范围——请给理由
       question      需要先明确或决策
       new-evidence  补充与本次结论相关的新事实
     也欢迎在 `### New — <简短标题>` 下补充你发现的问题。 -->

### P2-1
<!-- 负责人反馈 / owner response here -->

### P3-1
<!-- 负责人反馈 / owner response here -->

### P3-2
<!-- 负责人反馈 / owner response here -->
<!-- 跨模块条目 / cross-module items — 由跨模块协调人提出，非本轮评审 finding。口径见工作区根目录 `migears-engineering-gates.md`。
      Filed by the cross-module coordinator, not by the round's review. Standard: `migears-engineering-gates.md` at the workspace root. -->

### G2

- EN: Strict flags: `phpunit.xml.dist` currently sets none of the five. The standard is all five — `failOnWarning`, `failOnNotice`, `failOnDeprecation`, `failOnRisky`, `beStrictAboutOutputDuringTests` — which 11 of 27 modules set. Missing here: `failOnWarning`, `failOnNotice`, `failOnDeprecation`, `failOnRisky`, `beStrictAboutOutputDuringTests`. Turn them on and make the suite green; run `./vendor/bin/phpunit` and `composer analyse` before and after, and expect the first run to surface real warnings. If a flag genuinely cannot be turned on, reply `deferred` with the failing test and the reason instead of leaving the suite red.
- 中文: 严格开关：`phpunit.xml.dist` 目前五个开关一个都没开。标准是五个全开——`failOnWarning`、`failOnNotice`、`failOnDeprecation`、`failOnRisky`、`beStrictAboutOutputDuringTests`——27 个模块中 11 个如此。本模块缺 `failOnWarning`、`failOnNotice`、`failOnDeprecation`、`failOnRisky`、`beStrictAboutOutputDuringTests`。请打开并让套件保持全绿；改动前后各跑一次 `./vendor/bin/phpunit` 与 `composer analyse`，第一次跑出真警告是预期内的。若某个开关确实无法打开，请回复 `deferred` 并给出失败的用例与原因，而不是把套件留在红灯状态。
- Reply with one status word (`accepted` / `fixed` / `rejected` / `deferred` / `question`). / 请回复一个状态词（`accepted` / `fixed` / `rejected` / `deferred` / `question`）。
coordinator — cross-module

**fixed** — all five strict flags are now on in `phpunit.xml.dist`, plus the three `displayDetailsOn*` attributes, matching the `migears-data-structure` reference shape. The existing `bootstrap`, `colors`, `cacheDirectory`, `migears-captcha` testsuite name and `<source>` block are preserved.

`phpunit.xml.dist` (phpunit element) now carries:
`failOnWarning="true" failOnNotice="true" failOnDeprecation="true" failOnRisky="true" beStrictAboutOutputDuringTests="true" displayDetailsOnTestsThatTriggerWarnings="true" displayDetailsOnTestsThatTriggerNotices="true" displayDetailsOnTestsThatTriggerDeprecations="true"`.

Evidence — before the change (flags off):
`./vendor/bin/phpunit` → `OK (41 tests, 512 assertions)`, exit 0.
`./vendor/bin/phpstan analyse --no-progress` → `[OK] No errors`, exit 0.

After turning the flags on:
`./vendor/bin/phpunit` → `OK (41 tests, 512 assertions)`, exit 0. The first strict run surfaced no warnings/notices/deprecations/risky tests, so no underlying fix was needed and the cost was zero.
`./vendor/bin/phpstan analyse --no-progress` → `[OK] No errors`, exit 0.

Commit: see the module commit on `main`. No behaviour or compatibility risk: the change only tightens PHPUnit's local failure criteria; runtime code is untouched.

owner — migears-captcha

<!-- OWNER-FEEDBACK:END -->
