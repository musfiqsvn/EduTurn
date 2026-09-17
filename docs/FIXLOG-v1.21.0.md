# EduTurn v1.21.0 — Class-first Student Directory + Structured Row Editors

Date: 2026-09-16. Ship: `eduturn.zip` (5,044,423 B, FLAT, ZIP-EQUALS-SOURCE).
Backup: `eduturn-1.20.2.zip`. DB: `wptest-db.sql.gz` (123 wp_posts rows). Debug: clean.

Recovery #5 happened first (fifth env wipe: tree 129, php+mariadbd gone,
datadir corrupt — ibdata1 missing but undo logs present → InnoDB refused start,
wp-content + core partial). Recovered: 8 flat-zip files → 137, apt update +
reinstall (needed `apt-get update`: php8.4-cli 404 on stale lists),
`mariadb-install-db` (first attempt silently made no mysql/ tables — reran
verbose, 88 tables), fresh import 123, WP 7.1 overlay, DEPLOY-CLEAN,
`:3306` + `:8080` up. Note: `/usr/sbin` not in PATH (`which mariadbd` misses
it); mariadbd needs `--pid-file=/tmp/mysql-wp.pid` (no /run/mysqld access).

## A. Frontend Student Directory — class-first (`page-students-list.php` + CSS)

Old: all students (cap 300, "সকল শ্রেণি" default) rendered, JS-only filter.
New (server-side, works without JS, shareable URLs):
- No `cls` → filter card + "🎒 শ্রেণি নির্বাচন করুন" prompt, ZERO cards.
- Class dropdown lists only classes having students (taxonomy ladder order +
  orphan meta values); invalid `cls` resets to prompt.
- After class: Section dropdown (that class's sections), Group dropdown (only
  if groups exist), name/roll Search (roll matches Bangla AND English digits
  via `uturn_bn`/`uturn_bn_to_en` variants), 24/page Pagination preserving all
  filters, bilingual count ("৫ জন শিক্ষার্থী · শ্রেণি ১ম · পৃষ্ঠা ১/২").
- Same card markup/photo/roll display; new `.filter-card`/`.pagination a`/
  `.empty-state .big` CSS, responsive (fields stack ≤720px).

## B. Structured row editors (screenshots 3+4 + system-wide same pattern)

New shared component `inc/helpers.php`: `uturn_rows_columns()` presets,
`uturn_row_editor()` (labeled table, per-field text/number/select/textarea
cells, add/remove rows, self-contained CSS+JS printed once per page),
`uturn_rows_to_text()` (drops all-empty rows + trailing empties, strips `|`
from cells). STORAGE FORMAT UNCHANGED everywhere — every reader/display works
byte-for-byte; prose/code textareas (history, messages, map, CSS, SMS, JSON…)
deliberately untouched, as are CSV import and ID lists.
- Exam routine (`routines.php`): grid তারিখ|বার(select incl. short forms
  বৃহ/রবি… + custom preservation)|বিষয়|সময়; save accepts `exam_grid`,
  textarea-parse fallback kept. Frontend `/routine/` unchanged.
- Result meta (`meta.php` new `rows` type): `_ut_subjects` →
  বিষয়|নম্বর(number)|গ্রেড(select A+…F)|পূর্ণমান(optional — readers already
  support it, display shows /full); album `_ut_photos` → single-col rows.
- Site settings (`options.php`): 15 pipe-keys converted
  (hero_meta, stats, management, seats, fees, adm_dates, calendar, goals,
  programs, exams, process, docs, features, clubs, conduct); save implodes
  arrays, textarea path intact for the rest.
- Homepage editor (`editor.php`): hero_meta + stats_lines → rows (same stored
  format, both editors interoperate).

## QA (live :8080; temp data created then byte-restored)

- Directory: prompt/0-cards, ১ম→৫ cards + count, invalid→prompt, sec=ক→3,
  grp hidden (no groups in QA), name/roll-bn/roll-en search →1 each,
  miss→empty-state; pagination via 25 temp students (24+6, পৃষ্ঠা ১/২, active
  marker, search-scoped 25→2 pages); temps deleted (16 students, 123 posts).
- Routine: view 200 + grid rendered + old textarea gone; real admin-post save
  proved pipe-strip (`পাইপ|টেস্ট`→`পাইপটেস্ট`), empty-row drop, pad-to-4;
  option restored byte-identical (8 rows, frontend table shows 8).
- Helpers eval: trailing/interior empties, pipe strip, single-col, render
  counts (3 tr / 2 selects / 4 numbers), JS-once ✓.
- Meta eval: `uturn_save_meta` round-trip exact; post-90 meta restored exact.
  (Test bug, not code: nonce must be created AFTER `wp_set_current_user`.)
- Options eval: 2 tables render, sanitize implodes rows, prose pipes kept.
- Editor eval: 2 rows tables (hero_meta grid confirmed).
- Regression: 9 frontend pages 200, 5 dashboard views 200, debug clean.
- New lesson: parallel `edit_file` calls to the SAME file race and corrupt —
  use one atomic python edit per file instead (meta.php tail was clobbered
  and repaired this way).
