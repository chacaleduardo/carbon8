<?
//ini_set("display_errors","1");
//error_reporting(E_ALL);
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$idnf= $_GET['idnf'];

if(empty($idnf)){
	die('ID no pedido não informado');
}

$sqlimp="select ip from tag t
            where t.varcarbon='IMPRESSORA_ALMOXARIFADO_ITEM'
            ".share::otipo('cb::usr')::tagimpressora('t.idtag')."
            and t.ip is not null 
            and t.status=	'ATIVO'";
$resimp=d::b()->query($sqlimp) or die("Erro ao buscar impressora do diagnostico: ".mysqli_error(d::b()));
$qtdimp=mysqli_num_rows($resimp);
if($qtdimp<1){die("Não encontrada impressora do diagnostico em tags var carbon.");}
$rowimp=mysqli_fetch_assoc($resimp);
define("_IP_IMPRESSORA_ALMOXARIFADO_ITEM",$rowimp['ip']);

$sql="select n.*,
		n.status as nfstatus,
		t.nome as transporte,
		n.pedidoext,
		n.idnf as idpedido,
		ps.dddfixo,ps.telfixo,
		ps.nome,
		ps.razaosocial,
		LENGTH(ps.nome) as nometamanho,		
		SUBSTRING(ps.nome, 1, 35) as nomeinicio,
		SUBSTRING(ps.nome, 36, 78) as nomefim,
		pf.obslogistica,
		e.*
		from nf n
		join pessoa ps on (ps.idpessoa=n.idpessoa)
		join empresa e on (e.idempresa = n.idempresa)
		left join pessoa t on( n.idtransportadora = t.idpessoa)
		left join preferencia pf on (pf.idpreferencia = ps.idpreferencia)
		where n.idnf in (".$idnf.")";
$res=d::b()->query($sql) or die('Erro ao buscar dados do pedido sql='.$sql);
$qtdrow=mysqli_num_rows($res);
while($row=mysqli_fetch_assoc($res)){


		$mn=0;

		$str = "";
		$altura="60";

		if(!empty($row['idendereco']) and $row['impendereco']=='Y'){
				$mn += 4;
						$str .='TEXT 10,20,"2",0,1,1,"DESTINATARIO:"';
						$str = $str.chr(10);

		//DESTINATARIO

			if($row['nometamanho']<36){
				$mn += 4;
				$altura=$altura+40;
							$str .='TEXT 10,60,"2",0,1,1,"'.retira_acentos(str_replace('  ', '',trim($row['nome']))).'"';
							$str = $str.chr(10);
			}else{
				$mn += 8;
							$str .='TEXT 10,60,"2",0,1,1,"'.retira_acentos(str_replace('  ', '',trim($row['nomeinicio']))).'" ';
							$str = $str.chr(10);
							$str .='TEXT 10,90,"2",0,1,1,"'.retira_acentos(str_replace('  ', '',trim($row['nomefim']))).'" ';
							$str = $str.chr(10);
				$altura=$altura+30;
			}
			
			if(!empty($row['aoscuidados'])){
				$mn += 4;
				$altura=$altura+30;
							$str .='TEXT 10,'.$altura.',"2",0,1,1,"AC: '.retira_acentos(str_replace('  ', '',trim($row['aoscuidados']))).' "';
							$str = $str.chr(10);
			}elseif($row['idcontato']){
				
				$sqlcont="select p.nome
					from pessoa p
					where p.idpessoa =".$row['idcontato'];
				$rescont=d::b()->query($sqlcont) or die("Erro ao buscar informações do contato do pedido sql=".$sqlcont);
				$rowcont=mysqli_fetch_assoc($rescont);
				if(!empty($rowcont['nome'])){
					$mn += 4;
					$altura=$altura+30;
								$str .='TEXT 10,'.$altura.',"2",0,1,1,"AC: '.retira_acentos(str_replace('  ', '',trim($rowcont['nome']))).' "';
								$str = $str.chr(10);
				}
			}


			//ENDERECO
			$sqlf="select c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf,e.obsentrega
			from nfscidadesiaf c,endereco e
			where c.codcidade = e.codcidade
			and e.idendereco =".$row['idendereco'];
			$resf=d::b()->query($sqlf) or die("erro ao buscar informações do endereço sql=".$sqlf);
			$rowf=mysqli_fetch_assoc($resf);

			$mn += 16;
			$altura=$altura+30;
						$str .='TEXT 10,'.$altura.',"2",0,1,1,"'.$rowf["logradouro"].' '.retira_acentos(str_replace('  ', '',trim($rowf['endereco']))).'"';
						$str = $str.chr(10);
			$altura=$altura+30;
						$str .='TEXT 10,'.$altura.',"2",0,1,1,"'.$rowf["numero"].' '.$rowf["complemento"].'"';
						$str = $str.chr(10);
			$cep=formatarCEP($rowf["cep"],true);
			
			$altura=$altura+30;
						$str .='TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos($rowf["bairro"]).' CEP: '.$cep.' "';
						$str = $str.chr(10);
			$altura=$altura+30;
						$str .='TEXT 10,'.$altura.',"2",0,1,1,"Cidade:'.retira_acentos(str_replace('  ', '',trim($rowf['cidade']))).' UF: '.$rowf["uf"].'"';
						$str = $str.chr(10);
			if(!empty($rowf["dddfixo"]) and !empty($rowf["telfixo"])){
				$mn += 4;
				$altura=$altura+30;
							$str .='TEXT 10,'.$altura.',"2",0,1,1,"Telefone:'.$rowf["dddfixo"].'-'.$rowf["telfixo"].'"';
							$str = $str.chr(10);
			}
			if(!empty($rowf['obsentregaa'])){
				$mn += 4;
				$altura=$altura+30;
							$str .='TEXT 10,'.$altura.',"2",0,1,1,"OBS:'.retira_acentos($rowf['obsentrega']).'"';
							$str = $str.chr(10);
			}
			
			$mn += 28;
			//REMETENTE	
			// $altura=$altura+60;
			// 			$str .='TEXT 10,'.$altura.',"2",0,1,1,"REMETENTE"';
			// 			$str = $str.chr(10);

			// $altura=$altura+30;
			// 			$str .='TEXT 10,'.$altura.',"2",0,1,1,"LAUDO LABORATORIO AVICOLA UBERLANDIA LTDA"';
			// 			$str = $str.chr(10);
			
			// $altura=$altura+30;
			// 			$str .='TEXT 10,'.$altura.',"2",0,1,1,"CNPJ: 23.259.427/0001-04"';
			// 			$str = $str.chr(10);
			
			// $altura=$altura+30;
			// 			$str .='TEXT 10,'.$altura.',"2",0,1,1,"I.E: 702.387.177.0001"';
			// 			$str = $str.chr(10);
			
			// $altura=$altura+30;
			// 			$str .='TEXT 10,'.$altura.',"2",0,1,1,"Rod. BR 365, KM 615 - S/N, Bairro Alvorada"';
			// 			$str = $str.chr(10);
			
			// $altura=$altura+30;
			// 			$str .='TEXT 10,'.$altura.',"2",0,1,1,"CEP: 38.407-180"';
			// 			$str = $str.chr(10);
			
			// $altura=$altura+30;
			// 			$str .='TEXT 10,'.$altura.',"2",0,1,1,"Uberlandia-MG Tel: (34) 3222-5700"';
			// 			$str = $str.chr(10);

			$log="Cabecalho [OK]";
		}//if(!empty($»1»u»pedido»idendereco)){


		if($row['impitem']=='Y'){
			
			$mn += 28;
			//REMETENTE
			$altura=$altura+65;
			$str .='TEXT 10,'.$altura.',"3",0,3,2," '.$row['nfstatus'].'"';
		$str = $str.chr(10);
		$altura=$altura+65;
		$str .='TEXT 10,'.$altura.',"4",0,3,3," '.$row['idpedido'].'"';
		$str = $str.chr(10);
			
			if($row['nometamanho']<36){
				$mn += 4;
				$altura=$altura+120;
		$str .='TEXT 10,'.$altura.',"3",0,1,2,"CLIENTE: '.retira_acentos(str_replace('  ', '',trim($row['nome']))).'" ';
		$str = $str.chr(10);
			}else{
				$mn += 8;
				$altura=$altura+120;
		$str .='TEXT 10,'.$altura.',"4",0,1,1,"CLIENTE: '.retira_acentos(str_replace('  ', '',trim($row['nomeinicio']))).'" ';
		$str = $str.chr(10);
				$altura=$altura+50;
		$str .='TEXT 10,'.$altura.',"4",0,1,1,"'.retira_acentos(str_replace('  ', '',trim($row['nomefim']))).'" ';
		$str = $str.chr(10);
			}
			
			if($row['obslogistica']){
				if(strlen($row['obslogistica']) < 36){
					$mn += 4;
					$altura=$altura+50;
					$str .='TEXT 10,'.$altura.',"3",0,1,1,"OBS: '.retira_acentos(str_replace('  ', '',trim($row['obslogistica']))).'"';
					$str = $str.chr(10);
				}else{
					$mn += 8;
					$altura=$altura+50	;
					$str .='TEXT 10,'.$altura.',"3",0,1,1,"OBS: '.retira_acentos(substr(str_replace('  ', '',trim($row['obslogistica'])),0,35)).'" ';
					$str = $str.chr(10);
							$altura=$altura+50;
					$str .='TEXT 10,'.$altura.',"3",0,1,1,"'.retira_acentos(substr(str_replace('  ', '',trim($row['obslogistica'])),35)).'" ';
					$str = $str.chr(10);
				}
			}
			
			if(!empty($row['aoscuidados'])){
				$mn += 4;
				$altura=$altura+30;
							$str .='TEXT 10,'.$altura.',"2",0,1,1,"SOLICITANTE:'.retira_acentos(str_replace('  ', '',trim($row['aoscuidados']))).'"';
							$str = $str.chr(10);
			}elseif($row['idcontato']){
				
				$sqlcont="select p.nome
					from pessoa p
					where p.idpessoa =".$row['idcontato'];
				$rescont=d::b()->query($sqlcont) or die("Erro ao buscar informações do contato do pedido sql=".$sqlcont);
				$rowcont=mysqli_fetch_assoc($rescont);
				if(!empty($rowcont['nome'])){
					$mn += 4;
					$altura=$altura+50;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"AC: '.retira_acentos($rowcont['nome']).' "';
		$str = $str.chr(10);
				}
			}
			
			
			if(!empty($row['transporte'])){
				$mn += 4;
				$altura=$altura+40;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"TRANSPORTE:'.retira_acentos(str_replace('  ', '',trim($row['transporte']))).'"';
		$str = $str.chr(10);
			}
			
		$sqli = "SELECT p.descr,p.codprodserv,p.idprodserv,p.un,p.local,i.qtd,i.idnfitem,p.tipo,p.material,
					LENGTH(p.descr) as nometamanho,
				UPPER(SUBSTRING(p.descr, 1, 49)) as descrinicio,
				UPPER(SUBSTRING(p.descr, 50, 99)) as descrfim
					FROM nfitem i,prodserv p
					where p.idprodserv = i.idprodserv
					and i.idnf =".$row['idnf']." order by p.descr";
			$qri = d::b()->query($sqli) or die("Erro ao buscar itens da nota:".mysqli_error()." sql=".$sqli);
			$qtdrowsi= mysqli_num_rows($qri);
			if($qtdrowsi>0){
				
					$mn += 4;
					$altura=$altura+50;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"ITENS SOLICITADOS"';
		$str = $str.chr(10);
				$i=0;
				while ($rowi = mysqli_fetch_array($qri)){
					$i=$i+1;
					
					if($rowi['nometamanho']<50){
						$mn += 4;
						$altura=$altura+30;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"'.number_format($rowi["qtd"], 0, '', '.').' - '.retira_acentos(str_replace('  ', '',trim($rowi['descr']))).'" ';
		$str = $str.chr(10);
					}else{
						$mn += 8;
						$altura=$altura+30;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"'.number_format($rowi["qtd"], 0, '', '.').' - '.retira_acentos(str_replace('  ', '',trim($rowi['descrinicio']))).'" ';
		$str = $str.chr(10);		
				$altura=$altura+20;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"'.retira_acentos(str_replace('  ', '',trim($rowi['descrfim']))).'" ';
		$str = $str.chr(10);
					}
				
				}
				$mn += 4;
				$altura=$altura+25;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"Qtd. Item:'.$i.'"';
		$str = $str.chr(10);
			}
			
			
			$sqlpr="select dma(p.prazo) as prazo,pr.nomecurto 
					from nf p left join pessoa pr on(pr.idpessoa = p.respenvio)
					where p.idnf =".$row['idnf'];
			$respr=d::b()->query($sqlpr) or die("Erro ao buscar informações do preparo do pedido sql=".$sqlpr);
			$rowpr=mysqli_fetch_assoc($respr);

			$mn += 60;

			$altura=$altura+30;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"PREPARADO POR:'.retira_acentos($rowpr['nomecurto']).' em '.$rowpr['prazo'].'"';
		$str = $str.chr(10);	
			
		// 	$altura=$altura+40;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"Para mais informacoes, duvidas e sugestoes, entre em"';
		// $str = $str.chr(10);
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"contato conosco atraves do:"';
		// $str = $str.chr(10);
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"TEL.: ('.$row['DDDPrestador'].') '.$row['TelefonePrestador'].', (34) 9 9942-2028"';
		// $str = $str.chr(10);
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"PRODUTO '.$row['empresa'].': '.$row['email'].'"';
		// $str = $str.chr(10);
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"MATERIAL DE COLETA: '.$row['email'].'"';
		// $str = $str.chr(10);
		// 	$altura=$altura+30;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"*** Devido a questoes de tempo de preparo de materiais"';
		// $str = $str.chr(10);
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"e de logistica, sugerimos que mantenham controle de"';
		// $str = $str.chr(10);
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"estoque de produtos e/ou materiais de coleta,"';
		// $str = $str.chr(10);
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"solicitando-os com antecedencia. Obrigado! ***"';
		// $str = $str.chr(10);

		// 	//REMETENTE
		// 	$altura=$altura+40;
		// $str .='TEXT 10,'.$altura.',"2",0,1,1,"REMETENTE"';
		// $str = $str.chr(10);
			
		$altura=$altura+30;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"'.$row['razaosocial'].'"';
		$str = $str.chr(10);
			
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"CNPJ: '.formatarCPF_CNPJ($row['cnpj']).' - I.E: '.$row['inscestadual'].'"';
		// $str = $str.chr(10);
			
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"ROD. BR 365, KM 615 - S/N, BAIRRO ALVORADA"';
		// $str = $str.chr(10);
			
		// 	$altura=$altura+20;
		// $str .='TEXT 10,'.$altura.',"1",0,1,1,"UBERLANDIA-MG - CEP: 38.407-180 - TEL: (34) 3222-5700"';
		// $str = $str.chr(10);
			
		// 	$log.=" Item [OK] ";

		}//if($»1»u»pedido»impitem=='Y'){

		if($row['implocal']=='Y'){
			//Alterada o campo ,pl.qtdd as qtddsol, acrescentei um d pois estava trazendo o qtdsol duas vezes e o ultimo trazia dados nulos.
			//Lidiane - 08/05/2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=316551
			// Acrescentado and l.status <> 'CANCELADO' para não imprimir lotes cancelados.
			$sqlp = "SELECT 
						UPPER(CASE
									WHEN p.descrcurta = NULL OR p.descrcurta = '' THEN p.descr
									ELSE p.descrcurta
								END) AS descr,
						LENGTH(CASE
									WHEN p.descrcurta = NULL OR p.descrcurta = '' THEN p.descr
									ELSE p.descrcurta
								END) AS nometamanho,
						UPPER(SUBSTRING(CASE
										WHEN p.descrcurta = NULL OR p.descrcurta = '' THEN p.descr
										ELSE p.descrcurta
									END,
									1,
									49)) AS descrinicio,
						UPPER(SUBSTRING(CASE
										WHEN p.descrcurta = NULL OR p.descrcurta = '' THEN p.descr
										ELSE p.descrcurta
									END,
									50,
									99)) AS descrfim,
						p.codprodserv,
						p.idprodserv,
						p.un,
						p.local,
						l.partida,
						l.partidaext,
						CONCAT(l.partida, '/', l.exercicio) AS partida,
						DMA(l.fabricacao) AS dataf,
						DMA(l.vencimento) AS datav,
						l.qtddisp,
						l.status,
						pl.qtdd AS qtddsol,
						i.idnfitem,
						l.idlote,
						l.rotuloform,
						pf.volumeformula,
						pf.un,
						pt.plantel,
						SUBSTRING(SUBSTRING_INDEX(li.descr, '(', - 1),
							1,
							LOCATE(')', SUBSTRING_INDEX(li.descr, '(', - 1)) - 1) AS selo
					FROM
						nfitem i,
						prodserv p,
						lotecons pl,
						lote l
							LEFT JOIN
						prodservformula pf ON (pf.idprodservformula = l.idprodservformula)
							LEFT JOIN
						plantel pt ON (pf.idplantel = pt.idplantel)
							LEFT JOIN
						loteitem li ON (li.idlote = l.idlote
							AND li.descr LIKE ('%selo%'))
					WHERE
						l.idlote = pl.idlote
							AND p.idprodserv = i.idprodserv
							AND pl.qtdd > 0
							AND l.status <> 'CANCELADO'
							AND pl.tipoobjeto = 'nfitem'
							AND pl.idobjeto = i.idnfitem
							AND i.idnf = ".$row['idnf']." 
					UNION SELECT 
						UPPER(CASE
									WHEN p.descrcurta = NULL OR p.descrcurta = '' THEN p.descr
									ELSE p.descrcurta
								END) AS descr,
						LENGTH(CASE
									WHEN p.descrcurta = NULL OR p.descrcurta = '' THEN p.descr
									ELSE p.descrcurta
								END) AS nometamanho,
						UPPER(SUBSTRING(CASE
										WHEN p.descrcurta = NULL OR p.descrcurta = '' THEN p.descr
										ELSE p.descrcurta
									END,
									1,
									49)) AS descrinicio,
						UPPER(SUBSTRING(CASE
										WHEN p.descrcurta = NULL OR p.descrcurta = '' THEN p.descr
										ELSE p.descrcurta
									END,
									50,
									99)) AS descrfim,
						p.codprodserv,
						p.idprodserv,
						p.un,
						p.local,
						l.partida,
						l.partidaext,
						CONCAT(l.partida, '/', l.exercicio) AS partida,
						DMA(l.fabricacao) AS dataf,
						DMA(l.vencimento) AS datav,
						l.qtddisp,
						l.status,
						pl.qtd AS qtddsol,
						i.idnfitem,
						l.idlote,
						l.rotuloform,
						pf.volumeformula,
						pf.un,
						pt.plantel,
						SUBSTRING(SUBSTRING_INDEX(li.descr, '(', - 1),
							1,
							LOCATE(')', SUBSTRING_INDEX(li.descr, '(', - 1)) - 1) AS selo
					FROM
						nfitem i,
						prodserv p,
						lotereserva pl,
						lote l
							LEFT JOIN
						prodservformula pf ON (pf.idprodservformula = l.idprodservformula)
							LEFT JOIN
						plantel pt ON (pf.idplantel = pt.idplantel)
							LEFT JOIN
						loteitem li ON (li.idlote = l.idlote
							AND li.descr LIKE ('%selo%'))
					WHERE
						l.idlote = pl.idlote
							AND p.idprodserv = i.idprodserv
							AND pl.qtd > 0
							AND l.status <> 'CANCELADO'
							AND pl.status = 'PENDENTE'
							AND pl.tipoobjeto = 'nfitem'
							AND pl.idobjeto = i.idnfitem
							AND i.idnf = ".$row['idnf']."
					ORDER BY descr";
			
			$qrp = d::b()->query($sqlp) or die("Erro ao buscar itens do pedido:".mysqli_error());
			
			$mn += 8;
			$altura=$altura+40;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"-------------------------------------------"';
		$str = $str.chr(10);
				
			$altura=$altura+40;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"ITENS SOLICITADOS"';
		$str = $str.chr(10);
			
			$i=0;
			while ($rowp = mysqli_fetch_array($qrp)){
				$i = $i+1;
				
				if($rowp['nometamanho']<50){
					$mn += 3;
					$altura=$altura+30;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos(str_replace('  ', '',trim($rowp['descr']))).'" ';
		$str = $str.chr(10);
				}else{
					$mn += 6;
					$altura=$altura+30;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos(str_replace('  ', '',trim($rowp['descrinicio']))).'" ';
		$str = $str.chr(10);
					$altura=$altura+20;
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos(str_replace('  ', '',trim($rowp['descrfim']))).'" ';
		$str = $str.chr(10);
				}	
				
						
									$sqlloc="select * from lotelocalizacao lc where lc.idlote = ".$rowp['idlote']." and lc.idobjeto is not null";
									$resloc = d::b()->query($sqlloc);
									$qtdlocal=0;
									while($rowloc=mysqli_fetch_assoc($resloc)){
						
										if($rowloc['tipoobjeto']=="pessoa" and !empty($rowloc['idobjeto'])){
											$qtdlocal=1;
											$sqle="select nomecurto from pessoa where idpessoa=".$rowloc['idobjetolc'];
											$rese=d::b()->query($sqle) or die("erro ao buscar pessoa sql=".$sqle);
											$rowe=mysqli_fetch_assoc($rese);
											
											$mn += 3;
											$altura=$altura+20;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"'.retira_acentos($rowe['nomecurto']).'"';
		$str = $str.chr(10);

										}elseif($rowloc['tipoobjeto']=='tagdim' and !empty($rowloc['idobjeto'])){

												$sloc="select p.idtagdim,concat(l.descricao,' ',concat(case p.coluna 
														when 0 then '0' when 1 then 'A'	when 2 then 'B' when 3 then 'C' when 4 then 'D'	when 5 then 'E'	when 6 then 'F'
														when 7 then 'G' when 8 then 'H' when 9 then 'I' when 10 then 'J' when 11 then 'K' when 12 then 'L'
														when 13 then 'M' when 14 then 'N' when 15 then 'O' when 16 then 'P' when 17 then 'Q' when 18 then 'R'
														when 19 then 'S' when 20 then 'T' when 21 then 'U' when 22 then 'V' when 23 then 'X' when 24 then 'Z'
														end,' ',p.linha) )as campo
													from tag l,tagdim p,unidade u
													WHERE p.idtagdim= ".$rowloc['idobjeto']."
														and l.idunidade=u.idunidade
														and u.status='ATIVO'
														and u.idtipounidade in (3,21)
														and p.idtag = l.idtag";

												$rel = d::b()->query($sloc) or die("Erro ao buscar localização dos lotes:".mysqli_error(d::b())."sql=".$sloc);
												$qtdloc= mysqli_num_rows($rel);
												if($qtdloc>0){
													$qtdlocal=1;
													$rloc= mysqli_fetch_assoc($rel);
													$local=$rloc['campo'];
													$mn += 3;
													$altura=$altura+20;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"'.$local.'  "';
		$str = $str.chr(10);
												}

										}elseif(($rowloc['tipoobjeto']=='tagbotijao' or $rowloc['tipoobjeto']=='tagsala') and !empty($rowloc['idobjeto'])){

												$sloc="select idtag,concat(descricao,'- TAG ',tag) as campo
																from tag t,unidade u
																where t.idtag= ".$rowloc['idobjeto']."  
																	and t.idunidade=u.idunidade
																	and u.status='ATIVO'
																	and u.idtipounidade in (3,21)";

												$rel = d::b()->query($sloc) or die("Erro ao buscar localização botijao dos lotes:".mysqli_error(d::b())."sql=".$sloc);
												$qtdloc= mysqli_num_rows($rel);
												if($qtdloc>0){
													$qtdlocal=1;
													$rloc= mysqli_fetch_assoc($rel);
													$local=$rloc['campo'];
													$mn += 3;
													$altura=$altura+20;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"'.$local.'  "';
		$str = $str.chr(10);
												}

										}
									}  
									if( $qtdlocal==0){
										$local="Sem localização especifica.";
										$mn += 3;
										$altura=$altura+20;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"'.$local.'  "';
		$str = $str.chr(10);
									}
					$mn += 6;
					$altura=$altura+20;
					if($rowp['plantel']){
						$str .='TEXT 10,'.$altura.',"1",0,1,2,"'.$rowp['plantel'].' - '.$rowp['volumeformula'].$rowp['un'].'  "';
						$str = $str.chr(10);
						$mn += 6;
						$altura=$altura+30;
					}
					$datav = (empty($rowp['datav']))?"":" Venc:".$rowp['datav'];
		$str .='TEXT 10,'.$altura.',"2",0,1,1,"'.number_format($rowp["qtddsol"], 0, '', '.').' - '.$rowp['partida'].' - '.$rowp['partidaext'].' '.$datav.'"'; 
		$str = $str.chr(10);
		
		if(!empty($rowp["selo"])){
			$altura=$altura+20;
			$str .='TEXT 10,'.$altura.',"2",0,1,1,"Selo: '.$rowp["selo"].'"';
			$str = $str.chr(10);
		}

		
		$espacamentoTopo = 20;

		// QRCode
		if (strpos(strtolower($rowp['descr']), 'vacina') !== false) {
			$espacamentoTopo =  220;
			$altura=$altura+20;
			$str .='QRCODE 10,'.$altura.',L,7,A,0,"'.$rowp['idnfitem'].'"';
			$str = $str.chr(10);
			$mn += 25;
		}

		$altura=$altura+$espacamentoTopo;

		$altura=$altura+20;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"--------------------------------------------------------"';
		$str = $str.chr(10);
			}

			$mn += 3;
			$altura=$altura+25;
		$str .='TEXT 10,'.$altura.',"1",0,1,1,"Qtd. Item:'.$i.'"';
		$str = $str.chr(10);
			$log.=" Local [OK] ";
		}

		$cabecalho ="CLS";
		$cabecalho = $cabecalho.chr(10);
		$cabecalho.="DIRECTION 0";
		$cabecalho = $cabecalho.chr(10);
		$cabecalho.="GAP 10 mm, 0 mm";
		$cabecalho = $cabecalho.chr(10);
		$cabecalho.="SIZE 76 mm, ".$mn." mm";
		$cabecalho = $cabecalho.chr(10);

		$final = "PRINT 1";
		$final = $final.chr(10);

		//echo $mn;die;
		imprimir($cabecalho);
		$aux = explode("TEXT", $str);
		foreach($aux as $a){
			if(!empty($a)){
				imprimir("TEXT".$a);
			}
		}
		imprimir($final);

}//while($row=mysqli_fetch_assoc($res)){

if($qtdrow>0){
	//VERSIONAR A IMPRESSÃO
	/*$sql = "insert into impetiquetaped
				(idempresa,idpedido,log,criadopor,criadoem)
				(select ".$_SESSION["SESSAO"]["IDEMPRESA"].",r.idnf ,
					-- ((select count(*) from impetiquetaped e where e.idpedido = r.idnf)+1) as versao,
					'".$log."',
						'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
				from nf r
				where  r.idnf=".$idnf.")";
	//echo($sql);
	$res = d::b()->query($sql) or die("ERRO 1: ".mysqli_error()."\n SQL: ".$sql);*/
	
//	echo($sql);
}


function imprimir($strprint){
	
	//$strprint = $strprint.chr(10);
	echo($strprint);

	$data = array('content'=>$strprint,	'Send'=>' Print Test ');	

	//print_r($data); die;

	$QueryString= http_build_query($data);
	//echo("\n impressao ");
	//echo($QueryString); 

	// create context
	$context = stream_context_create(array(
			'http' => array(
					'method' => 'GET',
					'content' => $QueryString,
			),
	));
//echo "http://"._IP_IMPRESSORA_ALMOXARIFADO_ITEM."/prt_test.htm?".$QueryString; die;
	//Tratar erro quando não encontrar IP 192.168.0.48
	// send request and collect data
	$response = file_get_contents("http://"._IP_IMPRESSORA_ALMOXARIFADO_ITEM."/prt_test.htm?".$QueryString, false, $context);

}
?>