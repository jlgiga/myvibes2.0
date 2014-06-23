<?php

/*
start of execution time
*/

$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime;


include "/home/myvibes/myvibes/PageRank/to_page_rank.php";
include "/home/myvibes/myvibes/PageRank/top_songs.php";

function pagerank($uid) {
	$damping_factor = 0.85;
	$matrix = array();
	$matrix = adjacencyMatrix($uid);
	$row_stochastic_matrix = rowStochastic($damping_factor, $matrix);
	$transposed_matrix = transpose($row_stochastic_matrix);
	$eigen_vector = eigenVector($transposed_matrix);
	$normalized_eigenvector = normalizeVector($eigen_vector);
	insertToUsrPR($uid, $normalized_eigenvector);
}

function pagerankGrp($uid) {
	$damping_factor = 0.85;
	$matrix = array();
	$matrix = adjacencyMatrixGrp($uid);
	$row_stochastic_matrix = rowStochastic($damping_factor, $matrix);
	$transposed_matrix = transpose($row_stochastic_matrix);
	$eigen_vector = eigenVector($transposed_matrix);
	$normalized_eigenvector = normalizeVector($eigen_vector);
	insertToGrpPR($uid, $normalized_eigenvector);
}

function pagerankGlb() {
	$damping_factor = 0.85;
	$matrix = array();
	$matrix = adjacencyMatrixGlb();
	if(sizeof($matrix) > 0){
		$row_stochastic_matrix = rowStochastic($damping_factor, $matrix);
		$transposed_matrix = transpose($row_stochastic_matrix);
		$eigen_vector = eigenVector($transposed_matrix);
		$normalized_eigenvector = normalizeVector($eigen_vector);
		insertToGlbPR($normalized_eigenvector);
	}else{
		exit();	
	}
}

$users = array();
$users = users();
$usersGrp = array();
$usersGrp = usersGrp();

echo "MyVibes PR Engine\nStarting MyVibes PR Engine...\nMyVibes PR Engine started...\n";

/*
foreach($users as &$uid){
	$utime = microtime(); 
	$utime = explode(" ",$utime); 
	$utime = $utime[1] + $utime[0]; 
	$ustarttime = $utime;
	echo "Computing song ranking for " . $uid . " " . gettype($uid) ."...\n";
	pagerank($uid);
	$utime = microtime(); 
 	$utime = explode(" ",$utime); 
 	$utime = $utime[1] + $utime[0]; 
 	$uendtime = $utime; 
 	$utotaltime = ($uendtime - $ustarttime); 
 	echo "Computation took ".$utotaltime." seconds\n";
}
foreach($usersGrp as &$uid){
	$utime = microtime(); 
	$utime = explode(" ",$utime); 
	$utime = $utime[1] + $utime[0]; 
	$ustarttime = $utime;
	echo "Computing song ranking for " . $uid . " " . gettype($uid) ."...\n";
	pagerankGrp($uid);
	$utime = microtime(); 
 	$utime = explode(" ",$utime); 
 	$utime = $utime[1] + $utime[0]; 
 	$uendtime = $utime; 
 	$utotaltime = ($uendtime - $ustarttime); 
 	echo "Computation took ".$utotaltime." seconds\n";
}

*/
pagerankGlb();

// end of execution

 $mtime = microtime(); 
 $mtime = explode(" ",$mtime); 
 $mtime = $mtime[1] + $mtime[0]; 
 $endtime = $mtime; 
 $totaltime = ($endtime - $starttime); 
 echo "Computations took ".$totaltime." seconds\n";
?>
