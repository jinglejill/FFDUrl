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
    
    
    $sql = "select receipt.ReceiptDate ReceiptDate,menutype.Name MenuType,menu.TitleThai Menu,ordertaking.Quantity,ordertaking.Quantity*ordertaking.SpecialPrice as TotalAmount from receipt LEFT JOIN ordertaking ON receipt.ReceiptID = ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID LEFT JOIN menutype ON menu.MenuTypeID = menutype.MenuTypeID WHERE receipt.Status in (2,3,4) and  date_format(receiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and orderTaking.status in (0,6) ORDER BY receipt.ReceiptDate;";
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
