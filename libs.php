<?php
  
//функция возвращает номера проверок Bazel_DWH у которых есть ошибки или false если ошибок нет
function IsError($systems_name){       
    global $open_connection;
    
    $query_err = "  
            SELECT DQ_CONTROL_UK FROM monitoring.dq_detail
            WHERE 
            xk IN (SELECT MAX(xk) FROM monitoring.dq_detail
            WHERE dq_control_uk IN (SELECT list_id FROM monitoring.dq_list_control WHERE rep_system in ($systems_name))
            AND TRUNC(REPORT_TIME) = TRUNC(SYSDATE)    
            AND error_flag in ('Y')
            GROUP BY dq_control_uk)
             ";
                
    ora_parse($open_connection, $query_err, 0);         
	ora_exec($open_connection);
    
    while(ora_fetch_into($open_connection, $row_err))
    { 
        $data_err[] = $row_err[0];
    }
    
    if($data_err){
        return $data_err; 
    }            
    
    return false;
}  
    
//функция получения названия проверки
function GetName($num){       
    global $open_connection;
    
    $query_name = "  select REV_NAME
                from MONITORING.DQ_LIST_CONTROL
                where LIST_ID = $num 
             ";
                
    ora_parse($open_connection, $query_name, 0);         
	ora_exec($open_connection);
    
   while(ora_fetch_into($open_connection, $row_name))
    { 
        $data_name = $row_name[0];
    }  
    return $data_name;                
} 
        
        
//функция получения заголовков проверки        
function GetHeder($num){       
    global $open_connection;
    
    $query_hed = "  select COLUMN_NAME 
                from MONITORING.DQ_HEADINGS_CONTROL
                where LIST_ID = $num 
             ";
                
    ora_parse($open_connection, $query_hed, 0);         
	ora_exec($open_connection);
    
   while(ora_fetch_into($open_connection, $row_hed))
    { 
        $data_hed[] = $row_hed[0];
    }  
    return $data_hed;                 
}   
   
        
//функция получения результатов проверки        
function GetData($num){       
    global $open_connection;
    
    //получаем текст запроса
    $query_res_sel = "  
                SELECT 'select ' || REPLACE (sql_text, '#')
|| ' from MONITORING.DQ_LIST_CONTROL, MONITORING.DQ_DETAIL where DQ_DETAIL.DQ_CONTROL_UK= DQ_LIST_CONTROL.LIST_ID and DQ_LIST_CONTROL.LIST_ID = '
|| $num
|| ' and REPORT_TIME in (SELECT max(REPORT_TIME) FROM MONITORING.DQ_DETAIL WHERE DQ_CONTROL_UK ='
|| $num
|| ') AND error_flag in (''Y'')'
  AS sql_text
FROM (    SELECT rn,
           cnt,
           list_id,
           SYS_CONNECT_BY_PATH (sql_text, '#') sql_text
      FROM (SELECT rn,
                   cnt,
                   list_id,
                   sql_text,
                   LAG (rn, 1, 0) OVER (PARTITION BY list_id ORDER BY rn)
                      ld_rn
              FROM (SELECT TO_NUMBER (
                              list_id
                              || ROW_NUMBER ()
                                 OVER (PARTITION BY list_id
                                       ORDER BY sort_num)) rn,
                           list_id,
                           TO_NUMBER (
                              list_id
                              || COUNT (1) OVER (PARTITION BY list_id))
                              cnt,
                           CASE
                              WHEN sort_num =
                                      MIN (sort_num)
                                         OVER (PARTITION BY list_id)
                              THEN
                                    table_name
                                 || '.'
                                 || field_name
                              ELSE
                                    ','
                                 || table_name
                                 || '.'
                                 || field_name
                           END
                              sql_text
                      FROM DQ_HEADINGS_CONTROL
                     WHERE rep_flag = 1
                     and LIST_ID = $num ))
START WITH rn = TO_NUMBER (list_id || 1)
CONNECT BY PRIOR rn = ld_rn)
WHERE rn = cnt
             ";
                
    ora_parse($open_connection, $query_res_sel, 0);         
	ora_exec($open_connection);
    
   while(ora_fetch_into($open_connection, $row_res_sel))
    { 
        $query_res = $row_res_sel[0];
    }            
    
    // выполняем запрос
    $exec_query = "$query_res";            
    ora_parse($open_connection, $exec_query, 0);         
	ora_exec($open_connection); 
    
    while(ora_fetch_into($open_connection, $row_res))
    { 
        $data_res[] = $row_res;
    } 
    
    return $data_res;               
}          

//функция получения списка почтовых адресов
function GetEmail($systems_name_quot){
    global $open_connection;
            
            $query_mail = "  SELECT EMAIL FROM MONITORING.DQ_CONTROL_MAIL
                        WHERE SYSTEM IN ($systems_name_quot)
                     ";
                        
            ora_parse($open_connection, $query_mail, 0);         
        	ora_exec($open_connection);
            
           while(ora_fetch_into($open_connection, $row_mail))
            { 
                $data_mail[] = $row_mail[0];
            }  
            return $data_mail;  
}


?>