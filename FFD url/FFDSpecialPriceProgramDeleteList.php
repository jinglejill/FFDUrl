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
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from SpecialPriceProgram where SpecialPriceProgramID in ('$specialPriceProgramID[0]'";
        for($i=1; $i<$countSpecialPriceProgram; $i++)
        {
            $sql .= ",'$specialPriceProgramID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        for($i=0; $i<$countSpecialPriceProgram; $i++)
        {
            //query statement
            $sql = "delete from SpecialPriceProgram where SpecialPriceProgramID in ('$specialPriceProgramID[0]'";
            for($i=1; $i<$countSpecialPriceProgram; $i++)
            {
                $sql .= ",'$specialPriceProgramID[$i]'";
            }
            $sql .= ")";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        //broadcast ไป device token อื่น
        $type = 'SpecialPriceProgram';
        $action = 'd';
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
