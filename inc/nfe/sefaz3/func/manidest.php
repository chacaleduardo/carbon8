<?php
include_once("../../../php/functions.php");


//conectabanco();
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

$sql="select idnfe from nf where idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['idnfe']) ){
	die("N�o foi encontrada a chave da notafiscal");
}

require_once('../libs/NFe/ToolsNFePHP.class.php');
$nfe = new ToolsNFePHP('',1,false);
$modSOAP = '2'; //usando cURL
$tpAmb = '1';//usando produ��o
$chNFe = $row['idnfe'];

/*
$tpEvento = '210200';//Confirmacao da Operacao //confirma a opera��o e o recebimento da mercadoria (para as opera��es com circula��o de mercadoria)
                    //Ap�s a Confirma��o da Opera��o pelo destinat�rio, a empresa emitente fica automaticamente impedida de cancelar a NF-e
                    
$tpEvento = '210210'; //Ciencia da Operacao //encrenca !!! N�o usar
                    //O evento de "Ci�ncia da Opera��o" � um evento opcional e pode ser evitado
                    //Ap�s um per�odo determinado, todas as opera��es com "Ci�ncia da Opera��o" dever�o
                    //obrigatoriamente ter a manifesta��o final do destinat�rio declarada em um dos eventos de
                    //Confirma��o da Opera��o, Desconhecimento ou Opera��o n�o Realizada
                    
$tpEvento = '210220'; //Desconhecimento da Operacao
                    //Uma empresa pode ficar sabendo das opera��es destinadas a um determinado CNPJ
                    //consultando o "Servi�o de Consulta da Rela��o de Documentos Destinados" ao seu CNPJ.
                    //O evento de "Desconhecimento da Opera��o" permite ao destinat�rio informar o seu
                    //desconhecimento de uma determinada opera��o que conste nesta rela��o, por exemplo
                    
$tpEvento = '210240'; //Operacao nao Realizada 
                      //n�o aceita��o no recebimento que antes se fazia com apenas um carimbo na NF
 */

$tpEvento = '210200';
$resp = '';

if (!$xml = $nfe->manifDest($chNFe,$tpEvento,'',$tpAmb,$modSOAP,$resp)){
    header('Content-type: text/html; charset=UTF-8');
    echo "Houve erro !! $nfe->errMsg";
    //echo '<br><br><PRE>';
   // echo htmlspecialchars($nfe->soapDebug);
  // echo '</PRE><BR>';
} else {
	
	//nao juntar os updates
	$sql1="update nf
	set envionfe='MANIFESTADA'
	where idnf = ".$idnotafiscal;
	$retx1 = d::b()->query($sql1) or die("Erro ao atualizar nf sql:".$sql1);
	
	$sql="update nf 
	set xml = '".$xml."'
	where idnf = ".$idnotafiscal;
	$retx = d::b()->query($sql);
	
    header('Content-type: text/xml; charset=UTF-8');
   // echo($xml);
   echo("OK");
    //echo '<BR><BR><BR><BR><BR>';
    //print_r($resp);
}

?>
<?php
include_once("../../../functions.php");


//conectabanco();
$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

$sql="select idnfe from nf where idnf=".$idnotafiscal;
$res=mysql_query($sql) or die(mysql_error()." erro ao buscar o numero do recibo ".$sql);
$row=mysql_fetch_assoc($res);

if(empty($row['idnfe']) ){
	die("Não foi encontrada a chave da notafiscal");
}

require_once('../libs/NFe/ToolsNFePHP.class.php');
$nfe = new ToolsNFePHP('',1,false);
$modSOAP = '2'; //usando cURL
$tpAmb = '1';//usando produção
$chNFe = $row['idnfe'];

/*
$tpEvento = '210200';//Confirmacao da Operacao //confirma a opera��o e o recebimento da mercadoria (para as opera��es com circula��o de mercadoria)
                    //Ap�s a Confirma��o da Opera��o pelo destinat�rio, a empresa emitente fica automaticamente impedida de cancelar a NF-e
                    
$tpEvento = '210210'; //Ciencia da Operacao //encrenca !!! N�o usar
                    //O evento de "Ci�ncia da Opera��o" � um evento opcional e pode ser evitado
                    //Ap�s um per�odo determinado, todas as opera��es com "Ci�ncia da Opera��o" dever�o
                    //obrigatoriamente ter a manifesta��o final do destinat�rio declarada em um dos eventos de
                    //Confirma��o da Opera��o, Desconhecimento ou Opera��o n�o Realizada
                    
$tpEvento = '210220'; //Desconhecimento da Operacao
                    //Uma empresa pode ficar sabendo das opera��es destinadas a um determinado CNPJ
                    //consultando o "Servi�o de Consulta da Rela��o de Documentos Destinados" ao seu CNPJ.
                    //O evento de "Desconhecimento da Opera��o" permite ao destinat�rio informar o seu
                    //desconhecimento de uma determinada opera��o que conste nesta rela��o, por exemplo
                    
$tpEvento = '210240'; //Operacao nao Realizada 
                      //n�o aceita��o no recebimento que antes se fazia com apenas um carimbo na NF
 */

$tpEvento = '210200';
$resp = '';

if (!$xml = $nfe->manifDest($chNFe,$tpEvento,'',$tpAmb,$modSOAP,$resp)){
    header('Content-type: text/html; charset=UTF-8');
    echo "Houve erro !! $nfe->errMsg";
    //echo '<br><br><PRE>';
   // echo htmlspecialchars($nfe->soapDebug);
  // echo '</PRE><BR>';
} else {
	
	//nao juntar os updates
	$sql1="update nf
	set envionfe='MANIFESTADA'
	where idnf = ".$idnotafiscal;
	$retx1 = mysql_query($sql1) or die("Erro ao atualizar nf sql:".$sql1);
	
	$sql="update nf 
	set xml = '".$xml."'
	where idnf = ".$idnotafiscal;
	$retx = mysql_query($sql);
	
    header('Content-type: text/xml; charset=UTF-8');
   // echo($xml);
   echo("OK");
    //echo '<BR><BR><BR><BR><BR>';
    //print_r($resp);
}

?>
