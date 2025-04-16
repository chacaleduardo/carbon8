<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

$sql = " select * from pessoa where flgsocio='Y'  and idpessoa=" . $_SESSION["SESSAO"]["IDPESSOA"];
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor = mysqli_num_rows($res);
if ($flgdiretor < 1) {
	$clausulalp = " and not exists (select 1 from nf n where n.idnf = c.idobjeto and n.tiponf = 'D')";
}
$semnota = '';
/*
if ($_SESSION["SESSAO"]["IDEMPRESA"] != 1 and $_SESSION["SESSAO"]["IDEMPRESA"] !=2){
	$flgdiretor = 1;
}
*/

if (array_key_exists("saldoextrato", getModsUsr("MODULOS"))) {
	$saldoextrato = 1;
} else {
	$saldoextrato = 0;
}
if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or array_key_exists("quitardebito", getModsUsr("MODULOS"))  or array_key_exists("quitarrh", getModsUsr("MODULOS")) or $flgdiretor > 0) {
	//se não for diretor
	if ($flgdiretor < 1) {

		/* if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) and array_key_exists("quitardebito", getModsUsr("MODULOS"))){
             ?>  

            <link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
            <br>
            <div class="row">
                    <div class="col-md-12">
                            <div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

                            <strong><i class="glyphicon glyphicon-info-sign"></i> Usuário só deve ter:
                            <br/>
                            <br/>Quitar Crédito ou Quitar Débito em suas permissões no sistema.
                            <br/>
                            <br/>Favor entrar em contato com Departamento de Processos - Ramal: 110
                            </div>
                    </div>
            </div>
			<?
             die;
        }*/
		if (array_key_exists("quitarcredito", getModsUsr("MODULOS"))) {
			$clausulalp1 = " and c.tipo in ('C') and c.tipoobjeto  in('nf','notafiscal') and c.visivel='S'";
			$joincontaitem1 = "";
			$contapagaritem1 = "
                            and exists (select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem in ('nf','notafiscal') )
                                ";
		}

		if (array_key_exists("quitardebito", getModsUsr("MODULOS"))) {
			$clausulalp2 = " and c.tipo in ('D') and c.visivel='S' ";
			$joincontaitem2 = " join nf n on (  c.idobjeto = n.idnf and (c.tipoobjeto like ('nf%') or c.tipoobjeto='gnre') and n.tiponf not in('R','D'))  ";

			$contapagaritem2 = " and (exists (select 1 from contapagaritem i,nf n where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem ='nf' and i.idobjetoorigem = n.idnf and n.tiponf not in('D','R') ) 
                                    or 
                                    exists (select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem ='contapagar' )
                                    )   ";

			if (array_key_exists("quitarrh", getModsUsr("MODULOS"))) {
				$tipoespecifico = "('AGRUPAMENTO','REPRESENTACAO','IMPOSTO')";
			} else {
				$tipoespecifico = "('AGRUPAMENTO','IMPOSTO')";
			}
		}

		if (array_key_exists("quitarrh", getModsUsr("MODULOS"))) {
			$clausulalp3 = " and c.tipo in ('D','C') "; 
			// $joincontaitem3=" join contaitem i on (  c.idcontaitem = i.idcontaitem and i.idcontaitem=9) ";
			$joincontaitem3 = " join nf n on (  c.idobjeto = n.idnf and c.tipoobjeto like ('nf%') and n.tiponf='R')  ";

			$contapagaritem3 = "
                            and exists (select 1 from contapagaritem i,nf n where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem ='nf' and i.idobjetoorigem = n.idnf and n.tiponf='R' )
                                ";
			$semnota = ' and c.idobjeto is null ';

			$contapagaritemRh = "
							and  exists  (select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem ='contapagar' and i.idobjetoorigem is not null ) 
							";
		}
	} else {
		//$clausulalp='';
		$joincontaitem = "";
	}
} else {
?>

	<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
	<br>
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

				<strong><i class="glyphicon glyphicon-info-sign"></i> Usuário sem permissão para visualização.
					<br />
					<br />É necessário liberar nas permissões do usuário uma das opções:
					<br />*Quitar Débitos
					<br />*Quitar Créditos
					<br />*Quitar RH
					<br />Favor entrar em contato com Departamento de Processos - Ramal: 110
			</div>
		</div>
	</div>
<?
	die;
}

//ini_set("display_errors","1");
//error_reporting(E_ALL);
################################################## Atribuindo o resultado do metodo GET

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idagencia 		= $_GET["idagencia"];
$tipo           = $_GET["tipo"];
$status         = $_GET["status"];
if (empty($_GET["idempresa"])) {
	$idempresa = cb::idempresa();
} else {
	$idempresa = $_GET["idempresa"];
}


//print_r($_SESSION["post"]);

if (!empty($vencimento_1) or !empty($vencimento_2)) {
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim) {
		$clausulad .= "and (c.datareceb  BETWEEN '" . $dataini . "' and '" . $datafim . "')";
	} else {
		die("Datas n&atilde;o V&aacute;lidas!");
	}
} //if (!empty($vencimento_1) or !empty($vencimento_2)){

/*
 * colocar condição para executar select
 */
if ($_GET and !empty($clausulad) and !empty($idagencia)) {

	// atualizar as contas conforme os valores a pagar
	/*    
    $sqlc1="select func_agrupa_contapagar('".$idagencia."') as retorno";
    $resc1 = d::b()->query($sqlc1) or die("Falha ao atualizar contas especificas: " . mysqli_error(d::b()) . "<p>SQL: $sqlc1");
    $rowc1=mysqli_fetch_assoc($resc1);

    if($rowc1["retorno"]!="OK"){
	die($rowc1["retorno"]);
    }else{
	d::b()->query("COMMIT") or die("erro : Falha ao atualizar contas especificas: ".mysqli_error(d::b()));
    }
    // atualiza a contaitem com a conta criada na funcao acima
    
    $sqlc2="select func_agrupa_contapagaritem('".$idagencia."') as retorno";
    $resc2 = d::b()->query($sqlc2) or die("Falha ao atualizar contapagaritem : " . mysqli_error(d::b()) . "<p>SQL: $sqlc2");
    $rowc2=mysqli_fetch_assoc($resc2);

    if($rowc2["retorno"]!="OK"){
	die($rowc2["retorno"]);
    }else{
	d::b()->query("COMMIT") or die("erro : Falha ao atualizar contapagaritem: ".mysqli_error(d::b()));
    }
 */


	$sqlsa = "select saldo,datareceb from contapagar 
            where 1
			and idempresa=" . $idempresa . "
            and datareceb  < '" . $dataini . "' 
            and status='QUITADO' 
            and idagencia='" . $idagencia . "' 
            and saldo is not null
            order by datareceb desc,quitadoemseg desc  limit 1";
	$resat = d::b()->query($sqlsa) or die("Falha ao pesquisar ultimo saldo valido menor que a data inicial: " . mysqli_error(d::b()) . "<p>SQL: $sqlsa");
	$qtdat = mysqli_num_rows($resat);
	$rowat = mysqli_fetch_assoc($resat);
	if ($qtdat < 1) {
		$rowat['saldo'] = '0.00';
		$rowat['datareceb'] = $dataini;
	}

	$sqlsok = "select idcontapagar from contapagar where saldo is not null and status = 'QUITADO' and idagencia='" . $idagencia . "' and idempresa = " . $idempresa . "  and saldook='Y' order by quitadoemseg desc limit 1;";
	$resok = d::b()->query($sqlsok) or die("Falha ao pesquisar ultimo saldo ok: " . mysqli_error(d::b()) . "<p>SQL: $sqlsok");
	$rowok = mysqli_fetch_assoc($resok);

	if (!empty($rowok["idcontapagar"])) {

		$sqlo = "select organizasaldo(" . $rowok["idcontapagar"] . ") as retorno";
		$reso = d::b()->query($sqlo) or die("Falha ao atualizar contas: " . mysqli_error(d::b()) . "<p>SQL: $sqlo");
		$rowo = mysqli_fetch_assoc($reso);

		if ($rowo["retorno"] != "OK") {
			die($row["retorno"]);
		} else {
			d::b()->query("COMMIT") or die("erro : Falha ao recalcular saldo: " . mysqli_error(d::b()));
		}
	} //if(!empty($rowok["idcontapagar"])){

	$sqlant = "select * from contapagar where saldo is not null and status = 'QUITADO' and idagencia='" . $idagencia . "' and idempresa = " . $idempresa . "  order by datareceb desc, quitadoemseg desc limit 1;";

	$sql = '';
	$union = '';
	$else = '';
	if (!(empty($clausulalp1))) {
		$sql .= "SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
		WHEN 'PENDENTE' THEN 2 
		WHEN 'ABERTO' THEN 3 
		ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido
                FROM contapagar c " . $joincontaitem1 . "
                where
                    1
					and c.idempresa =" . $idempresa . "
                   " . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp1 . "
					" . $clausulalp . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO')
                and c.tipoespecifico='NORMAL'
                union
                SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
		WHEN 'PENDENTE' THEN 2 
		WHEN 'ABERTO' THEN 3 
		ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido  
                FROM contapagar c 
                where
                    1
					and c.idempresa =" . $idempresa . "
                   " . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp1 . "
					" . $clausulalp . "
                    " . $contapagaritem1 . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO')
                and c.tipoespecifico in ('AGRUPAMENTO','REPRESENTACAO','IMPOSTO')
				";
		$union = ' union ';
		$else = true;
	}

	if ( /*!empty($joincontaitem2) and*/!empty($clausulalp2)) {
		$sql .= $union . "SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
		WHEN 'PENDENTE' THEN 2 
		WHEN 'ABERTO' THEN 3 
		ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido 
                FROM contapagar c " . $joincontaitem2 . "
                where
				 1
				and c.idempresa =" . $idempresa . "
				" . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp2 . "
					" . $clausulalp . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO') 
                and c.tipoespecifico='NORMAL'
                union
                SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
		WHEN 'PENDENTE' THEN 2 
		WHEN 'ABERTO' THEN 3 
		ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido  
                FROM contapagar c 
                where
				 1
				and c.idempresa =" . $idempresa . "
				" . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp2 . "
					" . $clausulalp . "
                    " . $contapagaritem2 . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO') 
                and c.tipoespecifico in " . $tipoespecifico . "
                 union
                SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
		WHEN 'PENDENTE' THEN 2 
		WHEN 'ABERTO' THEN 3 
		ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido  
                FROM contapagar c 
                where
				 1
				and c.idempresa =" . $idempresa . "
				" . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp2 . "
					" . $clausulalp . "
                and (c.tipoobjeto is null or c.tipoobjeto ='')
				and c.tipoespecifico in " . $tipoespecifico . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO')
				";
		$union = ' union ';
		$else = true;
	}
	if (!empty($joincontaitem3) and !empty($clausulalp3)) {	//contapagaritemRh
		$sql .= $union . "SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
		WHEN 'PENDENTE' THEN 2 
		WHEN 'ABERTO' THEN 3 
		ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido  
                FROM contapagar c " . $joincontaitem3 . "
                where
				 1
				and c.idempresa =" . $idempresa . "
				" . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp3 . "
					" . $clausulalp . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO')
                and c.tipoespecifico='NORMAL' 
                 union
                SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
					WHEN 'PENDENTE' THEN 2 
					WHEN 'ABERTO' THEN 3 
					ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido  
                FROM contapagar c 
                where
				 1
				and c.idempresa =" . $idempresa . "
				" . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp3 . "
					" . $clausulalp . "
                    " . $contapagaritem3 . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO')
				and c.tipoespecifico in ('AGRUPAMENTO','REPRESENTACAO','IMPOSTO')
				union
                SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
					WHEN 'PENDENTE' THEN 2 
					WHEN 'ABERTO' THEN 3 
					ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido  
                FROM contapagar c 
                where
				 1
				and c.idempresa =" . $idempresa . "
				" . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp3 . "
					" . $clausulalp . "
                    " . $contapagaritemRh . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO')
				and c.tipoespecifico in ('REPRESENTACAO','IMPOSTO')
				union
				SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
				WHEN 'PENDENTE' THEN 2 
				WHEN 'ABERTO' THEN 3 
				ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido  
						FROM contapagar c 
						where
						1
						and c.idempresa =" . $idempresa . "
						" . $clausulad . "
						and c.idagencia='" . $idagencia . "' 
							" . $clausulalp3 . "
							" . $clausulalp . "
							" . $semnota . "
						and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO')
						and c.tipoespecifico in ('REPRESENTACAO','IMPOSTO')
				";
		$else = true;
	}

	if (!($else)) {
		$sql = "SELECT c.*,dma(c.datareceb) as dtreceb,CASE c.status    WHEN 'QUITADO' THEN 1
		WHEN 'PENDENTE' THEN 2 
		WHEN 'ABERTO' THEN 3 
		ELSE 4 END as ordem,if((DATE_ADD(c.datareceb, INTERVAL 1 DAY) > now()),'N','Y') as vencido  
                FROM contapagar c " . $joincontaitem . "
                where
				 1
				and c.idempresa =" . $idempresa . "
				" . $clausulad . "
                and c.idagencia='" . $idagencia . "' 
                    " . $clausulalp . "
                and c.status not in ('INATIVO','DEVOLVIDO','CANCELADO') 
				";
	}

	$sql = "select c.*,cc.idcontapagar as sok
			 from (" . $sql . ")c 
				left join contapagar cc on( cc.idagencia = c.idagencia 
				    						and cc.status = 'QUITADO' 
											and cc.idempresa=c.idempresa 
											AND c.datareceb =cc.datareceb
											 and cc.saldook='Y') 
				group by c.idcontapagar
				order by c.datareceb asc,c.tipo desc,c.ordem asc,c.valor asc";
	echo "<!--";
	echo $sqlant;
	echo "-->";
	echo "<!--";
	echo $sql;
	echo "-->";
	if (!empty($sql)) {

		$res = d::b()->query($sql) or die("Falha ao pesquisar contas: " . mysqli_error(d::b()) . "<p>SQL: $sql");
		$ires = mysqli_num_rows($res);

		$resant = d::b()->query($sqlant) or die("Falha ao pesquisar saldo anterior: " . mysqli_error(d::b()) . "<p>SQL: $sqlant");

		$somatotais = 0;
		$vlrcredito = 0;
		$vlrdebito = 0;
		$qtdcred = 0;
		$qtddeb = 0;
		$parc = '';
		$vlrpendcredito = 0;
		$vlrpenddebito = 0;
		$qtdpendcred = 0;
		$qtdpenddeb = 0;
		$prevsaldototal = 0;
		$saldofim = 0;
		$data = date("Y/m/d");

		$vlrcreditox = 0;
		$vlrdebitox = 0;
		$vlrpendcreditox = 0;
		$vlrinadimplente = 0;
		$vlrpenddebitox = 0;
		$vlrquitadocredito = 0;
		$vlrquitadodebito = 0;
		$vlrprogramado = 0;
		$vlrprogramadopagar = 0;
		$vlrndescriminados = 0;
		$vlrndescriminadosD = 0;
		$vlrndescriminadosC = 0;
		$arrprogramado = array();
	} //if (!empty($sql)){
} //if($_GET and !empty($clausulad)){
?>
<!-- Mostrar mensagem de Aguarde e bloquear tela  -->
<style>
	/* footer= linha onde fica os botàµes assinar retirar assinatura e o alerta*/
	#Footer {
		text-align: center;
		align: center;
		color: black;
		position: fixed;
		/**adjust location**/
		right: 0px;
		bottom: 0px;
		width: 100%;
		/* Netscape 4, IE 4.x-5.0/Win and other lesser browsers will use this */
		_position: absolute;
		width: calc(100% - 50px);
		left: 50px;
	}

	.clsFootera {
		background: #00FF00;
	}

	.clsFooterf {
		background: silver;
	}

	#resumo {
		background-color: #f5f5f5;
		padding: 1rem 0;
	}

	.btassina {
		cursor: pointer;
		border: solid 1px #ccc;
		background: #2832A5;
		color: white;
		height: 30px;
		/*background:url(../img/btbg.gif) repeat-x left top;*/
	}

	.btassinafoco {
		cursor: pointer;
		border: solid 1px #ccc;
		color: white;
		background: rgb(0, 255, 0);
		height: 30px;
	}

	.respreto td {
		padding-top: 0px !important;
		padding-bottom: 0px !important;
	}

	tr#linha:hover {
		background: #DCDCDC !important;
		color: black;
		box-shadow: 2px 2px 5px 0px rgba(0, 0, 0, 0.45);
	}
</style>
<style>
	/* Adicione um estilo de coluna ao contêiner da lista */
	.cbLegenda {
		columns: 2;
		/* Define o número de colunas */
		column-gap: 20px;
		/* Espaçamento entre as colunas */
		list-style-type: none;
		padding: 0;
	}

	/* Adicione algum estilo para melhorar a aparência (opcional) */
	.cbLegenda li {
		margin-bottom: 8px;
	}


	/* Adicione cabeçalho para a primeira coluna (Débito) */
	.cbLegenda li:nth-child(1)::before {
		content: "DÉBITO";
		display: block;
		font-weight: bold;
		margin-bottom: 8px;
	}

	/* Adicione cabeçalho para a segunda coluna (Crédito) */
	.cbLegenda li:nth-child(6)::before {
		content: "CRÉDITO";
		display: block;
		font-weight: bold;
		margin-bottom: 8px;
	}

	#cbPanelLegenda {
		box-shadow: none !important;
		border: none !important;
		background: none;
	}

	#cbPanelLegenda:not(.collapsed) {
		background-color: #f5f5f5 !important;
		padding: 1rem 1.5rem !important;
	}

	#cbPanelLegenda.collapsed .fechar-legenda {
		display: none;
	}

	#cbPanelLegendaBody {
		background-color: rgb(255, 255, 255);
		padding-top: 0.5rem !important;
	}

	#cbPanelLegenda .panel-heading {
		position: relative;
		background: none;
		display: flex;
		align-items: center;
		color: black !important;
		padding-left: 0;
		padding-right: 0;
	}

	#cbPanelLegenda:not(.collapsed) {
		margin-bottom: 1rem;
	}

	#cbPanelLegenda .panel-heading label {
		font-size: 18px;
	}
</style>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">Filtros para Listagem </div>
			<div class="panel-body">
				<table>
					<tr>
						<td class="rotulo">Empresa: </td>
						<td>
							<select name="idempresa" name="idempresa" onchange="selecionarAgencia(this)">
								<?
								$sql = 'SELECT e.idempresa,e.nomefantasia from empresa e where exists (select 1 from matrizconf m where m.idmatriz =' . cb::idempresa() . ' and m.idempresa=e.idempresa) and e.status="ATIVO"
										UNION
										SELECT idempresa,nomefantasia from empresa where idempresa =' . cb::idempresa() . ';';


								fillselect($sql, $idempresa);
								?>
							</select>
						</td>
						<td class="rotulo">Período</td>
						<td>
							<font class="9graybold">entre</font>
						</td>
						<td><input name="vencimento_1" vpar="" id="vencimento_1" class="calendario" size="10" style="width: 90px;" value="<?= $vencimento_1 ?>" autocomplete="off"></td>
						<td>
							<font class="9graybold">&nbsp;e&nbsp;</font>
						</td>
						<td><input name="vencimento_2" vpar="" id="vencimento_2" class="calendario" size="10" style="width: 90px;" value="<?= $vencimento_2 ?>" autocomplete="off"></td>
						<td align="right">Agência:</td>
						<td colspan="10">
							<select name="idagencia" id="idagencia" vnulo>
								<option></option>
								<?= getAgencia($idagencia, $idempresa) ?>
							</select>
						</td>
						<? if ($flgdiretor > 0 || $saldoextrato > 0) { ?>
							<td align="right">Tipo:</td>
							<td>
								<select name="tipo" id="tipo">
									<? fillselect("select '','Todos' union select 'C','Crédito' union select 'D','Débito'", $tipo); ?>
								</select>

							</td>
						<? } else { ?><td><input name="tipo" id="tipo" value="" autocomplete="off" type="hidden"></td><? } ?>
						<td align="right">Status:</td>
						<td>
							<select name="status" id="status">
								<? fillselect("select '','Todos'  union select 'ABERTO','Aberto' union select 'FECHADO','Fechado' union select 'PENDENTE','Pendente' union select 'QUITADO','Quitado'", $status); ?>
							</select>

						</td>
						<td></td>
						<td>
							<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
								<span class="fa fa-search"></span>
							</button>
						</td>
						<td><?

							if ($flgdiretor > 0 || $saldoextrato > 0) {
							?>
								<span class="dropdown" style=" margin-left:12px">
									<button class="btn btn-info dropdown-toggle  btn-primary" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										<span class="fa fa-print"></span>
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1" 
										style="color:#898989; font-size:11px;text-transform:uppercase;text-transform: uppercase; margin-top: 17px; left: -120px;">
										<li style="padding: 2px 0px;"><a href="javascript:void(0)" onclick="reldet(1);" data-value="another action" style="color:#898989 !important;">Extrato</a></li>
										
									</ul>
								</span>
							<?
							}

							?>
						</td>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td>
							<div class="row">
								<div class="col-md-8 nowrap">
									<?
									if ($flgdiretor > 0  || $saldoextrato > 0) { ?>
										<a class="fa hoverazul pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=i');">
											<font style="color: blue;display:inline;cursor:pointer; text-decoration: underline;">Nova Compra</font>
										</a>
									<? } ?>
								</div>
								<div class="col-md-2">


								</div>
							</div>
						</td>

					</tr>
				</table>

			</div>
		</div>
	</div>
</div>

<?
if ($_GET and $ires > 0) {
?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Relatório de saldos (<?= $ires ?> itens)</div>
				<div class="panel-body">
					<table class="normal" style="font-size: 10px; width: 100%;" id="inftable">
						<tr class="header">
							<td align="center"></td>
							<td align="center">Data</td>
							<td align="center">Tipo</td>
							<td align="center">Parcela</td>
							<td align="center">Última <br> Edição</td>
							<td align="center">Nº Documento</td>
							<td align="center">Pessoa</td>
							<td align="center" style="max-width: 500px;">Obs.</td>
							<td align="center">Valor</td>
							<td align="center">Status</td>
						</tr>
						<tr>
							<? if ($flgdiretor > 0 || $saldoextrato > 0) {

								if ($rowat['saldo'] >= 0) {
									$corprevsaldo = "#98FB98"; //verde
								} else {
									$corprevsaldo = "#FFFF00"; //amarelo
								}
								?>
								<td colspan="8"></td>
								<td align="right" class="respreto aqui" colspan="3"><? echo "Saldo em " ?><?= dma($rowat['datareceb']) ?>: </td>
								<td class="respreto" style="background-color:<?= $corprevsaldo ?>">
									<b><?= number_format(tratanumero($rowat['saldo']), 2, ',', '.'); ?></b>
								</td>
							<? } else {
								echo ("<td colspan='12'></td>");
							} ?>
						</tr>
						<?
						$rowant = mysqli_fetch_array($resant);

						if (!empty($rowant["saldo"])) {
							$saldototal = $rowant["saldo"];

							if ($saldototal >= 0) {
								$corsaldo = "#c8d0ff";
							} else {
								$corsaldo = "#f0bfbf";
							}
						} else {
							$saldototal = 0;
						}
						$vprogramado = 0;
						$vlrprogramadox = 0;
						$ip = 0; //variavel para o form

						while ($row = mysqli_fetch_array($res)) {

							$datastring = str_replace("-", "", $row['datareceb']);
							$somarelatorio = 'Y';
							$alertastatus = "";

							$sqls = "select * from contapagar 
									where saldo is not null 
									and status = 'QUITADO' 
									and idagencia='" . $idagencia . "' 
									and idempresa = " . $idempresa . " 
									and datareceb = '" . $row["datareceb"] . "'
									order by quitadoemseg desc limit 1;";
							echo "<!--";
							echo $sqls;
							echo "-->";
							$ress = d::b()->query($sqls) or die("Falha ao pesquisar saldo anterior 2: " . mysqli_error(d::b()) . "<p>SQL: $sqls");
							$rows = mysqli_fetch_assoc($ress);


							$stemarq = "select a2.* from contapagar c  
										join arquivo a2 on(a2.idobjeto = c.idcontapagar and a2.tipoobjeto ='contapagar')                        
										where c.idcontapagar  =" . $row["idcontapagar"] . "
										union 
										select a.* from contapagar c  
										join arquivo a on(a.idobjeto =  c.idobjeto and a.tipoobjeto  in ('nf','cotacaoforn','notafiscal'))                        
										where c.idcontapagar  =" . $row["idcontapagar"] . ";";

							$rtemarq = d::b()->query($stemarq) or die("Falha ao buscar anexos: " . mysqli_error(d::b()) . "<p>SQL: $stemarq");
							$rwtemar = mysqli_num_rows($rtemarq);



							$ip = $ip + 1;
							$stredit = "janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=" . $row['idcontapagar'] . "&_idempresa=" . $row['idempresa'] . "');";
							//verificar se a conta possui lançamentos

							if ($row["tipoespecifico"] == 'AGRUPAMENTO') {
								$sqloc = "select * from contapagaritem i join nf n on(n.idnf=i.idobjetoorigem and n.tiponf='O')
											where i.idcontapagar=" . $row["idcontapagar"] . "
											and i.status NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
											and tipoobjetoorigem='nf'";
								$reoc = d::b()->query($sqloc);
								$qtdoutros = mysqli_num_rows($reoc);
								if ($qtdoutros > 0) {
									$somarelatorio = 'N';
								}
							}

							if (($row["tipoobjeto"] == "nf" or $row["tipoobjeto"] == "gnre" or $row["tipoobjeto"] == 'nf_darf'  or $row["tipoobjeto"] == 'nf_ir'  or $row["tipoobjeto"] == 'nf_inss'  or $row["tipoobjeto"] == 'nf_issret') and !empty($row["idobjeto"])) {


								$sqlf = "select n.idnf,p.nome,n.tiponf,n.controle,n.nnfe,
										CASE
										WHEN  n.status ='COBRANCA' THEN 'COBRANÇA' 
										WHEN  n.status ='DIVERGENCIA' THEN 'DIVERGÊNCIA' 
										WHEN  n.status ='EXPEDICAO' THEN 'EXPEDIÇÃO' 
										WHEN  n.status ='ORCAMENTO' THEN 'ORÇAMENTO' 
										WHEN  n.status ='PREVISAO' THEN 'PREVISÃO' 
										WHEN  n.status ='PRODUCAO' THEN 'PRODUÇÃO' 
										ELSE n.status END as status
										from nf n,pessoa p where p.idpessoa = n.idpessoa  and idnf =" . $row["idobjeto"];
								$qrf = d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:" . mysqli_error(d::b()));
								$qtdrowsf = mysqli_num_rows($qrf);
								$resf = mysqli_fetch_assoc($qrf);

								if ($resf["tiponf"] == "V") {
									$janelanf = "janelamodal('?_modulo=pedido&_acao=u&idnf=" . $row["idobjeto"] . "&_idempresa=" . $row['idempresa'] . "');";
								} elseif ($resf["tiponf"] == 'R') {
									$janelanf = "janelamodal('?_modulo=comprasrh&_acao=u&idnf=" . $row["idobjeto"] . "&_idempresa=" . $row['idempresa'] . "');";
								} elseif ($resf["tiponf"] == 'D') {
									$janelanf = "janelamodal('?_modulo=comprassocios&_acao=u&idnf=" . $row["idobjeto"] . "&_idempresa=" . $row['idempresa'] . "');";
								} elseif ($resf["tiponf"] == 'T') {
									$janelanf = "janelamodal('?_modulo=nfcte&_acao=u&idnf=" . $row["idobjeto"] . "&_idempresa=" . $row['idempresa'] . "');";
								} else {
									$janelanf = "janelamodal('?_modulo=nfentrada&_acao=u&idnf=" . $row["idobjeto"] . "&_idempresa=" . $row['idempresa'] . "');";
								}

								if ($resf["tiponf"] == "C") {
									$tiponf = "ENTRADA";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == "V") {
									$tiponf = "SAÍDA";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == 'S') {
									$tiponf = "SERVIÇO";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == 'T') {
									$tiponf = "CTE";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == 'O') {
									$tiponf = "Outros";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
									if ($row["tipoespecifico"] == 'NORMAL') {
										$somarelatorio = 'N';
									}
								}
								if ($resf["tiponf"] == 'E') {
									$tiponf = "CONCESSIONÁRIA";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == 'M') {
									$tiponf = "GUIA/CUPOM";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == 'B') {
									$tiponf = "RECIBO";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == 'R') {
									$tiponf = "RH";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == 'F') {
									$tiponf = "FATURA";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}
								if ($resf["tiponf"] == 'D') {
									$tiponf = "SÓCIOS";
									$descnf = "NF " . $tiponf . " - " . $resf["nnfe"];
								}

								$pessoa = $resf["nome"];
								if ($resf['status'] != "CONCLUIDO") {
									$alertastatus = "<i class='fa fa-exclamation-triangle vermelho pointer' title='" . $resf['status'] . "'></i>";
								}
							} elseif ($row["tipoobjeto"] == "notafiscal" and !empty($row["idobjeto"])) {

								$sqlf = "select p.nome,n.numerorps,n.nnfe,n.idnotafiscal from notafiscal n,pessoa p where  p.idpessoa = n.idpessoa and idnotafiscal =" . $row["idobjeto"];
								$qrf = d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:" . mysqli_error(d::b()));
								$qtdrowsf = mysqli_num_rows($qrf);
								$resf = mysqli_fetch_assoc($qrf);
								$tiponf = "SAÍDA";
								$pessoa = $resf["nome"];
								$descnf = "NFS-E - " . $resf["nnfe"];

								$janelanf = "janelamodal('?_modulo=nfs&_acao=u&idnotafiscal=" . $row["idobjeto"] . "&_idempresa=" . $row['idempresa'] . "');";
							} else {

								if (!empty($row["idformapagamento"])) {
									$sqlff = "select c.descricao from formapagamento c  where c.idformapagamento =" . $row["idformapagamento"];
									$qrff = d::b()->query($sqlff) or die("Erro ao buscar descrição da formapagamento:" . mysqli_error(d::b()));
									$resff = mysqli_fetch_assoc($qrff);
									$descnf = $row["ndocumento"];
									$pessoa = $resff["descricao"];
									$janelanf = "janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=" . $row["idcontapagar"] . "&_idempresa=" . $row['idempresa'] . "');";
								} else {
									$descnf = $row["ndocumento"];
									$pessoa = "";
									$janelanf = "";
								}
							}

							if (!empty($row["idformapagamento"])) {
								$sqlff = "select c.* from formapagamento c  where c.idformapagamento =" . $row["idformapagamento"];
								$qrff = d::b()->query($sqlff) or die("Erro ao buscar descrição da formapagamento:" . mysqli_error(d::b()));
								$resff = mysqli_fetch_assoc($qrff);
								$formapagamento = $resff["descricao"];
							} else {
								$formapagamento = "";
							}
							
							if ($row["idpessoa"]) {
								$sqlpessoa = "SELECT idpessoa,nome,razaosocial,contrato,idtipopessoa FROM pessoa WHERE idpessoa = " . $row["idpessoa"];
								$respessoa = d::b()->query($sqlpessoa) or die("Erro ao buscar pessoa: " . $sqlpessoa);
								$rowpessoa = mysqli_fetch_assoc($respessoa);

								if (($rowpessoa["idtipopessoa"] == 1 and $rowpessoa["contrato"] == 'PJ') and (!empty($rowpessoa["razaosocial"]))) {
									$pessoa = $rowpessoa["razaosocial"];
								} else {
									$pessoa = $rowpessoa["nome"];
								}
							} elseif ($row["idcontadesc"]) {
								$pessoa = traduzid("contadesc", "idcontadesc", "contadesc", $row["idcontadesc"]);
							}


							if (empty($dtpagmento)) {
								$dtpagmento = $row["datareceb"];
							} elseif ($dtpagmento ==  $row["datareceb"]) {
								$quebralinha = 'N';
							} elseif ($dtpagmento <> $row["datareceb"]) {
								$quebroulinha = 'S';

								$data = date('Y-m-d');
								if ($quebroulinha == 'S' and (strtotime($data) < strtotime($dtpagmento) and  (strtotime($data) <> strtotime($dtpagmento)))) {

									$prevsaldototal = 0;
									$prevsaldototal =  $saldototal + $vlrpendcredito;
									$prevsaldototal =  $prevsaldototal - $vlrpenddebito;

									if ($prevsaldototal >= 0) {
										$corprevsaldo = "#98FB98"; //verde
									} else {
										$corprevsaldo = "#FFFF00"; //amarelo
									}
									$saldofim =	$prevsaldototal;
									?>
									<tr>
										<? if ($flgdiretor > 0 || $saldoextrato > 0) { ?>
											<td colspan="8">

											</td>
											<td align="right" class="respreto" colspan="3"><? echo "Prev. Saldo em " . $datasaldo . ":" ?></td>
											<td class="respreto" style="background-color:<?= $corprevsaldo ?>">
												<b><?= number_format(tratanumero($prevsaldototal), 2, ',', '.'); ?></b>
											</td>
										<? } else {
											echo ("<td colspan='12'></td>");
										} ?>
									</tr>
								<?
								} else {
									?>
									<tr>
										<? if ($flgdiretor > 0 || $saldoextrato > 0) { ?>
											<td colspan="8"><!-- a class="fa hoverazul pointer" onclick="janelamodal('?_modulo=contapagar&_acao=i&idagencia=<?= $idagencia ?>');"><font style="color: blue;display:inline;cursor:pointer; text-decoration: underline;">Nova Previsão</font></a --></td>
											<td align="right" class="respreto aqui 3" colspan="3"><? echo "Saldo em " . $datasaldo . ":" ?></td>
											<td class="respreto" style="background-color:<?= $corsaldo ?>">
												<b><?= number_format(tratanumero($saldototal), 2, ',', '.'); ?></b>
											</td>
											<?
											if ($saldook == 'N') {
											?>
												<td align="center">
													<a title="Aberto" class="fa fa-unlock fa-1x preto hoverazul btn-lg pointer" onclick="saldook(<?= $idcontapagarok ?>);"></a>
												</td>
											<?
											} else {
											?>
												<td align="center" style="cursor: pointer;">
													<a title="fechado" class="fa fa-lock fa-1x preto hoverazul btn-lg pointer" onclick=""></a>
												</td>
										<?
											}
										} else {
											echo ("<td colspan='12'></td>");
										}
										?>
									</tr>
									<?
									//se  for o dia atual deve mostrar a previsão eo saldo
									if (strtotime($data) == strtotime($dtpagmento)) {
										$prevsaldototal = 0;
										$prevsaldototal =  $saldototal + $vlrpendcredito;
										$prevsaldototal =  $prevsaldototal - $vlrpenddebito;

										if ($prevsaldototal >= 0) {
											$corprevsaldo = "#98FB98"; //verde
										} else {
											$corprevsaldo = "#FFFF00"; //amarelo
										}
										?>
										<tr>
											<? if ($flgdiretor > 0 || $saldoextrato > 0) { ?>
												<td colspan="8"></td>
												<td align="right" class="respreto" colspan="3"><? echo "Prev. Saldo em " . $datasaldo . ":" ?></td>
												<td class="respreto" style="background-color:<?= $corprevsaldo ?>">
													<b><?= number_format(tratanumero($prevsaldototal), 2, ',', '.'); ?></b>
												</td>
											<? } else {
												echo ("<td colspan='12'></td>");
											} ?>
										</tr>
										<?
									}
								}
								$dtpagmento =  $row["datareceb"];
							} //}elseif($dtpagmento <> $row["datareceb"]){		

							if ($row["tipo"] == "C") {
								if ($row["status"] == "PENDENTE") {
									$vlrpendcredito = $vlrpendcredito + $row["valor"];
									$cortr = "#98FB98"; //verde
									if (strtotime($data) >= strtotime($row["datareceb"])) {
										$mostracheck = "S";
										$newsaldo = $row["valor"] + $rowant["saldo"];
									} elseif (strtotime($data) < strtotime($row["datareceb"])) {
										$mostracheck = "S";
									}
								} elseif ($row["status"] == "ABERTO") {
									$vlrpendcredito = $vlrpendcredito + $row["valor"];
									$cortr = "#69b769";
									$mostracheck = "N";
									if (strtotime($data) >= strtotime($row["datareceb"])) {
										$mostracheck = "N";
										$newsaldo = $row["valor"] + $rowant["saldo"];
									} elseif (strtotime($data) < strtotime($row["datareceb"])) {
										$mostracheck = "N";
									}
								} elseif ($row["status"] == "FECHADO") {
									$vlrpendcredito = $vlrpendcredito + $row["valor"];
									$cortr = "#98FB98";
									$mostracheck = "N";
									if (strtotime($data) >= strtotime($row["datareceb"])) {
										$newsaldo = $row["valor"] + $rowant["saldo"];
									}
								} else {
									$vlrcredito = $vlrcredito + $row["valor"];
									$cortr = "#c8d0ff"; //azul
									$mostracheck = "N";
								}

								//$vlrcreditox =$vlrcreditox+$row["valor"];
								if ($row["status"] == "QUITADO") {
									if ($somarelatorio == 'Y') {
										$vlrquitadocredito = $vlrquitadocredito + $row["valor"];
									}
								} else {
									if ($somarelatorio == 'Y') {
										if ($row["vencido"] == 'N') {
											$vlrpendcreditox = $vlrpendcreditox + $row["valor"];
										} else {
											$vlrinadimplente = $vlrinadimplente + $row["valor"];
										}
									}
								}
							} elseif ($row["tipo"] == "D") {
								if ($row["status"] == "PENDENTE") {
									$vlrpenddebito = $vlrpenddebito + $row["valor"];
									$cortr = "#FFFF00"; //amarelo
									if (strtotime($data) >= strtotime($row["datareceb"])) {
										$mostracheck = "S";
										$newsaldo = $row["valor"] - $rowant["saldo"];
									} elseif (strtotime($data) < strtotime($row["datareceb"])) {
										$mostracheck = "S";
									}
									if ($row['progpagamento'] == "S") {
										$cortr = "#FF8C00"; //red + fraco
									}
								} elseif ($row["status"] == "ABERTO") {
									$vlrpenddebito = $vlrpenddebito + $row["valor"];
									$mostracheck = "N";
									$cortr = "#eea105cc"; //red + fraco
									if (strtotime($data) >= strtotime($row["datareceb"])) {
										$newsaldo = $row["valor"] - $rowant["saldo"];
									} elseif (strtotime($data) < strtotime($row["datareceb"])) {
										$mostracheck = "N";
									}
									if ($row['progpagamento'] == "S") {
										$cortr = "#FF8C00"; //red + fraco
									}
								} elseif ($row["status"] == "FECHADO") {
									$vlrpenddebito = $vlrpenddebito + $row["valor"];
									$cortr = "#b205ee5c"; //amarelo
									$mostracheck = "N";
									if (strtotime($data) >= strtotime($row["datareceb"])) {
										$newsaldo = $row["valor"] - $rowant["saldo"];
									}
									if ($row['progpagamento'] == "S") {
										$cortr = "#FF8C00"; //red + fraco
									}
								} else {
									$vlrdebito = $vlrdebito + $row["valor"];
									$cortr = "#f0bfbf"; //red + fraco
									$mostracheck = "N";
									$agendar = "N";
								}

								// $vlrdebitox =$vlrdebitox+$row["valor"];
								if ($row["status"] == "QUITADO") {
									if ($somarelatorio == 'Y') {
										$vlrquitadodebito = $vlrquitadodebito + $row["valor"];
									}
								} elseif ($row["status"] != "ABERTO") {
									if ($somarelatorio == 'Y') {
										$vlrpenddebitox = $vlrpenddebitox + $row["valor"];
									}
								}
								if ($row["status"] == "ABERTO" and $somarelatorio == 'Y') {

									if (($resff['formapagamento'] == 'C.CREDITO') or ($resff['formapagamento'] == 'BOLETO' and $resff['agruppessoa'] == 'Y')) {
										$vlrprogramadox = $vlrprogramadox + $row["valor"];
									} else {
										$vlrprogramadopagar = $vlrprogramadopagar + $row["valor"];
									}
								}
							} //elseif($row["tipo"]=="D"){
							$temcheckporcor = 'N';

							if ($datastring != $ultimadatastring) {
								$ultimadatastring = $datastring;
								$strtipocd = $row["tipo"];
								$temcheckporcor = 'Y';
								?>
								<tr>
									<td colspan="12"></td>
									<td style="text-align-last: center;">
										<input title="Marcar/Desmarcar todos do dia" valor="" name="chkdia" value="" type="checkbox" onchange="checkdia(this,'<?= $datastring ?>')">
									</td>
								</tr>
							<?
							} elseif ($strtipocd != $row["tipo"]) {
								$strtipocd = $row["tipo"];
								$temcheckporcor = 'Y';
							}
							if ((empty($tipo) or $tipo == $row["tipo"]) and (empty($status) or $status == $row["status"])) {
							?>

								<tr class="respreto" style="background-color: <?= $cortr ?>;" id="linha">
									<td>

									</td>
									<td><?= $row["dtreceb"] ?></td>
									<td align="center"><?= $row["tipo"] ?></td>
									<td><? echo ($row["parcela"] . " / " . $row["parcelas"]); ?></td>
									<td title="<?= $row['alteradopor'] ?>" style="font-size: 9px;" class="nowrap"><?= dmahms($row["alteradoem"]) ?></td>
									<td>
										<a class="fa show-print hoverazul pointer" onclick="<?= $janelanf ?>">
											<b style="font-size: 11px;"><?= $descnf ?></b>
										</a>
									</td>
									<td><?= $pessoa ?></td>
									<td style="max-width: 450px;font-size: 9px;"><?= $row["obs"] ?></td>
									<td align="right">
										<a class="fa show-print hoverazul pointer" onclick="<?= $stredit ?>">
											<b style="font-size: 11px;"><? if (empty($row["valor"])) {
																			echo '0.00';
																		} else { ?><?= number_format(tratanumero($row["valor"]), 2, ',', '.'); ?><? } ?></b>
										</a>
									</td>
									<td>
										<? echo ($row["status"]); ?>
									</td>
									<td>
										<?
										if ($row['progpagamento'] == 'S') {
											$vprogramado = $vprogramado + $row["valor"];
										?>
											<a title="Agendado para ser quitado automaticamente pelo sistema." class="fa fa-clock-o fa-1x preto hoverazul btn-lg pointer" onclick=""></a>

										<?
										}

										?>
									</td>
									<td class="nowrap">
										<?
										if ($_1_u_contapagar_tipoespecifico != 'NORMAL') {
											$sqlci = "select sum(valor) as valori from contapagaritem where status  NOT IN('INATIVO','DEVOLVIDO','CANCELADO') and idcontapagar=" . $row['idcontapagar'];
											$resci = d::b()->query($sqlci) or die("Erro ao buscar valor da contapagaritem sql=" . $sqlci);
											$rowci = mysqli_fetch_assoc($resci);
											if (tratanumero($row['valor']) != $rowci['valori']) {
												$dif = $rowci['valori'] - tratanumero($_1_u_contapagar_valor);

												$strdif = "<i class='fa fa-exclamation-triangle vermelho pointer' title='Valor dos itens difere da parcela!!!'></i>";
											} else {
												$strdif = "";
											}

											if ($row['tipoespecifico'] == 'REPRESENTACAO' or $row["tipoespecifico"] == 'IMPOSTO') {
										?>
												<?= $strdif ?>
											<?
											} elseif ($row['tipoespecifico'] == 'AGRUPAMENTO') {
											?>
												<?= $strdif ?>
										<?
											}
										}

										echo $alertastatus;
										?>
										<? if ($rwtemar > 0) { ?>
											<a class="fa fa-paperclip fa-1x verde hoverazul pointer" title="Existe arquivo anexo na fatura ou na nota;" onclick="poparquivo(<?= $row['idcontapagar'] ?>)"></a>
											<div id="arquivo<?= $row['idcontapagar'] ?>" class="hide">
												<ul class="listaitens">
													<li class="cab">Arquivos Anexos (<?= $rwtemar ?>)</li>
													<? while ($rowtemar = mysqli_fetch_array($rtemarq)) { ?>
														<li><a style="overflow-wrap: break-word;" title="Abrir arquivo" target="_blank" href="../upload/<?= $rowtemar["nome"] ?>"><?= $rowtemar["nome"] ?></a></li>
													<?			}
													?>
												</ul>
											</div>
										<? } ?>
									</td>
									<td align='center'>
										<?
										$disabledok = '';
										if ($mostracheck == "N") {
										?>
											<input name="naoenviar_idcontapagar" type="hidden" value="<?= $row["idcontapagar"] ?>">
											<input title="Marcar/Desmarcar este" class="<?= $datastring ?>_<?= $row["tipo"] ?> <?= $datastring ?>" <?= $disabledok ?> valor="<? if ($row["tipo"] == 'D') {
																																											echo '-';
																																										} ?><?= number_format($row["valor"], 2, '.', ''); ?>" style="border: 2px solid green;" name="chk[<?= $ip ?>]" value="<?= $row["idcontapagar"] ?>" type="checkbox" onchange="somavalores()">

											<?
										} elseif ($mostracheck == "S") { //MOSTRA O CHECK PARA SELECIONAR
											if ($row["sok"] > 0) {
												$disabledok = "disabled='disabled'";
											}

											if ($row["tipoespecifico"] != 'NORMAL') {

												if ($row["tipoespecifico"] == 'REPRESENTACAO' or $row["tipoespecifico"] == 'IMPOSTO') {
													$sqlci = "select sum(valori) as valori from (
																select sum(i.valor) as valori from contapagaritem i join contapagar c on(c.idcontapagar = i.idobjetoorigem )
																where  i.status  NOT IN('INATIVO','DEVOLVIDO','CANCELADO')
																and i.tipoobjetoorigem='contapagar'
																and i.idcontapagar=" . $row['idcontapagar'] . "
																union                     
																select sum(valor) as valori from contapagaritem where status NOT IN('INATIVO','DEVOLVIDO','CANCELADO') and tipoobjetoorigem ='nf' 
																and idcontapagar=" . $row['idcontapagar'] . "
															) as u";
												} else {
													$sqlci = "select sum(valor) as valori from contapagaritem where status  NOT IN('INATIVO','DEVOLVIDO','CANCELADO') and idcontapagar=" . $row['idcontapagar'];
												}

												$resci = d::b()->query($sqlci) or die("Erro ao buscar valor da contapagaritem sql=" . $sqlci);
												$rowci = mysqli_fetch_assoc($resci);
												if (tratanumero($row['valor']) != $rowci['valori']) {
													$dif = $rowci['valori'] - tratanumero($row['valor']);
													?>
													<i class='fa fa-exclamation-triangle vermelho pointer' title='Valor dos itens difere da parcela!!!'></i>
													<?
												} else {
												?>
													<span style="display:inline;" roh="true">
														<input name="_<?= $ip ?>_u_contapagar_idcontapagar" type="hidden" value="<?= $row["idcontapagar"] ?>">
														<input title="Marcar/Desmarcar este" class="<?= $datastring ?>_<?= $row["tipo"] ?> <?= $datastring ?>" <?= $disabledok ?> valor="<? if ($row["tipo"] == 'D') {
																																														echo '-';
																																													} ?><?= number_format($row["valor"], 2, '.', ''); ?>" style="background-color:#cccccc;" name="chk[<?= $ip ?>]" value="<?= $row["idcontapagar"] ?>" type="checkbox" onchange="somavalores()">
													</span>
												<?
												}
											} else {
												?>
												<span style="display:inline;" roh="true">
													<input name="_<?= $ip ?>_u_contapagar_idcontapagar" type="hidden" value="<?= $row["idcontapagar"] ?>">
													<input title="Marcar/Desmarcar este" class="<?= $datastring ?>_<?= $row["tipo"] ?> <?= $datastring ?>" <?= $disabledok ?> valor="<? if ($row["tipo"] == 'D') {
																																													echo '-';
																																												} ?><?= number_format($row["valor"], 2, '.', ''); ?>" style="background-color:#cccccc;" name="chk[<?= $ip ?>]" value="<?= $row["idcontapagar"] ?>" type="checkbox" onchange="somavalores()">
												</span>
										<?
											}
										}
										?>
									</td>
									<?
									if ($row['tipo'] == 'D') {
										//$corcheck="#f0bfbf";
										$strtipodescr = "débitos";
									} else {
										//$corcheck="#98FB98";
										$strtipodescr = "crébitos";
									}
									?>
									<td style="text-align-last: center; ">
										<? if ($temcheckporcor == 'Y') { ?>
											<input title="Marcar/Desmarcar todos os <?= $strtipodescr ?>" valor="" name="chkdia" value="" type="checkbox" onchange="checkdia(this,'<?= $datastring ?>_<?= $row['tipo'] ?>')">
										<? } ?>
									</td>
								</tr>
							<?
							}
							if ($rows["status"] == "QUITADO" and !empty($rows["saldo"])) {
								//ja esta quitado 
								$saldototal = $rows["saldo"];
								if ($saldototal >= 0) {
									$corsaldo = "#c8d0ff";
								} else {
									$corsaldo = "#f0bfbf";
								}
							}

							$idcontapagarok = $row["idcontapagar"];
							if ($row["saldook"] == 'Y') {
								$saldook = 'Y';
							} else /*if($row["status"]!="PENDENTE" or $row["vencido"]=='N' )*/ {
								$saldook = 'N';
							}
							$datasaldo = $row["dtreceb"];

							if ($somarelatorio == 'N') {
								if ($row["tipo"] == "D") {
									$vlrndescriminados = $vlrndescriminados - $row["valor"];
									$vlrndescriminadosD = $vlrndescriminadosD + $row["valor"];
								} else {
									$vlrndescriminados = $vlrndescriminados + $row["valor"];
									$vlrndescriminadosC = $vlrndescriminadosC + $row["valor"];
								}
							}
						} //while ($row = mysqli_fetch_array($res)){

						$saldofim = $saldototal;
						$somatotais = $vlrcredito - $vlrdebito; //a soma do total
						$fimpendcredito = $vlrpendcredito + $vlrcredito;
						$fimpenddebito = $vlrpenddebito + $vlrdebito;
						// $saldofim = $fimpendcredito - $fimpenddebito;
						$vlrpendentefim = $vlrpendcredito - $vlrpenddebito;

						if ($quebroulinha == 'S' and (strtotime($data) < strtotime($dtpagmento) and  (strtotime($data) <> strtotime($dtpagmento)))) {

							$prevsaldototal = 0;
							$prevsaldototal =  $saldototal + $vlrpendcredito;
							$prevsaldototal =  $prevsaldototal - $vlrpenddebito;

							if ($prevsaldototal >= 0) {
								$corprevsaldo = "#98FB98"; //verde
							} else {
								$corprevsaldo = "#FFFF00"; //amarelo
							}
							?>
							<tr>
								<? if ($flgdiretor > 0 || $saldoextrato > 0) { ?>
									<td colspan="8"><!-- a class="fa hoverazul pointer" onclick="janelamodal('?_modulo=contapagar&_acao=i&idagencia=<?= $idagencia ?>');"><font style="color: blue;display:inline;cursor:pointer; text-decoration: underline;">Nova Previsão</font></a --></td>
									<td align="right" class="respreto" colspan="3"><? echo "Prev. Saldo em " . $datasaldo . ":" ?></td>
									<td class="respreto" style="background-color:<?= $corprevsaldo ?>">
										<b><?= number_format(tratanumero($prevsaldototal), 2, ',', '.'); ?></b>
									</td>
								<? } else {
									echo ("<td colspan='12'></td>");
								} ?>
							</tr>
						<?
						} else { //if($quebroulinha=='S' and (strtotime($data) < strtotime($dtpagmento) and  (strtotime($data) <> strtotime($dtpagmento)))){
							if ($saldototal >= 0) {
								$corsaldo = "#c8d0ff";
							} else {
								$corsaldo = "#f0bfbf";
							}
						?>
							<tr>
								<? if ($flgdiretor > 0 || $saldoextrato > 0) { ?>
									<td colspan="8"><!-- a class="fa hoverazul pointer" onclick="janelamodal('?_modulo=contapagar&_acao=i&idagencia=<?= $idagencia ?>');"><font style="color: blue;display:inline;cursor:pointer; text-decoration: underline;">Nova Previsão</font></a --></td>
									<td align="right" class="respreto aqui2" colspan="3"><? echo "Saldo em " . $datasaldo . ":" ?></td>
									<td class="respreto" style="background-color: <?= $corsaldo ?>">
										<b> <?= number_format(tratanumero($saldototal), 2, ',', '.'); ?></b>
									</td>
									<?
									if ($saldook == 'N') {
									?>
										<td align="center">
											<a title="Aberto" class="fa fa-unlock fa-1x preto hoverazul btn-lg pointer" onclick="saldook(<?= $idcontapagarok ?>);"></a>
										</td>
									<?
									} else {
									?>
										<td align="center" style="cursor: pointer;">
											<a title="fechado" class="fa fa-lock fa-1x preto hoverazul btn-lg pointer" onclick=""></a>
										</td>
								<?
									}
								} else {
									echo ("<td colspan='12'></td>");
								} ?>
							</tr>
							<?
							//se  for o dia atual deve mostrar a previsão eo saldo
							if (strtotime($data) == strtotime($dtpagmento)) {
								$prevsaldototal = 0;
								$prevsaldototal =  $saldototal + $vlrpendcredito;
								$prevsaldototal =  $prevsaldototal - $vlrpenddebito;

								if ($prevsaldototal >= 0) {
									$corprevsaldo = "#98FB98"; //verde
								} else {
									$corprevsaldo = "#FFFF00"; //amarelo
								}
							?>
								<tr>
									<? if ($flgdiretor > 0 || $saldoextrato > 0) { ?>
										<td colspan="8"></td>
										<td align="right" class="respreto"><? echo "Prev. Saldo:" ?></td>
										<td class="respreto" style="background-color:<?= $corprevsaldo ?>">
											<b> <?= number_format(tratanumero($prevsaldototal), 2, ',', '.'); ?></b>
										</td>
									<? } else {
										echo ("<td colspan='10'></td>");
									} ?>
								</tr>
						<?
							}
						} //if($quebroulinha=='S' and (strtotime($data) < strtotime($dtpagmento) and  (strtotime($data) <> strtotime($dtpagmento)))){
						echo ("<tr><td>&nbsp;</td></tr>");

						$cortrfim = "";
						if ($somatotais >= 0) {
							$cortrfim = "#c8d0ff";
						} else {
							$cortrfim = "#FF6347";
						}
						?>

					</table>
					<br>
					<?
					//$vlrprogramado=0;
					foreach ($arrprogramado as $idcontapagar => $valor) {
						$vlrprogramadopagar = $vlrprogramadopagar + $valor;
					}

					//novos calculos conforme despesas
					$vlrcreditox = $vlrquitadocredito + $vlrpendcreditox + $vlrinadimplente + $vlrndescriminadosC;

					$vlrdebitox = $vlrquitadodebito + $vlrpenddebitox + $vlrprogramadopagar + $vlrprogramadox + $vlrndescriminadosD;

					$somatotais = $vlrcreditox - $vlrdebitox; //a soma do total

					?>

				</div>
			</div>
		</div>
	</div>
<?
} //if($_GET){
?>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

<style>
	.borda-redonda {
		border-collapse: collapse;
		/* Garante que as bordas das células colidam e formem uma borda única */
		width: 100%;
		/* Define a largura da tabela */
		overflow: hidden;
		/* Garante que as bordas redondas sejam aplicadas corretamente */
	}

	.borda-redonda th,
	.borda-redonda td {
		padding: 8px;
		/* Adicione preenchimento conforme necessário */
		text-align: left;
		/* Ajuste o alinhamento do texto conforme necessário */
	}

	.linha-cabecalho {
		background-color: #ddd !important;
	}
</style>
<div id="Footer">
	<div id="resumo" class="w-100 hide">
		<div class="w-100 d-flex flex-wrap" style="width: 95% !important;margin: auto;">
			<!-- CRÉDITO -->
			<div class="col-xs-6">
				<table class="table table-striped planilha borda-redonda">
					<thead>
						<tr class="linha-cabecalho">
							<td class="text-center fw-bold py-1" colspan="12">CRÉDITO</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td bgcolor="#c8d0ff">Crédito Recebido</td>
							<td style="text-align: right;" bgcolor="#c8d0ff">
								<? if ((array_key_exists("quitarcredito", getModsUsr("MODULOS")) and  $saldoextrato>0 ) or $flgdiretor > 0 ) { ?>
									<?= number_format(tratanumero($vlrquitadocredito), 2, ',', '.'); ?>
								<? } ?>
							</td>
						</tr>
						<tr>
							<td bgcolor="#98FB98">Crédito a Receber</td>
							<td style="text-align: right;" bgcolor="#98FB98">
								<? if ((array_key_exists("quitarcredito", getModsUsr("MODULOS")) and $saldoextrato>0) or $flgdiretor > 0) { ?>
									<?= number_format(tratanumero($vlrpendcreditox), 2, ',', '.'); ?>
								<? } ?>
							</td>
						</tr>
						<tr>
							<td bgcolor="">Crédito Inadimplente</td>
							<td style="text-align: right;" bgcolor=""><b><?= number_format(tratanumero($vlrinadimplente), 2, ',', '.'); ?></b></td>
						</tr>
						<tr>
							<td bgcolor="">Valores não Discriminados</td>
							<td style="text-align: right;" bgcolor=""><b><?= number_format(tratanumero($vlrndescriminadosC), 2, ',', '.'); ?></b></td>
						</tr>
					</tbody>
				</table>
				<table class="table table-striped planilha borda-redonda">
					<tbody>
						<tr class="fw-bold linha-cabecalho">
							<td bgcolor="">CRÉDITO TOTAL</td>
							<td style="text-align: right;" bgcolor="">
								<? if ((array_key_exists("quitarcredito", getModsUsr("MODULOS")) and  $saldoextrato>0) or $flgdiretor > 0) { ?>
									<b><?= number_format(tratanumero($vlrcreditox), 2, ',', '.'); ?></b>
								<? } ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<!-- DÉBITO -->
			<div class="col-xs-6 pr-0">
				<table class="table table-striped planilha borda-redonda">
					<thead>
						<tr class="linha-cabecalho fw-bold">
							<td colspan="2" class="text-center py-1">DÉBITO</td>
							<!-- td colspan="2" style="text-align: center;">OUTROS VALORES</td -->
						</tr>
					</thead>
					<tbody>
						<tr>
							<td bgcolor="#f0bfbf"> Despesa Paga</td>
							<td style="text-align: right;" bgcolor="#f0bfbf">
								<? if ((array_key_exists("quitardebito", getModsUsr("MODULOS")) and  $saldoextrato>0) or $flgdiretor > 0) { ?>
									-<?= number_format(tratanumero($vlrquitadodebito), 2, ',', '.'); ?>
								<? } ?>
							</td>
							<!-- td bgcolor="" title="Transação de valores entre contas">Valores não Discriminados</td>
							<td  style="text-align: right;" bgcolor="" title="Transação de valores entre contas"><b><?= number_format(tratanumero($vlrndescriminados), 2, ',', '.'); ?></b></td -->
						</tr>
						<tr>
							<td bgcolor="#FFFF00">Despesa a Pagar</td>
							<td style="text-align: right;" bgcolor="#FFFF00">
								<? if ((array_key_exists("quitardebito", getModsUsr("MODULOS")) and  $saldoextrato>0) or $flgdiretor > 0) { ?>
									-<?= number_format(tratanumero($vlrpenddebitox), 2, ',', '.'); ?>
								<? } ?>
							</td>
							<!-- td ></td>
							<td ></td -->
						</tr>
						<tr>
							<td bgcolor="#FF8C00" title="Débito provisionado, parcela em aberto">Despesa Programada</td>
							<td style="text-align: right;" bgcolor="#FF8C00" title="Débito provisionado, parcela em aberto">
								<? if ((array_key_exists("quitardebito", getModsUsr("MODULOS")) and  $saldoextrato>0) or $flgdiretor > 0) { ?>
									-<?= number_format(tratanumero($vlrprogramadopagar), 2, ',', '.'); ?>
								<? } ?>
							</td>
						</tr>
						<tr>
							<td bgcolor="#FF8C00" title="Diferença entre valor provisionado e item lançado, ex fat cartão credito">Despesa Provisionada</td>
							<td style="text-align: right;" bgcolor="#FF8C00" title="Diferença entre valor provisionado e item lançado, ex fat cartão credito">
								-<?= number_format(tratanumero($vlrprogramadox), 2, ',', '.'); ?>
							</td>
						</tr>
						<tr>
							<td bgcolor="">Valores não Discriminados</td>
							<td style="text-align: right;" bgcolor=""><b><?= number_format(tratanumero($vlrndescriminadosD), 2, ',', '.'); ?></b></td>
						</tr>
						<tr class="fw-bold">
							<td bgcolor="">DÉBITO TOTAL</td>
							<td style="text-align: right;" bgcolor="">
								<? if ((array_key_exists("quitardebito", getModsUsr("MODULOS")) and  $saldoextrato>0) or $flgdiretor > 0) { ?>
									<b>-<?= number_format(tratanumero($vlrdebitox), 2, ',', '.'); ?></b>
								<? } ?>
							</td>
						</tr>

						<?
						if ($vlrtotal > 0) {
							$corvalor = '#c8d0ff';
						} else {
							$corvalor = '#f0bfbf';
						}
						?>
					</tbody>
				</table>
				<? if ($flgdiretor > 0 || $saldoextrato > 0) { ?>
					<table class="table table-striped planilha borda-redonda">
						<tbody>
							<tr class="fw-bold">
								<?
								if ($somatotais > 0) {
									$corvalor = '#c8d0ff';
								} else {
									$corvalor = '#f0bfbf';
								}
								?>
								<td bgcolor="<?= $corvalor ?>"><b>SOMA</b> (Crédito+Débito)</td>
								<td style="text-align: right;" bgcolor="<?= $corvalor ?>"><?= number_format(tratanumero($somatotais), 2, ',', '.'); ?> </td>
							</tr>
						</tbody>
					</table>
				<? } ?>
			</div>
			<hr>
		</div>
	</div>
	<div class="row clsFooterf pt-2 d-flex align-items-center m-0">
		<div class="col-xs-2 nowrap"></div>
		<div class="col-xs-3 text-left">
			<? if ((array_key_exists("quitardebito", getModsUsr("MODULOS")) or array_key_exists("quitarrh", getModsUsr("MODULOS")) and $saldoextrato>0) or $flgdiretor > 0) { ?>
				<button class="btn btn-info btn-xs disabled botaoacao mr-4 py-1" onclick="quitar(this,'PROGRAMAR', '1339');">
					<i class="fa fa-circle"></i> Programar
				</button>
			<? } ?>
			<? if ((array_key_exists("quitarcredito", getModsUsr("MODULOS")) and $saldoextrato>0) or $flgdiretor > 0) { ?>
				<button class="btn btn-danger btn-xs disabled botaoacao py-1" onclick="quitar(this,'QUITAR', '906');">
					<i class="fa fa-circle"></i> Quitar
				</button>
			<? } ?>
		</div>
		<div class="col-md-3 nowrap" style="font-size: 14px;">
			<span>Dia:</span>
			<input class="size3" id="dia" name="dia" type="text" size="1" value="1" style="background-color:white; text-align: center; align-self: center;letter-spacing: 3px;">
			<button style="font-size: 14px;" class="btn btn-default btn-primary btn-xs disabled botaoacao" onclick="maisumdia(this);">
				<span class="fa fa-plus"> ( Dia )</span>
			</button>
			<button style="font-size: 14px;" class="btn btn-default btn-primary btn-xs disabled botaoacao" onclick="menosumdia(this);">
				<span class="fa fa-minus"> ( Dia )</span>
			</button>
		</div>
		<div class="col-md-3" style="font-size: 14px;">
			<span>Itens Selecionados</span> (<span id="qtdvalor" style="font-weight: bold;">0</span>) <br>
			<span>Total: R$</span> <span id="valor" style="font-weight: bold;">0.00</span>
		</div>
		<div class="col-md-1" style="padding: 10px 5px;">
			<i id="resumofechar" title="Fechar Tabela" class="fa fa-chevron-down fa-2x pointer  hide  " onclick="afechar('resumoabrir','resumofechar','hide');"></i>
			<i id="resumoabrir" title="Mostrar Tabela" class="fa fa-chevron-up fa-2x pointer  " onclick="afechar('resumofechar','resumoabrir','show');"></i>
		</div>
	</div>
</div>
<script>
	/*
	 * Funcao para preencher automaticamente valores de campos "gemeos" ex: data_1 e data_2
	 */
	function fill_2(inobj) {
		//Confirma se o objeto possui a identificacao correta (nomecampo_1) para gemeos
		if (inobj.id.indexOf("_2") > -1) {
			var strnome_1 = inobj.id.replace("_2", "_1");
			var obj_1 = document.getElementById(strnome_1);

			if (inobj != null && inobj.value == "") {
				inobj.value = obj_1.value;
				inobj.select();
			}
		}
	}

	function afechar(mostrar, ocultar, classtb) {
		$("#" + ocultar).addClass('hide');
		$("#" + mostrar).removeClass('hide');
		$("#resumo").removeClass('hide');
		$("#resumo").removeClass('show');
		$("#resumo").addClass(classtb);

	}


	function somavalores() {
		var valor = 0;
		var qtd = 0;
		//debugger;
		$('input[type=checkbox]:checked').not('[name*="chkdia"]').each(function(i, el) {
			var elem = $(el);
			console.log(elem.attr('valor'));
			valor = valor + parseFloat(elem.attr('valor'));
			qtd = qtd + 1;
		});

		const formatoNumerico = valor.toLocaleString('pt-BR', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});

		document.getElementById("valor").innerHTML = formatoNumerico;
		document.getElementById("qtdvalor").innerHTML = qtd;


		var inputprenchido = $("#inftable").children().find("input:checkbox:checked");

		//pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
		var vsubmit = $(inputprenchido).parent().parent().find("input:text, input:hidden").not('[name*="naoenviar_idcontapagar"]').serialize();

		if (vsubmit == '') {
			$(".botaoacao").addClass('disabled');
		} else {
			$(".botaoacao").removeClass('disabled');
		}

	}


	//FUNààO PARA QUITAR 
	function quitar(vthis, status, idfluxostatus) {
		//pega todos os inputs checkados 		
		var inputprenchido = $("#inftable").children().find("input:checkbox:checked");
		var inputchecado = $("#inftable").children().find("input:checkbox:checked").not('[name*="chkdia"]');

		//pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
		var vsubmit = $(inputprenchido).parent().parent().find("input:text, input:hidden").not('[name*="naoenviar_idcontapagar"]').serialize();

		var contador = 0;

		if (vsubmit !== '') {
			contador = (vsubmit.match(/&/g) || []).length;
			contador = contador + 1;
		}
		var qMarcados = inputchecado.length;

		vsubmit = vsubmit.concat("&status=" + status + "&idfluxostatus" + idfluxostatus);

		if (confirm("Apenas os itens selecionados com status PENDENTE serão alterados: \n (" + contador + ") - PENDENTE \n (" + qMarcados + ") - Total selecionado")) {
			//insere no banco de dados via submitajax
			CB.post({
				objetos: vsubmit,
				parcial: true
			})
		}
	}
	//AVANàAR A CONTA PARA UM DIA A MAIS
	function maisumdia(vthis) {
		var dia = $('#dia').val();

		//pega todos os inputs checkados 		
		var inputprenchido = $("#inftable").children().find("input:checkbox:checked");
		var inputchecado = $("#inftable").children().find("input:checkbox:checked").not('[name*="chkdia"]');

		//pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
		var vsubmit = $(inputprenchido).parent().parent().find("input:text, input:hidden").not('[name*="naoenviar_idcontapagar"]').serialize();

		var contador = 0;

		if (vsubmit !== '') {
			contador = (vsubmit.match(/&/g) || []).length;
			contador = contador + 1;
		}
		var qMarcados = inputchecado.length;

		vsubmit = vsubmit.concat("&status=MAISUMDIA&dia=" + dia);

		if (confirm("Apenas os itens selecionados com status PENDENTE serão alterados: \n (" + contador + ") - PENDENTE \n (" + qMarcados + ") - Total selecionado")) {
			//insere no banco de dados via submitajax
			CB.post({
				objetos: vsubmit,
				parcial: true
			})
		}
	}
	//VOLTAR A CONTA UM DIA MENOS
	function menosumdia(vthis) {
		var dia = $('#dia').val();
		//pega todos os inputs checkados 		
		var inputprenchido = $("#inftable").children().find("input:checkbox:checked");

		var inputchecado = $("#inftable").children().find("input:checkbox:checked").not('[name*="chkdia"]');

		//pega todos os input e os inputs checkados e transforma em string para enviar para submit 		
		var vsubmit = $(inputprenchido).parent().parent().find("input:text, input:hidden").not('[name*="naoenviar_idcontapagar"]').serialize();
		var contador = 0;
		if (vsubmit !== '') {
			contador = (vsubmit.match(/&/g) || []).length;
			contador = contador + 1;
		}

		var qMarcados = inputchecado.length;

		vsubmit = vsubmit.concat("&status=MENOSUMDIA&dia=" + dia);
		if (confirm("Apenas os itens selecionados com status PENDENTE serão alterados: \n (" + contador + ") - PENDENTE \n (" + qMarcados + ") - Total selecionado")) {
			//insere no banco de dados via submitajax
			CB.post({
				objetos: vsubmit,
				parcial: true
			})
		}
	}

	function saldook(vidcontapagar) {
		if (confirm("Fechar o saldo dos dias anteriores?")) {
			document.body.style.cursor = 'wait';
			$.get("ajax/saldook.php", {
					idcontapagar: vidcontapagar
				},
				function(resposta) {
					$("#resp").html(resposta);
					if (resposta == "OK") {
						//$('#frm').submit();
						document.location.reload(true);
					} else {
						alert(resposta);
					}
				}
			);
			document.body.style.cursor = '';
		}
	}

	function pesquisar(vthis) {
		var idempresa = $("[name=idempresa]").val();
		var idagencia = $("[name=idagencia]").val();
		if (!idempresa || !idagencia) {
			alert('Selecione a agência.');
		} else {
			$(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
			var vencimento_1 = $("[name=vencimento_1]").val();
			var vencimento_2 = $("[name=vencimento_2]").val();
			var idagencia = $("[name=idagencia]").val();
			var tipo = $("[name=tipo]").val();
			var status = $("[name=status]").val();
			var str = "vencimento_1=" + vencimento_1 + "&vencimento_2=" + vencimento_2 + "&idempresa=" + idempresa + "&idagencia=" + idagencia + "&tipo=" + tipo + "&status=" + status;
			CB.go(str);
			CB.oCarregando.hide();
		}
	}

	$(document).keypress(function(e) {
		if (e.which == 13) {
			pesquisar();
		}
	});

	function reldet(inrel) {

		//var idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa="+getUrlParameter("_idempresa") : '';

		var vencimento_1 = $("[name=vencimento_1]").val();
		var vencimento_2 = $("[name=vencimento_2]").val();
		var idagencia = $("[name=idagencia]").val();
		var idempresa = ($("[name=idempresa]").val()) ? "&_idempresa=" + $("[name=idempresa]").val() : '';

		var str = "vencimento_1=" + vencimento_1 + "&vencimento_2=" + vencimento_2 + "&idagencia=" + idagencia + idempresa;
		if (inrel == 1) {
			janelamodal('report/relextrato.php?' + str + '');
		}
		if (inrel == 2) {
			janelamodal('report/relextratocp.php?' + str + '');
		}
		if (inrel == 3) {
			janelamodal('report/relextratoctpag.php?' + str + '');
		}
		if (inrel == 4) {
			janelamodal('report/relextratocap.php?' + str + '');
		}
		if (inrel == 5) {
			janelamodal('report/relextratoespecie.php?' + str + '');
		}

	}

	function reldetc(intipo) {

		var vencimento_1 = $("[name=vencimento_1]").val();
		var vencimento_2 = $("[name=vencimento_2]").val();
		var idagencia = $("[name=idagencia]").val();

		var str = "vencimento_1=" + vencimento_1 + "&vencimento_2=" + vencimento_2 + "&idagencia=" + idagencia + "&tipo=" + intipo;
		janelamodal('report/relextratocompras.php?' + str + '');

	}

	function selecionarAgencia(valor) {
		var idempresa = $("[name=idempresa]").val();
		var vencimento_1 = $("[name=vencimento_1]").val();
		var vencimento_2 = $("[name=vencimento_2]").val();
		var tipo = $("[name=tipo]").val();
		var status = $("[name=status]").val();
		var str = "vencimento_1=" + vencimento_1 + "&vencimento_2=" + vencimento_2 + "&idempresa=" + idempresa + "&tipo=" + tipo + "&status=" + status;
		CB.go(str);
	}

	//Montar legenda para o usuário
	CB.montaLegenda({
		"#f0bfbf;": "Quitado Débito (Parcela Quitada)",
		"#FF8C00": "Programado Débito (Parcela Registrada no Banco)",
		"#FFFF00": "Pendente Débito (Parcelada Pendente de Pagamento)",
		"#b205ee5c": "Fechado Débito (Lançamento em Análise. Ex: Fatura de Cartão de Crédito)",
		"#eea105cc": "Aberto Débito (Lançamento Provisionado)",
		"#c8d0ff": "Quitado Crédito (Crédito Recebido)",
		"#98FB98": "Pendente Crédito (Crédito a Receber)",
		"#69b769": "Aberto (Crédito Programado. Ex: NF não emitida)",
		"#80808000; border: none": " ",
		"#817d7d00; border: none": " "
	});



	function checkdia(vthis, strdia) {

		if ($(vthis).is(":checked")) {
			$("." + strdia).prop("checked", true);
		} else {
			$("." + strdia).prop("checked", false);
		}
		somavalores();
	}

	function poparquivo(id) {
		CB.modal({
			titulo: "Anexos",
			corpo: $("#arquivo" + id).html(),
			// classe: "vinte"
		})
	}

	CB.oPanelLegenda.css("zIndex", 901).css("left", "100px").css("right", "unset").addClass('screen');

	$(document).ready(function() {
		/*	
			$(document).on('click', e => {
			
				if(!$(e.target).closest('#cbPanelLegenda').length);{
					$('#cbPanelLegendaBody').collapse('hide');
					CB.setPrefUsuario('u',CB.modulo+'.legenda','N');
				}	

			})
		*/
		/*
		$(window).scroll(function() {
			if ($(window).scrollTop() + $(window).height() == $(document).height()) {
				// Se chegou ao final da página, chame afechar
				afechar('resumofechar', 'resumoabrir', 'show')
			}
		});
*/
		//	CB.setPrefUsuario('u',CB.modulo+'.legenda','N');

		$("#cbLegendaAbrir").removeClass('fa-chevron-up');
		$("#cbLegendaFechar").removeClass('fa-chevron-down');

		$("#cbLegendaAbrir")
			.addClass('fa-question-circle')
			.addClass('fa-3x')
			.addClass('mr-2');

		$("#cbLegendaFechar")
			.addClass('fa-question-circle')
			.addClass('fa-3x')
			.addClass('mr-2');

		$("#cbPanelLegenda .panel-heading label").remove();
		$("#cbPanelLegenda .panel-heading").append(`<label style='color: #000 !important;'>Ajuda</label>`);

		if (!$("#cbPanelLegenda .panel-heading .fechar-legenda").get().length)
			$("#cbPanelLegenda .panel-heading").append(`<i class="fa fa-close fechar-legenda pointer" style="position: absolute;right: 1rem;color: #595959;font-size: 1.5rem;" onclick="CB.setPrefUsuario('u', CB.modulo+'.legenda', 'N');"></i>`);
	});





	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>