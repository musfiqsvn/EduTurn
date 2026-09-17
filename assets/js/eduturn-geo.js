/* ============================================================
   EduTurn — Live geo-location tracker (topbar).
   Consent-safe: only runs when geolocation permission is already
   granted (never prompts on page load). Reverse-geocodes via the
   keyless BigDataCloud client API with Bengali locality names.
   Graceful no-op on deny / error / offline.
   ============================================================ */
(function () {
  'use strict';
  var chip = document.getElementById('topbar-geo');
  if (!chip || !('geolocation' in navigator)) return;
  var label = chip.querySelector('[data-geo-text]');
  function show(txt) {
    if (label) label.textContent = txt;
    chip.hidden = false;
  }
  try {
    var cached = sessionStorage.getItem('uturn_geo');
    if (cached) { show(cached); return; }
  } catch (e) { /* private mode */ }
  function locate() {
    navigator.geolocation.getCurrentPosition(function (pos) {
      var url = 'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude='
        + pos.coords.latitude + '&longitude=' + pos.coords.longitude + '&localityLanguage=bn';
      fetch(url).then(function (r) { return r.json(); }).then(function (j) {
        var city = j.city || j.locality || j.principalSubdivision || '';
        if (city) {
          try { sessionStorage.setItem('uturn_geo', city); } catch (e) {}
          show(city);
        }
      }).catch(function () { /* stay hidden */ });
    }, function () { /* denied: stay hidden */ }, { timeout: 8000, maximumAge: 3600000 });
  }
  if (navigator.permissions && navigator.permissions.query) {
    navigator.permissions.query({ name: 'geolocation' }).then(function (st) {
      if ('granted' === st.state) locate();
    }).catch(function () {});
  }
})();
