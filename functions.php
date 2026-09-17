<?php
/**
 * EduTurn — Theme Bootstrap
 *
 * Developer : UTurn Digital Solutions (https://uturndigital.com.bd)
 * Social    : https://www.facebook.com/uturndigitalsolutions
 * Doctrine  : 100% bespoke, zero-dependency. No plugins. Ever.
 *
 * MVC-inspired load order: helpers -> setup -> walker -> assets ->
 * models (cpt/meta) -> access (roles) -> dashboard (options) ->
 * controllers (entity-admin/forms/applications/results/routines/portal)
 * -> installer (seeder).
 */

defined( 'ABSPATH' ) || exit;

define( 'UTURN_VERSION', '1.21.2' );
define( 'UTURN_DIR', get_template_directory() );
define( 'UTURN_URI', get_template_directory_uri() );
define( 'UTURN_OPT', 'uturn_edu_options' );   // single dashboard options row
define( 'UTURN_SEED_VERSION', '1.1.0' );      // demo-seeder idempotency key
define( 'UTURN_ROLES_V', '1.8.0' );           // RBAC capability-sync key

require_once UTURN_DIR . '/inc/helpers.php';
require_once UTURN_DIR . '/inc/photos.php';
require_once UTURN_DIR . '/inc/seo.php';
require_once UTURN_DIR . '/inc/setup.php';
require_once UTURN_DIR . '/inc/walker.php';
require_once UTURN_DIR . '/inc/assets.php';
require_once UTURN_DIR . '/inc/cpt.php';
require_once UTURN_DIR . '/inc/meta.php';
require_once UTURN_DIR . '/inc/roles.php';
require_once UTURN_DIR . '/inc/mobile-login.php';
require_once UTURN_DIR . '/inc/login-design.php';
require_once UTURN_DIR . '/inc/notify.php';
require_once UTURN_DIR . '/inc/options.php';
require_once UTURN_DIR . '/inc/dashboard.php';
require_once UTURN_DIR . '/inc/entity-admin.php';
require_once UTURN_DIR . '/inc/forms.php';
require_once UTURN_DIR . '/inc/applications.php';
require_once UTURN_DIR . '/inc/results.php';
require_once UTURN_DIR . '/inc/result-entry.php';
require_once UTURN_DIR . '/inc/routines.php';
require_once UTURN_DIR . '/inc/portal.php';
require_once UTURN_DIR . '/inc/dash.php';
require_once UTURN_DIR . '/inc/dash-crud.php';
require_once UTURN_DIR . '/inc/dash-school.php';
require_once UTURN_DIR . '/inc/dash-users.php';
require_once UTURN_DIR . '/inc/dash-academic.php';
require_once UTURN_DIR . '/inc/academic-data.php';
require_once UTURN_DIR . '/inc/board.php';
require_once UTURN_DIR . '/inc/editor.php';
require_once UTURN_DIR . '/inc/seeder.php';
require_once UTURN_DIR . '/inc/license-client.php';
