# FIXLOG — v1.18.0 (exam setup + full marks + print + branding)

Date: 2026-09-16 · Tree: `/home/user/eduturn/` · Ship: `eduturn.zip` (137 files, ZIP-EQUALS-SOURCE ✓) · DB: `wptest-db.sql.gz` (48,632 B, 123 posts)

## Context: environment rebuild
The sandbox was partially wiped mid-task (tree rolled back with 18 files missing/different, wptest wp-content + wp-includes gutted, PHP/MySQL gone). Recovered by: restoring the tree byte-exact from the v1.17.0 ship zip, reinstalling PHP 8.4.24 + MariaDB 11.8.6, fresh datadir + dump import (123 posts ✓), fresh WP core download for wp-includes/wp-admin. Full E2E re-run green before shipping.

## A. Exam subject setup + per-subject full marks (NEW)
- `uturn_exam_setup_get/save_rows` (`inc/result-entry.php`): option `uturn_exam_subjects` keyed `class|exam|norm-year` → rows of `{subject, full}`.
- Entry view: office (publish-cap) sees a 📋 setup card (prefilled from class map, editable rows + add/remove); teachers see an info notice when unset (fallback = class-map subjects @100).
- Grid: subjects from setup; headers show `পূর্ণ ১০০/৫০`; inputs carry per-subject `max` + `data-full`; **teacher scope auto-merges** so mapped subjects are always enterable (auto-added to shared draft rows via existing merge logic).
- `uturn_calc_result($marks, $fulls)`: grades on **percentage** (mark÷full), clamps to full; JS live calc rewritten full-aware (`%` = obtained ÷ entered-fulls).
- Save handler clamps per subject, stores 4-part lines `name|mark|grade|full` (readers stay back-compat: legacy 3-part = 100).
- New `uturn_exam_setup_save` handler: nonce + `publish_ut_results`/`manage_options`; **allowlisted** in `uturn_shell_post_actions()` (roles.php) — without this the admin gate 302s shell users to /dashboard/.
- Verified live: setup save → grid ১০০/৫০ → draft save (গণিত 90→A+, ইংরেজি 45/50→A+, GPA 5.00, total 135, 4-part lines ✓).

## B. Grading / merit
- Grading editor already existed + editable; relabelled to **শতকরা হার %** with percent semantics (works for 50/100 alike). No scale-format change.
- `uturn_merit_label()`: ১ম/২য়/৩য়/৪র্থ/৫ম… applied to review table, review tabulation, landscape print, student-sheet profile, public card (JS `ord()` with EN 1st/2nd/3rd).
- Verified: totals 135 vs 120 → merits ১ম/২য় across UI + AJAX.

## C. Displays of mark/full
- Public result card: `45/50` when full ≠ 100 (AJAX subs carry 4th element); student portal my-results same; tabulation headers (both orientations) show `পূর্ণ X` from setup, else stored 4th parts.

## D. Printing
- **Admit cards**: one student per A4 page (`break-after: page`, single column, roomier card). Screen grid unchanged.
- **Individual result**: 🖨️ button per my-results card isolates that card for print (opens details, body-class isolation, afterprint cleanup).
- **Class tabulation landscape**: standalone `?ut_print=tabulation&cls&exam&year` minimal page (`template_redirect`, `edit_ut_results` + teacher class-scope enforced, anon → die): `@page A4 landscape`, fixed colgroup widths, repeating thead, roll-sorted, full-mark headers, merit ordinals, signature row. Button added next to portrait print.

## E. Branding (user-supplied logo)
- `logo eduturn.png` (black bg) → `assets/images/eduturn-logo.png`: black→transparent, trimmed, 640px, 95KB.
- Dashboard shell: logo banner above school name (all roles, shared shell) + `.utd-ebrand` CSS.
- Login: portal cards show EduTurn logo above school logo/name; wp-login `h1 a` replaced via login.css; U-Turn credit untouched below.

## F. Already-present (verified, no build needed)
- Class add (＋ নতুন শ্রেণি) with super/SA/HM access via `uturn_manage_academic` ✓ · Subject add with Science/Humanities/Business groups ✓ + NEW explicit সাধারণ state (helper text + pill) for groupless subjects · Teacher marks already merge into shared drafts; SA/HM can edit published rows ✓.

## G. E2E (final, all live)
17/17 dashboard views 200 (sa1) · 5/5 public URLs · setup→grid→save→publish→merit flow · AJAX lookup (gpa/merit/subs) · landscape 200 + anon-die · teacher scoped grid · student my-results · admit render · php -l all clean · DEPLOY-CLEAN · ZIP-EQUALS-SOURCE.
Test artifacts removed (posts 144/145, setup option, student link); DB back to 123 posts.
Logins (unchanged): sa1 / teacher1 / teacher2 / student1 = qa123456.
