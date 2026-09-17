/* EduTurn 1.13 — shell interactions: modals, toasts, confirms, drawer, live editor. */
jQuery(function ($) {
	/* ---------- toast ---------- */
	function toast(msg, cls) {
		var box = $('#utx-toasts');
		if (!box.length || !msg) return;
		var t = $('<div class="utx-toast ' + (cls || '') + '"></div>').text(msg);
		box.append(t);
		setTimeout(function () { t.fadeOut(300, function () { t.remove(); }); }, 4200);
	}
	try {
		var q = new URLSearchParams(window.location.search);
		if (q.get('uturn_toast')) toast(q.get('uturn_toast'), 'ok');
	} catch (e) {}

	/* ---------- modal ---------- */
	function fillModal(modal, data) {
		data = data || {};
		modal.find('input[name=sid],input[name=gid]').val(data.id || 0);
		modal.find('input[name=name]').val(data.name || '');
		modal.find('input[name=code]').val(data.code || '');
		modal.find('input[name=desc]').val(data.desc || '');
		modal.find('input[name=full]').val(data.full || 100);
		modal.find('input[name=pass]').val((data.pass === 0 || data.pass) ? data.pass : 33);
		modal.find('input[name=ord]').val(data.ord || 0);
		modal.find('input[name=active]').prop('checked', data.id ? !!data.active : true);
		var g = data.group_ids || [], sb = data.subject_ids || [];
		modal.find('input[name="group_ids[]"]').each(function () { this.checked = g.indexOf(parseInt(this.value, 10)) > -1; });
		modal.find('input[name="subject_ids[]"]').each(function () { this.checked = sb.indexOf(parseInt(this.value, 10)) > -1; });
		modal.find('.utx-modal-head h3').text((modal.attr('id') === 'utx-group-modal' ? '🏛️ ' : '📚 ') + (data.id ? 'সম্পাদনা' : 'নতুন যোগ'));
	}
	$(document).on('click', '[data-utx-modal]', function (e) {
		e.preventDefault();
		var m = $('#' + $(this).data('utx-modal'));
		if (!m.length) return;
		var d = {};
		try { d = JSON.parse($(this).attr('data-utx-edit') || '{}'); } catch (e2) {}
		fillModal(m, d);
		m.prop('hidden', false);
		m.find('input[name=name]').trigger('focus');
	});
	function closeModal(m) { m.prop('hidden', true); }
	$(document).on('click', '[data-utx-close]', function () { closeModal($(this).closest('.utx-modal')); });
	$(document).on('click', '.utx-modal', function (e) { if (e.target === this) closeModal($(this)); });
	$(document).on('keydown', function (e) { if (e.key === 'Escape') $('.utx-modal').prop('hidden', true); });

	/* ---------- pro confirm ---------- */
	$(document).on('click', '[data-confirm]', function (e) {
		e.preventDefault();
		var href = $(this).attr('href'), msg = $(this).data('confirm');
		var m = $('<div class="utx-modal"><div class="utx-modal-box utx-confirm-box"><div class="utx-modal-head"><h3>⚠️ নিশ্চিত করুন</h3></div><p></p><div class="utx-modal-foot" style="justify-content:center"><button type="button" class="button" data-x>বাতিল</button> <a class="button button-primary" href="' + href + '">হ্যাঁ, চালিয়ে যান</a></div></div></div>');
		m.find('p').text(msg);
		m.on('click', '[data-x]', function () { m.remove(); });
		m.on('click', function (ev) { if (ev.target === m[0]) m.remove(); });
		$('body').append(m);
	});

	/* ---------- mobile drawer ---------- */
	$(document).on('click', '[data-utx-burger]', function () { $('body').toggleClass('utx-nav-open'); });
	$(document).on('click', '[data-utx-scrim]', function () { $('body').removeClass('utx-nav-open'); });

	/* ---------- visual editor ---------- */
	var edForm = $('.utx-ed-left');
	if (edForm.length) {
		var frame = $('#utx-ed-frame'), wrap = $('.utx-ed-framewrap'), saveTimer = null, saving = false, dirty = false;
		function bustFrame() {
			if (!frame.length) return;
			var src = frame.attr('src').split('?')[0];
			frame.attr('src', src + '?uturn_preview=' + Date.now());
		}
		function autoSave() {
			if (saving) { dirty = true; return; }
			saving = true;
			$.ajax({
				url: edForm.attr('action'), method: 'POST', data: edForm.serialize(),
				complete: function () {
					saving = false;
					bustFrame();
					if (dirty) { dirty = false; autoSave(); }
				}
			});
		}
		function queueSave() { clearTimeout(saveTimer); saveTimer = setTimeout(autoSave, 1200); }
		edForm.on('input change', '.utx-ed-field, input[name^=sec_vis], input[name^=sec_add]', queueSave);
		/* media-picker selections don't fire change — watch preview nodes */
		if (window.MutationObserver) {
			var mo = new MutationObserver(queueSave);
			edForm.find('.eduturn-media-prev').each(function () { mo.observe(this, { childList: true }); });
		}
		/* visibility styling */
		edForm.find('.utx-ed-sec').each(function () {
			var s = $(this), eye = s.find('.utx-eye input');
			s.toggleClass('hidden-sec', !eye.prop('checked'));
		});
		edForm.on('change', '.utx-eye input', function () {
			$(this).closest('.utx-ed-sec').toggleClass('hidden-sec', !this.checked);
		});
		/* reorder: swap pos values + DOM order, then autosave */
		edForm.on('click', '[data-ed-move]', function () {
			var sec = $(this).closest('.utx-ed-sec');
			var other = $(this).data('ed-move') === 'up' ? sec.prev('.utx-ed-sec') : sec.next('.utx-ed-sec');
			if (!other.length) return;
			var a = sec.find('input[name^=sec_pos]'), b = other.find('input[name^=sec_pos]');
			var t = a.val(); a.val(b.val()); b.val(t);
			if ($(this).data('ed-move') === 'up') sec.insertBefore(other); else sec.insertAfter(other);
			queueSave();
		});
		/* device modes */
		$('[data-device]').on('click', function () {
			$('[data-device]').removeClass('on');
			$(this).addClass('on');
			wrap.removeClass('tablet mobile');
			if ($(this).data('device') !== 'desktop') wrap.addClass($(this).data('device'));
		});
	}
});
