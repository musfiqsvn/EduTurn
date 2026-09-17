<?php
/**
 * EduTurn — Native meta-box engine (ACF replacement, zero dependencies).
 * Declarative schema -> renderers + one guarded save routine.
 */

defined( 'ABSPATH' ) || exit;

function uturn_meta_schema() {
	$testi_cats = array( 'parent' => 'অভিভাবক', 'student' => 'শিক্ষার্থী', 'alumni' => 'প্রাক্তন শিক্ষার্থী' );
	return array(
		'ut_notice'      => array(
			array( 'id' => '_ut_date_bn', 'label' => 'তারিখ (বাংলা)', 'type' => 'text', 'ph' => '১০ সেপ্টেম্বর ২০২৬' ),
			array( 'id' => '_ut_excerpt', 'label' => 'সংক্ষিপ্ত বিবরণ', 'type' => 'textarea' ),
			array( 'id' => '_ut_attachment', 'label' => 'সংযুক্তি (PDF/ছবি)', 'type' => 'media' ),
		),
		'ut_event'       => array(
			array( 'id' => '_ut_date', 'label' => 'তারিখ (ISO: YYYY-MM-DD)', 'type' => 'text', 'ph' => '2026-12-18' ),
			array( 'id' => '_ut_date_bn', 'label' => 'তারিখ (বাংলা)', 'type' => 'text' ),
			array( 'id' => '_ut_time', 'label' => 'সময়', 'type' => 'text', 'ph' => 'সকাল ৯টা' ),
			array( 'id' => '_ut_location', 'label' => 'স্থান', 'type' => 'text', 'ph' => 'স্কুল অডিটোরিয়াম' ),
			array( 'id' => '_ut_excerpt', 'label' => 'সংক্ষিপ্ত বিবরণ', 'type' => 'textarea' ),
			array( 'id' => '_ut_image', 'label' => 'ইভেন্ট ছবি', 'type' => 'media' ),
		),
		'ut_news'        => array(
			array( 'id' => '_ut_date_bn', 'label' => 'তারিখ (বাংলা)', 'type' => 'text' ),
			array( 'id' => '_ut_excerpt', 'label' => 'সংক্ষিপ্ত বিবরণ', 'type' => 'textarea' ),
			array( 'id' => '_ut_image', 'label' => 'সংবাদ ছবি', 'type' => 'media' ),
		),
		'ut_teacher'     => array(
			array( 'id' => '_ut_designation', 'label' => 'পদবি', 'type' => 'text', 'ph' => 'সহকারী শিক্ষক' ),
			array( 'id' => '_ut_subject', 'label' => 'বিষয়', 'type' => 'text', 'ph' => 'গণিত' ),
			array( 'id' => '_ut_phone', 'label' => 'মোবাইল', 'type' => 'text', 'ph' => '01XXXXXXXXX' ),
			array( 'id' => '_ut_education', 'label' => 'শিক্ষাগত যোগ্যতা', 'type' => 'textarea' ),
			array( 'id' => '_ut_experience', 'label' => 'অভিজ্ঞতা', 'type' => 'text', 'ph' => '১০ বছর' ),
			array( 'id' => '_ut_bg', 'label' => 'মনোগ্রাম রঙ', 'type' => 'color' ),
			array( 'id' => '_ut_photo', 'label' => 'ছবি', 'type' => 'media' ),
			array( 'id' => '_ut_user_id', 'label' => 'লিংকড শিক্ষক অ্যাকাউন্ট', 'type' => 'user', 'role' => 'uturn_teacher' ),
		),
		'ut_staff'       => array(
			array( 'id' => '_ut_designation', 'label' => 'পদবি', 'type' => 'text' ),
			array( 'id' => '_ut_phone', 'label' => 'মোবাইল', 'type' => 'text' ),
			array( 'id' => '_ut_bg', 'label' => 'মনোগ্রাম রঙ', 'type' => 'color' ),
			array( 'id' => '_ut_photo', 'label' => 'ছবি', 'type' => 'media' ),
			array( 'id' => '_ut_user_id', 'label' => 'লিংকড স্টাফ অ্যাকাউন্ট', 'type' => 'user', 'role' => 'uturn_staff' ),
		),
		'ut_student'     => array(
			array( 'id' => '_ut_class', 'label' => 'শ্রেণি', 'type' => 'text', 'ph' => '৬ষ্ঠ' ),
			array( 'id' => '_ut_roll', 'label' => 'রোল', 'type' => 'text' ),
			array( 'id' => '_ut_section', 'label' => 'শাখা', 'type' => 'text', 'ph' => 'ক' ),
			array( 'id' => '_ut_guardian', 'label' => 'অভিভাবক', 'type' => 'text' ),
			array( 'id' => '_ut_phone', 'label' => 'মোবাইল', 'type' => 'text' ),
			array( 'id' => '_ut_dob', 'label' => 'জন্ম তারিখ', 'type' => 'date' ),
			array( 'id' => '_ut_bg', 'label' => 'মনোগ্রাম রঙ', 'type' => 'color' ),
			array( 'id' => '_ut_photo', 'label' => 'ছবি', 'type' => 'media' ),
			array( 'id' => '_ut_star', 'label' => 'কৃতি শিক্ষার্থী (হোমপেজ)', 'type' => 'checkbox' ),
			array( 'id' => '_ut_star_text', 'label' => 'কৃতিত্বের বিবরণ', 'type' => 'text', 'ph' => 'জাতীয় বিজ্ঞান মেলায় ১ম' ),
			array( 'id' => '_ut_user_id', 'label' => 'লিংকড শিক্ষার্থী অ্যাকাউন্ট', 'type' => 'user', 'role' => 'uturn_student' ),
		),
		'ut_album'       => array(
			array( 'id' => '_ut_date_bn', 'label' => 'তারিখ (বাংলা)', 'type' => 'text' ),
			array( 'id' => '_ut_cover', 'label' => 'কভার ছবি', 'type' => 'media' ),
			array( 'id' => '_ut_photos', 'label' => 'ছবির তালিকা (প্রতিটি ছবি আলাদা সারিতে)', 'type' => 'rows', 'preset' => 'photo_list' ),
		),
		'ut_download'    => array(
			array( 'id' => '_ut_file', 'label' => 'ফাইল', 'type' => 'media' ),
			array( 'id' => '_ut_ftype', 'label' => 'ফাইলের ধরন', 'type' => 'select', 'options' => array( 'PDF' => 'PDF', 'DOC' => 'Word/DOC', 'IMG' => 'ছবি', 'OTHER' => 'অন্যান্য' ) ),
			array( 'id' => '_ut_date_bn', 'label' => 'তারিখ (বাংলা)', 'type' => 'text' ),
		),
		'ut_testimonial' => array(
			array( 'id' => '_ut_role', 'label' => 'পরিচয়', 'type' => 'text', 'ph' => 'অভিভাবক · সপ্তম শ্রেণি' ),
			array( 'id' => '_ut_category', 'label' => 'ধরন', 'type' => 'select', 'options' => $testi_cats ),
			array( 'id' => '_ut_bg', 'label' => 'মনোগ্রাম রঙ', 'type' => 'color' ),
			array( 'id' => '_ut_photo', 'label' => 'ছবি (ঐচ্ছিক)', 'type' => 'media' ),
		),
		'ut_achievement' => array(
			array( 'id' => '_ut_num', 'label' => 'সংখ্যা/মান', 'type' => 'text', 'ph' => '১০০%' ),
			array( 'id' => '_ut_year', 'label' => 'বছর', 'type' => 'text', 'ph' => '২০২৬' ),
		),
		'ut_message'     => array(
			array( 'id' => '_ut_phone', 'label' => 'মোবাইল', 'type' => 'text' ),
			array( 'id' => '_ut_email', 'label' => 'ইমেইল', 'type' => 'text' ),
			array( 'id' => '_ut_subject', 'label' => 'বিষয়', 'type' => 'text' ),
		),
		'ut_application' => array(
			array( 'id' => '_ut_ref', 'label' => 'রেফারেন্স নম্বর', 'type' => 'text' ),
			array( 'id' => '_ut_status', 'label' => 'অবস্থা', 'type' => 'select', 'options' => array( 'pending' => 'অপেক্ষমান', 'approved' => 'অনুমোদিত', 'rejected' => 'বাতিল' ) ),
			array( 'id' => '_ut_class', 'label' => 'ভর্তিচ্ছু শ্রেণি', 'type' => 'text' ),
			array( 'id' => '_ut_name_en', 'label' => 'নাম (ইংরেজি)', 'type' => 'text' ),
			array( 'id' => '_ut_dob', 'label' => 'জন্ম তারিখ', 'type' => 'date' ),
			array( 'id' => '_ut_gender', 'label' => 'লিঙ্গ', 'type' => 'select', 'options' => array( 'ছেলে' => 'ছেলে', 'মেয়ে' => 'মেয়ে' ) ),
			array( 'id' => '_ut_birthreg', 'label' => 'জন্ম নিবন্ধন নম্বর', 'type' => 'text' ),
			array( 'id' => '_ut_blood', 'label' => 'রক্তের গ্রুপ', 'type' => 'text' ),
			array( 'id' => '_ut_address', 'label' => 'ঠিকানা', 'type' => 'textarea' ),
			array( 'id' => '_ut_father', 'label' => 'পিতার নাম', 'type' => 'text' ),
			array( 'id' => '_ut_mother', 'label' => 'মাতার নাম', 'type' => 'text' ),
			array( 'id' => '_ut_occupation', 'label' => 'পেশা', 'type' => 'text' ),
			array( 'id' => '_ut_mobile', 'label' => 'মোবাইল', 'type' => 'text' ),
			array( 'id' => '_ut_email', 'label' => 'ইমেইল', 'type' => 'text' ),
			array( 'id' => '_ut_relation', 'label' => 'সম্পর্ক', 'type' => 'text' ),
			array( 'id' => '_ut_school', 'label' => 'পূর্ববর্তী বিদ্যালয়', 'type' => 'text' ),
			array( 'id' => '_ut_lastclass', 'label' => 'সর্বশেষ শ্রেণি', 'type' => 'text' ),
			array( 'id' => '_ut_lastresult', 'label' => 'সর্বশেষ ফলাফল', 'type' => 'text' ),
			array( 'id' => '_ut_year', 'label' => 'পাসের সন', 'type' => 'text' ),
			array( 'id' => '_ut_photo', 'label' => 'শিক্ষার্থীর ছবি', 'type' => 'media' ),
			array( 'id' => '_ut_docs', 'label' => 'সংযুক্ত কাগজপত্র (JSON)', 'type' => 'textarea' ),
		),
		'ut_result'      => array(
			array( 'id' => '_ut_exam', 'label' => 'পরীক্ষা', 'type' => 'select', 'options' => array( 'half-yearly' => 'অর্ধ-বার্ষিক পরীক্ষা', 'annual' => 'বার্ষিক পরীক্ষা', 'first-term' => '১ম সাময়িক পরীক্ষা', 'test' => 'নির্বাচনী পরীক্ষা' ) ),
			array( 'id' => '_ut_exam_bn', 'label' => 'পরীক্ষার নাম (বাংলা)', 'type' => 'text' ),
			array( 'id' => '_ut_year', 'label' => 'বছর', 'type' => 'text', 'ph' => '২০২৬' ),
			array( 'id' => '_ut_class', 'label' => 'শ্রেণি', 'type' => 'text', 'ph' => 'দশম' ),
			array( 'id' => '_ut_roll', 'label' => 'রোল', 'type' => 'text' ),
			array( 'id' => '_ut_reg', 'label' => 'রেজিস্ট্রেশন', 'type' => 'text' ),
			array( 'id' => '_ut_gpa', 'label' => 'জিপিএ', 'type' => 'text', 'ph' => '৪.৮৮' ),
			array( 'id' => '_ut_status', 'label' => 'ফলাফল', 'type' => 'select', 'options' => array( 'pass' => 'উত্তীর্ণ', 'fail' => 'অনুত্তীর্ণ' ) ),
			array( 'id' => '_ut_subjects', 'label' => 'বিষয়ভিত্তিক নম্বর', 'type' => 'rows', 'preset' => 'result_subjects' ),
		),
		'ut_attendance'  => array(
			array( 'id' => '_ut_date', 'label' => 'তারিখ (YYYY-MM-DD)', 'type' => 'date' ),
			array( 'id' => '_ut_class', 'label' => 'শ্রেণি', 'type' => 'text' ),
			array( 'id' => '_ut_present', 'label' => 'উপস্থিত (শিক্ষার্থী পোস্ট ID, কমা দিয়ে)', 'type' => 'textarea' ),
		),
		'ut_fee'         => array(
			array( 'id' => '_ut_student_id', 'label' => 'শিক্ষার্থী (ডিরেক্টরি পোস্ট ID)', 'type' => 'number' ),
			array( 'id' => '_ut_user_id', 'label' => 'লিংকড ইউজার ID', 'type' => 'number' ),
			array( 'id' => '_ut_month', 'label' => 'মাস', 'type' => 'text', 'ph' => 'সেপ্টেম্বর ২০২৬' ),
			array( 'id' => '_ut_amount', 'label' => 'টাকার পরিমাণ', 'type' => 'text', 'ph' => '1600' ),
			array( 'id' => '_ut_status', 'label' => 'অবস্থা', 'type' => 'select', 'options' => array( 'paid' => 'পরিশোধিত', 'due' => 'বকেয়া' ) ),
			array( 'id' => '_ut_method', 'label' => 'মাধ্যম', 'type' => 'text', 'ph' => 'ক্যাশ / বিকাশ' ),
			array( 'id' => '_ut_txn', 'label' => 'লেনদেন নম্বর', 'type' => 'text' ),
			array( 'id' => '_ut_date', 'label' => 'তারিখ', 'type' => 'date' ),
		),
		'ut_board'       => array(
			array( 'id' => '_ut_designation', 'label' => 'পদবি', 'type' => 'text', 'ph' => 'সভাপতি, পরিচালনা পর্ষদ' ),
			array( 'id' => '_ut_excerpt', 'label' => 'সংক্ষিপ্ত পরিচিতি (ঐচ্ছিক)', 'type' => 'textarea' ),
			array( 'id' => '_ut_order', 'label' => 'প্রদর্শন ক্রম', 'type' => 'number' ),
			array( 'id' => '_ut_bg', 'label' => 'মনোগ্রাম রঙ', 'type' => 'color' ),
			array( 'id' => '_ut_photo', 'label' => 'ছবি', 'type' => 'media' ),
		),
	);
}

add_action( 'add_meta_boxes', 'uturn_add_meta_boxes' );
function uturn_add_meta_boxes() {
	foreach ( uturn_meta_schema() as $cpt => $fields ) {
		if ( empty( $fields ) ) {
			continue;
		}
		add_meta_box( 'eduturn-details', 'বিস্তারিত তথ্য — EduTurn', 'uturn_render_meta_box', $cpt, 'normal', 'high' );
	}
}

function uturn_render_meta_box( $post ) {
	$schema = uturn_meta_schema();
	$fields = isset( $schema[ $post->post_type ] ) ? $schema[ $post->post_type ] : array();
	wp_nonce_field( 'uturn_meta_' . $post->post_type, 'uturn_meta_nonce' );
	echo '<table class="form-table eduturn-meta">';
	foreach ( $fields as $f ) {
		$val = get_post_meta( $post->ID, $f['id'], true );
		echo '<tr><th scope="row"><label for="' . esc_attr( $f['id'] ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';
		$ph = isset( $f['ph'] ) ? $f['ph'] : '';
		switch ( $f['type'] ) {
			case 'textarea':
				echo '<textarea class="large-text" rows="3" id="' . esc_attr( $f['id'] ) . '" name="' . esc_attr( $f['id'] ) . '">' . esc_textarea( $val ) . '</textarea>';
				break;
			case 'rows':
				uturn_row_editor( $f['id'], $val, (array) uturn_rows_columns( isset( $f['preset'] ) ? $f['preset'] : '' ) );
				break;
			case 'select':
				echo '<select id="' . esc_attr( $f['id'] ) . '" name="' . esc_attr( $f['id'] ) . '">';
				foreach ( $f['options'] as $k => $label ) {
					echo '<option value="' . esc_attr( $k ) . '"' . selected( $val, $k, false ) . '>' . esc_html( $label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'checkbox':
				echo '<label><input type="checkbox" id="' . esc_attr( $f['id'] ) . '" name="' . esc_attr( $f['id'] ) . '" value="1"' . checked( $val, '1', false ) . '> হ্যাঁ</label>';
				break;
			case 'color':
				echo '<input type="text" class="eduturn-color" id="' . esc_attr( $f['id'] ) . '" name="' . esc_attr( $f['id'] ) . '" value="' . esc_attr( $val ? $val : '#0B4EA8' ) . '">';
				break;
			case 'media':
				$prev = '';
				if ( is_numeric( $val ) && $val > 0 ) {
					$prev = wp_get_attachment_image( (int) $val, 'thumbnail', false, array( 'style' => 'max-width:80px;height:auto;display:block;margin-bottom:6px' ) );
				} elseif ( $val ) {
					$prev = '<code>' . esc_html( $val ) . '</code><br>';
				}
				echo $prev . '<input type="hidden" class="eduturn-media-id" id="' . esc_attr( $f['id'] ) . '" name="' . esc_attr( $f['id'] ) . '" value="' . esc_attr( $val ) . '">'
					. '<button type="button" class="button eduturn-media-btn">মিডিয়া বেছে নিন</button> '
					. '<button type="button" class="button-link eduturn-media-clear">সরান</button>';
				break;
			case 'user':
				$users = get_users( array( 'role' => $f['role'], 'number' => 200, 'fields' => array( 'ID', 'display_name' ) ) );
				echo '<select id="' . esc_attr( $f['id'] ) . '" name="' . esc_attr( $f['id'] ) . '"><option value="0">— লিংক নেই —</option>';
				foreach ( $users as $u ) {
					echo '<option value="' . (int) $u->ID . '"' . selected( (int) $val, $u->ID, false ) . '>' . esc_html( $u->display_name ) . '</option>';
				}
				echo '</select>';
				break;
			default:
				$type = in_array( $f['type'], array( 'date', 'time', 'number' ), true ) ? $f['type'] : 'text';
				echo '<input type="' . $type . '" class="regular-text" id="' . esc_attr( $f['id'] ) . '" name="' . esc_attr( $f['id'] ) . '" value="' . esc_attr( $val ) . '" placeholder="' . esc_attr( $ph ) . '">';
		}
		echo '</td></tr>';
	}
	echo '</table>';
	$GLOBALS['uturn_meta_rendered'] = true;
}

/* Media/color pickers for meta boxes (core libraries only). */
add_action( 'admin_enqueue_scripts', 'uturn_meta_admin_assets' );
function uturn_meta_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! array_key_exists( $screen->post_type, uturn_meta_schema() ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	add_action(
		'admin_print_footer_scripts',
		function () {
			echo "<script>jQuery(function($){\$('.eduturn-color').wpColorPicker();var f;$('.eduturn-media-btn').on('click',function(e){e.preventDefault();var b=$(this);f=wp.media({title:'মিডিয়া বেছে নিন',button:{text:'ব্যবহার করুন'},multiple:false});f.on('select',function(){var a=f.state().get('selection').first().toJSON();b.siblings('.eduturn-media-id').val(a.id);b.siblings('.eduturn-media-clear').show();});f.open();});$('.eduturn-media-clear').on('click',function(e){e.preventDefault();$(this).siblings('.eduturn-media-id').val('');});});</script>";
		}
	);
}

add_action( 'save_post', 'uturn_save_meta', 10, 2 );
function uturn_save_meta( $post_id, $post ) {
	$schema = uturn_meta_schema();
	if ( ! isset( $schema[ $post->post_type ] ) ) {
		return;
	}
	if ( ! isset( $_POST['uturn_meta_nonce'] ) || ! wp_verify_nonce( $_POST['uturn_meta_nonce'], 'uturn_meta_' . $post->post_type ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( $schema[ $post->post_type ] as $f ) {
		$id = $f['id'];
		if ( 'checkbox' === $f['type'] ) {
			if ( isset( $_POST[ $id ] ) ) {
				update_post_meta( $post_id, $id, '1' );
			} else {
				delete_post_meta( $post_id, $id );
			}
			continue;
		}
		if ( ! isset( $_POST[ $id ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $id ] );
		switch ( $f['type'] ) {
			case 'textarea':
				$val = sanitize_textarea_field( $raw );
				break;
			case 'rows':
				$rcols = uturn_rows_columns( isset( $f['preset'] ) ? $f['preset'] : '' );
				$val = uturn_rows_to_text( $raw, $rcols ? count( $rcols ) : 0 );
				break;
			case 'color':
				$val = sanitize_hex_color( $raw );
				break;
			case 'number':
			case 'user':
				$val = absint( $raw );
				break;
			case 'media':
				$val = is_numeric( $raw ) ? absint( $raw ) : sanitize_text_field( $raw );
				break;
			default:
				$val = sanitize_text_field( $raw );
		}
		if ( '' === $val || null === $val ) {
			delete_post_meta( $post_id, $id );
		} else {
			update_post_meta( $post_id, $id, $val );
		}
	}
	/* Auto-fill Bengali display date from publish date when left blank. */
	$autodate = array( 'ut_notice', 'ut_event', 'ut_news', 'ut_album', 'ut_download' );
	if ( in_array( $post->post_type, $autodate, true ) && ! get_post_meta( $post_id, '_ut_date_bn', true ) ) {
		update_post_meta( $post_id, '_ut_date_bn', uturn_bn_date( get_the_date( 'Y-m-d', $post_id ) ) );
	}
}
