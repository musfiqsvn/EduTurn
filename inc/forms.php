<?php
/**
 * EduTurn — Frontend form controllers (contact inbox) + toast feedback.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_uturn_contact', 'uturn_handle_contact' );
add_action( 'admin_post_uturn_contact', 'uturn_handle_contact' );
function uturn_handle_contact() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$fail = function () use ( $back ) {
		wp_safe_redirect( add_query_arg( 'uturn_msg', 'error', $back ) );
		exit;
	};
	if ( ! isset( $_POST['uturn_contact_nonce'] ) || ! wp_verify_nonce( $_POST['uturn_contact_nonce'], 'uturn_contact' ) ) {
		$fail();
	}
	if ( ! empty( $_POST['c_web'] ) ) { // honeypot
		$fail();
	}
	$name    = sanitize_text_field( wp_unslash( isset( $_POST['name'] ) ? $_POST['name'] : '' ) );
	$phone   = uturn_valid_phone( isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '' );
	$email   = sanitize_email( wp_unslash( isset( $_POST['email'] ) ? $_POST['email'] : '' ) );
	$subject = sanitize_text_field( wp_unslash( isset( $_POST['subject'] ) ? $_POST['subject'] : '' ) );
	$message = sanitize_textarea_field( wp_unslash( isset( $_POST['message'] ) ? $_POST['message'] : '' ) );
	if ( mb_strlen( $name ) < 3 || ! '' === $phone || mb_strlen( $message ) < 5 ) {
		$fail();
	}
	wp_insert_post(
		array(
			'post_type'   => 'ut_message',
			'post_title'  => $name . ( $subject ? ' — ' . $subject : '' ),
			'post_content'=> $message,
			'post_status' => 'publish',
			'meta_input'  => array( '_ut_phone' => $phone, '_ut_email' => $email, '_ut_subject' => $subject ),
		)
	);
	$admin = get_option( 'admin_email' );
	if ( $admin ) {
		eduturn_safe_mail( $admin, 'নতুন যোগাযোগ বার্তা: ' . $name, "নাম: {$name}\nমোবাইল: {$phone}\nইমেইল: {$email}\nবিষয়: {$subject}\n\n{$message}" );
	}
	wp_safe_redirect( add_query_arg( 'uturn_msg', 'sent', $back ) );
	exit;
}

/* Toast feedback after redirect (reuses the theme's toast component). */
add_action( 'wp_footer', 'uturn_form_toast', 30 );
function uturn_form_toast() {
	if ( ! isset( $_GET['uturn_msg'] ) ) {
		return;
	}
	$map = array(
		'sent'             => array( 'sent', 'আপনার বার্তা পাঠানো হয়েছে। শীঘ্রই যোগাযোগ করা হবে।' ),
		'attendance-saved' => array( 'sent', 'হাজিরা সফলভাবে সংরক্ষিত হয়েছে।' ),
		'error'            => array( 'error', 'অনুগ্রহ করে সঠিক তথ্য দিন।' ),
		'form-error'       => array( 'error', 'তথ্য যাচাই ব্যর্থ — ফরমটি সঠিকভাবে পূরণ করুন।' ),
		'app-error'        => array( 'error', 'আবেদন জমা হয়নি — সব আবশ্যক ঘর ও ছবি (≤১০০KB) যাচাই করুন।' ),
	);
	$key = sanitize_key( $_GET['uturn_msg'] );
	if ( ! isset( $map[ $key ] ) ) {
		return;
	}
	echo '<script>document.addEventListener("DOMContentLoaded",function(){if(window.AB&&AB.toast){AB.toast(' . wp_json_encode( $map[ $key ][1], JSON_UNESCAPED_UNICODE ) . ',' . wp_json_encode( $map[ $key ][0], JSON_UNESCAPED_UNICODE ) . ');}});</script>';
}
