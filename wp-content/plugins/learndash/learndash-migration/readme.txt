=== LearnDash LMS - Migration ===
Author: LearnDash
Author URI: https://learndash.com/
Plugin URI: https://learndash.com/add-on/migration/
LD Requires at least: 4.10.0
Slug: learndash-migration
Tags: learndash, migration,
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Migrate your LMS to LearnDash.

== Description ==

Official LearnDash LMS addon that helps user to migrate from other LMS platforms to LeanDash in just a few clicks.

= Features =

* Migrate a course and its lessons, topics, quizzes, questions, and answers.
* Migrate from LearnPress, Tutor LMS, and more LMS platforms (coming soon).

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE.

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating.

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= [1.0.1] =

* Deprecate - Classes: `LearnDash\Migration\Container`, `LearnDash\Migration\App`.
* Tweak - Added compatibility with LearnDash Core v4.13.0.
* Tweak - Updated functions: `learndash_migration_extra_autoloading`.

= [1.0.0] =

* Initial version.
* Tweak - Added filters: `learndash_migration_integration_classes`, `learndash_migration_format_settings`, `learndash_migration_format_meta`, `learndash_migration_new_course_url`.
* Tweak - Added functions: `learndash_migration_extra_autoloading`.
