<?php
/**
 * EduTurn — Modular homepage. Section order + visibility are driven
 * by the dashboard sorting engine (uturn_home_sections()).
 */
get_header();
?>
<main id="main">
<?php
foreach ( uturn_home_sections() as $section ) {
	get_template_part( 'template-parts/home/' . $section );
}
?>
</main>
<?php
get_footer();
