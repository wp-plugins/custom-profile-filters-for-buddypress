<?php

/*
Plugin Name: Custom Profile Filters for BuddyPress
Plugin URI: http://dev.commons.gc.cuny.edu
Description: Changes the way that profile data fields get filtered into clickable URLs.
Version: 0.3
Author: Boone Gorges
Author URI: http://teleogistic.net
*/

/* Only load the BuddyPress plugin functions if BuddyPress is loaded and initialized. */
function custom_profile_filters_for_buddypress_init() {
	require( dirname( __FILE__ ) . '/custom-profile-filters-for-buddypress-bp-functions.php' );
}
add_action( 'bp_init', 'custom_profile_filters_for_buddypress_init' );

?>