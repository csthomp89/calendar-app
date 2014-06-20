<?php

require_once 'db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/calendar_app/class.iCalReader.php';

new load_feeds();

class load_feeds {
	private $db;
	
	public function __construct() {
		$this->db = new calendar_app_database();
		$this->refresh();
	}

	public function refresh() {
		$calendars = $this->db->get_calendar_list();
		foreach($calendars as $calendar) {
			switch ($calendar['type']) {
				case 'google':
					$this->google($calendar);
					break;
				case 'activedata' :
					$this->activedata($calendar);
					break;
				case 'ical' :
					$this->ical($calendar);
					break;
			}
		}
	}

	function ical($calendar) {
		
		$ical   = new ICal($calendar['url']);
		$events = $ical->events();
		
		foreach($events as $event) {
			
			$url = null;
			$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
			if(preg_match($reg_exUrl, $event['DESCRIPTION'], $tempUrl)) {
				$url = $tempUrl[0];
			}
			
			$this->db->add_event(
				$event['UID'],
				$event['SUMMARY'],
				str_replace("\\n","<br />",$event['DESCRIPTION']),
				$event['DTSTART'],
				$event['LOCATION'],
				$calendar['id'],
				$url
			);
		}
	}
	
	function google($calendar) {
		$feed = simplexml_load_file($calendar['url']);
		foreach($feed->entry as $event) {
		
			$this->db->add_event(
				$event->children('gCal',true)->uid->attributes()->value,
				$event->title,
				$event->content,
				date("Y-m-d H:i:s", strtotime($event->children('gd',true)->when->attributes()->startTime)),
				$event->children('gd',true)->where->attributes()->valueString,
				$calendar['id'],
				$event->link->attributes()->href
			);
		}
	}
	
	function activedata($calendar) {
		error_log($calendar['url']);
		$feed = simplexml_load_file($calendar['url']);
		foreach($feed->EVENT as $event) {
		
			$this->db->add_event(
				$event->EventGUID,
				$event->Name,
				$event->Description,
				date("Y-m-d H:i:s", strtotime($event->StartDate . " " . $event->StartTime)),
				$event->Locations->Location->LocationName,
				$calendar['id'],
				$event->EventURL
			);
		}
	}

}

?>