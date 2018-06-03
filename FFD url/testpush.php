<?php
    include_once('dbConnect.php');
    setConnectionValue("MAMARIN5");
    $arrBody = array(
                     'alert' => 'เทสจิ๋ว
                     เอ'//ข้อความ
                      ,'sound' => 'default'//,//เสียงแจ้งเตือน
                     ,'content-available' => 1
//                      ,'badge' => 3 //ขึ้นข้อความตัวเลขที่ไม่ได้อ่าน
                      );
    sendTestApplePushNotification('1877301d04f677b7fcc415b7f0bcbd799bf679013b14f76ce746d778087a22f6',$arrBody);
?>

//<table><tr><td style="text-align: center;border: 1px solid black; padding-left: 10px;padding-right: 10px; border-radius: 15px;">x</td></tr></table>
