<?
ini_set("display_errors","1");
error_reporting(E_ALL);
session_start();
$sessionid = session_id();//PEGA A SESSÃO 

/*
 * Pega um pedido pedente para transferência e insere uma nf de entrada para outra empresa cadastrada no sistema, dando a entrada automáticamente
 * hermesp 18-08-2020
 */

if (defined('STDIN')){//se estiver sendo executao em linhade comando
    include_once("/var/www/carbon8/inc/php/functions.php");	
}else{//se estiver seno executao via requisicao http
    include_once("../inc/php/functions.php");
}

    $sql="select *
            from nf 
            where statustransf='PENDENTE'
            AND idpessoatransf is not null
            and idempresatransf is not null";
    $res = d::b()->query($sql) or die("cron transferirnf: Falha ao recuperar nf:\n".mysqli_error(d::b())."\n".$sql);
    $arrColunas = mysqli_fetch_fields($res);
     $colidnf="idnf";
    while($r = mysqli_fetch_assoc($res)){
        foreach ($arrColunas as $col) {
            $arrnf[$r[$colidnf]][$col->name]=$r[$col->name];
        }
    }
    

    $lin=0;
    foreach ($arrnf as $_idnf => $arrvalue) {
        $lin=$lin+1;
        $su="update nf set statustransf='CONCLUIDO' where idnf=".$_idnf;
        $ru = d::b()->query($su) or die("cron transferirnf: Falha ao concluir transferencia nf:\n".mysqli_error(d::b())."\n".$su);
        
        
        $insnf = new Insert();
	$insnf->setTable("nf");	
	foreach ($arrvalue as $key => $value) {	   
            // echo "{$key} => {$value} ";            
            if($key=='status'){
                $value="APROVADO";
            }
            if($key=='tiponf'){
                $value="C";
            }
            if($key=='idunidade'){
                $qrUnid = "SELECT idunidade FROM unidade WHERE idtipounidade = 19 and idempresa = ".$arrnf[$_idnf]['idempresatransf'];
                $rsUnid = d::b()->query($qrUnid) or die("[transferirnf][1]: Erro ao buscar idunidade. SQL: ".$qrUnid);
                $rwUnid = mysqli_fetch_assoc($rsUnid);
                $value=$rwUnid['idunidade'];
            }
            if($key=='idpessoa'){
                $value= $arrnf[$_idnf]['idpessoatransf'];
            }
            if($key=='idempresa'){
                $value= $arrnf[$_idnf]['idempresatransf'];
            }
	    if($key=='alteradopor' or $key=='criadopor'){
                $value='Sislaudo Cron';
            }
            if(!empty($value) and $key!='idnf' and $key!='alteradoem' and $key!='criadoem' and $key!='idformapagamento'  and $key!='idtransportadora' 
                    and $key!='idpessoafat' and $key!='idendereco' and $key!='idenderecofat' and $key!='idpessoatransf' and $key!='idempresatransf'  and $key!='statustransf'){
                $insnf->$key=$value;
            }
        }
        $insnf->idobjetosolipor=$_idnf;
        $insnf->tipoobjetosolipor='nf';
        //print_r($insnf); die;
        $idnf_novo=$insnf->save();
        reset($arrnf);
        
        
        $sqli="select * from nfitem where nfe='Y' and idnf=".$_idnf;
        $resi = d::b()->query($sqli) or die("cron transferirnf: Falha ao recuperar nfitem:\n".mysqli_error(d::b())."\n".$sqli);
        $arrColunasi = mysqli_fetch_fields($resi);
        $colid="idnfitem";
        while($ri = mysqli_fetch_assoc($resi)){
            foreach ($arrColunasi as $coli) {
                $arrnfitem[$ri[$colid]][$coli->name]=$ri[$coli->name];
            }
        }
        
        foreach ($arrnfitem as $arritem ) {
	    $insnfItem = new Insert();
	    $insnfItem->setTable("nfitem");
	    foreach ($arritem as $key => $value) {		
		if($key=='idnf'){
		    $value=$idnf_novo;
		}
                if($key=='tiponf'){
                    $value="C";
                }
                if($key=='idempresa'){
                    $value= $arrnf[$_idnf]['idempresatransf'];
                }
                if($key=='alteradopor' or $key=='criadopor'){
                    $value='Transferência';
                }
                
		if(!empty($value) and $key!='idnfitem' and $key!='alteradoem' and $key!='criadoem' and $key!='idprodserv' and $key!='idcontaitem' and $key!='idtipoprodserv'){
		    $insnfItem->$key=$value;
		}
	    }
	    $idnfitem=$insnfItem->save();	    
        }
        reset($arrnfitem);
          
    }
    echo($lin." -Pedido(s) transferido(s)");
  
  
?>
