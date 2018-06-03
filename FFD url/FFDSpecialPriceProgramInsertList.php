<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countSpecialPriceProgram"]))
    {
        $countSpecialPriceProgram = $_POST["countSpecialPriceProgram"];
        for($i=0; $i<$countSpecialPriceProgram; $i++)
        {
            $specialPriceProgramID[$i] = $_POST["specialPriceProgramID".sprintf("%02d", $i)];
            $menuID[$i] = $_POST["menuID".sprintf("%02d", $i)];
            $startDate[$i] = $_POST["startDate".sprintf("%02d", $i)];
            $endDate[$i] = $_POST["endDate".sprintf("%02d", $i)];
            $specialPrice[$i] = $_POST["specialPrice".sprintf("%02d", $i)];
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
    
    
    
    if($countSpecialPriceProgram > 0)
    {
        for($k=0; $k<$countSpecialPriceProgram; $k++)
        {
            //query statement
            $sql = "INSERT INTO SpecialPriceProgram(MenuID, StartDate, EndDate, SpecialPrice, ModifiedUser, ModifiedDate) VALUES ('$menuID[$k]', '$startDate[$k]', '$endDate[$k]', '$specialPrice[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
            $sql = "select $specialPriceProgramID[$k] as SpecialPriceProgramID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'SpecialPriceProgram';
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
            $specialPriceProgramID[$k] = $newID;
            $sql = "select *, 1 IdInserted from SpecialPriceProgram where SpecialPriceProgramID = '$specialPriceProgramID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'SpecialPriceProgram';
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
        $sql = "select *, 1 IdInserted from SpecialPriceProgram where SpecialPriceProgramID in ('$specialPriceProgramID[0]'";
        for($i=1; $i<$countSpecialPriceProgram; $i++)
        {
            $sql .= ",'$specialPriceProgramID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'SpecialPriceProgram';
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
