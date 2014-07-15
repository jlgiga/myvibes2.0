<?php

include "includes/functions.php";
include "includes/loginreq.php";
include "class/pgsql_db.class.php";
include "includes/sql_stata.php";
include('class/paginator.class.php');

// mood

$queryGlHappyMoodPr = pg_query("SELECT songs.sid, songs.title, songs.artist, globalmood_pr.pagerank, DENSE_RANK() OVER(ORDER BY globalmood_pr.pagerank DESC) dense_rank 
		FROM songs, globalmood_pr 
		WHERE songs.sid = globalmood_pr.sid and globalmood_pr.mood = 'happy'");

if(pg_num_rows($queryGlHappyMoodPr) == 0){
	 $glHappyMoodPr = "No Top 10 Tracks Determined Yet.";
}
else{
	$glHappyMoodPr="";
	while($qry = pg_fetch_array($queryGlHappyMoodPr)){
		if($qry['dense_rank'] <= 10){				
			$glHappyMoodPr .= "<br/>" . preg_replace("/((?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i","<a href='$1' rel='nofollow' target='blank'>$1</a>", $qry['dense_rank'] . ". <b><a href='get_data.php?id=" . $qry['sid'] . "&type=global' onclick='return false' style='text-decoration: none' class='sample'>" . $qry['title'] . "</a></b>");
		}
	}	
}

$queryGlSadMoodPr = pg_query("SELECT songs.sid, songs.title, songs.artist, globalmood_pr.pagerank, DENSE_RANK() OVER(ORDER BY globalmood_pr.pagerank DESC) dense_rank 
		FROM songs, globalmood_pr 
		WHERE songs.sid = globalmood_pr.sid and globalmood_pr.mood = 'sad'");

if(pg_num_rows($queryGlSadMoodPr) == 0){
	 $glSadMoodPr = "No Top 10 Tracks Determined Yet.";
}
else{
	$glSadMoodPr="";
	while($qry = pg_fetch_array($queryGlSadMoodPr)){
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
	while($qry = pg_fetch_array($queryGlFearPrisedMoodPr)){
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
	while($qry = pg_fetch_array($queryGlAngstMoodPr)){
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
});

</script>
<style>
#hor-minimalist-a
{
	font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
	font-size: 12px;
	background: #fff;
	margin: 45px;
	width: 480px;
	border-collapse: collapse;
	text-align: left;
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
<body>
<?php echo $glHappyMoodPr;?>

</body>
</html>
