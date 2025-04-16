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

        if(!$qtd) continue;

        $tpag=ceil($qtd/6);
        $l=0;
        $cabecalho = "^XA";
        $pagina=0;
        foreach($res as $k1 => $row) {
            if(!empty($row['idsecretaria'])){
                $of=" (OF)";
            }else{
                $of=" ";
            }
            
            if($l==0){
                $altura=20;
                $_CONTEUDOIMPRESSAO.=$cabecalho;
                $_CONTEUDOIMPRESSAO.='^CF0,30^FO100,'.$altura.'^FD'.$Vstatusp.''.str_pad($row['idregistro'].'PET',9).' '.retira_acentos($row['tipoamostra']).'^FS';
                
                /*			$strprint.='
                REVERSE 6,12,80,30';*/
                // se tiver nucleo
                if(!empty($row['idregistroprovisorio'])){
                    $altura=$altura+30;
                    $_CONTEUDOIMPRESSAO.='^CF0,30^FO100,'.$altura.'^FD'.$row['idregistroprovisorio'].'PET'.'^FS';
                    $altura=$altura+30;
                }
                
                if(!empty($row['nucleo']) and ($row['nucleo']!=" - ")){
                    //$altura="90";
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO100,'.$altura.'^FD'.retira_acentos(str_replace(' ', '',trim($row['nucleo']))).'^FS';
    
                }
                if(!empty($row['lote'])){
            
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO100,'.$altura.'^FD'.$row['lote'].'^FS';
                }
                if(!empty($row['galpao'])){

                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO100,'.$altura.'^FD'.$row['galpao'].'^FS';
                }
            
                if(!empty($row['localcoleta'])){
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO100,'.$altura.'^FD'.$row['localcoleta'].'^FS';
                }

                if(!empty($row['lacre'])){
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO100,'.$altura.'^FD'.$row['lacre'].'^FS';
                }
                if(!empty($row['estexterno'])){
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO100,'.$altura.'^FDReg. Externo '.$row['estexterno'].' ^FS';
                }

                $altura=$altura+25;
                $_CONTEUDOIMPRESSAO.='^CF1,20^FO100,'.$altura.'^FD'.$row['quantidade'].' - '.retira_acentos($row['codprodserv']).' '.$of.'^FS';
            }else{
                $altura=$altura+25;
                $_CONTEUDOIMPRESSAO.='^CF1,20^FO100,'.$altura.'^FD'.$row['quantidade'].' - '.retira_acentos($row['codprodserv']).' '.$of.'^FS';
            }
            $l=$l+1;
            if($l==6){
                $pagina=$pagina+1;
                $l=0;
                $_CONTEUDOIMPRESSAO.='^CF1,20^FO500,290^FD'.$pagina.'/'.$tpag.'^FS';
                $_CONTEUDOIMPRESSAO.="^XZ
%_quebrapagina_%

            ";
            }
        }//while($row=mysql_fetch_assoc($res))
        if($l>0){
            $pagina=$pagina+1;
            $_CONTEUDOIMPRESSAO.='^CF1,20^FO500,290^FD'.$pagina.'/'.$tpag.'^FS';
            $_CONTEUDOIMPRESSAO.="^XZ
%_quebrapagina_%
            ";
        }
    
        //Grava histórico de impressão
        EtiquetaController::versionarEtiqueta($_POST['idempresa'],$rl['idamostra'],$_SESSION["SESSAO"]["USUARIO"]);
    }
}
