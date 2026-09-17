# FIXLOG — v1.18.1 (full recheck + hardening patch)

Date: 2026-09-16 · Tree: `/home/user/eduturn/` · Ship: `eduturn.zip` (137 files, ZIP-EQUALS-SOURCE ✓) · DB: `wptest-db.sql.gz` (48,819 B, 123 posts)

## Recheck scope ("recheck everything")
- Environment had been wiped again (packages, DB datadir, wp-includes, theme dir, 8 tree files). Fully rebuilt: PHP 8.4.24 + MariaDB 11.8.6, fresh datadir + dump import, fresh WP core, tree restored byte-exact from the v1.18.0 ship zip.
- Ran ~70 page loads (21 public URLs incl. 404 + sitemap/llms, 44 dashboard views as SA, 13 as teacher, 8 as student) plus full mutation flows. **debug.log stayed 0 bytes throughout** — no warnings/notices/fatals anywhere.
- Unauthorized views (teacher→classes/grading, student→result-entry) fall back to home content — no data leak ✓.

## Bugs found (all fixed + live-verified)
1. **`|` in subject names corrupts result parsing (REAL).** Subject names flow into pipe-delimited `_ut_subjects` lines (`name|mark|grade|full`); a `|` in a name would shift mark/grade/full columns. Fixed by stripping `|` in exam-setup save, class-map save, and global subject/group save.
2. **Duplicate subjects in setup/class map (REAL).** Repeated names created twin grid columns whose marks overwrote each other on save (`$marks[$name]`). Fixed: dedupe on save (keep first).
3. **Student portal showed plain marks for 50-mark subjects (consistency).** `page-student-portal.php` now renders `45/50` like the dashboard, public card and my-results.
4. Non-bug note: my own hostile-attendance retest initially used the wrong action name (`uturn_attendance_save` instead of `uturn_attendance`); redone correctly — cross-class save blocked with `form-error`, legit save stored (45,46) ✓. The v1.17 guard itself was always in the right handler.

## Flows re-verified live (then cleaned up)
Setup save (incl. dupe+pipe attack → stored clean) → grid full-marks → marks save (90→A+, 45/50→A+, GPA 5.00) → publish → merits ১ম/২য় → landscape print (fixed layout, ordinals, full headers) → AJAX lookup (4-part subs, merit, cum avg 4.75 on 2-exam fixture) → CSV sample+import roundtrip (linked sid=45, total=135) → grading edit (75/A+) + reset-to-default → class create (pipe/dedupe) + delete → attendance hostile+legit. All test artifacts removed; DB back to 123 posts, results 90/91/92 draft, zero attendance rows.
Logins (unchanged): sa1 / teacher1 / teacher2 / student1 = qa123456.
