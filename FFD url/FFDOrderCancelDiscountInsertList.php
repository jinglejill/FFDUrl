<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countOrderCancelDiscount"]))
    {
        $countOrderCancelDiscount = $_POST["countOrderCancelDiscount"];
        for($i=0; $i<$countOrderCancelDiscount; $i++)
        {
            $orderCancelDiscountID[$i] = $_POST["orderCancelDiscountID".sprintf("%02d", $i)];
            $orderTakingID[$i] = $_POST["orderTakingID".sprintf("%02d", $i)];
            $type[$i] = $_POST["type".sprintf("%02d", $i)];
            $discountType[$i] = $_POST["discountType".sprintf("%02d", $i)];
            $discountAmount[$i] = $_POST["discountAmount".sprintf("%02d", $i)];
            $reason[$i] = $_POST["reason".sprintf("%02d", $i)];
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
    
    
    
    if($countOrderCancelDiscount > 0)
    {
        for($k=0; $k<$countOrderCancelDiscount; $k++)
        {
            //query statement
            $sql = "INSERT INTO OrderCancelDiscount(OrderTakingID, Type, DiscountType, DiscountAmount, Reason, ModifiedUser, ModifiedDate) VALUES ('$orderTakingID[$k]', '$type[$k]', '$discountType[$k]', '$discountAmount[$k]', '$reason[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
            $sql = "select $orderCancelDiscountID[$k] as OrderCancelDiscountID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'OrderCancelDiscount';
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
            $orderCancelDiscountID[$k] = $newID;
            $sql = "select *, 1 IdInserted from OrderCancelDiscount where OrderCancelDiscountID = '$orderCancelDiscountID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'OrderCancelDiscount';
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
        $sql = "select *, 1 IdInserted from OrderCancelDiscount where OrderCancelDiscountID in ('$orderCancelDiscountID[0]'";
        for($i=1; $i<$countOrderCancelDiscount; $i++)
        {
            $sql .= ",'$orderCancelDiscountID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'OrderCancelDiscount';
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
