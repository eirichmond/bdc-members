<?php
/**
 * Plugin Name:       BDC Members
 * Plugin URI:        https://britishdalmatianclub.org
 * Description:       Creates a members-only area with gated access, a custom members role, and a hierarchical members custom post type.
 * Version:           1.0.1
 * Requires at least: 6.9
 * Requires PHP:      8.2
 * Author:            Elliott Richmond
 * Author Email:      elliott@squareonemd.co.uk
 * Author URI:        https://elliottrichmond.co.uk
 * Text Domain:       bdc-members
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package BDC_Members
 * @since   1.0.1
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Does nothing demo git braching test.
 *
 * @return void
 */
function this_does_nothing() {
	echo 'this does nothing';
}


/**
 * PHP version gate.
 */
if ( PHP_VERSION_ID < 80200 ) {
	add_action(
		'admin_notices',
		static function (): void {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'BDC Members requires PHP 8.2 or higher. Please upgrade your PHP version.', 'bdc-members' );
			echo '</p></div>';
		}
	);
	return;
}

/**
 * WordPress version gate.
 */
global $wp_version;
if ( version_compare( $wp_version, '6.9', '<' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'BDC Members requires WordPress 6.9 or higher. Please update WordPress.', 'bdc-members' );
			echo '</p></div>';
		}
	);
	return;
}

/**
 * Plugin constants.
 */
define( 'BDC_MEMBERS_VERSION', '1.0.0' );
define( 'BDC_MEMBERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDC_MEMBERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BDC_MEMBERS_PLUGIN_FILE', __FILE__ );

/**
 * Load class files.
 */
require_once BDC_MEMBERS_PLUGIN_DIR . 'includes/class-bdc-roles.php';
require_once BDC_MEMBERS_PLUGIN_DIR . 'includes/class-bdc-cpt.php';
require_once BDC_MEMBERS_PLUGIN_DIR . 'includes/class-bdc-access.php';
require_once BDC_MEMBERS_PLUGIN_DIR . 'includes/class-bdc-admin.php';
require_once BDC_MEMBERS_PLUGIN_DIR . 'includes/class-bdc-members.php';

/**
 * Boot the plugin.
 */
new BDC_Members();

/**
 * Activation hook: create roles, capabilities, and schedule rewrite flush.
 */
register_activation_hook(
	__FILE__,
	static function (): void {
		BDC_Roles::activate();
		BDC_CPT::flush_rewrite_rules_on_activation();
	}
);
