<?php
/**
 * EduTurn — "ড্যাশবোর্ড" control hub: branded top-level admin page,
 * tabbed sections mirroring every frontend zone. No Customizer dependency.
 */

defined( 'ABSPATH' ) || exit;

function uturn_default_options() {
	return array(
		/* Brand + header */
		'school_name_bn' => 'আলোকিত বিদ্যানিকেতন',
		'school_name_en' => 'Alokito Biddyaniketon',
		'tagline'        => 'আলোকিত ভবিষ্যতের পথে',
		'established'    => '2001',
		'logo_id'        => 0,
		'color_primary'   => '#0B4EA8',
		'color_secondary' => '#14324A',
		'color_accent'    => '#1B7FC2',
		'font_bn'        => 'tiro-bangla',
		'font_en'        => 'inter',
		'geo_enabled'    => '1',
		'management_lines' => "আলহাজ্ব মোঃ করিম উদ্দিন|সভাপতি, পরিচালনা পর্ষদ\nড. শারমিন সুলতানা|সহ-সভাপতি, পরিচালনা পর্ষদ\nঅধ্যাপক ফারহানা আহমেদ|সদস্য সচিব ও প্রধান শিক্ষক\nমোঃ রফিকুল ইসলাম|অভিভাবক সদস্য\nমোসা. নাসরিন আক্তার|অভিভাবক সদস্য\nজনাব তানভীর হোসেন|শিক্ষক প্রতিনিধি",
		'footer_about'   => 'আধুনিক শিক্ষা পদ্ধতি, অভিজ্ঞ শিক্ষক এবং শিক্ষাবান্ধব পরিবেশের মাধ্যমে আমরা প্রতিটি শিক্ষার্থীর সম্ভাবনাকে বিকশিত করতে কাজ করি।',
		'portal_gate'  => 'class',
		/* Home: hero */
		'hero_bg_id'     => 0,
		'hero_eyebrow'   => '২০০১ সাল থেকে মানসম্মত শিক্ষায় অগ্রণী',
		'hero_title'   => 'শিক্ষা, শৃঙ্খলা ও চরিত্র — উজ্জ্বল ভবিষ্যতের ঠিকানা',
		'hero_lead'      => 'প্লে থেকে দশম শ্রেণি পর্যন্ত আধুনিক পাঠদান, স্মার্ট ক্লাসরুম, ল্যাব ও সহশিক্ষা কার্যক্রম। ভর্তি চলছে — শিক্ষাবর্ষ ২০২৬।',
		'hero_meta'      => "২৫+|বছরের অভিজ্ঞতা\n১৫০০+|শিক্ষার্থী\n৯৫%|সাফল্যের হার",
		'stats_lines'    => "25|+|বছরের অভিজ্ঞতা\n1500|+|শিক্ষার্থী\n80|+|শিক্ষক ও কর্মী\n95|%|সাফল্যের হার",
		'principal_name' => 'অধ্যাপক ফারহানা আহমেদ', 'principal_title' => 'প্রধান শিক্ষক', 'principal_photo' => '', 'principal_msg' => 'আসসালামু আলাইকুম। আলোকিত বিদ্যানিকেতনের পক্ষ থেকে সকলকে আন্তরিক শুভেচ্ছা। আমরা বিশ্বাস করি, প্রতিটি শিশুর মধ্যেই লুকিয়ে আছে অসীম সম্ভাবনা। আধুনিক পাঠদান, নৈতিক শিক্ষা ও সহশিক্ষা কার্যক্রমের মাধ্যমে আমরা শিক্ষার্থীদের ভবিষ্যতের জন্য প্রস্তুত করি।',
		'principal_edu' => 'এম.এ (বাংলা), ঢাকা বিশ্ববিদ্যালয় · বি.এড (প্রথম শ্রেণি)',
		'principal_full' => "আসসালামু আলাইকুম ও শুভেচ্ছা। আলোকিত বিদ্যানিকেতনের পক্ষ থেকে আপনাদের সবাইকে আন্তরিক স্বাগত জানাই।\n\n২০০১ সালে মাত্র ১২০ জন শিক্ষার্থী নিয়ে আমাদের যাত্রা শুরু হয়েছিল। আজ আমরা দেড় হাজারেরও বেশি শিক্ষার্থীর একটি বড় পরিবার। এই দীর্ঘ পথচলায় আমাদের সবচেয়ে বড় অর্জন হলো অভিভাবকদের আস্থা ও শিক্ষার্থীদের সাফল্য।\n\nশিক্ষা শুধু পাঠ্যপুস্তকের জ্ঞান নয়—এটি চরিত্র, শৃঙ্খলা ও মূল্যবোধ গঠনের প্রক্রিয়া। আমরা বিশ্বাস করি, প্রতিটি শিশুই অনন্য এবং প্রত্যেকের মধ্যেই লুকিয়ে আছে অসীম সম্ভাবনা। আমাদের কাজ হলো সেই সম্ভাবনার দুয়ার খুলে দেওয়া।\n\nআধুনিক স্মার্ট ক্লাসরুম, সমৃদ্ধ লাইব্রেরি, বিজ্ঞান ও কম্পিউটার ল্যাব, সহশিক্ষা কার্যক্রম এবং অভিজ্ঞ শিক্ষকমণ্ডলীর সমন্বয়ে আমরা এমন একটি শিক্ষাবান্ধব পরিবেশ তৈরি করেছি, যেখানে শিশুরা আনন্দের সঙ্গে শেখে এবং আত্মবিশ্বাসের সঙ্গে বেড়ে ওঠে।\n\nআপনাদের সন্তানের উজ্জ্বল ভবিষ্যতের জন্য আমরা প্রতিশ্রুতিবদ্ধ। আসুন, একসঙ্গে গড়ি একটি আলোকিত আগামী।",
		'ticker_speed' => '45',
		'ticker_count' => '8',
		'board_strip'    => 'জেএসসি ও এসএসসি পরীক্ষায় টানা শতভাগ পাসের গৌরবময় রেকর্ড',
		/* Home: admission CTA */
		'adm_title'    => '২০২৬ শিক্ষাবর্ষে ভর্তি চলছে',
		'adm_sub'      => 'অনলাইনে আবেদন করুন মাত্র ৫ মিনিটে — আসন সীমিত!',
		'adm_deadline' => '2026-12-15', 'deadline_bn' => '১৫ ডিসেম্বর ২০২৬', 'adm_session' => 'শিক্ষাবর্ষ ২০২৬',
		'seats_lines'  => "প্লে – কেজি|৬০|৪+ বছর\n১ম – ৫ম শ্রেণি|১২০|৬+ বছর\n৬ষ্ঠ – ৮ম শ্রেণি|১০০|—\n৯ম শ্রেণি|৮০|—",
		'fees_lines'   => "প্লে – কেজি|৫,০০০ টাকা|৮০০ টাকা\n১ম – ৫ম শ্রেণি|৬,০০০ টাকা|১,২০০ টাকা\n৬ষ্ঠ – ৮ম শ্রেণি|৭,০০০ টাকা|১,৬০০ টাকা\n৯ম – ১০ম শ্রেণি|৮,০০০ টাকা|২,০০০ টাকা",
		'adm_dates_lines' => "অনলাইন আবেদন শুরু|১ সেপ্টেম্বর ২০২৬\nআবেদনের শেষ তারিখ|১৫ ডিসেম্বর ২০২৬\nভর্তি পরীক্ষা (৬ষ্ঠ–৯ম)|৩ জানুয়ারি ২০২৭\nফলাফল প্রকাশ|১০ জানুয়ারি ২০২৭\nভর্তি নিশ্চয়ন|১১–২০ জানুয়ারি ২০২৭\nক্লাস শুরু|২৫ জানুয়ারি ২০২৭",
		/* Routine */
		'exam_title'   => 'নির্বাচনী পরীক্ষা ২০২৬ (দশম শ্রেণি)',
		/* Academic */
		'calendar_lines' => "জানুয়ারি|নতুন শিক্ষাবর্ষ শুরু · বই বিতরণ উৎসব\nফেব্রুয়ারি|আন্তর্জাতিক মাতৃভাষা দিবস · ১ম মডেল টেস্ট\nমার্চ|স্বাধীনতা দিবস উদযাপন · ১ম সাময়িক পরীক্ষা\nএপ্রিল|শিক্ষা সফর · বিজ্ঞান মেলা (আন্তঃবিদ্যালয়)\nমে|অর্ধ-বার্ষিক পরীক্ষা প্রস্তুতি\nজুন|অর্ধ-বার্ষিক পরীক্ষা · গ্রীষ্মকালীন ছুটি\nজুলাই|ফলাফল প্রকাশ · অভিভাবক সমাবেশ\nআগস্ট|শোক দিবস পালন · বিতর্ক প্রতিযোগিতা\nসেপ্টেম্বর|বইমেলা · নির্বাচনী পরীক্ষা (দশম) প্রস্তুতি\nঅক্টোবর|বিজ্ঞান মেলা · দুর্গাপূজার ছুটি\nনভেম্বর|নির্বাচনী পরীক্ষা · সাংস্কৃতিক উৎসব\nডিসেম্বর|বিজয় দিবস · বার্ষিক ক্রীড়া · বার্ষিক পরীক্ষা",
		/* Inner pages: about */
		'about_history' => "২০০১ সালে মাত্র ১২০ জন শিক্ষার্থী ও ৮ জন শিক্ষক নিয়ে %SCHOOL%-এর যাত্রা শুরু হয়। আজ আমরা দেড় হাজারেরও বেশি শিক্ষার্থী, ৪৫+ অভিজ্ঞ শিক্ষক এবং আধুনিক ক্যাম্পাস নিয়ে এই অঞ্চলের অন্যতম সেরা শিক্ষাপ্রতিষ্ঠান।\n\nপ্রতিষ্ঠার পর থেকেই বোর্ড পরীক্ষায় আমাদের ফলাফল ধারাবাহিকভাবে শতভাগ পাসের ধারা বজায় রেখেছে। শিক্ষার পাশাপাশি খেলাধুলা, সাংস্কৃতিক কর্মকাণ্ড ও বিজ্ঞানচর্চায় আমাদের শিক্ষার্থীরা জাতীয় পর্যায়েও কৃতিত্বের স্বাক্ষর রেখেছে।",
		'about_vision' => 'জ্ঞান, শৃঙ্খলা ও নৈতিকতায় সমৃদ্ধ একটি আলোকিত প্রজন্ম গড়ে তোলা।',
		'about_mission' => 'আধুনিক পাঠদান পদ্ধতি, স্মার্ট ক্লাসরুম, ল্যাব সুবিধা ও সহশিক্ষা কার্যক্রমের মাধ্যমে প্রতিটি শিক্ষার্থীর সুপ্ত প্রতিভার বিকাশ ঘটানো।',
		'about_goals_lines' => "শতভাগ পাসের ধারা বজায় রাখা\nনৈতিক ও মানবিক মূল্যবোধের চর্চা\nবিজ্ঞান ও প্রযুক্তিতে দক্ষতা\nনিরাপদ ও আনন্দময় শিখন পরিবেশ",
		/* Inner pages: academic */
		'academic_programs_lines' => "🧸|প্রি-প্রাইমারি|প্লে – কেজি · বয়স ৪+|খেলার মাধ্যমে শেখা;ভাষা ও সংখ্যা পরিচিতি;নৈতিক গল্প ও ছড়া\n📚|প্রাইমারি|১ম – ৫ম শ্রেণি · বয়স ৬+|জাতীয় পাঠ্যক্রম;ইংরেজি স্পোকেন ক্লাব;কম্পিউটার পরিচিতি\n🔬|জুনিয়র সেকেন্ডারি|৬ষ্ঠ – ৮ম শ্রেণি|বিজ্ঞান ল্যাব সেশন;গণিত অলিম্পিয়াড প্রস্তুতি;প্রজেক্টভিত্তিক শেখা\n🎓|সেকেন্ডারি|৯ম – ১০ম শ্রেণি|এসএসসি বিশেষ ব্যাচ;মডেল টেস্ট ও রিভিশন;ক্যারিয়ার কাউন্সেলিং",
		'academic_curriculum' => 'জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB)-এর পাঠ্যক্রম অনুসরণ করে পাঠদান পরিচালিত হয়। পাঠ্যবইয়ের পাশাপাশি সৃজনশীল অ্যাসাইনমেন্ট, ল্যাব ওয়ার্ক ও ফিল্ড ভিজিটের মাধ্যমে শেখাকে বাস্তবমুখী করা হয়।',
		'academic_evaluation' => 'ধারাবাহিক মূল্যায়নের পাশাপাশি বছরে তিনটি সাময়িক পরীক্ষা, অর্ধ-বার্ষিক ও বার্ষিক পরীক্ষা অনুষ্ঠিত হয়। প্রতিটি পরীক্ষার ফলাফল অনলাইনে প্রকাশ করা হয় এবং অভিভাবক সমাবেশে অগ্রগতি পর্যালোচনা করা হয়।',
		'academic_exams_lines' => "১ম সাময়িক পরীক্ষা|মার্চ|প্লে – ৯ম\nঅর্ধ-বার্ষিক পরীক্ষা|জুন|১ম – ১০ম\n২য় সাময়িক পরীক্ষা|সেপ্টেম্বর|প্লে – ৯ম\nনির্বাচনী পরীক্ষা|নভেম্বর|১০ম\nবার্ষিক পরীক্ষা|ডিসেম্বর|প্লে – ৯ম",
		/* Inner pages: admission */
		'admission_process_lines' => "অনলাইন আবেদন|ওয়েবসাইট থেকে ফরম পূরণ করে ছবি ও কাগজপত্র আপলোড করুন\nরেফারেন্স সংরক্ষণ|জমার পর প্রাপ্ত রেফারেন্স নম্বরটি যত্নে রাখুন\nভর্তি পরীক্ষা / সাক্ষাৎকার|৬ষ্ঠ–৯ম শ্রেণির জন্য লিখিত পরীক্ষা; অন্যান্যের জন্য সাক্ষাৎকার\nফলাফল ও নিশ্চয়ন|ফলাফল প্রকাশের পর নির্ধারিত সময়ে ভর্তি নিশ্চয়ন করুন\nক্লাস শুরু|বই বিতরণ ও ওরিয়েন্টেশনের মাধ্যমে নতুন যাত্রা",
		'admission_docs_lines' => "শিক্ষার্থীর পাসপোর্ট সাইজ ছবি (৪ কপি)\nজন্ম নিবন্ধন সনদের ফটোকপি\nপূর্ববর্তী শ্রেণির মার্কশিট / ছাড়পত্র\nপিতা/মাতার জাতীয় পরিচয়পত্রের ফটোকপি\nঠিকানার প্রমাণপত্র (বিদ্যুৎ বিল ইত্যাদি)",
		/* Inner pages: students */
		'students_features_lines' => "📚|সমৃদ্ধ লাইব্রেরি|১০,০০০+ বই, দৈনিক পত্রিকা ও শান্ত পাঠকক্ষ\n🔬|বিজ্ঞান ও কম্পিউটার ল্যাব|হাতে-কলমে পরীক্ষণ ও প্রোগ্রামিং শেখার সুযোগ\n🚌|পরিবহন সুবিধা|নিরাপদ স্কুল বাস — নির্ধারিত রুটে যাতায়াত\n🍱|স্বাস্থ্যকর ক্যান্টিন|পুষ্টিকর ও সাশ্রয়ী টিফিনের ব্যবস্থা\n🩺|স্বাস্থ্যসেবা|প্রাথমিক চিকিৎসা কক্ষ ও নিয়মিত স্বাস্থ্য পরীক্ষা\n🎓|বৃত্তি ও কাউন্সেলিং|মেধাবী ও অসচ্ছলদের বৃত্তি, ক্যারিয়ার পরামর্শ",
		'students_clubs_lines' => "🔭|বিজ্ঞান ক্লাব|বিজ্ঞান মেলা, অলিম্পিয়াড ও গবেষণা প্রজেক্ট\n🎤|বিতর্ক ক্লাব|আন্তঃবিদ্যালয় বিতর্ক ও পাবলিক স্পিকিং\n⚽|ক্রীড়া ক্লাব|ফুটবল, ক্রিকেট, ব্যাডমিন্টন ও বার্ষিক ক্রীড়া\n🎨|সাংস্কৃতিক ক্লাব|সংগীত, নৃত্য, আবৃত্তি ও চিত্রাঙ্কন\n💻|আইসিটি ক্লাব|প্রোগ্রামিং, রোবোটিক্স ও ওয়েব ডিজাইন\n⛺|স্কাউট ও রেড ক্রিসেন্ট|শৃঙ্খলা, সেবা ও নেতৃত্বের প্রশিক্ষণ",
		'students_conduct_lines' => "নির্ধারিত ইউনিফর্মে সময়মতো উপস্থিতি\nশিক্ষক ও সহপাঠীদের প্রতি শ্রদ্ধাশীল আচরণ\nবিদ্যালয়ের সম্পদের যত্ন\nমোবাইল ফোন আনা নিষেধ",
		/* Contact */
		'phone'        => '+৮৮০ ৯৬১১-২৩৪৫৬৭',
		'phone_href'   => '+8809611234567',
		'email'        => 'info@alokitobiddyaniketon.edu.bd',
		'address'      => 'বাড়ি ১২, রোড ০৭, ধানমন্ডি, ঢাকা-১২০৫',
		'address_en'   => 'House 12, Road 07, Dhanmondi, Dhaka-1205',
		'map_embed'    => '',
		'hours'        => 'রবি–বৃহস্পতি: সকাল ৮টা – বিকেল ৪টা',
		'hours_short'  => 'রবি–বৃহস্পতি, সকাল ৮টা–বিকেল ৪টা',
		/* Socials */
		'social_fb'    => 'https://facebook.com', 'social_yt' => 'https://youtube.com', 'social_x' => 'https://x.com', 'social_wa' => 'https://wa.me/8809611234567',
		/* SMS */
		'sms_enable' => '0', 'wa_enable' => '0', 'sms_test_mode' => '1', 'nt_pref' => 'wa',
		'sms_masking' => 'ALOKITO', 'sms_api' => '', 'sms_url' => '', 'sms_method' => 'GET', 'sms_headers' => '', 'sms_body' => '', 'sms_success' => '',
		'wa_phone_id' => '', 'wa_token' => '',
		'tpl_absent' => 'প্রিয় অভিভাবক, {name} (শ্রেণি: {class}, রোল: {roll}) আজ {date} তারিখে বিদ্যালয়ে অনুপস্থিত। — {school}',
		'tpl_result' => 'অভিনন্দন {name}! {exam} {year} ফলাফল: GPA {gpa} ({result})। শ্রেণি: {class}, রোল: {roll} — {school}',
		'sms_event' => '0', 'sms_notice' => '0', 'sms_result' => '0', 'sms_attendance' => '0',
		/* Extra */
		'custom_css'   => '',
		'show_teacher_phone' => '1',
	);
}

function uturn_opts() {
	static $cache = null;
	if ( null === $cache ) {
		$cache = wp_parse_args( (array) get_option( UTURN_OPT, array() ), uturn_default_options() );
	}
	return $cache;
}
function uturn_opt( $key, $fallback = '' ) {
	$o = uturn_opts();
	return isset( $o[ $key ] ) && '' !== $o[ $key ] ? $o[ $key ] : $fallback;
}

add_action( 'admin_menu', 'uturn_options_menu' );
function uturn_options_menu() {
	// Parent visible to all staff (cap 'read'); every submenu enforces its own cap.
	add_menu_page( 'EduTurn ড্যাশবোর্ড', 'EduTurn', 'read', 'eduturn', 'uturn_dashboard_page', 'dashicons-bank', 25 );
	add_submenu_page( 'eduturn', 'ড্যাশবোর্ড', '🏠 ড্যাশবোর্ড', 'read', 'eduturn', 'uturn_dashboard_page' );
	add_submenu_page( 'eduturn', 'সেটিংস', '⚙️ সেটিংস', 'uturn_manage_settings', 'eduturn-settings', 'uturn_options_page' );
	add_submenu_page( 'eduturn', 'হোমপেজ সাজান', '🏫 হোমপেজ সাজান', 'uturn_manage_settings', 'eduturn-homepage', 'uturn_homepage_redirect' );
	add_submenu_page( 'eduturn', 'ডেমো কনটেন্ট', 'ডেমো কনটেন্ট', 'manage_options', 'eduturn-seeder', 'uturn_seeder_page' );
}

function uturn_option_tabs() {
	return array(
		'brand' => 'ব্র্যান্ড ও হেডার', 'home' => 'হোমপেজ', 'pages' => 'পেজ কনটেন্ট', 'contact' => 'যোগাযোগ',
		'social' => 'সোশ্যাল', 'sms' => '📲 নোটিফিকেশন', 'extra' => 'অতিরিক্ত',
	);
}

function uturn_homepage_redirect() {
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-settings&tab=home' ) ) );
	exit;
}

function uturn_options_page() {
	if ( ! current_user_can( 'uturn_manage_settings' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'brand';
	$tabs = uturn_option_tabs();
	if ( ! isset( $tabs[ $tab ] ) ) {
		$tab = 'brand';
	}

	echo '<div class="wrap"><h1>EduTurn — সেটিংস</h1>';
	if ( ! empty( $_GET['uturn_saved'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সংরক্ষণ করা হয়েছে।</p></div>';
	}
	echo '<h2 class="nav-tab-wrapper">';
	foreach ( $tabs as $k => $label ) {
		echo '<a class="nav-tab' . ( $k === $tab ? ' nav-tab-active' : '' ) . '" href="' . esc_url( admin_url( 'admin.php?page=eduturn-settings&tab=' . $k ) ) . '">' . esc_html( $label ) . '</a>';
	}
	echo '</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	settings_fields( 'uturn_edu_opts' );
	/* Must come AFTER settings_fields(): it outputs action=update and PHP keeps the last value. */
	echo '<input type="hidden" name="action" value="uturn_opts_save">';
	echo '<input type="hidden" name="_uturn_tab" value="' . esc_attr( $tab ) . '">';
	call_user_func( 'uturn_tab_' . $tab );
	submit_button( 'সংরক্ষণ করুন' );
	echo '</form></div>';
}

add_action( 'admin_init', 'uturn_register_options' );
function uturn_register_options() {
	register_setting( 'uturn_edu_opts', UTURN_OPT, 'uturn_sanitize_options' );
}

/* Settings save via admin-post so Headmaster (no manage_options) can save too.
 * options.php hard-requires manage_options — this handler requires uturn_manage_settings. */
add_action( 'admin_post_uturn_opts_save', 'uturn_opts_save' );
function uturn_opts_save() {
	if ( ! current_user_can( 'uturn_manage_settings' ) ) {
		wp_die( 'Unauthorized.' );
	}
	check_admin_referer( 'uturn_edu_opts-options' );
	$in = isset( $_POST[ UTURN_OPT ] ) && is_array( $_POST[ UTURN_OPT ] ) ? $_POST[ UTURN_OPT ] : array();
	update_option( UTURN_OPT, $in ); // sanitize runs once via the registered sanitize_option filter
	$tab = isset( $_POST['_uturn_tab'] ) ? sanitize_key( wp_unslash( $_POST['_uturn_tab'] ) ) : 'brand';
	$tabs = uturn_option_tabs();
	if ( ! isset( $tabs[ $tab ] ) ) {
		$tab = 'brand';
	}
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-settings&tab=' . $tab . '&uturn_saved=1' ) ) );
	exit;
}

function uturn_sanitize_options( $in ) {
	$out = (array) get_option( UTURN_OPT, array() );
	$tab = isset( $_POST['_uturn_tab'] ) ? sanitize_key( wp_unslash( $_POST['_uturn_tab'] ) ) : '';
	$texts = array( 'school_name_bn', 'school_name_en', 'tagline', 'established', 'hero_eyebrow', 'hero_title', 'hero_lead', 'principal_name', 'principal_title', 'principal_photo', 'principal_edu', 'ticker_speed', 'adm_title', 'adm_sub', 'adm_deadline', 'deadline_bn', 'adm_session', 'exam_title', 'about_vision', 'about_mission', 'phone', 'phone_href', 'email', 'address', 'address_en', 'hours', 'hours_short', 'footer_about', 'social_fb', 'social_yt', 'social_x', 'social_wa', 'sms_masking', 'sms_api', 'sms_url', 'sms_method', 'sms_success', 'wa_phone_id', 'wa_token', 'nt_pref', 'portal_gate' );
	$areas = array( 'hero_meta', 'stats_lines', 'board_strip', 'principal_msg', 'principal_full', 'management_lines', 'calendar_lines', 'seats_lines', 'fees_lines', 'adm_dates_lines', 'about_history', 'about_goals_lines', 'academic_programs_lines', 'academic_curriculum', 'academic_evaluation', 'academic_exams_lines', 'admission_process_lines', 'admission_docs_lines', 'students_features_lines', 'students_clubs_lines', 'students_conduct_lines', 'map_embed', 'custom_css', 'sms_headers', 'sms_body', 'tpl_absent', 'tpl_result' );
	foreach ( $texts as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = sanitize_text_field( wp_unslash( $in[ $k ] ) );
		}
	}
	foreach ( $areas as $k ) {
		if ( ! isset( $in[ $k ] ) ) {
			continue;
		}
		$rcols = uturn_rows_columns( $k );
		if ( $rcols && is_array( $in[ $k ] ) ) {
			$out[ $k ] = uturn_rows_to_text( wp_unslash( $in[ $k ] ), count( $rcols ) );
		} else {
			$out[ $k ] = ( 'map_embed' === $k ) ? wp_kses_post( wp_unslash( $in[ $k ] ) ) : sanitize_textarea_field( wp_unslash( $in[ $k ] ) );
		}
	}
	/* Flags live on their own tabs: only touch them when that tab posts,
	 * otherwise saving one tab would wipe the other tab's checkboxes. */
	if ( 'sms' === $tab ) {
		foreach ( array( 'sms_enable', 'wa_enable', 'sms_test_mode', 'sms_event', 'sms_notice', 'sms_result', 'sms_attendance' ) as $k ) {
			$out[ $k ] = ! empty( $in[ $k ] ) ? '1' : '0';
		}
	}
	if ( 'brand' === $tab ) {
		$out['geo_enabled'] = ! empty( $in['geo_enabled'] ) ? '1' : '0';
	}
	foreach ( array( 'logo_id', 'hero_bg_id' ) as $k ) {
		if ( isset( $in[ $k ] ) ) {
			$out[ $k ] = absint( $in[ $k ] );
		}
	}
	foreach ( array( 'color_primary' => '#0B4EA8', 'color_secondary' => '#14324A', 'color_accent' => '#1B7FC2' ) as $k => $fb ) {
		if ( isset( $in[ $k ] ) ) {
			$c = sanitize_hex_color( wp_unslash( $in[ $k ] ) );
			$out[ $k ] = $c ? $c : ( isset( $out[ $k ] ) && $out[ $k ] ? $out[ $k ] : $fb );
		}
	}
	if ( isset( $in['font_bn'] ) && in_array( $in['font_bn'], array( 'tiro-bangla', 'hind-siliguri', 'noto-bengali', 'baloo-da' ), true ) ) {
		$out['font_bn'] = $in['font_bn'];
	}
	if ( isset( $in['font_en'] ) && in_array( $in['font_en'], array( 'inter', 'public-sans' ), true ) ) {
		$out['font_en'] = $in['font_en'];
	}
	if ( isset( $in['sms_method'] ) && in_array( strtoupper( $in['sms_method'] ), array( 'GET', 'POST' ), true ) ) {
		$out['sms_method'] = strtoupper( $in['sms_method'] );
	}
	if ( isset( $in['nt_pref'] ) && in_array( $in['nt_pref'], array( 'wa', 'sms' ), true ) ) {
		$out['nt_pref'] = $in['nt_pref'];
	}
	if ( isset( $in['ticker_count'] ) ) {
		$out['ticker_count'] = (string) max( 1, absint( $in['ticker_count'] ) );
	}
	if ( ! isset( $out['portal_gate'] ) || ! in_array( $out['portal_gate'], array( 'class', 'roll' ), true ) ) {
		$out['portal_gate'] = 'class';
	}
	/* Homepage section order (only when the home tab posts these). */
	if ( isset( $in['sections_order'] ) && is_array( $in['sections_order'] ) ) {
		/* Visual editor posts a prebuilt order — validate + keep (else update_option would revert it). */
		$keep = array();
		foreach ( $in['sections_order'] as $row ) {
			$rid = isset( $row['id'] ) ? (string) $row['id'] : '';
			if ( $rid !== '' && preg_match( '/^[a-z0-9_-]+$/', $rid ) && file_exists( get_template_directory() . '/template-parts/home/' . $rid . '.php' ) ) {
				$keep[] = array( 'id' => $rid, 'visible' => 1 );
			}
		}
		$out['sections_order'] = $keep;
	} elseif ( isset( $in['sec_vis'] ) || isset( $in['sec_pos'] ) ) {
		$defs = function_exists( 'uturn_section_defs' ) ? uturn_section_defs() : array();
		$vis = isset( $in['sec_vis'] ) ? (array) $in['sec_vis'] : array();
		$pos = isset( $in['sec_pos'] ) ? (array) $in['sec_pos'] : array();
		$order = array();
		foreach ( $defs as $id => $label ) {
			if ( ! empty( $vis[ $id ] ) ) {
				$order[] = array( 'id' => $id, 'pos' => isset( $pos[ $id ] ) ? (int) $pos[ $id ] : 99 );
			}
		}
		usort(
			$order,
			function ( $a, $b ) {
				if ( $a['pos'] === $b['pos'] ) {
					return 0;
				}
				return $a['pos'] < $b['pos'] ? -1 : 1;
			}
		);
		$final = array_map(
			function ( $r ) {
				return array( 'id' => $r['id'], 'visible' => 1 );
			},
			$order
		);
		/* Preserve visual-editor pool sections (custom templates outside defs). */
		foreach ( (array) ( $out['sections_order'] ?? array() ) as $prow ) {
			if ( isset( $prow['id'] ) && ! isset( $defs[ $prow['id'] ] )
				&& preg_match( '/^[a-z0-9_-]+$/', $prow['id'] )
				&& file_exists( get_template_directory() . '/template-parts/home/' . $prow['id'] . '.php' ) ) {
				$final[] = array( 'id' => $prow['id'], 'visible' => 1 );
			}
		}
		$out['sections_order'] = $final;
	}
	add_settings_error( 'uturn_edu_opts', 'uturn_saved', 'সেটিংস সংরক্ষিত হয়েছে।', 'updated' );
	return $out;
}

/* ---- Field helpers ---- */
function uturn_field_text( $o, $key, $label, $wide = false ) {
	echo '<tr><th scope="row"><label for="ut-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><input type="text" id="ut-' . esc_attr( $key ) . '" name="' . esc_attr( UTURN_OPT ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( $o[ $key ] ) . '" class="' . ( $wide ? 'large-text' : 'regular-text' ) . '"></td></tr>';
}
function uturn_field_area( $o, $key, $label, $rows = 4 ) {
	echo '<tr><th scope="row"><label for="ut-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><textarea id="ut-' . esc_attr( $key ) . '" name="' . esc_attr( UTURN_OPT ) . '[' . esc_attr( $key ) . ']" rows="' . (int) $rows . '" class="large-text">' . esc_textarea( $o[ $key ] ) . '</textarea></td></tr>';
}
function uturn_field_rows( $o, $key, $label ) {
	$cols = uturn_rows_columns( $key );
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
	if ( $cols ) {
		uturn_row_editor( UTURN_OPT . '[' . $key . ']', isset( $o[ $key ] ) ? $o[ $key ] : '', $cols );
	} else {
		uturn_field_area( $o, $key, $label );
	}
	echo '</td></tr>';
}
function uturn_field_media( $o, $key, $label ) {
	$id = (int) $o[ $key ];
	$prev = $id ? wp_get_attachment_image( $id, 'thumbnail', false, array( 'style' => 'max-width:90px;height:auto;display:block;margin-bottom:6px' ) ) : '';
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td><span class="eduturn-media-prev">' . $prev . '</span>'
		. '<input type="hidden" class="eduturn-media-id" name="' . esc_attr( UTURN_OPT ) . '[' . esc_attr( $key ) . ']" value="' . $id . '">'
		. '<button type="button" class="button eduturn-media-btn">ছবি বেছে নিন</button> '
		. '<button type="button" class="button-link eduturn-media-clear">সরান</button></td></tr>';
}
function uturn_field_color( $o, $key, $label ) {
	echo '<tr><th scope="row"><label for="ut-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><input type="text" id="ut-' . esc_attr( $key ) . '" name="' . esc_attr( UTURN_OPT ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( $o[ $key ] ) . '" class="eduturn-color" data-default-color="' . esc_attr( $o[ $key ] ) . '"></td></tr>';
}
function uturn_field_select( $o, $key, $label, $choices ) {
	echo '<tr><th scope="row"><label for="ut-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><select id="ut-' . esc_attr( $key ) . '" name="' . esc_attr( UTURN_OPT ) . '[' . esc_attr( $key ) . ']">';
	foreach ( $choices as $val => $name ) {
		echo '<option value="' . esc_attr( $val ) . '"' . selected( $o[ $key ], $val, false ) . '>' . esc_html( $name ) . '</option>';
	}
	echo '</select></td></tr>';
}

function uturn_tab_brand() {
	$o = uturn_opts();
	wp_enqueue_media();
	echo '<table class="form-table">';
	uturn_field_text( $o, 'school_name_bn', 'বিদ্যালয়ের নাম (বাংলা)', true );
	uturn_field_text( $o, 'school_name_en', 'বিদ্যালয়ের নাম (ইংরেজি)', true );
	uturn_field_text( $o, 'tagline', 'স্লোগান', true );
	uturn_field_text( $o, 'established', 'প্রতিষ্ঠার বছর' );
	uturn_field_media( $o, 'logo_id', 'লোগো (SVG/PNG)' );
	echo '</table><h2>রঙ ও ফন্ট</h2><table class="form-table">';
	uturn_field_color( $o, 'color_primary', 'প্রাইমারি রঙ' );
	uturn_field_color( $o, 'color_secondary', 'সেকেন্ডারি রঙ' );
	uturn_field_color( $o, 'color_accent', 'অ্যাকসেন্ট রঙ' );
	uturn_field_select( $o, 'font_bn', 'বাংলা ফন্ট', array( 'tiro-bangla' => 'Tiro Bangla', 'hind-siliguri' => 'Hind Siliguri', 'noto-bengali' => 'Noto Sans Bengali', 'baloo-da' => 'Baloo Da 2' ) );
	uturn_field_select( $o, 'font_en', 'ইংরেজি ফন্ট', array( 'inter' => 'Inter', 'public-sans' => 'Public Sans' ) );
	echo '<tr><th scope="row">টপবারে অবস্থান চিপ</th><td><label><input type="checkbox" name="' . esc_attr( UTURN_OPT ) . '[geo_enabled]" value="1"' . checked( $o['geo_enabled'], '1', false ) . '> চালু (ভিজিটরের এলাকা দেখাবে)</label></td></tr>';
	echo '</table><h2>ফুটার ও পোর্টাল</h2><table class="form-table">';
	uturn_field_area( $o, 'footer_about', 'ফুটার পরিচিতি', 3 );
	uturn_field_rows( $o, 'management_lines', 'পরিচালনা পর্ষদ' );
	echo '<tr><th scope="row">স্টুডেন্ট পোর্টাল প্রবেশ</th><td><label><input type="radio" name="' . esc_attr( UTURN_OPT ) . '[portal_gate]" value="class"' . checked( $o['portal_gate'], 'class', false ) . '> শ্রেণি বাছাই (ড্রপডাউন)</label><br><label><input type="radio" name="' . esc_attr( UTURN_OPT ) . '[portal_gate]" value="roll"' . checked( $o['portal_gate'], 'roll', false ) . '> ক্লাস রোল দিয়ে</label></td></tr>';
	echo '</table>';
}

function uturn_tab_home() {
	$o = uturn_opts();
	echo '<h2>হোমপেজ সেকশন: কোনটা দেখাবে / লুকাবে</h2><p class="description">যেগুলোতে টিক থাকবে শুধু সেগুলো হোমপেজে দেখাবে; ক্রম নম্বর ছোট থেকে বড় অনুযায়ী সাজবে। নিচে প্রতিটা সেকশনের লেখা/ছবি বদলানোর ঘর আছে।</p><table class="form-table">';
	$defs = uturn_section_defs();
	$cur = array();
	foreach ( (array) uturn_opt( 'sections_order', array() ) as $i => $row ) {
		if ( isset( $row['id'] ) ) {
			$cur[ $row['id'] ] = $i + 1;
		}
	}
	$i = 0;
	foreach ( $defs as $id => $label ) {
		$i++;
		$checked = $cur ? isset( $cur[ $id ] ) : true;
		$p = isset( $cur[ $id ] ) ? $cur[ $id ] : $i;
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td><label><input type="checkbox" name="' . esc_attr( UTURN_OPT ) . '[sec_vis][' . esc_attr( $id ) . ']" value="1"' . checked( $checked, true, false ) . '> দেখাও</label> &nbsp; ক্রম: <input type="number" min="1" max="30" style="width:70px" name="' . esc_attr( UTURN_OPT ) . '[sec_pos][' . esc_attr( $id ) . ']" value="' . (int) $p . '"></td></tr>';
	}
	echo '</table>';
	echo '<h2>হিরো</h2><table class="form-table">';
	uturn_field_media( $o, 'hero_bg_id', 'হিরো পেছনের ছবি' );
	uturn_field_text( $o, 'hero_eyebrow', 'হিরো ব্যাজ', true );
	uturn_field_text( $o, 'hero_title', 'হিরো শিরোনাম', true );
	uturn_field_area( $o, 'hero_lead', 'হিরো সাবটেক্সট', 3 );
	uturn_field_rows( $o, 'hero_meta', 'হিরো পরিসংখ্যান' );
	echo '</table><h2>পরিসংখ্যান ব্যান্ড</h2><table class="form-table">';
	uturn_field_rows( $o, 'stats_lines', 'পরিসংখ্যান' );
	echo '</table><h2>প্রধান শিক্ষক</h2><table class="form-table">';
	uturn_field_text( $o, 'principal_name', 'নাম', true );
	uturn_field_text( $o, 'principal_title', 'পদবি' );
	uturn_field_text( $o, 'principal_edu', 'শিক্ষাগত যোগ্যতা', true );
	uturn_field_text( $o, 'principal_photo', 'ছবির URL', true );
	uturn_field_area( $o, 'principal_msg', 'সংক্ষিপ্ত বাণী (হোমপেজ)', 4 );
	uturn_field_area( $o, 'principal_full', 'পূর্ণ বাণী (আমাদের সম্পর্কে পেজ)', 8 );
	echo '</table><h2>নোটিশ টিকার</h2><table class="form-table">';
	uturn_field_text( $o, 'ticker_speed', 'টিকার গতি (px/সেকেন্ড)' );
	uturn_field_text( $o, 'ticker_count', 'টিকারে নোটিশ সংখ্যা' );
	echo '</table><h2>অর্জন স্ট্রিপ</h2><table class="form-table">';
	uturn_field_area( $o, 'board_strip', 'বোর্ড ফলাফল স্ট্রিপ', 3 );
	echo '</table><h2>ভর্তি CTA</h2><table class="form-table">';
	uturn_field_text( $o, 'adm_title', 'শিরোনাম', true );
	uturn_field_text( $o, 'adm_sub', 'সাবটেক্সট', true );
	uturn_field_text( $o, 'adm_deadline', 'শেষ তারিখ (YYYY-MM-DD)' );
	uturn_field_text( $o, 'deadline_bn', 'শেষ তারিখ (বাংলা)' );
	uturn_field_text( $o, 'adm_session', 'শিক্ষাবর্ষ' );
	uturn_field_rows( $o, 'seats_lines', 'আসন তালিকা' );
	uturn_field_rows( $o, 'fees_lines', 'ফি তালিকা' );
	uturn_field_rows( $o, 'adm_dates_lines', 'ভর্তি তারিখসমূহ' );
	echo '</table><h2>রুটিন</h2><table class="form-table">';
	uturn_field_text( $o, 'exam_title', 'পরীক্ষার রুটিন শিরোনাম', true );
	echo '</table><h2>একাডেমিক ক্যালেন্ডার</h2><table class="form-table">';
	uturn_field_rows( $o, 'calendar_lines', 'মাসভিত্তিক কর্মসূচি' );
	echo '</table>';
}

function uturn_tab_pages() {
	$o = uturn_opts();
	echo '<h2>আমাদের সম্পর্কে</h2><table class="form-table">';
	uturn_field_area( $o, 'about_history', 'ইতিহাস (প্যারা ভাগ করতে খালি লাইন দিন; %SCHOOL% = বিদ্যালয়ের নাম)', 6 );
	uturn_field_text( $o, 'about_vision', 'রূপকল্প', true );
	uturn_field_text( $o, 'about_mission', 'অভিলক্ষ্য', true );
	uturn_field_rows( $o, 'about_goals_lines', 'লক্ষ্যসমূহ' );
	echo '</table><h2>একাডেমিক</h2><table class="form-table">';
	uturn_field_rows( $o, 'academic_programs_lines', 'প্রোগ্রাম' );
	uturn_field_area( $o, 'academic_curriculum', 'পাঠ্যক্রম বর্ণনা', 3 );
	uturn_field_area( $o, 'academic_evaluation', 'মূল্যায়ন বর্ণনা', 3 );
	uturn_field_rows( $o, 'academic_exams_lines', 'পরীক্ষার সময়সূচি' );
	echo '</table><h2>ভর্তি তথ্য</h2><table class="form-table">';
	uturn_field_rows( $o, 'admission_process_lines', 'ভর্তি প্রক্রিয়া' );
	uturn_field_rows( $o, 'admission_docs_lines', 'প্রয়োজনীয় কাগজপত্র' );
	echo '</table><h2>শিক্ষার্থী কর্নার</h2><table class="form-table">';
	uturn_field_rows( $o, 'students_features_lines', 'সুবিধা' );
	uturn_field_rows( $o, 'students_clubs_lines', 'ক্লাব' );
	uturn_field_rows( $o, 'students_conduct_lines', 'আচরণবিধি' );
	echo '</table>';
}

function uturn_tab_contact() {
	$o = uturn_opts();
	echo '<table class="form-table">';
	uturn_field_text( $o, 'phone', 'ফোন (শুধু টপবারে দেখাবে)' );
	uturn_field_text( $o, 'phone_href', 'ফোন লিংক (tel: এর জন্য, ইংরেজি সংখ্যায়)' );
	uturn_field_text( $o, 'email', 'ইমেইল' );
	uturn_field_text( $o, 'address', 'ঠিকানা (বাংলা)', true );
	uturn_field_text( $o, 'address_en', 'ঠিকানা (ইংরেজি)', true );
	uturn_field_text( $o, 'hours', 'অফিস সময় (বিস্তারিত)', true );
	uturn_field_text( $o, 'hours_short', 'অফিস সময় (সংক্ষিপ্ত)', true );
	uturn_field_area( $o, 'map_embed', 'গুগল ম্যাপ এমবেড কোড', 4 );
	echo '</table>';
}

function uturn_tab_social() {
	$o = uturn_opts();
	echo '<table class="form-table">';
	uturn_field_text( $o, 'social_fb', 'Facebook', true );
	uturn_field_text( $o, 'social_yt', 'YouTube', true );
	uturn_field_text( $o, 'social_x', 'X (Twitter)', true );
	uturn_field_text( $o, 'social_wa', 'WhatsApp', true );
	echo '</table>';
}

function uturn_tab_sms() {
	$o = uturn_opts();
	$U = esc_attr( UTURN_OPT );
	echo '<h2>🔛 মাস্টার সুইচ (শুধু Super Admin)</h2><table class="form-table">';
	echo '<tr><th scope="row">SMS চ্যানেল</th><td><label><input type="checkbox" name="' . $U . '[sms_enable]" value="1"' . checked( $o['sms_enable'], '1', false ) . '> চালু</label> <span class="description">নিচে API URL সেট না করলে চালু হবে না।</span></td></tr>';
	echo '<tr><th scope="row">WhatsApp চ্যানেল</th><td><label><input type="checkbox" name="' . $U . '[wa_enable]" value="1"' . checked( $o['wa_enable'], '1', false ) . '> চালু</label> <span class="description">Meta WhatsApp Cloud API (ফ্রি টায়ার)।</span></td></tr>';
	echo '<tr><th scope="row">🧪 টেস্ট মোড</th><td><label><input type="checkbox" name="' . $U . '[sms_test_mode]" value="1"' . checked( $o['sms_test_mode'], '1', false ) . '> চালু (সুপারিশকৃত)</label> <span class="description">চালু থাকলে বার্তা আসলে যায় না — শুধু লগে জমা হয়। সব ঠিকঠাক দেখে বন্ধ করুন।</span></td></tr>';
	echo '<tr><th scope="row">অগ্রাধিকার</th><td><select name="' . $U . '[nt_pref]"><option value="wa"' . selected( $o['nt_pref'], 'wa', false ) . '>WhatsApp আগে, না গেলে SMS</option><option value="sms"' . selected( $o['nt_pref'], 'sms', false ) . '>SMS আগে, না গেলে WhatsApp</option></select></td></tr>';
	echo '</table><h2>📨 SMS গেটওয়ে (যেকোনো Bulk SMS API)</h2><table class="form-table">';
	uturn_field_text( $o, 'sms_masking', 'সেন্ডার মাস্কিং' );
	uturn_field_text( $o, 'sms_api', 'API কী (রেফারেন্স)', true );
	echo '<tr><th scope="row">API URL টেমপ্লেট *</th><td><input type="text" name="' . $U . '[sms_url]" value="' . esc_attr( $o['sms_url'] ) . '" class="large-text" placeholder="https://api.provider.com/send?api_key=XXXX&to={to}&msg={message}"><p class="description"><code>{to}</code> = 8801XXXXXXXXX · <code>{message}</code> = বার্তা · <code>{masking}</code> = মাস্কিং। উদাহরণ: <code>https://bulksmsbd.net/api/smsapi?api_key=KEY&type=text&number={to}&senderid={masking}&message={message}</code></p></td></tr>';
	echo '<tr><th scope="row">মেথড</th><td><select name="' . $U . '[sms_method]"><option value="GET"' . selected( $o['sms_method'], 'GET', false ) . '>GET</option><option value="POST"' . selected( $o['sms_method'], 'POST', false ) . '>POST</option></select></td></tr>';
	uturn_field_area( $o, 'sms_headers', 'POST হেডার (প্রতি লাইন: Name: value)', 3 );
	uturn_field_area( $o, 'sms_body', 'POST বডি টেমপ্লেট (খালি = শুধু URL)', 3 );
	uturn_field_text( $o, 'sms_success', 'সফল-কীওয়ার্ড (রেসপন্সে থাকলে সফল; খালি = HTTP 200-ই সফল)' );
	echo '</table><h2>💬 WhatsApp Cloud API (Meta)</h2><table class="form-table">';
	uturn_field_text( $o, 'wa_phone_id', 'Phone Number ID', true );
	uturn_field_text( $o, 'wa_token', 'Access Token', true );
	echo '</table><p class="description">Meta for Developers → WhatsApp → API Setup থেকে Phone Number ID + Token নিন। প্রতি মাসে ১০০০ কথোপকথন ফ্রি। বিস্তারিত গাইডে।</p>';
	echo '<h2>🤖 অটোমেশন</h2><table class="form-table">';
	foreach ( array( 'sms_attendance' => 'হাজিরা অটো-SMS (অনুপস্থিতদের অভিভাবকে)', 'sms_result' => 'ফলাফল অটো-SMS (CSV ইমপোর্টের সময়)', 'sms_notice' => 'নোটিশ অটো-SMS (শীঘ্রই)', 'sms_event' => 'ইভেন্ট অটো-SMS (শীঘ্রই)' ) as $k => $label ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td><label><input type="checkbox" name="' . $U . '[' . esc_attr( $k ) . ']" value="1"' . checked( $o[ $k ], '1', false ) . '> চালু</label></td></tr>';
	}
	echo '</table><h2>📝 বার্তা টেমপ্লেট</h2><table class="form-table">';
	uturn_field_area( $o, 'tpl_absent', 'অনুপস্থিতি ({name} {class} {roll} {date} {school})', 3 );
	uturn_field_area( $o, 'tpl_result', 'ফলাফল ({name} {class} {roll} {exam} {year} {gpa} {result} {school})', 3 );
	echo '</table><p class="description">✍️ ম্যানুয়াল পাঠানো + লগ: <b>EduTurn → 📲 SMS পাঠান</b> (School Admin-ও পারবেন)। টেস্ট করতে সেখানে কাস্টম নম্বরে পাঠান।</p>';
}

function uturn_tab_extra() {
	$o = uturn_opts();
	$U = esc_attr( UTURN_OPT );
	echo '<h2>🔒 গোপনীয়তা</h2><table class="form-table">';
	echo '<tr><th scope="row">শিক্ষক ফোন নম্বর</th><td><label><input type="checkbox" name="' . $U . '[show_teacher_phone]" value="1"' . checked( $o['show_teacher_phone'], '1', false ) . '> শিক্ষক পেজে ফোন নম্বর দেখাও</label> <span class="description">বন্ধ রাখলে শুধু ইমেইল/বিষয় দেখাবে।</span></td></tr>';
	echo '</table><h2>🎨 কাস্টমাইজেশন</h2><table class="form-table">';
	uturn_field_area( $o, 'custom_css', 'কাস্টম CSS', 8 );
	echo '</table>';
}
