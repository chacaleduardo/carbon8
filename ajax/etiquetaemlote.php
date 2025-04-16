<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    header("HTTP/1.1 401 Unauthorized");
    die;
}
$ip = $_POST['ip'];
foreach($_POST['data'] as $data){
    [ $idtag, $coluna, $linha ] = explode('_',$data);
    $clausulaLote = '';

    if($_POST['idlote']) {
        $clausulaLote = " and l.idlote = {$_POST['idlote']}";
    }
    
    $sql="SELECT l.idlote,concat(l.spartida,l.npartida,'/',l.exercicio) as descr,p.descr as produto,
        LEFT(p.descr,40) as nomeinicio,LEFT(SUBSTRING(p.descr,41),40) as nomemeio,
        SUBSTRING(p.descr,81) as nomefim,
        l.criadoem as criadoem,
        dma(l.vencimento) as vencimento,l.idempresa,e.sigla,concat(t.descricao,' ',concat(case tp.coluna 
        when 0 then '0' when 1 then 'A'	when 2 then 'B' when 3 then 'C' when 4 then 'D'	when 5 then 'E'	when 6 then 'F'
        when 7 then 'G' when 8 then 'H' when 9 then 'I' when 10 then 'J' when 11 then 'K' when 12 then 'L'
        when 13 then 'M' when 14 then 'N' when 15 then 'O' when 16 then 'P' when 17 then 'Q' when 18 then 'R'
        when 19 then 'S' when 20 then 'T' when 21 then 'U' when 22 then 'V' when 23 then 'X' when 24 then 'Z'
        end,' ',tp.linha) )as campo,l.observacao as obslote
            from lote l 
            join prodserv p on (p.idprodserv = l.idprodserv)
            join empresa e on (l.idempresa = e.idempresa)
            join lotelocalizacao c on (c.idlote=l.idlote and c.tipoobjeto ='tagdim')
            join tagdim tp on (tp.idtagdim= c.idobjeto)
            join tag t on (tp.idtag = t.idtag)
            join lotefracao f on (f.idlote = l.idlote and f.qtd > 0 and f.idunidade = t.idunidade)
            where t.idtag = $idtag and tp.coluna = $coluna and tp.linha = $linha
            $clausulaLote;";


    $res=d::b()->query($sql);

    if($res and mysqli_num_rows($res) > 0){
        $_CONTEUDOIMPRESSAO = '';
    
        while($row=mysql_fetch_assoc($res)){
                
                for($i = 0; $i < (int)$_POST['qtd']; $i++) {
                    $_CONTEUDOIMPRESSAO .= "^XA
                    ^CF0,200,27
                    ^FO110,20^FH\^FD".$row["sigla"]."^FS
                    ^FO155,10^BQN,2,4,M^FDMA,https://sislaudo.laudolab.com.br/?_modulo=".$_POST["modulo"]."&_acao=u&idlote=".$row['idlote']."^FS^FX
                    ^CF0,25
                    ^FO312,20^FH\^FD".retira_acentos($row['descr'])."^FS
                    ^FO312,60^FH\^FDV: ".$row['vencimento']."^FS
                    ^CF0,19
                    ^FO110,180^FB430,5,,^FH\^FD".retira_acentos($row['produto'])."^FS";

                    if(!empty($row['obslote'])){
                    $_CONTEUDOIMPRESSAO .= "
                    ^CF0,17
                    ^FO110,220^FH\^FDData Lote: ".retira_acentos(dma($row['criadoem']))."^FS
                    ^CF0,17
                    ^FO110,240^FB430,2,,^FH\^FDLocal: ".retira_acentos($row['campo'])."^FS
                    ^FO110,260^FB430,2,,^FH\^FDObs: ".retira_acentos($row['obslote'])."^FS
                    ^XZ";
                    }else{
                    $_CONTEUDOIMPRESSAO .="
                    ^CF0,17
                    ^FO110,240^FH\^FDData Lote: ".retira_acentos(dma($row['criadoem']))."^FS
                    ^CF0,17
                    ^FO110,255^FB430,2,,^FH\^FDLocal: ".retira_acentos($row['campo'])."^FS
                    ^XZ";
                }
            }
                  
            // $_CONTEUDOIMPRESSAO.="%_quebrapagina_%";
        }
    }

}
imprimir($_CONTEUDOIMPRESSAO,$ip);

function imprimir($strprint,$ip){

    // $strprint = explode("%_quebrapagina_%",$strprint);
    // foreach($strprint as $etiqueta){
         try
		{
            $fp = pfsockopen($ip, 9100);
            fputs($fp, $strprint);
            fclose($fp);

            return true;
		}
		catch (Exception $e) {
            return array("erro" => $e->getMessage());
		}
    // }

}
