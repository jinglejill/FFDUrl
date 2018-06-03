<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    

//    @[billPrintList,Receipt];
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
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    
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
