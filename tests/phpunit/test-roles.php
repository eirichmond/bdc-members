<?php
/**
 * Tests for BDC_Roles.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

declare( strict_types=1 );

/**
 * Test the role and capability setup.
 *
 * @since 1.0.0
 */
class Test_BDC_Roles extends WP_UnitTestCase {

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		BDC_Roles::activate();
	}

	/**
	 * Test that the bdc_members role exists after activation.
	 *
	 * @return void
	 */
	public function test_bdc_members_role_exists(): void {
		$role = get_role( 'bdc_members' );
		$this->assertNotNull( $role, 'bdc_members role should exist.' );
	}

	/**
	 * Test that the bdc_members role has only read and read_member capabilities.
	 *
	 * @return void
	 */
	public function test_bdc_members_role_has_correct_capabilities(): void {
		$role = get_role( 'bdc_members' );
		$this->assertTrue( $role->has_cap( 'read' ), 'bdc_members should have read capability.' );
		$this->assertTrue( $role->has_cap( 'read_member' ), 'bdc_members should have read_member capability.' );
		$this->assertFalse( $role->has_cap( 'edit_members' ), 'bdc_members should not have edit_members capability.' );
		$this->assertFalse( $role->has_cap( 'publish_members' ), 'bdc_members should not have publish_members capability.' );
		$this->assertFalse( $role->has_cap( 'delete_members' ), 'bdc_members should not have delete_members capability.' );
	}

	/**
	 * Test that the administrator role has all custom capabilities.
	 *
	 * @return void
	 */
	public function test_administrator_has_all_custom_capabilities(): void {
		$role = get_role( 'administrator' );
		$caps = array(
			'read_member',
			'read_private_members',
			'edit_members',
			'edit_others_members',
			'edit_published_members',
			'edit_private_members',
			'publish_members',
			'delete_members',
			'delete_others_members',
			'delete_published_members',
			'delete_private_members',
			'create_members',
		);

		foreach ( $caps as $cap ) {
			$this->assertTrue( $role->has_cap( $cap ), "administrator should have {$cap} capability." );
		}
	}

	/**
	 * Test that the editor role has the correct capabilities.
	 *
	 * @return void
	 */
	public function test_editor_has_correct_capabilities(): void {
		$role = get_role( 'editor' );
		$this->assertTrue( $role->has_cap( 'read_member' ) );
		$this->assertTrue( $role->has_cap( 'read_private_members' ) );
		$this->assertTrue( $role->has_cap( 'edit_members' ) );
		$this->assertTrue( $role->has_cap( 'edit_others_members' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_members' ) );
		$this->assertTrue( $role->has_cap( 'edit_private_members' ) );
		$this->assertTrue( $role->has_cap( 'publish_members' ) );
		$this->assertTrue( $role->has_cap( 'delete_members' ) );
		$this->assertTrue( $role->has_cap( 'delete_others_members' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_members' ) );
		$this->assertTrue( $role->has_cap( 'delete_private_members' ) );
		$this->assertTrue( $role->has_cap( 'create_members' ) );
	}

	/**
	 * Test that the author role has correct capabilities (no edit_others or delete_others).
	 *
	 * @return void
	 */
	public function test_author_has_correct_capabilities(): void {
		$role = get_role( 'author' );
		$this->assertTrue( $role->has_cap( 'read_member' ) );
		$this->assertTrue( $role->has_cap( 'read_private_members' ) );
		$this->assertTrue( $role->has_cap( 'edit_members' ) );
		$this->assertFalse( $role->has_cap( 'edit_others_members' ) );
		$this->assertTrue( $role->has_cap( 'edit_published_members' ) );
		$this->assertTrue( $role->has_cap( 'publish_members' ) );
		$this->assertTrue( $role->has_cap( 'delete_members' ) );
		$this->assertFalse( $role->has_cap( 'delete_others_members' ) );
		$this->assertTrue( $role->has_cap( 'delete_published_members' ) );
		$this->assertTrue( $role->has_cap( 'create_members' ) );
	}

	/**
	 * Test that calling activate() twice does not cause errors.
	 *
	 * @return void
	 */
	public function test_activate_twice_does_not_error(): void {
		BDC_Roles::activate();
		BDC_Roles::activate();

		$role = get_role( 'bdc_members' );
		$this->assertNotNull( $role, 'bdc_members role should still exist after double activation.' );
	}

	/**
	 * Test that user_has_members_access returns true for allowed roles.
	 *
	 * @return void
	 */
	public function test_user_has_members_access_for_allowed_roles(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'bdc_members' ) );
		$this->assertTrue( BDC_Roles::user_has_members_access( $user_id ) );

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->assertTrue( BDC_Roles::user_has_members_access( $admin_id ) );
	}

	/**
	 * Test that user_has_members_access returns false for subscriber.
	 *
	 * @return void
	 */
	public function test_user_has_members_access_returns_false_for_subscriber(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->assertFalse( BDC_Roles::user_has_members_access( $user_id ) );
	}
}
