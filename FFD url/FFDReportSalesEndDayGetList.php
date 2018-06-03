<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    

    if(isset($_POST["endDate"]))
    {       
        $endDate = $_POST["endDate"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "select case receipt.CustomerType when 1 then 'Dine in' when 2 then 'Take away' when 3 then 'Delivery' END as CustomerType,menutype.Name MenuType, submenutype.Name SubMenuType,receipt.CustomerType as CustomerTypeID ,menutype.MenuTypeID ,(select COUNT(*) from submenutype WHERE MenuTypeID = menutype.MenuTypeID) AS CountSubMenuType, count(*) as Quantity, ifnull(sum(ordertaking.SpecialPrice*ordertaking.Quantity),0) Sales from receipt LEFT JOIN ordertaking ON receipt.ReceiptID=ordertaking.ReceiptID LEFT JOIN menu ON ordertaking.MenuID = menu.MenuID LEFT JOIN submenutype ON menu.SubMenuTypeID = submenutype.SubMenuTypeID LEFT JOIN menutype ON menu.MenuTypeID = menutype.MenuTypeID WHERE receipt.STATUS in (2,3,4) and date_format(receipt.ReceiptDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d') and ordertaking.status in (0,6) GROUP BY receipt.CustomerType,menutype.MenuTypeID, submenutype.SubMenuTypeID ORDER BY receipt.CustomerType, menutype.OrderNo, submenutype.OrderNo;";
    $sql .= "select ifnull(sum(ReceiptSales.Sales),0) Sales, receipt.CustomerType as CustomerTypeID, case receipt.CustomerType when 1 then 'Dine in' when 2 then 'Take away' when 3 then 'Delivery' END as CustomerType, ifnull(sum(round((CASE DiscountType when 0 then 0 when 1 then DiscountAmount when 2 then DiscountAmount*0.01*ReceiptSales.Sales end),2)),0) AS DiscountValue ,ifnull(sum(round(0.07*(ReceiptSales.Sales - round((CASE DiscountType when 0 then 0 when 1 then DiscountAmount when 2 then DiscountAmount*0.01*ReceiptSales.Sales end),2)),2)),0) AS Vat, ifnull(sum(round(ReceiptSales.Sales-round((CASE DiscountType when 0 then 0 when 1 then DiscountAmount when 2 then DiscountAmount*0.01*ReceiptSales.Sales end),2)+round(0.07*(ReceiptSales.Sales - round((CASE DiscountType when 0 then 0 when 1 then DiscountAmount when 2 then DiscountAmount*0.01*ReceiptSales.Sales end),2)),2),0)-(ReceiptSales.Sales-round((CASE DiscountType when 0 then 0 when 1 then DiscountAmount when 2 then DiscountAmount*0.01*ReceiptSales.Sales end),2)+round(0.07*(ReceiptSales.Sales - round((CASE DiscountType when 0 then 0 when 1 then DiscountAmount when 2 then DiscountAmount*0.01*ReceiptSales.Sales end),2)),2))),0) Round from receipt LEFT JOIN (select receipt.ReceiptID, ifnull(sum(ordertaking.SpecialPrice*ordertaking.Quantity),0) Sales from receipt LEFT JOIN ordertaking ON receipt.ReceiptID =  ordertaking.ReceiptID WHERE receipt.STATUS in (2,3,4) and date_format(receipt.ReceiptDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d') and ordertaking.status in (0,6) GROUP BY receipt.ReceiptID)ReceiptSales ON receipt.ReceiptID = ReceiptSales.ReceiptID WHERE receipt.STATUS in (2,3,4) and date_format(receipt.ReceiptDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d') GROUP BY receipt.CustomerType;";
    $sql .= "select receipt.CustomerType as CustomerTypeID, case receipt.CustomerType when 1 then 'Dine in' when 2 then 'Take away' when 3 then 'Delivery' END as CustomerType,count(*) CountReceipt,ifnull(sum(receipt.ServingPerson),0) ServingPerson from receipt WHERE receipt.STATUS in (2,3,4) and date_format(receipt.ReceiptDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d') GROUP BY receipt.CustomerType;";
    $sql .= "SELECT ifnull(sum(CashAmount),0) CashAmount FROM `receipt` WHERE CashAmount != 0 and receipt.STATUS in (2,3,4) and date_format(receipt.ReceiptDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d');";
    $sql .= "SELECT CreditCardType as CreditCardTypeID, case CreditCardType when 1 then 'American express' when 2 then 'JCB' when 3 then 'Master card' when 4 then 'Union pay' when 5 then 'Visa' when 0 then 'Other' end CreditCardType, ifnull(sum(CreditCardAmount),0) CreditCardAmount FROM `receipt` WHERE CreditCardAmount != 0 and receipt.STATUS in (2,3,4) and date_format(receipt.ReceiptDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d') GROUP BY CreditCardType;";
//    $sql .= "SELECT ifnull(sum(TransferAmount),0) TransferAmount FROM `receipt` WHERE TransferAmount != 0 and receipt.STATUS in (2,3,4) and date_format(receipt.ReceiptDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d');";

    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;

    
    // Close connections
    mysqli_close($con);
?>
