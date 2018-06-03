<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["orderTakingID"]) && isset($_POST["customerTableID"]) && isset($_POST["menuID"]) && isset($_POST["quantity"]) && isset($_POST["specialPrice"]) && isset($_POST["price"]) && isset($_POST["takeAway"]) && isset($_POST["noteIDListInText"]) && isset($_POST["orderNo"]) && isset($_POST["status"]) && isset($_POST["receiptID"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]) &&
    isset($_POST["ccOrderTakingID"]) && isset($_POST["ccCustomerTableID"]) && isset($_POST["ccMenuID"]) && isset($_POST["ccQuantity"]) && isset($_POST["ccSpecialPrice"]) && isset($_POST["ccPrice"]) && isset($_POST["ccTakeAway"]) && isset($_POST["ccNoteIDListInText"]) && isset($_POST["ccOrderNo"]) && isset($_POST["ccStatus"]) && isset($_POST["ccReceiptID"]) && isset($_POST["ccModifiedUser"]) && isset($_POST["ccModifiedDate"])
    )
    {
        $orderTakingID = $_POST["orderTakingID"];
        $customerTableID = $_POST["customerTableID"];
        $menuID = $_POST["menuID"];
        $quantity = $_POST["quantity"];
        $specialPrice = $_POST["specialPrice"];
        $price = $_POST["price"];
        $takeAway = $_POST["takeAway"];
        $noteIDListInText = $_POST["noteIDListInText"];
        $orderNo = $_POST["orderNo"];
        $status = $_POST["status"];
        $receiptID = $_POST["receiptID"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
        
        
        $ccOrderTakingID = $_POST["ccOrderTakingID"];
        $ccCustomerTableID = $_POST["ccCustomerTableID"];
        $ccMenuID = $_POST["ccMenuID"];
        $ccQuantity = $_POST["ccQuantity"];
        $ccSpecialPrice = $_POST["ccSpecialPrice"];
        $ccPrice = $_POST["ccPrice"];
        $ccTakeAway = $_POST["ccTakeAway"];
        $ccNoteIDListInText = $_POST["ccNoteIDListInText"];
        $ccOrderNo = $_POST["ccOrderNo"];
        $ccStatus = $_POST["ccStatus"];
        $ccReceiptID = $_POST["ccReceiptID"];
        $ccModifiedUser = $_POST["ccModifiedUser"];
        $ccModifiedDate = $_POST["ccModifiedDate"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    
    //insert cancel ordertaking
    //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
    //query statement
    for($j=0;$j<$retryNo;$j++)
    {
        $sql = "INSERT INTO OrderTaking(OrderTakingID, CustomerTableID, MenuID, Quantity, SpecialPrice, Price, TakeAway, NoteIDListInText, OrderNo, Status, ReceiptID, ModifiedUser, ModifiedDate) VALUES ('" . ($ccOrderTakingID+$j) . "', '$ccCustomerTableID', '$ccMenuID', '$ccQuantity', '$ccSpecialPrice', '$ccPrice', '$ccTakeAway', '$ccNoteIDListInText', '$ccOrderNo', '$ccStatus', '$ccReceiptID', '$ccModifiedUser', '$ccModifiedDate')";
        $ret = doQueryTask($sql);
        if($ret == "")
        {
            //insert ผ่าน
            break;
        }
    }
    
    
    
    if($j==$retryNo)
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    else if($j != 0)
    {
        //มีการเปลี่ยน id
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select $ccOrderTakingID as OrderTakingID, 1 as ReplaceSelf, '$ccModifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'OrderTaking';
        $action = 'd';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $ccOrderTakingID = $ccOrderTakingID+$j;
        $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$ccOrderTakingID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'OrderTaking';
        $action = 'i';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    else if($j == 0)
    {
        //update IdInserted
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$ccOrderTakingID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'OrderTaking';
        $action = 'u';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$ccOrderTakingID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'OrderTaking';
    $action = 'i';
    $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    
    
    //update ordertaking
    //query statement
    $sql = "update OrderTaking set CustomerTableID = '$customerTableID', MenuID = '$menuID', Quantity = '$quantity', SpecialPrice = '$specialPrice', Price = '$price', TakeAway = '$takeAway', NoteIDListInText = '$noteIDListInText', OrderNo = '$orderNo', Status = '$status', ReceiptID = '$receiptID', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where OrderTakingID = '$orderTakingID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$orderTakingID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'OrderTaking';
    $action = 'u';
    $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    //update ordertaking
    //query statement
    $sql = "update OrderTaking set CustomerTableID = '$customerTableID', MenuID = '$menuID', Quantity = '$quantity', SpecialPrice = '$specialPrice', Price = '$price', TakeAway = '$takeAway', NoteIDListInText = '$noteIDListInText', OrderNo = '$orderNo', Status = '$status', ReceiptID = '$receiptID', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where OrderTakingID = '$orderTakingID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$orderTakingID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'OrderTaking';
    $action = 'u';
    $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    //do script successful
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
