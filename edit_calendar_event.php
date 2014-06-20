<?php
require_once 'db.php';

new select_stories();

class select_stories {
	private $db;
	
	public function __construct() {
		$this->db = new calendar_app_database();
		if(isset($_POST['id'])) {
			$this->db->update_story($_POST['id'],$_POST['title'],$_POST['description'],$_POST['url']);
		}
		include 'header.php';
		$this->print_page();
	}
	
	public function print_edit_page() {
		echo '
			<html>
			<head>
				<title>Story Selection</title>
				<meta http-equiv="Content-Type"
				    content="text/html; charset=UTF-8" />
				<style type="text/css">
					#story_edit input[type="text"] {
						width: 350px;
					}
				</style>
			</head>
			<body>
			<div id="story_edit">
				'.$this->print_story_edit().'
			</div>
			</body>
		';
	}
	
	public function print_story_edit() {
		$stories = $this->db->get_story($_GET['id']);
		$output .= '<h1>Story Update</h1>';
		$output .= '<form name="story_update" method="POST"><table>';
		foreach($stories as $story) {
			$output .= '<tr><td>Title:<br />';
			$output .= '<input type="hidden" name="id" value="'.$story['id'].'" />';
			$output .= '<input type="text" name="title" value="'.$story['title'].'" />';
			$output .= '</td></tr><tr><td>Description:<br />';
			$output .= '<textarea type="text" name="description" rows=10 cols=100>' . $story['description']. '</textarea>';
			$output .= '</td></tr><tr><td>URL:<br />';
			$output .= '<input type="text" name="url" value="'.$story['url'].'" />';
			$output .= '</td></tr>';
		}
		$output .= '</table><input type="submit" name="submit" value="Submit" /></form>';
		return $output;
	}
	
}

?>