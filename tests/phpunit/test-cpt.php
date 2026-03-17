<?php
/**
 * Tests for BDC_CPT.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

/**
 * Test the members custom post type registration.
 *
 * @since 1.0.0
 */
class Test_BDC_CPT extends WP_UnitTestCase {

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$cpt = new BDC_CPT();
		$cpt->register();
	}

	/**
	 * Test that the members post type is registered.
	 *
	 * @return void
	 */
	public function test_members_post_type_is_registered(): void {
		$this->assertTrue( post_type_exists( 'members' ), 'members post type should be registered.' );
	}

	/**
	 * Test that the members post type is hierarchical.
	 *
	 * @return void
	 */
	public function test_members_post_type_is_hierarchical(): void {
		$post_type = get_post_type_object( 'members' );
		$this->assertTrue( $post_type->hierarchical, 'members post type should be hierarchical.' );
	}

	/**
	 * Test that the members post type supports expected features.
	 *
	 * @return void
	 */
	public function test_members_post_type_supports(): void {
		$this->assertTrue( post_type_supports( 'members', 'title' ) );
		$this->assertTrue( post_type_supports( 'members', 'editor' ) );
		$this->assertTrue( post_type_supports( 'members', 'thumbnail' ) );
		$this->assertTrue( post_type_supports( 'members', 'page-attributes' ) );
		$this->assertTrue( post_type_supports( 'members', 'revisions' ) );
		$this->assertTrue( post_type_supports( 'members', 'custom-fields' ) );
	}

	/**
	 * Test that the members post type has an archive.
	 *
	 * @return void
	 */
	public function test_members_post_type_has_archive(): void {
		$post_type = get_post_type_object( 'members' );
		$this->assertTrue( $post_type->has_archive, 'members post type should have an archive.' );
	}

	/**
	 * Test that the members post type is available in REST.
	 *
	 * @return void
	 */
	public function test_members_post_type_show_in_rest(): void {
		$post_type = get_post_type_object( 'members' );
		$this->assertTrue( $post_type->show_in_rest, 'members post type should show in REST.' );
	}

	/**
	 * Test that the members post type uses custom capability type.
	 *
	 * @return void
	 */
	public function test_members_post_type_capability_type(): void {
		$post_type = get_post_type_object( 'members' );
		$this->assertSame( 'member', $post_type->capability_type[0] ?? $post_type->capability_type );
	}

	/**
	 * Test that rewrite rules contain the members slug.
	 *
	 * @return void
	 */
	public function test_rewrite_rules_contain_members_slug(): void {
		flush_rewrite_rules();
		$rules = get_option( 'rewrite_rules' );

		if ( ! is_array( $rules ) ) {
			$this->markTestSkipped( 'Rewrite rules not available in this test environment.' );
		}

		$found = false;
		foreach ( array_keys( $rules ) as $rule ) {
			if ( str_contains( $rule, 'members' ) ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, 'Rewrite rules should contain the members slug.' );
	}
}
