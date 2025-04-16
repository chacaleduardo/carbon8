<?
require_once("../inc/php/functions.php");
require_once("../inc/php/laudo.php");

require_once(__DIR__."/../form/controllers/fluxo_controller.php");

$idsgdoc 	= $_GET["idsgdoc"];

if(empty($idsgdoc)){
	die("Erro, não foram enviados os parâmetros necessários para a cópia.");
}
            $sql="select d.*,count(dd.idsgdoc) as qtdcp 
                    from sgdoc d left join sgdoc dd on(dd.idsgdoccopia = d.idsgdoc and d.versao =dd.versao)
                    where d.idsgdoc= ".$idsgdoc." group by d.idsgdoc";
            $res= d::b()->query($sql) or die("Erro ao buscar tipo do documento: ".mysqli_error(d::b())." SQL=".$sql);
            $row= mysqli_fetch_assoc($res);
           /* if(empty($row['idsgdoctipo'])){ die('Não identificado o tipo do documento.');}
            if($row['idsgdoctipo']=='questionario' or $row['idsgdoctipo']=='avaliacao'  ){
                $_idregistro = geraRegistrosgdoc($row['idsgdoctipo']);
                $versao=0;
                
            }{
            
            */
                $_idregistro=$row['idregistro'];
                $versao=$row['versao'];
                $revisao=$row['versao'];
                $copia=$row['qtdcp']+1;

            //}
		/*
		 * Insere linha
		*/

		//LTM - 28-04-2021: Retorna o Idfluxo Amostra
		$idfluxostatus = FluxoController::getIdFluxoStatus('documento', 'AGUARDANDO');	

		//GERAR DOCUMENTO
		$sqli="INSERT INTO `sgdoc`
					(idsgdoccopia,
                                        idregistro,
					`idempresa`,
					`idunidade`,
					`titulo`,
					idpessoa,
					`idsgdoctipo`,					
					`versao`,
					`revisao`,
					`copia`,
					`status`,
					`idfluxostatus`,
					`tipoacesso`,
					`conteudo`,					
					`criadopor`,
					`criadoem`,
					`alteradopor`,
					`alteradoem`
					)
					(select 
					idsgdoc,
					".$_idregistro.",
					`idempresa`,
					`idunidade`,
					`titulo`,
					".$_SESSION["SESSAO"]["IDPESSOA"].",
					`idsgdoctipo`,					
					'".$versao."',
					'".$revisao."',
					'".$copia."',
					'AGUARDANDO',
					'$idfluxostatus',
					`tipoacesso`,
					`conteudo`,					
					'".$_SESSION["SESSAO"]["USUARIO"]."',
					now(),
					'".$_SESSION["SESSAO"]["USUARIO"]."',
					now() from sgdoc where idsgdoc = ".$idsgdoc.")";
		$resi= d::b()->query($sqli) or die("Erro ao copiar documento: ".mysqli_error(d::b())." SQL=".$sqli);;
		if(!$resi){
			
			die("1-Falha ao gerar documento: " . mysqli_error(d::b()) . "<p>SQL: ".$sqli);
		}else{
			$nidsgdoc=mysqli_insert_id(d::b());
			//$strnd=" NOVO DOCUMENTO   <a class='btbr20' href='../forms/sgdocnovo.php?acao=u&idsgdoc=$nidsgdoc'>".$nidsgdoc."</a>";
			
			//LTM - 28-04-2021: Insere FluxoHist Amostra        
            FluxoController::inserirFluxoStatusHist('documento', $nidsgdoc, $idfluxostatus, 'PENDENTE');
		}

			
		/*
		 * COPIAR AS PAGINAS DO DOCUMENTO PARA O DOCUMENTO ATUAL
		*/
		
		$sqlinsereitens = "INSERT INTO `sgdocpag`
									(idsgdoc,
                                                                        idempresa,
                                                                        pagina,
                                                                        conteudo,                                                                                                                                    criadopor,
                                                                        criadoem,
                                                                        alteradopor,
                                                                        alteradoem)
									(select ".$nidsgdoc.",
									`idempresa`,
									`pagina`,
									`conteudo`,									
									'".$_SESSION["SESSAO"]["USUARIO"]."',
									now(),
									'".$_SESSION["SESSAO"]["USUARIO"]."',
									now()
									 from sgdocpag where idsgdoc = ".$idsgdoc.")";
		d::b()->query($sqlinsereitens) or die("A insercão das paginas do documento falhou : " .mysqli_error(d::b()) . "<p>SQL: $sqlinsereitens");
	
		

echo($nidsgdoc);


