<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once(__DIR__."/../../form/controllers/bioterioanalise_controller.php");
 

//abre variavel com a acao que veio da tela
$idficharep = $_SESSION['arrpostbuffer']['x']['i']['bioensaio']['idficharep'];

//abre variavel com a acao que veio da tela
$idanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idanalise'];

$idbioterioanalise = $_SESSION['arrpostbuffer']['x']['u']['analise']['idbioterioanalise'];


//se for um update na analise deve atulizar os servicos
if(!empty($idanalise) and !empty($idbioterioanalise)){
    $dtinicio= implode("-",array_reverse(explode("/",$_SESSION['arrpostbuffer']['x']['u']['analise']['datadzero'])));
    // $sql="select * from servicobioterioconf where tipoobjeto = 'bioterioanalise' and idobjeto=".$idbioterioanalise." and idservicobioterio is not null";
     
    $sqld="DELETE s.*  from servicoensaio s
                    where s.idobjeto=".$idanalise." 
                    and s.status = 'PENDENTE'
                    and  s.tipoobjeto='analise'";
            d::b()->query($sqld) or die("1-Falha ao ATUALIZAR bioensaio na [servicoensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqld);
            
	$sqlins="INSERT INTO servicoensaio (idempresa,idobjeto,tipoobjeto,idservicobioterio,dia,diazero,data,status,criadopor,criadoem,alteradopor,alteradoem)
             (select ".cb::idempresa().",".$idanalise.",'analise',c.idservicobioterio,c.dia,c.diazero,DATE_ADD('".$dtinicio."', INTERVAL c.dia DAY) as datafim,'PENDENTE'
             ,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
             from servicobioterioconf c
             where c.idobjeto = ".$idbioterioanalise."
             and c.tipoobjeto='bioterioanalise')";
	$res1=d::b()->query($sqlins) or die("1-Erro ao gerar sevicos da analise: ".mysqli_error(d::b())."<p>SQL: ".$sqlins);
       
      
}

$_x_i_lote_idprodserv= $_SESSION['arrpostbuffer']['x']['i']['lote']['idprodserv'];
$_x_i_lote_status= $_SESSION['arrpostbuffer']['x']['i']['lote']['status'];
$_x_i_lote_idunidade= $_SESSION['arrpostbuffer']['x']['i']['lote']['idunidade'];
$_x_i_lote_qtdprod= $_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'];
$_x_i_lote_qtdpedida= $_SESSION['arrpostbuffer']['x']['i']['lote']['qtdpedida'];
$_x_i_lote_tipoobjetosolipor= $_SESSION['arrpostbuffer']['x']['i']['lote']['tipoobjetosolipor'];
$_x_i_lote_idobjetosolipor= $_SESSION['arrpostbuffer']['x']['i']['lote']['idobjetosolipor'];
if (!empty($_x_i_lote_idprodserv) and !empty($_x_i_lote_status) and !empty($_x_i_lote_idunidade) 
		and !empty($_x_i_lote_qtdprod) and !empty($_x_i_lote_qtdpedida) and !empty($_x_i_lote_tipoobjetosolipor) and !empty($_x_i_lote_idobjetosolipor)) {

	$_arrlote = geraLote($_x_i_lote_idprodserv);
		
	if(strlen($_arrlote[0])==0 or strlen($_arrlote[1])==0){
		die("Falha na geração da Partida (sequence). [".$_arrlote[0]."][".$_arrlote[1]."]");
	}else{
		$_numlote = $_arrlote[0].$_arrlote[1];
		//Enviar o campo para a pagina de submit
		$partida= $_numlote;
		$spartida = $_arrlote[0];
		$npartida = $_arrlote[1];
	}
	//die($partida);
	$_SESSION['arrpostbuffer']['x']['i']['lote']['idprodserv'] = $_x_i_lote_idprodserv;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['status'] = $_x_i_lote_status;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['idunidade'] = $_x_i_lote_idunidade;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdprod'] = $_x_i_lote_qtdprod;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['qtdpedida'] = $_x_i_lote_qtdpedida;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['tipoobjetosolipor'] = $_x_i_lote_tipoobjetosolipor;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['idobjetosolipor'] = $_x_i_lote_idobjetosolipor;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['partida'] = $partida;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['idpartida'] = $partida;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['npartida'] = $npartida;
	$_SESSION['arrpostbuffer']['x']['i']['lote']['spartida'] = $spartida;
}

$acaoBioensaio = '_b_i_bioensaio';

$bioensaios = FicharepController::buscarBioensaiosDaFichadeRep($_POST['_1_u_ficharep_idficharep']);
$qtd_bioensaios = $_POST['qtd_bioensaios'];

if(!$bioensaios && !$qtd_bioensaios) $acaoBioensaio = '_1_u_ficharep';

$b_idficharep = $_POST["{$acaoBioensaio}_idficharep"];
$b_nascimento = $_POST["{$acaoBioensaio}_nascimento"] ?? $_POST["{$acaoBioensaio}_fim"];
$b_idespeciefinalidade = $_POST["{$acaoBioensaio}_idespeciefinalidade"];
$b_idunidade = $_POST["{$acaoBioensaio}_idunidade"];
$_x_i_bioensaio_qtd = $_POST["{$acaoBioensaio}_qtd"];
$b_idlote = $_POST["{$acaoBioensaio}_idlotepd"] ?? $_POST["{$acaoBioensaio}_idlote"];
$bioensaio_status = $_POST["{$acaoBioensaio}_status"];

/**
 * Adicionar consulta para verificar se há protocolos vinculados 
 * se não houver gerar um automaticamente
*/
if (
	(
		!empty($b_idficharep) && !empty($b_nascimento) 
		&& !empty($b_idespeciefinalidade) && !empty($b_idunidade) 
		&& (!empty($_x_i_bioensaio_qtd) || $acaoBioensaio == '_1_u_ficharep') && !empty($b_idlote) 
		&& !empty($bioensaio_status)
	)
) 
{
	if($acaoBioensaio === '_1_u_ficharep') $qtd_bioensaios = 1;
	else unset($_SESSION['arrpostbuffer']);
	if(!$_x_i_bioensaio_qtd) $_x_i_bioensaio_qtd = 1;

	if($qtd_bioensaios<1){die("Não foi identificado a quantidade de estudos a criar.");}

	$sqk="select * from lotefracao where idlotefracao=".$b_idlote;
	$rek=d::b()->query($sqk) or die(mysqli_error(d::b())."<p>SQL: ".$sqk);
	$rk=mysqli_fetch_assoc($rek);

	for ($i = 1; $i <= intval($qtd_bioensaios) ; $i++) {

		### Inicializa a sequence para bioensaio
		$sqlini = "SELECT count(*) as quant FROM sequence where sequence = 'bioensaio' and  exercicio = year(current_date) ";
		$resini = mysql_query($sqlini);
		if(!$resini){
			echo "[saveprechange]- Falha ao inicializar Sequence [bioensaio] : " . mysql_error() . "<p>SQL: $sqlini";
			die();
		}
		$rowini = mysql_fetch_array($resini);
		### Caso nao exista a sequence inicializada 
		if($rowini["quant"]==0){
			$sqlins = "insert into sequence  (`sequence`, `chave1`,`idempresa`,exercicio) values ('bioensaio',0,".$_SESSION["SESSAO"]["IDEMPRESA"].",year(current_date))";
			mysql_query($sqlins) or die("[saveprechange]- Falha ao inserir Sequence [bioensaio] : " . mysql_error() . "<p>SQL: ".$sqlins);
		}

		### Incrementa e  a sequence
		//mysql_query("LOCK TABLES sequence WRITE") or die("seqmeiolote: Falha 1 ao efetuar LOCK [sequence]: ".mysql_error());
		//mysql_query("START TRANSACTION") or die("[saveprechange]- sequence: Falha 2 ao abrir transacao: ".mysql_error());

		mysql_query("update sequence set chave1 = (chave1 + 1) where sequence = 'bioensaio'  and  exercicio = year(current_date) ");
		//mysql_query("COMMIT") or die("[saveprechange]- sequence: Falha ao efetuar COMMIT [sequence update]: ".mysql_error());

		$sql = "SELECT chave1,exercicio FROM sequence where sequence = 'bioensaio'  and  exercicio = year(current_date)";

		$res = mysql_query($sql);

		if(!$res){
			//mysql_query("UNLOCK TABLES;") or die("sequence: Falha 3 ao efetuar UNLOCK [sequence]: ".mysql_error());
			echo "[saveprechange]- Falha Pesquisando Sequence [biensaio] : " . mysql_error() . "<p>SQL: $sql";
			die();
		}

		$row = mysql_fetch_array($res);

		### Caso nao retorne nenhuma linha ou retorn valor vazio
		if(empty($row["chave1"]) or $row["chave1"]==0){
			if(!$resexercicio){
			//	mysql_query("UNLOCK TABLES") or die("sequence: Falha 4 ao efetuar UNLOCK [sequence]: ".mysql_error());
			//	mysql_query("ROLLBACK;") or die("sequence: Falha 5 ao efetuar UNLOCK [sequence]: ".mysql_error());

				echo "[saveprechange]- Falha Pesquisando Sequence [bionsaio] : " . mysql_error() . "<p>SQL: $sql";
				die();
			}
		}else{
			//LTM - 15-04-2021: Retorna o Idfluxo Bioensaio
			$idfluxostatus = FluxoController::buscarIdfluxostatusInicioPorModulo('bioensaio');

			// Buscar bioterioanlise para vinculo do protocolo
			$bioterioAnalise = null;
			if($acaoBioensaio === '_1_u_ficharep')
				$bioterioAnalise = BioterioAnaliseController::buscarPorIdEspecieFinalidadeEIdEmpresa($b_idespeciefinalidade, cb::idempresa());

			$ins = new Insert();
			$ins->setTable("bioensaio");
			$ins->idficharep = $b_idficharep; 
			$ins->idregistro = $row["chave1"];
			$ins->exercicio = $row["exercicio"];
			$ins->idempresa = cb::idempresa();
			$ins->nascimento = validadate($b_nascimento); 
			$ins->idespeciefinalidade = $b_idespeciefinalidade; 
			
			if($bioterioAnalise) $ins->idbioterioanalise = $bioterioAnalise['idbioterioanalise']; 

			$ins->idunidade = $b_idunidade; 
			$ins->qtd = $_x_i_bioensaio_qtd;
			$ins->idlote = $b_idlote;
			$ins->status = $bioensaio_status;
			$ins->idfluxostatus = $idfluxostatus;
			$idbioensaio = $ins->save();

			//LTM - 13-04-2021: Insere FluxoHist Amostra           
			FluxoController::inserirFluxoStatusHist('bioensaio', $idbioensaio, $idfluxostatus, 'PENDENTE');

/*

			$inss = new Insert();
			$inss->setTable("lotecons");
			$inss->idlotefracao=$b_idlote; 
			$inss->idlote= $rk['idlote'];
			$inss->idempresa = cb::idempresa();
			$inss->idobjeto=$idbioensaio; 
			$inss->tipoobjeto="bioensaio";
			$inss->qtdd=$_x_i_bioensaio_qtd; 
			$idanalise=$inss->save();
*/			
			$insa = new Insert();
			$insa->setTable("analise");
			$insa->idobjeto=$idbioensaio; 
			if($bioterioAnalise) {
				$insa->idbioterioanalise = $bioterioAnalise['idbioterioanalise'];
				$insa->qtd = 1;
			}
			$insa->idempresa = cb::idempresa();
			$insa->objeto='bioensaio';
			$idanalise=$insa->save();

			$_SESSION['arrpostbuffer']['xx'.$i]['i']['localensaio']['idanalise'] = $idanalise;
		}
	}
	montatabdef();
}
//die($_SESSION["arrpostbuffer"]);
?>

