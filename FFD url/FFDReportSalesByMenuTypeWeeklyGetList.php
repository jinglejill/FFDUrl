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
    
    
    

    $sql = "SELECT week(date_format(ReceiptDate,'%Y-%m-%d'),1) WeekNo,date_format(DATE_ADD(ReceiptDate, INTERVAL(-WEEKDAY(ReceiptDate)) DAY),'%Y-%m-%d') SalesDate, menu.MenuTypeID, sum(ordertaking.SpecialPrice*ordertaking.Quantity) Sales FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and ordertaking.Status in (0,6) GROUP BY week(date_format(ReceiptDate,'%Y-%m-%d'),1), date_format(DATE_ADD(ReceiptDate, INTERVAL(-WEEKDAY(ReceiptDate)) DAY),'%Y-%m-%d'), menu.MenuTypeID order by week(date_format(ReceiptDate,'%Y-%m-%d'),1);";
    
    
    $sql .= "SELECT menu.MenuTypeID, sum(ordertaking.SpecialPrice*ordertaking.Quantity)/(SELECT COUNT(*) from (SELECT date_format(DATE_ADD(ReceiptDate, INTERVAL(-WEEKDAY(ReceiptDate)) DAY),'%Y-%m-%d') SalesDate FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format(now(),'%Y') and date_format(now(),'%Y-%m-%d') and ordertaking.Status in (0,6) GROUP BY date_format(DATE_ADD(ReceiptDate, INTERVAL(-WEEKDAY(ReceiptDate)) DAY),'%Y-%m-%d'))a1) Sales FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format(now(),'%Y') and date_format(now(),'%Y-%m-%d') and ordertaking.Status in (0,6) GROUP BY menu.MenuTypeID order by week(date_format(ReceiptDate,'%Y-%m-%d'),1);";
    
    $sql .= "SELECT sum(ordertaking.SpecialPrice*ordertaking.Quantity)/(SELECT COUNT(*) from (SELECT date_format(DATE_ADD(ReceiptDate, INTERVAL(-WEEKDAY(ReceiptDate)) DAY),'%Y-%m-%d') SalesDate FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format(now(),'%Y') and date_format(now(),'%Y-%m-%d') and ordertaking.Status in (0,6) GROUP BY date_format(DATE_ADD(ReceiptDate, INTERVAL(-WEEKDAY(ReceiptDate)) DAY),'%Y-%m-%d'))a1) Sales FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format(now(),'%Y') and date_format(now(),'%Y-%m-%d') and ordertaking.Status in (0,6);";
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
