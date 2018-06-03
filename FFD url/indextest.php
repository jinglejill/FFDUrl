<?php
    include_once("dbConnect.php");
    setConnectionValue('MAMARIN5');
    
    /* Attempt MySQL server connection. Assuming you are running MySQL
     server with default setting (user 'root' with no password) */
    $con3 = mysqli_connect("localhost", "นห", "");
    
//    // Check connection
    if($con3 === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
    
    // Attempt create database query execution
    $sql = "CREATE DATABASE demo";
//    $sql = "select DeviceToken from useraccount ";
    if(mysqli_query($con3, $sql)){
        echo "Database created successfully";
    } else{
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($con3);
    }
    
    // Close connection
    mysqli_close($con3);
?>
