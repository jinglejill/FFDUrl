<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countIngredient"]))
    {
        $countIngredient = $_POST["countIngredient"];
        for($i=0; $i<$countIngredient; $i++)
        {
            $ingredientID[$i] = $_POST["ingredientID".sprintf("%02d", $i)];
            $ingredientTypeID[$i] = $_POST["ingredientTypeID".sprintf("%02d", $i)];
            $subIngredientTypeID[$i] = $_POST["subIngredientTypeID".sprintf("%02d", $i)];
            $name[$i] = $_POST["name".sprintf("%02d", $i)];
            $uom[$i] = $_POST["uom".sprintf("%02d", $i)];
            $uomSmall[$i] = $_POST["uomSmall".sprintf("%02d", $i)];
            $smallAmount[$i] = $_POST["smallAmount".sprintf("%02d", $i)];
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
    
    
    
    if($countIngredient > 0)
    {
        for($i=0; $i<$countIngredient; $i++)
        {
            //query statement
            $sql = "update Ingredient set IngredientTypeID = '$ingredientTypeID[$i]', SubIngredientTypeID = '$subIngredientTypeID[$i]', Name = '$name[$i]', Uom = '$uom[$i]', UomSmall = '$uomSmall[$i]', SmallAmount = '$smallAmount[$i]', OrderNo = '$orderNo[$i]', Status = '$status[$i]', ModifiedUser = '$modifiedUser[$i]', ModifiedDate = '$modifiedDate[$i]' where IngredientID = '$ingredientID[$i]'";
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
        $sql = "select *, 1 IdInserted from Ingredient where IngredientID in ('$ingredientID[0]'";
        for($i=1; $i<$countIngredient; $i++)
        {
            $sql .= ",'$ingredientID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'Ingredient';
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
