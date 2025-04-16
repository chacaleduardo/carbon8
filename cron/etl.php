<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

$executadoem=date("Y-m-d H:i:s");

if (defined('STDIN')){//se estiver sendo executao em linhade comando
    include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
    include_once("../inc/php/functions.php");
}
//busca  as configurações ativas validas
$s1="select c.* from etlconf c 
            where c.status ='ATIVO'
            and c.multiplo is not null
            and c.apartirde <= now()
            and exists (select 1 from etlconffiltros f where f.valor!=' ' and f.valor is not null and f.idetlconf = c.idetlconf)
            and exists (select 1 from etlconffiltros f2 where f2.somaf <>'' and f2.idetlconf = c.idetlconf )
            and exists (select 1 from "._DBCARBON."._mtotabcol tc where tc.tab = c.tabela and tc.primkey ='Y')";

$re1=d::b()->query($s1) or die("A Consulta na etlconf falhou : " . mysqli_error(d::b()) . "<p>SQL: $s1");

while($r1=mysqli_fetch_assoc($re1)){
    
    //verica se esta pendente para executar
    $s2="select c.* from etlconf c 
                where c.idetlconf = ".$r1['idetlconf']."
                and ((c.executadoem is null) or(sysdate() >= (DATE_ADD(executadoem, INTERVAL ".$r1['multiplo']."))))";

    $re2=d::b()->query($s2) or die("A consulta para verificar se e para rodar falhou : " . mysqli_error(d::b()) . "<p>SQL: $s2");
    $qtd=mysqli_num_rows($re2);
    if($qtd>0){
        $r2=mysqli_fetch_assoc($re2);

        //busca os filtros para seleção
        $sqlf="select a.col,a.sinal,a.valor,a.idetlconffiltros,a.nowdias,a.grpconcat,a.somaf ,a.addselect,a.separador ,a.grp,( select  c.col from   carbonnovo._mtotabcol c where (c.tab=e.tabela and c.primkey='Y' )) as idtipoobjeto,tb.datatype
                from etlconffiltros a join  etlconf e on(e.idetlconf=a.idetlconf)
                join "._DBCARBON."._mtotabcol tb on(tb.tab=e.tabela and tb.col=a.col )
                where ((a.valor!='' and a.valor is not null )
                or (a.somaf <> '') or (a.grp='Y') or (a.separador='Y') or (a.addselect='Y') or (a.grpconcat='Y'))
                and a.idetlconf =".$r2["idetlconf"];
                        //echo($sqlf);
        $resf=d::b()->query($sqlf) or die("A Consulta na etlconffiltros falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlf");
        $and=" ";
        $somaf=" ";
        $grpconcat=" ";
        $group=" ";
        $colseparador="";
        $colseparadorselect="";
        $grouby=' group by ';
        $clausula='';
        $colselect = '';
        $virg = '';
    
        while($rowf=mysqli_fetch_assoc($resf)){
            if($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!=''){
                if($rowf["valor"]=='now'){
                    if($rowf['datatype']=='date'){
                        $valor=date("Y-m-d"); 
                    }else{
                        $valor=date("Y-m-d H:i:s");
                    }
                        
                }else if($rowf["valor"]=='mais'){
                    if(!empty($rowf["nowdias"])){
                        if($rowf['datatype']=='date'){
                            $date=date("Y-m-d");
                            $valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                        }else{
                            $date=date("Y-m-d H:i:s");
                            $valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                        }
                    }else{
                        if($rowf['datatype']=='date'){
                            $valor=date("Y-m-d"); 
                        }else{
                            $valor=date("Y-m-d H:i:s");
                        } 
                    }
                }else if($rowf["valor"]=='menos'){
                    if(!empty($rowf["nowdias"])){
                        if($rowf['datatype']=='date'){
                            $date=date("Y-m-d");
                            $valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                        }else{
                            $date=date("Y-m-d H:i:s");
                            $valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                        }

                        
                    }else{
                        if($rowf['datatype']=='date'){
                            $valor=date("Y-m-d"); 
                        }else{
                            $valor=date("Y-m-d H:i:s");
                        }
                    }
                }else{
                    $valor=$rowf["valor"];  
                }							
                if($rowf['sinal']=='in'){
                    $strvalor = str_replace(",","','",$valor);
                    $clausula.= $and." a.".$rowf["col"]." in ('".$strvalor."')";
                }elseif($rowf['sinal']=='like'){
                    $clausula.= $and." a.".$rowf["col"]." like ('%".$valor."%')";
                }else{
                    $clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." '".$valor."'";
                }
                $and=" and ";
            }

            if($rowf['somaf'] == 'semf'){

                $somaf=','.$rowf['col'].'';
                $colvalsum=$rowf['col'];

            }elseif($rowf['somaf'] == 'sum'){

                $somaf=',sum('.$rowf['col'].') as '.$rowf['col'];
                $colvalsum=$rowf['col'];

            }elseif($rowf['somaf'] == 'count'){

                $somaf=',count('.$rowf['col'].') as '.$rowf['col'];
                $colvalsum=$rowf['col'];

            }elseif($rowf['somaf'] == 'countdist'){

                $somaf=',count(distinct('.$rowf['col'].')) as '.$rowf['col'];
                $colvalsum=$rowf['col'];

            }elseif($rowf['somaf'] == 'sum'){

                $somaf=',sum('.$rowf['col'].') as '.$rowf['col'];
                $colvalsum=$rowf['col'];

            }else{

            }

            if($rowf['grpconcat'] == 'grpdist'){
                $grpconcat=',group_concat(distinct('.$rowf['col'].')) as concat'.$rowf['col'];
            }elseif($rowf['grpconcat'] == 'grp'){
                $grpconcat=',group_concat('.$rowf['col'].') as concat'.$rowf['col'];
            }else{

            }

            if($rowf['grp']=='Y'){
                $group.=$grouby.$rowf['col'];
                $grouby=',';
            }

            if($rowf['separador']=='Y'){
                $colseparador=$rowf['col'];
                $colseparadorselect=",".$rowf['col'];
            }
            if($rowf['addselect']=='Y'){
                $virg=',';
                $colselect.=$virg.$rowf['col'];
            }

            $colid=$rowf['idtipoobjeto'];
            
        }



        if($r1['repetereg']=='N'){
            $andnot="and not exists(select 1 from etl e 
                                        join etlitem i on(i.idetl=e.idetl) 
                                        where e.idetlconf = ".$r1['idetlconf']."
                                        and i.idobjeto =  a.".$colid." 
                                        and i.objeto='".$r2["tabela"]."' )";
        }else{
            $andnot=" "; 
        }

        $sqlx="SELECT 
                idempresa,a.".$colid." AS idpk ".$grpconcat." ".$somaf."".$colseparadorselect.$colselect."
            FROM
                ".$r2["tabela"]." a 
            WHERE
                ".$clausula." ".$andnot." ".$group." order by idempresa ".$colseparadorselect;
        echo($sqlx.";<br>");
        //die();
        $today =date('Y-m-d'); 
        

        $rex=d::b()->query($sqlx) or die("A Consulta executada pela configuração ".$r2["idetlconf"]." falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlx");
        $arrColunas = mysqli_fetch_fields($rex);
        $qtdx=mysqli_num_rows($rex);
        if($qtdx>0){    

             
            if($r2['sepempresa']=='N' and empty($colseparador)){
                
                $sm = "INSERT INTO etl
                (idempresa,consulta,idetlconf,criacao,titulo,objeto,valor,criadoem,criadopor,alteradopor,alteradoem)
                VALUES
                (".$r['idempresa'].",'".addslashes($sqlx)."',".$r1['idetlconf'].",'".$today."','".$r1['titulo']."','".$r1['tabela']."','0.0000',now(),'crontab','crontab',now());
                "; 
                
                d::b()->query($sm) or die("Erro ao criar etl: ".mysqli_error(d::b())."sql=".$sm);
                //recupera o ultimo ID inserido
                $idetl = mysqli_insert_id(d::b());
            }
         
            $i=0;
            $tvalor=0;
            $arrret=array();
            $idempresa=0;
            $valseparador=0;
            $trsep='';
            while($r=mysqli_fetch_assoc($rex)){
                
                if($r2['sepempresa']=='Y'){
                    if($idempresa != $r['idempresa']){

                        if($idempresa > 0 ){
                            
                            $aRes["dados"][$idetl]=$arrret;

                           
                        
                            $sqlup="update etl set ".$trsep." itensjson='".base64_encode(serialize($aRes))."',valor='".$tvalor."' where idetl=".$idetl;
                            d::b()->query($sqlup) or die("Erro ao atulizar etl: ".mysqli_error(d::b()));
                            unset($arrret);
                            unset ($aRes);
                            $tvalor=0;
                            
                        }


                        $sm = "INSERT INTO etl
                        (idempresa,consulta,idetlconf,criacao,titulo,objeto,valor,criadoem,criadopor,alteradopor,alteradoem)
                        VALUES
                        (".$r['idempresa'].",'".addslashes($sqlx)."',".$r1['idetlconf'].",'".$today."','".$r1['titulo']."','".$r1['tabela']."','0.0000',now(),'crontab','crontab',now());
                        "; 
                        
                        d::b()->query($sm) or die("Erro ao criar etl: ".mysqli_error(d::b())."sql=".$sm);
                        //recupera o ultimo ID inserido
                        $idetl = mysqli_insert_id(d::b());

                        $idempresa = $r['idempresa'];

                        $valseparador = $r[$colseparador];

                        $trsep= " idobjeto='".$r[$colseparador]."', ";
                    
                    }
                }

                if(!empty($colseparador)){
                    if($valseparador != $r[$colseparador]){

                        if($valseparador > 0 ){
                            
                            $aRes["dados"][$idetl]=$arrret;
                           
                            $sqlup="update etl set ".$trsep." itensjson='".base64_encode(serialize($aRes))."',valor='".$tvalor."' where idetl=".$idetl;
                            d::b()->query($sqlup) or die("Erro ao atulizar etl: ".mysqli_error(d::b()));
                            unset($arrret);
                            unset ($aRes);
                            $tvalor=0;
                            
                        }    
    
                        $sm = "INSERT INTO etl
                        (idempresa,consulta,idetlconf,criacao,titulo,objeto,valor,criadoem,criadopor,alteradopor,alteradoem)
                        VALUES
                        (".$r['idempresa'].",'".addslashes($sqlx)."',".$r1['idetlconf'].",'".$today."','".$r1['titulo']."','".$r1['tabela']."','0.0000',now(),'crontab','crontab',now());
                        "; 
                        
                        d::b()->query($sm) or die("Erro ao criar etl: ".mysqli_error(d::b())."sql=".$sm);
                        //recupera o ultimo ID inserido
                        $idetl = mysqli_insert_id(d::b());
    
                        $valseparador = $r[$colseparador];
                        $trsep= " idobjeto='".$r[$colseparador]."', ";
                       
                    }
                }//if(!empty($colseparador))

                $i++;
                foreach ($arrColunas as $col) {                  
                    $arrret[$i][$col->name]=$r[$col->name];      
                }

                $tvalor=$tvalor+$r[$colvalsum];

                if(!empty($r['idempresa']) and !empty($idetl)){
                    $sqli="INSERT INTO etlitem
                    (idempresa,idetl,idobjeto,objeto,valor,criadopor,criadoem,alteradopor,alteradoem)
                    VALUES
                    (".$r['idempresa'].",".$idetl.",".$r['idpk'].",'".$r1['tabela']."','".$r[$colvalsum]."','crontab',now(),'crontab',now())";
                    d::b()->query($sqli) or die("Erro ao criar etlitem: ".mysqli_error(d::b())." sql=".$sqli);
                }
                     
             
            }
            $aRes["dados"][$idetl]=$arrret;
                       
            $sqlup="update etl set itensjson='".base64_encode(serialize($aRes))."',valor='".$tvalor."' where idetl=".$idetl;
            d::b()->query($sqlup) or die("Erro ao atulizar etl: ".mysqli_error(d::b()));
            unset($arrret);
            unset ($aRes);
            $tvalor=0;           
           
        }

        $pos = strpos($r1['multiplo'], 'DAY');
        if($pos === false){
            $sqlupf="update etlconf set executadoem='".$executadoem."' where idetlconf=".$r1['idetlconf'];
            d::b()->query($sqlupf) or die("Erro ao atulizar etl config: ".mysqli_error(d::b()));
        }else{
            $sqlupf="update etlconf set executadoem=concat(SUBSTRING('".$executadoem."',1,11),SUBSTRING(apartirde,12,8))  where idetlconf=".$r1['idetlconf'];
            d::b()->query($sqlupf) or die("Erro ao atulizar etl config 2: ".mysqli_error(d::b()));
        }
       
    }elseif($_GET['_inspecionar_sql'] == "Y"){//$qtd

        //busca os filtros para seleção
        $sqlf="select a.col,a.sinal,a.valor,a.idetlconffiltros,a.nowdias,a.grpconcat,a.somaf ,a.addselect,a.separador ,a.grp,( select  c.col from   carbonnovo._mtotabcol c where (c.tab=e.tabela and c.primkey='Y' )) as idtipoobjeto,tb.datatype
                from etlconffiltros a join  etlconf e on(e.idetlconf=a.idetlconf)
                join "._DBCARBON."._mtotabcol tb on(tb.tab=e.tabela and tb.col=a.col )
                where ((a.valor!='' and a.valor is not null )
                or (a.somaf <> '') or (a.grp='Y') or (a.separador='Y') or (a.addselect='Y') or (a.grpconcat='Y'))
                and a.idetlconf =".$r1["idetlconf"];
                        //echo($sqlf);
        $resf=d::b()->query($sqlf) or die("A Consulta na etlconffiltros falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlf");
        $and=" ";
        $somaf=" ";
        $grpconcat=" ";
        $group=" ";
        $colseparador="";
        $colseparadorselect="";
        $grouby=' group by ';
        $clausula='';
        $colselect = '';
        $virg = '';

        while($rowf=mysqli_fetch_assoc($resf)){
            if($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!=''){
                if($rowf["valor"]=='now'){
                    if($rowf['datatype']=='date'){
                        $valor=date("Y-m-d"); 
                    }else{
                        $valor=date("Y-m-d H:i:s");
                    }
                        
                }else if($rowf["valor"]=='mais'){
                    if(!empty($rowf["nowdias"])){
                        if($rowf['datatype']=='date'){
                            $date=date("Y-m-d");
                            $valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                        }else{
                            $date=date("Y-m-d H:i:s");
                            $valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                        }
                    }else{
                        if($rowf['datatype']=='date'){
                            $valor=date("Y-m-d"); 
                        }else{
                            $valor=date("Y-m-d H:i:s");
                        } 
                    }
                }else if($rowf["valor"]=='menos'){
                    if(!empty($rowf["nowdias"])){
                        if($rowf['datatype']=='date'){
                            $date=date("Y-m-d");
                            $valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                        }else{
                            $date=date("Y-m-d H:i:s");
                            $valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                        }

                        
                    }else{
                        if($rowf['datatype']=='date'){
                            $valor=date("Y-m-d"); 
                        }else{
                            $valor=date("Y-m-d H:i:s");
                        }
                    }
                }else{
                    $valor=$rowf["valor"];  
                }							
                if($rowf['sinal']=='in'){
                    $strvalor = str_replace(",","','",$valor);
                    $clausula.= $and." a.".$rowf["col"]." in ('".$strvalor."')";
                }elseif($rowf['sinal']=='like'){
                    $clausula.= $and." a.".$rowf["col"]." like ('%".$valor."%')";
                }else{
                    $clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." '".$valor."'";
                }
                $and=" and ";
            }

            if($rowf['somaf'] == 'semf'){

                $somaf=','.$rowf['col'].'';
                $colvalsum=$rowf['col'];

            }elseif($rowf['somaf'] == 'sum'){

                $somaf=',sum('.$rowf['col'].') as '.$rowf['col'];
                $colvalsum=$rowf['col'];

            }elseif($rowf['somaf'] == 'count'){

                $somaf=',count('.$rowf['col'].') as '.$rowf['col'];
                $colvalsum=$rowf['col'];

            }elseif($rowf['somaf'] == 'countdist'){

                $somaf=',count(distinct('.$rowf['col'].')) as '.$rowf['col'];
                $colvalsum=$rowf['col'];

            }elseif($rowf['somaf'] == 'sum'){

                $somaf=',sum('.$rowf['col'].') as '.$rowf['col'];
                $colvalsum=$rowf['col'];

            }else{

            }

            if($rowf['grpconcat'] == 'grpdist'){
                $grpconcat=',group_concat(distinct('.$rowf['col'].')) as concat'.$rowf['col'];
            }elseif($rowf['grpconcat'] == 'grp'){
                $grpconcat=',group_concat('.$rowf['col'].') as concat'.$rowf['col'];
            }else{

            }

            if($rowf['grp']=='Y'){
                $group.=$grouby.$rowf['col'];
                $grouby=',';
            }

            if($rowf['separador']=='Y'){
                $colseparador=$rowf['col'];
                $colseparadorselect=",".$rowf['col'];
            }
            if($rowf['addselect']=='Y'){
                $virg=',';
                $colselect.=$virg.$rowf['col'];
            }

            $colid=$rowf['idtipoobjeto'];
    
        }

        $sqlx="SELECT 
                idempresa,a.".$colid." AS idpk ".$grpconcat." ".$somaf."".$colseparadorselect.$colselect."
            FROM
                ".$r1["tabela"]." a 
            WHERE
                ".$clausula." ".$andnot." ".$group." order by idempresa ".$colseparadorselect;
        echo('ETLCONF '.$r1["idetlconf"].' ==>  '.$sqlx.";<br><br>");
    }
   
 

    
}//while($r1=mysqli_fetch_assoc($re1))

?>