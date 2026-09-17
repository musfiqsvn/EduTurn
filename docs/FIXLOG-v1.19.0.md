# FIXLOG — v1.19.0 (Configurable Academics — spec gap-fill)

Date: 2026-09-16. Tree: `/home/user/eduturn` (137 files) · Ship: `eduturn.zip` (4,936,236 B, ZIP-EQUALS-SOURCE ✓) · DB: 123 posts, dump 46,162 B · debug.log: 0 bytes (never created during whole QA loop).

## What shipped (6 spec gaps vs v1.18.1)

1. **Configurable exams** — Academic → পরীক্ষাসমূহ: add / activate-deactivate / delete (blocked with `exam-used` while results exist). Deactivation never breaks old data: public lookup, review, print, promotion and CSV import validate against ALL exams; only new-entry dropdowns are active-only. Legacy slugs (`half-yearly/annual/first-term/test`) preserved.
2. **Class code / order / archive** — code + display-order fields; classes with students/results **archive** instead of deleting (soft `ut_archived` term meta), restore anytime; archived classes vanish from every dropdown. Empty classes still hard-delete.
3. **Subject full / pass / order** — master rows gain default full marks, pass marks, display order (sorted lists); exam-setup prefill pulls full marks from the master (verified: গণিত=50 while others 100).
4. **Student extra subjects** — per-student checkbox set on the student form (e.g. 4th subject); entry grid shows union ✳ columns, N/A cells disabled, submit-completeness enforced per student's applicable set (positive + negative cases verified).
5. **Rank-by total | GPA** — grading-page setting; merit recalc honors it (verified both modes flip 90/91 correctly). Bonus hardening: BN-digit totals/GPAs normalized before float-cast (legacy rows ranked 0 before).
6. **Individual-result GP column + photo** — public lookup card and student portal subject tables show grade points; student photo renders in both (AJAX `photo` key + portal header). `uturn_grade_point()` derives points from the live scale.

Also: CSV-import help text now lists live exam slugs (was hardcoded).

## Verification (live E2E, all green)

Exams add/toggle/delete-guard + hostile delete of used exam → `exam-used`; retired-exam public lookup still `ok:true` with GP data; class create-with-code → archive → dropdown exclusion → restore; subject save (full/pass/ord + group preservation) → prefill; rank-by both modes; portal GP+photo with real upload; extras union grid (1 enabled / 4 disabled cells) → per-student stored lines → submit completeness + incomplete rejection; CSV import round-trip; 7 dashboard views 200; DB restored to 123, all test posts/options reverted.

## Environment note

Sandbox was wiped mid-task (PHP, MariaDB server, entire `wptest/` install gone; 8 theme files missing from tree). Recovered: tree restored to 137 from v1.18.1 zip (v1.19.0 edits intact), WP 7.1 core re-downloaded (db_version 61833 exact match — no DB upgrade), DB re-imported from dump, servers restarted (MariaDB :3306, PHP :8080, pretty-permalink router recreated).

## Deferred (with rationale, unchanged)

Grading max-field (min-implied equivalent exists), absent markers, incomplete pill, multi-school isolation (single-school-per-install architecture). Exam rename (display-name edit) noted as future polish — add/toggle/delete covers the lifecycle.
