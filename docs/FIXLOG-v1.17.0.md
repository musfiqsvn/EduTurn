# FIXLOG — v1.17.0 (deeper production-hardening pass over v1.16.0)

Date: 2026-09-16 · Tree: `/home/user/eduturn/` · Ship: `eduturn.zip` (147 files, `eduturn/` wrapper, ZIP-EQUALS-SOURCE ✓) · DB: `wptest-db.sql.gz` (48,433 B)

## A. Performance
- **Hero LCP preload** — `uturn_preload_hero()` (`inc/assets.php`): front-page `<link rel="preload" as="image">` using the same `$bg` logic as hero.php. Verified in home HTML.
- **Query profiling** — mu-plugin SAVEQUERIES probe (`?qp=1`): home = 88 queries, top repeat only 7 term queries, all fast — **no N+1**. SITE_DATA transient-cached (14KB); no jQuery/dashicons on frontend; hero-bg absolute-filled (no CLS).
- **DB index** — `uturn_db_index_once()` (`inc/setup.php`): one-time `ut_meta_kv (meta_key(191), meta_value(191))` index on postmeta. Confirmed live in INFORMATION_SCHEMA.
- **Reduced motion** — `@media (prefers-reduced-motion)` kills ticker animation, orbs, transitions (4 hits in eduturn.css).
- **Images** — all 20 JPGs recompressed via GD q78 (widths already ≤1600px); ~300KB saved; hero 1376×768 @ 247KB renders visually clean.

## B. SEO
- **Sitemap 404 bug (REAL, found+fixed)** — sub-sitemaps served HTTP 404-with-valid-body on post-less sites (empty main query → core sets `is_404=1`). `uturn_seo_sitemap_no_404()` (`inc/seo.php`, `pre_handle_404`) returns 200 when the sitemap query var is set. Verified: index/posts/taxonomy sitemaps 200, fake URL still 404.
- **BreadcrumbList JSON-LD** (`inc/seo.php`, `wp_head`) — pages, archives, singles. Verified on /contact/, /notices/, notice single (হোম→নোটিশসমূহ→title).
- **h1 audit** — every rendered template has exactly 1; page-apply's two h1s are if/else-exclusive. Clean.
- **alt audit** — clean; admin media-preview `<img>` fixed with `alt=""` (dash.php JS).
- Event single already carries Event JSON-LD; School JSON-LD global (kept).

## C. Security / integrity
- **Class-scoped teachers** — new `uturn_scoped_classes()` (`inc/portal.php`); scoped my-students, take-attendance, results-view (`dash-school.php`), admit-cards (`dash-academic.php`); **server-side guard** in `uturn_attendance_save`. Verified live as teacher1 (CT ২য় only): dropdowns/rosters show ২য় only; SA sees all (১০ম/১ম/২য়/৩য়/৪র্থ/৬ষ্ঠ); hostile t1→৩য় save → 302 blocked + redirected.
- **IDOR audit** — my-results: none (linked-CPT + publish-only); admit-cards were roster-filtered but unscoped (fixed above); results-view free-text cls was publish-only (fixed via scoping anyway).
- **Dupe unification** — CSV import now reuses `uturn_find_result()` (`inc/results.php`), same rule as the grid.
- **Merit recalc** — on publish-state transitions (`inc/result-entry.php`).

## D. Accessibility
- `--muted` #54687E ≈ 5.7:1 on white — AA pass.
- **Labels** — aria-labels on all dashboard filter selects (class/exam/year/section/stage/target/ct/date), table aria-labels on ~30 dashboard tables, `<label>`-wrapped results-view selects kept, attAll/ut*All checkbox labels.
- **Notices** — `role="status"` on success/info/warning (33+2+4), `role="alert"` on errors (21) across 18 files.
- **Keyboard** — drawer focus management + Escape, focus trap, `:focus-within` dropdowns, ticker pauses on hover/focus. focus-visible global style kept.

## E. Agentic / AI-ready
- Descriptive buttons: 'খুলুন'→'এন্ট্রি গ্রিড খুলুন', 'দেখুন'→'ফলাফল দেখুন' (×2).
- Table captions via aria-label (see D); llms.txt enriched (result-lookup params, academics/students/gallery routes, freshness notes) — all linked routes verified 200 (dropped 3 dead slugs found during QA: fees/admit-card/calendar have no pages).
- False-alarm log: `class=\"…\"` in tool output was JSON escaping, not a source bug; verified rendered HTML clean.

## F. Cumulative GPA (new feature)
- `uturn_cumulative_gpa()` + `uturn_result_siblings()` (`inc/results.php`): same-student, same-year published exams (BN-digit aware via `uturn_norm_num`, raw-value sibling matching).
- Public AJAX lookup returns `cum{avg,n,fails,total}`; result card renders a dashed `cum-strip` when n≥2. Student portal my-results shows a সামগ্রিক গড় card.
- Verified live: (৪.৮৮+৪.৫০)/2 = **4.69**, then test post deleted and demo results restored to draft (90/91/92 draft ✓).

## G. Print CSS + mobile tables
- Dashboard print block reviewed: side/top/forms hidden, admit 2-col A4, fee/student docs letterheaded; frontend print block reviewed. Admit cards render outside the filter form (print-safe) ✓.
- Mobile: `.utd-card{overflow-x:auto}` already covers in-card tables; wrapped the 3 wrap-level tables (dash-crud ×2, entity-admin) in overflow divs; frontend info/routine tables verified already in `.table-wrap`.

## H. E2E (final, all live)
Home 200 + preload ✓ · sitemaps 200/200/200/fake-404 ✓ · BreadcrumbList ✓ · llms.txt ✓ · teacher scoping + hostile-block ✓ · 14/14 dashboard views 200 (sa1) ✓ · aria present ✓ · reduced-motion ✓ · php -l all clean ✓ · DEPLOY-CLEAN ✓ · ZIP-EQUALS-SOURCE ✓.

## QC notes
- No new write handlers or public forms → no new nonce surface; cumulative + siblings are read-only.
- Test artifacts removed: temp result 143 deleted; post 90 re-drafted; `/tmp` jars/html cleaned (jars in /tmp never ship).
- Known non-blockers: my-results cumulative card render path not fixture-tested live (helper covered by AJAX test); fees/admit-card/calendar public pages don't exist (llms.txt avoids them).
- Logins (unchanged): sa1 / teacher1 / teacher2 = qa123456.
