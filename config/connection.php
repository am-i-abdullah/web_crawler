<?php
$hostname = 'localhost';
$uname = 'root';
$password = '';
$db_name = 'webcrawler';
$conn = mysqli_connect($hostname,$uname,$password,$db_name);
if(!$conn){
    echo "connection failed";
}

?>