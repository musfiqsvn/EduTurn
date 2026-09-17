<?php
/**
 * Template Name: Apply (ভর্তি আবেদন)
 * 6-step admission wizard -> ut_application CPT via admin-post.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$ref = isset( $_GET['app'] ) ? sanitize_text_field( wp_unslash( $_GET['app'] ) ) : '';
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'ভর্তি আবেদন', 'Admission Application' ) ); ?></span></nav>

  <?php if ( $ref ) : ?>
    <div class="wizard-step active print-doc" style="text-align:center;padding:56px 24px">
      <div class="print-head"><b><?php echo esc_html( 'en' === uturn_lang() ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?></b><span><?php echo esc_html( uturn_opt( 'address' ) ); ?></span><i><?php echo esc_html( uturn_t( 'ভর্তি আবেদন রসিদ', 'Admission Application Receipt' ) ); ?></i></div>
      <div style="font-size:64px">✅</div>
      <h1 style="color:var(--primary)"><?php echo esc_html( uturn_t( 'আবেদন সফলভাবে জমা হয়েছে!', 'Application Submitted Successfully!' ) ); ?></h1>
      <p class="muted"><?php echo esc_html( uturn_t( 'আপনার রেফারেন্স নম্বরটি সংরক্ষণ করুন — ভর্তি সংক্রান্ত যেকোনো প্রয়োজনে এটি লাগবে।', 'Please save your reference number — you will need it for anything admission-related.' ) ); ?></p>
      <p style="font-size:28px;font-weight:800;letter-spacing:2px;background:#eef4ff;border:2px dashed var(--primary);border-radius:12px;display:inline-block;padding:10px 28px"><?php echo esc_html( $ref ); ?></p>
      <p class="muted"><?php echo esc_html( uturn_t( 'আমাদের ভর্তি টিম শীঘ্রই আপনার মোবাইলে যোগাযোগ করবে।', 'Our admission team will call your mobile soon.' ) ); ?></p>
      <p><button class="btn" onclick="window.print()"><?php echo esc_html( uturn_t( '🖨️ প্রিন্ট করুন', '🖨️ Print' ) ); ?></button> <a class="btn btn-outline" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোমে ফিরুন', 'Back to Home' ) ); ?></a></p>
    </div>
  <?php else : ?>
  <div class="apply-layout">
    <div>
      <div class="page-head">
        <h1><?php echo esc_html( uturn_t( 'অনলাইন ভর্তি আবেদন', 'Online Admission Application' ) ); ?> <span class="badge-soft"><?php echo esc_html( uturn_opt( 'adm_session' ) ); ?></span></h1>
        <p class="muted"><?php echo esc_html( uturn_t( 'মাত্র ৫টি ধাপে আবেদন সম্পন্ন করুন — ছবি ও প্রতিটি ডকুমেন্ট সর্বোচ্চ ১০০KB।', 'Complete in just 5 steps — photo and each document max 100KB.' ) ); ?></p>
      </div>
      <ol class="steps" id="applySteps">
        <li class="active" data-goto="1"><span class="step-n"><?php echo esc_html( 'en' === uturn_lang() ? '1' : '১' ); ?></span> <?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?></li>
        <li data-goto="2"><span class="step-n"><?php echo esc_html( 'en' === uturn_lang() ? '2' : '২' ); ?></span> <?php echo esc_html( uturn_t( 'শিক্ষার্থী', 'Student' ) ); ?></li>
        <li data-goto="3"><span class="step-n"><?php echo esc_html( 'en' === uturn_lang() ? '3' : '৩' ); ?></span> <?php echo esc_html( uturn_t( 'অভিভাবক', 'Guardian' ) ); ?></li>
        <li data-goto="4"><span class="step-n"><?php echo esc_html( 'en' === uturn_lang() ? '4' : '৪' ); ?></span> <?php echo esc_html( uturn_t( 'একাডেমিক', 'Academic' ) ); ?></li>
        <li data-goto="5"><span class="step-n"><?php echo esc_html( 'en' === uturn_lang() ? '5' : '৫' ); ?></span> <?php echo esc_html( uturn_t( 'কাগজপত্র ও নিশ্চিত', 'Documents & Confirm' ) ); ?></li>
      </ol>
      <form id="applyForm" class="wizard" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="action" value="uturn_apply">
        <?php wp_nonce_field( 'uturn_apply', 'uturn_apply_nonce' ); ?>
        <input type="text" name="a_web" value="" style="position:absolute;left:-9999px" tabindex="-1" autocomplete="off" aria-hidden="true">

        <section class="wizard-step active" data-step="1">
          <h2><?php echo esc_html( uturn_t( '১. ভর্তিচ্ছু শ্রেণি নির্বাচন', '1. Select Admission Class' ) ); ?></h2>
          <div class="class-grid">
            <?php foreach ( uturn_apply_classes() as $c ) : ?>
              <label class="class-pill"><input type="radio" name="s-class" value="<?php echo esc_attr( $c ); ?>"> <?php echo esc_html( $c ); ?></label>
            <?php endforeach; ?>
          </div>
          <p class="err" id="e-class"><?php echo esc_html( uturn_t( '⚠️ একটি শ্রেণি নির্বাচন করুন', '⚠️ Please select a class' ) ); ?></p>
          <div class="wizard-nav"><span></span><button type="button" class="btn" data-next><?php echo esc_html( uturn_t( 'পরবর্তী →', 'Next →' ) ); ?></button></div>
        </section>

        <section class="wizard-step" data-step="2">
          <h2><?php echo esc_html( uturn_t( '২. শিক্ষার্থীর তথ্য', '2. Student Information' ) ); ?></h2>
          <div class="photo-row">
            <img id="photoPrev" class="photo-prev" src="<?php echo esc_url( UTURN_URI . '/assets/images/logo.svg' ); ?>" alt="<?php echo esc_attr( uturn_t( 'ছবি প্রিভিউ', 'Photo preview' ) ); ?>">
            <div>
              <label class="btn btn-outline" for="s-photo"><?php echo esc_html( uturn_t( '📷 ছবি আপলোড (≤১০০KB)', '📷 Upload Photo (≤100KB)' ) ); ?></label>
              <input type="file" id="s-photo" name="s-photo" accept="image/*" hidden required>
              <p class="muted small"><?php echo esc_html( uturn_t( 'পাসপোর্ট সাইজ, JPG/PNG — ১০০KB এর মধ্যে', 'Passport size, JPG/PNG — within 100KB' ) ); ?></p>
              <p class="err" id="e-photo"><?php echo esc_html( uturn_t( '⚠️ ছবি আবশ্যক (সর্বোচ্চ ১০০KB)', '⚠️ Photo required (max 100KB)' ) ); ?></p>
            </div>
          </div>
          <div class="form-grid">
            <div class="field"><label for="s-name"><?php echo esc_html( uturn_t( 'শিক্ষার্থীর নাম (বাংলা) *', 'Student Name (Bangla) *' ) ); ?></label><input id="s-name" name="s-name" required minlength="3" placeholder="যেমন: আরিফ হোসেন"><p class="err" id="e-name"><?php echo esc_html( uturn_t( '⚠️ সঠিক নাম লিখুন (কমপক্ষে ৩ অক্ষর)', '⚠️ Enter a valid name (min 3 characters)' ) ); ?></p></div>
            <div class="field"><label for="s-name-en"><?php echo esc_html( uturn_t( 'নাম (ইংরেজিতে)', 'Name (in English)' ) ); ?></label><input id="s-name-en" name="s-name-en" placeholder="ARIF HOSSAIN"></div>
            <div class="field"><label for="s-dob"><?php echo esc_html( uturn_t( 'জন্ম তারিখ *', 'Date of Birth *' ) ); ?></label><input id="s-dob" name="s-dob" type="date" required max="2021-12-31"><p class="err" id="e-dob"><?php echo esc_html( uturn_t( '⚠️ জন্ম তারিখ দিন', '⚠️ Enter date of birth' ) ); ?></p></div>
            <div class="field"><label for="s-gender"><?php echo esc_html( uturn_t( 'লিঙ্গ *', 'Gender *' ) ); ?></label><select id="s-gender" name="s-gender" required data-attr="gender"><option value=""><?php echo esc_html( uturn_t( 'নির্বাচন করুন', 'Select' ) ); ?></option><option><?php echo esc_html( uturn_t( 'ছেলে', 'Boy' ) ); ?></option><option><?php echo esc_html( uturn_t( 'মেয়ে', 'Girl' ) ); ?></option></select><p class="err" id="e-gender"><?php echo esc_html( uturn_t( '⚠️ লিঙ্গ নির্বাচন করুন', '⚠️ Select gender' ) ); ?></p></div>
            <div class="field"><label for="s-birthreg"><?php echo esc_html( uturn_t( 'জন্ম নিবন্ধন নম্বর', 'Birth Registration No.' ) ); ?></label><input id="s-birthreg" name="s-birthreg" inputmode="numeric" placeholder="<?php echo esc_attr( uturn_t( '১৭ সংখ্যা', '17 digits' ) ); ?>"></div>
            <div class="field"><label for="s-blood"><?php echo esc_html( uturn_t( 'রক্তের গ্রুপ', 'Blood Group' ) ); ?></label><select id="s-blood" name="s-blood" data-attr="blood"><option value=""><?php echo esc_html( uturn_t( 'নির্বাচন করুন', 'Select' ) ); ?></option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>O+</option><option>O-</option><option>AB+</option><option>AB-</option></select></div>
          </div>
          <div class="field"><label for="s-address"><?php echo esc_html( uturn_t( 'বর্তমান ঠিকানা *', 'Present Address *' ) ); ?></label><textarea id="s-address" name="s-address" rows="2" required minlength="5" placeholder="<?php echo esc_attr( uturn_t( 'বাড়ি, রোড, এলাকা, থানা, জেলা', 'House, road, area, thana, district' ) ); ?>"></textarea><p class="err" id="e-address"><?php echo esc_html( uturn_t( '⚠️ সম্পূর্ণ ঠিকানা লিখুন', '⚠️ Enter the full address' ) ); ?></p></div>
          <div class="wizard-nav"><button type="button" class="btn btn-outline" data-prev><?php echo esc_html( uturn_t( '← পূর্ববর্তী', '← Previous' ) ); ?></button><button type="button" class="btn" data-next>পরবর্তী →</button></div>
        </section>

        <section class="wizard-step" data-step="3">
          <h2><?php echo esc_html( uturn_t( '৩. অভিভাবকের তথ্য', '3. Guardian Information' ) ); ?></h2>
          <div class="form-grid">
            <div class="field"><label for="g-father"><?php echo esc_html( uturn_t( 'পিতার নাম *', 'Father\'s Name *' ) ); ?></label><input id="g-father" name="g-father" required minlength="3"><p class="err" id="e-father"><?php echo esc_html( uturn_t( '⚠️ পিতার নাম লিখুন', '⚠️ Enter father\'s name' ) ); ?></p></div>
            <div class="field"><label for="g-mother"><?php echo esc_html( uturn_t( 'মাতার নাম *', 'Mother\'s Name *' ) ); ?></label><input id="g-mother" name="g-mother" required minlength="3"><p class="err" id="e-mother"><?php echo esc_html( uturn_t( '⚠️ মাতার নাম লিখুন', '⚠️ Enter mother\'s name' ) ); ?></p></div>
            <div class="field"><label for="g-occupation"><?php echo esc_html( uturn_t( 'পেশা', 'Occupation' ) ); ?></label><input id="g-occupation" name="g-occupation" placeholder="<?php echo esc_attr( uturn_t( 'যেমন: ব্যবসা', 'e.g. Business' ) ); ?>"></div>
            <div class="field"><label for="g-mobile"><?php echo esc_html( uturn_t( 'মোবাইল নম্বর *', 'Mobile Number *' ) ); ?></label><input id="g-mobile" name="g-mobile" required inputmode="tel" placeholder="01XXXXXXXXX"><p class="err" id="e-mobile"><?php echo esc_html( uturn_t( '⚠️ সঠিক ১১ সংখ্যার মোবাইল নম্বর দিন', '⚠️ Enter a valid 11-digit mobile number' ) ); ?></p></div>
            <div class="field"><label for="g-email"><?php echo esc_html( uturn_t( 'ইমেইল (যদি থাকে)', 'Email (if any)' ) ); ?></label><input id="g-email" name="g-email" type="email" placeholder="you@example.com"><p class="err" id="e-email"><?php echo esc_html( uturn_t( '⚠️ সঠিক ইমেইল দিন', '⚠️ Enter a valid email' ) ); ?></p></div>
            <div class="field"><label for="g-relation"><?php echo esc_html( uturn_t( 'সম্পর্ক', 'Relation' ) ); ?></label><select id="g-relation" name="g-relation" data-attr="relation"><option><?php echo esc_html( uturn_t( 'পিতা', 'Father' ) ); ?></option><option><?php echo esc_html( uturn_t( 'মাতা', 'Mother' ) ); ?></option><option><?php echo esc_html( uturn_t( 'অন্যান্য', 'Other' ) ); ?></option></select></div>
          </div>
          <div class="wizard-nav"><button type="button" class="btn btn-outline" data-prev>← পূর্ববর্তী</button><button type="button" class="btn" data-next>পরবর্তী →</button></div>
        </section>

        <section class="wizard-step" data-step="4">
          <h2><?php echo esc_html( uturn_t( '৪. পূর্ববর্তী একাডেমিক তথ্য', '4. Previous Academic Info' ) ); ?></h2>
          <div class="form-grid">
            <div class="field"><label for="a-school"><?php echo esc_html( uturn_t( 'পূর্ববর্তী বিদ্যালয়', 'Previous School' ) ); ?></label><input id="a-school" name="a-school" placeholder="<?php echo esc_attr( uturn_t( 'বিদ্যালয়ের নাম', 'School name' ) ); ?>"></div>
            <div class="field"><label for="a-lastclass"><?php echo esc_html( uturn_t( 'সর্বশেষ শ্রেণি', 'Last Class' ) ); ?></label><input id="a-lastclass" name="a-lastclass" placeholder="<?php echo esc_attr( uturn_t( 'যেমন: ৫ম', 'e.g. Class 5' ) ); ?>"></div>
            <div class="field"><label for="a-result"><?php echo esc_html( uturn_t( 'সর্বশেষ ফলাফল', 'Last Result' ) ); ?></label><input id="a-result" name="a-result" placeholder="<?php echo esc_attr( uturn_t( 'যেমন: জিপিএ ৫.০০', 'e.g. GPA 5.00' ) ); ?>"></div>
            <div class="field"><label for="a-year"><?php echo esc_html( uturn_t( 'পাসের সন', 'Passing Year' ) ); ?></label><input id="a-year" name="a-year" inputmode="numeric" placeholder="<?php echo esc_attr( uturn_t( '২০২৫', '2025' ) ); ?>"></div>
          </div>
          <div class="wizard-nav"><button type="button" class="btn btn-outline" data-prev>← পূর্ববর্তী</button><button type="button" class="btn" data-next>পরবর্তী →</button></div>
        </section>

        <section class="wizard-step" data-step="5">
          <h2><?php echo esc_html( uturn_t( '৫. প্রয়োজনীয় কাগজপত্র', '5. Required Documents' ) ); ?></h2>
          <div class="doc-list">
            <label class="doc-item"><input type="checkbox" data-doc="birth" data-title="জন্ম নিবন্ধন"> <span><strong><?php echo esc_html( uturn_t( 'জন্ম নিবন্ধন সনদ', 'Birth Registration Certificate' ) ); ?></strong><small><?php echo esc_html( uturn_t( 'ছবি বা PDF (≤১০০KB)', 'Image or PDF (≤100KB)' ) ); ?></small></span><input type="file" class="doc-file" data-for="birth" name="f-birth" accept="image/*,.pdf" hidden></label>
            <label class="doc-item"><input type="checkbox" data-doc="result" data-title="পূর্ববর্তী ফলাফল"> <span><strong><?php echo esc_html( uturn_t( 'পূর্ববর্তী ফলাফল (মার্কশিট)', 'Previous Result (Marksheet)' ) ); ?></strong><small>ছবি বা PDF (≤১০০KB)</small></span><input type="file" class="doc-file" data-for="result" name="f-result" accept="image/*,.pdf" hidden></label>
            <label class="doc-item"><input type="checkbox" data-doc="nid" data-title="<?php echo esc_html( uturn_t( 'পিতা/মাতার এনআইডি', 'Parent\'s NID' ) ); ?>"> <span><strong>পিতা/মাতার এনআইডি</strong><small>ছবি বা PDF (≤১০০KB)</small></span><input type="file" class="doc-file" data-for="nid" name="f-nid" accept="image/*,.pdf" hidden></label>
          </div>
          <div id="docChips" class="doc-chips"></div>
          <p class="err" id="e-docs"><?php echo esc_html( uturn_t( '⚠️ কমপক্ষে ১টি ডকুমেন্ট সংযুক্ত করুন', '⚠️ Attach at least 1 document' ) ); ?></p>
          <h3><?php echo esc_html( uturn_t( 'পর্যালোচনা', 'Review' ) ); ?></h3>
          <dl class="review" id="summaryItems">
            <div><dt><?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?></dt><dd id="r-class">—</dd></div>
            <div><dt><?php echo esc_html( uturn_t( 'নাম', 'Name' ) ); ?></dt><dd id="r-name">—</dd></div>
            <div><dt><?php echo esc_html( uturn_t( 'জন্ম তারিখ', 'Date of Birth' ) ); ?></dt><dd id="r-dob">—</dd></div>
            <div><dt><?php echo esc_html( uturn_t( 'অভিভাবক', 'Guardian' ) ); ?></dt><dd id="r-guardian">—</dd></div>
            <div><dt><?php echo esc_html( uturn_t( 'মোবাইল', 'Mobile' ) ); ?></dt><dd id="r-mobile">—</dd></div>
          </dl>
          <ul id="docsPreviewList" class="docs-preview"></ul>
          <label class="agree"><input type="checkbox" id="agree"> <?php echo esc_html( uturn_t( 'আমি নিশ্চিত করছি যে উপরের তথ্যসমূহ সঠিক।', 'I confirm that the above information is correct.' ) ); ?></label>
          <p class="err" id="e-agree"><?php echo esc_html( uturn_t( '⚠️ সম্মতি দিন', '⚠️ Please agree' ) ); ?></p>
          <div class="wizard-nav"><button type="button" class="btn btn-outline" data-prev>← পূর্ববর্তী</button><button type="button" class="btn" id="finalSubmit"><?php echo esc_html( uturn_t( '✅ আবেদন জমা দিন', '✅ Submit Application' ) ); ?></button></div>
        </section>
      </form>

      <h2 style="margin-top:28px"><?php echo esc_html( uturn_t( 'ভর্তি বিষয়ক জিজ্ঞাসা', 'Admission FAQs' ) ); ?></h2>
      <div class="faq-list">
        <?php
        $faqs = new WP_Query( array( 'post_type' => 'ut_faq', 'posts_per_page' => 6, 'tax_query' => array( array( 'taxonomy' => 'ut_faq_cat', 'field' => 'slug', 'terms' => 'admission' ) ) ) );
        while ( $faqs->have_posts() ) : $faqs->the_post();
          ?>
          <details class="faq"><summary><?php the_title(); ?></summary><div class="faq-body"><?php the_content(); ?></div></details>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
    <aside>
      <div class="help-card">
        <h3><?php echo esc_html( uturn_t( 'সহায়তা প্রয়োজন?', 'Need Help?' ) ); ?></h3>
        <p>📞 <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', uturn_opt( 'phone' ) ) ); ?>"><?php echo esc_html( uturn_opt( 'phone' ) ); ?></a></p>
        <p class="muted small"><?php echo esc_html( uturn_opt( 'hours_short' ) ); ?></p>
        <a class="btn btn-outline" href="<?php echo esc_url( uturn_url( 'contact' ) ); ?>"><?php echo esc_html( uturn_t( 'যোগাযোগ করুন', 'Contact Us' ) ); ?></a>
      </div>
      <div class="help-card steps-card">
        <h3><?php echo esc_html( uturn_t( 'আবেদনের ধাপ', 'Application Steps' ) ); ?></h3>
        <ol class="mini-steps"><li><?php echo esc_html( uturn_t( 'ফরম পূরণ করুন', 'Fill in the form' ) ); ?></li><li><?php echo esc_html( uturn_t( 'ছবি ও কাগজপত্র আপলোড', 'Upload photo & documents' ) ); ?></li><li><?php echo esc_html( uturn_t( 'জমা দিয়ে রেফারেন্স সংরক্ষণ', 'Submit & save reference' ) ); ?></li><li><?php echo esc_html( uturn_t( 'ভর্তি পরীক্ষা / সাক্ষাৎকার', 'Admission test / interview' ) ); ?></li></ol>
      </div>
    </aside>
  </div>
  <?php endif; ?>
</div></main>

<?php if ( ! $ref ) : ?>
<script>
(function(){
  var cur=1,form=document.getElementById('applyForm'),errShown={};
  function show(n){cur=n;document.querySelectorAll('.wizard-step').forEach(function(s){s.classList.toggle('active',+s.dataset.step===n);});
    document.querySelectorAll('#applySteps li').forEach(function(li){var g=+li.dataset.goto;li.classList.toggle('active',g===n);li.classList.toggle('done',g<n);});
    window.scrollTo({top:0,behavior:'smooth'});}
  function bad(id,on){var e=document.getElementById(id);if(e)e.style.display=on?'block':'none';return !on;}
  function val(id){var el=document.getElementById(id);return el?el.value.trim():'';}
  function bn(s){return String(s).replace(/[০-৯]/g,function(d){return '০১২৩৪৫৬৭৮৯'.indexOf(d);});}
  var photoOk=false;
  document.getElementById('s-photo').addEventListener('change',function(){var f=this.files[0];photoOk=false;
    if(f&&f.size<=102400){photoOk=true;var r=new FileReader();r.onload=function(e){document.getElementById('photoPrev').src=e.target.result;};r.readAsDataURL(f);bad('e-photo',false);}
    else{bad('e-photo',true);this.value='';}});
  var docs={};
  document.querySelectorAll('.doc-item input[type=checkbox]').forEach(function(cb){cb.addEventListener('change',function(){
    var k=cb.dataset.doc;if(cb.checked){var fi=document.querySelector('.doc-file[data-for="'+k+'"]');if(fi)fi.click();else{docs[k]=cb.dataset.title;chips();}}
    else{delete docs[k];var fi2=document.querySelector('.doc-file[data-for="'+k+'"]');if(fi2)fi2.value='';chips();}});});
  document.querySelectorAll('.doc-file').forEach(function(fi){fi.addEventListener('change',function(){var k=fi.dataset.for,cb=document.querySelector('.doc-item input[data-doc="'+k+'"]');
    if(fi.files[0]&&fi.files[0].size>102400){fi.value='';if(cb)cb.checked=false;delete docs[k];bad('e-docs',true);chips();return;}
    if(fi.files[0]){docs[k]=(cb?cb.dataset.title:k)+(fi.files[0]?(' ('+fi.files[0].name+')'):'');}chips();bad('e-docs',false);});});
  function chips(){var w=document.getElementById('docChips');w.innerHTML='';Object.keys(docs).forEach(function(k){var s=document.createElement('span');s.className='chip';s.textContent='📎 '+docs[k];w.appendChild(s);});
    var ul=document.getElementById('docsPreviewList');ul.innerHTML='';Object.keys(docs).forEach(function(k){var li=document.createElement('li');li.textContent='📎 '+docs[k];ul.appendChild(li);});}
  function review(){document.getElementById('r-class').textContent=(form.querySelector('input[name=s-class]:checked')||{}).value||'—';
    document.getElementById('r-name').textContent=val('s-name')||'—';document.getElementById('r-dob').textContent=val('s-dob')||'—';
    document.getElementById('r-guardian').textContent=val('g-father')||'—';document.getElementById('r-mobile').textContent=val('g-mobile')||'—';}
  function valid(n){if(n===1){return bad('e-class',!form.querySelector('input[name=s-class]:checked'));}
    if(n===2){var ok=true;ok=bad('e-photo',!photoOk)&&ok;ok=bad('e-name',val('s-name').length<3)&&ok;ok=bad('e-dob',!val('s-dob'))&&ok;
      ok=bad('e-gender',!val('s-gender'))&&ok;ok=bad('e-address',val('s-address').length<5)&&ok;return ok;}
    if(n===3){var ok=true;ok=bad('e-father',val('g-father').length<3)&&ok;ok=bad('e-mother',val('g-mother').length<3)&&ok;
      var m=bn(val('g-mobile')).replace(/[\s\-+]/g,'');ok=bad('e-mobile',!/^(01\d{9}|8801\d{9}|1\d{9})$/.test(m))&&ok;
      var em=val('g-email');ok=bad('e-email',em&&!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(em))&&ok;return ok;}
    if(n===4){return true;}
    if(n===5){var ok=true;ok=bad('e-docs',Object.keys(docs).length<1)&&ok;ok=bad('e-agree',!document.getElementById('agree').checked)&&ok;return ok;}
    return true;}
  form.addEventListener('click',function(e){if(e.target.closest('[data-next]')){if(valid(cur)){if(cur===4)review();show(Math.min(5,cur+1));if(cur===5)review();}}
    if(e.target.closest('[data-prev]')){show(Math.max(1,cur-1));}});
  document.querySelectorAll('#applySteps li').forEach(function(li){li.addEventListener('click',function(){var g=+li.dataset.goto;if(g<cur)show(g);});});
  document.getElementById('finalSubmit').addEventListener('click',function(){review();if(valid(5))form.submit();});
})();
</script>
<?php endif; ?>
<?php get_footer(); ?>
