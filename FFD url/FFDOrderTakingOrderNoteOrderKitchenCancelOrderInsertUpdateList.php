<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    //order update
    if(isset($_POST["moOrderTakingID"]) && isset($_POST["moCustomerTableID"]) && isset($_POST["moMenuID"]) && isset($_POST["moQuantity"]) && isset($_POST["moSpecialPrice"]) && isset($_POST["moPrice"]) && isset($_POST["moTakeAway"]) && isset($_POST["moNoteIDListInText"]) && isset($_POST["moOrderNo"]) && isset($_POST["moStatus"]) && isset($_POST["moReceiptID"]) && isset($_POST["moModifiedUser"]) && isset($_POST["moModifiedDate"]))
    {
        $moOrderTakingID = $_POST["moOrderTakingID"];
        $moCustomerTableID = $_POST["moCustomerTableID"];
        $moMenuID = $_POST["moMenuID"];
        $moQuantity = $_POST["moQuantity"];
        $moSpecialPrice = $_POST["moSpecialPrice"];
        $moPrice = $_POST["moPrice"];
        $moTakeAway = $_POST["moTakeAway"];
        $moNoteIDListInText = $_POST["moNoteIDListInText"];
        $moOrderNo = $_POST["moOrderNo"];
        $moStatus = $_POST["moStatus"];
        $moReceiptID = $_POST["moReceiptID"];
        $moModifiedUser = $_POST["moModifiedUser"];
        $moModifiedDate = $_POST["moModifiedDate"];
    }
    
    if(isset($_POST["otOrderTakingID"]) && isset($_POST["otCustomerTableID"]) && isset($_POST["otMenuID"]) && isset($_POST["otQuantity"]) && isset($_POST["otSpecialPrice"]) && isset($_POST["otPrice"]) && isset($_POST["otTakeAway"]) && isset($_POST["otNoteIDListInText"]) && isset($_POST["otOrderNo"]) && isset($_POST["otStatus"]) && isset($_POST["otReceiptID"]) && isset($_POST["otModifiedUser"]) && isset($_POST["otModifiedDate"]))
    {
        $otOrderTakingID = $_POST["otOrderTakingID"];
        $otCustomerTableID = $_POST["otCustomerTableID"];
        $otMenuID = $_POST["otMenuID"];
        $otQuantity = $_POST["otQuantity"];
        $otSpecialPrice = $_POST["otSpecialPrice"];
        $otPrice = $_POST["otPrice"];
        $otTakeAway = $_POST["otTakeAway"];
        $otNoteIDListInText = $_POST["otNoteIDListInText"];
        $otOrderNo = $_POST["otOrderNo"];
        $otStatus = $_POST["otStatus"];
        $otReceiptID = $_POST["otReceiptID"];
        $otModifiedUser = $_POST["otModifiedUser"];
        $otModifiedDate = $_POST["otModifiedDate"];
    }
    
    if(isset($_POST["okOrderKitchenID"]) && isset($_POST["okCustomerTableID"]) && isset($_POST["okOrderTakingID"]) && isset($_POST["okSequenceNo"]) && isset($_POST["okModifiedUser"]) && isset($_POST["okModifiedDate"]))
    {
        $okOrderKitchenID = $_POST["okOrderKitchenID"];
        $okCustomerTableID = $_POST["okCustomerTableID"];
        $okOrderTakingID = $_POST["okOrderTakingID"];
        $okSequenceNo = $_POST["okSequenceNo"];
        $okCustomerTableIDOrder = $_POST["okCustomerTableIDOrder"];
        $okModifiedUser = $_POST["okModifiedUser"];
        $okModifiedDate = $_POST["okModifiedDate"];
    }
    
    if (isset($_POST["countOrderNote"]))
    {
        $countOrderNote = $_POST["countOrderNote"];
        for($i=0; $i<$countOrderNote; $i++)
        {
            $orderNoteID[$i] = $_POST["orderNoteID".sprintf("%02d", $i)];
            $orderTakingID[$i] = $_POST["orderTakingID".sprintf("%02d", $i)];
            $noteID[$i] = $_POST["noteID".sprintf("%02d", $i)];
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
    
    
    //ordertaking update
    //query statement
    $sql = "update OrderTaking set CustomerTableID = '$moCustomerTableID', MenuID = '$moMenuID', Quantity = '$moQuantity', SpecialPrice = '$moSpecialPrice', Price = '$moPrice', TakeAway = '$moTakeAway', NoteIDListInText = '$moNoteIDListInText', OrderNo = '$moOrderNo', Status = '$moStatus', ReceiptID = '$moReceiptID', ModifiedUser = '$moModifiedUser', ModifiedDate = '$moModifiedDate' where OrderTakingID = '$moOrderTakingID'";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$moOrderTakingID'";
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
    //-----
    
    
    
    
    

    //ordertaking insert
    {
        //query statement
        $sql = "INSERT INTO OrderTaking(CustomerTableID, MenuID, Quantity, SpecialPrice, Price, TakeAway, NoteIDListInText, OrderNo, Status, ReceiptID, ModifiedUser, ModifiedDate) VALUES ('$otCustomerTableID', '$otMenuID', '$otQuantity', '$otSpecialPrice', '$otPrice', '$otTakeAway', '$otNoteIDListInText', '$otOrderNo', '$otStatus', '$otReceiptID', '$otModifiedUser', '$otModifiedDate')";
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
        $sql = "select $otOrderTakingID as OrderTakingID, 1 as ReplaceSelf, '$otModifiedUser' as ModifiedUser";
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
        $otOrderTakingID = $newID;
        $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$otOrderTakingID'";
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
    
    
    
    
        //****device อื่น insert
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from OrderTaking where OrderTakingID = '$otOrderTakingID'";
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
    //-----



    //orderkitchen insert
    {
        //query statement
        $sql = "INSERT INTO OrderKitchen(CustomerTableID, OrderTakingID, SequenceNo,CustomerTableIDOrder, ModifiedUser, ModifiedDate) VALUES ('$okCustomerTableID', '$otOrderTakingID', '$okSequenceNo','$okCustomerTableIDOrder', '$okModifiedUser', '$okModifiedDate')";
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
        $sql = "select $okOrderKitchenID as OrderKitchenID, 1 as ReplaceSelf, '$okModifiedUser' as ModifiedUser";
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
        $okOrderKitchenID = $newID;
        $sql = "select *, 1 IdInserted from OrderKitchen where OrderKitchenID = '$okOrderKitchenID'";
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
    
    
    
    
        //****device อื่น insert
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from OrderKitchen where OrderKitchenID = '$okOrderKitchenID'";
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
    //-----
    
    
    
    
    //orderNote insert
    if($countOrderNote > 0)
    {
        
        for($k=0; $k<$countOrderNote; $k++)
        {
            //query statement
            $sql = "INSERT INTO OrderNote(OrderTakingID, NoteID, ModifiedUser, ModifiedDate) VALUES ('$otOrderTakingID', '$noteID[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
            $sql = "select $orderNoteID[$k] as OrderNoteID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
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
            $orderNoteID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderNote where OrderNoteID = '$orderNoteID[$k]'";
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
        
        
        
        //****device อื่น insert
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from OrderNote where OrderNoteID in ('$orderNoteID[0]'";
        for($i=1; $i<$countOrderNote; $i++)
        {
            $sql .= ",'$orderNoteID[$i]'";
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
