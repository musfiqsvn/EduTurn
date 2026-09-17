# FIXLOG — v1.20.0 (EduTurn brand refresh + mobile school name)

Date: 2026-09-16. Tree: `/home/user/eduturn` (137 files) · Ship: `eduturn.zip` (5,038,560 B, ZIP-EQUALS-SOURCE ✓) · DB: 123 posts, dump 49,381 B · debug.log: clean.

## What changed

1. **New official EduTurn logo** (`assets/images/eduturn-logo.png` replaced): user-supplied artwork, trimmed of transparent padding, resized to 1000px (195KB, retina-crisp at display sizes). Auto-appears everywhere the brand is referenced:
   - Dashboard sidebar top, above the school name (all roles — single shared shell).
   - Portal login cards (student + teacher), above the school name.
   - wp-login header.
2. **wp-login school name**: school name now renders under the EduTurn logo (was logo-only), single line with binary-search auto-fit (23px→12px) inside the fixed-width login box; name passed via `wp_json_encode` (quotes/Bangla verified safe). Login CSS h1 box resized to the new logo aspect (230×152).
3. **Mobile/tablet header fix**: the old rule hid the school name below 1100px. Now the brand stacks — logo mark on top, school name below in ONE line, font auto-shrinks to fit the fixed width (JS `fitBrandName` with responsive cap: 20px stacked / 40px desktop; CSS ellipsis as fallback). Header actions center on their own row.

## Verification (live E2E)

wp-login renders logo + name + tag (script + CSS confirmed in HTML); long name with quotes decodes exactly and stays single-line; portal logins show logo + name; sidebar shows logo-above-name; new stacked CSS served and old hide-rule gone; 4 frontend + 9 dashboard views all 200; DB untouched at 123 (test option reverted); one self-caught bug fixed before ship (PHP `+` vs `.` concatenation in the login injector — fatal in debug.log, fixed, log clean).

## Environment note

Second sandbox wipe this session (PHP, MariaDB server, WP core partial, theme dir, datadir). Recovered: packages reinstalled, 8 tree files restored from v1.19.0 zip, WP 7.1 core overlaid, DB re-imported from dump, servers restarted (:3306 + :8080).

## Unchanged (per standing rules)

Footer stays mark-only; drawer untouched; UTurn credit untouched; no data/schema changes.
