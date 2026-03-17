=== BDC Members ===
Contributors: elliottrichmond
Tags: members, membership, access control, custom post type, login
Requires at least: 6.9
Tested up to: 6.9
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Creates a members-only area with gated access, a custom members role, and a hierarchical members custom post type.

== Description ==

BDC Members is a WordPress plugin for the British Dalmatian Club that creates a members-only area on your site. It introduces a custom role (`bdc_members`), a members-area page with gated access and a login panel, and a hierarchical custom post type (`members`) that is only visible to authorised roles.

**Features:**

* Custom `bdc_members` role with read-only access to members content.
* Hierarchical `members` custom post type with block editor support.
* Automatic login form display for logged-out visitors on the members-area page.
* Access gating on both the members-area page and all members CPT content.
* Login redirect — `bdc_members` users are sent to the members-area page after login.
* REST API restriction — members endpoints are locked to authorised roles.
* Admin bar hidden for `bdc_members` users.
* `bdc_members` users are redirected away from the WordPress admin.
* Members CPT excluded from search results, RSS feeds, and sitemaps for unauthorised users.
* Designed for block themes — all gated content is injected via `the_content` filter.

**Roles and access:**

* `bdc_members` — can view the members-area page and all members CPT content on the front end.
* `administrator`, `editor` — full editorial access to members CPT posts.
* `author` — can create, edit, and delete their own members CPT posts.
* All other roles — see an "Access Denied" message on gated pages.

== Installation ==

1. Upload the `bdc-members` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Create a page with the slug `members-area` (or any slug you choose).
4. Go to **Settings > BDC Members** and select your members-area page.
5. The login form will appear automatically for logged-out visitors.
6. Start creating content under the **Members** menu in the admin.

== Frequently Asked Questions ==

= Do I need to add a shortcode to the members-area page? =

No. The plugin automatically displays a login form for logged-out visitors and replaces the page content with an "Access Denied" message for unauthorised logged-in users.

= Does this work with block themes? =

Yes. The plugin is designed for block themes. All gated content is injected via the `the_content` filter so your block theme's templates, header, and footer render normally.

= What happens if I deactivate the plugin? =

The `bdc_members` role and custom capabilities are intentionally left in place. They become inert without the plugin's logic and prevent users from being locked out of content they previously had access to.

= Can I change the members-area page slug? =

Yes. Go to **Settings > BDC Members** and select any page. You can also filter the fallback slug using the `bdc_members_area_slug` filter.

= Are members posts visible in search results? =

Only to users with the `bdc_access_members` capability. Unauthorised users and logged-out visitors will not see members posts in search results, RSS feeds, or sitemaps.

== Changelog ==

= 1.0.0 =
* Initial release.
* Custom `bdc_members` role with `read` and `bdc_access_members` capabilities.
* Hierarchical `members` custom post type with block editor support.
* Members-area page with automatic login form for logged-out visitors.
* Access gating via `the_content` filter (block theme compatible).
* Login redirect for `bdc_members` users.
* REST API restriction for members endpoints.
* Admin bar hidden and wp-admin redirect for `bdc_members` users.
* Members CPT excluded from search, feeds, and sitemaps.
* Settings page for selecting the members-area page.
