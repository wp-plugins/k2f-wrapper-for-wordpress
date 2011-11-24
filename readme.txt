=== Plugin Name ===
Contributors: Christian Sciberras, Paul Portelli
Tags: framework, K2F, management, adapter, interface, core
Requires at least: 2.0.2
Tested up to: 3.2.1
Stable tag: trunk

K2F is a generic framework for Rapid Application Development (RAD) in PHP.

== Description ==

This plugin holds the K2F framework which other K2F-based plugins depend on.

== Frequently Asked Questions ==

= Where is K2F? =

This plugin does not contain K2F, you have to download K2F separately.

= Where do I put K2F? =

NOTE: As of v2.0, K2F can be installed automatically within the plugin.

K2F can be put anywhere on the server. It can be shared between different CMSes
as well as multiple instance of WordPress. You just have to change settings in
the K2F Wrapper Plugin to reflect this path.

== Installation ==

1. Upload the plugin folder, `K2F`, into the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Install K2F-based plugins or develop your own PHP files based on K2F.

== Changelog ==

= 2.0 =
* Added K2F installer/updater.

= 1.5 =
* Fixed issues when Wordpress was loaded "artificially", by loading a plugin file directly.

= 1.4 =
* Fixed crash in previous version.

= 1.3 =
* Parameter hotfix can be disarmed.
* Fixed issue with wordpress not fully loading prior to framework load.

= 1.0 =
* Fixed issue with output buffering in WordPress.
* Created options page to manage K2F path and K2F debug mode.
* Fixed issue with fatal errors if K2F was not found, now a warning is shown.

= 0.5 =
* Initial implementation.

== Upgrade Notice ==

= 1.0 =
Previous release was not as flexible. Now supports better management of K2F.

= 1.3 =
Newer plugins may not run correctly on older version.

= 1.4 =
Fixed a crash in the previous version (1.4).

= 1.5 =
Some plugins, like NextGen gallery, fault horribly on older interface versions.
Security of the system wasn't compromised, however, depending on the fault, real FS path could be disclosed.