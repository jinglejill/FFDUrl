<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countSubIngredientType"]))
    {
        $countSubIngredientType = $_POST["countSubIngredientType"];
        for($i=0; $i<$countSubIngredientType; $i++)
        {
            $subIngredientTypeID[$i] = $_POST["subIngredientTypeID".sprintf("%02d", $i)];
            $ingredientTypeID[$i] = $_POST["ingredientTypeID".sprintf("%02d", $i)];
            $name[$i] = $_POST["name".sprintf("%02d", $i)];
            $orderNo[$i] = $_POST["orderNo".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
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
    
    
    
    if($countSubIngredientType > 0)
    {
        for($i=0; $i<$countSubIngredientType; $i++)
        {
            //query statement
            $sql = "update SubIngredientType set IngredientTypeID = '$ingredientTypeID[$i]', Name = '$name[$i]', OrderNo = '$orderNo[$i]', Status = '$status[$i]', ModifiedUser = '$modifiedUser[$i]', ModifiedDate = '$modifiedDate[$i]' where SubIngredientTypeID = '$subIngredientTypeID[$i]'";
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
        $sql = "select *, 1 IdInserted from SubIngredientType where SubIngredientTypeID in ('$subIngredientTypeID[0]'";
        for($i=1; $i<$countSubIngredientType; $i++)
        {
            $sql .= ",'$subIngredientTypeID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'SubIngredientType';
        $action = 'u';
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
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
    
    

?>
