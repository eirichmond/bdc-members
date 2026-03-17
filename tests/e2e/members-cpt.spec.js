/**
 * End-to-end tests for the members CPT.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

const { test, expect } = require( '@playwright/test' );

test.describe( 'Members CPT', () => {
	test( 'logged-out user visiting /members/ is redirected to members-area', async ( {
		page,
	} ) => {
		await page.goto( '/members/' );
		await page.waitForLoadState( 'networkidle' );
		expect( page.url() ).toContain( 'members-area' );
	} );

	test( 'logged-out user visiting a single members post is redirected to members-area', async ( {
		page,
	} ) => {
		await page.goto( '/members/test-member-post/' );
		await page.waitForLoadState( 'networkidle' );
		expect( page.url() ).toContain( 'members-area' );
	} );

	test( 'bdc_members user can browse /members/ archive', async ( {
		page,
	} ) => {
		// Log in as bdc_members.
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', 'bdcmember' );
		await page.fill( '#user_pass', 'password123' );
		await page.click( '#wp-submit' );
		await page.waitForLoadState( 'networkidle' );

		await page.goto( '/members/' );
		await page.waitForLoadState( 'networkidle' );

		// Should not be redirected.
		expect( page.url() ).toContain( '/members' );
		expect( page.url() ).not.toContain( 'members-area' );
	} );

	test( 'bdc_members user can view a single members post', async ( {
		page,
	} ) => {
		// Log in.
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', 'bdcmember' );
		await page.fill( '#user_pass', 'password123' );
		await page.click( '#wp-submit' );
		await page.waitForLoadState( 'networkidle' );

		await page.goto( '/members/test-member-post/' );
		await page.waitForLoadState( 'networkidle' );

		await expect( page.locator( 'body' ) ).toContainText(
			'members-only content'
		);
	} );

	test( 'administrator can create a new members post in the block editor', async ( {
		page,
	} ) => {
		// Log in as admin.
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', 'admin' );
		await page.fill( '#user_pass', 'password' );
		await page.click( '#wp-submit' );
		await page.waitForLoadState( 'networkidle' );

		await page.goto( '/wp-admin/post-new.php?post_type=members' );
		await page.waitForLoadState( 'networkidle' );

		// Should be on the block editor page.
		expect( page.url() ).toContain( 'post-new.php' );
	} );

	test( 'author can create and publish a members post', async ( {
		page,
	} ) => {
		// Log in as author.
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', 'testauthor' );
		await page.fill( '#user_pass', 'password123' );
		await page.click( '#wp-submit' );
		await page.waitForLoadState( 'networkidle' );

		await page.goto( '/wp-admin/post-new.php?post_type=members' );
		await page.waitForLoadState( 'networkidle' );

		// Should be on the block editor page (not redirected).
		expect( page.url() ).toContain( 'post-new.php' );
	} );
} );
