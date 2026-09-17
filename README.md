# EduTurn — School Management System with ERP

Feature-full, all-in-one School Management System with built-in School ERP for
schools & colleges. Complete SMS (staff/teacher/student governance, attendance,
routines, results, fees) plus ERP dashboards with role-based portals, admissions
pipeline, modular drag-and-drop homepage, and bilingual (Bengali/English)
frontend. Zero dependencies — no plugins required.

- **Current version:** 1.21.2 (`UTURN_VERSION` in `functions.php`)
- **Requires:** WordPress 6.0+, PHP 7.4+
- **License:** Proprietary — UTurn Digital Solutions

> Keep this repository **private**. It contains a QA database dump and test
> credentials (see `qa/`).

## Repo layout

| Path | What |
|---|---|
| `/` (theme root) | EduTurn theme source — `style.css`, `functions.php`, `inc/`, `assets/`, `page-*.php`, `template-parts/`, `README.txt` |
| `docs/FIXLOG-*.md` | Per-version ship logs (what changed, why, QA proof) |
| `qa/wptest-db.sql.gz` | QA database dump matching this source |
| `qa/README.md` | How to rebuild the QA site + test logins |

## Build the install zip (flat, like releases)

```bash
cd eduturn   # this repo root
zip -qr ../eduturn.zip . -x ".*"
```

Upload `eduturn.zip` via Appearance → Themes. Activation auto-seeds pages,
menus, taxonomies (see `README.txt` §1).

## Working from this repo (agent notes)

- Source of truth = this repo root (theme files). Never commit `eduturn.zip`,
  `node_modules`, or live `wp-content/uploads`.
- QA: import `qa/wptest-db.sql.gz`, deploy theme to `wp-content/themes/eduturn`,
  serve with `php -S 0.0.0.0:8080 router.php` + MariaDB on `:3306`
  (details + logins in `qa/README.md`).
- After any fix: bump `UTURN_VERSION` + `style.css` + `README.txt`, re-zip,
  re-dump DB, write `docs/FIXLOG-vX.Y.Z.md`.
- Ship rule: verify zip vs source by FULL byte content, not just file names.
