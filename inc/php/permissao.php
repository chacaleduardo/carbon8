<?php

/**
  Esta classe é usado para injecao de clausulas SQL conforme configuracoes em database, usando
  as variaveis existentes durante a execucao de um script por qualquer usuario
  - Denominacoes importantes:

		-- OTIPO: a indicacao de qual elemento de variavel sera utilizado. Possibilidades: 
			--- session
			--- get (nao implementado)
			--- post (nao implementado)

		-- OKEY: a chave da variavel desejada. Ex:
			--- IDEMPRESA (valor default. nao e necessario informar caso a key desejada seja esta)

		-- ACCESS: controlar regras restritivas ou permissivas. *Nao esta sendo utilizado

		-- apor: array com as colunas do join: tab.coluna=tab2.coluna
			--- Caso o usuario informe 1 parametro somente, este deve vir acompanhado do alias da tabela a sofrer exists.
				Com 1 coluna somente, esta classe ira recuperar a PK da tabela em questao, consultando o dicionario de dados
			--- Caso o usuario informe 2 parametros, o segundo parametro sera usado para compor e relacao de Join, e o dicionario de dados nao sera utilizado

  - Inicialmente configura-se um modelo de permissao na tabela PERM no database
  - A busca no database será feita por alguma variavel de sessao, atualmente, juntamente com o valor existente do usuário corrente
  - Um exemplo basico, e neste caso nao é necessario passar muitos parametros, é:

		-- EX1: Solicitacao: Usuario da empresa 1 deve poder acessar pessoas das empresas 1 e 8. Ex. de utilizacao da classe

			share::pessoasPorSessionIdempresa('a.idpessoa');

			--- Neste exemplo, deve-se existir na tabela perm um registro com o nome 'pessoasPorSessionIdempresa'
				Nesse registro havera a configuracao de qual tabela sera usada para gerar a clausula EXISTS(...)
				Como a chama está em "modo default", a oKey usada será

			--- Esta Classe utiliza o metodo magic __callStatic, para permitir facilidade de uso e extrema flexibilidade compondo esta camada de controle
				---- Métodos que exigem complexidade maior poderao, apos discussao com os lideres sobre a arquitetura, ser declarados de forma verbosa aqui, e nao se utilizar o recurso __callStatic

		-- Solicitacao: Usuario da empresa 1 deve poder acessar pessoas das empresas 1 e 8. Ex. de utilizacao da classe

			share::representantesPorSessionIdempresa('a.idpessoa','idobjeto');

			-- Neste exemplo, subentende-se que esta sendo utilizada uma view, e assim nao sera possivel para a classe recuperar a coluna PK.
				Por este motivo informa-se a coluna para relacionamento de join. O relacionamento sera:

				--- a.idpessoa = idobjeto

  	- É possivel informar a coluna para o relacionamento tanto na chamada principal, ou utilizando-se um metodo anterior parametrizando a clausula
	  Deste modo o metodo de ajuste da clausula para join (APOR) e redundante. Ex:

	  share::por('a.idpessoa')::representantesPorSessionIdempresa();
	  ou
	  share::por('a.idpessoa','idobjeto')::representantesPorSessionIdempresa();
	  
	- Deve ser dada atencao ao fato de que a classe é estatica, e utiliza-se o recurso de CHAINING para a meioria dos metodos,
		EXCETO o metodo principal que retorna uma string
		Portanto o metodo principal __callStatic nao aceita chaining. Exemplo ERRADO:

		#ERRADO: share::representantesPorSessionIdempresa()::por('a.idpessoa');

 */


/* MAF: Exemplo de uso: Injetar clausula de permissao com EXISTS para recuperar participantes no evento ************************************** /

//model/pessoa.php
$sql = "SELECT a.idpessoa AS pessoa, CONCAT('<i class=\"fa fa-user\" style=\"color:#ddd;font-size:10px;\"></i> ',IF(a.nomecurto is NULL, a.nome, a.nomecurto)) AS nome, 'pessoa' AS 'tipo', IF(a.nomecurto is NULL, a.nome, a.nomecurto) as labelnome
					FROM pessoa a
					WHERE  a.status ='ATIVO'
					".
						share::bloqueia(false)::pessoasPorSessionIdempresa('a.idpessoa')//::str();
					."
					AND (a.idtipopessoa = 1)
					AND NOT idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]." 
					AND NOT usuario is null";
*/


class share{
	public static $apor=[];//Parametros de relacionamento para o sql/exists/inner
	public static $str="";
	public static $ashare=[];
	public static $share="";
	public static $otipo="session";
	public static $okey="IDEMPRESA";
	public static $access="Y";
	//Estas variaveis controlam se o programador informou o que quer, ou se serao usados parametros default
	public static $bootipo;
	public static $bookey;
	//Outras
	public static $res;//Armazena o resultset do banco
	public static $alias="_a";
	public static $iregras=0;//Quantidade de regras encontradas no db
	private static $modulo = false;
	private static $moduloAux = "";
	private static $table = "";
	private static $operador = "AND EXISTS";
	private static $die = true;

	/*
	 * Magic method: https://www.php.net/manual/en/language.oop5.magic.php
	 * Este metodo é chamado como uma especie de trigger, disparada quando um metodo estatico nao existente é invocado
	 */
	public static function __callStatic($magicmetodo, $arguments){
		self::$share=$magicmetodo;
		self::$apor=$arguments;
		//self::$ashare=self::getshare();
		
		return self::sqlShare();
		//return __CLASS__;
	}

	/*
	 * Metodo de exemplo para ajustar ANTES da execucao principal, uma mudanca de alguma propriedade interna:
	 * Ex:
	 * Antes: share::pessoasPorSessionIdempresa('a.idpessoa');
	 * Depois: share::okey('IDMODULO')::pessoasPorSessionIdempresa('a.idpessoa');
	 */
	public static function okey($tipo){
		self::$okey=$tipo;
		return __CLASS__;
	}
	public static function otipo($tipo){
		self::$otipo=$tipo;
		return __CLASS__;
	}
	public static function odie($die){
		self::$die=$die;
		return __CLASS__;
	}
	public static function otable($table){
		self::$table=$table;
		return __CLASS__;
	}
	public static function omodulo($moduloAux){
		self::$moduloAux=$moduloAux;
		return __CLASS__;
	}

	/*
	 * Ajusta as colunas utilizadas para realizar o relacionamento
	   As colunas tambem podem ser informadas como parametro no metodo caller da regra
	 * Podem ser enviadas 1 ou mais colunas:
	 * Caso de 1 coluna: a coluna fornecida será inserida na clausula where relacionando-se com a coluna PK (via dicionario de dados) da permissao configurada
	 * Caso de 2 colunas: as 2 colunas informadas serão utilizadas
	 **Obs: Estes parâmetros podem ser alterados para informacao completa de clausula where, caso seja necessario futuramente
	 */
	public static function por(){
		//Este metodo valida aq uantidade de parametros. Se nenhum for enviado, gera erro
		if(func_num_args()==0)die(__CLASS__.": Coluna(s) para relacionamento não informadas");
		self::$apor=func_get_args();
		return __CLASS__;
	}

	//Monta array final com a perm já estruturada
	public static function getShare(){
		//Monta o sql com a perm
		//return self::sqlPerm();
	}

	//Monta array final com a perm já estruturada
	public static function sqlShare(){
		$share=self::buscarRegra();

		if(self::$iregras==0){
			if(self::$die){
				die(__CLASS__.": Nenhuma regra encontrada. Share: ".self::$share);
			}else{
				return false;
			}
		}

		//Recupera informacoes da tabela em questao. A coluna PK deve ser recuperada aqui para ser realizada a construcao do(s) relacionamento(s)
		if($share["ptipoobj"]=="table"){
			if(!empty(self::$table)){
				$tdef=retarraytabdef(self::$table);
				$share["pobj"] = self::$table;
			}else{
				$tdef=retarraytabdef($share["pobj"]);
			}
		}

		//Contera todas as clausulas para realizacao de implode com AND
		$awhere=[];

		//Valida as colunas para relacionamento. Esta regra pode ser flexibilizada caso seja necessario deixar de informar colunas, e realizar o relacionamento automaticamente
		if(count(self::$apor)==0){
			if(self::$die){
				die(__CLASS__.": Nenhuma coluna informada para relacionamento. Informe a coluna desejada na chamada da regra, ou utilize o metodo ::por('alias.nomecoluna') antes da regra");
			}else{
				return false;
			}
		}else{
			if(count(self::$apor)==1){
				$awhere[]=self::$alias.".".$tdef["#pkfld"]."=".self::$apor[0];
			}elseif(count(self::$apor)==2){
				$awhere[]=self::$apor[0]."=".self::$apor[1];
			}else{
				if(self::$die){
					//@todo: Tratar outros casos
					die(__CLASS__.": Mais de 2 colunas para relacionamento informadas. Verificar documentacao no código");
				}else{
					return false;
				}
			}
		}
		
		foreach ($share["aclauswhere"] as $col => $vlrs) {
			if(empty($vlrs)){
				$awhere[]=self::$alias.".".$col." = ''";
			}elseif(strpos($vlrs,",")){
				$awhere[]=self::$alias.".".$col." in (".$vlrs.")";
			}else{
				$awhere[]=self::$alias.".".$col." = ".$vlrs;
			}
		}		
		$sain=implode(" and ",$awhere);

		//Monta consulta exists
		$sql="\n-- ".__CLASS__."::".__METHOD__."\n ".self::$operador." (\n\tselect 1 from ".nomeTabela($share["pobj"])." ".self::$alias." where ".$sain."\n)";
		$cin[]=$sql;

		return $sql;
	}

	//Consulta a share no banco e devolve um array pre-formatado
	private static function buscarRegra(){
	
		//Recupera valor da Sessao do usuario
		$value="";
		switch (self::$otipo) {
			case "session":
				$value=$_SESSION["SESSAO"][self::$okey];
				if(empty($value))die(__CLASS__.": Session vazia: ".self::$okey);
				break;
			case "cb::usr":
				$value=cb::$usr[self::$okey];
				if(empty($value))die(__CLASS__.": Session vazia: ".self::$okey);
				break;
			default:
				break;
		}

		if(self::$modulo){
			if(!empty(self::$moduloAux)){
				$oModulo = "and modulo = '".d::b()->real_escape_string(self::$moduloAux)."'";
				self::$modulo = false;
			}else{
				$oModulo = "and modulo = '".d::b()->real_escape_string($_GET["_modulo"])."'";
				self::$modulo = false;
			}
		}else{
			$oModulo = "";
		}


		$sql="select otipo,ptipoobj,pobj,jclauswhere
			from "._DBAPP.".share p
			where sharemetodo = '".self::$share."'
			and acesso='".self::$access."'
			and okey='".self::$okey."'
			and ovalue='".$value."'
			".$oModulo;

		$res = d::b()
			->query($sql);
		//Reseta
		self::$ashare=mysqli_fetch_assoc($res);
		self::$iregras=mysqli_num_rows($res);
		self::$ashare["aclauswhere"]=json_decode(self::$ashare["jclauswhere"],true);
		return self::$ashare;
	}

	public static function moduloFiltrosPesquisa($apor){
		self::$share='modulofiltrospesquisa';
		self::$otipo='cb::usr';
		self::$modulo = true;
		self::$die = false;
		self::$operador = "";
		self::$apor = [$apor];

		return self::sqlShare();
	}

}