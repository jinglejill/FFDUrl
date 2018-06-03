<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["subIngredientTypeID"]) && isset($_POST["ingredientTypeID"]) && isset($_POST["name"]) && isset($_POST["orderNo"]) && isset($_POST["status"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $subIngredientTypeID = $_POST["subIngredientTypeID"];
        $ingredientTypeID = $_POST["ingredientTypeID"];
        $name = $_POST["name"];
        $orderNo = $_POST["orderNo"];
        $status = $_POST["status"];
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
    
    
    
    //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
    //query statement
    for($j=0;$j<$retryNo;$j++)
    {
        $sql = "INSERT INTO SubIngredientType(SubIngredientTypeID, IngredientTypeID, Name, OrderNo, Status, ModifiedUser, ModifiedDate) VALUES ('" . ($subIngredientTypeID+$j) . "', '$ingredientTypeID', '$name', '$orderNo', '$status', '$modifiedUser', '$modifiedDate')";
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
        $sql = "select $subIngredientTypeID as SubIngredientTypeID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'SubIngredientType';
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
        $subIngredientTypeID = $subIngredientTypeID+$j;
        $sql = "select *, 1 IdInserted from SubIngredientType where SubIngredientTypeID = '$subIngredientTypeID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'SubIngredientType';
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
        $sql = "select *, 1 IdInserted from SubIngredientType where SubIngredientTypeID = '$subIngredientTypeID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'SubIngredientType';
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
    $sql = "select *, 1 IdInserted from SubIngredientType where SubIngredientTypeID = '$subIngredientTypeID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'SubIngredientType';
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
