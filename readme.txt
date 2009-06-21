=== Custom Profile Filters for BuddyPress ===
Contributors: boonebgorges
Tags: buddypress, profile, filter
Requires at least: WPMu 2.7.1, BuddyPress 1.0
Tested up to: WPMu 2.7.1, BuddyPress 1.0.1
Stable tag: trunk

Allows users to override the automatic links in their BuddyPress profiles by enclosing tags in square brackets.

== Description ==

BuddyPress has a built in feature that automatically turns words and phrases in the fields of a user profile into clickable links to other users with the same phrases in their own profiles. This plugin allows users to override this automatic filter, by enclosing their desired tags in square brackets.

This plugin was created as part of the CUNY Academic Commons of the City University of New York. See http://commons.gc.cuny.edu to learn more about this bodacious project.


== Installation ==

1. Upload `custom-profile-filters-for-buddypress.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress (/wp-admin/)



== Notes ==

The plugin checks each profile for square brackets and activates if it finds any. If no square brackets are found, the default automatic filter will kick in.

You might want to insert a small explanation into your BP profile edit template (/wp-content/bp-themes/[your-member-theme]/profile/edit.php that tells your site's users how to use these brackets. Here's what I use: 
	
"Words or phrases in your profile can be linked to the profiles of other members that contain the same phrases. To specify which words or phrases should be linked, add square brackets: e.g. "I enjoy [English literature] and [technology]." If you do not specify anything, phrases will be chosen automatically."

Future features include: admin tab with toggle switch; ability to tweak BP's automatic profile filter (e.g. to parse semi-colon separated lists in addition to commas).
