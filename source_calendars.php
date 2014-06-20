<?php
session_start();
require_once 'db.php';

new source_calendars();

class source_calendars {
	private $db;	
	
	public function __construct() {
		$this->db = new calendar_app_database();
		if(isset($_POST['new'])) {
			$this->db->add_calendar($_POST['description'], $_POST['url'], $_POST['cal_type']);
			include 'load_feeds.php';
		} else if (isset($_POST['delete'])) {
			foreach($_POST['delete_calendar'] as $id) {
				$this->db->delete_calendar($id);
			}
		}
		echo $this->print_page();
	}
	
	public function print_calendars() {
		$feed_list = $this->db->get_calendar_list();
		$output .= '<h2>Current feeds</h2>';
		$output .= '<form name="sources" method="POST" action="?page=source_calendars"><table>';
		foreach($feed_list as $item) {
			$output .= '<tr><td>';
			$output .= '<input type="checkbox" name="delete_calendar[]" value="'.$item['id'].'" />';
			$output .= "</td><td>";
			$output .= $item['description'];
			$output .= '</td><td>';
			$output .= '<a href="'.$item['url'].'">'.$item['url'].'</a>';
			$output .= '</td><td>';
			$type = "";
			switch ($item['type']) {
				case 'google':
					$type = "Google Calendar";
					break;
				case 'activedata' :
					$type = "ActiveData";
					break;
				case 'ical' :
					$type ="iCal Feed";
					break;
			}
			$output .= '<p>' . $type . '</p>';
			$output .= '</td></tr>';
		}
		$output .= '</table><input type="submit" name="delete" value="Delete Calendar" /></form>';
		return $output;
	}
	
	public function print_form() {
		$output .= '
			<h2>Add a new feed</h2>
			<form name="new_calendar" method="POST" action="?page=source_calendars">
				Feed URL<br /><input type="text" name="url" /><br />
				Description<br /><input type="text" name="description" /><br />
				Type<br /><select name="cal_type">
					<option value="google">Google Calendar (XML)</option>
					<!-- <option value="activedata">ActiveData (XML)</option> -->
					<option value="ical">Generic iCal Feed</option>
				</select>
				<p><strong>Note:</strong> If adding a Google Calendar XML feed, replace "/basic" on the end of the URL with "/full".  This will allow your website to pull additional event information from Google Calendar.</p>
				<input type="submit" name="new" value="Add Calendar" />
			</form>
		';
		return $output;
	}
	
	public function print_page() {
		$output .= '
			<html>
			<head>
				<title>RSS Feeds</title>
			</head>
			<body>
				'. $this->print_form(). '<br />' . $this->print_calendars().'
			</body>
			</html>
		';
		return $output;
	}
}

?>