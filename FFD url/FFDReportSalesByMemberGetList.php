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
    
    
    
    $sql = "select case receipt.MemberID when 0 then '' else member.PhoneNo end as Customer,ifnull(MemberDate,'0000-00-00 00:00:00') MemberDate, ifnull(a1.Sales,0) SalesEatIn, ifnull(a2.Sales,0) SalesTakeAway, ifnull(a3.Sales,0) SalesDelivery,sum(CreditCardAmount+CashAmount+TransferAmount) Sales from receipt LEFT JOIN member ON receipt.MemberID = member.MemberID LEFT JOIN (select MemberID, sum(CreditCardAmount+CashAmount+TransferAmount) Sales from receipt WHERE status in (2,3,4) and date_format(receiptdate,'%Y-%m-%d') BETWEEN date_format('$startDate','%Y-%m-%d') AND date_format('$endDate','%Y-%m-%d') and CustomerType = 1 GROUP BY MemberID)a1 ON member.MemberID = a1.MemberID LEFT JOIN (select MemberID, sum(CreditCardAmount+CashAmount+TransferAmount) Sales from receipt WHERE status in (2,3,4) and date_format(receiptdate,'%Y-%m-%d') BETWEEN date_format('$startDate','%Y-%m-%d') AND date_format('$endDate','%Y-%m-%d') and CustomerType = 0 GROUP BY MemberID)a2 ON member.MemberID = a1.MemberID LEFT JOIN (select MemberID, sum(CreditCardAmount+CashAmount+TransferAmount) Sales from receipt WHERE status in (2,3,4) and date_format(receiptdate,'%Y-%m-%d') BETWEEN date_format('$startDate','%Y-%m-%d') AND date_format('$endDate','%Y-%m-%d') and CustomerType = 3 GROUP BY MemberID)a3 ON member.MemberID = a1.MemberID WHERE status in (2,3,4) and date_format(receiptdate,'%Y-%m-%d') BETWEEN date_format('$startDate','%Y-%m-%d') AND date_format('$endDate','%Y-%m-%d') GROUP BY member.MemberID order by sum(CreditCardAmount+CashAmount+TransferAmount) desc;";
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
