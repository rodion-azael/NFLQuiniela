<?php
session_start();

require_once('db.class.php');
require_once('API.class.php');

// define('TIMEZONE', 'America/Mexico_City');
// date_default_timezone_set(TIMEZONE);

error_reporting(E_ALL);
// error_reporting(0);
class apiNFL extends API{

	private	
		$conn,
		$season;
	
	/*
	* Public class constructor.
	* Calls parent constructor which 
	* contains all API base methods
	*/
	public function __construct($request, $origin) {
		parent::__construct($request);			
		
		$this->conn = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
		if (!$this->conn) {
			echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
			echo "errno de depuración: " . mysqli_connect_errno() . PHP_EOL;
			echo "error de depuración: " . mysqli_connect_error() . PHP_EOL;
			die();
		}
		$this->season = 1;
		$this->conn->set_charset("utf8");
	}	
	
	/**
	* Executes a Query and returns an Assoc Array with the query result.
	*/
	private function runQueryToArray($sql){
		$result = array();
		$res = $this->conn->query($sql);
		
		if(!$res){
			return array(
				'Error' => $this->conn->error
			);
		}
			
		while($_res = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			$result[] = $_res;
		}
		$res->free();
		return $result;
	}
	
	/*
	*
	*/
	private function validateSession($type = ''){
		if($type == 'admin'){
			return $_SESSION['user']['IsAdmin'];
		}		
		return $_SESSION['user']['UserID'];
	}
	
	/*
	*
	*/
	// private function loginAsAdmin($req){

	// }
	
	
	/*
	*
	*/
	public function getAllSeasons($args, $req){
		$sql = "SELECT SeasonID as ID, Name FROM Seasons ORDER BY ID";
		return $this->runQueryToArray($sql);
	}
	
	
	/*
	*
	*/
	public function getAllWeeks($args, $req){
		if(!$this->validateSession())
			return array(
				'error' => 'Permisos insuficientes.',
				'success' => false,
				'relogin' => true
			);
			
		if(!is_numeric($args[0]))
			return array(
				'success' => false,
				'error' => 'Datos invalidos'
			);
		
		$order = (isset($args[1]) && !empty($args[1])) ? $args[1] : 'ASC';
			
		
		$sql = "SELECT WeekID, Number, 
						DATE_FORMAT(Start, '%b %d %Y %h:%i %p') AS Start,
						DATE_FORMAT(End, '%b %d %Y %h:%i %p') AS End,
						IF(End > NOW(), 1, 0) AS IsEditable
				FROM Weeks 
				WHERE SeasonID = ". $args[0]."
				ORDER BY CAST(Number AS INT) ". $order;
				
		return $this->runQueryToArray($sql);
	}
	
	/**
	* getWeekData/WeekID
	*/
	public function getWeekData($args, $req){
		if(!$this->validateSession('admin'))
			return array(
				'error' => 'Permisos insuficientes.',
				'success' => false,
				'relogin' => true
			);
		
		if(!is_numeric($args[0]))
			return array(
				'success' => false,
				'error' => 'Datos invalidos'
			);
		
		$sql = "SELECT WeekID, Number, 
						DATE_FORMAT(Start, '%b %d %Y %h:%i %p') AS Start,
						DATE_FORMAT(End, '%b %d %Y %h:%i %p') AS End 
				FROM Weeks 
				WHERE WeekID = ".$args[0];
		return $this->runQueryToArray($sql);
	}
	
	public function getWeekResults($args, $req){
		// if(!$this->validateSession('admin'))
			// return array(
				// 'error' => 'Permisos insuficientes.',
				// 'success' => false,
				// 'relogin' => true
			// );
		
		if(!is_numeric($args[0]))
			return array(
				'success' => false,
				'error' => 'Datos invalidos'
			);
		
		$sql = "SELECT 
				IFNULL(mr.ResultID, null) as ID,
				m.MatchID,
				IFNULL(mr.HomePts, 0) as HomePts,
				IFNULL(mr.AwayPts, 0) as AwayPts,
				m.HomeTeamID,
				m.AwayTeamID,
				lt.Name as HomeName,
				lt.State as HomeState,
				lt.Image as HomeImage,
				at.Name as AwayName,
				at.State as AwayState,
				at.Image as AwayImage
			FROM Matchs as m
				LEFT JOIN MatchResults as mr ON m.MatchID LIKE mr.MatchID
				LEFT JOIN Teams as lt ON m.HomeTeamID LIKE lt.TeamId
				LEFT JOIN Teams as at ON m.AwayTeamID LIKE at.TeamId
			WHERE m.WeekID = ".$args[0]."
			ORDER BY m.Time ASC, m.MatchID ASC";
			
		return $this->runQueryToArray($sql);
	}
	
	/*
	*
	*/
	private function updateResult($args, $req){
		$stmt = $this->conn->prepare("UPDATE MatchResults SET HomePts = ?, AwayPts = ? WHERE MatchID = ?");
		$stmt->bind_param('iii', $req['HomePts'], $req['AwayPts'], $req['MatchID']);
		
		if ($stmt->execute()) {
			return array(
				'success' => true,
				'message' => 'Actualizada con exito'
			);
		}else {
			return array(
				'success' => false,
				'message' => $stmt->error,
				'errno' => $stmt->errno
			);
		}
	}
	
	/*
	*
	*/
	private function finalPts($high, $win, $lose){
		$difference = intval($win) - intval($lose);
		if($difference > 7 && intval($high) == 1){
			return 3;
		}else if($difference <= 7 && intval($high) == 0){
			return 3;
		}else{
			return 1;
		} 
	}
	
	
	/*
	*
	*/
	private function calculatePts($input, $req){
		if(intval($req['HomePts']) > intval($req['AwayPts']) && intval($req['HomeID']) == intval($input['TeamID'])){
			return $this->finalPts($input['High'], $req['HomePts'], $req['AwayPts']);
		}
		else if(intval($req['HomePts']) < intval($req['AwayPts']) && intval($req['AwayID']) == intval($input['TeamID'])){
			return $this->finalPts($input['High'], $req['AwayPts'], $req['HomePts']);
		}
		else{
			return 0;
		}
	}
	
	/*
	*
	*/
	private function saveMatchPts($args, $req){
		$sql = "SELECT * FROM UserInputs WHERE MatchID = ".$req['MatchID'];
		$inputs = $this->runQueryToArray($sql);
		
		$stmt = $this->conn->prepare("DELETE FROM UserPtsByMatch WHERE MatchID = ?");
		$stmt->bind_param('i', $req['MatchID']);
		$pts = 0;
		
		if($stmt->execute()){
			$stmt->close();
			$stmt = $this->conn->prepare("INSERT INTO UserPtsByMatch (MatchID, WeekID, UserID, Pts, InputID) VALUES (?, ?, ?, ?, ?)");
			foreach($inputs as $input){
				$pts = $this->calculatePts($input, $req);
				$stmt->bind_param('iiiii', $req['MatchID'], $req['WeekID'], $input['UserID'], $pts, $input['InputID']);
				$stmt->execute();
			}			
		}
	}
	
	/*
	*
	*/
	public function saveNewResult($args, $req){
		if(!$this->validateSession('admin'))
			return array(
				'error' => 'Permisos insuficientes.',
				'success' => false,
				'relogin' => true
			);
		
		$stmt = $this->conn->prepare("INSERT INTO MatchResults (MatchID, HomePts, AwayPts) VALUES (?, ?, ?)");
		$stmt->bind_param('iii', $req['MatchID'], $req['HomePts'], $req['AwayPts']);
		
		if ($stmt->execute()) {
			$stmt->close();
			$this->saveMatchPts($args, $req);
			return array(
				'success' => true,
				'message' => 'Guardado con exito'
			);
		}else if ($stmt->errno == 1062){
			$stmt->close();
			$this->saveMatchPts($args, $req);
			$stmt->close();
			return $this->updateResult($args, $req);
		} else {
			return array(
				'success' => false,
				'message' => $stmt->error,
				'errno' => $stmt->errno
			);
		}
	}
	
	
	/**
	*
	*/
	public function deleteWeek($args, $req){
		if(!$this->validateSession('admin'))
			return array(
				'error' => 'Permisos insuficientes.',
				'success' => false,
				'relogin' => true
			);		
		
		if(!is_numeric($args[0]))
			return array(
				'success' => false,
				'error' => 'Datos invalidos.'
			);
		
		$stmt = $this->conn->prepare("DELETE FROM Weeks WHERE WeekID LIKE ?");
		$stmt->bind_param('i', $args[0]);
		
		if ($stmt->execute()) {
			return array(
				'success' => true,
				'message' => 'Creada con exito'
			);
		} else {
			return array(
				'success' => false,
				'message' => $stmt->error
			);
		}		
	}
	
	/*
	*
	*/
	public function getTotalResultsBySeasonID($args, $req){
		if(!is_numeric($args[0]))
			return array(
				'error' => 'Datos invalidos.',
				'success' => false
			);
			
		// $sql = "SELECT Number, WeekID FROM Weeks WHERE SeasonID = ".$args[0];
		// $semanas = $this->runQueryToArray($sql);
		
		$sql = "SELECT Name, LastName, UserID FROM Users WHERE Active = 1 AND IsAdmin = 0";
		$usuarios = $this->runQueryToArray($sql);
		
		foreach($usuarios as &$user){
			$sql = "SELECT SUM(p.Pts) as Points, p.WeekID, w.Number as WeekNumber
					FROM UserPtsByMatch p
					LEFT JOIN Users as u ON p.UserID LIKE u.UserID
					LEFT JOIN Weeks as w ON p.WeekID LIKE w.WeekID
					WHERE p.UserID = ".$user['UserID']." AND w.SeasonID = ".$args[0]."
					GROUP BY p.WeekID, u.UserID
					ORDER BY WeekNumber ASC";
			$sql2 = "SELECT 
						SUM(p.Pts) as Points
					FROM UserPtsByMatch p
					LEFT JOIN Users as u ON p.UserID LIKE u.UserID
					LEFT JOIN Weeks as w ON p.WeekID LIKE w.WeekID
					WHERE p.UserID = ".$user['UserID']." AND w.SeasonID = ".$args[0]."					
					GROUP BY u.UserID";
					
			$user['Points'] = $this->runQueryToArray($sql);
			$_t = $this->runQueryToArray($sql2);
			$user['TotalPoints'] = $_t[0]['Points'];
		}
		
		usort($usuarios, function ($a, $b) {
			return $a['TotalPoints'] < $b['TotalPoints'];
		});
		return $usuarios;
	}
	
	/*
	* /WeekID
	*/
	public function getAllMatchByWeek($args, $req){
		if(!is_numeric($args[0]))
			return array(
				'error' => 'Datos invalidos.',
				'success' => false
			);
		
		$weekID = $args[0];
		
		$sql = "SELECT m.MatchID as ID,
						DATE_FORMAT(m.Time, '%b %d %Y %h:%i %p') as GameTime, 
						DATE_FORMAT(m.CaptureLimit, '%b %d %Y %h:%i %p') as CaptureLimit, 
						h.Name as HomeName, 
						h.State as HomeState, 
						h.Image as HomeImage, 
						a.Name as AwayName, 
						a.State as AwayState,
						a.Image as AwayImage
				FROM Matchs m
				LEFT JOIN Teams h ON m.HomeTeamID LIKE h.TeamId
				LEFT JOIN Teams a ON m.AwayTeamID LIKE a.TeamId
				WHERE m.WeekID = '". $weekID . "'
				ORDER BY m.Time ASC, ID ASC";
				
		return $this->runQueryToArray($sql);
	}
	
	/*
	*
	*/
	public function getMatchByID($args, $req){
		if(!is_numeric($args[0]))
			return array(
				'error' => 'Datos invalidos.',
				'success' => false
			);
		
		$MatchID = $args[0];
		
		$sql = "SELECT m.MatchID as ID,
						DATE_FORMAT(m.Time, '%b %d %Y %h:%i %p') as GameTime, 
						DATE_FORMAT(m.CaptureLimit, '%b %d %Y %h:%i %p') as CaptureLimit, 
						h.Name as HomeName, 
						h.State as HomeState, 
						h.Image as HomeImage, 
						a.Name as AwayName, 
						a.State as AwayState,
						a.Image as AwayImage
				FROM Matchs m
				LEFT JOIN Teams h ON m.HomeTeamID LIKE h.TeamId
				LEFT JOIN Teams a ON m.AwayTeamID LIKE a.TeamId
				WHERE m.MatchID = '". $MatchID . "'";
				
		return $this->runQueryToArray($sql);
	}
	
	/**
	*
	*/
	public function saveNewMatch($args, $req){
		if(!$this->validateSession('admin'))
			return array(
				'error' => 'Permisos insuficientes.',
				'success' => false,
				'relogin' => true
			);
		$stmt = $this->conn->prepare("INSERT INTO Matchs (WeekID, HomeTeamID, AwayTeamID, Time, CaptureLimit) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param('iiiss', $req['Week'], $req['Home'], $req['Away'], $req['Time'], $req['Limit']);
		
		if ($stmt->execute()) {
			$newMatch = $this->getMatchByID(array($stmt->insert_id), array());
			return array(
				'success' => true,
				'message' => 'Creada con exito',
				'newMatch' => $newMatch
			);
		} else {
			return array(
				'success' => false,
				'message' => $stmt->error,
				'errno' => $stmt->errno
			);
		}	
		
	}
	
	/*
	*
	*/
	public function saveInputs($args, $req){
		if(!is_numeric($args[0]))
			return array(
				'error' => 'Datos invalidos.',
				'success' => false
			);
		
		$week = $args[0];
		$userID = (isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID'])) ? $_SESSION['user']['UserID'] : 0;
		if($userID == 0){
			return array(
				'error' => 'Permisos insuficientes.',
				'success' => false,
				'relogin' => true
			);
		}
		
		$clean = $this->conn->prepare("DELETE FROM UserInputs WHERE UserID = ? AND WeekID = ?");
		$clean->bind_param('ii', $userID, $week);
		
		
		if(!$clean->execute()){
			return array(
				'success' => false,
				'message' => $clean->error
			);
		}
		$clean->close();
			
		$stmt = $this->conn->prepare("INSERT INTO UserInputs (UserID, MatchID, TeamId, WeekID, High) VALUES (?,?,?,?,?)");
		$stmt->bind_param('iiiii', $userID, $matchId, $team, $week, $high);
		
		$count = 0;
		foreach($req as $matchId => $match){
			$team = $match['team'];
			$high = $match['high'];
			$stmt->execute();
			$count++;
		}
		$stmt->close();
		
		return array(
			'success' => true,
			'saved' =>	$count
		);
	}
	
	/*
	*
	*/
	public function getUserInputsByWeekId($args, $req){
		if(!is_numeric($args[0]))
			return array(
				'error' => 'Datos invalidos.',
				'success' => false
			);
		$weekID = $args[0];
		$userID = $this->getUserID();
		
		if($userID == 0){
			return array(
				'error' => 'Permisos insuficientes.',
				'success' => false,
				'relogin' => true
			);
		}
		
		$sql = "SELECT 
				h.Name as HomeName, h.State as HomeState, h.Image as HomeImage, h.TeamId as HomeID,
				a.Name as AwayName, a.State as AwayState, a.Image as AwayImage, a.TeamId as AwayID,
				m.MatchID as MatchID, m.Time as GameTime, m.CaptureLimit as CaptureLimit, IFNULL(ui.TeamId, 0) as UserInput, IFNULL(ui.High, 0) as High,
				IFNULL(mr.HomePts, 'NA') as HomePts, IFNULL(mr.AwayPts, 'NA') as AwayPts,
				IF(m.CaptureLimit < (NOW() - INTERVAL 1 HOUR), 0, 1) AS IsEditable
			FROM Matchs as m
			LEFT JOIN MatchResults as mr ON mr.MatchID LIKE m.MatchID
			LEFT JOIN UserInputs as ui ON m.MatchID LIKE ui.MatchID AND ui.UserID = ".$userID."
			LEFT JOIN Teams as h ON h.TeamId LIKE m.HomeTeamID
			LEFT JOIN Teams as a ON a.TeamId LIKE m.AwayTeamID
			WHERE m.WeekID = ".$weekID."
			ORDER BY GameTime ASC, MatchID ASC";
		return $this->runQueryToArray($sql);
	}
	
	/*
	*
	*/
	public function getAllTeams($args, $req){
		$sql = "SELECT * FROM Teams";
		
		return $this->runQueryToArray($sql);
	}
	
	/*
	*
	*/
	public function getAllRestulsByWeekID($args, $req){
		if(!is_numeric($args[0])){
			return array(
				'success' => 'false',
				'error' => 'Inputs invalidos.'
			);
		}
		
		$weekID = $args[0];
		$userID = $this->getUserID();
		
		$sql = "SELECT 
					u.UserID, u.Name, u.LastName, IFNULL(p.Pts, 0) as Points
				FROM Users as u
				LEFT JOIN (SELECT UserID, SUM(Pts) as Pts FROM UserPtsByMatch WHERE WeekID = ".$weekID." GROUP BY UserID) as p ON u.UserID LIKE p.UserID
				WHERE Active = 1 AND IsAdmin = 0 
				ORDER BY Points DESC, LastName ASC";
		
		$usuarios = $this->runQueryToArray($sql);
		
		if(count($usuarios) < 1){
			return array(
				'success' => 'false',
				'error' => 'No se pudo leer usuarios activos.'
			);
		}
		
		foreach($usuarios as &$user){
			if($userID == $user['UserID']){
				$sql = "SELECT 
							m.MatchID,
							IFNULL(ui.TeamID, '0') as Pick,
							IFNULL(ui.High, '-1') as High,
							IFNULL(t.Image, 'select.png') as PickImage,
							IFNULL(p.Pts, '-1') as Points,
							m.Time as GameTime,
							CASE WHEN ui.TeamID IS NOT NULL
								THEN 1
								ELSE 0
							END as IsCaptured,
							m.CaptureLimit as CaptureLimit
						FROM Matchs as m
						LEFT JOIN UserInputs as ui ON m.MatchID LIKE ui.MatchID AND ui.UserID = ".$user['UserID']."
						LEFT JOIN Teams as t ON ui.TeamID LIKE t.TeamID
						LEFT JOIN UserPtsByMatch as p ON m.MatchID LIKE p.MatchID AND p.UserID = ".$user['UserID']."
						WHERE m.WeekID = ".$weekID."
						ORDER BY m.Time ASC, m.MatchID ASC";
			}else{
				$sql = "SELECT 
							m.MatchID,
							IFNULL(IF(m.CaptureLimit < (NOW() - INTERVAL 1 HOUR), ui.TeamID, NULL), '0') as Pick,
							IFNULL(IF(m.CaptureLimit < (NOW() - INTERVAL 1 HOUR), ui.High, NULL), '-1') as High,
							IFNULL(IF(m.CaptureLimit < (NOW() - INTERVAL 1 HOUR), t.Image, NULL), 'select.png') as PickImage,
							IFNULL(p.Pts, '-1') as Points,
							CASE WHEN ui.TeamID IS NOT NULL
								THEN 1
								ELSE 0
							END as IsCaptured,
							m.Time as GameTime,
							m.CaptureLimit as CaptureLimit
						FROM Matchs as m
						LEFT JOIN UserInputs as ui ON ui.MatchID LIKE m.MatchID AND ui.UserID = ".$user['UserID']."
						LEFT JOIN Teams as t ON ui.TeamID LIKE t.TeamID
						LEFT JOIN UserPtsByMatch as p ON m.MatchID LIKE p.MatchID AND p.UserID = ".$user['UserID']."
						WHERE m.WeekID = ".$weekID."
						ORDER BY m.Time ASC, m.MatchID ASC";
			}
			$user['Picks'] = $this->runQueryToArray($sql);			
		}
		
		return $usuarios;
		
	}
	
	
	/**
	*
	*/
	public function saveNewWeek($args, $req){
		if(!$this->validateSession('admin'))
			return array(
				'error' => 'Permisos insuficientes.',
				'success' => false,
				'relogin' => true
			);
		
		$stmt = $this->conn->prepare("INSERT INTO Weeks (Number, Start, End, SeasonID) VALUES (?, ?, ?, ?)");
		$stmt->bind_param('issi', $req['number'], $req['start'], $req['end'], $req['season']);
		
		if ($stmt->execute()) {
			$newWeek = $this->getWeekData(array($stmt->insert_id), array());
			return array(
				'success' => true,
				'message' => 'Creada con exito',
				'newWeek' => $newWeek
			);
		} else {
			return array(
				'success' => false,
				'message' => $stmt->error
			);
		}		
	}
	
	/*
	*
	*/
	public function preProAPI(){
		if($this->endpoint == 'login'){
			return $this->processAPI();
		}
		
		if(!$this->validateSession()){
			header("HTTP/1.1 200 OK");
			return json_encode(
				array(
					'error' => 'Permisos insuficientes.',
					'success' => false,
					'relogin' => true
				)
			);
		}
		return $this->processAPI();
	}
	
	public function saveNewPass($args, $req){		
		$userID = $this->getUserID();
		$stmt = $this->conn->prepare("UPDATE Users SET Password = ? WHERE UserID = ? AND Password = ?");
		$stmt->bind_param('sis', $req['new'], $userID, $req['cur']);
		$stmt->execute();
		if($stmt->affected_rows){
			return array(
				'success' => true
			);
		}
		
		return array(
			'success' => false,
			'error' => $stmt->error
		);
	}
	
	/*
	*
	*/
	private function getUserID(){
		return (isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID'])) ? $_SESSION['user']['UserID'] : 0;
	}
	
	
	/*
	*
	*/
	public function login($args, $req){
		// if(isset($this->verb) && $this->verb == 'admin'){
			// return $this->loginAsAdmin($req);
		// }
		// return false;
		
		$user = (isset($req['user']) && !empty($req['user'])) ? $req['user'] : false;
		$pass = (isset($req['pass']) && !empty($req['pass'])) ? $req['pass'] : false;
		
		$user = $this->conn->real_escape_string($user);
		$pass = $this->conn->real_escape_string($pass);
		
		$sql = "SELECT UserID, Name, LastName, Email, IsAdmin 
				FROM Users 
				WHERE Email = '".$user."'
				AND Password = '".$pass."'
				AND Active = 1";
		$result = mysqli_query($this->conn, $sql);
		$row = mysqli_fetch_assoc($result);
		if($row['UserID']){
			$_SESSION['user'] = $row;
			return $row;
		}else{
			return false;
		}
	}	
}

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $res = new apiNFL($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $res->preProAPI();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}