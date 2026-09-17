# EduTurn v1.15.0 — Results 2.0 + A4 Print

Date: 2026-09-15. Ship: `/home/user/eduturn.zip` (147 files, ~4.8MB; backup `eduturn-1.14.0.zip`).

## 1. Public results page — demo removed
- `page-results.php`: "ডেমো দিয়ে দেখুন / Try a Demo" button + its JS deleted.
- `inc/results.php`: `demo=1` AJAX branch deleted (nonce + rate-limit kept).
- Verified: 0 demo references; pending/draft results return `{"ok":false}`.

## 2. Graphical results dashboard (new `inc/result-entry.php`, ~700 lines)
- **নম্বর এন্ট্রি** (shell view, teachers own-classes / SA+HM all): class × exam ×
  year picker → student × subject grid. Subjects from the class subject-map
  (fallback: global subjects). Live JS auto-calc per row (total, GPA, pass/fail)
  + class summary; server-side recalculation on save (BD board scale
  A+…F, GPA = mean of points, any F = fail).
- Incomplete submit rejected with roll numbers; drafts allow partial grids.
- Preloads existing records; teachers see published rows locked.
- CSV import KEPT for publishers, upgraded: auto student-link + merit recalc.

## 3. Publish workflow (draft → pending → publish, WP post_status)
- Teachers: new `edit/read_ut_result(s)` caps (`UTURN_ROLES_V` 1.7→1.8) —
  draft + submit only. Publish blocked at 3 layers: handler cap check,
  CRUD save downgrade, shell view registry.
- `ফলাফল পর্যালোচনা` (SA/HM): pending list w/ totals, per-row + bulk publish,
  send-back-to-draft, pass/GPA stats, SMS-on-publish option.
- Merit positions computed on publish (pass-only, total desc, ties share).
- Lookup + student portal + teacher view are publish-gated; portal history
  linked by student ID so promotion never breaks it.

## 4. Promotion engine (one stable student ID, class = meta)
- Annual publish optionally auto-promotes passed students (`promo` count);
  failed stay; top-class (১০ম) pass-outs → `passed` status.
- `শ্রেণি উন্নীতকরণ` view: auto-run for any exam+year (idempotent) + manual
  move + TC/left marking. Writes user meta + CPT meta + `ut_class` term +
  `_ut_class_history` together.
- `left`/`passed` students leave entry/attendance/admit rosters
  (`uturn_class_students` + entry queries filter) but keep old results.
- Entity student form: status select + class-history display.

## 5. A4 document printing (never the website)
- New `@page A4` print systems in `eduturn.css` (frontend) + `dashboard.css`
  (shell): chrome/forms/buttons hidden, letterhead `.print-head` shown,
  signature `.print-sign` rows, bordered tables, no page-break splits.
- Documents: result card, admission receipt, class + exam routines (exam
  panel gained its own print button), news release, admit cards (2-up),
  NEW tabulation sheet in the review view.

## QA evidence (live, teacher1/sa1/hm1/student1)
- E2E: draft (247/4.67 ✓, partial 115/3.25 ✓) → incomplete-submit rejected
  (roll listed) → submit → pending invisible publicly → SA publish (merits
  1,2) → public JSON has total+merit → teacher publish-hack `Unauthorized`
  → annual publish+promote (`pub=3&promo=2`: 2 moved w/ history, 1 failed
  stayed) → manual move + TC → left excluded from grid, old result visible.
- No leaks: student/teacher views exclude drafts (marker test).
- Regression: frontend 14/14, EN results, SEO (lang/desc/OG/FAQ/llms/robots),
  shell homes + student form, `(/n)`=0, debug.log zero site entries.
- QA data cleaned (results 126–131 deleted, classes/status restored).
