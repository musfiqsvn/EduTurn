# EduTurn v1.14.0 — Discovery & Access (SEO + Agentic + A11y + Perf)

Date: 2026-09-15. Ship: `/home/user/eduturn.zip` (146 files, ~4.8MB; backup `eduturn-1.13.1.zip`).
Baseline: v1.13.1 ship restored byte-identical after env wipe, live-verified, then extended.

## New: Discovery module (`inc/seo.php`, new file)
- Context-aware `<meta name="description">`: front page (BN/EN), singles
  (`_ut_excerpt` → content trim 155), CPT archives, search.
- Open Graph (7 tags: type/site_name/locale/title/desc/url/image +
  article publish/modify times) + Twitter summary_large_image (4 tags).
- Correct `<html lang>`: `bn` in Bangla mode (was hardcoded `en-US`),
  `en-US` in English mode. Helps screen readers + Google language targeting.
- JSON-LD: `WebSite` (front) + `FAQPage` (12 Q&A from `ut_faq`, front) +
  `BreadcrumbList` (all singles/pages); `School` schema gained `url` + `logo`.
- `robots.txt`: explicit `Allow: /` for GPTBot, ClaudeBot, PerplexityBot,
  Google-Extended, Applebot-Extended, CCBot (core sitemap line kept).
- Virtual `/llms.txt` (rewrite + `template_redirect`, `text/plain`, HTTP 200):
  school summary, 12 key-page URLs, AI-assistant notes (results need roll,
  portals need login), sitemap + contact. `redirect_canonical` short-circuit
  so no trailing-slash 301.
- One-time rewrite flush keyed on `UTURN_VERSION` (no admin visit needed).

## Accessibility
- `aria-current="page"` on current nav item: desktop walker, drawer walker,
  fallback menu (both layouts). Live-verified on home link.
- Lightbox: focus moves to Close on open, Tab trap inside dialog,
  focus returns to trigger on close, `aria-modal="true"`, BN/EN labels
  (বন্ধ করুন/আগের/পরের).
- Drawer: focus to close button on open, back to hamburger on close.
- Search overlay: focus returns to search button on close.
- `scope="col"/"row"` on ALL 190 table headers (18 files, frontend + dashboards).
- Unique `aria-label`s on repeated links: Details/Read + item title
  (notices/news/events/programs home cards + notice archive) — 22 labelled.
- Already-strong baselines kept: 100% alt text, AAA contrast (5.3–15.0),
  skip link, focus-visible, reduced-motion (global + reveal + ticker),
  44px+ touch targets, labelled forms.

## Performance
- LCP: `fetchpriority="high"` on hero (kept) + news/event single covers (new).
- CLS: `.single-cover` gained `aspect-ratio:16/7` (stable box, still capped).
- Kept lean: 2 CSS + 2 deferred JS, zero jQuery/emoji/oEmbed on frontend,
  preconnected fonts, lazy below-fold images, `no_found_rows` queries.

## QA evidence (fresh seeded env, 127.0.0.1:8080)
- Home: `lang="bn"`, 1 desc, 7 OG, 4 TW, WebSite + FAQPage(12Q), School url/logo,
  aria-current ×1, labelled links ×22, `(/n)`=0, warnings=0.
- EN home (cookie): `lang="en-US"`, EN desc, `en_US` locale, EN labels ×22.
- Single notice: BreadcrumbList, article times, desc, OG image, `(/n)`=0.
- `/llms.txt` → 200 `text/plain` (28 lines); robots lists 3+ AI bots.
- Frontend sweep 14/14 (home + 13 pages, EN spot 3/3), dashboard logged-out
  302 (no fatal), `debug.log` zero entries after fixes, photo regen re-verified
  (purge → fetch → `*-400x400.jpg` resurrected → QA cleaned).
- `php -l` all files + `node --check` clean; source↔deploy↔zip identical.

## Notes
- Sitemap XML content is correct; its HTTP 404 status is a `php -S` sandbox
  quirk (identical under Twenty Twenty-Four) — real hosting serves 200.
- Fresh-DB seed wrote `/wptest/`-prefixed menu URLs (siteurl fixed after
  seeding); corrected via `search-replace` (135 replacements), dump updated.
- Env lesson: never parallelize `edit_file` calls on the SAME file
  (last-write-wins race); JS tail mangled once, restored from ship zip.
