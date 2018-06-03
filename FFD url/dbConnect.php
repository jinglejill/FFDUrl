<?php
    require './../../phpmailermaster/PHPMailerAutoload.php';
    
    //conection variable
    $con;
    $globalDBName;
    $retryNo;

    function executeMultiQueryArray($sql)
    {
        global $con;
        if (mysqli_multi_query($con, $sql)) {
            $arrOfTableArray = array();
            $resultArray = array();
            do {
                /* store first result set */
                if ($result = mysqli_store_result($con)) {
                    while ($row = mysqli_fetch_object($result)) {
                        array_push($resultArray, $row);
                    }
                    array_push($arrOfTableArray,$resultArray);
                    $resultArray = [];
                    mysqli_free_result($result);
                }
                if(!mysqli_more_results($con))
                {
                    break;
                }
            } while (mysqli_next_result($con));
            
            return $arrOfTableArray;
        }
        return "";
    }
    
    function executeMultiQuery($sql)
    {
        global $con;
        if (mysqli_multi_query($con, $sql)) {
            $arrOfTableArray = array();
            $resultArray = array();
            do {
                /* store first result set */
                if ($result = mysqli_store_result($con)) {
                    while ($row = mysqli_fetch_object($result)) {
                        array_push($resultArray, $row);
                    }
                    array_push($arrOfTableArray,$resultArray);
                    $resultArray = [];
                    mysqli_free_result($result);
                }
                if(!mysqli_more_results($con))
                {
                    break;
                }
            } while (mysqli_next_result($con));
            
            return json_encode($arrOfTableArray);
        }
        return "";
    }
    
    function printAllPost()
    {
        global $con;
        $paramAndValue;
        $i = 0;
        foreach ($_POST as $param_name => $param_val)
        {
            if($i == 0)
            {
                $paramAndValue = "Param=Value: ";
            }
            $paramAndValue .= "$param_name=$param_val&";
            $_POST['$param_name'] = mysqli_real_escape_string($con,$param_val);
            $i++;
        }
        
        if(sizeof($_POST) > 0)
        {
            writeToLog($paramAndValue);
        }
    }
    
    function putAlertToDevice()
    {
        global $con;
        $user = $_POST["modifiedUser"];
        $actionScreen = $_POST["actionScreen"];
        
        
        // push alert to device
        // Set autocommit to on
        mysqli_autocommit($con,TRUE);
        writeToLog("set auto commit to on");
        
        
        //alert query fail-> please check recent transactions again
        $type = 'alert';
        $action = '';
        
        
        $selectedRow["alert"] = $actionScreen;
        $deviceToken = getDeviceTokenFromUsername($user);
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceToken','$type','$action','" . json_encode($selectedRow, JSON_UNESCAPED_UNICODE) . "',now())";
        $res = mysqli_query($con,$sql);
        if(!$res)
        {
            $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
            writeToLog($error);
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified user: " . $user);
            
            $pushSyncID = mysqli_insert_id($con);
            writeToLog('pushsyncid: '.$pushSyncID);
            $paramBody = array(
                               'badge' => 0
                               ,'content-available' => 1
                               );
            sendPushNotification($deviceToken, $paramBody);
            //----------
        }
        mysqli_close($con);
    }

    function setConnectionValue($dbName)
    {
        global $con;
        global $globalDBName;
        global $retryNo;
        $retryNo = 100;
        
        
        if($_GET['dbName'])
        {
            $dbName = $_GET['dbName'];
        }
        $globalDBName = $dbName;
        
        
        
        // Create connection
        $con=mysqli_connect("localhost","FFD","123456",$dbName);
        
        
        $timeZone = mysqli_query($con,"SET SESSION time_zone = '+07:00'");
        mysqli_set_charset($con, "utf8");
        
    }
    
    function getDeviceTokenFromUsername($user)
    {
        global $con;
        $sql = "select DeviceToken from useraccount where username = '$user'";
        $selectedRow = getSelectedRow($sql);
        $deviceToken = $selectedRow[0]['DeviceToken'];
        
        
        writeToLog('getDeviceTokenFromUsername deviceToken: ' . $deviceToken);
        return $deviceToken;
    }
    
    function doQueryTask($sql)
    {
        global $con;
        $user = $_POST["modifiedUser"];
        $res = mysqli_query($con,$sql);        
        if(!$res)
        {
            $error = "query fail: " .  mysqli_error($con). ", sql: $sql, modified user: $user";
            writeToLog($error);
            $response = array('status' => $error);
            return $response;
        }
        else
        {
            writeToLog("query success, sql: $sql, modified user: $user");
        }
        return "";
    }
    
    function doMultiQueryTask($sql)
    {
        global $con;
        $user = $_POST["modifiedUser"];
        $res = mysqli_multi_query($con,$sql);
        if(!$res)
        {
            $error = "query fail: " .  mysqli_error($con). ", sql: $sql, modified user: $user";
            writeToLog($error);
            $response = array('status' => $error);
            return $response;
        }
        else
        {
            writeToLog("query success, sql: $sql, modified user: $user");
        }
        return "";
    }

    function doPushNotificationTaskToDevice($deviceToken,$selectedRow,$type,$action)
    {
        global $con;
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceToken','$type','$action','" . json_encode($selectedRow, JSON_UNESCAPED_UNICODE) . "',now())";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);            
            return $ret;
        }
        $pushSyncID = mysqli_insert_id($con);
        writeToLog('pushsyncid: '.$pushSyncID);
        
        return "";
    }
    
    function doPushNotificationTask($deviceToken,$selectedRow,$type,$action)
    {
        global $con;
        $pushDeviceTokenList = getOtherDeviceTokensList($deviceToken);
        
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            //query statement
            if(strcmp($type,"sProductSales") == 0)
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . $selectedRow . "',now())";
            }
            else if(strcmp($type,"sCompareInventory") == 0)
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . $selectedRow . "',now())";
            }
            else
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . json_encode($selectedRow, JSON_UNESCAPED_UNICODE) . "',now())";
            }
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                return $ret;
            }
            $pushSyncID = mysqli_insert_id($con);
            writeToLog('pushsyncid: '.$pushSyncID);
        }
        return "";
    }
    
    function doPushNotificationTaskAsLog($con,$user,$deviceToken,$selectedRow,$type,$action)
    {
        //query statement
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync,TimeSynced) values ('$deviceToken','$type','delete log','" . json_encode($selectedRow, true) . "',now(),now())";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            return $ret;
        }
        $pushSyncID = mysqli_insert_id($con);
        writeToLog('delete log pushsyncid: '.$pushSyncID);
        return "";
    }
    
    function sendPushNotificationToAllDevices()
    {
        $pushDeviceTokenList = getAllDeviceTokenList();
        
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            sendPushNotificationToDevice($iDeviceToken);
        }
    }
    
    function sendPushNotificationToOtherDevices($deviceToken)
    {
        $pushDeviceTokenList = getOtherDeviceTokensList($deviceToken);
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            sendPushNotificationToDevice($iDeviceToken);
        }
    }
    
    function sendPushNotificationToDevice($deviceToken)
    {
        $paramBody = array(
                           'badge' => 0
                           ,'content-available' => 1
                           );
        sendPushNotification($deviceToken, $paramBody);
    }
    
    function sendPushNotificationToDeviceWithPath($deviceToken,$path,$passForCk,$msg)
    {
        $paramBody = array(
                           'alert' => $msg
                           );
        sendPushNotificationWithPath($deviceToken, $paramBody, $path, $passForCk);
    }
    
    function doApplePushNotificationTask($con,$user,$deviceToken,$badge)
    {
        $deviceTokenAndCountNotSeenList = getDeviceTokenAndCountNotSeenList($user,$deviceToken);
        foreach ($deviceTokenAndCountNotSeenList as $deviceTokenAndCountNotSeen)
        {
            $deviceTokenCountNotSeen = $deviceTokenAndCountNotSeen["DeviceToken"];
            $countNotSeen = $deviceTokenAndCountNotSeen["CountNotSeen"];
            $username = $deviceTokenAndCountNotSeen["Username"];
            writeToLog('device token: ' . $deviceToken. ', count not seen: ' . $countNotSeen);
            $updateBadge = $badge+$countNotSeen;
            

            //query statement
            $sql = "update useraccount set countnotseen = '$updateBadge' where username = '$username'";
            $res = mysqli_query($con,$sql);
            if(!$res)
            {
                $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
                writeToLog($error);
                
                
                $response = array('status' => $error);
                return $response;
            }
            else
            {
                writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
            }
 
            $paramBody = array(
                               'badge' => $updateBadge
                               );
            sendApplePushNotification($deviceTokenCountNotSeen, $paramBody);
        }
        return "";
    }
    
    function updateCountNotSeen($con,$user,$deviceToken,$badge)
    {
        $deviceTokenAndCountNotSeenList = getDeviceTokenAndCountNotSeenList($user,$deviceToken);
        foreach ($deviceTokenAndCountNotSeenList as $deviceTokenAndCountNotSeen)
        {
            $deviceTokenCountNotSeen = $deviceTokenAndCountNotSeen["DeviceToken"];
            $countNotSeen = $deviceTokenAndCountNotSeen["CountNotSeen"];
            $username = $deviceTokenAndCountNotSeen["Username"];
            writeToLog('device token: ' . $deviceToken. ', count not seen: ' . $countNotSeen);
            writeToLog('badge to add: ' . $badge);
            $updateBadge = $badge+$countNotSeen;
            
            
            //query statement
            $sql = "update useraccount set countnotseen = $updateBadge where username = '$username'";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
//                mysqli_rollback($con);
                return $ret;
            }
            
            $paramBody = array(
                               'badge' => $updateBadge
                               );
            sendPushNotification($deviceTokenCountNotSeen, $paramBody);
        }
        return "";
    }
    
    function getSelectedRow($sql)
    {
        global $con;        
        if ($result = mysqli_query($con, $sql))
        {
            $resultArray = array();
            $tempArray = array();
            
            while($row = mysqli_fetch_array($result))
            {
                $tempArray = $row;
                array_push($resultArray, $tempArray);
            }
            mysqli_free_result($result);
        }
        if(sizeof($resultArray) == 0)
        {
            $error = "query: selected row count = 0, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"];
            writeToLog($error);
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
        }
        
        return $resultArray;
    }
    
    function getAllDeviceTokenList()
    {
        global $con;
        $sql = "select DeviceToken from Device where DeviceToken != ''";
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                array_push($deviceTokenList, $strDeviceToken);
            }
            mysqli_free_result($result);
        }
        return $deviceTokenList;
    }
    
    function getOtherDeviceTokensList($modifiedDeviceToken)
    {
        global $con;
        $sql = "select DeviceToken from Device where DeviceToken != '' and DeviceToken != '" . $modifiedDeviceToken . "'";
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                array_push($deviceTokenList, $strDeviceToken);
            }
            mysqli_free_result($result);
        }

        return $deviceTokenList;
    }
    
    function getDeviceTokenAndCountNotSeenList($modifiedUser,$modifiedDeviceToken)
    {
        global $con;
        $sql = "select Device.DeviceToken, UserAccount.CountNotSeen, UserAccount.Username from Device left join UserAccount on Device.DeviceToken = UserAccount.DeviceToken where Device.DeviceToken != '" . $modifiedDeviceToken . "' and Device.DeviceToken != '' and UserAccount.PushOnSale = 1";
        writeToLog("countNotSeenList: " . $sql);
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenAndCountNotSeenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                $strCountNotSeen = $row["CountNotSeen"];
                $strUsername = $row["Username"];
                array_push($deviceTokenAndCountNotSeenList, array("DeviceToken" => $strDeviceToken,"CountNotSeen" => $strCountNotSeen,"Username"=>$strUsername));
            }
            mysqli_free_result($result);
        }
        return $deviceTokenAndCountNotSeenList;
    }
    
    function writeToLogFromParentFolder($message)
    {
        global $globalDBName;
        $year = date("Y")."<br>";
        $month = date("m")."<br>";
        $day = date("d")."<br>";
        $logPath = './' . $globalDBName . '/TransactionLog/';
        $logFile = 'transactionLog' . $year . $month . $day . '.log';
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }
        $logPath = $logPath . $logFile;
        
        
        if ($fp = fopen($logPath, 'at'))
        {
            fwrite($fp, date('c') . ' ' . $message . PHP_EOL);
            fclose($fp);
        }
    }
    
    function writeToLog($message)
    {
        global $globalDBName;
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        
        $fileName = 'transactionLog' . $year . $month . $day . '.log';
        $filePath = './' . $globalDBName . '/TransactionLog/';
        if (!file_exists($filePath))
        {        
            mkdir($filePath, 0777, true);
        }
        $filePath = $filePath . $fileName;
        
        
        
        if ($fp = fopen($filePath, 'at'))
        {
            $arrMessage = explode("\\n",$message);
            if(sizeof($arrMessage) > 1)
            {
                foreach($arrMessage as $eachLine)
                {
                    $newMessge .= PHP_EOL . $eachLine ;
                }
            }
            else
            {
                $newMessge = $message;
            }
            
            fwrite($fp, date('c') . ' ' . $newMessge . PHP_EOL);
            fclose($fp);
        }
    }

    function sendPushNotification($strDeviceToken,$arrBody)
    {
        if($strDeviceToken == "simulator")
        {
            return;
        }
        writeToLog("send push to device: " . $strDeviceToken . ", body: " . json_encode($arrBody));
        global $pushFail;
        $token = $strDeviceToken;
        $pass = 'jill';
        $message = 'คุณพิสุทธิ์ กำลังไปเขาใหญ่กับฉัน แกอยากได้อะไรไหมกั๊ง (สายน้ำผึ้ง)pushnotification';
        
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        

        if(!$pushFail)
        {
            $fp = stream_socket_client(
                                       'ssl://gateway.sandbox.push.apple.com:2195', $err,
                                       $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        }
        
        
        if (!$fp)
        {
            $pushFail = true;
            $error = "ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
            writeToLog($error);
            
            return;
        }

        
        $body['aps'] = $arrBody;
        $json = json_encode($body);
        $msg = chr(0).pack('n', 32).pack('H*',$token).pack('n',strlen($json)).$json;
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
        {
            $status = "0";
            writeToLog("push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }
    function sendPushNotificationWithPath($strDeviceToken,$arrBody,$path,$passForCk)
    {
        if($strDeviceToken == "simulator")
        {
            return;
        }
        writeToLog("send push to device: " . $strDeviceToken . ", body: " . json_encode($arrBody));
        global $pushFail;
        $token = $strDeviceToken;
        $pass = $passForCk;//'jill';
        $message = 'คุณพิสุทธิ์ กำลังไปเขาใหญ่กับฉัน แกอยากได้อะไรไหมกั๊ง (สายน้ำผึ้ง)pushnotification';
        
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', "$path".'ck.pem');//./../../FFD/MAMARIN5/
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        
        
        if(!$pushFail)
        {
            $fp = stream_socket_client(
                                       'ssl://gateway.sandbox.push.apple.com:2195', $err,
                                       $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        }
        
        
        if (!$fp)
        {
            $pushFail = true;
            $error = "ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
            writeToLog($error);
            
            return;
        }
        
        
        $body['aps'] = $arrBody;
        $json = json_encode($body);
        $msg = chr(0).pack('n', 32).pack('H*',$token).pack('n',strlen($json)).$json;
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
        {
            $status = "0";
            writeToLog("push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }
    function sendApplePushNotification($strDeviceToken,$arrBody)
    {
        global $pushFail;
        $token = $strDeviceToken;
        $pass = 'jill';
        $message = 'คุณพิสุทธิ์ กำลังไปเขาใหญ่กับฉัน แกอยากได้อะไรไหมกั๊ง (สายน้ำผึ้ง)pushnotification';
        
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        
        
        if(!$pushFail)
        {            
            $fp = stream_socket_client(
                                       'ssl://gateway.sandbox.push.apple.com:2195', $err,
                                       $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        }
        
        
        if (!$fp)
        {
            $pushFail = true;
            $error = "apple push: ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
            writeToLog($error);
            
            return;
        }
        
        
        $body['aps'] = $arrBody;
        $json = json_encode($body);
        $msg = chr(0).pack('n', 32).pack('H*',$token).pack('n',strlen($json)).$json;
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
        {
            $status = "0";
            writeToLog("apple push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("apple push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }
    function sendTestApplePushNotification($strDeviceToken,$arrBody)
    {
        global $pushFail;
        $token = $strDeviceToken;
        $pass = 'jill';
        $message = 'คุณพิสุทธิ์ กำลังไปเขาใหญ่กับฉัน แกอยากได้อะไรไหมกั๊ง (สายน้ำผึ้ง)pushnotification';
        
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        
        
        if(!$pushFail)
        {
            $fp = stream_socket_client(
                                       'ssl://gateway.sandbox.push.apple.com:2195', $err,
                                       $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        }
        
        
        if (!$fp)
        {
            $pushFail = true;
            $error = "apple push: ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
            writeToLog($error);
            
            return;
        }
        
        
        $body['aps'] = $arrBody;
        $json = json_encode($body);
        $msg = chr(0).pack('n', 32).pack('H*',$token).pack('n',strlen($json)).$json;
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
        {
            $status = "0";
            writeToLog("apple push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("apple push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }

    function sendEmail($toAddress,$subject,$body)
    {
        $mail = new PHPMailer;
//        writeToLog("phpmailer");
        //$mail->SMTPDebug = 3;                               // Enable verbose debug output
        
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'cpanel02mh.bkk1.cloud.z.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication // if not need put false
        $mail->Username = 'admin@jinglejill.com';                 // SMTP username
        $mail->Password = 'Jin1210!88';                           // SMTP password
        
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted // if nedd
        $mail->Port = 465;                                    // TCP port to connect to // if nedd
        
        $mail->From = 'admin@jinglejill.com'; // mail form user mail auth smtp
        $mail->FromName = 'JUMMUM';//$_POST['dbName'];
        $mail->addAddress($toAddress); // Add a recipient
        //$mail->addAddress('ellen@example.com'); // if nedd
        //$mail->addReplyTo('info@example.com', 'Information'); // if nedd
        //$mail->addCC('cc@example.com'); // if nedd
        //$mail->addBCC('bcc@example.com'); // if nedd
        
        $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments // if nedd
        //$mail->addAttachment('http://minimalist.co.th/imageupload/34664/minimalistLogoReceipt.gif', 'logo.gif');    // Optional name // if nedd
        $mail->AddEmbeddedImage('minimalistLogoReceipt.jpg', 'logo', 'minimalistLogoReceipt.jpg');
        $mail->isHTML(true);                                  // Set email format to HTML // if format mail html // if no put false
        
        $mail->Subject = $subject; // text subject
        $mail->Body    = $body; // body
        
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients'; // if nedd
//        writeToLog("before send()");
        if(!$mail->send())
        { // check send mail true/false
            echo 'Message could not be sent.'; // message if send mail not complete
            echo 'Mailer Error: ' . $mail->ErrorInfo; // message error
            $response = array('status' => 'Mailer Error: ' . $mail->ErrorInfo);
            
            $error = "send email fail, Mailer Error: " . $mail->ErrorInfo . ", modified user: " . $user;
            writeToLog($error);
            
            
            
            
            {
                //--------- ใช้สำหรับกรณี หน้าที่เรียกใช้ homemodel back ออกจากหน้าตัวเองไปแล้ว
                // Set autocommit to on
                mysqli_autocommit($con,TRUE);
                writeToLog("set auto commit to on");
                
                
                //alert query fail-> please check recent transactions again
                $type = 'alert';
                $action = '';
                
                
                
                $deviceToken = getDeviceTokenFromUsername($user);
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceToken','$type','$action','',now())";
                $res = mysqli_query($con,$sql);
                if(!$res)
                {
                    $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
                    writeToLog($error);
                }
                else
                {
                    writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
                    
                    
                    $pushSyncID = mysqli_insert_id($con);
                    mysqli_close($con);
                    
                    writeToLog('pushsyncid: '.$pushSyncID);
                    $paramBody = array(
                                       'badge' => 0
                                       ,'content-available' => 1
                                       //                               'type' => 'alert',
                                       //                               'pushSyncID' => $pushSyncID
                                       );
                    sendPushNotification($deviceToken, $paramBody);
                    
                    ///----
                }
            }
        }
        else
        {
            //    echo 'Message has been sent'; // message if send mail complete
            $response = array('status' => '1');
        }
    }
?>
