<?php
/**
 * EduTurn — <head> + server-rendered chrome.
 * DOM is byte-identical to the static source (same roots, classes, IDs);
 * the JS compatibility shim detects data-wp="1" and binds instead of re-rendering.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo esc_attr( uturn_page_key() ); ?>" data-root="" data-wp="1" lang="<?php echo esc_attr( uturn_lang() ); ?>">
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php echo esc_html( uturn_t( 'মূল কনটেন্টে যান', 'Skip to content' ) ); ?></a>
<div id="topbar-root"><?php get_template_part( 'template-parts/chrome/topbar' ); ?></div>
<div id="header-root"><?php get_template_part( 'template-parts/chrome/header-main' ); ?></div>
<?php get_template_part( 'template-parts/chrome/drawer' ); ?>
