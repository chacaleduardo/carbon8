<?
require_once("functions.php");
require_once("permissao.php");
require_once("cmd.php");

require_once(__DIR__."/../../api/notifitem/notif.php");
require_once(__DIR__."/../../form/controllers/amostra_controller.php");

function getAmostra($inIdamostra){
	$sql = "SELECT 
				a.*,
				concat(a.dataamostra,' ',time(a.criadoem)) as dataamostrah,
				
				sta.subtipoamostra AS subtipoamostra,
				p.centrocusto,
				ifnull(e.nomepropriedade,p.nome) as nome,
				n.idnucleo,
				n.nucleo,
				p.razaosocial,
				(SELECT CONCAT(IFNULL(en.logradouro, ''),
						' ',IFNULL(en.endereco, ''),
						', ',IFNULL(en.numero, ''),
						', ',IF((IFNULL(en.complemento, '') <> ''),CONCAT(IFNULL(en.complemento, ''), ', '),''),
						IFNULL(en.bairro, ''),
						' - ',CONCAT(SUBSTR(en.cep, 1, 5),'-',SUBSTR(en.cep, 6, 3)),
						' - ',IFNULL(cs.cidade, ''),
						'/',IFNULL(en.uf, ''))
					FROM endereco en
						LEFT JOIN nfscidadesiaf cs ON cs.codcidade = en.codcidade
						-- LEFT JOIN tipoendereco te ON te.idtipoendereco = en.idtipoendereco
					WHERE en.status = 'ATIVO'
							AND en.idpessoa = a.idpessoa
							AND en.idtipoendereco = 6
					-- ORDER BY IF(te.tipoendereco = 'Propriedade', 0,1)
					-- LIMIT 1
					) AS enderecosacado,
				e.cnpjend AS cpfcnpj,
				e.inscest AS inscrest,
				ef.especietipofinalidade AS especietipofinalidade,
				ef.especiefinalidade AS especiefinalidade,
				ef.tipoespeciefinalidade AS tipoespeciefinalidade,
				da.valorobjeto AS valorobjeto
                
			FROM
				amostra a
				JOIN pessoa p on p.idpessoa = a.idpessoa
				LEFT JOIN dadosamostra da on da.idamostra=a.idamostra				
				JOIN subtipoamostra sta on sta.idsubtipoamostra = a.idsubtipoamostra
				LEFT JOIN nucleo n ON a.idnucleo = n.idnucleo
				LEFT JOIN vwespeciefinalidade ef ON ef.idespeciefinalidade = a.idespeciefinalidade
				left join endereco e on 
					(e.status = 'ATIVO'
					AND e.idpessoa = a.idpessoa
					AND e.idtipoendereco = 6)
			WHERE
				a.idamostra =".$inIdamostra;
	
	$res = d::b()->query($sql) or die("getAmostraResultados: Falha:\n".mysqli_error(d::b())."\n".$sql);
	
	$arrret=$res->fetch_array(MYSQLI_ASSOC) ?? [];

	$arrret['identificadores'] = AmostraController::buscarIdentificadoresDaAmostra($arrret['idamostra']);

	return $arrret;
}

function getResultados($inIdamostra){
	
	$sql = "select 
		r.*,
		a.*,
		r.status as statusresult,
		concat(a.dataamostra,' ',time(a.criadoem)) as dataamostrah,		
		sta.subtipoamostra AS subtipoamostra,
		p.centrocusto,
		p.nome,
		n.idnucleo,
		n.nucleo,
		p.razaosocial,
		(SELECT CONCAT(IFNULL(en.logradouro, ''),
				' ',IFNULL(en.endereco, ''),
				', ',IFNULL(en.numero, ''),
				', ',IF((IFNULL(en.complemento, '') <> ''),CONCAT(IFNULL(en.complemento, ''), ', '),''),
				IFNULL(en.bairro, ''),
				' - ',CONCAT(SUBSTR(en.cep, 1, 5),'-',SUBSTR(en.cep, 6, 3)),
				' - ',IFNULL(cs.cidade, ''),
				'/',IFNULL(en.uf, ''))
			FROM endereco en
				LEFT JOIN nfscidadesiaf cs force index (codcidade) ON cs.codcidade = en.codcidade
				-- LEFT JOIN tipoendereco te ON te.idtipoendereco = en.idtipoendereco
			WHERE  en.status = 'ATIVO'
					AND en.idpessoa = a.idpessoa
					AND en.idtipoendereco = 6
			-- ORDER BY IF(te.tipoendereco = 'Propriedade', 0,1)
			-- LIMIT 1
			) AS enderecosacado,
		p.cpfcnpj AS cpfcnpj,
		p.inscrest AS inscrest,
		ef.especietipofinalidade AS especietipofinalidade,
		ef.especiefinalidade AS especiefinalidade,
		ef.tipoespeciefinalidade AS tipoespeciefinalidade,
		ps.codprodserv,
		ps.descr,
                ps.modelo,
                ps.modo,
		ps.tipoespecial,
		ra.idpessoa as idassinadopor,
		MAX(ra.criadoem) as ascriadoem,
		rj.jresultado,
		sbaal.subtipoamostra
	   from amostra a
		    join resultado r on ( r.idamostra = a.idamostra)
		    JOIN pessoa p on p.idpessoa = a.idpessoa
				JOIN subtipoamostra sta on sta.idsubtipoamostra = a.idsubtipoamostra
				LEFT JOIN nucleo n ON a.idnucleo = n.idnucleo
				LEFT JOIN vwespeciefinalidade ef ON ef.idespeciefinalidade = a.idespeciefinalidade
		   join prodserv ps on (ps.idprodserv=r.idtipoteste and ps.especial = 'Y')
		   left join resultadoassinatura ra on(ra.idresultado = r.idresultado)
		   left join resultadojson rj on rj.idresultado = r.idresultado 

			LEFT JOIN resultadoamostralad ral ON ((ral.idresultado = r.idresultado))
			LEFT JOIN amostra aal ON ((ral.idamostra = aal.idamostra))
			LEFT JOIN subtipoamostra sbaal ON ((sbaal.idsubtipoamostra = aal.idsubtipoamostra))
	   where
		   a.idamostratra=  ".$inIdamostra." group by r.idresultado order by a.idregistro";
	
	$res = d::b()->query($sql) or die("getResultados: Falha:\n".mysqli_error(d::b())."\n".$stmp);
	$qtdres= mysqli_num_rows($res);
	//se era amostra no modelo antigo tra e amostra hermesp 03-05-2019
	if($qtdres<1){
		
		$sql = "select 
			r.*,
			a.*,
			r.status as statusresult,
			concat(a.dataamostra,' ',time(a.criadoem)) as dataamostrah,		
			sta.subtipoamostra AS subtipoamostra,
			p.centrocusto,
			p.nome,
			n.idnucleo,
			n.nucleo,
			p.razaosocial,
			(SELECT CONCAT(IFNULL(en.logradouro, ''),
					' ',IFNULL(en.endereco, ''),
					', ',IFNULL(en.numero, ''),
					', ',IF((IFNULL(en.complemento, '') <> ''),CONCAT(IFNULL(en.complemento, ''), ', '),''),
					IFNULL(en.bairro, ''),
					' - ',CONCAT(SUBSTR(en.cep, 1, 5),'-',SUBSTR(en.cep, 6, 3)),
					' - ',IFNULL(cs.cidade, ''),
					'/',IFNULL(en.uf, ''))
				FROM endereco en
					LEFT JOIN nfscidadesiaf cs force index (codcidade) ON cs.codcidade = en.codcidade
					-- LEFT JOIN tipoendereco te ON te.idtipoendereco = en.idtipoendereco
				WHERE  en.status = 'ATIVO'
						AND en.idpessoa = a.idpessoa
						AND en.idtipoendereco = 6
				-- ORDER BY IF(te.tipoendereco = 'Propriedade', 0,1)
				-- LIMIT 1
				) AS enderecosacado,
			p.cpfcnpj AS cpfcnpj,
			p.inscrest AS inscrest,
			ef.especietipofinalidade AS especietipofinalidade,
			ef.especiefinalidade AS especiefinalidade,
			ef.tipoespeciefinalidade AS tipoespeciefinalidade,
			ps.codprodserv,
			ps.descr,
					ps.modelo,
					ps.modo,
			ps.tipoespecial,
			ra.idpessoa as idassinadopor,
			MAX(ra.criadoem) as ascriadoem,
			rj.jresultado
		   from amostra a
				join resultado r on ( r.idamostra = a.idamostra)
				JOIN pessoa p on p.idpessoa = a.idpessoa
					JOIN subtipoamostra sta on sta.idsubtipoamostra = a.idsubtipoamostra
					LEFT JOIN nucleo n ON a.idnucleo = n.idnucleo
					LEFT JOIN vwespeciefinalidade ef ON ef.idespeciefinalidade = a.idespeciefinalidade
			   join prodserv ps on (ps.idprodserv=r.idtipoteste and ps.especial = 'Y')
			   left join resultadoassinatura ra on(ra.idresultado = r.idresultado)
			left join resultadojson rj on rj.idresultado = r.idresultado
		   where
			   a.idamostra=  ".$inIdamostra." group by r.idresultado order by a.idregistro";
		
		$res = d::b()->query($sql) or die("getResultados: Falha2:\n".mysqli_error(d::b())."\n".$stmp);
	}

	$arrColunas = mysqli_fetch_fields($res);
	$i=0;
	$arrret=array();
	while($r = mysqli_fetch_assoc($res)){
		$i=$i+1;
		//para cada coluna resultante do select cria-se um item no array
		foreach ($arrColunas as $col) {
			//$arrret[$i][$col->name]=$robj[$col->name];
			$arrret[$r["idresultado"]][$col->name]=$r[$col->name];
		}
	}
	return $arrret;
}

function getAmostraConfInputs($inUnidadePadrao){

	if(empty($inUnidadePadrao)){
		die("getAmostraConfInputs: Unidade padrão não informada");
	}
	
	$sql= "SELECT idtelaamostraconf, local, idsubtipoamostra, campo
			FROM telaamostraconf t
			WHERE idunidade=".$inUnidadePadrao."
			ORDER BY  idsubtipoamostra, idtelaamostraconf";

	$res = d::b()->query($sql) or die("getAmostraConfInputs: Erro: ".mysqli_error(d::b())."\n".$sql);
        $qtdres=mysqli_num_rows($res);
        $arrret=array();
        if($qtdres>0){
            
            $i=0;
            while ($r = mysqli_fetch_assoc($res)) {
                    //monta 2 estruturas json para finalidades (loops) diferentes
                    $arrret["arrtipo"][$r["local"]][$r["idsubtipoamostra"]][]=$r["campo"];
                    $arrret["arrcoluna"][$r["local"]][$r["campo"]][$r["idsubtipoamostra"]]=$r["idtelaamostraconf"];

            $i++;
            }
        }else{// se retornar vazio ira dar erro na tela de amostra
            $arrret["arrtipo"]['TELA'][99][]='observacaointerna';
            $arrret["arrcoluna"]['TELA']['observacaointerna'][99]=99;
        }
	
	return $arrret;
}

function getUnidadePadraoModulo($inmod,$inidempresa=null){
	if(!empty($inidempresa)){
		$sun = "select o.idunidade
				from unidadeobjeto o join unidade u on(u.idunidade = o.idunidade  and u.idempresa=".$inidempresa." and u.status='ATIVO')
			where o.idobjeto='".$inmod."' and o.tipoobjeto = 'modulo'";
	}else{
		$sun = "select o.idunidade
					from unidadeobjeto o join unidade u on(u.idunidade = o.idunidade ".getidempresa('u.idempresa',$inmod)." and u.status='ATIVO')
			where o.idobjeto='".$inmod."' and o.tipoobjeto = 'modulo'";
	}

	$resu = d::b()->query($sun);

	$r = mysqli_fetch_assoc($resu);
	return empty($r["idunidade"]) ? "" : $r["idunidade"];
}

function getUnidadePadraoMatriz($inMod,$inIdempresa,$matriz){
	
	if($matriz=="Y"){
		$sqlIdempresa = "select group_concat(idempresa) as inIdempresa from matrizconf where idmatriz= $inIdempresa";
		$resIdempresa = d::b()->query($sqlIdempresa);
		$inIdempresa = mysqli_fetch_assoc($resIdempresa)['inIdempresa'];

	}

		$getUnidade = "select o.idunidade
					from unidadeobjeto o join unidade u on(u.idunidade = o.idunidade and u.idempresa in ($inIdempresa) and u.status='ATIVO')
			where o.idobjeto= '$inMod' and o.tipoobjeto = 'modulo'";
	

	$resUn = d::b()->query($getUnidade);

	$unidades = "";
	$virg = "";

	while ($row=mysqli_fetch_assoc($resUn)){
		$unidades .=$virg.$row['idunidade'];
		$virg = ",";

	}
	return $unidades;
}


function getUnidadePadraoModuloMultiempresa($inmod,$inidempresa=null,$ignora =false){
	if(!empty($inidempresa)){
		$sun = "select o.idunidade
				from unidadeobjeto o join unidade u on(u.idunidade = o.idunidade  and u.idempresa=".$inidempresa." and u.status='ATIVO')
			where o.idobjeto='".$inmod."' and o.tipoobjeto = 'modulo'";
	}else{
		$sun = "select o.idunidade
					from unidadeobjeto o join unidade u on(u.idunidade = o.idunidade ".getidempresa('u.idempresa',$inmod,$ignora)." and u.status='ATIVO')
			where o.idobjeto='".$inmod."' and o.tipoobjeto = 'modulo'";
	}

	$resu = d::b()->query($sun);
	$virg = '';
	$idunidade='';
	while($r = mysqli_fetch_assoc($resu)){
		$idunidade .= $virg.$r['idunidade'];
		$virg=',';
	}
	return empty($idunidade) ? "" : $idunidade;
}

function getModuloResultadoPadrao($inIdUnidade){
	$sobj = "select idobjeto 
			from unidadeobjeto
			where tipoobjeto='moduloresultadoun'
			and idunidade=".$inIdUnidade;
	$res = d::b()->query($sobj);

	$r = mysqli_fetch_assoc($res);
	return $r["idobjeto"];
}

function getModuloAmostraPadrao($inIdUnidade){
	$sobj = "select idobjeto 
			from unidadeobjeto
			where tipoobjeto='moduloamostraun'
			and idunidade=".$inIdUnidade;
	$res = d::b()->query($sobj);

	$r = mysqli_fetch_assoc($res);
	return $r["idobjeto"];
}

/*
 * Recupera string json ou array com os estoques disponiveis para o produto
 */
function getEstoque($inIdProdServ,$booArray=false,$inIdObj=false,$idprodservformula=null,$idlote=null,$idpessoa=null,$status=null){

	if(empty($idlote)){
        $sqlun="select idunidade from unidade where idempresa=".cb::idempresa()." and idtipounidade = 5";
    }else{
        $sqlun="select idunidade from lote where idlote = ".$idlote;
    }
    $resun = d::b()->query($sqlun) or die("getEstoque: Falha ao buscar unidade producao:\n".mysqli_error(d::b())."\n".$sqlun);
    $rowun=mysqli_fetch_assoc($resun);
    
    if(empty($rowun['idunidade'])){
        die('Não foi possivel identificar a unidade de produção desta empresa.');
    }
    
    $idunidade=$rowun['idunidade'];
    
    if($idprodservformula==null){
	$str=" and l.status not in ('ESGOTADO','CANCELADO','REPROVADO') AND f.status='DISPONIVEL' ";
    }

    if($status!="FORMALIZACAO" and $status!="PROCESSANDO" and $status!="TRIAGEM" and $status!="AGUARDANDO" and $status!="LIBERADO"){//status diferente FORMALIZACAO informa o IDLOTE o que faz buscar somente os lotes utilizados
       // retirei por não listar as sementes utilizadas
        
	
        $strexists=" AND EXISTS (select 1 from lotecons c WHERE c.idlote = l.idlote AND c.idobjeto = ".$idlote." and c.tipoobjeto = 'lote' AND c.qtdd >0) ";
        
        
    }else{// status FORMALIZACAO IDLOTE vazio busca somente lotes com status
       // $strcons=" and l.status in('ABERTO','FORMALIZACAO','PROCESSANDO','QUARENTENA','APROVADO') ";
        $strcons="  and 
                        ( (l.status in('TRIAGEM','PROCESSANDO','QUARENTENA','APROVADO','LIBERADO') and  f.status='DISPONIVEL') or
                        exists 
                        (select * from lotecons con where con.idlote = l.idlote and con.idobjeto = ".$idlote." and con.tipoobjeto ='lote' and qtdd>0))";
    }

	$clauCriadoPor = (!$inIdObj)?"":" and l.tipoobjetosolipor='lote'
			and l.idobjetosolipor=".$inIdObj;
        
   if(empty($idpessoa))
   {
		$sql = " -- Executado por: ".$_SESSION["SESSAO"]["USUARIO"]."-".date("Y-m-d H:i:s")."-".$_SERVER["REMOTE_ADDR"]."-".$_SERVER["SCRIPT_FILENAME"]." 
			SELECT p.descr,p.unconv,p.un,l.idlote,f.idlotefracao,l.idempresa,l.idprodserv,l.idunidade,l.idloteorigem,l.tipo,
            	   l.partida,l.spartida,l.npartida,l.exercicio,l.partidaext,l.fabricante,
            	   l.piloto,sum(l.qtdprod) as qtdprod,l.qtdprod_exp,l.emissao,l.fabricacao,l.vencimento,sum(f.qtd) as qtddisp,
            	   f.qtd_exp as qtddisp_exp,sum(l.qtdpedida) as qtdpedida,l.qtdpedida_exp,sum(l.qtdajust) as qtdajust,l.qtdajust_exp,
            	   l.volumeprod,l.prioridade,l.observacao,
				   CASE
						WHEN f.status ='ESGOTADO' THEN  f.status  
						ELSE l.status 
				   END as status
            	   ,f.status as statusfr,l.situacao,l.statusao, fo.idformalizacao, st.rotulo, stl.rotulo AS rotulolote, l.criadopor,l.criadoem,l.alteradopor,l.alteradoem
			  FROM lote l JOIN prodserv p ON l.idprodserv = p.idprodserv
			  JOIN fluxostatus fsl ON fsl.idfluxostatus = l.idfluxostatus
			  JOIN "._DBCARBON."._status stl ON stl.idstatus = fsl.idstatus
			  JOIN lotefracao f ON f.idlote = l.idlote
			  LEFT JOIN formalizacao fo ON fo.idlote = l.idlote
			  LEFT JOIN fluxostatus fs ON fs.idfluxostatus = fo.idfluxostatus
			  LEFT JOIN "._DBCARBON."._status st ON st.idstatus = fs.idstatus
			 WHERE l.idprodserv = ".$inIdProdServ."
	    	   AND f.idunidade =".$idunidade." -- somente lote da producao e diagnostico autogena daniel 18042018	  
            -- AND p.especial ='N'
			   ".$str."
               ".$strcons."
               ".$clauCriadoPor."
			   ".$strexists."		 
          GROUP BY l.partida,l.exercicio
		  ORDER BY l.idlote ASC";
        
    }else{

        $sql="SELECT u.descr,unconv,un,u.idlote,u.idlotefracao,u.idempresa,u.idprodserv,u.idunidade,u.idloteorigem,u.tipo,
                     u.partida,u.spartida,u.npartida,u.exercicio,u.partidaext,u.fabricante,
                     u.piloto,sum(u.qtdprod) as qtdprod,u.qtdprod_exp,u.emissao,u.fabricacao,u.vencimento,sum(u.qtd) as qtddisp,
                     u.qtd_exp as qtddisp_exp,sum(u.qtdpedida) as qtdpedida,u.qtdpedida_exp,sum(u.qtdajust) as qtdajust,u.qtdajust_exp,
                     u.volumeprod,u.prioridade,u.observacao, u.idformalizacao, u.rotulo, u.rotulolote, 
                     CASE
                        WHEN u.statusfr ='ESGOTADO' THEN  u.statusfr  
                        ELSE u.status 
                     END as status
                     ,u.statusfr,u.situacao,u.statusao,u.criadopor,u.criadoem,u.alteradopor,u.alteradoem
                FROM (SELECT p.descr,p.unconv,p.un,f.idlotefracao,f.qtd,f.qtd_exp,f.status as statusfr, fo.idformalizacao, st.rotulo, stl.rotulo AS rotulolote, 
						l.emissao,l.fabricacao,l.vencimento,l.criadoem,l.alteradoem,l.volumeprod,l.qtdprod,l.qtddisp,
						l.qtdpedida,l.qtdajust,						
						l.idlote,l.idempresa,l.idprodserv,l.idunidade,l.idloteorigem,l.tipo,l.partida,l.spartida,l.npartida,l.exercicio,l.partidaext,l.fabricante,
						l.piloto,l.qtdprod_exp,l.qtdpedida_exp,l.qtdajust_exp,l.prioridade,l.observacao, l.status,l.situacao,l.statusao,l.criadopor,l.alteradopor
						FROM lote l JOIN lotefracao f ON f.idlote = l.idlote
						JOIN fluxostatus fsl ON fsl.idfluxostatus = l.idfluxostatus
			  			JOIN "._DBCARBON."._status stl ON stl.idstatus = fsl.idstatus
						JOIN prodserv p ON p.idprodserv = l.idprodserv
				   LEFT JOIN formalizacao fo ON fo.idlote = l.idlote
				   LEFT JOIN fluxostatus fs ON fs.idfluxostatus = fo.idfluxostatus
			  	   LEFT JOIN "._DBCARBON."._status st ON st.idstatus = fs.idstatus  
					   WHERE l.idprodserv = ".$inIdProdServ."
						 AND f.idunidade =".$idunidade." -- somente lote da producao e diagnostico autogena daniel 18042018                          
						 AND p.especial ='N'
						 AND (if(l.vencimento IS NULL, ADDDATE(l.fabricacao, INTERVAL p.validade MONTH), l.vencimento) >= now() OR l.vencimento IS NULL)
						 	 ".$str."
							 ".$strcons."
						 	 ".$clauCriadoPor."   
							  ".$strexists."		                       
					UNION 
					  SELECT p.descr,p.unconv,p.un,f.idlotefracao,f.qtd,f.qtd_exp,f.status as statusfr, fo.idformalizacao, st.rotulo, stl.rotulo AS rotulolote, 
					  l.emissao,l.fabricacao,l.vencimento,l.criadoem,l.alteradoem,l.volumeprod,l.qtdprod,l.qtddisp,
					  l.qtdpedida,l.qtdajust,						
					  l.idlote,l.idempresa,l.idprodserv,l.idunidade,l.idloteorigem,l.tipo,l.partida,l.spartida,l.npartida,l.exercicio,l.partidaext,l.fabricante,
					  l.piloto,l.qtdprod_exp,l.qtdpedida_exp,l.qtdajust_exp,l.prioridade,l.observacao, l.status,l.situacao,l.statusao,l.criadopor,l.alteradopor
						FROM lote l JOIN lotefracao f ON f.idlote = l.idlote
						JOIN fluxostatus fsl ON fsl.idfluxostatus = l.idfluxostatus
			  			JOIN "._DBCARBON."._status stl ON stl.idstatus = fsl.idstatus
						JOIN prodserv p ON p.idprodserv = l.idprodserv
				   LEFT JOIN formalizacao fo ON fo.idlote = l.idlote
				   LEFT JOIN fluxostatus fs ON fs.idfluxostatus = fo.idfluxostatus
			  	   LEFT JOIN "._DBCARBON."._status st ON st.idstatus = fs.idstatus		
					   WHERE l.idprodserv = ".$inIdProdServ."
						 AND f.idunidade =".$idunidade." -- somente lote da producao e diagnostico autogena daniel 18042018
						 AND l.idpessoa = ".$idpessoa."
						 AND p.especial ='Y'
						 AND (if(l.vencimento IS NULL, ADDDATE(l.fabricacao, INTERVAL p.validade MONTH), l.vencimento) >= now() OR l.vencimento IS NULL)
                             ".$str."
                             ".$strcons."
                             ".$clauCriadoPor."
							 ".$strexists."		 
                     UNION 
                       SELECT p.descr,p.unconv,p.un,f.idlotefracao,f.qtd,f.qtd_exp,f.status as statusfr, fo.idformalizacao, st.rotulo, stl.rotulo AS rotulolote,  
					   l.emissao,l.fabricacao,l.vencimento,l.criadoem,l.alteradoem,l.volumeprod,l.qtdprod,l.qtddisp,
					   l.qtdpedida,l.qtdajust,						
					   l.idlote,l.idempresa,l.idprodserv,l.idunidade,l.idloteorigem,l.tipo,l.partida,l.spartida,l.npartida,l.exercicio,l.partidaext,l.fabricante,
					   l.piloto,l.qtdprod_exp,l.qtdpedida_exp,l.qtdajust_exp,l.prioridade,l.observacao, l.status,l.situacao,l.statusao,l.criadopor,l.alteradopor
						 FROM lote l JOIN lotefracao f ON f.idlote = l.idlote
						 JOIN fluxostatus fsl ON fsl.idfluxostatus = l.idfluxostatus
			  			 JOIN "._DBCARBON."._status stl ON stl.idstatus = fsl.idstatus
						 JOIN prodserv p ON p.idprodserv = l.idprodserv
						 
						LEFT JOIN formalizacao fo ON fo.idlote = l.idlote
						LEFT JOIN fluxostatus fs ON fs.idfluxostatus = fo.idfluxostatus
			 			LEFT JOIN "._DBCARBON."._status st ON st.idstatus = fs.idstatus
						WHERE l.idprodserv = ".$inIdProdServ."
						  AND f.idunidade =".$idunidade." -- somente lote da producao e diagnostico autogena daniel 18042018
						  AND p.especial ='Y'
						  AND  if(l.vencimento IS NULL, ADDDATE(l.fabricacao, INTERVAL p.validade MONTH), l.vencimento) >= now()
						  AND EXISTS (select 1 from 
						  resultado r 
						  JOIN amostra a ON a.idamostra = r.idamostra  AND a.idpessoa = ".$idpessoa."
						  WHERE
						  r.idresultado = l.idobjetosolipor AND l.tipoobjetosolipor ='resultado')
                              ".$str."
							  ".$strcons."
							  ".$strexists."
                              ".$clauCriadoPor."
					UNION 
						SELECT p.descr,p.unconv,p.un,f.idlotefracao,f.qtd,f.qtd_exp,f.status as statusfr, fo.idformalizacao, st.rotulo, stl.rotulo AS rotulolote,  
						l.emissao,l.fabricacao,l.vencimento,l.criadoem,l.alteradoem,l.volumeprod,l.qtdprod,l.qtddisp,
						l.qtdpedida,l.qtdajust,						
						l.idlote,l.idempresa,l.idprodserv,l.idunidade,l.idloteorigem,l.tipo,l.partida,l.spartida,l.npartida,l.exercicio,l.partidaext,l.fabricante,
						l.piloto,l.qtdprod_exp,l.qtdpedida_exp,l.qtdajust_exp,l.prioridade,l.observacao, l.status,l.situacao,l.statusao,l.criadopor,l.alteradopor
						FROM  lote l 
						JOIN lotefracao f ON f.idlote = l.idlote 
						JOIN fluxostatus fsl ON fsl.idfluxostatus = l.idfluxostatus
			  			JOIN "._DBCARBON."._status stl ON stl.idstatus = fsl.idstatus
						JOIN prodserv p ON p.idprodserv = l.idprodserv 						
						LEFT JOIN formalizacao fo ON fo.idlote = l.idlote
						LEFT JOIN fluxostatus fs ON fs.idfluxostatus = fo.idfluxostatus
			 			LEFT JOIN "._DBCARBON."._status st ON st.idstatus = fs.idstatus 
						WHERE l.idprodserv = ".$inIdProdServ."
							AND f.idunidade =".$idunidade." -- somente lote da producao e diagnostico autogena daniel 18042018
							AND p.especial ='Y'
							AND (if(l.vencimento IS NULL, ADDDATE(l.fabricacao, INTERVAL p.validade MONTH), l.vencimento) >= now() OR l.vencimento IS NULL)
							AND EXISTS( SELECT 
											1
										FROM
											resultado r
												JOIN
											amostra a ON (a.idamostra = r.idamostra)
												JOIN
											solfabadj ad ON (ad.idpessoa = a.idpessoa)
												JOIN
											solfab sf ON (ad.idsolfab = sf.idsolfab
												AND sf.idpessoa = ".$idpessoa.")
										WHERE
											r.idresultado = l.idobjetosolipor
												AND l.tipoobjetosolipor = 'resultado')
								".$str."
								".$strcons."
								".$strexists."
								".$clauCriadoPor."
                    ) AS u  
				GROUP BY u.partida, u.exercicio
				ORDER BY u.idlote ASC;";
    }

	$res = d::b()->query($sql) or die("getEstoque: Falha ao recuperar estoque:\n".mysqli_error(d::b())."\n".$sql);

	$arrret=array();
    if (mysqli_num_rows($res) > 0){
        $arrColunas = mysqli_fetch_fields($res);
		//$colid=$arrColunas[0]->name;//"agrupar" pela primeira coluna do select
		$colid="idlote";//"agrupar" pela primeira coluna do select
		while($r = mysqli_fetch_assoc($res)){
			foreach ($arrColunas as $col) {
				$arrret[$r[$colid]][$col->name]=$r[$col->name];
			}
			$arrret[$r[$colid]]["consumosdolote"] = getConsumosDoLote($r["idlote"], $status);
		}

		if($booArray==false){
			$json = new Services_JSON();
			$strJson = $json->encode($arrret);
			return $strJson;
		}else{
			return $arrret;
		}
	}else{
		if($booArray==false){
			return "{}";
		}else{
			return array();
		}
	}
}

function getArvoreInsumosOLD($inIdProdServ,$booArray=false,$getEstoque=false,$inniveis=false){
	global $LOGGER;
	$LOGGER->log("getArvoreInsumos: ".$inIdProdServ."#".$inniveis);
	
	$sProc = "select concat(0,'_',p.idprodserv) as nopai
		, p.idprodserv as idpai
		, p.idprodserv
		, p.codprodserv
		, p.descr
		, ifnull(f.qtdpadraof,p.qtdpadrao) as qtdpadrao
		, p.estmin
		, p2.idprodserv as idprodservins
		, p2.codprodserv as codprodservins
		, p2.un as unins
		, p2.estmin as estminins
		, pi.qtdi as qtdpadraoins
		, pi.qtdi_exp as qtdpadraoexpins
		, p2.descr as descrins
		, (select count(*) from prodservinsumo pi2 where pi2.idprodserv=p2.idprodserv) as iins
	from prodserv p
	join prodservformula f on f.idprodserv=p.idprodserv
	join prodservformulains pi on pi.idprodservformula=f.idprodservformula
	join prodserv p2 on p2.idprodserv=pi.idprodserv AND f.status = 'ATIVO'
	where p.idprodserv=".$inIdProdServ." and pi.status='ATIVO' ";
	
	$res = d::b()->query($sProc) or die("getArvoreInsumos: ".mysqli_error(d::b())."\n".$sProc);

	$arrtmp=array();
	
	if (mysqli_num_rows($res) > 0){
		while ($row = mysqli_fetch_assoc($res)){

			//Este bloco desenha somente a primeira caixa, para mostrar o produto final, sendo que o segundo nível com os Filhos do produto final são passados via $row
			$arrtmp[$row["nopai"]]["nodepai"] = "";
			$arrtmp[$row["nopai"]]["idprodserv"] = $row['idprodserv'];
			$arrtmp[$row["nopai"]]["codprodserv"] = htmlentities(($row['codprodserv']));
			$arrtmp[$row["nopai"]]["qtdpadrao"] = $row['qtdpadrao'];
			$arrtmp[$row["nopai"]]["estmin"] = $row['estmin'];
			$arrtmp[$row["nopai"]]["descr"] = htmlentities(($row['descr']));
			$arrtmp[$row["nopai"]]["arrPrAtivInsumos"] = $arrPrAtivInsumos;
			//if($row['iins']>0){
				//Transfere o objeto atual para o filho, para poder fazer referencia ao parent
				$arrtmp[$row["nopai"]]["insumos"][$row['idprodserv']."_".$row['idprodservins']] = getInsumosRec($row,$getEstoque,$inniveis);
			//}
		}
		if($booArray==false){
			$json = new Services_JSON();
			$strJson = $json->encode($arrtmp);
			return $strJson;
		}else{
			return $arrtmp;
		}
	}else{
		if($booArray==false){
			return "{}";
		}else{
			return array();
		}
	}
}

$_globalNestingLevel=0;

//Recupera insumos recursivamente para montar a árvore de processos
function getInsumosRec($inRowPai,$getEstoque=false,$inniveis=false) {
	global $_globalNestingLevel, $LOGGER;
	
	//Consulta insumos. @todo: futuramente considerar mais de 1 processo por produto
	$sql = "select concat(0,'_',p.idprodserv) as nopai
		, p.idprodserv as idpai
		, p.idprodserv
		, p.codprodserv
		, p.descr
		, p2.idprodserv as idprodservins
		, p2.codprodserv as codprodservins
		, p2.un as unins
		, pi.qtdi as qtdpadraoins
		, pi.qtdi_exp as qtdpadraoexpins
		, p2.descr as descrins
		, (select count(*) from prodservinsumo pi2 where pi2.idprodserv=p2.idprodserv) as iins
	from prodserv p
	join prodservformula f on f.idprodserv=p.idprodserv
	join prodservformulains pi on pi.idprodservformula=f.idprodservformula
	join prodserv p2 on p2.idprodserv=pi.idprodserv
	where pi.status='ATIVO' and p.idprodserv=".$inRowPai["idprodservins"]."   ";

	//echo "\n\n$stmp";
    $res = d::b()->query($sql) or die("Falha ao recuperar insumos:\n".mysqli_error(d::b())."\n".$stmp);

	$arrtmp=array();
	$arrtmp["nodepai"] = $inRowPai["nopai"];
	$arrtmp["codprodserv"] = htmlentities(($inRowPai['codprodservins']));
	$arrtmp["idprodserv"] = $inRowPai['idprodservins'];
	$arrtmp["descr"] = htmlentities(($inRowPai['descrins']));
	$arrtmp["un"] = htmlentities(($inRowPai['unins']));
	$arrtmp["qtdpadrao"] = htmlentities(($inRowPai['qtdpadraoins']));
	$arrtmp["qtdpadrao_exp"] = htmlentities(($inRowPai['qtdpadraoexpins']));
	$arrtmp["estmin"] = $inRowPai['estminins'];

	$LOGGER->log("getArvoreInsumos:".$inRowPai["idprodservins"]."#codprodserv:".$inRowPai['codprodservins']."#inniveis:".$inniveis."#globalNestingLevel:".$_globalNestingLevel);
	
	if($getEstoque){
		$arrtmp["estoque"] = getEstoque($inRowPai['idprodservins'],true);
	}
	
    if (mysqli_num_rows($res) > 0){
        while ($row = mysqli_fetch_assoc($res)) {
			//Transfere para o filho os dados do pai (1 nível acima deste)
			$row["nopai"] = $inRowPai["idpai"]."_".$row["idprodserv"];
			$row["idpai"] = $row["idprodserv"];
			/*
			 * maf060617: Evitar recursividade infinita (stackoverflow) com $row["idprodserv"]!=$row['idprodservins']
			 */
			if($row["iins"]>0 and $row["idprodserv"]!=$row['idprodservins']){
				$_globalNestingLevel++;
				if($inniveis==false or $_globalNestingLevel<=$inniveis){
					//Monta o nó correspondente ao nível atual, concatenando o ID do Pai com o ID do objeto atual
					$arrtmp["insumos"][$row["idprodserv"]."_".$row["idprodservins"]] = getInsumosRec($row,$getEstoque,$inniveis);
				}
				$_globalNestingLevel--;
			}else{
				//Simplesmente adiciona a última folha sem precisar entrar no último nível. Isto é necessário porque alguns insumos que devem aparecer necessariamente na árvore não possuem insumo. ex: Semente Escherilia
				$arrtmp["insumos"][$row["idprodserv"]."_".$row["idprodservins"]]["nodepai"] = $row["nopai"];
				$arrtmp["insumos"][$row["idprodserv"]."_".$row["idprodservins"]]["codprodserv"] = $row["codprodservins"];
				$arrtmp["insumos"][$row["idprodserv"]."_".$row["idprodservins"]]["idprodserv"] = $row["idprodservins"];
				$arrtmp["insumos"][$row["idprodserv"]."_".$row["idprodservins"]]["descr"] = $row["descrins"];
				$arrtmp["insumos"][$row["idprodserv"]."_".$row["idprodservins"]]["estmin"] = $row["estminins"];
				if($row["idprodserv"]==$row['idprodservins']){//Caso idfilho==idpai indicar no json
					$arrtmp["insumos"][$row["idprodserv"]."_".$row["idprodservins"]]["erro"] = "idprodserv==idprodservins: recursividade infinita";
				}
			}
		}
	}
	return $arrtmp;
}

//buscar salas de fabricação do lote
function getAtivObjetoselsala($inIdlote,$inidLoteAtiv){
	

	$arrret=array();
	$sql="select s.idloteobj,s.idprativ,s.ativ,s.descr,s.idobjeto,s.tipoobjeto,t.tag,t.descricao
			from loteobj s,tag t
			where s.idlote = ".$inIdlote."
			and s.idloteativ=".$inidLoteAtiv."
			and t.idtagclass=2
			and t.idtag = s.idobjeto
			and s.tipoobjeto = 'tag'";
	$res = d::b()->query($sql) or die("Erro ao recuperar informações da loteobj: ".mysqli_error(d::b()));
			$arrColunas = mysqli_fetch_fields($res);
			$i=0;
			while($robj = mysqli_fetch_assoc($res)) {
				$i=$i+1;
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					$arrret[$i][$col->name]=$robj[$col->name];
				}
			}
	return $arrret;
}

//buscar equipamentos da atividade do lote
function getAtivObjetoselequip($inIdlote,$inidLoteAtiv){
	$arrret=array();
	$sql="select s.idloteobj,s.idprativ,s.ativ,s.descr,s.idobjeto,s.tipoobjeto,t.tag,t.descricao
		from loteobj s,tag t
		where s.idlote = ".$inIdlote."
		 and s.idLoteAtiv=".$inidLoteAtiv."
		and t.idtagclass=1
                 ".getidempresa('t.idempresa','tag')."
		and t.idtag = s.idobjeto
		and s.tipoobjeto = 'tag' order by t.descricao";
	$res = d::b()->query($sql) or die("[getAtivObjetoselequip]-Erro ao recuperar informações da loteobj: ".mysqli_error(d::b()));
			$arrColunas = mysqli_fetch_fields($res);
			$i=0;
			while($robj = mysqli_fetch_assoc($res)) {
				$i=$i+1;
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					$arrret[$i][$col->name]=$robj[$col->name];
				}
			}
	return $arrret;
}

//buscar testes da atividade do lote
function getAtivObjetoselteste($inIdlote,$inidLoteAtiv){
	$arrret=array();
	$sql="select s.idloteobj,s.idprativ,s.ativ,s.descr,s.idobjeto,s.tipoobjeto,s.idloteativ,s.idobjeto,
			a.idamostra,a.idregistro,a.exercicio,
			r.idresultado,r.quantidade,
			p.descr as teste,p.codprodserv
			from loteobj s 
			join amostra a on(a.idobjetosolipor=s.idloteativ and a.tipoobjetosolipor = 'loteativ')
			join resultado r on( r.idamostra = a.idamostra and r.idtipoteste = s.idobjeto)
			join prodserv p on(p.idprodserv =r.idtipoteste)
						where s.tipoobjeto = 'prodserv'
						and s.idlote =  ".$inIdlote."
						and s.idloteativ = ".$inidLoteAtiv." order by a.idregistro,s.descr";
	
	$res = d::b()->query($sql) or die("[getAtivObjetoselteste]-Erro ao recuperar informações da loteobj: ".mysqli_error(d::b()));
			$arrColunas = mysqli_fetch_fields($res);
			$i=0;
			while($robj = mysqli_fetch_assoc($res)) {
				$i=$i+1;
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					$arrret[$i][$col->name]=$robj[$col->name];
				}
			}
	return $arrret;
}

//buscar testes da atividade do lote
function getAtivObjetoselctrproc($inIdlote,$inidLoteAtiv,$intipoobjeto){
	$arrret=array();
	$sql="select s.idloteobj,s.idprativ,s.ativ,s.descr,s.idobjeto,s.tipoobjeto
			from loteobj s
			where s.idlote = ".$inIdlote."
			and s.idloteativ=".$inidLoteAtiv."
			and s.tipoobjeto = '".$intipoobjeto."' order by s.descr";
	$res = d::b()->query($sql) or die("[getAtivObjetoselctrproc]-Erro ao recuperar informações da loteobj : ".mysqli_error(d::b()));
			$arrColunas = mysqli_fetch_fields($res);
			$i=0;
			while($robj = mysqli_fetch_assoc($res)) {
				$i=$i+1;
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					$arrret[$i][$col->name]=$robj[$col->name];
				}
			}
	return $arrret;
}



//retorna os objetos selecionados na formalização
function getAtivObjetosel($inIdLoteAtiv,$inIdlote){
	$arrret=array();
	$sql="SELECT idloteobj,idprativ,ativ,descr,idobjeto,tipoobjeto,qtd,qtd_exp,ord 
				FROM loteobj s 
				where s.idlote =".$inIdlote."
				and idloteativ=".$inIdLoteAtiv;
	$res = d::b()->query($sql) or die("Erro ao recuperar informações da loteobj: ".mysqli_error(d::b()));
			$arrColunas = mysqli_fetch_fields($res);
			$i=0;
			while($robj = mysqli_fetch_assoc($res)) {
				$i=$i+1;
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					$arrret[$i][$col->name]=$robj[$col->name];
				}
			}
	return $arrret;
}

//Recupera detalhes dos Objetos relacionados às atividades de cada grupo de atividades
function getPrAtivObjetos($inIdPrAtiv,$inidloteativ,$inIdlote=null,$congelado=null){
    
	
	if(empty($congelado) or empty($inIdlote)){
        $sqlo = "select o.idprativobj
		,o.tipoobjeto
		,o.idobjeto
		,o.descr
            from prativobj o 
            where o.idprativ = ".$inIdPrAtiv." and (idobjeto is not null or descr is not null) order by o.ord";
        }else{
				$sqlo = "select o.idprativobj
			,o.tipoobjeto
			,o.idobjeto
			,o.descr
				from prativobjlote o 
				where o.idlote = ".$inIdlote."
				and o.idprativ = ".$inIdPrAtiv." and (idobjeto is not null or descr is not null) order by o.ord";
        }
	//echo "<!-- ".$sqlo." -->";
	$reso = d::b()->query($sqlo) or die("getPrAtivObjeto: Falha ao recuperar objetos: " . mysqli_error(d::b()) . "\nSQL: ".$sqlo);
		
	if(mysqli_num_rows($reso)==0){
		return array();
	}else{
		$arrret=array();
		//Sera colocado um prefixo 'o_' para não conflitar com colunas de tabelas que possuam o mesmo nome
		while ($r = mysqli_fetch_assoc($reso)) 
		{
			//Colunas padrão da prativobj
			$arrret[$r["idprativobj"]]["o_idprativ"]=$inIdPrAtiv;
			$arrret[$r["idprativobj"]]["o_idobjeto"]=$r["idobjeto"];
			$arrret[$r["idprativobj"]]["o_tipoobjeto"]=$r["tipoobjeto"];
			$arrret[$r["idprativobj"]]["o_descr"]=$r["descr"];
			
			//MAF: Colocar no SQL as colunas necessárias para utilização
			if($r["tipoobjeto"]=="tagtipo") {
				$sqlt ="select tagtipo, idtagclass, idtagclass as grupo
						from tagtipo 
						where idtagtipo = ".$r["idobjeto"];

				$sqlitens = "SELECT IF(u.status = 'LOCADO' AND tr.idobjeto IS NOT NULL, tr.idobjeto, u.idtag) AS idtag,
									IF(u.status = 'LOCADO' AND tr.idobjeto IS NOT NULL, t2.tag, u.tag) AS tag,
									IF(u.status = 'LOCADO' AND tr.idobjeto IS NOT NULL, t2.descricao, u.descricao) AS descricao,
									u.idtagtipo,
									u.idtagpai,
									GROUP_CONCAT(DISTINCT u.idtagpai SEPARATOR '#') AS idtag_pais,
									u.inputmanual
							   FROM (SELECT t.idtag,
							   				t.tag,
											t.descricao,
											t.idtagtipo,
											s.idtagpai,
											po.inputmanual,
											t.status
									   FROM tag t JOIN tagsala s ON s.idtag = t.idtag
								  LEFT JOIN prativobj po ON po.idobjeto = t.idtagtipo AND po.tipoobjeto = 'tagtipo' AND idprativ = '$inIdPrAtiv'
									  WHERE t.idtagclass = 1
									  ".share::otipo('cb::usr')::tagPorSessionIdempresa("t.idtag")."
									  	AND t.idtagtipo = ".$r["idobjeto"]."
								  UNION 
									 SELECT t.idtag,
											t.tag,
											t.descricao,
											t.idtagtipo,
											s.idtagpai,
											po.inputmanual,
											t.status
									   FROM tag t JOIN tagsala s ON s.idtag = t.idtag
								  LEFT JOIN prativobj po ON po.idobjeto = t.idtagtipo AND po.tipoobjeto = 'tagtipo' AND idprativ = '$inIdPrAtiv'
									  WHERE t.idtagclass = 2
									  ".share::otipo('cb::usr')::tagPorSessionIdempresa("t.idtag")."
									    AND t.idtagtipo = ".$r["idobjeto"]."
								  UNION
								  	 SELECT t.idtag,
											t.tag,
											t.descricao,
											t.idtagtipo,
											t.idtag AS idtagpai,
											po.inputmanual,
											t.status
									   FROM tag t LEFT JOIN prativobj po ON po.idobjeto = t.idtagtipo AND po.tipoobjeto = 'tagtipo' AND idprativ = '$inIdPrAtiv'
									  WHERE t.idtagclass = 2
									  ".share::otipo('cb::usr')::tagPorSessionIdempresa("t.idtag")."
									    AND t.idtagtipo = ".$r["idobjeto"].") AS u
								  LEFT JOIN tagreserva tr ON tr.idtag = u.idtag AND tr.objeto = 'tag'
								  LEFT JOIN tag t2 ON t2.idtag = tr.idobjeto
								  GROUP BY u.idtag
							UNION 
								 SELECT t.idtag AS idtag,
										t.tag AS tag,
										t.descricao AS descricao,
										t.idtagtipo,
										s.idtagpai,
										GROUP_CONCAT(DISTINCT s.idtagpai SEPARATOR '#') AS idtag_pais,
										po.inputmanual
								   FROM loteobj lo JOIN tag t ON t.idtag = lo.idobjeto AND lo.tipoobjeto = 'tag'
								   JOIN tagsala s ON s.idtag = t.idtag
							  LEFT JOIN prativobj po ON po.idobjeto = t.idtagtipo AND po.tipoobjeto = 'tagtipo'
								  WHERE lo.idlote = $inIdlote
							   GROUP BY t.idtag;";

			}elseif($r["tipoobjeto"]=="prodserv") {
				$sqlt ="select p.codprodserv,p.tipo,p.descr,'prodserv' as grupo
						from prodserv p
						where  p.idprodserv = ".$r["idobjeto"];
				$sqlitens=false;
			}elseif($r["tipoobjeto"]=="ctrlproc") {
				$sqlt = "select p.idprativobj,p.descr,p.inputmanual,'prativobj' as grupo, p.ord
						from prativobj p
						where p.idprativobj=".$r["idprativobj"]." order by p.ord";
				$sqlitens=false;
			}elseif($r["tipoobjeto"]=="materiais") {
				$sqlt = "select p.idprativobj,p.descr,p.inputmanual,'materiais' as grupo, p.ord
						from prativobj p
						where p.idprativobj=".$r["idprativobj"]." order by p.ord";
				$sqlitens=false;
			}elseif($r["tipoobjeto"]=="prativopcao") {
				$sqlt = "select opcao, descr,ord,tipo,textoajuda,status, ord
						from prativopcao
						where idprativopcao = ".$r["idobjeto"]." order by ord";
				$sqlitens=false;
			}else{
				$sqlt = "select ''";
				$sqlitens=false;
			}
			//echo "<!-- ".$sqlt." -->";
			$rest = d::b()->query($sqlt) or die("Erro ao recuperar informações do tipoobjeto: SQL:".$sqlt);
			$arrColunas = mysqli_fetch_fields($rest);
			while($robj = mysqli_fetch_assoc($rest)) {
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					$arrret[$r["idprativobj"]][$col->name]=$robj[$col->name];
				}

				//Recupera os subitens do objeto conforme sqldet
				if($sqlitens){
					//echo "<!-- ".$sqlitens." -->";
					$resitens = d::b()->query($sqlitens) or die("Erro ao recuperar subitens do objeto: ".mysqli_error(d::b())."\nSQL: ".$sqlitens);
					$arrColunas2 = mysqli_fetch_fields($resitens);
					$ii=0;
					while($ritem = mysqli_fetch_assoc($resitens)) {
						$ii++;
						//para cada coluna resultante do select cria-se um item no array
						foreach ($arrColunas2 as $col2) {
							$arrret[$r["idprativobj"]]["subitens"][$ii][$col2->name]=$ritem[$col2->name];
						}
					}
				}
			}
		}
		return $arrret;
	}
}

//Recupera informações da configuração de Grupos de Atividade
function getPrAtivGrupo($inIdProdServ){
	if(empty($inIdProdServ)){
		return array();
	}else{

		//Recupera o Grupo de Atividades vinculado ou o grupo de atividades padrão
		$sqlAPad = "select idprproc from (
						select c.idprproc, 0 as ord 
						from prodservprproc c 
						where  idprodserv = ".$inIdProdServ."
						union
						select idprproc, 1 as ord from prproc c where  tipo='PADRAO'
					) a
					order by ord ";		
		$resapad = d::b()->query($sqlAPad) or die("getPrAtivGrupo: Falha ao recuperar grupo de atividades padrao: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlAPad);
		
		if(mysqli_num_rows($resapad)==0){
			echo "getPrAtivGrupo: Erro: Produto sem atividade vinculada, e nenhuma atividade Padrão está configurada no sistema.";
			return false;
		}

		$rat = mysqli_fetch_assoc($resapad);
		$idprproc = $rat["idprproc"];

		//Recupera as atividades do grupo
		$sqlAtg = "select p.idprproc
						,p.proc
						,p.tipo
						,a.idprativ
						,a.ativ
						,pa.ord ordativ
					from 
						prproc p
						join prprocprativ pa on(p.idprproc=pa.idprproc)
						join prativ a on (a.idprativ = pa.idprativ)
					where p.idprproc = ".$idprproc."
					order by pa.ord";

		$resag = d::b()->query($sqlAtg);
		if(!$resag){
			echo "getPrAtivGrupo: Falha ao recuperar ativgrupo : " . mysqli_error(d::b()) . "<p>SQL: $sqlAtg";
		}
		
		$arrRet=array();
		if(mysqli_num_rows($resag)==0){
			return array();
		}else{
			while ($row = mysqli_fetch_assoc($resag)) {
				$arrRet[$row["idprproc"]]["ativgrupo"]=htmlentities(($row["proc"]));
				$arrRet[$row["idprproc"]]["tipo"]=$row["tipo"];
				$arrRet[$row["idprproc"]]["atividades"][$row["idprativ"]]["ativ"]=$row["ativ"];
				$arrRet[$row["idprproc"]]["atividades"][$row["idprativ"]]["ord"]=$row["ordativ"];
				$arrRet[$row["idprproc"]]["atividades"][$row["idprativ"]]["objetos"]=getPrAtivObjetos($row["idprativ"]);
			}
		}
		return $arrRet;
	}
}

function getAmostrasLoteAtiv($inIdloteativ)
{
	$sql = "SELECT a.exercicio
				   ,a.idregistro
				   ,a.idamostra
				   ,r.idresultado
				   ,r.idtipoteste
				   ,r.status
				   ,r.conformidade
			  FROM amostra a JOIN resultado r on r.idamostra = a.idamostra 
			  JOIN objetovinculo o ON o.idobjeto = r.idresultado AND o.tipoobjeto = 'resultado' AND o.idobjetovinc = ".$inIdloteativ." and o.tipoobjetovinc = 'loteativ'";
	$res = d::b()->query($sql) or die("getAmostrasLoteAtiv: ".mysqli_error(d::b()));
	$arrColunas = mysqli_fetch_fields($res);
	$arrret=array();
	$colid = "idtipoteste";
	while($r = mysqli_fetch_assoc($res)){
		foreach ($arrColunas as $col) {
			$arrret[$r[$colid]][$col->name]=$r[$col->name];
		}
	}
	return $arrret;
}

function getBioensaioLoteAtiv($inIdloteativ){
	$sql = "SELECT
				r.idresultado
				,b.idregistro
				,b.exercicio
				,b.idbioensaio
				,p.descr
				,r.status
			FROM bioensaio b,analise a,servicoensaio s,resultado r,prodserv p
			WHERE b.idloteativ= $inIdloteativ
			AND a.idobjeto = b.idbioensaio
			AND a.objeto = 'bioensaio'
			AND s.idobjeto = a.idanalise
			AND s.tipoobjeto ='analise'
			AND r.idservicoensaio = s.idservicoensaio
			AND p.idprodserv = r.idtipoteste";
	$res = d::b()->query($sql) or die("getBioensaioLoteAtiv: ".mysqli_error(d::b()));
	$arrColunas = mysqli_fetch_fields($res);
	$arrret=array();
	$colid="idresultado";
	while($r = mysqli_fetch_assoc($res)){
		foreach ($arrColunas as $col) {
			$arrret[$r[$colid]][$col->name]=$r[$col->name];
		}
	}
	return $arrret;
}

function getLoteAtiv($inIdlote, $congelado=null, $modulo = null){

		//Recupera as atividades do processo
		$sqlAtg = "select l.idloteativ
						 ,l.idprativ
						 ,l.ativ
    					 ,IFNULL(NULLIF(pa.nomecurtoativ, ''), l.ativ) AS nomecurtoativ
						 ,l.status
						 ,l.execucao
						 ,l.execucaofim
						 ,l.duracao
						 ,l.ord
						 ,l.impresso
						 ,pa.travasala
						 ,pa.idsgareasetor
						 ,l.loteimpressao
						 ,f.idformalizacao
						 ,l.idfluxostatus
					from loteativ l
					JOIN formalizacao f ON l.idlote = f.idlote
			   left join prativ pa on pa.idprativ=l.idprativ
					where l.idlote=".$inIdlote." 
					order by l.loteimpressao, l.ord";

		$res = d::b()->query($sqlAtg);
		if(!$res){
			echo "getLoteAtiv: Falha ao recuperar ativgrupo : " . mysqli_error(d::b()) . "<p>SQL: $sqlAtg";
		}

		if(mysqli_num_rows($res)==0){
			return array();
		}else{

			$arrColunas = mysqli_fetch_fields($res);
			$arrret=array();
			while($r = mysqli_fetch_assoc($res)){		
				//Manter o objeto json (javascript) ordenado
				$idgrp= $r["loteimpressao"]."#".$r["idloteativ"];
				foreach ($arrColunas as $col){
					$arrret[$idgrp][$col->name]=$r[$col->name];
				}

				//Pega a última atividade Pendente 
				$sqlAtiv = "SELECT MAX(idloteativ) AS idloteativ
								FROM loteativ
								WHERE idlote = '".$inIdlote."' AND status IN ('PENDENTE', 'PROCESSANDO')";

				$resAtiv = d::b()->query($sqlAtiv);
				$rowAtiv = mysqli_fetch_array($resAtiv);
				$arrret[$idgrp]["idloteativConcluir"] = $rowAtiv['idloteativ'];

				//LTM - 30-03-2021: Retorna as atividades que estão Ativas ou Pendentes para mostra-las na formalização
				if($r["idformalizacao"]){
					$status = getFluxoHistoricoIdFormalizacao($r["idformalizacao"], $r["idloteativ"], $modulo);
				}			
				
				if(!empty($status['status'])){$statusFormalizacao = $status['status'];} else {$statusFormalizacao = "SEMSTATUS";}
				$arrret[$idgrp]["statusFormalizacao"] = $statusFormalizacao;

				$arrret[$idgrp]["objetos"]=getPrAtivObjetos($r["idprativ"],$r["idloteativ"],$inIdlote,$congelado);
				$arrret[$idgrp]["amostrasRelacionadas"]=getAmostrasLoteAtiv($r["idloteativ"]);
				$arrret[$idgrp]["objetos"]["amostrasRelacionadasbioterio"]=getBioensaioLoteAtiv($r["idloteativ"]);
			}
		}
		return $arrret;
}
//registro de documento
function geraRegistrosgdoc($inTipo){
	### Inicializa a sequence para cada tipo de meio
	$sqlini = "SELECT count(*) as quant
				FROM sequence
				WHERE sequence = '".$inTipo."' and idempresa = ".cb::idempresa()."";

	$resini = d::b()->query($sqlini);
	if(!$resini){
		echo "1-Falha ao inicializar Sequence [".$inTipo."] : " . mysqli_error(d::b()) . "<p>SQL: $sqlini";
		die();
	}

	$rowini = mysqli_fetch_array($resini);
	### Caso nao exista a sequence inicializada para o tipo de meio
	if($rowini["quant"]==0){
		$sqlins = "insert into sequence  (sequence, chave1,idempresa,exercicio)
					values ('".$inTipo."',0,".cb::idempresa().",year(current_date))";

		d::b()->query($sqlins) or die("2-Falha ao inicializar Sequence [".$inTipo."] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);		
	}

	### Incrementa a sequence para o lote
	d::b()->query("LOCK TABLES sequence WRITE") or die("geraRegistrosgdoc: Falha 1 ao efetuar LOCK [sequence]: ".mysqli_error(d::b()));
	//d::b()->query("START TRANSACTION") or die("geraRegistrosgdoc: Falha 2 ao abrir transacao: ".mysqil_error());

	d::b()->query("update sequence set chave1 = (chave1 + 1) where sequence = '".$inTipo."' AND idempresa = ".cb::idempresa()."");
	## d::b()->query("COMMIT") or die("geraLote: Falha ao efetuar COMMIT [sequence update]: ".mysqli_error(d::b()));
	
	d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha ao efetuar UNLOCK normal [sequence]: ".mysqli_error(d::b()));
	
	$sql = "SELECT chave1 FROM sequence where sequence = '".$inTipo."' AND idempresa = ".cb::idempresa()."" ;

	$res =d::b()->query($sql);

	if(!$res){
		d::b()->query("UNLOCK TABLES;") or die("geraRegistrosgdoc: Falha 3 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
		echo "1-Falha Sequence [".$inTipo."] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
		die();
	}

	$row = mysqli_fetch_array($res);

	### Caso nao retorne nenhuma linha ou retorne valor vazio
	if(empty($row["chave1"])){
		
			d::b()->query("UNLOCK TABLES") or die("geraRegistrosgdoc: Falha 4 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
			d::b()->query("ROLLBACK;") or die("geraRegistrosgdoc: Falha 5 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));

			echo "2-Falha Pesquisando Sequence [".$inTipo."] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		
	}
	
	
	return $row["chave1"];
}

function geraLote($inIdprodserv,$tipo='lote'){
	### Inicializa a sequence para cada tipo de meio
	$sqlini = "SELECT count(*) as quant
				FROM sequence
				WHERE sequence = '".$tipo."'
					and exercicio = ".date("Y")."
					-- and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
					and chave1 = ".$inIdprodserv;

	$resini = d::b()->query($sqlini);
	if(!$resini){
		echo "1-Falha ao inicializar Sequence [".$tipo."] : " . mysqli_error(d::b()) . "<p>SQL: $sqlini";
		die();
	}

	$rowini = mysqli_fetch_array($resini);
	### Caso nao exista a sequence inicializada para o tipo de meio
	if($rowini["quant"]==0){
		$sqlins = "insert into sequence  (sequence, chave1,idempresa,exercicio)
					values ('".$tipo."',".$inIdprodserv.", ".$_SESSION["SESSAO"]["IDEMPRESA"].", ".date("Y").")";

		d::b()->query($sqlins) or die("2-Falha ao inicializar Sequence [".$tipo."] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);		
	}

	### Incrementa a sequence para o lote
	//d::b()->query("LOCK TABLES sequence WRITE") or die("geraLote: Falha 1 ao efetuar LOCK [sequence]: ".mysqli_error(d::b()));
	//d::b()->query("START TRANSACTION") or die("geraLote: Falha 2 ao abrir transacao: ".mysqil_error());

	d::b()->query("update sequence set chave2 = (chave2 + 1) where sequence = '".$tipo."'  and  exercicio = ".date("Y")." and chave1 = ".$inIdprodserv);
	## d::b()->query("COMMIT") or die("geraLote: Falha ao efetuar COMMIT [sequence update]: ".mysqli_error(d::b()));
	
//	d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha ao efetuar UNLOCK normal [sequence]: ".mysqli_error(d::b()));
	
	$sql = "SELECT chave2,exercicio FROM sequence where sequence = '".$tipo."'
				
				and  exercicio = year(current_date)
            -- and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and chave1 = ".$inIdprodserv;

	$res =d::b()->query($sql);

	if(!$res){
	//	d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha 3 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
		echo "1-Falha Sequence [".$tipo."] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
		die();
	}

	$row = mysqli_fetch_array($res);

	### Caso nao retorne nenhuma linha ou retorne valor vazio
	if(empty($row["chave2"]) or $row["chave2"]==0){
		if(!$resexercicio){
		//	d::b()->query("UNLOCK TABLES") or die("geraLote: Falha 4 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
		//	d::b()->query("ROLLBACK;") or die("geraLote: Falha 5 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));

			echo "2-Falha Pesquisando Sequence [lote] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		}
	}
	
	###pesquisa qual o prefixo (sigla) para compor o numero do lote
	$sql1 = "SELECT CASE
						WHEN tipo = 'SERVICO' THEN idprodserv   
						ELSE codprodserv
					END as codprodserv, descr, infprod,tipo FROM prodserv where idprodserv = ".$inIdprodserv;

	$res1 = d::b()->query($sql1);

	if(!$res1){
	//	d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha 6 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
	//	d::b()->query("ROLLBACK;") or die("geraLote: Falha 7 ao efetuar ROLLBACK [sequence]: ".mysqli_error(d::b()));

		echo "1-Falha na Sigla para o Produto [".$inIdprodserv."] : " . mysqli_error(d::b()) . "<p>SQL: $sql1";
		die();
	}

	$row1 = mysqli_fetch_array($res1);

	### Caso nao retorne nenhuma linha ou retorn valor vazio
	if(empty($row1["codprodserv"])){
	//	d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha 8 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
	//	d::b()->query("ROLLBACK;") or die("geraLote: Falha 9 ao efetuar ROLLBACK [sequence]: ".mysqli_error(d::b()));
		echo "2-Sigla nao informada no meio [".$row1["tipo"]."] : " . mysqli_error(d::b()) . "<p>SQL: $sql1";
		die();
	}

	//die($row1["sigla"].$row["chave2"]);
        
 /*       
        
            //GERA A PARTIDA          
            $inspart = new Insert();
            $inspart->setTable("partida");            
            $inspart->partida=$row1["codprodserv"].$row["chave2"];
            $inspart->exercicio=$row["exercicio"];
            $inspart->spartida=$row1["codprodserv"];
            $inspart->npartida=$row["chave2"];         
            $idpartida=$inspart->save();
*/
        

	//A sigla e a partida estão sendo armazenados em separado na tabela, para permitir diferentes usos. Por Daniel, 221117
	$arrlote = array($row1["codprodserv"],$row["chave2"], $row1["infprod"]);

	return $arrlote;
}

function geraLoteServico($inIdprodserv,$tipo='lote'){
	### Inicializa a sequence para cada tipo de meio
	$sqlini = "SELECT count(*) as quant
				FROM sequence
				WHERE sequence = '".$tipo."'
					and exercicio = year(current_date)
                                        and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
					and chave1 = ".$inIdprodserv;

	$resini = d::b()->query($sqlini);
	if(!$resini){
		echo "1-Falha ao inicializar Sequence [".$tipo."] : " . mysqli_error(d::b()) . "<p>SQL: $sqlini";
		die();
	}

	$rowini = mysqli_fetch_array($resini);
	### Caso nao exista a sequence inicializada para o tipo de meio
	if($rowini["quant"]==0){
		$sqlins = "insert into sequence  (sequence, chave1,idempresa,exercicio)
					values ('".$tipo."',".$inIdprodserv.",".$_SESSION["SESSAO"]["IDEMPRESA"].",year(current_date))";

		d::b()->query($sqlins) or die("2-Falha ao inicializar Sequence [".$tipo."] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);		
	}

	### Incrementa a sequence para o lote
	d::b()->query("LOCK TABLES sequence WRITE") or die("geraLote: Falha 1 ao efetuar LOCK [sequence]: ".mysqli_error(d::b()));
	//d::b()->query("START TRANSACTION") or die("geraLote: Falha 2 ao abrir transacao: ".mysqil_error());

	d::b()->query("update sequence set chave2 = (chave2 + 1) where sequence = '".$tipo."'  and  exercicio = year(current_date)  and chave1 = ".$inIdprodserv);
	## d::b()->query("COMMIT") or die("geraLote: Falha ao efetuar COMMIT [sequence update]: ".mysqli_error(d::b()));
	
	d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha ao efetuar UNLOCK normal [sequence]: ".mysqli_error(d::b()));
	
	$sql = "SELECT chave2,exercicio FROM sequence where sequence = '".$tipo."'
				
				and  exercicio = year(current_date)
                                and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and chave1 = ".$inIdprodserv;

	$res =d::b()->query($sql);

	if(!$res){
		d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha 3 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
		echo "1-Falha Sequence [".$tipo."] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
		die();
	}

	$row = mysqli_fetch_array($res);

	### Caso nao retorne nenhuma linha ou retorne valor vazio
	if(empty($row["chave2"]) or $row["chave2"]==0){
		if(!$resexercicio){
			d::b()->query("UNLOCK TABLES") or die("geraLote: Falha 4 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
			d::b()->query("ROLLBACK;") or die("geraLote: Falha 5 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));

			echo "2-Falha Pesquisando Sequence [lote] : " . mysqli_error(d::b()) . "<p>SQL: $sql";
			die();
		}
	}
/*	
	###pesquisa qual o prefixo (sigla) para compor o numero do lote
	$sql1 = "SELECT codprodserv, descr FROM prodserv where   idprodserv = ".$inIdprodserv;

	$res1 = d::b()->query($sql1);

	if(!$res1){
		d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha 6 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
		d::b()->query("ROLLBACK;") or die("geraLote: Falha 7 ao efetuar ROLLBACK [sequence]: ".mysqli_error(d::b()));

		echo "1-Falha na Sigla para o Produto [".$inIdprodserv."] : " . mysqli_error(d::b()) . "<p>SQL: $sql1";
		die();
	}

	$row1 = mysqli_fetch_array($res1);

	### Caso nao retorne nenhuma linha ou retorn valor vazio
	if(empty($row1["codprodserv"])){
		d::b()->query("UNLOCK TABLES;") or die("geraLote: Falha 8 ao efetuar UNLOCK [sequence]: ".mysqli_error(d::b()));
		d::b()->query("ROLLBACK;") or die("geraLote: Falha 9 ao efetuar ROLLBACK [sequence]: ".mysqli_error(d::b()));
		echo "2-Sigla nao informada no meio [".$row1["tipo"]."] : " . mysqli_error(d::b()) . "<p>SQL: $sql1";
		die();
	}
*/
	//die($row1["sigla"].$row["chave2"]);
        
   /*     
        
            //GERA A PARTIDA          
            $inspart = new Insert();
            $inspart->setTable("partida");            
            $inspart->partida=$row["chave2"];
            $inspart->exercicio=$row["exercicio"];
            //$inspart->spartida=$row1["codprodserv"];
            $inspart->npartida=$row["chave2"];         
            $idpartida=$inspart->save();

    */    

	//A sigla e a partida estão sendo armazenados em separado na tabela, para permitir diferentes usos. Por Daniel, 221117
	$arrlote = array($row["chave2"]);

	return $arrlote;
}

function geraatividadelote($inidlote,$inIdProdServFormula)
{    
	$queryd="delete from  loteformulains where idlote =".$inidlote;
	$resq=d::b()->query($queryd)or die("Erro ao inicar a formula insumo sql=".$queryd);
	
	$queryd="delete from  loteformula where idlote =".$inidlote;
	$resq=d::b()->query($queryd)or die("Erro ao inicar a formula sql=".$queryd);
	
	$queryd="delete from  prativobjlote where idlote =".$inidlote;
	$resq=d::b()->query($queryd)or die("Erro ao inicar itens da atividade sql=".$queryd);
                      
	// congelar para busca posterior pela getArvoreInsumos()
	$query="INSERT INTO loteformulains
				(idempresa,idlote,idprodservformulains,idprodserv,codprodserv,especial,fabricado,descr,descrcurta,descrgenerica,estmin,qtdpadrao,qtdpadrao_exp,un,ord,qtdi,qtdi_exp,qtdpd,qtdpd_exp,idprodservformula,rotulo,cor,idprodservpai,
				criadopor,criadoem,alteradopor,alteradoem)
				( select ".$_SESSION["SESSAO"]["IDEMPRESA"]."
					,".$inidlote."
					,i.idprodservformulains
					, i.idprodserv
					, p.codprodserv
					, p.especial
					, p.fabricado
					, p.descr
					, p.descrcurta
					, p.descrgenerica
					, p.estmin
					, ifnull(f.qtdpadraof,p.qtdpadrao)  as qtdpadrao
					, ifnull(f.qtdpadraof_exp,p.qtdpadrao_exp)  as qtdpadrao_exp
					,(case when p.unconv = null  then p.un when p.unconv = '' then p.un else p.un end) as un
					, i.ord
					, i.qtdi
					, i.qtdi_exp
					, i.qtdpd
					, i.qtdpd_exp
					, f.idprodservformula
					, f.rotulo
					, f.cor
					, f.idprodserv as idprodservpai,
					'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
			from prodservformulains i
					join prodservformula f on (f.idprodservformula=i.idprodservformula and f.status='ATIVO')
					join prodserv p on p.idprodserv=i.idprodserv
			where (f.idprodservformula = ".$inIdProdServFormula.")
			and i.status='ATIVO'
			)";
	$resquery = d::b()->query($query) or die("geraatividadelote: Falha ao gravar insumos da formula: " . mysqli_error(d::b()) . "<p>SQL: ".$query);
            
	//congelar sementes
	$query2="INSERT INTO loteformulains
		(idempresa,idlote,idprodservformulains,idprodserv,codprodserv,especial,fabricado,descr,descrcurta,descrgenerica,estmin,qtdpadrao,qtdpadrao_exp,un,ord,qtdi,qtdi_exp,qtdpd,qtdpd_exp,idprodservformula,rotulo,cor,idprodservpai,
		criadopor,criadoem,alteradopor,alteradoem)
		(select ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				,".$inidlote."
				,i.idprodservformulains
				, i.idprodserv
				, p.codprodserv
				, p.especial
				, p.fabricado
				, p.descr
				,p.descrcurta
				, p.descrgenerica
				, p.estmin
				, ifnull(f.qtdpadraof,p.qtdpadrao)  as qtdpadrao
				, ifnull(f.qtdpadraof_exp,p.qtdpadrao_exp)  as qtdpadrao_exp
				,(case when p.unconv = null  then p.un when p.unconv = '' then p.un else p.un end) as un
				, i.ord
				, i.qtdi
				, i.qtdi_exp
				, i.qtdpd
				, i.qtdpd_exp
				, f.idprodservformula
				, f.rotulo
				, f.cor
				, f.idprodserv as idprodservpai,
				'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
					from prodservformula fi
					join prodservformulains ii on (fi.idprodservformula=ii.idprodservformula)
					join prodservformula f on(f.idprodserv=ii.idprodserv)
					join prodservformulains i on (f.idprodservformula=i.idprodservformula and f.status='ATIVO')
					join prodserv p on (i.idprodserv = p.idprodserv and p.especial ='Y')
					where  fi.idprodservformula = ".$inIdProdServFormula." and i.status='ATIVO')";
	$resquery = d::b()->query($query2) or die("geraatividadelote: Falha ao gravar sementes da formula: " . mysqli_error(d::b()) . "<p>SQL: ".$query2);

	//congelar para busca posterior na getPrativInsumo();
	$query="INSERT INTO loteformula
			(idempresa,idlote,idprproc,proc,idprativ,idprocprativinsumo,idprodservprproc,idprodservformulains,idprodserv,qtdi,qtdi_exp,
			criadopor,criadoem,alteradopor,alteradoem)
			(select 
				".$_SESSION["SESSAO"]["IDEMPRESA"].",
				l.idlote,
				p.idprproc,
				p.proc,
				pai.idprativ,
				pai.idprocprativinsumo,
				pai.idprodservprproc,
				pi.idprodservformulains,
				pi.idprodserv,
				pi.qtdi,
				pi.qtdi_exp,
				'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
			from lote l
				join prodservformula f on (f.idprodserv = l.idprodserv)
				join prodservformulains pi on pi.idprodservformula=f.idprodservformula
				join procprativinsumo pai
				join prodservprproc pp
				join prproc p on p.idprproc = pp.idprproc           
			where l.idlote =".$inidlote."
			and pai.idprodservformulains=pi.idprodservformulains
			and pi.status='ATIVO'
			and pai.idprodservprproc=pp.idprodservprproc)";

	$resquery = d::b()->query($query) or die("geraatividadelote: Falha ao gravar processo e insumos da formula: " . mysqli_error(d::b()) . "<p>SQL: ".$query);

	//LTM (06/05/2021): Alterado para pegar a Atividade de acordo com o lote selecioinado
	$sqlAPad = "select pp.idprproc from prodservformula f
		    join prodservformulains pi on pi.idprodservformula=f.idprodservformula
		    join procprativinsumo pai  on  pai.idprodservformulains=pi.idprodservformulains
		    join prodservprproc pp on  pai.idprodservprproc=pp.idprodservprproc
		    where f.idprodservformula= ".$inIdProdServFormula." and pi.status='ATIVO' limit 1";
		
    $resapad = d::b()->query($sqlAPad) or die("getPrAtivGrupo: Falha ao recuperar processo ligado a formula: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlAPad);

    if(mysqli_num_rows($resapad)==0){
        
        $sqlAPad ="select s.idprproc from prodservformula f 
                    join prodserv p on(p.idprodserv = f.idprodserv and p.tipo='SERVICO')
                    join prodservprproc s on(s.idprodserv=p.idprodserv)
                    where f.idprodservformula = ".$inIdProdServFormula." limit 1";
        
        $resapad = d::b()->query($sqlAPad) or die("getPrAtivGrupo: Falha ao recuperar processo de servico: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlAPad);
         
        if(mysqli_num_rows($resapad)==0){
            echo "geraatividadelote: Erro: Produto sem processo vinculado ao produto no sistema. Verificar o cadastro do produto.";
            return false;
        }
    }

    $rat = mysqli_fetch_assoc($resapad);
    $idprproc = $rat["idprproc"];

	//Recupera as atividades do grupo
    $sqlAtg = "SELECT p.idprproc
                     ,p.proc
                     ,p.tipo
                     ,a.idprativ
                     ,a.ativ
                     ,pa.dia
					 ,a.statuspai
					 ,pa.idetapa
					 ,a.nomecurtoativ
					 ,pa.idprprocprativ
                     ,ifnull(pa.loteimpressao,0) loteimpressao
                     ,ifnull(pa.ord,0) ordativ, -- maf: estava vindo nulo e causando erro
					 pa.idfluxostatus
                FROM prproc p JOIN prprocprativ pa on(p.idprproc=pa.idprproc)
				JOIN prativ a on (a.idprativ = pa.idprativ)
               WHERE p.idprproc = ".$idprproc."
                order by pa.ord";

    $resag = d::b()->query($sqlAtg);
    if(!$resag){
        echo "geraatividadelote: Falha ao recuperar ativgrupo : " . mysqli_error(d::b()) . "<p>SQL: $sqlAtg";
	}	
    while ($row = mysqli_fetch_assoc($resag)) 
	{
        $sqlins = "INSERT INTO loteativ (idempresa,
										 idlote,
										 idprativ,
										 ativ,
										 ord,
										 dia,
										 loteimpressao, 
										 statuslote, 
										 nomecurtoativ, 
										 idetapa, 
										 idprprocprativ, 
										 idfluxostatus,
										 criadopor,
										 criadoem,
										 alteradopor,
										 alteradoem)
                                 VALUES (".$_SESSION["SESSAO"]["IDEMPRESA"].",
								 		 ".$inidlote.",
										  ".$row['idprativ'].",
										  '".$row['ativ']."',
										  ".$row['ordativ'].", 
										  if('".$row['dia']."' = '', 0, '".$row['dia']."'),
										  '".$row['loteimpressao']."', 
										  '".$row['statuspai']."', 
										  '".$row['nomecurtoativ']."', 
										  '".$row['idetapa']."', 
										  '".$row['idprprocprativ']."', 
										  '".$row['idfluxostatus']."', 
										  '".$_SESSION["SESSAO"]["USUARIO"]."',
										  sysdate(),
										  '".$_SESSION["SESSAO"]["USUARIO"]."',
										  sysdate())";

        d::b()->query($sqlins) or die("1-Falha ao inserir na [loteativ] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);
        //echo $sqlins."\n\n";

        $idloteativ= mysqli_insert_id(d::b());
        //congelar a atividade da formalizacao --hermesp 03-07-2020
        congelaAtividade($inidlote,$row['idprativ'],$idloteativ);//congelar a atividade da formalizacao
                
    }// while ($row = mysqli_fetch_assoc($resag)) {	

	//LTM (06/05/2021): Atualiza para inserir no formalizacao o idprproc para saber qual a atividade utlizada
	$sqlup = "UPDATE formalizacao set idprproc = '".$idprproc."' 
				WHERE idlote = '".$inidlote."'";
	d::b()->query($sqlup) or die("1-Falha ao atualizar na [servicoensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqlup);
	
	//HP 477936 (18/08/2021): Atualiza o rotulo da formula no lote
	$sqlrot="select concat(f.rotulo,' ',ifnull(f.dose,' '),' ',p.conteudo,' ',' (',f.volumeformula,' ',f.un,')') as rotulo
					from prodservformula f 
						join prodserv p on(p.idprodserv=f.idprodserv)
			where f.idprodservformula = ".$inIdProdServFormula;
	$resrot=d::b()->query($sqlrot) or die("Falha ao buscar rotulo da formula : ".mysqli_error(d::b())."<p>SQL: ".$sqlrot);
	$rowrot=mysqli_fetch_assoc($resrot);

	if(!empty($rowrot['rotulo'])){
		
		$sqlup = "UPDATE lote set rotuloform = '".$rowrot['rotulo']."' 
		WHERE idlote = '".$inidlote."'";
		d::b()->query($sqlup) or die("1-Falha ao atualizar na [servicoensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqlup);
	}
	

  	return "OK";
  
}

//congelar os itens da atividade - hermesp criada separada para pode ser chamada pelo bioterio
function congelaAtividade($inidlote,$inidprativ,$idloteativ){
            //congelar os itens da atividade
        $sqlins ="INSERT INTO  prativobjlote
                (idlote,idprativobj,idempresa,idprativ,idobjeto,tipoobjeto,descr,inputmanual,ord,criadopor,criadoem,alteradopor,alteradoem)
            (select ".$inidlote.",o.idprativobj,o.idempresa,o.idprativ,o.idobjeto,o.tipoobjeto,o.descr,
             o.inputmanual,o.ord,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
             from prativobj o
             where o.idprativ=".$inidprativ." and (o.idobjeto is not null or o.descr is not null))";
        
        d::b()->query($sqlins) or die("1-Falha ao inserir na [prativobjlote] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);
        
        //echo $sqlins."\n\n";
        
        /* Recuperar a sala
         * INSERÇÃO DE 1 sala por atividade
         */
        $sqls="select 
                    t.idtag					
                from prativobj p,tagtipo tt,tag t
                where p.idprativ = ".$inidprativ."
                and t.idtagtipo = tt.idtagtipo
                ".getidempresa('t.idempresa','tag')."
                and tt.idtagclass=2
                and tt.idtagtipo = p.idobjeto
                and p.tipoobjeto ='tagtipo' 
                order by t.descricao  
                ";
        $ress = d::b()->query($sqls);
        if(!$ress){
            echo "geraatividadelote: Falha ao recuperar sala : " . mysqli_error(d::b()) . "<p>SQL: $sqls";
        }
        $qtds= mysqli_num_rows($ress);
        
        if($qtds>0){
            $s=0;
           while($rows=mysqli_fetch_assoc($ress)){
               $s=$s+1;
              /*
               if($s==1){//seleciona a primeira sala
                //inserir a sala na loteobj
                $sqlins = "INSERT INTO loteobj
                        (
                            idempresa,idlote,idprativ,idloteativ,idobjeto,tipoobjeto,
                            criadopor,criadoem,alteradopor,alteradoem
                         )
                        values
                        ( 
                            ".$_SESSION["SESSAO"]["IDEMPRESA"].",".$inidlote.",".$row['idprativ'].",".$idloteativ.",".$rows['idtag'].",'tag',
                            '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
                         )";

                d::b()->query($sqlins) or die("2-Falha ao inserir na [loteobj-sala] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);
               }
                */
                //Recuperar equipamento da atividade e sala
                $sqle="select t.idtag,tc.tagclass				
                        from prativobj p,tagtipo tt,tag t,tagclass tc  ,tagsala s 
                        where p.idprativ = ".$inidprativ."                  
                        and t.idtagtipo = tt.idtagtipo 
                        and tt.idtagclass=tc.idtagclass                    
                        and tt.idtagclass=1
                        ".getidempresa('t.idempresa','tag')."
                        and s.idtag =t.idtag
                        and s.idtagpai= ".$rows['idtag']."
                        and tt.idtagtipo = p.idobjeto
                        and p.tipoobjeto ='tagtipo' 
                        order by t.descricao";
                $rese = d::b()->query($sqle);
                if(!$rese){
                    echo "geraatividadelote: Falha ao recuperar equipamento : " . mysqli_error(d::b()) . "<p>SQL: $sqle";
                }
                $qtde= mysqli_num_rows($rese);
                if($qtde>0){
                   while($rowe=mysqli_fetch_assoc($rese)){

                    //inserir a equipamento na loteobj
                    $sqlins = "INSERT INTO loteobj
                            (
                                idempresa,idlote,idprativ,idloteativ,idobjeto,tipoobjeto,
                                criadopor,criadoem,alteradopor,alteradoem
                             )
                            values
                            ( 
                                ".$_SESSION["SESSAO"]["IDEMPRESA"].",".$inidlote.",".$inidprativ.",".$idloteativ.",".$rowe['idtag'].",'tag-".$rowe['tagclass']."',
                                '".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
                             )";

                    d::b()->query($sqlins) or die("3-Falha ao inserir na [loteobj-equipamento] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);
                   }
                }//if($qtde>0){
           }//while($rows=mysqli_fetch_assoc($ress)){
        }// if($qtds>0){
    
    // INSERIR NA ATIVOBJETOSEL DEMAIS TIPOS
    $sqlins = "INSERT INTO loteobj
    (idempresa,idlote,idprativ,idloteativ,idobjeto,tipoobjeto,descr,
    criadopor,criadoem,alteradopor,alteradoem)
    (select ".$_SESSION["SESSAO"]["IDEMPRESA"].",l.idlote,p.idprativ,l.idloteativ,
    CASE p.tipoobjeto
    WHEN 'ctrlproc' THEN p.idprativobj
    WHEN 'prativobj' THEN p.idprativobj
    WHEN 'materiais' THEN p.idprativobj
    ELSE
            p.idobjeto
    END  as idobjeto,	
    p.tipoobjeto,p.descr,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
     from loteativ l, prativobj p,prativ a
    where l.idlote =".$inidlote."
    and l.idprativ=p.idprativ
    and p.tipoobjeto !='tagtipo'
    and a.idprativ = p.idprativ
    and a.idprativ=".$inidprativ."
    );";
    
    d::b()->query($sqlins) or die("4-Falha ao inserir na [loteobj] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);
 
}//function congelaAtividade(){

/*
 * Recuperar insumos com lotes que possuem ligação com clientes e que não foram esgotados
 */
function getProdutosEspeciaisNaoEsgotados($inIdProdServ,$inIdpessoa,$inIdSF=''){
	
	if(empty($inIdpessoa)){
		return array();
	}
	
	if(!empty($inIdSF)){
		$strSF=" and  ta.idsolfab=".$inIdSF." ";
	}
	
	//Recupera insumos do produto que possuem ligação com o cliente selecionado
	$sql="select l2.idlote
				, l2.idprodserv
				, l2.partida
				, l2.idobjetosolipor
				, a.idpessoa
				, a.idamostra
				, tra.idsolfab
			from prodservinsumo i2
				join lote l2 on l2.idprodserv=i2.idprodservi
				join resultado r on r.idresultado=l2.idobjetosolipor
				join amostra a on a.idamostra=r.idamostra
				join (
					select ta.idsolfab, ta.idlote, ta.status, ti.idobjeto, ti.idsolfabitem
					from solfabitem ti
						join solfab ta on (ta.idsolfab=ti.idsolfab ".$strSF." and ta.status in ('APROVADO','UNIFICADO'))
					where ti.tipoobjeto='lote'
				) tra on tra.idobjeto=l2.idlote
			where i2.idprodserv=".$inIdProdServ." -- deve ser insumo de insumo de segundo nível da formalização
				and l2.tipoobjetosolipor='resultado'
				and l2.idobjetosolipor > 0
				and l2.status!='ESGOTADO'
				and a.idpessoa=".$inIdpessoa;
	
	//echo $sql;
	$res = d::b()->query($sql) or die("Falha ao recuperar prod especiais: " . mysqli_error(d::b()) . "<p>SQL: ".$sql);
	$arrret=array();
	while($row= mysqli_fetch_assoc($res)){
		$arrret[$row["idlote"]]["idlote"]=$row["idlote"];
		$arrret[$row["idlote"]]["partida"]=$row["partida"];
		$arrret[$row["idlote"]]["idprodserv"]=$row["idprodserv"];
		$arrret[$row["idlote"]]["idresultado"]=$row["idobjetosolipor"];
		$arrret[$row["idlote"]]["idamostra"]=$row["idamostra"];
		$arrret[$row["idlote"]]["iSF"]=$row["idsolfab"];
	}
	return $arrret;
}

//buscar testes da atividade do lote
function getConsumoLote($inIdObj,$inTipoObj){
	$arrret=array();
	

	
	$sql="select c.idlotecons
			,c.qtdd
			,c.qtdd_exp
			,c.qtdc
			,c.qtdc_exp
			,c.idlotefracao
			,l.idlote
			,l.partida
			,l.exercicio
			,p.descr
			from lotecons c,lotefracao f,lote l,prodserv p
			where c.idobjeto = ".$inIdObj."
			and c.tipoobjeto = '".$inTipoObj."'			
			and f.idlotefracao = c.idlotefracao
			and f.idlote = l.idlote
			and l.idprodserv = p.idprodserv order by p.descr";
	$res = d::b()->query($sql) or die("[getConsumoLote]-Erro ao recuperar informações de consumo no lote: ".mysqli_error(d::b()));

	$arrColunas = mysqli_fetch_fields($res);
			$i=0;
			while($robj = mysqli_fetch_assoc($res)) {
				$i=$i+1;
				//para cada coluna resultante do select cria-se um item no array
				foreach ($arrColunas as $col) {
					$arrret[$robj['idlotefracao']][$col->name]=$robj[$col->name];
				}
			}

	return $arrret;
}

//participa relatório de produtos utilizados na formalizacao
function getconsumoloteproduto($inidlote,$incpde=''){
    //PED    
    $sqlu="select * from unidade where idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and idtipounidade=13 ";
    $resu = d::b()->query($sqlu) or die("getconsumoloteproduto erro ao buscar unidade de PeD: ".mysqli_error(d::b()));
    $rowu=mysqli_fetch_assoc($resu);

    $idunidade=$rowu['idunidade'];
    
    if(empty($incpde) and !empty($idunidade)){// quando não quiser o consumo do PeD
        $strped="and l.idunidade != ".$idunidade;
    }else{
        $strped="";
    }
    
        
    $sql="SELECT 
	    c.idlotecons,
	    p.idprodserv,
	    CONCAT(l.partida, '/', l.exercicio) AS partida,
	    c.qtdd,
	    c.qtdd_exp,
	    pf.volumeformula,
	    p.volumeprod,
	    ifnull(pf.qtdpadraof,p.qtdpadrao)  as qtdpadrao,
            ifnull( pf.qtdpadraof_exp,p.qtdpadrao_exp) as qtdpadrao_exp,
	   l.idprodservformula
	FROM
	    lotecons c,
	    prodserv p,
	    lote l
		LEFT JOIN
	    prodservformula pf ON (pf.idprodservformula = l.idprodservformula)
	    where l.idlote =c.idlote
	    and l.idprodserv = p.idprodserv
             ".$strped."
	    and c.idobjeto=".$inidlote." 
	    and c.tipoobjeto = 'lote' 
	    and c.qtdd <> 0 and c.qtdd <> '' and c.qtdd is not null  order by idprodserv";
    
    $res = d::b()->query($sql) or die("getconsumoloteproduto: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
	    //monta 2 estruturas json para finalidades (loops) diferentes
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["idprodserv"]=$r["idprodserv"];
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["partida"]=$r["partida"];
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["qtdd"]=$r["qtdd"];
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["qtdd_exp"]=$r["qtdd_exp"];
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["qtdpadrao"]=$r["qtdpadrao"];
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["qtdpadrao_exp"]=$r["qtdpadrao_exp"];
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["volumeformula"]=$r["volumeformula"];
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["volumeprod"]=$r["volumeprod"];
	    $arrret[$r["idprodserv"]][$r["idlotecons"]]["idprodservformula"]=$r["idprodservformula"];
		
    }
	return $arrret;    
}

function calculavolumecons($inidlote,$incpde=''){
    $arrLotecons=getconsumoloteproduto($inidlote,$incpde);
    $volume=0;
    $volumeconsf=0;
    $arrret=array();
    //print_r($arrLotecons);
    foreach ($arrLotecons as $v1) {					   
        foreach ($v1 as $v2) {						
            if(strpos(strtolower($v2['qtdd_exp']),"d")){
                $arrExp=explode('d', strtolower($v2['qtdd_exp']));
                $volumecons= $arrExp[0];						    
            }elseif(strpos(strtolower($v2['qtdd_exp']),"e")){
                $arrExp=explode('e', strtolower($v2['qtdd_exp']));
                $volumecons= $arrExp[0];						    			
            }else{
                $volumecons=$v2['qtdd'];
            }

            if(strpos(strtolower($v2['qtdpadrao_exp']),"d")){
                $arrPad=explode('d', strtolower($v2['qtdpadrao_exp']));
                $volumepradrao= $arrPad[0];						    
            }elseif(strpos(strtolower($v2['qtdpadrao_exp']),"e")){
                $arrPad=explode('e', strtolower($v2['qtdpadrao_exp']));
                $volumepradrao= $arrPad[0];						    			
            }else{
                $volumepradrao=$v2['qtdpadrao'];
            }

            if($v2['idprodservformula']){
               $volumeformula=$v2['volumeformula'];
            }else{
               $volumeformula=$v2['volumeprod'];
            }
            if($volumeformula>0){
                if($volumeformula<1){$volumeformula=0;}
                if($volumepradrao<1){$volumepradrao=1;}
                $strcalc.="[".$volumeconsf."+".$volumecons."*".$volumeformula."/".$volumepradrao."]";
                $volumeconsf=$volumeconsf + (($volumecons*$volumeformula)/$volumepradrao);
            }
        }
    }
    $arrret['strcalc']=$strcalc;
    $arrret['volumeconsf']=$volumeconsf;
    return $arrret; 
}


function listaconsumoreport($inidobj,$intipoobj)
{
	global $_1_u_lote_idlote;
	reset($arrCons);
	$arrCons=getConsumoLote($inidobj,$intipoobj);
	
	if(count($arrCons)>0)
	{
		$i=0;
		while(list($row, $obj) = each($arrCons)){		
			?>	
			<div class="row grid">
							<div class="col grupo 20 quebralinha">
							<?if($i==0){?><div class="titulogrupo">Qtd.</div><?}?>
							
							<?=recuperaExpoente($obj['qtd'],$obj['qtd_exp'])?>	
							
							</div>	
							<div class="col grupo 80 quebralinha">
							<?if($i==0){?><div class="titulogrupo">Item</div><?}?>
						
							<?=$obj['partida']?>] - <?=$obj['descr']?>
		
							</div>	
						</div>
<?
			$i++;
		}
?>							
					

<?		
	}
	
}

function getClientes(){

	$sql= "SELECT p.idpessoa
				,p.nome
				, p.centrocusto
			FROM pessoa p			
			WHERE p.status = 'ATIVO'
				AND p.idtipopessoa = 2	
".getidempresa('p.idempresa','pessoa')."			
			ORDER BY p.nome";

	$res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

	$arrret=array();
	while($r = mysqli_fetch_assoc($res)){
		//monta 2 estruturas json para finalidades (loops) diferentes
		$arrret[$r["idpessoa"]]["nome"]=$r["nome"];
		$arrret[$r["idpessoa"]]["centrocusto"]=$r["centrocusto"];
    }
	return $arrret;
}

function getCadastroInsumos(){
	$sql = "SELECT 
				p.idprodserv
				,concat(e.sigla,' - ',p.descr) as descr
				,p.especial
				,p.codprodserv
				,p.un
				,p.unconv
			from prodserv p
			join empresa e on (e.idempresa = p.idempresa)
			where p.status = 'ATIVO'
				and p.tipo='PRODUTO'
				".getidempresa('p.idempresa','prodserv')."
			order by descr";

	$res = d::b()->query($sql) or die("getCadastroInsumos: Falha:\n".mysqli_error(d::b())."\n".$stmp);

	$arrColunas = mysqli_fetch_fields($res);
	$arrret=array();
	$colid=$arrColunas[0]->name;//"agrupar" pela primeira coluna do select
	while($r = mysqli_fetch_assoc($res)){
		foreach ($arrColunas as $col) {
			$arrret[$r[$colid]][$col->name]=$r[$col->name];
		}
	}
	return $arrret;
	
}

function arvoreInsumosPara($var){
  $out = '<li>';
  foreach($var as $v){
    if(is_array($v)){
      $out .= '<ul>'.arvoreInsumosPara($v).'</ul>';
    }else{
      $out .= $v;
    }
  }
  return $out.'</li>';
}

function armazenaArvoreLote($inIdprodserv){
	if(empty($inIdprodserv)){
		die(__FUNCTION__.": idprodserv vazio");
	}else{
		$arrInsumos=getArvoreInsumos($inIdprodserv, true, false);
		print_r($arrInsumos);die;
	}
}

/*
 * Recupera listagem de todos os produtos que possuem alguma formulacao
 */
function getProdutosFormulacao($atualizaarvore = false, $idprodservs = null){
	(!$atualizaarvore)	?	$str = "" 			: $str = " and f.atualizaarvore = 'Y'";
	(!$idprodservs)		?	$idprodservs = "" 	: $idprodservs = " and p.idprodserv in (".$idprodservs.")";

	$sql = "SELECT
				p.idprodserv
				, p.idempresa
				, p.codprodserv
				, p.especial
				, p.fabricado
				, p.descr
				, p.codprodserv
				, p.estmin
				, p.un
			from (
				select distinct f.idprodserv
				from prodservformula f 
				where  f.status = 'ATIVO' ".$str.") f2
				join prodserv p on p.idprodserv=f2.idprodserv and p.tipo='PRODUTO' ".$idprodservs;

	$res = d::b()->query($sql) or die("getProdutosFormulacao: \n".mysqli_error(d::b())."\n".$sql);
	re::dis()->hMSet('cron:logprodserv',['getProdutosFormulacao' => Date('d/m/Y H:i:s') . " - " .$idprodservs." - ".$sql]);
	$str = "";
	$arrColunas = mysqli_fetch_fields($res);
	$arrret=array();
	$colid=$arrColunas[0]->name;//"agrupar" pela primeira coluna do select
	while($r = mysqli_fetch_assoc($res)){
		foreach ($arrColunas as $col) {
			$arrret[$r[$colid]][$col->name]=$r[$col->name];
		}
	}
	return $arrret;
}

/*
 * Recupera a listagem de insumos completa, em somente 1 consulta ao banco de dados: 
 * maior velocidade na montagem da árvore e otimização de recursos de conexão
 */
function getHieraquiaInsumosAutorelacionada($idprodservformula=null){
   /*
    *  esta condição busca evitar que se carregue as formulas de todos os produtos
    *  com ela sera buscado somente a formula do produto e as formulas dos produtos
    * especiais que é necessario para carregar as sementes
    */  
    /*
    if(!empty($idprodservformula)){
	$str=" and( p.especial = 'Y' or f.idprodservformula = ".$idprodservformula.") ";       
    }
    */
    if(empty($idprodservformula)){
    
	$query = "select i.idprodservformulains
					, i.idprodserv
					, p.codprodserv
					, p.especial
					, p.fabricado
					, p.descr
                                        ,p.descrcurta
					, p.descrgenerica
					, p.estmin					
                                        , ifnull(f.qtdpadraof,p.qtdpadrao) as qtdpadrao
					, ifnull(f.qtdpadraof_exp,p.qtdpadrao_exp) as qtdpadrao_exp
                                        ,(case when p.unconv = null  then p.un when p.unconv = '' then p.un else p.un end) as un
					, i.ord
					, i.qtdi
					, i.qtdi_exp
					, i.qtdpd
					, i.qtdpd_exp
					, f.idprodservformula
					, f.rotulo
					, f.cor
					, f.idprodserv as idprodservpai
				from prodservformulains i
					join prodservformula f on (f.idprodservformula=i.idprodservformula and f.status='ATIVO')
					join prodserv p on p.idprodserv=i.idprodserv
				where p.status='ATIVO'
				and i.status='ATIVO'
				    ".$str."
				order by f.idprodservformula, i.ord";
    }else{// if(empty($idprodservformula)){
        $query="select * from (
                                select i.idprodservformulains
                                                            , i.idprodserv
                                                            , p.codprodserv
                                                            , p.especial
                                                            , p.fabricado
                                                            , p.descr
                                                             ,p.descrcurta
                                                            , p.descrgenerica
                                                            , p.estmin
                                                            , ifnull(f.qtdpadraof,p.qtdpadrao) as qtdpadrao
                                                            , ifnull(f.qtdpadraof_exp,p.qtdpadrao_exp) as qtdpadrao_exp
                                                            ,(case when p.unconv = null  then p.un when p.unconv = '' then p.un else p.un end) as un
                                                            , i.ord
                                                            , i.qtdi
                                                            , i.qtdi_exp
                                                            , i.qtdpd
                                                            , i.qtdpd_exp
                                                            , f.idprodservformula
                                                            , f.rotulo
                                                            , f.cor
                                                            , f.idprodserv as idprodservpai
                                                    from prodservformulains i
                                                            join prodservformula f on (f.idprodservformula=i.idprodservformula and f.status='ATIVO')
                                                            join prodserv p on p.idprodserv=i.idprodserv
                                                    where (f.idprodservformula = ".$idprodservformula.") and i.status='ATIVO'
                                    union           
                                        select  i.idprodservformulains
                                                            , i.idprodserv
                                                            , p.codprodserv
                                                            , p.especial
                                                            , p.fabricado
                                                            , p.descr
                                                            ,p.descrcurta
                                                            , p.descrgenerica
                                                            , p.estmin
                                                            , ifnull(f.qtdpadraof,p.qtdpadrao) as qtdpadrao
                                                            , ifnull(f.qtdpadraof_exp,p.qtdpadrao_exp) as qtdpadrao_exp
                                                            ,(case when p.unconv = null  then p.un when p.unconv = '' then p.un else p.un end) as un
                                                            , i.ord
                                                            , i.qtdi
                                                            , i.qtdi_exp
                                                            , i.qtdpd
                                                            , i.qtdpd_exp
                                                            , f.idprodservformula
                                                            , f.rotulo
                                                            , f.cor
                                                            , f.idprodserv as idprodservpai
                                    from prodservformula fi
                                    join prodservformulains ii on (fi.idprodservformula=ii.idprodservformula)
                                    join prodservformula f on(f.idprodserv=ii.idprodserv)
                                    join prodservformulains i on (f.idprodservformula=i.idprodservformula and f.status='ATIVO')
                                    join prodserv p on (i.idprodserv = p.idprodserv and p.especial ='Y')
                                    where  fi.idprodservformula = ".$idprodservformula."  and i.status='ATIVO'
                        ) as u order by u.idprodservformula, u.ord";
    }// if(empty($idprodservformula)){
	$res = d::b()->query($query) or die ('getHieraquiaInsumosAutorelacionada: ' . mysqli_error(d::b()));

	$rows = array();
	while($r=mysqli_fetch_assoc($res)){ 
		$rows[]=$r;
	}
	return $rows;
}

function getHieraquiaInsumosLoteAutorelacionada($idlote){
    
    	$query = "select idprodservformulains
					, idprodserv
					, codprodserv
					, especial
					, fabricado
					, descr
					, descrcurta
					, descrgenerica
					, estmin
					, qtdpadrao
					, qtdpadrao_exp
					, un
					, ord
					, qtdi
					, qtdi_exp
					, qtdpd
					, qtdpd_exp
					, idprodservformula
					, rotulo
					, cor
					, idprodservpai
				from loteformulains
				where idlote =".$idlote." 
				order by idprodservformula, ord";
    	$res = d::b()->query($query) or die ('getHieraquiaInsumosLoteAutorelacionada: ' . mysqli_error(d::b()));

	$rows = array();
	while($r=mysqli_fetch_assoc($res)){ 
		$rows[]=$r;
	}
	return $rows;
}

/*
 * Montagem da árvore de insumos a partir da coluna idprodservformulains, que é única.
 * Isto permite montar a árvore de insumos com todas as fórmulas disponíveis, e a partir disso filtrar por idprodservformula conforme informado pelo usuário
 */
function getArvoreInsumos($parentId = 0, $getEstoque=false, $insumos = null, $idprodservformulainsPai = null, $idprodservformula = null, $idlote = null, $idpessoa = null, $status = null, $debbug = false, $qtdNivel = 0){
    $branch = array();

	if($insumos === null && ($status == "FORMALIZACAO" || $status == "PROCESSANDO" || $status=="TRIAGEM" || $status=="AGUARDANDO")){
		//Recupera a estutura de insumos completa em 1 consulta somente
		$insumos = getHieraquiaInsumosLoteAutorelacionada($idlote);
	}elseif($insumos === null && $status != "FORMALIZACAO" && $status != "TRIAGEM" && $status != "PROCESSANDO"){
		//este busca os insumos pelo congelamento da formula este foi 
		//realizado ao selecionar a formula e executo no prechange da formalizacao
		$insumos = getHieraquiaInsumosLoteAutorelacionada($idlote);
	}

    //Evita loop infinito de idprodserv já recuperado. Isto pode causar produtos sem atualizacao, futuramente
	cb::$session["arrInsumosRecuperados"][$parentId] = null;
    foreach ($insumos as $ins) {
		
        if ($ins['idprodservpai'] == $parentId) {
			if($ins['idprodserv'] !== $parentId){//Evitar stackoverflow em casos de falha de configuração: idinsumo==idproduto
				if($qtdNivel == 1 && cb::$session['inicioArvoreIdProdserv'] == $ins['idprodserv']){
					echo "Neto é igual ao Avô";
					continue;
				} else {
					$children = getArvoreInsumos($ins['idprodserv'], $getEstoque, $insumos, $ins['idprodservformulains'], $idprodservformula, $idlote, $idpessoa, $status, false, $qtdNivel + 1);
				}
			}
			
			$ins['idprodservformulainspai'] = $idprodservformulainsPai;

            if ($children) {
                $ins['insumos'] = $children;
            }

			if($getEstoque){                            
				$ins["estoque"] = getEstoque($ins['idprodserv'], true, false, $idprodservformula, $idlote, $idpessoa, $status);				
			}
			
			//String "#" evita a ordenação automática do PHP em keys numéricas, mantendo assim a configuração de ordem dos insumos
			$branch["#".$ins['idprodservformulains']] = $ins;
        }
    }

    return $branch;
}

/*
 * Armazenar configurações da árvore de insumos para cada alteração de qualquer insumo
 * Isto garante rastreamento de informações conforme a época em que foram geradas e permite consultas mais leves para montagem de árvore de insumos
 * @todo: gerar logs, porque esta função pode ser chamada em "background" e não retornar erro para a tela dos usuário
 */
function armazenaConfiguracaoArvoreInsumos($idprodservs=null, $full = true, $debug = false){
	global $JSON;

	//Produtos que possuem formulação: Fórmulas
	$aProdutosFormulacao = getProdutosFormulacao(true, $idprodservs);

	//Recupera toda a estrutura de produtos existente (pais ), em modo flat, para não executar consultas subsequentes ao DB
	$aHierarquiaInsumos = getHieraquiaInsumosAutorelacionada();
	re::dis()->hMSet('cron:logprodserv',['aHierarquiaInsumos' => Date('d/m/Y H:i:s') . " - " .$idprodservs." - Passou pela aHierarquiaInsumos"]);

	$sSqlUpdateArvore = "";
	$oSqlUpdateArvore = "";

	//Loop em cada um (todos) dos produtos
	foreach($aProdutosFormulacao as $idprodserv => $v){
		
		$tree = null;
		//Recupera informações para o primeiro nível. O restante será em modo recursivo
		$tree["#0"]["idprodserv"] = $idprodserv;
		$tree["#0"]["codprodserv"] = $v["codprodserv"];
		$tree["#0"]["especial"] = $v["especial"];
		$tree["#0"]["fabricado"] = $v["fabricado"];
		$tree["#0"]["descr"] = $v["descr"];
		$tree["#0"]["estmin"] = $v["estmin"];
		$tree["#0"]["un"] = $v["un"];

		cb::$session['inicioArvoreIdProdserv'] = $idprodserv;
		$tree["#0"]["insumos"] = getArvoreInsumos($idprodserv, false, $aHierarquiaInsumos, "0");

		re::dis()->hMSet('cron:logprodserv',['getArvoreInsumos' => Date('d/m/Y H:i:s') . " - " .$idprodservs." - full -  getArvoreInsumos"]);

		$jtree = json_encode($tree, true);
		$md5sum = md5($jtree);

		if($full){
			//Monta sql para atualização das árvores de insumos
			$oSqlUpdateArvore = "UPDATE prodserv SET jarvore = '$jtree', jarvorehash='$md5sum' WHERE idprodserv = ".$idprodserv;
			
			d::b(_DBSERVER)->query($oSqlUpdateArvore);
			mysqli_close(d::b());

			$oSqlUpdateArvore = "UPDATE prodservformula SET atualizaarvore = 'N' WHERE idprodserv = ".$idprodserv;
			d::b(_DBSERVER)->query($oSqlUpdateArvore);
			mysqli_close(d::b());

			if($debug){
			    echo PHP_EOL."###:".date("d/m h:i");
			    echo PHP_EOL.$oSqlUpdateArvore;
			}
			re::dis()->hMSet('cron:logprodserv',['full' => Date('d/m/Y H:i:s') . " - " .$idprodservs." - full -  $oSqlUpdateArvore"]);
		}
		
		if(!$full){
			$sSqlUpdateArvore = "UPDATE prodserv SET jarvore = '$jtree', jarvorehash = '$md5sum' WHERE idprodserv = ".$idprodserv;
			d::b()->query($sSqlUpdateArvore) or die("armazenaConfiguracaoArvoreInsumos[IDPRODSERV ".$idprodserv."]: ".  mysqli_error(d::b())." ".$sSqlUpdateArvore);

			$sSqlUpdateArvore = "UPDATE prodservformula SET atualizaarvore = 'N' WHERE idprodserv = ".$idprodserv;
			d::b()->query($sSqlUpdateArvore) or die("armazenaConfiguracaoArvoreInsumos[IDPRODSERV ".$idprodserv."]: ".  mysqli_error(d::b())." ".$sSqlUpdateArvore);

			re::dis()->hMSet('cron:logprodserv',['notFull' => Date('d/m/Y H:i:s') . " - " .$idprodservs." - !full -  $oSqlUpdateArvore"]);
		}
	}
}

/*
 * Recuperar a versão mais recente da árvore de insumos
 */
function getProdservarvore($inIdprodserv){

	$sql = "select * from prodservarvore 
			where idprodserv=$inIdprodserv
			order by criadoem desc limit 1";
	
	
	$res = d::b()->query($sql) or die("getProdservarvore: \n".mysqli_error(d::b())."\n".$sql);

	$arrColunas = mysqli_fetch_fields($res);
	$arrret=array();
	$colid=$arrColunas[0]->name;//"agrupar" pela primeira coluna do select
	while($r = mysqli_fetch_assoc($res)){
		foreach ($arrColunas as $col) {
			$arrret[$r[$colid]][$col->name]=$r[$col->name];
		}
	}
	return $arrret;
	
}

/*
 * Recupera recursivamente todos os lotes produzidos a partir de determinado lote informado
 */
function getLotesProduzidosPara($inIdLote){
    $branch = array();

	$query = "select *
				from lote l
				where l.tipoobjetoprodpara='lote'
				and l.idobjetoprodpara=".$inIdLote;

	$res = d::b()->query($query) or die('Erro getLotesProduzidosPara: ' . mysqli_error(d::b()));

	$arrColunas = mysqli_fetch_fields($res);
	while($r = mysqli_fetch_assoc($res)){
		//Monta o objeto de Lote
		$aLote=array();
		foreach($arrColunas as $col){
			$aLote[$col->name]=$r[$col->name];
		}
		
		//buscar os consumos do lote
		$aLote["consumosdolote"]=getConsumosDoLote($r["idlote"]);
		//Verifica recursivamente se existem Lotes filhos (produzidos para o superior)
		$aLote["lotesproduzidospara"]=getLotesProduzidosPara($r["idlote"]);

		$branch[$r["idlote"]]=$aLote;
	}

    return $branch;
}

/*
 * Recupera array com todos os consumos associados ao lote informado
 */
function getConsumosDoLote($inIdLote, $status = false){

	if($status == 'APROVADO'){
		$query = "SELECT * FROM lotecons WHERE idlote = $inIdLote AND tipoobjetoconsumoespec IN('loteativ', 'loteativespecial');";
	} else {
		$query = "SELECT lc.* 
					FROM lotecons lc JOIN lote l ON l.idlote = lc.idlote
					JOIN loteativ la ON la.idloteativ = lc.idobjetoconsumoespec AND lc.tipoobjetoconsumoespec IN('loteativ', 'loteativespecial')
					JOIN lote lla ON lla.idlote = la.idlote
			   	   WHERE lc.idlote = $inIdLote";
	}	

	$res = d::b()->query($query) or die('Erro getConsumosDoLote: ' . mysqli_error(d::b()));

	$arrColunas = mysqli_fetch_fields($res);
	$arrret=array();
	//$colid=$arrColunas[0]->name;//"agrupar" pela primeira coluna do select
	while($r = mysqli_fetch_assoc($res)){
		foreach ($arrColunas as $col) {
			$arrret[$r["tipoobjetoconsumoespec"]][$r["idobjetoconsumoespec"]][$col->name]=$r[$col->name];
		}
	}
	return $arrret;
}

function getLotesProduto($inIdprodserv){
    $branch = array();

	$query = "select *
				from lote l
				where l.idprodserv=".$inIdprodserv."
				and l.status not in('APROVADO','REPROVADO')";

	$res = d::b()->query($query) or die('Erro getLotesProduto: ' . mysqli_error(d::b()));

	$arrColunas = mysqli_fetch_fields($res);
	while($r = mysqli_fetch_assoc($res)){
		//Monta o objeto de Lote
		$aLote=array();
		foreach($arrColunas as $col){
			$aLote[$col->name]=$r[$col->name];
		}
		
		//buscar os consumos do lote
		$aLote["consumosdolote"]=getConsumosDoLote($r["idlote"]);
		//Verifica recursivamente se existem Lotes filhos (produzidos para o superior)
		$aLote["lotesproduzidospara"]=getLotesProduzidosPara($r["idlote"]);

		$branch[$r["idlote"]]=$aLote;
		
	}
	
    return $branch;
}


function AtualizaServicoensaio($iniu,$inidobj,$intipoobj,$inidobjorigem,$intipoobjorigem,$indtinicio){  
   //$inicio=$data = implode("-",array_reverse(explode("/",$indtinicio)));
    if($iniu=='i'){
        $sqlins="INSERT INTO servicoensaio (idempresa,idobjeto,tipoobjeto,servico,dia,diazero,data,status,criadopor,criadoem,alteradopor,alteradoem)
             (select ".$_SESSION["SESSAO"]["IDEMPRESA"].",".$inidobj.",'".$intipoobj."',b.servico,c.dia,c.diazero,DATE_ADD('".$indtinicio."', INTERVAL c.dia DAY) as datafim,'PENDENTE'
             ,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
             from servicobioterioconf c,servicobioterio b
             where c.idservicobioterio =b.idservicobioterio
             and c.idobjeto = ".$inidobjorigem."
             and c.tipoobjeto='".$intipoobjorigem."')";
        
        if($intipoobjorigem=='bioterioanalise'){
            $sqld="delete s.*  from servicoensaio s,servicobioterio c 
                    where s.idobjeto=".$inidobj." 
                    and s.status = 'PENDENTE'
                    and  s.tipoobjeto='bioensaio' 
                    and c.servico=s.servico 
                    and c.padrao='N'";
            d::b()->query($sqld) or die("1-Falha ao ATUALIZAR bioensaio na [servicoensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqld);
            
           
       } 
       if($intipoobj=='ficharep'){
            $sqld="delete s.*  from servicoensaio s,servicobioterio c 
                    where s.idobjeto=".$inidobj." 
                    and s.status != 'CONCLUIDO'
                    and  s.tipoobjeto='ficharep' 
                    and c.servico=s.servico";
            d::b()->query($sqld) or die("1-Falha ao ATUALIZAR  ficharep na [servicoensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqld);            
           
       }
       
        if( $intipoobjorigem=='especiefinalidade_bioensaio'){
            
            
             $sql1="INSERT INTO servicoensaio (idempresa,idobjeto,tipoobjeto,servico,dia,diazero,data,status,criadopor,criadoem,alteradopor,alteradoem)
             (select s.idempresa,b.idbioensaio,'bioensaio',s.servico,s.dia,s.diazero,s.data,s.status             
             ,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
             from bioensaio b,servicoensaio s
              where b.idbioensaio=".$inidobj."
                    and s.idobjeto = b.idficharep
                    and s.tipoobjeto = 'ficharep')";       
             //echo($sql1); die();
            $res1=d::b()->query($sql1) or die("5-Erro ao migrar serviços da ficha para o biensaio: ".mysqli_error(d::b())."<p>SQL: ".$sql1);
       
        }

      // die($sqlins);
        d::b()->query($sqlins) or die("1-Falha ao inserir na [servicoensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqlins);
        
        $sql1="select max(data) as mdata from servicoensaio s where s.idobjeto = ".$inidobj." and s.tipoobjeto='bioensaio'";
        $res1=d::b()->query($sql1) or die("2-Falha ao buscar a maior data de serviço : ".mysqli_error(d::b())."<p>SQL: ".$sql1);
        $row1=mysqli_fetch_assoc($res1);

        $sqlu1="update servicoensaio s 
               set s.data='".$row1['mdata']."',s.dia=datediff('".$row1['mdata']."','".$indtinicio."') 
               where ( s.data<'".$row1['mdata']."' or s.dia!=datediff('".$row1['mdata']."','".$indtinicio."')  )
                and s.idobjeto = ".$inidobj." 
                and s.tipoobjeto='bioensaio' and servico='ABATE'";      
        $resu1=d::b()->query($sqlu1) or die("3-Erro ao atualizar o ultimo servico  ABATE: ".mysqli_error(d::b())."<p>SQL: ".$sqlu1);
            
    }elseif($iniu=='u'){        
         $sqlup="update servicoensaio set data=DATE_ADD('".$indtinicio."', INTERVAL dia DAY)
             where idobjeto = ".$inidobj."
             and tipoobjeto='".$intipoobj."'";

        //die($sqlins);
        d::b()->query($sqlup) or die("1-Falha ao atualizar na [servicoensaio] : ".mysqli_error(d::b())."<p>SQL: ".$sqlup);

    }
   return "OK";
}


function BioensaioCorrigeDzero($idservicoensaio){
   


    if( empty($idservicoensaio)){
            die("Dados necessários não informados.");
    }


    $sql1="select  s.data,s.idobjeto as idbioensaio,b.idbioensaioctr
                            from servicoensaio s,bioensaio b
                            where b.idbioensaio=s.idobjeto
                            and s.status !='OFFLINE'
                            and s.tipoobjeto = 'bioensaio'
                            and s.idservicoensaio =".$idservicoensaio;
    $res1= d::b()->query($sql1) or die("BioensaioCorrigeDzero: Erro ao buscar ensaio :\n".mysqli_error(d::b())."\n".$sql1);
    $row1=mysqli_fetch_assoc($res1);
	
	
    $sql="select s.* from servicoensaio s 
                    where s.idobjeto = ".$row1['idbioensaio']." 
                    and s.tipoobjeto ='bioensaio'
                    and s.status !='OFFLINE'
                    and s.diazero = 'Y' order by s.data";
    $res= d::b()->query($sql) or die("BioensaioCorrigeDzero: Erro ao buscar diazero :\n".mysqli_error(d::b())."\n".$sql);

    $qtdiazero=mysqli_num_rows($res);	

    if($qtdiazero<1){

        $sqlup="update servicoensaio s
                        set  s.dia  =  null
                        where s.tipoobjeto ='bioensaio'
                        and  s.idobjeto =".$row1['idbioensaio'];
        $resup=d::b()->query($sqlup) or die("BioensaioCorrigeDzero: Erro-1 ao alterar as datas :\n".mysqli_error(d::b())."\n".$sqlup);
      
        //echo($sqlup." nao tem dia zero");
 
    }else{
	
        while($row=mysqli_fetch_assoc($res)){

            $sqlup="update servicoensaio s
                    set  s.dia  =  DATEDIFF(s.data,'".$row['data']."')
                    where s.data >= '".$row['data']."'
                    and s.diazero = 'N'
                    and s.status !='OFFLINE'
                    and s.tipoobjeto='bioensaio'
                    and  s.idobjeto =".$row1['idbioensaio'];
            $resup=d::b()->query($sqlup) or die("BioensaioCorrigeDzero: Erro-2 ao alterar as datas  :\n".mysqli_error(d::b())."\n".$sqlup);


        }
        $sqlup="update servicoensaio s
                        set  s.dia  =  '0'
                        where  s.diazero = 'Y'
                        and s.status !='OFFLINE'
                        and  s.idservicoensaio =".$idservicoensaio;
        $resup=d::b()->query($sqlup) or die("BioensaioCorrigeDzero: Erro-3 ao alterar as datas  :\n".mysqli_error(d::b())."\n".$sqlup);
    }
	
	//atulizar o controle quando tiver
    if(!empty($row1['idbioensaioctr'])){	
        $resc = d::b()->query("call proc_servicoensaio(".$row1['idbioensaio'] .");");
    }

    return "OK";
}



/*
 * Gerar uma amostra para um servico do bioensaio
 * CHAMADA NO SAVEPRECHANGE_BIOENSAIO.PHP
 */
function geraamostra($intipoobjeto,$inidobjeto){
	
    require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
	 

    $criadopor = $_SESSION["SESSAO"]["USUARIO"];
    
    if($intipoobjeto=='servicoensaio'){
    $idservicoensaio=$inidobjeto;
    }
     
    $sqlU = "select * from unidade where idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and idtipounidade=5 and status='ATIVO' ";

    $resU =  d::b()->query($sqlU) or die("Erro ao buscar se existe amostra para o servico sql=".$sqlU);
    $rowU = mysqli_fetch_assoc($resU);
    
    $idunidade=$rowU['idunidade'];
    if(empty($idunidade)){
        die("ID unidade produção não encontrado.");
    }

    if(empty($idservicoensaio)){
            die("Serviço ensaio não informado.");
    }


    $sql = "SELECT idamostra
                            FROM servicoensaio
                            where idservicoensaio= ".$idservicoensaio;

    $res =  d::b()->query($sql) or die("Erro ao buscar se existe amostra para o servico sql=".$sql);
    $row = mysqli_fetch_assoc($res);

    if(empty($row['idamostra'])){

        //BUSCAR DADOS PARA INSERIR NA AMOSTRA
        $sqlam="select b.idnucleo,b.idpessoa,b.exercicio,b.estudo,b.partida,sysdate() as criadoem,sb.idsubtipoamostra,s.data,f.idespeciefinalidade
                from bioensaio b,servicoensaio s,servicobioterio sb,ficharep f
                where sb.servico=s.servico
                and b.idbioensaio = s.idobjeto
                and s.tipoobjeto='bioensaio'
                and f.idficharep=b.idficharep
                and s.idservicoensaio =".$idservicoensaio;

        $resam= d::b()->query($sqlam) or die("Erro ao buscar amostra sql".$sqlam);
        $rowam=mysqli_fetch_assoc($resam);

        IF(empty($rowam['idsubtipoamostra'])){
                die("Configurar tipo e subtipo da amostra em cadastro - tecnico- servico do bioterio");
        }

        //BUSCAR A IDADE PARA INSERIR NA AMOSTRA
        $sqlal="select datediff(ss.data, s.data) as idade,s.data,b.idnucleo
                                        from servicoensaio s,servicoensaio ss,bioensaio b,ficharep f
                                        where s.servico = 'TRANSFERENCIA'
                                        and s.tipoobjeto = 'ficharep'
                                        and  s.idobjeto =b.idficharep
                                        and b.idbioensaio= ss.idobjeto
                                        and ss.tipoobjeto='bioensaio'
                                        and ss.idservicoensaio=".$idservicoensaio;
        $resal= d::b()->query($sqlal) or die("Erro ao verificar se data e maior que o abate sql=".$sqlal);
        $rowal=mysqli_fetch_assoc($resal);

		//LTM - 13-04-2021: Retorna o Idfluxo ContaPagar
		$idfluxostatus = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);

        //GERA A AMOSTRA
		$arrReg=geraIdregistro($_SESSION["SESSAO"]["IDEMPRESA"],$idunidade);
		$insamostra = new Insert();
		$insamostra->setTable("amostra");
		$insamostra->idunidade=$idunidade;
		$insamostra->status = 'ABERTO';
		$insamostra->idfluxostatus = $idfluxostatus;
		$insamostra->idregistro=$arrReg['idregistro'];
		$insamostra->exercicio=$arrReg["exercicio"];
		$insamostra->idespeciefinalidade=$rowam["idespeciefinalidade"];
		$insamostra->idsubtipoamostra=$rowam['idsubtipoamostra'];
		$insamostra->idpessoa=$rowam['idpessoa'];
		$insamostra->idnucleo=$rowam['idnucleo'];
		$insamostra->tipoidade='Dia(s)';
		$insamostra->idade=$rowal['idade'];
		$insamostra->dataamostra=$rowam['data'];
		$insamostra->nucleoamostra=$rowam['estudo'];
		$insamostra->partida=$rowam['partida'];
		//$insamostra->lote=$rowam['partida'];
		$insamostra->tipoobjetosolipor="servicoensaio";
		$insamostra->idobjetosolipor=$idservicoensaio;
		$insamostra->estexterno=$rowam['estudo'];
		$idamostra=$insamostra->save();

		//LTM - 13-04-2021: Insere FluxoHist Amostra
		$modulo = getModuloPadrao('amostra', $idunidade); 
		FluxoController::inserirFluxoStatusHist($modulo, $idamostra, $fluxostatus['idfluxostatus'], 'PENDENTE');

        $sqlu="update servicoensaio set idamostra =".$idamostra."
                                        where idservicoensaio =".$idservicoensaio;
         d::b()->query($sqlu) or die("erro ao atualizar amostra no servicoensaio sql=".$sqlu);

        return($idamostra);

    }else{
        return($row['idamostra']);
    }   
}

function geraAmostrasRelacionadasAoLote($inidlote, $fluxo = null)
{
	if(!isset($fluxo))
	{
		require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
		 
	}

	//Recuperar testes que não existem mas foram marcados pelo usuário da formalização
	$sqlt = "SELECT la.idloteativ
				  , lo.idobjeto
				  , p.idsubtipoamostra
				  , e.idpessoaform
				  , l.idpessoa
				  , l.tipo
				  , ifnull(ps.descrcurta,ps.descr) as descr
				  , l.idempresa
			   FROM lote l JOIN loteativ la ON la.idlote = l.idlote
			   JOIN loteobj lo ON lo.idloteativ = la.idloteativ AND lo.tipoobjeto = 'prodserv'
			   JOIN prativ p ON la.idprativ = p.idprativ 
			   JOIN empresa e ON e.idempresa = l.idempresa
			   JOIN prodserv ps ON ps.idprodserv = l.idprodserv
			  WHERE l.idlote = $inidlote
				AND NOT EXISTS (SELECT 1 FROM objetovinculo WHERE idobjetovinc = la.idloteativ AND tipoobjetovinc = 'loteativ')";

	$rt = d::b()->query($sqlt) or die("geraAmostrasRelacionadasAoLote: ".mysqli_error(d::b()));
	$qtdrt=mysqli_num_rows($rt);
	$arrAtivAmostras=array();
	$arrConfAmostras=array();    
               
	while($r = mysqli_fetch_assoc($rt))
	{
		$arrAtivAmostras[$r["idloteativ"]][$r["idamostra"]][$r["idobjeto"]]=$r["idobjeto"];
		if($r["tipo"]=='PRODUTO'){
			$arrConfAmostras[$r["idloteativ"]]["idpessoa"]=$r["idpessoaform"];
		}else{
			$arrConfAmostras[$r["idloteativ"]]["idpessoa"]=$r["idpessoa"];
		}                
		$arrConfAmostras[$r["idloteativ"]]["idsubtipoamostra"]=$r["idsubtipoamostra"];
		$arrConfAmostras[$r["idloteativ"]]["descricao"]=$r["descr"];
		$idempresaLote = $r["idempresa"];
	}
          
	if($qtdrt>0){
		$sqlu="select * from unidade where idtipounidade = 7 AND idempresa = ".cb::idempresa()." and status='ATIVO'";
		$resu = d::b()->query($sqlu) or die("geraAmostrasRelacionadasAoLote erro ao buscar unidade de CQ: ".mysqli_error(d::b()));
		$rowu=mysqli_fetch_assoc($resu);
		
		$idunidade=$rowu['idunidade'];
		
		if(empty($idunidade)){
			die("A unidade de CQ da empresa não esta configurada para empresa.");
		}
		
		//Verificar unidade correta
		//$idunidade=2;

		//LTM - 13-04-2021: Retorna o Idfluxo ContaPagar
		$idfluxostatusAmostra = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);
		$idfluxostatusResultado = FluxoController::getIdFluxoStatus('resultado', 'ABERTO', $idunidade);

		//Atividade: Criar somente 1 amostra para cada atividade. Caso exista, reutilizar
		foreach($arrAtivAmostras as $idloteativ => $arramostra)
		{
			//Amostra: Caso nulo, criar nova amostra
			foreach($arramostra as $idamostra => $arrprodserv)
			{
				if(!in_array($arrConfAmostras[$idloteativ]["idsubtipoamostra"], $subtipoamostra))
				{
					if(empty($arrConfAmostras[$idloteativ]["idsubtipoamostra"])){
						$idsubtipoamostra=49;
					}else{
						$idsubtipoamostra=$arrConfAmostras[$idloteativ]["idsubtipoamostra"];
					} 
					if(empty($arrConfAmostras[$idloteativ]["idpessoa"])){
						die("Configurar no cadastro de empresa a empresa de ordem de produção.");
					}

					//Gerar nova amostra
					$arrReg=geraIdregistro($_SESSION["SESSAO"]["IDEMPRESA"],$idunidade);
					$insamostra = new Insert();
					$insamostra->setTable("amostra");
					$insamostra->idpessoa = $arrConfAmostras[$idloteativ]["idpessoa"];//INATA 3019
					$insamostra->descricao = $arrConfAmostras[$idloteativ]["descricao"];
					$insamostra->idunidade = $idunidade;
					$insamostra->status = 'ABERTO';
					$insamostra->idfluxostatus = $idfluxostatusAmostra;
					$insamostra->dataamostra = sysdate();
					//$insamostra->idtipoamostra=15;///////////////////////////?
					$insamostra->idsubtipoamostra = $idsubtipoamostra;
					$insamostra->lote = $_SESSION['arrpostbuffer']['1']['u']['lote']['partida']."/".$_SESSION['arrpostbuffer']['1']['u']['lote']['exercicio'];
					//$insamostra->tipoobjetosolipor = "loteativ";
					//$insamostra->idobjetosolipor = $idloteativ;
					$insamostra->idempresa = $idempresaLote;
					$insamostra->exercicio = $arrReg["exercicio"];
					$insamostra->idregistro = $arrReg["idregistro"];
					$idamostraOrig = $insamostra->save();

					//LTM - 13-04-2021: Insere FluxoHist Amostra
					$moduloAmostra = getModuloPadrao('amostra', $idunidade); 
					FluxoController::inserirFluxoStatusHist($moduloAmostra, $idamostraOrig, $idfluxostatusAmostra, 'PENDENTE');
				}

				//LTM: (08/07/2021) - Valida se o tipo de amostra do loteativ atual é diferente da anterior
				$subtipoamostra[] = $arrConfAmostras[$idloteativ]["idsubtipoamostra"];

				//Prodserv: testes que foram marcados na formalização mas não existem na tabela de resultados
				foreach($arrprodserv as $idprodserv => $val)
				{
					//Gerar novo resultado
					$insresultado = new Insert();
					$insresultado->setTable("resultado");
					$insresultado->idamostra = $idamostraOrig;
					$insresultado->idtipoteste = $idprodserv;
					$insresultado->quantidade = 1;
					$insresultado->idempresa = $idempresaLote;
					$insresultado->status = 'ABERTO';
					$insresultado->idfluxostatus = $idfluxostatusResultado;
					$idresultado = $insresultado->save();

					//LTM - 13-04-2021: Insere FluxoHist Resultado
					$moduloResultado = getModuloPadrao('resultado', $idunidade);
					FluxoController::inserirFluxoStatusHist($moduloResultado, $idresultado, $idfluxostatusResultado, 'PENDENTE');

					//Insere as atividades no ObjetoVinculo com os Resultados
					$objv = "INSERT INTO objetovinculo (idobjeto, tipoobjeto, idobjetovinc, tipoobjetovinc, criadopor, criadoem, alteradopor, alteradoem)
												VALUES (".$idresultado.", 'resultado', $idloteativ, 'loteativ', '".$_SESSION["SESSAO"]["USUARIO"]."', now(), '".$_SESSION["SESSAO"]["USUARIO"]."', now())";
					d::b()->query($objv) or die("Insert objetovinculo: ". mysqli_error(d::b()));
				}
			}
		}
	}
}

//funcao gerar amostras para lote AO
function geraAmostrasRelacionadasAoLoteao($inidlote,$partida,$exercicio)
{
	require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
	 

	//Recuperar testes que não existem mas foram marcados pelo usuário da formalização
	$sqlt = "SELECT la.idloteativ
				  , lo.idobjeto
				  , p.idsubtipoamostra
				  , e.idpessoaform
			   FROM loteativ la JOIN loteobj lo ON lo.idloteativ = la.idloteativ AND lo.tipoobjeto = 'prodserv'
			   JOIN prativ p ON la.idprativ = p.idprativ 
			   JOIN empresa e ON e.idempresa = la.idempresa
			  WHERE la.idlote = $inidlote
				AND NOT EXISTS (SELECT 1 FROM objetovinculo WHERE idobjetovinc = la.idloteativ AND tipoobjetovinc = 'loteativ')";

	$rt = d::b()->query($sqlt) or die("geraAmostrasRelacionadasAoLote: ".mysqli_error(d::b()));

	$arrAtivAmostras=array();
	while($r = mysqli_fetch_assoc($rt))
	{
		$arrAtivAmostras[$r["idloteativ"]][$r["idamostra"]][$r["idobjeto"]] = $r["idobjeto"];
		$arrAtivAmostras[$r["idloteativ"]]["idpessoa"] = $r["idpessoaform"];
		$arrAtivAmostras[$r["idloteativ"]]["idsubtipoamostra"] = $r["idsubtipoamostra"];
	}
	
	$sqlu="select * from unidade where idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and idtipounidade=13 and status='ATIVO'";
	$resu = d::b()->query($sqlu) or die("geraAmostrasRelacionadasAoLoteao erro ao buscar unidade de PeD: ".mysqli_error(d::b()));
	$rowu=mysqli_fetch_assoc($resu);
	
	$idunidade=$rowu['idunidade'];
	
	if(empty($idunidade)){
		die("A unidade de PeD da empresa não esta configurada para empresa.");
	}
	
	//LTM - 13-04-2021: Retorna o Idfluxo Amostra e Resultado
	$idfluxostatusAmostra = FluxoController::getIdFluxoStatus('amostra', 'ABERTO', $idunidade);
	$idfluxostatusResultado = FluxoController::getIdFluxoStatus('resultado', 'ABERTO', $idunidade);

	//Atividade: Criar somente 1 amostra para cada atividade. Caso exista, reutilizar
	foreach($arrAtivAmostras as $idloteativ => $arramostra){
		//Amostra: Caso nulo, criar nova amostra
		foreach($arramostra as $idamostra => $arrprodserv)
		{
			if(!in_array($arrConfAmostras[$idloteativ]["idsubtipoamostra"], $subtipoamostra))
			{                
				if(empty($arrAtivAmostras[$idloteativ]["idsubtipoamostra"])){
					$idsubtipoamostra=49;
				}else{
					$idsubtipoamostra=$arrAtivAmostras[$idloteativ]["idsubtipoamostra"];
				} 
				if(empty($arrAtivAmostras[$idloteativ]["idpessoa"])){
					die("Configurar no cadastro de empresa a empresa de ordem de produção.");
				}

				//Gerar nova amostra
				$arrReg=geraIdregistro($_SESSION["SESSAO"]["IDEMPRESA"],$idunidade);
				$insamostra = new Insert();
				$insamostra->setTable("amostra");
				$insamostra->idpessoa = $arrAtivAmostras[$idloteativ]["idpessoa"];//INATA 3019
				$insamostra->idunidade = $idunidade;
				$insamostra->status = 'ABERTO';
				$insamostra->idfluxostatus = $idfluxostatusAmostra;
				$insamostra->dataamostra = sysdate();
				//$insamostra->idtipoamostra=15;///////////////////////////?
				$insamostra->idsubtipoamostra=$idsubtipoamostra;///////////////////////////?
				$insamostra->lote = $partida."/".$exercicio;
				//$insamostra->tipoobjetosolipor="loteativ";
				//$insamostra->idobjetosolipor=$idloteativ;
				$insamostra->exercicio = $arrReg["exercicio"];
				$insamostra->idregistro = $arrReg["idregistro"];
				$idamostra = $insamostra->save();

				//LTM - 13-04-2021: Insere FluxoHist Amostra
				$moduloAmostra = getModuloPadrao('amostra', $idunidade); 
           		FluxoController::inserirFluxoStatusHist($moduloAmostra, $idamostra, $idfluxostatusAmostra, 'PENDENTE');
			}

			//LTM: (08/07/2021) - Valida se o tipo de amostra do loteativ atual é diferente da anterior
			$subtipoamostra[] = $arrConfAmostras[$idloteativ]["idsubtipoamostra"];

			//Prodserv: testes que foram marcados na formalização mas não existem na tabela de resultados
			foreach($arrprodserv as $idprodserv => $val)
			{			
				//Gerar novo resultado
				$insresultado = new Insert();
				$insresultado->setTable("resultado");
				$insresultado->idamostra = $idamostra;
				$insresultado->idtipoteste = $idprodserv;
				$insresultado->quantidade = 1;
				$insresultado->status = 'ABERTO';
				$insresultado->idfluxostatus = $idfluxostatusResultado;
				$idresultado = $insresultado->save();

				//LTM - 13-04-2021: Insere FluxoHist Resultado
				$moduloResultado = getModuloPadrao('resultado', $idunidade);
				FluxoController::inserirFluxoStatusHist($moduloResultado, $idresultado, $idfluxostatusResultado, 'PENDENTE');

				//Insere as atividades no ObjetoVinculo com os Resultados
				$objv = "INSERT INTO objetovinculo (idobjeto, tipoobjeto, idobjetovinc, tipoobjetovinc, criadopor, criadoem, alteradopor, alteradoem)
                       	 	  				 VALUES (".$idresultado.", 'resultado', $idloteativ, 'loteativ', '".$_SESSION["SESSAO"]["USUARIO"]."', now(), '".$_SESSION["SESSAO"]["USUARIO"]."', now())";
				d::b()->query($objv) or die("Insert objetovinculo AO: ". mysqli_error(d::b()));
			}
		}
	}
}


/*
 * Função para geração de IDs de Registro seguindo os seguintes parâmetros:
 * - Id da Empresa
 * - Id da Unidade
 * - Ano/Exercício
 */
function geraIdregistro($inIdempresa, $inIdunidade, $exercicio = NULL){

	if(empty($inIdunidade)){
		die("geraIdregistro: Unidade não informada");
	}
	
	if(!empty($exercicio)) { $exercicio = $exercicio; } else { $exercicio = date('Y'); }

	$sqlSeq = "UPDATE seqregistro SET idregistro = (idregistro + 1) 
				WHERE exercicio = '$exercicio'
				  AND idunidade = ".$inIdunidade;
	//Incrementa o ID Atual do exercicio corrente
	d::b()->query($sqlSeq) or die("geraIdregistro: Falha 1: " . mysqli_error(d::b()). "\nSQL: $sqlSeq");

	//Recupera o ID Atual do exercicio corrente
	$sql = "SELECT left(exercicio, 4) AS exercicio, idregistro 
			  FROM seqregistro 
			 where exercicio = '$exercicio'
			   and idunidade = ".$inIdunidade;
	
	$resexercicio = d::b()->query($sql);
	if(!$resexercicio){
		echo "geraIdregistro: Falha 2:\nSQL: ".$sql."\nErro:".mysqli_error(d::b());
		die();
	}
	
	$rowexercicio = mysqli_fetch_assoc($resexercicio);
	
	//Caso nao retorne nenhuma linha, sera necessario inicializar um novo ano de exercicio, com idamostra=1
	if(empty($rowexercicio["idregistro"])){
		$sqlatualizaexercicio =	"INSERT INTO seqregistro (idempresa, exercicio, idregistro, idunidade) 
								 VALUES (".$inIdempresa.", '$exercicio', 1, ".$inIdunidade.");";

		$resexercicio = d::b()->query($sqlatualizaexercicio) or die("geraIdregistro: Falha 2: " . mysqli_error(d::b()) . "<p>SQL: $sqlatualizaexercicio");
	
		if(!$resexercicio){
			echo "geraIdregistro: Falha 3: " . mysqli_error(d::b()) . "\nSQL: $sql";
			die();
		}
	
		$sql = "SELECT left(exercicio, 4) AS exercicio, idregistro 
				  FROM seqregistro where exercicio = '$exercicio'
				   AND idunidade = ".$inIdunidade;
		
		$sexercicio = d::b()->query($sql) or die("geraIdregistro: Falha 4: " . mysql_error() . "\nSQL: $sql");
	
		if(!$sexercicio){
			echo "Falha 4 Pesquisando Exercicio X IdAmostra : " . mysql_error() . "<p>SQL: $sql";
			die();
		}
		$rowexercicio = mysqli_fetch_array($sexercicio);
	}
	
	return $rowexercicio;
}

/*
 * Limpeza de arquivos de grafico temporarios
 */
function delgraf(){

	$comparedate = date("Y-m-d H:i:s"); //Data e hora atual

	$address = _PARPASTATMPGRAF;

	$parintervalo = $_SESSION["parintervalo"];
	if(empty($parintervalo)){
		$parintervalo = _intervalolimpezagraficostemp;
		$_SESSION["parintervalo"] = $parintervalo;
	}

	@$dir = opendir($address);

	if(!$dir){
		echo "<!-- Erro ao abrir a pasta temporaria para graficos. <br> Caminho: [".$address."] -->";
		return false;
	}
	while($entry = readdir($dir)){
		if(is_dir("$address/$entry") && ($entry != ".." && $entry != ".")){
			directory_tree("$address/$entry",$comparedate);
		}else{
			if($entry != ".." && $entry != ".") {

				$fulldir=$address.'/'.$entry;
				$last_modified = filemtime($fulldir);
				$last_modified_str = date("Y-m-d H:m:i", $last_modified);

				$diff = (strtotime($comparedate) - strtotime($last_modified_str));

				if($diff > $parintervalo){
					$res = unlink($fulldir);
					//echo $fulldir . "  -  " . $diff . "<br>";
				}
			}
		}
	}
	//echo teste;
	return true;

}

/*
 * Armazena os dados da emissão do resultado em formato estruturado, para atendimento à norma
 */
function congelaResultado($inIdres,$versiona=false,$status='FECHADO'){
	$aRes=array();

	$aTiposElisa=array("ELISA","ELISASGMT");

	if($versiona===true){
		//Atualiza o resultado
		d::b()->query("UPDATE resultado
						SET versao = versao + 1
						where idresultado=".$inIdres) or die("Incrementa versão: ". mysqli_error(d::b()));
	}
	
	//Resultado
	$sql = "SELECT jresultado from resultadojson where  idresultado = ".$inIdres;
	$res = d::b()->query($sql);
	if($res && mysqli_num_rows($res) > 0){
		$row = mysqli_fetch_assoc($res);
		$json = unserialize(base64_decode($row["jresultado"]));
	}else{
		$json = null;
	}
	if($json){
		$jsonconfig = $json["resultado"]["res"]['jsonconfig'];
		$aRes["resultado"]["sql"] = "select * from resultado where  idresultado = ".$inIdres;
		$aRes["resultado"]["res"] = sql2array($aRes["resultado"]["sql"],true);
		$aRes["resultado"]["res"]['jsonconfig'] = $jsonconfig;
	}else{
		$aRes["resultado"]["sql"] = "select * from resultado where  idresultado = ".$inIdres;
		$aRes["resultado"]["res"] = sql2array($aRes["resultado"]["sql"],true);
	}
	//Amostra
	$aRes["amostra"]["sql"] = "select * from amostra where  idamostra = ".coalesce($aRes["resultado"]["res"]["idamostra"],"null");
	$aRes["amostra"]["res"] = sql2array($aRes["amostra"]["sql"]);
	
	//Dados Amostra
	$aRes["dadosamostra"]["sql"] = "select * from dadosamostra where idamostra = ".coalesce($aRes["amostra"]["res"]["idamostra"],"null");
	$aRes["dadosamostra"]["res"] = sql2array($aRes["dadosamostra"]["sql"],true,array(),true);


	//Cliente
	$aRes["pessoa"]["sql"] = "select idpessoa, nome, razaosocial from pessoa where idpessoa = ".coalesce($aRes["amostra"]["res"]["idpessoa"],"null");
	$aRes["pessoa"]["res"] = sql2array($aRes["pessoa"]["sql"]);

	//Nucleo
	if(!empty($aRes["amostra"]["res"]["idnucleo"])){
		$aRes["nucleo"]["sql"] = "select * from nucleo where  idnucleo = ".$aRes["amostra"]["res"]["idnucleo"];
		$aRes["nucleo"]["res"] = sql2array($aRes["nucleo"]["sql"]);
	}

	//Tipo
	$aRes["tipoamostra"]["sql"] = "select idtipoamostra, tipoamostra from tipoamostra where  idtipoamostra = ".coalesce($aRes["amostra"]["res"]["idtipoamostra"],"null");
	$aRes["tipoamostra"]["res"] = sql2array($aRes["tipoamostra"]["sql"]);

	//Subtipo
	$aRes["subtipoamostra"]["sql"] = "select subtipoamostra,normativa from subtipoamostra where idsubtipoamostra = ".coalesce($aRes["amostra"]["res"]["idsubtipoamostra"],"null");
	$aRes["subtipoamostra"]["res"] = sql2array($aRes["subtipoamostra"]["sql"]);
	
	//Amostracampos
	$aRes["amostracampos"]["sql"] = "select * from amostracampos where  idunidade = ".coalesce($aRes["amostra"]["res"]["idunidade"],"null")." and idsubtipoamostra = ".coalesce($aRes["amostra"]["res"]["idsubtipoamostra"],"null");
	$aRes["amostracampos"]["res"] = sql2array($aRes["amostracampos"]["sql"],true,array(),true);

	//Serviço
	if($json){
		$aRes["prodserv"]["sql"] = "SELECT descr as tipoteste,
											codprodserv as sigla,
											tipogmt,
											tipoespecial,
											geralegenda,
											geragraf,
											geracalc,
											textointerpretacao,
											textopadrao,
											tipobact,
											logoinmetro,
											modelo,
											modo,
											comparativodelotes,
											idsgdoc 
									from prodserv
									where
										idprodserv = ".coalesce($aRes["resultado"]["res"]["idtipoteste"],"null");
		$aRes["prodserv"]["res"] = $json['prodserv']["res"];
	}else{
		$aRes["prodserv"]["sql"] = "SELECT descr as tipoteste,
											codprodserv as sigla,
											tipogmt,
											tipoespecial,
											geralegenda,
											geragraf,
											geracalc,
											textointerpretacao,
											textopadrao,
											tipobact,
											logoinmetro,
											modelo,
											modo,
											comparativodelotes,
											idsgdoc
									from prodserv
									where
										idprodserv = ".coalesce($aRes["resultado"]["res"]["idtipoteste"],"null");
		$aRes["prodserv"]["res"] = sql2array($aRes["prodserv"]["sql"],true);
	}

	if(coalesce($aRes["prodserv"]["res"]["idsgdoc"],"null")){
		$aRes["sgdoc"]["sql"] = "SELECT idregistro,idsgdoc,titulo,versao from sgdoc where idsgdoc = ".coalesce($aRes["prodserv"]["res"]["idsgdoc"],"null");
		$aRes["sgdoc"]["res"] = sql2array($aRes["sgdoc"]["sql"]);
	}
	
	$aRes["prodservtipoopcao"]["sql"] = "SELECT idprodservtipoopcao, valor FROM prodservtipoopcao where  idprodserv = ".coalesce($aRes["resultado"]["res"]["idtipoteste"],"null")." order by valor*1, valor";
	$aRes["prodservtipoopcao"]["res"] = sql2array($aRes["prodservtipoopcao"]["sql"],true,array(),true);
	
	$aRes["prodservtipoopcaoespecie"]["sql"] = "
	    SELECT idespeciefinalidade, valorinicio, valorfim, cor,	ptoe.valorinicio, ptoe.valorfim, msg
		FROM prodservtipoopcaoespecie ptoe
		WHERE
			 ptoe.idprodserv = '".coalesce($aRes["resultado"]["res"]["idtipoteste"],"null")."' AND status = 'ATIVO' AND
			idadeinicio <= '".$aRes["amostra"]["res"]["idade"]."' AND idadefim >= '".$aRes["amostra"]["res"]["idade"]."' 
			AND idespeciefinalidade = '".$aRes["amostra"]["res"]["idespeciefinalidade"]."' order by valorinicio;";
	$aRes["prodservtipoopcaoespecie"]["res"] = sql2array($aRes["prodservtipoopcaoespecie"]["sql"],true,array(),true);

	$aRes["lotecons"]["sql"] = "
	    select c.qtdd, c.qtdd_exp, pl.descr, l.spartida, l.partidaext, DATE_FORMAT(l.vencimento, '%d/%m/%Y') as vencimento, DATE_FORMAT(l.fabricacao, '%d/%m/%Y') as fabricacao, l.fabricante
			FROM lotecons c
			JOIN lote l ON c.idlote=l.idlote
			JOIN prodservformulains i ON i.idprodserv=l.idprodserv
			JOIN prodservformula p ON p.idprodservformula = i.idprodservformula
			JOIN prodserv pl ON pl.idprodserv = l.idprodserv
			WHERE 
			c.tipoobjeto ='resultado'  and i.status='ATIVO' and c.idobjeto ='".$aRes["resultado"]["res"]["idresultado"]."'	and p.idprodserv = '".$aRes["resultado"]["res"]["idtipoteste"]."' and c.qtdd>0 and i.listares='Y';";
	$aRes["lotecons"]["res"] = sql2array($aRes["lotecons"]["sql"],true,array(),true);

	//Espécie
	$aRes["especiefinalidade"]["sql"] = "select e.idespeciefinalidade,p.plantel as especie,e.finalidade,concat(p.plantel,'-',e.finalidade) as especiefinalidade
                                                    from especiefinalidade e
                                                        left join plantel p on(p.idplantel = e.idplantel)
                                                        where  e.idespeciefinalidade = ".coalesce($aRes["amostra"]["res"]["idespeciefinalidade"],"null");

	$aRes["especiefinalidade"]["res"] = sql2array($aRes["especiefinalidade"]["sql"]);

	//Lotes associados @todo: idempresa
	if(!empty($aRes["amostra"]["res"]["idwfxprocativ"])){
		$aRes["vwpartidaamostra"]["sql"] = "SELECT * FROM vwpartidaamostra where idamostra =".$aRes["amostra"]["res"]["idamostra"];
		$aRes["vwpartidaamostra"]["res"] = sql2array($aRes["vwpartidaamostra"]["sql"]);
	}

	//Bioensaio relacionado @todo: idempresa e remover os *
	if(!empty($aRes["resultado"]["res"]["idservicoensaio"])){
		$aRes["bioensaio"]["sql"] = "select  s.*,b.*,if(s.dia is null,' ',concat(' - D',s.dia)) as rotulo,concat(l.tipo,' ',right(l.local, 2)) as rot,e.gaiola
                                                    from servicoensaio s join bioensaio b join analise a
                                                    left join localensaio e on(e.idbioensaio = b.idbioensaio and e.idlocal > 3)
                                                    left join local l on(l.idlocal = e.idlocal)
                                                    where  b.idbioensaio = a.idobjeto
                                                    and a.objeto='bioensaio'
                                                    and a.idanalise = s.idobjeto
                                                    and s.tipoobjeto = 'analise'
                                                    and s.idservicoensaio= ".$aRes["resultado"]["res"]["idservicoensaio"];
		$aRes["bioensaio"]["res"] = sql2array($aRes["bioensaio"]["sql"]);
	}

	//Endereço
	$aRes["endereco"]["sql"] = "select c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf
								from nfscidadesiaf c,endereco e
								where c.codcidade = e.codcidade
								and e.idtipoendereco=2
								and e.idpessoa =".coalesce($aRes["amostra"]["res"]["idpessoa"],"null");
	$aRes["endereco"]["res"] = sql2array($aRes["endereco"]["sql"]);
	
	//Titulos
	//if(!empty($aRes["prodserv"]["res"]["tipoespecial"])){
	$aRes["titulos"]["res"]=rettitulos($aRes["prodserv"]["res"]["tipoespecial"],$aRes["resultado"]["res"]["idtipoteste"]);
	//}
	
	//Historico GMT
	//maf070513: conforme edison, o valor informado em semanas deve considerar os primeiros caracteres numericos, descosiderando o restante, para casos em que eh informado mais de 1 lote na mesma amostra. Por este motivo está sendo utilizado o cast (int)
	//Hermes13052015: conforme Daniel para o historico do nucleo podemos considerar somente o idnucleo e desconsiderar o cliente -- AND a.idpessoa   = "  .$row["idpessoa"]."
	$intIDADE = (int)$aRes["amostra"]["res"]["idade"];
	if(!empty($aRes["amostra"]["res"]["idnucleo"]) 
			and !empty($aRes["amostra"]["res"]["idpessoa"]) 
			and !empty($aRes["resultado"]["res"]["idtipoteste"])
			and !empty($intIDADE)
			and !in_array($aRes["prodserv"]["res"]["tipoespecial"],$aTiposElisa)){
		$aRes["hist_gmt"]["sql"] = "SELECT
											cast(a.idade as UNSIGNED) as idade,
											r.gmt					
									   FROM
										   resultado r,
										   amostra a
									   WHERE
										   (r.idamostra = a.idamostra)
										 
										   and cast(a.idade as UNSIGNED) <= ".$intIDADE." -- MAF: 160518: foi colocado um cast para realizar a comparação por int
										   and a.idnucleo        = " .$aRes["amostra"]["res"]["idnucleo"]."
										   -- AND a.idpessoa   = "  .$aRes["amostra"]["res"]["idpessoa"]."
										   AND r.idtipoteste = "  .$aRes["resultado"]["res"]["idtipoteste"]."
										   AND r.status != 'CANCELADO'
									   ORDER BY idade";
		$aRes["hist_gmt"]["res"] = sql2array($aRes["hist_gmt"]["sql"],true,array(),true);
	}

	//mcc - 28/07/2020 COMENTADO PARA SE ADEQUAR À CONFIG DA PRODSERV. NÃO EXISTE MAIS TIPOESPECIAL.
	//Elisa
	//if($aRes["prodserv"]["res"]["tipoespecial"]=="ELISA" or $aRes["prodserv"]["res"]["tipoespecial"]=="ELISASGMT"){
		if($aRes["prodserv"]["res"]["modelo"]=="UPLOAD" ){
		//Tabela inteira
		$aRes["resultadoelisa"]["sql"] = "SELECT * 
											FROM resultadoelisa 
											WHERE idresultado = ".$inIdres." 
											AND status = 'A' 
											ORDER BY idresultadoelisa";
		$aRes["resultadoelisa"]["res"] = sql2array($aRes["resultadoelisa"]["sql"],true,array(),true);
		
		//Grafico de contagem de 'groups'
		$aRes["resultadoelisa_graf1"]["sql"] = "SELECT grupo, count(*) as quant 
							FROM resultadoelisa 
							WHERE  idresultado = ".$inIdres."
								and (grupo is not null or grupo != '') 
								and status = 'A'
							GROUP BY grupo 
							ORDER BY count(*) desc";
		$aRes["resultadoelisa_graf1"]["res"] = sql2array($aRes["resultadoelisa_graf1"]["sql"],true,array(),true);
		
		//Grafico de historico
		if(!empty($aRes["amostra"]["res"]["idnucleo"]) 
			and !empty($aRes["amostra"]["res"]["idpessoa"]) 
			and !empty($aRes["resultado"]["res"]["idtipoteste"])){

			$aRes["resultadoelisa_graf2"]["sql"] = "SELECT
											r.idresultado,
											a.idade,
											re.titer as gmt
										FROM
											resultadoelisa re,
											resultado r,
											amostra a
										WHERE
											(r.idamostra = a.idamostra)
											AND re.idresultado = r.idresultado
											AND re.nome = 'GMN'
											AND re.status = 'A'
											
											AND a.idnucleo = ".$aRes["amostra"]["res"]["idnucleo"]."
											AND a.idpessoa = ".$aRes["amostra"]["res"]["idpessoa"]."
											AND r.idtipoteste = ".$aRes["resultado"]["res"]["idtipoteste"]."
										ORDER BY convert(idade,unsigned)";

			$aRes["resultadoelisa_graf2"]["res"] = sql2array($aRes["resultadoelisa_graf2"]["sql"],true,array(),true);
		}	
		
	}

	//Assinatura
	//hermesp 27-08-2015 alterado select (limit 1) para mostrar somente uma assinatura conforme conversado com daniel e andre pois o resultado era assinado e posteriormente aberto alterado e assinado novamente ficando assim com uma assinatura invalida.
	$aRes["resultadoassinatura"]["sql"] = "SELECT idresultado, idpessoa, dma(criadoem) as criadoem
									FROM resultadoassinatura
									WHERE  idresultado = ".$inIdres." order by criadoem desc limit 1";
	//Mesmo com a regra de mostrar somente 1 assinatura, existe um loop para recuperá-las. Será recuperado como um array
	$aRes["resultadoassinatura"]["res"] = sql2array($aRes["resultadoassinatura"]["sql"],true,array(),true);

	//Maf: Versao do software para auditoria
	$aRes["versaosoft"]["sql"] = "select 'git' as ref, versaocurta from versao where referencia= 'git' order by alteradoem desc limit 1";
	$aRes["versaosoft"]["res"] = sql2array($aRes["versaosoft"]["sql"]);

	//Maf: Versao do database para auditoria
	$aRes["versaodb"]["sql"] = "select 'db' as ref, versaocurta from versao where referencia= 'db' order by alteradoem desc limit 1";
	$aRes["versaodb"]["res"] = sql2array($aRes["versaodb"]["sql"]);

	//Congela o resultado
if($status=='PROCESSANDO'){
	$icong = "INSERT into resultadoprocjson (idresultado,jresultado,alteradoem)
                        VALUES (".$inIdres.",'".base64_encode(serialize($aRes))."',now())
                        on duplicate key update alteradoem=now(), jresultado='".base64_encode(serialize($aRes))."'";

}else{
	$icong = "INSERT into resultadojson (idresultado,jresultado,alteradoem)
                        VALUES (".$inIdres.",'".base64_encode(serialize($aRes))."',now())
                        on duplicate key update alteradoem=now(), jresultado='".base64_encode(serialize($aRes))."'";

}

	d::b()->query($icong) or die("congelaResultado: ". mysqli_error(d::b()));
if($versiona===true){
        $sqlaud = "INSERT INTO _auditoria (idempresa,linha,acao,objeto,idobjeto,coluna,valor,criadoem,criadopor,tela) 
                    values(1,'1','i','resultadojson',".$inIdres.",'jresultado','".base64_encode(serialize($aRes))."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','".$_SERVER["HTTP_REFERER"]."')";
        $resaud = mysql_query($sqlaud) or die("ERRO ao gerar auditoria: ".mysql_error()."\n SQL: ".$sqlaud);

		//$aRes["_auditoria"]["sql"] = "select DATE_FORMAT(max(criadoem), '%d/%m/%Y') as dataconclusao from _auditoria where objeto = 'resultado' and idobjeto = '".$inIdres."' and coluna = 'status' and valor = 'FECHADO'";
		//$aRes["_auditoria"]["res"] = sql2array($aRes["_auditoria"]["sql"],true);

}

	return $aRes;
}

function rettitulos($inespecial, $inidprodserv = 0){

	//echo "<!-- inespecial:".$inespecial." ". $inidprodserv."-->";
	//$arrtitulo = $_SESSION["arrtitulo"][$inespecial];

	if(!empty($arrtitulo)){
		return $arrtitulo;
	}else{
		if(!empty($inespecial) and empty($inidprodserv)){
			$sql = "SELECT * FROM titulo where  titulo = '". $inespecial ."'";
			
			
			echo "<!-- ".$sql." -->";// die();

			$res = mysql_query($sql) or die("A Consulta dos titulos falhou : " . mysql_error() . "<p>SQL: $sql");
			$row = mysql_fetch_array($res);

			$_SESSION["arrtitulo"][$inespecial]["1"] = $row["x1"];
			$_SESSION["arrtitulo"][$inespecial]["2"] = $row["x2"];
			$_SESSION["arrtitulo"][$inespecial]["3"] = $row["x3"];
			$_SESSION["arrtitulo"][$inespecial]["4"] = $row["x4"];
			$_SESSION["arrtitulo"][$inespecial]["5"] = $row["x5"];
			$_SESSION["arrtitulo"][$inespecial]["6"] = $row["x6"];
			$_SESSION["arrtitulo"][$inespecial]["7"] = $row["x7"];
			$_SESSION["arrtitulo"][$inespecial]["8"] = $row["x8"];
			$_SESSION["arrtitulo"][$inespecial]["9"] = $row["x9"];
			$_SESSION["arrtitulo"][$inespecial]["10"] = $row["x10"];
			$_SESSION["arrtitulo"][$inespecial]["11"] = $row["x11"];
			$_SESSION["arrtitulo"][$inespecial]["12"] = $row["x12"];
			$_SESSION["arrtitulo"][$inespecial]["13"] = $row["x13"];
					
			$_SESSION["arrtitulo"][$inespecial]["corterecsem"] = $row["corterecsem"];
			$_SESSION["arrtitulo"][$inespecial]["corteprodsem"] = $row["corteprodsem"];
			$_SESSION["arrtitulo"][$inespecial]["postrecsem"] = $row["postrecsem"];
			$_SESSION["arrtitulo"][$inespecial]["postprodsem"] = $row["postprodsem"];
			
			$_SESSION["arrtitulo"][$inespecial]["crsmin"] = $row["crsmin"];
			$_SESSION["arrtitulo"][$inespecial]["cpsmin"] = $row["cpsmin"];
			$_SESSION["arrtitulo"][$inespecial]["prsmin"] = $row["prsmin"];
			$_SESSION["arrtitulo"][$inespecial]["ppsmin"] = $row["ppsmin"];

			$_SESSION["arrtitulo"][$inespecial]["crsmax"] = $row["crsmax"];
			$_SESSION["arrtitulo"][$inespecial]["cpsmax"] = $row["cpsmax"];
			$_SESSION["arrtitulo"][$inespecial]["prsmax"] = $row["prsmax"];
			$_SESSION["arrtitulo"][$inespecial]["ppsmax"] = $row["ppsmax"];


			$_SESSION["arrtitulo"][$inespecial]["msgmin"] = $row["msgmin"];
			$_SESSION["arrtitulo"][$inespecial]["msgmax"] = $row["msgmax"];
			
			return $_SESSION["arrtitulo"][$inespecial];
			
			
		}else if(!empty($inidprodserv)){
			
			$sql = "select valor from prodservtipoopcao where idprodserv = '".$inidprodserv."' order by valor*1";
			//echo "<!-- ".$sql." -->";// die();
			
			
			$res = d::b()->query($sql);
			//die($sql);
			$i = 0;
			while($row=mysqli_fetch_assoc($res)){
				$i++;
				$_SESSION["arrtitulo"][$inespecial][$i] = $row['valor'];
			}
	
			
			return $_SESSION["arrtitulo"][$inespecial];
		}else{
				return array();
		}
		/*
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x1"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x2"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x3"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x4"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x5"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x6"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x7"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x8"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x9"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x10"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x11"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x12"];
		 $_SESSION["arrgmtrotulo"][$ingmt] = $row["x13"];
		 */

	}
}

function verificaTagReserva($inIdTag, $inInicio, $inFim){
	$sql =  "	SELECT 
					true as travado
				FROM 
					tagreserva tr 
				WHERE
					(
						if (tr.inicio<='".$inInicio."','".$inInicio."',tr.inicio) = '".$inInicio."' and 
						if(tr.fim>='".$inFim."','".$inFim."',tr.fim )= '".$inFim."'
					) 
					or
					(
						(tr.inicio > '".$inInicio."' and tr.inicio < '".$inFim."') 
						or 
						(tr.fim > '".$inInicio."' and tr.fim < '".$inFim."')
					)
					and trava = 'S'";
					
		$res = d::b()->query($sql) or die("verificaTagReserva: Erro ao verificar tag reserva: " . mysql_error() . "\nSQL: $sql");
	
		if (mysqli_num_rows($res) > 0){
			
			return('true');
			
		}else{
			
			return('false');
		}
		
		
}

function preencheAssinaturas($modulo, $id){
	$tabela = getModuloTab($modulo);
	$sql = "SELECT p.idpessoa,
				   p.nome,
				   CASE
						WHEN c.status IN ('ATIVO' , 'ASSINADO', 'REJEITADO') THEN DMA(c.alteradoem)
						ELSE ''
				   END AS dataassinatura,
				   CASE
						WHEN c.status IN ('ATIVO' , 'ASSINADO') THEN 'ASSINADO'
						WHEN c.status IN ('REJEITADO') THEN 'REJEITADO'
						ELSE 'PENDENTE'
				   END AS status,
				   DMA(assinaturaanterior) AS assinaturaanterior,
				   c.idobjetoext,
				   c.idcarrimbo
			  FROM carrimbo c JOIN pessoa p ON c.idpessoa = p.idpessoa
			 WHERE c.status IN ('ATIVO' , 'PENDENTE', 'ASSINADO', 'REJEITADO')
			   AND c.tipoobjeto IN ('$modulo' , '$tabela')
			   AND c.idobjeto = $id
			ORDER BY nome";
	return $sql;
}

function replaceEventoUnion($idevento, $tokeninicial)
{
	$sql = "SELECT DISTINCT NULL AS idfluxostatuspessoa, 
				e.idempresa as idempresa,
				gp.idpessoa as idobjeto,
				gp.idimgrupo as idobjetoext, 
				'imgrupo' as tipoobjetoext,
				'N' as inseridomanualmente,
				e.idpessoa,
				t.eventotipo,
				e.evento
			FROM evento e 
			JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' and r.tipoobjeto = 'imgrupo' 
			JOIN eventotipo t ON t.ideventotipo = e.ideventotipo
			JOIN imgrupopessoa gp on gp.idimgrupo = r.idobjeto
			LEFT JOIN fluxostatuspessoa r2 on r2.idmodulo = e.idevento AND r2.modulo = 'evento' and r2.idobjetoext = r.idobjetoext and r2.idobjeto = gp.idpessoa
			LEFT JOIN pessoa p on p.idpessoa = gp.idpessoa
			WHERE r.idmodulo = '".$idevento."' AND r.modulo = 'evento' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa
			UNION
			SELECT DISTINCT NULL as idfluxostatuspessoa,  
				e.idempresa as idempresa,
				r.idobjeto as idobjeto,
				NULL as idobjetoext, 
				NULL as tipoobjetoext,
				'Y' as inseridomanualmente,
				e.idpessoa,
				t.eventotipo,
				e.evento
			FROM evento e
			JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' and r.tipoobjeto = 'pessoa' 
			JOIN eventotipo t ON t.ideventotipo = e.ideventotipo
			WHERE r.idmodulo = '".$idevento."' AND r.modulo = 'evento'";
	$res = d::b()->query($sql);
	$arrInsertFluxoStatusPessoa = array();
	$arrDestinatarios = array();
	$i = 0;
	while($row = mysqli_fetch_assoc($res)){
		$qr = "SELECT 1 FROM fluxostatuspessoa WHERE modulo = 'evento' AND idmodulo = '".$idevento."' AND tipoobjeto = 'pessoa' and idobjeto = ".$row["idobjeto"];
		$rs = d::b()->query($qr);
		if(mysqli_num_rows($rs) == 0){
			$eventoTipoDescr = $row["eventotipo"];
			$eventoTitulo = $row["evento"];
			
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_oculto"] 				= 0;
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_visualizado"] 			= 0;
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_assinar"] 				= 'N';
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_modulo"] 				= 'evento';
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_tipoobjeto"] 			= 'pessoa';
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idmodulo"] 				= $idevento;
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idfluxostatus"] 			= $tokeninicial;
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idobjeto"] 				= $row["idobjeto"];
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idempresa"] 				= $row["idempresa"];
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idobjetoext"] 			= $row["idobjetoext"];
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_tipoobjetoext"] 			= $row["tipoobjetoext"];
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_inseridomanualmente"] 	= $row["inseridomanualmente"];
			
			if($row["idpessoa"] != $row["idobjeto"])
				$arrDestinatarios[] = $row["idobjeto"];

			$i++;
		}
	}

	if($i > 0){
		$_CMD = new cmd();
		$_CMD->disablePrePosChange = true;
		$res = $_CMD->save($arrInsertFluxoStatusPessoa);
		if(!$res){
			die($_CMD->erro);
		}else if(count($arrDestinatarios) > 0){
			$notif = Notif::ini()
				->canal("browser")
				->conf([
					"mod" => "evento",
					"idmod" => 155, // id do modulo - necessário por conta das restrições do usuario
					"modpk" => "idevento", // 
					"idmodpk" => $idevento,
					"title" => "Você foi adicionado em um evento de ".$eventoTipoDescr,
					"corpo" => $eventoTitulo ?? '',
					"localizacao" => "dashboardsnippet",
					"url" => "https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=".$idevento
				]);

			foreach ($arrDestinatarios as $key => $idpessoa) {
				$notif->addDest($idpessoa);
			}
			
			$notif->send();
		}
		$_CMD = null;
	}

	// GVT - 10/03/2022 - Migrado para C9 p/ gerar notificação 
	/*
	$sql = "INSERT INTO fluxostatuspessoa (
			 SELECT DISTINCT NULL AS idfluxostatuspessoa, 
			 				 null as idpessoa, 
							 e.idempresa as idempresa, 
							 '".$idevento."' AS idmodulo,
							 'evento' AS modulo,
							 gp.idpessoa as idobjeto, 
							 'pessoa' as tipoobjeto, 
							 NULL AS status,
							 '".$tokeninicial."' as idfluxostatus, 
							 0 as oculto, 
							 gp.idimgrupo as idobjetoext, 
							 'imgrupo' as tipoobjetoext,
							 'N' as inseridomanualmente,
							 0 as visualizado,
							 'N' as assinar,
							 NULL as editar,
							 e.criadopor,
							 e.criadoem,
							 e.alteradopor, 
							 now()
			FROM evento e JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' and r.tipoobjeto = 'imgrupo' 
			JOIN imgrupopessoa gp on gp.idimgrupo = r.idobjeto
	   LEFT JOIN fluxostatuspessoa r2 on r2.idmodulo = e.idevento AND r2.modulo = 'evento' and r2.idobjetoext = r.idobjetoext and r2.idobjeto = gp.idpessoa
	   LEFT JOIN pessoa p on p.idpessoa = gp.idpessoa
		   WHERE r.idmodulo = '".$idevento."' AND r.modulo = 'evento' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa
		UNION
		  SELECT DISTINCT NULL as idfluxostatuspessoa,  
		  				  NULL as idpessoa, 
						  e.idempresa as idempresa, 
						  '".$idevento."' AS idmodulo,
						  'evento' AS modulo,
						  r.idobjeto as idobjeto, 
						  'pessoa' as tipoobjeto, 
						  NULL AS status,
						  '".$tokeninicial."' as idfluxostatus, 
						  0 as oculto, 
						  NULL as idobjetoext, 
						  NULL as tipoobjetoext,
						  'Y' as inseridomanualmente,
						  0 as visualizado,
						  'N' as assinar,
						  NULL as editar,
						  e.criadopor,
						  e.criadoem,
						  e.alteradopor, 
						  now()
		   	FROM evento e
			JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' and r.tipoobjeto = 'pessoa' 
		   WHERE r.idmodulo = '".$idevento."' AND r.modulo = 'evento')
		   ON DUPLICATE KEY 
		   UPDATE alteradoem = now();";
	d::b()->query($sql) or die("Erro ao atualizar replaceEventoUnion: ".mysql_error(d::b()));
	*/
}

function replaceEvento($idevento, $tokeninicial)
{
	$sql = "SELECT DISTINCT NULL AS idfluxostatuspessoa,
				e.idempresa AS idempresa, 
				e.idevento AS idmodulo, 
				gp.idpessoa AS idobjeto, 
				gp.idimgrupo as idobjetoext,
				e.idpessoa,
				t.eventotipo,
				e.evento
			FROM evento e 
			JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' AND r.tipoobjeto = 'imgrupo' 
			JOIN eventotipo t ON t.ideventotipo = e.ideventotipo
			JOIN imgrupopessoa gp on gp.idimgrupo = r.idobjeto
			LEFT JOIN fluxostatuspessoa r2 on r2.idmodulo = e.idevento AND r2.modulo = 'evento' AND r2.idobjetoext = r.idobjetoext AND r2.idobjeto = gp.idpessoa
			LEFT JOIN pessoa p on p.idpessoa = gp.idpessoa
			WHERE r.idmodulo = '".$idevento."' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa";
	$res = d::b()->query($sql);
	$arrInsertFluxoStatusPessoa = array();
	$arrDestinatarios = array();
	$i = 0;
	while($row = mysqli_fetch_assoc($res)){
		$qr = "SELECT 1 FROM fluxostatuspessoa WHERE modulo = 'evento' AND idmodulo = '".$idevento."' AND tipoobjeto = 'pessoa' and idobjeto = ".$row["idobjeto"];
		$rs = d::b()->query($qr);
		if(mysqli_num_rows($rs) == 0){
			$eventoTipoDescr = $row["eventotipo"];
			$eventoTitulo = $row["evento"];

			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_oculto"] 				= 0;
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_visualizado"] 			= 0;
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_assinar"] 				= 'N';
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_inseridomanualmente"] 	= 'N';
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_modulo"] 				= 'evento';
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_tipoobjeto"] 			= 'pessoa';
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_tipoobjetoext"] 			= 'imgrupo';
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idfluxostatus"] 			= $tokeninicial;
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idmodulo"] 				= $row["idmodulo"];
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idobjeto"] 				= $row["idobjeto"];
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idempresa"] 				= $row["idempresa"];
			$arrInsertFluxoStatusPessoa["_evento".$i."_i_fluxostatuspessoa_idobjetoext"] 			= $row["idobjetoext"];
			
			if($row["idpessoa"] != $row["idobjeto"])
				$arrDestinatarios[] = $row["idobjeto"];

			$i++;
		}
	}

	if($i > 0){
		$_CMD = new cmd();
		$_CMD->disablePrePosChange = true;
		$res = $_CMD->save($arrInsertFluxoStatusPessoa);
		if(!$res){
			die($_CMD->erro);
		}else if(count($arrDestinatarios) > 0){
			$notif = Notif::ini()
				->canal("browser")
				->conf([
					"mod" => "evento",
					"idmod" => 155, // id do modulo - necessário por conta das restrições do usuario
					"modpk" => "idevento", // 
					"idmodpk" => $idevento,
					"title" => "Você foi adicionado em um evento de ".$eventoTipoDescr,
					"corpo" => $eventoTitulo ?? '',
					"localizacao" => "dashboardsnippet",
					"url" => "https://sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=".$idevento
				]);

			foreach ($arrDestinatarios as $key => $idpessoa) {
				$notif->addDest($idpessoa);
			}
			
			$notif->send();
		}
	}

	// GVT - 10/03/2022 - Migrado para C9 p/ gerar notificação 
	/*
	$sql = "INSERT INTO fluxostatuspessoa (
			 SELECT DISTINCT NULL AS idfluxostatuspessoa,
			 				 NULL AS idpessoa, 
							 e.idempresa AS idempresa, 
							 e.idevento AS idmodulo, 
							 'evento' AS modulo,
							 gp.idpessoa AS idobjeto, 
							 'pessoa' as tipoobjeto, 
							 '' as status,
							 '".$tokeninicial."' AS idfluxostatus, 
							 0 as oculto, 
							 gp.idimgrupo as idobjetoext,
							 'imgrupo' as tipoobjetoext, 
							 'N' as inseridomanualmente,
							 0 as visualizado,
							 'N' as assinar,
							 NULL as editar,
							 e.criadopor,
							 e.criadoem,
							 e.alteradopor, 
							 now()
			 FROM evento e JOIN fluxostatuspessoa r on r.idmodulo = e.idevento AND r.modulo = 'evento' AND r.tipoobjeto = 'imgrupo' 
			 JOIN imgrupopessoa gp on gp.idimgrupo = r.idobjeto
		LEFT JOIN fluxostatuspessoa r2 on r2.idmodulo = e.idevento AND r2.modulo = 'evento' AND r2.idobjetoext = r.idobjetoext AND r2.idobjeto = gp.idpessoa
		LEFT JOIN pessoa p on p.idpessoa = gp.idpessoa
			WHERE r.idmodulo = '".$idevento."' and r2.idobjeto is null and not gp.idpessoa = e.idpessoa)
			 ON DUPLICATE KEY 
		   UPDATE alteradoem = now();";
	d::b()->query($sql) or die("Erro ao aturalizar replaceEvento: ".mysql_error(d::b()));
	*/
}

function sqlPessoa($resultpessoa)
{
	$sqlPessoa = "SELECT 	p.idpessoa,
							p.nomecurto,
							p.idtipopessoa,
							p.status,
							p.idempresa
					FROM 	pessoa p
					WHERE 	1 ".getidempresa('p.idempresa','evento')."
						AND p.status 		= 'ATIVO'
						AND p.idtipopessoa 	= 1
						AND p.idpessoa 		in (".$resultpessoa.");";
							
	return $sqlPessoa;
}

function sqlImGrupo($resultsetor)
{
	$sqlImGrupo = "		SELECT 	i.idimgrupo,
								i.grupo,
								i.idempresa
						FROM 	imgrupo i 
						WHERE 	i.status	= 'ATIVO'
							AND	i.idimgrupo	in (".$resultsetor.")
								".getidempresa('i.idempresa','evento').";";
								
	return $sqlImGrupo;
}

//Função para verificar se o cliente pode entrar no evento (31-01-2020 - Lidiane)
function sqlEventoTipoResp($ideventotipo, $idobjeto, $idEmpresa)
{
	$sqlFuncEvento = "SELECT fo.* FROM fluxo f join fluxoobjeto fo on fo.idfluxo = f.idfluxo
	where f.idobjeto = ".$ideventotipo." AND f.tipoobjeto = 'ideventotipo'
	AND fo.idobjeto = ".$idobjeto." and fo.tipo = 'PARTICIPANTE'";
	return $sqlFuncEvento;
}

function criaAssinatura($idPessoa, $modulo, $idmodulo) 
{
	require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
	 
    
	if ( $modulo == 'documento'){
		$sql = "SELECT 
					c.idcarrimbo, if(s.versao = c.versao, null, s.versao) as versao
				FROM
					sgdoc s
				JOIN
					carrimbo c on s.idsgdoc = c.idobjeto and (s.versao = c.versao or c.versao = 0)
				WHERE 
					c.status      in ('PENDENTE', 'ATIVO')
					AND c.idpessoa    = ".$idPessoa."
					AND c.idobjeto    = ".$idmodulo."
					AND c.tipoobjeto  = '".$modulo."'
					LIMIT 1";
			
			$sqld="SELECT 
					s.versao 
				FROM
					sgdoc s
							WHERE  idsgdoc   = ".$idmodulo;
			$resd = d::b()->query($sqld) or die("Erro vesao do documento para assinatura: ".mysqli_error(d::b()));
			$rowd=mysqli_fetch_assoc($resd);
		
	}else{
	 	$sql = "SELECT 
				c.idcarrimbo,
				c.status					
			FROM 
				carrimbo c
			WHERE 
			 c.status      in ('PENDENTE', 'ATIVO')				
				AND c.idpessoa    = ".$idPessoa."
				AND c.idobjeto    = ".$idmodulo."
				AND c.tipoobjeto  = '".$modulo."'
				limit 1;";
 	}

   // if ($idPessoa != $_SESSION["SESSAO"]["IDPESSOA"]) {

        $res = d::b()->query($sql) or die("Erro ao executar consulta de Assinatura: ".mysqli_error(d::b()));

        if (!(mysqli_num_rows($res))) 
		{
			$idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'PENDENTE');
            $intabela               = new Insert();
            $intabela->setTable("carrimbo");
            $intabela->idempresa     = $_SESSION["SESSAO"]["IDEMPRESA"];
            $intabela->idpessoa      = $idPessoa;
            $intabela->idobjeto      = $idmodulo;
            $intabela->tipoobjeto    = $modulo;
			$intabela->idobjetoext   = $idfluxostatus;
            $intabela->tipoobjetoext = 'idfluxostatus';
            $intabela->status       = "PENDENTE";
            if ( $modulo == 'documento'){
                $intabela->versao       = $rowd['versao'];
            }
            $idtabela=$intabela->save();
        }
       //die('mers');
    //}
}

function listaPessoaEventoRelatorio()
{
	global $_1_u_evento_idevento, $_1_u_evento_idpessoa,$_1_u_evento_modulo,$_1_u_evento_idmodulo, $modelo;
	$s = "SELECT r.idfluxostatuspessoa,s.nomecurto,s.idpessoa, r.visualizado, r.oculto, r.inseridomanualmente,r.criadopor,r.criadoem,
				 r.status,s.idtipopessoa, g.grupo, ss.setor, rg.idfluxostatuspessoa as idfluxostatuspessoagrupo,es.rotuloresp as respstatus,
				 es.cor as respcor,r.assinar, et.anonimo,if(e.idpessoa = r.idobjeto, 'Y', 'N') as dono, g.idimgrupo
			FROM fluxostatuspessoa r JOIN evento e on r.idmodulo = e.idevento AND r.modulo = 'evento'
			JOIN eventotipo et on et.ideventotipo = e.ideventotipo
			JOIN pessoa s on s.idpessoa = r.idobjeto and r.tipoobjeto ='pessoa'
	   LEFT JOIN imgrupo g on g.idimgrupo = r.idobjeto AND r.tipoobjeto = 'idimgrupo'
	   LEFT JOIN pessoaobjeto ps on ps.idpessoa =  s.idpessoa
	   LEFT JOIN sgsetor ss on ss.idsgsetor = ps.idobjeto and ss.status = 'ATIVO'  
	   LEFT JOIN fluxostatuspessoa rg on rg.idobjeto = r.idobjetoext and rg.idmodulo = r.idmodulo AND rg.modulo = 'evento'
	   LEFT JOIN fluxostatus fs on rg.idfluxostatus = fs.idfluxostatus
	   LEFT JOIN "._DBCARBON."._status es on(es.idstatus=fs.idstatus)
	       WHERE e.idevento = '".$_1_u_evento_idevento."'
		GROUP BY s.nome
		ORDER BY g.grupo, s.nome";

	$rts = d::b()->query($s) or die("listaPessoa: ". mysqli_error(d::b()));

	while ($r = mysqli_fetch_assoc($rts)) 
	{
		echo '<tr style="height: 25px;">';
		$cor = $r['respcor'];
		$respstatus = $r['respstatus'];
		if(!empty($_1_u_evento_modulo) and !empty($_1_u_evento_idmodulo))
		{
			$versao=0;
			$cassinar='Y';
			if($_1_u_evento_modulo == 'documento')
			{
				$sqld="SELECT s.versao 
						 FROM sgdoc s
						WHERE idsgdoc = ".$_1_u_evento_idmodulo;
				$resd = d::b()->query($sqld) or die("Erro vesao do documento para assinatura: ".mysqli_error(d::b()));
				$rowd=mysqli_fetch_assoc($resd);
				$versao = $rowd['versao'];                   

				$sqlx = "SELECT c.idcarrimbo,c.status,if(s.versao = c.versao, null, s.versao) AS versao
						   FROM sgdoc s JOIN carrimbo c ON s.idsgdoc = c.idobjeto AND (s.versao = c.versao or c.versao = 0)
						  WHERE c.status in ('PENDENTE', 'ATIVO')
							AND c.idpessoa = ".$r['idpessoa']."
							AND c.idobjeto = ".$_1_u_evento_idmodulo."
							AND c.tipoobjeto = '".$_1_u_evento_modulo."'                                   
							LIMIT 1";
				$resx = d::b()->query($sqlx) or die("Erro versao assinada do documento para assinatura: ".mysqli_error(d::b()));
				$rowx=mysqli_fetch_assoc($resx);
				if($rowx['status']=='PENDENTE'){
					$clbt="warning";
					$cassinar='N';
				}elseif($rowx['status']=='ATIVO'){
					$clbt="success";
				}else{
					$clbt="default";
				}
			}else{
				$versao=0;
				$sqlx = "SELECT c.idcarrimbo,c.status
						   FROM carrimbo c 
						  WHERE c.status in ('PENDENTE', 'ATIVO')
							AND c.idpessoa    = ".$r['idpessoa']."
							AND c.idobjeto    = ".$_1_u_evento_idmodulo."
							AND c.tipoobjeto  = '".$_1_u_evento_modulo."'   
					   ORDER BY idcarrimbo desc
						  LIMIT 1"; 
				$resx = d::b()->query($sqlx) or die("Erro versao assinada do anexo para assinatura: ".mysqli_error(d::b()));
				$rowx=mysqli_fetch_assoc($resx);
				if($rowx['status']=='PENDENTE')
				{																
					$clbt="success-signature"; 															
					$cassinar='N';
					$idcarrimbo = $rowx['idcarrimbo'];
				}elseif($rowx['status']=='ATIVO'){
					$clbt="success disabled";
				}else{
					$clbt="default";
				}
			}

			$title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
			if ($r["setor"]){
				$cl = "&nbsp<span style='background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
			}else{
				$cl = '';
			}	

			if($rowx['status'] == 'ATIVO'){
				$inbtstatus = "<button onclick=\"criaassinatura(".$r['idpessoa'].",'".$_1_u_evento_modulo."',".$_1_u_evento_idmodulo.",".$versao.",'".$cassinar."',".$r['idfluxostatuspessoa'].",".$idcarrimbo.")\" type='button' class=' btn btn-xs btn-".$clbt."  hovercinza pointer floatright ' title='Solicitação de Assinatura' style='margin-right: 8px; border-radius: 4px;font-size:9px'><i class='fa fa-check'></i>&nbsp;Assinatura</button>";
			} else {
				$inbtstatus = '';
			}

			if ($modelo == 'xs'){
			echo "<td><span data-toggle='collapse' href='#".$r['idpessoa']."' title=".$respstatus." onclick='carregaFiltroTipoEvento(".$r['idfluxostatuspessoa'].", ".$r['idpessoa'].", ".$_1_u_evento_idevento.");' class='circle button-".$cor."' style='background:".$cor."; border:none; width:0	'></span> ".$r['respstatus']."</td>
				  <td>".$r["nomecurto"]."</td>
				  <td>".$cl."</td>
				  <td><div class='col-md-".$md2."'><div style='float:right;font-size:9px;cursor:default;' class='btn btn-xs btn-".$clbt."'><i class='fa fa-check'></i>&nbsp;Assinatura</button></div></td>";
			}else{
				echo "<td><span data-toggle='collapse' href='#".$r['idpessoa']."' title=".$respstatus." onclick='carregaFiltroTipoEvento(".$r['idfluxostatuspessoa'].", ".$r['idpessoa'].", ".$_1_u_evento_idevento.");' class='circle button-".$cor."' style='background:".$cor."; border:none; width:0'></span> ".$r['respstatus']."</td>
					  <td>".$r["nomecurto"]."</td>
					  <td>".$cl."</td>
					  <td><div class='col-md-".$md2."'>".$inbtstatus."</div><div class='col-md-".$md3."'>".$botao."</div></td>";
			}

			if($rowx['idcarrimbo']){
				$idcarrimbo = $rowx['idcarrimbo'];
			} else {
				$idcarrimbo = 0;
			}	
		}

		$pad = 'padding: 2px 24px;';

		if ( $r['oculto'] == '1'){
			$op = 'opacity:0.5;';
		}else{
			$op ='';
		}


		if ($grupo != $r["grupo"]){
			echo '</tr><tr><td colspan="4" style="padding-top: 15px;">';
			if ($grupo != ''){
				echo '</div></fieldset>';
			}
			$grupo = $r["grupo"];
			if($_SESSION["SESSAO"]["IDPESSOA"] != $_1_u_evento_idpessoa){
				echo "<div style='padding:0px 6px;'><legend class='scheduler-border'>".$grupo." <a class='fa fa-bars pointer hoverazul' style='color: #23527c; text-decoration: none;' title='Grupo' onclick=\"janelamodal('../?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></legend>";
			}else{
				if ($modelo == 'xs'){
					echo "<div style='padding:0px 6px;'><legend class='scheduler-border'>".$grupo." <a class='fa fa-bars pointer hoverazul' style='color: #23527c; text-decoration: none;' title='Grupo' onclick=\"janelamodal('../?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></legend>";
				}else{
					echo "<div style='padding:0px 6px;'><legend class='scheduler-border'>".$grupo." <a class='fa fa-bars pointer hoverazul' style='color: #23527c; text-decoration: none;' title='Grupo' onclick=\"janelamodal('../?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></legend>";
				}
			}
			echo '</td></tr><tr>';
		}	

		if (!empty($r["grupo"])){
			$pad = '';
		}

		if($r['idtipopessoa']==1){
			$mod='funcionario';
		}else{
			$mod='pessoa';
		}

		if ($r['oculto']== '1'){
			$vs = "<i class='fa fa-eye-slash' style='font-size: 14px;color:silver'></i>&nbsp";
		}elseif ($r["visualizado"] == '1'){
			$vs = "<i class='fa fa-check' style='font-size: 14px;color:#4FC3F7'></i>&nbsp";
		}else{
			$vs = "<i class='fa fa-check' style='font-size: 14px;color:#fff'></i>&nbsp";
		}

		if ($r['aprova'] == 1){
			$va = "<i class='fa fa-edit' style='font-size: 14px;color:#fff'></i>&nbsp";
		}else{
			$va = "<i class='fa fa-edit' style='font-size: 14px;color:#fff'></i>&nbsp";	
		}

		if ($r["anonimo"] == 'Y' && $r["dono"] == 'Y'){
			$r["nomecurto"] = '<i><b>ANÔNIMO</b></i>';
			$cl = '';
		}
	}
	
	echo '</tr>';    
}

// Agrupar o contapagaritem no tabela contapagar
// Agrupar em uma contapagar por pessoa e formapagamento           
// Agrupar em uma contapagar por forma de pagamento 

//*não usar mais usar a api nf cnf::agrupaCP();  */
/*
function agrupaContapagar($fluxo){
  
  
    $sql="select i.idcontapagaritem,i.idpessoa,i.idformapagamento,i.idagencia,i.idcontaitem,
                month(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as mes,
                year(LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as ano,
                (LAST_DAY(i.datapagto) + INTERVAL ifnull(f.diavenc,1) DAY) as datavencimento,
                DATE_ADD((LAST_DAY(i.datapagto) + INTERVAL ifnull(f.diavenc,1) DAY), INTERVAL 1 MONTH) as datavencimentoseq,
                (LAST_DAY(i.datapagto) + INTERVAL 1 DAY) as inicio,
                LAST_DAY(LAST_DAY(i.datapagto) + INTERVAL 1 day) as fim,
                i.datapagto,
                f.agruppessoa,
                f.agrupfpagamento,
                f.agrupnota,
                i.idobjetoorigem,               
                i.tipoobjetoorigem,
                i.valor,
                i.parcela,
                i.parcelas,
                i.tipo,
                i.visivel,
				f.previsao,
				i.status
        from contapagaritem i join 
                formapagamento f on(i.idformapagamento=f.idformapagamento)
            where i.status IN ('ABERTO','PENDENTE','PAGAR')
                and (idcontapagar is null or  idcontapagar='')
                and i.idpessoa is not null and i.idpessoa !=''
                and i.idformapagamento is not null and i.idformapagamento !=''
                and i.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                and i.idagencia is not null and i.idagencia !=''";
    $res= d::b()->query($sql) or die($sql."Erro ao buscar contapagaritem agrupado por pessoa para agrupamento: <br>".mysqli_error());
    
    while($row=mysqli_fetch_assoc($res)){
        //se for comissao o tipo da conta agrupadora e REPRESENTACAO por comportar de forma diferente das demais
        $sqlfo="select * from confcontapagar where idformapagamento =".$row['idformapagamento']." and tipo='COMISSAO' and status='ATIVO'";
        $resfo= d::b()->query($sqlfo) or die($sql."Fala ao buscar se forma de pagamento e comissao: <br>".mysqli_error());
        $qtdfo=mysqli_num_rows($resfo);
        if($qtdfo>0){$tipoespecifico='REPRESENTACAO';}else{$tipoespecifico='AGRUPAMENTO';}
        
        if($row['agrupnota']=='Y'){
            $qtd1=0;
        }elseif($row['agruppessoa']=='Y'){
            //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
            $sql1="select * from contapagar c 
                    where c.idpessoa = ".$row['idpessoa']."
                    and c.idformapagamento= ".$row['idformapagamento']."
                    and c.idagencia = ".$row['idagencia']."
                    and c.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                    and c.status='ABERTO'
                    and c.tipoespecifico='".$tipoespecifico."'
                    and c.datareceb >= '".$row['datavencimento']."' 
                    -- and '".$row['fim']."'  
                    order by c.datareceb asc limit 1";  
            $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error());
            $qtd1=mysqli_num_rows($res1);
        }else{
            //alterado a buscar para pegar a primeira em aberto apartir da data de vencimento datapagto 11-09-2020 hermesp
            $sql1="select * from contapagar c 
                    where c.idformapagamento= ".$row['idformapagamento']."
                    and c.idagencia = ".$row['idagencia']."                   
                    and c.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                    and c.status='ABERTO'
                    and c.tipoespecifico='".$tipoespecifico."'
                    and c.datareceb >= '".$row['datapagto']."' 
                   -- and '".$row['fim']."' 
                    order by c.datareceb asc limit 1";  
            $res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar para agrupar por pessoa e formapagto: <br>".mysqli_error());
            $qtd1=mysqli_num_rows($res1);
  
        }
            
		if($qtd1>0){
			$row1=mysqli_fetch_assoc($res1);
			$squ="update contapagaritem set idcontapagar=".$row1['idcontapagar']." where idcontapagaritem=".$row['idcontapagaritem'];
			$reu= d::b()->query($squ) or die($squ."Erro vincular contapagaritem na contapagar: <br>".mysqli_error());
		}else{
			/* 
			* Fatura cartão: ao lançar um item de conta, 
			* verificar se ha  uma fatura "pendente e/ou quitado"
			* no mes do lançamento. Caso haja, jogar para o proximo mes.                     * 
			*/
			/*
			if($row['agrupnota']=='Y'){
				
				$datavencimento=$row['datapagto'];
				
			}*/
			/*elseif($row['agruppessoa']=='N'){                 
				
				$sql1="select * from contapagar c 
					where c.idformapagamento= ".$row['idformapagamento']."
					and c.idagencia = ".$row['idagencia']."
					and c.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
					and c.status in ('PENDENTE','QUITADO')
					and c.tipoespecifico='AGRUPAMENTO'
					and c.datareceb between '".$row['inicio']."' and '".$row['fim']."' order by c.datareceb asc limit 1";  
				$res1= d::b()->query($sql1) or die($sql1."Erro ao buscar contapagar PENDENTE ou QUITADO: <br>".mysqli_error());
				$qtd1=mysqli_num_rows($res1);
				if($qtd1>0){
					$datavencimento= $row['datavencimentoseq'];//data do mês sequinte
				}else{
					$datavencimento= $row['datavencimento'];
				}
			}*/
			/*else{
				$datavencimento=$row['datavencimento'];
			}
			
			
			$inscontapagar = new Insert();
			$inscontapagar->setTable("contapagar");
			
			$inscontapagar->idagencia = $row['idagencia'];
							
			if($row['agruppessoa']=='Y'){
				$inscontapagar->idpessoa 	= $row['idpessoa'];
				$inscontapagar->status 		= 'ABERTO';
				$inscontapagar->parcela 	= 1;                                
				$inscontapagar->parcelas 	= 1;

				if(!empty($row['idcontaitem'])){
					$inscontapagar->idcontaitem = $row['idcontaitem'];
				}

				$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'ABERTO');
				$inscontapagar->idfluxostatus = $idfluxostatus;
			}elseif($row['agrupnota']=='Y'){
				$inscontapagar->idpessoa 	= $row['idpessoa'];
				$inscontapagar->tipoobjeto 	= $row['tipoobjetoorigem'];
				$inscontapagar->idobjeto 	= $row['idobjetoorigem'];
				$inscontapagar->parcela 	= $row['parcela'];
				$inscontapagar->parcelas 	= $row['parcelas'];
				$inscontapagar->valor 		= $row['valor'];
				$inscontapagar->status 		= $row['status'];

				if(!empty($row['idcontaitem'])){
					$inscontapagar->idcontaitem = $row['idcontaitem'];
				}

				$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', $row['status']);
				$inscontapagar->idfluxostatus = $idfluxostatus;
			}else{
				$inscontapagar->idcontaitem = 46;
				$inscontapagar->status 		= 'ABERTO';
				$inscontapagar->parcela 	= 1;                                
				$inscontapagar->parcelas 	= 1;

				$idfluxostatus = FluxoController::getIdFluxoStatus('contapagar', 'ABERTO');
				$inscontapagar->idfluxostatus = $idfluxostatus;
			}
			
			$inscontapagar->idformapagamento = $row['idformapagamento'];
			if(!empty($row['previsao']) and $row['agrupnota']!='Y'){
				$inscontapagar->valor = $row['previsao'];
			}
							
							
			$inscontapagar->tipo 			= $row['tipo'];
			$inscontapagar->visivel 		= $row['visivel'];
			$inscontapagar->tipoespecifico 	= $tipoespecifico;
			$inscontapagar->datapagto 		= $datavencimento;
			$inscontapagar->datareceb 		= $datavencimento;
			
			$idcontapagar=$inscontapagar->save();                            
							
			$sqlu="UPDATE contapagaritem set idcontapagar =".$idcontapagar."
						where idcontapagaritem =".$row['idcontapagaritem']." and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
			d::b()->query($sqlu) or die("erro ao atualizar contapagaritem com novo contapagar sql=".$sqlu);
		}
        
     
    }// while($row=mysqli_fetch_assoc($res)){ 
}
*/

function getInsertUpdateObjempresa($idpessoa){
	//------------ Setar a empresa para os tipos funcionário(1) ou Representante(15) na tabela objempresa para que apareça no evento ----------//
	//Lidiane (07/05/2020)
	$sqlObjeto="SELECT * FROM objempresa WHERE idobjeto = ".$idpessoa." AND objeto = 'pessoa' AND empresa = '".cb::idempresa()."'";
	$resObjeto = d::b()->query($sqlObjeto) or die("A Consulta dos objeto falhou :".mysql_error()."<br>Sql:".$sqlObjeto);
	$qtdObjeto = mysqli_num_rows($resObjeto);
	if($qtdObjeto > 0)
	{
		$sql="UPDATE objempresa SET empresa = '".cb::idempresa()."', alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."', alteradoem = sysdate() WHERE idobjeto = ".$idpessoa." AND objeto = 'pessoa' AND empresa = '".cb::idempresa()."'";
		$res=d::b()->query($sql) or die("Erro ao atualizar objempresa: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
		if(!$res)
		{
			d::b()->query("ROLLBACK;");
			die("1-Falha ao atualizar as objeto do Tipo Plantel Pessoa: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
		}
	} else {
		$sql="INSERT INTO objempresa (idempresa, idobjeto, objeto, empresa, criadoem, criadopor)
			  VALUES (".cb::idempresa().", ".$idpessoa.", 'PESSOA', ".cb::idempresa().",sysdate(), '".$_SESSION["SESSAO"]["USUARIO"]."')";
		$res=d::b()->query($sql) or die("Erro ao vincular representante ao cliente: <br> sql=".$sql." <br> ".mysqli_error(d::b()));
		if(!$res){
			d::b()->query("ROLLBACK;");
			die("1-Falha ao vincular representante ao cliente: " .mysqli_error(d::b()). "<p>SQL: ".$sql);
		} 
	}
	//------------ Setar a empresa para os tipos funcionário(1) ou Representante(15) na tabela objempresa para que apareça no evento ----------//
}
//migrado para api/nf

function getDadosConfContapagar($tipo){
    $sqlrep = "select * from confcontapagar where status='ATIVO' and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and tipo='".$tipo."'";
    $resrep = d::b()->query($sqlrep) or die("A Consulta de configuração automatica contapagar falhou :".mysqli_error()."<br>Sql:".$sqlrep);
    $qtdresp = mysqli_num_rows($resrep); 

    $rowrep= mysql_fetch_assoc($resrep);
         
    return $rowrep;    
}

function getModuloPadrao($_modulo, $idunidade)
{
	$sqlModulo = "SELECT modulo
					FROM unidadeobjeto uo JOIN "._DBCARBON."._modulo m ON m.modulo = uo.idobjeto AND m.modulotipo = '$_modulo'
					WHERE idunidade = '$idunidade' AND uo.tipoobjeto = 'modulo';";
	
	$resModulo = d::b()->query($sqlModulo) or die(mysqli_error(d::b())." Erro ao buscar fluxo getIdFluxoStatus ".$sqlModulo);
	$rowModulo = mysqli_fetch_assoc($resModulo);

	return $rowModulo['modulo'];
}

function getModuloTipo($_modulo, $idunidade)
{
	$sqlModulo = "SELECT modulotipo
					FROM unidadeobjeto uo JOIN "._DBCARBON."._modulo m ON m.modulo = uo.idobjeto AND m.modulo = '$_modulo'
					WHERE idunidade = '$idunidade' AND uo.tipoobjeto = 'modulo';";
	
	$resModulo = d::b()->query($sqlModulo) or die(mysqli_error(d::b())." Erro ao buscar fluxo getIdFluxoStatus ".$sqlModulo);
	$rowModulo = mysqli_fetch_assoc($resModulo);

	return $rowModulo['modulotipo'];
}

function getFluxoHistoricoIdFormalizacao($idformalizacao, $idloteativ, $modulo, $status = null)
{
	if(!$modulo){
		die("[Erro] Módulo vazio.");
	}

	if(!empty($status)){$sqlStatus = "AND fh.status = 'PENDENTE'";}
	$sqlFluxo = "SELECT fh.idfluxostatushist, fh.status
				   FROM formalizacao f JOIN loteativ la ON la.idlote = f.idlote 
			  LEFT JOIN fluxostatushist fh ON fh.idmodulo = '$idformalizacao' and fh.modulo = '".$modulo."' AND la.idfluxostatus = fh.idfluxostatus
				  WHERE la.idloteativ = '$idloteativ' AND fh.status <> 'INATIVO' $sqlStatus;";
	$resIdFluxo = d::b()->query($sqlFluxo) or die(mysqli_error(d::b())." erro ao buscar histórico da Formalização ". $sqlFluxo);
	$rowFormalizacao = mysql_fetch_assoc($resIdFluxo);
	return $rowFormalizacao;
}

function insertFormalizacao($idunidade, $idlote, $fluxo, $idempresa, $idloteorigem = NULL)
{
	if(!empty($idloteorigem)){ $idloteProproc = $idloteorigem; } else { $idloteProproc = $idlote; }
	$select = "SELECT pp.idprproc, if(s.status = 'APROVADO', 'TRIAGEM', 'ABERTO') AS 'statussolfab'
				 FROM prodservformula f JOIN prodservformulains pi ON pi.idprodservformula = f.idprodservformula
				 JOIN procprativinsumo pai ON pai.idprodservformulains = pi.idprodservformulains
				 JOIN prodservprproc pp ON pai.idprodservprproc = pp.idprodservprproc
				 JOIN lote l ON l.idprodservformula = pi.idprodservformula
			LEFT JOIN solfab s ON s.idsolfab = l.idsolfab
				WHERE l.idlote = ".$idloteProproc."  and pi.status='ATIVO' limit 1";
	$rescv = d::b()->query($select) or die("saveposchange__loteprodução: Falha ao buscar idprproc");
    $rcv = mysqli_fetch_assoc($rescv);

	if(!empty($rcv['idprproc']))
	{
		$idfluxostatus = FluxoController::getIdFluxoStatus('formalizacao', $rcv['statussolfab'], $rcv['idprproc'], 'INICIO');

		$sqlf="INSERT INTO formalizacao (idempresa, 
	 								  idunidade, 
									  idlote, 
									  idprproc, 
									  exercicio, 
									  idfluxostatus,
									  status,
									  criadopor, 
									  criadoem, 
									  alteradopor, 
									  alteradoem)
				              VALUES (".$idempresa.",
							  		  ".$idunidade.",
									  ".$idlote.",
									  ".$rcv['idprproc'].",
									  '".date('Y')."',
									  '$idfluxostatus',
									  '".$rcv['statussolfab']."',
									  '".$_SESSION["SESSAO"]["USUARIO"]."',
									  sysdate(),									  
									  '".$_SESSION["SESSAO"]["USUARIO"]."',
									  sysdate())";
		mysql_query($sqlf) or die ("Erro ao inserir formalizacao 1: ".$sqlf ." ". mysqli_error(d::b()));
		$lastinsert = d::b()->insert_id;

		//Insere a Hist da Formalização
		FluxoController::inserirFluxoStatusHist('formalizacao', $lastinsert, $idfluxostatus, 'ATIVO');

		//Insere o Fluxo do Lote correspondente a Formalização
		$idfluxostatusLote = FluxoController::getIdFluxoStatus('loteproducao', $rcv['statussolfab']);
		if($idfluxostatusLote){
			FluxoController::atualizaModuloTab('loteproducao', 'idlote', $idlote, $rcv['statussolfab'], $idfluxostatusLote);
			FluxoController::inserirFluxoStatusHist('loteproducao', $idlote, $idfluxostatusLote, 'PENDENTE');
		} else {
			die("Configurar fluxo do Lote: ".$rcv['statussolfab']);
		}		

	} else {
		
		$sqlf="INSERT INTO formalizacao (idempresa, 
										idunidade, 
										idlote, 
										exercicio, 
										status,
										criadopor, 
										criadoem, 
										alteradopor, 
										alteradoem)
								VALUES (".$idempresa.",
										".$idunidade.",
										".$idlote.",
										'".date('Y')."',
										'ABERTO',
										'".$_SESSION["SESSAO"]["USUARIO"]."',
										sysdate(),									  
										'".$_SESSION["SESSAO"]["USUARIO"]."',
										sysdate())";
		mysql_query($sqlf) or die ("Erro ao inserir formalizacao 2: ".$sqlf ." ". mysqli_error(d::b()));

		//Insere o Fluxo do Lote correspondente a Formalização
		$idfluxostatusLote = FluxoController::getIdFluxoStatus('loteproducao', 'ABERTO');
		if($idfluxostatusLote){
			FluxoController::atualizaModuloTab('loteproducao', 'idlote', $idlote, 'ABERTO', $idfluxostatusLote);
			FluxoController::inserirFluxoStatusHist('loteproducao', $idlote, $idfluxostatusLote, 'PENDENTE');
		} else {
			die("Configurar fluxo do Lote: ABERTO");
		}
	}
}

function congelaNfCotacao($idnf, $tipocotacao)
{
	//NF - Cotação
	$aRes["cotacao"]["sql"] = "SELECT * FROM nf WHERE idnf = ".$idnf;
	$aRes["cotacao"]["res"] = sql2array($aRes["cotacao"]["sql"],true);
	
	d::b()->query("insert into log 
					(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
					(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','NF OK',now())");

	//Orçamento
	$aRes["orcamento"]["sql"] = "SELECT * FROM cotacao WHERE idcotacao = ".$aRes["cotacao"]["res"]["idobjetosolipor"];
	$aRes["orcamento"]["res"] = sql2array($aRes["orcamento"]["sql"],true);

	d::b()->query("insert into log 
					(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
					(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','cotacao OK',now())");

	//Fornecedor
	$aRes["fornecedor"]["sql"] = "SELECT * 
									FROM pessoa p INNER JOIN endereco e ON p.idpessoa = e.idpessoa
									JOIN nfscidadesiaf c ON e.codcidade = c.codcidade
								   WHERE p.idpessoa = ".coalesce($aRes["cotacao"]["res"]["idpessoa"],"null");
	$aRes["fornecedor"]["res"] = sql2array($aRes["fornecedor"]["sql"],true);
	d::b()->query("insert into log 
					(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
					(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','Fornecedor OK',now())");
	//Empresa
	$aRes["empresa"]["sql"] = "SELECT * FROM empresa WHERE idempresa = ".coalesce($aRes["cotacao"]["res"]["idempresa"],"null");
	$aRes["empresa"]["res"] = sql2array($aRes["empresa"]["sql"],true);
	d::b()->query("insert into log 
					(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
					(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','empresa OK',now())");

	//Finalidade
	$aRes["finalidade"]["sql"] = "SELECT c.idfinalidadeprodserv, c.finalidadeprodserv
									FROM finalidadepessoa i JOIN finalidadeprodserv c ON c.idfinalidadeprodserv = i.idfinalidadeprodserv
								   WHERE c.status = 'ATIVO'	
								   	 ".getidempresa('c.idempresa', 'finalidadeprodserv')."				
									 AND i.idpessoa = ".coalesce($aRes["cotacao"]["res"]["idpessoa"],"null")."
									 AND c.idfinalidadeprodserv = ".coalesce($aRes["cotacao"]["res"]["idfinalidadeprodserv"],"null");
	$aRes["finalidade"]["res"] = sql2array($aRes["finalidade"]["sql"],true);
	d::b()->query("insert into log 
	(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
	(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','Finalidade OK',now())");
	//NFitem
	$aRes["nfitem"]["sql"] = "SELECT p.idprodserv,
									  IF(i.un IS NULL, p.un, i.un) AS unidade,
									  p.descr,
									  CASE
										WHEN p.idprodserv > 0 AND pf.codforn IS NULL THEN CONCAT(p.descr, '-', p.codprodserv)
                                        WHEN p.idprodserv > 0 AND pf.codforn IS NOT NULL THEN pf.codforn
                                        ELSE i.prodservdescr
									  END as codforn,
									  pf.unforn,
									  t.tipoprodserv,
									  i.idnf,
									  i.nfe,
									  i.moeda,
									  i.total,
									  i.valipi,
									  i.des,
									  i.totalext,
									  i.idnfitem,
									  i.qtd,
									  i.qtdsol,
									  i.un AS unidade,
									  p.codprodserv,
									  i.prodservdescr,
									  i.idcontaitem,
									  i.moedaext,
									  i.convmoeda,
									  i.vlritemext,
									  i.vlritem,
									  i.des,
									  i.aliqicms,
									  i.validade,
									  i.previsaoent,
									  i.previsaoentrega,
									  i.obs,
									  (i.total / (IF(IFNULL(i.qtd, 1) = 0, 1, i.qtd) * IF(IFNULL(pf.valconv, 1) = 0, 1, pf.valconv))) AS vlr
								 FROM nfitem i JOIN nf n 
							LEFT JOIN prodserv p ON (p.idprodserv = i.idprodserv)
							LEFT JOIN prodservforn pf ON (pf.idprodserv = i.idprodserv AND pf.idprodservforn = i.idprodservforn AND pf.status = 'ATIVO' AND pf.idpessoa = n.idpessoa)
							LEFT JOIN tipoprodserv t ON (t.idtipoprodserv = p.idtipoprodserv)
						 		WHERE i.idnf = n.idnf
								".getidempresa('i.idempresa','cotacao')."
								  AND i.idnf = ".coalesce($aRes["cotacao"]["res"]["idnf"],"null")."
								  AND i.nfe != 'C'
							 ORDER BY p.descr";
	$aRes["nfitem"]["res"] = sql2array($aRes["nfitem"]["sql"],true, array(), true);
	d::b()->query("insert into log 
	(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
	(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','NFitem OK',now())");
	//Transporte
	$aRes["transporte"]["sql"] = "SELECT p.idpessoa,
										 p.nome,
										 p.observacaore,
										 p.emailresult, 
										 CASE WHEN(n.previsaoentrega< CURDATE() AND n.status='APROVADO') THEN 'atrasado' ELSE 'normal' END AS pedidoentrega,
										 n.status,
										 n.idnf,
										 n.idnforigem,
										 n.idfinalidadeprodserv,
										 n.dtemissao,
										 n.emailaprovacao,
										 n.envioemailorc,
										 n.pedidoext,
										 n.aoscuidados,
										 n.telefone,
										 n.marcartodosnfitem,
										 n.modfrete,
										 n.frete,
										 n.idtransportadora,
										 n.obsenvio,
										 n.formapgto,
										 n.idformapagamento,
										 n.diasentrada,
										 n.parcelas,
										 n.intervalo,
										 n.obs,
										 n.obsinterna
									FROM nf n, pessoa p
								   WHERE n.idnf = ".$idnf." 
										 ".getidempresa('n.idempresa','nf')." AND p.idpessoa = n.idpessoa";
	$aRes["transporte"]["res"] = sql2array($aRes["cotacao"]["sql"],true);
	d::b()->query("insert into log 
	(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
	(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','Transporte OK',now())");
	//Transportadora
	$aRes["transportadora"]["sql"] = "SELECT idpessoa, nome 
										FROM pessoa 
									   WHERE idtipopessoa = 11  
									     AND idpessoa = '".$aRes["transporte"]["res"]["idtransportadora"]."'
									   ".getidempresa('idempresa','pessoa')."  
									     AND status = 'ATIVO'";
	$aRes["transportadora"]["res"] = sql2array($aRes["transportadora"]["sql"],true);
	d::b()->query("insert into log 
	(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
	(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','Transportadora OK',now())");

	//Pagamento
	$aRes["pagamento"]["sql"] = "SELECT idformapagamento, descricao 
								   FROM formapagamento 
								  WHERE status='ATIVO' 
								  	AND debito = 'Y' 
								    AND idformapagamento = '".$aRes["transporte"]["res"]["idformapagamento"]."'
								  ".getidempresa('idempresa','formapagamento');
	$aRes["pagamento"]["res"] = sql2array($aRes["pagamento"]["sql"],true);
	d::b()->query("insert into log 
			(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
			(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','Pagamento OK',now())");
	//Responsalvel Fornecedor
	if($tipocotacao == 'cotacaoforn')
	{
		$aRes["responsavel"]["sql"] = "SELECT c.prazo,n.criadoem,p.nomecurto
										FROM nf n JOIN cotacao c LEFT JOIN pessoa p On c.idresponsavel = p.idpessoa
										WHERE n.idnf = '".$idnf."'
										AND c.idcotacao = n.idobjetosolipor
										AND n.tipoobjetosolipor = 'cotacao'";
		$aRes["responsavel"]["res"] = sql2array($aRes["responsavel"]["sql"],true);
		d::b()->query("insert into log 
		(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
		(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','Responsavel OK',now())");
	}

	$sqlUltimaVersao = "SELECT versaoobjeto
						  FROM objetojson
						 WHERE idobjeto = ".$aRes["cotacao"]["res"]["idobjetosolipor"]." AND tipoobjeto = '$tipocotacao' 
						   AND idsubtipoobjeto = '$idnf' AND subtipoobjeto = 'nf' 
					  ORDER BY idobjetojson DESC LIMIT 1";
	$resUltimaVersao = d::b()->query($sqlUltimaVersao) or die("Erro ao buscar Versão objeto: ".mysqli_error(d::b()).$sqlUltimaVersao);
	$rowsUltimaVersao = mysqli_fetch_assoc($resUltimaVersao);
	$ultimaVersao = $rowsUltimaVersao['versaoobjeto'] + 1;
	d::b()->query("insert into log 
	(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
	(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','Versiona OK',now())");

	if($tipocotacao == 'cotacaoforn')
	{
		$sqlMail = "SELECT idobjetoext, tipoobjetoext
					  FROM objetojson
					 WHERE idobjeto = ".$aRes["cotacao"]["res"]["idobjetosolipor"]." AND tipoobjeto = 'cotacao' 
					   AND idsubtipoobjeto = '$idnf' AND subtipoobjeto = 'nf' 
				  ORDER BY idobjetojson DESC LIMIT 1";
		$resMail = d::b()->query($sqlMail) or die("Erro ao buscar Versão objeto: ".mysqli_error(d::b()).$sqlMail);
		$rowsMail = mysqli_fetch_assoc($resMail);

		$idobjetoext = $rowsMail['idobjetoext'];
		$tipoobjetoext = $rowsMail['tipoobjetoext'];

		if(!empty($idobjetoext))
		{
			$ins = ', idobjetoext, tipoobjetoext';
			$insval = ", '$idobjetoext', '$tipoobjetoext'";
		}
		d::b()->query("insert into log 
		(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
		(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','Transporte OK',now())");
	}

	$icong = "INSERT INTO objetojson (idempresa, idobjeto, tipoobjeto, idsubtipoobjeto, subtipoobjeto, jobjeto, versaoobjeto, criadopor, criadoem, alteradopor, alteradoem $ins)
			  VALUES (".$_SESSION['SESSAO']['IDEMPRESA'].", ".$aRes["cotacao"]["res"]["idobjetosolipor"].", '$tipocotacao', '".$idnf."', 'nf', '".base64_encode(serialize($aRes))."',".$ultimaVersao.",'".$_SESSION['SESSAO']['USUARIO']."',now(),'".$_SESSION['SESSAO']['USUARIO']."',now() $insval)";
	d::b()->query($icong) or die("congelaCotacao: ". mysqli_error(d::b())."\n SQL: ".$icong);
	d::b()->query("insert into log 
	(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
	(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','objetojson OK',now())");
	$sqlaud = "INSERT INTO _auditoria (idempresa, linha, acao, objeto, idobjeto, coluna, valor, criadoem, criadopor, tela) 
                    VALUES (".$_SESSION['SESSAO']['IDEMPRESA'].", '1', 'i', 'objetojson', ".$idnf.", 'jobjeto','".base64_encode(serialize($aRes))."', now(), '".$_SESSION["SESSAO"]["USUARIO"]."', '".$_SERVER["HTTP_REFERER"]."')";
	mysql_query($sqlaud) or die("ERRO ao gerar auditoria Congela Cotacao: ".mysql_error()."\n SQL: ".$sqlaud);
	d::b()->query("insert into log 
	(idempresa,sessao,idobjeto,tipoobjeto,tipolog,log,criadoem) values
	(1,'".session_id()."',$idnf,'congelaNfCotacao','congelaNfCotacao','objetojson OK',now())");
}

function updateObjetojson($idobjeto, $tipoobjeto, $idsubtipoobjeto, $subtipoobjeto, $idobjetoext, $tipoobjetoext)
{
	$sqlObjetojson = "SELECT idobjetoext, tipoobjetoext, idobjetojson
						  FROM objetojson
						 WHERE idobjeto = ".$idobjeto." AND tipoobjeto = '$tipoobjeto' 
						   AND idsubtipoobjeto = $idsubtipoobjeto AND subtipoobjeto = '$subtipoobjeto' 
					  ORDER BY idobjetojson DESC LIMIT 1";
	$resObjetojson = d::b()->query($sqlObjetojson) or die("Erro ao buscar Versão objeto: ".mysqli_error(d::b()).$sqlObjetojson);
	$rowsObjetojson = mysqli_fetch_assoc($resObjetojson);
	$idobjetoextBanco = $rowsObjetojson['idobjetoext'];
	$tipoobjetoextBanco = $rowsObjetojson['tipoobjetoext'];

	if(empty($idobjetoextBanco) && empty($tipoobjetoextBanco))
	{
		$icong = "UPDATE objetojson 
					SET idobjetoext = '$idobjetoext', tipoobjetoext = '$tipoobjetoext'
			 	  WHERE idobjetojson = ".$rowsObjetojson['idobjetojson'];
		d::b()->query($icong) or die("congelaResultado: ". mysqli_error(d::b()));
	}
}

function validarAssinaturaTesteTEA($idamostra)
{
	require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

	$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'ASSINAR', '', '', '');

	//Valida se os testes estão todos assinados e o TEA
	$sqlAssinaturaTesteTEA = "SELECT COUNT(r.idresultado) AS totalresultado,
									 COUNT(ra.idresultado) AS totalassinado,
									 c.status,
									 a.idregistroprovisorio,
									 (SELECT a.idarquivo FROM arquivo a WHERE a.tipoarquivo in('ANEXO','AMOSTRA')  and a.tipoobjeto = 'amostra' and a.idobjeto = '$idamostra' limit 1) AS idarquivo
						 	    FROM amostra a JOIN resultado r ON r.idamostra = a.idamostra
								JOIN fluxostatus fs ON fs.idfluxostatus = a.idfluxostatus
                        	 	JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo = 'ABERTO'
						   LEFT JOIN resultadoassinatura ra ON ra.idresultado = r.idresultado
						   LEFT JOIN carrimbo c ON c.idobjeto = $idamostra AND tipoobjeto = '".$rowFluxo['modulo']."'
						 	   WHERE a.idamostra =  $idamostra;";
	$resAssinaturaTesteTEA = d::b()->query($sqlAssinaturaTesteTEA) or die("Erro ao buscar Assinatura Teste/TEA: ".mysqli_error(d::b()).$sqlAssinaturaTesteTEA);
	$rowsAssinaturaTesteTEA = mysqli_fetch_assoc($resAssinaturaTesteTEA);
	$totalresultado = $rowsAssinaturaTesteTEA['totalresultado'];
	$totalassinado = $rowsAssinaturaTesteTEA['totalassinado'];
	$status = $rowsAssinaturaTesteTEA['status'];
	$idarquivo = $rowsAssinaturaTesteTEA['idarquivo'];

	//Se o TEA for assinado e todos os testes, mudará o status do TRA para 'SOLICITAR ASSINATURA'.
	//Caso ainda não tenha transferido a Amostra, esta não mudará de status.
	$statuscarrimbo = array('ATIVO', 'ASSINADO');
	if(((in_array($status, $statuscarrimbo) || !empty($idarquivo)) && $totalresultado == $totalassinado) && !empty($rowsAssinaturaTesteTEA['idregistroprovisorio']))
	{				
		FluxoController::alterarStatus($rowFluxo['modulo'], 'idamostra', $idamostra, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
	}
}

function getValorTag(){
	
    $_idempresa=cb::idempresa();
	
    $sql2 = "SELECT chave1 FROM sequence WHERE sequence = 'tag' AND idempresa = ".$_idempresa."";
    $res2 = d::b()->query($sql2) or die("A Consulta das ultimas tags falhou :".mysql_error()."<br>Sql:".$sql2);

    $rowt2 = mysqli_fetch_assoc($res2);

	if(empty($rowt2['chave1'])){
		$sql = "INSERT INTO laudo.sequence (sequence, idempresa, chave1, chave2, chave3, exercicio) VALUES ('tag', '$_idempresa', '2', '$_idempresa', '0', YEAR(NOW()))";
		$rowt2['chave1'] = 1;
	} else {	
		$sql = "UPDATE sequence SET chave1 = (chave1 + 1) WHERE sequence = 'tag'  AND idempresa = ".$_idempresa."";
	}

	$res = d::b()->query($sql) or die("ERRO : ".mysql_error(d::b())."\n SQL: ".$sql);

	return $rowt2['chave1'];

}

function getAgencia($idagencia, $idempresa = NULL)
{
	if(!empty($idempresa)){
		$idempresa = "AND idempresa = '$idempresa'";
	}else{
		$idempresa = 'AND idempresa ='.cb::idempresa();
	}
	if(count(getModsUsr("AGENCIAS")) > 0) {$agencias = getModsUsr("AGENCIAS"); } else {$agencias = "''";}
	$sql = "SELECT idagencia, agencia 
			  FROM agencia a 
			 WHERE status = 'ATIVO' 
			   AND idagencia IN (".$agencias.")
			$idempresa  
		  ORDER BY ord";
	
	return fillselect($sql, $idagencia);
}

function getPagamentoFiltro($idformapagamento, $idempresa = NULL)
{
	if(!empty($idempresa)){
		$idempresa = "AND idempresa = '$idempresa'";
	}

	$sql = "SELECT idformapagamento, descricao 
	FROM formapagamento 
	WHERE status = 'ATIVO' AND credito = 'Y'
			$idempresa  
		  ORDER BY descricao";
	
return fillselect($sql, $idformapagamento);

}

function getContaItemSelect($somarel = NULL)
{
	//foi migrado para contaitem_controller: buscarContaItemAtivoShare
	//Apagar após migrar todos.
	$sqlContaItem = "SELECT * FROM (SELECT c.idcontaitem, c.contaitem
									FROM contaitem c 
									WHERE c.status='ATIVO' $somarel
									and c.idempresa=".cb::idempresa()."
									) AS c
                  ORDER BY contaitem";                
    return $sqlContaItem;
}

function getTagShare($tag = NULL)
{
	$sqlTag = "SELECT * FROM (SELECT t.idtag, concat(tag,' - ',t.descricao) as tagdescr
								FROM tag t
							   WHERE t.status in ('DISPONIVEL','ATIVO')
								".getidempresa('t.idempresa','tag')."
							UNION 
							  SELECT t.idtag, concat(tag,' - ',t.descricao) as tagdescr
								FROM tag t
							   WHERE t.status in ('DISPONIVEL','ATIVO', 'ALOCADO')
									".share::otipo('cb::usr')::compartilharCbUserTag("t.idtag").") AS c
                  ORDER BY tagdescr";                
    return $sqlTag;
}

function listarProdutosConcentrados($idpessoa, $idprodserv, $Vqtddisp_exp, $idempresa)
{
	global $semestoque, $arrSementesLote, $haproduzir, $preto, $azul, $laranja, $roxo; 
	$arraProd = array();	
	$faltaestoque = 0;
	foreach($arrSementesLote as $key => $_sementes) 
	{
		echo '<!-- listarProdutosConcentrados '.$arrSementesLote[$key]['produzir'].' - Lote: '.$_sementes['idlote'].' -->';
		if($arrSementesLote[$key]['produzir'] > 0)
		{
			$semestoque = 1;
			$faltaestoque = 1;
			$arraProd[$arrSementesLote[$key]['pool']][$key]['spartida'] = $arrSementesLote[$key]['partida']."/".$arrSementesLote[$key]['exercicio'];
			$arraProd[$arrSementesLote[$key]['pool']][$key]['produzir'] = $arrSementesLote[$key]['produzir'];
			$arraProd[$arrSementesLote[$key]['pool']][$key]['ord'] = $arrSementesLote[$key]['ord'];
			$arraProd[$arrSementesLote[$key]['pool']][$key]['flgalerta'] = $arrSementesLote[$key]['flgalerta'];                                                        
		}
	}

	foreach($arraProd as $pool => $lote) 
	{
		$haproduzir = $haproduzir + 1;
		$pl = 0;
		?>
		<tr >					
			<td title="Concentrado à produzir">
				<span class="label label-danger fonte10 itemestoque  especial especialvisivel">
					<a href="?_modulo=prodserv&_acao=u&idprodserv=<?=$idprodserv?>" target="_blank" style="color: inherit;  font-size: 12px !important;">
						Concentrado: <?=traduzid('prodserv', 'idprodserv', 'codprodserv', $idprodserv)?>
					</a>

					<div class="insumosEspeciais" style="font-size: 10px !important;">
						<?                                              
						$sproduzir = 0;
						foreach($lote as $idlote => $value) 
						{
							$tipificacao = traduzid('lote', 'idlote', 'tipificacao', $idlote);
							if(empty($tipificacao)){
								$tipificacao = 'SEM TIPIFICAÇÃO';
							}
							if($value['flgalerta'] == 'P'){ 
								$estrela = 'preto';
								$preto = $preto + 1;
							}elseif($value['flgalerta'] == 'A'){ 
								$estrela = 'azul';
								$azul = $azul + 1;
							}elseif($value['flgalerta'] == 'R'){ 
								$estrela = 'roxo';
								$roxo = $roxo + 1;
							}else{
								$estrela = 'laranja';
								$laranja = $laranja + 1;
							}
							?>
							<i class="fa fa-star <?=$estrela?> bold fa-1x btn-lg" title="<?=$tipificacao?>"; ></i> 
							<?
							echo($value['spartida']);
							$sproduzir = $sproduzir + $value['produzir'];
							$pl = $pl + 1;
						}
						?>
					</div>
					<span style="font-size: 10px !important">Produzir: <?=recuperaExpoente(tratanumero($sproduzir), $Vqtddisp_exp)?></span>
				</span>
			</td>
			<td>
				<a class="fa fa-plus-circle pointer fade hoververde fa-2x" href="javascript:janelamodal('?_modulo=formalizacao&_acao=i&idpessoa=<?=$idpessoa?>&idprodserv=<?=$idprodserv?>&_idempresa=<?=$idempresa?>')"></a>
			</td>
		</tr> 
	<?                                
	}

	if($_GET['status'] == 'TODOS' && $_GET['tipo'] == 'TODOS')
	{
		GerconcentradolsController::atualizarDashboardGerencimentoConcentrados($haproduzir);
	}

	$ocultarSimNao = $faltaestoque == 0 ? 'ocultar' : 'naoocultar';

	?>
	<tr class='<?=$ocultarSimNao?>' id='<?=$idpessoa?>_<?=$idprodserv?>'>
		<td></td>
	</tr>
	<?
}

function listarItensSolcom($i, $tipo, $cadastrado = false)
{
    global $_1_u_solcom_idsolcom, $readonly, $_1_u_solcom_status, $_1_u_solcom_idpessoa, $escondeCadAss, $_1_u_solcom_idunidade, $comprasMaster, $qtdItensSolcomCancelados;

	$itensSolcom = SolcomController::buscarItensSolcomAssociadosSolmat($_1_u_solcom_idsolcom, $tipo);            
    echo '<!-- ItensSolcom: '.$itensSolcom['sql'].' -->';
	
    if($itensSolcom['qtdLinhas'] > 0)
    {
        foreach($itensSolcom['dados'] as $_itemSolcom)
        {
			if($_itemSolcom['idcotacao'])
			{
				$itensSolcomAssociadosCotacao = SolcomController::buscarItensSolcomAssociadosCotacao($_itemSolcom['idcotacao'], $_itemSolcom['idprodserv']);
            	$qtditensSolcomAssociadosCotacao = $itensSolcomAssociadosCotacao['qtdLinhas'];
			} else {
				$qtditensSolcomAssociadosCotacao = 0;
			}            

            //Retorna os links cadastrados de cada item
            $linkProduto = SolcomController::buscarLinksProdutos($_itemSolcom['idsolcomitem'], 'solcomitem');
            $qtdlinkProduto = $linkProduto['qtdLinhas'];

            ?>
			<div class='panel panel-default'>
				<div class='panel-heading'>
					<div class='row <?= ($cadastrado ? 'cabecalho-item' : 'cabecalho-item-nao-cadastrado') ?>'>
						<? if($cadastrado)
						{ ?>
							<div>Qtd</div>
							<div>Un</div>
							<div>Descrição</div>
							<div></div>
							<div class="text-center">Detalhes</div>
							<div class="text-center">Urgente</div>
							<div class="text-center">Imagem</div>
							<div class="text-center">Orçamento</div>
							<div class="text-center">Solmat Automática</div>
							<div class="text-center">Qtd Solmat</div>
							<div class="text-center">Sol. Mat</div>
							<!-- <div class="text-center">Sol. Tag</div> -->
							<div class="text-center">Ação</div>
						<? } else { ?>
							<div>Qtd</div>
							<div>Un</div>
							<div>Descrição</div>
							<div>Cadastrar/Associar Produto</div>
							<div class="text-center">Detalhes</div>
							<div class="text-center">Urgente</div>                                            
							<div class="text-center">Imagem</div>   
							<div class="text-center">Orçamento</div>
							<div class="text-center">Solmat Automática</div>
							<div class="text-center">Qtd Solmat</div>                                             
							<div class="text-center">Sol. Mat</div>   
							<div class="text-center">Sol. Mat</div>   
							<div class="text-center">Sol. Tag</div> 
							<div class="text-center">Sol. Mat</div>  
							<!-- <div class="text-center">Sol. Tag</div>  -->
							<div id="satus_reprovadoncad" hidden></div>                                     
							<div class="text-center">Ação</div>
						<? } ?>
					</div>
				</div>
				<div class="panel-body bg-white" style="padding: 0 !important;">
					<div class="row dragExcluir align-items-center planilha" idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" <?if($_itemSolcom['urgencia'] == 'Y') {?>style="background-color: mistyrose;" <? } ?>>
						<!-- Idsolcomitem / QTD -->
						<div>
							<input name="_is<?=$i?>_u_solcomitem_idsolcomitem" type="hidden" value="<?=$_itemSolcom['idsolcomitem']?>"> 
							<input class="size7" onkeypress="return (event.charCode >= 48 || event.charCode == 44 || event.charCode == 46)" min="1" name="_is<?=$i?>_u_solcomitem_qtdc" type="number" value="<?=$_itemSolcom['qtdc']?>" <?=$readonly?> vnulo>
						</div>
						<!-- Un -->
						<div>
							<? if($tipo == 'cadastrado' or $tipo == 'fabricado'){ ?>
									<? echo (empty($_itemSolcom['un']) || $_itemSolcom['un'] == 'null') ? '-' : $_itemSolcom['un']; ?>
							<? } else { ?>
								<select id="#unItemSolcom" name="_is<?=$i?>_u_solcomitem_un" class="un"  placeholder="Un" <?=$readonly?>> 
									<option value=""></option>
									<? fillselect(SolcomController::listarUnidadeVolume(), $_itemSolcom['un']);?>
								</select>
							<? } ?>
						</div>
						<!-- Descricao -->
						<div class="px-2 d-flex" title="<?=$_itemSolcom['descr']?>">
							<?
							$desc = empty($_itemSolcom['descrprod']) ? $_itemSolcom['descr'] : $_itemSolcom['descrprod'];

							if($tipo == 'cadastrado' or $tipo == 'fabricado')
							{
								$unidadesProduto = explode(",", SolcomController::buscarGrupoUnidadePorTipoObjeto($_itemSolcom['idprodserv'], 'prodserv'));
								?>
								<div class="col-xs-<?= (in_array($_1_u_solcom_idunidade, $unidadesProduto) ? '12' : '11') ?> px-0">
									<a  title="<?=$_itemSolcom['descr']?>" class="pointer" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$_itemSolcom['idprodserv']?>')"><?=mb_strtoupper($desc,'UTF-8')?></a>
									<input name="_is<?=$i?>_u_solcomitem_idprodserv" type="hidden" value="<?=$_itemSolcom['idprodserv']?>">
								</div>
								<? 
								if(!in_array($_1_u_solcom_idunidade, $unidadesProduto)){
									?>
									<div class="col-xs-1">
										<a title="Produto não pertence a Unidade do Solicitante" style="text-align: end;" class="fa fa-exclamation-triangle fa-1x laranja pointer"></a>
									</div>
									<?
								}
							} else {
								echo mb_strtoupper($desc,'UTF-8');
							}
							?>
						</div>
						<!-- Produto / Servico -->
						<div class="d-flex align-items-center">
							<? if($tipo != 'cadastrado' && $tipo != 'fabricado' && ($_1_u_solcom_status == 'CADASTRO' || $_1_u_solcom_status == 'SOLICITADO')) { ?>
								<span class="pr-2">
									<? if(!empty($_GET['_idempresa'])) { $idempresa = '&_idempresa='.$_GET['_idempresa'];} ?>
									<a class="fa fa-plus-circle verde pointer hoverazul" title="Criar Produto" onclick="janelamodal('?_modulo=prodserv&_acao=i&idsolcomitem=<?=$_itemSolcom['idsolcomitem']?>&nomeprod=<?=strtoupper($_itemSolcom['descr'])?><?=$idempresa?>')"></a>
								</span>
								<span>
									<input type="text" class="size7 idprodservNaoCadastradoItemSolcom" idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" cbvalue="<?=$_itemSolcom['idsolcomitem']?>" placeholder="Selecione o produto / serviço" style="width:100% !important">
								</span>
								<? 
								$escondeCadAss = false;
							} elseif($tipo != 'cadastrado' && $tipo != 'fabricado') {
								$escondeCadAss = true;
							} ?>
						</div>
						<!-- Detalhes -->
						<div class="text-center">
							<? if($qtdlinkProduto > 0 || !empty($_itemSolcom['obs']) || !empty($_itemSolcom['valormedio'])) {$corLocal = 'azul';} else {$corLocal = 'cinza';} ?>
							<i title="Local Compra / Observação" class="fa fa-th-list <?=$corLocal?> pointer hoverazul tip detalheSolcom" id="modallocalcompraobs" idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>"></i>
						</div>
						<!-- Urgente -->
						<div class="text-center">
							<? $checked = ($_itemSolcom['urgencia'] == 'Y') ? 'checked="checked"' : ""; ?>
							<i title="Urgente" class="fa btn-sm fa-info-circle azul pointer hoverazul tip modalUrgente" <?=$checked?>  idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>"></i>                    
						</div>
						<!-- Imagem -->
						<div class="text-center">
							<? 
							if($_itemSolcom['totalArquivo'] == 0) 
							{ 
								?>
								<i id="modalimagens<?=$_itemSolcom['idsolcomitem']?>" idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" class="fa fa-plus-circle fa-1x verde btn-lg pointer modalimagens" title="Adicione uma Imagem do Produto"></i>
							<? } else { ?>
								<i id="modalimagens<?=$_itemSolcom['idsolcomitem']?>" idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" class="fa fa-photo fa-2x btn-lg pointer modalimagens" title="Clique para ver as Imagens do Produto"></i>
							<? } ?>
						</div>
						<!-- Orçamento -->
						<? if($tipo == 'cadastrado' or  $tipo == 'fabricado') { ?>  
							<? if($_itemSolcom['idcotacao'] && $qtditensSolcomAssociadosCotacao > 0) { ?>
								<div class="text-center">
									<label class="idbox">
										<?=$_itemSolcom['idcotacao']?>
										<a title="Orçamento" class="fa fa-bars fade pointer hoverazul" href="?_modulo=cotacao&_acao=u&idcotacao=<?=$_itemSolcom['idcotacao']?>" target="_blank"></a>
									</label>
								</div>
							<? } else { ?>
								<div class="text-center">
									<i title="Orçamento" class="fa btn-sm fa-info-circle azul pointer hoverazul tip" onclick="modalOrcamento('<?=$_itemSolcom['idsolcomitem']?>');"></i>
								</div>
							<? } ?>
							<!-- Sol. Mat Auto -->
							<div class="text-center">
								<? if($_itemSolcom['solmatautomatica'] == 'Y') { ?>
									<i title="Solmat Automática" class="fa fa-check-circle fa-1x verde btn-lg pointer hoverazul tip" idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" valor="<?=$_itemSolcom['solmatautomatica']?>" onclick="alterarAutoSolmat(this,<?=$_itemSolcom['idsolcomitem']?>)"></i>
								<? } else { ?>
									<i title="Solmat Automática" class="fa fa-times-circle fa-1x vermelho btn-lg pointer hoverazul tip" idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" valor="<?=$_itemSolcom['solmatautomatica']?>" onclick="alterarAutoSolmat(this,<?=$_itemSolcom['idsolcomitem']?>)"></i>
								<? } ?>
							</div>
							<!-- Qtd Solmat -->
							<div class="text-center">
								<?
								if($_itemSolcom['solmatautomatica'] == "Y"){?>
									<input class="size7" onkeypress="return (event.charCode >= 48 || event.charCode == 44 || event.charCode == 46)" min="1" name="_is<?=$i?>_u_solcomitem_qtdsolmatautomatica" type="number" value="<?=$_itemSolcom['qtdsolmatautomatica']?>" <?=$readonly?> vnulo>
								<?}else{?>
									-
								<?}?>
							</div>
							<!-- Sol. Mat -->
							<div class="text-center">
								<? 
								$mostrarSolcom = false;
								foreach($itensSolcomAssociadosCotacao['dados'] AS $_nfitemStatus)
								{
									if($_nfitemStatus['status'] == 'CONCLUIDO' || $_nfitemStatus['status'] == 'CONFERIDO'){
										$mostrarSolcom = true;
									}                            
								}
								
								if(empty($_itemSolcom['idsolmat']) && $mostrarSolcom == true) { ?>
									<i idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" title="Solicitação de Materiais" class="fa fa-plus-circle fa-1x verde btn-lg pointer modalsolmat"></i>    
								<?  } elseif(!empty($_itemSolcom['idsolmat'])) { ?>
									<label class="alert-warning">
										<?=$_itemSolcom['idsolmat']?>
										<a title="Solicitação de Materiais" class="fa fa-bars fade pointer hoverazul" href="?_modulo=solmat&_acao=u&idsolmat=<?=$_itemSolcom['idsolmat']?>" target="_blank"></a>
									</label>
								<? } else {
									echo '-';
								}  ?>
							</div>

							<!-- Sol. Tag -->
							<!-- <div class="text-center">
								<? 
								if(empty($_itemSolcom['idsoltag']) && $mostrarSolcom == true && $_itemSolcom['vinculo'] == 'soltag') { ?>
									<i idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" title="Solicitação de Tags" class="fa fa-plus-circle fa-1x verde btn-lg pointer modalsoltag"></i>    
								<?  } elseif(!empty($_itemSolcom['idsoltag'])) { ?>
									<label class="alert-warning">
										<?=$_itemSolcom['idsoltag']?>
										<a title="Solicitação de Materiais" class="fa fa-bars fade pointer hoverazul" href="?_modulo=soltag&_acao=u&idsolmat=<?=$_itemSolcom['idsoltag']?>" target="_blank"></a>
									</label>
								<? } else {
									echo '-';
								}  ?>
							</div> -->
						<? } else { ?>    
							<div class="text-center">-</div>
							<div class="text-center">-</div>
							<div class="text-center">-</div>
						<? } ?>
						
						<!-- Status -->
						<?if($_itemSolcom['status'] == 'REPROVADO' || $_itemSolcom['status'] == 'CANCELADO') 
						{	
							?>
							<div class="text-center">
								<? $motivo =  str_replace('<b>', '', str_replace('</b>', '', explode('Motivo da Reprovação:', $_itemSolcom['descricao']))); ?>
								<label for="" class="alert alert-warning" title="<?=$motivo[1]?>">
									<?=$_itemSolcom['status']?>
								</label>
							</div>
							<?
						} elseif($qtdItensSolcomCancelados > 0){
							?>
							<div></div>
						<?}?>
						<div class="text-right">
						<? if($qtditensSolcomAssociadosCotacao > 0)
							{
								?>
								<i class="fa fa-arrows-v cinzaclaro pointer <?=$tipo?>" title="Estoque"  data-toggle="collapse" idnfitem="<?=$_itemSolcom['idsolcomitem']?>" href="#solcomitem<?=$_itemSolcom['idsolcomitem']?>"></i>
							<? } 

							$arrayStatusAutorizados = array('SOLICITADO', 'AUTORIZADO', 'CADASTRO');
							if(in_array($_1_u_solcom_status, $arrayStatusAutorizados) && $_1_u_solcom_idpessoa != $_SESSION['SESSAO']['IDPESSOA']) { 
								if($_itemSolcom['status'] == 'PENDENTE'){
									?>
									<a class="fa fa-times fa-1x cinzaclaro hoververmelho pointer ui-droppable excluir" onclick="motivoReprovarItem('<?=$_itemSolcom['idsolcomitem']?>', '<?=str_replace('\'', '', $_itemSolcom['descr'])?>')" title="Reprovar Item"></a>
								<? } elseif((($_itemSolcom['status'] == 'CANCELADO' || $_itemSolcom['restauraitemassociado'] == 1) && $comprasMaster == 0) || ($comprasMaster == 1 && $_itemSolcom['status'] != 'PENDENTE')) { ?>
									<a class="fa fa-check-circle fa-1x cinzaclaro pointer ui-droppable excluir" onclick="restaurarItemSolcom('<?=$_itemSolcom['idsolcomitem']?>')" title="Aprovar Item"></a>
								<? }
							} elseif(($_1_u_solcom_status == 'APROVADO' || $_1_u_solcom_status == 'CONCLUIDO') && $_1_u_solcom_idpessoa != $_SESSION['SESSAO']['IDPESSOA'] && $qtditensSolcomAssociadosCotacao == 0) {
								if(($_itemSolcom['restauraitemassociado'] == 1 && !empty($_itemSolcom['idcotacao']) && $_itemSolcom['status'] == 'ASSOCIADO' && $comprasMaster == 0) || ($comprasMaster == 1 && $_itemSolcom['status'] != 'PENDENTE' && $_itemSolcom['status'] != 'REPROVADO')) {
									?>
									<a class="fa fa-warning vermelho fa-1x pointer ui-droppable excluir" onclick="restaurarItemSolcom('<?=$_itemSolcom['idsolcomitem']?>')" title="Restaurar Item"></a>
									<?
								} elseif($_itemSolcom['status'] != 'REPROVADO') {
									?>
									<a class="fa fa-times fa-1x cinzaclaro hoververmelho pointer ui-droppable excluir" onclick="motivoReprovarItem('<?=$_itemSolcom['idsolcomitem']?>')" title="Reprovar Item"></a>
									<?
								} else {
									echo '-';
								}
							} 
							
							if(($_1_u_solcom_status == 'ABERTO' && $_itemSolcom['status'] == 'PENDENTE') && $_1_u_solcom_idpessoa == $_SESSION['SESSAO']['IDPESSOA']){ ?>
								<a class="fa fa-trash fa-1x cinzaclaro pointer ui-droppable excluir" onclick="excluirItemSolcom('<?=$_itemSolcom['idsolcomitem']?>')" title="Excluir Item"></a>
								&nbsp;&nbsp;&nbsp;
							<? } elseif(($_1_u_solcom_status == 'ABERTO' && $_itemSolcom['status'] == 'PENDENTE') && $_1_u_solcom_idpessoa != $_SESSION['SESSAO']['IDPESSOA']) { ?>
								<a class="fa fa-times fa-1x cinzaclaro hoververmelho pointer ui-droppable excluir" onclick="motivoReprovarItem('<?=$_itemSolcom['idsolcomitem']?>')" title="Reprovar Item"></a>
							<? } elseif(($_itemSolcom['status'] == 'REPROVADO' || $_itemSolcom['status'] == 'CANCELADO') && $comprasMaster == 0) { ?>  
								-
							<? } 
							?> 
						</div>
					</div>
				</div>
			<?
			$i++;   
			if($_1_u_solcom_status == 'APROVADO')
			{
				if($qtditensSolcomAssociadosCotacao > 0)
				{
					?>
					<div class="collapse in overflow-hidden transition" id="solcomitem<?=$_itemSolcom['idsolcomitem']?>">
						<table class="table table-striped planilha m-0">
							<tbody>
								<tr style="height:40px;">
									<th></th>
									<th class="sol_cot">Cotação</th>           
									<th class="sol_num">Qtd Sol</th> 
									<th class="sol_num">Qtd</th> 
									<th class="sol_num">Valor Un</th>
									<th class="sol_num">Total</th>
									<th class="sol_num">Previsão Entrega</th>                                    
									<th class="sol_forn">Obs</th>
									<th><b>Status</b></th>
									<th></th>
								</tr>
								<?
								foreach($itensSolcomAssociadosCotacao['dados'] as $_itensAssociados)
								{ 
									$arrayStatusIgual = array('CONCLUIDO', 'APROVADO');
									$arrayStatusDiferente = array('CONCLUIDO', 'APROVADO');
									if(($_itensAssociados['qtd'] > 0 && in_array($_itensAssociados['status'], $arrayStatusIgual)) || (!in_array($_itensAssociados['status'], $arrayStatusDiferente)))
									{
										?>
										<tr>
											<td style="padding: 16px 0;"></td>
											<td><?=$_itensAssociados['idnf']?></td>
											<td>
												<?
												if(in_array($_itensAssociados['status'], $arrayStatusIgual))
													echo $_itensAssociados['qtd'];
												else   
													echo $_itensAssociados['qtdsol'];
												?>
											</td>
											<td>
												<?
												if($_itensAssociados['status'] == 'CONCLUIDO')
													echo $_itensAssociados['qtd'];
												else   
													echo '0.00';
												?>
											</td>            
											<td><?=$_itensAssociados["vlritem"]?></td>   
											<td><?=$_itensAssociados['total']?></td>         
											<td><?=dma($_itensAssociados['previsaoentrega'])?></td> 
											<td>            
												<? 
												$corurgente = (empty($_itensAssociados['obsinterna'])) ? 'cinza' : 'azul'; 
												$mostrarObs = !empty($_itensAssociados['obsinterna']) ? true : false;
												?>
												<i title="Observação" mostrarobs="<?=$mostrarObs?>" idnf="<?=$_itensAssociados['idnf']?>" idprodserv="<?=$_itemSolcom['idprodserv']?>" class="fa btn-sm fa-info-circle <?=$corurgente?> pointer hoverazul tip modalobsinterna px-0"></i>  
												<div class="panel panel-default" hidden>
													<div id="modalobsinterna<?=$_itensAssociados['idnf']?><?=$_itemSolcom['idprodserv']?>" class="panel-body">
														<div style="word-break: break-word;">
															<?=$_itensAssociados['obsinterna']?>
														</div>
													</div>
												</div>
											</td>
											<td>
												<? if($_itensAssociados['status'] == 'CONCLUIDO'){
													echo "<label style='background-color:#9dffb2 !important; padding: 3px 10px;border-radius: 3px;'>".$_itensAssociados['rotulo']."</label>";
												} else{
													echo "<label style='background-color:#e6e6e6 !important; padding: 3px 10px;border-radius: 3px;'>".$_itensAssociados['rotulo']."</label>";
												}   
												?>
											</td>
											<td></td>
										</tr>
										<?
									}
								}?>
							</tbody>
						</table>
					</div>
					<?
				}
			}
			?>
			</div>
            <div class="panel panel-default" hidden>
                <div id="modallocalcompra<?=$_itemSolcom['idsolcomitem']?>" class="panel-body">
                    <div class="row" style="width: 100%;">
                        <div class="col-md-3 head" style="color:#333; text-align: right;">Valor Médio R$:</div>
                        <div class="col-md-9">
                            <input type="text" id="#valormedio" vdecimal name="_is<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_valormedio" placeholder="Informar Valor Médio" class="size10" value="<?=$_itemSolcom['valormedio']?>">
                        </div>
                    </div>
                    <div class="row" style="width: 100%;">
                        <div class="col-md-3 head" style="color:#333; text-align: right;">Local Compra:</div>
                        <div class="col-md-9">
                            <input class="linkCotacao<?=$_itemSolcom['idsolcomitem']?> size30" onkeyup="atualizaCampoLink(this, '<?=$_itemSolcom['idsolcomitem']?>')" idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" type="text" placeholder="Link para Cotação do Produto">
                            <i idsolcomitem="<?=$_itemSolcom['idsolcomitem']?>" class="fa fa-plus-circle fa-1x verde pointer" style="padding-left:10px;" title="Adicionar Link" onclick="insereLink(<?=$_itemSolcom['idsolcomitem']?>)"></i>
                            <?
                            foreach($linkProduto['dados'] as $_link)
                            { 
                                ?>
                                <div class="opcoes" style="width: 100%;">
                                    <div class="col-md-11" style="overflow-wrap: break-word;">
                                        <label><a href="<?=$_link['link']?>" target="_blank"><?=parse_url($_link['link'], PHP_URL_HOST)?></a></label>
                                    </div>
                                    <div class="col-md-1">
                                        <a class="fa fa-times fa-1x cinzaclaro hoververmelho pointer ui-droppable excluir" onclick="excluirLink('<?=$_link['idobjetolink']?>', '<?=$_itemSolcom['idsolcomitem']?>')" title="Excluir Link"></a>
                                    </div>
                                </div>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row" style="width: 100%;">
                        <div class="col-md-3 head" style="color:#333; text-align: right;">Observação:</div>
                        <div class="col-md-9">
                            <input type="hidden" name="_is<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_idsolcomitem" value="<?=$_itemSolcom['idsolcomitem']?>">
                            <textarea id="obslocal<?=$_itemSolcom['idsolcomitem']?>" style="height: 80px;" name="_is<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_obs" placeholder="Informar Detalhes do Produto (material, tamanho, etc)"><?=$_itemSolcom['obs']?></textarea>
                        </div>
                    </div>
                </div>

                <div id="modalmotivoreprocacao<?=$_itemSolcom['idsolcomitem']?>" class="panel-body">
                    <div class="row" style="width: 100%;">
                        <div class="col-md-2 head" style="color:#333; text-align: right;">Motivo:</div>
                        <div class="col-md-10">
                            <input type="hidden" name="_99rep<?=$_itemSolcom['idsolcomitem']?>_i_modulocom_idmodulo" id="_99rep<?=$_itemSolcom['idsolcomitem']?>_i_modulocom_idmodulo" value="<?=$_1_u_solcom_idsolcom?>" disabled>
                            <input type="hidden" name="_99rep<?=$_itemSolcom['idsolcomitem']?>_i_modulocom_modulo" id="_99rep<?=$_itemSolcom['idsolcomitem']?>_i_modulocom_modulo" value="solcom" disabled>
                            <input type="text" list="listadescricao" value="" name="_99rep<?=$_itemSolcom['idsolcomitem']?>_i_modulocom_descricao" id="_99rep<?=$_itemSolcom['idsolcomitem']?>_i_modulocom_descricao" placeholder="Motivo da Reprovação" onkeyup="atualizaCampo(this, '<?=$_itemSolcom['idsolcomitem']?>')" disabled>
                            <datalist id="listadescricao">
                                <?if($tipo == 'cadastrado') { ?>
                                    <option value="MOTIVO DE CANCELAMENTO DE CADASTRO">MOTIVO DE CANCELAMENTO DE CADASTRO</option>
                                <? } else { ?>
                                    <option value="CANCELAMENTO DE ITEM DE COMPRA">CANCELAMENTO DE ITEM DE COMPRA</option>
                                <? } ?>
                            </datalist>
                            <input type="hidden" name="_rep<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_idsolcomitem" id="_rep<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_idsolcomitem" value="<?=$_itemSolcom['idsolcomitem']?>" disabled>
                            <input type="hidden" name="_rep<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_status" id="_rep<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_status" value="REPROVADO" disabled>
                            <input type="hidden" name="_rep<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_descr" id="_rep<?=$_itemSolcom['idsolcomitem']?>_u_solcomitem_descr" value="<?=$_itemSolcom['descr']?>" disabled>
                        </div>
                        <div class="col-md-12">
                            <button type="button" onclick="reprovarItemSolcom(<?=$_itemSolcom['idsolcomitem']?>)" class="btn btn-danger btn-sm" style="float: right;"><i class="fa fa-circle"></i>Salvar</button>
                        </div>
                    </div>
                </div>

                <div id="modalOrcamento<?=$_itemSolcom['idsolcomitem']?>" class="panel-body">
                    <div class="row" style="width: 100%;padding-left: 2%;padding-right: 2%;padding-bottom: 6%;">
                        <div class="col-md-12" style="background: #e6e6e6; height: 36px; font-weight: bold;">                            
                            <div class="col-md-5">Título</div>
                            <div class="col-md-2">Responsável</div>
                            <div class="col-md-2">Prazo</div>
                            <div class="col-md-1">Status</div>
                            <div class="col-md-1">Cotação</div>
                        </div>
                        <?
						$orcamento = SolcomController::buscarCotacaoDisponivelPorGrupoEsTipoItem($_itemSolcom['idprodserv']);
						$ii = 0;
						foreach($orcamento as $_itemOrcamento)
						{   
							if(($ii % 2) == 0) {$corlinha = 'background-color: #ffffff;';} else {$corlinha = 'background-color: #f0f0f0;';}
							?>
							<div class="col-md-12" style="font-size: 11px; <?=$corlinha?>">
								<div class="col-md-5"><?=$_itemOrcamento['titulo']?></div>
								<div class="col-md-2"><?=$_itemOrcamento['nome']?></div>
								<div class="col-md-2"><?=dma($_itemOrcamento['prazo'])?></div>
								<div class="col-md-1"><?=$_itemOrcamento['status']?></div>
								<div class="col-md-1">
									<label class="idbox">
										<?=$_itemOrcamento['idcotacao']?>
										<a title="Orçamento" class="fa fa-bars fade pointer hoverazul" href="?_modulo=cotacao&_acao=u&idcotacao=<?=$_itemOrcamento['idcotacao']?>" target="_blank"></a>
									</label>
								</div>
							</div>
							<?
							$ii++;
						}
                        ?>
                    </div>
                </div>
            </div>    
            <?
        }
    } else {
        $escondeCadAss = true;
    }
}

function desenhaSF($_idsolfab)
{
	global $arrLoteSF, $arrSFAssociado, $_1_u_solfab_idempresa;
	$arrTmp = $arrLoteSF;
	$_idamostracount = '';
	$i = 1;
	foreach ($arrTmp['amostras'] as $_amostra) {
		foreach ($_amostra as $_amostra2) {
			foreach ($_amostra2 as $_amostra3) {
				if ($_amostra3['partida'] != $_idamostracount) {
					$countAmostra = $i++;
				}
				$_idamostracount = $_amostra3['partida'];
			}
		}
	}
	?>
	<div class="papel hover" id="formSF" style="width: 100%;">
		<h5 class="cinzaclaro" style="white-space: nowrap">Agentes inclusos: <?= $countAmostra; ?></h5>
		<hr>
		<?
		if (!is_array($arrTmp)) {
			//somente mensagem de erro
			echo ($arrTmp);
		} else {
			$a = 0;
			asort($arrTmp["SF"][$_idsolfab]);
			foreach ($arrTmp["SF"][$_idsolfab] as $idlote => $lote) 
			{
				$a = $a + 1;
				$idamostra = $lote["idamostra"];
				$sExcluir = ($arrSFAssociado["status"] == "ABERTO" or $arrSFAssociado["status"] == "PENDENCIA") ? "<i class='fa fa-trash floatright cinzaclaro hoververmelho' onclick=excluiSFItem(" . $lote["idsolfabitem"] . ")></i> " : $link;
				$string1lote = $lote["partida"];
				$string1 = " - " . $lote["statuslote"];
				$rotulo = getStatusFluxo('amostra', 'idamostra', $idamostra);
				$string2 = "TRA: " . $lote["idregistro"] . "/" . $lote["exercicio"] . "-" . mb_strtoupper($rotulo['rotulo'], 'UTF-8');
				$achor1lote = "?_modulo=semente&_acao=u&idlote=" . $idlote . "&_idempresa=$_1_u_solfab_idempresa";						
				$achor2 = "?_modulo=amostratra&_acao=u&idamostra=" . $lote["idamostra"]."&_idempresa=$_1_u_solfab_idempresa";
				echo "<h5 class='nowrap' >";
				echo "<a style='color: " . $lote["corv"] . "' href='$achor1lote'>" . $string1lote . "</a>" . " - " . dma($lote["vencimento"]);
				echo $string1;
				echo "&nbsp;&nbsp;&nbsp;&nbsp;<a style='color: " . $lote["corv"] . "' target='_blank' title=" . $lote['status'] . " href='" . $achor2 . "'>" . $string2 . "</a>";
				echo ($sExcluir . "</h5>");
			}
		}
		?> <br />
	</div>
	<?
}

function arquivostra($_idsolfab)
{
	$listarSolfaItem = SolfabController::buscarDadosSolfabItem($_idsolfab);
	$numtra = $listarSolfaItem['numLinhas'];
	if ($numtra > 0) 
	{
		?>
		<div class="papel hover" id="formSF" style="width: 100%;">
			<hr>
			<table border='0' style="border-collapse: collapse;">
				<tr style="border-bottom:1px solid silver;">
					<td style='font-size:9px;padding-left: 10px;'>Arquivo TRA</td>
					<td style='font-size:9px;padding-left: 10px;'>TRA</td>
				</tr>
				<?
				foreach ($listarSolfaItem as $solfabItem) 
				{
					$idamostra = $solfabItem["idamostra"];
					$unidadepadrao = $solfabItem["idunidade"];
					$achor2 = "report/tra.php?idamostra=".$idamostra."&unidadepadrao=".$unidadepadrao."";
					$string2 = " -TRA: ".$solfabItem["idregistro"]."/".$solfabItem["exercicio"]."-".$solfabItem["status"];

					$listarArquivos = SolfabController::buscarAnexosPorTipoObjetoIdObjeto('amostra', $idamostra);
					$numarq = count($listarArquivos);

					if ($numarq > 0) 
					{
						foreach($listarArquivos['dados'] as $arquivos) 
						{
							?>
							<tr style="border-bottom:1px dotted silver;">
								<td style="font-size: 11px;padding-left: 10px;vertical-align:middle;" nowrap>
									<a title="Abrir arquivo" target="_blank" href="../upload/<?=$arquivos["nome"] ?>">
										<?=$arquivos["nome"] ?>
									</a>
								</td>
								<td>
									<?
									echo "<a target='_blank' href='".$achor2."'>".$string2."</a>";
									?>
								</td>
							</tr>
							<?
						} //while
					} //$numarq
				}
				?>
			</table>
			<br />
		</div>
		<?
	} //if($numtra>0){
}

function listaLotesSF()
{
	global $_1_u_amostra_idamostra, $arrLoteSF, $_1_u_solfab_idsolfab, $_1_u_solfab_idempresa;
	$arrTmp = $arrLoteSF;

	//LISTA: Lotes do cliente selecionado
	if (count($arrTmp["amostras"]) > 0 and is_array($arrLoteSF)) 
	{
		echo "<div class='papel-cinza hover'><h5 class='cinza'><input type='checkbox' id='incluirTodasSementes' title='Selecionar todas as sementes'>&nbsp;&nbsp;Agentes Isolados</h5>";
		foreach($arrTmp["amostras"] as $idamostra => $arrres) 
		{
			if ($idamostra != $_1_u_amostra_idamostra) { //Listar somente outras amostras
				foreach($arrres as $idresultado => $arrlote) 
				{
					foreach($arrlote as $idlote => $lote) 
					{
						//se o agente ja estiver na SF ele não deve aparecer
						if (array_key_exists($idlote, $arrTmp["SF"][$_1_u_solfab_idsolfab])) {
							echo "";
						} else {
							echo "<h5 idlote='".$idlote."' style='color: ".$lote["corv"]."'><input type='checkbox' class='dragsf' idlote='".$idlote."'>&nbsp;&nbsp;<a href='?_modulo=semente&_acao=u&idlote=".$idlote."&_idempresa=$_1_u_solfab_idempresa' target='_blank'>".$lote["partida"]."</a> - ".dma($lote["vencimento"])." - ".$lote["statuslote"]." &nbsp;&nbsp;&nbsp;&nbsp;<a href='?_modulo=amostratra&_acao=u&idamostra=".$idamostra."&_idempresa=$_1_u_solfab_idempresa' target='_blank'>TRA: ".$lote["idregistro"]."/".$lote["exercicio"]."-".$lote["status"]."</a></h5>";
						}
					}
				}
			}
		}

		echo "<button class='btn btn-xs btn-success' id='btnIncluirSementes'><i class='fa fa-arrow-down'></i> Incluir</button></div>";
	} else {
		echo "<div class='alert alert-warning'><i class='fa fa-exclamation-triangle'></i>  Nenhum Agente encontrado para o cliente!</div>";
		echo "<!-- ";
		print_r($arrLoteSF);
		echo " -->";
	}
}

function criarlotecompra($_idprodserv, $idnfitem, $qtd, $exercicio)
{
    $totalImposto = '';
    $arrins = array();
    $arrObj = getObjeto("nfitem",  $idnfitem, "idnfitem");
    $_idempresa = traduzid('nf', 'idnf', 'idempresa', $arrObj['idnf']);


    $idunidadeest = traduzid('prodserv', 'idprodserv', 'idunidadeest', $_idprodserv);

    if (empty($idunidadeest)) {
        $_unidade = NfEntradaController::buscarIdunidadePorTipoUnidadeDescricao(3, $_idempresa, 'stoque');
        $idunidade = $_unidade['idunidade']; // unidade almoxarifado
    } else {
        $idunidade = $idunidadeest; // unidade almoxarifa
    }

    $moduloLote = NfEntradaController::buscarModuloTipoLoteViculadoAUnidade($idunidade);

    $idfluxostatus = FluxoController::getIdFluxoStatus('lote', 'ABERTO',  $idunidade);

    $arrins[1]["idprodserv"] = $_idprodserv;
    $arrins[1]["idempresa"] = $_idempresa;
    $arrins[1]["idnfitem"] = $idnfitem;
    $arrins[1]["qtdpedida"] = $qtd;
    $arrins[1]["qtdprod"] = $qtd;
    $arrins[1]["exercicio"] = $exercicio;
    if ($idfluxostatus) $arrins[1]["idfluxostatus"] = $idfluxostatus;
    $arrins[1]["status"] = "ABERTO";

    if (!empty($_idprodserv)) {
        $imobilizado = traduzid('prodserv', 'idprodserv', 'imobilizado', $_idprodserv);
        $idprodservforn = $arrObj['idprodservforn'];
        $nitotal = $arrObj['total'];

        $moedas = ['USD', 'EUR'];
        if (!empty($arrObj['moedaext']) && in_array($arrObj['moedaext'], $moedas)) {

            $valorUnitarioImportacao = ($arrObj['impostoimportacao'] + $arrObj['valipi'] + $arrObj['pis'] + $arrObj['cofins']) / $arrObj['qtd'];
            $valorTotalImportacaoProduto = NfController::buscarValorImpostoTotalPorTotalItem($_SESSION['arrpostbuffer']['x']['u']['nf']['idnf']);
            $totalImposto = $valorUnitarioImportacao + $valorTotalImportacaoProduto['valorcomimposto'];
        } else {
            if (!empty($arrObj['valipi'])) {
                $nitotal = $nitotal + $arrObj['valipi'];
            }

            if (!empty($arrObj['frete'])) {
                $nitotal = $nitotal + $arrObj['frete'];
            }
        }

        if ($imobilizado != 'Y') {
            if (!empty($idnfitem)) {
                if (empty($idprodservforn) && !empty($arrObj['idprodserv'])) {
                    $rowx = NfEntradaController::buscarProdservfornPorIdprodservIdnf($arrObj['idprodserv'], $arrObj['idnf']);
                    $idprodservforn =  $rowx['idprodservforn'];
                }

                if (!empty($idprodservforn)) {
                    $arrins[1]['idprodservforn'] = $idprodservforn;
                    $_prodservFornCompra = NfEntradaController::buscarProdservfornPorId($idprodservforn);

                    if ($_prodservFornCompra['converteest'] == "Y") {
                        if (empty($_prodservFornCompra['valconv'])) {
                            $valconv = 1;
                        } else {
                            $valconv = $_prodservFornCompra['valconv'];
                        }

                        $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                        $qtdprod = $qtd;
                        $valor = round((($nitotal / $arrObj['qtd']) / $valconv), 4);
                        if ($valor <= 0) {
                            die('Não foi possível criar lote. Valor total não pode ser menor ou igual a zero.');
                        }
                        $arrins[1]['vlrlote'] = $valor + $totalImposto;
                        $arrins[1]['qtdprod'] = $qtdprod;
                        $arrins[1]['unlote'] = $_prodservFornCompra['unforn'];
                        $arrins[1]['unpadrao'] = $un;
                        $arrins[1]['valconvori'] = $_prodservFornCompra['valconv'];
                        $arrins[1]['converteest'] = $_prodservFornCompra['converteest'];
                    } else {
                        $qtdprod = $qtd;
                        $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                        $valor = round(($nitotal / $arrObj['qtd']), 4);
                        if ($valor <= 0) {
                            die('Não foi possível criar lote. Valor total não pode ser menor ou igual a zero.');
                        }
                        $arrins[1]['vlrlote'] = $valor + $totalImposto;
                        $arrins[1]['qtdprod'] = $qtdprod;
                        $arrins[1]['unlote'] = $un;
                        $arrins[1]['unpadrao'] = $un;
                        $arrins[1]['valconvori'] = 1;
                        $arrins[1]['converteest'] = $_prodservFornCompra['converteest'];
                    }
                } else { // if(!empty($idprodservforn))
                    $valconv = 1;
                    $qtdprod = $qtd;
                    $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                    $valor = round(($nitotal / $arrObj['qtd']), 4);
                    if ($valor <= 0) {
                        die('Não foi possível criar lote. Valor total não pode ser menor ou igual a zero.');
                    }
                    $arrins[1]['vlrlote'] = $valor + $totalImposto;
                    $arrins[1]['qtdprod'] = $qtdprod;
                    $arrins[1]['unlote'] = $un;
                    $arrins[1]['unpadrao'] = $un;
                    $arrins[1]['valconvori'] = 1;
                    $arrins[1]['converteest'] = 'N';
                }
            }

            $_arrlote = geraLote($_idprodserv);
            $_numlote = $_arrlote[0] . $_arrlote[1];

            //Enviar o campo para a pagina de submit
            $arrins[1]["partida"] = $_numlote;
            $arrins[1]["idpartida"] = $_numlote;
            $arrins[1]["spartida"] = $_arrlote[0];
            $arrins[1]["npartida"] = $_arrlote[1];
            $arrins[1]["idunidade"] = $idunidade;

            $idlote = cnf::inseredb($arrins, 'lote');
            $idlote = $idlote[0];

            //Atribuir o valor para retorno por session['post'] ah pagina anterior.
            $_SESSION["post"]["_x_u_lote_partida"] = $_numlote;
        } else {
            //if($imobilizado =='Y')
            //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
            // buscar lote do produto com status aprovado, se tem fracao e unidade tipoalmoxarifado   
            $_lotePorUnidade = NfEntradaController::buscarLotePorIdprodservIdunidade($idunidade, $_idprodserv);
            $num_rows = mysqli_num_rows($_lotePorUnidade);
            if ($num_rows == 0) {
                // se o numeros de linhas for 0, significa que nao existe lote para o produto em questão, sera criado entao um novo lote
                // adicionei todas as funcoes responsaveis pela criacao do lote novo. 
                if (!empty($idprodservforn)) {
                    $arrins[1]['idprodservforn'] = $idprodservforn;

                    $sqly = "select * from prodservforn where idprodservforn =" . $idprodservforn;
                    $resy = d::b()->query($sqly) or die("presave: Erro ao buscar configurações do fornecedor do produto: " . mysqli_error(d::b()) . "\n" . $sqly);
                    $rowy = mysqli_fetch_assoc($resy);

                    if ($rowy['converteest'] == "Y") {
                        if (empty($rowy['valconv'])) {
                            $valconv = 1;
                        } else {
                            $valconv = $rowy['valconv'];
                        }

                        $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);

                        $qtdprod = $qtd;
                        //$qtddest= $qtdprod*$rowy['valconv'];
                        //round(ifnull(((ni.total/l.qtdprod)/l.valconvori),0),2)
                        $valor = round((($nitotal / $arrObj['qtd']) / $valconv), 4);
                        if ($valor <= 0) {
                            die('Não foi possível criar lote. Valor total não pode ser menor ou igual a zero.');
                        }
                        $arrins[1]['vlrlote'] = $valor + $totalImposto;
                        $arrins[1]['qtdprod'] = $qtdprod;
                        $arrins[1]['unlote'] = $rowy['unforn'];
                        $arrins[1]['unpadrao'] = $un;
                        $arrins[1]['valconvori'] = $rowy['valconv'];
                        $arrins[1]['converteest'] = $rowy['converteest'];
                    } else {
                        $qtdprod = $qtd;
                        $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                        $valor = round(($nitotal / $arrObj['qtd']), 4);
                        if ($valor <= 0) {
                            die('Não foi possível criar lote. Valor total não pode ser menor ou igual a zero.');
                        }
                        $arrins[1]['vlrlote'] = $valor + $totalImposto;
                        $arrins[1]['qtdprod'] = $qtdprod;
                        $arrins[1]['unlote'] = $un;
                        $arrins[1]['unpadrao'] = $un;
                        $arrins[1]['valconvori'] = 1;
                        $arrins[1]['converteest'] = $rowy['converteest'];
                    }
                } else { // if(!empty($idprodservforn)){
                    $qtdprod = $qtd;
                    $un = traduzid('prodserv', 'idprodserv', 'un', $_idprodserv);
                    $valor = round(($nitotal / $arrObj['qtd']), 4);
                    if ($valor <= 0) {
                        die('Não foi possível criar lote. Valor total não pode ser menor ou igual a zero.');
                    }
                    $arrins[1]['vlrlote'] = $valor + $totalImposto;
                    $arrins[1]['qtdprod'] = $qtdprod;
                    $arrins[1]['unlote'] = $un;
                    $arrins[1]['unpadrao'] = $un;
                    $arrins[1]['valconvori'] = 1;
                    $arrins[1]['converteest'] = 'N';
                }

                $_arrlotenovo = geraLote($_idprodserv);
                $_numlotenovo = $_arrlotenovo[0] . $_arrlotenovo[1];
                $idfluxostatus = FluxoController::getIdFluxoStatus('lote', 'APROVADO', $idunidade);
                //Enviar o campo para a pagina de submit
                $arrins[1]["partida"] = $_numlotenovo;
                $arrins[1]["idpartida"] = $_numlotenovo;
                $arrins[1]["spartida"] = $_arrlotenovo[0];
                $arrins[1]["npartida"] = $_arrlotenovo[1];
                $arrins[1]["idunidade"] = $idunidade;
                $arrins[1]["idfluxostatus"] = $idfluxostatus;
                $arrins[1]["status"] = "APROVADO";

                $idlote = cnf::inseredb($arrins, 'lote');
                $idlote = $idlote[0];

                //Atribuir o valor para retorno por session['post'] ah pagina anterior.
                $_SESSION["post"]["_x_u_lote_partida"] = $_numlotenovo;
            } else {
                // se existir lote ja criado busca o idfracao correspondente ao lote cadastrado com a unidade correspondente ao almoxarifado															
                $_lotes = NfEntradaController::buscarLoteFracaoPorIdloteEIdUnidade($_lotePorUnidade['idlote'], $idunidade);
                $num_rows_fr = count($_lotes);

                //se o numero retornado da fracao for menor que 1 , cria uma fraçao nova com status disponivel, idlote (que nesse momento ja existe), qtdprod, e idunidade do almoxarifado.
                if ($num_rows_fr < 1) {
                    $qtdprod = $qtd;
                    $ifracao = new Insert();
                    $ifracao->setTable("lotefracao");
                    $ifracao->status = "DISPONIVEL";
                    $ifracao->idlote = $_lotePorUnidade['idlote'];
                    $ifracao->qtdini = $qtdprod;
                    $ifracao->idempresa = $_idempresa;
                    $ifracao->idunidade = $idunidade;
                    $idlotefracao = $ifracao->save();
                    $_SESSION["arrpostbuffer"]["xx"]["u"]["lote"]["idlote"] = $_lotePorUnidade['idlote'];
                    $_SESSION["arrpostbuffer"]["xx"]["u"]["lote"]["status"] = $_lotePorUnidade['status'];
                } else { //se existir fracao (com idlote correspondente), ja faz todo processo abaixo de new insert()
                    $idlotefracao = $_lotes['idlotefracao'];
                }
                $qtdprod = $qtd;
                $ilotecons = new Insert();
                $ilotecons->setTable("lotecons");
                $ilotecons->idlote = $_lotePorUnidade['idlote'];
                $ilotecons->idempresa = $_idempresa;
                $ilotecons->idlotefracao = $idlotefracao;
                $ilotecons->idobjetoconsumoespec = $idnfitem;
                $ilotecons->tipoobjetoconsumoespec = 'nfitem';
                $ilotecons->qtdc = $qtdprod;
                $ilotecons->save();
                unset($_SESSION["arrpostbuffer"]["x"]["u"]["idlote"]["idlote"]);
                unset($_SESSION["arrpostbuffer"]["x"]["u"]["status"]["status"]);

                $_SESSION["arrpostbuffer"]["xx"]["u"]["lote"]["idlote"] = $_lotePorUnidade['idlote'];
                $_SESSION["arrpostbuffer"]["xx"]["u"]["lote"]["status"] = $_lotePorUnidade['status'];
            }
        }

        FluxoController::inserirFluxoStatusHist($moduloLote, $idlote, $idfluxostatus, 'ATIVO');
        
    }
}

//gerar rateio para o resultado
function geraRateioResultado($idresultado,$semente = false){

	if($semente==false){
		$sqlsemente=" and not exists(select 1 from lote l where l.tipoobjetosolipor = 'resultado' and l.idobjetosolipor =r.idresultado)  ";
	}
	// Nao ratear se tiver semente, for de uma op de op no bioterio
	$sql="select r.idresultado , a.idunidade,a.idempresa,(select idrateioitem from rateioitem i where i.idobjeto=r.idresultado and i.tipoobjeto = 'resultado') as idrateioitem,ifnull(r.dataconclusao,now()) as dataconclusao
			from resultado r 
			join amostra a on(a.idamostra = r.idamostra)
			where r.idresultado = ".$idresultado."
			and (r.idservicoensaio is null or r.idservicoensaio ='' or r.idservicoensaio =0)
			and not exists (select 1 from objetovinculo o where ( o.tipoobjetovinc  = 'loteativ' and o.idobjeto= r.idresultado))
			".$sqlsemente;
	$res = d::b()->query($sql) or die("Erro ao buscar rateio do resultado sql:".$sql);
	$qtd=mysqli_num_rows($res);
	if($qtd>0){
		$row=mysqli_fetch_assoc($res);

		

		if(empty($row['idrateioitem'])){
			$_idrateio =retidrateio($row['dataconclusao'],$row['idempresa']);
			
			$insrateioitem = new Insert();
			$insrateioitem->setTable("rateioitem");
			$insrateioitem->idrateio=$_idrateio;
			$insrateioitem->idobjeto=$row['idresultado'];
			$insrateioitem->tipoobjeto='resultado';
			$row['idrateioitem']=$insrateioitem->save();
			/*
			}else{
				$sqldel="delete from rateioitemdest where idrateioitem=".$row['idrateioitem'];
				$resdel = d::b()->query($sqldel) or die("Erro ao atualizar rateio do resultado sql:".$sqldel);
			}
				^*/
			$insrateioitemd = new Insert();
			$insrateioitemd->setTable("rateioitemdest");
			$insrateioitemd->idrateioitem=$row['idrateioitem'];                          
			$insrateioitemd->idobjeto=$row['idunidade'];
			$insrateioitemd->tipoobjeto='unidade';                          
			$insrateioitemd->valor=100;
			$insrateioitemd->criadopor='sislaudo';
			$insrateioitemd->criadoem= sysdate();
			$insrateioitemd->alteradopor='sislaudo';
			$insrateioitemd->alteradoem= sysdate();
			$_idrateioitemd=$insrateioitemd->save();
		}

	}
}


function retidrateio($data,$idempresa){

	// Verificar se a data passada é menor que a data de hoje
	if (strtotime($data) < strtotime(date('Y-m-d'))) {
        $data = date('Y-m-d'); // Atualizar $data para a data de hoje
    }

	// Extrair mês e ano da data fornecida
	   $mes = date('m', strtotime($data));
	   $ano = date('Y', strtotime($data));
   
	   // Conectar ao banco de dados
	   $conexao = d::b(); // Função d::b() usada como exemplo para obter a conexão com o banco de dados
   
	   // Verificar se existe um registro com status 'ABERTO' no mesmo mês, ano e idempresa
	   $sql = "SELECT idrateio 
			   FROM rateio 
			   WHERE idempresa = $idempresa 
				 AND mes = $mes 
				 AND ano = $ano 
				 AND status = 'ABERTO'";
	   $resultado = d::b()->query($sql) or die("Erro ao buscar rateio: " . mysqli_error(d::b()));
	   $qtdrows1= mysqli_num_rows($resultado);
   
	   if ($qtdrows1 > 0) {
		   // Retornar o idrateio encontrado
		   $row =mysqli_fetch_assoc($resultado);
		   return $row['idrateio'];
	   }else{
			// Verificar se existe um registro do mesmo mês e ano com status diferente de 'ABERTO'
		   $sql = "SELECT idrateio 
			   FROM rateio 
			   WHERE idempresa = $idempresa 
			   AND mes = $mes 
			   AND ano = $ano 
			   AND status != 'ABERTO'";
		   $resultado =d::b()->query($sql) or die("Erro ao buscar rateio: " . mysqli_error(d::b()));
		   $qtdrows1= mysqli_num_rows($resultado);
   
   
		   if ($qtdrows1 > 0) {
		   // Verificar se há um registro mais recente com status 'ABERTO'
			   $sql = "SELECT idrateio 
					   FROM rateio 
					   WHERE idempresa = $idempresa 
					   AND status = 'ABERTO'
					   ORDER BY ano DESC, mes DESC 
					   LIMIT 1";
			   $resultadoAberto = d::b()->query($sql) or die("Erro ao buscar rateio: " . mysqli_error(d::b()));
			   $qtdrows1= mysqli_num_rows($resultadoAberto);
			   if ($qtdrows1 > 0) {
				   $row = mysqli_fetch_assoc($resultadoAberto);
				   return $row['idrateio'];
			   }else{
					// Criar novo registro de rateio com mês e ano da data atual
				   $mesAtual = date('m');
				   $anoAtual = date('Y');
				   $criadopor = 'sistema'; // Usuário que está criando o registro (ajustar conforme necessário)
   
				   $sql = "INSERT INTO rateio (idempresa, mes, ano, status, criadopor, criadoem) 
						   VALUES ($idempresa, $mesAtual, $anoAtual, 'ABERTO', '$criadopor', NOW())";
				   d::b()->query($sql) or die("Erro ao criar rateio: " . mysqli_error(d::b()));
   
				   // Retornar o ID do novo registro criado
				   return d::b()->insert_id;
			   }
		   }else{
   
				// Criar novo registro de rateio com mês e ano da data atual
			   $criadopor = 'sistema'; // Usuário que está criando o registro (ajustar conforme necessário)
   
			   $sql = "INSERT INTO rateio (idempresa, mes, ano, status, criadopor, criadoem) 
					   VALUES ($idempresa,$mes , $ano, 'ABERTO', '$criadopor', NOW())";
			   d::b()->query($sql) or die("Erro ao criar rateio: " . mysqli_error(d::b()));
   
			   // Retornar o ID do novo registro criado
			   return d::b()->insert_id;
		   }
   
	   }
   
	  
   
	  
   }
?>
