<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    //ordertaking
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
    
    //orderNote
    if (isset($_POST["countOrderNote"]))
    {
        $countOrderNote = $_POST["countOrderNote"];
        for($i=0; $i<$countOrderNote; $i++)
        {
            $onOrderNoteID[$i] = $_POST["onOrderNoteID".sprintf("%02d", $i)];
            $onOrderTakingID[$i] = $_POST["onOrderTakingID".sprintf("%02d", $i)];
            $onNoteID[$i] = $_POST["onNoteID".sprintf("%02d", $i)];
            $onModifiedUser[$i] = $_POST["onModifiedUser".sprintf("%02d", $i)];
            $onModifiedDate[$i] = $_POST["onModifiedDate".sprintf("%02d", $i)];
        }
    }


    //orderKitchen
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
    
    //receipt
    if (isset($_POST["countReceipt"]))
    {
        $countReceipt = $_POST["countReceipt"];
        for($i=0; $i<$countReceipt; $i++)
        {
            $rtReceiptID[$i] = $_POST["rtReceiptID".sprintf("%02d", $i)];
            $rtCustomerTableID[$i] = $_POST["rtCustomerTableID".sprintf("%02d", $i)];
            $rtMemberID[$i] = $_POST["rtMemberID".sprintf("%02d", $i)];
            $rtServingPerson[$i] = $_POST["rtServingPerson".sprintf("%02d", $i)];
            $rtCustomerType[$i] = $_POST["rtCustomerType".sprintf("%02d", $i)];
            $rtOpenTableDate[$i] = $_POST["rtOpenTableDate".sprintf("%02d", $i)];
            $rtCashAmount[$i] = $_POST["rtCashAmount".sprintf("%02d", $i)];
            $rtCashReceive[$i] = $_POST["rtCashReceive".sprintf("%02d", $i)];
            $rtCreditCardType[$i] = $_POST["rtCreditCardType".sprintf("%02d", $i)];
            $rtCreditCardNo[$i] = $_POST["rtCreditCardNo".sprintf("%02d", $i)];
            $rtCreditCardAmount[$i] = $_POST["rtCreditCardAmount".sprintf("%02d", $i)];
            $rtTransferDate[$i] = $_POST["rtTransferDate".sprintf("%02d", $i)];
            $rtTransferAmount[$i] = $_POST["rtTransferAmount".sprintf("%02d", $i)];
            $rtRemark[$i] = $_POST["rtRemark".sprintf("%02d", $i)];
            $rtDiscountType[$i] = $_POST["rtDiscountType".sprintf("%02d", $i)];
            $rtDiscountAmount[$i] = $_POST["rtDiscountAmount".sprintf("%02d", $i)];
            $rtDiscountReason[$i] = $_POST["rtDiscountReason".sprintf("%02d", $i)];
            $rtStatus[$i] = $_POST["rtStatus".sprintf("%02d", $i)];
            $rtReceiptNoID[$i] = $_POST["rtReceiptNoID".sprintf("%02d", $i)];
            $rtReceiptNoTaxID[$i] = $_POST["rtReceiptNoTaxID".sprintf("%02d", $i)];
            $rtReceiptDate[$i] = $_POST["rtReceiptDate".sprintf("%02d", $i)];
            $rtMergeReceiptID[$i] = $_POST["rtMergeReceiptID".sprintf("%02d", $i)];
            $rtModifiedUser[$i] = $_POST["rtModifiedUser".sprintf("%02d", $i)];
            $rtModifiedDate[$i] = $_POST["rtModifiedDate".sprintf("%02d", $i)];
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
    
    
    
    //delete ordernote and ordertaking where ordertaking status = 1
    //ordernote
    {
        //query statement
        $sql = "delete from ordernote where OrderTakingID in (select OrderTakingID from ordertaking where customerTableID = '$customerTableID[0]' and status = 1)";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    //ordertaking
    {
        //query statement
        $sql = "delete from ordertaking where customerTableID = '$customerTableID[0]' and status = 1";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }


    
    
    //orderKitchen getMaxSequenceNo before insert ordertaking
    $sql = "select ifnull(max(sequenceNo),0) MaxSequenceNo from ordertaking LEFT JOIN orderkitchen ON ordertaking.OrderTakingID = orderkitchen.OrderTakingID WHERE ordertaking.customertableid = '$customerTableID[0]' and STATUS = 2";
    $selectedRow = getSelectedRow($sql);
    $maxSequenceNo = $selectedRow[0]["MaxSequenceNo"];
    $nextSequenceNo = $maxSequenceNo+1;
    
    
    
    
    
    //ordertaking
    $orderTakingOldNew = array();
    if($countOrderTaking > 0)
    {
        for($k=0; $k<$countOrderTaking; $k++)
        {
            
            //query statement
            $sql = "INSERT INTO OrderTaking(CustomerTableID, MenuID, Quantity, SpecialPrice, Price, TakeAway, NoteIDListInText, OrderNo, Status, ReceiptID, ModifiedUser, ModifiedDate) VALUES ('$customerTableID[$k]', '$menuID[$k]', '$quantity[$k]', '$specialPrice[$k]', '$price[$k]', '$takeAway[$k]', '$noteIDListInText[$k]', '$orderNo[$k]', '$status[$k]', '$receiptID[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
            
            
            
            //insert ผ่าน
            $newID = mysqli_insert_id($con);
            
            
            
            //sync generated id back to app
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select $orderTakingID[$k] as OrderTakingID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
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
            $orderTakingOldNew[$orderTakingID[$k]] = $newID;
            $orderTakingID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$orderTakingID[$k]'";
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
        $action = 'i';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    
    //ordernote
    if($countOrderNote > 0)
    {
        for($k=0; $k<$countOrderNote; $k++)
        {
            //query statement
            $onOrderTakingID[$k] = $orderTakingOldNew[$onOrderTakingID[$k]];
            $sql = "INSERT INTO OrderNote(OrderTakingID, NoteID, ModifiedUser, ModifiedDate) VALUES ('$onOrderTakingID[$k]', '$onNoteID[$k]', '$onModifiedUser[$k]', '$onModifiedDate[$k]')";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
            
            
            
            //insert ผ่าน
            $newID = mysqli_insert_id($con);
            
            
            
            //**********sync device token ตัวเอง delete old id and insert newID
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select $onOrderNoteID[$k] as OrderNoteID, 1 as ReplaceSelf, '$onModifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'OrderNote';
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
            $onOrderNoteID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderNote where OrderNoteID = '$onOrderNoteID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'OrderNote';
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
        
        
        
        //**********sync device token อื่น
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from OrderNote where OrderNoteID in ('$onOrderNoteID[0]'";
        for($i=1; $i<$countOrderNote; $i++)
        {
            $sql .= ",'$onOrderNoteID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'OrderNote';
        $action = 'i';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }


    
    
    
    //orderkitchen
    if($countOrderKitchen > 0)
    {
        for($k=0; $k<$countOrderKitchen; $k++)
        {
            //query statement
            $okOrderTakingID[$k] = $orderTakingOldNew[$okOrderTakingID[$k]];
            $sql = "INSERT INTO OrderKitchen(CustomerTableID, OrderTakingID, SequenceNo,CustomerTableIDOrder, ModifiedUser, ModifiedDate) VALUES ('$okCustomerTableID[$k]', '$okOrderTakingID[$k]', '$okSequenceNo[$k]','$okCustomerTableIDOrder[$k]', '$okModifiedUser[$k]', '$okModifiedDate[$k]')";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
            
            
            
            //insert ผ่าน
            $newID = mysqli_insert_id($con);
            
            
            
            //**********sync device token ตัวเอง delete old id and insert newID
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select $okOrderKitchenID[$k] as OrderKitchenID, 1 as ReplaceSelf, '$okModifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'OrderKitchen';
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
            $okOrderKitchenID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderKitchen where OrderKitchenID = '$okOrderKitchenID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'OrderKitchen';
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
        
        
        
        //**********sync device token อื่น
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
        $action = 'i';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    
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
    
    
    
    //receipt
    if($countReceipt > 0)
    {
        for($i=0; $i<$countReceipt; $i++)
        {
            //query statement
            $sql = "update Receipt set CustomerTableID = '$rtCustomerTableID[$i]', MemberID = '$rtMemberID[$i]', ServingPerson = '$rtServingPerson[$i]', CustomerType = '$rtCustomerType[$i]', OpenTableDate = '$rtOpenTableDate[$i]', CashAmount = '$rtCashAmount[$i]', CashReceive = '$rtCashReceive[$i]', CreditCardType = '$rtCreditCardType[$i]', CreditCardNo = '$rtCreditCardNo[$i]', CreditCardAmount = '$rtCreditCardAmount[$i]', TransferDate = '$rtTransferDate[$i]', TransferAmount = '$rtTransferAmount[$i]', Remark = '$rtRemark[$i]', DiscountType = '$rtDiscountType[$i]', DiscountAmount = '$rtDiscountAmount[$i]', DiscountReason = '$rtDiscountReason[$i]', Status = '$rtStatus[$i]', ReceiptNoID = '$receiptNoID', ReceiptNoTaxID = '$rtReceiptNoTaxID[$i]', ReceiptDate = '$rtReceiptDate[$i]', MergeReceiptID = '$rtMergeReceiptID[$i]', ModifiedUser = '$rtModifiedUser[$i]', ModifiedDate = '$rtModifiedDate[$i]' where ReceiptID = '$rtReceiptID[$i]'";
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
        $sql = "select *, 1 IdInserted from Receipt where ReceiptID in ('$rtReceiptID[0]'";
        for($i=1; $i<$countReceipt; $i++)
        {
            $sql .= ",'$rtReceiptID[$i]'";
        }
        $sql .= ")";
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
    }
    
    
    
    
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
