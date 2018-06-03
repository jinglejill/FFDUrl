<?php
    include_once("dbConnect.php");
    setConnectionValue("JUMMUM2");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    
    

    if(isset($_POST["receiptID"]) && isset($_POST["branchID"]))
    {
        $receiptID = $_POST["receiptID"];
        $branchID = $_POST["branchID"];
    }
    else
    {
        $receiptID = $_GET["receiptID"];
        $branchID = $_GET["branchID"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    $sql = "select '$branchID' BranchID, Receipt.*, 1 IdInserted from JUMMUM2.Receipt where ReceiptID = '$receiptID';";
    $sql .= "select '$branchID' BranchID, OrderTaking.*, 1 IdInserted from JUMMUM2.OrderTaking where ReceiptID = '$receiptID';";
    $sql .= "select '$branchID' BranchID, OrderNote.*, 1 IdInserted from JUMMUM2.OrderNote where OrderTakingID in (select orderTakingID from JUMMUM2.OrderTaking where ReceiptID = '$receiptID');";
    $sql .= "select Dispute.*, 1 IdInserted from JUMMUM2.Dispute where ReceiptID = '$receiptID';";
    writeToLog($sql);
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;
    
    
    
    // Close connections
    mysqli_close($con);
?>
