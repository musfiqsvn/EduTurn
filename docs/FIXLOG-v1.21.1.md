# EduTurn v1.21.1 — Crush-proof EVERY table (frontend + dashboard + print-safe)

Date: 2026-09-16. Ship: `eduturn.zip` (5,045,124 B, FLAT, ZIP-EQUALS-SOURCE).
Backup: `eduturn-1.21.0.zip`. DB: `wptest-db.sql.gz` (123 wp_posts). Debug: clean.

Recovery #7 first (seventh wipe: tree 129, php+mariadbd gone, datadir corrupt,
core partial, wp-content wiped). Same routine: apt update+reinstall, 8 files
from zip, fresh datadir + import 123, WP 7.1 overlay, DEPLOY-CLEAN, servers up.

## User report (`uploads/5.PNG`)

Still vertical text — pill cells + dark header = frontend **result card**
`.rc-table` (বিষয় column crushed, `.grade` pills). User: fix this pattern
**everywhere**. Full audit: all 40+ `<table>` in theme.

## Fix (pure CSS + 5 micro-markup edits; print untouched via `@media screen`)

`assets/css/dashboard.css` — 26 tables get `min-width` (forces the card/wrap
to scroll instead of crushing) + name/text column floors (`min-width`
130–150px, capped 200–220px so long names wrap per-word):
ক্লাস রুটিন, শিক্ষার্থী তালিকা, ফলাফল তালিকা, নোটিশ তালিকা, শ্রেণি তালিকা,
অ্যাসাইনমেন্ট তালিকা, অ্যাসাইনমেন্ট, আমার শ্রেণি, বিষয় তালিকা,
পর্যালোচনা সারি, প্রকাশিত ফলাফল (2 tables share the label — one safe rule),
আর্কাইভকৃত ফলাফল, ট্যাবুলেশন শিট, শ্রেণির শিক্ষার্থী, উপস্থিতি শিট,
উপস্থিতি তালিকা, শ্রেণি উন্নীতকরণ তালিকা, পরীক্ষা তালিকা, বিষয় সেটআপ,
কনটেন্ট তালিকা, তালিকা (verified unique), ফি বিবরণ + 3 newly labeled tables.
Small/fitting tables (2-col tallies, periods, grading, key-value receipts,
admit cards) deliberately untouched — forcing scroll there would be worse.

`assets/css/eduturn.css` — `.routine-table` min 620 + day/period cell floors,
exam বিষয় floor, `.rc-table` min 480 + nowrap headers + subject floor +
`.grade` nowrap, `.prose table` GitHub-style block-scroll (user-authored
post/page tables). Academic/admission/portal info-tables already fit — skipped.

Markup (php -l clean, all render-verified): `aria-label` added to SMS log
("পাঠানো বার্তার লগ"), weekly grid ("সাপ্তাহিক রুটিন গ্রিড"), role matrix
("ভূমিকা ও অনুমতি"); `utSubTbl` wrapped in `.table-wrap` (direct-in-main).

## QA (live :8080)

- Markers: `v1.21.1` served in both CSS; braces balanced.
- Rendered: sms label, grid label, roles label, utSubTbl wrap, ক্লাস রুটিন,
  শিক্ষার্থী তালিকা (sms seeded + cleaned; others live).
- result-review empty in QA ("কোনো ফলাফল নেই") — rule targets pre-existing
  label, applies when rows exist.
- Regression: 8 frontend + 6 dashboard views 200, debug clean.
- Version bumped (functions/style/README → 1.21.1).

Note: no headless browser in sandbox — verified via rendered-markup assertions
+ cascade reasoning. Rule: every data table now scrolls horizontally instead
of squeezing Bangla text vertical, on phone and desktop.
