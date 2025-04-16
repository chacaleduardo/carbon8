<?php
include_once("../functions.php");

function is_cli() {

    if (defined('STDIN')) {
        return true;
	}
	
	if (empty($_SERVER['REMOTE_ADDR']) and 
		!isset($_SERVER['HTTP_USER_AGENT']) and 
		count($_SERVER['argv']) > 0) {

        return true;
	}
	
	return false;
	
}

/*
 * Inspecionar usuário. Default: false
 * para inspecionar, deve-se ajustar o usuário desejado
 */

$_SESSION["inspecionarSql"] = false;

//d::b()->query('SET NAMES \'utf8\'');//Aceitar Latinos

//d::b()->query('SET CHARACTER SET utf8');

ini_set("magic_quotes_runtime", 0);

// Strip slashes de GET/POST/COOKIE (se o magic_quotes_gpc estiver habilitado) //
if ( get_magic_quotes_gpc() ) {

	function stripslashes_array($array) {
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	$_COOKIE = stripslashes_array($_COOKIE);

}

//if($_SESSION["SESSAO"]["usuario"]=="marcelo"){
//	d::b()->query("SET global log_output = 'table'");
//	d::b()->query("SET global general_log = 0");
//}

/**
 * Classe principal
 **/
class IM {

	//Debug
	private $debugsql		= false;
	//Parametros
	private $tabmsg			= "immsg";
	private $tabpessoa		= "pessoa";
	private $tabimgrupo		= "imgrupo";
	private $tabimpessoa	= "impessoa";
	private $tabcontato		= "imcontato";
	private $segundosStatusOffline = 30;
	//Segundos para considerar usuário como offline

	//Token:
	private $token			= false;
	private $exp			= false;
	private $ramalfixo		= false;
	private $idlp			= false;
	private $idtipopessoa 	= false;
	private $idpessoa		= false;
	private $usuario		= false;
	private $nome			= false;
	private $idempresa		= false;
	private $acoesPermitidas = array('T','M','A');
	
	function IM($call) {

		global $JSON;
		
		if ($call=="atualiza") {
			$this->atualizaChat();
			die;
		}
		
		if ($call!=='login') {

			if (!logado()) {

				// Validar token enviado via Header, GET ou POST.
				// Usuário não logado no sistema. Somente utilizando o chat via token

				$arrJwt=validaToken();

				if (!$arrJwt["sucesso"]) {
					die('{"erro":"Token inválido: '.$arrJwt["erro"].'"}');
				} else {
					
					$this->token=$arrJwt["token"];
					
					if($this->token->iss!="sislaudo") {
						die('{"erro":"Token com chave inválida #1"}');
					} else {
						$this->setPrivateVars();
					}
				}

			} elseif(logado()) {
				$this->setPrivateVars();
			}
		}

		//Desativa o log binário, diminuindo o tamanho dos arquivos de log para replicação
		d::b()->query("SET sql_log_bin = 0");

		//Executa um coletor de lixo
		$this->gc();
                
		//Executado randomicamente, gera mensagems programadas no carbon para o chat;
		//$this->eventosLaudo();

		//Executa a ação enviada por POST
		switch($call) {

			case 'login':
				print $this->login(strtolower($_POST['usuario']), $_POST['senha']);
				break;

			case 'logout':
				print $this->logout();
				break;

			case 'contatos':
				print $this->contatos();
				break;

			case 'refresh':
				print $this->refresh();
				break;
			
			case 'clonargrupo':
				print $this->clonargrupo($_idimgrupo, $_idpessoa, $_nomepessoa, $_idimregra, $_grupo);
				break;
			
			case 'grupogrupo':
				print $this->grupogrupo($_idimregra, $_grupo);
				break;
				
			case 'historico':
				print $this->historico();
				break;

			case 'conversa':
				print $this->conversa($_POST['sender'], $_POST['objetocontato'], $_POST['idimmsg']);
				break;
			
			case 'enviar':
				
				if (empty($_POST['contatos'])) die('{"code":"CONTATOS_NAO_INFORMADOS"}');
				
				$idimmsgbody = !empty($_POST["idimmsgbody"]) ? $_POST["idimmsgbody"] : false;
				//Função de compartilhar: o msgbody não será criado
				print $this->enviar(
					$_POST['contatos'], 
					$_POST['msg'],
					$idimmsgbody,
					$_POST["msgtipo"],
					$_POST["datatarefa"],
					$_POST["modulopk"],
					$_POST["modulo"]
				);
				//d::b()->query("SET global general_log = 0");
				break;
			/*
			case 'compartilhar':
				//print '{"code":"MANUTENCAO"}';die;
				if(empty($_POST['contatos']))die('{"code":"CONTATOS_COMPART_NAO_INFORMADOS"}');
				print $this->enviar($_POST['contatos'], $_POST['msg'], $_POST['immsgbody']);
				break;
			*/
			case 'alterar':
				print $this->alterar($_POST['idimmsgbody'], $_POST['message']);
				break;

			case 'ler':
				print $this->alterarStatusMensagem($_POST['idcontato'], $_POST['objetocontato'], $_POST['idimmsg'], "L");
				break;

			case 'anexo':
				print $this->anexo($_POST['arquivo'], $_POST['nome'], $_POST['idimmsgbody'], $_POST['idimarq']);
				break;

			case 'msg2tarefa':
				print $this->msg2tarefa($_POST['idimmsgbody'], $_POST['transformar']);
				break;

			case 'apagar':
				print $this->apagarMsg($_POST['idimmsgbody']);
				break;

			 default:
				header("HTTP/1.1 501 Not Implemented");
				die('{"erro":"'.$call.' Not Implemented"}');
				break;
		}
	}
   
	/**
     * Logs the user in and sets the session for the user.
     **/
	function login($usuario, $password) 
	{	
		global $JSON;

		if (!empty($usuario) and !empty($password)) {
			
			//Recupera os dados do usuário atravás do token devolvido pela função de login
			$this->token=logincarbon($usuario, $password);
			
			//Instancia variaveis privadas
			$this->setPrivateVars();

			if ($this->idpessoa) {

				$this->setOnline(true);
		
				//recupera a lista de contatos do usuario
				$arrcontatos = $this->getListaContatos();

				if (count($arrcontatos) > 0) {
					//Atualiza o status para os contatos
					//MAF: o chat nao controla mais o status do funcionario
					//$this->enviaEventoContatos($arrcontatos, "{\"online\":\"".$this->idpessoa."\"}");
					//Devolve contatos para o client
					return $JSON->encode($arrcontatos);
				} else {
					return '{"erro":"Usuário não possui contatos configurados"}';
				}

			} else {
				return '{"erro":"Erro Login #2"}';
			}

		} else {
			return '{"erro":"Erro Login #1"}';
		}
	}
	
	function logout() 
	{
		global $JSON;

		//Deletar todos os EVENTOS referentes ao usuário logado
		//maf0109: desativado temporariamente		$sdev = "DELETE FROM immsg WHERE tipo='E' AND descr LIKE '%o%line%".$this->idpessoa."%'";
		d::b()->query($sdev) or die("logout erro #1:". mysqli_error(d::b()));
		
		//maf: o chat nao controla mais o status do funcionario
		//Enviar evento de OFFLINE para os contatos online
		//$arrContatos = $this->getListaContatos();
		//$this->enviaEventoContatos($arrContatos, "{\"offline\":\"".$this->idpessoa."\"}");

		//Alterar status do usuário de chat
		//$supi = "update impessoa set online=0 where idpessoa=".$this->idpessoa.";";
		//d::b()->query($supi) or die("logout erro #2:". mysqli_error(d::b()));
		$this->setOnline(false);
		return "";
	}
	
	function contatos() 
	{
		global $JSON;
		$arrContatos = $this->getListaContatos();
		
		//Deletar todos os EVENTOS referentes ao usuário
		//maf010920: Implentacao incorreta de limpeza de eventos do chat		
		//$sdev = "DELETE FROM immsg WHERE tipo='E' AND descr LIKE '%o%line%".$this->idpessoa."%'";
		d::b()->query($sdev) or die("contatos erro:". mysqli_error(d::b()));
		
		//Enviar o evento de ONLINE para os contatos online
		//O chat nao controla mais o status do funcionario
		//$this->enviaEventoContatos($arrContatos, "{\"online\":\"".$this->idpessoa."\"}");

		return $JSON->encode($arrContatos);
	}
	
	function setPrivateVars() {

		if (!logado()) {
			$this->exp			= $this->token->exp; 
			$this->ramalfixo	= $this->token->ramalfixo;
			$this->idlp			= $this->token->idlp;
			$this->idtipopessoa = $this->token->idtipopessoa;
			$this->idpessoa		= $this->token->idpessoa;
			$this->usuario		= $this->token->usuario;
			$this->nome			= $this->token->nome;
			$this->idempresa	= $this->token->idempresa;
		} elseif(logado) {
			$this->exp			= null;
			$this->ramalfixo	= $_SESSION["SESSAO"]["RAMALFIXO"];
			$this->idlp			= $_SESSION["SESSAO"]["IDLP"];
			$this->idtipopessoa = $_SESSION["SESSAO"]["IDTIPOPESSOA"];
			$this->idpessoa		= $_SESSION["SESSAO"]["IDPESSOA"];
			$this->usuario		= $_SESSION["SESSAO"]["USUARIO"];
			$this->nome			= $_SESSION["SESSAO"]["NOME"];
			$this->idempresa	= $_SESSION["SESSAO"]["IDEMPRESA"];
		}
	}
		
	function setOnline($inOnline=true) {

		$ionline=($inOnline)?1:0;
		$sqlOnline = "UPDATE ". $this->tabimpessoa . " force index(idpessoa) 
						SET online=".$ionline."
						,ultimoip='". $_SERVER['REMOTE_ADDR'] ."'
						,ultimoping=now()
						WHERE idpessoa=" . $this->idpessoa;

		d::b()->query($sqlOnline) or die("setOnline:". mysqli_error(d::b()));
	}

	function getRowsetContatos($inIdcontato=false,$inObjetocontato=false){
		
		if (!empty($inIdcontato) and !empty($inObjetocontato)) {
			$sqlContatoEspecifico=" and c.idcontato= ".$inIdcontato." and c.objetocontato='".$inObjetocontato."'";
		}
		
		//Comentado o -- and ipd.idempresa = oe.empresa pois não estava aparecendo as pessoas no setor - LTM (29-07-2020)
		//Alterado onde busca o id da empresa para aparecer no chat - LTM (03-08-2020 - 363640)
		$sqlcontatos = "SELECT c.idcontato
							,c.objetocontato
							,IF(LENGTH(IFNULL(pdes.nomecurto,''))>0,pdes.nomecurto,pdes.nome) AS rotulo
							,c.ultimamsg
							,(SELECT nome 
									FROM arquivo ar FORCE index(idobjeto_tipoobjeto) 
									WHERE ar.tipoarquivo='AVATAR'
									AND ar.tipoobjeto='pessoa' 
									AND ar.idobjeto=c.idcontato
									ORDER BY idarquivo DESC
									LIMIT 1) AS arquivoavatar
							,sgc.cargo AS cargo
							,pdes.webmailemail
							,ipd.online
							,ipd.ultimoip
							,ipd.ultimoping
							,(SELECT ai.setor FROM pessoaobjeto ps,sgsetor ai
								WHERE ps.idpessoa = c.idcontato
                                AND ai.idsgsetor = ps.idobjeto
								AND ps.tipoobjeto = 'sgsetor'
								ORDER BY ps.alteradoem DESC, ps.criadoem DESC
								LIMIT 1) AS area,
							IF (NOT pdes.ramalfixo = '', concat('<i class=\"fa fa-phone-square\"></i>&nbsp', pdes.ramalfixo), '') AS ramalfixo,
							'chat' AS tipocontato
						FROM imcontato c 
							JOIN pessoa pdes ON 1
								join objempresa oe on oe.idobjeto = pdes.idpessoa -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								AND oe.objeto = 'pessoa'
								".getidempresa('oe.empresa','objempresa')."
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='pessoa'
								AND not pdes.status='INATIVO'
								-- AND pdes.idempresa=c.idempresa
								AND pdes.idpessoa=c.idcontato
								AND c.status = 'A'
							JOIN impessoa ipd on ipd.idpessoa=c.idcontato -- and ipd.idempresa = oe.empresa
							LEFT JOIN sgcargo sgc on sgc.idsgcargo=pdes.idsgcargo
						UNION all
						SELECT c.idcontato
							,c.objetocontato
							,if(length(ifnull(pdes.nomecurto,''))>0,pdes.nomecurto,pdes.nome) as rotulo
							,c.ultimamsg
							,(select nome 
									from arquivo ar force index(idobjeto_tipoobjeto) 
									where ar.tipoarquivo='AVATAR'
									and ar.tipoobjeto='pessoa' 
									and ar.idobjeto=c.idcontato
									order by idarquivo desc
									limit 1) as arquivoavatar
							,sgc.cargo as cargo
							,pdes.webmailemail
							,ipd.online
							,ipd.ultimoip
							,ipd.ultimoping
							,(select ai.setor from pessoaobjeto ps,sgsetor ai
								where ps.idpessoa = c.idcontato
                                and ai.idsgsetor = ps.idobjeto
								and ps.tipoobjeto = 'sgsetor'
								order by ps.alteradoem desc, ps.criadoem desc
								limit 1
                                                        ) as area,
													
							if (not pdes.ramalfixo = '', concat('<i class=\"fa fa-phone-square\"></i>&nbsp', pdes.ramalfixo), '') as ramalfixo,
							'chat' as tipocontato
						FROM imcontato c 
							JOIN pessoa pdes ON 1
								join objempresa oe on oe.idobjeto = pdes.idpessoa -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								AND oe.objeto = 'pessoa'
								".getidempresa('oe.empresa','objempresa')."
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='pessoa'
								AND not pdes.status='INATIVO'
								-- AND pdes.idempresa=c.idempresa
								AND pdes.idpessoa=c.idcontato
								AND c.status = 'A'
							JOIN impessoa ipd on ipd.idpessoa=c.idcontato -- and ipd.idempresa = oe.empresa
							LEFT JOIN sgcargo sgc on sgc.idsgcargo=pdes.idsgcargo
						--	JOIN 
						--		imgrupopessoa gpp on gpp.idpessoa = ipd.idpessoa and gpp.idimgrupo in (
						--			select g.idimgrupo from imgrupopessoa gp 
						--			join imgrupo g on gp.idimgrupo = g.idimgrupo and not tipoobjetoext = 'clone' 
						--			-- join sgsetor s on g.idobjetoext = s.idsgsetor
						--	 where idpessoa = c.idpessoa) and not gpp.idpessoa = c.idpessoa
							 UNION all
						SELECT c.idcontato
							,c.objetocontato
							,if(length(ifnull(pdes.nomecurto,''))>0,pdes.nomecurto,pdes.nome) as rotulo
							,c.ultimamsg
							,(select nome 
									from arquivo ar force index(idobjeto_tipoobjeto) 
									where ar.tipoarquivo='AVATAR'
									and ar.tipoobjeto='pessoa' 
									and ar.idobjeto=c.idcontato
									order by idarquivo desc
									limit 1) as arquivoavatar
							,sgc.cargo as cargo
							,pdes.webmailemail
							,1 as online
							,ipd.ultimoip
							,ipd.ultimoping
							,(select ai.setor from pessoaobjeto ps,sgsetor ai
								where ps.idpessoa = c.idcontato
                                and ai.idsgsetor = ps.idobjeto
								and ps.tipoobjeto = 'sgsetor'
								order by ps.alteradoem desc, ps.criadoem desc
								limit 1
                                                        ) as area,
													
							if (not pdes.ramalfixo = '', concat('<i class=\"fa fa-phone-square\"></i>&nbsp', pdes.ramalfixo), '') as ramalfixo,
							'compartilhamento' as tipocontato
						FROM imcontato c 
							JOIN pessoa pdes ON 1
								join objempresa oe on oe.idobjeto = pdes.idpessoa -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								".getidempresa('oe.empresa','objempresa')."
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='pessoa'
								AND not pdes.status='INATIVO'
								-- AND pdes.idempresa=c.idempresa
								AND pdes.idpessoa=c.idcontato
								AND c.status = 'A'
							JOIN impessoa ipd on ipd.idpessoa=c.idcontato -- and ipd.idempresa = oe.empresa
							LEFT JOIN sgcargo sgc on sgc.idsgcargo=pdes.idsgcargo
						--	JOIN 
						--		imgrupopessoa gpp on gpp.idpessoa = ipd.idpessoa and gpp.idimgrupo in (
						--			select g.idimgrupo from imgrupopessoa gp 
						--			join imgrupo g on gp.idimgrupo = g.idimgrupo and not tipoobjetoext = 'clone' 
						--			-- join sgsetor s on g.idobjetoext = s.idsgsetor
						--	 where idpessoa = c.idpessoa) and not gpp.idpessoa = c.idpessoa;
							 
							 ;";
		//die($sqlcontatos);
		
		$query = d::b()->query($sqlcontatos) or die("getRowsetContatos: \n\n". mysqli_error(d::b()));

		//$this->getRowsetGrupos();die;
		
		return $query;
	}
	
	function getListaContatos() {

		$resa = $this->getRowsetContatos();

		$resg = $this->getRowsetGrupos();
		
		$arrcontatos = array();

		while($row = mysqli_fetch_assoc($resa)) {
			$arrcontatos[] = $row;
		}
		
		while ($row = mysqli_fetch_assoc($resg)) {

			if ($row["objetocontato"]=="imgrupo") {
				$row["membros"]=$this->getMembrosGrupo($row["idcontato"],$row["objetocontato"]);
			}

			$arrcontatos[] = $row;
		}
		
		return $arrcontatos;
	}
	
	//Grupos que o tipo de usuário pode ver
	//@todo: melhoria para recuperar dinamicamente os grupos, para não ser necessária intervenção de código
	function getRowsetGrupos($inIdcontato=false, $inObjetocontato=false) 
	{
		//Recupera informações de um grupo específico
		if (!empty($inIdcontato) and !empty($inObjetocontato)) {
			$sqlContatoEspecifico=" and c.idcontato= ".$inIdcontato." and c.objetocontato='".$inObjetocontato."'";
		}

		$sqlgr = "SELECT c.idcontato
							,c.objetocontato
							,g.grupo as rotulo
							,c.ultimamsg
							,(select nome 
									from arquivo ar force index(idobjeto_tipoobjeto) 
									where ar.tipoarquivo='AVATAR'
									and ar.tipoobjeto='sgsetor' 
									and ar.idobjeto=c.idcontato
									order by idarquivo desc
									limit 1) as arquivoavatar
							,null as cargo
							,null as webmailemail
							,1 as `online`
							,null as ultimoip
							,null as ultimoping
							,null as area
							,if(gc.idimgrupo > 0, gc.idimgrupo, if(g.tipoobjetoext = 'clone',c.idcontato, 0)) as idimclonegrupo
							,s.idtipopessoa,
							'' as ramalfixo,
							'chat' as tipocontato
						FROM imcontato c
							JOIN imgrupo g on g.idimgrupo=c.idcontato
								".getidempresa('g.idempresa','imgrupo')." -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='imgrupo'
								AND g.status = 'ATIVO'	
								AND c.status = 'A'
								AND g.tipoobjetoext = 'clone'

						 LEFT JOIN sgsetor s on s.idsgsetor = g.idobjetoext
						 left JOIN imgrupo gc on gc.idobjetoext = g.idimgrupo	-- AND c.idempresa=1 
						 and gc.tipoobjetoext = 'clone'
						 UNION
						 SELECT c.idcontato
							,c.objetocontato
							,g.grupo as rotulo
							,c.ultimamsg
							,(select nome 
									from arquivo ar force index(idobjeto_tipoobjeto) 
									where ar.tipoarquivo='AVATAR'
									and ar.tipoobjeto='sgsetor' 
									and ar.idobjeto=c.idcontato
									order by idarquivo desc
									limit 1) as arquivoavatar
							,null as cargo
							,null as webmailemail
							,1 as `online`
							,null as ultimoip
							,null as ultimoping
							,null as area
							,if(gc.idimgrupo > 0, gc.idimgrupo, if(g.tipoobjetoext = 'clone',c.idcontato, 0)) as idimclonegrupo
							,s.idtipopessoa,
							'' as ramalfixo,
							'chat' as tipocontato
								
						FROM imcontato c
							JOIN imgrupo g on g.idimgrupo=c.idcontato
								".getidempresa('g.idempresa','imgrupo')." -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='imgrupo'
								AND g.status = 'ATIVO'	
								-- AND c.status = 'A'
								AND not g.tipoobjetoext = 'clone'
						
						 LEFT JOIN sgsetor s on s.idsgsetor = g.idobjetoext
						 left JOIN imgrupo gc on gc.idobjetoext = g.idimgrupo	-- AND c.idempresa=1 
						 and gc.tipoobjetoext = 'clone'
						 where
							exists (select 1 from imgrupopessoa a where a.idimgrupo = g.idimgrupo and a.idpessoa = c.idpessoa)
						UNION
						 SELECT c.idcontato
							,c.objetocontato
							,g.grupo as rotulo
							,c.ultimamsg
							,(select nome 
									from arquivo ar force index(idobjeto_tipoobjeto) 
									where ar.tipoarquivo='AVATAR'
									and ar.tipoobjeto='sgsetor' 
									and ar.idobjeto=c.idcontato
									order by idarquivo desc
									limit 1) as arquivoavatar
							,null as cargo
							,null as webmailemail
							,1 as `online`
							,null as ultimoip
							,null as ultimoping
							,null as area
							,if(gc.idimgrupo > 0, gc.idimgrupo, if(g.tipoobjetoext = 'clone',c.idcontato, 0)) as idimclonegrupo
							,s.idtipopessoa,
							'' as ramalfixo,
							'compartilhamento' as tipocontato
						FROM imcontato c
							JOIN imgrupo g on g.idimgrupo=c.idcontato
								".getidempresa('g.idempresa','imgrupo')." -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='imgrupo'
								AND g.status = 'ATIVO'	
								-- AND c.status = 'A'
								AND g.tipoobjetoext in ('sgsetor', 'manual')
								
						
						 LEFT JOIN sgsetor s on s.idsgsetor = g.idobjetoext AND idtipopessoa is null
						 left JOIN imgrupo gc on gc.idobjetoext = g.idimgrupo	-- AND c.idempresa=1 
						 and gc.tipoobjetoext = 'clone'
						 UNION
						 SELECT c.idcontato
							,c.objetocontato
							,g.grupo as rotulo
							,c.ultimamsg
							,(select nome 
									from arquivo ar force index(idobjeto_tipoobjeto) 
									where ar.tipoarquivo='AVATAR'
									and ar.tipoobjeto='sgsetor' 
									and ar.idobjeto=c.idcontato
									order by idarquivo desc
									limit 1) as arquivoavatar
							,null as cargo
							,null as webmailemail
							,1 as `online`
							,null as ultimoip
							,null as ultimoping
							,null as area
							,if(gc.idimgrupo > 0, gc.idimgrupo, if(g.tipoobjetoext = 'clone',c.idcontato, 0)) as idimclonegrupo
							,s.idtipopessoa,
							'' as ramalfixo,
							'compartilhamento' as tipocontato
						FROM imcontato c
							JOIN imgrupo g on g.idimgrupo=c.idcontato
								".getidempresa('g.idempresa','imgrupo')." -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='imgrupo'
								AND g.status = 'ATIVO'	
								-- AND c.status = 'A'
								AND g.tipoobjetoext in ('sgsetor', 'manual','tipopessoa','_lp')				
						 LEFT JOIN sgsetor s on s.idsgsetor = g.idobjetoext AND idtipopessoa is null
						 left JOIN imgrupo gc on gc.idobjetoext = g.idimgrupo	-- AND c.idempresa=1 
						 and gc.tipoobjetoext = 'clone'					
					-- AREA
					UNION
					SELECT c.idcontato
							,c.objetocontato
							,g.grupo as rotulo
							,c.ultimamsg
							,(select nome 
									from arquivo ar force index(idobjeto_tipoobjeto) 
									where ar.tipoarquivo='AVATAR'
									and ar.tipoobjeto='sgarea' 
									order by idarquivo desc
									limit 1) as arquivoavatar
							,null as cargo
							,null as webmailemail
							,1 as `online`
							,null as ultimoip
							,null as ultimoping
							,null as area
							,if(gc.idimgrupo > 0, gc.idimgrupo, if(g.tipoobjetoext = 'clone',c.idcontato, 0)) as idimclonegrupo
							,null AS idtipopessoa,
							'' as ramalfixo,
							'chat' as tipocontato
						FROM imcontato c
							JOIN imgrupo g on g.idimgrupo=c.idcontato -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								".getidempresa('g.idempresa','imgrupo')." 
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='imgrupo'
								AND g.status = 'ATIVO'	
								AND c.status = 'A'
								AND g.tipoobjetoext = 'clone'

						 LEFT JOIN sgarea s on s.idsgarea = g.idobjetoext
						 left JOIN imgrupo gc on gc.idobjetoext = g.idimgrupo	-- AND c.idempresa=1 
						 and gc.tipoobjetoext = 'clone'
					-- DEPARTAMENTO
					UNION
						SELECT c.idcontato
							,c.objetocontato
							,g.grupo as rotulo
							,c.ultimamsg
							,(select nome 
									from arquivo ar force index(idobjeto_tipoobjeto) 
									where ar.tipoarquivo='AVATAR'
									and ar.tipoobjeto='sgdepartamento' 
									and ar.idobjeto=c.idcontato
									order by idarquivo desc
									limit 1) as arquivoavatar
							,null as cargo
							,null as webmailemail
							,1 as `online`
							,null as ultimoip
							,null as ultimoping
							,null as area
							,if(gc.idimgrupo > 0, gc.idimgrupo, if(g.tipoobjetoext = 'clone',c.idcontato, 0)) as idimclonegrupo
							,null AS idtipopessoa,
							'' as ramalfixo,
							'chat' as tipocontato
						FROM imcontato c
							JOIN imgrupo g on g.idimgrupo=c.idcontato
								".getidempresa('g.idempresa','imgrupo')." -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
								AND c.idpessoa=".$this->idpessoa."
								".$sqlContatoEspecifico."
								AND c.objetocontato='imgrupo'
								AND g.status = 'ATIVO'	
								AND c.status = 'A'
								AND g.tipoobjetoext = 'clone'

						 LEFT JOIN sgdepartamento s on s.idsgdepartamento = g.idobjetoext
						 left JOIN imgrupo gc on gc.idobjetoext = g.idimgrupo	-- AND c.idempresa=1 
						 and gc.tipoobjetoext = 'clone'				
						 ";
	
		//die($sqlgr);
		$query = d::b()->query($sqlgr) or die("getRowsetGrupos: \n\n". mysqli_error(d::b()));
		
		return $query;		
	}
	
   /**
    * Verificar mensagens antigas
    **/
	function historico() {

		global $JSON;

		$this->setOnline();

		$sm = "SELECT m.idimmsg
					,b.idimmsgbody
					,ifnull(b.msg, m.descr) as msg
					,m.tipo
					,if(b.idpessoa=".$this->idpessoa.",'eu',b.idpessoa) as sender
					,m.idimgrupo
					,m.status
					,m.statustarefa
					,m.criadoem
					,m.datatarefa
				FROM immsg m force index(idpessoa_idimmsg) 
					LEFT JOIN immsgbody b on (b.idimmsgbody = m.idimmsgbody) 
				WHERE m.idpessoa = ".$this->idpessoa."
					and m.tipo in ('M','T','X','P')
				ORDER BY b.idimmsgbody DESC -- últimas mensagens
				LIMIT "._CHAT_MAX_HISTORICO;

		$res = d::b()->query($sm) or die("Erro ao recuperar mensagens#1: ".mysqli_error(d::b()));

		$arrColunas = mysqli_fetch_fields($res);
		$arrret = array();
		$i = mysqli_num_rows($res);
		//A consulta á ordenada decrescente, mas á necessário implementar uma lógica
		// inversa para que a lista de notificações apareça com "a mais recente primeiro"
		
		while ($r = mysqli_fetch_assoc($res)) {
			//para cada coluna resultante do select cria-se um item no array
			foreach ($arrColunas as $col) {
				$arrret[$i][$col->name]=$r[$col->name];
			}

			$i--;
		}

		return $JSON->encode($arrret);
	}

   /**
    * Recuperar conversa
    **/
	function conversa($inIdcontato, $inObjetocontato, $inIdimmsg=false)
	{	
		global $JSON;

		$this->setOnline();

		if (!$inIdcontato) {
			//Sender não informado
			return '{"code":"SENDER_NAO_INFORMADO_CONVERSA"}';
		}
		
		if (!is_numeric($inIdcontato)) {
			//Formato do Sender invalido
			return '{"code":"SENDER_FORMATO_INVALIDO_CONVERSA"}';
		}
		
		//Filtrar por conversas tipo imgrupo ou pessoa
		if ($inObjetocontato == "pessoa") {
			$sConversa = " ((b.idpessoa = ".$this->idpessoa." and m.idpessoa = ".$inIdcontato.") or (b.idpessoa = ".$inIdcontato." and m.idpessoa = ".$this->idpessoa.")) and m.idimgrupo is null -- conversa pessoa para pessoa ";
		} elseif($inObjetocontato=="imgrupo") {
			$sConversa = " m.idimgrupo=".$inIdcontato." and m.idpessoa = ".$this->idpessoa;
		} else {
			return '{"code":"SENDER_TIPO_INVALIDO_CONVERSA"}';
		}
		
		//Lógica para recuperar mensagens anteriores
		$sMsgAnteriores = (!empty($inIdimmsg) and is_numeric($inIdimmsg)) ? " and m.idimmsg < ".$inIdimmsg : "";

		
		$sm = "SELECT m.idimmsg
					,b.idimmsgbody
					,IFNULL(b.msg, m.descr) AS msg
					,m.tipo
					,IF(b.idpessoa=".$this->idpessoa.",'eu',b.idpessoa) as sender
					,m.idimgrupo
					,m.status
					,m.statustarefa
					,m.criadoem
					,m.datatarefa
				FROM immsg m
					 JOIN immsgbody b on (b.idimmsgbody = m.idimmsgbody) 
				WHERE 
					".$sConversa."
					and m.tipo in ('M','X','P')
					".$sMsgAnteriores."
				ORDER BY idimmsg DESC LIMIT 100";
		//10-07-2018 ALTERADO O FILTRO PARA EXIBIR APENAS MENSAGENS DE TEXTO NO CHAT
		//and m.tipo in ('M','T','X')
		//die($sm);

		$res = d::b()->query($sm) or die("Erro ao recuperar mensagens#2: ".mysqli_error(d::b()));

		$arrColunas = mysqli_fetch_fields($res);
		$arrret = array();

		while ($r = mysqli_fetch_assoc($res)) {
			//para cada coluna resultante do select cria-se um item no array
			foreach ($arrColunas as $col) {
				$arrret[$r["idimmsg"]][$col->name] = $r[$col->name];
			}

			$smbody = "SELECT modulo, modulopk FROM immsgbody WHERE idimmsgbody = ". $arrret[$r["idimmsg"]]["idimmsgbody"] . ";";
			$resBody = d::b()->query($smbody) or die("Erro ao recuperar mensagens#3: ".mysqli_error(d::b()));
			$arrColunasBody = mysqli_fetch_fields($resBody);

			while ($rBody = mysqli_fetch_assoc($resBody)) {
				foreach ($arrColunasBody as $colBody) {
					$arrret[$r["idimmsg"]][$colBody->name] = $rBody[$colBody->name];
				}
			}
			//Trata a coluna de mensagem
			$arrret[$r["idimmsg"]]["msg"] = stripslashes($r["msg"]);
			$arrret[$r["idimmsg"]]["_anexos"] = $this->getAnexos($r["idimmsgbody"]);
		}

		return $JSON->encode($arrret);
	}

   /**
    * Verificar novas mensagens
    **/
	function refresh() 
	{
		global $JSON;
		
		$this->setOnline();

		/* $sm = "SELECT m.idimmsg,
					  b.idimmsgbody,
					  IFNULL(b.msg, m.descr) AS msg,
					  m.tipo,
					  IF(b.idpessoa = '".$this->idpessoa."', 'eu', b.idpessoa) AS sender,
					  m.idimgrupo,
					  m.status,
					  m.statustarefa,
					  m.criadoem,
					  m.datatarefa
				 FROM immsg m FORCE INDEX (IDPESSOA_STATUS) LEFT JOIN immsgbody b ON (b.idimmsgbody = m.idimmsgbody)
				WHERE m.idpessoa = '".$this->idpessoa."' AND (m.status = 'N')
				  AND m.tipo IN ('M' , 'X', 'P') 
			UNION 
				SELECT e.idevento AS idimmsg,
					   e.idevento AS idimmsgbody,
					   e.evento AS msg,
					   'T' AS tipo,
					   e.idpessoa AS sender,
					   NULL AS idimgrupo,
					   e.status,
					   'A' AS statustarefa,
					   e.criadoem,
					   e.inicio AS datatarefa
				  FROM evento e JOIN eventotipo et ON et.ideventotipo = e.ideventotipo
				 WHERE et.dashboard = 'Y'
				   AND EXISTS( SELECT 1
								 FROM fluxostatuspessoa fsp
								WHERE fsp.idmodulo = e.idevento
								  AND fsp.modulo = 'evento'
								  AND IF(e.ideventopai, e.inicio <= DATE_FORMAT(NOW(), '%Y-%m-%d'), 1) = 1
								  AND (repetirate IS NULL)
								  AND fsp.idobjeto = '".$this->idpessoa."'
								  AND fsp.tipoobjeto = 'pessoa'
								  AND oculto = 0)"; */
								  
		//MAF: removida a consulta em UNION do chat: o chat nao vai ser mais usado para "novas notificacoes de evento"
		$sm = "SELECT m.idimmsg,
					  b.idimmsgbody,
					  IFNULL(b.msg, m.descr) AS msg,
					  m.tipo,
					  IF(b.idpessoa = '".$this->idpessoa."', 'eu', b.idpessoa) AS sender,
					  m.idimgrupo,
					  m.status,
					  m.statustarefa,
					  m.criadoem,
					  m.datatarefa
				 FROM immsg m FORCE INDEX (IDPESSOA_STATUS) LEFT JOIN immsgbody b ON (b.idimmsgbody = m.idimmsgbody)
				WHERE m.idpessoa = '".$this->idpessoa."' AND (m.status = 'N')
				  AND m.tipo IN ('M' , 'X', 'P')";

		//die($sm);
		$res = d::b()->query($sm) or die("Erro ao recuperar mensagens#4: ".mysqli_error(d::b()));

		$arrColunas = mysqli_fetch_fields($res);
		$arrret=array();
		while($r = mysqli_fetch_assoc($res)){
			//para cada coluna resultante do select cria-se um item no array
			foreach ($arrColunas as $col) {
				$arrret[$r["idimmsg"]][$col->name]=strip_tags($r[$col->name]);
			}
			//Exclui eventos
			if($r["tipo"]=="E"){
				$sdev = "DELETE FROM immsg WHERE idimmsg = ".$r["idimmsg"];
				d::b()->query($sdev) or die("refresh erro:". mysqli_error(d::b()));
			}
		}

		return $JSON->encode($arrret);
	}
	
	function clonargrupo($_idimgrupo, $_idpessoa, $_nomepessoa, $_idimregra, $_grupo) 
	{
		global $JSON;
      
		if($_idpessoa and $_idimgrupo)
		{	
			$sql = "SELECT 1 FROM imcontato WHERE idpessoa = ".$_idpessoa." and idcontato = ".$_idimgrupo." -- and idimregra = ".$_idimregra.";";				
			$resf=d::b()->query($sql) or die("A Consulta na imcontato falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");                
			$qtdf=mysqli_num_rows($resf);
			
			if ($qtdf == 0)
			{
				$sm = "INSERT INTO imgrupo (idimgrupo, idempresa, grupo, idobjetoext, tipoobjetoext, descr, STATUS, inseridomanualmente, criadopor, criadoem, alteradopor, alteradoem) 
											(SELECT NULL, g.idempresa, CONCAT('".$_nomepessoa."', ' + ', grupo) AS novonome, '".$_idimregra."' AS referencia, 'clone', 'Vinculado atraves da regra', 'ATIVO', 'N', NULL, NOW(), NULL, NOW()
												FROM imgrupo g
												WHERE idimgrupo = ".$_idimgrupo.") ON DUPLICATE KEY
											UPDATE idimgrupo = g.idimgrupo, idempresa = g.idempresa,grupo = CONCAT(g.grupo,' - ', '".$_nomepessoa."'), idobjetoext = '".$_idimregra."', tipoobjetoext = 'clone', descr = 'Vinculado atraves da regra', STATUS = 'ATIVO', inseridomanualmente = 'N', alteradopor = NULL, alteradoem = NOW()";
				d::b()->query($sm) or die("Erro ao criar clonar grupo: ".mysqli_error(d::b()));
				
				$_novogrupo =  mysqli_insert_id(d::b());
				
				$sm = "INSERT INTO imgrupopessoa (SELECT NULL, idempresa, ".$_novogrupo.", idpessoa, NOW()
													FROM imgrupopessoa i
													WHERE idimgrupo = ".$_idimgrupo." 
												UNION
													SELECT DISTINCT NULL, idempresa, LAST_INSERT_ID(), ".$_idpessoa.", NOW()
													FROM imgrupopessoa i
													WHERE idimgrupo = ".$_idimgrupo.")";
				echo $sm.'<br>';
				d::b()->query($sm) or die("Erro ao cadastrar pessoas após clonar grupo: ".mysqli_error(d::b()));
				
				$sm = "INSERT INTO imregra VALUES(null,1,'imgrupo',".$_novogrupo.",'imgrupo',".$_novogrupo.",'GRUPO', 'ATIVO');";
				//echo $sm.'<br>';
				d::b()->query($sm) or die("Erro ao criar regra após clonar grupo: ".mysqli_error(d::b()));
				
				$sm = "SELECT 1, r.idimregra, gp.idpessoa, gc.idimgrupo AS idcontato, 'imgrupo' AS objetocontato, 'N', NOW(),'A', NOW() AS ultimamsg
							FROM imregra r
							JOIN imgrupopessoa gp ON gp.idimgrupo=r.idobjetoorigem -- TODAS AS PESSOAS
							JOIN imgrupo gc ON gc.idimgrupo=r.idobjetodestino -- GRUPOS RELACIONADOS
						WHERE r.tiporegra='GRUPO' AND r.tipoobjetoorigem='imgrupo' AND r.tipoobjetodestino = 'imgrupo' AND gc.idimgrupo = ".$_novogrupo .";";
				//echo $sm.'<br>';
				d::b()->query($sm) or die($sm."Erro ao criar atualizar contatos após criar regra de clonar grupo: ".mysqli_error(d::b()));		
				
				return $_novogrupo; 				
			} 
		}
	}

	function grupogrupo($_idimregra, $_grupo) 
	{
		global $JSON;

		if($_idimregra)
		{
			
			$sqlf = "SELECT idimgrupo FROM imgrupo WHERE idobjetoext = ".$_idimregra."";			
			//d::b()->query($sqlf) or die("Erro ao criar grupogrupo: ".mysqli_error(d::b()));
			
			$resf=d::b()->query($sqlf) or die("A Consulta na imcontato falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlf");			
			$qtdf=mysqli_num_rows($resf);
			
			while($row = mysqli_fetch_assoc($resf)){
				$_novogrupo = $row['idimgrupo'];
			}
				
			if ($_novogrupo ==  '')
			{
				$_novogrupo;
				$sm = "INSERT INTO imgrupo (idimgrupo, idempresa, grupo, idobjetoext, tipoobjetoext, descr, STATUS, inseridomanualmente, criadopor, criadoem, alteradopor, alteradoem) 
											(SELECT NULL, 1, '".$_grupo."', '".$_idimregra."' AS referencia, 'grupogrupo', 'Vinculado atraves da regra', 'ATIVO', 'N', NULL, NOW(), NULL, NOW())";
				d::b()->query($sm) or die("Erro ao criar clonar grupo: ".mysqli_error(d::b()));
				
				$_novogrupo =  mysqli_insert_id(d::b());
				
				//$sm = "Insert into imregra values(null,1,'imgrupo',".$_novogrupo.",'imgrupo',".$_novogrupo.",'grupogrupo', 'ATIVO');";
				//echo $sm.'<br>';
				//d::b()->query($sm) or die("Erro ao criar regra após criar grupogrupo: ".mysqli_error(d::b()));			
			}

			$sm = "INSERT INTO imgrupopessoa (SELECT NULL, go.idempresa, ".$_novogrupo.", gpo.idpessoa, NOW()
												FROM imregra r
												JOIN imgrupo go ON go.idimgrupo = r.idobjetoorigem
												JOIN imgrupopessoa gpo ON (gpo.idimgrupo = go.idimgrupo)
												JOIN imgrupo gd ON gd.idimgrupo = r.idobjetodestino
											   WHERE r.tiporegra = 'grupogrupo' AND r.idimregra = ".$_idimregra." AND r.status = 'ATIVO' 
											     AND NOT EXISTS (SELECT 1
												 				   FROM imgrupopessoa a
																  WHERE a.idimgrupo = ".$_novogrupo." AND gpo.idpessoa = a.idpessoa) 
											UNION
											  SELECT NULL, gd.idempresa, ".$_novogrupo.", gpd.idpessoa, NOW()
											  	FROM imregra r
												JOIN imgrupo gd ON gd.idimgrupo = r.idobjetodestino
												JOIN imgrupopessoa gpd ON (gpd.idimgrupo = gd.idimgrupo)
												JOIN imgrupo go ON go.idimgrupo = r.idobjetoorigem
											   WHERE r.tiporegra = 'grupogrupo' AND r.idimregra = ".$_idimregra." AND r.status = 'ATIVO' 
											     AND NOT EXISTS (SELECT 1
												 				   FROM imgrupopessoa a
																  WHERE a.idimgrupo = ".$_novogrupo." AND gpd.idpessoa = a.idpessoa));";	
			$sm.'<br>';
			d::b()->query($sm) or die("Erro ao cadastrar pessoas após cadastrar grupogrupo: ".mysqli_error(d::b()));
			
			
			//Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
			$sm = "replace into imcontato (idempresa, idimregra, idpessoa, idcontato, objetocontato, inseridomanualmente,criadoem,status, ultimamsg)
					SELECT gp.idempresa, r.idimregra, gp.idpessoa, gc.idimgrupo as idcontato, 'imgrupo' as objetocontato, 'N', now(),'A', (
					SELECT ultimamsg from imcontato where idpessoa = gp.idpessoa and idcontato = gc.idimgrupo and objetocontato = 'imgrupo')
					as ultimamsg
					FROM imregra r 
					JOIN imgrupo gc on gc.idobjetoext  =r.idimregra-- GRUPOS RELACIONADOS 
					JOIN imgrupopessoa gp on gp.idimgrupo=gc.idimgrupo-- TODAS AS PESSOAS 
					where r.tiporegra='grupogrupo' and r.tipoobjetoorigem='imgrupo' and r.tipoobjetodestino = 'imgrupo' and gc.idimgrupo = ".$_novogrupo .";";
			//echo $sm.'<br>';
			d::b()->query($sm) or die($sqm."Erro ao criar atualizar contatos após criar regra de clonar grupo: ".mysqli_error(d::b()));
	
			return $_novogrupo; 			
		}		
	}
   
   /**
    * Enviar mensagem para outro usuário
    **/
	function enviar($inContatos, $inMsg, $inImmsgbody=false, $inMsgTipo="M", $inDatatarefa, $inmodulopk, $inmodulo) {
		
		global $JSON;
		
		//Os contatos devem necessariamente vir dentro de um array de javascript: [{idcontato:vlr, objetocontato:vlr},{...}]
		$contatos = $JSON->decode($inContatos);

		if (!is_array($contatos)) return '{"code":"JSON_INVALIDO"}';

		//Loop em cada contato para validar se as propriedades foram devidamente informados *para cada contato*
		$ic = 0;

		foreach ($contatos as $i=>$c) {
			$ic++;
			if (empty($c->idcontato)) { return '{"code":"RECIPIENT_NAO_INFORMADO"}'; }
			if (empty($c->objetocontato)) { return '{"code":"OBJ_RECIPIENT_NAO_INFORMADO"}'; }
		}
		
		//Quantidade de contatos
		if ($ic == 0) { return '{"code":"RECIPIENT_0"}'; }

		//Valida e transcodifica a mensagem
		if (empty($inMsg)) { return '{"code":"MSG_NAO_INFORMADA"}'; }

		$message = ($inMsg);
		$message = d::b()->real_escape_string($message);
		
		//Valida a data da mensagem
		if (!$inImmsgbody and $inMsgTipo=="T" and empty($inDatatarefa)) return '{"code":"TAREFA_INFORMADA_INCORRETAMENTE"}';
		$inDatatarefa = ($inMsgTipo=="T" and !empty($inDatatarefa)) ? "'". validadatetime($inDatatarefa)."'" : "NULL";
		$statustarefa = ($inMsgTipo=="T" or $inMsgTipo=="A") ? "'A'" : "NULL";

		//Valida o tipo da mensagem
		if (!$inImmsgbody and !in_array($inMsgTipo, $this->acoesPermitidas)) return '{"code":"MSG_NAO_PREVISTA"}';
		
		//Atualiza status
		$this->setOnline();

		$compartilhamento = (!$inImmsgbody) ? false : true;

		if (!$inImmsgbody) {
			/****************************************************************
			 *			Cria corpo da mensagem: Insere na msgbody
			 ****************************************************************/
			$sm = "";

			if (strpos($message, 'img') !== false) {

			}

			if (empty($inmodulo) and empty($inmodulopk)) {
				
				$sm = "INSERT INTO immsgbody(msg, idpessoa, criadoem)
					VALUES ('".$message."',".$this->idpessoa.",now())";

			} else {

				$sm = "INSERT INTO immsgbody(msg, idpessoa, modulo, modulopk, criadoem)
					VALUES ('".$message."',".$this->idpessoa.",'".$inmodulo."','".$inmodulopk."',now())";

			}

			d::b()->query($sm) or die("Erro ao criar msgbody: ".mysqli_error(d::b()));

			//recupera o ultimo ID inserido
			$idimmsgbodyins = mysqli_insert_id(d::b());

			if (empty($idimmsgbodyins)) {
				//Erro: valor vazio ao recuperar insert_id
				return '{"code":"VALOR_VAZIO_INSERTID"}';
			}

		} else {

			if (!is_numeric($inImmsgbody)) {
				return '{"code":"MSGBODY_INFORMADO_INVALIDO"}';
			} else {
				$idimmsgbodyins = $inImmsgbody;
			}

		}
		
	
		//Loop em cada contato
		foreach ($contatos as $i => $c) {

			//Recupera as informações dos contato(s)/Grupo(s) de destino informados por _POST 
			$contato = $this->contato($c->idcontato, $c->objetocontato);

			if ($contato === false) {
				//Usuário não á contato
				return '{"code":"USUARIO_NAO_ENCONTRADO"}';
			} else {
				
				//Verificar se trata-se de uma mensagem direta ou mensagem para grupo. Em cada caso irá realizar loop: 1 vez para mensagem direta e várias vezes para cada membro de grupo
				$arrRecipients=array();

				if ($c->objetocontato == "imgrupo") {
					$arrRecipients = $contato["membros"];
					$idimgrupo=$c -> idcontato;
				} else {
					$arrRecipients[$c->idcontato] = "";
					$idimgrupo = "null";
				}
				
				$nsql = "SELECT idimmsg FROM ".$this->tabmsg." WHERE idpessoa = ".$this->idpessoa." and idimmsgbody = ".$idimmsgbodyins." and tipo = '".$inMsgTipo."';";
				
				$resn = d::b()->query($nsql) or die("Erro ao recuperar mensagens#5: ".mysqli_error(d::b()));
				
				while ($rn = mysqli_fetch_assoc($resn)) {
					$idimmsg = $rn['idimmsg'];
				}
				
				if ($inMsgTipo == "T" and $idimmsg == '') {

					$si = "INSERT INTO ".$this->tabmsg." 
						(idimmsgbody, idimgrupo, tipo, idpessoa, criadoem, datatarefa, statustarefa) VALUES 
						(".$idimmsgbodyins.",".$idimgrupo.",'".$inMsgTipo."',".$this->idpessoa.",now(),".$inDatatarefa.",".$statustarefa.")";
					
					d::b()->query($si) or die("Erro ao inserir msg: ".mysqli_error(d::b()));

				}
					
				//Loop em cada usuário de destino para enviar mensagens
				$is = 0;
				
				while (list($idcontatorec, $v) = each($arrRecipients)) {
					
					//echo $idcontatorec.' ';
					if ($compartilhamento and $idcontatorec == $this->idpessoa) {
						continue;//Não compartilhar mensagem consigo mesmo
					}
					//echo $idcontatorec.'<br>';
					$is++;
					
					$nsql = "SELECT idimmsg FROM ".$this->tabmsg." WHERE idpessoa = ".$idcontatorec." and idimmsgbody = ".$idimmsgbodyins." and tipo = '".$inMsgTipo."';";
					
					$resn = d::b()->query($nsql) or die("Erro ao recuperar mensagens#6: ".mysqli_error(d::b()));
					$idmsginsn = '';

					while ($rn = mysqli_fetch_assoc($resn)) {
						$idmsginsn = $rn['idimmsg'];
					}
				
					if (!$idmsginsn) {
					/****************************************************************
					* Insere a mensagem na tabela de chat com o id da mensagem relacionada
					****************************************************************/
						$si = "INSERT INTO ".$this->tabmsg." 
								(idimmsgbody, idimgrupo, tipo, idpessoa, criadoem, datatarefa, statustarefa) VALUES 
								(".$idimmsgbodyins.",".$idimgrupo.",'".$inMsgTipo."',". $idcontatorec . ",now(),".$inDatatarefa.",".$statustarefa.")";
						
								d::b()->query($si) or die("Erro ao inserir msg: ".mysqli_error(d::b()));
						
						//recupera o ultimo ID inserido
						$idmsgins = mysqli_insert_id(d::b());

					} else {
						$idmsgins = $idmsginsn;
					}

					if ($inMsgTipo=="A") {

					    $sa="INSERT INTO carrimbo
						    (idempresa,idpessoa, idobjeto, tipoobjeto, status,criadopor, criadoem,alteradopor,alteradoem)
						    VALUES
						    (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idcontatorec.", '".$inmodulopk."', '".$inmodulo."', 'PENDENTE','".$_SESSION["SESSAO"]["USUARIO"]."', now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now());";
						
						d::b()->query($sa) or die("Erro ao inserir msg: ".mysqli_error(d::b()));

					}

					/****************************************************************
					* Atualiza a ordenação dos contatos no painel de contatos, conforme a data da última mensagem enviada
					****************************************************************/
					//Atualiza para o contato:
					$sau1 = "UPDATE ".$this->tabcontato." 
							SET ultimamsg=now()
							WHERE 1 -- idempresa=".$this->idempresa." 
								AND idpessoa=".$idcontatorec."
								AND idcontato=".$this->idpessoa." 
								AND objetocontato='".$c->objetocontato."'";

					d::b()->query($sau1) or die("Atualizar recipient: Enviadas [".$is."] de [".count($arrRecipients)."]. Procedimento interrompido: ".mysqli_error(d::b()));
				}

				//Atualiza para o usuário que enviou a mensagem:
				$sau2 = "UPDATE ".$this->tabcontato." 
						SET ultimamsg=now()
						WHERE 1 -- idempresa=".$this->idempresa." 
							AND idpessoa=".$this->idpessoa." 
							AND idcontato=".$c->idcontato." 
							AND objetocontato='".$c->objetocontato."'";

				d::b()->query($sau2) or die("Atualizar sender: ".mysqli_error(d::b()));

				//Caso seja somente 1 recipient, responder com o id gerado
				if ($ic == 0) {
					//Responde de acordo com o status do contato
					if ($c->contato["online"] == "1") {
						//Mensagem enviada com sucesso
						return $JSON->encode(array("code"=>"MSG_ENVIADA",
										"idimmsg"=>$idmsgins,
										"idimmsgbody"=>$idimmsgbodyins,
										//,"msg"=>stripslashes($message)
										"criadoem"=>date("Y-m-d H:i:s")));
					} else {
						//Mensagem enviada para contato offline
						//return '{"code":"MSG_ENVIADA_OFFLINE","idimmsg":"'.$idmsgins.'","msg":"'.stripslashes($message).'","criadoem":"'.date("Y-m-d H:i:s").'"}';
						return $JSON -> encode(
							array ( "code" => "MSG_ENVIADA_OFFLINE",
									"idimmsg"=>$idmsgins,
									"idimmsgbody"=>$idimmsgbodyins,
									//,"msg"=>stripslashes($message)
									"criadoem"=>date("Y-m-d H:i:s")
							)
						);
					}
				}
			}
		}//foreach ($contatos as $i=>$c){
		
		//Caso haja mais de 1 recipient, responder somente com o codigo da msg
		if ($ic > 0) {
			//Mensagens enviadas com sucesso
			return $JSON -> encode(
				array ( "code"=>"MSG_ENVIADA",
						"idimmsg" => $idmsgins,
						"idimmsgbody"=>$idimmsgbodyins,
						"criadoem"=>date("Y-m-d H:i:s")
				)
			);
		}
	}
   
	/**
	 * Efetuar Update na mensagem
	 **/
	function alterar($inIdimmsgbody, $message){
		
		global $JSON;

		//Atualiza status
		$this->setOnline();

		//maf: decodificar texto utf8
		$message = ($message);
		$message = d::b()->real_escape_string($message);

		//Update na msgbody
		$sm = "UPDATE immsgbody 
				SET msg ='".$message."'
				WHERE idimmsgbody =".$inIdimmsgbody;

		d::b()->query($sm) or die("Erro ao alterar msgbody: ".mysqli_error(d::b()));

		if(mysqli_affected_rows(d::b())>=0){
			//Mensagem enviada com sucesso
			return $JSON->encode(array("code"=>"MSG_ALTERADA"
							,"idimmsgbody"=>$inIdimmsgbody
							,"msg"=>stripslashes($message)
							,"criadoem"=>date("Y-m-d H:i:s")));
		} else {
			return '{"code":"FALHA_UPDATE_MSG"}';
		}
	}
   
	
	/**
	 * Recupera o status do usuario
	 **/
	function contato($inidcontato,$inobjetocontato){
		
		if($inobjetocontato=="imgrupo"){
			$resContato = $this->getRowsetGrupos($inidcontato,$inobjetocontato);
		} else {
			$resContato = $this->getRowsetContatos($inidcontato,$inobjetocontato);
		}
		
		$iContato=mysqli_num_rows($resContato);

		if($iContato==0){
			//O idpessoa não está configurado como contato (@todo: ou não tem permissão para ver o grupo?)
			return false;
		} else {
			$row = mysqli_fetch_assoc($resContato);
			//Acrescenta os membros do grupo
			if($inobjetocontato=="imgrupo"){
				$row["membros"]=$this->getMembrosGrupo($inidcontato,$inobjetocontato);
			}
			return $row;
		}
	}

	/*
	 * Recuperar os membros do grupo
	 */
	function getMembrosGrupo($inIdcontato,$inObjcontato){
		if($inObjcontato=="imgrupo"){
			$sa = "select p.idpessoa, p.nomecurto
					from imgrupopessoa gp 
						join pessoa p on p.idpessoa=gp.idpessoa
						join objempresa oe on oe.idobjeto = p.idpessoa and oe.empresa =".idempresa()." -- Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
							and NOT p.status='INATIVO'
							and gp.idimgrupo = ".$inIdcontato;

			$res = d::b()->query($sa) or die("Erro ao recuperar membros: ".mysqli_error(d::b()));

			$arrret=array();
			while($r = mysqli_fetch_assoc($res)){
				//Cria cor de back e foreground para o avatar do usuário
				$bg = str2Color($r["nomecurto"]);
				$fc = colorContrastYIQ($bg);

				$arrret[$r["idpessoa"]]["nome"]=$r["nomecurto"];
				$arrret[$r["idpessoa"]]["bg"]=$bg;
				$arrret[$r["idpessoa"]]["fc"]=$fc;
			}
			
			//Caso o sender não pertença ao grupo, mas possua permissão para vê-lo, deve receber uma mensagem também
			if(count($arrret[$this->idpessoa])==0){
				$arrret[$this->idpessoa]["nome"]= $this->nome;
			}
		}
		return $arrret;
	}
	
	
	/**
	 * Enviar eventos (mensagens de status, servidor, etc)
	 **/
	function enviaEventoContatos($arrcontatos, $evento) {      
		
		if(count($arrcontatos)>0){
			
			$aonline=array();
			//Verifica quais contatos estão online
			//foreach($arrcontatos as $group => $users){
				foreach($arrcontatos as $idcontato => $user){
					if($user["objetocontato"]=="pessoa" and $user["online"]>0){
						$aonline[]=$user["idcontato"];
					}
				}
			//}

			//Envia eventos para eles
			if(count($aonline) > 0) {
				$v="";
				foreach($aonline as $k => $idpessoa){
					$insert_str .= $v."('".$evento."', 'E', ".$idpessoa.", now())";
					$v=",";
				}
				$sqle = "INSERT INTO ".$this->tabmsg." (descr, tipo, idpessoa, criadoem) VALUES " . $insert_str;
				d::b()->query($sqle) or die('{"erro":"enviaEventoContatos: erro ao enviar eventos"}\n'.$sqle);
			}
		}
	}

	/**
	 * Alterar status de mensagems
	 * Aceita '*' como parâmetro d idimmsg para alterar todas de uma vez. Ex: caso de Marcar Todas como Lidas
	 **/
	function alterarStatusMensagem($inIdcontato,$inObjetocontato,$inidimmsg,$instatus){

		if(strlen($inidimmsg)>0 and strlen($instatus)>0 and !is_numeric($instatus)){

			//Pessoa
			if(!empty($inIdcontato) and !empty($inObjetocontato)){
				if($inObjetocontato=="imgrupo"){
					$strIdPessoa=" and m.idimgrupo=".d::b()->real_escape_string($inIdcontato);
				} else {
					$strIdPessoa=" and exists(
						select 1 from immsgbody b where b.idimmsgbody = m.idimmsgbody and b.idpessoa=".d::b()->real_escape_string($inIdcontato)."
					) ";
				}
			} else {
				return '{"code":"PARAMETRO_INEX_ALTERAR_STATUS_MSG"}';
			}

			//Seleção de mensagens a serem lidas
			if($inidimmsg=="*"){
				
			}elseif(is_numeric($inidimmsg)){
				$strWId=" and m.idimmsg=".d::b()->real_escape_string($inidimmsg)." ";
			} else {
				return '{"code":"PARAMETRO_INV_ALTERAR_STATUS_MSG"}';
			}

			//Update na mensagem
			$su = "UPDATE immsg m
				SET m.status ='".d::b()->real_escape_string($instatus)."'
				WHERE m.idpessoa='".$this->idpessoa."'
					and m.status='N'
					".$strWId
					 .$strIdPessoa;

			d::b()->query($su) or die('{"code":"FALHA_ALTERAR_STATUS_MSG"}');
		} else {
			return '{"code":"FALHA_PARAMETROS_STATUS_MSG"}';
		}
	}

	function anexo($inArquivo,$inNome,$inIdimmsgbody,$inIdimarq){
		
		if(!empty($inArquivo) and !empty($inIdimmsgbody) and empty($inIdimarq)){
			$arq = ($inArquivo);
			$arq = d::b()->real_escape_string($arq);

			$nome = ($inNome);
			$nome = d::b()->real_escape_string($nome);

			//Insere o anexo
			$si = "INSERT INTO imarq(idempresa,idimmsgbody,arq,nome,tipo,criadopor)
				VALUES (".$this->idempresa.",".$inIdimmsgbody.",'".$arq."','".$nome."','L','bim.php')";
			//die($si);
			d::b()->query($si) or die('{"code":"FALHA_INSERIR_ANEXO_1"}');
			
			//recupera o ultimo ID inserido
			$idarq = mysqli_insert_id(d::b());

			return '{"code":"ANEXO_OK","idarq":"'.$idarq.'"}';
			
		} else {
			return '{"code":"ANEXO_NAO_IMPLEMENTADO"}';
		}
	}

	function getAnexos($inIdimmsgbody){
		$sa = "select idimarq 
					,arq
					,nome
				    ,tipo
				from imarq
				where 
					idimmsgbody = ".$inIdimmsgbody;

		$res=d::b()->query($sa) or die('{"code":"FALHA_REC_ANEXO"}');

		$arrret=array();
		while($r = mysqli_fetch_assoc($res)){
			$arrret[$r["idimarq"]]["arq"]=$r["arq"];
			$arrret[$r["idimarq"]]["nome"]=$r["nome"];
			$arrret[$r["idimarq"]]["tipo"]=$r["tipo"];
		}
		return $arrret;
	}

   /**
    * Garbage collector. Resets users who have been inactive for more than 5 minutes.
    *
    * @return void
    * @author Joshua Gross
    **/
   function gc() {
      //Limpar usuários offline [~20% de chance]
      if(rand(1, 100) <= 20) {
         

        //$cleanup_chats = d::b()->query('DELETE FROM ' . SQL_PREFIX . 'chats WHERE user IN(SELECT usuario FROM ' . $this->tabpessoa . ' WHERE  permissaochat = \'Y\' and last_ping < ' . $expire_time . ' AND online > 0)');

		//maf: limpa registros de controle
        //$cleanup_ctrl  = d::b()->query("DELETE FROM " . SQL_PREFIX . "messages WHERE tipo!='M' and stamp < " . (time() - 300));
		//$cleanup_ctrl  = d::b()->query("DELETE FROM " . SQL_PREFIX . "messages WHERE tipo!='M' and timestamp < date_add(sysdate(), interval -5 minute)");

		//maf: expurgo
         //$cleanup_msgs  = d::b()->query("DELETE FROM " . SQL_PREFIX . "messages WHERE tipo='M' and stamp < " . (time() - 300));

		
		//Passar para OFFLINE usuários online que pararam de realizar consultas ao servidor, e notificar os contatos da alteração de status
		$suo = "SELECT 
					p.idpessoa
					,c.idcontato
				FROM ".$this->tabimpessoa." p force index(last_ping_isonline) -- Usuários que permanceram offline por motivos de fechamento do browser ou travamento
					JOIN imcontato c ON (c.idpessoa=p.idpessoa AND c.idcontato!=p.idpessoa AND c.objetocontato='pessoa') -- Contatos de cada usuário offline
					JOIN impessoa pc ON pc.idpessoa=c.idcontato AND pc.online=1 -- Verificação de quais contatos estão online para receber notificações de status
				WHERE 1 -- p.idempresa=".$this->idempresa."
					AND p.ultimoping < date_add(now(), interval -".$this->segundosStatusOffline." second)
					AND p.`online` > 0";

		$ruo = d::b()->query($suo) or die("GC_ERRO1");
		
		//Agrupar por usuário
		$arrUo=array();
		while ($r = mysqli_fetch_assoc($ruo)) {
			//Simula a montagem da estutura de contatos
			$arrUo[$r["idpessoa"]][$r["idcontato"]]["idcontato"]=$r["idcontato"];
			$arrUo[$r["idpessoa"]][$r["idcontato"]]["objetocontato"]="pessoa";
			$arrUo[$r["idpessoa"]][$r["idcontato"]]["online"]=1;
		}
		
		//Efetua update para o usuário e notifica os contatos online dele
		$idpessoa=$this->idpessoa;
		foreach ($arrUo as $idpessoa => $contatos) {
			$uu = "UPDATE ".$this->tabimpessoa." SET online=0
					WHERE idpessoa = ".$idpessoa;

			d::b()->query($uu) or die("GC_ERRO2_".$idpessoa);

			//maf: o chat nao controla mais o status do funcionario
			//Envia notificaçãoo de offline para os contatos online
			//$this->enviaEventoContatos($contatos, "{\"offline\":\"".$idpessoa."\"}");
		}
      }
   }
   
   //Gera mensagens no chat confome configuração realizada no carbon
    function eventosLaudo(){
        //Limpar usuários offline [~10% de chance]
        
        if(rand(1, 100) <= 2) {
            session_start();
            $sessionid = session_id();//PEGA A SESSÃO 
            //Altera o status das notas PENDENTES para CONSULTANDO e reserva para a sessão
            $sqlc = "update immsgconf ic set statusprocesso = 'PROCESSANDO', sessionid = '".$sessionid."' 
                        where tipo not in('E','ET','EP')
                        and statusprocesso = 'ABERTO'
                        and status='ATIVO'";
            $retc = mysql_query($sqlc);

            if(!$retc){
                    //LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
                    echo("Erro ao alterar ALERTA para consulta: \n<br>".mysql_error()."\n<br>".$sqlc);
                    return false;
            }
        
       //busca  as configurações para envio da mensagem
            $sql="select 
                            m.tab,m.modulo,m.rotulomenu,tc.col,ic.idimmsgconf,ic.titulo,ic.tipo,ic.code,ic.mensagem,ic.apartirde
                        from "._DBCARBON."._modulo m,immsgconf ic,"._DBCARBON."._mtotabcol tc
                        where m.modulo =ic.modulo                 
                            and tc.primkey ='Y'         
                            and tc.tab = m.tab
                            and ic.tipo not in('E','ET','EP')
                            and ic.status='ATIVO'
                            and ic.statusprocesso = 'PROCESSANDO'
                            and ic.sessionid = '".$sessionid."'
                            and exists (select 1 from immsgconffiltros f where f.valor!=' ' and f.valor is not null and f.idimmsgconf = ic.idimmsgconf)";
            $res=d::b()->query($sql) or die("A Consulta na immsgconf falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

            while($row=mysqli_fetch_assoc($res)){
                //busca os filtros para seleção
                $sqlf="select col,sinal,valor,nowdias,idimmsgconffiltros from immsgconffiltros where valor!='' and valor!=' ' and valor!='null' and valor is not null and idimmsgconf =".$row["idimmsgconf"];
                $resf=d::b()->query($sqlf) or die("A Consulta na immsgconffiltros falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlf");
                $qtdf=mysqli_num_rows($resf);
                $and=" ";
                if($qtdf>0){
			$clausula="";
                    while($rowf=mysqli_fetch_assoc($resf)){
                       if($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!=''){
                           if($rowf["valor"]=='now'){
                                $valor=date("Y-m-d H:i:s"); 
							}else if($rowf["valor"]=='mais'){
								if(!empty($rowf["nowdias"])){
									$date=date("Y-m-d H:i:s");
									$valor=date('Y-m-d H:i:s', strtotime($date. ' + '.$rowf["nowdias"].' day'));
								}else{
									$valor=date("Y-m-d H:i:s"); 
								}
							}else if($rowf["valor"]=='menos'){
								if(!empty($rowf["nowdias"])){
									$date=date("Y-m-d H:i:s");
									$valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$rowf["nowdias"].' day'));
								}else{
									$valor=date("Y-m-d H:i:s"); 
								}
							}else{
								$valor=$rowf["valor"];  
							}			

                            if($rowf['sinal']=='in'){
                                $strvalor = str_replace(",","','",$valor);
                                $clausula.= $and." a.".$rowf["col"]." in ('".$strvalor."')";
                            }elseif($rowf['sinal']=='like'){
                                $clausula.= $and." a.".$rowf["col"]." like ('%".$valor."%')";
                            } else {
                                 $clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." '".$valor."'";
                            }
                            $and=" and ";
                        }
                    }
                    // busca na tabela configurada os ids
                    $sqlx="SELECT 
                            a.".$row['col']." AS idpk, 1029 as idpessoa
                        FROM
                            ".$row["tab"]." a 
                        WHERE
                            ".$clausula."               
                                AND a.alteradoem > '".$row['apartirde']."'
                                AND NOT EXISTS( SELECT 
                                    1
                                FROM
                                    immsgconflog l
                                WHERE
                                    l.idpk = a.".$row['col']."
                                        AND l.modulo = '".$row['modulo']."'
                                        AND l.idimmsgconf = ".$row['idimmsgconf'].")";
                    //echo($sqlx);die;
                    $resx=d::b()->query($sqlx) or die("A Consulta na tabela de origem dos dados falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlx");
                    while($rowx=mysqli_fetch_assoc($resx)){  
                        // insere um log
                        $sl="INSERT INTO immsgconflog
                            (idempresa,idimmsgconf,idpk,modulo,status,criadopor,criadoem,alteradopor,alteradoem)
                            VALUES
                            (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$row['idimmsgconf'].",".$rowx['idpk'].",'".$row['modulo']."','ENVIANDO','immsgconf',now(),'immsgconf',now())";
                        d::b()->query($sl) or die("Erro ao gerar log [immsgconflog]: ".mysqli_error(d::b()));
                            //recupera o ultimo ID inserido
                        $idimmsgconflog = mysqli_insert_id(d::b());
                         if(empty($idimmsgconflog)){
                            //Erro: valor vazio ao recuperar insert_id
                            return '{"code":"VALOR_VAZIO_INSERTID_LOG"}';
                        }
                        /****************************************************************
                         *			Cria corpo da mensagem: Insere na msgbody
                         ****************************************************************/

                        $sm = "INSERT INTO immsgbody (msg, idpessoa,modulo,modulopk,criadoem)
                                VALUES ('".$row['mensagem']."',".$rowx['idpessoa'].",'".$row['modulo']."','".$rowx['idpk']."',now())";

                        d::b()->query($sm) or die("Erro ao criar msgbody: ".mysqli_error(d::b()));

                        //recupera o ultimo ID inserido
                        $idimmsgbodyins = mysqli_insert_id(d::b());

                        if(empty($idimmsgbodyins)){
                            //Erro: valor vazio ao recuperar insert_id
                            return '{"code":"VALOR_VAZIO_INSERTID"}';
                        }

                        $sqlc="select distinct(idpessoa) as idpessoa 
                                from (	
                                     SELECT 
                                            p.idpessoa, p.nome
                                    FROM
                                            pessoa p,
                                            immsgconfdest c
                                    WHERE
                                            c.objeto = 'pessoa'
                                        and c.status='ATIVO'
                                        AND p.idpessoa = c.idobjeto
                                        AND c.idimmsgconf = ".$row['idimmsgconf']."
                                        AND not p.status = 'INATIVO'
                                ) as u";
                         $resc=d::b()->query($sqlc) or die("A busca dos contatos falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlc");
                        while($rowc=mysqli_fetch_assoc($resc)){
                            /****************************************************************
                            * Insere a mensagem na tabela de chat com o id da mensagem relacionada
                            ****************************************************************/

                            $si = "INSERT INTO immsg
                                            (idimmsgbody, tipo, idpessoa,statustarefa, criadoem) 
                                            VALUES 
                                            (".$idimmsgbodyins.",'".$row['tipo']."',". $rowc['idpessoa'] . ",'A',now())";
                            d::b()->query($si) or die("Erro ao inserir msg: ".mysqli_error(d::b()));

                            //recupera o ultimo ID inserido
                            $idmsgins = mysqli_insert_id(d::b());                       

                            if($row['tipo']=="A"){
                                /****************************************************************
                                * Insere uma assinatura pedente
                                ****************************************************************/
                                $sa="INSERT INTO carrimbo
                                        (idempresa,idpessoa, idobjeto, tipoobjeto, status,criadopor, criadoem,alteradopor,alteradoem)
                                        VALUES
                                        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$rowc['idpessoa'].", '".$rowx['idpk']."', '".$row['modulo']."', 'PENDENTE','immsgconf', now(),'immsgconf',now());";
                                d::b()->query($sa) or die("Erro ao inserir msg: ".mysqli_error(d::b()));    
                            }             
                        }// while($rowc=mysqli_fetch_assoc($resc)){

                        $link="?_modulo=".$row['modulo']."&_acao=u&".$row['col']."=".$rowx['idpk'];
                        $nome=$row['rotulomenu'].": ".$row['col']."=".$rowx['idpk'];

                        $a="INSERT INTO imarq
                                (idempresa,idimmsgbody,arq,nome,tipo,criadopor) 
                                VALUES
                                (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idimmsgbodyins.",'".$link."','".$nome."','L','bim.php2')";
                        d::b()->query($a) or die("Erro ao inserir arquivo: ".mysqli_error(d::b()));   

                        // atualiza o log para sucesso
                        $su="update immsgconflog set status='SUCESSO',idimmsgbody=".$idimmsgbodyins." where idimmsgconflog=".$idimmsgconflog;
                        d::b()->query($su) or die("Erro ao atualizar log [immsgconflog] : ".mysqli_error(d::b()));        
                    }// while($rowx=mysqli_fetch_assoc($resx)){
                }// if($qtdf>0){
            }//while($row=mysqli_fetch_assoc($res)){
            
             //Altera o status das notas PENDENTES para CONSULTANDO e reserva para a sessão
            $sqlc = "update immsgconf ic set statusprocesso = 'ABERTO'
                        where tipo not in('E','ET','EP')
                    and statusprocesso = 'PROCESSANDO'
                    and status='ATIVO'";
            $retc = mysql_query($sqlc);

            if(!$retc){
                    //LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
                    echo("Erro ao voltar status do  ALERTA para ABERTO: \n<br>".mysql_error()."\n<br>".$sqlc);
                    return false;
            }
        }
    }//function eventosLaudo(){

	//Alterar tipo da mensagem
	function msg2tarefa($inIdimmsgbody,$inTransformar){
		if(!empty($inIdimmsgbody) and is_numeric($inIdimmsgbody) and !empty($inTransformar)){
			if($inTransformar==="true"){
				$vtipo="T";
			} else {
				return '{"code":"TRANS_MSG_TIPO_INCORRETO"}';
			}
			
			// Armazena os dados da mensagem, 	
			//A MENSAGEM DEIXARA DE SER ATUALIZADA PARA TAREFA, 
			//E UMA NOVA TAREFA SERÁ CRIADA, E ASSIM TAREFA E MENSAGEM EXISTIRÃO
			
			
			//CRIA O NOVO MSGBODY (CORPO) DA MENSAGEM COPIANDO DA ANTIGA
			$si = "INSERT INTO immsgbody SELECT null, msg, '".$this->idpessoa."', modulo, modulopk, now() from immsgbody where idimmsgbody = ".d::b()->real_escape_string($inIdimmsgbody).";";

			d::b()->query($si) or die('{"code":"FALHA_TRANS_MSG"}');
			
			// RECUPERA O ID DO NOVO BODY INSERIDO
			$idimmsgbodyins = mysqli_insert_id(d::b());
			
			//PEGA OS DADOS DA MSG ANTIGA
			  $check = "select 
					   mb.idpessoa as idpessoa, ifnull(m.datatarefa,m.criadoem) as datatarefa, m.idpessoa as para
					from 
					    immsg m
					join
						immsgbody mb on (mb.idimmsgbody = m.idimmsgbody)
					WHERE mb.idimmsgbody = ".d::b()->real_escape_string($inIdimmsgbody)." ";
				//die();		
			$ruo = d::b()->query($check) or die("GC_ERRO1");

			$i = 0;
			while($r = mysqli_fetch_assoc($ruo)){
				if ($r['idpessoa'] != $r['para']){
						$i++;
						
					$idpessoa = $r['idpessoa'];
					$datatarefa = $r['datatarefa'];
					$para = $r['para'];
				}
			}
			//SE A MENSAGEM FOR PRA UM GRUPO
			if ($i > 1){
				 $si = "INSERT INTO immsg 
					select distinct
					    null, 
					    idpessoa, 
					    ".$idimmsgbodyins." as idimmsgbody,
					    idimgrupo, 
					    'N', 
					    '' as descr, 
					    'T', 
					    null,
					    null,
					    now(), 
					    '".$this->usuario."' as alteradopor, 
					    now(), 
					    null,
					    ifnull(datatarefa,criadoem) as datatarefa, 
					    'A' as statustarefa
					from 
					    immsg 
					WHERE idimmsgbody = ".d::b()->real_escape_string($inIdimmsgbody)." and exists(
						select * from (
							select 1 from immsg m2 where m2.idimmsgbody and m2.idpessoa = ".$this->idpessoa."
						) a2
					)";
					//die();
			} else {
				//SE A MENSAGEM FOR PRA PESSOA
				//echo $para.' '.$idpessoa;
				if ($para == $this->idpessoa){
					$enviar = $idpessoa;
				} else {
					$enviar = $para;
				}
				  $si = "INSERT INTO immsg values
				 (null, '".$enviar."',  ".$idimmsgbodyins.", null, 'N', null,'T',null,null,now(),null,null,null,'".$datatarefa."','A');";
				 
				 
						d::b()->query($si) or die('{"code":"FALHA_TRANS_MSG"}');
				//echo $para.' - '.$idpessoa.' - '.$this->idpessoa;
				
				if ($para != $idpessoa or $this->idpessoa != $para){
				
				  $si = "INSERT INTO immsg 
					select distinct
					    null, 
					    ".$this->idpessoa." as idpessoa, 
					    ".$idimmsgbodyins." as idimmsgbody,
					    idimgrupo, 
					    'N', 
					    '' as descr, 
					    'T', 
					   null,
					    null,
					    now(), 
					    '".$this->usuario."' as alteradopor, 
					    now(), 
					    null,
					    ifnull(datatarefa,criadoem) as datatarefa, 
					    'A' as statustarefa
					from 
					    immsg 
					WHERE idimmsgbody = ".d::b()->real_escape_string($inIdimmsgbody)." and exists(
						select * from (
							select 1 from immsg m2 where m2.idimmsgbody and m2.idpessoa = ".$this->idpessoa."
						) a2
					)";
					
					//die();
				}
			}	
			
			//die();
			//Altera a mensagem
//			$si = "UPDATE immsg 
//					SET tipo='T'
//						, datatarefa=ifnull(datatarefa,criadoem)
//						, alteradoem=now()
//						, alteradopor='".$this->usuario."'
//					WHERE idimmsgbody = ".d::b()->real_escape_string($inIdimmsgbody)." and exists(
//						select * from (
//							select 1 from immsg m2 where m2.idimmsgbody and m2.idpessoa = ".$this->idpessoa."
//						) a2
//					)";

			d::b()->query($si) or die('{"code":"FALHA_TRANS_MSG"}');
			
			

			return '{"code":"TRANS_MSG_OK"}';
			
		} else {
			return '{"code":"TRANS_MSG_PARAMETROS_INCORRETOS"}';
		}
	}


	//Apagar mensagem
	function apagarMsg($inIdimmsgbody) {

		if (!empty($inIdimmsgbody) and is_numeric($inIdimmsgbody)) {

			//Deleta a mensagem
			$sql = "UPDATE immsg 
					SET tipo='X', 
						alteradoem=now(), 
						alteradopor='".$this->usuario."'
					WHERE idimmsgbody = ".d::b()->real_escape_string($inIdimmsgbody)." AND exists(
						SELECT * FROM (
							SELECT 1 FROM immsg m2 WHERE m2.idimmsgbody AND m2.idpessoa = ".$this->idpessoa."
						) a2
					)";

			d::b()->query($sql) or die('{"code":"FALHA_X_MSG"}');

			$this->enviaEventoContatos($this->getListaContatos(), "{\"apagarmsg\":\"".$inIdimmsgbody."\"}");
			
			return '{"code":"X_MSG_OK"}';
			
		} else {
			return '{"code":"X_MSG_PARAMETROS_INCORRETOS"}';
		}
	}

	
	//Atualizar a lista de contatos automaticamente
	function atualizaChat(){
		
		echo "Início: ".date("d/m/Y H:i:s", time()).'<br>'; 

		$grupo = rstr(8);


		re::dis()->hMSet('bim',['inicio' => Date('d/m/Y H:i:s')]);

		$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
									VALUES ('1', '".$grupo ."', 'cron', 'bim', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

		d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);



		pessoaLog('ATBIM');
		//Inativa os usuários temporariamente. Os idpessoa que não forem atualizados serão excluídos no final
		d::b()->query("update ".$this->tabimpessoa." set status='INATIVAR' where inseridomanualmente='N'") or die("ERRO_ATUALIZA_10");
		
		d::b()->query("	update 
							imgrupo g
								join sgsetor s on s.idsgsetor = g.idobjetoext and tipoobjetoext = 'sgsetor' and not g.grupo =  s.setor
						set g.grupo =  s.setor") or die("ERRO_ATUALIZA_11");
		
		//Atualiza o Grupo Área (Lidiane 13-03-2020)
		d::b()->query("	update 
							imgrupo g
								join sgarea s on s.idsgarea = g.idobjetoext and tipoobjetoext = 'sgarea' and not g.grupo =  s.area
						set g.grupo =  s.area") or die("ERRO_ATUALIZA_11_AREA");
		
		//Atualiza o Grupo Departamento (Lidiane 13-03-2020)
		d::b()->query("	update 
							imgrupo g
								join sgdepartamento s on s.idsgdepartamento = g.idobjetoext and tipoobjetoext = 'sgdepartamento' and not g.grupo =  s.departamento
						set g.grupo =  s.departamento") or die("ERRO_ATUALIZA_11_DEPARTAMENTO");
		
		d::b()->query("	update 
							imgrupo g
								join carbonnovo._lp l on l.idlp = g.idobjetoext and tipoobjetoext = '_lp' and not g.grupo =  l.descricao
						set g.grupo =  l.descricao") or die("ERRO_ATUALIZA_11");
		
		d::b()->query("update imgrupo set status = 'ATIVO' where tipoobjetoext = 'sgsetor' and idobjetoext in (select idsgsetor from sgsetor where (status = 'ATIVO' and grupo = 'Y'));") or die("ERRO_ATUALIZA_13");
		d::b()->query("update imgrupo set status = 'INATIVO' where tipoobjetoext = 'sgsetor' and idobjetoext in (select idsgsetor from sgsetor where (status = 'INATIVO' or grupo = 'N'));") or die("ERRO_ATUALIZA_13");
		
		//Atualiza o Grupo Área (Lidiane 13-03-2020)
		d::b()->query("update imgrupo set status = 'ATIVO' where tipoobjetoext = 'sgarea' and idobjetoext in (select idsgarea from sgarea where (status = 'ATIVO' and grupo = 'Y'));") or die("ERRO_ATUALIZA_13_sgarea");
		d::b()->query("update imgrupo set status = 'INATIVO' where tipoobjetoext = 'sgarea' and idobjetoext in (select idsgarea from sgarea where (status = 'INATIVO' or grupo = 'N'));") or die("ERRO_ATUALIZA_13_sgarea");
		
		//Atualiza o Grupo Departamento (Lidiane 13-03-2020)
		d::b()->query("update imgrupo set status = 'ATIVO' where tipoobjetoext = 'sgdepartamento' and idobjetoext in (select idsgdepartamento from sgdepartamento where (status = 'ATIVO' and grupo = 'Y'));") or die("ERRO_ATUALIZA_13_sgdepartamento");
		d::b()->query("update imgrupo set status = 'INATIVO' where tipoobjetoext = 'sgdepartamento' and idobjetoext in (select idsgdepartamento from sgdepartamento where (status = 'INATIVO' or grupo = 'N'));") or die("ERRO_ATUALIZA_13_sgdepartamento");
		
		d::b()->query("update imgrupo set status = 'ATIVO' where tipoobjetoext = '_lp' and idobjetoext in (select idlp from carbonnovo._lp where (status = 'ATIVO' and grupo = 'Y'));") or die("ERRO_ATUALIZA_13");
		d::b()->query("update imgrupo set status = 'INATIVO' where tipoobjetoext = '_lp' and idobjetoext in (select idlp from carbonnovo._lp where (status = 'INATIVO' or grupo = 'N'));") or die("ERRO_ATUALIZA_13");		
		
		//Listagem de usuários do tipoobjeto (grupo) sgsetor
		$aObjetosExternos["sgsetor"]["sql"] = "
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.setor as grupo
													, a.desc as descr
													, a.idsgsetor as idobjetoext
													, 'sgsetor' as tipoobjetoext
												FROM sgsetor a
													JOIN pessoaobjeto fas on fas.idobjeto=a.idsgsetor AND fas.tipoobjeto = 'sgsetor'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													AND NOT p.status='INATIVO' and a.grupo = 'Y'
												UNION
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.setor as grupo
													, a.desc as descr
													, a.idsgsetor as idobjetoext
													, 'sgsetor' as tipoobjetoext
												FROM sgsetor a
													JOiN objetovinculo o on o.idobjeto = a.idsgsetor and o.tipoobjeto = 'sgsetor'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc AND fas.tipoobjeto = 'sgsetor'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
														AND NOT p.status='INATIVO' and a.grupo = 'Y'
												UNION
												SELECT DISTINCT
													a.idempresa
													, p.idpessoa
													, p.nome
													, a.setor as grupo
													, a.desc as descr
													, a.idsgsetor as idobjetoext
													, 'sgsetor' as tipoobjetoext
												FROM sgsetor a
													JOIN pessoa p on p.idtipopessoa=a.idtipopessoa 
														AND NOT p.status='INATIVO'  and  p.idtipopessoa = 9 and a.grupo = 'Y'
													
												union
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, if (s.idsgsetor, s.setor, a.grupo) as grupo
													, a.descr as descr
													-- , if(s.idsgsetor, s.idsgsetor, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                                                    ,s.idsgsetor as idobjetoext
													-- , if(s.idsgsetor, 'sgsetor', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                                                    ,'sgsetor' as tipoobjetoext  
												FROM imgrupo a
													JOIN sgsetor s on s.idsgsetor = a.idobjetoext and a.tipoobjetoext in ('sgsetor')
													LEFT JOIN carbonnovo._lp l on l.idlp = a.idobjetoext and a.tipoobjetoext in ('_lp')
													JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor' and o.tipoobjetovinc = 'sgsetor'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
														AND NOT p.status='INATIVO' and a.tipoobjetoext = 'sgsetor'
												;";
												
		//Listagem de usuários do tipoobjeto (grupo) Área
		$aObjetosExternos["sgarea"]["sql"] = "SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.area as grupo
													, a.desc as descr
													, a.idsgarea as idobjetoext
													, 'sgarea' as tipoobjetoext
												FROM sgarea a
													JOIN pessoaobjeto fas on fas.idobjeto=a.idsgarea AND fas.tipoobjeto = 'sgarea'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													AND NOT p.status='INATIVO' and a.grupo = 'Y'
												UNION
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.area as grupo
													, a.desc as descr
													, a.idsgarea as idobjetoext
													, 'sgarea' as tipoobjetoext
												FROM sgarea a
													JOiN objetovinculo o on o.idobjeto = a.idsgarea and o.tipoobjeto = 'sgarea'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc AND fas.tipoobjeto = 'sgarea'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													AND NOT p.status='INATIVO' and a.grupo = 'Y'
																									
												union
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, if (s.idsgarea, s.area, a.grupo) as grupo
													, a.descr as descr
													-- , if(s.idsgarea, s.idsgarea, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                                       ,s.idsgarea as idobjetoext
													-- , if(s.idsgarea, 'sgarea', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                                       ,'sgarea' as tipoobjetoext  
												FROM imgrupo a
													JOIN sgarea s on s.idsgarea = a.idobjetoext and a.tipoobjetoext in ('sgarea')
													LEFT JOIN carbonnovo._lp l on l.idlp = a.idobjetoext and a.tipoobjetoext in ('_lp')
													JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgarea' and o.tipoobjetovinc = 'sgarea'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													AND NOT p.status='INATIVO' and a.tipoobjetoext = 'sgarea';";
		
		//Listagem de usuários do tipoobjeto (grupo) Departamento
		$aObjetosExternos["sgdepartamento"]["sql"] = "SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.departamento as grupo
													, a.desc as descr
													, a.idsgdepartamento as idobjetoext
													, 'sgdepartamento' as tipoobjetoext
												FROM sgdepartamento a
													JOIN pessoaobjeto fas on fas.idobjeto=a.idsgdepartamento AND fas.tipoobjeto = 'sgdepartamento'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													AND NOT p.status='INATIVO' and a.grupo = 'Y'
												UNION
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.departamento as grupo
													, a.desc as descr
													, a.idsgdepartamento as idobjetoext
													, 'sgdepartamento' as tipoobjetoext
												FROM sgdepartamento a
													JOiN objetovinculo o on o.idobjeto = a.idsgdepartamento and o.tipoobjeto = 'sgdepartamento'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc AND fas.tipoobjeto = 'sgdepartamento'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													AND NOT p.status='INATIVO' and a.grupo = 'Y'										
												union
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, if (s.idsgdepartamento, s.departamento, a.grupo) as grupo
													, a.descr as descr
													-- , if(s.idsgdepartamento, s.idsgdepartamento, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                                       ,s.idsgdepartamento as idobjetoext
													-- , if(s.idsgdepartamento, 'sgdepartamento', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                                       ,'sgdepartamento' as tipoobjetoext  
												FROM imgrupo a
													JOIN sgdepartamento s on s.idsgdepartamento = a.idobjetoext and a.tipoobjetoext in ('sgdepartamento')
													LEFT JOIN carbonnovo._lp l on l.idlp = a.idobjetoext and a.tipoobjetoext in ('_lp')
													JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgdepartamento' and o.tipoobjetovinc = 'sgdepartamento'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													AND NOT p.status='INATIVO' and a.tipoobjetoext = 'sgdepartamento';";
												
		$aObjetosExternos["_lp"]["sql"] = "
											SELECT DISTINCT 
													l.idempresa
													, p.idpessoa
													, p.nomecurto
													, l.descricao as grupo
													, '' as descr
													, l.idlp as idobjetoext
													, '_lp' as tipoobjetoext
												FROM 
													carbonnovo._lp l
												 join lpobjeto lo on lo.idlp = l.idlp    
													JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and fas.tipoobjeto = 'sgarea' and lo.tipoobjeto = 'sgarea'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
														AND NOT p.status='INATIVO' AND l.grupo = 'Y'														
												UNION														
												SELECT DISTINCT 
													l.idempresa
													, p.idpessoa
													, p.nomecurto
													, l.descricao as grupo
													, '' as descr
													, l.idlp as idobjetoext
													, '_lp' as tipoobjetoext
												FROM 
													carbonnovo._lp l
												 join lpobjeto lo on lo.idlp = l.idlp    
													JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and fas.tipoobjeto = 'sgdepartamento' and lo.tipoobjeto = 'sgdepartamento'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
														AND NOT p.status='INATIVO' AND l.grupo = 'Y'
														
														UNION
												SELECT DISTINCT 
													l.idempresa
													, p.idpessoa
													, p.nomecurto
													, l.descricao as grupo
													, '' as descr
													, l.idlp as idobjetoext
													, '_lp' as tipoobjetoext
												FROM 
													carbonnovo._lp l
												 join lpobjeto lo on lo.idlp = l.idlp    
													JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and fas.tipoobjeto = 'sgsetor' and lo.tipoobjeto = 'sgsetor'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
														AND NOT p.status='INATIVO' AND l.grupo = 'Y'
												UNION
												SELECT DISTINCT 
													l.idempresa
													, p.idpessoa
													, p.nomecurto
													, l.descricao as grupo
													, '' as descr
													, l.idlp as idobjetoext
													, '_lp' as tipoobjetoext
												FROM 
													carbonnovo._lp l
												 join lpobjeto lo on lo.idlp = l.idlp 
												 JOIN objetovinculo o on o.idobjeto = lo.idobjeto and lo.tipoobjeto = 'sgsetor' and o.tipoobjeto = 'sgsetor' 
												 JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor' and lo.tipoobjeto = 'sgsetor'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
														AND NOT p.status='INATIVO' AND l.grupo = 'Y'
												UNION
												SELECT DISTINCT 
													l.idempresa
													, p.idpessoa
													, p.nomecurto
													, l.descricao as grupo
													, '' as descr
													, l.idlp as idobjetoext
													, '_lp' as tipoobjetoext
												FROM 
													carbonnovo._lp l
												 join lpobjeto lo on lo.idlp = l.idlp    
													
													JOIN pessoa p on p.idpessoa = lo.idobjeto and lo.tipoobjeto = 'pessoa'
														AND NOT p.status='INATIVO'  AND l.grupo = 'Y' 
												UNION
											   SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, l.descricao as grupo
													, a.descr as descr
													-- , if(s.idsgsetor, s.idsgsetor, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                                                    ,l.idlp as idobjetoext
													-- , if(s.idsgsetor, 'sgsetor', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                                                    ,'_lp' as tipoobjetoext  
                                                    
												FROM imgrupo a
													JOIN carbonnovo._lp l on l.idlp = a.idobjetoext and a.tipoobjetoext in ('_lp')
													JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor' and o.tipoobjetovinc = 'sgsetor'
                                                    
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
														AND NOT p.status='INATIVO' and a.tipoobjetoext = '_lp' 
												;";
												
		$aObjetosExternos["manual"]["sql"] = "					
	
                                                SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.grupo as grupo
													, a.descr as descr
													-- , if(s.idsgsetor, s.idsgsetor, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                                                    ,a.idimgrupo as idobjetoext
													-- , if(s.idsgsetor, 'sgsetor', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                                                    ,'manual' as tipoobjetoext                                                      
												FROM imgrupo a									
													JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor' and o.tipoobjetovinc = 'sgsetor'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													 AND NOT p.status='INATIVO'
                                                     where a.tipoobjetoext = 'manual'
												UNION
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.grupo as grupo
													, a.descr as descr
													-- , if(s.idsgarea, s.idsgarea, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                                                    ,a.idimgrupo as idobjetoext
													-- , if(s.idsgarea, 'sgarea', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                                                    ,'manual' as tipoobjetoext                                                      
												FROM imgrupo a									
													JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgarea' and o.tipoobjetovinc = 'sgarea'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													 AND NOT p.status='INATIVO'
                                                     where a.tipoobjetoext = 'manual'
												UNION
												SELECT DISTINCT 
													a.idempresa
													, p.idpessoa
													, p.nomecurto
													, a.grupo as grupo
													, a.descr as descr
													-- , if(s.idsgdepartamento, s.idsgdepartamento, if(l.idlp, l.idlp, a.idimgrupo))  as idobjetoext
                                                    ,a.idimgrupo as idobjetoext
													-- , if(s.idsgdepartamento, 'sgdepartamento', if(l.idlp, '_lp', 'imgrupo'))   as tipoobjetoext  
                                                    ,'manual' as tipoobjetoext                                                      
												FROM imgrupo a									
													JOiN objetovinculo o on o.idobjeto = a.idimgrupo and o.tipoobjeto = 'imgrupo'
													JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgdepartamento' and o.tipoobjetovinc = 'sgdepartamento'
													JOIN pessoa p on p.idpessoa=fas.idpessoa 
													 AND NOT p.status='INATIVO'
                                                     where a.tipoobjetoext = 'manual'";


		//Listagem de usuários do tipoobjeto (grupo) sgsetor
		$aObjetosExternos["tipopessoa"]["sql"] = "select 
													p.idempresa
													, p.idpessoa
													, tp.tipopessoa as grupo
													, '' as descr
													, p.idtipopessoa  as idobjetoext
													, 'tipopessoa' as tipoobjetoext
												from pessoa p join tipopessoa tp on tp.idtipopessoa=p.idtipopessoa
												where 
												NOT p.status='INATIVO'
												and p.idtipopessoa in (1,9)
												and p.senha > ''";

		$aGrupos=array();
		$aUsr=array();
		//A consulta de cada grupo externo deve trazer junto o usuário e os grupos ao qual ele pertence, e serão separados aqui em arrays de grupos e usuarios
		foreach ($aObjetosExternos as $objext=>$val) {
				//echo $val["sql"];
			$roe=d::b()->query($val["sql"]) or die("ERRO_ATUALIZA_20_".$objext);
			while($r = mysqli_fetch_assoc($roe)){
				//Monta os grupos
				//Concatenado o ID empresa para separar os gupos por empresa. (Lidiane -26-03-2020)
				$aGrupos[$objext.$r["idempresa"]][$r["idobjetoext"]]["tipoobjetoext"]=$r["tipoobjetoext"];
				$aGrupos[$objext.$r["idempresa"]][$r["idobjetoext"]]["descr"]=mysqli_escape_string(d::b(),$r["descr"]);
				$aGrupos[$objext.$r["idempresa"]][$r["idobjetoext"]]["grupo"]=mysqli_escape_string(d::b(),$r["grupo"]);
				$aGrupos[$objext.$r["idempresa"]][$r["idobjetoext"]]["idempresa"]=$r["idempresa"];
				
				//Separa os usuários dentro dos grupos
				$aUsr[$objext][$r["idobjetoext"]][$r["idpessoa"]]["idempresa"]=$r["idempresa"];
			}
		}
				
		foreach ($aGrupos as $oExt=>$grupo){
			//Inativa os grupos do Objeto Externo temporariamente. Os que não forem atualizados serão excluídos no final
			$sUpd1="update ".$this->tabimgrupo." set status='INATIVAR' where inseridomanualmente='N' and not status = 'INATIVO' and tipoobjetoext='".$oExt."'";
			if($this->debugsql)echo "\n\n".$sUpd1;
			d::b()->query($sUpd1) or die("ERRO_ATUALIZA_30");

			//Para cada "grupo" do obj externo, verifica se já existe. Caso negativo: insere. Update para ATIVO caso exista. Caso não exista: permanecerá com status INATIVAR.
			foreach ($grupo as $k=>$v){
				$sia = "INSERT INTO imgrupo (idempresa,grupo,idobjetoext,tipoobjetoext,descr,status, criadopor, criadoem, alteradopor, alteradoem)
				SELECT * FROM (SELECT ".$v["idempresa"]." as ide,'".$v["grupo"]."' as gr,'".$k."' as idex,'".$v["tipoobjetoext"]."' as te,'".$v["descr"]."' as de,'ATIVO' as st, null as cr, now() as ce, null as ar, now() as ae) AS tmp
				WHERE NOT EXISTS (
					SELECT 1 FROM imgrupo WHERE idempresa=".$v["idempresa"]." and idobjetoext = '".$k."' and tipoobjetoext='".$v["tipoobjetoext"]."'
				)";
				d::b()->query($sia) or die("ERRO_ATUALIZA_40_".$k."\n\n". mysqli_error(d::b())."\n\n".$sia);
				
				d::b()->query("update ".$this->tabimgrupo." set status='ATIVAR' where idempresa=".$v["idempresa"]."  and not status = 'INATIVO' and idobjetoext = '".$k."' and tipoobjetoext='".$v["tipoobjetoext"]."'")or die("ERRO_ATUALIZA_50".$k);
			}
		}
		
		
		 
		//Exclui os usuarios dos grupos com status INATIVAR e ATIVAR
		d::b()->query("delete from imgrupopessoa where exists(select 1 from imgrupo g where g.status IN ('ATIVAR','INATIVAR') and g.idimgrupo=imgrupopessoa.idimgrupo) and inseridomanualmente = 'N'")or die("ERRO_ATUALIZA_60".$k);
		//Inclui os usuário snos respectivos grupos
		foreach ($aUsr as $oExt=>$obj){//objetoexterno
			foreach ($obj as $idobjetoexterno=>$pessoas){//idpbjetoexterno 
				foreach ($pessoas as $idpessoa=>$v){//pessoa
					$si="replace into imgrupopessoa (idempresa,idimgrupo,idpessoa) 
					select ".$v["idempresa"].", idimgrupo, ".$idpessoa."
					from imgrupo g where g.status IN ('ATIVAR','INATIVAR') 
					and g.idempresa = ".$v["idempresa"]."
					and g.tipoobjetoext='".$oExt."' and g.idobjetoext=".$idobjetoexterno;
					//if($this->debugsql)echo "\n\n".$si;
					d::b()->query($si) or die("ERRO_ATUALIZA_70_".$k);
					
				}
			}
		}

		//Reativa os usuários que vieram nas consultas
		foreach ($aUsr as $oExt=>$obj){//objetoexterno
			foreach ($obj as $idobjetoexterno=>$pessoas){//idpbjetoexterno
				foreach ($pessoas as $idpessoa=>$v){//pessoa
				
					$sip = "INSERT INTO impessoa (idempresa,idpessoa,status)
					SELECT distinct * FROM (SELECT ".$v["idempresa"]." ide,".$idpessoa." idp,'ATIVO' st) AS tmp
					WHERE NOT EXISTS (
						SELECT 1 FROM impessoa WHERE  idpessoa = ".$idpessoa."
					)";
					d::b()->query($sip) or die("ERRO_ATUALIZA_71_".$k."\n\n". mysqli_error(d::b())."\n\n".$sip);
					d::b()->query("update ".$this->tabimpessoa." set status='ATIVAR' where inseridomanualmente='N' and idpessoa=".$idpessoa) or die("ERRO_ATUALIZA_80");
				}
			}
		}
		 
		//Exclui grupos e pessoas que não vieram nas consultas
		d::b()->query("delete from ".$this->tabimpessoa." where status='INATIVAR' AND inseridomanualmente='N'") or die("ERRO_ATUALIZA_90");
		d::b()->query("delete from ".$this->tabimgrupo." where status='INATIVAR' AND inseridomanualmente='N'") or die("ERRO_ATUALIZA_100");

		//Ativa grupos e pessoas novas/alteradas
		d::b()->query("update ".$this->tabimpessoa." set status='ATIVO' where status='ATIVAR' AND inseridomanualmente='N'") or die("ERRO_ATUALIZA_110");
		d::b()->query("update ".$this->tabimgrupo." set status='ATIVO' where status='ATIVAR'") or die("ERRO_ATUALIZA_120");


		//Final das contas
/*
INSERT INTO IMREGRA

delete from imregra where tiporegra='GRUPO';
insert into imregra (idempresa,tipoobjetoorigem,idobjetoorigem,tipoobjetodestino,idobjetodestino,tiporegra)
SELECT 1, 'imgrupo', 39, 'imgrupo',idobjetoext,'GRUPO' FROM imgrupo;

select * from imregra
*/
		// ADICIONA OS NOVOS GRUPOS (CADASTRADOS ACIMA ATRAVÉS DA SGSETOR) NA TABELA DE REGRAS DO CHAT. 39 É O GRUPO DE FUNCIONÁRIOS
		//Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
		d::b()->query("insert into imregra (SELECT 
												NULL,
												sgsetor.idempresa,
												'imgrupo',
												ge.idimgrupo,
												'imgrupo',
												imgrupo.idimgrupo,
												'GRUPO',
												'ATIVO'
											FROM
												imgrupo
													JOIN
												sgsetor ON imgrupo.idobjetoext = sgsetor.idsgsetor
												join imgrupo ge on ge.idempresa = sgsetor.idempresa and ge.tipoobjetoext = 'tipopessoa' and ge.idobjetoext = 1
											WHERE
												imgrupo.tipoobjetoext = 'sgsetor'
													AND imgrupo.status = 'ativo'
													AND ISNULL(NULLIF(idtipopessoa, ''))
													AND NOT exists (SELECT 
														1
													FROM
														imregra
													WHERE
														tiporegra = 'GRUPO'
															AND idobjetoorigem =  ge.idimgrupo
															and idobjetodestino = imgrupo.idimgrupo));") or die("ERRO_ATUALIZA_130_SETOR");
		
		// ADICIONA OS NOVOS GRUPOS (CADASTRADOS ACIMA ATRAVÉS DA SGAREA) NA TABELA DE REGRAS DO CHAT. 39 É O GRUPO DE FUNCIONÁRIOS
		//Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
		d::b()->query("insert into imregra (SELECT 
							NULL,
							sgarea.idempresa,
							'imgrupo',
							ge.idimgrupo,
							'imgrupo',
							imgrupo.idimgrupo,
							'GRUPO',
							'ATIVO'
						FROM
							imgrupo
								JOIN
							sgarea ON imgrupo.idobjetoext = sgarea.idsgarea
							  join imgrupo ge on ge.idempresa = sgarea.idempresa and ge.tipoobjetoext = 'tipopessoa' and ge.idobjetoext = 1
						WHERE
							imgrupo.tipoobjetoext = 'sgarea'
								AND imgrupo.status = 'ativo'
								and not exists (SELECT 
									1
								FROM
									imregra
								WHERE
									tiporegra = 'GRUPO'
										AND idobjetoorigem =  ge.idimgrupo
										and idobjetodestino = imgrupo.idimgrupo));") or die("ERRO_ATUALIZA_130_AREA");
		
		// ADICIONA OS NOVOS GRUPOS (CADASTRADOS ACIMA ATRAVÉS DA SGDEPARTAMENTO) NA TABELA DE REGRAS DO CHAT. 39 É O GRUPO DE FUNCIONÁRIOS
		//Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016)
		d::b()->query("insert into imregra (SELECT 
							NULL,
							sgdepartamento.idempresa,
							'imgrupo',
							ge.idimgrupo,
							'imgrupo',
							imgrupo.idimgrupo,
							'GRUPO',
							'ATIVO'
						FROM
							imgrupo
								JOIN
							sgdepartamento ON imgrupo.idobjetoext = sgdepartamento.idsgarea
							  join imgrupo ge on ge.idempresa = sgdepartamento.idempresa and ge.tipoobjetoext = 'tipopessoa' and ge.idobjetoext = 1
						WHERE
							imgrupo.tipoobjetoext = 'sgdepartamento'
								AND imgrupo.status = 'ativo'
								and not exists (SELECT 
									1
								FROM
									imregra
								WHERE
									tiporegra = 'GRUPO'
										AND idobjetoorigem =  ge.idimgrupo
										and idobjetodestino = imgrupo.idimgrupo)
										);") or die("ERRO_ATUALIZA_130_DEPARTAMENTO");
		
		d::b()->query("insert into imregra ( SELECT 
							NULL,
							imgrupo.idempresa,
							'imgrupo',
							ge.idimgrupo,
							'imgrupo',
							imgrupo.idimgrupo,
							'GRUPO',
							'ATIVO'
						FROM
							imgrupo
								JOIN
							carbonnovo._lp l ON imgrupo.idobjetoext = l.idlp
							   join imgrupo ge on ge.idempresa = l.idempresa and ge.tipoobjetoext = 'tipopessoa' and ge.idobjetoext = 1
						WHERE
							imgrupo.tipoobjetoext = '_lp'
								AND imgrupo.status = 'ativo'
								AND ISNULL(NULLIF(idtipopessoa, ''))
							   AND NOT exists (SELECT 
									1
								FROM
									imregra
								WHERE
									tiporegra = 'GRUPO'
										AND idobjetoorigem =  ge.idimgrupo
										and idobjetodestino = imgrupo.idimgrupo));") or die("ERRO_ATUALIZA_130_LP");
		d::b()->query("insert into imregra (SELECT 
								NULL,
								imgrupo.idempresa,
								'imgrupo',
								ge.idimgrupo,
								'imgrupo',
							   imgrupo.idimgrupo,
								'GRUPO',
								'ATIVO'
							FROM
								imgrupo
								  join imgrupo ge on ge.idempresa = imgrupo.idempresa and ge.tipoobjetoext = 'tipopessoa' and ge.idobjetoext = 1
							WHERE
								imgrupo.tipoobjetoext = 'manual'
									AND imgrupo.status = 'ativo'
									AND NOT exists (SELECT 
										1
									FROM
										imregra
									WHERE
										tiporegra = 'GRUPO'
											AND idobjetoorigem =  ge.idimgrupo
											and idobjetodestino = imgrupo.idimgrupo));") or die("ERRO_ATUALIZA_130_MANUAL");
		   
	
		////	: Cria clone de grupos para que as pessoas possam ter conversas privadas com cada grupo, mesmo não pertencendo ao grupo.
		///Somente visualiza o grupo se estiver configurado na regra (tela sgs/tor)
			//Comentado pois não está funcionando mais, a pedido do Marcelo (08/07/2020)
			/*$sql="	SELECT  		 		
						-- go.grupo,
						gp.idpessoa, 
                         gc.grupo,
						gc.idimgrupo,
						p.nomecurto,
						r.idimregra
					FROM 
						imregra r
					JOIN 
						imgrupo go ON go.idimgrupo=r.idobjetoorigem and not go.tipoobjetoext = 'clone' -- GRUPOS RELACIONADOS
					JOIN 
						imgrupopessoa gp on gp.idimgrupo=r.idobjetoorigem  and not gp.idimgrupo = 39 -- TODAS AS PESSOAS
					JOIN 
						pessoa p on p.idpessoa=gp.idpessoa -- TODAS AS PESSOAS	
					JOIN 
						imgrupo gc on gc.idimgrupo=r.idobjetodestino and not gc.idimgrupo = 39  -- GRUPOS RELACIONADOS
					WHERE 
						r.tiporegra='GRUPO' and r.tipoobjetoorigem='imgrupo' and r.tipoobjetodestino = 'imgrupo' and r.status = 'ATIVO'
						 and not  r.idobjetoorigem = r.idobjetodestino
						AND NOT EXISTS (select 1 from imgrupo gclone 
						 	join imgrupopessoa gpclone on gpclone.idimgrupo = gclone.idimgrupo 
							where gclone.tipoobjetoext = 'clone' and gpclone.idpessoa = gp.idpessoa and 
							gclone.idobjetoext = r.idimregra);";
			//echo '<pre>'.$sql.'</pre>';
            $res = d::b()->query($sql) or die("A Consulta na immsgconf falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

            while ($row=mysqli_fetch_assoc($res)) {
				//$this->clonargrupo($row['idimgrupo'],$row['idpessoa'],$row['nomecurto'], $row['idimregra'], $row['grupo']);
				//echo 'oi';
			 }
			 
		 	d::b()->query(" UPDATE imgrupo SET status = 'INATIVO' where tipoobjetoext = 'clone' and not exists (select 1 from imregra where idobjetoext = idimregra and imregra.status = 'ATIVO');") or die("ERRO_ATUALIZA_140");
			*/
			
			//DELETA TODAS AS PESSOAS DO "GRUPO" QUE ESTÃO INATIVAS
			 $sm0x1 = "delete from imgrupopessoa where idpessoa in (select * from (
				select idpessoa from pessoa p where p.status = 'INATIVO' and p.idtipopessoa = 1)a);";
				
			//DELETA TODAS AS PESSOAS DO "EVENTO" QUE ESTÃO INATIVAS
			 $sm0x2 = "delete from eventoresp where ideventoresp in ( select * from (select ideventoresp from eventoresp r join evento e on e.idevento = r.idevento 
              where e.status is null and r.tipoobjeto = 'pessoa' and r.idobjeto in  (
				select idpessoa from pessoa p where p.status = 'INATIVO' and p.idtipopessoa = 1) )a );";
				
			//DELETA TODAS AS PESSOAS DO "ALERTA" QUE ESTÃO INATIVAS
			 $sm0x3 = "delete from immsgconfdest where objeto = 'pessoa' and idobjeto in (select * from (
				select idpessoa from pessoa p where p.status = 'INATIVO' and p.idtipopessoa = 1)a);";
				
				
			//DELETA TODAS AS PESSOAS DO "GRUPO" QUE NÃO FAZEM MAIS PARTE DO SETOR E DO SETOR VINCULADO.
			 $sm1 = "delete from imgrupopessoa where idimgrupopessoa in (select * from (
				select gp.idimgrupopessoa from imgrupopessoa gp 
				join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = 'sgsetor'
				where gp.inseridomanualmente = 'N' and
				not exists(select 1 from pessoaobjeto ps where ps.idpessoa = gp.idpessoa and ps.idobjeto = g.idobjetoext and ps.tipoobjeto = 'sgsetor') and 
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor'
							where o.idobjeto = g.idobjetoext and o.tipoobjeto = 'sgsetor')
				)a);";
				
			//DELETA TODAS AS PESSOAS DO "GRUPO" QUE NÃO FAZEM MAIS PARTE DA AREA E DA AREA VINCULADA. (Lidiane - 17-03-2020)
			$sm1area = "delete from imgrupopessoa where idimgrupopessoa in (select * from (
				select gp.idimgrupopessoa from imgrupopessoa gp 
				join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = 'sgarea'
				where gp.inseridomanualmente = 'N' and
				not exists(select 1 from pessoaobjeto ps where ps.idpessoa = gp.idpessoa and ps.idobjeto = g.idobjetoext and ps.tipoobjeto = 'sgarea') and 
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgarea'
							where o.idobjeto = g.idobjetoext and o.tipoobjeto = 'sgarea')
				)a);";
			
			//DELETA TODAS AS PESSOAS DO "GRUPO" QUE NÃO FAZEM MAIS PARTE DEPARTAMENTO E DEPARTAMENTO VINCULADO. (Lidiane - 17-03-2020)
			$sm1dpto = "delete from imgrupopessoa where idimgrupopessoa in (select * from (
				select gp.idimgrupopessoa from imgrupopessoa gp 
				join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = 'sgdepartamento'
				where gp.inseridomanualmente = 'N' and
				not exists(select 1 from pessoaobjeto ps where ps.idpessoa = gp.idpessoa and ps.idobjeto = g.idobjetoext and ps.tipoobjeto = 'sgdepartamento') and 
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgdepartamento'
							where o.idobjeto = g.idobjetoext and o.tipoobjeto = 'sgdepartamento')
				)a);";
			
			//DELETA TODAS AS PESSOAS DO "GRUPO" QUE NÃO FAZEM MAIS PARTE DA LP
			$sm2 = "delete from imgrupopessoa where idimgrupopessoa in (select * from (
				select gp.idimgrupopessoa from imgrupopessoa gp 
				join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = '_lp'
				where gp.inseridomanualmente = 'N' and
				not exists(select 1 from lpobjeto lo where lo.idobjeto = gp.idpessoa and lo.idlp = g.idobjetoext and lo.tipoobjeto = 'pessoa' )
				and
				not exists(select 1 from lpobjeto lo JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and (fas.tipoobjeto = 'sgsetor' or fas.tipoobjeto = 'sgarea'  or fas.tipoobjeto = 'sgdepartamento') and (lo.tipoobjeto = 'sgsetor' or lo.tipoobjeto = 'sgarea'  or lo.tipoobjeto = 'sgdepartamento') where gp.idpessoa=fas.idpessoa)
				)a);";
			 
				//DELETA TODAS AS PESSOAS DO "GRUPO" CUJO SETORES NÃO FAZEM MAIS PARTE DA LP
				//Alterado para não apagar as pessoas que podem estar no area ou departamento - Lidiane (12/06/2020). 
				//Por exemplo, o Paulo estava na area Comercial Bovino, o mesmo inserido no grupo, apagava ele nesta parte e seu nome ficava fora e aceitava somente sgsetor.
			 $sm3 = "delete from imgrupopessoa where idimgrupopessoa in (select * from (
				select gp.idimgrupopessoa from imgrupopessoa gp 
				join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = '_lp'
				where gp.inseridomanualmente = 'N' and
				not exists(select 1 from lpobjeto lo JOIN pessoaobjeto fas on fas.idobjeto=lo.idobjeto and (fas.tipoobjeto = 'sgsetor' or fas.tipoobjeto = 'sgarea'  or fas.tipoobjeto = 'sgdepartamento') and (lo.tipoobjeto = 'sgsetor' or lo.tipoobjeto = 'sgarea'  or lo.tipoobjeto = 'sgdepartamento') where gp.idpessoa=fas.idpessoa)
				)a);";
				
			//DELETA TODAS AS PESSOAS DO "GRUPO" MANUAL QUE NÃO FAZEM MAIS PARTE DO SETOR E DO SETOR VINCULADO. 			
			 $sm4 = "delete from imgrupopessoa where idimgrupopessoa in (select * from (
				select gp.idimgrupopessoa from imgrupopessoa gp 
				join imgrupo g on gp.idimgrupo = g.idimgrupo and g.tipoobjetoext = 'manual'
				where gp.inseridomanualmente = 'N' and g.status = 'ATIVO' and
				not exists(select 1 from pessoaobjeto ps where ps.idpessoa = gp.idpessoa and ps.idobjeto = g.idobjetoext and ps.tipoobjeto = 'sgsetor') and 
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor'
							where o.idobjeto = g.idobjetoext and o.tipoobjeto = 'sgsetor') and
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgsetor'
							where o.idobjeto = g.idimgrupo and o.tipoobjeto = 'imgrupo')
				and
				not exists(select 1 from pessoaobjeto ps where ps.idpessoa = gp.idpessoa and ps.idobjeto = g.idobjetoext and ps.tipoobjeto = 'sgarea') and 
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgarea'
							where o.idobjeto = g.idobjetoext and o.tipoobjeto = 'sgarea') and
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgarea'
							where o.idobjeto = g.idimgrupo and o.tipoobjeto = 'imgrupo') and
				not exists(select 1 from pessoaobjeto ps where ps.idpessoa = gp.idpessoa and ps.idobjeto = g.idobjetoext and ps.tipoobjeto = 'sgdepartamento') and 
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgdepartamento'
							where o.idobjeto = g.idobjetoext and o.tipoobjeto = 'sgdepartamento') and
				not exists(select 1 from objetovinculo o JOIN pessoaobjeto fas on fas.idobjeto=o.idobjetovinc and fas.tipoobjeto = 'sgdepartamento'
							where o.idobjeto = g.idimgrupo and o.tipoobjeto = 'imgrupo')
				)a);";
			
						
			d::b()->query($sm0x1) or die("Erro ao deletar pessoas do grupo sm0x1: ".mysqli_error(d::b()));
			d::b()->query($sm0x2) or die("Erro ao deletar pessoas do evento sm0x2: ".mysqli_error(d::b()));
			d::b()->query($sm0x3) or die("Erro ao deletar pessoas do alerta sm0x3: ".mysqli_error(d::b()));
			
			d::b()->query($sm1) or die("Erro ao deletar pessoas do grupo sm1: ".mysqli_error(d::b()));
			d::b()->query($sm1area) or die("Erro ao deletar pessoas do grupo sm1area: ".mysqli_error(d::b()));
			d::b()->query($sm1dpto) or die("Erro ao deletar pessoas do grupo sm1dpto: ".mysqli_error(d::b()));
			
			d::b()->query($sm2) or die("Erro ao deletar pessoas do grupo sm2: ".mysqli_error(d::b()));
			
			d::b()->query($sm3) or die("Erro ao deletar pessoas do grupo sm3: ".mysqli_error(d::b()));	
			
			d::b()->query($sm4) or die("Erro ao deletar pessoas do grupo sm4: ".mysqli_error(d::b()));		
					
				/*//DELETA TODAS AS PESSOAS DO "GRUPO GRUPO" QUE NÃO FAZEM MAIS PARTE DO SETOR.
				$sm = "DELETE FROM imgrupopessoa WHERE idimgrupopessoa IN (SELECT * FROM (
				SELECT gp.idimgrupopessoa FROM imgrupopessoa gp 
				JOIN imgrupo g ON gp.idimgrupo = g.idimgrupo
				JOIN imregra r ON g.idobjetoext = r.idimregra
				JOIN imgrupo GO ON GO.idimgrupo = r.idobjetoorigem
				JOIN sgsetor s ON s.idsgsetor = GO.idobjetoext
				JOIN imgrupo gd ON gd.idimgrupo = r.idobjetodestino
				JOIN sgsetor sd ON sd.idsgsetor =gd.idobjetoext
				AND NOT EXISTS(SELECT 1 FROM pessoaobjeto ps join pessoa p on p.idpessoa = ps.idpessoa and p.status in ('ATIVO', 'PENDENTE') and ps.tipoobjeto = 'sgsetor' WHERE ps.idpessoa = gp.idpessoa AND (ps.idobjeto =  s.idsgsetor OR ps.idobjeto =  sd.idsgsetor) and ps.tipoobjeto = 'sgsetor')
				WHERE gp.inseridomanualmente = 'N' and g.tipoobjetoext = 'grupogrupo')a);";

				d::b()->query($sm) or die("Erro ao deletar pessoas do grupo grupo: ".mysqli_error(d::b()));	
			
			////	: Cria clone de grupos para que as pessoas possam ter conversas coletivas entre 2 grupos

		   $sql = "	SELECT r.idimregra, concat(GO.grupo, ' + ', gd.grupo) AS grupo
					FROM imregra r
					JOIN imgrupo GO ON GO.idimgrupo = r.idobjetoorigem
					JOIN imgrupo gd ON gd.idimgrupo = r.idobjetodestino
					WHERE
						r.tiporegra = 'grupogrupo'";

            $res=d::b()->query($sql) or die("A Consulta na immsgconf falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
			//echo '<pre>'.$sql.'</pre>';
			//die();
            while ($row=mysqli_fetch_assoc($res)) {
				$this->grupogrupo($row['idimregra'],$row['grupo']);
				//echo 'oi';
			 }
			 
		 	d::b()->query(" UPDATE imgrupo SET status = 'INATIVO' where tipoobjetoext = 'clone' and not exists (select 1 from imregra where idobjetoext = idimregra and imregra.status = 'ATIVO');") or die("ERRO_ATUALIZA_140");
			*/
		//Acrescetado no union os dois ultimos para buscar as pessoas que podem acessar a outra empresa. (LTM - 03-08-2020 - 363640)
		d::b()->query("update imcontato set status='I' where inseridomanualmente='N';") or die("ERRO_ATUALIZA_140");

		d::b()->query("replace into imcontato (idempresa, idimregra, idpessoa, idcontato, objetocontato, inseridomanualmente,criadoem,status, ultimamsg)
		select * from (
SELECT distinct   r.idempresa, r.idimregra, gp.idpessoa, gc.idpessoa as idcontato, 'pessoa' as objetocontato, 'N', now(), 'A' as status, 
(SELECT ultimamsg from imcontato where idpessoa = gp.idpessoa and idcontato = gc.idpessoa and objetocontato = 'pessoa' AND gc.idempresa = idempresa) as ultimamsg
		FROM imregra r
			JOIN imgrupopessoa gp on gp.idimgrupo=r.idobjetoorigem -- TODAS AS PESSOAS
		    JOIN imgrupopessoa gc on gc.idimgrupo=r.idobjetodestino -- TODAS AS PESSOAS DO GRUPO RELACIONADO
		where r.status = 'ATIVO' and r.tiporegra='MENSAGEMDIRETA' and r.tipoobjetoorigem='imgrupo' and r.tipoobjetodestino = 'imgrupo' 
union
SELECT distinct r.idempresa, r.idimregra, gp.idpessoa, gc.idpessoa as idcontato, 'pessoa' as objetocontato, 'N', now(), 'A' as status, 
(SELECT ultimamsg from imcontato where idpessoa = gp.idpessoa and idcontato = gc.idpessoa and objetocontato = 'pessoa' AND gc.idempresa = idempresa) as ultimamsg
		FROM imregra r
			JOIN imgrupopessoa gp on gp.idimgrupo=r.idobjetodestino -- TODAS AS PESSOAS
		    JOIN imgrupopessoa gc on gc.idimgrupo=r.idobjetoorigem -- TODAS AS PESSOAS DO GRUPO RELACIONADO
		where r.status = 'ATIVO' and r.tiporegra='MENSAGEMDIRETA' and r.tipoobjetoorigem='imgrupo' and r.tipoobjetodestino = 'imgrupo'
union 
SELECT DISTINCT oe.empresa, im.idimgrupo, oe.idobjeto, im.idpessoa,  'pessoa' as objetocontato, 'N', now(), 'A' as STATUS, 
(SELECT ultimamsg from imcontato where idpessoa = im.idpessoa and idcontato = im.idpessoa and objetocontato = 'pessoa' AND oe.idempresa = idempresa) as ultimamsg
FROM objempresa oe JOIN imgrupopessoa im ON oe.empresa = im.idempresa
AND oe.idempresa <> im.idempresa AND oe.objeto = 'pessoa'
union 
SELECT DISTINCT oe.empresa, im.idimgrupo,  im.idpessoa, oe.idobjeto, 'pessoa' as objetocontato, 'N', now(), 'A' as STATUS, 
(SELECT ultimamsg from imcontato where idpessoa = im.idpessoa and idcontato = im.idpessoa and objetocontato = 'pessoa' AND oe.idempresa = idempresa) as ultimamsg
FROM objempresa oe JOIN imgrupopessoa im ON oe.empresa = im.idempresa
AND oe.idempresa <> im.idempresa AND oe.objeto = 'pessoa'
)
       a group by idpessoa, idcontato") or die("ERRO_ATUALIZA_150");
		
		//Inserido para aparecer as pessoas do laudo nas outras emrpesas. (LTM - 363640)
		/*
		Retirado pois estava agrupando as pessoas das empresas e com isso não aparecia a pessoa da empresa 1 para 6 por exemplo (LTM - 12-08-2020 - 367247)
		d::b()->query("replace into imcontato (idempresa, idimregra, idpessoa, idcontato, objetocontato, inseridomanualmente,criadoem,status, ultimamsg)
		SELECT DISTINCT oe.idempresa, im.idimgrupo, oe.idobjeto, im.idpessoa, 'pessoa' as objetocontato, 'N', now(), 'A' as STATUS, 
		(SELECT ultimamsg from imcontato where idpessoa = im.idpessoa and idcontato = im.idpessoa and objetocontato = 'pessoa' AND oe.idempresa = idempresa) as ultimamsg
		FROM objempresa oe JOIN imgrupopessoa im ON oe.empresa = im.idempresa
		AND oe.idempresa <> im.idempresa AND oe.objeto = 'pessoa'") or die("Erro insere imcontato Empresas	");*/

		//GRUPO: Todas as pessoas do Grupo de origem podem ter como contato destino Todos os grupos de destino
		d::b()->query("replace into imcontato (idempresa, idimregra, idpessoa, idcontato, objetocontato, inseridomanualmente,criadoem,status, ultimamsg)
		SELECT r.idempresa, r.idimregra, gp.idpessoa, gc.idimgrupo as idcontato, 'imgrupo' as objetocontato, 'N', now(),if(r.status = 'INATIVO','I', 'A'),
		(SELECT ultimamsg from imcontato where idpessoa = gp.idpessoa and idcontato = gc.idimgrupo and objetocontato = 'imgrupo' AND gc.idempresa = idempresa) as ultimamsg
		FROM imregra r
			JOIN imgrupopessoa gp on gp.idimgrupo=r.idobjetoorigem -- TODAS AS PESSOAS
		    JOIN imgrupo gc on gc.idimgrupo=r.idobjetodestino -- GRUPOS RELACIONADOS
		where r.status = 'ATIVO' and r.tiporegra='GRUPO' and r.tipoobjetoorigem='imgrupo' and r.tipoobjetodestino = 'imgrupo'") or die("ERRO_ATUALIZA_160");
		
		

		//DELETA TODAS AS PESSOAS QUE NÃO PERTENCEM MAIS AOS GRUPOS DENTRO DOS EVENTOS
		//LTM - 09-10-2020: Alterado para deletar apenas quando estiver no status FIM
		d::b()->query("DELETE r FROM eventoresp r
						JOIN evento e on r.idevento = e.idevento
                        JOIN eventotipostatus es ON e.ideventotipo = es.ideventotipo AND e.ideventostatus = es.ideventostatus AND (es.posicao is null OR es.posicao = 'INICIO') 
					   WHERE r.tipoobjeto = 'pessoa' 
						 AND tipoobjetoext = 'imgrupo' 
						 AND NOT EXISTS(SELECT 1 FROM imgrupopessoa g WHERE g.idimgrupo = r.idobjetoext AND g.idpessoa = r.idobjeto);");

		//INSERE TODAS AS PESSOAS QUE FORAM ADICIONADAS RECENTEMENTE DENTRO DOS EVENTOS COM STATUS DIFERENTE DE FIM
		
		d::b()->query("insert into eventoresp (
						select distinct null as ideventoresp, e.idevento, 1029 as idpessoa, 1 as idempresa, gp.idpessoa as idobjeto, 'pessoa' as tipoobjeto, ets.ideventostatus as status, if (ets.ocultar = 'N',0,1) as oculto, gp.idimgrupo as idobjetoext, 'imgrupo' as tipoobjetoext,'N' as inseridomanualmente,0 as visualizado,e.criadopor,e.criadoem,e.alteradopor, now()
						from evento e
						join eventoresp r on r.idevento = e.idevento and `r`.`tipoobjeto` = 'imgrupo' 
						join imgrupopessoa gp on gp.idimgrupo = r.idobjeto
						left join eventoresp r2 on r2.idevento = r.idevento and r2.tipoobjetoext = 'imgrupo' and r2.idobjetoext = r.idobjeto and r2.idobjeto = gp.idpessoa
						left join pessoa p on p.idpessoa = gp.idpessoa
						LEFT JOIN eventotipo et on et.ideventotipo = e.ideventotipo
						LEFT JOIN eventotipostatus ets on ets.ideventotipo = et.ideventotipo and posicao = 'INICIO' 
						JOIN eventotipostatus etsf on etsf.ideventotipo = et.ideventotipo and etsf.ideventostatus = e.ideventostatus and NOT etsf.posicao = 'FIM' 
						where
						r2.idobjeto is null and not gp.idpessoa = e.idpessoa);");
						
		//DELETA TODAS AS PESSOAS QUE NÃO PERTENCEM MAIS AOS GRUPOS DENTRO DAS CONFIGURAÇÕES DE ALERTA
		d::b()->query("
						delete from 
							immsgconfdest 
						where 
							idimmsgconfdest in (
												select * 
												from (
														select 
															idimmsgconfdest 
														from 
															immsgconfdest er
														where 
															not er.idobjetoext is null 
															and er.objetoext = 'imgrupo' 
															and not exists (
																				select 1 from  imgrupopessoa igp 
																				where igp.idimgrupo = er.idobjetoext 
																				and igp.idpessoa = er.idobjeto 
																				and er.objeto = 'pessoa'
																			)
													)a
												);"
					) or die("O Delete na immsgconfdest falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

		//INSERE TODAS AS PESSOAS QUE FORAM ADICIONADAS RECENTEMENTE DENTRO DAS CONFIGURAÇÕES DE ALERTA
		//Alterado para aparecer as pessoas do grupo de cada empresa (Lidiane - 12-05-2020 - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=315016) - Alterado o 1 para p.idempresa
		d::b()->query("insert into immsgconfdest (
						select distinct null as idimmsgconfdest, p.idempresa as idempresa, e.idimmsgconf, gp.idpessoa, 'pessoa' as objeto,  r.idobjeto, 'imgrupo' as objetoext, 'N' as inseridomanualmente, 'ATIVO', e.criadopor,e.criadoem,e.alteradopor, now()
						from immsgconf e
						join immsgconfdest r on r.idimmsgconf = e.idimmsgconf and `r`.`objeto` = 'imgrupo' 
						join imgrupopessoa gp on gp.idimgrupo = r.idobjeto
						left join immsgconfdest r2 on r2.idimmsgconf = r.idimmsgconf and r2.objetoext = 'imgrupo' and r2.idobjetoext = r.idobjeto and r2.idobjeto = gp.idpessoa
						left join pessoa p on p.idpessoa = gp.idpessoa
						where
						r2.idobjeto is null and not exists (select 1 from immsgconfdest d2 where d2.idimmsgconf = e.idimmsgconf and d2.idobjeto = gp.idpessoa and d2.objeto = 'pessoa'));")  or die("O Insert na immsgconfdest falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

		// GVT - 24/08/2020 - Deleta as assinaturas pendentes de colaboradores INATIVOS
		d::b()->query("DELETE c
						FROM 
							carrimbo c 
						WHERE 
							c.status = 'PENDENTE' 
								AND c.idpessoa IN (
									SELECT p.idpessoa 
										FROM 
											pessoa p 
										WHERE 
											p.idpessoa = c.idpessoa 
											AND p.status = 'INATIVO')");

		d::b()->query("DELETE c
						FROM
							carrimbo c
						WHERE
							c.status = 'PENDENTE'
							AND c.tipoobjetoext <> 'pessoa'
							AND NOT EXISTS(
									SELECT 1
										FROM
											pessoaobjeto p
									WHERE
										c.idpessoa = p.idpessoa
										AND c.idobjetoext = p.idobjeto
										AND c.tipoobjetoext = p.tipoobjeto)");
		
		d::b()->query("DELETE po 
						FROM 
							pessoaobjeto po 
						WHERE 
							NOT EXISTS (
								SELECT 1 
									FROM 
										pessoa p 
									WHERE 
										p.idpessoa = po.idpessoa 
										AND p.status = 'ATIVO')");
									
		//Atualizar o redirecionamento de email, as informacoes devem ser salvas na emailvirtualconf para que a mesma seja sincronizada ao servidor de email
		// GVT - 07/02/2020 - Adicionado verificação de status de pessoa e grupo.
		/*		
		$sql="select  GROUP_CONCAT(p.webmailemail SEPARATOR ' ')  as emaildest , e.webmailemail as emailor,ge.idpessoa
					from imgrupopessoaemail ge ,imgrupo g,imgrupopessoa gp,pessoa e,pessoa p
					where ge.idpessoa = e.idpessoa
					and ge.idimgrupo=g.idimgrupo
					and gp.idimgrupo = g.idimgrupo
					and p.webmailemail is not null
					and p.webmailpermissao = 'Y'
					and e.webmailpermissao = 'Y'
					and g.status = 'ATIVO'
                    and p.status = 'ATIVO'
                    and e.status = 'ATIVO'
					and p.idpessoa = gp.idpessoa  group by e.webmailemail";
                */
                $sql="SELECT idemailvirtualconf,original,GROUP_CONCAT(emaildest SEPARATOR ' ')  as emaildest
						FROM (
							SELECT e.idemailvirtualconf as idemailvirtualconf,e.email_original as original,if(p.webmailemail <> '',p.webmailemail,p.email) AS emaildest
							FROM emailvirtualconf e
							JOIN emailvirtualconfpessoa ec ON (ec.idemailvirtualconf = e.idemailvirtualconf)
							JOIN pessoa p ON (ec.idpessoa = p.idpessoa)
							WHERE
								p.status = 'ATIVO'
								AND p.webmailemail IS NOT NULL
								AND p.webmailpermissao = 'Y'
								AND e.tipoemailvirtual = 'GRUPO'
								AND p.webmailemail REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$'
								AND e.status = 'ATIVO'
								".getidempresa('e.idempresa','emailvirtualconf')."
                            UNION
                            SELECT 
                                em.idemailvirtualconf as idemailvirtualconf,em.email_original as original,p.webmailemail AS emaildest
                            FROM emailvirtualconf em
                                join emailvirtualconfimgrupo evg on (em.idemailvirtualconf = evg.idemailvirtualconf)
                                join imgrupopessoa igp on (evg.idimgrupo = igp.idimgrupo)
                                join imgrupo im on (igp.idimgrupo = im.idimgrupo)
                                join pessoa p on (igp.idpessoa = p.idpessoa)
                            WHERE em.tipoemailvirtual = 'GRUPO'
								AND em.status = 'ATIVO'
                                AND im.status = 'ATIVO'
                                AND p.status = 'ATIVO'
                                AND p.webmailemail IS NOT NULL
                                AND p.webmailpermissao = 'Y'
								AND p.webmailemail REGEXP '^[A-Za-z0-9._]+@[A-Za-z0-9.-]+[.][A-Za-z]+$'
								".getidempresa('im.idempresa','imgrupo')."
                        ) as u
                        GROUP BY idemailvirtualconf";
		$res=d::b()->query($sql) or die("Erro ao buscar configuração para grupos de email");

		while($row=mysqli_fetch_assoc($res)){
			$su="UPDATE emailvirtualconf 
				SET email_original ='{$row['original']}',emails_destino = '{$row['emaildest']}' 
				WHERE idemailvirtualconf={$row['idemailvirtualconf']}";
			d::b()->query($su) or die("Erro ao atualizar email saveposchange sql=".$su);
		}	
	//	echo 'call=atualiza finalizado '.date('d/m/Y H:i:s');

		echo "Fim: ".date("d/m/Y H:i:s", time()).'<br>'; 

		$grupo = rstr(8);


		re::dis()->hMSet('bim',['fim' => Date('d/m/Y H:i:s')]);

		$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
									VALUES ('1', '".$grupo ."', 'cron', 'bim', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

		d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


	}

}


$_call=(!empty($_GET['call']))?$_GET['call']:$_POST['call'];

if($_call){
	$ajax_im = new IM($_call);
}


//mysqli_close(d::b());//@todo: Está retornando uma string desconhecida: SysSession[2]:
?>
