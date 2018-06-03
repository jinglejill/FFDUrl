<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset($_POST["countMoneyCheck"]))
    {
        $countMoneyCheck = $_POST["countMoneyCheck"];
        for($i=0; $i<$countMoneyCheck; $i++)
        {
            $moneyCheckID[$i] = $_POST["moneyCheckID".sprintf("%02d", $i)];
            $openClose[$i] = $_POST["type".sprintf("%02d", $i)];
            $method[$i] = $_POST["method".sprintf("%02d", $i)];
            $amount[$i] = $_POST["amount".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
            $checkUser[$i] = $_POST["checkUser".sprintf("%02d", $i)];
            $checkDate[$i] = $_POST["checkDate".sprintf("%02d", $i)];
            $modifiedUser[$i] = $_POST["modifiedUser".sprintf("%02d", $i)];
            $modifiedDate[$i] = $_POST["modifiedDate".sprintf("%02d", $i)];
        }
    }
    if(isset($_POST["period"]) && isset($_POST["emailAddress"]))
    {
        $period = $_POST["period"];
        $emailAddress = $_POST["emailAddress"];
    }
    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    if($countMoneyCheck > 0)
    {
        for($k=0; $k<$countMoneyCheck; $k++)
        {
            //query statement
            $sql = "INSERT INTO MoneyCheck(Type, Method, Amount, Status, CheckUser, CheckDate, ModifiedUser, ModifiedDate) VALUES ('$openClose[$k]', '$method[$k]', '$amount[$k]', '$status[$k]', '$checkUser[$k]', '$checkDate[$k]', '$modifiedUser[$k]', '$modifiedDate[$k]')";
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
            $sql = "select $moneyCheckID[$k] as MoneyCheckID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'MoneyCheck';
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
            $moneyCheckID[$k] = $newID;
            $sql = "select *, 1 IdInserted from MoneyCheck where MoneyCheckID = '$moneyCheckID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'MoneyCheck';
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
        $sql = "select *, 1 IdInserted from MoneyCheck where MoneyCheckID in ('$moneyCheckID[0]'";
        for($i=1; $i<$countMoneyCheck; $i++)
        {
            $sql .= ",'$moneyCheckID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'MoneyCheck';
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
    
    
    
    //send mail
    //type
    $strType = $openClose[0]==1?"ตรวจสอบเงินก่อนเริ่มงาน":"ตรวจสอบเงินหลังสิ้นสุดงาน";
    
    
    //period
    $strKeyNameOpen = $period == 1?"shift1OpenTime":$period == 2?"shift2OpenTime":"shift3OpenTime";
    $strKeyNameClose = $period == 1?"shift1CloseTime":$period == 2?"shift2CloseTime":"shift3CloseTime";
    $sql = "select * from setting where keyName = '" . $strKeyNameOpen . "'";
    $selectedRow = getSelectedRow($sql);
    $strOpenTime = $selectedRow[0]["Value"];
    
    $sql = "select * from setting where keyName = '" . $strKeyNameClose . "'";
    $selectedRow = getSelectedRow($sql);
    $strCloseTime = $selectedRow[0]["Value"];
    $strPeriod = $strOpenTime . " - " . $strCloseTime;
    
    
    //checkDate
    $strCheckDate = date('d M Y G:i',strtotime(substr($checkDate[0],0,19)));//2018-02-03 23:02:18
    
    
    //fullname
    $sql = "select * from useraccount where username = '" . $checkUser[0] . "'";
    $selectedRow = getSelectedRow($sql);
    $strFullName = $selectedRow[0]["FullName"];
    
    
    
    $content = $openClose[0]==1?file_get_contents('./emailTemplateCheckMoneyOpen.html'):file_get_contents('./emailTemplateCheckMoneyClose.html');
    $content = str_replace("#checkMoneyType#",$strType,$content);
    $content = str_replace("#openCloseTime#",$strPeriod,$content);
    $content = str_replace("#checkDate#",$strCheckDate,$content);
    $content = str_replace("#checkUser#",$strFullName,$content);
    $replaceMethod = array("#cashDrawerInitialAmount#", "#cash#", "#credit#", "#transfer#","#eWallet#");
    for($i=0; $i<$countMoneyCheck; $i++)
    {
        $strStatus = $status[$i] == 1?"ถูกต้อง":"ไม่ถูกต้อง";
        $strColor = $status[$i] == 1?"339966":"ff0000";
        
        
        $content = str_replace($replaceMethod[$i],number_format($amount[$i]),$content);
        $content = str_replace("#colorStatus" . ($i+1) ."#",$strColor,$content);
        $content = str_replace("#status" . ($i+1) ."#",$strStatus,$content);
    }
    
    sendEmail($emailAddress,"ตรวจสอบเงินในลิ้นชัก",$content);
    writeToLog("sendemail");
    

    
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

