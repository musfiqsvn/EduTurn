<?php
/**
 * Template Name: Routine (ক্লাস রুটিন)
 * Class routine tabs + exam routine, powered by the Routine Builder.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$r = uturn_routines();
$classes = array_keys( $r['classes'] );
?>

<main id="main"><div class="container">
  <nav class="breadcrumb" aria-label="<?php echo esc_attr( uturn_t( 'ব্রেডক্রাম্ব', 'Breadcrumb' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( uturn_t( 'হোম', 'Home' ) ); ?></a><span>›</span><span><?php echo esc_html( uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) ); ?></span></nav>
  <div class="page-head">
    <h1><?php echo esc_html( uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) ); ?></h1>
    <p class="muted"><?php echo esc_html( uturn_t( 'শ্রেণিভিত্তিক সাপ্তাহিক রুটিন ও পরীক্ষার সময়সূচি', 'Class-wise weekly routine and exam schedule' ) ); ?></p>
  </div>
  <div data-tabs>
    <div class="tabs" role="tablist">
      <button class="tab active" data-tab="class" aria-selected="true">📚 <?php echo esc_html( uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) ); ?></button>
      <button class="tab" data-tab="exam" id="examTabBtn" aria-selected="false">📝 <?php echo esc_html( uturn_t( 'পরীক্ষার রুটিন', 'Exam Routine' ) ); ?></button>
    </div>

    <div class="tab-panel print-doc" data-panel="class">
      <div class="print-head"><b><?php echo esc_html( 'en' === uturn_lang() ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?></b><span><?php echo esc_html( uturn_opt( 'address' ) ); ?></span><i><?php echo esc_html( uturn_t( 'ক্লাস রুটিন', 'Class Routine' ) ); ?></i></div>
      <div class="routine-controls">
        <div class="field"><label for="r-class"><?php echo esc_html( uturn_t( 'শ্রেণি', 'Class' ) ); ?></label>
          <select id="r-class">
            <?php foreach ( $classes as $c ) : ?>
              <option><?php echo esc_html( $c ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="r-section"><?php echo esc_html( uturn_t( 'শাখা', 'Section' ) ); ?></label><select id="r-section"><option><?php echo esc_html( uturn_t( 'ক', 'A' ) ); ?></option><option><?php echo esc_html( uturn_t( 'খ', 'B' ) ); ?></option><option><?php echo esc_html( uturn_t( 'গ', 'C' ) ); ?></option></select></div>
        <div class="field"><label for="r-shift"><?php echo esc_html( uturn_t( 'শিফট', 'Shift' ) ); ?></label><select id="r-shift"><option><?php echo esc_html( uturn_t( 'প্রভাতি', 'Morning' ) ); ?></option><option><?php echo esc_html( uturn_t( 'দিবা', 'Day' ) ); ?></option></select></div>
      </div>
      <p class="muted" id="routine-cap"></p>
      <div class="table-wrap">
        <table class="routine-table">
          <thead id="routine-head"></thead>
          <tbody id="routine-body"></tbody>
        </table>
      </div>
      <p style="margin-top:14px"><button class="btn btn-outline" onclick="window.print()">🖨️ <?php echo esc_html( uturn_t( 'প্রিন্ট করুন', 'Print' ) ); ?></button> <a class="btn btn-outline" href="<?php echo esc_url( uturn_url( 'downloads' ) ); ?>">⬇️ <?php echo esc_html( uturn_t( 'ডাউনলোড সেকশন', 'Downloads Section' ) ); ?></a></p>
    </div>

    <div class="tab-panel hide print-doc" data-panel="exam" id="exam">
      <div class="print-head"><b><?php echo esc_html( 'en' === uturn_lang() ? uturn_opt( 'school_name_en' ) : uturn_opt( 'school_name_bn' ) ); ?></b><span><?php echo esc_html( uturn_opt( 'address' ) ); ?></span><i><?php echo esc_html( $r['exam_title'] ? $r['exam_title'] : uturn_t( 'পরীক্ষার রুটিন', 'Exam Routine' ) ); ?></i></div>
      <h2><?php echo esc_html( $r['exam_title'] ); ?></h2>
      <div class="table-wrap">
        <table class="routine-table">
          <thead><tr><th scope="col"><?php echo esc_html( uturn_t( 'তারিখ', 'Date' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'বার', 'Day' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'বিষয়', 'Subject' ) ); ?></th><th scope="col"><?php echo esc_html( uturn_t( 'সময়', 'Time' ) ); ?></th></tr></thead>
          <tbody id="exam-body">
            <?php foreach ( (array) $r['exam'] as $row ) : $row = array_values( (array) $row ); ?>
              <tr><td><?php echo esc_html( isset( $row[0] ) ? $row[0] : '' ); ?></td><td><?php echo esc_html( isset( $row[1] ) ? $row[1] : '' ); ?></td><td><?php echo esc_html( isset( $row[2] ) ? $row[2] : '' ); ?></td><td><?php echo esc_html( isset( $row[3] ) ? $row[3] : '' ); ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p style="margin-top:14px"><button class="btn btn-outline" onclick="window.print()">🖨️ <?php echo esc_html( uturn_t( 'প্রিন্ট করুন', 'Print' ) ); ?></button> <a class="btn btn-outline" href="<?php echo esc_url( uturn_url( 'downloads' ) ); ?>">⬇️ <?php echo esc_html( uturn_t( 'PDF ডাউনলোড', 'Download PDF' ) ); ?></a></p>
    </div>
  </div>
</div></main>

<script>
(function(){
  var PERIODS=<?php echo wp_json_encode( $r['periods'] ); ?>;
  var TIMES=<?php echo wp_json_encode( $r['times'] ); ?>;
  var DATA=<?php echo wp_json_encode( $r['classes'] ); ?>;
  var UPDATED=<?php echo wp_json_encode( $r['updated'] ); ?>;
  var EN=<?php echo 'en' === uturn_lang() ? 'true' : 'false'; ?>;
  var sel=document.getElementById('r-class'),sec=document.getElementById('r-section'),sh=document.getElementById('r-shift');
  function esc(s){return String(s==null?'':s).split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;').split('"').join('&quot;');}
  function render(){
    var cls=sel.value,rows=DATA[cls]||[];
    document.getElementById('routine-cap').textContent=EN?(cls+' class · Section '+sec.value+' · '+sh.value+(UPDATED?' · Updated: '+UPDATED:'')):(cls+' শ্রেণি · '+sec.value+' শাখা · '+sh.value+(UPDATED?' · হালনাগাদ: '+UPDATED:''));
    var h='<tr><th scope="row">'+(EN?'Day':'দিন')+'</th>';for(var i=0;i<PERIODS.length;i++){h+='<th scope="row">'+esc(PERIODS[i])+'<br><small>'+esc(TIMES[i]||'')+'</small></th>';}h+='</tr>';
    document.getElementById('routine-head').innerHTML=h;
    var b='';for(var r=0;r<rows.length;r++){b+='<tr><td><strong>'+esc(rows[r][0])+'</strong></td>';
      for(var c=1;c<=PERIODS.length;c++){var v=rows[r][c]||'';b+='<td'+(v==='বিরতি'?' class="break-cell"':'')+'>'+esc(v)+'</td>';}b+='</tr>';}
    document.getElementById('routine-body').innerHTML=b;
  }
  sel.addEventListener('change',render);sec.addEventListener('change',render);sh.addEventListener('change',render);
  render();
  if(location.hash==='#exam'){var t=document.getElementById('examTabBtn');if(t)t.click();}
})();
</script>
<?php get_footer(); ?>
