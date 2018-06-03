<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countOrderTaking"]))
    {
        $countOrderTaking = $_POST["countOrderTaking"];
        for($i=0; $i<$countOrderTaking; $i++)
        {
            $orderTakingID[$i] = $_POST["orderTakingID".sprintf("%02d", $i)];
            $customerTableID[$i] = $_POST["customerTableID".sprintf("%02d", $i)];
            $menuID[$i] = $_POST["menuID".sprintf("%02d", $i)];
            $quantity[$i] = $_POST["quantity".sprintf("%02d", $i)];
            $specialPrice[$i] = $_POST["specialPrice".sprintf("%02d", $i)];
            $price[$i] = $_POST["price".sprintf("%02d", $i)];
            $takeAway[$i] = $_POST["takeAway".sprintf("%02d", $i)];
            $noteIDListInText[$i] = $_POST["noteIDListInText".sprintf("%02d", $i)];
            $orderNo[$i] = $_POST["orderNo".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
            $receiptID[$i] = $_POST["receiptID".sprintf("%02d", $i)];
            $modifiedUser[$i] = $_POST["modifiedUser".sprintf("%02d", $i)];
            $modifiedDate[$i] = $_POST["modifiedDate".sprintf("%02d", $i)];
        }
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    if($countOrderTaking > 0)
    {
        for($i=0; $i<$countOrderTaking; $i++)
        {
            //query statement
            $sql = "update OrderTaking set CustomerTableID = '$customerTableID[$i]', MenuID = '$menuID[$i]', Quantity = '$quantity[$i]', SpecialPrice = '$specialPrice[$i]', Price = '$price[$i]', TakeAway = '$takeAway[$i]', NoteIDListInText = '$noteIDListInText[$i]', OrderNo = '$orderNo[$i]', Status = '$status[$i]', ReceiptID = '$receiptID[$i]', ModifiedUser = '$modifiedUser[$i]', ModifiedDate = '$modifiedDate[$i]' where OrderTakingID = '$orderTakingID[$i]'";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID in ('$orderTakingID[0]'";
        for($i=1; $i<$countOrderTaking; $i++)
        {
            $sql .= ",'$orderTakingID[$i]'";
        }
        $sql .= ")";
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
    }
    
    
    
    //do script successful
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
