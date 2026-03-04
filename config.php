<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "mustso_election";

$conn = new mysqli($host,$user,$password,$database);

if($conn->connect_error){
    die("Database Connection Failed");
}

?>