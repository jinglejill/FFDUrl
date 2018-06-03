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
            $oNOrderNoteID[$i] = $_POST["oNOrderNoteID".sprintf("%02d", $i)];
            $oNOrderTakingID[$i] = $_POST["oNOrderTakingID".sprintf("%02d", $i)];
            $oNNoteID[$i] = $_POST["oNNoteID".sprintf("%02d", $i)];
            $oNModifiedUser[$i] = $_POST["oNModifiedUser".sprintf("%02d", $i)];
            $oNModifiedDate[$i] = $_POST["oNModifiedDate".sprintf("%02d", $i)];
        }
    }
    
    //customerTable for info not insert
    if(isset($_POST["ctCustomerTableID"]) && isset($_POST["ctTableName"]) && isset($_POST["ctType"]) && isset($_POST["ctColor"]) && isset($_POST["ctZone"]) && isset($_POST["ctOrderNo"]) && isset($_POST["ctStatus"]) && isset($_POST["ctModifiedUser"]) && isset($_POST["ctModifiedDate"]))
    {
        $ctCustomerTableID = $_POST["ctCustomerTableID"];
        $ctTableName = $_POST["ctTableName"];
        $ctType = $_POST["ctType"];
        $ctColor = $_POST["ctColor"];
        $ctZone = $_POST["ctZone"];
        $ctOrderNo = $_POST["ctOrderNo"];
        $ctStatus = $_POST["ctStatus"];
        $ctModifiedUser = $_POST["ctModifiedUser"];
        $ctModifiedDate = $_POST["ctModifiedDate"];
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
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from OrderNote where OrderTakingID in (select OrderTakingID from ordertaking where customerTableID = '$ctCustomerTableID' and status = 1)";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //query statement
        $sql = "delete from ordernote where OrderTakingID in (select OrderTakingID from ordertaking where customerTableID = '$ctCustomerTableID' and status = 1)";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
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
    
    //ordertaking
    {
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from OrderTaking where customerTableID = '$ctCustomerTableID' and status = 1";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //query statement
        $sql = "delete from ordertaking where customerTableID = '$ctCustomerTableID' and status = 1";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice();
            echo json_encode($ret);
            exit();
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
            $oNOrderTakingID[$k] = $orderTakingOldNew[$oNOrderTakingID[$k]];
            $sql = "INSERT INTO OrderNote(OrderTakingID, NoteID, ModifiedUser, ModifiedDate) VALUES ('$oNOrderTakingID[$k]', '$oNNoteID[$k]', '$oNModifiedUser[$k]', '$oNModifiedDate[$k]')";
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
            $sql = "select $oNOrderNoteID[$k] as OrderNoteID, 1 as ReplaceSelf, '$oNModifiedUser[$k]' as ModifiedUser";
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
            $oNOrderNoteID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderNote where OrderNoteID = '$oNOrderNoteID[$k]'";
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
        $sql = "select *, 1 IdInserted from OrderNote where OrderNoteID in ('$oNOrderNoteID[0]'";
        for($i=1; $i<$countOrderNote; $i++)
        {
            $sql .= ",'$oNOrderNoteID[$i]'";
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
