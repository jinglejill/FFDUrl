<?php    
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countMenuIngredient"]))
    {
        $countMenuIngredient = $_POST["countMenuIngredient"];
        for($i=0; $i<$countMenuIngredient; $i++)
        {
            $menuIngredientID[$i] = $_POST["menuIngredientID".sprintf("%02d", $i)];
            $menuID[$i] = $_POST["menuID".sprintf("%02d", $i)];
            $ingredientID[$i] = $_POST["ingredientID".sprintf("%02d", $i)];
            $amount[$i] = $_POST["amount".sprintf("%02d", $i)];
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
    
    
    
    if($countMenuIngredient > 0)
    {
        for($k=0; $k<$countMenuIngredient; $k++)
        {
            //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
            //query statement
            for($j=0;$j<$retryNo;$j++)
            {
                $sql = "INSERT INTO MenuIngredient(MenuIngredientID, MenuID, IngredientID, Amount, ModifiedUser, ModifiedDate) VALUES ('" . ($menuIngredientID[$k]+$j) . "', '$menuID[$k]', '$ingredientID[$k]', '$amount[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
                $sql = "select $menuIngredientID[$k] as MenuIngredientID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
                $selectedRow = getSelectedRow($sql);
                
                
                
                //broadcast ไป device token ตัวเอง
                $type = 'MenuIngredient';
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
                $menuIngredientID[$k] = $menuIngredientID[$k]+$j;
                $sql = "select *, 1 IdInserted from MenuIngredient where MenuIngredientID = '$menuIngredientID[$k]'";
                $selectedRow = getSelectedRow($sql);
                
                
                
                //broadcast ไป device token ตัวเอง
                $type = 'MenuIngredient';
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
                $sql = "select *, 1 IdInserted from MenuIngredient where MenuIngredientID = '$menuIngredientID[$k]'";
                $selectedRow = getSelectedRow($sql);
                
                
                
                //broadcast ไป device token ตัวเอง
                $type = 'MenuIngredient';
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
        $sql = "select *, 1 IdInserted from MenuIngredient where MenuIngredientID in ('$menuIngredientID[0]'";
        for($i=1; $i<$countMenuIngredient; $i++)
        {
            $sql .= ",'$menuIngredientID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'MenuIngredient';
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
