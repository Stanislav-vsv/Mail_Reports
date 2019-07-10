<?php require '../connection.php'?>
<?php require 'libs.php'?>
<?php

array_shift($argv); // ������� ������ �������(���� � �����) � ������� $argv ����������� ��� ��������� ������������ � ������ �� ������ �������
                    // cmd �����������: c:\php\php.exe D:\mondq\MAIL_REPORTS\test\sending_report.php DMPR  (�������� DMPR)
    
$systems_name = implode(', ',$argv); // ����������� ������ $argv � ������ �������� ��������: DMPR, DWH

foreach($argv as $val){ // ��������� ��� �������� ������� $argv � ������� ''
$result[] = "'".$val."'";
}
$systems_name_quot = implode(',',$result); // ����������� ������ � ������ �������� ��������: 'DMPR', 'DWH'

// �������� ������ �������� � ��������, ���� ����� ���, �� �������� �������� false
$dq_control_uk_err = IsError($systems_name_quot);

//�������� �������� ������ ��� ��������  
$emails = GetEmail($systems_name_quot);  
//$emails = implode(', ',$emails);

// �������� �����
ob_start();
 
// ���������� ���������� � �����   
echo "<html>";
echo "<head>";
echo "</head>";
echo "<body style='padding:20px 0px'>";

if($dq_control_uk_err){
    
   foreach ($dq_control_uk_err as $num){
        $name = GetName($num);
        $headers = GetHeder($num);
        $details = GetData($num);
        
        array_shift($headers); //�������� ������ ������� ������� (�������� ��������) 
        
        echo "<h3 style='color: #696969; margin-top: 20px; font-size: 14px;'>�".$num."&nbsp;&nbsp;&nbsp;".$name."</h3>";  
        
        echo "<table style='width: 90%; border: 1px solid #EEB4B4; border-collapse: collapse; padding: 4px 10px 4px 10px;  margin: 10px 0 30px 0px; font-size: 14px;'>";
        
        echo '<tr>';
        foreach($headers as $head){
            echo "<th style='border: 1px solid #EEB4B4; padding: 2px 7px 2px 7px; border-collapse: collapse; background-color: #F08080; color: #fff;  border-color: #fff; font-size: 14px;'>".$head."</th>"; 
        }
        echo '</tr>';         
                
        foreach($details as $row){
            
            array_shift($row);
            
            echo '<tr>';
            
            foreach($row as $data){
                
                if($data == 'N'){
                echo "<td style='color: #228B22; border: 1px solid #EEB4B4; border-collapse: collapse; text-align: center; padding: 4px 10px 4px 10px;  margin: 20px 0 50px 0px; font-size: 13px;'>";
                echo '���';
                }
                elseif($data == 'Y'){
                    echo "<td style='color: #DC143C; border: 1px solid #EEB4B4; border-collapse: collapse; text-align: center; padding: 4px 10px 4px 10px;  margin: 20px 0 50px 0px; font-size: 13px;'>";
                    echo '��';
                }
                else{
                    echo "<td style='border: 1px solid #EEB4B4; border-collapse: collapse; text-align: center; padding: 4px 10px 4px 10px;  margin: 20px 0 50px 0px; font-size: 13px;'>";
                    echo $data;
                }                  
                echo '</td>';                 
            }            
            echo '</tr>';             
        }     
        echo "</table>";  
    }
}
else{
    //echo "<p style='font:bold 20px Times New Roman; color: #228B22; margin: 200px 100px; border: 2px solid #228B22; padding: 20px 30px; display: inline; '>������ �� ����������</p>";
    echo "<div style='font:bold 20px Times New Roman; color: #228B22; width: 300px; margin: 20px 300px; border: 2px solid #228B22; padding: 20px 30px;'>������ �� ����������</div>";
}
/*
echo "<br>������ argv: $argv";
echo "<br>������ systems_name: $systems_name";
echo "<br>������ systems_name_quot: $systems_name_quot";
echo "<br>������ Emails: $emails";
*/
echo "<br> <a href='http://albertp1:8080/dq/REPORTS/REESTR/reestr-proverok.php?rep_system_1=$systems_name' style='margin: 20px 50px; display: block;' > ������� � ������ ���� �������� $systems_name </a>";

     
  
    
     
// ��������� �� ��� ���� � ������ � ���������� $content
$content = ob_get_contents();
 
// ��������� � ������� �����
ob_end_clean();



foreach ($emails as $email){
    /*
    $content .=  "<br> <a href='http://albertp1:8080/dq/REPORTS/unsubscribe.php?email=$email&system=$systems_name' style='margin: 10px 50px; display: block;'> ���������� �� �������� ������� $systems_name </a> </body></html>";  
    */
    $to = "$email";   
    $from = "DQ_SYSTEM";
    if($dq_control_uk_err){
        $subject = "����� �� ��������� $systems_name - ���� ������!";
    }else{
        $subject = "����� �� ��������� $systems_name"; 
    }
    $subject = "=?windows-1251?b?".base64_encode($subject)."?="; // ��������� ��������� 
    $headers = "From: $from\r\nReply-to:$from\r\nContent-type:text/html;charset=windows-1251\r\n"; // � Content-type:text/html ������� ��� ����� html ����
    mail($to, $subject, $content, $headers);
    
}




 


?>

