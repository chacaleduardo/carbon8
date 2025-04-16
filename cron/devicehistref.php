<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	$prefu="stdin_";
	include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
}


    
  echo  $sql=" 
        select
            iddevice,
            calib_press,
            iddeviceref,
            (select max(registradoem) as ultimoregistro from devicehistref dhr where dhr.iddevice = d.iddevice) ultimoregistro,
            mac_address
        from
            device d
        where
            d.status = 'ATIVO'
      
        order by
            d.iddevice;";
    

    $res = d::b()->query($sql) or die("erro ao buscar devices: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
  
     //echo("<!-- primeiro: ".$sqlv." -->");
?>
  <!-- sql-2= <?=$sql?>-->
<?
        
    $ins = '';
    while($row= mysqli_fetch_assoc($res)){
        
            if(empty($row['ultimoregistro'])){

       echo         $sql1=" 
                        select registradoem as ultimoregistro 
                        from devicehist dhr where dhr.mac_address = '".$row['mac_address']."' 
                        order by alteradoem  limit 1;";
                
                $res1 = d::b()->query($sql1) or die("erro ao buscar devices: " . mysqli_error(d::b()) . "<p>SQL: ".$sql1);
                while($linha= mysqli_fetch_assoc($res1)){
                    $row['ultimoregistro'] = $linha['ultimoregistro'];
                }
                echo("<!-- primeiro: ".$sql1." -->");
                ?>
                <!-- sql-2= <?=$sql1?>-->
                <?
             }

        
        if (!empty($row['ultimoregistro'])){
     
      echo         $s = "
            select
            d.iddevice,
            round(AVG(dh.pressao),2) as pressao,
            FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(dh.registradoem) / (2*60))
                                * (2*60))  registrado,
            d.calib_press,
            d.idempresa,
            dh.criadopor,
            dh.criadoem,
            dh.alteradopor,
            dh.alteradoem
                            
        FROM
            devicehist dh
                JOIN
            device d ON d.mac_address = dh.mac_address
        WHERE
            d.iddevice = ".$row['iddevice']."
            and dh.registradoem >  DATE_ADD('".$row['ultimoregistro']."', INTERVAL 2 MINUTE)
            group by 
            registrado; ";
            echo("<!-- primeiro: ".$s." -->");
            $rs = d::b()->query($s) or die("erro ao buscar leituras: " . mysqli_error(d::b()) . "<p>SQL: ".$s);
            $qtd= mysqli_num_rows($rs);
            
            while($r= mysqli_fetch_assoc($rs)){
                if (empty($row['calib_press'])){
                    $row['calib_press'] = "null";
                }
                if (empty($row['iddeviceref'])){
                    $row['iddeviceref'] = "null";
                }
                $ins .= $virgula."(
                            '".$row['iddevice']."', 
                            ".$row['iddeviceref'].",
                            ".$r['pressao'].",
                            ".$row['calib_press'].",
                            '".$r['registrado']."',
                            '".$r['idempresa']."',
                            '".$r['criadopor']."',
                            '".$r['criadoem']."',
                            '".$r['alteradopor']."',
                            '".$r['alteradoem']."'
                        )";
                    $virgula = ',';


            }
        }
    }

    if (!empty($virgula)){
        echo  $sql_insert = "INSERT INTO devicehistref (iddevice, iddeviceref, pressao, calib_press, registradoem, idempresa, criadopor, criadoem, alteradopor, alteradoem)
        values ".$ins."";  
        ?>

        <!-- sql-2= <?=$sql_insert?>-->

        
        <?
        
     $rs = d::b()->query($sql_insert) or die("erro ao inserir leituras: " . mysqli_error(d::b()) . "<p>SQL: ".$s);

    }
               
?>                      
