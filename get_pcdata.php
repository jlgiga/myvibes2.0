<?php

pg_connect("host=localhost port=5432 dbname=myvibes user=myvibes password=tseree");

$title = '';
$artist = '';
$album = '';
$year = '';
$genre = '';
$rank = '';

if(isset($_GET['id']) && isset($_GET['type'])){
	$sid = (int) $_GET['id'];
	$type = $_GET['type'];
	if($type == 'user'){
		if(isset($_GET['u'])){
			$uname = $_GET['u'];
			$usql = pg_query("SELECT userprof.userid FROM userprof 
					WHERE userprof.username = '$uname'");
			while($u_row = pg_fetch_object($usql)){
				$uid = (int) $u_row->userid;			
			}
			$sql = pg_query("SELECT songs.*, SUM(link)/2 AS links
					FROM 
						(SELECT songs.sid AS songid, edges.linked AS link 
							FROM songs, edges
							WHERE edges.userid = $uid
							AND edges.parentid = $sid
							AND songs.sid = edges.parentid
							
						UNION ALL
						
						SELECT songs.sid AS songid, edges.linked AS link
							FROM songs, edges
							WHERE edges.userid = $uid
							AND edges.childid = $sid
							AND songs.sid = edges.childid
						) AS X, songs
					WHERE songs.sid = $sid
					GROUP BY songs.sid");
		}
	}
	elseif($type == 'friends'){
		if(isset($_GET['u'])){	
			$uname = $_GET['u'];
			$idq = pg_query("SELECT userid FROM userprof WHERE username = '$uname'");
				while($idqr = pg_fetch_object($idq)){
					$myid = $idqr->userid;
				}

				$users = array();
				$users[0] = $myid;

				$friends = pg_query("SELECT userprof.userid AS userid FROM userprof, friends 
					WHERE friends.userid = '$myid'
					AND userprof.username = friends.username");
							
				while($f_row = pg_fetch_array($friends)) {
					$users[] = $f_row['userid']; 		
				}

				$sql = pg_query("SELECT songs.*, SUM(link)/2 AS links
					FROM 
						(SELECT songs.sid AS songid, edges.linked AS link 
							FROM songs, edges
							WHERE edges.userid = ANY(ARRAY[" . implode(',', $users) . "]) 
							AND edges.parentid = $sid
							AND songs.sid = edges.parentid
							
						UNION ALL
						
						SELECT songs.sid AS songid, edges.linked AS link
							FROM songs, edges
							WHERE edges.userid = ANY(ARRAY[" . implode(',', $users) . "]) 
							AND edges.childid = $sid
							AND songs.sid = edges.childid
						) AS X, songs
					WHERE songs.sid = $sid
					GROUP BY songs.sid");
	
		}
	}
	elseif($type == 'global'){
		$sql = pg_query("SELECT songs.*, SUM(link)/2 AS links
					FROM 
						(SELECT songs.sid AS songid, edges.linked AS link 
							FROM songs, edges
							WHERE edges.parentid = $sid
							AND songs.sid = edges.parentid
							
						UNION ALL
						
						SELECT songs.sid AS songid, edges.linked AS link
							FROM songs, edges
							WHERE edges.childid = $sid
							AND songs.sid = edges.childid
						) AS X, songs
					WHERE songs.sid = songid
					GROUP BY songs.sid, x.songid");
	}
}


while($row = pg_fetch_object($sql)){
		$title = $row->title;
		$artist = $row->artist;
		$album = $row->album;
		$year = $row->year;
		$genre = $row->genre;
		$rank = $row->links;
}


if(strlen($album)==0 && strlen($year)>0 && strlen($genre)>0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Year:</b> ". utf8_decode($year) . "<br/><b>Genre:</b> " . utf8_decode($genre) . "<br/><b>Play Count:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)>0 && strlen($year)==0 && strlen($genre)>0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Album:</b> ". utf8_decode($album) . "<br/><b>Genre:</b> " . utf8_decode($genre) . "<br/><b>Play Count:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)>0 && strlen($year)>0 && strlen($genre)==0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Album:</b> ". utf8_decode($album) . "<br/><b>Year:</b> " . utf8_decode($year) . "<br/><b>Play Count:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)>0 && strlen($year)==0 && strlen($genre)==0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Album:</b> " . utf8_decode($album) . "<br/><b>Play Count:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)==0 && strlen($year)>0 && strlen($genre)==0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Year:</b> ". utf8_decode($year) . "<br/><b>Play Count:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)==0 && strlen($year)==0 && strlen($genre)>0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Genre:</b> " . utf8_decode($genre) . "<br/><b>Play Count:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)==0 && strlen($year)==0 && strlen($genre)==0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br><b>Play Count:</b> " . utf8_decode($rank) . "<br/>";
}
else{
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Album:</b> " . utf8_decode($album) . "<br/><b>Year:</b> ". utf8_decode($year) . "<br/><b>Genre:</b> " . utf8_decode($genre) . "<br/><b>Play Count:</b> " . utf8_decode($rank) . "<br/>";
}

?>
