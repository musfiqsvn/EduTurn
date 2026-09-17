/* ============================================================
   আলোকিত বিদ্যানিকেতন — Core Application JS
   Header/footer chrome · navigation · i18n · interactions
   ============================================================ */
(function () {
  'use strict';

  const D = () => window.SITE_DATA || {}; // UTURN-WP: localized bridge (replaces data.js)
  const root = () => (document.body.dataset.root || '');
  const R = (p) => root() + p;

  /* ---------- Utilities ---------- */
  const BN_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
  const toBn = (v) => String(v).replace(/\d/g, (d) => BN_DIGITS[+d]);
  const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const $ = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
  const initials = (name) => {
    const parts = String(name).replace(/^(মোঃ|মোসা\.|ড\.|ডা\.|ইঞ্জি\.|জনাব|আলহাজ্ব)\s*/u, '').trim().split(/\s+/);
    return ((parts[0] || '')[0] || 'অ') + ((parts[1] || '')[0] || '');
  };

  function toast(msg, type) {
    let wrap = $('.toast-wrap');
    if (!wrap) { wrap = document.createElement('div'); wrap.className = 'toast-wrap'; document.body.appendChild(wrap); }
    const t = document.createElement('div');
    t.className = 'toast' + (type ? ' ' + type : '');
    t.setAttribute('role', 'status');
    t.innerHTML = (type === 'success'
      ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg>'
      : type === 'error'
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>')
      + '<span>' + esc(msg) + '</span>';
    wrap.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 320); }, 3200);
  }

  /* ---------- Mini i18n (chrome labels) ---------- */
  const I18N = {
    bn: { home: 'হোম', about: 'আমাদের সম্পর্কে', academic: 'একাডেমিক', student: 'শিক্ষার্থী', admission: 'ভর্তি', notice: 'নোটিশ', events: 'ইভেন্ট', gallery: 'গ্যালারি', news: 'সংবাদ', contact: 'যোগাযোগ', apply: 'অনলাইনে ভর্তি', studentLogin: 'শিক্ষার্থী লগইন', teacherLogin: 'শিক্ষক লগইন', welcome: 'স্বাগতম' },
    en: { home: 'Home', about: 'About Us', academic: 'Academic', student: 'Students', admission: 'Admission', notice: 'Notice', events: 'Events', gallery: 'Gallery', news: 'News', contact: 'Contact', apply: 'Apply Online', studentLogin: 'Student Login', teacherLogin: 'Teacher Login', welcome: 'Welcome' }
  };
  const lang = () => localStorage.getItem('ab_lang') || 'bn';
  const T = (k) => (I18N[lang()] || I18N.bn)[k] || k;
  function setLang(l) {
    localStorage.setItem('ab_lang', l);
    document.documentElement.lang = l === 'bn' ? 'bn' : 'en';
    document.body.setAttribute('lang', l);
    renderChrome();
    toast(l === 'bn' ? 'বাংলা ভাষা নির্বাচন করা হয়েছে' : 'English selected — key navigation translated', 'success');
  }

  /* ---------- Icons ---------- */
  const IC = {
    logo: '<svg viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#0B4EA8"/><circle cx="32" cy="21" r="6" fill="#7FC4F2"/><path d="M14 33c6-3.5 12-3.5 18 0 6-3.5 12-3.5 18 0v15c-6-3.5-12-3.5-18 0-6-3.5-12-3.5-18 0z" fill="none" stroke="#fff" stroke-width="4.5" stroke-linejoin="round"/></svg>',
    search: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
    menu: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
    close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18"/></svg>',
    caret: '<svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m6 9 6 6 6-6"/></svg>',
    arrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>',
    up: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 19V5m-6 6 6-6 6 6"/></svg>',
    left: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 6l-6 6 6 6"/></svg>',
    right: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 6l6 6-6 6"/></svg>',
    check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg>',
    pin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.6"/></svg>',
    phone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.13.96.36 1.9.7 2.8a2 2 0 0 1-.45 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.45c.9.34 1.84.57 2.8.7A2 2 0 0 1 22 16.9Z"/></svg>',
    mail: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
    clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    file: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>',
    bell: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 8-3 8h18s-3-1-3-8"/><path d="M10.3 21a2 2 0 0 0 3.4 0"/></svg>',
    result: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="9" r="6"/><path d="m8.5 14-1.8 7 5.3-3 5.3 3-1.8-7"/></svg>',
    routine: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>',
    cal: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18M8 15h3"/></svg>',
    book: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></svg>',
    users: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg>',
    cap: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3 2 8l10 5 10-5-10-5Z"/><path d="M6 10.5V15c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/></svg>',
    bulb: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18h6M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.4 1 2.3h6c0-.9.4-1.8 1-2.3A7 7 0 0 0 12 2Z"/></svg>',
    shield: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 4 6v6c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6l-8-4Z"/><path d="m9 12 2 2 4-4"/></svg>',
    flask: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6M10 3v6L4.5 19a2 2 0 0 0 1.8 3h11.4a2 2 0 0 0 1.8-3L14 9V3"/><path d="M7 15h10"/></svg>',
    monitor: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8m-4-4v4"/></svg>',
    trophy: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8m-4-4v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M7 6H4a1 1 0 0 0-1 1c0 2.5 2 4 4 4M17 6h3a1 1 0 0 1 1 1c0 2.5-2 4-4 4"/></svg>',
    heart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7 7-7Z"/></svg>',
    chat: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a8 8 0 0 1-8 8H4l2-3a8 8 0 1 1 15-5Z"/></svg>',
    fb: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8h2.7l.4-3.2h-3.1V8.7c0-.9.3-1.6 1.7-1.6h1.6V4.2c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.7H7.8V14h2.8v8h2.9Z"/></svg>',
    yt: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.4-.4-5A2.8 2.8 0 0 0 20.6 5C18.9 4.5 12 4.5 12 4.5s-6.9 0-8.6.5A2.8 2.8 0 0 0 1.4 7C1 8.6 1 12 1 12s0 3.4.4 5a2.8 2.8 0 0 0 2 2c1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5a2.8 2.8 0 0 0 2-2c.4-1.6.4-5 .4-5ZM9.8 15.5v-7l6 3.5-6 3.5Z"/></svg>',
    send: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4 20-7Z"/></svg>',
    grid: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
    logout: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>',
    download: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>'
  };

  /* ---------- Navigation model ---------- */
  function navModel() {
    return [
      { label: T('home'), href: R('index.html'), key: 'home' },
      {
        label: T('about'), href: R('about.html'), key: 'about', children: [
          { label: lang() === 'bn' ? 'প্রতিষ্ঠানের ইতিহাস' : 'History', href: R('about.html#history') },
          { label: lang() === 'bn' ? 'লক্ষ্য ও উদ্দেশ্য' : 'Mission & Vision', href: R('about.html#mission') },
          { label: lang() === 'bn' ? 'প্রধান শিক্ষকের বার্তা' : "Principal's Message", href: R('about.html#principal') },
          { label: lang() === 'bn' ? 'পরিচালনা পর্ষদ' : 'Management', href: R('about.html#management') },
          { label: lang() === 'bn' ? 'শিক্ষক ও কর্মচারী' : 'Faculty & Staff', href: R('teachers.html') }
        ]
      },
      {
        label: T('academic'), href: R('academic.html'), key: 'academic', children: [
          { label: lang() === 'bn' ? 'শ্রেণিসমূহ' : 'Classes', href: R('academic.html#classes') },
          { label: lang() === 'bn' ? 'একাডেমিক ক্যালেন্ডার' : 'Academic Calendar', href: R('academic.html#calendar') },
          { label: lang() === 'bn' ? 'ক্লাস রুটিন' : 'Class Routine', href: R('routine.html') },
          { label: lang() === 'bn' ? 'পরীক্ষার রুটিন' : 'Exam Routine', href: R('routine.html#exam') },
          { label: lang() === 'bn' ? 'ফলাফল' : 'Results', href: R('results.html') }
        ]
      },
      {
        label: T('student'), href: R('students.html'), key: 'students', children: [
          { label: lang() === 'bn' ? 'শিক্ষার্থী কর্নার' : 'Student Corner', href: R('students.html') },
          { label: lang() === 'bn' ? 'শিক্ষার্থী তালিকা' : 'Student Directory', href: R('students-list.html') },
          { label: lang() === 'bn' ? 'ফলাফল' : 'Results', href: R('results.html') },
          { label: lang() === 'bn' ? 'নোটিশ' : 'Notices', href: R('notices.html') },
          { label: lang() === 'bn' ? 'ডাউনলোড' : 'Downloads', href: R('downloads.html') }
        ]
      },
      {
        label: T('admission'), href: R('admission.html'), key: 'admission', children: [
          { label: lang() === 'bn' ? 'ভর্তি তথ্য' : 'Admission Info', href: R('admission.html') },
          { label: lang() === 'bn' ? 'ভর্তি নির্দেশিকা' : 'Guideline', href: R('admission.html#guideline') },
          { label: lang() === 'bn' ? 'অনলাইন আবেদন' : 'Apply Online', href: R('apply.html') }
        ]
      },
      { label: T('notice'), href: R('notices.html'), key: 'notices' },
      { label: T('events'), href: R('events.html'), key: 'events' },
      { label: T('gallery'), href: R('gallery.html'), key: 'gallery' },
      { label: T('news'), href: R('news.html'), key: 'news' },
      { label: T('contact'), href: R('contact.html'), key: 'contact' }
    ];
  }

  /* ---------- Chrome rendering ---------- */
  /* ---------- Auto-fit school name: one line, fixed area ---------- */
  // Name too long -> shrink font to fit; short name -> grow to max. Never wraps.
  const BRAND_MAX = 40; // px (= 2.5rem)
  const BRAND_MIN = 15; // px
  function fitBrandName() {
    const el = document.querySelector('.identity-row .brand-name');
    if (!el || !el.getClientRects().length) return; // hidden on mobile: skip
    const box = el.clientWidth;
    if (box <= 0) return;
    el.style.fontSize = BRAND_MAX + 'px';
    if (el.scrollWidth <= box) return; // fits at max size
    let lo = BRAND_MIN, hi = BRAND_MAX; // binary search: biggest size that fits
    while (hi - lo > 0.5) {
      const mid = (lo + hi) / 2;
      el.style.fontSize = mid + 'px';
      if (el.scrollWidth > box) hi = mid; else lo = mid;
    }
    el.style.fontSize = lo + 'px';
  }
  function bindBrandFit() {
    if (bindBrandFit.done) return;
    bindBrandFit.done = true;
    let t = null;
    window.addEventListener('resize', () => { clearTimeout(t); t = setTimeout(fitBrandName, 120); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitBrandName);
    window.addEventListener('load', fitBrandName);
    if ('ResizeObserver' in window) {
      let tick = false;
      const ro = new ResizeObserver(() => {
        if (tick) return; tick = true;
        requestAnimationFrame(() => { tick = false; fitBrandName(); });
      });
      const anchor = document.querySelector('.identity-row .brand');
      if (anchor) ro.observe(anchor);
    }
  }

  /* ---------- Notice ticker (auto-scroll below menu) ---------- */
  const TK_BELL = '<svg viewBox=\"0 0 24 24\" width=\"15\" height=\"15\" fill=\"currentColor\" aria-hidden=\"true\"><path d=\"M12 22c1.1 0 2-.9 2-2h-4a2 2 0 0 0 2 2zm6-6v-5a6 6 0 0 0-4.5-5.8V4a1.5 1.5 0 0 0-3 0v1.2A6 6 0 0 0 6 11v5l-2 2v1h16v-1l-2-2z\"/></svg>';
  function tickerHTML(d) {
    const list = (d.notices || []).slice();
    if (!list.length) return '';
    const norm = (s) => String(s || '').replace(/[\u09E6-\u09EF]/g, (c) => '0123456789'['\u09E6\u09E7\u09E8\u09E9\u09EA\u09EB\u09EC\u09ED\u09EE\u09EF'.indexOf(c)]);
    list.sort((a, b) => (norm(b.date) > norm(a.date) ? 1 : -1));
    const half = list.slice(0, 8).map((n) =>
      '<a class=\"ticker-item\" href=\"' + R('notice.html?slug=' + n.slug) + '\">'
      + '<span class=\"tk-date\">' + esc(n.dateBn) + '</span>'
      + '<span>' + esc(n.title) + '</span></a>').join('');
    if (!half) return '';
    const dup = half.replace(/<a /g, '<a tabindex=\"-1\" '); // 2nd loop copy: no double tab-stops
    return '<div class=\"notice-ticker\" role=\"region\" aria-label=\"' + esc(T('notice')) + '\"><div class=\"container ticker-inner\">'
      + '<a class=\"ticker-label\" href=\"' + R('notices.html') + '\">' + TK_BELL + '<span>' + esc(T('notice')) + '</span></a>'
      + '<div class=\"ticker-view\"><div class=\"ticker-track\">' + half + dup + '</div></div>'
      + '</div></div>';
  }
  function fitTickerSpeed() {
    const tr = document.querySelector('.ticker-track');
    if (!tr) return;
    tr.style.animationDuration = Math.max(8, (tr.scrollWidth / 2) / (window.UTURN_TICKER_SPEED || 45)) + 's'; // UTURN-WP: dashboard speed
  }
  function initTicker() {
    const bar = document.querySelector('.notice-ticker');
    if (!bar) return;
    fitTickerSpeed();
    if (!initTicker.bound) {
      initTicker.bound = true;
      let t = null;
      window.addEventListener('resize', () => { clearTimeout(t); t = setTimeout(fitTickerSpeed, 150); });
      if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitTickerSpeed);
      window.addEventListener('load', fitTickerSpeed);
    }
  }

  /* ---------- UTURN-WP: bind to PHP-rendered chrome (WordPress mode) ---------- */
  // header.php/footer.php print the exact DOM static renderChrome() used to build.
  // When <body data-wp="1"> is present we skip ALL innerHTML overwrites and only
  // wire up interactions, so PHP output (menus, ticker, brand) is never clobbered.
  function bindWpChrome() {
    const langBtns = document.querySelectorAll('[data-lang]');
    langBtns.forEach((b) => b.addEventListener('click', () => {
      try { localStorage.setItem('ab_lang', b.dataset.lang); } catch (e) { /* private mode */ }
      document.cookie = 'uturn_lang=' + b.dataset.lang + ';path=/;max-age=31536000';
      location.reload();
    }));
    const dr = document.getElementById('drawer'), bg = document.getElementById('drawer-bg');
    const menuBtn = document.getElementById('menu-btn');
    const openDrawer = (o) => {
      if (dr) dr.classList.toggle('open', o);
      if (bg) bg.classList.toggle('open', o);
      document.body.style.overflow = o ? 'hidden' : '';
      if (menuBtn) menuBtn.setAttribute('aria-expanded', o);
    };
    if (menuBtn) menuBtn.addEventListener('click', () => openDrawer(true));
    const dc = document.getElementById('drawer-close');
    if (dc) dc.addEventListener('click', () => openDrawer(false));
    if (bg) bg.addEventListener('click', () => openDrawer(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { openDrawer(false); closeSearch(); } });
    document.querySelectorAll('.mnav-btn').forEach((b) => b.addEventListener('click', () => {
      const li = b.parentElement, exp = li.classList.toggle('expanded');
      b.setAttribute('aria-expanded', exp);
    }));
    const sb = document.getElementById('search-btn');
    if (sb) sb.addEventListener('click', openSearch);
    renderSearchOverlay();
    const bt = document.getElementById('back-top');
    if (bt && !bt.dataset.bound) {
      bt.dataset.bound = '1';
      bt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
      window.addEventListener('scroll', () => bt.classList.toggle('show', window.scrollY > 600), { passive: true });
    }
    fitBrandName();
    initTicker();
  }

  function renderChrome() {
    const d = D();
    if (!d) return;
    if (document.body.dataset.wp === '1') { bindWpChrome(); return; } // UTURN-WP early path
    const page = document.body.dataset.page || '';
    const nav = navModel();

    const topbar = $('#topbar-root');
    if (topbar) {
      topbar.innerHTML =
        '<div class="topbar"><div class="container">'
        + '<div class="topbar-left"><a href="tel:' + d.brand.phoneHref + '">' + IC.phone + '<span>' + esc(d.brand.phone) + '</span></a></div>'
        + '<div class="topbar-right">'
        + '<a href="' + R('notices.html') + '">' + esc(T('notice')) + '</a><span class="sep">|</span>'
        + '<a href="' + R('student/login.html') + '">' + esc(T('studentLogin')) + '</a><span class="sep">|</span>'
        + '<a href="' + R('teacher/login.html') + '">' + esc(T('teacherLogin')) + '</a>'
        + '<span class="lang-toggle" role="group" aria-label="Language"><button type="button" data-lang="bn" class="' + (lang() === 'bn' ? 'active' : '') + '">বাংলা</button><button type="button" data-lang="en" class="' + (lang() === 'en' ? 'active' : '') + '">English</button></span>'
        + '</div></div></div>';
      $$('[data-lang]', topbar).forEach((b) => b.addEventListener('click', () => setLang(b.dataset.lang)));
    }

    const header = $('#header-root');
    if (header) {
      const items = nav.map((n) => {
        const active = page === n.key ? ' active' : '';
        if (!n.children) return '<li class="nav-item"><a class="nav-link' + active + '" href="' + n.href + '">' + esc(n.label) + '</a></li>';
        return '<li class="nav-item"><a class="nav-link' + active + '" href="' + n.href + '" aria-haspopup="true">' + esc(n.label) + IC.caret + '</a>'
          + '<ul class="dropdown">' + n.children.map((c) => '<li><a href="' + c.href + '">' + esc(c.label) + '</a></li>').join('') + '</ul></li>';
      }).join('');
      const ticker = tickerHTML(d);
      header.innerHTML =
        '<header class="site-header">' + '<div class="container identity-row">'
        + '<a class="brand" href="' + R('index.html') + '" aria-label="' + esc(d.brand.nameBn) + '">'
        + '<span class="brand-mark">' + '<img src="' + R('assets/images/logo.svg') + '" alt="">' + '</span>'
        + '<span class="brand-name">' + esc(lang() === 'bn' ? d.brand.nameBn : d.brand.nameEn) + '</span></a>'
        + '<div class="header-actions">'
        + '<button class="icon-btn" id="search-btn" aria-label="Search">' + IC.search + '</button>'
        + '<a class="btn btn-primary header-cta" href="' + R('apply.html') + '">' + esc(T('apply')) + '</a>'
        + '<button class="icon-btn hamburger" id="menu-btn" aria-label="Menu" aria-expanded="false">' + IC.menu + '</button>'
        + '</div></div>'
        + '<nav class="nav-row" aria-label="Primary"><div class="container"><ul class="main-nav">' + items + '</ul></div></nav>'
        + ticker + '</header>';
      fitBrandName();
      initTicker();
      const mnav = nav.map((n) => {
        if (!n.children) return '<li><a href="' + n.href + '">' + esc(n.label) + '</a></li>';
        return '<li><button type="button" class="mnav-btn" aria-expanded="false">' + esc(n.label) + IC.caret + '</button>'
          + '<ul class="sub">' + n.children.map((c) => '<li><a href="' + c.href + '">' + esc(c.label) + '</a></li>').join('') + '</ul></li>';
      }).join('');
      const _oldDr = document.getElementById('drawer');
      if (_oldDr) _oldDr.remove();
      const _oldBg = document.getElementById('drawer-bg');
      if (_oldBg) _oldBg.remove();
      const drawer = document.createElement('div');
      drawer.innerHTML =
        '<div class="drawer-backdrop" id="drawer-bg"></div>'
        + '<aside class="drawer" id="drawer" aria-label="Mobile navigation">'
        + '<div class="drawer-head"><img src="' + R('assets/images/logo.svg') + '" alt="" style="width:44px;height:44px;border-radius:10px;display:block">' + '<button class="icon-btn" id="drawer-close" aria-label="Close" style="background:rgba(255,255,255,.12);border-color:transparent;color:#fff">' + IC.close + '</button></div>'
        + '<div class="drawer-body"><ul class="mnav">' + mnav + '</ul></div>'
        + '<div class="drawer-foot"><a class="btn btn-primary btn-block" href="' + R('apply.html') + '">' + esc(T('apply')) + '</a>'
        + '<div style="display:flex;gap:.5rem"><a class="btn btn-outline btn-sm" style="flex:1" href="' + R('student/login.html') + '">' + esc(T('studentLogin')) + '</a><a class="btn btn-outline btn-sm" style="flex:1" href="' + R('teacher/login.html') + '">' + esc(T('teacherLogin')) + '</a></div></div>'
        + '</aside>';
      document.body.appendChild(drawer);
      const dr = $('#drawer'), bg = $('#drawer-bg');
      const openDrawer = (o) => { dr.classList.toggle('open', o); bg.classList.toggle('open', o); document.body.style.overflow = o ? 'hidden' : ''; $('#menu-btn').setAttribute('aria-expanded', o); };
      $('#menu-btn').addEventListener('click', () => openDrawer(true));
      $('#drawer-close').addEventListener('click', () => openDrawer(false));
      bg.addEventListener('click', () => openDrawer(false));
      if (!renderChrome.keysBound) {
        renderChrome.keysBound = true;
        document.addEventListener('keydown', (e) => {
          if (e.key !== 'Escape') return;
          const _dr = document.getElementById('drawer'), _bg = document.getElementById('drawer-bg'), _mb = document.getElementById('menu-btn');
          if (_dr) _dr.classList.remove('open');
          if (_bg) _bg.classList.remove('open');
          if (_mb) _mb.setAttribute('aria-expanded', 'false');
          document.body.style.overflow = '';
          closeSearch();
        });
      }
      $$('.mnav-btn', drawer).forEach((b) => b.addEventListener('click', () => {
        const li = b.parentElement, exp = li.classList.toggle('expanded');
        b.setAttribute('aria-expanded', exp);
      }));
      $('#search-btn').addEventListener('click', openSearch);
    }

    renderSearchOverlay();

    const footer = $('#footer-root');
    if (footer) {
      footer.innerHTML =
        '<footer class="site-footer"><div class="footer-main"><div class="container footer-grid">'
        + '<div class="footer-brand"><a class="brand" href="' + R('index.html') + '" aria-label="' + esc(d.brand.nameBn) + '"><span class="brand-mark">' + '<img src="' + R('assets/images/logo.svg') + '" alt="">' + '</span><span><span class="brand-name">' + esc(d.brand.nameBn) + '</span></span></a>'
        + '<p class="mt-1">আধুনিক শিক্ষা পদ্ধতি, অভিজ্ঞ শিক্ষক এবং শিক্ষাবান্ধব পরিবেশের মাধ্যমে আমরা প্রতিটি শিক্ষার্থীর সম্ভাবনাকে বিকশিত করতে কাজ করি।</p>'
        + '<div class="social-row"><a class="social-btn" href="#" aria-label="Facebook" title="Facebook">' + IC.fb + '</a><a class="social-btn" href="#" aria-label="YouTube" title="YouTube">' + IC.yt + '</a><a class="social-btn" href="' + R('contact.html') + '" aria-label="Contact" title="Contact">' + IC.send + '</a></div></div>'
        + '<div><h3>গুরুত্বপূর্ণ লিংক</h3><ul class="footer-links">'
        + '<li><a href="' + R('about.html') + '">আমাদের সম্পর্কে</a></li><li><a href="' + R('notices.html') + '">নোটিশ বোর্ড</a></li><li><a href="' + R('results.html') + '">ফলাফল</a></li><li><a href="' + R('routine.html') + '">ক্লাস রুটিন</a></li><li><a href="' + R('downloads.html') + '">ডাউনলোড</a></li><li><a href="' + R('gallery.html') + '">গ্যালারি</a></li><li><a href="' + R('teachers.html') + '">শিক্ষক ও কর্মচারী</a></li></ul></div>'
        + '<div><h3>একাডেমিক</h3><ul class="footer-links">'
        + '<li><a href="' + R('academic.html') + '">একাডেমিক তথ্য</a></li><li><a href="' + R('admission.html') + '">ভর্তি তথ্য</a></li><li><a href="' + R('apply.html') + '">অনলাইন আবেদন</a></li><li><a href="' + R('teachers.html') + '">শিক্ষকমণ্ডলী</a></li><li><a href="' + R('events.html') + '">ইভেন্টসমূহ</a></li><li><a href="' + R('news.html') + '">সংবাদ</a></li><li><a href="' + R('students-list.html') + '">শিক্ষার্থী তালিকা</a></li></ul></div>'
        + '<div><h3>যোগাযোগ</h3><ul class="footer-contact">'
        + '<li>' + IC.pin + '<span>' + esc(d.brand.address) + '</span></li>'
        + '<li>' + IC.phone + '<span><a href="tel:' + d.brand.phoneHref + '">' + esc(d.brand.phone) + '</a></span></li>'
        + '<li>' + IC.mail + '<span><a href="mailto:' + d.brand.email + '">' + esc(d.brand.email) + '</a></span></li>'
        + '<li>' + IC.clock + '<span>' + esc(d.brand.hoursShort) + '</span></li>'
        + '</ul><a class="btn btn-outline-light btn-sm" href="' + R('student/login.html') + '">পোর্টাল লগইন</a></div>'
        + '</div></div>'
        + '<div class="footer-bottom"><div class="container"><span>© ২০২৬ ' + esc(d.brand.nameBn) + '। সর্বস্বত্ব সংরক্ষিত।</span><span>Designed &amp; Developed by UTurn Digital Solutions</span></div></div></footer>';
    }

    // Mobile sticky CTA
    if (!$('#mobile-cta') && !document.body.classList.contains('no-cta')) {
      document.body.classList.add('has-mobile-cta');
      const cta = document.createElement('nav');
      cta.id = 'mobile-cta';
      cta.className = 'mobile-cta';
      cta.setAttribute('aria-label', 'Quick actions');
      cta.innerHTML =
        '<a href="' + R('apply.html') + '" class="primary">' + IC.cap + '<span>ভর্তি</span></a>'
        + '<a href="' + R('results.html') + '">' + IC.result + '<span>ফলাফল</span></a>'
        + '<a href="tel:' + d.brand.phoneHref + '">' + IC.phone + '<span>কল করুন</span></a>';
      document.body.appendChild(cta);
    }

    // Back to top
    if (!$('#back-top')) {
      const b = document.createElement('button');
      b.id = 'back-top'; b.className = 'back-top'; b.setAttribute('aria-label', 'Back to top');
      b.innerHTML = IC.up;
      b.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
      document.body.appendChild(b);
      window.addEventListener('scroll', () => b.classList.toggle('show', window.scrollY > 600), { passive: true });
    }
  }

  /* ---------- Global search ---------- */
  function searchIndex() {
    const d = D(), items = [];
    // UTURN-WP: localized items carry absolute `url`; static fallbacks preserved.
    (d.notices || []).forEach((n) => items.push({ type: 'নোটিশ', title: n.title, desc: n.excerpt, href: n.url || R('notice.html?slug=' + n.slug) }));
    (d.events || []).forEach((e) => items.push({ type: 'ইভেন্ট', title: e.title, desc: e.excerpt, href: e.url || R('event.html?slug=' + e.slug) }));
    (d.news || []).forEach((n) => items.push({ type: 'সংবাদ', title: n.title, desc: n.excerpt, href: n.url || R('news-article.html?slug=' + n.slug) }));
    (d.teachers || []).forEach((t) => items.push({ type: 'শিক্ষক', title: t.name + ' — ' + t.subject, desc: t.designation, href: t.url || R('teacher.html?slug=' + t.slug) }));
    (d.downloads || []).forEach((f) => items.push({ type: 'ডাউনলোড', title: f.name, desc: d.downloadCats ? (d.downloadCats[f.category] || '') : (f.category || ''), href: f.url || R('downloads.html') }));
    return items;
  }
  function renderSearchOverlay() {
    if ($('#search-overlay')) return;
    const o = document.createElement('div');
    o.id = 'search-overlay'; o.className = 'search-overlay';
    o.innerHTML = '<div class="search-box" role="dialog" aria-label="Site search"><div class="search-input-row">' + IC.search
      + '<input id="search-input" type="search" placeholder="নোটিশ, ইভেন্ট, শিক্ষক, সংবাদ খুঁজুন…" aria-label="Search site">'
      + '<button class="icon-btn" id="search-close" aria-label="Close">' + IC.close + '</button></div>'
      + '<div class="search-results" id="search-results"><div class="empty-state" style="border:0"><p class="mb-2">কিছু লিখে খোঁজা শুরু করুন</p></div></div></div>';
    document.body.appendChild(o);
    $('#search-close').addEventListener('click', closeSearch);
    o.addEventListener('click', (e) => { if (e.target === o) closeSearch(); });
    $('#search-input').addEventListener('input', (e) => {
      const q = e.target.value.trim();
      const box = $('#search-results');
      if (q.length < 2) { box.innerHTML = '<div class="empty-state" style="border:0"><p>কমপক্ষে ২ অক্ষর লিখুন</p></div>'; return; }
      const hits = searchIndex().filter((i) => (i.title + ' ' + i.desc).includes(q)).slice(0, 8);
      box.innerHTML = hits.length
        ? hits.map((h) => '<a class="search-hit" href="' + h.href + '"><span class="tag tag-general">' + esc(h.type) + '</span><span><strong>' + esc(h.title) + '</strong><br><span class="muted small">' + esc(h.desc).slice(0, 90) + '</span></span></a>').join('')
        : '<div class="empty-state" style="border:0"><h3>কোনো ফলাফল পাওয়া যায়নি</h3><p>অন্য কীওয়ার্ড দিয়ে চেষ্টা করুন</p></div>';
    });
  }
  function openSearch() { const o = $('#search-overlay'); if (o) { o.classList.add('open'); setTimeout(() => $('#search-input').focus(), 60); } }
  function closeSearch() { const o = $('#search-overlay'); if (o) o.classList.remove('open'); }

  /* ---------- Scroll interactions ---------- */
  function initReveal() {
    const els = $$('.reveal');
    if (!('IntersectionObserver' in window) || !els.length) { els.forEach((e) => e.classList.add('in')); return; }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { threshold: 0.12 });
    els.forEach((e) => io.observe(e));
  }
  function initCounters() {
    const nums = $$('[data-count]');
    if (!nums.length) return;
    const animate = (el) => {
      const target = parseFloat(el.dataset.count);
      const dur = 1400, t0 = performance.now();
      const step = (t) => {
        const p = Math.min((t - t0) / dur, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = toBn(Math.round(target * eased).toLocaleString('en-US'));
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    if (!('IntersectionObserver' in window)) { nums.forEach(animate); return; }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) { animate(en.target); io.unobserve(en.target); } });
    }, { threshold: 0.4 });
    nums.forEach((e) => io.observe(e));
  }

  /* ---------- Generic tabs ---------- */
  function initTabs() {
    $$('[data-tabs]').forEach((wrap) => {
      const btns = $$('[data-tab]', wrap);
      const panels = $$('[data-panel]', wrap);
      btns.forEach((b) => b.addEventListener('click', () => {
        btns.forEach((x) => { x.classList.remove('active'); x.setAttribute('aria-selected', 'false'); });
        b.classList.add('active'); b.setAttribute('aria-selected', 'true');
        const key = b.dataset.tab;
        panels.forEach((p) => p.classList.toggle('hide', p.dataset.panel !== key));
        wrap.dispatchEvent(new CustomEvent('tabchange', { detail: key }));
      }));
    });
  }

  /* ---------- Carousels ---------- */
  function initCarousels() {
    $$('[data-carousel]').forEach((car) => {
      const prev = car.parentElement.querySelector('[data-car-prev]');
      const next = car.parentElement.querySelector('[data-car-next]');
      const step = () => Math.min(car.clientWidth * 0.8, 360);
      if (prev) prev.addEventListener('click', () => car.scrollBy({ left: -step(), behavior: 'smooth' }));
      if (next) next.addEventListener('click', () => car.scrollBy({ left: step(), behavior: 'smooth' }));
    });
  }

  /* ---------- FAQ accordion ---------- */
  function initAccordion(scope) {
    $$('.acc-item', scope || document).forEach((item) => {
      const q = $('.acc-q', item), a = $('.acc-a', item);
      if (!q || q.dataset.bound) return;
      q.dataset.bound = '1';
      q.setAttribute('aria-expanded', 'false');
      q.addEventListener('click', () => {
        const open = item.classList.toggle('open');
        q.setAttribute('aria-expanded', open);
        a.style.maxHeight = open ? a.scrollHeight + 'px' : '0px';
      });
    });
  }

  /* ---------- Lightbox ---------- */
  const LB = { imgs: [], i: 0 };
  function openLightbox(imgs, i, cap) {
    LB.imgs = imgs; LB.i = i || 0;
    let lb = $('#lightbox');
    if (!lb) {
      lb = document.createElement('div');
      lb.id = 'lightbox'; lb.className = 'lightbox'; lb.setAttribute('role', 'dialog'); lb.setAttribute('aria-label', 'Image viewer');
      lb.innerHTML = '<button class="lb-btn lb-close" aria-label="Close">' + IC.close + '</button>'
        + '<button class="lb-btn lb-prev" aria-label="Previous">' + IC.left + '</button>'
        + '<img id="lb-img" alt=""><button class="lb-btn lb-next" aria-label="Next">' + IC.right + '</button>'
        + '<div class="lb-cap" id="lb-cap"></div><div class="lb-count" id="lb-count"></div>';
      document.body.appendChild(lb);
      $('.lb-close', lb).addEventListener('click', closeLightbox);
      $('.lb-prev', lb).addEventListener('click', (e) => { e.stopPropagation(); navLB(-1); });
      $('.lb-next', lb).addEventListener('click', (e) => { e.stopPropagation(); navLB(1); });
      lb.addEventListener('click', (e) => { if (e.target === lb) closeLightbox(); });
      document.addEventListener('keydown', (e) => {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navLB(-1);
        if (e.key === 'ArrowRight') navLB(1);
      });
    }
    lb.dataset.cap = cap || '';
    renderLB();
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function renderLB() {
    const lb = $('#lightbox');
    const _u = LB.imgs[LB.i] || '';
    $('#lb-img').src = /^(https?:|data:|blob:|\/)/.test(_u) ? _u : root() + _u;
    $('#lb-img').alt = lb.dataset.cap || ('ছবি ' + (LB.i + 1));
    $('#lb-cap').textContent = lb.dataset.cap || '';
    $('#lb-count').textContent = (LB.i + 1) + ' / ' + LB.imgs.length;
  }
  function navLB(d) { LB.i = (LB.i + d + LB.imgs.length) % LB.imgs.length; renderLB(); }
  function closeLightbox() { const lb = $('#lightbox'); if (lb) { lb.classList.remove('open'); document.body.style.overflow = ''; } }
  function initLightboxTriggers(scope) {
    $$('[data-lightbox]', scope || document).forEach((el) => {
      if (el.dataset.bound) return;
      el.dataset.bound = '1';
      el.addEventListener('click', (e) => {
        e.preventDefault();
        const group = el.dataset.lightbox;
        const siblings = $$('[data-lightbox="' + group + '"]');
        const imgs = siblings.map((s) => s.dataset.full || s.getAttribute('href') || (s.querySelector('img') || {}).src || '');
        openLightbox(imgs, siblings.indexOf(el), el.dataset.caption || el.getAttribute('title') || '');
      });
    });
  }

  /* ---------- Countdown ---------- */
  function initCountdown() {
    const el = $('#countdown');
    if (!el || !D().admissionInfo) return;
    const rawDl = String((D().admissionInfo || {}).deadline || '');
    const normDl = rawDl.replace(/[০-৯]/g, (d) => '০১২৩৪৫৬৭৮৯'.indexOf(d));
    const target = new Date(normDl).getTime();
    const cells = { d: $('[data-cd="d"]', el), h: $('[data-cd="h"]', el), m: $('[data-cd="m"]', el), s: $('[data-cd="s"]', el) };
    const tick = () => {
      let diff = Math.max(0, target - Date.now());
      const dd = Math.floor(diff / 864e5); diff -= dd * 864e5;
      const hh = Math.floor(diff / 36e5); diff -= hh * 36e5;
      const mm = Math.floor(diff / 6e4); diff -= mm * 6e4;
      const ss = Math.floor(diff / 1e3);
      if (cells.d) cells.d.textContent = toBn(dd);
      if (cells.h) cells.h.textContent = toBn(hh);
      if (cells.m) cells.m.textContent = toBn(mm);
      if (cells.s) cells.s.textContent = toBn(ss);
    };
    tick();
    setInterval(tick, 1000);
  }

  /* ---------- Person photo (photo or monogram fallback) ---------- */
  function personPhoto(p, cls) {
    cls = cls || '';
    if (p.photo) {
      const src = typeof p.photo === 'string' ? p.photo : p.photo.img;
      const pos = (p.photo && p.photo.pos) || 'full';
      return '<span class="p-photo ' + pos + ' ' + cls + '"><img src="' + R(src) + '" alt="' + esc(p.name || p.n || '') + '" loading="lazy"></span>';
    }
    return '<span class="avatar ' + cls + '" style="background:' + (p.bg || '#0B4EA8') + '">' + esc(initials(p.name || p.n || '')) + '</span>';
  }

  /* ---------- Query helpers ---------- */

  function qs(name) { return new URLSearchParams(location.search).get(name); }

  /* ---------- Auto-fit text: fixed width, text zooms to fit ---------- */
  // Elements with [data-autofit] shrink (never grow past CSS size) so long
  // labels stay inside their padded box on any screen. Mirrors fitBrandName.
  function fitText(el) {
    if (!el || !el.getClientRects().length) return;
    const max = parseFloat(el.dataset.autofit) || parseFloat(getComputedStyle(el).fontSize) || 16;
    const min = parseFloat(el.dataset.autofitMin) || 11;
    const box = el.clientWidth;
    if (box <= 0) return;
    el.style.fontSize = max + 'px';
    if (el.scrollWidth <= box + 1) return;
    let lo = min, hi = max;
    while (hi - lo > 0.5) {
      const mid = (lo + hi) / 2;
      el.style.fontSize = mid + 'px';
      if (el.scrollWidth > box + 1) hi = mid; else lo = mid;
    }
    el.style.fontSize = lo + 'px';
  }
  function fitAll(scope) {
    $$('[data-autofit]', scope || document).forEach(fitText);
  }
  function bindAutofit() {
    if (bindAutofit.done) return;
    bindAutofit.done = true;
    let t = null;
    window.addEventListener('resize', () => { clearTimeout(t); t = setTimeout(() => fitAll(), 120); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => fitAll());
    window.addEventListener('load', () => fitAll());
  }

  /* ---------- Boot ---------- */
  document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.lang = lang() === 'bn' ? 'bn' : 'en';
    document.body.setAttribute('lang', lang());
    renderChrome();
    bindBrandFit();
    bindAutofit(); fitAll();
    initReveal(); initCounters(); initTabs(); initCarousels();
    initAccordion(); initLightboxTriggers(); initCountdown();
    if (window.AB_PAGE && typeof window.AB_PAGE.init === 'function') {
      try { window.AB_PAGE.init(); } catch (err) { console.error('Page init error:', err); }
    }
    initReveal(); initCounters(); initAccordion(); initLightboxTriggers(); initCarousels();
  });

  window.AB = { toBn, esc, $, $$, toast, initials, IC, root: R, qs, setLang, lang, openLightbox, initAccordion, initLightboxTriggers, renderSearchOverlay, personPhoto, fitText, fitAll };
})();
