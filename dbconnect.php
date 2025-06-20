<?php

$server="localhost";
$user="root";
$password="";
$dbase="greenlife";

$conn=mysqli_connect($server, $user, $password, $dbase );

if(!$conn){

	die("Connection failed: ".$mysqli_error());

}
//echo "Connected successfully";
?>
