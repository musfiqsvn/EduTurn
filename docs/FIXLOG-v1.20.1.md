# FIXLOG — v1.20.1 (wp-login order + injector fix)

Date: 2026-09-16. Tree: `/home/user/eduturn` (137 files) · Ship: `eduturn.zip` (5,038,547 B, ZIP-EQUALS-SOURCE ✓) · DB: 123 posts (untouched) · debug.log: clean.

## What changed

wp-login brand stack reordered per request: EduTurn logo → **"স্কুল ম্যানেজমেন্ট সিস্টেম"** → school name (single-line auto-fit) → form. The combined "EduTurn — স্কুল ম্যানেজমেন্ট সিস্টেম" line is gone; spacing adjusted (tag tight under logo, school keeps bottom margin).

## Real bug fixed (shipped in v1.20.0, caught now)

The v1.20.0 login injector was written with `\"` escapes inside a PHP single-quoted string, so browsers received literal backslashes → SyntaxError → school name AND tagline never rendered. Fixed by using plain quotes; the live-rendered script is now extracted and `node --check` validated, with tagline/school/order assertions. Also audited the other two inline injections (admission toast, setup add-row): both file- and render-valid. QA lesson: substring checks can't catch JS escaping bugs — rendered scripts must be extracted + parsed (and tool-output `\"` must be confirmed with `od`, not eyeballed).

## Verification

Rendered injector VALID with correct order/text; home/login/portal/dash all 200; DB 123; another sandbox wipe recovered first (tree + core + DB + servers rebuilt, v1.20.0 code intact).
