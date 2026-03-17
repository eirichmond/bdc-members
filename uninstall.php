<?php
/**
 * Uninstall handler for BDC Members.
 *
 * Intentionally left empty. The bdc_members role and custom capabilities are
 * not removed on uninstall because they become inert without the plugin's
 * logic, and removing them could lock users out of content they previously
 * had access to.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
