=== ThriveCart for LearnDash ===
Author: LearnDash
Author URI: https://learndash.com
Plugin URI: https://learndash.com/add-on/thrivecart/
LD Requires at least: 4.7.0
Slug: learndash-thrivecart
Tags: integration, thrivecart,
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrates LearnDash LMS with Thrivecart.

== Description ==

Integrate LearnDash LMS with Thrivecart.

ThriveCart is a powerful eCommerce solution for selling digital products with features such as order bumps and upsells.
With this integration you can create products in ThriveCart and then create a product in WordPress and associate it with a LearnDash courses. Customers are then auto-enrolled into courses after payment.

= Integration Features =

* Supports all ThriveCart functionality including order bumps and upsells
* Automatic enrollment upon successful payment
* Coupon and discount support

See the [Add-on](https://learndash.com/add-on/thrivecart/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE.

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating.

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= [1.0.3] =

* Deprecate - Classes: `LearnDash\Elementor\Container`, `LearnDash\Elementor\App`.
* Tweak - Added compatibility with LearnDash Core v4.13.0.
* Tweak - Updated functions: `learndash_thrivecart_extra_autoloading`.

= [1.0.2] =

* Feature - Add setting to allow user to set partial refund behavior.
* Fix - Remove access when subscription ends, not when cancelled.

= [1.0.1] =

* Fix - Update `learndash_thrivecart_after_create_user` action to include $password argument.
* Tweak - Added `learndash_thrivecart_process_webhook` filter.

= [1.0.0] =

* Initial release
