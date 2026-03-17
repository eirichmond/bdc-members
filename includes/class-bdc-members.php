<?php
/**
 * Main plugin orchestrator for BDC Members.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Centralises all hook registrations and delegates to the
 * individual classes that implement each piece of functionality.
 *
 * @since 1.0.0
 */
final class BDC_Members {

	/**
	 * CPT handler instance.
	 *
	 * @since 1.0.0
	 * @var BDC_CPT
	 */
	private BDC_CPT $cpt;

	/**
	 * Access control handler instance.
	 *
	 * @since 1.0.0
	 * @var BDC_Access
	 */
	private BDC_Access $access;

	/**
	 * Admin settings handler instance.
	 *
	 * @since 1.0.0
	 * @var BDC_Admin
	 */
	private BDC_Admin $admin;

	/**
	 * Constructor. Instantiates dependencies and registers all hooks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->cpt    = new BDC_CPT();
		$this->access = new BDC_Access();
		$this->admin  = new BDC_Admin();

		$this->register_hooks();
	}

	/**
	 * Registers all WordPress hooks in a single location.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		// Ensure roles and capabilities are in place.
		add_action( 'init', array( BDC_Roles::class, 'maybe_activate' ), 5 );

		// CPT registration and rewrite flush.
		add_action( 'init', array( $this->cpt, 'register' ) );
		add_action( 'init', array( $this->cpt, 'maybe_flush_rewrite_rules' ), 99 );

		// Access control.
		add_action( 'template_redirect', array( $this->access, 'handle_template_redirect' ) );
		add_filter( 'login_redirect', array( $this->access, 'handle_login_redirect' ), 10, 3 );
		add_action( 'pre_get_posts', array( $this->access, 'filter_pre_get_posts' ) );
		add_action( 'rest_api_init', array( $this->access, 'restrict_rest_api' ) );
		add_filter( 'show_admin_bar', array( $this->access, 'handle_admin_bar' ) );
		add_action( 'admin_init', array( $this->access, 'redirect_bdc_members_from_admin' ) );
		add_action( 'wp_login_failed', array( $this->access, 'handle_login_failed' ) );
		add_filter( 'wp_sitemaps_post_types', array( $this->access, 'exclude_from_sitemaps' ) );

		// Admin settings.
		add_action( 'admin_menu', array( $this->admin, 'register_settings' ) );
		add_action( 'admin_init', array( $this->admin, 'register_settings_fields' ) );
		add_action( 'admin_notices', array( $this->admin, 'maybe_show_admin_notice' ) );
	}
}
