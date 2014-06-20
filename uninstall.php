<?php 

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS calendar_entries" );
$wpdb->query( "DROP TABLE IF EXISTS calendars" );