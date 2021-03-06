<?php

include "includes/functions.php";
include "includes/loginreq.php";
include "class/pgsql_db.class.php";
include "includes/sql_stata.php";
include('class/paginator.class.php');

$conn = new pgsql_db();
$uname = $_SESSION['username'];
	
$qry_var = "SELECT COUNT (*) FROM songs AS s 
	JOIN last_played AS l on l.sid = s.sid 
	WHERE l.userid = (SELECT userid FROM profile WHERE username = '$uname')";
$num_rows = $conn->get_var($qry_var);

$pages = new Paginator;
$pages->items_total = $num_rows[0];
$pages->mid_range = 9; // Number of pages to display. Must be odd and > 3
$pages->paginate();

$counter = 1;
$q = "SELECT s.*, l.* FROM songs AS s JOIN last_played AS l on l.sid = s.sid WHERE l.userid = (SELECT userid FROM profile WHERE username = '$uname') ORDER BY l.date_time DESC";
$query = pg_query($q);

//$cTime = date('H:i:s');			// returns something like '06:14:06'
if (time() >= strtotime('06:00:00') && time() <= strtotime('11:59:00')) {
	//$sample = "SELECT s.title FROM songs AS s JOIN last_played AS l on l.sid = s.sid WHERE to_char(date_time, 'HH24:MI:SS') >= '06:00:00' AND to_char(date_time, 'HH24:MI:SS') <= '11:59:00'";
	$timeOfDayQuery = "SELECT s.title, count(*) FROM songs AS s JOIN last_played AS l on l.sid = s.sid 
					   WHERE to_char(date_time, 'HH24:MI:SS') BETWEEN '06:00:00' AND '11:59:00' 
					   GROUP BY title ORDER BY COUNT(*) DESC LIMIT 10";
} else if (time() >= strtotime('12:00:00') && time() <= strtotime('17:59:00')) {
	//$sample = "SELECT s.title FROM songs AS s JOIN last_played AS l on l.sid = s.sid WHERE to_char(date_time, 'HH24:MI:SS') >= '12:00:00' AND to_char(date_time, 'HH24:MI:SS') <= '17:59:00'";
	$timeOfDayQuery = "SELECT s.title, count(*) FROM songs AS s JOIN last_played AS l on l.sid = s.sid 
					   WHERE to_char(date_time, 'HH24:MI:SS') BETWEEN '12:00:00' AND '17:59:00' 
					   GROUP BY title ORDER BY COUNT(*) DESC LIMIT 10";
} else {
	//$sample = "SELECT s.title FROM songs AS s JOIN last_played AS l on l.sid = s.sid WHERE to_char(date_time, 'HH24:MI:SS') >= '18:00:00' AND to_char(date_time, 'HH24:MI:SS') >= '05:59:00'";
	$timeOfDayQuery = "SELECT s.title, count(*) FROM songs AS s JOIN last_played AS l on l.sid = s.sid 
					   WHERE to_char(date_time, 'HH24:MI:SS') BETWEEN '18:00:00' AND '05:59:00' 
					   GROUP BY title ORDER BY COUNT(*) DESC LIMIT 10";
}

//$timeOfDayResult = $conn->get_results($timeOfDayQuery);
$timeOfDayResult = pg_query($timeOfDayQuery);

if($num_rows == 0){
	$timeline = "You haven't started listening to songs yet.";
}
else{
	$timeline = "";
	while($row=pg_fetch_assoc($query)){
		$row2 = "is listening to ".$row['title']." by ".$row['artist']." ";
		$row3 = "are listening to ".$row['title']." by ".$row['artist']." ";
		if ($uname) {
			$timeline .= formatTweet($row3, $row['date_time'], "you", $counter, $row['mood']);
			$timeline .= '
			    <div class="mood" id="target_'. $counter .'" style="width:670px;height:150px;background-color:white;">
					<br/>
					<table id="hor-minimalist-a">
					<tbody>
					<tr>
					<td><img src="img/emoticon/happy.png" width="50" height="20" id="happy" title="happy" alt="happy" onclick="getMood(this.id, ' .$row['sid']. ', '.$row['userid'].', '.strtotime($row['date_time']).', '. $counter .')" /></td>
					<td><img src="img/emoticon/sad.png" width="50" height="20" id="sad" title="sad" alt="sad" onclick="getMood(this.id, ' .$row['sid']. ', '.$row['userid'].', '.strtotime($row['date_time']).')" /></td>
					<td><img src="img/emoticon/surprised.png" width="50" height="20" id="surprisedAfraid" title="surprised/afraid" alt="surprised" onclick="getMood(this.id, ' .$row['sid']. ', '.$row['userid'].', '.strtotime($row['date_time']).')" /></td>
					<td><img src="img/emoticon/angry.png" width="50" height="20" id="angry/disgusted" title="angryDisgusted" alt="angry" onclick="getMood(this.id, ' .$row['sid']. ', '.$row['userid'].', '.strtotime($row['date_time']).')" /></td>
					</tr>
					<tr id="moodPercent">
					<td><div class="per" id="perHappy"></div></td>
					<td><div class="per" id="perSad"></div></td>
					<td><div class="per" id="perFearPrised"></div></td>
					<td><div class="per" id="perAngst"></div></td>
					</tr>
					</tbody>
					</table>
					<br/>
				</div>';
		} else {
			$timeline .= formatTweet($row2,$row['date_time'],$uname,$row['mood']);
		}
		$counter = $counter + 1;
	}
}

$sql4 =	pg_query("SELECT songs.sid, songs.title, user_pr.pagerank, DENSE_RANK() OVER(ORDER BY user_pr.pagerank DESC) dense_rank 
		FROM songs, user_pr 
		WHERE user_pr.sid IN (SELECT esid.id 
				FROM (SELECT edges.parentid as id 
					FROM edges WHERE edges.userid = (SELECT userprof.userid FROM userprof 										WHERE userprof.username = '$uname') 
					UNION 
					SELECT edges.childid as id 
					FROM edges WHERE edges.userid = (SELECT userprof.userid FROM userprof 										WHERE userprof.username = '$uname')
					) AS esid) 
		AND songs.sid = user_pr.sid
		AND user_pr.userid = (SELECT userprof.userid FROM userprof WHERE userprof.username = '$uname')");

if(pg_num_rows($sql4) == 0){
	$row7 = "No Top 10 Tracks Determined Yet.";
}
else{
	$row7 = "";
	while($qry = pg_fetch_array($sql4)){
		if($qry['dense_rank'] <= 10){			
			$row7 .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . (int) $qry['sid'] . "&u=" . $uname . "&type=user' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
		}
	}
}	
 
 
$users = array();

$friends = pg_query("SELECT users.user FROM (SELECT userid AS user FROM userprof 						WHERE username = '$uname'
					UNION
					SELECT userprof.userid AS user FROM userprof, friends 
					WHERE friends.userid = (SELECT userid FROM userprof 									WHERE username = '$uname')
					AND userprof.username = friends.username
					) AS users");

while($f_row = pg_fetch_array($friends)) {
	$users[] = $f_row['user']; 		
}

$sql5 = pg_query("SELECT songs.sid, songs.title, friends_pr.pagerank, DENSE_RANK() OVER(ORDER BY friends_pr.pagerank DESC) dense_rank 
		FROM songs, friends_pr 
		WHERE friends_pr.sid IN (SELECT esid.id 
				FROM (SELECT edges.parentid as id 
					FROM edges WHERE edges.userid = ANY(ARRAY[" . implode(',', $users) . "]) 
					UNION 
					SELECT edges.childid as id 
					FROM edges WHERE edges.userid = ANY(ARRAY[" . implode(',', $users) . "])
					) AS esid) 
		AND songs.sid = friends_pr.sid
		AND friends_pr.userid = (SELECT userprof.userid FROM userprof WHERE userprof.username ='$uname')");

if(pg_num_rows($sql5) == 0){
	 $row8 = "No Top 10 Tracks Determined Yet.";
}
else{	
	$row8="";
	while($qry = pg_fetch_array($sql5)){
		if($qry['dense_rank'] <= 10){			
			$row8 .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . (int) $qry['sid'] . "&u=" . $uname . "&type=friends' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
		}
	}	
} 
 
$sql6 = pg_query("SELECT songs.sid, songs.title, songs.artist, global_pr.pagerank, DENSE_RANK() OVER(ORDER BY global_pr.pagerank DESC) dense_rank 
		FROM songs,global_pr 
		WHERE songs.sid = global_pr.sid");

if(pg_num_rows($sql6) == 0){
	 $row9 = "No Top 10 Tracks Determined Yet.";
}
else{
	$row9="";
	while($qry = pg_fetch_array($sql6)){
		if($qry['dense_rank'] <= 10){				
			$row9 .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . $qry['sid'] . "&type=global' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
		}
	}	
}
 
$pc_query = pg_query("SELECT songs.sid, songs.artist, songs.title, SUM(link)/2 AS links, DENSE_RANK() OVER(ORDER BY SUM(link)/2 DESC) rank_dense
					FROM 
						(SELECT songs.sid AS songid, edges.linked AS link 
							FROM songs, edges
							WHERE edges.userid = (SELECT userprof.userid FROM userprof WHERE userprof.username = '$uname')
							AND songs.sid = edges.parentid
							
						UNION ALL
						
						SELECT songs.sid AS songid, edges.linked AS link
							FROM songs, edges
							WHERE edges.userid = (SELECT userprof.userid FROM userprof WHERE userprof.username = '$uname')
							AND songs.sid = edges.childid
						) AS X, songs
					WHERE songs.sid = songid
					GROUP BY songs.sid, x.songid");
	
if(pg_num_rows($pc_query) == 0){
	$row11 = "No Top 10 Tracks Determined Yet.";
}
else{
	$row11 = "";
	while($qry = pg_fetch_array($pc_query)){
		if($qry['rank_dense'] <= 10){				
			$row11 .= " <br/>" . $qry['rank_dense'] . ". <b>" . $qry['title'] . "</b><br/>[" . $qry['links'] . "]";
		}
	}	
}
 
// global

$pc_query2 = pg_query("SELECT songs.sid, songs.artist, songs.title, SUM(link)/2 AS links, DENSE_RANK() OVER(ORDER BY SUM(link)/2 DESC) rank_dense
					FROM 
						(SELECT songs.sid AS songid, edges.linked AS link 
							FROM songs, edges
							WHERE songs.sid = edges.parentid
							
						UNION ALL
						
						SELECT songs.sid AS songid, edges.linked AS link
							FROM songs, edges
							WHERE songs.sid = edges.childid
						) AS X, songs
					WHERE songs.sid = songid
					GROUP BY songs.sid, x.songid");

if(pg_num_rows($pc_query2) == 0){
	$row31 = "No Top 10 Tracks Determined Yet.";
}
else{
	$row31 = "";
	while($qry = pg_fetch_array($pc_query2)){
		if($qry['rank_dense'] <= 10){				
			$row31 .= " <br/>" . $qry['rank_dense'] . ". <b>" . $qry['title'] . "</b><br/>[" . $qry['links'] . "]";
		}
	}	
}
 
// friends

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
$userStr = implode("', '", $users);
$pc_query3 = pg_query("SELECT songs.sid, songs.artist, songs.title, SUM(link)/2 AS links, DENSE_RANK() OVER(ORDER BY SUM(link)/2 DESC) rank_dense
					FROM 
						(SELECT songs.sid AS songid, edges.linked AS link 
							FROM songs, edges
							WHERE edges.userid = (ARRAY['$userStr']) 
							AND songs.sid = edges.parentid
							
						UNION ALL
						
						SELECT songs.sid AS songid, edges.linked AS link
							FROM songs, edges
							WHERE edges.userid = ANY(ARRAY['$userStr']) 
							AND songs.sid = edges.childid
						) AS X, songs
					WHERE songs.sid = songid
					GROUP BY songs.sid, x.songid");
	
if(pg_num_rows($pc_query3) == 0){
 	$row32 = "No Top 10 Tracks Determined Yet.";
}
else{	
	$row32 = "";
	while($qry = pg_fetch_array($pc_query3)){		
		if($qry['rank_dense'] <= 10){				
			$row32 .= " <br/>" . $qry['rank_dense'] . ". <b>" . $qry['title'] . "</b><br/>[" . $qry['links'] . "]";
		}
	}	
}

/*SELECT songs.sid AS songid, edges.linked AS link
							FROM songs, edges
							WHERE edges.userid = ANY(ARRAY[" . implode(',', $users) . "])*/

// mood

$queryGlHappyMoodPr = pg_query("SELECT songs.sid, songs.title, songs.artist, globalmood_pr.pagerank, DENSE_RANK() OVER(ORDER BY globalmood_pr.pagerank DESC) dense_rank 
		FROM songs, globalmood_pr 
		WHERE songs.sid = globalmood_pr.sid and globalmood_pr.mood = 'happy'");

if(pg_num_rows($queryGlHappyMoodPr) == 0){
	 $glHappyMoodPr = "No Top 10 Tracks Determined Yet.";
}
else{
	$glHappyMoodPr="";
	while($qry = pg_fetch_array($glHappyMoodPr)){
		if($qry['dense_rank'] <= 10){				
			$glHappyMoodPr .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . $qry['sid'] . "&type=global' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
		}
	}	
}

$queryGlHappyMoodPr = pg_query("SELECT songs.sid, songs.title, songs.artist, globalmood_pr.pagerank, DENSE_RANK() OVER(ORDER BY globalmood_pr.pagerank DESC) dense_rank 
		FROM songs, globalmood_pr 
		WHERE songs.sid = globalmood_pr.sid and globalmood_pr.mood = 'sad'");

if(pg_num_rows($queryGlSadMoodPr) == 0){
	 $glSadMoodPr = "No Top 10 Tracks Determined Yet.";
}
else{
	$glSadMoodPr="";
	while($qry = pg_fetch_array($glSadMoodPr)){
		if($qry['dense_rank'] <= 10){				
			$glSadMoodPr .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . $qry['sid'] . "&type=global' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
		}
	}	
}

$queryGlFearPrisedMoodPr = pg_query("SELECT songs.sid, songs.title, songs.artist, globalmood_pr.pagerank, DENSE_RANK() OVER(ORDER BY globalmood_pr.pagerank DESC) dense_rank 
		FROM songs, globalmood_pr 
		WHERE songs.sid = globalmood_pr.sid and globalmood_pr.mood = 'surprisedAfraid'");

if(pg_num_rows($queryGlFearPrisedMoodPr) == 0){
	 $glFearPrisedMoodPr = "No Top 10 Tracks Determined Yet.";
}
else{
	$glFearPrisedMoodPr="";
	while($qry = pg_fetch_array($glFearPrisedMoodPr)){
		if($qry['dense_rank'] <= 10){				
			$glFearPrisedMoodPr .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . $qry['sid'] . "&type=global' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
		}
	}	
}

$queryGlHappyMoodPr = pg_query("SELECT songs.sid, songs.title, songs.artist, globalmood_pr.pagerank, DENSE_RANK() OVER(ORDER BY globalmood_pr.pagerank DESC) dense_rank 
		FROM songs, globalmood_pr 
		WHERE songs.sid = globalmood_pr.sid and globalmood_pr.mood = 'angryDisgusted'");

if(pg_num_rows($queryGlAngstMoodPr) == 0){
	 $glAngstMoodPr = "No Top 10 Tracks Determined Yet.";
}
else{
	$glAngstMoodPr="";
	while($qry = pg_fetch_array($glAngstMoodPr)){
		if($qry['dense_rank'] <= 10){				
			$glAngstMoodPr .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . $qry['sid'] . "&type=global' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
		}
	}	
}

?>

<!DOCTYPE html>
<html>

<link rel="shortcut icon" type="image/ico" href="img/logo.ico"/>
<meta charset="utf-8">
<title>MyVibes - Music Recommender System</title>
<head><script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/twitter-bootstrap-hover-dropdown.js"></script>
<script src="js/tabs.js"></script>
<script src="js/jquery.balloon.js"></script>
<script >
$(function () {
	$('.sample').each(function(index){
		var link = $(this);
		$.get(link.attr('href'),function(data){
			link.balloon({
				position: "right",
				contents: data
			});
		});
	});
	$('.mood').hide();
	$('#moodPercent').hide();
	$('button[data-target]').on('click', function(event){
        event.preventDefault();
        $('#target_' + $(this).attr('data-target')).fadeToggle("slow");
        $(this).attr("disabled", true);
    });
});

function getMood(mood, songid, userid, datetime, counter) {
	$.ajax({
	    type : 'POST',
	    url: 'includes/get_mood.php',
	    data: {
			moodTemp  : mood,
			songidTemp: songid,
			useridTemp: userid,
			datetimeTemp: datetime
		},
		dataType: "json"
	}).done(function(data) {
		//alert(data);
		$('#moodPercent').show();
		document.getElementById("perHappy").innerHTML = data[0] + "%";
        document.getElementById("perSad").innerHTML = data[1] + "%";
        document.getElementById("perFearPrised").innerHTML = data[2] + "%";
        document.getElementById("perAngst").innerHTML = data[3] + "%";
	}).fail(function() {
		$(waiting).hide(500);
		$(message).removeClass().addClass('error')
			.text('There was an error.').show(500);
	});
		
	return false;
}

</script>
<style>
#hor-minimalist-a
{
	font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
	font-size: 12px;
	background: #fff;
	margin: 10px;
	width: 480px;
	border-collapse: collapse;
	text-align: center;
}
#hor-minimalist-a th
{
	font-size: 14px;
	font-weight: normal;
	color: #039;
	padding: 10px 8px;
	border-bottom: 2px solid #6678b1;
}
#hor-minimalist-a td
{
	color: #669;
	padding: 9px 8px 0px 8px;
}
#hor-minimalist-a tbody tr:hover td
{
	color: #009;
}
</style>
<link rel="stylesheet" type="text/css" href="css/demo2.css" />
<script type="text/javascript" src="js/script2.js"></script>
<link type="text/css" rel="stylesheet" href="css/jquery.ratings.css" />
<script src="js/jquery.ratings.js"></script>
<script src="js/example.js"></script>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php include "includes/css.inc.php"; ?>
<?php include "includes/header-profile.inc.php"; ?>
<body>

	<div class="container">
	<div class="span12">
		<div class="row show-grid">
			<div class="span3 offset1">
			<!--Profile info-->
				<div class="row background1 profile-circle">
					<?php include "includes/profile.inc.php"; ?>
				</div>
			</div>
			<!--Tabs-->
			<div class="span7">
			<ul class="nav nav-tabs" id="myTab2">
				<li><a href="#tracks">Tracks</a></li>
				<li><a href="#top10">My Top Hits</a></li>
				<li><a href="#friends-hits">My Circle's Hits</a></li>
				<li><a href="#community-hits">Community Hits</a></li>
				<li><a href="#mood">Community Mood</a></li>
			</ul>
			<!--Contents-->
			<div class="tab-content form-tracks background1">
				<!-- Tracks -->	
				<div class="tab-pane" id="tracks"> 
					<?php include "includes/tracks.inc.php"; ?>
				</div>
				<!-- Top10 -->	
				<div class="tab-pane" id="top10"> 
					<?php include "includes/top10.inc.php"; ?>
				</div>
				<!--Friends Hits-->
				<div class="tab-pane" id="friends-hits">
					<?php include "includes/friends-hits.inc.php"; ?>
				</div>
				<!--Community Hits-->
				<div class="tab-pane" id="community-hits">
					<?php include "includes/community-hits.inc.php"; ?>
				</div>
				<!--Mood Recommendations-->
				<div class="tab-pane" id="mood-recommendations">
					<?php include "includes/mood-recommendation.inc.php"; ?>
				</div>
			</div>

			</div>
		</div>
	<div class="row"><div class="span12"><div class="footerInverse"><?php include "includes/footer-index.inc.php"; ?></div></div></div>
</div>
</div>
</body>
</html>
