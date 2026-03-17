/**
 * Global setup for Playwright tests.
 *
 * Creates test users and content in the wp-env instance.
 *
 * @package BDC_Members
 * @since   1.0.0
 */

const { execSync } = require( 'child_process' );

/**
 * Run a WP-CLI command in the test environment.
 *
 * @param {string} command WP-CLI command to run.
 * @return {string} Command output.
 */
function wpCli( command ) {
	try {
		return execSync( `npx wp-env run tests-cli wp ${ command }`, {
			encoding: 'utf-8',
			stdio: [ 'pipe', 'pipe', 'pipe' ],
		} ).trim();
	} catch ( error ) {
		// Command may fail if resource already exists.
		return error.stdout ? error.stdout.trim() : '';
	}
}

module.exports = async function globalSetup() {
	// Create test users.
	wpCli(
		'user create bdcmember bdcmember@test.local --role=bdc_members --user_pass=password123'
	);
	wpCli(
		'user create testsubscriber subscriber@test.local --role=subscriber --user_pass=password123'
	);
	wpCli(
		'user create testauthor author@test.local --role=author --user_pass=password123'
	);

	// Create the members-area page with the login form shortcode.
	wpCli(
		'post create --post_type=page --post_title="Members Area" --post_name=members-area --post_status=publish --post_content="[bdc_login_form] Welcome to the members area."'
	);

	// Set the members-area page in options.
	const pageId = wpCli(
		'post list --post_type=page --name=members-area --field=ID'
	);
	if ( pageId ) {
		wpCli( `option update bdc_members_area_page_id ${ pageId }` );
	}

	// Create test members CPT posts.
	wpCli(
		'post create --post_type=members --post_title="Test Member Post" --post_name=test-member-post --post_status=publish --post_content="This is members-only content."'
	);
	wpCli(
		'post create --post_type=members --post_title="Child Member Post" --post_name=child-member-post --post_status=publish --post_content="This is a child post."'
	);

	// Flush rewrite rules.
	wpCli( 'rewrite flush' );
};
