=== Restrict Content Pro for LearnDash ===
Author: LearnDash
Author URI: https://learndash.com
Plugin URI:
LD Requires at least: 2.5
Slug: learndash-restrict-content-pro
Tags: integration, membership, restrict content pro,
Requires at least: 4.9
Tested up to: 6.2.2
Requires PHP: 7.0
Stable tag: 1.1.1

Integrate LearnDash LMS with Restrict Content Pro.

== Description ==

Integrate LearnDash LMS with Restrict Content Pro.

= Integration Features =

See the [Add-on](https://learndash.com) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE.

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating.

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= 1.1.1 =

* Fix - Retroactive process on existing membership.
* Fix - Free membership subscription triggers user enrollment for manual paid membership.
* Fix - Support RCP Group Accounts addon.
* Fix - Unenroll user upon membership expiration instead of membership cancellation.
* Fix - Allow retroactive tool to be run at any point in time.
* Fix - Potential timeout issue when processing course access during cron job.
* Fix - Support for LearnDash Groups enrollment.
* Fix - User losing course access after more than one membership providing access to a course and the user losing access to at least one membership.
* Fix - Update course access when switching between memberships.
* Fix - Remove course access upon membership expiration.
* Fix - Enroll into course after successful payment verification.
* Fix - Fatal error when using PHP 8.0.

= 1.1.0 =

* Added RCP 3.0 compatibility hook function
* Fixed course association
