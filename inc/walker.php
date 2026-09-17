<?php
/**
 * EduTurn — Menu walkers emitting the static DOM verbatim.
 * Desktop:  li.nav-item > a.nav-link[.active] + ul.dropdown
 * Drawer:   li > (a | button.mnav-btn) + ul.sub
 */

defined( 'ABSPATH' ) || exit;

class UTurn_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="dropdown">';
	}
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = (array) $item->classes;
		$active  = array_intersect( $classes, array( 'current-menu-item', 'current-page-ancestor', 'current-menu-ancestor', 'current-menu-parent' ) ) ? true : false;
		$has     = in_array( 'menu-item-has-children', $classes, true );
		$output .= '<li class="nav-item"><a class="nav-link' . ( $active ? ' active' : '' ) . '" href="' . esc_url( $item->url ) . '"' . ( $active ? ' aria-current="page"' : '' ) . ( $has ? ' aria-haspopup="true"' : '' ) . '>'
			. esc_html( $item->title ) . ( $has ? uturn_icon( 'caret' ) : '' ) . '</a>';
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

class UTurn_Drawer_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="sub">';
	}
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$has = in_array( 'menu-item-has-children', (array) $item->classes, true );
		$cur = in_array( 'current-menu-item', (array) $item->classes, true );
		if ( $has ) {
			$output .= '<li><button type="button" class="mnav-btn" aria-expanded="false">' . esc_html( $item->title ) . uturn_icon( 'caret' ) . '</button>';
		} else {
			$output .= '<li><a href="' . esc_url( $item->url ) . '"' . ( $cur ? ' aria-current="page"' : '' ) . '>' . esc_html( $item->title ) . '</a>';
		}
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/** Pre-seed fallback: hardcoded menu mirroring the static navModel (same classes). */
function uturn_fallback_menu( $drawer = false ) {
	$tree = array(
		array( 'হোম', home_url( '/' ), array() ),
		array( 'আমাদের সম্পর্কে', uturn_url( 'about' ), array(
			array( 'প্রতিষ্ঠানের ইতিহাস', uturn_url( 'about' ) . '#history' ),
			array( 'লক্ষ্য ও উদ্দেশ্য', uturn_url( 'about' ) . '#mission' ),
			array( 'প্রধান শিক্ষকের বার্তা', uturn_url( 'about' ) . '#principal' ),
			array( 'পরিচালনা পর্ষদ', uturn_url( 'about' ) . '#management' ),
			array( 'শিক্ষক ও কর্মচারী', uturn_url( 'teachers' ) ),
		) ),
		array( 'একাডেমিক', uturn_url( 'academic' ), array(
			array( 'শ্রেণিসমূহ', uturn_url( 'academic' ) . '#classes' ),
			array( 'একাডেমিক ক্যালেন্ডার', uturn_url( 'academic' ) . '#calendar' ),
			array( 'ক্লাস রুটিন', uturn_url( 'routine' ) ),
			array( 'পরীক্ষার রুটিন', uturn_url( 'routine' ) . '#exam' ),
			array( 'ফলাফল', uturn_url( 'results' ) ),
		) ),
		array( 'শিক্ষার্থী', uturn_url( 'students' ), array(
			array( 'শিক্ষার্থী কর্নার', uturn_url( 'students' ) ),
			array( 'শিক্ষার্থী তালিকা', uturn_url( 'students-list' ) ),
			array( 'ফলাফল', uturn_url( 'results' ) ),
			array( 'নোটিশ', get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' ) ),
			array( 'ডাউনলোড', uturn_url( 'downloads' ) ),
		) ),
		array( 'ভর্তি', uturn_url( 'admission' ), array(
			array( 'ভর্তি তথ্য', uturn_url( 'admission' ) ),
			array( 'ভর্তি নির্দেশিকা', uturn_url( 'admission' ) . '#guideline' ),
			array( 'অনলাইন আবেদন', uturn_url( 'apply' ) ),
		) ),
		array( 'নোটিশ', get_post_type_archive_link( 'ut_notice' ) ? get_post_type_archive_link( 'ut_notice' ) : home_url( '/notices/' ), array() ),
		array( 'ইভেন্ট', get_post_type_archive_link( 'ut_event' ) ? get_post_type_archive_link( 'ut_event' ) : home_url( '/events/' ), array() ),
		array( 'গ্যালারি', get_post_type_archive_link( 'ut_album' ) ? get_post_type_archive_link( 'ut_album' ) : home_url( '/gallery/' ), array() ),
		array( 'সংবাদ', get_post_type_archive_link( 'ut_news' ) ? get_post_type_archive_link( 'ut_news' ) : home_url( '/news/' ), array() ),
		array( 'যোগাযোগ', uturn_url( 'contact' ), array() ),
	);
	$key = uturn_page_key();
	$map = array( 'হোম' => 'home', 'আমাদের সম্পর্কে' => 'about', 'একাডেমিক' => 'academic', 'শিক্ষার্থী' => 'students', 'ভর্তি' => 'admission', 'নোটিশ' => 'notices', 'ইভেন্ট' => 'events', 'গ্যালারি' => 'gallery', 'সংবাদ' => 'news', 'যোগাযোগ' => 'contact' );
	$html = '';
	foreach ( $tree as $node ) {
		list( $label, $href, $kids ) = $node;
		$active = ( isset( $map[ $label ] ) && $map[ $label ] === $key ) ? ' active' : '';
		if ( $drawer ) {
			if ( $kids ) {
				$html .= '<li><button type="button" class="mnav-btn" aria-expanded="false">' . esc_html( $label ) . uturn_icon( 'caret' ) . '</button><ul class="sub">';
				foreach ( $kids as $k ) {
					$html .= '<li><a href="' . esc_url( $k[1] ) . '">' . esc_html( $k[0] ) . '</a></li>';
				}
				$html .= '</ul></li>';
			} else {
				$html .= '<li><a href="' . esc_url( $href ) . '"' . ( '' !== $active ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a></li>';
			}
		} else {
			$html .= '<li class="nav-item"><a class="nav-link' . $active . '" href="' . esc_url( $href ) . '"' . ( '' !== $active ? ' aria-current="page"' : '' ) . ( $kids ? ' aria-haspopup="true"' : '' ) . '>' . esc_html( $label ) . ( $kids ? uturn_icon( 'caret' ) : '' ) . '</a>';
			if ( $kids ) {
				$html .= '<ul class="dropdown">';
				foreach ( $kids as $k ) {
					$html .= '<li><a href="' . esc_url( $k[1] ) . '">' . esc_html( $k[0] ) . '</a></li>';
				}
				$html .= '</ul>';
			}
			$html .= '</li>';
		}
	}
	return $html;
}
