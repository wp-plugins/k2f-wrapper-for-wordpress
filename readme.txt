=== Plugin Name ===
Contributors: Christian Sciberras, Paul Portelli
Tags: framework, K2F, management, adapter, interface, core
Requires at least: 2.0.2
Tested up to: 3.0.2
Stable tag: trunk

K2F is a generic framework for Rapid Application Development (RAD) in PHP.

== Description ==

This plugin holds the K2F framework which other K2F-based plugins depend on.

== Frequently Asked Questions ==

= Where is K2F? =

This plugin does not contain K2F, you have to download K2F separately.

= Where do I put K2F? =

K2F can be put anywhere on the server. It can be shared between different CMSes
as well as multiple instance of WordPress. You just have to change settings in
the K2F Wrapper Plugin to reflect this path.

== Installation ==

1. Upload the plugin folder, `K2F`, into the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Install K2F-based plugins or develop your own PHP files based on K2F.

== Changelog ==

= 1.0 =
* Fixed issue with output buffering in WordPress.
* Created options page to manage K2F path and K2F debug mode.
* Fixed issue with fatal errors if K2F was not found, now a warning is shown.

= 0.5 =
* Initial implementation.

== Upgrade Notice ==

= 1.0 =
Previous release was not as flexible. Now supports better management of K2F.