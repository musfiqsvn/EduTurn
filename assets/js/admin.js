/* EduTurn — Dashboard tooling: color pickers, media buttons, section sorter. */
jQuery(function ($) {
  if ($.fn.wpColorPicker) { $('.eduturn-color').wpColorPicker(); }

  /* Generic media picker (dashboard fields + SMS entity photo). */
  var frame = null;
  $(document).on('click', '.eduturn-media-btn', function (e) {
    e.preventDefault();
    var btn = $(this);
    frame = wp.media({ title: 'মিডিয়া বেছে নিন', button: { text: 'ব্যবহার করুন' }, multiple: false });
    frame.on('select', function () {
      var a = frame.state().get('selection').first().toJSON();
      btn.siblings('.eduturn-media-id').val(a.id);
      var prev = btn.siblings('.eduturn-media-prev');
      if (prev.length && a.sizes && a.sizes.thumbnail) {
        prev.html('<img src="' + a.sizes.thumbnail.url + '" style="max-width:80px;height:auto;display:block;margin-bottom:6px">');
      }
      btn.siblings('.eduturn-media-clear').show();
    });
    frame.open();
  });
  $(document).on('click', '.eduturn-media-clear', function (e) {
    e.preventDefault();
    $(this).siblings('.eduturn-media-id').val('');
    $(this).siblings('.eduturn-media-prev').empty();
  });

  /* Homepage section sorter -> JSON order payload. */
  var sort = $('#eduturn-section-sorter');
  if (sort.length && $.fn.sortable) {
    var sync = function () {
      var order = [];
      sort.find('li').each(function () {
        order.push({ id: $(this).data('id'), visible: $(this).find('.eduturn-sec-vis').prop('checked') ? 1 : 0 });
      });
      $('#eduturn-sections-order').val(JSON.stringify(order));
    };
    sort.sortable({ handle: '.dashicons-move', update: sync });
    sort.on('change', '.eduturn-sec-vis', sync);
    sort.closest('form').on('submit', sync);
    sync();
  }
});
