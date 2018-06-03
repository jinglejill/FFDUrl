<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countReceiptCustomerTable"]))
    {
        $countReceiptCustomerTable = $_POST["countReceiptCustomerTable"];
        for($i=0; $i<$countReceiptCustomerTable; $i++)
        {
            $receiptCustomerTableID[$i] = $_POST["receiptCustomerTableID".sprintf("%02d", $i)];
            $receiptID[$i] = $_POST["receiptID".sprintf("%02d", $i)];
            $customerTableID[$i] = $_POST["customerTableID".sprintf("%02d", $i)];
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
    
    
    
    if($countReceiptCustomerTable > 0)
    {
        for($k=0; $k<$countReceiptCustomerTable; $k++)
        {
            //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
            //query statement
            for($j=0;$j<$retryNo;$j++)
            {
                $sql = "INSERT INTO ReceiptCustomerTable(ReceiptCustomerTableID, ReceiptID, CustomerTableID, ModifiedUser, ModifiedDate) VALUES ('" . ($receiptCustomerTableID[$k]+$j) . "', '$receiptID[$k]', '$customerTableID[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
                $sql = "select $receiptCustomerTableID[$k] as ReceiptCustomerTableID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
                $selectedRow = getSelectedRow($sql);
                
                
                
                //broadcast ไป device token ตัวเอง
                $type = 'ReceiptCustomerTable';
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
                $receiptCustomerTableID[$k] = $receiptCustomerTableID[$k]+$j;
                $sql = "select *, 1 IdInserted from ReceiptCustomerTable where ReceiptCustomerTableID = '$receiptCustomerTableID[$k]'";
                $selectedRow = getSelectedRow($sql);
                
                
                
                //broadcast ไป device token ตัวเอง
                $type = 'ReceiptCustomerTable';
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
                $sql = "select *, 1 IdInserted from ReceiptCustomerTable where ReceiptCustomerTableID = '$receiptCustomerTableID[$k]'";
                $selectedRow = getSelectedRow($sql);
                
                
                
                //broadcast ไป device token ตัวเอง
                $type = 'ReceiptCustomerTable';
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
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from ReceiptCustomerTable where ReceiptCustomerTableID in ('$receiptCustomerTableID[0]'";
        for($i=1; $i<$countReceiptCustomerTable; $i++)
        {
            $sql .= ",'$receiptCustomerTableID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'ReceiptCustomerTable';
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
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
?>
