<?
require_once("../inc/php/functions.php");
require_once("../inc/php/laudo.php");
require_once(__DIR__."/../form/controllers/etiqueta_controller.php");
$idamostra= $_GET['idamostra'];
$impressora= $_GET['impressora'];

if( empty($idamostra) ){
	die("Não foi informado o ID amostra");
}

$sqlimp="SELECT t.ip from tag t
            where t.varcarbon='".$impressora."'
            ".share::tagimpressora("t.idtag")."
            and t.ip is not null 
            and t.status in ('ATIVO','ALOCADO')";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do diagnostico: ".mysqli_error(d::b()));
$qtdimp=mysqli_num_rows($resimp);
if($qtdimp<1){die("Não encontrada impressora do diagnostico em tags var carbon.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IMPRESSORA_TERMICA_DIAG_AMOSTRA",$rowimp['ip']);


$reslotetiqueta = EtiquetaController::buscarLoteEtiquetaResultado( getidempresa('r.idempresa',''), $idamostra );
if(count($reslotetiqueta) > 0){
    foreach($reslotetiqueta as $k => $rl) {
		$statusp= traduzid('amostra', 'idamostra', 'status', $rl["idamostra"]);
		if($statusp=='PROVISORIO'){	
			$Vstatusp = 'PD'; 
		}else{
			$Vstatusp = ''; 
		}
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
                $_CONTEUDOIMPRESSAO.='^CF0,30^FO10,'.$altura.'^FD'.$Vstatusp.''.str_pad($row['idregistro'],6).' '.retira_acentos($row['tipoamostra']).'^FS';
                
                /*			$strprint.='
                REVERSE 6,12,80,30';*/
                // se tiver nucleo
                if(!empty($row['idregistroprovisorio'])){
                    $altura=$altura+30;
                    $_CONTEUDOIMPRESSAO.='^CF0,30^FO10,'.$altura.'^FD'.$row['idregistroprovisorio'].'^FS';
                    $altura=$altura+30;
                }
                
                if(!empty($row['nucleo']) and ($row['nucleo']!=" - ")){
                    //$altura="90";
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO10,'.$altura.'^FD'.retira_acentos(str_replace(' ', '',trim($row['nucleo']))).'^FS';
    
                }
                if(!empty($row['lote'])){
            
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO10,'.$altura.'^FD'.$row['lote'].'^FS';
                }
                if(!empty($row['galpao'])){

                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO10,'.$altura.'^FD'.$row['galpao'].'^FS';
                }
            
                if(!empty($row['localcoleta'])){
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO10,'.$altura.'^FD'.$row['localcoleta'].'^FS';
                }

                if(!empty($row['lacre'])){
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO10,'.$altura.'^FD'.$row['lacre'].'^FS';
                }
                if(!empty($row['estexterno'])){
                    $altura=$altura+25;
                    $_CONTEUDOIMPRESSAO.='^CF0,20^FO10,'.$altura.'^FDReg. Externo '.$row['estexterno'].' ^FS';
                }

                $altura=$altura+25;
                $_CONTEUDOIMPRESSAO.='^CF1,20^FO10,'.$altura.'^FD'.$row['quantidade'].' - '.retira_acentos($row['codprodserv']).' '.$of.'^FS';
            }else{
                $altura=$altura+25;
                $_CONTEUDOIMPRESSAO.='^CF1,20^FO10,'.$altura.'^FD'.$row['quantidade'].' - '.retira_acentos($row['codprodserv']).' '.$of.'^FS';
            }
            $l=$l+1;
            if($l==6){
                $pagina=$pagina+1;
                $l=0;
                $_CONTEUDOIMPRESSAO.='^CF1,20^FO400,310^FD'.$pagina.'/'.$tpag.'^FS';
                $_CONTEUDOIMPRESSAO.="^XZ";
            }
        }//while($row=mysql_fetch_assoc($res))
        if($l>0){
            $pagina=$pagina+1;
            $_CONTEUDOIMPRESSAO.='^CF1,20^FO400,310^FD'.$pagina.'/'.$tpag.'^FS';
            $_CONTEUDOIMPRESSAO.="^XZ";
        }
    
        //Grava histórico de impressão
        EtiquetaController::versionarEtiqueta($_GET['_idempresa'],$rl['idamostra'],$_SESSION["SESSAO"]["USUARIO"]);
    }
}

imprimir($_CONTEUDOIMPRESSAO);

function imprimir($content){
	try{
		$fp = pfsockopen(_IMPRESSORA_TERMICA_DIAG_AMOSTRA, 9100);
		fputs($fp, $content);
		fclose($fp);

		return true;
	}catch (Exception $e) {
		return array("erro" => $e->getMessage());
	}
}
