<?php
require_once 'db.php';

new select_stories();

class select_stories {
	private $db;
	
	public function __construct() {
		$this->db = new calendar_app_database();
		if(isset($_POST['submit'])) {
			$this->db->hide_events();
			foreach($_POST['event_id'] as $id) {
				$this->db->show_event($id);
			}
		}
		$this->print_page();
	}
	
	public function print_page() {
		echo '
			<html>
			<head>
				<title>Story Selection</title>
				<meta http-equiv="Content-Type"
				    content="text/html; charset=UTF-8" />
				<style type="text/css">
					#eventss {
						width: 80%;
					}
					
					td {
						padding: 10px 0 10px 10px;
					}
					
					th {
						padding: 10px;
					}
					
					tr:nth-child(odd) {
						background-color: #ccc;
					}
				</style>
			</head>
			<body>
			<div id="events">
				'.$this->print_events().'
			</div>
			</body>
		';
	}
	
	public function print_events() {
		$events = $this->db->get_events();
		if($events==false) {
			return "No events are available.  <a href='admin.php?page=source_calendars'>Add a calendar feed</a>.";
		} else {
			$output .= '<h1>Events</h1>';
			$output .= '<button onclick="refresh_calendars()" class="refresh-calendars">Refresh Calendars</button>';
			$output .= '<button onclick="window.location=\'admin.php?page=source_calendars\'" class="refresh-calendars">Add New Calendar</button><br /><br />';
			$output .= '<form name="events" method="POST"><table>';
			$output .= '<tr><th>Displayed</th><th>Start Time</th><th>Title</th><th>Description</th><th>Location</th></tr>';
			foreach($events as $event) {
				$output .= '<tr><td style="width: 5%">';
				if($event['displayed']=='1') {
					$output .= '<input type="checkbox" name="event_id[]" value="'.$event['id'].'" checked />';
				} else {
					$output .= '<input type="checkbox" name="event_id[]" value="'.$event['id'].'" />';
				}
				$output .= '<td style="width: 10%">';
				$output .= date("M j", strtotime($event['date'])) . '<br />' . date("g:i a", strtotime($event['date']));
				$output .= '</td><td style="width: 30%">';
				$output .= '<a href="'.$event['url'].'">'.$event['summary'].'</a>';
				$output .= '</td><td style="width: 45%">';
				$output .= substr($event['description'],0,200);
				$output .= '</td><td style="width: 20%;">';
				$output .= $event['location'];
				$output .= '</td></tr>';
			}
			$output .= '</table><input type="submit" name="submit" value="Submit" /></form>';	
			$output .= '
				<script type="text/javascript">
					function refresh_calendars() {
						jQuery.get("/wp-content/plugins/calendar_app/load_feeds.php", function() {
							jQuery(".refresh-calendars").html("Calendars refreshed");
						});
					}
				</script>
			';
			return $output;
		}
	}
	
}

?>