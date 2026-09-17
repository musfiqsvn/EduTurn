<?php
/**
 * EduTurn — Generic frontend CRUD engine for the dashboard shell.
 * Replaces native wp-admin list/edit screens. Meta fields come from the
 * declarative uturn_meta_schema(); saving reuses uturn_save_meta().
 */

defined( 'ABSPATH' ) || exit;

function uturn_dash_cpt_config() {
	return array(
		'attendance'   => array( 'cpt' => 'ut_attendance', 'plural' => 'হাজিরা রেকর্ড', 'single' => 'হাজিরা', 'cap' => 'edit_ut_attendances', 'new' => false, 'edit' => false, 'delete' => true, 'ed' => false, 'thumb' => false, 'cols' => array( '_ut_date' => 'তারিখ', '_ut_class' => 'শ্রেণি' ), 'tax' => array() ),
		'results'      => array( 'cpt' => 'ut_result', 'plural' => 'ফলাফল ডাটাবেজ', 'single' => 'ফলাফল', 'cap' => 'publish_ut_results', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => false, 'thumb' => false, 'cols' => array( '_ut_exam_bn' => 'পরীক্ষা', '_ut_class' => 'শ্রেণি', '_ut_roll' => 'রোল', '_ut_gpa' => 'জিপিএ', '_ut_status' => 'ফল' ), 'tax' => array() ),
		'fees'         => array( 'cpt' => 'ut_fee', 'plural' => 'ফি রেকর্ড', 'single' => 'ফি', 'cap' => 'edit_ut_fees', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => false, 'thumb' => false, 'cols' => array( '_ut_month' => 'মাস', '_ut_amount' => 'পরিমাণ', '_ut_status' => 'অবস্থা' ), 'tax' => array(), 'toggle' => '_ut_status' ),
		'applications' => array( 'cpt' => 'ut_application', 'plural' => 'ভর্তি আবেদন', 'single' => 'আবেদন', 'cap' => 'edit_ut_applications', 'new' => false, 'edit' => true, 'delete' => true, 'ed' => false, 'thumb' => false, 'cols' => array( '_ut_ref' => 'রেফারেন্স', '_ut_class' => 'শ্রেণি', '_ut_mobile' => 'মোবাইল', '_ut_status' => 'অবস্থা' ), 'tax' => array(), 'review' => true ),
		'messages'     => array( 'cpt' => 'ut_message', 'plural' => 'ইনবক্স', 'single' => 'বার্তা', 'cap' => 'edit_ut_messages', 'new' => false, 'edit' => false, 'delete' => true, 'ed' => false, 'thumb' => false, 'cols' => array( '_ut_subject' => 'বিষয়', '_ut_phone' => 'মোবাইল' ), 'tax' => array() ),
		'notices'      => array( 'cpt' => 'ut_notice', 'plural' => 'নোটিশ', 'single' => 'নোটিশ', 'cap' => 'edit_ut_notices', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => true, 'thumb' => true, 'cols' => array( '_ut_date_bn' => 'তারিখ' ), 'tax' => array( 'ut_notice_cat' => 'ক্যাটাগরি' ) ),
		'news'         => array( 'cpt' => 'ut_news', 'plural' => 'সংবাদ', 'single' => 'সংবাদ', 'cap' => 'edit_ut_news_items', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => true, 'thumb' => true, 'cols' => array( '_ut_date_bn' => 'তারিখ' ), 'tax' => array( 'ut_news_cat' => 'ক্যাটাগরি' ) ),
		'events'       => array( 'cpt' => 'ut_event', 'plural' => 'ইভেন্ট', 'single' => 'ইভেন্ট', 'cap' => 'edit_ut_events', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => true, 'thumb' => true, 'cols' => array( '_ut_date_bn' => 'তারিখ', '_ut_location' => 'স্থান' ), 'tax' => array( 'ut_event_cat' => 'ক্যাটাগরি' ) ),
		'albums'       => array( 'cpt' => 'ut_album', 'plural' => 'গ্যালারি অ্যালবাম', 'single' => 'অ্যালবাম', 'cap' => 'edit_ut_albums', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => true, 'thumb' => true, 'cols' => array( '_ut_date_bn' => 'তারিখ' ), 'tax' => array( 'ut_gallery_cat' => 'ক্যাটাগরি' ) ),
		'downloads'    => array( 'cpt' => 'ut_download', 'plural' => 'ডাউনলোড', 'single' => 'ডাউনলোড', 'cap' => 'edit_ut_downloads', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => true, 'thumb' => false, 'cols' => array( '_ut_ftype' => 'ধরন', '_ut_date_bn' => 'তারিখ' ), 'tax' => array( 'ut_download_cat' => 'ক্যাটাগরি' ) ),
		'faqs'         => array( 'cpt' => 'ut_faq', 'plural' => 'জিজ্ঞাসা', 'single' => 'জিজ্ঞাসা', 'cap' => 'edit_ut_faqs', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => true, 'thumb' => false, 'cols' => array(), 'tax' => array( 'ut_faq_cat' => 'ক্যাটাগরি' ) ),
		'testimonials' => array( 'cpt' => 'ut_testimonial', 'plural' => 'মতামত', 'single' => 'মতামত', 'cap' => 'edit_ut_testimonials', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => true, 'thumb' => false, 'cols' => array( '_ut_role' => 'পরিচয়' ), 'tax' => array() ),
		'achievements' => array( 'cpt' => 'ut_achievement', 'plural' => 'অর্জন', 'single' => 'অর্জন', 'cap' => 'edit_ut_achievements', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => false, 'thumb' => false, 'cols' => array( '_ut_num' => 'মান', '_ut_year' => 'বছর' ), 'tax' => array() ),
		'board-list'   => array( 'cpt' => 'ut_board', 'plural' => 'পরিচালনা পর্ষদ', 'single' => 'সদস্য', 'cap' => 'edit_ut_boards', 'new' => true, 'edit' => true, 'delete' => true, 'ed' => false, 'thumb' => true, 'cols' => array( '_ut_designation' => 'পদবি' ), 'tax' => array() ),
	);
}

function uturn_dash_cfg_by_cpt( $cpt ) {
	foreach ( uturn_dash_cpt_config() as $view => $cfg ) {
		if ( $cfg['cpt'] === $cpt ) {
			$cfg['view'] = $view;
			return $cfg;
		}
	}
	return null;
}

function uturn_dash_crud_dispatch( $view ) {
	$cfgs = uturn_dash_cpt_config();
	if ( ! isset( $cfgs[ $view ] ) ) {
		echo '<div class="notice notice-error" role="alert"><p>Unknown module.</p></div>';
		return;
	}
	$cfg = $cfgs[ $view ];
	$cfg['view'] = $view;
	uturn_dash_crud_list( $cfg );
}

function uturn_dash_crud_edit_dispatch() {
	$cpt = isset( $_GET['cpt'] ) ? sanitize_key( $_GET['cpt'] ) : '';
	$id  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	if ( $id ) {
		$p = get_post( $id );
		if ( ! $p ) {
			echo '<div class="notice notice-error" role="alert"><p>পাওয়া যায়নি।</p></div>';
			return;
		}
		$cpt = $p->post_type;
	}
	$cfg = uturn_dash_cfg_by_cpt( $cpt );
	if ( ! $cfg || ! current_user_can( $cfg['cap'] ) ) {
		echo '<div class="notice notice-error" role="alert"><p>অ্যাক্সেস নেই।</p></div>';
		return;
	}
	if ( $id && ! current_user_can( 'edit_post', $id ) ) {
		echo '<div class="notice notice-error" role="alert"><p>অ্যাক্সেস নেই।</p></div>';
		return;
	}
	if ( ! $id && empty( $cfg['new'] ) ) {
		echo '<div class="notice notice-error" role="alert"><p>নতুন যোগ করা যায় না।</p></div>';
		return;
	}
	uturn_dash_crud_edit( $cfg, $id );
}

/* ================= list ================= */
function uturn_dash_status_pill( $cpt, $key, $val ) {
	if ( $val === '' || $val === null ) {
		return '—';
	}
	$v = (string) $val;
	if ( $key === '_ut_status' ) {
		$map = array(
			'paid' => array( 'পরিশোধিত', 'green' ), 'due' => array( 'বকেয়া', 'red' ),
			'pass' => array( 'উত্তীর্ণ', 'green' ), 'fail' => array( 'অনুত্তীর্ণ', 'red' ),
			'pending' => array( 'অপেক্ষমান', 'amber' ), 'approved' => array( 'অনুমোদিত', 'green' ), 'rejected' => array( 'বাতিল', 'red' ),
		);
		if ( isset( $map[ $v ] ) ) {
			return '<span class="utd-pill ' . $map[ $v ][1] . '">' . $map[ $v ][0] . '</span>';
		}
	}
	return esc_html( mb_substr( $v, 0, 60 ) );
}

function uturn_dash_crud_list( $cfg ) {
	$view = $cfg['view'];
	$sub  = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : '';
	$s    = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$paged = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$in_trash = $sub === 'trash';
	$args = array( 'post_type' => $cfg['cpt'], 'posts_per_page' => 20, 'paged' => $paged, 'orderby' => 'date', 'order' => 'DESC', 'post_status' => $in_trash ? 'trash' : array( 'publish', 'draft', 'pending' ), 's' => $s );
	$q = new WP_Query( $args );
	$n_trash = (int) wp_count_posts( $cfg['cpt'] )->trash;
	echo '<h2 class="wp-heading-inline">' . esc_html( $cfg['plural'] ) . '</h2> ';
	if ( ! empty( $cfg['new'] ) && ! $in_trash ) {
		echo '<a class="page-title-action" href="' . esc_url( uturn_dash_view_url( 'edit', array( 'cpt' => $cfg['cpt'] ) ) ) . '">নতুন ' . esc_html( $cfg['single'] ) . '</a> ';
	}
	if ( $view === 'applications' ) {
		echo '<a class="page-title-action" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_app_export' ), 'uturn_app_export' ) ) . '">CSV ডাউনলোড</a> ';
	}
	echo '<hr class="wp-header-end">';
	if ( isset( $_GET['trashed'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>আবর্জনায় পাঠানো হয়েছে।</p></div>';
	} elseif ( isset( $_GET['restored'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>ফিরিয়ে আনা হয়েছে।</p></div>';
	} elseif ( isset( $_GET['wiped'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>স্থায়ীভাবে মুছে ফেলা হয়েছে।</p></div>';
	} elseif ( isset( $_GET['saved'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সংরক্ষণ করা হয়েছে।</p></div>';
	}
	echo '<p><a href="' . esc_url( uturn_dash_view_url( $view ) ) . '">সব</a> (' . (int) $q->found_posts . ')';
	if ( $n_trash ) {
		echo ' | <a href="' . esc_url( uturn_dash_view_url( $view, array( 'sub' => 'trash' ) ) ) . '">আবর্জনা (' . $n_trash . ')</a>';
	}
	echo '</p>';
	echo '<form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="' . esc_attr( $view ) . '">';
	if ( $in_trash ) {
		echo '<input type="hidden" name="sub" value="trash">';
	}
	echo '<p class="search-box"><input type="search" name="s" value="' . esc_attr( $s ) . '" placeholder="খুঁজুন…"> ';
	submit_button( 'খুঁজুন', 'secondary', '', false );
	echo '</p></form>';
	echo '<div style="overflow-x:auto"><table class="widefat striped" aria-label="কনটেন্ট তালিকা"><thead><tr><th scope="col">শিরোনাম</th>';
	foreach ( $cfg['cols'] as $label ) {
		echo '<th scope="col">' . esc_html( $label ) . '</th>';
	}
	if ( $cfg['tax'] ) {
		echo '<th scope="col">ক্যাটাগরি</th>';
	}
	echo '<th scope="col">তারিখ</th><th scope="col">অ্যাকশন</th></tr></thead><tbody>';
	if ( ! $q->posts ) {
		echo '<tr><td colspan="8">কোনো রেকর্ড নেই।</td></tr>';
	}
	foreach ( $q->posts as $p ) {
		$edit_url = uturn_dash_view_url( 'edit', array( 'cpt' => $cfg['cpt'], 'id' => $p->ID ) );
		echo '<tr><td><strong>' . esc_html( $p->post_title ? $p->post_title : '(শিরোনামহীন)' ) . '</strong>';
		if ( $p->post_status !== 'publish' ) {
			echo ' <span class="utd-pill amber">' . esc_html( $p->post_status === 'draft' ? 'খসড়া' : ( $p->post_status === 'pending' ? 'পর্যালোচনাধীন' : $p->post_status ) ) . '</span>';
		}
		echo '</td>';
		foreach ( $cfg['cols'] as $mk => $label ) {
			echo '<td>' . uturn_dash_status_pill( $cfg['cpt'], $mk, get_post_meta( $p->ID, $mk, true ) ) . '</td>';
		}
		if ( $cfg['tax'] ) {
			$tnames = array();
			foreach ( array_keys( $cfg['tax'] ) as $tx ) {
				foreach ( (array) wp_get_object_terms( $p->ID, $tx, array( 'fields' => 'names' ) ) as $tn ) {
					$tnames[] = $tn;
				}
			}
			echo '<td>' . esc_html( $tnames ? implode( ', ', $tnames ) : '—' ) . '</td>';
		}
		echo '<td>' . esc_html( mysql2date( 'j M Y', $p->post_date ) ) . '</td><td>';
		if ( $in_trash ) {
			echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_dash_delete&sub=restore&id=' . $p->ID ), 'uturn_dash_del_' . $p->ID ) ) . '">ফিরান</a>';
			if ( ! empty( $cfg['delete'] ) ) {
				echo ' | <a style="color:#b32d2e" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_dash_delete&sub=wipe&id=' . $p->ID ), 'uturn_dash_del_' . $p->ID ) ) . '" onclick="return confirm(\'স্থায়ীভাবে মুছবেন? ফেরানো যাবে না।\')">স্থায়ী মুছুন</a>';
			}
		} else {
			$acts = array();
			if ( ! empty( $cfg['edit'] ) && current_user_can( 'edit_post', $p->ID ) ) {
				$acts[] = '<a href="' . esc_url( $edit_url ) . '">সম্পাদনা</a>';
			} elseif ( current_user_can( 'read_post', $p->ID ) || current_user_can( 'edit_post', $p->ID ) ) {
				$acts[] = '<a href="' . esc_url( $edit_url ) . '">দেখুন</a>';
			}
			if ( $view === 'applications' && current_user_can( 'edit_post', $p->ID ) ) {
				$stt = get_post_meta( $p->ID, '_ut_status', true );
				if ( $stt !== 'approved' ) {
					$acts[] = '<a style="color:#0a7b3c" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_app_status&id=' . $p->ID . '&to=approved' ), 'uturn_app_status' ) ) . '">অনুমোদন</a>';
				}
				if ( $stt !== 'rejected' ) {
					$acts[] = '<a style="color:#b32d2e" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_app_status&id=' . $p->ID . '&to=rejected' ), 'uturn_app_status' ) ) . '">বাতিল</a>';
				}
			}
			if ( $view === 'fees' && current_user_can( 'edit_post', $p->ID ) ) {
				$paid = get_post_meta( $p->ID, '_ut_status', true ) === 'paid';
				$acts[] = '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_dash_setmeta&id=' . $p->ID . '&key=_ut_status&val=' . ( $paid ? 'due' : 'paid' ) ), 'uturn_dash_meta_' . $p->ID ) ) . '">' . ( $paid ? 'বকেয়া করুন' : 'পরিশোধ চিহ্নিত' ) . '</a>';
				if ( function_exists( 'uturn_dash_view_url' ) ) {
					$acts[] = '<a href="' . esc_url( add_query_arg( 'id', $p->ID, uturn_dash_view_url( 'fee-receipt' ) ) ) . '">🧾 রসিদ</a>';
				}
			}
			if ( ! empty( $cfg['delete'] ) && current_user_can( 'delete_post', $p->ID ) ) {
				$acts[] = '<a style="color:#b32d2e" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?ut_shell=1&action=uturn_dash_delete&id=' . $p->ID ), 'uturn_dash_del_' . $p->ID ) ) . '" onclick="return confirm(\'আবর্জনায় পাঠাবেন?\')">মুছুন</a>';
			}
			echo implode( ' | ', $acts );
		}
		echo '</td></tr>';
	}
	echo '</tbody></table></div>';
	$total = (int) $q->max_num_pages;
	if ( $total > 1 ) {
		echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links( array( 'total' => $total, 'current' => $paged, 'base' => esc_url( uturn_dash_view_url( $view, array( 's' => $s, 'sub' => $sub ) ) ) . '%_%', 'format' => '&paged=%#%' ) ) . '</div></div>';
	}
}

/* ================= edit ================= */
function uturn_dash_crud_edit( $cfg, $id ) {
	$p = $id ? get_post( $id ) : null;
	$readonly = empty( $cfg['edit'] );
	echo '<p><a href="' . esc_url( uturn_dash_view_url( $cfg['view'] ) ) . '">← সব ' . esc_html( $cfg['plural'] ) . '</a></p>';
	echo '<h2 class="wp-heading-inline">' . ( $id ? 'সম্পাদনা' : 'নতুন ' . esc_html( $cfg['single'] ) ) . '</h2><hr class="wp-header-end">';
	if ( isset( $_GET['saved'] ) ) {
		echo '<div class="notice notice-success" role="status"><p>✅ সংরক্ষণ করা হয়েছে।</p></div>';
	}
	if ( ! empty( $cfg['review'] ) && $p && function_exists( 'uturn_app_review_render' ) ) {
		echo '<div class="utd-card"><h2>📋 আবেদন পর্যালোচনা</h2>';
		uturn_app_review_render( $p );
		echo '</div>';
	}
	if ( $readonly && $p ) {
		echo '<div class="utd-card"><h2>' . esc_html( $p->post_title ) . '</h2>';
		if ( $p->post_content ) {
			echo '<div>' . wp_kses_post( wpautop( $p->post_content ) ) . '</div>';
		}
		echo '<div class="utd-kv">';
		foreach ( (array) ( function_exists( 'uturn_meta_schema' ) ? ( uturn_meta_schema()[ $cfg['cpt'] ] ?? array() ) : array() ) as $f ) {
			echo '<span>' . esc_html( $f['label'] ) . '</span><b>' . esc_html( (string) get_post_meta( $p->ID, $f['id'], true ) ) . '</b>';
		}
		echo '<span>তারিখ</span><b>' . esc_html( mysql2date( 'j F Y, g:i a', $p->post_date ) ) . '</b></div></div>';
		if ( $cfg['cpt'] === 'ut_attendance' ) {
			$present = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $p->ID, '_ut_present', true ) ) ) );
			echo '<div class="utd-card"><h2>✅ উপস্থিত (' . count( $present ) . ' জন)</h2><ul class="utd-list">';
			foreach ( $present as $sid ) {
				$sp = get_post( (int) $sid );
				echo '<li>' . esc_html( $sp ? $sp->post_title : ( '#' . $sid ) ) . ( $sp ? ' <small class="utd-muted">রোল ' . esc_html( get_post_meta( $sp->ID, '_ut_roll', true ) ) . '</small>' : '' ) . '</li>';
			}
			echo '</ul></div>';
		}
		return;
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="uturn_dash_save"><input type="hidden" name="ut_shell" value="1">';
	echo '<input type="hidden" name="cpt" value="' . esc_attr( $cfg['cpt'] ) . '"><input type="hidden" name="id" value="' . (int) $id . '">';
	wp_nonce_field( 'uturn_dash_' . $cfg['cpt'] );
	echo '<table class="form-table">';
	echo '<tr><th scope="row"><label>শিরোনাম *</label></th><td><input type="text" class="large-text" name="post_title" value="' . esc_attr( $p ? $p->post_title : '' ) . '" required style="font-size:17px"></td></tr>';
	$stt = $p ? $p->post_status : 'publish';
	echo '<tr><th scope="row">অবস্থা</th><td><select name="post_status"><option value="publish"' . selected( $stt, 'publish', false ) . '>প্রকাশিত</option><option value="pending"' . selected( $stt, 'pending', false ) . '>পর্যালোচনাধীন</option><option value="draft"' . selected( $stt, 'draft', false ) . '>খসড়া</option></select></td></tr>';
	foreach ( $cfg['tax'] as $tx => $tlabel ) {
		$terms = get_terms( array( 'taxonomy' => $tx, 'hide_empty' => false ) );
		$cur = $id ? wp_get_object_terms( $id, $tx, array( 'fields' => 'ids' ) ) : array();
		echo '<tr><th scope="row">' . esc_html( $tlabel ) . '</th><td>';
		if ( ! is_wp_error( $terms ) && $terms ) {
			echo '<div style="max-height:150px;overflow:auto;border:1px solid #e3edf7;border-radius:8px;padding:8px 12px;background:#fff">';
			foreach ( $terms as $t ) {
				echo '<label style="display:block;font-weight:400"><input type="checkbox" name="tax[' . esc_attr( $tx ) . '][]" value="' . (int) $t->term_id . '"' . checked( in_array( $t->term_id, (array) $cur, true ), true, false ) . '> ' . esc_html( $t->name ) . '</label>';
			}
			echo '</div>';
		}
		echo '<p style="margin:6px 0 0"><input type="text" name="newterm[' . esc_attr( $tx ) . ']" placeholder="নতুন যোগ করুন (ঐচ্ছিক)" style="width:16em"></p></td></tr>';
	}
	if ( ! empty( $cfg['thumb'] ) ) {
		$tid = $id ? (int) get_post_meta( $id, '_thumbnail_id', true ) : 0;
		$prev = $tid ? wp_get_attachment_image( $tid, 'thumbnail', false, array( 'style' => 'max-width:90px;height:auto;display:block;margin-bottom:6px' ) ) : '';
		echo '<tr><th scope="row">ফিচার ছবি</th><td><span class="eduturn-media-prev">' . $prev . '</span><input type="hidden" class="eduturn-media-id" name="_thumbnail_id" value="' . $tid . '"> <button type="button" class="button eduturn-media-btn">ছবি বেছে নিন</button> <button type="button" class="button-link eduturn-media-clear">সরান</button></td></tr>';
	}
	echo '</table>';
	if ( ! empty( $cfg['ed'] ) ) {
		echo '<h3>বিস্তারিত</h3>';
		wp_editor( $p ? $p->post_content : '', 'utd_content', array( 'textarea_name' => 'post_content', 'media_buttons' => true, 'textarea_rows' => 10 ) );
	}
	if ( function_exists( 'uturn_render_meta_box' ) ) {
		$fake = (object) array( 'ID' => (int) $id, 'post_type' => $cfg['cpt'] );
		echo '<h3>বিস্তারিত তথ্য</h3>';
		uturn_render_meta_box( $fake );
	}
	submit_button( $id ? 'হালনাগাদ করুন' : 'প্রকাশ করুন', 'primary', 'submit', false );
	echo ' <a class="button button-secondary" href="' . esc_url( uturn_dash_view_url( $cfg['view'] ) ) . '">বাতিল</a>';
	echo '</form>';
}

/* ================= save / delete / setmeta ================= */
add_action( 'admin_post_uturn_dash_save', 'uturn_dash_save' );
function uturn_dash_save() {
	$cpt = isset( $_POST['cpt'] ) ? sanitize_key( $_POST['cpt'] ) : '';
	$id  = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$cfg = uturn_dash_cfg_by_cpt( $cpt );
	if ( ! $cfg || empty( $cfg['edit'] ) || ! current_user_can( $cfg['cap'] ) || ! check_admin_referer( 'uturn_dash_' . $cpt ) ) {
		wp_die( 'Unauthorized.' );
	}
	if ( $id && ! current_user_can( 'edit_post', $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	$title = sanitize_text_field( wp_unslash( $_POST['post_title'] ?? '' ) );
	if ( $title === '' ) {
		wp_die( 'শিরোনাম দিন।' );
	}
	$status = in_array( ( $_POST['post_status'] ?? '' ), array( 'draft', 'pending' ), true ) ? $_POST['post_status'] : 'publish';
	if ( 'publish' === $status && isset( $cfg['cpt'] ) && 'ut_result' === $cfg['cpt'] && ! current_user_can( 'publish_ut_results' ) && ! current_user_can( 'manage_options' ) ) {
		$status = 'pending'; // teachers can only submit, never publish
	}
	$data = array(
		'post_type' => $cpt, 'post_title' => wp_slash( $title ),
		'post_content' => isset( $_POST['post_content'] ) ? wp_slash( wp_unslash( $_POST['post_content'] ) ) : '',
		'post_status' => $status,
	);
	if ( $id ) {
		$data['ID'] = $id;
		$id = wp_update_post( $data, true );
	} else {
		$id = wp_insert_post( $data, true );
	}
	if ( is_wp_error( $id ) || ! $id ) {
		wp_die( 'সংরক্ষণ ব্যর্থ।' );
	}
	/* taxonomies (post caps govern — same as the rest of the shell) */
	foreach ( $cfg['tax'] as $tx => $tlabel ) {
		$ids = array_map( 'absint', (array) ( $_POST['tax'][ $tx ] ?? array() ) );
		$new = sanitize_text_field( wp_unslash( $_POST['newterm'][ $tx ] ?? '' ) );
		if ( $new !== '' ) {
			$t = wp_insert_term( $new, $tx );
			if ( ! is_wp_error( $t ) ) {
				$ids[] = (int) $t['term_id'];
			}
		}
		wp_set_object_terms( $id, $ids, $tx );
	}
	/* thumbnail */
	if ( ! empty( $cfg['thumb'] ) ) {
		$tid = absint( $_POST['_thumbnail_id'] ?? 0 );
		if ( $tid ) {
			set_post_thumbnail( $id, $tid );
		} else {
			delete_post_meta( $id, '_thumbnail_id' );
		}
	}
	/* meta: save_post already fired uturn_save_meta() via the meta-box nonce. */
	delete_transient( 'uturn_dash_stats' );
	wp_safe_redirect( uturn_dash_view_url( 'edit', array( 'cpt' => $cpt, 'id' => $id, 'saved' => 1 ) ) );
	exit;
}

add_action( 'admin_post_uturn_dash_delete', 'uturn_dash_delete' );
function uturn_dash_delete() {
	$id  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : '';
	$p   = $id ? get_post( $id ) : null;
	$cfg = $p ? uturn_dash_cfg_by_cpt( $p->post_type ) : null;
	if ( $cfg && $cfg['view'] === 'board-list' ) {
		$cfg['view'] = 'board'; // custom manager view
	}
	if ( ! $p || ! $cfg || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_dash_del_' . $id ) || ! current_user_can( 'delete_post', $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	if ( $sub === 'restore' ) {
		wp_untrash_post( $id );
		$go = uturn_dash_view_url( $cfg['view'], array( 'restored' => 1 ) );
	} elseif ( $sub === 'wipe' ) {
		if ( empty( $cfg['delete'] ) ) {
			wp_die( 'Unauthorized.' );
		}
		wp_delete_post( $id, true );
		$go = uturn_dash_view_url( $cfg['view'], array( 'sub' => 'trash', 'wiped' => 1 ) );
	} else {
		if ( empty( $cfg['delete'] ) ) {
			wp_die( 'Unauthorized.' );
		}
		wp_trash_post( $id );
		$go = uturn_dash_view_url( $cfg['view'], array( 'trashed' => 1 ) );
	}
	delete_transient( 'uturn_dash_stats' );
	wp_safe_redirect( $go );
	exit;
}

/* One-click meta flips (fee paid/due). Strict allowlist. */
add_action( 'admin_post_uturn_dash_setmeta', 'uturn_dash_setmeta' );
function uturn_dash_setmeta() {
	$id  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$key = isset( $_GET['key'] ) ? sanitize_key( $_GET['key'] ) : '';
	$val = isset( $_GET['val'] ) ? sanitize_key( $_GET['val'] ) : '';
	$p   = $id ? get_post( $id ) : null;
	$ok  = $p && $p->post_type === 'ut_fee' && $key === '_ut_status' && in_array( $val, array( 'paid', 'due' ), true );
	if ( ! $ok || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_dash_meta_' . $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_die( 'Unauthorized.' );
	}
	update_post_meta( $id, $key, $val );
	delete_transient( 'uturn_dash_stats' );
	wp_safe_redirect( uturn_dash_view_url( 'fees' ) );
	exit;
}

/* ================= read-only views ================= */
function uturn_dash_snotices() {
	$s = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$q = new WP_Query( array( 'post_type' => 'ut_notice', 'posts_per_page' => 20, 'paged' => max( 1, absint( $_GET['paged'] ?? 1 ) ), 'post_status' => 'publish', 's' => $s ) );
	echo '<form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="snotices">';
	echo '<p class="search-box"><input type="search" name="s" value="' . esc_attr( $s ) . '" placeholder="নোটিশ খুঁজুন…"> ';
	submit_button( 'খুঁজুন', 'secondary', '', false );
	echo '</p></form><div class="utd-card"><ul class="utd-list">';
	if ( ! $q->posts ) {
		echo '<li>কোনো নোটিশ নেই।</li>';
	}
	foreach ( $q->posts as $p ) {
		echo '<li><a href="' . esc_url( uturn_dash_view_url( 'notice', array( 'id' => $p->ID ) ) ) . '"><b>' . esc_html( $p->post_title ) . '</b></a><br><small class="utd-muted">' . esc_html( get_post_meta( $p->ID, '_ut_date_bn', true ) ? get_post_meta( $p->ID, '_ut_date_bn', true ) : mysql2date( 'j F Y', $p->post_date ) ) . '</small></li>';
	}
	echo '</ul></div>';
	if ( $q->max_num_pages > 1 ) {
		echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links( array( 'total' => $q->max_num_pages, 'current' => max( 1, absint( $_GET['paged'] ?? 1 ) ), 'base' => esc_url( uturn_dash_view_url( 'snotices', array( 's' => $s ) ) ) . '%_%', 'format' => '&paged=%#%' ) ) . '</div></div>';
	}
}

function uturn_dash_notice_read() {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$p  = $id ? get_post( $id ) : null;
	if ( ! $p || $p->post_type !== 'ut_notice' || $p->post_status !== 'publish' ) {
		echo '<div class="notice notice-error" role="alert"><p>নোটিশ পাওয়া যায়নি।</p></div>';
		return;
	}
	echo '<p><a href="' . esc_url( uturn_dash_view_url( 'snotices' ) ) . '">← সব নোটিশ</a></p>';
	echo '<div class="utd-card"><h2>' . esc_html( $p->post_title ) . '</h2>';
	echo '<p class="utd-muted">' . esc_html( get_post_meta( $p->ID, '_ut_date_bn', true ) ? get_post_meta( $p->ID, '_ut_date_bn', true ) : mysql2date( 'j F Y', $p->post_date ) ) . '</p>';
	echo '<div>' . wp_kses_post( wpautop( $p->post_content ) ) . '</div>';
	$att = get_post_meta( $p->ID, '_ut_attachment', true );
	if ( $att ) {
		$url = is_numeric( $att ) ? wp_get_attachment_url( (int) $att ) : $att;
		if ( $url ) {
			echo '<p><a class="button button-secondary" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">📎 সংযুক্তি দেখুন</a></p>';
		}
	}
	echo '</div>';
}

function uturn_dash_directory() {
	$tab = isset( $_GET['tab'] ) && $_GET['tab'] === 'teachers' ? 'teachers' : 'students';
	$s = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$role = $tab === 'teachers' ? 'uturn_teacher' : 'uturn_student';
	echo '<h2 class="nav-tab-wrapper"><a class="nav-tab' . ( $tab === 'students' ? ' nav-tab-active' : '' ) . '" href="' . esc_url( uturn_dash_view_url( 'directory' ) ) . '">শিক্ষার্থী</a><a class="nav-tab' . ( $tab === 'teachers' ? ' nav-tab-active' : '' ) . '" href="' . esc_url( uturn_dash_view_url( 'directory', array( 'tab' => 'teachers' ) ) ) . '">শিক্ষক</a></h2>';
	echo '<form method="get" action="' . esc_url( uturn_dash_url() ) . '"><input type="hidden" name="view" value="directory"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '">';
	echo '<p class="search-box"><input type="search" name="s" value="' . esc_attr( $s ) . '"> ';
	submit_button( 'খুঁজুন', 'secondary', '', false );
	echo '</p></form>';
	$q = new WP_User_Query( array( 'role' => $role, 'number' => 50, 'search' => $s ? '*' . $s . '*' : '', 'search_columns' => array( 'user_login', 'display_name' ) ) );
	echo '<div style="overflow-x:auto"><table class="widefat striped" aria-label="তালিকা"><thead><tr><th scope="col">নাম</th><th scope="col">' . ( $tab === 'teachers' ? 'বিষয় / পদবি' : 'শ্রেণি / রোল' ) . '</th><th scope="col">মোবাইল</th></tr></thead><tbody>';
	foreach ( (array) $q->get_results() as $u ) {
		$c2 = $tab === 'teachers'
			? trim( ( function_exists( 'uturn_teacher_subject_names' ) ? implode( ', ', uturn_teacher_subject_names( $u->ID ) ) : get_user_meta( $u->ID, '_ut_subject', true ) ) . ' / ' . get_user_meta( $u->ID, '_ut_designation', true ), ' /' )
			: trim( get_user_meta( $u->ID, '_ut_class', true ) . ' / রোল ' . get_user_meta( $u->ID, '_ut_roll', true ), ' /' );
		echo '<tr><td><strong>' . esc_html( $u->display_name ) . '</strong></td><td>' . esc_html( $c2 ? $c2 : '—' ) . '</td><td>' . esc_html( get_user_meta( $u->ID, '_ut_phone', true ) ? get_user_meta( $u->ID, '_ut_phone', true ) : '—' ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
}
