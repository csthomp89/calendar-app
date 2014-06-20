<?php

class calendar_app_database {
	private $conn;
	
	public function __construct() {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php';
		// Update database host, username, and password
		$this->conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';',DB_USER,DB_PASSWORD);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
		$this->create_tables();
	}
	
	public function add_calendar($description, $url, $type) {
		$insertsql = "INSERT INTO calendars (description, url, type) VALUES (:description, :url, :type)";
		$query = $this->conn->prepare($insertsql);
		$query->bindParam(":description",$description);
		$query->bindParam(":url",$url);
		$query->bindParam(":type",$type);
		$query->execute();
	}
	
	public function delete_calendar($id) {
		$deletesql = "DELETE FROM calendars WHERE id=:id";
		$query = $this->conn->prepare($deletesql);
		$query->bindParam(":id", $id);
		$query->execute();

		$deletesql = "DELETE FROM calendar_entries WHERE calendar_id=:id";
		$query = $this->conn->prepare($deletesql);
		$query->bindParam(":id", $id);
		$query->execute();
	}
	
	public function get_calendar_list() {
		$sql = "SELECT * FROM calendars";
		return $this->conn->query($sql);
	}
	
	public function get_events() {
		$sql = "SELECT * FROM calendar_entries WHERE date > CURDATE() ORDER BY date ASC LIMIT 25";
		$results = $this->conn->query($sql);
		if($results->rowCount()==0) {
			return false;
		} else {
			return $results;
		}
	}

	public function get_upcoming_events($num = NULL) {
		if ($num == NULL) {
			$query = $this->get_events();
		}
		else {
			$sql = "SELECT * FROM calendar_entries WHERE date > CURDATE() AND displayed=1 ORDER BY date ASC LIMIT :num";
			$query = $this->conn->prepare($sql);
			$query->bindParam(":num", $num, PDO::PARAM_INT);
			$query->execute();
		}
		return $query->fetchAll(PDO::FETCH_ASSOC);	
	}
	
	public function add_event($uid, $summary, $description, $date, $location, $calendar_id, $url) {
		$insertsql = "INSERT INTO calendar_entries (uid, summary, description, date, location, calendar_id, url) VALUES (:uid, :summary, :description, :date, :location, :calendar_id, :url) ON DUPLICATE KEY UPDATE summary=:summary, description=:description, date=:date, location=:location, url=:url";
		$query = $this->conn->prepare($insertsql);
		$query->bindParam(":uid", $uid);
		$query->bindParam(":summary", $summary);
		$query->bindParam(":description", $description);
		$query->bindParam(":date", date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $date))));
		$query->bindParam(":location", $location);
		$query->bindParam(":calendar_id", $calendar_id);
		$query->bindParam(":url", $url);
		$query->execute();
	}

	public function toggle_event($id) {
		$sql = "UPDATE calendar_entries SET displayed =NOT displayed WHERE id=:id";
		$query = $this->conn->prepare($sql);
		$query->bindParam(":id", $id);
		$query->execute();
	}
	
	public function hide_event($id) {
		$sql = "UPDATE calendar_entries SET displayed='0' WHERE id=:id";
		$query = $this->conn->prepare($sql);
		$query->bindParam(":id", $id);
		$query->execute();
	}
	
	public function hide_events() {
		$sql = "UPDATE calendar_entries SET displayed='0'";
		$query = $this->conn->prepare($sql);
		$query->bindParam(":id", $id);
		$query->execute();
	}
	
	public function show_event($id) {
		$sql = "UPDATE calendar_entries SET displayed='1' WHERE id=:id";
		$query = $this->conn->prepare($sql);
		$query->bindParam(":id", $id);
		$query->execute();
	}
	
	public function create_tables() {
			$createquery = "CREATE TABLE calendar_entries (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  displayed tinyint(1) NOT NULL DEFAULT '1',
  uid text,
  summary text,
  description text,
  date datetime DEFAULT NULL,
  location text,
  calendar_id int(11) DEFAULT NULL,
  url text,
  PRIMARY KEY (id),
  UNIQUE KEY uid_unique (uid(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
			
CREATE TABLE calendars (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  description text,
  url text NOT NULL,
  type text,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
			
			";
			$this->conn->exec($createquery);
			
			$insertsql = "INSERT IGNORE INTO feeds (id, title, description, link) VALUES (1, 'RSS App Newsfeed', 'RSS App Newsfeed', :link)";
			$query = $this->conn->prepare($insertsql);
			/*$query->bindParam(":title", "RSS App Newsfeed");
			$query->bindParam(":description", "RSS App Newsfeed");*/
			$query->bindParam(":link", $_SERVER['SERVER_NAME']);
			$query->execute();
		}
}
?>