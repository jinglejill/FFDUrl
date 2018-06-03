<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    $dbName = $_POST["dbName"];
    
    
    if (isset($_POST["countReceiptPrint"]))
    {
        $countReceiptPrint = $_POST["countReceiptPrint"];
        for($i=0; $i<$countReceiptPrint; $i++)
        {
            $receiptPrintID[$i] = $_POST["receiptPrintID".sprintf("%02d", $i)];
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
    
    
    
    if($countReceiptPrint > 0)
    {
        for($k=0; $k<$countReceiptPrint; $k++)
        {
            //query statement
            $sql = "INSERT INTO ReceiptPrint(ReceiptID, ModifiedUser, ModifiedDate) VALUES ('$receiptID[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
            $sql = "select $receiptPrintID[$k] as ReceiptPrintID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'ReceiptPrint';
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
            $receiptPrintID[$k] = $newID;
            $sql = "select *, 1 IdInserted from ReceiptPrint where ReceiptPrintID = '$receiptPrintID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'ReceiptPrint';
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
        $sql = "select *, 1 IdInserted from ReceiptPrint where ReceiptPrintID in ('$receiptPrintID[0]'";
        for($i=1; $i<$countReceiptPrint; $i++)
        {
            $sql .= ",'$receiptPrintID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'ReceiptPrint';
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
    
    
    //update receipt status at JUMMUM
    $sql = "select * from FFD.Branch where dbName = '$dbName';";
    $selectedRow = getSelectedRow($sql);
    $branchID = $selectedRow[0]["BranchID"];
    
    
    if($countReceiptPrint > 0)
    {        
        $sql = "update JUMMUM2.receipt set status = 5, statusRoute = concat(statusRoute,',','5'), modifiedDate='$modifiedDate[0]', modifiedUser='$modifiedUser[0]' where branchID = '$branchID' and receiptID in ('$receiptID[0]'";
        for($k=1; $k<$countReceiptPrint; $k++)
        {
            $sql .= ",'$receiptID[$k]'";
        }
        $sql .= ")";
    }
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
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
