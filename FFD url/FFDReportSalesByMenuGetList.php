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
    
    
    
    $sql = "select a1.MenuTypeID,a1.MenuID,ifnull(a2.Sales,0) Sales,ifnull(a1.Sales,0) SalesYTD from (SELECT menu.MenuTypeID, menu.MenuID, sum(ordertaking.SpecialPrice*ordertaking.Quantity)/(select count(*) from (select date_format(ReceiptDate,'%Y-%m-%d') from receipt WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') GROUP BY date_format(ReceiptDate,'%Y-%m-%d'))a3) Sales, menutype.OrderNo MenuTypeOrderNo, menu.OrderNo MenuOrderNo FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID LEFT JOIN menutype ON menu.MenuTypeID = menutype.MenuTypeID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and ordertaking.Status in (0,6) GROUP BY menu.MenuTypeID,menu.MenuID)a1 LEFT JOIN (SELECT menu.MenuTypeID, menu.MenuID, sum(ordertaking.SpecialPrice*ordertaking.Quantity)/(select count(*) from (select date_format(ReceiptDate,'%Y-%m-%d') from receipt  WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') GROUP BY date_format(ReceiptDate,'%Y-%m-%d'))a4) Sales FROM `receipt` LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID WHERE receipt.Status in (2,3,4) and date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and ordertaking.Status in (0,6) GROUP BY menu.MenuTypeID,menu.MenuID)a2 ON a1.MenuTypeID = a2.MenuTypeID and a1.MenuID = a2.MenuID order by MenuTypeOrderNo, MenuOrderNo";
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
