<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    
    if(isset($_POST["ttTableTakingID"]) && isset($_POST["ttCustomerTableID"]) && isset($_POST["ttServingPerson"]) && isset($_POST["ttReceiptID"]) && isset($_POST["ttModifiedUser"]) && isset($_POST["ttModifiedDate"]))
    {
        $ttTableTakingID = $_POST["ttTableTakingID"];
        $ttCustomerTableID = $_POST["ttCustomerTableID"];
        $ttServingPerson = $_POST["ttServingPerson"];
        $ttReceiptID = $_POST["ttReceiptID"];
        $ttModifiedUser = $_POST["ttModifiedUser"];
        $ttModifiedDate = $_POST["ttModifiedDate"];
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


    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    //query statement
    $sql = "update TableTaking set CustomerTableID = '$ttCustomerTableID', ServingPerson = '$ttServingPerson', ReceiptID = '$ttReceiptID', ModifiedUser = '$ttModifiedUser', ModifiedDate = '$ttModifiedDate' where TableTakingID = '$ttTableTakingID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from TableTaking where TableTakingID = '$ttTableTakingID'";
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
    //-----
    
    
    
    //table : receiptNo
    {
        $sql = "select * from ReceiptNo";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            $mon = getdate()[mon];
            $month = sprintf("%02d", $mon);
            $year = getdate()[year];
            $receiptNoID = $year . $month . sprintf("%06d", 1);
        }
        else
        {
            $sql = "select * from ReceiptNo order by ReceiptNoID desc";
            $selectedRow = getSelectedRow($sql);
            $maxReceiptNoID = $selectedRow[0]["ReceiptNoID"];
            $yearMonth = substr($maxReceiptNoID,0,6);
            
            $mon = getdate()[mon];
            $month = sprintf("%02d", $mon);
            $year = getdate()[year];
            $currentYearMonth = $year . $month;
            if($yearMonth == $currentYearMonth)
            {
                $receiptNoID = $year . $month . sprintf("%06d", intval(substr($maxReceiptNoID,6,6))+1);
            }
            else
            {
                $receiptNoID = $year . $month . sprintf("%06d", 1);
            }
        }
        
        
        
        //query statement
        $sql = "INSERT INTO ReceiptNo(ReceiptNoID, ModifiedUser, ModifiedDate) VALUES ('$receiptNoID', '$rtModifiedUser[0]', '$rtModifiedDate[0]')";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    //update receipt
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
    $ret2 = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "" || $ret2 != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
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



    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " .  $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
