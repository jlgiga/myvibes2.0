<?php

pg_connect("host=localhost port=5432 dbname=myvibes user=myvibes password=tseree");

function insertToUsrPR($uid, $array){
	echo "From top songs " . $uid . " " . gettype($uid) ."\n";
	$size = sizeof($array);
	$checkUser = pg_query("SELECT DISTINCT userid FROM user_pr WHERE userid = $uid");
	if(pg_num_rows($checkUser) > 0) {
		echo "Deleting previous ranking...\n";
		pg_query("DELETE FROM user_pr WHERE userid = $uid");
		
	}
	echo "Inserting new ranking...\n";
	for($i = 1; $i < $size + 1; $i++) {
		$value = $array[$i-1];
		pg_query("INSERT INTO user_pr (userid,sid,pagerank) VALUES ($uid,$i,'$value')");
	}
}

function insertToGrpPR($uid, $array){
	$size = sizeof($array);
	$checkUser = pg_query("SELECT DISTINCT userid FROM friends_pr WHERE userid = $uid");
	if(pg_num_rows($checkUser) > 0) {
		echo "Deleting previous ranking...\n";
		pg_query("DELETE FROM friends_pr WHERE userid = $uid");
		
	}
	echo "Inserting new ranking...\n";
	for($i = 1; $i < $size + 1; $i++) {
		$value = $array[$i-1];
		pg_query("INSERT INTO friends_pr (userid,sid,pagerank) VALUES ($uid,$i,'$value')");
	}
}

function insertToGlbPR($array){
	$size = sizeof($array);
	pg_query("DELETE FROM global_pr");
	for($i = 1; $i < $size + 1; $i++){
		$value = $array[$i-1];
		pg_query("INSERT INTO global_pr (sid,pagerank) VALUES ($i,'$value')");
	}
}

function insertToGlbMoodPR($array, $mood){
	$size = sizeof($array);
	pg_query("DELETE FROM globalmood_pr WHERE mood = '$mood'");
	for($i = 1; $i < $size + 1; $i++){
		$value = $array[$i-1];
		pg_query("INSERT INTO globalmood_pr (sid,pagerank,mood) VALUES ($i,'$value','$mood')");
	}
}

?>
