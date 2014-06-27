<?php
include "includes/loginreq.php";
include "includes/sql_statb.php";
include "includes/functions.php";
include('class/paginator.class.php');

error_reporting(0);

$conn = new pgsql_db();
$uname = $_GET['username'];
	
$qry_var = "SELECT COUNT (*) FROM songs AS s JOIN last_played AS l on l.sid = s.sid WHERE l.userid = (SELECT userid FROM profile WHERE username = '$uname')";
$num_rows = $conn->get_var($qry_var);
//var_dump($num_rows);

	$pages = new Paginator;
	$pages->items_total = $num_rows[0];
	$pages->mid_range = 9; // Number of pages to display. Must be odd and > 3
	$pages->paginate();

$q = "SELECT s.*, l.date_time FROM songs AS s JOIN last_played AS l on l.sid = s.sid WHERE l.userid = (SELECT userid FROM profile WHERE username = '$uname') ORDER BY l.date_time DESC";
$query = pg_query($q);
$ry = $conn->get_results($q);



if($num_rows == 0){
	$timeline = "You haven't started listening to songs yet.";
}
else{
	$timeline="";
	while($row=pg_fetch_assoc($query)){
		$row2 = "is listening to ".$row['title']." by ".$row['artist']." ";
		$timeline.=formatTweet($row2,$row['date_time'],$uname);
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
	$row7="";
	while($qry = pg_fetch_array($sql4)){
		if($qry['dense_rank'] <= 10){			
			$row7 .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . $qry['sid'] . "&u=" . $uname . "&type=user' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
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
});

</script>
<link rel="stylesheet" type="text/css" href="css/demo2.css" />
<link type="text/css" rel="stylesheet" href="css/jquery.ratings.css" />
<script type="text/javascript" src="js/script2.js"></script>
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
					<?php include "includes/add-friend-prof.inc.php"; ?>
				</div>
			</div>
			<!--Tabs-->
			<div class="span7">
			<ul class="nav nav-tabs" id="myTab2">
				<li><a href="#tracks">Tracks</a></li>
				<li><a href="#top10">My Top Hits</a></li>
			</ul>
			<div class="tab-content form-tracks background1">
				<!-- Tracks -->	
				<div class="tab-pane" id="tracks"> 
			
				<?php include "includes/tracks.inc.php"; ?>
				
				</div>
				<!-- Top10 -->	
				<div class="tab-pane" id="top10"> 
				
					<?php include "includes/top10.inc.php"; ?>
				
				</div>
			</div>

			</div>
		</div>
	<div class="row"><div class="span12"><div class="footerInverse"><?php include "includes/footer-index.inc.php"; ?></div></div></div>
</div>
</div>
</body>
</html>
