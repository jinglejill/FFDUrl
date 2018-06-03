<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
//    @[inDbOrderTakingList,inDbOrderNoteList,inDbOrderKitchenList,splitOrderTakingList,splitOrderNoteList,splitOrderKitchenList, billPrintList, rewardPointList,tableTakingList,_usingReceipt];
    
    if (isset($_POST["countDotOrderTaking"]))
    {
        $countDotOrderTaking = $_POST["countDotOrderTaking"];
        for($i=0; $i<$countDotOrderTaking; $i++)
        {
            $dotOrderTakingID[$i] = $_POST["dotOrderTakingID".sprintf("%02d", $i)];
            $dotCustomerTableID[$i] = $_POST["dotCustomerTableID".sprintf("%02d", $i)];
            $dotMenuID[$i] = $_POST["dotMenuID".sprintf("%02d", $i)];
            $dotQuantity[$i] = $_POST["dotQuantity".sprintf("%02d", $i)];
            $dotSpecialPrice[$i] = $_POST["dotSpecialPrice".sprintf("%02d", $i)];
            $dotPrice[$i] = $_POST["dotPrice".sprintf("%02d", $i)];
            $dotTakeAway[$i] = $_POST["dotTakeAway".sprintf("%02d", $i)];
            $dotNoteIDListInText[$i] = $_POST["dotNoteIDListInText".sprintf("%02d", $i)];
            $dotOrderNo[$i] = $_POST["dotOrderNo".sprintf("%02d", $i)];
            $dotStatus[$i] = $_POST["dotStatus".sprintf("%02d", $i)];
            $dotReceiptID[$i] = $_POST["dotReceiptID".sprintf("%02d", $i)];
            $dotModifiedUser[$i] = $_POST["dotModifiedUser".sprintf("%02d", $i)];
            $dotModifiedDate[$i] = $_POST["dotModifiedDate".sprintf("%02d", $i)];
        }
    }
    
    if (isset($_POST["countDonOrderNote"]))
    {
        $countDonOrderNote = $_POST["countDonOrderNote"];
        for($i=0; $i<$countDonOrderNote; $i++)
        {
            $donOrderNoteID[$i] = $_POST["donOrderNoteID".sprintf("%02d", $i)];
            $donOrderTakingID[$i] = $_POST["donOrderTakingID".sprintf("%02d", $i)];
            $donNoteID[$i] = $_POST["donNoteID".sprintf("%02d", $i)];
            $donModifiedUser[$i] = $_POST["donModifiedUser".sprintf("%02d", $i)];
            $donModifiedDate[$i] = $_POST["donModifiedDate".sprintf("%02d", $i)];
        }
    }
    
    if (isset($_POST["countDokOrderKitchen"]))
    {
        $countDokOrderKitchen = $_POST["countDokOrderKitchen"];
        for($i=0; $i<$countDokOrderKitchen; $i++)
        {
            $dokOrderKitchenID[$i] = $_POST["dokOrderKitchenID".sprintf("%02d", $i)];
            $dokCustomerTableID[$i] = $_POST["dokCustomerTableID".sprintf("%02d", $i)];
            $dokOrderTakingID[$i] = $_POST["dokOrderTakingID".sprintf("%02d", $i)];
            $dokSequenceNo[$i] = $_POST["dokSequenceNo".sprintf("%02d", $i)];
            $dokCustomerTableIDOrder[$i] = $_POST["dokCustomerTableIDOrder".sprintf("%02d", $i)];
            $dokModifiedUser[$i] = $_POST["dokModifiedUser".sprintf("%02d", $i)];
            $dokModifiedDate[$i] = $_POST["dokModifiedDate".sprintf("%02d", $i)];
        }
    }
    
    if (isset($_POST["countSotOrderTaking"]))
    {
        $countSotOrderTaking = $_POST["countSotOrderTaking"];
        for($i=0; $i<$countSotOrderTaking; $i++)
        {
            $sotOrderTakingID[$i] = $_POST["sotOrderTakingID".sprintf("%02d", $i)];
            $sotCustomerTableID[$i] = $_POST["sotCustomerTableID".sprintf("%02d", $i)];
            $sotMenuID[$i] = $_POST["sotMenuID".sprintf("%02d", $i)];
            $sotQuantity[$i] = $_POST["sotQuantity".sprintf("%02d", $i)];
            $sotSpecialPrice[$i] = $_POST["sotSpecialPrice".sprintf("%02d", $i)];
            $sotPrice[$i] = $_POST["sotPrice".sprintf("%02d", $i)];
            $sotTakeAway[$i] = $_POST["sotTakeAway".sprintf("%02d", $i)];
            $sotNoteIDListInText[$i] = $_POST["sotNoteIDListInText".sprintf("%02d", $i)];
            $sotOrderNo[$i] = $_POST["sotOrderNo".sprintf("%02d", $i)];
            $sotStatus[$i] = $_POST["sotStatus".sprintf("%02d", $i)];
            $sotReceiptID[$i] = $_POST["sotReceiptID".sprintf("%02d", $i)];
            $sotModifiedUser[$i] = $_POST["sotModifiedUser".sprintf("%02d", $i)];
            $sotModifiedDate[$i] = $_POST["sotModifiedDate".sprintf("%02d", $i)];
        }
    }
    
    if (isset($_POST["countSonOrderNote"]))
    {
        $countSonOrderNote = $_POST["countSonOrderNote"];
        for($i=0; $i<$countSonOrderNote; $i++)
        {
            $sonOrderNoteID[$i] = $_POST["sonOrderNoteID".sprintf("%02d", $i)];
            $sonOrderTakingID[$i] = $_POST["sonOrderTakingID".sprintf("%02d", $i)];
            $sonNoteID[$i] = $_POST["sonNoteID".sprintf("%02d", $i)];
            $sonModifiedUser[$i] = $_POST["sonModifiedUser".sprintf("%02d", $i)];
            $sonModifiedDate[$i] = $_POST["sonModifiedDate".sprintf("%02d", $i)];
        }
    }
    
    if (isset($_POST["countSokOrderKitchen"]))
    {
        $countSokOrderKitchen = $_POST["countSokOrderKitchen"];
        for($i=0; $i<$countSokOrderKitchen; $i++)
        {
            $sokOrderKitchenID[$i] = $_POST["sokOrderKitchenID".sprintf("%02d", $i)];
            $sokCustomerTableID[$i] = $_POST["sokCustomerTableID".sprintf("%02d", $i)];
            $sokOrderTakingID[$i] = $_POST["sokOrderTakingID".sprintf("%02d", $i)];
            $sokSequenceNo[$i] = $_POST["sokSequenceNo".sprintf("%02d", $i)];
            $sokCustomerTableIDOrder[$i] = $_POST["sokCustomerTableIDOrder".sprintf("%02d", $i)];
            $sokModifiedUser[$i] = $_POST["sokModifiedUser".sprintf("%02d", $i)];
            $sokModifiedDate[$i] = $_POST["sokModifiedDate".sprintf("%02d", $i)];
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

    if (isset($_POST["countBillPrint"]))
    {
        $countBillPrint = $_POST["countBillPrint"];
        for($i=0; $i<$countBillPrint; $i++)
        {
            $bpBillPrintID[$i] = $_POST["bpBillPrintID".sprintf("%02d", $i)];
            $bpReceiptID[$i] = $_POST["bpReceiptID".sprintf("%02d", $i)];
            $bpBillPrintDate[$i] = $_POST["bpBillPrintDate".sprintf("%02d", $i)];
            $bpModifiedUser[$i] = $_POST["bpModifiedUser".sprintf("%02d", $i)];
            $bpModifiedDate[$i] = $_POST["bpModifiedDate".sprintf("%02d", $i)];
        }
    }
    
    if (isset($_POST["countRewardPoint"]))
    {
        $countRewardPoint = $_POST["countRewardPoint"];
        for($i=0; $i<$countRewardPoint; $i++)
        {
            $rpRewardPointID[$i] = $_POST["rpRewardPointID".sprintf("%02d", $i)];
            $rpMemberID[$i] = $_POST["rpMemberID".sprintf("%02d", $i)];
            $rpReceiptID[$i] = $_POST["rpReceiptID".sprintf("%02d", $i)];
            $rpPoint[$i] = $_POST["rpPoint".sprintf("%02d", $i)];
            $rpStatus[$i] = $_POST["rpStatus".sprintf("%02d", $i)];
            $rpModifiedUser[$i] = $_POST["rpModifiedUser".sprintf("%02d", $i)];
            $rpModifiedDate[$i] = $_POST["rpModifiedDate".sprintf("%02d", $i)];
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
    
    
    
    //receiptNoTax
    {
        $sql = "select * from setting where KeyName = 'minimumReceiptNoTaxPerDay'";
        $selectedRow = getSelectedRow($sql);
        $minimumReceiptNoTaxPerDay = intval($selectedRow[0]["Value"]);
        
        
        $sql = "select count(*) count from receiptnotax where date_format(modifiedDate,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d')";
        $selectedRow = getSelectedRow($sql);
        $receiptNoTaxUpToNowToday = intval($selectedRow[0]["count"]);
        
        if($receiptNoTaxUpToNowToday < $minimumReceiptNoTaxPerDay)
        {
            //gen next receiptNoTax
            $sql = "select ReceiptNoTaxID,Year,Month,RunningNo from receiptnotax where substring(ReceiptNoTaxID,1,6) = date_format(now(),'%Y%m') order by ReceiptNoTaxID desc";
            $selectedRow = getSelectedRow($sql);
            if(sizeof($selectedRow)>0)//ในเดือนปัจจุบันมีออกใบกำกับแล้ว
            {
                $maxID = $selectedRow[0]["ReceiptNoTaxID"];
                $receiptNoTaxID = $maxID+1;
                $year = $selectedRow[0]["Year"];
                $month = $selectedRow[0]["Month"];
                $runningNo = intval($selectedRow[0]["RunningNo"])+1;
            }
            else//ในเดือนปัจจุบันยังไม่มีออกใบกำกับ
            {
                $sql = "select concat(date_format(now(),'%Y%m'),'000001') NextID, date_format(now(),'%Y') Year, date_format(now(),'%m') Month";
                $selectedRow = getSelectedRow($sql);
                $receiptNoTaxID = $selectedRow[0]['NextID'];
                $year = $selectedRow[0]["Year"];
                $month = $selectedRow[0]["Month"];
                $runningNo = 1;
            }
            
            
            //part receiptnotax
            //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
            //query statement
            for($j=0;$j<$retryNo;$j++)
            {
                $sql = "INSERT INTO ReceiptNoTax(ReceiptNoTaxID, Year, Month, RunningNo, ModifiedUser, ModifiedDate) VALUES ('" . ($receiptNoTaxID+$j) . "', '$year', '$month', '$runningNo', '$modifiedUser', '$modifiedDate')";
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
            else
            {
                //select row ที่แก้ไข ขึ้นมาเก็บไว้
                $receiptNoTaxID = $receiptNoTaxID + $j;
            }
            //-----
        }
    }
    
    
    //Receipt
    $receiptIDOld = $receiptID;
    {
        //query statement
        $sql = "INSERT INTO Receipt(CustomerTableID, MemberID, ServingPerson, CustomerType, OpenTableDate, CashAmount, CashReceive, CreditCardType, CreditCardNo, CreditCardAmount, TransferDate, TransferAmount, Remark, DiscountType, DiscountAmount, DiscountReason, Status, ReceiptNoID, ReceiptNoTaxID, ReceiptDate, MergeReceiptID, ModifiedUser, ModifiedDate) VALUES ('$customerTableID', '$memberID', '$servingPerson', '$customerType', '$openTableDate', '$cashAmount', '$cashReceive', '$creditCardType', '$creditCardNo', '$creditCardAmount', '$transferDate', '$transferAmount', '$remark', '$discountType', '$discountAmount', '$discountReason', '$status', '$receiptNoID', '$receiptNoTaxID', '$receiptDate', '$mergeReceiptID', '$modifiedUser', '$modifiedDate')";
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
        
        
        
        //device ตัวเอง ลบแล้ว insert
        //sync generated id back to app
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select $receiptID as ReceiptID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'Receipt';
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
        $receiptID = $newID;
        $sql = "select *, 1 IdInserted from Receipt where ReceiptID = '$receiptID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'Receipt';
        $action = 'i';
        $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        
        //****device อื่น insert
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from Receipt where ReceiptID = '$receiptID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'Receipt';
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
    }
    
    
    
    //billPrint
    if($countBillPrint > 0)
    {
        //query statement
        $sql = "delete from BillPrint where receiptID = '$receiptIDOld'";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }

        //-----
        
        
        for($k=0; $k<$countBillPrint; $k++)
        {
            //query statement
            $sql = "INSERT INTO BillPrint(ReceiptID, BillPrintDate, ModifiedUser, ModifiedDate) VALUES ('$receiptID', '$bpBillPrintDate[$k]', '$bpModifiedUser[$k]', '$bpModifiedDate[$k]')";
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
            $sql = "select $bpBillPrintID[$k] as BillPrintID, 1 as ReplaceSelf, '$bpModifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'BillPrint';
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
            $bpBillPrintID[$k] = $newID;
            $sql = "select *, 1 IdInserted from BillPrint where BillPrintID = '$bpBillPrintID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'BillPrint';
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
        $sql = "select *, 1 IdInserted from BillPrint where BillPrintID in ('$bpBillPrintID[0]'";
        for($i=1; $i<$countBillPrint; $i++)
        {
            $sql .= ",'$bpBillPrintID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'BillPrint';
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
    
    
    //delete ordertakingList
    if($countDotOrderTaking > 0)
    {
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from OrderTaking where OrderTakingID in ('$dotOrderTakingID[0]'";
        for($i=1; $i<$countDotOrderTaking; $i++)
        {
            $sql .= ",'$dotOrderTakingID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        for($i=0; $i<$countDotOrderTaking; $i++)
        {
            //query statement
            $sql = "delete from OrderTaking where OrderTakingID in ('$dotOrderTakingID[0]'";
            for($i=1; $i<$countDotOrderTaking; $i++)
            {
                $sql .= ",'$dotOrderTakingID[$i]'";
            }
            $sql .= ")";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        //broadcast ไป device token อื่น
        $type = 'OrderTaking';
        $action = 'd';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    //delete orderNote
    if($countDonOrderNote > 0)
    {
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from OrderNote where OrderNoteID in ('$donOrderNoteID[0]'";
        for($i=1; $i<$countDonOrderNote; $i++)
        {
            $sql .= ",'$donOrderNoteID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        for($i=0; $i<$countDonOrderNote; $i++)
        {
            //query statement
            $sql = "delete from OrderNote where OrderNoteID in ('$donOrderNoteID[0]'";
            for($i=1; $i<$countDonOrderNote; $i++)
            {
                $sql .= ",'$donOrderNoteID[$i]'";
            }
            $sql .= ")";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        //broadcast ไป device token อื่น
        $type = 'OrderNote';
        $action = 'd';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    //delete orderKitchenList
    if($countDokOrderKitchen > 0)
    {
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from OrderKitchen where OrderKitchenID in ('$dokOrderKitchenID[0]'";
        for($i=1; $i<$countDokOrderKitchen; $i++)
        {
            $sql .= ",'$dokOrderKitchenID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        for($i=0; $i<$countDokOrderKitchen; $i++)
        {
            //query statement
            $sql = "delete from OrderKitchen where OrderKitchenID in ('$dokOrderKitchenID[0]'";
            for($i=1; $i<$countDokOrderKitchen; $i++)
            {
                $sql .= ",'$dokOrderKitchenID[$i]'";
            }
            $sql .= ")";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        //broadcast ไป device token อื่น
        $type = 'OrderKitchen';
        $action = 'd';
        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    //insert orderTakingList
    $orderTakingOldNew = array();
    if($countSotOrderTaking > 0)
    {
        for($k=0; $k<$countSotOrderTaking; $k++)
        {
            //query statement
            if($sotReceiptID[$k] == $receiptIDOld)
            {
                $sotReceiptID[$k] = $receiptID;
            }
            $sql = "INSERT INTO OrderTaking(CustomerTableID, MenuID, Quantity, SpecialPrice, Price, TakeAway, NoteIDListInText, OrderNo, Status, ReceiptID, ModifiedUser, ModifiedDate) VALUES ('$sotCustomerTableID[$k]', '$sotMenuID[$k]', '$sotQuantity[$k]', '$sotSpecialPrice[$k]', '$sotPrice[$k]', '$sotTakeAway[$k]', '$sotNoteIDListInText[$k]', '$sotOrderNo[$k]', '$sotStatus[$k]', '$sotReceiptID[$k]', '$sotModifiedUser[$k]', '$sotModifiedDate[$k]')";
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
            $sql = "select $sotOrderTakingID[$k] as OrderTakingID, 1 as ReplaceSelf, '$sotModifiedUser[$k]' as ModifiedUser";
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
            $sotOrderTakingID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$sotOrderTakingID[$k]'";
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
        
        
        
        //**********sync device token อื่น
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID in ('$sotOrderTakingID[0]'";
        for($i=1; $i<$countSotOrderTaking; $i++)
        {
            $sql .= ",'$sotOrderTakingID[$i]'";
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
    
    
    //insert orderNoteList
    if($countSonOrderNote > 0)
    {
        for($k=0; $k<$countSonOrderNote; $k++)
        {
            //query statement
            $sonOrderTakingID[$k] = $orderTakingOldNew[$sonOrderTakingID[$k]];
            $sql = "INSERT INTO OrderNote(OrderTakingID, NoteID, ModifiedUser, ModifiedDate) VALUES ('$sonOrderTakingID[$k]', '$sonNoteID[$k]', '$sonModifiedUser[$k]', '$sonModifiedDate[$k]')";
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
            $sql = "select $sonOrderNoteID[$k] as OrderNoteID, 1 as ReplaceSelf, '$sonModifiedUser[$k]' as ModifiedUser";
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
            $sonOrderNoteID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderNote where OrderNoteID = '$sonOrderNoteID[$k]'";
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
        $sql = "select *, 1 IdInserted from OrderNote where OrderNoteID in ('$sonOrderNoteID[0]'";
        for($i=1; $i<$countSonOrderNote; $i++)
        {
            $sql .= ",'$sonOrderNoteID[$i]'";
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
    
    
    //insert orderKitchenList
    if($countSokOrderKitchen > 0)
    {
        for($k=0; $k<$countSokOrderKitchen; $k++)
        {
            //query statement
            $sokOrderTakingID[$k] = $orderTakingOldNew[$sokOrderTakingID[$k]];
            $sql = "INSERT INTO OrderKitchen(CustomerTableID, OrderTakingID, SequenceNo, CustomerTableIDOrder, ModifiedUser, ModifiedDate) VALUES ('$sokCustomerTableID[$k]', '$sokOrderTakingID[$k]', '$sokSequenceNo[$k]', '$sokCustomerTableIDOrder[$k]', '$sokModifiedUser[$k]', '$sokModifiedDate[$k]')";
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
            $sql = "select $sokOrderKitchenID[$k] as OrderKitchenID, 1 as ReplaceSelf, '$sokModifiedUser[$k]' as ModifiedUser";
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
            $sokOrderKitchenID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderKitchen where OrderKitchenID = '$sokOrderKitchenID[$k]'";
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
        $sql = "select *, 1 IdInserted from OrderKitchen where OrderKitchenID in ('$sokOrderKitchenID[0]'";
        for($i=1; $i<$countSokOrderKitchen; $i++)
        {
            $sql .= ",'$sokOrderKitchenID[$i]'";
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
    
    
    //rewardPoint
    if($countRewardPoint > 0)
    {
        for($k=0; $k<$countRewardPoint; $k++)
        {
            //check pointUse <= pointRemaining
            if($rpStatus[$k] == -1)
            {
                $sql = @"SELECT ifnull(sum(Point*Status),0) PointRemaining FROM `rewardpoint` WHERE memberID = 0;";
                $selectedRow = getSelectedRow($sql);
                $pointRemaining = $selectedRow[0]["PointRemaining"];
                if($rpPoint[$k] > $pointRemaining)
                {
                    alertToDevice("แต้มสะสมไม่เพียงพอ",$_POST["modifiedDeviceToken"]);
                    echo json_encode($ret);
                    exit();
                }
            }
            
            
            //query statement
            $sql = "INSERT INTO RewardPoint(MemberID, ReceiptID, Point, Status, ModifiedUser, ModifiedDate) VALUES ('$rpMemberID[$k]', '$receiptID', '$rpPoint[$k]', '$rpStatus[$k]', '$rpModifiedUser[$k]', '$rpModifiedDate[$k]')";
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
            $sql = "select $rpRewardPointID[$k] as RewardPointID, 1 as ReplaceSelf, '$rpModifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'RewardPoint';
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
            $rpRewardPointID[$k] = $newID;
            $sql = "select *, 1 IdInserted from RewardPoint where RewardPointID = '$rpRewardPointID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'RewardPoint';
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
        $sql = "select *, 1 IdInserted from RewardPoint where RewardPointID in ('$rpRewardPointID[0]'";
        for($i=1; $i<$countRewardPoint; $i++)
        {
            $sql .= ",'$rpRewardPointID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'RewardPoint';
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
    
    


    
    
    //do script successful
    //delete and insert ตัวเอง, insert คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql,'returnID' => $receiptNoID);
    echo json_encode($response);
    exit();
?>
