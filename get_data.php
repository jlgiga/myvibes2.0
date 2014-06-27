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
			$sql = pg_query("SELECT songs.*, user_pr.pagerank 
					FROM songs, user_pr 
					WHERE user_pr.userid = $uid
					AND user_pr.sid = $sid
					AND songs.sid = user_pr.sid");
		}
	}
	elseif($type == 'friends'){
		if(isset($_GET['u'])){	
			$uname = $_GET['u'];
			$sql = pg_query("SELECT songs.*, friends_pr.pagerank 
					FROM songs,friends_pr 
					WHERE friends_pr.userid = (SELECT userprof.userid FROM userprof 
								WHERE userprof.username = '$uname') 
					AND songs.sid = $sid 
					AND friends_pr.sid = songs.sid");
		}
	}
	elseif($type == 'global'){
		$sql = pg_query("SELECT songs.*, global_pr.pagerank 
				FROM songs, global_pr 
				WHERE songs.sid = $sid 
				AND global_pr.sid = songs.sid");
	}
}


while($row = pg_fetch_object($sql)){
		$title = $row->title;
		$artist = $row->artist;
		$album = $row->album;
		$year = $row->year;
		$genre = $row->genre;
		$rank = $row->pagerank;
}


if(strlen($album)==0 && strlen($year)>0 && strlen($genre)>0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Year:</b> ". utf8_decode($year) . "<br/><b>Genre:</b> " . utf8_decode($genre) . "<br/><b>PageRank:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)>0 && strlen($year)==0 && strlen($genre)>0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Album:</b> ". utf8_decode($album) . "<br/><b>Genre:</b> " . utf8_decode($genre) . "<br/><b>PageRank:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)>0 && strlen($year)>0 && strlen($genre)==0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Album:</b> ". utf8_decode($album) . "<br/><b>Year:</b> " . utf8_decode($year) . "<br/><b>PageRank:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)>0 && strlen($year)==0 && strlen($genre)==0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Album:</b> " . utf8_decode($album) . "<br/><b>PageRank:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)==0 && strlen($year)>0 && strlen($genre)==0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Year:</b> ". utf8_decode($year) . "<br/><b>PageRank:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)==0 && strlen($year)==0 && strlen($genre)>0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Genre:</b> " . utf8_decode($genre) . "<br/><b>PageRank:</b> " . utf8_decode($rank) . "<br/>";
}
elseif(strlen($album)==0 && strlen($year)==0 && strlen($genre)==0){
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br><b>PageRank:</b> " . utf8_decode($rank) . "<br/>";
}
else{
echo "<b>Title:</b> " . utf8_decode($title) . "<br/><b>Artist:</b> ". utf8_decode($artist) . "<br/><b>Album:</b> " . utf8_decode($album) . "<br/><b>Year:</b> ". utf8_decode($year) . "<br/><b>Genre:</b> " . utf8_decode($genre) . "<br/><b>PageRank:</b> " . utf8_decode($rank) . "<br/>";
}

?>
