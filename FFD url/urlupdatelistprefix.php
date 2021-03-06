<?php
    include_once('dbConnect.php');
    setConnectionValue('dbName');
    
    function makeFirstLetterLowerCase($text)
    {
        return strtolower(substr($text,0,1)) . substr($text,1,strlen($text)-1);
    }
    
    function getTableNameFromPrimaryKey($text)
    {
        return substr($text,0,strlen($text)-2);
    }
    
    function tab()
    {
        return "&nbsp;&nbsp;&nbsp;&nbsp;";
    }
    
    $i = 0;
    $dbColumnName = array();
    $dbColumnNameWithoutPrefix = array();
    $dbColumnType = array();
    $dbColumnTypeName = array();
    $propertyAttribute = array();
    $property;
    $primaryKey;
    $primaryKeyWithPrefix;
    $sql = "select * from " . $_GET['tableName'];
    $prefix = $_GET['prefix'];
    $varModifiedUser = 'modifiedUser';
    if($prefix != '')
    {
        $varModifiedUser = $prefix.'ModifiedUser';
    }
    if ($result=mysqli_query($con,$sql))
    {
        
        // Get field information for all fields
        while ($fieldinfo=mysqli_fetch_field($result))
        {
            $dbColumnName[$i] = $prefix . $fieldinfo->name;
            $dbColumnNameWithoutPrefix[$i] = $fieldinfo->name;
            $dbColumnType[$i] = $fieldinfo->type;
            
            
            if ($fieldinfo->flags & MYSQLI_PRI_KEY_FLAG) {
                //it is a primary key!
                $primaryKey = $fieldinfo->name;
                $primaryKeyWithPrefix = $prefix . $fieldinfo->name;
            }
            $i++;
        }
       
        
        //set $dbColumnTypeName and $propertyAttribute
        for($j=0;$j<sizeof($dbColumnName);$j++)
        {
            if($dbColumnType[$j] == 1)//tinyint
            {
                $dbColumnTypeName[$j] = "NSInteger";
                $propertyAttribute[$j] = "(nonatomic)";
            }
            else if($dbColumnType[$j] == 3)//int
            {
                $dbColumnTypeName[$j] = "NSInteger";
                $propertyAttribute[$j] = "(nonatomic)";
            }
            else if($dbColumnType[$j] == 4)//float
            {
                $dbColumnTypeName[$j] = "float";
                $propertyAttribute[$j] = "(nonatomic)";
            }
            else if($dbColumnType[$j] == 253)//varchar
            {
                $dbColumnTypeName[$j] = "NSString *";
                $propertyAttribute[$j] = "(retain, nonatomic)";
            }
            else if($dbColumnType[$j] == 12)//datetime
            {
                $dbColumnTypeName[$j] = "NSDate *";
                $propertyAttribute[$j] = "(retain, nonatomic)";
            }
        }
        
        
        
        
        
        

        $code .= tab() . 'include_once("dbConnect.php");<br>';
        $code .= tab() . 'setConnectionValue($_POST["dbName"]);<br>';
        $code .= tab() . 'writeToLog("file: " . basename(__FILE__) . ", user: " .  $_POST["modifiedUser"]);<br>';        
        $code .= tab() . 'printAllPost();<br>';
        $code .= '<br><br><br>';
        $code .= tab() . 'if (isset($_POST["count' . getTableNameFromPrimaryKey($primaryKey) . '"]))<br>';
        $code .= tab() . '{<br>';
        $code .= tab() . tab() . '$count' . getTableNameFromPrimaryKey($primaryKey) . ' = $_POST["count' . getTableNameFromPrimaryKey($primaryKey) . '"];<br>';
        $code .= tab() . tab() . 'for($i=0; $i<$count' . getTableNameFromPrimaryKey($primaryKey) . '; $i++)<br>';
        $code .= tab() . tab() . '{<br>';
        for($j=0;$j<sizeof($dbColumnName);$j++)
        {
            $code .= tab() . tab() . tab() . '$' . makeFirstLetterLowerCase($dbColumnName[$j]) . '[$i] = $_POST["' . makeFirstLetterLowerCase($dbColumnName[$j]) . '".sprintf("%02d", $i)];<br>';
        }
        $code .= tab() . tab() . '}<br>';
        $code .= tab() . '}<br>';
        $code .= '<br><br><br>';
        
        
        
        $code .= tab() . '// Check connection<br>';
        $code .= tab() . 'if (mysqli_connect_errno())<br>';
        $code .= tab() . '{<br>';
        $code .= tab() . tab() . 'echo "Failed to connect to MySQL: " . mysqli_connect_error();<br>';
        $code .= tab() . '}<br>';
        $code .= '<br><br><br>';
        
        
        
        $code .= tab() . '// Set autocommit to off<br>';
        $code .= tab() . 'mysqli_autocommit($con,FALSE);<br>';
        $code .= tab() . 'writeToLog("set auto commit to off");<br>';
        $code .= '<br><br><br>';
        
        
        
        $code .= tab() . 'if($count' . getTableNameFromPrimaryKey($primaryKey) . ' > 0)<br>';
        $code .= tab() . '{<br>';
        $code .= tab() . tab() . 'for($i=0; $i<$count' . getTableNameFromPrimaryKey($primaryKey) . '; $i++)<br>';
        $code .= tab() . tab() . '{<br>';
        $code .= tab() . tab() . tab() . '//query statement<br>';
        $code .= tab() . tab() . tab() . '$sql = "update '. getTableNameFromPrimaryKey($primaryKey) . ' set ' . $dbColumnNameWithoutPrefix[1] . ' = ' . "'" . '$' . makeFirstLetterLowerCase($dbColumnName[1]) . '[$i]' . "'";
        for($j=2;$j<sizeof($dbColumnNameWithoutPrefix);$j++)
        {
            $code .= ', ' . $dbColumnNameWithoutPrefix[$j] . ' = ' . "'" . '$' . makeFirstLetterLowerCase($dbColumnName[$j]) . '[$i]' .  "'";
        }
        $code .= ' where ' . $primaryKey . ' = ' . "'" . '$' . makeFirstLetterLowerCase($primaryKeyWithPrefix) . '[$i]' . "'" . '";<br>';
        
        $code .= tab() . tab() . tab() . '$ret = doQueryTask($sql);<br>';
        $code .= tab() . tab() . tab() . 'if($ret != "")<br>';
        $code .= tab() . tab() . tab() . '{<br>';
        $code .= tab() . tab() . tab() . tab() . 'mysqli_rollback($con);<br>';
        $code .= tab() . tab() . tab() . tab() . 'putAlertToDevice();<br>';
        $code .= tab() . tab() . tab() . tab() . 'echo json_encode($ret);<br>';
        $code .= tab() . tab() . tab() . tab() . 'exit();<br>';
        $code .= tab() . tab() . tab() . '}<br>';
        $code .= tab() . tab() . '}<br>';
        $code .= '<br><br><br>';
        
        
        
        $code .= tab() . tab() . '//select row ที่แก้ไข ขึ้นมาเก็บไว้<br>';
        $code .= tab() . tab() . '$sql = "select *, 1 IdInserted from ' . getTableNameFromPrimaryKey($primaryKey) . ' where ' . $primaryKey . ' in ' . "('" . '$' . makeFirstLetterLowerCase($primaryKeyWithPrefix) . '[0]' . "'" . '";<br>';
        $code .= tab() . tab() . 'for($i=1; $i<$count' . getTableNameFromPrimaryKey($primaryKey) . '; $i++)<br>';
        $code .= tab() . tab() . '{<br>';
        $code .= tab() . tab() . tab() . '$sql .= ",' . "'" . '$' . makeFirstLetterLowerCase($primaryKeyWithPrefix) . '[$i]' . "'" . '";<br>';
        $code .= tab() . tab() . '}<br>';
        $code .= tab() . tab() . '$sql .= ")";<br>';
        $code .= tab() . tab() . '$selectedRow = getSelectedRow($sql);<br>';
        $code .= '<br><br><br>';
        
        
        $code .= tab() . tab() . '//broadcast ไป device token อื่น<br>';
        $code .= tab() . tab() . '$type = ' . "'" . getTableNameFromPrimaryKey($primaryKey) . "'" . ';<br>';
        $code .= tab() . tab() . '$action = ' ."'". 'u'. "'" . ';<br>';
        $code .= tab() . tab() . '$ret = doPushNotificationTask($_POST["modifiedDeviceToken"],$selectedRow,$type,$action);<br>';
        $code .= tab() . tab() . 'if($ret != "")<br>';
        $code .= tab() . tab() . '{<br>';
        $code .= tab() . tab() . tab() . 'mysqli_rollback($con);<br>';
        $code .= tab() . tab() . tab() . 'putAlertToDevice();<br>';
        $code .= tab() . tab() . tab() . 'echo json_encode($ret);<br>';
        $code .= tab() . tab() . tab() . 'exit();<br>';
        $code .= tab() . tab() . '}<br>';
        $code .= tab() . '}<br>';//ตัวปิด count > 0
        $code .= '<br><br><br>';
        
        
        
        
        
        
        $code .= tab() . '//do script successful<br>';
        $code .= tab() . '//update ตัวเอง สำหรับกรณี insert duplicate และ update IdInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด<br>';
        $code .= tab() . 'mysqli_commit($con);<br>';
        $code .= tab() . 'sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);<br>';        
        $code .= tab() . 'mysqli_close($con);<br>';
        $code .= '<br><br><br>';
        
        
        
        $code .= tab() . 'writeToLog("query commit, file: " . basename(__FILE__) . ", user: " .  $_POST[' . "'" . 'modifiedUser' . "'" . ']);<br>';
        $code .= tab() . '$response = array(' . "'" . 'status' . "'" . ' => ' . "'" . '1' . "'" . ', ' . "'" . 'sql' . "'" . ' => $sql);<br>';
        $code .= tab() . 'echo json_encode($response);<br>';
        $code .= tab() . 'exit();<br>';
        $code .= '<br><br><br>';

        
    
        
        
        
        
        
        
        
        
        
        // Free result set
        mysqli_free_result($result);
    }
    
    
    
    echo $code;

?>
