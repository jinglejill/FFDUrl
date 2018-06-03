<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    
    
    if(isset($_POST["startDate"]) && isset($_POST["endDate"]))
    {
        $startDate = $_POST["startDate"];
        $endDate = $_POST["endDate"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    

    $sql = "SELECT date_format(ReceiptDate,'%Y-%m') Month,date_format(DATE_SUB(ReceiptDate, INTERVAL DAYOFMONTH(ReceiptDate)-1 DAY),'%Y-%m-%d') SalesDate, sum(CashAmount+CreditCardAmount+TransferAmount) Sales FROM `receipt` WHERE Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') GROUP BY date_format(ReceiptDate,'%Y-%m'),date_format(DATE_SUB(ReceiptDate, INTERVAL DAYOFMONTH(ReceiptDate)-1 DAY),'%Y-%m-%d') order by date_format(ReceiptDate,'%Y-%m');";
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
