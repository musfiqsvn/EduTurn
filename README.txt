=== EduTurn ===
Contributors: UTurn Digital Solutions (https://uturndigital.com.bd)
Social: https://www.facebook.com/uturndigitalsolutions
Version: 1.21.2 | Requires WP 6.0+ | Requires PHP 7.4+ | License: Proprietary

EduTurn is a feature-full, all-in-one School Management System with built-in
School ERP for schools & colleges — complete SMS (staff/teacher/student
governance, attendance, routines, results, fees) plus powerful ERP dashboards
with role-based portals, admissions pipeline, modular drag-and-drop homepage,
and 1:1 pixel-faithful Bengali/English frontend. Zero dependencies — no
plugins required.

----------------------------------------------------------------
1. INSTALLATION (PLUG-AND-PLAY)
----------------------------------------------------------------
1. Zip the `eduturn` folder (or upload via SFTP to wp-content/themes/).
2. Appearance > Themes > Activate "EduTurn".
3. On activation, `after_switch_theme` auto-seeds (no XML importer):
   - 14 pages (Home set as front page), Primary + 2 footer menus
   - 8 hierarchical taxonomies with Bengali terms + class ladder
   - 9 demo users (1 staff, 3 teachers, 5 students; one-time creds notice)
   - Notices, events, news, 12 teachers, 9 staff, 16+ students, star
     students, testimonials, FAQs, downloads, achievements, albums
   - Full dashboard defaults (brand, palette, sections, contact)
4. Re-run any time: EduTurn > Advanced > "Demo re-seed" (idempotent —
   existing slugs are never duplicated or clobbered).

----------------------------------------------------------------
2. ARCHITECTURE (MVC-INSPIRED)
----------------------------------------------------------------
functions.php          Bootstrap: constants + ordered module loader.
inc/helpers.php        Options accessor, Bengali digits/dates, color math,
                       server-side SVG icon set (1:1 with static IC),
                       photo/initials, section registry, lang + URL helpers.
inc/setup.php          Theme supports, image sizes, menu locations,
                       white-labeling (admin footer, login screen),
                       dynamic School JSON-LD + theme-color.
inc/walker.php         Bespoke menu walkers emitting the static DOM verbatim
                       (.nav-item/.nav-link/.dropdown, .mnav/.mnav-btn/.sub)
                       + pre-seed fallback menu mirroring navModel.
inc/assets.php         Font pipeline (dashboard-selected), 1:1 stylesheet,
                       dynamic :root palette, SITE_DATA bridge (replaces
                       data.js), geo tracker, admin tooling.
inc/cpt.php            12 bespoke CPTs + 8 taxonomies, custom capability
                       types with map_meta_cap for RBAC bounding.
inc/meta.php           Declarative native meta-box engine (text/date/time/
                       select/textarea/checkbox/color/media/user-link).
inc/roles.php          uturn_staff / uturn_teacher / uturn_student roles,
                       god-mode administrator caps, admin-bar + wp-admin
                       gates, role-aware login redirects.
inc/options.php        Centralized Settings-API dashboard (6 tabs): brand,
                       palette, typography, homepage sorter, contact,
                       advanced (geo toggle, custom CSS, re-seed).
inc/entity-admin.php   Module 2: bespoke SMS list/add/edit screens per role
                       with auto-synced directory CPT profiles.
inc/forms.php          Contact inbox controller (validation mirrors the
                       static regexes) + toast feedback on redirect.
inc/seeder.php         Zero-dependency demo seeder (see §5).
assets/seed/demo.json  Content dataset exported from the static source.

----------------------------------------------------------------
3. 1:1 FIDELITY PROTOCOL (HOW PHP WAS INJECTED WITHOUT BREAKAGE)
----------------------------------------------------------------
RULE 1 — SERVER-FIRST, SAME DOM: every static mount point
  (#topbar-root, #header-root, .site-header, .notice-ticker, #drawer,
  #footer-root, #mobile-cta, #back-top, #stats-grid, #notice-list …)
  is printed by PHP with IDENTICAL classes/IDs. No class was renamed,
  no grid altered; only additive rules (geo chip) were appended.
RULE 2 — JS COMPAT SHIM (all marked `UTURN-WP` in assets/js/eduturn.js):
  a) <body data-wp="1"> triggers bindWpChrome(): interactions are bound
     to PHP markup; every innerHTML overwrite is skipped. Nothing is
     ever clobbered — drawer, search, ticker-speed, brand auto-fit,
     tabs, carousels, accordions, lightbox, counters all work unchanged.
  b) D() reads window.SITE_DATA, injected by wp_add_inline_script from
     LIVE WP queries (notices/events/news/teachers/downloads/brand/
     admissionInfo with absolute `url`s). searchIndex() prefers `url`.
  c) Language toggle persists localStorage + `uturn_lang` cookie and
     reloads; PHP renders chrome labels per cookie (static parity).
  d) Per-section inline scripts REPLACE the old AB_PAGE fillers 1:1
     (same IDs, same filter/pager/validation behavior, zero AJAX).
RULE 3 — STYLESHEET VERBATIM: assets/css/eduturn.css is the static
  css/style.css copied byte-for-byte; the dashboard palette is layered
  on top as a :root override via wp_add_inline_style (primary 600/700/
  900/soft + accent shades auto-derived by color math).

----------------------------------------------------------------
4. STATIC → WORDPRESS ROUTE MAP
----------------------------------------------------------------
index.html            → front-page.php (19 sortable sections)
notices.html          → archive-ut_notice.php (+ single-ut_notice.php)
teachers.html         → page-teachers.php (tabs + live filters)
students-list.html    → page-students-list.php
student/ teacher/     → page-{role}-portal.php (gated, Phase-1 boards)
contact form          → admin-post uturn_contact → ut_message inbox
apply.html wizard     → Phase 2 (CPT ut_application + uploads + admin queue)
results/routine/      → Phase 2 (results engine, routine builder, downloads,
academic/downloads/     gallery/event/news archives + singles, about page)
  gallery/events/news

----------------------------------------------------------------
5. MODULE STATUS
----------------------------------------------------------------
Module 1 (Layout engine, palette, typography, geo topbar, section
  sorter, institutional settings): COMPLETE.
Module 2 (RBAC roles+caps, bespoke SMS CRUD screens with CPT sync,
  portals Phase-1): COMPLETE. Phase-2 dashboards (attendance, fees,
  results entry, messaging) plug into the same role/cap foundation.

----------------------------------------------------------------
© UTurn Digital Solutions — https://uturndigital.com.bd

----------------------------------------------------------------
BACKEND-COMPLETE UPDATE (v1.1.0+) — 100% DASHBOARD-EDITABLE
----------------------------------------------------------------
* New "পেজ কনটেন্ট" dashboard tab: every inner-page text zone is now
  an option — about history/vision/mission/goals, academic programs/
  curriculum/evaluation/exam table, admission process/docs, students
  features/clubs/conduct. Templates render 100% from options/CPTs;
  no hardcoded page prose remains.
* Homepage section order UI (হোমপেজ tab, bottom): show/hide each of
  the 19 sections + numeric ordering. Stored as sections_order and
  honored by front-page.php via uturn_home_sections().
* Static preview (/home/user/alokito/admin/) upgraded to a working
  demo CRUD dashboard (localStorage-backed): add/edit/remove for
  notices, news, events, teachers, students, results, downloads,
  testimonials; approve/reject/remove for applications; working
  settings save; one-click demo reset. For LOOK-and-TRY only — the
  real backend is this EduTurn system.

----------------------------------------------------------------
v1.2.0 — SUPER ADMIN vs SCHOOL ADMIN
----------------------------------------------------------------
* New role "স্কুল অ্যাডমিন (School Admin)": full add/edit/remove over
  every content CPT (students, teachers, staff, notices, news, events,
  albums, downloads, FAQs, testimonials, achievements, messages,
  applications, results, attendance, fees) + Routine Builder + uploads.
  NO access to: EduTurn settings hub, user accounts, SMS settings,
  demo seeder, themes/plugins/system. WordPress hides those menus
  automatically (no manage_options / user / theme caps).
* Super Admin = administrator: everything, unchanged + new
  uturn_manage_routines cap. Roles re-sync automatically via
  UTURN_ROLES_V bump (1.2.0).
* Demo login (after seeder): schooladmin / school123 (School Admin),
  office / office123 (Staff). Seeder never resets existing passwords.
* Static preview /admin/ mirrors it: role switcher (super/school) in
  the topbar; school view hides Settings, Classes structure and the
  demo reset button.

== v1.2.1 (Deep-audit bugfix) ==
- Dashboard schema restored to the full frontend contract: school_name_bn/en,
  tagline, established, phone_href, hours/hours_short, footer_about, hero_bg_id,
  hero_eyebrow/lead/meta, stats_lines, board_strip, ticker_count, geo_enabled,
  color_primary/secondary/accent, font_bn/en (21 keys) — header brand, hero
  lead/meta, stats band, footer about/hours, social links now render + are
  dashboard-editable. Removed 11 dead keys.
- Sanitizer: SMS/geo checkboxes now tab-gated (saving one tab no longer wipes
  other tabs' flags); colors hex-validated; fonts whitelisted.
- Lightbox: absolute image URLs no longer get the root prefix (fixes broken
  viewer images on nested pages). Language toggle no longer stacks duplicate
  mobile drawers / Escape handlers.
- Layout: right-edge dropdowns open right-aligned (no viewport spill);
  download rows stack cleanly on small phones. mini RTL-safe guards kept.
- Fixed 3 corrupted Bengali strings in dashboard defaults/labels.

== v1.2.2 (Rendered-QC bugfix) ==
- Layout: minmax(0,1fr) grid tracks + min-width:0/overflow-wrap guards across
  all 30 grids (both breakpoints mirrored) — fixes desktop two-col table
  spill, contact email/flex blowout, footer contact spill on phones.
- Countdown NaN fixed (data deadline digit format + JS digit-normalizing
  hardening); mobile countdown cells now shrink (flex:1, min-width:0).
- Events archive: removed viewport-breaking inline 3-col style -> .cols-3.
- Auto-fit text engine: [data-autofit] shrinks labels into fixed padded
  width (dashboard titles, user chips, sidebar brand call AB.fitAll()).
- Class-pick radios constrained to 1px (were spilling past viewport).
- Static portals: student/teacher password-change views (localStorage demo,
  current-check + min-4 + confirm); logins verify stored password with live
  hint; avatar initials; teacher "সময়" label unified.

== v1.3.0 (Rebrand to EduTurn) ==
- Renamed theme UTurn Edu -> EduTurn: theme header, text domain (eduturn),
  asset bundle (eduturn.css/js), admin menu slugs, dashboard labels, README.
- Description rewritten: full SMS + School ERP positioning.
- Internals unchanged: UTURN_* constants, uturn_edu_options key, ut_* post
  types, capabilities and function names kept for backward compatibility.

== v1.3.1 (Deep-QC hotfix) ==
- CRITICAL: fixed PHP parse error in inc/meta.php (unescaped {$ inside
  double-quoted JS string broke the whole file) — all meta boxes work again.
- Fixed missing eduturn-geo.js (rebrand left the file under its old name).
- Verified: php -l clean on all files, all hooked callbacks defined, all
  enqueued assets + home sections + CPT templates present, nonces on all
  public form handlers.

----------------------------------------------------------------
9. CHANGELOG
----------------------------------------------------------------
= 1.4.0 =
* Built-in licensing client (inc/license-client.php): email + license-key
  activation against the UTurn License Manager server, signed periodic
  verification with 7-day offline grace, no data deletion on expiry.
* Expiry/disabled states lock ERP (students/teachers/attendance/routine/
  exams/accounts/SMS/reports/admin + student/teacher portals) with a branded
  renewal notice; public website stays ON. Renewal restores instantly.
* EduTurn > License admin page: activate/deactivate, status, renewal, SSO
  login support, expiry reminders (30/15/7/3/1/0 days).
= 1.4.1 =
* FIXED: activation failed with "License server signature invalid." — the
  client verified the wrong payload. Response verification now matches the
  server exactly (proven by true sign→JSON→verify roundtrip tests).
* License server URL is now editable on EduTurn > License (for staging).
* Automatic ?rest_route= fallback when central runs plain permalinks.
= 1.4.2 =
* FIXED "Sorry, you are not allowed to access this page." on the License
  page: License is now a top-level admin menu (same URL), with zero
  dependence on the parent menu chain.
* Lock screen is now role-aware: non-admins see "ask your administrator"
  instead of a link they cannot open.
= 1.5.0 =
* FIXED 404 on every inner page: seeder now creates all 13 essential pages
  (about/academic/admission/apply/results/routine/downloads/contact/
  students/students-list/portals/home), sets the front page, site name,
  tagline, Asia/Dhaka timezone, and flushes rewrite rules. Auto-runs on
  theme activation + self-heals missing pages on admin visits.
* NEW /teachers/ archive template (was falling back to blog layout).
* Permalink health monitor: warns when mod_rewrite is missing, with
  one-click rule flush + emergency plain-link fallback.
* School Admin can now add/edit teachers & students (uturn_manage_sms).
* New teacher/student accounts default to password "eduturn"; login
  credentials shown once after creation; per-row password reset.
* Teachers & students can change their own password inside their portal.
* Login page redesigned (branded gradient + portals quick links).
* Speed: deferred JS, font preconnect, emoji/oEmbed/migrate trimmed.
= 1.6.0 =
* NEW command-center dashboard (EduTurn menu): stat cards, grouped module
  grid, pending-admission actions, license + system widgets — fully
  role-aware for Super Admin / School Admin / Staff.
* Menu overhaul: parent visible to all staff (per-item caps), all content
  CPTs grouped under EduTurn in logical order, directory CPTs hidden
  (managed via SMS screens), settings moved to its own submenu.
* School Admin + Staff now land directly on the EduTurn dashboard.
= 1.6.1 =
* License client now honors the server's check interval
  (check_hours) instead of a hardcoded 6 hours.
= 1.7.0 =
* NEW roles: Headmaster (above School Admin: all operations + site
  settings/homepage + staff accounts, guarded from Super Admin
  accounts) and Accountant (fee records + read-only directories).
* Mobile-number login for everyone: normalized phone matching
  (01.../8801...), unique-number enforcement, profile + Add-User
  phone fields, portal + wp-login labels updated.
* No more duplicate paths: WP Dashboard menu hidden + index.php
  redirects to the graphical EduTurn dashboard for all custom roles;
  direct add/edit URLs of directory CPTs reroute to SMS screens.
* NEW EduTurn submenu: direct Homepage control shortcut; settings
  save moved to admin-post so Headmaster can save without
  manage_options. Dashboard: Bengali role labels, monthly collection
  card, license status visible to Headmaster.
= 1.7.1 =
* FIX: homepage section visibility/order silently failed to save
  (double-sanitize trap in the settings saver) — now saves correctly.
* FIX: portal_gate notice + get_page_by_title deprecations removed.
* Mail hardening: all outbound mail via eduturn_safe_mail()
  (catches mail-transport fatals); admin warning when PHP mail()
  is disabled (SMTP plugin needed).
* Homepage tab: section show/hide block moved to the top.
* Seeder: accepts both demo.json key styles (cls/desig/img/dateBn AND
  long forms) — seeded teachers/students/downloads now carry real
  designation/class/title values; all 249 seed warnings eliminated.
= 1.8.0 =
* NEW notification engine (SMS + WhatsApp): generic Bulk-SMS API
  template (GET/POST, headers, success keyword) + Meta WhatsApp
  Cloud API; master switches + test-mode (default ON) for Super
  Admin; per-channel status, automation toggles, manual compose
  (class/all/custom + {name}/{class}/{roll} vars), and 200-entry
  send log on the new EduTurn → SMS screen.
* Attendance auto-SMS: absent guardians notified when the class
  teacher (or an SMS manager) saves attendance; re-saves never
  re-spam; works from teacher portal and admin alike.
* Result auto-SMS on CSV import (per-run checkbox + global flag).
* Class-teacher assignment per class (teacher form + badges +
  mapping table); WhatsApp number fields (entity + profile).
= 1.8.1 =
* License enforcement that actually lands: hourly check-in cron
  (auto-rescheduled from twice-daily), ≤6h latency cap, and a
  throttled opportunistic check on frontend visits — suspend /
  revoke / website-off now take effect in ~1h instead of ~12h.
* Fail closed: a license deleted or reset on the server locks the
  school install on its next check-in (was: kept running on stale
  cache); clear re-activation message for the school admin.
* Admin preview banner: logged-in Super Admins viewing a locked
  site see a floating notice (visitors get the lock screen).
= 1.9.0 =
* Mail-transport hardening: password reset / Add User / password
  change can no longer white-screen when mail() is disabled or the
  SMTP plugin is broken — sends are skipped with a debug.log entry,
  passwords still save, and Users screens + lost-password form show
  exactly what to fix (WP Mail SMTP + Test Email).
* Homepage visual rebuild ("Aurora institutional"): gradient-mesh
  hero with glass stat chips + scroll cue, floating quick-access
  cards, eyebrow pills, gradient buttons/stats band, card hover
  physics, gallery zoom — scoped to body.home, inner pages untouched.
* Homepage FAQ + contact sections permanently removed (registry,
  templates, dead CSS). ut_faq content + standalone contact page stay.
= 1.10.0 =
* Portal logins redesigned: split-screen branded cards (school logo,
  feature bullets, wave graphic), inline Bangla errors on wrong/empty
  credentials (stays on the portal page), show/hide password toggle,
  mobile-number hint, student↔teacher cross-links.
* wp-login errors now show Bangla explanations alongside WP messages.
= 1.9.2 =
* mail()-disabled admin notice removed entirely (per request).
= 1.9.1 =
* mail()-disabled notice: auto-hides once an SMTP plugin is active
  (detected via phpmailer_init), and adds a persistent per-user
  "নোটিশটি লুকান" dismiss link for servers where mail stays off.
= 1.14.0 =
* NEW Discovery module (inc/seo.php): context-aware meta descriptions,
  Open Graph + Twitter Cards, WebSite + BreadcrumbList + FAQPage JSON-LD,
  School schema url/logo, correct <html lang="bn"> in Bangla mode,
  robots.txt welcome for AI crawlers, and a virtual /llms.txt.
* Accessibility: aria-current on nav, focus move/trap/return for
  lightbox + drawer + search, BN/EN lightbox labels, scope on all
  table headers, unique aria-labels on repeated Details/Read links.
* Performance: fetchpriority on LCP covers, stable aspect-ratio for
  single covers (no layout shift); frontend stays jQuery-free with
  deferred scripts and preconnected fonts.
= 1.15.0 =
* Results 2.0: graphical class-wise marks-entry grid (teachers enter,
  auto-calculated total/GPA/grade/pass-fail live); draft → review →
  publish workflow — only published results go public (lookup, student
  portal and teacher view are publish-gated).
* Teachers can submit but never publish (caps + CRUD + handler guards);
  School Admin / Headmaster review, publish, send back; bulk publish
  with merit positions, optional result SMS, optional auto-promotion.
* Promotion engine: annual pass auto-promotes to the next class (one
  stable student ID forever; class history kept); manual move + TC /
  left-school status (left students leave rosters, keep old results);
  top-class pass-outs marked graduated.
* Result lookup card now shows total + merit position; demo lookup
  removed from the public results page.
* A4 document printing everywhere: result card, admission receipt,
  class/exam routines, news release, admit cards, tabulation sheet —
  print CSS outputs letterheaded A4 documents, never the website.
* CSV import kept for publishers (auto student-link + merit recalc).
= 1.20.1 =
* wp-login order: EduTurn logo, then "স্কুল ম্যানেজমেন্ট সিস্টেম",
  then the school name, then the form (combined tagline removed).
* Fixed: login injector shipped with escaped quotes that broke the
  script in real browsers (school name/tagline never rendered).
  Rendered inline scripts are now node-validated in QA.

= 1.20.0 =
* Brand refresh: new official EduTurn logo in the dashboard sidebar
  (above the school name), portal login cards and wp-login.
* wp-login now shows the school name under the EduTurn logo with
  single-line auto-fit.
* Mobile/tablet header: school name stacks below the logo in one line
  with auto-shrinking font (fixed width) instead of hiding.

= 1.19.0 =
* Configurable exams: add/edit/activate/deactivate exams from the
  dashboard (Academic > Exams); deactivation never breaks old results
  and delete is blocked while results exist.
* Class code, display order and archive status; classes with students
  or results archive instead of deleting (restore anytime).
* Subject master gains default full marks, pass marks and display
  order; exam setup pre-fills full marks from the master.
* Student-specific extra subjects (e.g. 4th subject): union ✳ columns
  in the entry grid, N/A cells disabled, per-student completeness.
* Merit rank can follow total marks or GPA (grading page setting).
* Individual result cards show a grade-point column and the student's
  photo (public lookup + student portal).

= 1.18.1 =
* Hardening: strip the | delimiter from subject/group names (it would
  corrupt result line parsing); dedupe repeated subjects in exam setup
  and class subject maps.
* Consistency: student portal subject table shows mark/full (45/50)
  like the dashboard and public result card.
= 1.18.0 =
* Exam subject setup: office declares per-exam subjects + full marks
  (100/50/…) before entry; grid headers/inputs honor full marks and
  subject-teachers' mapped subjects auto-merge into the grid.
* Percentage grading: grades/GPA computed on mark÷full, so mixed
  full-marks share one scale; grading editor relabelled for percent.
* Merit places as ordinals (১ম/২য়/৩য়/৪র্থ/৫ম…) on review,
  tabulation, profile sheet and public result card.
* Printing: admit card = one student per A4 page; per-result single
  print on student portal; class tabulation landscape-A4 print view
  with fixed column widths.
* Branding: EduTurn logo above the school name on all dashboards
  and on login (portal cards + wp-login); U-Turn credit kept below.
* Subjects: explicit "general / no-group" state with helper text.
* Security: new exam-setup save action allowlisted for shell roles
  with nonce + publisher-cap enforcement.
= 1.17.0 =
* Performance: hero LCP preload, one-time ut_meta_kv postmeta index,
  reduced-motion support, production JPG recompression (~300KB saved).
* SEO: sitemap sub-URLs forced HTTP 200 on post-less sites (real 404s
  untouched), BreadcrumbList JSON-LD on pages/archives/singles, h1 and
  image-alt audits clean.
* Security: class-scoped teacher views (students, attendance, admit,
  results) with server-side save guard; unified result dedup; merit
  recalc on publish-state transitions.
* Accessibility: labelled filters/selects/tables across dashboards,
  status/alert roles on notices, keyboard-operable menus and dialogs.
* AI-ready: descriptive action buttons, table labels, enriched llms.txt.
* Cumulative GPA: average across a student's same-year published exams
  on public lookup and student portal.
* Mobile: every dashboard/frontend table scrolls safely; print CSS
  reviewed (A4 letterhead output incl. admit cards).
= 1.16.0 =
* Permanent Student ID (STU-XXXXX): auto-assigned on first save,
  never changes on promotion; shown in entry grid, student list,
  receipts and profile sheet; public result lookup by ID.
* Entry grid: section filter + roll/name search; subject-teachers see
  only their mapped subjects (draft-only), class-teachers submit;
  per-subject marks merge safely; live % column; sticky ID columns.
* Configurable grading scale (Grading view): custom min/grade/point
  rows drive server calc + live grid JS; one-click board-default
  reset.
* 7-stage result workflow (draft/submitted/review/correction/
  approved/published/archived) with per-result audit trail, stage
  filters, bulk stage moves, archive/republish with merit recalc.
* Full student lifecycle statuses: active/promoted/retained/
  transferred/left/graduated/inactive; auto-promotion marks fails
  Retained; manual retain/transfer/graduate/reactivate.
* A4 prints: daily attendance sheet, fee receipt, student profile
  sheet (history + published results); CSV import now computes
  totals, snapshots section/code/stage, writes audit.
* Production hardening: demo results seed as draft + one-time
  demotion of old published demos; noindex for 404/empty archives;
  shell skip-link + bilingual back-to-top + labelled dashboard
  searches; QC sweep (links/contrast/placeholders/console) clean.
