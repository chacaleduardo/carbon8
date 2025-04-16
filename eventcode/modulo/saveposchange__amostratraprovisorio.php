<?
//print_r($_POST);//die;
//rint_r($_SESSION["arrpostbuffer"]);die;

//while (list($chave, $vlr) = each($arrpilha)) {
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$arrInsTestes=array();
foreach($_POST as $k=>$v) {
	if(preg_match("/_(\d*)#(.*)/", $k, $res)){
		$arrInsTestes[$res[1]][$res[2]]=$v;
	}
}

//print_r($arrInsTestes); die;

foreach($arrInsTestes as $k=>$v){
 if(empty($v["identificacao"])){
	//VAlidado para inserir N quando o campo vier vazio - Lidiane (26/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=328394
	if(empty($v["cobrar"])){
		$v["cobrar"]='N';
	}

	//LTM (08-04-2021): Retorna o idfluxostatus de acordo com cada módulo.
	$idamostra = $_SESSION["_pkid"];	
	$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'ABERTO', 'resultado', '', '');	

	$sql = "insert into resultado (
			idempresa,idamostra,idtipoteste,quantidade,idsecretaria,loteetiqueta,npedido,ord,status, idfluxostatus, criadopor,criadoem,alteradopor,alteradoem,cobrar
		)values (
			".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idamostra.",".($v["idtipoteste"]?:"null").",".($v["quantidade"]?:"0").",".($v["idsecretaria"]?:"null").",".($v["loteetiqueta"]?:"0").",'".($v["npedido"]?:"null")."',".($v["ord"]?:"0").",'ABERTO', '".$rowFluxo['idfluxostatus']."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".($v["cobrar"])."'
		)";
	//echo "<br>".$sql;
	$res = d::b()->query($sql);
	if(!$res){
		echo("Erro inserindo teste: ".mysqli_error(d::b()));
	}

	//LTM (08-04-2021): Retorna o idfluxostatus de acordo com cada módulo.
	$idresultado = mysqli_insert_id(d::b());
	FluxoController::inserirFluxoStatusHist($rowFluxo['modulo'], $idresultado, $rowFluxo['idfluxostatus'], 'PENDENTE');

}elseif(!empty($v["identificacao"])){
        $sql = "insert into identificador (
			idempresa,idobjeto,tipoobjeto,identificacao,criadopor,criadoem,alteradopor,alteradoem
		)values (
			".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_SESSION["_pkid"].",'amostra',".($v["identificacao"]?:"null").",'".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now()
		)";
	//echo "<br>".$sql;
	$res = d::b()->query($sql);
	if(!$res){
		echo("Erro inserindo identificacao: ".mysqli_error(d::b()));
	}
    }
     /*   
        if(!empty($v["npedido"])){
            $idresultado= mysqli_insert_id(d::b()); 
          
            $sqlad = "insert into resultadoadd (
			idempresa,idresultado,npedido,criadopor,criadoem,alteradopor,alteradoem
		)values (
			".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idresultado.",'".$v["npedido"]."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now()
		)";
            //echo "<br>".$sql;
            $resad = d::b()->query($sqlad);
            if(!$resad){
                    echo("Erro inserindo adicionais do teste: ".mysqli_error(d::b()));
            }
          
        }
	*/
}

//$sqlAttualizaFts= "call "._DBCARBON."._ftsByHostnameAtualizarDb(true,'laudo')";
//$res = d::b()->query($sqlAttualizaFts) or die("Erro ao atualizar FTS: ".mysqli_error(d::b()));

/*
 * Atualizar nucleo e pessoa na dashboardnucleopessoa para que não ocorra erro na dashbord do cliente
 *  ao alterar idnucleo ou idpessoa em uma amostra
 */
if(!empty($_SESSION['arrpostbuffer']['1']['u']['amostra']['idamostra']) and $_SESSION['arrpostbuffer']['1']['u']['amostra']['idunidade']!=6 and $_SESSION['arrpostbuffer']['1']['u']['amostra']['idunidade']!=9 and $_SESSION['arrpostbuffer']['1']['u']['amostra']['status']!='FECHADO'){
    $idamostra = $_SESSION['arrpostbuffer']['1']['u']['amostra']['idamostra'];
/*
	$sql="update dashboardnucleopessoa dnp 
	    join resultado r on r.idresultado=dnp.idresultado
		join amostra a on a.idamostra=r.idamostra and a.idamostra=".$idamostra."
	set dnp.idnucleo=a.idnucleo, dnp.idcliente=a.idpessoa
	where
	(
		dnp.idnucleo=a.idnucleo or dnp.idcliente=a.idpessoa
	)";
*/

	//LTM (08-04-2021): Retorna as informações do módulo de acordo com o tipo de unidade (modulo)
	$rowFluxo = FluxoController::getDadosResultadoAmostra('resultado', 'idamostra', $idamostra, 'FECHADO', 'amostra', '', 'ASSINADO');	
	if(!empty($rowFluxo['modulo'])){
		FluxoController::restaurarFluxo($rowFluxo['modulo'], 'idresultado', $rowFluxo["idresultado"], $rowFluxo['statustipo'], $rowFluxo['idfluxostatus']);
	}
	
	$idfluxostatus = FluxoController::getIdFluxoStatus('resultsuinos','FECHADO');

    $sql="update resultado set status='FECHADO', idfluxostatus = ".$idfluxostatus." where idamostra=".$idamostra." and status='ASSINADO'";   
    $sqlr="select idresultado from resultado where idamostra= ".$idamostra." and status='ASSINADO'";
    $resr = d::b()->query($sqlr);       
    $res = d::b()->query($sql);
    if(!$res){
        echo("[saveposchange__amostraaves]-Erro ao atualizar resultado Assinado para fechado : ".mysqli_error(d::b()));
    }else{
        $sql="delete a.* from resultadoassinatura a,resultado r,amostra am
                    where a.idresultado = r.idresultado 
                    and r.idamostra = am.idamostra
                    and am.idunidade = 1
                    and am.idamostra=".$idamostra;
        $res = d::b()->query($sql); 
        
        while($rowr=mysqli_fetch_assoc($resr)){    
            $sqlaud = "INSERT INTO `_auditoria` (idempresa,linha,acao,objeto,idobjeto,coluna,valor,criadoem,criadopor,tela) 
            values(".$_SESSION["SESSAO"]["IDEMPRESA"].",'1','u','resultado',".$rowr['idresultado'].",'status','FECHADO',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','".$_SERVER["HTTP_REFERER"]."')";
	    $resaud = mysql_query($sqlaud) or die("ERRO aud: ".mysql_error()."\n SQL: ".$sqlaud);
        }
    }
}

if($_SESSION['arrpostbuffer']['1']['u']['amostra']['idunidade'] == 9 and $_SESSION['arrpostbuffer']['2']['u']['amostra']['status'] == 'ASSINADO'){
	$idamostra = $_SESSION['arrpostbuffer']['1']['u']['amostra']['idamostra'];
	
	$sql="update amostra set status='ASSINAR' where idamostra=".$idamostra."";
	$res = d::b()->query($sql);
	if(!$res){
        echo("[saveposchange__amostraaves]-Erro ao atualizar amostra ASSINADO para ASSINAR : ".mysqli_error(d::b()));
    }

	$idamostra = $_SESSION["arrpostbuffer"]["1"]["u"]["amostra"]["idamostra"];
	$sqlFluxo = "SELECT fs.idfluxostatus, 
							f.idfluxo,
							fs.ordem,
							s.statustipo,
							s.tipobotao,
							m.modulo
						FROM amostra a JOIN unidadeobjeto uo ON a.idunidade = uo.idunidade AND uo.tipoobjeto = 'modulo'
						JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'amostra' 
						JOIN fluxo f ON f.modulo = uo.idobjeto AND f.status = 'ATIVO'
						JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
						JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo = 'ASSINAR'
						WHERE a.idamostra = '$idamostra'";
	$resFluxo = d::b()->query($sqlFluxo) or die(mysqli_error(d::b())." Erro ao buscar fluxo fluxo ".$sqlFluxo);
	$rowFluxo = mysqli_fetch_assoc($resFluxo);
	FluxoController::restaurarFluxo($rowFluxo['modulo'], 'idamostra', $idamostra, $rowFluxo['statustipo'], $rowFluxo['idfluxostatus']);
}

//LTM (23/04/2021): Insere o nome do Representante 
//Verifica se tem pessoa inserida para assinatura
$idamostra = $_SESSION["_pkid"];
$sqls = "SELECT idcarrimbo, idpessoa, status, alteradoem
			FROM carrimbo 
			WHERE idobjeto = '$idamostra' AND tipoobjeto = 'amostra' AND idobjetoext = '882' AND tipoobjetoext = 'idfluxostatus' AND status IN ('PENDENTE','ASSINADO')";
$ress = d::b()->query($sqls);
$row = mysqli_fetch_assoc($ress); 
$total = mysqli_num_rows($ress);

$iu = $_SESSION['arrpostbuffer']['1']['i']['amostra']['idpessoaresponsavel'] ? 'i' : 'u';
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavel']))
{
	updateAmostra($idamostra, 'responsavel', getNomePessoa($_SESSION['arrpostbuffer']['1']['u']['amostra']['idpessoaresponsavel']));        

	if($row['idpessoa'] != $_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavel']){
		$retiraAssinatura = "DELETE FROM carrimbo WHERE idobjeto = '".$idamostra."' AND idobjetoext = '882' AND tipoobjetoext = 'idfluxostatus'
									AND status = 'PENDENTE'";
		d::b()->query($retiraAssinatura) or die("[ajax]-Erro ao excluir Objeto Carrimbo: ".mysql_error(d::b()));
	}

	if($row['idpessoa'] != $_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavel'] || $total == 0)
	{
		$sqlns = "INSERT INTO carrimbo 
						(idempresa, 
						idpessoa, 
						idobjeto, 
						tipoobjeto, 
						idobjetoext, 
						tipoobjetoext, 
						status,     
						tipoassinatura,
						criadopor, 
						criadoem, 
						alteradopor, 
						alteradoem)
				VALUES        
						(".$_SESSION['SESSAO']['IDEMPRESA'].",
						'".$_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavel']."',
						'$idamostra',
						'".$_GET["_modulo"]."', 
						'882',
						'idfluxostatus',
						'PENDENTE',
						'',
						'".$_SESSION['SESSAO']['USUARIO']."',
						now(),
						'".$_SESSION['SESSAO']['USUARIO']."',
						now());";
									
		$resIns = d::b()->query($sqlns);
	}	
}

//Seta o Nome do Responsável Oficial
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelof']))
{  
	updateAmostra($idamostra, 'responsavelof', getNomePessoa($_SESSION['arrpostbuffer']['1']['u']['amostra']['idpessoaresponsavelof']));    
}

//Seta o responsável Coleta CRMV
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelcrmv']))
{
	updateAmostra($idamostra, 'responsavelcolcrmv', getCRMV($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelcrmv']));
}

//Seta o responsável Oficial CRMV
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelcrmvof']))
{
	updateAmostra($idamostra, 'responsavelofcrmv', getCRMV($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelcrmvof']));
}

function getCRMV($idpessoacrmv)
{
	$sql="SELECT crmv from pessoacrmv where idpessoacrmv = ".$idpessoacrmv."";
	$res = d::b()->query($sql);
	$row = mysql_fetch_assoc($res);
	return $row['crmv'];
}

function getNomePessoa($idpessoa)
{
	$sqlr="SELECT nome from pessoa where idpessoa = ".$idpessoa."";
	$res = d::b()->query($sqlr);  
	$row = mysql_fetch_assoc($res);   
	return $row['nome'];
}

function updateAmostra($idamostra, $campo, $setCampo)
{
	$sql="UPDATE amostra set $campo = '".$setCampo."' WHERE idamostra = ".$idamostra."";   
    d::b()->query($sql); 
}

//Altera a Data do Carrimbo de acordo com a data 
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['datacoleta']) && dma($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['datacoleta']) != dma($row['alteradoem']) && !empty($row['idcarrimbo']))
{
	date_default_timezone_set('America/Sao_Paulo');
	$data = explode("/", $_SESSION['arrpostbuffer']['1'][$iu]['amostra']['datacoleta']);
	$data = $data[2]."-".$data[1]."-".$data[0]." ".date('H:m:s');
	$sql = "UPDATE carrimbo
			   SET alteradoem = '".$data."'
			 WHERE idcarrimbo = " .$row['idcarrimbo'];
	$res = mysql_query($sql) or die("ERRO ao atualizar a tabela carrimbo: ".mysql_error()."\n SQL: ".$sql);
}

?>