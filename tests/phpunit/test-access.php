<?php
/**
 * Tests for BDC_Access.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

/**
 * Test access control logic.
 *
 * @since 1.0.0
 */
class Test_BDC_Access extends WP_UnitTestCase {

	/**
	 * Members-area page ID.
	 *
	 * @var int
	 */
	private int $members_area_page_id;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		BDC_Roles::activate();
		BDC_Access::reset_cache();

		// Create the members-area page.
		$this->members_area_page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => 'members-area',
				'post_title'  => 'Members Area',
			)
		);

		update_option( 'bdc_members_area_page_id', $this->members_area_page_id );
	}

	/**
	 * Tear down test fixtures.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		BDC_Access::reset_cache();
		delete_option( 'bdc_members_area_page_id' );
		parent::tear_down();
	}

	/**
	 * Test that a bdc_members user can access the members-area page.
	 *
	 * @return void
	 */
	public function test_bdc_members_user_can_access_members_area(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'bdc_members' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( current_user_can( BDC_Roles::ACCESS_CAP ) );
	}

	/**
	 * Test that a subscriber cannot access members content.
	 *
	 * @return void
	 */
	public function test_subscriber_cannot_access_members_content(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$this->assertFalse( current_user_can( BDC_Roles::ACCESS_CAP ) );
	}

	/**
	 * Test that an administrator can access members content.
	 *
	 * @return void
	 */
	public function test_administrator_can_access_members_content(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( current_user_can( BDC_Roles::ACCESS_CAP ) );
	}

	/**
	 * Test that an editor can access members content.
	 *
	 * @return void
	 */
	public function test_editor_can_access_members_content(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( current_user_can( BDC_Roles::ACCESS_CAP ) );
	}

	/**
	 * Test that an author can access members content.
	 *
	 * @return void
	 */
	public function test_author_can_access_members_content(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( current_user_can( BDC_Roles::ACCESS_CAP ) );
	}

	/**
	 * Test that login_redirect returns members-area URL for bdc_members user.
	 *
	 * @return void
	 */
	public function test_login_redirect_for_bdc_members_user(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'bdc_members' ) );
		$user    = get_user_by( 'id', $user_id );
		$access  = new BDC_Access();

		$result = $access->handle_login_redirect( admin_url(), '', $user );

		$this->assertStringContainsString( 'members-area', $result );
	}

	/**
	 * Test that login_redirect returns default for administrator.
	 *
	 * @return void
	 */
	public function test_login_redirect_returns_default_for_administrator(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$user    = get_user_by( 'id', $user_id );
		$access  = new BDC_Access();

		$default = admin_url();
		$result  = $access->handle_login_redirect( $default, '', $user );

		$this->assertSame( $default, $result );
	}

	/**
	 * Test that the admin bar is hidden for bdc_members users.
	 *
	 * @return void
	 */
	public function test_admin_bar_hidden_for_bdc_members(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'bdc_members' ) );
		wp_set_current_user( $user_id );

		$access = new BDC_Access();
		$this->assertFalse( $access->handle_admin_bar( true ) );
	}

	/**
	 * Test that the admin bar is shown for administrators.
	 *
	 * @return void
	 */
	public function test_admin_bar_shown_for_administrator(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$access = new BDC_Access();
		$this->assertTrue( $access->handle_admin_bar( true ) );
	}

	/**
	 * Test that get_members_area_url returns the correct URL.
	 *
	 * @return void
	 */
	public function test_get_members_area_url_returns_correct_url(): void {
		BDC_Access::reset_cache();
		$url = BDC_Access::get_members_area_url();

		$this->assertNotEmpty( $url );
		$this->assertNotSame( home_url( '/' ), $url, 'Should return the page URL, not the home URL fallback.' );
	}

	/**
	 * Test that get_members_area_url falls back to slug lookup.
	 *
	 * @return void
	 */
	public function test_get_members_area_url_falls_back_to_slug(): void {
		delete_option( 'bdc_members_area_page_id' );
		BDC_Access::reset_cache();

		$url = BDC_Access::get_members_area_url();

		// The page was created with the 'members-area' slug, so it should still be found.
		$this->assertNotSame( home_url( '/' ), $url );
	}

	/**
	 * Test that the members CPT is excluded from sitemaps.
	 *
	 * @return void
	 */
	public function test_members_excluded_from_sitemaps(): void {
		$access     = new BDC_Access();
		$post_types = array(
			'post'    => get_post_type_object( 'post' ),
			'page'    => get_post_type_object( 'page' ),
		);

		// Simulate members being present.
		$cpt = new BDC_CPT();
		$cpt->register();
		$members_type = get_post_type_object( 'members' );
		if ( $members_type ) {
			$post_types['members'] = $members_type;
		}

		$filtered = $access->exclude_from_sitemaps( $post_types );

		$this->assertArrayNotHasKey( 'members', $filtered );
		$this->assertArrayHasKey( 'post', $filtered );
		$this->assertArrayHasKey( 'page', $filtered );
	}

	/**
	 * Test that the filter_pre_get_posts excludes members from search for unauthorised users.
	 *
	 * @return void
	 */
	public function test_members_excluded_from_search_for_unauthorised_user(): void {
		wp_set_current_user( 0 );

		$access = new BDC_Access();
		$query  = new WP_Query();
		$query->init();
		$query->is_search = true;
		$query->is_main_query = true;

		// Simulate being the main query.
		$GLOBALS['wp_the_query'] = $query;

		$access->filter_pre_get_posts( $query );

		$excluded = $query->get( 'post_type__not_in' );
		$this->assertContains( 'members', $excluded );

		// Clean up.
		unset( $GLOBALS['wp_the_query'] );
	}

	/**
	 * Test that filter_pre_get_posts does not exclude members for authorised users.
	 *
	 * @return void
	 */
	public function test_members_not_excluded_from_search_for_authorised_user(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'bdc_members' ) );
		wp_set_current_user( $user_id );

		$access = new BDC_Access();
		$query  = new WP_Query();
		$query->init();
		$query->is_search = true;
		$query->is_main_query = true;

		$GLOBALS['wp_the_query'] = $query;

		$access->filter_pre_get_posts( $query );

		$excluded = $query->get( 'post_type__not_in' );
		if ( is_array( $excluded ) ) {
			$this->assertNotContains( 'members', $excluded );
		} else {
			$this->assertEmpty( $excluded );
		}

		unset( $GLOBALS['wp_the_query'] );
	}
}
