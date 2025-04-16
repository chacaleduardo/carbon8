	<?

	ini_set('memory_limit', '-1');
	require_once("../inc/php/validaacesso.php");
	//ini_set("display_errors",true);
	//error_reporting(E_ALL^E_NOTICE);
	require_once("../inc/php/laudo.php");
		require_once("reltesteinct2.php");
	if (!empty($_REQUEST['csv'])){
		$indesejaveis= array("-", "\"", ",", ";","
		");

	/*	$conteudoexport = ("
	Cliente;R. Social;Endereço;Registro;Data Registro;Amostra;Rejeitadas;Estudo;Descrição;Data Coleta;Lacre;Galpão/Aviário;Linha;Local Coleta;TC;Tipo;Data Fabricação;Responsável;Resp. Oficial;Sexo;N.Doses;Partida;Especificações;Fornecedor;Nota Fiscal;Vencimento;Semana;Identificação/Chip;Diluições;N. Placas;Fabricante;Sexadores;Local Específico;N. Clifor/Pedido;N. SVO;CPF/CNPJ;Cidade;Vol. Aplicado;N Doses;Via de Inoculação;Cor da Anilha;Registro Biotério;Granja;Tipo Aves;Espécie;Núcleo;Núcleo Origem;Cliente Terceiro;N. Reg. Oficial;Orgão Oficial;Lote;Idade;N. de Animais;Local;Observações;Amostra(s) testada(s);ID Teste;Resultado;Interpretação;Considerações"."\n");
		*/
		$conteudoexport = ("
	Cliente;Registro;Data Coleta;Data Registro;Qtd amostra;Tipo Amostra;	Descrição Amostra;Espécie;Granja;Núcleo;Galpão/Aviário;Lote;Idade;N. SVO;Responsável;Nome do Teste;Resultado;GMT/GMN"."\n");
	}

	$_token = $_GET["_token"]==''?$_GET["token"]:$_GET["_token"];

	//verifica se foi enviado o _token de autenticação
	if(!empty($_token)){	
		//desencripta o _token
		$str_token=des($_token);
		//verifica se deu certo a desencriptação
		if($str_token==false){	  
			die("CB-ERROR: Falha #2 ao autenticar _token");
		}else{
		/*
		* Passa a string da variavel $str_token para o array $arr_token
		*/
		   parse_str($str_token,$arr_token);
		   //print_r($arr_token);
		   /*
		* while list
		* o array contem chave mais valor 
		* O comando abaixo irá preencher os GETS com a chave = valor
		*/
		   while(list($chave,$valor)=each($arr_token)){
			   //echo $chave;
			   $_GET[$chave]=$valor;
			   //echo $_GET[$chave];
		   }
		}
	}

	/*
	 * PARAMETROS GET MODULO RESULTAVES (DIAGNÓSTICO > RESULTADOS) 
	 * 05/11/2018
	 */
	$idresultado		= $_GET['idresultado'];
	$idnucleo 			= $_GET['idnucleo'];
	$exercicio 			= $_GET['exercicio'];
	$idtipoteste 		= $_GET['idtipoteste'];
	$status 			= $_GET['status'];
	$idsubtipoamostra 	= $_GET['idsubtipoamostra'];
	$idunidade 			= $_GET['idunidade'];
	$flgoficial 		= $_GET['flgoficial'];
	$dataamostra_1      = $_GET['dataamostra_1'];
	$dataamostra_2      = $_GET['dataamostra_2'];

	/*
	 * PARAMETROS GET MODULO RESULTAVES (ARQUIVO report/imprimiresoficial.php) 
	 * 05/11/2018
	 */
	$controle			= $_GET['controle'];
	$chkoficial 		= $_GET['chkoficial'];
	$impoficial 		= $_GET['impoficial'];

	/*
	 * PARAMETRO GET ENVIA EMAIL OFICIAL SISTEMA ANTIGO ajax/enviaemailsecretaria.php 
	 * 10/12/2018
	 */
	$idcomunicacaoext=$_GET["idcomunicacaoext"];

	/*
	 * PARAMETRO GET ENVIA EMAIL OFICIAL SISTEMA NOVO, VIA ALERTA CONFIGURADO NO SISTEMA 
	 * 10/12/2018
	 */
	$idcontroleemissao=$_GET["idcontroleemissao"];


	/*
	 * PARAMETROS GET MODULO RESULTAVES (ARQUIVO form/bioensaio.php) 
	 * 05/11/2018
	 */
	$exerciciobiot		= $_GET['exerciciobiot'];
	$idregistrob		= $_GET['idregistrob'];

	/*
	 * PARAMETROS GET (INTRANET) 
	 * 05/11/2018
	 */
	$idpessoa			= $_GET['idpessoa'];
	$idregistro_1		= $_GET['idregistro_1'];
	$idregistro_2		= $_GET['idregistro_2'];
	$idsecretaria		= $_GET['idsecretaria'];
	$nome 				= $_GET["nome"];
	$mostraass 			= $_GET['mostraass'];
	$mostracabecalho 	= $_GET['mostracabecalho'];

	/*
	 * PARAMETROS GET (EXTRANET) 
	 * 05/11/2018
	 */
	$_vids				= $_GET['_vids'];

	$sql = false;


	$fromnf="";
	$straight_join="";


	$sqlp="select * from pessoa where idpessoa =".$_SESSION["SESSAO"]["IDPESSOA"];
	$resp = d::b()->query($sqlp) or die("Falha ao buscar dados do acesso: " . mysql_error() . "<p>SQL: $sqlp");
	$rowp=mysql_fetch_assoc($resp);

	if($rowp['status']!='ATIVO'){
		die('Usuário inativo no sistema.');
	}


	/*
	 * TRATAMENTO DOS PARAMETROS GET 
	 */

	if(!empty($idnucleo)){
			$sqlin .= " and a.idnucleo  = '" . $idnucleo ."' ";
	}

	if(!empty($idtipoteste)){
			$sqlin .= " and a.idtipoteste  = '" . $idtipoteste ."' ";
	}

	if(!empty($status)){
			$sqlin .= " and r.status  = '" . $status ."' ";
	}

	if(!empty($idsubtipoamostra)){
			$sqlin .= " and a.idsubtipoamostra  = '" . $idsubtipoamostra ."' ";
	}

	if(!empty($idunidade)){
			$sqlin .= " and a.idunidade  = '" . $idunidade ."' ";
	}
	if(!empty($flgoficial)){
		if($flgoficial=="Y"){
				$sqlin .= " and (r.idsecretaria is not null and r.idsecretaria != '')";
		}elseif ($flgoficial=="N"){
				$sqlin .= " and (r.idsecretaria is null or r.idsecretaria = '')";
		}
	}

	if(!empty($dataamostra_1) and !empty($dataamostra_2)){
		
		$dataini = validadate($dataamostra_1);
		$datafim = validadate($dataamostra_2);
		
		if ($dataini and $datafim){
			$sqlin .= " and (a.dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."') ";
		}else{
			die ("Datas n&atilde;o V&aacute;lidas!");
		}
	}

	if(!empty($idregistrob) and !empty($exerciciobiot)){
		$fromnf=$fromnf." bioensaio b,servicoensaio s,analise an, ";
			$sqlin .=" and b.idregistro =".$idregistrob." and b.exercicio='".$exerciciobiot."'  and b.idbioensaio =an.idobjeto and an.objeto='bioensaio'  and an.idanalise =s.idobjeto and s.tipoobjeto='analise' and s.idamostra = r.idamostra ";  
	}


	/*
	 * TRATAMENTO DOS PARAMETROS GET PARA CONCATENACAO POSTERIOR COM SQL
	 */
	if(!empty($controle)){
		// desta forma para dar index pelo nf
		$sqlin .= " and ni2.idresultado = r.idresultado
				and  nf2.idnotafiscal = ni2.idnotafiscal 
				and nf2.numerorps = '".$controle."'";
		
		$fromnf ="notafiscal nf2
				,notafiscalitens ni2
				,";
		
		$straight_join=" /*! STRAIGHT_JOIN */ ";
		
	}elseif(!empty($idresultado)){
		$sqlin .= " and r.idresultado =".$idresultado;	
		$straight_join=" /*! STRAIGHT_JOIN */ ";

	}elseif(!empty($exercicio) and !empty($idregistro)){
		
		$sqlin .=" and a.idregistro = '".$idregistro."'";
		$sqlin .=" and a.exercicio ='".$exercicio."'";

	}elseif(!empty($idregistro_1) and !empty($idregistro_2) and !empty($exercicio)){
		$sqlin .=" and a.idregistro BETWEEN ".$idregistro_1." AND ".$idregistro_2;
		$sqlin .=" and a.exercicio ='".$exercicio."'";	
		
	}elseif(!empty($exercicio)){
		$sqlin .=" and a.exercicio ='".$exercicio."'";
	}
	if(!empty($idcomunicacaoext)){
			$sqlin .=" and r.idresultado in (
						select coi.idobjeto from comunicacaoextitem coi,comunicacaoext co
						where co.status = 'SUCESSO'
						and co.idcomunicacaoext = coi.idcomunicacaoext 
						and coi.tipoobjeto = 'resultado'
						and coi.idcomunicacaoext = ".$idcomunicacaoext.") ";
	}

	if(!empty($idcontroleemissao)){
		$sqlin .=" and r.idresultado in (
						select coi.idobjeto from controleemissaoitem coi,controleemissao co
						where co.status IN ('PENDENTE','ENVIADO')
						and co.idcontroleemissao = coi.idcontroleemissao
						and coi.tipoobjeto = 'resultado'
						and coi.idcontroleemissao = ".$idcontroleemissao.") ";
	}


	/*
	 * quando for secretaria não colocar idpessoa, 
	 * porque no token e informado o idpessoa da secretaria então não pode usar ele para buscar no idpessoa da amostra,
	 * este idpessoa e informado na pesquisa a selecionar um cliente para busca
	 */ 
	if(!empty($idpessoa) and $rowp['idtipopessoa']!='10'  and $rowp['idtipopessoa']!='4'){
		$sqlin .=" and a.idpessoa = ".$idpessoa." ";
	}

	if(!empty($idpessoa) and $rowp['idtipopessoa']=='4'){
		$sqlin .=" and r.idsecretaria = ".$idpessoa." ";
	}

	if (!empty($nome)){
		$sqlin .= " and p.nome like ('%".$nome."%')  ";
	}
	if (!empty($mostraass)){
		if ($mostraass == 'Y'){
			$mostraass=true;
		} else{
			$mostraass=false;
		}
	}else{
		$mostraass=true;
	}

	if(!empty($_vids)){
		$sqlin .= " and r.idresultado in (" . mysql_real_escape_string($_vids) .")";
	}

	if(empty($sqlin)){
		die ("Parâmentros necessarios não informados para a consulta.");
	}


	//verifica se o contato e do cliente para buscar o resultado em casos onde o acesso esta sendo realizado por um contato com um token
	//(garantir que o usuario não veja resultados de outros clientes)
	if($rowp["idtipopessoa"]==3 or $rowp["idtipopessoa"]==12){
		$sqlin.=" and exists (select 1 from pessoacontato c where c.idpessoa = a.idpessoa and c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"].") and r.status = 'ASSINADO' ";

	}elseif($rowp["idtipopessoa"]==4){// e um contato oficial
		
		$sqlin.=" and exists (select 1 from pessoacontato c where c.idpessoa = r.idsecretaria and c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"].")  and r.status = 'ASSINADO'  ";

	}elseif(($chkoficial=="S" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N')or($impoficial=="Y")){	//$impoficial vem da impressão de res oficial
			$sqlin .=" and r.status = 'ASSINADO'		
					and r.idsecretaria != ''
					and not exists(
						select 1
						from controleimpressaoitem c 
						where c.idresultado = r.idresultado 
							and c.oficial = 'S' 
							and c.via >= 1 and c.status = 'ATIVO'
					) ";	
	}elseif($chkoficial=="N" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N'){
		$sqlin .=" and r.status = 'ASSINADO'	
					and not exists(
					select 1
					from controleimpressaoitem c 
					where c.idresultado = r.idresultado 
						and c.oficial = 'N' 
						and c.via >= 1 and c.status = 'ATIVO'
				) ";	
	}elseif($chkoficial=="S"){
		$sqlin .=" and r.idsecretaria != '' ";
	}

	if($impoficial=="Y"){//$impoficial vem da impressão de res oficial
		$sqlin .=" and not exists(
							select 1 from resultado rr  USE INDEX (idamostra_status) ,amostra aa
							where rr.idamostra = aa.idamostra 
							and rr.idsecretaria != ''
							and rr.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO')
							and aa.idnucleo = a.idnucleo
													and aa.dataamostra < DATE_ADD(a.dataamostra, INTERVAL 4 DAY)
							) ";

		$chkoficial="S";
	}

	// $sql = " select /*! STRAIGHT_JOIN */ * from vwreltesteassinado  where idamostra in(". $sqlin .") ".$sqloficial." order by idpessoa, exercicio, idregistro , tipoteste";

	 $sql ="select ".$straight_join."
			r.idresultado
			,rj.jresultado
					,p.resvincnf
					 ,cp.status as statuspgto
		from 
			
				(
					(".$fromnf."
					resultado r 
					,resultadojson rj
					,amostra a
					,pessoa p
					
					,subtipoamostra st
					,vwtipoteste tt) 
						left join nucleo n on (n.idnucleo = a.idnucleo))
					left join especiefinalidade ef on ef.idespeciefinalidade=a.idespeciefinalidade
									LEFT JOIN `notafiscalitens` `nfi` ON `nfi`.`idresultado` = `r`.`idresultado`
									LEFT JOIN `notafiscal` `nf` ON `nf`.`idnotafiscal` = `nfi`.`idnotafiscal`
									LEFT JOIN `contapagar` `cp` ON `cp`.idobjeto = `nf`.`idnotafiscal` and `cp`.tipoobjeto = 'notafiscal'
		where r.idamostra = a.idamostra
			and rj.idresultado=r.idresultado
			
			and p.idpessoa = a.idpessoa

			and st.idsubtipoamostra = a.idsubtipoamostra
			and tt.idtipoteste = r.idtipoteste
			-- sqlin ini
			".$sqlin."
			-- sqlin fim
			order by a.idpessoa, a.exercicio, a.idregistro  , tt.tipoteste";

	//if($echosql){
		echo "<!-- " . $sql . "  -->";
	//}

//die($sql);

	$res = mysql_query($sql) or die("Falha no Relatório de Testes: " . mysql_error() . "<p>SQL: $sql");
	$qtd = mysql_num_rows($res);
	if (empty($qtd)){
	?>
	<link rel="stylesheet" href="../inc/css/alerttemporario.css" />
	<br>
	<div class="col-md-6">
		<div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

		<strong>Este teste está <b>em análise</b>.
		<br/>
		<br/>Em caso de dúvida, entre em contato conosco:
		<br/>Email: resultados@laudolab.com.br
		<br/>Telefone: (34) 3222 5700 / Whatsapp: (34) 9 9130-1330
		</div>
	</div>
	<?
	}

	//buscar os resultados agrupar por TC e tipoamostra para montar a ultima folha da impressão com os dados dos resultado que foram impressos
	if($impoficial=="Y"){
		// na impressão de resultado oficial deve-se buscar as informações antes de inserir a informação dos resultados ja impressos
		$sqlf1 ="select ".$straight_join."
			a.idempresa
			,a.idunidade
			,p.nome
			,a.idregistro
			,a.idamostra
			,a.exercicio
			,a.idpessoa
			,a.idtipoamostra
			,a.idsubtipoamostra
			,a.dataamostra
			,a.nroamostra
			,a.rejeitada
			,a.origem
			,a.lote
			,a.idade
			,a.tipoidade
			,a.observacao
			,a.descricao
			,a.lacre
			,a.galpao
			,a.linha
			,a.responsavelof
			,a.responsavel
			,a.tipo
			,a.nroplacas
			,a.diluicoes
			,a.tipoaves
			,a.identificacaochip
			,a.sexo
			,a.datafabricacao
			,a.partida
			,a.nrodoses
			,a.especificacao
			,a.fornecedor
			,a.localcoleta
			,a.localexp
			,a.meiotransp
			,a.datacoleta
			,a.tc
			,a.semana
			,a.notafiscal
			,a.vencimento
			,a.fabricante
			,a.sexadores
			,a.criadopor
			,a.criadoem
			,a.alteradopor
			,a.alteradoem
			,a.estexterno
			,a.clienteterceiro
			,a.nucleoorigem
			,a.idwfxprocativ
			,r.idservicoensaio
			,r.gmt
			,r.padrao
			,tt.tipoteste
			,tt.sigla
			,tt.tipogmt
			,tt.tipoespecial
			,tt.geralegenda
			,date_format(a.dataamostra,'%d/%m/%Y') AS dataamostraformatada
			,r.descritivo
			,r.gmt
			,r.idresultado
			,r.idt
			,r.idtipoteste
			,r.padrao
			,r.q1
			,r.q10
			,r.q11
			,r.q12
			,r.q13
			,r.q2
			,r.q3
			,r.q4
			,r.q5
			,r.q6
			,r.q7
			,r.q8
			,r.q9
			,r.status
			,r.var
			,tt.geragraf
			,tt.geracalc
			,st.subtipoamostra
			,r.quantidade as quantidadeteste
			,tt.textopadrao
			,tt.tipobact
			,r.idsecretaria
			,a.granja
			,a.unidadeepidemiologica
			,n.idnucleo
			,n.nucleo
			,a.regoficial
			,a.nsvo
			,a.nucleoamostra
			,ef.finalidade
			,r.interfrase
			,tt.logoinmetro
			,ef.idespeciefinalidade
			,concat(pl.plantel,'-',ef.finalidade) as especiefinalidade
			,rj.jresultado
			,r.versao
			,r.alerta
					,p.resvincnf
					,cp.status as statuspgto
					
		from
		
				(
					(".$fromnf."
					resultado r
					,resultadojson rj
					,amostra a
					,pessoa p
					,subtipoamostra st
					,vwtipoteste tt)
					left join nucleo n on (n.idnucleo = a.idnucleo))
					left join especiefinalidade ef on (ef.idespeciefinalidade=a.idespeciefinalidade)
									left join plantel pl on(pl.idplantel=ef.idplantel)
									LEFT JOIN `notafiscalitens` `nfi` ON `nfi`.`idresultado` = `r`.`idresultado`
									LEFT JOIN `notafiscal` `nf` ON `nf`.`idnotafiscal` = `nfi`.`idnotafiscal`
									LEFT JOIN `contapagar` `cp` ON `cp`.idobjeto = `nf`.`idnotafiscal` and `cp`.tipoobjeto = 'notafiscal'
									
		where r.idamostra = a.idamostra
			and rj.idresultado=r.idresultado
			-- and r.status = 'ASSINADO'
			and p.idpessoa = a.idpessoa
			and st.idsubtipoamostra = a.idsubtipoamostra
			and tt.idtipoteste = r.idtipoteste
			".$sqlin."
			group by a.tc
			order by a.idpessoa, a.exercicio, a.tc, a.idregistro ";
		$resf1 = mysql_query($sqlf1) or die("Falha no Relatório de Testes oficiais: " . mysql_error() . "<p>SQL: $sql");
		
	}

	/*
	 * PARAMETROS GERAIS
	 */
	$codepress = md5(date('dmYHis')); //gera um codigo para a impressao

	?>
	<html>
	<head>
	<title>Resultado - Impresso [<?=$codepress;?>]</title>

		<link href="../inc/css/emissaoresultadot.css?<?=rand(0,999);?>" rel="stylesheet" type="text/css" />

	<style>
	html{
		margin: 0px;
		padding: 0px;
	}
	body{
		margin: 0px;
		padding: 0px;
	}

	</style>

	</head>
	<body>
	<?

	$pb = ""; //controla a quebra de paginas
	//$irestotal = mysql_num_rows($res); //armazena o total de paginas

	//se for funcionario e não visualizar resultado, a solicitação não vier da impressão de oficiais controla a impressão
	if($rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N' and empty($impoficial)and $controle){
		controleimpressao($controle,$chkoficial);
	}
	$c = 0;
	global $pb;
	while($resultado = mysql_fetch_array($res)){
	$c++;


		//Recupera os dados congelados no resultado, para serem apresentados na tela juntamente à versão gerada após STATUS=ASSINADO
		$rc= unserialize(base64_decode($resultado["jresultado"]));

		if(empty($rc)){
			echo "Teste não possui informação de resultado: [".$resultado["idresultado"]."]";
		}
		

		//Abre um $row com os dados da coluna jresultado
		$row["idempresa"]		=$rc["amostra"]["res"]["idempresa"];
		$row["idunidade"]		=$rc["amostra"]["res"]["idunidade"];
		$row["idregistro"]		=$rc["amostra"]["res"]["idregistro"];
		$row["idamostra"]		=$rc["amostra"]["res"]["idamostra"];
		$row["exercicio"]		=$rc["amostra"]["res"]["exercicio"];
		$row["idpessoa"]		=$rc["amostra"]["res"]["idpessoa"];
		$row["idtipoamostra"]	=$rc["amostra"]["res"]["idtipoamostra"];
		$row["idsubtipoamostra"]=$rc["amostra"]["res"]["idsubtipoamostra"];
		$row["datacoleta"]		=dma($rc["amostra"]["res"]["datacoleta"]);
		$row["meiotransp"]		=$rc["amostra"]["res"]["meiotransp"];
		$row["nroamostra"]		=$rc["amostra"]["res"]["nroamostra"];
		$row["rejeitada"]		=$rc["amostra"]["res"]["rejeitada"];
		$row["origem"]			=$rc["amostra"]["res"]["origem"];
		$row["lote"]			=$rc["amostra"]["res"]["lote"];
		$row["idade"]			=$rc["amostra"]["res"]["idade"];
		$row["tipoidade"]		=$rc["amostra"]["res"]["tipoidade"];
		$row["observacao"]		=$rc["amostra"]["res"]["observacao"];
		$row["descricao"]		=$rc["amostra"]["res"]["descricao"];
		$row["lacre"]			=$rc["amostra"]["res"]["lacre"];
		$row["galpao"]			=$rc["amostra"]["res"]["galpao"];
		$row["alojamento"]		=$rc["amostra"]["res"]["alojamento"];
		$row["numgalpoes"]			=$rc["amostra"]["res"]["numgalpoes"];
		$row["linha"]			=$rc["amostra"]["res"]["linha"];
		$row["responsavelof"]	=$rc["amostra"]["res"]["responsavelof"];
		$row["responsavel"]		=$rc["amostra"]["res"]["responsavel"];
		$row["tipo"]			=$rc["amostra"]["res"]["tipo"];
		$row["nroplacas"]		=$rc["amostra"]["res"]["nroplacas"];
		$row["diluicoes"]		=$rc["amostra"]["res"]["diluicoes"];
		$row["tipoaves"]		=$rc["amostra"]["res"]["tipoaves"];
		$row["identificacaochip"]=$rc["amostra"]["res"]["identificacaochip"];
		$row["sexo"]			=$rc["amostra"]["res"]["sexo"];
		$row["datafabricacao"]	=$rc["amostra"]["res"]["datafabricacao"];
		$row["partida"]			=$rc["amostra"]["res"]["partida"];
		$row["nrodoses"]		=$rc["amostra"]["res"]["nrodoses"];
		$row["especificacao"]	=$rc["amostra"]["res"]["especificacao"];
		$row["fornecedor"]		=$rc["amostra"]["res"]["fornecedor"];
		$row["localcoleta"]		=$rc["amostra"]["res"]["localcoleta"];
		$row["localexp"]		=$rc["amostra"]["res"]["localexp"];
		$row["tc"]				=$rc["amostra"]["res"]["tc"];
		$row["semana"]			=$rc["amostra"]["res"]["semana"];
		$row["notafiscal"]		=$rc["amostra"]["res"]["notafiscal"];
		$row["vencimento"]		=$rc["amostra"]["res"]["vencimento"];
		$row["fabricante"]		=$rc["amostra"]["res"]["fabricante"];
		$row["sexadores"]		=$rc["amostra"]["res"]["sexadores"];
		$row["cpfcnpjprod"]		=$rc["amostra"]["res"]["cpfcnpjprod"];
		$row["uf"]				=$rc["amostra"]["res"]["uf"];
		$row["cidade"]			=$rc["amostra"]["res"]["cidade"];
		$row["pedido"]			=$rc["amostra"]["res"]["pedido"];
		$row["criadopor"]		=$rc["amostra"]["res"]["criadopor"];
		$row["criadoem"]		=$rc["amostra"]["res"]["criadoem"];
		$row["alteradopor"]		=$rc["amostra"]["res"]["alteradopor"];
		$row["alteradoem"]		=$rc["amostra"]["res"]["alteradoem"];
		$row["estexterno"]		=$rc["amostra"]["res"]["estexterno"];
		$row["clienteterceiro"]	=$rc["amostra"]["res"]["clienteterceiro"];
		$row["nucleoorigem"]	=$rc["amostra"]["res"]["nucleoorigem"];
		$row["idwfxprocativ"]	=$rc["amostra"]["res"]["idwfxprocativ"];
		$row["dataamostra"]		=$rc["amostra"]["res"]["dataamostra"];
		$row["dataamostraformatada"]=dma($rc["amostra"]["res"]["dataamostra"]);
		$row["granja"]			=$rc["amostra"]["res"]["granja"];
		$row["unidadeepidemiologica"] =$rc["amostra"]["res"]["unidadeepidemiologica"];
		$row["nsvo"]			=$rc["amostra"]["res"]["nsvo"];
		$row["nucleoamostra"]	=$rc["amostra"]["res"]["nucleoamostra"];
		$row["idespeciefinalidade"]=$rc["especiefinalidade"]["res"]["idespeciefinalidade"];
		$row["especiefinalidade"]=$rc["especiefinalidade"]["res"]["especiefinalidade"];
		$row["finalidade"]		=$rc["especiefinalidade"]["res"]["finalidade"];
		$row["idnucleo"]		=$rc["nucleo"]["res"]["idnucleo"];
		$row["nucleo"]			=$rc["nucleo"]["res"]["nucleo"];
		$row["regoficial"]		=$rc["amostra"]["res"]["regoficial"];
		$row["quantidadeteste"] =$rc["resultado"]["res"]["quantidade"];
		$row["nome"]			=$rc["pessoa"]["res"]["nome"];
		$row["razaosocial"]		=$rc["pessoa"]["res"]["razaosocial"];
		$row["idservicoensaio"]	=$rc["resultado"]["res"]["idservicoensaio"];
		$row["gmt"]				=$rc["resultado"]["res"]["gmt"];
		$row["padrao"]			=$rc["resultado"]["res"]["padrao"];
		$row["descritivo"]		=$rc["resultado"]["res"]["descritivo"];
		$row["idresultado"]		=$rc["resultado"]["res"]["idresultado"];
		$row["idt"]				=$rc["resultado"]["res"]["idt"];
		$row["idtipoteste"]		=$rc["resultado"]["res"]["idtipoteste"];
		$row["padrao"]			=$rc["resultado"]["res"]["padrao"];
		$row["q1"]				=$rc["resultado"]["res"]["q1"];
		$row["q10"]				=$rc["resultado"]["res"]["q10"];
		$row["q11"]				=$rc["resultado"]["res"]["q11"];
		$row["q12"]				=$rc["resultado"]["res"]["q12"];
		$row["q13"]				=$rc["resultado"]["res"]["q13"];
		$row["q2"]				=$rc["resultado"]["res"]["q2"];
		$row["q3"]				=$rc["resultado"]["res"]["q3"];
		$row["q4"]				=$rc["resultado"]["res"]["q4"];
		$row["q5"]				=$rc["resultado"]["res"]["q5"];
		$row["q6"]				=$rc["resultado"]["res"]["q6"];
		$row["q7"]				=$rc["resultado"]["res"]["q7"];
		$row["q8"]				=$rc["resultado"]["res"]["q8"];
		$row["q9"]				=$rc["resultado"]["res"]["q9"];
		$row["status"]			=$rc["resultado"]["res"]["status"];
		$row["var"]				=$rc["resultado"]["res"]["var"];
		$row["idsecretaria"]	=$rc["resultado"]["res"]["idsecretaria"];
		$row["interfrase"]		=$rc["resultado"]["res"]["interfrase"];
		$row["versao"]          =$rc["resultado"]["res"]["versao"];
		$row["alerta"]          =$rc["resultado"]["res"]["alerta"];
		$row["jsonresultado"]   =$rc["resultado"]["res"]["jsonresultado"];
		$row["jsonconfig"]      =$rc["resultado"]["res"]["jsonconfig"];
		$row["subtipoamostra"]	=$rc["subtipoamostra"]["res"]["subtipoamostra"];
		$row["normativa"]		=$rc["subtipoamostra"]["res"]["normativa"];
		$row["tipoamostra"]		=$rc["tipoamostra"]["res"]["tipoamostra"];
		$row["tipoamostraformatado"]=($row["tipoamostra"]==$row["subtipoamostra"])?$row["tipoamostra"]:$row["tipoamostra"]." Subtipo:".$row["subtipoamostra"];
		$row["tipoteste"]		=$rc["prodserv"]["res"]["tipoteste"];
		$row["sigla"]			=$rc["prodserv"]["res"]["sigla"];
		$row["tipoespecial"]	=$rc["prodserv"]["res"]["tipoespecial"];
		$row["geralegenda"]		=$rc["prodserv"]["res"]["geralegenda"];
		$row["geragraf"]		=$rc["prodserv"]["res"]["geragraf"];
		$row["geracalc"]		=$rc["prodserv"]["res"]["geracalc"];
		$row["textopadrao"]		=$rc["prodserv"]["res"]["textopadrao"];
		$row["textointerpretacao"]		=$rc["prodserv"]["res"]["textointerpretacao"];
		$row["tipobact"]		=$rc["prodserv"]["res"]["tipobact"];
		$row["logoinmetro"]		=$rc["prodserv"]["res"]["logoinmetro"];
		$row["modo"]			=$rc["prodserv"]["res"]["modo"];
		$row["modelo"]			=$rc["prodserv"]["res"]["modelo"];
		$row["tipogmt"]			=$rc["prodserv"]["res"]["tipogmt"];
		$row["comparativodelotes"]=$rc["prodserv"]["res"]["comparativodelotes"];
		$arrprodservtipoopcao 	=$rc["prodservtipoopcao"]["res"];
		$arrprodservtipoopcaoespecie =$rc["prodservtipoopcaoespecie"]["res"];
		$arrlotecons 			=$rc["lotecons"]["res"];
		
		$rowbio					=$rc["bioensaio"]["res"];
		$rowend					=$rc["endereco"]["res"];
		$rtitulos				=$rc["titulos"]["res"];
		$arrgrafgmt				=$rc["hist_gmt"]["res"];
		$arrelisa				=$rc["resultadoelisa"]["res"];
		$arrelisagr1			=$rc["resultadoelisa_graf1"]["res"];
		$arrelisagr2			=$rc["resultadoelisa_graf2"]["res"];
		$arrassinat				=$rc["resultadoassinatura"]["res"];
			
		$row['resvincnf']                        =$resultado["resvincnf"];
		$row['statuspgto']			=$resultado["statuspgto"];
		
		$sqlp = "select alertarotuloy, alertarotulon, alertarotulo from prodserv
						where
							idprodserv = '".$rc["resultado"]["res"]["idtipoteste"]."'";
			$resp=mysql_query($sqlp) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
			$x = 0;
			while($linhap=mysql_fetch_assoc($resp)){
				$alertarotuloy			= $linhap['alertarotuloy'];
				$alertarotulon			= $linhap['alertarotulon'];
				$alertarotulo			= $linhap['alertarotulo'];
				
			}
			
			
		
		if ($row["alerta"] == 'Y'){
			$alertarotulo          =$alertarotuloy;
		}else{
			$alertarotulo          =$alertarotulon;
		}
		
		
		//$row["dataconclusao"]	=$rc["_auditoria"]["res"]["dataconclusao"];
		if (!empty($rc["resultado"]["res"]["dataconclusao"])){
			
		$row["dataconclusao"]	= $rc["resultado"]["res"]["dataconclusao"];
		
		$row["dataconclusao"]	= date("d/m/Y", strtotime($row["dataconclusao"]));
		
		
		}else{
			$sqla = "select DATE_FORMAT(dataconclusao, '%d/%m/%Y') dataconclusao from resultado
						where
							idresultado = '".$row["idresultado"]."'";
			$resa=mysql_query($sqla) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
			$x = 0;
			while($linhaa=mysql_fetch_assoc($resa)){
				$row["dataconclusao"]			= $linhaa['dataconclusao'];
				
			}
		}
		
		 echo '<!-- cunha'.$row["dataconclusao"].'-->';
		

		foreach($arrassinat as $i => $rowass) {
			$row['dataassinatura'] = $rowass["criadoem"];
		}
		
		//Se for impressao oficial for funcionario e não vizualizar resultado controla a impressão
		if($impoficial=="Y" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N'){
			controleimpressaooficial($row['idregistro'],$row['exercicio']);
		}
		
		$sqlc = 
		"select 
			modelo,
			modo,
			comparativodelotes,
			tipogmt
		from 
			prodserv p 
		where 
			idprodserv = '".$row["idtipoteste"]."';";
			
		$resc = mysql_query($sqlc);
		
		while($linha=mysql_fetch_assoc($resc)){
			$modelo 			= $linha['modelo'];
			$modo 				= $linha['modo'];
			$comparativodelotes	= $linha['comparativodelotes'];
			$tipogmt 			= $linha['tipogmt'];
			$grafico 			= $linha['grafico'];
		}

		//Se for uma amostar com idunidade = 4 trata-se de uma amostra do bioterio
		/*
		if($row['idunidade']==4 and !empty($row['idservicoensaio'])){
			//Buscar a idade para o bioterio
			$sqlb="SELECT (DATEDIFF(s.data,ss.data)) AS idade,if(s.dia is null,'',concat('D',s.dia)) as rotulo,dma(s.data) as dmadata
				from servicoensaio ss,servicoensaio s
				where ss.servico = 'TRANSFERENCIA'
				and	ss.idbioensaio = s.idbioensaio
				and s.idservicoensaio =".$row['idservicoensaio'];
			$resb=mysql_query($sqlb) or die("Erro ao buscar a idade para o biotério sql".$sqlb);
			$rowb=mysql_fetch_assoc($resb);
			if(empty($rowb['rotulo'])){
				$row['idade']=$rowb['idade'];
				$row['dataamostraformatada']=$rowb['dmadata'];
			}else{
				$row['idade']=$rowb['rotulo'];
				$row["tipoidade"]="";
				$row['dataamostraformatada']=$rowb['dmadata'];
			}
			//$row["nroamostra"]=$row['quantidadeteste'];
			//echo($sqlb);
		}
		*/
			
			if (empty($_REQUEST['csv'])){

				// inserir informações do lote no resultado
				if(!empty($row['idwfxprocativ'])){		
						$row['partida']=		$rc["vwpartidaamostra"]["res"]["partidaext"];
						$row['nucleoamostra']=	$rc["vwpartidaamostra"]["res"]["partida"];
						$row['lote']=			$rc["vwpartidaamostra"]["res"]["partidaext"];
				}



				$ipage = 0; //controla o numero dap agina atual
				$irestotal = 1;
					if ($c > 1){
						$pb = 'page-break-before: always;';
					}
				  
					if ($row["resvincnf"] == 'S' and $row["statuspgto"] == 'PENDENTE' and $row["status"] == 'ASSINADO' and  $_SESSION["SESSAO"]["IDTIPOPESSOA"]!=1 ){
					echo '<table style="width:700px !important; margin:auto; margin-bottom:4px; '.$pb.' ">
							<tr>
								<td class="tdval grval" style="font-size:11px !important;background:#666;color:#fff !important;padding:8px;">Prezado cliente,<br><Br>
									Há uma pendência no registro <b>'.$row["idresultado"].'</b> que impossibilita sua visualização/impressão. <Br>
									Favor entrar em contato com o setor administrativo através do (34) 3222-5700 ou vendas@laudolab.com.br. <Br><br>Obrigado!
							</tr>
						</table>';
					}else{

				/*
				 * As condicoes abaixo invocam as funcoes para montagem das emissoes conforme o tipo especial
				 */

						imppaginisup();//inicia o controle da impressao: paginacao e controle encriptado
						cabecalhores();//monta o cabeçalho
						relresultado($mostraass, 1);
						imppagrodape(1);//finaliza a pagina

					}

				//se for funcionario e não visualizar resultado e a solicitação não vier da impressão oficial controla a impressão
				if($rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N' and empty($impoficial)){
						controleimpressaoitem($controle,$chkoficial,$row["idresultado"]);
				}
				//se vier da impressão dos oficiais for funcionario e não visualizar resultado controla a impressão
				if($impoficial=="Y" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N'){
						controleimpressaoitemoficial($row['idregistro'],$row["idresultado"],$row["exercicio"]);
				}


				/* * INDICACAO DE VISUALIZACAO DO RESULTADO */ 				
				$sqlvis = "insert into resultadovisualizacao (idresultado, idpessoa,criadoem,email) 
				values (".$row["idresultado"].",".$_SESSION["SESSAO"]["IDPESSOA"].",now(),'".$_SESSION["SESSAO"]["EMAIL"]."')"; 
				$resvis = mysql_query($sqlvis); 
				if(!$resvis){
						if($echosql){
								echo "\n<!-- Resultado Visualização: ".mysqli_error(d::b())."\nSql:".$sqlvis." -->";
						} 
				} 
			
			}else{
				$endereco = $rowend["logradouro"];
				if($rowend['numero']){$endereco .= "N&ordm;:".$rowend['numero'];}
				if($rowend['complemento']){$endereco .= ", ".$rowend["complemento"];}
				if($rowend['bairro']){$endereco .=(", BAIRRO: ". ($rowend['bairro']));}
				if($rowend['cep']){$endereco .=(", CEP:". formatarCEP($rowend['cep'],true) );}
				if($rowend['cidade']){$endereco .=(", ".($rowend['cidade']));}
				if($rowend['uf']){$endereco .=("-".$rowend['uf']);}
				$endereco = str_replace(';',' - ',$endereco);
				$endereco = str_replace(':',' - ',$endereco);
				$endereco = str_replace(',',' - ',$endereco);
				
				if ($row['nroamostra']){
					$nroamostra = str_replace(';', '',$row['nroamostra']);
				}

				if ($row['rejeitada']){
					$rejeitada = $row["rejeitada"];
				}

				if($row["meiotransp"]){
					$meiotransp = $row["meiotransp"];
				}
				
				if($row["datacoleta"]){
					$datacoleta = $row["datacoleta"];
				}elseif($row["normativa"]){
					$datacoleta = 'Não Informado';
				}
				
				if($row["lacre"]){
					$lacre = $row["lacre"];
				}elseif($row["normativa"]){
					$lacre = 'Não Informado';
				}
				if($row["galpao"]){
					$galpao = $row["galpao"];
				}elseif($row["normativa"]){
					$galpao = 'Não Informado';
				}
				if($row["tc"]){
					$tc = $row["tc"];
				}elseif($row["normativa"]){
					$tc = 'Não Informado';
				}
				if($row["nsvo"]){
					$nsvo = $row["nsvo"];
				}elseif($row["normativa"]){
					$nsvo = 'Não Informado';
				}
				if($row["cpfcnpjprod"]){
					$cpfcnpjprod = formatarCPF_CNPJ($row["cpfcnpjprod"],true);
				}elseif($row["normativa"]){
					$cpfcnpjprod = 'Não Informado';
				}
				if($row["cidade"]){
					$cidade = $row["cidade"]."-".$row["uf"];
				}elseif($row["normativa"]){
					$cidade = 'Não Informado';
				}
				if(!empty($rowbio["volume"]) and !empty($row['idservicoensaio'])){
					$volume = $rowbio["volume"];
				}else{
					$volume = '';
				}
				if(!empty($rowbio["doses"]) and !empty($row['idservicoensaio'])){
					$doses = $rowbio["doses"];
				}else{
					$doses = '';
				}
				if(!empty($rowbio["via"]) and !empty($row['idservicoensaio'])){
					$via = $rowbio["via"];
				}else{
					$via = '';
				}
				if(!empty($rowbio["coranilha"]) and !empty($row['idservicoensaio'])){
					$coranilha = $rowbio["coranilha"];
				}else{
					$coranilha = '';
				}
				if(!empty($rowbio['idregistro']) and !empty($row['idservicoensaio'])){
					$bidregistro= 'B'.$rowbio['idregistro'];
				}else{
					$bidregistro= '';
				}                     
				if($row["granja"]){
					$granja = $row["granja"];
				}elseif($row["normativa"]){
					$granja = 'Não Informado';
				}
				if($row["unidadeepidemiologica"]){
					$unidadeepidemiologica = $row["unidadeepidemiologica"];
				} 				
				if($row["nucleoorigem"]){
					$nucleoorigem = $row["nucleoorigem"];
				}elseif($row["normativa"]){
					$nucleoorigem = 'Não Informado';
				} 
				
				$idsecretaria = traduzid("pessoa","idpessoa","nome",$row["idsecretaria"]);
				
				if($row["idade"]){
					$idade = $row["idade"]." ".$row["tipoidade"];
				}elseif($row["normativa"]){
					$idade = 'Não Informado';
				}
				
				if(!empty($rowbio['qtd']) and !empty($row['idservicoensaio']) and !empty($row["quantidadeteste"])){
					$quantidadeteste = $row["quantidadeteste"];
				}else{
					$quantidadeteste= '';
				}  
				if(!empty($rowbio['rot']) and !empty($rowbio['gaiola'])){
					$rot = $rowbio['rot']." - GAIOLA".$rowbio['gaiola'];
				}else{
					$rot = '';
				}
				
				 $resultado = str_replace("&nbsp;", "", $row["descritivo"]);
				
				$_idresultado = $row["idresultado"];
				if($versao>0){
					$_idresultado = $_idresultado.'.'.$versao;
				}
				
			   
			   
		  
			   if ($modo == 'IND'){
				 
				$sqlind="
					   select 
							   ri.identificacao,
							   ri.resultado
					   from 
							   resultadoindividual ri
					   join
							   resultado r on r.idresultado = ri.idresultado
					   where 
							   ri.idresultado = ".$row['idresultado']." 
					   order 
							   by ri.idresultadoindividual";

				$resind=mysql_query($sqlind) or die("Erro ao buscar identificação e resultados dos orificios do teste do bioensaio sql".$sqlind);
				$y=1;

				$total = mysql_num_rows($resind);
				while($rowind=mysql_fetch_assoc($resind)){
				  

					   if(!empty($rowind['identificacao'])){

							   if($tipogmt == 'GMT'){
									   $resultado .= "Ave ".$rowind['identificacao']." apresentou título ".$strind[$rowind['resultado']].". ";
							   }elseif($tipogmt == 'ART' ){
									   $resultado .= "Ave ".$rowind['identificacao']." pesou ".$rowind['resultado']." (GR). ";
							   }else{
									   $resultado .= "Amostra ".$rowind['identificacao']." apresentou resultado ".$rowind['resultado'].". ";

							   }


					   }else{
							   $resultado .= "Amostra ".$rowind['identificacao']." apresentou resultado ".$rowind['resultado'].". ";
					   }															
					   $y++;														
				}
				if($tipogmt == "GMT"){
					   $resultado .= "Média Geométrica dos títulos: ".$row["gmt"];
				}else if($tipogmt == "ART"){
					   $resultado .= "Média Aritmética das pesagens: ".$row["gmt"];
				}

			   
				}else if ($modo == 'AGRUP'){

				for ($i = 1; $i <= 13; $i++) {//roda nos 13 orificios

					   //se o oficio foi marcado alguma vez
					   if($row["q".$i] > 0){


							   if($row["q".$i]>1){
									   $resultado .= "".$row["q".$i]." Amostras apresentaram título ".$rot[$i].". ";

							   }else{
									   $resultado .= "".$row["q".$i]." Amostra apresentou título ".$rot[$i].". ";
							   }


							   $y++;      	
					   }
				}
				if($tipogmt == "GMT"){
					   $resultado .= "Média Geométrica dos títulos: ".$row["gmt"].". ";
				}else if($tipogmt == "ART"){
					   $resultado .= "Média Aritmética das pesagens: ".$row["gmt"].". ";
				}
				}
				
				if(!empty($row["idade"]) and !empty($row["tipoidade"])){
					$templateinterpretacao .= "* Para inserção da interpretação não foram considerados registros posteriores a ".$row["idade"]." ".$row["tipoidade"];
				}
				
				$x =0;
				$consideracoes = '';
				//print_r($arrlotecons);
				foreach ($arrlotecons as $i => $linhai) {
				 
					$ins_nomepartida[$x]                            = $linhai['descr'];
					$ins_fabricante[$x]				= $linhai['fabricante'];
					$ins_partidaext[$x]				= $linhai['partidaext'];
					$ins_fabricacao[$x]				= $linhai['fabricacao'];
					$ins_vencimento[$x]				= $linhai['vencimento'];
				
				}
			   
				//die('');        
				while ($x < count($ins_partidaext)){

					
					$consideracoes .= 'PARTIDA DE '.$ins_nomepartida[$x].'<br>';
					$consideracoes .= 'FABRICANTE: '.$ins_fabricante[$x].'<br>';
					$consideracoes .= 'PARTIDA: '.$ins_partidaext[$x].'<br>';
					$consideracoes .= 'FABRICAÇÃO: '.$ins_fabricacao[$x].'<br>';
					$consideracoes .= 'VENCIMENTO: '.$ins_vencimento[$x].'<br>';
					

					$x++;

				}
				if(trim($intextopadrao)!==""){
					$consideracoes .= preg_replace('/<(\w+) [^>]+>/', '<$1>', $intextopadrao);
				}
				if(trim($row["textopadrao"])!==""){
					$consideracoes .= preg_replace('/<(\w+) [^>]+>/', '<$1>', $row["textopadrao"]);
				}
			   
					//    die();
				if ($modelo =="UPLOAD"){//Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao
				relelisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"],$row["idespeciefinalidade"],$mostraass, 1, $row["textointerpretacao"], $row["textopadrao"]);
				$resultado .=$templatecsv; 
				}

				
				$resultado = preg_replace('/<P[^>]*>\s*?<\/P[^>]*>/', '', $resultado);
				$resultado = preg_replace('/(<[^>]+) style=".*?"/i', '$1',  $resultado);
				$resultado = preg_replace('/(<[^>]+) align=".*?"/i', '$1',  $resultado);
				//Escreve diretamente na tela o resultado descritivo gerado pelo RTE
				$resultado = str_replace("\r\n",". ",$resultado);
				$resultado = strip_tags(html_entity_decode($resultado));
				//die( str_replace($indesejaveis, "", $resultado));   

				if ($row['idtipoteste'] == 584 or 
				$row['idtipoteste'] == 585 or 
				$row['idtipoteste'] == 614 or 
				$row['idtipoteste'] == 702 or 
				$row['idtipoteste'] == 717 or 
				$row['idtipoteste'] == 724 or 
				$row['idtipoteste'] == 761 or 
				$row['idtipoteste'] == 762 or 
				$row['idtipoteste'] == 764 or 
				$row['idtipoteste'] == 765 or 
				$row['idtipoteste'] == 766 or 
				$row['idtipoteste'] == 1317 or 
				$row['idtipoteste'] == 1418 or 
				$row['idtipoteste'] == 1513 or 
				$row['idtipoteste'] == 1514 or 
				$row['idtipoteste'] == 1515 or 
				$row['idtipoteste'] == 1516 or 
				$row['idtipoteste'] == 1517 or 
				$row['idtipoteste'] == 1518 or 
				$row['idtipoteste'] == 1519 or 
				$row['idtipoteste'] == 1520 or 
				$row['idtipoteste'] == 1521 or 
				$row['idtipoteste'] == 1522 or 
				$row['idtipoteste'] == 1523 or 
				$row['idtipoteste'] == 1524 or 
				$row['idtipoteste'] == 1584 or 
				$row['idtipoteste'] == 1585 or 
				$row['idtipoteste'] == 1681 or 
				$row['idtipoteste'] == 1684 or 
				$row['idtipoteste'] == 1693 or 
				$row['idtipoteste'] == 1694 or 
				$row['idtipoteste'] == 1775 or 
				$row['idtipoteste'] == 1901 or 
				$row['idtipoteste'] == 1904 or 
				$row['idtipoteste'] == 1914 or 
				$row['idtipoteste'] == 1933 or 
				$row['idtipoteste'] == 1937 or 
				$row['idtipoteste'] == 1938 or 
				$row['idtipoteste'] == 1940 or 
				$row['idtipoteste'] == 1941 or 
				$row['idtipoteste'] == 1942 or 
				$row['idtipoteste'] == 1957 or 
				$row['idtipoteste'] == 1980 or 
				$row['idtipoteste'] == 1984 or 
				$row['idtipoteste'] == 1986 or 
				$row['idtipoteste'] == 1988 or 
				$row['idtipoteste'] == 2005 or 
				$row['idtipoteste'] == 2073 or 
				$row['idtipoteste'] == 2074 or 
				$row['idtipoteste'] == 2075 or 
				$row['idtipoteste'] == 2077 or 
				$row['idtipoteste'] == 2095 or 
				$row['idtipoteste'] == 2247 or 
				$row['idtipoteste'] == 2248 or 
				$row['idtipoteste'] == 2249 or 
				$row['idtipoteste'] == 2250 or 
				$row['idtipoteste'] == 2259 or 
				$row['idtipoteste'] == 2288 or 
				$row['idtipoteste'] == 2289 or 
				$row['idtipoteste'] == 2290 or 
				$row['idtipoteste'] == 2294 or 
				$row['idtipoteste'] == 2310 or 
				$row['idtipoteste'] == 2328 or 
				$row['idtipoteste'] == 2335 or 
				$row['idtipoteste'] == 2384 or 
				$row['idtipoteste'] == 2385 or 
				$row['idtipoteste'] == 2409 or 
				$row['idtipoteste'] == 2410 or 
				$row['idtipoteste'] == 2411 or 
				$row['idtipoteste'] == 2491 or 
				$row['idtipoteste'] == 2607 or 
				$row['idtipoteste'] == 2737 or 
				$row['idtipoteste'] == 2767 or 
				$row['idtipoteste'] == 2772 or 
				$row['idtipoteste'] == 2773 or 
				$row['idtipoteste'] == 2774 or 
				$row['idtipoteste'] == 2783 or 
				$row['idtipoteste'] == 2786 or 
				$row['idtipoteste'] == 2788 or 
				$row['idtipoteste'] == 2929 or 
				$row['idtipoteste'] == 2930 or 
				$row['idtipoteste'] == 2938 or 
				$row['idtipoteste'] == 2939 or 
				$row['idtipoteste'] == 2999 or 
				$row['idtipoteste'] == 3000 or 
				$row['idtipoteste'] == 3001 or 
				$row['idtipoteste'] == 3009 or 
				$row['idtipoteste'] == 3020 or 
				$row['idtipoteste'] == 3193 or 
				$row['idtipoteste'] == 3227 or 
				$row['idtipoteste'] == 3228 or 
				$row['idtipoteste'] == 3229 or 
				$row['idtipoteste'] == 3238 or 
				$row['idtipoteste'] == 3254 or 
				$row['idtipoteste'] == 3324 or 
				$row['idtipoteste'] == 3325 or 
				$row['idtipoteste'] == 3348 or 
				$row['idtipoteste'] == 3349 or 
				$row['idtipoteste'] == 3525 or 
				$row['idtipoteste'] == 3538 or 
				$row['idtipoteste'] == 3576 or 
				$row['idtipoteste'] == 3641 or 
				$row['idtipoteste'] == 3642 or 
				$row['idtipoteste'] == 3664 or 
				$row['idtipoteste'] == 3665 or 
				$row['idtipoteste'] == 3968 or 
				$row['idtipoteste'] == 3992 or 
				$row['idtipoteste'] == 3993 or 
				$row['idtipoteste'] == 4068 or 
				$row['idtipoteste'] == 4091 or 
				$row['idtipoteste'] == 4092 or 
				$row['idtipoteste'] == 4124 or 
				$row['idtipoteste'] == 4136 or 
				$row['idtipoteste'] == 4353 
				){
					$resultado = '(vide tabela)';
				}
				
				//die('chegou');
				
				$consideracoes = strip_tags(html_entity_decode($consideracoes));
				$consideracoes = str_replace("\r\n",".",trim($consideracoes));
				
				$varobs = strip_tags(html_entity_decode($row["observacao"]));
				$varobs = str_replace("\r\n",".",$varobs);

				// $varobs = nl2br($row["observacao"]);
				//die($row["resvincnf"].$row["statuspgto"]);
				 if ($row["resvincnf"] == 'S' and $row["statuspgto"] == 'PENDENTE' and $_SESSION["SESSAO"]["IDTIPOPESSOA"]!=1 and !$_SESSION["SESSAO"]["SUPERUSUARIO"]){
								$resultado = 'PREZADO CLIENTE, HÁ UMA PENDÊNCIA NO REGISTRO <b>'.$row["idresultado"].'</b> QUE IMPOSSIBILITA SUA VISUALIZAÇÃO/IMPRESSÃO. FAVOR ENTRAR EM CONTATO COM O SETOR ADMINISTRATIVO ATRAVÉS DO (34) 3222-5700 OU VENDAS@LAUDOLAB.COM.BR. OBRIGADO!';
							}
							
				if (empty($csvgmt)){
					$csvgmt = 	$row['gmt'];
				}
				$conteudoexport.=(
					$row['nome'].";".
					$row['idregistro'].";".
					$datacoleta.";".
					$row['dataamostraformatada'].";".
					$nroamostra.";".
					$row['tipoamostraformatado'].";".
					$row['descricao'].";".
					$row['especiefinalidade'].";".
					$granja.";".
					$row['nucleo'].";".
					$galpao.";".
					$row['lote'].";".
					$idade.";".  
					$nsvo.";".
					$row['responsavel'].";".
					$row["tipoteste"].";".
					$alertarotulo.";".
					$csvgmt.'
				'); 
			}
		unset($ins_nomepartida);
														unset($ins_fabricante);
														unset($ins_partidaext);
														unset($ins_fabricacao);
														unset($ins_vencimento);
	}//while($row = mysql_fetch_array($res)){

	//imprimir relatorio da impressão para resultados oficiais
	if($impoficial=="Y" & empty($_REQUEST['csv'])){

		while($row=mysql_fetch_assoc($resf1)){

			if($cabecalho!=1){
			if($_SESSION["SESSAO"]["IDTIPOPESSOA"]!=1 or ( $rowp["visualizares"]=='Y' AND $mostracabecalho!="N" )){
		
		// imagem de https://www.base64-image.de/
		//../img/Cabecalho Resultado.png
		?>
		<table style="<?=$pb?>;width:700px !important; margin:auto;  ">
		<thead>
		<tr  style="position:relative;">
		<td>
		<div style=" width:700px ;">
			<table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px; valign:bottom;background:url('../inc/img/cabecalho-relatorio-de-ensaio.png'); 
				background-position: left; background-size: cover; border: 1px solid #eee; border-radius: 7px 0px 0px 0px; border-right:none;background-repeat:no-repeat;	">
			
			<tr>
				<td style="width:500px; height:127px;	" >
				&nbsp;
				</td>
				<td style="border: none; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:right;vertical-align:bottom">
						&nbsp;
				</td>
				<!-- <td style="width:171px;"  align="right">Rod. BR 365, KM 615 - B. Conj Alvorada<br>CEP 38407-180 - Uberl&acirc;ndia - MG<br>laudolab@laudolab.com.br<br>www.laudolab.com.br<br>Fone/Fax: (34) 3222-5700</td>-->
			</tr>
			
			</table>
		</div>
		</td>
		</tr>
		</thead> 
		
	<?
		$pb="";
	}else{
	?>	
		
		<table style="<?=$pb?>;width:700px !important; margin:auto;  ">
		<thead>
		<tr style="position:relative;">
		<td>
		<div style=" width:700px ;">
			<table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px;">
			
			
			<tr>
				<td style="width:498px; height:141px; " >
				&nbsp;
				</td>
				<td style="border: none; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:left; ">
				&nbsp;
				</td>
				<!-- <td style="width:171px;"  align="right">Rod. BR 365, KM 615 - B. Conj Alvorada<br>CEP 38407-180 - Uberl&acirc;ndia - MG<br>laudolab@laudolab.com.br<br>www.laudolab.com.br<br>Fone/Fax: (34) 3222-5700</td>-->
			</tr>
			
			</table>
		</div>
		</td>
		</tr>
		</thead>
		
		
	<? }	

	?>
				<tbody>
				<tr><td style="font-size:12px !important">
				<br clear="all">	
				<div style="text-align: center; font-size: 13px !important;">RELAÇÃO DE TERMO(S) DE COLHEITA - TC</div>
				<table >
				<tr>			
					<td><p><br><p></td>
				</tr>
				<tr>
					<td style="font-weight:bold;font-size:8px;">Aos cuidados do Dr(a). Fiscal,</td>
				</tr>
				<tr>
					<td nowrap style="font-size:8px;">Segue relação do(s) cliente(s) e do(s) termo(s) de colheita (TC) que consta(m) neste:</td>
				</tr>
				</table>
			
				<table >
				<tr>			
					<td><p><br><p></td>
				</tr>
				<tr>
					<td nowrap style="font-weight:bold;font-size:8px !important" width="160">SECRETARIA OFICIAL:</td>
					<td style="font-size:8px !important" ><?=traduzid("pessoa","idpessoa","nome",$row["idsecretaria"])?></td>
				</tr>
		 
				<tr>
					<td nowrap style="font-weight:bold;font-size:8px !important" >CLIENTE:</td>
					<td style="font-size:8px !important"><?=$row["nome"]?></td>
				</tr>
				<tr>			
					<td><p><br></td>
				</tr>
				</table>
				<table style="display: inline; margin:4px;"><tr><td>
				
		<?
			}
		?>	
			<?if($row["tc"]!=$xtc){ 
				if(!empty($xtc)){
					
				}
			?>
			
			<?}?>
			
			<?if($row["tc"]!=$xtc){	
				$xtc=$row["tc"]; 
			?>
			<div style="width:220px;float:left;">
			<table style=" border: 1px dashed #ccc;  ">
			<tr >			
				<td  style="width: 210px;font-size:8px !important"><p></td>
			</tr>
			<tr >
			<td nowrap="nowrap"  style="width: 210px;font-size:8px !important" ><font style="font-weight:bold;font-size:8px !important">TC:</font><?=$row["tc"]?></td>
			</tr>
			
			<tr >
			<td nowrap="nowrap"  style="width: 210px;font-size:8px !important "><?=$row["tipoamostra"]?><?if($row["subtipoamostra"]!=$row["tipoamostra"]){?> <?=$row["subtipoamostra"]?><?}?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			</tr>
	</table>
		</div>
		<?}?>
		<?		
			$cabecalho=1;
		}
		?>
		</td></tr>
			</table>
			<table>
			<tr>			
				<td><p><br><p></td>
			</tr>
			<tr>			
				<td><p><br><p></td>
			</tr>
			<tr>			
				<td><p><br><p></td>
			</tr>
			</table>
			<table>
			<tr>			
				<td nowrap="nowrap" style="vertical-align:top;font-size:8px !important">Atenciosamente:</td>
			</tr>
			<tr>
				<td><p><br><p></td>
			</tr>
			<tr>			
				<td nowrap="nowrap" style="vertical-align:top;font-size:8px !important">_______________________________________</td>
			</tr>
			<tr>			
				<td nowrap="nowrap" style="vertical-align:top;font-size:8px !important">Laudo Laboratório Avícola Uberlândia Ltda</td>
			</tr>
			<tr>			
				<td nowrap="nowrap" style="vertical-align:top;font-size:8px !important">Data: <?echo(date("d/m/Y"));?></td>
			</tr>
		</table>	
		</td>
		</tr>
		</tbody>
		</table>
		<?
	}

	?>
	</body>
	</html>

	<?
	$apagaDashboard = true;

	/*
		 * INDICACAO DE VISUALIZACAO DO RESULTADO
		 */
			if($_SESSION["SESSAO"]["IDTIPOPESSOA"]!=1 and !$_SESSION["SESSAO"]["SUPERUSUARIO"] and empty($_REQUEST['csv'])){//se nao for funcionario laudo

	/*
					$sqlvisres = "update resultado set alteradoem = now(), statusvisualizacao = 'Y' where idresultado = " . $row["idresultado"];
					//echo "<!-- ".$sqlvisres." -->";
					$resvisres = mysql_query($sqlvisres);
					if(!$resvisres){
						 if($echosql){echo "<!-- ".mysql_error()." -->";}
					}
	*/
				//Registra quando ocorreu a visualizacao
				$sqlvis = "insert into resultadovisualizacao (idresultado, idpessoa,criadoem) values (".$row["idresultado"].",".$_SESSION["SESSAO"]["IDPESSOA"].",now())";
				$resvis = mysql_query($sqlvis);
				if(!$resvis){
					 if($echosql){echo "<!-- ".mysql_error()." -->";}
				}

				//Retira notificação do dashboard
				if($apagaDashboard){
					$sqldash = "delete from dashboardnucleopessoa where idobjeto = ".$row["idresultado"]." and tipoobjeto = 'resultado' and idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"];
					$resdash = mysql_query($sqldash);
					if(!$resdash){
						 if($echosql){echo "<!-- ".mysql_error()." -->";}
					}
				}

			}
	if ($_REQUEST['gerapdf'] == 'Y'){
		$html = ob_get_contents();
		//limpar o codigo html
		$html = preg_replace('/>\s+</', "><", $html);

		ob_end_clean();
		define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");
		define("DOMPDF_ENABLE_HTML5PARSER", true);
		define("DOMPDF_ENABLE_FONTSUBSETTING", true);
		define("DOMPDF_UNICODE_ENABLED", true);
		
		define("DOMPDF_DPI", 86);
		define("DOMPDF_ENABLE_REMOTE", true);
		define("DOMPDF_DEFAULT_PAPER_SIZE", "A4");

		 

		// Incluímos a biblioteca DOMPDF
	require_once("../inc/dompdf/dompdf_config.inc.php");
		
	//	 require_once "../inc/dompdf3/vendor/autoload.php";
	//	use Dompdf\Dompdf;

		// Instanciamos a classe
		$dompdf = new DOMPDF();
		 $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		// Passamos o conteúdo que será convertido para PDF
		 $dompdf->load_html($html,'UTF-8'); 

		// Definimos o tamanho do papel e
		// sua orientação (retrato ou paisagem)
		$dompdf->set_paper('A4','portrait');
		 
		// O arquivo é convertido
		$dompdf->render();
		
		$gravaarquivo='N';
		$nomearq = $row["idresultado"];
		
		if($gravaarquivo=='Y'){
			
		// Salvo no diretório  do sistema
			$output = $dompdf->output();
			file_put_contents("/var/www/carbon8/tmp/resultadopdf/resultado".$nomearq.".pdf",$output);
			echo($newidcomunicacao);
		}else{   
		// Exibido para o usuário
			$dompdf->stream("resultado_".$nomearq.".pdf");
		}
	}	
	if (!empty($_REQUEST['csv'])){

		//echo($conteudoexport);
			ob_end_clean();//não envia nada para o browser antes do termino do processamento
		
		/* Gerar o nome do arquivo para exportar
		 * Substitui qualquer caractere estranho pelo sinal de '_'
		 * Caracteres que NAO SERAO substituidos:
		 *   - qualquer caractere de A a Z (maiusculos)
		 *   - qualquer caracteres de a a z (minusculos)
		 *   - qualquer caractere de 0 a 9
		 *   - e pontos '.'
		 */ 
		$infilename = 'resultados';	
		//gera o csv
		header('Content-Encoding: UTF-8');
		header("Content-type: text/csv; charset=UTF-8");
		header("Content-Disposition: attachment; filename=".$infilename.".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo iconv('UTF-8', 'ISO-8859-1', trim($conteudoexport));
		
		//echo($conteudoexport);
	}
	?>
