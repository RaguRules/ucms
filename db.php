<?php
$conn = mysqli_connect("localhost", "root", "");
if(!$conn){
    die("Error at connecting to Database".mysqli_connect_error());
}
$dbConnect = mysqli_select_db($conn, "courtsmanagement");
if(!$dbConnect){
    die("Error at selecting the Database");
}

?>