# EduTurn v1.20.2 — Vertical Bengali Text Fix (dashboard tables)

Date: 2026-09-16. Ship: `eduturn.zip` (5,038,742 B, FLAT, 137 files + dirs).
Backup: `eduturn-1.20.1.zip`. DB: `wptest-db.sql.gz` (123 wp_posts rows). Debug: clean.

## User report (+ `uploads/1.PNG`, `2.PNG`)

"likha gulo amon upor nich holo keno? table e pashapashi hbe likha" —
Bangla text rendered VERTICALLY (one character per line) in:
1. নম্বর এন্ট্রি marks grid (student names), 2. প্রোফাইল password-change form labels.

## Root cause

Dashboard CSS had NO `.table-wrap{overflow-x:auto}` rule (it existed only in
frontend `eduturn.css`). Dashboard tables are `width:100%`, so in a narrow
container (phone viewport, or the ~230–270px right column of `.utd-cols` at
tablet widths) the table never overflowed — it crushed inward instead:
- Marks grid (10+ columns, header "নাম" ≈40px floor): name cells squeezed to
  ~1 character wide → per-character vertical stacking.
- Password `table.form-table` (`th` 220px preferred): right column got ≈85px
  for the label → "বর্তমান পাসওয়ার্ড" broke per-character instead of per-word.
- The pre-existing `.utd-card{overflow-x:auto}` could not save `width:100%`
  tables: nothing overflows, so no scrollbar ever appears — columns just crush.

## Fix (dashboard CSS + 3 markup wraps, no data/schema changes)

`assets/css/dashboard.css` (appended):
- `.utd-wrap .table-wrap{overflow-x:auto}` — all 13 wrapped grids
  (marks, review, archive, published, exams, grading, attendance sheet/tally,
  receipts, class students, subject setup, profile…) now scroll instead of crush.
- `table.ut-marks{min-width:760px}` + name column
  `td:nth-child(2){min-width:140px;max-width:240px}` — names always horizontal
  (wrap at word spaces, never per-character).
- `table.utx-table{min-width:680px}` — subjects/groups 8-col table (same risk).
- `table.form-table th{min-width:120px}` — label floor; card scrolls instead.

`assets/css/dash-pro.css` (appended, loads last so it wins the cascade):
- `@media(max-width:1100px){.utd-cols{grid-template-columns:1fr}}` — profile /
  student-overview 2-col rows stack at tablet width; right-column forms get
  full width (crush zone 960–1100px eliminated).

Markup (pure `<div class="table-wrap">` wrappers + `</div>` closes, php -l clean):
- `inc/notify.php` — SMS/log 6-col table (was unwrapped).
- `inc/routines.php` — class-routine 7-col grid (was unwrapped).
- `inc/dash-school.php` — হাজিরা-নিন 4-col take grid (was unwrapped).
- Audited the rest: attendance-sheet, receipts, fees, periods, exam-routine,
  entity-admin (`fixed` + inline scroll), tabulation/admit (standalone print
  pages) — all already safe or covered by the new global rule.

## QA (live :8080, teacher1/sa1 qa123456)

- CSS markers: `v1.20.2` served in both dashboard.css + dash-pro.css.
- Views 200: profile, result-entry, sms, routines, take-attendance, attendance.
- Rendered-HTML proof: sms/routines/take-attention wraps = 1 each
  (sms seeded via `uturn_notify_log` option, take-att via `att_class=২য়`,
  seed deleted after — empty state restored); marks grid + profile form render.
- `php -l` clean (notify, routines, dash-school, functions); debug.log clean.
- ZIP-EQUALS-SOURCE (137 files; +10 dir entries); version bumped
  (functions.php, style.css, README.txt → 1.20.2).

## Note

No headless browser in sandbox, so no pixel screenshots — fix verified by
rendered-markup assertions + CSS-cascade reasoning (standard scroll-container
pattern; `width:100%` + `min-width` forces overflow → scroll, never crush).
On the user's phone/PC: grids scroll horizontally with full horizontal names;
profile cards stack full-width on tablet/phone with readable labels.
