<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    
    
    if(isset($_POST["receiptStartDate"]) && isset($_POST["receiptEndDate"]))
    {
        $receiptStartDate = $_POST["receiptStartDate"];
        $receiptEndDate = $_POST["receiptEndDate"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    

    $sql = "SELECT *, 1 as IdInserted FROM Receipt where receiptDate between '$receiptStartDate' and '$receiptEndDate' and status in (2,3,4) order by receiptDate desc;";
    $sql .= "SELECT *, 1 as IdInserted FROM OrderTaking where receiptID in (SELECT receiptID FROM Receipt where receiptDate between '$receiptStartDate' and '$receiptEndDate' and status in (2,3,4));";
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
