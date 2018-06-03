<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    $dbName = $_POST["dbName"];
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    //check expired date
    $sql = "SELECT * FROM Setting where KeyName = 'expiredDate'";
    $selectedRow = getSelectedRow($sql);
    $expiredDate = date('Y-m-d H:i:s',strtotime($selectedRow[0]["Value"]));
    $currentDate = date('Y-m-d H:i:s');
    
    if($currentDate >= $expiredDate)
    {
        writeToLog("Expired");
        $arrOfTableArray = array();
        $resultArray = array();
        $arrExpired = array();
        $arrExpired['Expired'] = "1";
        array_push($resultArray,$arrExpired);
        array_push($arrOfTableArray,$resultArray);
        echo json_encode($arrOfTableArray);
        mysqli_close($con);
        exit();
    }
    else
    {
        writeToLog("not expire");
    }
    
    
    
    $sql = "select * from FFD.Branch where DBName = '$dbName'";
    $selectedRow = getSelectedRow($sql);
    $branchID = $selectedRow[0]["BranchID"];
    
    
    
    
    //ตัว transaction ให้ select มาตัวสถานะใช้งานอยู่ union กับ maxid union กับ transaction วันปัจจุบัน
    $sql = "SELECT *, 1 as IdInserted FROM UserAccount order by Username;";
    $sql .= "select *, 1 as IdInserted from login where loginid = (select max(loginid) FROM login);";
    $sql .= "select *, 1 as IdInserted from customerTable order by orderNo;";
    $sql .= "select *, 1 as IdInserted from menuType order by orderNo;";
    $sql .= "select *, 1 as IdInserted from menu order by orderNo;";
    $sql .= "select *, 1 as IdInserted from tableTaking where receiptID = 0 union select *, 1 as IdInserted from tableTaking where tableTakingID = (select max(tableTakingID) FROM tableTaking) union SELECT *, 1 as IdInserted FROM `tableTaking` WHERE date_format(`ModifiedDate`,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d');";
    $sql .= "select *, 1 as IdInserted from orderTaking where status in (1,2,3) union select *, 1 as IdInserted from orderTaking where orderTakingID = (select max(orderTakingID) FROM orderTaking) union SELECT *, 1 as IdInserted FROM `ordertaking` WHERE date_format(`ModifiedDate`,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d');";//ongoing record + max record in order to get nextID + today record
    $sql .= "select *, 1 as IdInserted from menuTypeNote;";
    $sql .= "select *, 1 as IdInserted from note;";
    $sql .= "select *, 1 as IdInserted from orderNote where orderNoteID = (select max(orderNoteID) FROM orderNote) or orderTakingID in (select orderTakingID from orderTaking where status in (1,2,3) union select orderTakingID from orderTaking where orderTakingID = (select max(orderTakingID) FROM orderTaking) union SELECT orderTakingID FROM `ordertaking` WHERE date_format(`ModifiedDate`,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d'));";
    $sql .= "select *, 1 as IdInserted from orderKitchen where orderTakingID in (select orderTakingID from orderTaking where status in (1,2,3) union select orderTakingID from orderTaking where orderTakingID = (select max(orderTakingID) FROM orderTaking) union SELECT orderTakingID FROM `ordertaking` WHERE date_format(`ModifiedDate`,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d'));";
    $sql .= "select *, 1 as IdInserted from member;";
    $sql .= "select *, 1 as IdInserted from submenutype;";
    $sql .= "select *, 1 as IdInserted from address;";
    $sql .= "select *, 1 as IdInserted from receipt where status = 1 union select *, 1 as IdInserted from receipt where receiptID = (select max(receiptID) FROM receipt) union SELECT *, 1 as IdInserted FROM `receipt` WHERE date_format(`ModifiedDate`,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d');";
    $sql .= "select *, 1 as IdInserted from discount where status = 1;";
    $sql .= "select *, 1 as IdInserted from setting;";
    $sql .= "select *, 1 as IdInserted from rewardProgram where date_format(`startDate`,'%Y-%m-%d') <= date_format(now(),'%Y-%m-%d') and date_format(`endDate`,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d');";
    $sql .= "select *, 1 as IdInserted from rewardPoint;";
    $sql .= "select *, 1 as IdInserted from receiptCustomerTable;";
    $sql .= "select *, 1 as IdInserted from SpecialPriceProgram where date_format(`startDate`,'%Y-%m-%d') <= date_format(now(),'%Y-%m-%d') and date_format(`endDate`,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d');";
    $sql .= "select *, 1 as IdInserted from MenuIngredient;";
    $sql .= "select *, 1 as IdInserted from IngredientType;";
    $sql .= "select *, 1 as IdInserted from Ingredient;";
    $sql .= "select *, 1 as IdInserted from SubIngredientType;";
    $sql .= "select *, 1 as IdInserted from NoteType;";
    $sql .= "select *, 1 as IdInserted from Board;";
    $sql .= "select *, 1 as IdInserted from BillPrint;";
    $sql .= "select *, 1 as IdInserted from OrderCancelDiscount;";
    $sql .= "select *, 1 as IdInserted from Printer;";
    $sql .= "select *, 1 as IdInserted from RoleTabMenu;";
    $sql .= "select *, 1 as IdInserted from TabMenu;";
//    $sql .= "select *, 1 as IdInserted from FFD.Branch;";
    $sql .= "select *, 1 as IdInserted from SubDistrict;";
    $sql .= "select *, 1 as IdInserted from district;";
    $sql .= "select *, 1 as IdInserted from province;";
    $sql .= "select *, 1 as IdInserted from zipcode;";
    $sql .= "select *, 1 as IdInserted from moneyCheck;";
    
    
    //****-----
//    $sql2 = "(select DISTINCT JUMMUM2.receipt.*, 1 as IdInserted from JUMMUM2.receipt left join $dbName.receiptprint ON JUMMUM2.receipt.receiptID = $dbName.receiptprint.ReceiptID where branchID = '$branchID' and $dbName.receiptprint.receiptprintid is null order by receiptDate, receiptID) UNION (select DISTINCT JUMMUM2.receipt.*, 1 as IdInserted from JUMMUM2.receipt left join $dbName.receiptprint ON JUMMUM2.receipt.receiptID = $dbName.receiptprint.ReceiptID where branchID = '$branchID' and $dbName.receiptprint.receiptprintid is not null order by receipt.ReceiptDate DESC, receipt.ReceiptID DESC limit 20);";
    $sql2 = "(select JUMMUM2.receipt.*, 1 as IdInserted from JUMMUM2.receipt where JUMMUM2.receipt.branchID = '$branchID' and status in (2,5,7,8,11,12,13)) UNION (select JUMMUM2.receipt.*, 1 as IdInserted from JUMMUM2.receipt where branchID = '$branchID' and status = '6' order by receipt.ReceiptDate DESC, receipt.ReceiptID DESC limit 20) UNION (select JUMMUM2.receipt.*, 1 as IdInserted from JUMMUM2.receipt where branchID = '$branchID' and status in (9,10,14) order by receipt.ReceiptDate DESC, receipt.ReceiptID DESC limit 20);";
    $selectedRow = getSelectedRow($sql2);

    
    $receiptIDList = array();
    for($i=0; $i<sizeof($selectedRow); $i++)
    {
        array_push($receiptIDList,$selectedRow[$i]["ReceiptID"]);
    }
    if(sizeof($receiptIDList) > 0)
    {
        $receiptIDListInText = $receiptIDList[0];
        for($i=1; $i<sizeof($receiptIDList); $i++)
        {
            $receiptIDListInText .= "," . $receiptIDList[$i];
        }
    
        
        
        $sql2 .= "select *, 1 as IdInserted  from JUMMUM2.OrderTaking where receiptID in ($receiptIDListInText);";
        $sql2 .= "select *, 1 as IdInserted  from JUMMUM2.OrderNote where orderTakingID in (select orderTakingID from JUMMUM2.OrderTaking where receiptID in ($receiptIDListInText));";
        $sql2 .= "select *, 1 as IdInserted  from JUMMUM2.Dispute where receiptID in ($receiptIDListInText);";
        $sql2 .= "select *, 1 as IdInserted  from receiptPrint where receiptID in ($receiptIDListInText);";
    }
    else
    {
        $sql2 .= "select *, 1 as IdInserted  from JUMMUM2.OrderTaking where 0;";
        $sql2 .= "select *, 1 as IdInserted  from JUMMUM2.OrderNote where 0;";
        $sql2 .= "select *, 1 as IdInserted  from JUMMUM2.Dispute where 0;";
        $sql2 .= "select *, 1 as IdInserted  from receiptPrint where 0;";
    }
    $sql .= $sql2;
    
    $sql .= "select * from JUMMUM2.DisputeReason where status = 1;";
    
    //****-----
    
    
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;


    
    // Close connections
    mysqli_close($con);
    
?>
