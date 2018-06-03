<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    if(isset($_POST["historyStartDate"]) && isset($_POST["historyEndDate"]))
    {
        $historyStartDate = $_POST["historyStartDate"];
        $historyEndDate = $_POST["historyEndDate"];
    }

    
    if (isset($_POST["countIngredientReceive"]))
    {
        $countIngredientReceive = $_POST["countIngredientReceive"];
        for($i=0; $i<$countIngredientReceive; $i++)
        {
            $ingredientReceiveID[$i] = $_POST["ingredientReceiveID".sprintf("%03d", $i)];
            $ingredientID[$i] = $_POST["ingredientID".sprintf("%03d", $i)];
            $amount[$i] = $_POST["amount".sprintf("%03d", $i)];
            $amountSmall[$i] = $_POST["amountSmall".sprintf("%03d", $i)];
            $price[$i] = $_POST["price".sprintf("%03d", $i)];
            $receiveDate[$i] = $_POST["receiveDate".sprintf("%03d", $i)];
            $modifiedUser[$i] = $_POST["modifiedUser".sprintf("%03d", $i)];
            $modifiedDate[$i] = $_POST["modifiedDate".sprintf("%03d", $i)];
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
        for($k=0; $k<$countIngredientReceive; $k++)
        {
            //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
            //query statement
            for($j=0;$j<$retryNo;$j++)
            {
                $sql = "INSERT INTO IngredientReceive(IngredientID, Amount, AmountSmall, Price, ReceiveDate, ModifiedUser, ModifiedDate) VALUES ( '$ingredientID[$k]', '$amount[$k]', '$amountSmall[$k]', '$price[$k]', '$receiveDate[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
//                //มีการเปลี่ยน id
//                //select row ที่แก้ไข ขึ้นมาเก็บไว้
//                $sql = "select $ingredientReceiveID[$k] as IngredientReceiveID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
//                $selectedRow = getSelectedRow($sql);
//                
//                
//                
//                //broadcast ไป device token ตัวเอง
//                $type = 'IngredientReceive';
//                $action = 'd';
//                $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//                if($ret != "")
//                {
//                    putAlertToDevice();
//                    echo json_encode($ret);
//                    exit();
//                }
//                
//                
//                
//                //select row ที่แก้ไข ขึ้นมาเก็บไว้
//                $ingredientReceiveID[$k] = $ingredientReceiveID[$k]+$j;
//                $sql = "select *, 1 IdInserted from IngredientReceive where IngredientReceiveID = '$ingredientReceiveID[$k]'";
//                $selectedRow = getSelectedRow($sql);
//                
//                
//                
//                //broadcast ไป device token ตัวเอง
//                $type = 'IngredientReceive';
//                $action = 'i';
//                $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//                if($ret != "")
//                {
//                    putAlertToDevice();
//                    echo json_encode($ret);
//                    exit();
//                }
            }
            else if($j == 0)
            {
//                //update IdInserted
//                //select row ที่แก้ไข ขึ้นมาเก็บไว้
//                $sql = "select *, 1 IdInserted from IngredientReceive where IngredientReceiveID = '$ingredientReceiveID[$k]'";
//                $selectedRow = getSelectedRow($sql);
//                
//                
//                
//                //broadcast ไป device token ตัวเอง
//                $type = 'IngredientReceive';
//                $action = 'u';
//                $ret = doPushNotificationTaskToDevice($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//                if($ret != "")
//                {
//                    putAlertToDevice();
//                    echo json_encode($ret);
//                    exit();
//                }
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
//        $action = 'i';
//        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//        if($ret != "")
//        {
//            putAlertToDevice();
//            echo json_encode($ret);
//            exit();
//        }
    }
    
    
    
    //download ingredientReceiveHistory
    $sql = "SELECT ReceiveDate FROM `ingredientreceive` WHERE date_format(ReceiveDate,'%Y-%m-%d') between date_format('$historyStartDate','%Y-%m-%d') and date_format('$historyEndDate','%Y-%m-%d') GROUP by ReceiveDate ;";
    $sql .= "SELECT * FROM `ingredientreceive` WHERE date_format(ReceiveDate,'%Y-%m-%d') between date_format('$historyStartDate','%Y-%m-%d') and date_format('$historyEndDate','%Y-%m-%d');";
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $dataJson = executeMultiQueryArray($sql);
    writeToLog(json_encode($dataJson));
    
    
    
    
    
    
    
    //do script successful
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    mysqli_commit($con);
//    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql, 'dataJson' => $dataJson, 'tableName' => 'IngredientReceive');
    echo json_encode($response);
    exit();
?>
