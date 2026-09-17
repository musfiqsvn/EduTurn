# EduTurn QA environment

## Rebuild

1. Fresh MariaDB datadir + database `wptest`, user `wp`/`wp` (full rights).
2. Import: `zcat wptest-db.sql.gz | mariadb wptest` (expect 123 `wp_posts` rows).
3. Fresh WordPress 7.1 core + this repo's theme at `wp-content/themes/eduturn`.
4. `wp-config.php`: DB `wptest`/`wp`/`wp` @ `127.0.0.1`, salts, `WP_DEBUG` on.
5. Serve: `php -S 0.0.0.0:8080 router.php` from the WP root
   (`router.php` = pretty-permalink router for `php -S`); MariaDB on `:3306`.

## Test logins (all password `qa123456`)

| User | Role |
|---|---|
| `superadmin` | Administrator (Super Admin — everything) |
| `sa1` | School Admin (operational work only) |
| `teacher1` | Teacher |
| `student1` | Student |

Dashboard: `/dashboard/`. Student directory: `/students-list/`.
