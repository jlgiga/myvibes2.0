<?php

$conn = pg_connect("host=localhost port=5432 dbname=myvibes user=postgres password=postgres");

$mood = $_POST['moodTemp'];
$songid = $_POST['songidTemp'];
$userid = $_POST['useridTemp'];
$datetime = $_POST['datetimeTemp'];

$sql = "UPDATE last_played set mood = '$mood' WHERE userid = '$userid' AND sid = '$songid' AND date_time >= 
			to_timestamp('$datetime')::timestamp without time zone";
pg_query($conn, $sql);

$totalCount = 0;

$getHappyMoodQuery = "SELECT s.title, l.mood, COUNT(*) 
					  FROM songs AS s JOIN last_played AS l on l.sid = s.sid 
					  WHERE l.sid = '$songid' AND l.mood = 'happy'
					  GROUP BY s.title, l.mood";
$happyMoodResult = pg_query($conn, $getHappyMoodQuery);
while($row = pg_fetch_array($happyMoodResult)){
    $totalCount += $row['count'];
    $happyCount = $row['count'];
}

$getSadMoodQuery = "SELECT s.title, l.mood, COUNT(*) 
					FROM songs AS s JOIN last_played AS l on l.sid = s.sid 
					WHERE l.sid = '$songid' AND l.mood = 'sad'
					GROUP BY s.title, l.mood";
$sadMoodResult = pg_num_rows(pg_query($conn, $getSadMoodQuery));
while($row = pg_fetch_array($sadMoodResult)){
    $totalCount += $row['count'];
    $sadCount = $row['count'];
}
$getSurprisedAfraidMoodQuery = "SELECT s.title, l.mood, COUNT(*) 
							  	FROM songs AS s JOIN last_played AS l on l.sid = s.sid 
							  	WHERE l.sid = '$songid' AND l.mood = 'surprisedAfraid'
							  	GROUP BY s.title, l.mood";
$surprisedAfraidMoodResult = pg_query($conn, $getSurprisedAfraidMoodQuery);
while($row = pg_fetch_array($surprisedAfraidMoodResult)){
    $totalCount += $row['count'];
    $fearPrisedCount = $row['count'];
}

$getAngryDisgustedMoodQuery = "SELECT s.title, l.mood, COUNT(*) 
					  		   FROM songs AS s JOIN last_played AS l on l.sid = s.sid 
					  		   WHERE l.sid = '$songid' AND l.mood = 'angryDisgusted'
					  		   GROUP BY s.title, l.mood";
$angryDisgustedResult = pg_query($conn, $getAngryDisgustedMoodQuery);
while($row = pg_fetch_array($angryDisgustedMoodResult)){
    $totalCount += $row['count'];
    $angstCount = $row['count'];
}

//echo "<td>". percentage($happyCount, $totalCount) . "%</td>";
//echo "<td>". percentage($sadCount, $totalCount) . "%</td>";
//echo "<td>". percentage($fearPrisedCount, $totalCount) . "%</td>";
//echo "<td>". percentage($angstCount, $totalCount) . "%</td>";

$arr = array();
$arr[0] = percentage($happyCount, $totalCount);
$arr[1] = percentage($sadCount, $totalCount);
$arr[2] = percentage($fearPrisedCount, $totalCount);
$arr[3] = percentage($angstCount, $totalCount);

echo json_encode($arr);

pg_close($conn);


function percentage($x, $y) {
	return intval(($x * 100) / $y);
}