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
    
    
    
    $sql = "SELECT dayofweek(ReceiptDate) DayOfWeek, date_format(ReceiptDate,'%Y-%m-%d') SalesDate, menu.MenuTypeID, sum(ordertaking.SpecialPrice*ordertaking.Quantity) Sales FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and orderTaking.Status in (0,6) GROUP BY date_format(ReceiptDate,'%Y-%m-%d'),menu.MenuTypeID order by date_format(ReceiptDate,'%Y-%m-%d'), menu.MenuTypeID;";
    $sql .= "select MenuTypeID, ifnull(Sales,0) Sales  from (SELECT menu.MenuTypeID, sum(ordertaking.SpecialPrice*ordertaking.Quantity)/(select count(*) from (select date_format(receiptDate,'%Y-%m-%d') from receipt WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') GROUP BY date_format(receiptDate,'%Y-%m-%d'))a1) Sales FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and orderTaking.Status in (0,6) GROUP BY menu.MenuTypeID order by menu.MenuTypeID)a2;";
    $sql .= "select ifnull(Sales,0) Sales  from (SELECT sum(ordertaking.SpecialPrice*ordertaking.Quantity)/(select count(*) from (select date_format(receiptDate,'%Y-%m-%d') from receipt WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') GROUP BY date_format(receiptDate,'%Y-%m-%d'))a1) Sales FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and orderTaking.Status in (0,6))a2;";
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
