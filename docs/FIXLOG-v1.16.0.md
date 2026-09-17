# FIXLOG — v1.16.0 (Production-readiness: workflow, IDs, statuses, print, QC)

Date: 2026-09-16. Env: 4th sandbox wipe → full rebuild (php8.4 + MariaDB 11.8.6 reinstall, WP 7.1 re-download, DB restore from v1.15 dump, theme deploy from zip).

## A. Permanent Student ID (STU-XXXXX)
- `uturn_ensure_student_code()` — sequential unique code per `ut_student`, mirrored to user meta; resumed after max existing; idempotent.
- One-time backfill on `init` (`uturn_code_backfill_v1`) + auto-ensure on entity save, entry-grid render, marks save.
- Entity list: ID + section columns; form shows permanent ID (read-only).
- Results snapshot `_ut_student_code` at marks-save + CSV import; public lookup accepts ID (`/^STU-\d{4,}$/`); card shows ID.
- E2E: STU-00007…STU-00011 assigned; codes unchanged through promotion; lookup by code → total 279/merit 1.

## B. Entry grid (section / search / scoping / % / scale JS)
- Section dropdown (roster-derived) + roll/name search (`q`) with aria-labels.
- `uturn_entry_scoped_subjects()`: subject-teachers (mapped, non-CT) get own columns only, draft-only (server-forced), other columns 🔒-disabled; scoped saves merge over stored marks (never wipe other teachers' cells).
- Live `%` column; grid JS grading driven by localized `uturn_grade_scale()` (was hardcoded).
- CSS: sticky roll+name columns, sticky thead, compact cells, `.ut-scoped-out` hatch, mobile tweaks.
- E2E: teacher2 (ইংরেজি-only) saved 5 drafts with ইংরেজি lines only; CT saw prefilled cells + submitted (255/5.00, 217/4.00, 261/5.00, 140/2.33 fail, 279/5.00).

## C. Configurable grading scale
- `uturn_grade_scale()` option-backed (default = BD board scale); `uturn_grade_for_marks()` scale-driven.
- New `grading` shell view (nav 📚 একাডেমিক, cap `uturn_manage_academic`): min/grade/point rows, validation, zero-floor F guarantee, board-default reset.
- E2E: custom 90-floor scale saved (85→F), reset → default (85→A+).

## D. 7-stage workflow + audit
- Stages: draft/submitted/review/correction/approved/published/archived (`_ut_stage`; pending post_status for queue stages).
- `uturn_result_set_stage()` allowlist transitions; `uturn_result_audit()` capped-50 log (time/user/action/detail).
- Handlers: `uturn_result_stage` (bulk review/correction/approved/submitted + note), `uturn_result_archive` (archive↔republish + merit recalc); publish/sendback set stage + audit. Shell POST allowlist extended (roles.php) — without it new actions bounced to bare dashboard (found + fixed in QA).
- Review UI: stage pills, per-row audit `<details>`, section/stage/search filters, stats incl. stage counts + published/archive tables with bulk archive/republish.
- E2E: submitted→review→correction(with note)→resubmit(submitted, total 217→223)→publish; archive hid public + shifted merits (261→1); republish restored (279→1).

## E. Student lifecycle statuses
- `active/promoted/retained/transferred/left/graduated/passed(legacy)/inactive`; roster = active+promoted+retained (`uturn_student_on_roster()`).
- `uturn_class_students()` roster fix (was active-only → promoted/retained now in attendance/admit).
- Publish+promote: pass→`promoted`, fail→`retained`, top-class→`graduated`; auto-promote same; manual ops: move/retain/transfer/graduate/reactivate/left.
- E2E: annual pub=5/promo=4 → 45/46/47/49 ৩য়+promoted+history, 48 retained in ২য়; ৩য় grid shows promoted, ২য় shows only 48; transfer→transferred→reactivate→active.

## F. CSV import hardening
- Computes `_ut_total` from subject lines (was missing → merit ties), snapshots code/section/stage=published, audit entry, student link kept.

## G. Print (A4)
- Take-attendance: printable sheet (✔/☐, summary, signatures) + print button.
- `fee-receipt` view (🧾 link in fees rows): letterhead receipt w/ code, amount, method, signatures.
- `student-sheet` view: picker + profile (photo, ID, status, history, published-results table) + print.

## H. Seeder/demo hygiene
- `uturn_seed_post()` status param; demo results seed as **draft**; re-seed never flips manual status; seeded students get codes.
- One-time demote migration for pre-v1.16 published demos (90/91/92 → draft; verified publicly invisible).

## I. QC sweep (Req 1–4, 20)
- Perf: home 88 queries / 0.067s / 136KB; CSS 110KB, JS 37KB (all `node --check` clean); JS deferred, fonts display=swap+preconnect, hero fetchpriority, lazy below fold, single h1 (hero).
- SEO: noindex 404 + empty archives/search (verified meta); canonical/OG/JSON-LD intact; singles have breadcrumbs + recent-posts internal linking.
- A11y: shell skip-link (+CSS) to `#utdMain`; bilingual back-to-top label; all icon-only nav/social controls labelled; visible focus rings; pill contrasts strong (green/red/blue/amber pairs).
- Agentic: 56/56 internal links 200; no lorem/TODO/placeholder UI copy; only legit empty-states + admin CSV sample mention "demo".
- debug.log: zero site entries (4 lines = own QA artifacts).

## J. Cleanup/ship
- QA results 132–142 deleted; 45/46/47/49→২য়+active+history cleared; map restored (বাংলা→4); mu-plugin probe removed; DB re-dumped (48,188 B).
- Version 1.16.0 (style.css, UTURN_VERSION, README + changelog); DEPLOY-CLEAN; zip rebuilt with `eduturn/` wrapper — 147 files, ZIP-EQUALS-SOURCE.
