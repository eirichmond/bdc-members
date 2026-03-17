<?php
/**
 * Access control, redirects, and login handling for BDC Members.
 *
 * Designed for block themes — gated content is injected via the_content
 * filter so the block template system renders the page structure
 * (header, footer, layout) normally.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Gates access to the members-area page and the members CPT,
 * handles login redirects, query filtering, REST API restrictions,
 * admin bar visibility, and sitemap exclusion.
 *
 * @since 1.0.0
 */
final class BDC_Access {

	/**
	 * Cached members-area page URL.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private static ?string $members_area_url = null;

	/**
	 * The content replacement to inject via the_content filter.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $replacement_content = null;

	/**
	 * The post ID whose content should be replaced.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	private int $gated_post_id = 0;

	/**
	 * Handles the `template_redirect` action.
	 *
	 * For logged-out users on the members-area page, injects a login form.
	 * For logged-out users on members CPT pages, redirects to members-area.
	 * For logged-in users without access, replaces content with a
	 * no-permission message. All content injection goes through the_content
	 * filter so the block theme template renders normally.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_template_redirect(): void {
		$members_area_page_id = self::get_members_area_page_id();

		// Check if we are on the members-area page.
		if ( $members_area_page_id && is_page( $members_area_page_id ) ) {
			nocache_headers();

			if ( ! is_user_logged_in() ) {
				// Show the login form instead of the page content.
				$this->gated_post_id      = $members_area_page_id;
				$this->replacement_content = $this->get_login_form_html();
				add_filter( 'the_content', array( $this, 'replace_gated_content' ), 0 );
				return;
			}

			if ( current_user_can( BDC_Roles::ACCESS_CAP ) ) {
				return;
			}

			// Logged in but no access.
			$this->gated_post_id      = $members_area_page_id;
			$this->replacement_content = self::get_no_permission_html();
			status_header( 403 );
			add_filter( 'the_content', array( $this, 'replace_gated_content' ), 0 );
			return;
		}

		// Check if we are on a members CPT page (single or archive).
		if ( is_singular( 'members' ) || is_post_type_archive( 'members' ) ) {
			nocache_headers();

			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( self::get_members_area_url() );
				exit;
			}

			if ( current_user_can( BDC_Roles::ACCESS_CAP ) ) {
				return;
			}

			// Logged in but no access.
			$this->gated_post_id      = get_queried_object_id();
			$this->replacement_content = self::get_no_permission_html();
			status_header( 403 );
			add_filter( 'the_content', array( $this, 'replace_gated_content' ), 0 );
		}
	}

	/**
	 * Replaces the content for the gated post only.
	 *
	 * Checks get_the_ID() so that other posts rendered on the same page
	 * (e.g. in query loop blocks) are not affected.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content The original post content.
	 * @return string
	 */
	public function replace_gated_content( string $content ): string {
		if ( null === $this->replacement_content ) {
			return $content;
		}

		// Only replace content for the specific gated post.
		if ( $this->gated_post_id && get_the_ID() !== $this->gated_post_id ) {
			return $content;
		}

		return $this->replacement_content;
	}

	/**
	 * Returns the login form HTML.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_login_form_html(): string {
		$members_area_url = self::get_members_area_url();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$login_status = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';

		$error_html = '';
		if ( 'failed' === $login_status ) {
			$error_html = sprintf(
				'<div class="bdc-members-login__error" role="alert"><p>%s</p></div>',
				esc_html__( 'Login failed. Please check your username and password and try again.', 'bdc-members' )
			);
		}

		ob_start();
		wp_login_form(
			array(
				'redirect' => esc_url( $members_area_url ),
			)
		);
		$form_html = (string) ob_get_clean();

		return sprintf(
			'<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:paragraph -->%s<h2>%s</h2>%s<!-- /wp:paragraph --></div>
		<!-- /wp:group -->',
			$error_html,
			esc_html__( 'Member Login', 'bdc-members' ),
			$form_html
		);
		
	}

	/**
	 * Returns the no-permission HTML.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private static function get_no_permission_html(): string {
		return sprintf(
			'<div class="bdc-members-no-permission"><h2>%s</h2><p>%s</p><p><a href="%s">%s</a></p></div>',
			esc_html__( 'Access Denied', 'bdc-members' ),
			esc_html__( 'You do not have permission to view this content.', 'bdc-members' ),
			esc_url( home_url( '/' ) ),
			esc_html__( 'Return to the home page', 'bdc-members' )
		);
	}

	/**
	 * Filters the login redirect destination for bdc_members users.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $redirect_to           The redirect destination URL.
	 * @param string  $requested_redirect_to The requested redirect destination URL.
	 * @param WP_User $user                  The logged-in user object.
	 * @return string
	 */
	public function handle_login_redirect( string $redirect_to, string $requested_redirect_to, $user ): string {
		if ( ! ( $user instanceof \WP_User ) || ! in_array( 'bdc_members', (array) $user->roles, true ) ) {
			return $redirect_to;
		}

		return self::get_members_area_url();
	}

	/**
	 * Filters `pre_get_posts` to exclude the members CPT from public
	 * queries for unauthorised users.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Query $query The current query.
	 * @return void
	 */
	public function filter_pre_get_posts( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( current_user_can( BDC_Roles::ACCESS_CAP ) ) {
			return;
		}

		if ( $query->is_search() || $query->is_feed() || $query->is_home() ) {
			$excluded = $query->get( 'post_type__not_in' );

			if ( ! is_array( $excluded ) ) {
				$excluded = array();
			}

			$excluded[] = 'members';
			$query->set( 'post_type__not_in', $excluded );
		}
	}

	/**
	 * Restricts REST API access to the members CPT for unauthorised users.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function restrict_rest_api(): void {
		add_filter(
			'rest_members_query',
			static function ( array $args, \WP_REST_Request $request ): array {
				if ( ! current_user_can( BDC_Roles::ACCESS_CAP ) ) {
					$args['post__in'] = array( 0 );
				}
				return $args;
			},
			10,
			2
		);

		add_filter(
			'rest_pre_dispatch',
			static function ( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
				$route = $request->get_route();

				if ( 1 === preg_match( '#^/wp/v2/members#', $route ) && ! current_user_can( BDC_Roles::ACCESS_CAP ) ) {
					return new \WP_Error(
						'rest_forbidden',
						__( 'You do not have permission to access this content.', 'bdc-members' ),
						array( 'status' => 403 )
					);
				}

				return $result;
			},
			10,
			3
		);
	}

	/**
	 * Hides the admin bar for bdc_members users.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $show Whether to show the admin bar.
	 * @return bool
	 */
	public function handle_admin_bar( bool $show ): bool {
		$user = wp_get_current_user();

		if ( $user->exists() && in_array( 'bdc_members', (array) $user->roles, true ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Redirects bdc_members users away from the WordPress admin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function redirect_bdc_members_from_admin(): void {
		if ( wp_doing_ajax() ) {
			return;
		}

		$user = wp_get_current_user();

		if ( $user->exists() && in_array( 'bdc_members', (array) $user->roles, true ) ) {
			wp_safe_redirect( self::get_members_area_url() );
			exit;
		}
	}

	/**
	 * Handles failed login attempts from the members-area page.
	 *
	 * Redirects back to the members-area page with an error parameter
	 * instead of showing the default wp-login.php error page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $username The username that was attempted.
	 * @return void
	 */
	public function handle_login_failed( string $username ): void {
		$referrer = wp_get_referer();

		if ( ! $referrer ) {
			return;
		}

		$members_area_url = self::get_members_area_url();

		// Check if the referrer is the members-area page.
		if ( false !== strpos( $referrer, $members_area_url ) || false !== strpos( $referrer, '?' ) && str_starts_with( strtok( $referrer, '?' ), $members_area_url ) ) {
			wp_safe_redirect( add_query_arg( 'login', 'failed', $members_area_url ) );
			exit;
		}
	}

	/**
	 * Excludes the members CPT from WordPress core sitemaps.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, \WP_Post_Type> $post_types Registered post types.
	 * @return array<string, \WP_Post_Type>
	 */
	public function exclude_from_sitemaps( array $post_types ): array {
		unset( $post_types['members'] );
		return $post_types;
	}

	/**
	 * Returns the permalink of the members-area page.
	 *
	 * Uses the page ID stored in options, falling back to slug lookup.
	 * Caches the result in a static variable.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_members_area_url(): string {
		if ( null !== self::$members_area_url ) {
			return self::$members_area_url;
		}

		$page_id = self::get_members_area_page_id();

		if ( $page_id ) {
			$url = get_permalink( $page_id );

			if ( $url ) {
				self::$members_area_url = $url;
				return self::$members_area_url;
			}
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'BDC Members: members-area page not found. Falling back to home URL.' );
		self::$members_area_url = home_url( '/' );
		return self::$members_area_url;
	}

	/**
	 * Returns the members-area page ID from options or slug lookup.
	 *
	 * @since 1.0.0
	 *
	 * @return int Page ID, or 0 if not found.
	 */
	public static function get_members_area_page_id(): int {
		$page_id = (int) get_option( 'bdc_members_area_page_id', 0 );

		if ( $page_id > 0 ) {
			return $page_id;
		}

		/**
		 * Filters the members-area page slug used as a fallback.
		 *
		 * @since 1.0.0
		 *
		 * @param string $slug The page slug.
		 */
		$slug = (string) apply_filters( 'bdc_members_area_slug', 'members-area' );
		$page = get_page_by_path( $slug );

		if ( $page instanceof \WP_Post ) {
			return $page->ID;
		}

		return 0;
	}

	/**
	 * Resets the cached members-area URL. Useful in tests.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function reset_cache(): void {
		self::$members_area_url = null;
	}
}
