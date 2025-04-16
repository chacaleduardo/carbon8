<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");
$idamostra= $_OBJ['idamostra'];

if( empty($idamostra) ){
	die("Não foi informado o ID amostra");
}

$statusp= traduzid('amostra', 'idamostra', 'status', $rl["idamostra"]);
if($statusp=='PROVISORIO'){	
	$Vstatusp = 'PD'; 
}else{
	$Vstatusp = ''; 
}

$reslotetiqueta = EtiquetaController::buscarLoteEtiquetaResultado( getidempresa('r.idempresa',''), $idamostra );

if(count($reslotetiqueta) > 0){

    foreach($reslotetiqueta as $k => $rl) {

        $res = EtiquetaController::buscarInfoEtiquetaAmostra( $rl["idamostra"], $rl["loteetiqueta"]);
        $qtd=count($res);
        $tpag=ceil($qtd/7);
        $l=0;
        $cabecalho = EtiquetaController::$cabecalhoTSPL60x40;
        $pagina=0;
        foreach($res as $k1 => $row) {
        
            if(!empty($row['idsecretaria'])){
                $of=" (OF)";
            }else{
                $of=" ";
            }
            
            if($l==0){
                $altura="60";
                $_CONTEUDOIMPRESSAO.=$cabecalho;
                $_CONTEUDOIMPRESSAO.='
TEXT 10,20,"2",0,1,1,"'.$Vstatusp.''.str_pad($row['idregistro'],6).' '.retira_acentos($row['tipoamostra']).'"';
                
    /*			$strprint.='
    REVERSE 6,12,80,30';*/
                // se tiver nucleo
                if(!empty($row['idregistroprovisorio'])){
                    $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"",0,1,1," P-'.$row['idregistroprovisorio'].' "';
                    $altura=$altura+30;
                }
                if(!empty($row['nucleo']) and ($row['nucleo']!=" - ")){
                    //$altura="90";
                    $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos(str_replace(' ', '',trim($row['nucleo']))).'" ';
                    $altura=$altura+30;
                }
                if(!empty($row['lote'])){
                    $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"",0,1,1," '.$row['lote'].' "';
                    $altura=$altura+30;
                }
                if(!empty($row['galpao'])){
                    $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"",0,1,1," '.$row['galpao'].' "';
                    $altura=$altura+30;
                }
                if(!empty($row['localcoleta'])){
                    $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"",0,1,1," '.$row['localcoleta'].' "';
                    $altura=$altura+30;
                }

                if(!empty($row['lacre'])){
            
                    $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"",0,1,1," '.$row['lacre'].' "';
                    $altura=$altura+30;
                }
                if(!empty($row['estexterno'])){
                    $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"",0,1,1,"Reg. Externo '.$row['estexterno'].' "';
                    $altura=$altura+30;
                }
    
                    $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"",0,1,1," '.$row['quantidade'].' - '.retira_acentos($row['codprodserv']).' '.$of.' "';
            }else{
                $altura=$altura+30;
                $_CONTEUDOIMPRESSAO.='
TEXT 10,'.$altura.',"",0,1,1," '.$row['quantidade'].' - '.retira_acentos($row['codprodserv']).' '.$of.' "';
            }
            $l=$l+1;
            if($l==7){
                $pagina=$pagina+1;
                $l=0;
                $_CONTEUDOIMPRESSAO.='
TEXT 390,300,"",0,1,1," '.$pagina.'/'.$tpag.' "';
                $_CONTEUDOIMPRESSAO.="
PRINT 1
            ";
            
            }
        }//while($row=mysql_fetch_assoc($res))
        if($l>0){
            $pagina=$pagina+1;
            $_CONTEUDOIMPRESSAO.='
TEXT 390,300,"",0,1,1," '.$pagina.'/'.$tpag.' "';
            $_CONTEUDOIMPRESSAO.="
PRINT 1
            ";
        }
    
        //Grava histórico de impressão
        EtiquetaController::versionarEtiqueta($_POST['idempresa'],$rl['idamostra'],$_SESSION["SESSAO"]["USUARIO"]);
    }
}
?>