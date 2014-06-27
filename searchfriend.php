<?php
include "includes/authenticate.inc.php";
include "includes/logindb.php";
error_reporting(0);	
?>

<!DOCTYPE html>
<html>

<link rel="shortcut icon" type="image/ico" href="img/logo.ico"/>
<meta charset="utf-8">
<title>My Vibes - Music Recommender System</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>

<script src="js/jquery.js"></script>
<style type="text/css">
 .paginate {
 	font-family: Arial, Helvetica, sans-serif;
 	font-size: .7em;
 }
 
 a.paginate {
 	border: 1px solid #000080;
 	padding: 2px 6px 2px 6px;
 	text-decoration: none;
 	color: #000080;
 }
 
 
 a.paginate:hover {
 	background-color: #000080;
 	color: #FF;
 	text-decoration: underline;
 }
 
 a.current {
 	border: 1px solid #000080;
 	font: bold .7em Arial,Helvetica,sans-serif;
 	padding: 2px 6px 2px 6px;
 	cursor: default;
 	background:#000080;
 	color: #FFF;
 	text-decoration: none;
 }
 
 span.inactive {
 	border: 1px solid #999;
 	font-family: Arial, Helvetica, sans-serif;
 	font-size: .7em;
 	padding: 2px 6px 2px 6px;
 	color: #999;
 	cursor: default;
 }
 
 table {
 	margin: 8px;
 }
 
 th {
 	font-family: Arial, Helvetica, sans-serif;
 	font-size: .7em;
 	background: #666;
 	color: #FFF;
 	padding: 2px 6px;
 	border-collapse: separate;
 	border: 1px solid #000;
 }
 
 td {
 	font-family: Arial, Helvetica, sans-serif;
 	font-size: .7em;
 	border: 1px solid #DDD;
 }
 </style>
<script>
 function hilite(elem)
 {
 	elem.style.background = '#FFC';
 }
 
 function lowlite(elem)
 {
 	elem.style.background = '';
 }
 </script>
</head>
<?php include "includes/css.inc.php"; ?>
<?php include "includes/header.inc.php"; ?>
<body>

	<div class="container">
		Type the name of a friend here: <br/><br/>
		
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>"> <input type="text" name="tb1" onchange="submit()"> 
		<!--<?php echo $_SESSION['username']; ?>-->
   </form> 
		<div class="results"></div>
		
<?php

 if(!empty($_REQUEST[tb1])) {
  include('class/paginator.class.php');

 
 $query = "SELECT COUNT (*) FROM profile WHERE firstname LIKE '%$_REQUEST[tb1]%' OR lastname LIKE '%$_REQUEST[tb1]%'";
 $num_rows = $conn->get_var($query);
 
 
				if($num_rows > 0)
				{
				echo 'There are "'.$num_rows.'" result/s available! <br/> <br/>';
				}
				else{
				echo 'No results for "'.$_REQUEST[tb1].'" <br/> <br/>';
				}
 
 $pages = new Paginator;
 $pages->items_total = $num_rows[0];
 $pages->mid_range = 9; // Number of pages to display. Must be odd and > 3
 $pages->paginate();
 
 echo $pages->display_pages();
 echo "<span class=\"\">".$pages->display_jump_menu().$pages->display_items_per_page()."</span>";
 
 $query = "SELECT username,firstname,lastname FROM profile WHERE firstname LIKE '%$_REQUEST[tb1]%' OR lastname LIKE '%$_REQUEST[tb1]%' ORDER BY userid ASC $pages->limit";
 $data = $conn->get_results($query, "o");
 $debug = 0;
 
 if ($debug){
 var_dump($data);
 }

 
 /*for($i=0; $i<count($data); $i++){
	$row = $data[$i];
	echo $row['firstname']."<br>"; //array[]
 } */
 
 

/*$n=0; 
 while($n<count($data)){
 
	$row = $data[$n];
	echo $row['firstname']."<br>"; //array[]
	$n++;
 }*/
 
  
 echo "<table>";
 echo "<tr><th>UserName</th><th>FirstName</th><th>LastName</th></tr>";
 foreach($data as $res)
 {
 	echo "<tr onmouseover=\"hilite(this)\" onmouseout=\"lowlite(this)\">
			<td><a href='add-friend.php?username=".$res->username."'>".$res->username."</a></td> 
			<td>".$res->firstname."</td> 
			<td>".$res->lastname."</td>
		</tr>";
 }
 echo "</table>"; 
 
 echo $pages->display_pages();
 echo "<p class=\"paginate\">Page: $pages->current_page of $pages->num_pages</p>\n";
 echo "<p class=\"paginate\">SELECT * FROM table $pages->limit (retrieve records $pages->low-$pages->high from table - $pages->items_total item total / $pages->items_per_page items per page)";
 } ?>
	<div class="row"><div class="span12"><div class="footerInverse"><?php include "includes/footer.inc.php"; ?></div></div></div>
</div>
<!-- /container -->
<!-- jQuery latest version since Bootstrap is dependent on it -->
<script src="js/jquery-latest.js"></script>
<!-- Bootstrap JS file (it containes predefined functionalities. Read the manual online on how to use) -->
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/twitter-bootstrap-hover-dropdown.js"></script>
</body>
</html>