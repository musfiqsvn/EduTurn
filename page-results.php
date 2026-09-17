<?php
/**
 * Template Name: Results (ফলাফল)
 * Exam result lookup via secure AJAX (single record per query).
 */
defined( 'ABSPATH' ) || exit;
get_header();
$years = function_exists( 'uturn_result_years' ) ? uturn_result_years() : array( '২০২৬', '২০২৫' );
$logo = function_exists( 'uturn_logo_url' ) ? uturn_logo_url() : ( UTURN_URI . '/assets/images/logo.svg' );
$ajax = admin_url( 'admin-ajax.php' );
$nonce = wp_create_nonce( 'uturn_result_lookup' );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'ফলাফল', 'Results' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'পরীক্ষার ফলাফল', 'Exam Results' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'পরীক্ষা, বছর, শ্রেণি ও রোল নম্বর — অথবা স্থায়ী স্টুডেন্ট ID — দিয়ে ফলাফল দেখুন', 'View results by exam, year, class and roll number — or by permanent Student ID' ) ); ?></p>
  </div>
  <div class="grid grid-results">
    <div class="card result-form">
      <h2>🔍 <?php echo esc_html( uturn_t( 'ফলাফল খুঁজুন', 'Find Result' ) ); ?></h2>
      <form id="resultForm" novalidate>
        <div class="field"><label for="r-exam"><?php echo esc_html( uturn_t( 'পরীক্ষা *', 'Exam *' ) ); ?></label>
          <select id="r-exam" required>
            <?php foreach ( uturn_exams( false ) as $slug => $bn ) : ?>
              <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $bn ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="r-year"><?php echo esc_html( uturn_t( 'বছর *', 'Year *' ) ); ?></label>
          <select id="r-year" required>
            <?php foreach ( $years as $y ) : ?>
              <option><?php echo esc_html( $y ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="r-class"><?php echo esc_html( uturn_t( 'শ্রেণি *', 'Class *' ) ); ?></label>
          <select id="r-class" required>
            <?php foreach ( uturn_result_classes() as $c ) : ?>
              <option<?php selected( $c, 'দশম', false ); ?>><?php echo esc_html( $c ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="r-roll"><?php echo esc_html( uturn_t( 'রোল নম্বর *', 'Roll Number *' ) ); ?></label><input id="r-roll" inputmode="numeric" placeholder="<?php echo esc_attr( uturn_t( 'যেমন: ১০১', 'e.g. 101' ) ); ?>" required><p class="err" id="e-roll">⚠️ <?php echo esc_html( uturn_t( 'রোল নম্বর দিন', 'Enter the roll number' ) ); ?></p></div>
        <div class="field"><label for="r-code"><?php echo esc_html( uturn_t( 'অথবা স্টুডেন্ট ID', 'Or Student ID' ) ); ?></label><input id="r-code" placeholder="STU-00001" autocomplete="off" style="text-transform:uppercase"></div>
        <button class="btn btn-block" type="submit"><?php echo esc_html( uturn_t( 'ফলাফল দেখুন', 'View Result' ) ); ?></button>
      </form>
    </div>
    <div class="card result-panel" id="resultWrap" aria-live="polite">
      <div class="result-empty" id="resultEmpty">
        <div style="font-size:54px">🎓</div>
        <h3><?php echo esc_html( uturn_t( 'ফলাফল দেখতে ফরম পূরণ করুন', 'Fill in the form to view the result' ) ); ?></h3>
        <p class="muted"><?php echo esc_html( uturn_t( 'বাম পাশের ফরমে তথ্য দিয়ে "ফলাফল দেখুন" চাপুন', 'Enter the details in the form and press "View Result"' ) ); ?></p>
      </div>
    </div>
  </div>
  <div class="grid grid-2" style="margin-top:24px">
    <div class="help-card">
      <h3><?php echo esc_html( uturn_t( 'সহায়তা প্রয়োজন?', 'Need Help?' ) ); ?></h3>
      <p>📞 <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', uturn_opt( 'phone' ) ) ); ?>"><?php echo esc_html( uturn_opt( 'phone' ) ); ?></a></p>
      <p class="muted small"><?php echo esc_html( uturn_opt( 'hours_short' ) ); ?></p>
      <a class="btn btn-outline" href="<?php echo esc_url( uturn_url( 'contact' ) ); ?>"><?php echo esc_html( uturn_t( 'যোগাযোগ করুন', 'Contact Us' ) ); ?></a>
    </div>
    <div class="card">
      <h3><?php echo esc_html( uturn_t( 'যেভাবে ফলাফল দেখবেন', 'How to View Results' ) ); ?></h3>
      <ol class="mini-steps"><li><?php echo esc_html( uturn_t( 'পরীক্ষা ও বছর নির্বাচন করুন', 'Select the exam and year' ) ); ?></li><li><?php echo esc_html( uturn_t( 'শ্রেণি ও রোল নম্বর লিখুন', 'Enter class and roll number' ) ); ?></li><li><?php echo esc_html( uturn_t( '"ফলাফল দেখুন" চাপুন', 'Press "View Result"' ) ); ?></li><li><?php echo esc_html( uturn_t( 'প্রয়োজনে প্রিন্ট করে সংরক্ষণ করুন', 'Print and save if needed' ) ); ?></li></ol>
    </div>
  </div>
  <h2 style="margin-top:28px"><?php echo esc_html( uturn_t( 'সাধারণ জিজ্ঞাসা', 'General FAQs' ) ); ?></h2>
  <div class="faq-list">
    <?php
    $faqs = new WP_Query( array( 'post_type' => 'ut_faq', 'posts_per_page' => 6, 'tax_query' => array( array( 'taxonomy' => 'ut_faq_cat', 'field' => 'slug', 'terms' => 'general' ) ) ) );
    while ( $faqs->have_posts() ) : $faqs->the_post();
      ?>
      <details class="faq"><summary><?php the_title(); ?></summary><div class="faq-body"><?php the_content(); ?></div></details>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <p class="muted small" style="margin-top:16px">📱 <?php echo esc_html( uturn_t( 'ফলাফল প্রকাশের সাথে সাথে SMS পেতে অফিসে আপনার মোবাইল নম্বর হালনাগাদ রাখুন।', 'Keep your mobile number updated at the office to get an SMS as soon as results publish.' ) ); ?></p>
</div></main>

<script>
(function(){
  var AJAX=<?php echo wp_json_encode( $ajax ); ?>;
  var NONCE=<?php echo wp_json_encode( $nonce ); ?>;
  var EXAMS=<?php echo wp_json_encode( uturn_exams( false ) ); ?>;
  var EN=<?php echo 'en' === uturn_lang() ? 'true' : 'false'; ?>;
  var SCHOOL=<?php echo wp_json_encode( uturn_lang() === 'en' ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?>;
  var LOGO=<?php echo wp_json_encode( $logo ); ?>;
  function esc(s){return String(s==null?'':s).split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;').split('"').join('&quot;');}
  function bn(d){return String(d).replace(/[0-9]/g,function(c){return '০১২৩৪৫৬৭৮৯'[c];});}
  function ord(m){m=parseInt(m,10);if(!m||m<1)return '—';if(EN){var s='th';if(m%100<11||m%100>13){if(m%10===1)s='st';else if(m%10===2)s='nd';else if(m%10===3)s='rd';}return m+s;}if(m===1)return '১ম';if(m===2)return '২য়';if(m===3)return '৩য়';if(m===4)return '৪র্থ';return bn(m)+'ম';}
  var form=document.getElementById('resultForm'),wrap=document.getElementById('resultWrap');
  function loading(){wrap.innerHTML='<div class="result-empty"><div style="font-size:54px">⏳</div><h3>'+(EN?'Searching…':'খোঁজা হচ্ছে…')+'</h3></div>';}
  function notFound(limited){wrap.innerHTML='<div class="result-empty"><div style="font-size:54px">🔍</div><h3>'+(EN?'Sorry, no result found':(limited?'অনেকবার চেষ্টা করেছেন — কিছুক্ষণ পর আবার চেষ্টা করুন':'দুঃখিত, ফলাফল পাওয়া যায়নি'))+'</h3><p class="muted">'+(EN?'Verify the details and try again, or contact the office.':'তথ্য যাচাই করে আবার চেষ্টা করুন, অথবা অফিসে যোগাযোগ করুন।')+'</p><button class="btn btn-outline" onclick="location.reload()">'+(EN?'Search again':'আবার খুঁজুন')+'</button></div>';}
  function show(hit){
    var rows='';for(var j=0;j<hit.subjects.length;j++){var s=hit.subjects[j];
      var mk=esc(s[1]);if(s[3]!==''&&s[3]!=null&&s[3]!=='100'){mk+='/'+esc(s[3]);}
      rows+='<tr><td>'+esc(s[0])+'</td><td>'+mk+'</td><td><span class="grade">'+esc(s[2])+'</span></td><td>'+esc(s[4]==null?'':s[4])+'</td></tr>';}
    var pass=hit.status!=='fail';
    wrap.innerHTML='<article class="result-card print-doc"><header class="rc-head"><img src="'+esc(LOGO)+'" alt="logo"><div><h3>'+esc(SCHOOL)+'</h3><p>'+esc(hit.examBn||EXAMS[hit.exam]||'')+' · '+esc(hit.year)+'</p></div>'
      +'<span class="rc-'+(pass?'pass':'fail')+'">'+(pass?(EN?'Passed':'উত্তীর্ণ'):(EN?'Failed':'অনুত্তীর্ণ'))+'</span></header>'
      +(hit.photo?'<div class="rc-photo"><img src="'+esc(hit.photo)+'" alt=""></div>':'')
      +'<div class="id-grid"><div><span>'+(EN?'Name':'নাম')+'</span><strong>'+esc(hit.name)+'</strong></div><div><span>'+(EN?'Class':'শ্রেণি')+'</span><strong>'+esc(hit.cls)+'</strong></div>'
      +'<div><span>'+(EN?'Roll':'রোল')+'</span><strong>'+esc(hit.roll)+'</strong></div><div><span>'+(EN?'Registration':'রেজিস্ট্রেশন')+'</span><strong>'+esc(hit.reg||'—')+'</strong></div>'
      +(hit.code?'<div><span>'+(EN?'Student ID':'স্টুডেন্ট ID')+'</span><strong>'+esc(hit.code)+'</strong></div>':'')+'</div>'
      +'<div class="table-wrap"><table class="rc-table"><thead><tr><th scope="col">'+(EN?'Subject':'বিষয়')+'</th><th scope="col">'+(EN?'Marks':'প্রাপ্ত নম্বর')+'</th><th scope="col">'+(EN?'Grade':'গ্রেড')+'</th><th scope="col">'+(EN?'GP':'পয়েন্ট')+'</th></tr></thead><tbody>'+rows+'</tbody></table></div>'
      +'<div class="gpa-strip"><span>'+(EN?'Overall result':'সামগ্রিক ফলাফল')+'</span><strong>'+(EN?'GPA ':'জিপিএ ')+esc(hit.gpa)+'</strong>'+(hit.total!==''&&hit.total!=null?'<span>'+(EN?'Total: ':'মোট: ')+esc(hit.total)+'</span>':'')+(hit.merit!==''&&hit.merit!=null?'<span class="rc-merit">'+(EN?'Merit: ':'মেধাস্থান: ')+esc(ord(hit.merit))+'</span>':'')+'</div>'
      +(hit.cum&&hit.cum.avg!=null?'<div class="gpa-strip cum-strip"><span>'+(EN?'Cumulative average across '+hit.cum.n+' exams':''+hit.cum.n+'টি পরীক্ষার সামগ্রিক গড়')+'</span><strong>'+(EN?'Avg GPA ':'গড় জিপিএ ')+esc(hit.cum.avg)+'</strong>'+(hit.cum.fails>0?'<span class="rc-merit">'+(EN?hit.cum.fails+' failed':'অনুত্তীর্ণ '+hit.cum.fails+'টি')+'</span>':'<span>✅ '+(EN?'Passed all':'সবগুলোতে উত্তীর্ণ')+'</span>')+'</div>':'')
      +'<div class="rc-sign"><span>'+(EN?'Class Teacher':'শ্রেণি শিক্ষক')+'</span><span>'+(EN?'Exam Controller':'পরীক্ষা নিয়ন্ত্রক')+'</span><span>'+(EN?'Head Teacher':'প্রধান শিক্ষক')+'</span></div>'
      +'<p><button class="btn" onclick="window.print()">🖨️ '+(EN?'Print':'প্রিন্ট')+'</button> <button class="btn btn-outline" onclick="location.reload()">'+(EN?'Search again':'আবার খুঁজুন')+'</button></p></article>';
  }
  function lookup(fd){loading();
    fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
      if(j&&j.ok&&j.row){show(j.row);}else{notFound(j&&j.limited);}
    }).catch(function(){notFound(false);});
  }
  form.addEventListener('submit',function(e){e.preventDefault();
    var roll=document.getElementById('r-roll').value.trim();
    var code=(document.getElementById('r-code')?document.getElementById('r-code').value.trim().toUpperCase():'');
    document.getElementById('e-roll').style.display=(roll||code)?'none':'block';
    if(!roll&&!code)return;
    var fd=new FormData();fd.append('action','uturn_result_lookup');fd.append('nonce',NONCE);
    fd.append('exam',document.getElementById('r-exam').value);fd.append('year',document.getElementById('r-year').value);
    fd.append('cls',document.getElementById('r-class').value);fd.append('roll',roll);fd.append('code',code);lookup(fd);});
})();
</script>
<?php get_footer(); ?>
