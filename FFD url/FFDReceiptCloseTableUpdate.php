<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    

//    @[billPrintList,rewardPointList,tableTakingList,allOrderTakingList,Receipt];
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
    {
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
    }
    
    
    
    //billPrint
    if($countBillPrint > 0)
    {
        //query statement
        $sql = "delete from BillPrint where receiptID = '$receiptID'";
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
    
    


    //tabletaking
    if($countTableTaking > 0)
    {
        for($i=0; $i<$countTableTaking; $i++)
        {
            //query statement
            $sql = "update TableTaking set CustomerTableID = '$ttCustomerTableID[$i]', ServingPerson = '$ttServingPerson[$i]', ReceiptID = '$receiptID', ModifiedUser = '$ttModifiedUser[$i]', ModifiedDate = '$ttModifiedDate[$i]' where TableTakingID = '$ttTableTakingID[$i]'";
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
        $ret2 = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "" && $ret2 != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    //ordertaking
    if($countOrderTaking > 0)
    {
        for($i=0; $i<$countOrderTaking; $i++)
        {
            //query statement
            $sql = "update OrderTaking set CustomerTableID = '$otCustomerTableID[$i]', MenuID = '$otMenuID[$i]', Quantity = '$otQuantity[$i]', SpecialPrice = '$otSpecialPrice[$i]', Price = '$otPrice[$i]', TakeAway = '$otTakeAway[$i]', NoteIDListInText = '$otNoteIDListInText[$i]', OrderNo = '$otOrderNo[$i]', Status = '$otStatus[$i]', ReceiptID = '$receiptID', ModifiedUser = '$otModifiedUser[$i]', ModifiedDate = '$otModifiedDate[$i]' where OrderTakingID = '$otOrderTakingID[$i]'";
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
        $ret2 = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "" && $ret2 != "")
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
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
