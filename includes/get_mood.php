<?php
/*include "class/pgsql_db.class.php";

$conn = new pgsql_db();*/

$host        = "host=127.0.0.1";
$port        = "port=5432";
$dbname      = "dbname=myvibes";
$credentials = "user=postgres password=postgres";

$db = pg_connect( "$host $port $dbname $credentials"  );
if(!$db){
	echo "Error : Unable to open database\n";
} else {
	echo "Opened database successfully\n";
}

$mood = $_POST['moodTemp'];
$songid = $_POST['songidTemp'];
$userid = $_POST['useridTemp'];
$datetime = $_POST['datetimeTemp'];

/*2014-06-24 18:23:28.65
2014-06-24 18:22:30.508
2014-06-24 04:43:22.374
2014-06-24 04:43:22.374*/

//$datetime = date("Y-m-d H:i:s",$datetime);

//$query = "UPDATE last_played set mood = to_timestamp('$datetime')::timestamp without time zone WHERE userid = '$userid' AND sid = '$songid'";
//$query = "UPDATE last_played set mood = '$mood' WHERE userid = '$userid' AND sid = '$songid' AND DATEADD(ms, -DATEPART(ms, date_time), date_time) = to_timestamp('$datetime')::timestamp without time zone";
//$query = "UPDATE last_played set mood = '$mood' WHERE userid = '$userid' AND sid = '$songid' AND date_trunc('milliseconds', datetime) = to_timestamp('$datetime')::timestamp without time zone";
$query = "UPDATE last_played set mood = '$mood' WHERE userid = '$userid' AND sid = '$songid' AND datetime >= 
			to_timestamp('$datetime')::timestamp without time zone";

$ret = pg_query($db,$query);
//$ret = $conn->query($sql);

if(!$ret){
	echo pg_last_error($db);
} else {
    echo "Records created successfully\n";
}
pg_close($db);
//pg_close($conn);
