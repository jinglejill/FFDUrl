<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countIngredientReceive"]))
    {
        $countIngredientReceive = $_POST["countIngredientReceive"];
        for($i=0; $i<$countIngredientReceive; $i++)
        {
            $ingredientReceiveID[$i] = $_POST["ingredientReceiveID".sprintf("%02d", $i)];
            $ingredientID[$i] = $_POST["ingredientID".sprintf("%02d", $i)];
            $amount[$i] = $_POST["amount".sprintf("%02d", $i)];
            $amountSmall[$i] = $_POST["amountSmall".sprintf("%02d", $i)];
            $price[$i] = $_POST["price".sprintf("%02d", $i)];
            $receiveDate[$i] = $_POST["receiveDate".sprintf("%02d", $i)];
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
    
    
    
    if($countIngredientReceive > 0)
    {
        for($i=0; $i<$countIngredientReceive; $i++)
        {
            //query statement
            $sql = "update IngredientReceive set IngredientID = '$ingredientID[$i]', Amount = '$amount[$i]', AmountSmall = '$amountSmall[$i]', Price = '$price[$i]', ReceiveDate = '$receiveDate[$i]', ModifiedUser = '$modifiedUser[$i]', ModifiedDate = '$modifiedDate[$i]' where IngredientReceiveID = '$ingredientReceiveID[$i]'";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
//        //select row ที่แก้ไข ขึ้นมาเก็บไว้
//        $sql = "select *, 1 IdInserted from IngredientReceive where IngredientReceiveID in ('$ingredientReceiveID[0]'";
//        for($i=1; $i<$countIngredientReceive; $i++)
//        {
//            $sql .= ",'$ingredientReceiveID[$i]'";
//        }
//        $sql .= ")";
//        $selectedRow = getSelectedRow($sql);
//        
//        
//        
//        //broadcast ไป device token อื่น
//        $type = 'IngredientReceive';
//        $action = 'u';
//        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//        if($ret != "")
//        {
//            putAlertToDevice();
//            echo json_encode($ret);
//            exit();
//        }
    }
    
    
    
    //do script successful
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
//    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql);
    echo json_encode($response);
    exit();
    
    

?>
