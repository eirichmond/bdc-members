<?php
/**
 * PHPUnit bootstrap for BDC Members tests.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

// Load the WordPress test suite.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin for testing.
 */
tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__, 2 ) . '/bdc-members.php';
	}
);

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Run activation to set up roles and capabilities.
BDC_Roles::activate();
