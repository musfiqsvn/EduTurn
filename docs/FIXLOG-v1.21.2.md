# EduTurn v1.21.2 — Restore v1.21.0 filter CSS lost to env wipe

Date: 2026-09-17. Ship: `eduturn.zip` (5,045,414 B, FLAT, names + FULL
byte-content verified). Backup: `eduturn-1.21.1.zip`. DB unchanged
(`wptest-db.sql.gz`, 123 posts). No live QA (servers down, see below) —
change is a byte-verified restore of previously QA'd CSS.

Eighth wipe hit during the GitHub task (tree 129 incl. github/ dir gone,
php+mariadbd gone, datadir corrupt, wp-content wiped; servers NOT recovered
this turn — push-only task, QA env rebuilds from GitHub next session).

## Data loss found and fixed

`assets/css/eduturn.css` had silently lost the whole v1.21.0 block
(filter card, pagination links, `.empty-state .big`) — a wipe truncated /
reverted it without deleting, so file-count checks passed and v1.21.1 QA
(which only asserted the v1.21.1 marker) missed it. Shipped v1.21.1's
student directory therefore renders unstyled-but-working filters.

Fix: rebuilt `eduturn.css` = v1.21.0 file from `eduturn-1.21.0.zip` (has the
block, 2 `filter-card` lines) + intact v1.21.1 block from tree (braces
verified). Final: `filter-card` ×2, `v1.21.0` ×1, `v1.21.1` ×1, braces
balanced. All other 128 files byte-match the v1.21.0 zip (excluding the 9
intended v1.21.1 changes, each marker-verified intact).

## New ship rule (lesson)

`ZIP-EQUALS-SOURCE` must compare full byte content of every file, not just
names — done for this release (zero DIFFERS). Wipes can partially damage
files, not just delete them.
