<?
require_once("../inc/php/validaacesso.php");

if(empty($_GET["idlote"])){
	die("Idlote não enviado");
}


/*
 * Recupera recursivamente todos os lotes utilizados em produção, a partir de determinado lote informado
 */
function getEstruturaFormalizacao($inIdLote){
    $branch = array();

	/*
	 * Recupera informações do Lote
	 */
	$query = "select 
				l.idlote
				,l.partida
				,p.descr
				,p.codprodserv
				,l.qtdpedida
				,l.qtdpedida_exp
				,u.descr as unidade
				,p.fabricado
				,p.especial
				,l.idpessoa
				,pe.nome
				,s.idsolfab
				,concat(`s`.`idsolfab`,
							' - ',
							`lsf`.`partida`,
							'/',
							`lsf`.`exercicio`) as rotulosolfab
				,l.status
			from 
				lote l
				join prodserv p on p.idprodserv=l.idprodserv
				left join solfab s on s.idsolfab=l.idsolfab
				left join unidadevolume u on u.un=p.un
				left join pessoa pe on pe.idpessoa=l.idpessoa
				left join lote lsf ON lsf.idlote=s.idlote
			where l.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and l.idlote=".$inIdLote;

	$res = d::b()->query($query) or die('Erro getEstruturaFormalizacao: ' . mysqli_error(d::b()));

	$arrColunas = mysqli_fetch_fields($res);
	while($r = mysqli_fetch_assoc($res)){
		//Monta o objeto de Lote
		$aLote=array();
		foreach($arrColunas as $col){
			$aLote[$col->name]=$r[$col->name];
		}
		
		/*
		 * Recupera informações de Consumos desse lote e Atividades
		 */
		$queryc = "select 
						lc.idlotecons
						,lc.idlote
						,lc.qtdd
						,lc.qtdd_exp
						,lc.qtdc
						,lc.qtdc_exp
						,lc.qtdsaldo
						,lc.qtdsaldo_exp
						,la.idloteativ
						,la.idprativ
						,la.ativ
						,la.execucao
						,la.status as statusatividade
						,la.ord
					from lotecons lc
						left join loteativ la 
							on la.idloteativ=(
								case lc.tipoobjetoconsumoespec
								when 'formalizacao' then null -- Significa que o lote foi consumido diretamente no corpo da formalização, fora de qualquer atividade
								else lc.idobjetoconsumoespec
								end
							) 
							-- and la.idlote=386 -- Verificar necessidade futura de relacionamento, caso estejam retornando atividades incorretamente
					where lc.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
						and lc.idobjeto=".$r["idlote"]."
					order by la.ord";

		$resc = d::b()->query($queryc) or die('Erro 2 getEstruturaFormalizacao: ' . mysqli_error(d::b()));

		$arrColunasc = mysqli_fetch_fields($resc);
		$aLotec=array();
		while($rc = mysqli_fetch_assoc($resc)){
			//Monta o objeto de Consumo. Mostrar somente lotes que informaram no mínimo 0
			if($rc["qtdd"]<>""){
				foreach($arrColunasc as $col){
					$aLotec[$rc["ord"]][$rc["idloteativ"]][$rc["idlotecons"]][$col->name]=$rc[$col->name];
				}
			}
		}

		$branch[$r["idlote"]]=$aLote;
		$branch[$r["idlote"]]["consumosdolote"]=$aLotec;
	}
	
    return $branch;
}

$aLotes = getEstruturaFormalizacao($_GET["idlote"]);
print_r($aLotes);
die;

?>
<html>
<head>
	<title>Formalização #<?=$_GET["idlote"]?></title>
	<link href="../inc/css/report.css?_<?=date("dmYhms")?>" rel="stylesheet">
</head>
<body>
	<header class="row margem0.0">
		<div class="logosup col 15"><img src="../inc/img/impcab.png"></div>
		<div class="titulodoc"><label class="rot">OP: <?=$aLote["partida"]?> - </label> <?=$aProdserv["descr"]?></div>
		<div class="col 15"></div>
	</header>
	<div class="row">
		<div class="col 10 rot">Produto:</div>
		<div class="col 60"><?=$aProdserv["descr"]?></div>
		<div class="col 10 rot">Partida:</div>
		<div class="col 20"><?=$aLote["partida"]?></label></div>
	</div>
	<div class="row">
		<div class="col 10 rot">Qtd. solic.:</div>
		<div class="col 10"><?=$aLote["qtdpedida"]?> <?=traduzid("unidadevolume", "un", "descr", $aProdserv["un"])?></div>
		<div class="col 10 rot">Fabricação:</div>
		<div class="col 15">____/____/____</div>
		<div class="col 10 rot">Vencimento:</div>
		<div class="col 20">____/____/____</label></div>
	</div>
<?if(!empty($aLote["idsolfab"])){?>
	<div class="row">
		<div class="col 10 rot">Cliente:</div>
		<div class="col 50"><?=$aCliente["nome"]?></div>
		<div class="col 15 rot">Solicitação Fabr.:</div>
		<div class="col 25"><?=$aLote["idsolfab"]?><label class='rot'> / <?=dma($aSolfab["data"])?></label></div>
	</div>
<?}?>
	
<?
$arrInsumos=$JSON->decode($arvProdserv["jarvore"]);

//Produto em questão
while(list($pk, $pins) = each($arrInsumos)){
	while(list($ik, $iins) = each($pins->insumos)){
		print_r($iins);
	}
}

?>
</body>

</html>
