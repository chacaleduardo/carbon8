<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting (E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
$ler=true;//Gerar logs de erro
$rid="\n".rand()." - Sislaudo: ";
if($ler)error_log($rid.basename(__FILE__, '.php'));

session_start();
$sessionid = session_id();//PEGA A SESSÃO

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	$prefu="stdin_";
	require_once("/var/www/carbon8/inc/php/functions.php");
	require_once("/var/www/carbon8/api/notifitem/notif.php");
}else{//se estiver sendo executado via requisicao http
	require_once("../inc/php/functions.php");
	require_once("../api/notifitem/notif.php");
}

// Altera o status da configuracao dos alertas ABERTO para PROCESSANDO e reserva para a sessão
$sqlc = "	update 
				immsgconf ic 
			JOIN
				immsgconfplataforma ip on ic.idimmsgconf = ip.idimmsgconf
			set 
				statusprocesso = 'PROCESSANDO', 
				sessionid = '".$sessionid."' 
            where 
				tipo in('N')
				and statusprocesso = 'ABERTO'
				
				and status='ATIVO';"; 
				echo '<pre>'.$sqlc.'</pre>';	
$retc = mysql_query($sqlc);

if(!$retc){
	//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
	$strerr="Erro ao alterar ALERTA para consulta: \n<br>".mysql_error()."\n<br>".$sqlc;
	echo($strerr);
	if($ler)error_log($rid.$strerr);
	return false;
}else{
	if($ler)error_log($rid."update immsgconf ok");
}

//busca  as configurações para envio da mensagem
 $sql="select 
		ifnull(ic.tabela,m.tab) as tab,
		if (m.modulo = 'tarefaacumulada', 'pessoa',m.modulo) as modulo,
		m.rotulomenu,tc.col,ic.idimmsgconf,ic.titulo,ic.tipo,ic.ideventotipo,ic.code,ic.mensagem,ic.titulocurto,ic.apartirde,ic.multiplo,ic.localizacao,

		DATE_FORMAT(CASE
		  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Minute' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) MINUTE)
		  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Hour' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) HOUR)
		  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Year' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) YEAR)
		  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Month' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) MONTH)
		  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Day' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) DAY)
		  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Week' THEN DATE_ADD(now(), INTERVAL CAST(multiplo AS UNSIGNED) WEEK) 
		END , '%Y-%m-%d') as prazo,
		ip.idplataforma,
		ic.somentecriador
	from 
	immsgconf ic
	JOIN
		immsgconfplataforma ip on ic.idimmsgconf = ip.idimmsgconf
	JOIN "._DBCARBON."._modulo m ON m.modulo = ic.modulo
	JOIN "._DBCARBON."._mtotabcol tc on tc.tab = m.tab and  tc.primkey ='Y'
	where             
		ic.tipo = 'N'
		and ic.status='ATIVO'
		and ic.statusprocesso = 'PROCESSANDO'
		and ic.sessionid = '".$sessionid."'
		and exists (select 1 from immsgconffiltros f where f.valor!=' ' and f.valor is not null and f.idimmsgconf = ic.idimmsgconf)
		";	echo '<pre>'.$sql.'</pre>';	
	
	//die($sql);	
 $res=d::b()->query($sql);

if(!$res){
	$strerr="A Consulta na immsgconf falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql";
	if($ler)error_log($rid.$strerr);
}else{
	if($ler)error_log($rid."Consulta immsgconf ok: ".mysqli_num_rows($res)." registros");
}
 
while($row=mysqli_fetch_assoc($res)){
	//busca os filtros para seleção
	// GVT - 13/02/2020 - Alterado o select para trazer valores nulos quando estão acompanhados do sinal 'is' ou 'is not'
	$sqlf="SELECT 
				imf.col,mt.datatype, imf.sinal, imf.valor, imf.nowdias, imf.idimmsgconffiltros
			FROM
				immsgconffiltros imf
				JOIN carbonnovo._mtotabcol mt on (mt.col = imf.col and mt.tab = '".$row['tab']."')
			WHERE
				valor != '' AND valor != ' '
					AND ((valor = 'null'
					AND (sinal = 'is' OR sinal = 'is not'))
					OR valor != 'null')
					AND valor IS NOT NULL
					AND idimmsgconf = ".$row["idimmsgconf"];
	$resf=d::b()->query($sqlf) or die("A Consulta na immsgconffiltros falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlf");
	echo '<pre>'.$sqlf.'</pre>';	
	$qtdf=mysqli_num_rows($resf);
	$and=" ";
	if($qtdf>0){
		$clausula="";
		while($rowf=mysqli_fetch_assoc($resf)){
			// GVT - 13/02/2020 - Alterado a ci=ondição para permitir valores nulos quando estão acompanhados do sinal 'is' ou 'is not'
			if(($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!='') or ($rowf["valor"]=='null' and ($rowf["sinal"]=='is' or $rowf["sinal"]=='is not'))){
				if($rowf["valor"]=='now'){
					if(!empty($rowf["nowdias"])){
						if($rowf['datatype'] == 'date'){
							$rowf["nowdias"];
						   	$date=date("Y-m-d");
						   	$valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
						}else{
							$rowf["nowdias"];
							$date=date("Y-m-d H:i:s");
							$valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$rowf["nowdias"].' day'));
						}
					}else{
						if($rowf['datatype'] == 'date'){
							$valor=date("Y-m-d");
						}else{
							$valor=date("Y-m-d H:i:s");
						}
					}
				}else if($rowf["valor"]=='mais'){
					$date=date("Y-m-d H:i:s");
					$valor=date('Y-m-d H:i:s', strtotime($date. ' + '.$rowf["nowdias"].' day'));

				}else if($rowf["valor"]=='menos'){
					$date=date("Y-m-d H:i:s");
					$valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$rowf["nowdias"].' day'));

				}else{
					$valor=$rowf["valor"];

				}
				// $valor;

				if($rowf['sinal']=='in'){
					$strvalor = str_replace(",","','",$valor);
					$clausula.= $and." a.".$rowf["col"]." in ('".$strvalor."')";
				}elseif($rowf['sinal']=='like'){
					$clausula.= $and." a.".$rowf["col"]." like ('%".$valor."%')";
				}elseif($rowf['sinal']=='is'){
					$clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." ".$valor."";
				}elseif($rowf['sinal']=='sql'){
					$clausula.= $and.$valor;
				}elseif($rowf['sinal']=='between'){
					$clausula.= $and.'a.'.$rowf["col"]." between now() and ".$valor;
				}else{
					$clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." '".$valor."'";
				}
				$and=" and ";
			}else{
				if($ler)error_log($rid.'rowf[valor] não previsto');
			}
		}//while

		// busca na tabela configurada os ids
		 $sqlx="SELECT distinct a.".$row['col']." AS idpk, a.criadopor -- , 1029 as idpessoa, a.idempresa, a.idunidade
			FROM ".$row["tab"]." a 
			WHERE ".$clausula."               
				-- AND a.alteradoem >= '".$row['apartirde']."'
				AND NOT EXISTS( SELECT 1 
					FROM immsgconflog l
					JOIN immsgconf m on m.idimmsgconf = l.idimmsgconf
					JOIN immsgconfplataforma ip on m.idimmsgconf = ip.idimmsgconf
					WHERE l.idpk = a.".$row['col']."
					AND l.modulo = '".$row['modulo']."'
					AND l.idimmsgconf = ".$row['idimmsgconf']."
					and l.status = 'SUCESSO'
					and ip.idplataforma = '".$row['idplataforma']."')";
			echo '<pre>'.$sqlx.'</pre>';
		//echo($sqlx);die;
		$resx=d::b()->query($sqlx);

		if(!$resx){
			$strerr="A Consulta na tabela de origem dos dados falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlx";
			if($ler)error_log($rid.$strerr);
		}else{
			if($ler)error_log($rid."immsgconflog ok: ".mysqli_num_rows($resx)." registros");
		}

		while($rowx=mysqli_fetch_assoc($resx)){ 

			/****************************************************************
			 *			Verifica os destinatários 
			 ****************************************************************/
			if($row['somentecriador'] == "N"){
				if($row["tab"] == 'vwevento' || $row["tab"] == 'evento'){
					$addclaus = "AND EXISTS (SELECT 
												1
											FROM
												fluxostatuspessoa mp
											WHERE
												idmodulo = ".$rowx['idpk']."
													AND modulo = 'evento'
													AND mp.idobjeto = p.idpessoa
													AND mp.tipoobjeto = 'pessoa')";
				}else{
					$addclaus = '';
				}

				$sqlc="SELECT distinct(u.idpessoa) as idpessoa,u.usuario, u.nome, u.idobjetoext, u.objetoext
						from (	
							SELECT 
									p.idpessoa, p.nome,p.usuario, c.idobjetoext, c.objetoext
							FROM
									pessoa p,
									immsgconfdest c
							WHERE
									c.objeto = 'pessoa'
									AND c.status='ATIVO'
									AND p.idpessoa = c.idobjeto
									AND c.idimmsgconf = ".$row['idimmsgconf']."
									AND p.status = 'ATIVO'
									$addclaus
								
						) as u";
			}else{
				$sqlc = "SELECT distinct(u.idpessoa) as idpessoa,u.usuario, u.nome -- , u.idobjetoext, u.objetoext
						from (	
							SELECT 
									p.idpessoa, p.nome,p.usuario -- , c.idobjetoext, c.objetoext
							FROM
									pessoa p
							WHERE
								p.status = 'ATIVO'
								AND p.usuario = '".$rowx['criadopor']."'
						) as u";
			}
			
			echo '<pre>'.$sqlc.'</pre>';
			$resc=d::b()->query($sqlc);

			if(!$resc){
				$strerr="A busca dos contatos falhou : " . mysqli_error(d::b())."\n".$sqlc;
				if($ler)error_log($rid.$strerr);
			}else{
				if($ler)error_log($rid."immsgconfdest ok: ".mysqli_num_rows($resc)." registros");
			}
			
			
			if (mysqli_num_rows($resc) > 0){
				// insere um log
				 $sl="INSERT INTO immsgconflog
				(idempresa,idimmsgconf,idpk,modulo,status,criadopor,criadoem,alteradopor,alteradoem)
				VALUES
				(1,".$row['idimmsgconf'].",".$rowx['idpk'].",'".$row['modulo']."','ENVIANDO','".$prefu."immsgconf',now(),'immsgconf',now())";
				echo '<pre>'.$sl.'</pre>';
				$rlog=d::b()->query($sl);

				if(!$rlog){
					$strerr="Erro ao gerar log [immsgconflog]: ".mysqli_error(d::b())."\n".$sl;
					if($ler)error_log($rid.$strerr);
				}

				//recupera o ultimo ID inserido
				$idimmsgconflog = mysqli_insert_id(d::b());
				if(empty($idimmsgconflog)){
					//Erro: valor vazio ao recuperar insert_id
					if($ler)error_log($rid."VALOR_VAZIO_INSERTID_LOG");
					return '{"code":"VALOR_VAZIO_INSERTID_LOG"}';
				}

				/****************************************************************
				 *			Cria corpo da mensagem: Insere na msgbody
				 ****************************************************************/
				//$link="<a href=\"?_modulo=".$row['modulo']."&_acao=u&".$row['col']."=".$rowx['idpk']."\" target=\"_blank\">".$rowx['idpk']."</a>";

				$titulocurto 	= addslashes($row['titulocurto']).' '.$rowx['idpk'];
				$mensagem		= addslashes($row['mensagem']);
				$jsonresultado  = ('{"tags": [], "pessoas": [], "documentos": [], "tagsValores": [], "personalizados": [], "pessoasValores": [], "documentosValores": []}');
				$link="?_modulo=".$row['modulo']."&_acao=u&".$row['col']."=".$rowx['idpk'];
				$nome=$row['rotulomenu'].": ".$row['col']."=".$rowx['idpk'];
				//$canal= [$row['idplataforma']];
				$canal[$row['idplataforma']] = [
					"tipo" => "template", // ou idnotificacaoconfiguracao
					"template" => [
						"mod" => $row['modulo'],
						"modpk" => $row['col'],
						"idmodpk" => $rowx['idpk'],
						"title" => $titulocurto,
						"corpo" => $mensagem,
						"localizacao"=>$row['localizacao'],
						"url" => $link,
					],
				];
					
			
				$c = 0;

				$notif = Notif::ini()
					->canal("browser")
					->conf([
						"mod"        =>	 $row['modulo'],
						"modpk"      =>  $row['col'],
						"idmodpk"    =>  $rowx['idpk'],
						"title"      =>  $titulocurto,
						"corpo"      =>  $mensagem,
						"localizacao"=>  $row['localizacao'],
						"url"        =>  $link,
					]);

				while($rowc=mysqli_fetch_assoc($resc)){
					$notif->addDest($rowc["idpessoa"]);

					echo "<p>".var_dump($canal)."</p>";


					// $sm = "INSERT INTO notificacao 
					// (	idnotificacao, idpessoa, idimmsgconf, modulo, idmodulo, idempresa, 
					// 	idunidade, titulo,  descricao, status, idplataforma, criadopor, criadoem,
					// 	alteradopor, alteradoem
					// )
					// VALUES 		
					// 	(	null, '".$rowc['idpessoa']."','".$row['idimmsgconf']."','".$row['modulo']."','".$rowx['idpk']."',
					// 	'".$rowx['idempresa']."','".$rowx['idunidade']."', '".$row['titulo']."', '".$titulocurto."', 'PENDENTE', '".$row['idplataforma']."',   
					// 	'immsgconf',DATE_FORMAT(now(),'%Y-%m-%d %h:%m:%s'),'immsgconf',DATE_FORMAT(now(),'%Y-%m-%d %h:%m:%s')
					// )";
					// echo '<pre>'.$sm.'</pre>';
					// $rmb=d::b()->query($sm);

					// if(!$rmb){
					// $strerr="Erro ao criar notificacao: ".mysqli_error(d::b())."\n".$sm;
					// if($ler)error_log($rid.$strerr);
					// }

					//recupera o ultimo ID inserido
					//$idnotificacao = mysqli_insert_id(d::b());

					// atualiza o log para sucesso



							
								/****************************************************************
								* Insere a mensagem na tabela de chat com o id da mensagem relacionada
								****************************************************************/

							// 	$arrayFuncionario[] = array(
							// 		'idobjetoext' 			=> $rowc['idobjetoext'],
							// 		'tipoobjetoext' 		=> $rowc['objetoext'],
							// 		'inseridomanualmente' 	=> 'S',
							// 		'label' 				=> $rowc['nome'],
							// 		'status' 				=> $status,
							// 		'statusevento' 			=> $status,
							// 		'tipo' 					=> 'pessoa',
							// 		'value' 				=> $rowc['idpessoa'],
							// 		'visualizado' 			=> 0
							// 	);



							// 	if(!$rii){
							// 		$strerr="Erro ao inserir msg: ".mysqli_error(d::b())."\n".$si;
							// 		if($ler)error_log($rid.$strerr);
							// 	}

							// 	//recupera o ultimo ID inserido
							// 	$idmsgins = mysqli_insert_id(d::b());                       

							// 	if(empty($idmsgins)){
							// 		if($ler)error_log($rid."VALOR_VAZIO_INSERTID_MSGINS");
							// 	}

							// 	if($row['tipo']=="A" or $row['assinar']=="Y"){
							// $c++;
							// }
				}
				
				$notif->send();
				echo "<p>".var_dump($arrDestinatarios)."</p>";
				echo "<p>".var_dump($notif->response)."</p>";
				print_r([
					"canais" => [
						"browser" => [
						"tipo" => "template", // ou idnotificacaoconfiguracao
						"template" => [
							"mod"        =>	 $row['modulo'],
							"modpk"      =>  $row['col'],
							"idmodpk"    =>  $rowx['idpk'],
							"title"      =>  $titulocurto,
							"corpo"      =>  $mensagem,
							"localizacao"=>  $row['localizacao'],
							"url"        =>  $link,
							],
						]
					]
					,
					"destinatarios" => $arrDestinatarios,
					]);
				$su="UPDATE immsgconflog set status='SUCESSO', idimmsgbody='".($canal)."' where idimmsgconflog=".$idimmsgconflog;
				$rlog=d::b()->query($su);
				echo '<pre>'.$su.'</pre>';
				if(!$rlog){
				$strerr="Erro ao atualizar log [immsgconflog] : ".mysqli_error(d::b())."\n".$su;
				if($ler)error_log($rid.$strerr);
					}

				//$val = json_encode($arrayFuncionario);
			} 

		} //while($row=mysqli_fetch_assoc($res))
	}      
	// Altera o status da configuracao dos alertar de PROCESSANDO para ABERTO e reserva para a sessão
	echo $sqlc = "update immsgconf ic set statusprocesso = 'ABERTO'
			where tipo not in('E','ET','EP')
		and statusprocesso = 'PROCESSANDO'
		and status='ATIVO'";
	$retc = mysql_query($sqlc);
	echo '<pre>'.$sqlc.'</pre>';
	if(!$retc){
		$strerr="Erro ao voltar status do  ALERTA para ABERTO: \n<br>".mysql_error()."\n<br>".$sqlc;
		echo $strerr;
		if($ler)error_log($rid.$strerr);
	}
}