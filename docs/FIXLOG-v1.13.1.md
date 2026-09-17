# ✅ EduTurn v1.13.1 — Fix Log (2026-09-15)

Source: `/home/user/eduturn` · Ship: `/home/user/eduturn.zip` (145 files, 5.5MB) · Prev: `eduturn-1.13.0.zip`

## Report bugs (BUGHUNT-v1.13.md) — all fixed
| ID | Fix |
|----|-----|
| B-1 | Seeder menu location `primary` → `ut_primary` |
| B-2 | 8 deep-link anchors added (about/academic/admission/routine) |
| B-3 | `<main id="main">` — all 13 page templates + 4 archives + 4 singles + index/search/404/page |
| B-4 | About uses `established` setting (no hardcoded 2001) |
| B-5 | About management renders from `ut_board` CPT (lines = fallback only) |
| B-7 | Assignments nav links for teacher (`assignments`) + student (`my-assignments`) |
| B-8 | Role-escalation downgrade now shows warning banner (`role_warn=1`) |
| B-9 | Password change works for every role (was teacher/student only) |
| B-10 | Portal profile links → dashboard profile (no wp-admin bounce) |
| B-11 | New `uturn_manage_academic` cap (was `uturn_manage_sms`), roles v1.7.0 |
| B-12 | Explicit csv `$escape` args (PHP 8.4 deprecation gone — log-clean) |
| B-13 | Full BN/EN: every frontend template + 20 home sections + footer + JS digits/dates |
| Lows | `uturn_valid_phone()` unified (contact+apply+JS), favicon fallback, teacher-phone gate + setting UI, dynamic calendar year, pw fallback → dashboard |

## User demands
- **(/n) artifact**: root cause = literal `\n` bytes from dash remap (Fix1, od-verified); 150-view sweep → 0 occurrences.
- **Photo pipeline** (`inc/photos.php`): upload cap 2000px, on-demand size rebuild (fixed WP full-fallback trap via `is_intermediate`), small-image original fallback, `_ut_photo_id`→`_ut_photo` backfill + read-time self-heal, entity-sync mirror. Live-verified (teacher + student, incl. forced rebuild).
- **Multi-subject teachers** (NEW): `_ut_subject_ids` array + checkbox UI + primary mirrors; teacher list filter, subject usage guards, frontend cards/profile/directory, seeder demo (math+physics). E2E POST-verified.
- **Bonus finds fixed**: teachers page had 3 broken filters (missing `data-attr`) + BN-only staff select; JS slimmed 41KB→26KB (dead static-prototype removed, `node --check` clean).

## QA evidence (this session, post-restore)
- Frontend: 17 URLs + 404, all 200/404-correct, all `<main>`, 0 `(/n)`; EN mode verified (ut-en + strings).
- Dashboards: 6 roles × 25 views = 150/150, 0 `(/n)`; auth semantics content-checked (SA settings bounce, teacher users bounce, super form renders).
- E2E POSTs: entity multi-save `[5,1]` ✓, contact 880→017 normalize ✓, B-8 warn ✓, results AJAX (BN+ASCII digits, bad-nonce reject, no bulk leak) ✓.
- `debug.log`: zero entries after fixes (only pre-fix B-12 lines remain, historic).
- `php -l`: 88/88 ✓ · `node --check` ✓ · demo.json valid ✓ · source↔deploy diff clean ✓.

## Env notes (do NOT repeat mistakes)
- System MySQL 3306 serves wptest — never start 2nd MySQL.
- Wipes revert source/core/wp-content: restore core from `/tmp/wordpress-core-dl`, theme from source, uploads are expendable.
- Tool output doubles backslashes — build regex backslashes via `chr(92)`; verify with `od -c`/live tests.
- `wp_get_attachment_image_url()` returns FULL url for missing sizes — always check `image_src[3]`.
- Sandbox license shows `link_dead` (server unreachable); ERP views QA'd under DB-only grace extension (`grace_until`, test DB only — nothing ships).
