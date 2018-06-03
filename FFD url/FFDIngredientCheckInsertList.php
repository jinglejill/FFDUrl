<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    if(isset($_POST["startDate"]) && isset($_POST["endDate"]))
    {
        $startDate = $_POST["startDate"];
        $endDate = $_POST["endDate"];
    }
    
    
    if (isset($_POST["countIngredientCheck"]))
    {
        $countIngredientCheck = $_POST["countIngredientCheck"];
        for($i=0; $i<$countIngredientCheck; $i++)
        {
            $ingredientCheckID[$i] = $_POST["ingredientCheckID".sprintf("%03d", $i)];
            $ingredientID[$i] = $_POST["ingredientID".sprintf("%03d", $i)];
            $amount[$i] = $_POST["amount".sprintf("%03d", $i)];
            $amountSmall[$i] = $_POST["amountSmall".sprintf("%03d", $i)];
            $checkDate[$i] = $_POST["checkDate".sprintf("%03d", $i)];
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
    
    
    
    
    //delete by checkdate
    $sql = "Delete from IngredientCheck where date_format(CheckDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d')";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    
    
    
    if($countIngredientCheck > 0)
    {
        for($k=0; $k<$countIngredientCheck; $k++)
        {
            //หาก insert ไม่ผ่าน ให้เปลี่ยน id ขึ้นทีละ 1
            //query statement
            for($j=0;$j<$retryNo;$j++)
            {
                $sql = "INSERT INTO IngredientCheck(IngredientID, Amount, AmountSmall, CheckDate, ModifiedUser, ModifiedDate) VALUES ('$ingredientID[$k]', '$amount[$k]', '$amountSmall[$k]', '$checkDate[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
//                $sql = "select $ingredientCheckID[$k] as IngredientCheckID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
//                $selectedRow = getSelectedRow($sql);
//                
//                
//                
//                //broadcast ไป device token ตัวเอง
//                $type = 'IngredientCheck';
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
//                $ingredientCheckID[$k] = $ingredientCheckID[$k]+$j;
//                $sql = "select *, 1 IdInserted from IngredientCheck where IngredientCheckID = '$ingredientCheckID[$k]'";
//                $selectedRow = getSelectedRow($sql);
//                
//                
//                
//                //broadcast ไป device token ตัวเอง
//                $type = 'IngredientCheck';
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
//                $sql = "select *, 1 IdInserted from IngredientCheck where IngredientCheckID = '$ingredientCheckID[$k]'";
//                $selectedRow = getSelectedRow($sql);
//                
//                
//                
//                //broadcast ไป device token ตัวเอง
//                $type = 'IngredientCheck';
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
//        $sql = "select *, 1 IdInserted from IngredientCheck where IngredientCheckID in ('$ingredientCheckID[0]'";
//        for($i=1; $i<$countIngredientCheck; $i++)
//        {
//            $sql .= ",'$ingredientCheckID[$i]'";
//        }
//        $sql .= ")";
//        $selectedRow = getSelectedRow($sql);
//        
//        
//        
//        //broadcast ไป device token อื่น
//        $type = 'IngredientCheck';
//        $action = 'i';
//        $ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//        if($ret != "")
//        {
//            putAlertToDevice();
//            echo json_encode($ret);
//            exit();
//        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    //download ingredientchecklist
    //last check stock
    $sql = "select max(CheckDate) LastCheckDate from IngredientCheck Where date_format(CheckDate,'%Y-%m-%d') < date_format('$startDate','%Y-%m-%d')";
    $selectedRow = getSelectedRow($sql);
    $lastCheckDate = $selectedRow[0]['LastCheckDate'];
    
    
    
    //เช็คสตอคครั้งสุดท้าย
    $sql = "select ingredient.IngredientID, (Amount + AmountSmall/ingredient.SmallAmount) Amount from ingredientcheck LEFT JOIN ingredient ON ingredientcheck.IngredientID = ingredient.IngredientID where CheckDate = '$lastCheckDate';";
    
    
    //รวม receive stock หลังจากเช็คสตอค และน้อยกว่าวันนี้ $date
    $sql .= "select ingredient.IngredientID, sum(amount+amountSmall/ingredient.smallAmount) Amount from ingredientReceive LEFT JOIN ingredient ON ingredientreceive.IngredientID = ingredient.IngredientID where ReceiveDate > '$lastCheckDate' and date_format(ReceiveDate,'%Y-%m-%d') < date_format('$startDate','%Y-%m-%d') group by ingredient.IngredientID;";
    
    
    //ใช้ไปหลังเช็คสตอค และน้อยกว่าวันนี้ $startDate
    $sql .= "SELECT menuingredient.IngredientID,sum(menuingredient.Amount*Quantity) Amount FROM `ordertaking` LEFT JOIN receipt on ordertaking.ReceiptID = receipt.ReceiptID LEFT JOIN menuingredient ON ordertaking.MenuID = menuingredient.MenuID WHERE receiptdate > '$lastCheckDate' and date_format(ReceiptDate,'%Y-%m-%d') < date_format('$startDate','%Y-%m-%d') and menuingredient.IngredientID is not null GROUP BY menuingredient.IngredientID;";
    
    
    //รับเข้ามาระหว่างวันที่
    $sql .= "select ingredientreceive.IngredientID, sum(Amount+AmountSmall/Ingredient.SmallAmount) Amount from ingredientreceive LEFT JOIN ingredient ON ingredientreceive.IngredientID = ingredient.IngredientID where date_format(ReceiveDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') group by ingredientreceive.IngredientID;";
    
    
    //เช็คสตอควันที่
    $sql .= "select ingredient.IngredientID,ifnull(a1.Amount,0)Amount,ifnull(a1.AmountSmall,0)AmountSmall from ingredient LEFT JOIN (select IngredientID, Amount, AmountSmall from ingredientcheck where date_format(CheckDate,'%Y-%m-%d') = date_format('$endDate','%Y-%m-%d'))a1 ON ingredient.IngredientID = a1.IngredientID WHERE STATUS = 1;";
    
    
    
    //ปริมาณใช้จริง ระหว่างวันที่ startDate and endDate
    $sql .= "SELECT menuingredient.IngredientID,sum(menuingredient.Amount*Quantity) Amount FROM `ordertaking` LEFT JOIN receipt on ordertaking.ReceiptID = receipt.ReceiptID LEFT JOIN menuingredient ON ordertaking.MenuID = menuingredient.MenuID WHERE date_format(ReceiptDate,'%Y-%m-%d') between date_format('$startDate','%Y-%m-%d') and date_format('$endDate','%Y-%m-%d') and menuingredient.IngredientID is not null GROUP BY menuingredient.IngredientID;";
    
    
    
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
    $response = array('status' => '1', 'sql' => $sql, 'dataJson'=>$dataJson, 'tableName' => 'IngredientCheck');
    echo json_encode($response);
    exit();
?>
