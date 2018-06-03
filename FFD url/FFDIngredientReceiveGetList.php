<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    
    
    
    if(isset($_POST["historyStartDate"]) && isset($_POST["historyEndDate"]))
    {
        $historyStartDate = $_POST["historyStartDate"];
        $historyEndDate = $_POST["historyEndDate"];
    }

    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    

    $sql = "SELECT ReceiveDate FROM `ingredientreceive` WHERE date_format(ReceiveDate,'%Y-%m-%d') between date_format('$historyStartDate','%Y-%m-%d') and date_format('$historyEndDate','%Y-%m-%d') GROUP by ReceiveDate ;";
    $sql .= "SELECT * FROM `ingredientreceive`WHERE date_format(ReceiveDate,'%Y-%m-%d') between date_format('$historyStartDate','%Y-%m-%d') and date_format('$historyEndDate','%Y-%m-%d');";
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
