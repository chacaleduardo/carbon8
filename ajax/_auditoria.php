<?
require_once("../inc/php/validaacesso.php");


if(empty($_GET["objeto"]) or empty($_GET["idobjeto"])){
    die("{}");
}

$sql="select a.coluna, a.valor, a.criadoem
    from laudo._auditoria a 
        left join carbonnovo._mtotabcol mtc 
            on mtc.tab=a.objeto and mtc.col=a.coluna
    where 
        a.objeto='".d::b()->real_escape_string($_GET["objeto"])."' 
        and a.idobjeto=".d::b()->real_escape_string($_GET["idobjeto"])."
        and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
    and a.coluna not in ('criadopor','criadoem','alteradopor','alteradoem','exercicio')
    and mtc.primkey !='Y'
    order by a.criadoem, a.coluna";

$res=mysql_query($sql) or die("Erro#2");    	

$arrRet=array();
//Monta o array filtrando por valores unicos
while ($r=mysql_fetch_assoc($res)){    	
    $arrRet[$r["coluna"]][$r["valor"]]=dmahms($r["criadoem"],true);
//    $arrRet[$r["coluna"]]["data"] = $r["data"];
}

//Remonta o array ordenando pela data
$arrOrd=array();
foreach ($arrRet as $k=>$v){
//	print_r($v);
	foreach ($v as $k2=>$v2){
		$arrOrd[$k][$v2]=$k2;
	}
}
print_r($arrOrd);
die;
echo $JSON->encode($arrRet);
//print_r($arrRet);
