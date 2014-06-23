<?php
include "class/pgsql_db.class.php";

$conn = new pgsql_db();

$mood = $_POST['moodTemp']
$songid = $_POST['songidTemp'];
$userid = $_POST['useridTemp'];

//$sql = "INSERT INTO last_played(mood) values('".$mood."') WHERE userid = '".$userid."' AND sid = '".$songid."'";
//$sql = "INSERT INTO last_played(mood) values('".$mood."') WHERE userid = 1 AND sid = 3";
$sql = "INSERT INTO last_played(userid,sid,date_time,last_child, mood) VALUES (1,4,NOW(),0, 'happy')";
$result = $conn->query($sql);

if(!$result){
	echo pg_last_error($conn);
} else {
    echo "Records created successfully\n";
}

pg_close($con);
?>