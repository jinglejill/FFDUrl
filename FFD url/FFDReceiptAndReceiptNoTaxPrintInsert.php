<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
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
    
    
    
    $sql = "select * from setting where KeyName = 'minimumReceiptNoTaxPerDay'";
    $selectedRow = getSelectedRow($sql);
    $minimumReceiptNoTaxPerDay = intval($selectedRow[0]["Value"]);
    
    
    $sql = "select count(*) count from receiptnotax where date_format(modifiedDate,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d')";
    $selectedRow = getSelectedRow($sql);
    $receiptNoTaxUpToNowToday = intval($selectedRow[0]["count"]);
    
//    if($receiptNoTaxUpToNowToday < $minimumReceiptNoTaxPerDay)
    //gen receiptNoTaxID ทุกเคส เพราะ อยู่ใน process print tax
    {
        //gen next receiptNoTax
        $sql = "select ReceiptNoTaxID,Year,Month,RunningNo from receiptnotax where substring(ReceiptNoTaxID,1,6) = date_format(now(),'%Y%m') order by ReceiptNoTaxID desc";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow)>0)
        {
            $maxID = $selectedRow[0]["ReceiptNoTaxID"];
            $receiptNoTaxID = $maxID+1;
            $year = $selectedRow[0]["Year"];
            $month = $selectedRow[0]["Month"];
            $runningNo = intval($selectedRow[0]["RunningNo"])+1;
        }
        else
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
        else if($j != 0)
        {
            //มีการเปลี่ยน id
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select $receiptNoTaxID as ReceiptNoTaxID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'ReceiptNoTax';
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
            $receiptNoTaxID = $receiptNoTaxID + $j;
            $sql = "select *, 1 IdInserted from ReceiptNoTax where ReceiptNoTaxID = '$receiptNoTaxID'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'ReceiptNoTax';
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
            $sql = "select *, 1 IdInserted from ReceiptNoTax where ReceiptNoTaxID = '$receiptNoTaxID'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'ReceiptNoTax';
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
        $sql = "select *, 1 IdInserted from ReceiptNoTax where ReceiptNoTaxID = '$receiptNoTaxID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'ReceiptNoTax';
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
    if($ret != "" && $ret2 != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql , 'returnID' => strval($receiptNoTaxID));
    echo json_encode($response);
    exit();
?>
