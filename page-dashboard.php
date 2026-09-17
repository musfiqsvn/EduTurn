<?php
/**
 * Template Name: Dashboard Shell
 * Standalone role dashboard — intentionally NO site header/footer/menus.
 * The only way back to the public site is the in-shell button.
 */
defined( 'ABSPATH' ) || exit;
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( uturn_dash_url() ) );
	exit;
}
if ( ! function_exists( 'uturn_dash_render' ) ) {
	wp_die( 'Dashboard module missing.' );
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?php echo esc_html( wp_get_current_user()->display_name . ' · ড্যাশবোর্ড · ' . get_bloginfo( 'name' ) ); ?></title>
<?php wp_head(); ?>
</head>
<body class="ut-dash">
<?php uturn_dash_render(); ?>
<?php wp_footer(); ?>
</body>
</html>
