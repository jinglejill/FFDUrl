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
            $otOrderTakingID[$i] = $_POST["otOrderTakingID".sprintf("%02d", $i)];
            $otCustomerTableID[$i] = $_POST["otCustomerTableID".sprintf("%02d", $i)];
            $otMenuID[$i] = $_POST["otMenuID".sprintf("%02d", $i)];
            $otQuantity[$i] = $_POST["otQuantity".sprintf("%02d", $i)];
            $otSpecialPrice[$i] = $_POST["otSpecialPrice".sprintf("%02d", $i)];
            $otPrice[$i] = $_POST["otPrice".sprintf("%02d", $i)];
            $otTakeAway[$i] = $_POST["otTakeAway".sprintf("%02d", $i)];
            $otNoteIDListInText[$i] = $_POST["otNoteIDListInText".sprintf("%02d", $i)];
            $otOrderNo[$i] = $_POST["otOrderNo".sprintf("%02d", $i)];
            $otStatus[$i] = $_POST["otStatus".sprintf("%02d", $i)];
            $otReceiptID[$i] = $_POST["otReceiptID".sprintf("%02d", $i)];
            $otModifiedUser[$i] = $_POST["otModifiedUser".sprintf("%02d", $i)];
            $otModifiedDate[$i] = $_POST["otModifiedDate".sprintf("%02d", $i)];
        }
    }
    
    if (isset($_POST["countOrderKitchen"]))
    {
        $countOrderKitchen = $_POST["countOrderKitchen"];
        for($i=0; $i<$countOrderKitchen; $i++)
        {
            $okOrderKitchenID[$i] = $_POST["okOrderKitchenID".sprintf("%02d", $i)];
            $okCustomerTableID[$i] = $_POST["okCustomerTableID".sprintf("%02d", $i)];
            $okOrderTakingID[$i] = $_POST["okOrderTakingID".sprintf("%02d", $i)];
            $okSequenceNo[$i] = $_POST["okSequenceNo".sprintf("%02d", $i)];
            $okCustomerTableIDOrder[$i] = $_POST["okCustomerTableIDOrder".sprintf("%02d", $i)];
            $okModifiedUser[$i] = $_POST["okModifiedUser".sprintf("%02d", $i)];
            $okModifiedDate[$i] = $_POST["okModifiedDate".sprintf("%02d", $i)];
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
    
    
    //update ordertakingList
    if($countOrderTaking > 0)
    {
        for($i=0; $i<$countOrderTaking; $i++)
        {
            //query statement
            $sql = "update OrderTaking set CustomerTableID = '$otCustomerTableID[$i]', MenuID = '$otMenuID[$i]', Quantity = '$otQuantity[$i]', SpecialPrice = '$otSpecialPrice[$i]', Price = '$otPrice[$i]', TakeAway = '$otTakeAway[$i]', NoteIDListInText = '$otNoteIDListInText[$i]', OrderNo = '$otOrderNo[$i]', Status = '$otStatus[$i]', ReceiptID = '$otReceiptID[$i]', ModifiedUser = '$otModifiedUser[$i]', ModifiedDate = '$otModifiedDate[$i]' where OrderTakingID = '$otOrderTakingID[$i]'";
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
        $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID in ('$otOrderTakingID[0]'";
        for($i=1; $i<$countOrderTaking; $i++)
        {
            $sql .= ",'$otOrderTakingID[$i]'";
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
    //-----
    
    
    
    //update orderkitchenList
    if($countOrderKitchen > 0)
    {
        for($i=0; $i<$countOrderKitchen; $i++)
        {
            //query statement
            $sql = "update OrderKitchen set CustomerTableID = '$okCustomerTableID[$i]', OrderTakingID = '$okOrderTakingID[$i]', SequenceNo = '$okSequenceNo[$i]', CustomerTableIDOrder = '$okCustomerTableIDOrder[$i]', ModifiedUser = '$okModifiedUser[$i]', ModifiedDate = '$okModifiedDate[$i]' where OrderKitchenID = '$okOrderKitchenID[$i]'";
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
        $sql = "select *, 1 IdInserted from OrderKitchen where OrderKitchenID in ('$okOrderKitchenID[0]'";
        for($i=1; $i<$countOrderKitchen; $i++)
        {
            $sql .= ",'$okOrderKitchenID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'OrderKitchen';
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
    //-----
    
    
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
