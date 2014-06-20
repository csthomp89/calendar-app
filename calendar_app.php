<?php 
/**
 * Plugin Name: Calendar App
 * Plugin URI: https://github.com/csthomp89/calendar-app
 * Description: Aggregates iCal feeds and allows you to choose which events to display/hide.
 * Version: 1.0
 * Author: Scott Thompson, University Communications, NC State University
 * Author URI: http://sciences.ncsu.edu
 * License: MIT
 */

include_once 'db.php';

register_activation_hook( __FILE__, 'calendar_app_activation' );
register_deactivation_hook( __FILE__, 'calendar_app_deactivation' );

/** Step 2 (from text above). */
add_action( 'admin_menu', 'calendar_app_plugin_menu' );
add_action( 'calendar_app_hourly_refresh', 'calendar_app_refresh_calendars' );

/** Step 1. */
function calendar_app_plugin_menu() {
	add_menu_page( "Calendar", "Calendar", 'manage_options', 'calendar-app', 'calendar_app_plugin_options');
	add_submenu_page( 'calendar-app', 'Calendar Feeds', 'Source Calendars', 'manage_options', 'source_calendars', 'calendar_app_source_calendars');
	add_submenu_page( 'calendar-app', 'Calendar Settings', 'Settings', 'manage_options', 'calendar_settings', 'calendar_app_settings');
	//add_options_page( 'My Plugin Options', 'My Plugin', 'manage_options', 'my-unique-identifier', 'my_plugin_options' );
}

function calendar_app_activation() {
	wp_schedule_event( time(), 'hourly', 'calendar_app_hourly_refresh' );
}

function calendar_app_deactivation() {
	wp_clear_scheduled_hook( 'calendar_app_hourly_refresh' );
}

/** Step 3. */
function calendar_app_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	include 'calendar_events.php';
	echo '</div>';
}

function calendar_app_source_calendars() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	include 'source_calendars.php';
	echo '</div>';
}

function calendar_app_settings() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h2>Update Events</h2>';
	echo '<button onclick="refresh_calendars()" class="refresh-calendars">Refresh Calendars</button><br /><br />';
	echo '</div>';
	
	echo '
		<script type="text/javascript">
			function refresh_calendars() {
				jQuery.get("/wp-content/plugins/calendar_app/load_feeds.php", function() {
					jQuery(".refresh-calendars").html("Calendars refreshed");
				});
			}
		</script>
	';
}

function calendar_app_get_upcoming_events($num) {
	$db = new calendar_app_database();
	return $db->get_upcoming_events($num);
}

function calendar_app_refresh_calendars() {
	include 'load_feeds.php';
}