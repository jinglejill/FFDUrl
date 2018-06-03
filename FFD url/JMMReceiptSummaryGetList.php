<?php
    include_once("dbConnect.php");
    setConnectionValue($_POST["dbName"]);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    $dbName = $_POST["dbName"];


    if(isset($_POST["receiptDate"]) && isset($_POST["receiptID"]) && isset($_POST["branchID"]) && isset($_POST["status"]))
    {
        $receiptDate = $_POST["receiptDate"];
        $receiptID = $_POST["receiptID"];
        $branchID = $_POST["branchID"];
        $status = $_POST["status"];
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
   
    
    $sql = "select JUMMUM2.receipt.*, 1 as IdInserted from JUMMUM2.receipt where branchID = '$branchID' and status = '$status' and (receiptDate < '$receiptDate' or (receiptDate = '$receiptDate' and JUMMUM2.receipt.receiptID < '$receiptID')) order by receipt.ReceiptDate DESC, receipt.ReceiptID DESC limit 20;";
    $selectedRow = getSelectedRow($sql);
    
    
    $receiptIDList = array();
    for($i=0; $i<sizeof($selectedRow); $i++)
    {
        array_push($receiptIDList,$selectedRow[$i]["ReceiptID"]);
    }
    if(sizeof($receiptIDList) > 0)
    {
        $receiptIDListInText = $receiptIDList[0];
        for($i=1; $i<sizeof($receiptIDList); $i++)
        {
            $receiptIDListInText .= "," . $receiptIDList[$i];
        }
        
        
        $sql .= "select * from JUMMUM2.OrderTaking where receiptID in ($receiptIDListInText);";
        $sql .= "select * from JUMMUM2.OrderNote where orderTakingID in (select orderTakingID from JUMMUM2.OrderTaking where receiptID in ($receiptIDListInText));";
        $sql .= "select * from $dbName.receiptPrint where receiptID in ($receiptIDListInText);";
    }
    else
    {
        $sql .= "select * from JUMMUM2.OrderTaking where 0;";
        $sql .= "select * from JUMMUM2.OrderNote where 0;";
        $sql .= "select * from $dbName.receiptPrint where 0;";
    }
    
    
    
    
    
    
    
    writeToLog("sql = " . $sql);
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;
    
    
    
    // Close connections
    mysqli_close($con);
?>
