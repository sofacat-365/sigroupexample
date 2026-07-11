<?php
$users="root";
$password="";
$host="localhost";
$db="sigroup";
$dbh='mysql:host='.$host.';dbname='.$db.';charset=utf8';
$pdo=new PDO($dbh,$users,$password);
?>