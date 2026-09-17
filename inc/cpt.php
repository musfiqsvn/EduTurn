<?php
/**
 * EduTurn — Data models: bespoke CPTs + hierarchical taxonomies.
 * Zero plugins: pure register_post_type / register_taxonomy.
 */

defined( 'ABSPATH' ) || exit;

/** singular => plural capability bases (shared with roles.php seeder of caps). */
function uturn_cap_bases() {
	return array(
		'ut_notice'      => 'ut_notices',
		'ut_event'       => 'ut_events',
		'ut_news'        => 'ut_news_items',
		'ut_teacher'     => 'ut_teachers',
		'ut_staff'       => 'ut_staff_members',
		'ut_student'     => 'ut_students',
		'ut_album'       => 'ut_albums',
		'ut_download'    => 'ut_downloads',
		'ut_faq'         => 'ut_faqs',
		'ut_testimonial' => 'ut_testimonials',
		'ut_achievement' => 'ut_achievements',
		'ut_message'     => 'ut_messages',
		'ut_application' => 'ut_applications',
		'ut_result'      => 'ut_results',
		'ut_attendance'  => 'ut_attendances',
		'ut_fee'         => 'ut_fees',
		'ut_assignment'  => 'ut_assignments',
		'ut_submission'  => 'ut_submissions',
		'ut_board'       => 'ut_boards',
	);
}

add_action( 'init', 'uturn_register_models', 5 );
function uturn_register_models() {
	$pub = array( 'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ), 'cap' => true );
	$cpts = array(
		// slug           singular       plural            rewrite    archive     icon                    public menu
		array( 'ut_notice', 'নোটিশ', 'নোটিশসমূহ', 'notice', 'notices', 'dashicons-megaphone', true, 'eduturn' ),
		array( 'ut_event', 'ইভেন্ট', 'ইভেন্টসমূহ', 'event', 'events', 'dashicons-calendar-alt', true, 'eduturn' ),
		array( 'ut_news', 'সংবাদ', 'সংবাদ', 'news', 'news', 'dashicons-media-document', true, 'eduturn' ),
		array( 'ut_teacher', 'শিক্ষক', 'শিক্ষকমণ্ডলী', 'teacher', 'teachers', 'dashicons-businessman', true, false ),
		array( 'ut_album', 'অ্যালবাম', 'গ্যালারি অ্যালবাম', 'album', 'gallery', 'dashicons-format-gallery', true, 'eduturn' ),
		array( 'ut_staff', 'কর্মচারী', 'কর্মচারীবৃন্দ', null, null, 'dashicons-id', false, false ),
		array( 'ut_student', 'শিক্ষার্থী', 'শিক্ষার্থীবৃন্দ', null, null, 'dashicons-graduation-cap', false, false ),
		array( 'ut_download', 'ডাউনলোড', 'ডাউনলোডসমূহ', null, null, 'dashicons-download', false, 'eduturn' ),
		array( 'ut_faq', 'জিজ্ঞাসা', 'সাধারণ জিজ্ঞাসা', null, null, 'dashicons-editor-help', false, 'eduturn' ),
		array( 'ut_testimonial', 'মতামত', 'মতামতসমূহ', null, null, 'dashicons-format-quote', false, 'eduturn' ),
		array( 'ut_achievement', 'অর্জন', 'অর্জনসমূহ', null, null, 'dashicons-awards', false, 'eduturn' ),
		array( 'ut_message', 'বার্তা', 'ইনবক্স (যোগাযোগ)', null, null, 'dashicons-email-alt', false, 'eduturn' ),
		array( 'ut_application', 'ভর্তি আবেদন', 'ভর্তি আবেদনসমূহ', null, null, 'dashicons-clipboard', false, 'eduturn' ),
		array( 'ut_result', 'ফলাফল', 'ফলাফল (ডাটাবেজ)', null, null, 'dashicons-chart-bar', false, 'eduturn' ),
		array( 'ut_attendance', 'হাজিরা', 'হাজিরা রেকর্ড', null, null, 'dashicons-schedule', false, 'eduturn' ),
		array( 'ut_fee', 'ফি', 'ফি রেকর্ড', null, null, 'dashicons-money-alt', false, 'eduturn' ),
		array( 'ut_assignment', 'অ্যাসাইনমেন্ট', 'অ্যাসাইনমেন্ট ও অনলাইন টেস্ট', null, null, 'dashicons-clipboard', false, 'eduturn' ),
		array( 'ut_submission', 'জমা', 'শিক্ষার্থীর জমা', null, null, 'dashicons-upload', false, 'eduturn' ),
		array( 'ut_board', 'পর্ষদ সদস্য', 'পরিচালনা পর্ষদ', null, null, 'dashicons-bank', false, 'eduturn' ),
	);
	foreach ( $cpts as $c ) {
		list( $slug, $sing, $plur, $rw, $arch, $icon, $public, $menu ) = $c;
		$bases = uturn_cap_bases();
		register_post_type(
			$slug,
			array(
				'labels' => array(
					'name' => $plur, 'singular_name' => $sing, 'add_new_item' => 'নতুন ' . $sing . ' যোগ করুন',
					'edit_item' => $sing . ' সম্পাদনা', 'view_item' => $sing . ' দেখুন', 'search_items' => $plur . ' খুঁজুন',
				),
				'public'              => $public,
				'publicly_queryable'  => $public,
				'exclude_from_search' => ! $public,
				'show_ui'             => true,
				'show_in_menu'        => $menu,
				'menu_icon'           => $icon,
				'supports'            => $pub['supports'],
				'has_archive'         => $public ? $arch : false,
				'rewrite'             => $public ? array( 'slug' => $rw, 'with_front' => false ) : false,
				'capability_type'     => array( $slug, $bases[ $slug ] ),
				'map_meta_cap'        => true,
			)
		);
	}

	$taxes = array(
		// slug              plural              post types              rewrite
		array( 'ut_notice_cat', 'নোটিশ ক্যাটাগরি', array( 'ut_notice' ), 'notice-cat' ),
		array( 'ut_event_cat', 'ইভেন্ট ক্যাটাগরি', array( 'ut_event' ), 'event-cat' ),
		array( 'ut_news_cat', 'সংবাদ ক্যাটাগরি', array( 'ut_news' ), 'news-cat' ),
		array( 'ut_gallery_cat', 'গ্যালারি ক্যাটাগরি', array( 'ut_album' ), 'gallery-cat' ),
		array( 'ut_download_cat', 'ডাউনলোড ক্যাটাগরি', array( 'ut_download' ), 'download-cat' ),
		array( 'ut_faq_cat', 'জিজ্ঞাসা ক্যাটাগরি', array( 'ut_faq' ), 'faq-cat' ),
		array( 'ut_department', 'বিভাগ (শিক্ষক/কর্মচারী)', array( 'ut_teacher', 'ut_staff' ), 'department' ),
		array( 'ut_class', 'শ্রেণি (শিক্ষার্থী)', array( 'ut_student' ), 'class' ),
	);
	foreach ( $taxes as $t ) {
		list( $slug, $plur, $types, $rw ) = $t;
		register_taxonomy(
			$slug,
			$types,
			array(
				'labels' => array( 'name' => $plur, 'singular_name' => $plur ),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => $rw, 'with_front' => false ),
			)
		);
	}
}
