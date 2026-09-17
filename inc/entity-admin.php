<?php
/**
 * EduTurn — Module 2: bespoke SMS entity screens.
 * Super Admin manages Staff/Teachers/Students WITHOUT touching wp-admin/users.php.
 * Every account auto-syncs a linked directory CPT post (single source of truth).
 */

defined( 'ABSPATH' ) || exit;

function uturn_entities() {
	return array(
		'student' => array(
			'role' => 'uturn_student', 'cpt' => 'ut_student', 'label' => 'শিক্ষার্থী', 'plural' => 'শিক্ষার্থীবৃন্দ',
			'cols' => array( 'student_code' => 'স্টুডেন্ট ID', 'class' => 'শ্রেণি', 'roll' => 'রোল', 'section' => 'শাখা' ),
			'extras' => array(
				array( 'key' => '_ut_class', 'label' => 'শ্রেণি' ), array( 'key' => '_ut_roll', 'label' => 'রোল' ),
				array( 'key' => '_ut_section', 'label' => 'শাখা' ), array( 'key' => '_ut_guardian', 'label' => 'অভিভাবক' ),
			),
		),
		'teacher' => array(
			'role' => 'uturn_teacher', 'cpt' => 'ut_teacher', 'label' => 'শিক্ষক', 'plural' => 'শিক্ষকমণ্ডলী',
			'cols' => array( 'subject' => 'বিষয়', 'group' => 'বিভাগ', 'designation' => 'পদবি' ),
			'extras' => array(
				array( 'key' => '_ut_subject', 'label' => 'বিষয়' ), array( 'key' => '_ut_designation', 'label' => 'পদবি' ),
				array( 'key' => '_ut_education', 'label' => 'শিক্ষাগত যোগ্যতা', 'type' => 'textarea' ),
			),
		),
		'staff'   => array(
			'role' => 'uturn_staff', 'cpt' => 'ut_staff', 'label' => 'কর্মচারী', 'plural' => 'কর্মচারীবৃন্দ',
			'cols' => array( 'designation' => 'পদবি' ),
			'extras' => array( array( 'key' => '_ut_designation', 'label' => 'পদবি' ) ),
		),
	);
}

function uturn_default_pass() {
	return apply_filters( 'uturn_default_pass', 'eduturn' );
}

add_action( 'admin_menu', 'uturn_entity_menus' );
function uturn_entity_menus() {
	foreach ( uturn_entities() as $id => $e ) {
		add_submenu_page(
			'eduturn', $e['plural'], $e['plural'], 'uturn_manage_academic', 'eduturn-' . $id . 's',
			function () use ( $id ) { uturn_entity_screen( $id ); }
		);
	}
}

function uturn_entity_screen( $id ) {
	eduturn_license_require( 'erp' );
	$ents = uturn_entities();
	$e    = $ents[ $id ];
	$view = isset( $_GET['view'] ) ? $_GET['view'] : 'list';
	echo '<div class="wrap eduturn-dashboard"><h1 class="wp-heading-inline">' . esc_html( $e['plural'] ) . ' (SMS)</h1> ';
	if ( 'list' === $view ) {
		echo '<a class="page-title-action" href="' . esc_url( admin_url( 'admin.php?page=eduturn-' . $id . 's&view=form' ) ) . '">নতুন ' . esc_html( $e['label'] ) . '</a>';
	}
	echo '<hr class="wp-header-end">';
	if ( isset( $_GET['uturn_notice'] ) ) {
		$msgs = array( 'saved' => 'সফলভাবে সংরক্ষণ করা হয়েছে।', 'deleted' => 'মুছে ফেলা হয়েছে।', 'error' => 'ত্রুটি! তথ্য যাচাই করে আবার চেষ্টা করুন।', 'dup' => 'এই মোবাইল নম্বর অন্য অ্যাকাউন্টে ব্যবহৃত — অন্য নম্বর দিন।' );
		$code = $_GET['uturn_notice'];
		if ( 'saved' === $code && ! empty( $_GET['new_login'] ) ) {
			echo '<div class="notice notice-success" role="status"><p><b>লগইন তথ্য (এখনই লিখে রাখুন / দিয়ে দিন):</b> আইডি <code>' . esc_html( wp_unslash( $_GET['new_login'] ) ) . '</code> · পাসওয়ার্ড <code>' . esc_html( isset( $_GET['new_pass'] ) ? wp_unslash( $_GET['new_pass'] ) : uturn_default_pass() ) . '</code></p></div>';
		}
		if ( 'dup' === $code || 'error' === $code ) {
			echo '<div class="notice notice-error" role="alert"><p>' . esc_html( $msgs[ $code ] ) . '</p></div>';
		}
		if ( isset( $msgs[ $code ] ) && 'dup' !== $code && 'error' !== $code ) {
			echo '<div class="notice notice-' . ( 'error' === $code ? 'error' : 'success' ) . '"><p>' . esc_html( $msgs[ $code ] ) . '</p></div>';
		}
	}
	if ( 'form' === $view ) {
		uturn_entity_form( $id, $e );
	} else {
		uturn_entity_list( $id, $e );
	}
	echo '</div>';
}

function uturn_entity_list( $id, $e ) {
	$paged  = max( 1, absint( isset( $_GET['paged'] ) ? $_GET['paged'] : 1 ) );
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$args   = array(
		'role' => $e['role'], 'number' => 20, 'paged' => $paged,
		'search' => $search ? '*' . $search . '*' : '',
		'search_columns' => array( 'user_login', 'display_name', 'user_email' ),
	);
	$f_sub = $f_grp = 0;
	$f_desig = $f_status = '';
	if ( 'teacher' === $id ) {
		$f_sub = isset( $_GET['f_subject'] ) ? absint( $_GET['f_subject'] ) : 0;
		$f_grp = isset( $_GET['f_group'] ) ? absint( $_GET['f_group'] ) : 0;
		$f_desig = isset( $_GET['f_desig'] ) ? sanitize_text_field( wp_unslash( $_GET['f_desig'] ) ) : '';
		$f_status = isset( $_GET['f_status'] ) ? sanitize_key( $_GET['f_status'] ) : '';
		$mq = array();
		if ( $f_sub ) {
			$mq[] = array(
				'relation' => 'OR',
				array( 'key' => '_ut_subject_id', 'value' => (string) $f_sub ),
				array( 'key' => '_ut_subject_ids', 'value' => 'i:' . $f_sub . ';', 'compare' => 'LIKE' ),
			);
		}
		if ( $f_grp ) {
			$mq[] = array( 'key' => '_ut_group_id', 'value' => (string) $f_grp );
		}
		if ( $f_desig !== '' ) {
			$mq[] = array( 'key' => '_ut_designation', 'value' => $f_desig );
		}
		if ( 'inactive' === $f_status ) {
			$mq[] = array( 'key' => '_ut_inactive', 'value' => '1' );
		} elseif ( 'active' === $f_status ) {
			$mq[] = array( 'relation' => 'OR', array( 'key' => '_ut_inactive', 'compare' => 'NOT EXISTS' ), array( 'key' => '_ut_inactive', 'value' => '1', 'compare' => '!=' ) );
		}
		if ( $mq ) {
			$args['meta_query'] = $mq;
		}
	}
	$q = new WP_User_Query( $args );
	echo '<form method="get"><input type="hidden" name="page" value="eduturn-' . esc_attr( $id ) . 's">';
	if ( 'teacher' === $id && function_exists( 'uturn_subjects_all' ) ) {
		echo '<div class="utx-filters" style="margin:0 0 10px">';
		echo '<select name="f_subject"><option value="0">সব বিষয়</option>';
		foreach ( uturn_subjects_all() as $ss ) {
			echo '<option value="' . (int) $ss['id'] . '"' . selected( $f_sub, (int) $ss['id'], false ) . '>' . esc_html( $ss['name'] ) . '</option>';
		}
		echo '</select> <select name="f_group"><option value="0">সব বিভাগ</option>';
		foreach ( uturn_groups_all() as $gg ) {
			echo '<option value="' . (int) $gg['id'] . '"' . selected( $f_grp, (int) $gg['id'], false ) . '>' . esc_html( $gg['name'] ) . '</option>';
		}
		echo '</select> <select name="f_desig"><option value="">সব পদবি</option>';
		foreach ( array( 'সহকারী শিক্ষক', 'সিনিয়র শিক্ষক', 'সহকারী প্রধান শিক্ষক', 'প্রধান শিক্ষক', 'প্রভাষক', 'খণ্ডকালীন শিক্ষক' ) as $dg ) {
			echo '<option' . selected( $f_desig, $dg, false ) . '>' . esc_html( $dg ) . '</option>';
		}
		echo '</select> <select name="f_status"><option value="">সব অবস্থা</option><option value="active"' . selected( $f_status, 'active', false ) . '>সক্রিয়</option><option value="inactive"' . selected( $f_status, 'inactive', false ) . '>নিষ্ক্রিয়</option></select>';
		echo '</div>';
	}
	echo '<p class="search-box"><input type="search" name="s" value="' . esc_attr( $search ) . '"> ';
	submit_button( 'খুঁজুন', 'secondary', '', false );
	echo '</p></form>';
	echo '<div style="overflow-x:auto"><table class="widefat fixed striped utx-table"><thead><tr><th scope="col">নাম</th><th scope="col">ইউজারনেম</th>';
	foreach ( $e['cols'] as $label ) {
		echo '<th scope="col">' . esc_html( $label ) . '</th>';
	}
	if ( 'teacher' === $id ) {
		echo '<th scope="col">মোবাইল</th><th scope="col">অবস্থা</th><th scope="col">অ্যাকশন</th></tr></thead><tbody>';
	} else {
		echo '<th scope="row">মোবাইল</th><th scope="row">অ্যাকশন</th></tr></thead><tbody>';
	}
	if ( empty( $q->results ) ) {
		echo '<tr><td colspan="' . ( 'teacher' === $id ? '8' : ( 'student' === $id ? '8' : '6' ) ) . '"><div class="utx-empty utx-empty-sm"><div class="utx-empty-ic">🔍</div><h3>কোনো রেকর্ড পাওয়া যায়নি</h3><p>ফিল্টার বদলে আবার চেষ্টা করুন।</p></div></td></tr>';
	}
	foreach ( (array) $q->results as $u ) {
		$photo  = (int) get_user_meta( $u->ID, '_ut_photo_id', true );
		$avatar = $photo ? wp_get_attachment_image( $photo, 'thumbnail', false, array( 'style' => 'width:36px;height:36px;border-radius:50%;vertical-align:middle;margin-right:8px' ) ) : get_avatar( $u->ID, 36 );
		$edit   = admin_url( 'admin.php?page=eduturn-' . $id . 's&view=form&user_id=' . $u->ID );
		$del    = wp_nonce_url( admin_url( 'admin-post.php?action=uturn_entity_delete&entity=' . $id . '&user_id=' . $u->ID ), 'uturn_entity_delete_' . $u->ID );
		echo '<tr><td><strong>' . $avatar . esc_html( $u->display_name ) . '</strong>' . ( 'teacher' === $id && get_user_meta( $u->ID, '_ut_classteacher_of', true ) ? ' <span>🏫 ' . esc_html( get_user_meta( $u->ID, '_ut_classteacher_of', true ) ) . '</span>' : '' ) . '</td><td>' . esc_html( $u->user_login ) . '</td>';
		foreach ( $e['cols'] as $mk => $label ) {
			$cv = (string) get_user_meta( $u->ID, '_ut_' . $mk, true );
			if ( 'teacher' === $id && 'subject' === $mk && $cv !== '' ) {
				echo '<td><span class="utd-pill blue">' . esc_html( $cv ) . '</span></td>';
			} elseif ( 'teacher' === $id && 'group' === $mk && $cv !== '' ) {
				echo '<td><span class="utd-pill">' . esc_html( $cv ) . '</span></td>';
			} else {
				echo '<td>' . esc_html( $cv !== '' ? $cv : '—' ) . '</td>';
			}
		}
		echo '<td>' . esc_html( get_user_meta( $u->ID, '_ut_phone', true ) ) . '</td>';
		if ( 'teacher' === $id ) {
			$inact = get_user_meta( $u->ID, '_ut_inactive', true ) === '1';
			echo '<td>' . ( $inact ? '<span class="utd-pill amber">নিষ্ক্রিয়</span>' : '<span class="utd-pill green">সক্রিয়</span>' ) . '</td>';
		}
		$reset = wp_nonce_url( admin_url( 'admin-post.php?action=uturn_entity_resetpw&entity=' . $id . '&user_id=' . $u->ID ), 'uturn_entity_resetpw_' . $u->ID );
		$acts = array( '<a href="' . esc_url( $edit ) . '">সম্পাদনা</a>' );
		if ( 'teacher' === $id ) {
			$linked = get_posts( array( 'post_type' => 'ut_teacher', 'meta_key' => '_ut_user_id', 'meta_value' => $u->ID, 'posts_per_page' => 1, 'post_status' => 'any' ) );
			if ( $linked ) {
				$acts[] = '<a href="' . esc_url( get_permalink( $linked[0]->ID ) ) . '" target="_blank" rel="noopener">প্রোফাইল দেখুন</a>';
			}
			$tg = wp_nonce_url( admin_url( 'admin-post.php?action=uturn_teacher_toggle&entity=teacher&user_id=' . $u->ID ), 'uturn_teacher_toggle_' . $u->ID );
			$acts[] = '<a href="' . esc_url( $tg ) . '">' . ( $inact ? 'সক্রিয় করুন' : 'নিষ্ক্রিয় করুন' ) . '</a>';
		}
		$acts[] = '<a href="' . esc_url( $reset ) . '" onclick="return confirm(\'পাসওয়ার্ড ডিফল্টে রিসেট হবে। চালিয়ে যাবেন?\')">পাসওয়ার্ড রিসেট</a>';
		$acts[] = '<a href="' . esc_url( $del ) . '" style="color:#b32d2e" onclick="return confirm(\'মুছে ফেলবেন? সংশ্লিষ্ট ডিরেক্টরি প্রোফাইলও মুছে যাবে।\')">মুছুন</a>';
		echo '<td>' . implode( ' | ', $acts ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
	$total = (int) $q->get_total();
	if ( $total > 20 ) {
		$base = admin_url( 'admin.php?page=eduturn-' . $id . 's&s=' . rawurlencode( $search ) );
		if ( 'teacher' === $id ) {
			$base .= '&f_subject=' . $f_sub . '&f_group=' . $f_grp . '&f_desig=' . rawurlencode( $f_desig ) . '&f_status=' . $f_status;
		}
		echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links(
			array( 'total' => ceil( $total / 20 ), 'current' => $paged, 'base' => $base . '%_%', 'format' => '&paged=%#%' )
		) . '</div></div>';
	}
}

function uturn_entity_form( $id, $e ) {
	$uid = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
	$u   = $uid ? get_userdata( $uid ) : null;
	if ( $uid && ! $u ) {
		echo '<div class="notice notice-error" role="alert"><p>ব্যবহারকারী পাওয়া যায়নি।</p></div>';
		return;
	}
	$val = function ( $k ) use ( $u ) { return $u ? get_user_meta( $u->ID, $k, true ) : ''; };
	$pid = (int) $val( '_ut_photo_id' );
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="uturn_entity_save"><input type="hidden" name="entity" value="' . esc_attr( $id ) . '"><input type="hidden" name="user_id" value="' . (int) $uid . '">';
	wp_nonce_field( 'uturn_entity_save', 'uturn_entity_nonce' );
	echo '<table class="form-table">';
	$uname = $u ? $u->user_login : '';
	echo '<tr><th scope="row"><label>ইউজারনেম *</label></th><td><input type="text" class="regular-text" name="user_login" value="' . esc_attr( $uname ) . '" placeholder="যেমন: STD-2026-001"' . ( $u ? ' readonly' : ' required' ) . '><p class="description">এটাই লগইন আইডি — শিক্ষক/শিক্ষার্থীকে দিয়ে দিন। পরে বদলানো যায় না।</p></td></tr>';
	echo '<tr><th scope="row"><label>পুরো নাম *</label></th><td><input type="text" class="regular-text" name="display_name" value="' . esc_attr( $u ? $u->display_name : '' ) . '" required></td></tr>';
	echo '<tr><th scope="row"><label>ইমেইল</label></th><td><input type="email" class="regular-text" name="email" value="' . esc_attr( $u ? $u->user_email : '' ) . '"></td></tr>';
	echo '<tr><th scope="row"><label>পাসওয়ার্ড' . ( $u ? ' (খালি রাখলে অপরিবর্তিত)' : '' ) . '</label></th><td><input type="text" class="regular-text" name="pass" value="' . esc_attr( $u ? '' : uturn_default_pass() ) . '"' . ( $u ? '' : ' required' ) . '><p class="description">ডিফল্ট পাসওয়ার্ড — প্রথম লগইনের পর পোর্টাল থেকে বদলে নিতে বলুন।</p></td></tr>';
	echo '<tr><th scope="row"><label>মোবাইল</label></th><td><input type="text" class="regular-text" name="meta[_ut_phone]" value="' . esc_attr( $val( '_ut_phone' ) ) . '"><p class="description">এই নম্বর দিয়েও লগইন করা যাবে (প্রতিটি নম্বর শুধু একজনের)।</p></td></tr>';
	echo '<tr><th scope="row"><label>হোয়াটসঅ্যাপ নম্বর</label></th><td><input type="text" class="regular-text" name="meta[_ut_wa]" value="' . esc_attr( $val( '_ut_wa' ) ) . '"><p class="description">খালি রাখলে মোবাইল নম্বরেই হোয়াটসঅ্যাপ যাবে।</p></td></tr>';
	if ( 'teacher' === $id ) {
		$ct_terms = function_exists( 'uturn_class_terms' ) ? uturn_class_terms() : get_terms( array( 'taxonomy' => 'ut_class', 'hide_empty' => false ) );
		echo '<tr><th scope="row"><label>শ্রেণি-শিক্ষকের দায়িত্ব</label></th><td><input type="text" class="regular-text" name="meta[_ut_classteacher_of]" list="ut-ct-list" value="' . esc_attr( $val( '_ut_classteacher_of' ) ) . '" placeholder="যেমন: ৮ম">';
		if ( ! is_wp_error( $ct_terms ) && $ct_terms ) {
			echo '<datalist id="ut-ct-list">';
			foreach ( $ct_terms as $tt ) { echo '<option value="' . esc_attr( $tt->name ) . '">'; }
			echo '</datalist>';
		}
		echo '<p class="description">এই শিক্ষক যে শ্রেণির হাজিরা নিলে অনুপস্থিতদের অভিভাবকের কাছে SMS যাবে।</p></td></tr>';
	}
	if ( 'teacher' === $id && function_exists( 'uturn_subjects_all' ) ) {
		$gid = (int) $val( '_ut_group_id' );
		/* Preselect: saved multi ids + legacy single id + legacy text name match. */
		$sids = $val( '_ut_subject_ids' );
		$sids = is_array( $sids ) ? array_map( 'absint', $sids ) : array();
		$leg_id = (int) $val( '_ut_subject_id' );
		if ( $leg_id && ! in_array( $leg_id, $sids, true ) ) {
			$sids[] = $leg_id;
		}
		if ( ! $sids && $val( '_ut_subject' ) ) {
			foreach ( uturn_subjects_all() as $ss ) {
				if ( $ss['name'] === $val( '_ut_subject' ) ) { $sids[] = (int) $ss['id']; break; }
			}
		}
		echo '<tr><th scope="row"><label>বিষয়সমূহ</label></th><td><div class="ut-checklist">';
		foreach ( uturn_subjects_all() as $ss ) {
			$sv = (int) $ss['id'];
			$dis = empty( $ss['active'] ) && ! in_array( $sv, $sids, true );
			echo '<label><input type="checkbox" name="meta[_ut_subject_ids][]" value="' . $sv . '"' . checked( in_array( $sv, $sids, true ), true, false ) . ( $dis ? ' disabled' : '' ) . '> ' . esc_html( $ss['name'] ) . ( empty( $ss['active'] ) ? ' (নিষ্ক্রিয়)' : '' ) . '</label>';
		}
		echo '</div><p class="description">একাধিক বিষয় টিক দিন (যেমন: গণিত + পদার্থ)। প্রথম টিক-ই প্রধান বিষয়। তালিকায় নেই? <b>একাডেমিক → বিষয়সমূহ</b> থেকে আগে যোগ করুন।</p></td></tr>';
		echo '<tr><th scope="row"><label>বিভাগ / গ্রুপ</label></th><td><select name="meta[_ut_group_id]" class="regular-text"><option value="0">— বাছাই করুন —</option>';
		foreach ( uturn_groups_all() as $gg ) {
			echo '<option value="' . (int) $gg['id'] . '"' . selected( $gid, (int) $gg['id'], false ) . ( empty( $gg['active'] ) ? ' disabled' : '' ) . '>' . esc_html( $gg['name'] ) . ( empty( $gg['active'] ) ? ' (নিষ্ক্রিয়)' : '' ) . '</option>';
		}
		echo '</select></td></tr>';
	}
	foreach ( $e['extras'] as $f ) {
		if ( 'teacher' === $id && '_ut_subject' === $f['key'] ) {
			continue; // replaced by the dropdown above
		}
		$v = $val( $f['key'] );
		echo '<tr><th scope="row"><label>' . esc_html( $f['label'] ) . '</label></th><td>';
		if ( isset( $f['type'] ) && 'textarea' === $f['type'] ) {
			echo '<textarea class="large-text" rows="2" name="meta[' . esc_attr( $f['key'] ) . ']">' . esc_textarea( $v ) . '</textarea>';
		} else {
			$dl = ( 'teacher' === $id && '_ut_designation' === $f['key'] ) ? ' list="ut-desig-list"' : '';
			echo '<input type="text" class="regular-text" name="meta[' . esc_attr( $f['key'] ) . ']" value="' . esc_attr( $v ) . '"' . $dl . '>';
			if ( $dl ) {
				echo '<datalist id="ut-desig-list"><option value="সহকারী শিক্ষক"><option value="সিনিয়র শিক্ষক"><option value="সহকারী প্রধান শিক্ষক"><option value="প্রধান শিক্ষক"><option value="প্রভাষক"><option value="খণ্ডকালীন শিক্ষক"></datalist>';
			}
		}
		echo '</td></tr>';
	}
	if ( 'student' === $id ) {
		$code_now = $u ? (string) $val( '_ut_student_code' ) : '';
		echo '<tr><th scope="row">স্থায়ী স্টুডেন্ট ID</th><td>' . ( '' !== $code_now ? '<b>' . esc_html( $code_now ) . '</b>' : '<span class="description">প্রথম সংরক্ষণের সময় স্বয়ংক্রিয়ভাবে তৈরি হবে (যেমন STU-00125)। শ্রেণি বদলালেও এটি একই থাকবে।</span>' ) . '</td></tr>';
		$st_now = $val( '_ut_status' );
		if ( '' === $st_now ) { $st_now = 'active'; }
		$st_opts = function_exists( 'uturn_student_statuses' ) ? uturn_student_statuses() : array( 'active' => 'অধ্যয়নরত', 'left' => 'বিদ্যালয় ত্যাগ', 'passed' => 'উত্তীর্ণ' );
		echo '<tr><th scope="row"><label>শিক্ষার্থীর অবস্থা</label></th><td><select name="meta[_ut_status]" class="regular-text">';
		foreach ( $st_opts as $sk => $sl ) { echo '<option value="' . esc_attr( $sk ) . '"' . selected( $st_now, $sk, false ) . '>' . esc_html( $sl ) . '</option>'; }
		echo '</select><p class="description">বিদ্যালয় ত্যাগ / উত্তীর্ণ হলে সক্রিয় তালিকা (এন্ট্রি, হাজিরা, প্রবেশপত্র) থেকে বাদ যাবে; পুরনো ফলাফল থাকবে।</p></td></tr>';
		$ex_now = $val( '_ut_extra_subjects' );
		if ( ! is_array( $ex_now ) ) { $ex_now = array(); }
		echo '<tr><th scope="row"><label>অতিরিক্ত বিষয়</label></th><td>';
		if ( function_exists( 'uturn_subjects_all' ) ) {
			foreach ( uturn_subjects_all( true ) as $ss ) {
				if ( '' === ( $ss['name'] ?? '' ) ) { continue; }
				echo '<label style="display:inline-block;margin:0 12px 6px 0"><input type="checkbox" name="meta[_ut_extra_subjects][]" value="' . esc_attr( $ss['name'] ) . '"' . checked( in_array( $ss['name'], $ex_now, true ), true, false ) . '> ' . esc_html( $ss['name'] ) . '</label>';
			}
		}
		echo '<p class="description">শ্রেণির সাধারণ বিষয়ের বাইরে এই শিক্ষার্থীর জন্য প্রযোজ্য বিষয় (যেমন ৪র্থ বিষয়)। এন্ট্রি গ্রিডে ✳ কলাম হিসেবে আসবে।</p></td></tr>';
		$hist = $val( '_ut_class_history' );
		if ( is_array( $hist ) && $hist ) {
			$hs = array();
			foreach ( $hist as $hy => $hc ) { $hs[] = $hy . ' → ' . $hc; }
			echo '<tr><th scope="row">শ্রেণি ইতিহাস</th><td>' . esc_html( implode( ' · ', $hs ) ) . '</td></tr>';
		}
	}
	$img = $pid ? wp_get_attachment_image( $pid, 'thumbnail', false, array( 'style' => 'max-width:80px;display:block;margin-bottom:6px' ) ) : '';
	echo '<tr><th scope="row">ছবি</th><td>' . $img . '<input type="hidden" class="eduturn-media-id" name="meta[_ut_photo_id]" value="' . (int) $pid . '">'
		. '<button type="button" class="button eduturn-media-btn">ছবি বেছে নিন</button></td></tr>';
	echo '</table>';
	submit_button( $u ? 'হালনাগাদ করুন' : 'যোগ করুন' );
	echo '</form>';
}

/* ---------- Save / delete controllers ---------- */
add_action( 'admin_post_uturn_entity_save', 'uturn_entity_save' );
function uturn_entity_save() {
	eduturn_license_require( 'erp' );
	$ents   = uturn_entities();
	$entity = isset( $_POST['entity'] ) ? $_POST['entity'] : '';
	if ( ! isset( $ents[ $entity ] ) || ! current_user_can( 'uturn_manage_academic' ) || ! isset( $_POST['uturn_entity_nonce'] ) || ! wp_verify_nonce( $_POST['uturn_entity_nonce'], 'uturn_entity_save' ) ) {
		wp_die( 'Unauthorized.' );
	}
	$e   = $ents[ $entity ];
	$uid = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
	$back = function_exists( 'uturn_back' ) ? uturn_back( admin_url( 'admin.php?page=eduturn-' . $entity . 's' ) ) : admin_url( 'admin.php?page=eduturn-' . $entity . 's' );

	$name  = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	if ( '' === $name ) {
		wp_safe_redirect( $back . '&uturn_notice=error' );
		exit;
	}
	$meta = array( '_ut_phone' => '' );
	if ( isset( $_POST['meta'] ) && is_array( $_POST['meta'] ) ) {
		foreach ( wp_unslash( $_POST['meta'] ) as $k => $v ) {
			if ( 0 === strpos( $k, '_ut_' ) ) {
				if ( '_ut_subject_ids' === $k || '_ut_extra_subjects' === $k ) {
					continue; // arrays — handled below
				}
				$meta[ $k ] = in_array( $k, array( '_ut_photo_id', '_ut_subject_id', '_ut_group_id' ), true ) ? absint( $v ) : sanitize_text_field( $v );
			}
		}
	}
	if ( 'teacher' === $entity && function_exists( 'uturn_subject_name' ) ) {
		$valid = array();
		foreach ( uturn_subjects_all() as $ss ) {
			$valid[] = (int) $ss['id'];
		}
		$ids = array();
		if ( isset( $_POST['meta']['_ut_subject_ids'] ) && is_array( $_POST['meta']['_ut_subject_ids'] ) ) {
			foreach ( array_map( 'absint', wp_unslash( $_POST['meta']['_ut_subject_ids'] ) ) as $v ) {
				if ( $v && in_array( $v, $valid, true ) ) {
					$ids[] = $v;
				}
			}
			$ids = array_values( array_unique( $ids ) );
		} elseif ( ! empty( $meta['_ut_subject_id'] ) ) {
			$ids = array( absint( $meta['_ut_subject_id'] ) ); // old single posts
		}
		if ( 'student' === $entity && function_exists( 'uturn_subjects_all' ) ) {
			$valid_names = array();
			foreach ( uturn_subjects_all() as $ss ) {
				if ( '' !== ( $ss['name'] ?? '' ) ) { $valid_names[] = $ss['name']; }
			}
			$exs = array();
			if ( isset( $_POST['meta']['_ut_extra_subjects'] ) && is_array( $_POST['meta']['_ut_extra_subjects'] ) ) {
				foreach ( wp_unslash( $_POST['meta']['_ut_extra_subjects'] ) as $xn ) {
					$xn = str_replace( '|', '', sanitize_text_field( $xn ) );
					if ( '' !== $xn && in_array( $xn, $valid_names, true ) ) { $exs[] = $xn; }
				}
				$exs = array_values( array_unique( $exs ) );
			}
			$meta['_ut_extra_subjects'] = $exs;
		}
		$meta['_ut_subject_ids'] = $ids;
		$meta['_ut_subject_id'] = $ids ? $ids[0] : 0;
		$meta['_ut_group_id'] = absint( $meta['_ut_group_id'] ?? 0 );
		$meta['_ut_subject'] = $meta['_ut_subject_id'] ? uturn_subject_name( $meta['_ut_subject_id'] ) : '';
		$meta['_ut_group'] = $meta['_ut_group_id'] ? uturn_group_name( $meta['_ut_group_id'] ) : '';
	}
	if ( ! empty( $meta['_ut_phone'] ) && function_exists( 'uturn_norm_phone' ) ) {
		$meta['_ut_phone_norm'] = uturn_norm_phone( $meta['_ut_phone'] );
		if ( function_exists( 'uturn_phone_user_id' ) && uturn_phone_user_id( $meta['_ut_phone_norm'], $uid ) !== 0 ) {
			wp_safe_redirect( $back . '&uturn_notice=dup' );
			exit;
		}
	}
	if ( $uid && get_userdata( $uid ) ) {
		$up = array( 'ID' => $uid, 'display_name' => $name );
		if ( '' !== $email ) {
			$up['user_email'] = $email;
		}
		if ( ! empty( $_POST['pass'] ) ) {
			$up['user_pass'] = wp_unslash( $_POST['pass'] );
		}
		$uid = wp_update_user( $up );
	} else {
		$login = sanitize_user( wp_unslash( $_POST['user_login'] ) );
		if ( '' === $login || username_exists( $login ) ) {
			wp_safe_redirect( $back . '&uturn_notice=error' );
			exit;
		}
		if ( '' === $email ) {
			$email = $login . '@example.com';
		}
		$new_pass = isset( $_POST['pass'] ) && $_POST['pass'] !== '' ? wp_unslash( $_POST['pass'] ) : uturn_default_pass();
		$uid = wp_insert_user(
			array(
				'user_login' => $login, 'user_email' => $email, 'display_name' => $name,
				'user_pass'  => $new_pass, 'role' => $e['role'],
			)
		);
	}
	$is_new = ! isset( $_POST['user_id'] ) || ! absint( $_POST['user_id'] );
	if ( is_wp_error( $uid ) || ! $uid ) {
		wp_safe_redirect( $back . '&uturn_notice=error' );
		exit;
	}
	$user = get_userdata( $uid );
	$user->set_role( $e['role'] );
	foreach ( $meta as $k => $v ) {
		update_user_meta( $uid, $k, $v );
	}
	/* Two-way sync: teacher-form class-teacher duty → class term meta. */
	if ( 'teacher' === $entity && ! empty( $meta['_ut_classteacher_of'] ) ) {
		$ct_term = get_term_by( 'name', $meta['_ut_classteacher_of'], 'ut_class' );
		if ( $ct_term ) {
			update_term_meta( $ct_term->term_id, 'ut_cteacher', $uid );
		}
	}
	/* Sync linked directory CPT post (single source of truth for frontend). */
	$linked = get_posts( array( 'post_type' => $e['cpt'], 'meta_key' => '_ut_user_id', 'meta_value' => $uid, 'posts_per_page' => 1, 'post_status' => 'any' ) );
	$cpid   = $linked ? $linked[0]->ID : 0;
	$post_meta = array_merge( $meta, array( '_ut_user_id' => $uid ) );
	if ( ! empty( $meta['_ut_photo_id'] ) ) {
		/* Frontend reads _ut_photo — mirror the entity photo id there. */
		$post_meta['_ut_photo'] = absint( $meta['_ut_photo_id'] );
		if ( function_exists( 'uturn_ensure_image_size' ) ) {
			uturn_ensure_image_size( absint( $meta['_ut_photo_id'] ), 'ut-person' );
		}
	}
	$cpid   = wp_insert_post(
		array(
			'ID' => $cpid, 'post_type' => $e['cpt'], 'post_title' => $name,
			'post_status' => 'publish', 'meta_input' => $post_meta,
		),
		true
	);
	if ( ! is_wp_error( $cpid ) && 'student' === $entity && function_exists( 'uturn_ensure_student_code' ) ) {
		uturn_ensure_student_code( $cpid );
	}
	if ( ! is_wp_error( $cpid ) && 'student' === $entity && ! empty( $meta['_ut_class'] ) ) {
		$term = term_exists( $meta['_ut_class'], 'ut_class' );
		if ( ! $term ) {
			$term = wp_insert_term( $meta['_ut_class'], 'ut_class' );
		}
		if ( ! is_wp_error( $term ) ) {
			wp_set_object_terms( $cpid, (int) $term['term_id'], 'ut_class' );
		}
	}
	$go = $back . '&uturn_notice=saved';
	if ( $is_new ) {
		$go .= '&new_login=' . rawurlencode( $user->user_login ) . '&new_pass=' . rawurlencode( isset( $new_pass ) ? $new_pass : uturn_default_pass() );
	}
	wp_safe_redirect( $go );
	exit;
}

add_action( 'admin_post_uturn_teacher_toggle', 'uturn_teacher_toggle' );
function uturn_teacher_toggle() {
	eduturn_license_require( 'erp' );
	$uid = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
	$u = $uid ? get_userdata( $uid ) : null;
	if ( ! $u || ! in_array( 'uturn_teacher', (array) $u->roles, true ) || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'uturn_teacher_toggle_' . $uid ) ) {
		wp_die( 'Unauthorized.' );
	}
	$now_inactive = get_user_meta( $uid, '_ut_inactive', true ) !== '1';
	update_user_meta( $uid, '_ut_inactive', $now_inactive ? '1' : '0' );
	$linked = get_posts( array( 'post_type' => 'ut_teacher', 'meta_key' => '_ut_user_id', 'meta_value' => $uid, 'posts_per_page' => 1, 'post_status' => 'any' ) );
	if ( $linked ) {
		wp_update_post( array( 'ID' => $linked[0]->ID, 'post_status' => $now_inactive ? 'draft' : 'publish' ) );
	}
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-teachers&uturn_notice=saved' ) ) );
	exit;
}

add_action( 'admin_post_uturn_entity_resetpw', 'uturn_entity_resetpw' );
function uturn_entity_resetpw() {
	eduturn_license_require( 'erp' );
	$ents   = uturn_entities();
	$entity = isset( $_GET['entity'] ) ? $_GET['entity'] : '';
	$uid    = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
	$u      = $uid ? get_userdata( $uid ) : null;
	if ( ! isset( $ents[ $entity ] ) || ! $u || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'uturn_entity_resetpw_' . $uid ) ) {
		wp_die( 'Unauthorized.' );
	}
	/* Password saves before the notification email: a broken mail transport
	 * must never white-screen this (the new password shows on screen anyway). */
	try {
		wp_update_user( array( 'ID' => $uid, 'user_pass' => uturn_default_pass() ) );
	} catch ( Throwable $e ) {
		error_log( 'EduTurn entity password reset mail failed: ' . $e->getMessage() );
	}
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-' . $entity . 's&uturn_notice=saved&new_login=' . rawurlencode( $u->user_login ) . '&new_pass=' . rawurlencode( uturn_default_pass() ) ) ) );
	exit;
}

add_action( 'admin_post_uturn_entity_delete', 'uturn_entity_delete' );
function uturn_entity_delete() {
	eduturn_license_require( 'erp' );
	$ents   = uturn_entities();
	$entity = isset( $_GET['entity'] ) ? $_GET['entity'] : '';
	$uid    = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
	if ( ! isset( $ents[ $entity ] ) || ! current_user_can( 'uturn_manage_academic' ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'uturn_entity_delete_' . $uid ) ) {
		wp_die( 'Unauthorized.' );
	}
	$linked = get_posts( array( 'post_type' => $ents[ $entity ]['cpt'], 'meta_key' => '_ut_user_id', 'meta_value' => $uid, 'posts_per_page' => -1, 'post_status' => 'any' ) );
	foreach ( $linked as $p ) {
		wp_delete_post( $p->ID, true );
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	wp_delete_user( $uid );
	wp_safe_redirect( uturn_back( admin_url( 'admin.php?page=eduturn-' . $entity . 's&uturn_notice=deleted' ) ) );
	exit;
}

/* Directory CPTs are managed ONLY via SMS entity screens — no duplicate add/edit paths. */
add_action( 'load-post-new.php', 'uturn_directory_cpt_guard' );
add_action( 'load-post.php', 'uturn_directory_cpt_guard' );
function uturn_directory_cpt_guard() {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	$map = array( 'ut_teacher' => 'eduturn-teachers', 'ut_staff' => 'eduturn-staffs', 'ut_student' => 'eduturn-students' );
	$pt = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
	$pid = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	if ( $pid && ! $pt ) {
		$pt = get_post_type( $pid );
	}
	if ( ! isset( $map[ $pt ] ) ) {
		return;
	}
	$go = admin_url( 'admin.php?page=' . $map[ $pt ] . '&view=form' );
	if ( $pid ) {
		$uid = (int) get_post_meta( $pid, '_ut_user_id', true );
		if ( $uid ) {
			$go .= '&user_id=' . $uid;
		}
	}
	wp_safe_redirect( $go );
	exit;
}
