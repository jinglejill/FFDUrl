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
    if (isset($_POST["countTableTaking"]))
    {
        $countTableTaking = $_POST["countTableTaking"];
        for($i=0; $i<$countTableTaking; $i++)
        {
            $ttTableTakingID[$i] = $_POST["ttTableTakingID".sprintf("%02d", $i)];
            $ttCustomerTableID[$i] = $_POST["ttCustomerTableID".sprintf("%02d", $i)];
            $ttServingPerson[$i] = $_POST["ttServingPerson".sprintf("%02d", $i)];
            $ttReceiptID[$i] = $_POST["ttReceiptID".sprintf("%02d", $i)];
            $ttModifiedUser[$i] = $_POST["ttModifiedUser".sprintf("%02d", $i)];
            $ttModifiedDate[$i] = $_POST["ttModifiedDate".sprintf("%02d", $i)];
        }
    }
    if(isset($_POST["receiptID"]) && isset($_POST["customerTableID"]) && isset($_POST["memberID"]) && isset($_POST["servingPerson"]) && isset($_POST["customerType"]) && isset($_POST["openTableDate"]) && isset($_POST["cashAmount"]) && isset($_POST["cashReceive"]) && isset($_POST["creditCardType"]) && isset($_POST["creditCardNo"]) && isset($_POST["creditCardAmount"]) && isset($_POST["transferDate"]) && isset($_POST["transferAmount"]) && isset($_POST["remark"]) && isset($_POST["discountType"]) && isset($_POST["discountAmount"]) && isset($_POST["discountReason"]) && isset($_POST["status"]) && isset($_POST["receiptNoID"]) && isset($_POST["receiptNoTaxID"]) && isset($_POST["receiptDate"]) && isset($_POST["mergeReceiptID"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $receiptID = $_POST["receiptID"];
        $customerTableID = $_POST["customerTableID"];
        $memberID = $_POST["memberID"];
        $servingPerson = $_POST["servingPerson"];
        $customerType = $_POST["customerType"];
        $openTableDate = $_POST["openTableDate"];
        $cashAmount = $_POST["cashAmount"];
        $cashReceive = $_POST["cashReceive"];
        $creditCardType = $_POST["creditCardType"];
        $creditCardNo = $_POST["creditCardNo"];
        $creditCardAmount = $_POST["creditCardAmount"];
        $transferDate = $_POST["transferDate"];
        $transferAmount = $_POST["transferAmount"];
        $remark = $_POST["remark"];
        $discountType = $_POST["discountType"];
        $discountAmount = $_POST["discountAmount"];
        $discountReason = $_POST["discountReason"];
        $status = $_POST["status"];
        $receiptNoID = $_POST["receiptNoID"];
        $receiptNoTaxID = $_POST["receiptNoTaxID"];
        $receiptDate = $_POST["receiptDate"];
        $mergeReceiptID = $_POST["mergeReceiptID"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
    }

    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    
    //ordertaking
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
    
    
    
    
    //orderkitchen
    if($countOrderKitchen > 0)
    {
        for($i=0; $i<$countOrderKitchen; $i++)
        {
            //query statement
            $sql = "update OrderKitchen set CustomerTableID = '$okCustomerTableID[$i]', OrderTakingID = '$okOrderTakingID[$i]', SequenceNo = '$okSequenceNo[$i]',CustomerTableIDOrder = '$okCustomerTableIDOrder[$i]', ModifiedUser = '$okModifiedUser[$i]', ModifiedDate = '$okModifiedDate[$i]' where OrderKitchenID = '$okOrderKitchenID[$i]'";
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
    
    
    
    //tabletaking
    if($countTableTaking > 0)
    {
        for($i=0; $i<$countTableTaking; $i++)
        {
            //query statement
            $sql = "update TableTaking set CustomerTableID = '$ttCustomerTableID[$i]', ServingPerson = '$ttServingPerson[$i]', ReceiptID = '$ttReceiptID[$i]', ModifiedUser = '$ttModifiedUser[$i]', ModifiedDate = '$ttModifiedDate[$i]' where TableTakingID = '$ttTableTakingID[$i]'";
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
        $sql = "select *, 1 IdInserted from TableTaking where TableTakingID in ('$ttTableTakingID[0]'";
        for($i=1; $i<$countTableTaking; $i++)
        {
            $sql .= ",'$ttTableTakingID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'TableTaking';
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
    
    
    
    //receipt
    //query statement
    $sql = "update Receipt set CustomerTableID = '$customerTableID', MemberID = '$memberID', ServingPerson = '$servingPerson', CustomerType = '$customerType', OpenTableDate = '$openTableDate', CashAmount = '$cashAmount', CashReceive = '$cashReceive', CreditCardType = '$creditCardType', CreditCardNo = '$creditCardNo', CreditCardAmount = '$creditCardAmount', TransferDate = '$transferDate', TransferAmount = '$transferAmount', Remark = '$remark', DiscountType = '$discountType', DiscountAmount = '$discountAmount', DiscountReason = '$discountReason', Status = '$status', ReceiptNoID = '$receiptNoID', ReceiptNoTaxID = '$receiptNoTaxID', ReceiptDate = '$receiptDate', MergeReceiptID = '$mergeReceiptID', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where ReceiptID = '$receiptID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from Receipt where ReceiptID = '$receiptID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'Receipt';
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
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
    
    

?>
