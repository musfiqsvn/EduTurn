<?php
/**
 * EduTurn — Home 19: Contact info + inbox form + map.
 */
?>
<section class="section" aria-labelledby="contact-h">
  <div class="container">
    <div class="sec-head reveal">
      <span class="eyebrow">যোগাযোগ করুন</span>
      <h2 id="contact-h">আমরা আপনার পাশে আছি</h2>
    </div>
    <div class="contact-grid">
      <div class="contact-info reveal" id="contact-info">
        <div class="c-item"><span class="cicon"><?php echo uturn_icon( 'pin' ); ?></span><div><strong>ঠিকানা</strong><p><?php echo esc_html( uturn_opt( 'address' ) ); ?></p></div></div>
        <div class="c-item"><span class="cicon"><?php echo uturn_icon( 'phone' ); ?></span><div><strong>ফোন</strong><p><a href="tel:<?php echo esc_attr( uturn_opt( 'phone_href' ) ); ?>"><?php echo esc_html( uturn_opt( 'phone' ) ); ?></a></p></div></div>
        <div class="c-item"><span class="cicon"><?php echo uturn_icon( 'mail' ); ?></span><div><strong>ইমেইল</strong><p><a href="mailto:<?php echo esc_attr( uturn_opt( 'email' ) ); ?>"><?php echo esc_html( uturn_opt( 'email' ) ); ?></a></p></div></div>
        <div class="c-item"><span class="cicon"><?php echo uturn_icon( 'clock' ); ?></span><div><strong>অফিস সময়</strong><p><?php echo esc_html( uturn_opt( 'hours' ) ); ?></p></div></div>
      </div>
      <div class="form-card reveal d1">
        <h3 class="mt-0">বার্তা পাঠান</h3>
        <form id="contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
          <input type="hidden" name="action" value="uturn_contact">
          <?php wp_nonce_field( 'uturn_contact', 'uturn_contact_nonce' ); ?>
          <input type="text" name="c_web" value="" style="display:none" tabindex="-1" autocomplete="off" aria-hidden="true">
          <div class="form-grid">
            <div class="field"><label for="c-name">আপনার নাম <span class="req">*</span></label><input id="c-name" name="name" required autocomplete="name"><span class="err">নাম লিখুন</span></div>
            <div class="field"><label for="c-phone">মোবাইল <span class="req">*</span></label><input id="c-phone" name="phone" type="tel" required placeholder="01XXXXXXXXX"><span class="err">সঠিক মোবাইল নম্বর দিন</span></div>
            <div class="field full"><label for="c-sub">বিষয়</label><input id="c-sub" name="subject" placeholder="যেমন: ভর্তি সংক্রান্ত তথ্য"></div>
            <div class="field full"><label for="c-msg">বার্তা <span class="req">*</span></label><textarea id="c-msg" name="message" required></textarea><span class="err">বার্তা লিখুন</span></div>
          </div>
          <button class="btn btn-primary mt-1" type="submit">বার্তা পাঠান</button>
        </form>
      </div>
    </div>
    <div class="map-frame reveal">
      <iframe title="স্কুলের অবস্থান মানচিত্র" src="<?php echo esc_url( uturn_opt( 'map_embed' ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
</section>
<script>
(function () {
  var form = document.getElementById('contact-form');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    var name = document.getElementById('c-name'), phone = document.getElementById('c-phone'), msg = document.getElementById('c-msg');
    var ok = true;
    var mark = function (el, valid) { el.closest('.field').classList.toggle('invalid', !valid); if (!valid) ok = false; };
    mark(name, name.value.trim().length >= 3);
    mark(phone, /^01[3-9]\d{8}$/.test(phone.value.trim()));
    mark(msg, msg.value.trim().length >= 5);
    if (!ok) { e.preventDefault(); if (window.AB && AB.toast) AB.toast('অনুগ্রহ করে সঠিক তথ্য দিন', 'error'); }
  });
})();
</script>
