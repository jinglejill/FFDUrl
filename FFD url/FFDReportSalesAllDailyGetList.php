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
    
    
    $sql = "SELECT dayofweek(ReceiptDate) DayOfWeek, date_format(ReceiptDate,'%Y-%m-%d') SalesDate, ifnull(sum(CashAmount+CreditCardAmount+TransferAmount),0) Sales, ifnull(sum(round((CASE DiscountType when 0 then 0 when 1 then DiscountAmount when 2 then DiscountAmount*0.01*ReceiptSales.Sales end),2)),0) AS DiscountValue FROM `receipt` LEFT join (select receipt.ReceiptID, ifnull(sum(ordertaking.SpecialPrice*ordertaking.Quantity),0) Sales from receipt LEFT JOIN ordertaking ON receipt.ReceiptID =  ordertaking.ReceiptID WHERE receipt.STATUS in (2,3,4) and date_format(receipt.ReceiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and ordertaking.Status in (0,6) GROUP BY receipt.ReceiptID)ReceiptSales ON receipt.ReceiptID = ReceiptSales.ReceiptID WHERE Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') GROUP BY date_format(ReceiptDate,'%Y-%m-%d') order by date_format(ReceiptDate,'%Y-%m-%d');";
    $sql .= "SELECT ifnull(sum(CashAmount+CreditCardAmount+TransferAmount)/(SELECT count(*) from (select date_format(ReceiptDate,'%Y-%m-%d') from receipt WHERE Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') GROUP BY date_format(ReceiptDate,'%Y-%m-%d'))a1),0) Sales FROM `receipt` WHERE Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d');";
    $sql .= "SELECT ifnull(sum(CashAmount+CreditCardAmount+TransferAmount)/(SELECT count(*) from (select date_format(ReceiptDate,'%Y-%m-%d') from receipt WHERE Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and (dayofweek(receiptDate) != 1 and dayofweek(receiptDate) != 7) GROUP BY date_format(ReceiptDate,'%Y-%m-%d'))a1),0) Sales FROM `receipt` WHERE Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and (dayofweek(receiptDate) != 1 and dayofweek(receiptDate) != 7);";
    $sql .= "SELECT ifnull(sum(CashAmount+CreditCardAmount+TransferAmount)/(SELECT count(*) from (select date_format(ReceiptDate,'%Y-%m-%d') from receipt WHERE Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and (dayofweek(receiptDate) = 1 or dayofweek(receiptDate) = 7) GROUP BY date_format(ReceiptDate,'%Y-%m-%d'))a1),0) Sales FROM `receipt` WHERE Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and (dayofweek(receiptDate) = 1 or dayofweek(receiptDate) = 7);";
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
