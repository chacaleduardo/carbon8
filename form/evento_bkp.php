<?

    require_once("../inc/php/functions.php");
    require_once("../inc/php/validaacesso.php");

    $_acao = $_GET['_acao'];

    if ($_POST) {
        include_once("../inc/php/cbpost.php");
    }
    /*
    * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
    * $pagvalcampos: Informar os Parâmetros GET que devem ser validados para compor o select principal
    *                pk: indica Parâmetro chave para o select inicial
    *                vnulo: indica Parâmetros secundários que devem somente ser validados se nulo ou não
    */
    $pagvaltabela = "evento";
    $pagvalcampos = array(
        "idevento" => "pk"
    );

    /*
    * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
    */
    $pagsql = "SELECT * FROM evento WHERE idevento = '#pkid'";
    /*
    * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET
        e preenchimento das variáveis que vieram por POST
    */
    include_once("../inc/php/controlevariaveisgetpost.php");

    $eventoTipo = filter_input(INPUT_GET, 'eventotipo');
    $calendario = filter_input(INPUT_GET, 'calendario');
    $idmodulo   = filter_input(INPUT_GET, 'idmodulo');
    $modulo     = filter_input(INPUT_GET, 'modulo');
    $inicio     = filter_input(INPUT_GET, 'inicio');
    $fim        = filter_input(INPUT_GET, 'fim');
    
    $subevento  = false;
    $dataclick  = str_replace("/", "-", filter_input(INPUT_GET, 'dataclick'));

    $horainicio = roundUpToMinuteInterval(new DateTime(now));

    $horafim    = roundUpToMinuteInterval(new DateTime(now));
    $horafim    -> modify('+1 hour');
    $newdate    = date_create()->format('d/m/Y');

    // Rounded from 37 minutes to 40
    //  2018-06-27 20:40:00
    if (empty($_1_u_evento_inicio)) {
        $_1_u_evento_ideventotipo   = (empty($eventoTipo)) ? '' : $eventoTipo;
        $calendario                 = (empty($calendario)) ? '' : $calendario;
        $_1_u_evento_inicio         = (empty($inicio)) ? $newdate : $inicio;
        $_1_u_evento_prazo          = (empty($inicio)) ? $newdate : $inicio;
        $_1_u_evento_fim            = (empty($fim)) ? $newdate : $fim;
        $_1_u_evento_iniciohms      = $horainicio->format("H:i");
        $_1_u_evento_fimhms         = $horafim->format("H:i");
        $_1_u_evento_repetirate     = '';
        $_1_u_evento_fimsemana      = 'N';
        $_1_u_evento_periodicidade  = '';
        $_1_u_evento_idsgdoc        = '';
    }

    $idevento           = (empty($_1_u_evento_idevento)) ? 'undefined' : $_1_u_evento_idevento;
    $inicioformatado    = validadate($_1_u_evento_inicio);
    $fimformatado       = validadate($_1_u_evento_fim);
    $evento             = traduzid("eventotipo", "ideventotipo", "eventotipo", $_1_u_evento_ideventotipo);
	
	
	//SABER QUAL TOKEN INICIAL DO EVENTO
	if (!empty($idevento)){
		
		$sql = "select 
					getEventoStatusConfig(e.ideventotipo,CONCAT('\"',e.versao,'\"'),null, true,'token') AS tokeninicial
				FROM 
					evento e
				WHERE 
					e.idevento = '".$idevento."'";
		
		
		$res = d::b()->query($sql);

		while ($r = mysqli_fetch_assoc($res)) {
			$tokeninicial = $r['tokeninicial'];
		}
	}

    
    if (!empty($_1_u_evento_idevento)) {

        $sql = "UPDATE eventoresp
                SET visualizado = 1,
				alteradoem = now()
                WHERE idevento = ".$_1_u_evento_idevento."
                AND tipoobjeto = 'pessoa' 
                AND idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"].";";

        $res = d::b()->query($sql) or die("Erro ao resetar visualizados do evento: ".mysqli_error(d::b()));
		
		$versao = '\'"'.$_1_u_evento_versao.'"\''; 
		
		$sql = "SELECT 
					JSON_EXTRACT(jconfig,concat('$[*].',".$versao."))AS jconfig,
					jsonconfig,
					if (getEventoStatusConfig(ideventotipo,".$versao.",'".$_1_u_evento_status."',false,'acao') is null,
					getEventoStatusConfig(ideventotipo,".$versao.",'".$_1_u_evento_status."',false,'status'),
					getEventoStatusConfig(ideventotipo,".$versao.",'".$_1_u_evento_status."',false,'acao'))
					as statusevento
                FROM
					eventotipo
                WHERE ideventotipo = '".$_1_u_evento_ideventotipo."'";

        $res = d::b()->query($sql) or die("Erro ao carregar jconfig do evento: ".mysqli_error(d::b()));
		while ($r = mysqli_fetch_assoc($res)) {
                $jconfig = $r['jconfig'];
				$jconfig = json_decode($jconfig); 
				$jconfig = json_encode($jconfig[0],JSON_UNESCAPED_UNICODE);
				$jconfig = htmlspecialchars($jconfig, ENT_QUOTES);
				$statusevento = $r['statusevento'];
				
		}
		
	/*	if ($_SESSION["SESSAO"]["USUARIO"]=="marcelocunha") {
		echo $jconfig ;	
		echo '<br><br>';
		echo 
		}else{
			$jconfig = $r['jsonconfig'];
		}
		*/
		
    }else{
		$sql = "	SELECT 
					REPLACE(REPLACE(JSON_KEYS(JSON_EXTRACT(jconfig,'$[last]')),'[',''),']','') as versao,
					 JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(jconfig,concat('$[*].',REPLACE(REPLACE(JSON_KEYS(JSON_EXTRACT(jconfig,'$[last]')),'[',''),']',''))),'$[0]'), '$.statuses') as statuses
                FROM
					eventotipo
                WHERE ideventotipo = '".$eventoTipo."'";

        $res = d::b()->query($sql) or die("Erro ao resetar visualizados do evento: ".mysqli_error(d::b()));
		while ($r = mysqli_fetch_assoc($res)) {
                $_1_u_evento_versao = str_replace('"','',$r['versao']);
				
				$arrstatuses = json_decode($r["statuses"]);
					foreach ($arrstatuses as $key => $object) {
						if ($object->inicial == true){
							$_1_u_evento_status = $object->token;
						}
				}
				
		}
			
	}

    if (!empty($_1_u_evento_ideventopai)) {
        $subevento = true;
    }
    
    $jTag           = "null";
    $jSgdoc         = "null";
    $jMotivo        = "null";
    $jPessoa        = "null";
    $jDocumento     = "null";
    $jSgsetorvinc   = "null";
    $jSfornecedor   = "null";
    $jEquipamento   = "null";
    $jUserStatus    = getStatus();
    $jFuncionario   = getJfuncionario();


    $nomemodulo   = ($_1_u_evento_modulo) ? RetornaChaveModuloEvento($_1_u_evento_modulo) : '';

    /*
    * Centralizar a consulta de Módulo
    * Evitar falhas em relação à  Módulos Vinculados
    * Complementar com as colunas necessárias diretamente na consulta
    */
    function RetornaChaveModuloEvento($inModulo, $inbypass=false) {
        
        if (empty($inModulo)) die("retArrModuloConf: Parâmetro inModulo não informado");

        //Permite reaproveitamento sem verificação de segurança. Ex: Tela de _modulo necessita recuperar informaçàµes do módulo mesmo que não estejam devidamente atribuà­das em alguma LP
        if ($inbypass !== true) {
            $joinLp = ($_SESSION["SESSAO"]["LOGADO"])?"left join "._DBCARBON."._lpmodulo l on (l.modulo=m.modulo and l.idlp='".$_SESSION["SESSAO"]["IDLP"]."')":"";
            $whereMod = ($_SESSION["SESSAO"]["LOGADO"])?"and m.modulo in (".getModsUsr("SQLWHEREMOD").")":"";
            $ifrestaurar = (getModsUsr("SQLWHEREMOD"))?",IF(1=(select ('restaurar' in  (".getModsUsr("SQLWHEREMOD")."))),'Y','N') as oprestaurar":"";
        }
                
   
  /* $smod = "SELECT
                    CASE WHEN m.tipo='MODVINC' THEN mv.chavefts ELSE m.chavefts END as chavefts
                FROM 
                    "._DBCARBON."._modulo m 
                    left join "._DBCARBON."._modulo mv on (mv.modulo=m.modvinculado)
                    left join "._DBCARBON."._modulo mpar on (mpar.modulo=m.modulopar)
                    ".$joinLp."
                WHERE m.modulo = '".$inModulo."'
                    ".$whereMod;*/
					
	$smod = "select col as chavefts from carbonnovo._modulo m
				join carbonnovo._mtotabcol mt on mt.tab = m.tab and primkey = 'Y'
				where m.modulo = '".$inModulo."'";
        
        //die($smod);
        
        $rmod = d::b()->query($smod);
        
        if (!$rmod) die("retArrModuloConf: Erro ao recuperar Módulo ".  mysqli_error(d::b()));

        $rows = mysqli_fetch_assoc($rmod);
        return ($rows['chavefts']);
    }

    $idPessoa = $_SESSION["SESSAO"]["IDPESSOA"];
    $nomePessoa = $_SESSION["SESSAO"]["NOMECURTO"];

    function getStatus() {

        global $JSON, $_1_u_evento_idevento;

        $arrtmp = array();

        if (!empty($_1_u_evento_idevento)) {
            
            $sql = "SELECT 
                    er.ideventoresp,
                    er.status
                FROM eventoresp er
                WHERE er.idevento = ".$_1_u_evento_idevento."
                AND er.idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"]."
                AND er.tipoobjeto = 'pessoa'
                ORDER BY er.ideventoresp asc limit 1";

            $rts = d::b()->query($sql) or die("Erro: ". mysqli_error(d::b()));

            $i = 0;

            while ($r = mysqli_fetch_assoc($rts)) {
                $arrtmp[$i]["ideventoresp"] = $r["ideventoresp"];
                $arrtmp[$i]["status"] = $r["status"];
                $i++;
            }
            
        }

        return $JSON->encode($arrtmp); 
    }

    function getJfuncionario() {
        
        global $JSON, $_1_u_evento_idevento;
        
        $sql = "SELECT a.idpessoa ,a.nomecurto    
                FROM pessoa a
                WHERE a.idempresa =".idempresa()." 
                AND a.status ='ATIVO'
                AND a.idtipopessoa = 1
                and not idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]."
				 and not exists(
                    SELECT 1
                    FROM eventoresp r
                    where  r.idevento= '".$_1_u_evento_idevento."' 
                        and r.tipoobjeto ='pessoa'
                        and a.idpessoa = r.idobjeto				
            )
                ORDER BY a.nomecurto asc";

        $rts = d::b()->query($sql) or die("oioi: ". mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"]=$r["idpessoa"];
            $arrtmp[$i]["label"]= $r["nomecurto"];
            $i++;
        }
        
        return $JSON->encode($arrtmp);    
    }

    function getTags() {

        global $JSON, $_1_u_evento_idevento;

        $sql = "SELECT
                    t.idtag,
                    t.tag,
                    t.descricao,
                    t.idtagtipo
                FROM tag t
                WHERE t.idempresa =" . idempresa() . "
                AND t.status ='ATIVO'
                ORDER BY t.descricao";

        $rts = d::b()->query($sql) or die("getTags: " . mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["id"] = $r["idtag"];
            $arrtmp[$i]["idtag"] = $r["tag"];
            $arrtmp[$i]["descricao"] = $r["descricao"];
            $arrtmp[$i]["idtagtipo"] = $r["idtagtipo"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }

    function getPessoas() {

        global $JSON, $_1_u_evento_idevento;

        $sql = "SELECT
                    p.idpessoa,
                    p.nome,
                    p.idtipopessoa
                FROM pessoa p
                WHERE p.idempresa =" . idempresa() . "
                AND p.status ='ATIVO'
				
                ORDER BY p.nome";

        $rts = d::b()->query($sql) or die("getTags: " . mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["id"] = $r["idpessoa"];
            $arrtmp[$i]["idtipopessoa"] = $r["idtipopessoa"];
            $arrtmp[$i]["nome"] = $r["nome"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }

    function getDocumentos() {

        global $JSON, $_1_u_evento_idevento;

        $sql = "SELECT
                    d.idsgdoc,
                    d.titulo,
                    d.idsgdoctipo
                FROM sgdoc d
                WHERE d.idempresa =" . idempresa() . "
                ORDER BY d.titulo";

        $rts = d::b()->query($sql) or die("getDocumentos: " . mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["id"] = $r["idsgdoc"];
            $arrtmp[$i]["idsgdoctipo"] = $r["idsgdoctipo"];
            $arrtmp[$i]["titulo"] = $r["titulo"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }

    $jTag = getTags();
    $jPessoa = getPessoas();
    $jDocumento = getDocumentos();
    $jSgsetorvinc = getJSetorvinc();

    function getJSetorvinc() {

        global $JSON, $_1_u_evento_idevento;

        $sql = "SELECT idimgrupo, grupo
                FROM imgrupo g
                WHERE idempresa = ".idempresa()." 
                AND status='ATIVO'
				 and not exists(
                    SELECT 1
                    FROM eventoresp r
                    where  r.idevento= '".$_1_u_evento_idevento."' 
                        and r.tipoobjeto ='imgrupo'
                        and g.idimgrupo = r.idobjeto				
            )
                ORDER BY grupo ASC";

        $rts = d::b()->query($sql) or die("getJSetorvinc: ". mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idimgrupo"];
            $arrtmp[$i]["label"] = $r["grupo"];
            $i++;
        }

        return $JSON->encode($arrtmp);
    }

    function getJSgdoc() {

        global $JSON, $_1_u_evento_idevento;

        $s = "SELECT
                    a.idsgdoc
                    ,concat(a.idregistro,'-',a.titulo,'-(',a.idsgdoctipo,')') as  titulo
                    from sgdoc a
                    where a.idempresa =" . idempresa() . "
                        and not exists(
                            SELECT 1
                            FROM eventoobj v
                            where v.idempresa=a.idempresa
                                and v.idevento= " . $_1_u_evento_idevento . "
                                and v.objeto = 'SGDOC'
                                and v.idobjeto=a.idsgdoc
                        )
                    order by titulo";

        $rts = d::b()->query($s) or die("getJSetorvinc: " . mysqli_error(d::b()));
        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idsgdoc"];
            $arrtmp[$i]["label"] = $r["titulo"];
            $i++;
        }

        return $arrtmp;
    }

    if (!empty($_1_u_evento_idevento)) {
        $arrSgdoc = getJSgdoc();
        $jSgdoc = $JSON->encode($arrSgdoc);
    }

    function getJfornecedor() {

        $s = "select
                    a.idpessoa
                    ,a.nome
                from pessoa a
                where a.idempresa =" . idempresa() . "
                    and a.status ='ATIVO'
                    and a.idtipopessoa =5
                order by a.nome asc";

        $rts = d::b()->query($s) or die("getJSetorvinc: " . mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idpessoa"];
            $arrtmp[$i]["label"] = $r["nome"];
            $i++;
        }

        return $arrtmp;
    }


    if (!empty($_1_u_evento_idevento)) {
        $arrforn = getJfornecedor();
        $jSfornecedor = $JSON->encode($arrforn);
    }

    function getjEquipamento() {

        global $JSON, $_1_u_evento_idevento;

        $sql = "SELECT
                    a.idtag,
                    concat(a.tag,' - ',a.descricao) AS descricao
                FROM tag a
                WHERE a.idempresa =" . idempresa() . "
                AND NOT EXISTS(
                    SELECT 1
                    FROM eventoobj v
                    WHERE v.idempresa=a.idempresa
                    AND v.idevento= " . $_1_u_evento_idevento . "
                    AND v.objeto = 'EQUIPAMENTO'
                    AND v.idobjeto=a.idtag
                )
                ORDER BY a.tag";

        $rts = d::b()->query($sql) or die("getJSetorvinc: " . mysqli_error(d::b()));

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idtag"];
            $arrtmp[$i]["label"] = $r["descricao"];
            $i++;
        }

        return $JSON->encode($arrtmp);
    }

    if (!empty($_1_u_evento_idevento)) {
        $jEquipamento = getjEquipamento();
    }

    function jsonMotivo() {

        $sql    = " SELECT  idsgdoctipodocumento, tipodocumento
                    FROM    sgdoctipodocumento
                    WHERE   status='ativo'
                    AND idsgdoctipo='rnc'
                    ORDER BY  tipodocumento";

        $res    = d::b()->query($sql) or die("getSgdoctipodocumento: Erro: ".mysqli_error(d::b())."\n".$sql);

        $arrret = array();
        $i = 0;
        
        while ($r = mysqli_fetch_assoc($res)) {

            $arrtmp[$i]["value"]    =   $r["idsgdoctipodocumento"];
            $arrtmp[$i]["label"]    =   ($r["tipodocumento"]);
            $i++;	
        }
        
        $json = new Services_JSON();
        return $json->encode($arrtmp);
    }

    if (!empty($_1_u_evento_idevento)) {
        $jMotivo = jsonMotivo();
    }

    if (!empty($_1_u_evento_idevento)) {
        $disabled = "disabled='disabled' ";
    }

    function roundUpToMinuteInterval(\DateTime $dateTime, $minuteInterval = 30) {
        return $dateTime->setTime(
            $dateTime->format('H'),
            ceil($dateTime->format('i') / $minuteInterval) * $minuteInterval, 0
        );
    }

   
function getSetor(){
   global $JSON,$_1_u_evento_idevento;
    $sql="select s.idimgrupo,s.grupo
        from  imgrupo s 
        where s.status='ATIVO' 
        
            and not exists(
                    SELECT 1
                    FROM eventoresp r
                    where  r.idevento= ".$_1_u_evento_idevento." 
                        and r.tipoobjeto ='imgrupo'
                        and s.idimgrupo = r.idobjeto				
            )
        and s.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."  order by s.grupo";
    $res = d::b()->query($sql) or die("getSetor: Erro: ".mysqli_error(d::b())."\n".$sql);
    
    $arrtmp=array();
    $i=0;
    while ($r = mysqli_fetch_assoc($res)) {
        $arrtmp[$i]["value"]=$r["idimgrupo"];
        $arrtmp[$i]["label"]= $r["grupo"];
        $i++;
    }    
    
    return $JSON->encode($arrtmp);
}
  
    
function listaSgsetor(){
    global $_1_u_evento_idevento;
    $s = "select r.ideventoresp,s.grupo,s.idimgrupo,r.criadopor,r.criadoem,s.status
            from eventoresp r,imgrupo s
                where s.idimgrupo = r.idobjeto
                and r.tipoobjeto ='imgrupo'
                and r.idevento = ".$_1_u_evento_idevento." order by s.grupo";

    $rts = d::b()->query($s) or die("listaSgsetor: ". mysqli_error(d::b()));

    echo "<div class='table-hover row' style='margin-left:0px;margin-right:0px;'>";
    while ($r = mysqli_fetch_assoc($rts)) {
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
        if ($r["status"] == 'ATIVO'){ $cor = 'verde hoververde'; }else{ $cor = 'vermelho hoververmelho';}
        echo "<div class='col-md-6'><table class='table-hover' style='width:100%; background:#eee;font-size:10px;text-transform:uppercase;'><tbody><tr><td title='".$title."'>".$r["grupo"]."</td><td><i class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"retirasgsetor(".$r['ideventoresp'].")\" title='Excluir!'></i></td><td><a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></td></tr></tbody></table></div>";

    }
    echo "</div>";
}

function listaPessoa(){

    global $_1_u_evento_idevento, $_1_u_evento_idpessoa;
    $s = "select r.ideventoresp,s.nomecurto,s.idpessoa, r.visualizado, r.oculto, r.inseridomanualmente,r.criadopor,r.criadoem,r.status,s.idtipopessoa, g.grupo, ss.setor, rg.ideventoresp as ideventorespgrupo,
	JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(jconfig,concat('$[*].','\"',versao,'\"')), '$[0]'), '$.statuses') as statuses,
	getEventoStatusConfig(e.ideventotipo,CONCAT('\"',e.versao,'\"'),r.status,if(r.status != '',false,true),'color') as respcor,
	getEventoStatusConfig(e.ideventotipo,CONCAT('\"',e.versao,'\"'),r.status,if(r.status != '',false,true),'status') as respstatus,
	getEventoStatusConfig(e.ideventotipo,CONCAT('\"',e.versao,'\"'),r.status,if(r.status != '',false,true),'token') as resptoken
            from eventoresp r
				join evento e on r.idevento = e.idevento
				join eventotipo et on et.ideventotipo = e.ideventotipo
				join pessoa s on s.idpessoa = r.idobjeto and r.tipoobjeto ='pessoa'
				LEFT JOIN imgrupo g on g.idimgrupo = r.idobjetoext and r.tipoobjetoext ='imgrupo'
				LEFT JOIN pessoasgsetor ps on ps.idpessoa =  s.idpessoa
				LEFT JOIN sgsetor ss on ss.idsgsetor = ps.idsgsetor and ss.status = 'ATIVO'
				left join eventoresp rg on rg.idobjeto = r.idobjetoext and rg.idevento = r.idevento
                where 
                r.idevento = '".$_1_u_evento_idevento."' order by g.grupo, s.nome";

    $rts = d::b()->query($s) or die("listaPessoa: ". mysqli_error(d::b()));

    echo "<div class='table-hover table table-striped planilha'>";
    while ($r = mysqli_fetch_assoc($rts)) {
		$cor = $r['respcor'];
		$respstatus = $r['respstatus'];
		
		$arrstatuses = json_decode($r["statuses"]);
					foreach ($arrstatuses as $key => $object) {
						$corstatus[$object->token] = $object->color;
		}
				
		if ($r["idpessoa"] == $_SESSION["SESSAO"]["IDPESSOA"]){
			echo '<input
                            id="statusresp" 
                            type="hidden"
                            value="'.$r["resptoken"].'" 
                            readonly="readonly">';
		}
		
		
		$pad = 'padding: 2px 24px;';
		
		if ( $r['oculto'] == '1'){
			$op = 'opacity:0.5;';
		}else{
			$op ='';
		}
		
		if ($grupo != $r["grupo"]){
			if ($grupo != ''){
				echo '</div></fieldset>';
			}
			$grupo = $r["grupo"];
			if($_SESSION["SESSAO"]["IDPESSOA"] != $_1_u_evento_idpessoa){
				echo "<div style='padding:0px 6px;><fieldset class='scheduler-border'><legend class='scheduler-border'>".$grupo." <i class=\"fa fa-ban fa-1x cinzaclaro hovercinza btn-lg pointer ".$cor." ui-droppable\" title='Excluir!'></i><a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></legend>";
			}else{
				echo "<div style='padding:0px 6px;><fieldset class='scheduler-border'><legend class='scheduler-border'>".$grupo." <i class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"retirasgsetor(".$r['ideventorespgrupo'].")\" title='Excluir!'></i><a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></legend>";
			}
		}	

		if (!empty($r["grupo"])){
			$pad = '';
		}

        if($r['idtipopessoa']==1){
            $mod='funcionario';
        }else{
            $mod='pessoa';
        }
        
        if($r['inseridomanualmente']=='N' or $r['idpessoa'] == $_1_u_evento_idpessoa or $_SESSION["SESSAO"]["IDPESSOA"] != $_1_u_evento_idpessoa){
            $botao="";
        }else{
            $botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho  pointer ui-droppable' status='".$r["status"]."' ideventoresp='".$r["ideventoresp"]."' onclick='retirapessoa(".$r["ideventoresp"].")'></i>";
        }
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
        if ($r["setor"]){
			$cl = "&nbsp<span style='background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;'>".$r["setor"]."</span>";
		}else{
			$cl = '';
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
        echo "<div id=".$r["ideventoresp"]." class='".$opacity." col-md-12' style='".$pad."".$op."''><div class='col-md-11' style='line-height: 14px; padding: 8px; font-size: 10px;'><span title=".$respstatus." class='circle button-".$cor."' style='background:".$cor."; border:none;'></span>&nbsp".$vs."".$r["nomecurto"]." ".$cl."</div><div class='col-md-1'>".$botao."</div> </div>";

    }
	if ($grupo != ''){
		echo '</div></fieldset>';
	}
    echo "</div>";
    
}

function getJpessoa(){
    global $JSON, $_1_u_evento_idevento;
  /*  if($_1_u_immsgconf_tipo=='E' or $_1_u_immsgconf_tipo=='ET' or  $_1_u_immsgconf_tipo=='EP'){
        $andtppes=" ";
    }else{
        $andtppes=" and a.idtipopessoa =1 ";
    }*/
    $s = "select 
                a.idpessoa
                ,concat(a.nome,'-',t.tipopessoa) as nome
            from pessoa a join tipopessoa t on (t.idtipopessoa=a.idtipopessoa)
            where a.idempresa =".idempresa()." 
                and a.status ='ATIVO'
                 ".$andtppes."
                    and not exists(
                            SELECT 1
                            FROM eventoresp r
                            where  r.idevento= '".$_1_u_evento_idevento."'
                                and r.tipoobjeto ='pessoa'
                                and a.idpessoa = r.idobjeto				
                    )
            order by a.nome asc";

    $rts = d::b()->query($s) or die("getJSetorvinc: ". mysqli_error(d::b()));

    $arrtmp=array();
    $i=0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"]=$r["idpessoa"];
        $arrtmp[$i]["label"]= $r["nome"];
        $i++;
    }
    return $JSON->encode($arrtmp);    
}

?>
<style>
fieldset.scheduler-border {
    border: 1px solid #eee !important;
    padding: 8px;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow:  0px 0px 0px 0px #000;
            box-shadow:  0px 0px 0px 0px #000;
}

legend.scheduler-border {
    font-size: 11px !important;
    font-weight: bold !important;
    text-align: left !important;
text-transform:uppercase;
}
legend {
border-bottom:none;
margin-bottom:0px !important;

}
#mceu_4{
	top: 24px !important;
}

.multiselects{
	width:100% !important;
}
</style>
<div onload="fecharpag(<?= $idevento ?>, '<?= $evento ?>', '<?= $inicioformatado ?>', '<?= $fimformatado ?>', '<?= $dataclick ?>')">
	<div class="row">
    <div class="col-md-1"><span class="h5" data-toggle="dropdown">Carbon&nbsp;/&nbsp;Evento</span>
    
    </div>
    <div class="col-md-11">
    <button id="cbSalvar" type="button" class="btn btn-danger btn-xs pointer" onclick="CB.post()" title="Salvar">
                <i class="fa fa-circle"></i>Salvar
            </button>
    </div>
    </div>
    <div class="">
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div id="idEventoTitulo" class="col-md-2">
                            <span>
                                <strong >
                                    ID:<label  class="alert-warning ">
                                    <?= $_1_u_evento_idevento ?>
                                </label>
                                </strong>
                              
                            </span>
                        </div>
						
                        <div  class="col-md-5" >
                            <span id="tipoEventoSpan" style="line-height: 26px;"></span>
                        </div>
						<div  class="col-md-1">
						<?if ($_1_u_evento_ideventopai){?>
						<span>
                                <strong >
                                    Pai:<label class="alert-warning ">
                                    <a href="?_modulo=evento&_acao=u&idevento=<?= $_1_u_evento_ideventopai ?>"><?= $_1_u_evento_ideventopai ?></a>
                                </label>
                                </strong>
                              
                            </span>
						<? } ?>	
                           
                        </div>
                        <div class="col-md-4" style="text-align:right">
                            <span>
                               
                                <label class="alert-warning"  id="statusButton">
                                    
                                </label>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="panel-body">
					<div class="col-md-12">
					<span id="listabotoes" 	></span>
					</div>
                    <div class="col-md-12">

                        <input 
                            id="jsonconfig" 
                            type="hidden"
                            value="<?= $jconfig?>" 
                            readonly='readonly'>

                        <input name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_jsonresultado" 
                            id="jsonresultado" 
                            type="hidden"
                            value="<?= $_1_u_evento_jsonresultado?>" 
                            readonly='readonly'>

                        <input name="_1_<?= $_acao ?>_evento_idevento" 
                            id="idevento" 
                            type="hidden"
                            value="<?= $_1_u_evento_idevento ?>" 
                            readonly='readonly'>

                        <input name="_1_<?= $_acao ?>_evento_idpessoa" 
                            id="idpessoa" 
                            type="hidden"
                            value="<?= $_1_u_evento_idpessoa ?>" 
                            readonly='readonly'>

                        <input name="_1_<?= $_acao ?>_evento_ideventotipo" 
                            id="ideventotipo" 
                            type="hidden"
                            value="<?= $_1_u_evento_ideventotipo ?>" 
                            readonly='readonly'>
						 <input name="_1_<?= $_acao ?>_evento_versao" 
                            id="versao" 
                            type="hidden"
                            value="<?= $_1_u_evento_versao ?>" 
                            readonly='readonly'>

                        <input name="_1_<?= $_acao ?>_evento_status" 
                            id="status" 
                            type="hidden"
                            value="<?= ($_acao=='u') ? $_1_u_evento_status : $_1_u_evento_status ?>" 
                            readonly='readonly'>
							
						 <input name="jsonhistorico" 
                            id="jsonhistorico" 
                            type="hidden"
                            value="<?= $_1_u_evento_jsonhistorico?>" 
                            readonly='readonly'>
						

                        <div class="row">
                            <div class="col-md-2">Título:</div>
                            <?= (!empty($_1_u_evento_idevento)) ? '<div class="col-md-10" >' : '<div class="col-md-10" >';?>
                            <input maxlength="70" placeholder="Nome do Evento" name="_1_<?= $_acao ?>_evento_evento" id="idevento"
                                type="text" value="<?= $_1_u_evento_evento ?>" vnulo>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">Descrição:</div>
                            <div class="col-md-10">

                                <div id="diveditor" class="diveditor" onkeypress="pageStateChanged=true;" style="text-align: left; width:100% !important;border:1px solid #cccccc;border-radius:4px;font-size:12px;height:220px;"><?=$_1_u_evento_descricao?></div>
                          
                                <textarea style="display: none; text-align: left" name="_1_<?= $_acao ?>_evento_descricao"><?= $_1_u_evento_descricao ?></textarea>
                            </div>
                        </div>

                        <div id="divmodulo" class="row">
                            <div class="col-md-2">Módulo:</div>
                            <div class="col-md-10" >
                                <input placeholder="Link do Módulo" name="_1_<?= $_acao ?>_evento_modulo" id="inputmodulo"
                                        type="text" value="<?= traduzid("evento","idevento","modulo",$_1_u_evento_idevento); ?>">
                                <input placeholder="Link do Id do Módulo" name="_1_<?= $_acao ?>_evento_idmodulo" id="inputidmodulo"
                                        type="text" value="<?= traduzid("evento","idevento","idmodulo",$_1_u_evento_idevento); ?>">
                                <a id="modulo" name="_1_<?= $_acao ?>_evento_modulo" title="Módulo" href="javascript:janelamodal('?_modulo=<?=traduzid("evento","idevento","modulo",$_1_u_evento_idevento);?>&_acao=u&<?=$nomemodulo?>=<?=traduzid("evento","idevento","idmodulo",$_1_u_evento_idevento);?>')">
                                    <?=traduzid("evento","idevento","modulo",$_1_u_evento_idevento);?>
                                </a>
                            </div>
                        </div>

                        <div class="row tipoEvento">
                            <div class="col-md-2">Tipo Evento:
<?                         
                            if (!empty($_1_u_evento_idevento)) {
?>
                                <a class="fa fa-bars pointer hoverazul" title="Tipo Evento"
                                    onclick="janelamodal('?_modulo=eventotipo&_acao=u&ideventotipo=<?= $_1_u_evento_ideventotipo ?>')"></a>
<?
                            }
?>
                            </div>

                            <div class="col-md-10">
                                <select id="selectTipoEvento" 
                                    value="<?= $_1_u_evento_ideventotipo ?>" 
                                    name="_1_<?= $_acao ?>_evento_ideventotipo" 
                                    vnulo <?= ($_acao=='u') ? 'readonly="readonly"' : '';?>>

                                    <option selected="selected" disabled="disabled" value="">Selecione o tipo do evento</option>
                                    <? fillselect("SELECT ideventotipo, eventotipo FROM eventotipo WHERE STATUS = 'ATIVO' AND idempresa =" . $_SESSION["SESSAO"]["IDEMPRESA"] . " ORDER BY eventotipo", $_1_u_evento_ideventotipo); ?>

                                </select>
                            </div>
                        </div>

                        <div class="row" id="prazo" style="display:none">
                            <div class="col-md-2">Prazo:</div>
                            <div class="col-md-4" id="dataprazo">
                                <input class="calendario" name="_1_<?= $_acao ?>_evento_prazo"
                                    type="text" 
                                    size="15"
                                    value="<?= $_1_u_evento_prazo ?>" vnulo>
                            </div>
                        </div>

                        <div class="row" id="data">

                            <div class="col-md-2">Início:</div>
                            <div class="col-md-2" id="datainicio">
                                <input name="_1_<?= $_acao ?>_evento_inicio" 
                                    class="calendario" 
                                    type="text" 
                                    size="15"
                                    value="<?= $_1_u_evento_inicio ?>" vnulo>
                            </div>

                            <div class="col-md-2" id="timeinicio">
                                <input name="_1_<?= $_acao ?>_evento_iniciohms" 
                                    type="time" size="15"
                                    value="<?= $_1_u_evento_iniciohms ?>">
                            </div>

                            <div class="col-md-2">Fim:</div>

                            <div class="col-md-2" id="datafim">
                                <input name="_1_<?= $_acao ?>_evento_fim" 
                                    class="calendario" 
                                    type="text" 
                                    size="15"
                                    value="<?= $_1_u_evento_fim ?>" vnulo>
                            </div>

                            <div class="col-md-2" id="timefim">
                                <input name="_1_<?= $_acao ?>_evento_fimhms" 
                                    type="time" 
                                    size="15"
                                    value="<?= $_1_u_evento_fimhms ?>">
                            </div>
                        </div>

                        <div class="row" id="rowRepetition">

                            <div class="col-md-2"></div>
                            <div class="col-md-2 pointer">
                                <a id="diainteirolink" name="" type="text" value="">Dia Inteiro</a>
                            </div>
                            <div class="col-md-2">
                                <input id="diainteirocheckbox" type="checkbox">
                            </div>

                            <div class="col-md-2"></div>
                            <div class="col-md-2 pointer">
                                <a id="repetirlink" name="" type="text" value="">Repetir Evento</a>
                            </div>
                            <div class="col-md-2">
                                <input id="repetircheckbox" type="checkbox">
                            </div>
                        </div>

                        <div id="divrepetir">

                            <div class="row" style="margin-left: -15px;">

                                <div class="col-md-2">Periodicidade:</div>
                                <div class="col-md-4">
                                    <select name="_1_<?= $_acao ?>_evento_periodicidade">
                                        <? fillselect("SELECT 'DIARIO','Diario' UNION SELECT 'SEMANAL','Semanal' UNION SELECT 'MENSAL','Mensal' UNION SELECT 'BIMESTRAL','Bimestral' UNION SELECT 'TRIMESTRAL','Trimestral' UNION SELECT 'SEMESTRAL','Semestral' UNION SELECT 'ANUAL','Anual' UNION SELECT 'BIANUAL','Bianual' UNION SELECT 'TRIANUAL','Trianual'", $_1_u_evento_periodicidade); ?>
                                    </select>
                                </div>

                                <div class="col-md-2">Repetir ação:</div>
                                <div class="col-md-4">
                                    <input name="_1_<?= $_acao ?>_evento_repetirate" class="calendario" type="text" size="8"
                                        value="<?= $_1_u_evento_repetirate ?>">
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-2">Fim de Semana:</div>
                                <div class="col-md-4">
                                    <select name="_1_<?= $_acao ?>_evento_fimsemana">
                                        <? fillselect("SELECT 'Y','Sim' UNION SELECT 'N','Não'", $_1_u_evento_fimsemana); ?>
                                    </select>
                                </div>
<?                     
                                if ($_1_u_evento_periodicidade == 'SEsMANAL') {
?>
                                <div class="col-md-2">Intervalo: <small>(em dias)</small></div>
                                <div class="col-md-4"><input placeholder="Repetir após x dias"
                                        name="_1_<?= $_acao ?>_evento_intervalo" type="text" size="8"
                                        value="<?= $_1_u_evento_intervalo ?>"></div>
<? 
                                }
?>
                            </div>

                            
<?                 
                        if (!empty($_1_u_evento_idevento)) {
?>
                            <div class="row">
                                <div class="col-md-2">Resultados:</div>
                                <div class="col-md-10">
                                    <textarea style="width:101%; resize: none;" name="_1_<?= $_acao ?>_evento_resultado"><?= $_1_u_evento_resultado ?></textarea>
                                </div>
                            </div>
<? 
                        }
?>

                        </div>

                    </div>

                </div>
            </div>

            <div class="adicionaisRow">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-10" id="btnTabelaAdicionais" title="minimizar" onclick="adicionaisBody(this);">Campos adicionais</div>
                        </div>
                    </div>

                    <div id="tabelaAdicionais" class="panel-body">

                        <div class="row col-md-12">
                        
                            <div class="row rowcampos" style="margin-left: 5px;">
                                    
                                <div class="col-md-12 rowcamposevento" style="margin-left: -12px;">
                                    
                                    <div id="gerarRnc">
                                        <?
                                        //if (empty($_1_u_evento_idsgdoc)) {
                                        ?>
                                        <div id="rncempty">
                                            <div class="col-md-2">
                                                <label for="motivornc">RNC</label>
                                            </div>
                                            <div class="col-md-9">
                                                <input type="text" id="motivornc" value="" style="" vnulo>
                                            </div>
                                            <div class="col-md-1">
                                                <i id="criarrnc" class="fa fa-plus-circle verde btn-lg pointer" onclick="fnovornc(<?=$_1_u_evento_idevento?>);" title="Criar novo RNC"></i>
                                            </div>
                                        </div>
                                        <?
                                        //} else {
                                        ?>
                                        <div id="rnc">
                                            <div class="col-md-2">
                                                RNC:
                                            </div>
                                            <div class="col-md-7">
                                                <a title="Documento RNC" href="javascript:janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_evento_idsgdoc?>')">
                                                <?=traduzid("sgdoc","idsgdoc","titulo",$_1_u_evento_idsgdoc);?>
                                                </a>
                                            </div>
                                            <div class="col-md-3">
                                                <a title="Documento RNC" href="javascript:janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_evento_idsgdoc?>')">
                                                <?= $_1_u_evento_idsgdoc?>
                                                </a>
                                            </div>
                                        </div>
                                        <?
                                        //}
                                        ?>
                                    
                                    </div>

                                    <div id="comboPessoas">
                                        <div class="col-md-12">
                                            Pessoas:
                                        </div>
                                        <div class="col-md-12">
                                            <select class="multiselects pessoaSelect" multiple="multiple">
                                            </select>
                                        </div>
                                    </div>

                                    <div id="comboEquipamentos">
                                        <div class="col-md-12">
                                            Tags:
                                        </div>
                                        <div class="col-md-12">
                                            <select class="multiselects tagSelect" multiple="multiple">
                                            </select>
                                        </div>
                                    </div>

                                    <div id="comboDocumentos">
                                        <div class="col-md-12">
                                            Documentos:
                                        </div>
                                        <div class="col-md-12">
                                            <select class="multiselects docSelect" multiple="multiple">
                                            </select>
                                        </div>
                                    </div>

                                    <div id="inputs" class="row" style="margin-left: 0px;"></div>
                                </div>

                                <div class="col-md-6 rowcamposevento" style="margin-left: -12px;">
                                    <div id="texts" class="row" style="margin-left: 10px;"></div>
                                </div>

                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>
            
        </div>

        <div class="col-md-5">

                    <div class="panel panel-default divHistorico" >
                        
                        <div class="panel-heading" style="height:34px">
                            <div class="row">
                                <div class="col-md-10">Comentários</div>                                

                                <div class="col-md-2">
                                    <button id="adicionar"
                                        type="button"
                                        style="margin-top: -2px; margin-right: 10px; display:none"
                                        class="btn btn-success btn-xs fright"
                                        title="Adicionar">
                                        <i class="fa fa-check"></i>Salvar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="panel-body" style="max-height: 100px; min-height: 100px; height: 100px;">
                            <textarea class="caixa" id="obs" name="" style="width: 100%; height: 80px; resize: none;"></textarea>
                        </div>

                        <div class="panel-body">
                            <table class="table table-striped planilha" style="font-size: 10px; word-break: break-word;" 
                                    id="tblHistorico">
                            </table>
                        </div>
                        
                    </div>
					<? if (($_SESSION["SESSAO"]["IDPESSOA"] != '64294')){ 
?>

    <div class="panel panel-default"  >
        <div class="panel-heading" >Participantes
	</div>
        <div class="panel-body"  id="localInfo1"> <? if (empty($_1_u_evento_idevento)){ echo '<p style="color:#aaa;"><i>Crie o evento para adicionar os participantes</i></p>';}else{?>
            <table>
            <tr>
                <td id="tdfuncionario"><input id="pessoavinc" class="compacto" type="text" cbvalue placeholder="Selecione" <? if (empty($_1_u_evento_idevento)){ echo 'disabled="true"';}?>></td>
                <td id="tdsgsetor"><input id="sgsetorvinc" class="compacto" type="text" cbvalue placeholder="Selecione" <? if (empty($_1_u_evento_idevento)){ echo 'disabled="true"';}?>></td>
                <td class="nowrap" style="width: 110px">  
				
                    <div class="btn-group nowrap" role="group" aria-label="..."> 
                        <button onclick="showfuncionario()" type="button" class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright " title="Selecionar Funcionário" style="margin-right: 8px; border-radius: 4px;" <? if (empty($_1_u_evento_idevento)){ echo 'disabled="true"';}?>>&nbsp;</button>		
                        <button onclick="showsgsetor()" type="button" class=" btn btn-default fa fa-users hoverlaranja pointer floatright selecionado" title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;" <? if (empty($_1_u_evento_idevento)){ echo 'disabled="true"';}?>>&nbsp;</button>  
                    </div>
				
                </td>
            </tr>
            </table>
           <? } ?>
            <div class="col-md-12">
                <div class="panel panel-default" style="background:#fff;height: 100%;overflow: auto;">
                    <?=listaPessoa()?>
                </div>
            </div>
        </div>
    </div>

<? }else{ ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Participantes

                          

                            <button id="cancelar"
                                type="button"
                                style="margin-top: -2px;"
                                class="btn btn-danger btn-xs fright"
                                title="Cancelar execução">
                                <i class="fa fa-close"></i>Cancelar execução
                            </button>

                            <button id="concluir"
                                type="button"
                                style="margin-top: -2px;"
                                class="btn btn-success btn-xs fright"
                                title="Concluir">
                                <i class="fa fa-check"></i>Concluir
                            </button>
                
                            <button id="reabrir" 
                                type="button" 
                                style="margin-top: -2px;"
                                class="btn btn-danger btn-xs fright" 
                                title="Reabrir">
                                <i class="fa fa-refresh "></i>Reabrir
                            </button>

                            <!--<button id="finalizar" 
                                type="button" 
                                style="margin-top: -2px;"
                                class="btn btn-success btn-xs fright" 
                                title="Concluir">
                                <i class="fa fa-check "></i>Finalizar
                            </button>-->

                            <button id="executar" 
                                type="button" 
                                style="margin-top: -2px;"
                                class="btn btn-warning btn-xs fright" 
                                title="Executar">
                                <i class="fa fa-gear"></i>Executar
                            </button>

                        </div>

                        <div class="panel-body">
                            <table>
                                <tr id="menuPermissoes">
                                    <td id="tdfuncionario">
                                        <input id="eventoresp" class="compacto" type="text" cbvalue
                                            placeholder="Selecione">
                                    </td>
                                    <td id="tdsgsetor">
                                        <input id="sgsetorvinc" class="compacto" type="text" cbvalue
                                            placeholder="Selecione">
                                    </td>
                                    <td class="nowrap" style="width: 110px">
                                        <div class="btn-group nowrap" role="group" aria-label="...">
                                            <button onclick="showsgsetor()" type="button"
                                                    class=" btn btn-default fa fa-users hoverlaranja pointer floatright selecionado"
                                                    title="Selecionar Setor"
                                                    style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
                                            <button onclick="showfuncionario()" type="button"
                                                class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright"
                                                title="Selecionar Funcionário"
                                                style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
                                            
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table table-striped planilha" id="tblPermissao">
                                
                            </table>
                        </div>
                    </div>

<? } ?>
                    <div class="panel panel-default upload">
                        <div class="panel-heading">Arquivos Anexos</div>
                        <div class="panel-body" style="">
                            <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
                                <i class="fa fa-cloud-upload fonte18"></i>
                            </div>
                        </div>
                    </div>

        </div>
    </div>



    

<?
    if (!empty($_1_u_evento_idevento)) {
?>


<div class="tagRow">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" >
                <div class="row">
                    <div class="col-md-10" id="btnTabelaEquipamento" title="minimizar" onclick="equipamentosBody(this);">Tags</div>
                       
                </div>
            </div>
            <div id="tabelaEquipamento" class="panel-body">
                <div class="row col-md-12">
                    <table class="table table-striped tbTags">
                        <tr>
                            <th>Descrição</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="docRow">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" >
                <div class="row">
                    <div class="col-md-11" id="btnTabelaDocumento" title="minimizar" onclick="documentosBody(this);">Documentos</div>
                        
                </div>
            </div>
            <div id="tabelaDocumento" class="panel-body">
                <div class="row col-md-12">
                    <table class="table table-striped tbDocs">
                        <tr>
                            <th>Descrição</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="pessoaRow">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-10" id="btnTabelaPessoa" title="minimizar" onclick="pessoasBody(this);">Pessoas</div>
                        <span id="btnTabelaPessoa" 
                                class="col-md-1 btn btn-sm btn-secondary size3" 
                                style="float: right; margin-right: 15px;" 
                                title="expandir" onclick="pessoasBody(this);">
                            <i class="fa fa-minus pointer"></i>
                        </span>
                </div>
            </div>
            <div id="tabelaPessoa" class="panel-body">
                <div class="row col-md-12">
                    <table class="table table-striped tbPessoas">
                        <tr>
                            <th>Descrição</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    
<?
        }                
?>

<?
    if (!empty($_1_u_evento_idevento)) {
?>

    <div class="col-md-12">
        <? $tabaud = "evento"; ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row col-md-12">
                    <div class="col-md-2">Criado Por:</div>
                    <div class="col-md-4"><?= ${"_1_u_" . $tabaud . "_criadopor"} ?></div>
                    <div class="col-md-2">Criado Em:</div>
                    <div class="col-md-4"><?= ${"_1_u_" . $tabaud . "_criadoem"} ?></div>
                </div>
                <div class="row col-md-12">
                    <div class="col-md-2">Alterado Por:</div>
                    <div class="col-md-4"><?= ${"_1_u_" . $tabaud . "_alteradopor"} ?></div>
                    <div class="col-md-2">Alterado Em:</div>
                    <div class="col-md-4"><?= ${"_1_u_" . $tabaud . "_alteradoem"} ?></div>
                </div>
            </div>
        </div>
    </div>
<?
    }                

?>

<script language="javascript">


jFuncionario = <?=$jFuncionario?>;

//Autocomplete de Setores vinculados
$("#pessoavinc").autocomplete({
    source: jFuncionario
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            lbItem = item.label;			
            return $('<li>')
                .append('<a>' + lbItem + '</a>')
                .appendTo(ul);
        };
    }
    ,select: function(event, ui){
        CB.post({
            objetos: {
                "_x_i_eventoresp_idevento":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
				,"_x_i_eventoresp_idpessoa":$(":input[name=_1_"+CB.acao+"_evento_idpessoa]").val()
				,"_x_i_eventoresp_idempresa": '1'
				,"_x_i_eventoresp_idobjeto": ui.item.value
				,"_x_i_eventoresp_tipoobjeto": 'pessoa'
				,"_x_i_eventoresp_status": '<?=$tokeninicial;?>'
				,"_x_i_eventoresp_oculto": '0'
				,"_x_i_eventoresp_inseridomanualmente":'S'
            }
            ,parcial: true
        });
    }
});


function retirapessoa(inid){
    CB.post({
        objetos: {
            "_x_d_eventoresp_ideventoresp":inid
        }
        ,parcial: true

    });
}

function retirasgsetor(inid){
    CB.post({
        objetos: {
            "_x_d_eventoresp_ideventoresp":inid
        }
        ,parcial: true
		,refresh: false
		,posPost:function(){
			//console.log('entrei');
			$.ajax({
				type: "post",
				 url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val(),
				success: function(data){
					//console.log(data);
					//alertAzul("Participantes atualizados","",1000);
					location.reload();

				}
			});
		}
    });
}

$('#cbContainer').css('display', 'block');
$('#tdsgsetor').show();
$("#comboPessoas").hide();
$('#tdfuncionario').hide();
$('#menuPermissoes').hide();

//$('#finalizar').hide();

$('.docRow').hide();
$('.tagRow').hide();
$('.pessoaRow').hide();

jsonMotivo      = <?= $jMotivo?>;
JSgdoc          = <?= $jSgdoc ?>;
JEquipamento    = <?= $jEquipamento ?>;
JSfornecedor    = <?= $jSfornecedor ?>;
jFuncionario    = <?= $jFuncionario ?>;
jSgsetorvinc    = <?= $jSgsetorvinc ?>;
jUserStatus     = <?= $jUserStatus ?>;

tags            = <?= $jTag ?>;
acao            = "<?= $_acao ?>";
pessoas         = <?= $jPessoa ?>;
idevento        = <?= $idevento ?>;
subevento       = "<?= $subevento ?>";
documentos      = <?= $jDocumento ?>;
modulo          = "<?= $_1_u_evento_modulo?>";
idmodulo        = "<?= $_1_u_evento_idmodulo?>";
nomemodulo      = "";
iniciohms       = "<?= $_1_u_evento_iniciohms ?>";
fimsemana       = "<?= $_1_u_evento_fimsemana ?>";
repetirate      = "<?= $_1_u_evento_repetirate ?>";
periodicidade   = "<?= $_1_u_evento_periodicidade ?>";
calendario      = "<?= $calendario ?>";
idsgdoc         = "<?= $_1_u_evento_idsgdoc ?>";

statusevento 	= "<?= $statusevento ?>";

if ("<?= ($modulo)?>") {
    modulo = "<?= ($modulo)?>";
}

if ("<?= ($idmodulo)?>") {
    idmodulo = "<?= ($idmodulo)?>";
}

if ("<?= ($nomemodulo)?>") {
    nomemodulo = "<?= ($nomemodulo)?>";
}

var idPessoa    = <?= $idPessoa ?>;
var nomePessoa  = "<?= $nomePessoa ?>";

var camposTag   = [];
var camposDoc   = [];
var camposPessoa= [];
var count       = 0;
/*
var jsonconfig  = {
    rnc: false,
    alerta: false,
    arquivo: false,
    assinar: false,
    calendario: false,
    permissoes: {
        setores: [],
        funcionarios: []
    },
    tags: [],
    pessoas: [],
    documentos: [],
    personalizados: []
};
*/
var jsonresultado = {
    tags: [],
    pessoas: [],
    documentos: [],
    tagsValores: [],
    pessoasValores: [],
    documentosValores: [],
    personalizados: []
};


if (subevento) {

    $("#rowRepetition").hide();
    //$("#idevento").prop("readonly", "readonly");
    $("#selectTipoEvento").prop("disabled", "disabled");
    $("input[name=_1_u_evento_fim]").prop("readonly", "readonly");
    $("input[name=_1_u_evento_inicio]").prop("readonly", "readonly");
	 $("input[name=_1_u_evento_evento]").prop("readonly", "readonly");
    $("textarea[name=_1_u_evento_descricao]").prop("readonly", "readonly");
	
    //$(".rowcampos").remove();
    $(".calendario").removeClass('calendario');

}

$('.multiselects').selectpicker({
    liveSearch: true
});

$("#divrepetir").hide();
$("#comboPessoas").hide();
$("#comboDocumentos").hide();
$("#comboEquipamentos").hide();

function isDono() {
	//console.log('isdono'+idPessoa+' '+$('#idpessoa').val());
    if (idPessoa == $('#idpessoa').val()) {
        return true;
    }
    return false;
}
/*
function criaPermissao(item, title, modulo, status) {

    let span = '<span class="circle button-blue"></span>';
    
    if (status == 'warning') {
        span = '<span class="circle button-orange"></span>';
    }

    if (status == 'danger') {
        span = '<span class="circle button-red"></span>';
    }

    if (status == 'success') {
        span = '<span class="circle button-green"></span>';
    }

    let iconVisualizado = '';

    if (item.visualizado == 1) {
        iconVisualizado = '<i class="fa fa-check" style="color: #017a99;"></i>';
    }

    let trash;

    if (item.inseridomanualmente == "N") {

        trash ='<td align="center">\
                    <a class="fa fa-ban fa-1x cinzaclaro hovercinza btn-sm pointer ui-droppable"></a>\
                </td>';
    } else {

        trash ='<td align="center">\
                    <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable"\
                        onclick="removePermissao(this, '+item.value+', \''+modulo+'\')" title="Excluir"></a>\
                </td>';
    }

    let trashOption =   '<td align="center">\
                            <a class="fa fa-ban fa-1x cinzaclaro hovercinza btn-sm pointer ui-droppable"></a>\
                        </td>';
    
    if (isDono() && item.value != idPessoa) {
        trashOption = trash;
    }

    if (!(jsonconfig.statuses && jsonconfig.statuses[0])) {
            $('#executar').hide();
            return '<tr id="'+modulo+item.value+'">\
                <td style="min-width: 10px;" id="statuses"></td>\
                <td>'+item.label+'</td>\
                <td>\
                </td>'+trashOption+'\
            </tr>';
    }
    
    return '<tr id="'+modulo+item.value+'">\
                <td style="max-width: 27px !important;" id="statuses">\
                    '+span+'\
                    '+iconVisualizado+'\
                </td>\
                <td>'+item.label+'</td>\
                '+trashOption+'\
            </tr>';

}
*/
function criaHistorico(item) {
	
	const HOUR = 1000 * 60 * 60;
    const anHourAgo = moment(item.data).add(68, 'minutes')

	if ('<?=$_SESSION["SESSAO"]["NOMECURTO"];?>' == item.nome && moment(Date.now()) < anHourAgo){
		var dl = '<i class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"excluiComentario(\''+item.data+'\')\" title="Excluir!"></i>';
	}else{
		var dl = '';
	}
  
    return '<tr>\
                <td style="line-height: 14px; padding: 8px; font-size: 11px;color:#666;">'+moment(item.data).format("DD/MM/YY HH:mm")+' - '+item.nome+': '+item.obs+'</td><td style="w"> '+dl+'</td>\
            </tr>';
}
/*
$("#eventoresp").autocomplete({
    source: jFuncionario,
    delay: 0,
    create: function() {
        $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
            lbItem = item.label;
            
            return $('<li>')
                .append('<a>' + lbItem + '</a>')
                .appendTo(ul);
        };
    },
    select: function(event, funcionario) {

        if (funcionario && 
            funcionario.item !== undefined &&
            funcionario.item.value &&
            funcionario.item.label) {
            
            let i = 0, len = jsonconfig.permissoes.funcionarios.length, exists = false;

            for (i = 0; i < len; i++) {
                
                if (jsonconfig.permissoes && 
                    jsonconfig.permissoes.funcionarios && 
                    jsonconfig.permissoes.funcionarios[i] && 
                    jsonconfig.permissoes.funcionarios[i].value == funcionario.item.value) {
                    exists = true;
                    break;
                }
            }
        
            if(!exists) {

                let novoFuncionario = {
                    "value": funcionario.item.value,
                    "label": funcionario.item.label,
                    "tipo": 'pessoa',
                }
                
                jsonconfig.permissoes.funcionarios.push(novoFuncionario);
                let row = criaPermissao(novoFuncionario, 'Funcionário', 'pessoa', 'info');
                $("#jsonconfig").val("" + JSON.stringify(jsonconfig));
                $('#tblPermissao').append(row);
                if (acao === 'u') {
                    CB.post();
                }
            }

        }
        
        $('#eventoresp').val("");

    }
});
*//*
$("#sgsetorvinc").autocomplete({
    source: jSgsetorvinc,
    delay: 0,
    create: function() {
        $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
            lbItem = item.label;
            return $('<li>')
                .append('<a>' + lbItem + '</a>')
                .appendTo(ul);
        };
    },
    select: function(event, setor) {
        
        if (setor && 
            setor.item !== undefined &&
            setor.item.value &&
            setor.item.label) {
            
            let i = 0, len = jsonconfig.permissoes.setores.length, exists = false;

            for (i = 0; i < len; i++) {
                
                if (jsonconfig.permissoes && 
                    jsonconfig.permissoes.setores && 
                    jsonconfig.permissoes.setores[i] && 
                    jsonconfig.permissoes.setores[i].value == setor.item.value) {
                    exists = true;
                    break;
                }
            }
        
            if (!exists) {
                
                let novoSetor = {
                    "value": setor.item.value,
                    "label": setor.item.label,
                    "tipo": 'setor',
                }

                jsonconfig.permissoes.setores.push(novoSetor);
                let row = criaPermissao(novoSetor, 'Setor', 'sgsetor', 'info');
                $("#jsonconfig").val("" + JSON.stringify(jsonconfig));
                $('#tblPermissao').append(row);
                if (acao === 'u') {
                    CB.post();
                }
            }

        }
        
        $('#sgsetorvinc').val("");
    }
});
*/

function showfuncionario(){
    $('#tdsgsetor').hide();
    $('#tdfuncionario').show(); 
}
function showsgsetor(){
    $('#tdsgsetor').show();
    $('#tdfuncionario').hide();      
}

$("#sgsetorvinc").autocomplete({
	source: jSgsetorvinc
	,delay: 0
	,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);

            };
	}
        ,select: function(event, ui){
            CB.post({
                objetos: {
                 "_x_i_eventoresp_idevento":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
				,"_x_i_eventoresp_idempresa": '1'
				,"_x_i_eventoresp_idobjeto": ui.item.value
				,"_x_i_eventoresp_tipoobjeto": 'imgrupo'
				,"_x_i_eventoresp_inseridomanualmente":'S'
				,"_x_i_eventoresp_status": '<?=$tokeninicial;?>'
                }
				,parcial: true
				,refresh: false
				,posPost:function(){
			//console.log('entrei');
					$.ajax({
						type: "post",
						 url: "ajax/evento.php?vopcao=atualizaparticipantes&videvento="+$("[name=_1_u_evento_idevento]").val(),
						success: function(data){
							console.log(data);
							alertAzul("Participantes atualizados","",1000);
							//alert();
							location.reload();

						}
					});
				}
            });
        }
	
});

$("#fornecedor").autocomplete({
    source: JSfornecedor,
    delay: 0,
    create: function() {
        $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
            lbItem = item.label;
            return $('<li>')
                .append('<a>' + lbItem + '</a>')
                .appendTo(ul);
        };
    },
    select: function(event, ui) {
        CB.post({
            objetos: {
                "_x_i_eventoobj_idevento": $(":input[name=_1_" + CB.acao + "_evento_idevento]")
                .val(),
                "_x_i_eventoobj_objeto": 'PESSOA',
                "_x_i_eventoobj_idobjeto": ui.item.value
            },
            parcial: true
        });
    }
});

$("#sgdoc").autocomplete({
    source: JSgdoc,
    delay: 0,
    create: function() {
        $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
            lbItem = item.label;
            return $('<li>')
                .append('<a>' + lbItem + '</a>')
                .appendTo(ul);
        };
    },
    select: function(event, ui) {
        CB.post({
            objetos: {
                "_x_i_eventoobj_idevento": $(":input[name=_1_" + CB.acao + "_evento_idevento]")
                .val(),
                "_x_i_eventoobj_objeto": 'SGDOC',
                "_x_i_eventoobj_idobjeto": ui.item.value
            },
            parcial: true
        });
    }
});

$("#sgdoclayout").autocomplete({
    source: JSgdoc,
    delay: 0,
    create: function() {
        $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
            lbItem = item.label;
            return $('<li>')
                .append('<a>' + lbItem + '</a>')
                .appendTo(ul);
        };
    }
});

$("#equipamento").autocomplete({
    source: JEquipamento,
    delay: 0,
    create: function() {
        $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
            lbItem = item.label;
            return $('<li>')
                .append('<a>' + lbItem + '</a>')
                .appendTo(ul);
        };
    },
    select: function(event, ui) {
        CB.post({
            objetos: {
                "_x_i_eventoobj_idevento": $(":input[name=_1_" + CB.acao + "_evento_idevento]")
                .val(),
                "_x_i_eventoobj_objeto": 'EQUIPAMENTO',
                "_x_i_eventoobj_idobjeto": ui.item.value
            },
            parcial: true
        });
    }
});


$(".oPopover").webuiPopover({
	trigger: "hover",
	placement: "left",
	delay: {
        show: 300,
        hide: 0
    }
});



function removePermissao(elemento, id, modulo) {
    
    let removed = false;

    if (modulo == 'pessoa' && id != idPessoa) {

        let i = 0, len = jsonconfig.permissoes.funcionarios.length;
        
        for (i = 0; i < len; i++) {

            if (jsonconfig.permissoes &&
                jsonconfig.permissoes.funcionarios &&
                jsonconfig.permissoes.funcionarios[i] &&
                jsonconfig.permissoes.funcionarios[i].value == id) {
                
                jsonconfig.permissoes.funcionarios.splice(i, 1);
                removed = true;
                break;
            }
        }

    } else {
        
        let i = 0, len = jsonconfig.permissoes.setores.length;

        for (i; i < len; i++) {
            
            if (jsonconfig.permissoes &&
                jsonconfig.permissoes.setores &&
                jsonconfig.permissoes.setores[i] &&
                jsonconfig.permissoes.setores[i].value == id) {
                
                jsonconfig.permissoes.setores.splice(i, 1);
                removed = true;
                break;
            }
        }
    
    }

    if (removed) {
        $(elemento).closest('tr').remove();
        $("#jsonconfig").val("" + JSON.stringify(jsonconfig));
    }

    if (acao === 'u') {
        CB.post();
    }
    
}

if ($("[name=_1_u_evento_idevento]").val()) {
   
    $(".cbupload").dropzone({
        idObjeto: $("[name=_1_u_evento_idevento]").val(),
        tipoObjeto: 'evento',
	    tipoArquivo: 'ANEXO'
    });
}

$ini = $("[name=_1_" + CB.acao + "_evento_inicio]");
$fim = $("[name=_1_" + CB.acao + "_evento_fim]");
$repetir = $("[name=_1_" + CB.acao + "_evento_repetirate]");
$inihms = $("[name=_1_" + CB.acao + "_evento_iniciohms]");
$fimhms = $("[name=_1_" + CB.acao + "_evento_fimhms]");
$prazo = $("[name=_1_" + CB.acao + "_evento_prazo]");

$inihms.on('focusout', function() {
	console.log(1);
    var iniformat = $ini.val().split("/").reverse().join("-");
    var fimformat = $fim.val().split("/").reverse().join("-");

    if (iniformat >= fimformat) {

        $novo = $inihms.val().split(':');
        $novo[0] = parseInt($novo[0]) + 1;

        if ($novo[0] == 24) {
            $novo[0] = 23;
            $novo[1] = 59;
        }

        $fimhms.val($novo[0] + ':' + $novo[1]);
    }
});

$ini.on("apply.daterangepicker", function(e, picker) {

    $tempini = $("[name=_1_" + CB.acao + "_evento_inicio]");
    $tempini.val(picker.startDate.format("DD/MM/YYYY"));

    var iniformat = $tempini.val().split("/").reverse().join("-");
    var fimformat = $fim.val().split("/").reverse().join("-");
    var repetirformat = $repetir.val().split("/").reverse().join("-");
	console.log($tempini.val());
    if (iniformat > fimformat) {

        $fim.val(picker.startDate.format("DD/MM/YYYY"));
	console.log($fim.val());
        if ($inihms.val() >= $fimhms.val()) {

            $novo = $inihms.val().split(':');
            $novo[0] = parseInt($novo[0]) + 1;

            if ($novo[0] == 24) {
                $novo[0] = 23;
                $novo[1] = 59;
            }

            $fimhms.val($novo[0] + ':' + $novo[1]);
        }
    }

    if (iniformat > repetirformat && $('#repetircheckbox').prop('checked')) {
        $repetir.val(picker.startDate.format("DD/MM/YYYY")); 
    }

});

$fimhms.on('change', function() {
	
    var iniformat = $ini.val().split("/").reverse().join("-");
    var fimformat = $fim.val().split("/").reverse().join("-");
	$inihms.val(moment($inihms.val(),"HH:mm:ss").format('HH:mm:ss'));
	$fimhms.val(moment($fimhms.val(),"HH:mm:ss").format('HH:mm:ss'));
	

    if (iniformat+$inihms.val() < fimformat) {
		
        $novo = $inihms.val().split(':');
        $novo[0] = parseInt($novo[0]) +1 ;

        if ($novo[0] == 24) {
            $novo[0] = 23;
            $novo[1] = 59;
        }

        $fimhms.val($novo[0] + ':' + $novo[1] + ':00');
		console.log($fimhms.val());
    }
});

$fim.on("apply.daterangepicker", function(e, picker) {
	console.log(3);
    $temp = $("[name=_1_" + CB.acao + "_evento_fim]");
    $temp = $temp.val(picker.startDate.format("DD/MM/YYYY"));

    if ($temp.val() > $repetir.val()) {
        $repetir.val(picker.startDate.format("DD/MM/YYYY"));
    }

    if ($temp.val() == $ini.val() && $inihms > $fimhms) {

        $novo = $inihms.val().split(':');
        $novo[0] = parseInt($novo[0]) + 1;

        if ($novo[0] == 24) {
            $novo[0] = 23;
            $novo[1] = 59;
        }

        $fimhms.val($novo[0] + ':' + $novo[1]);
    }

});



$prazo.on("apply.daterangepicker", function(e, picker) {
	$ini.val(picker.startDate.format("DD/MM/YYYY")); 
	$fim.val(picker.startDate.format("DD/MM/YYYY"));
});



function loadTags(idTagTipo) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            type: "get",
            url: "ajax/evento.php?vopcao=tags&vidtagtipo=" + idTagTipo,
            success: function(data) {
                
                let dataformat = data.replace(/\\/g, '');
                let tags = JSON.parse(dataformat);

                if (tags !== undefined) {
                    resolve(tags);
                } else {
                    resolve([]);
                }
            }
        });
    });
}

function loadHistorico(idEvento) {
	
	hist = $("[name=jsonhistorico]").val();
    return new Promise(function(resolve, reject) {
        
                let dataformat = hist.replace(/\\/g, '');
                let comentarios = JSON.parse(dataformat);
                
                let historicos = comentarios.historico;

                if (historicos !== undefined) {
                    resolve(historicos);
                } else {
                    resolve([]);
                }
            
    });
}

function loadDocumentos(idSgDocTipo) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            type: "get",
            url: "ajax/evento.php?vopcao=documentos&vidsgdoctipo=" + idSgDocTipo,
            success: function(data) {
                if (data) {

                    let dataformat = data.replace(/\\/g, '');
                    let docs = JSON.parse(data);

                    if (docs !== undefined) {
                        resolve(docs);
                    } else {
                        resolve([]);
                    }
                }
            },
            error: function(objxml) {
                document.body.style.cursor = "default";
                alert('Erro: ' + objxml.status);
            }
        });
    });
}

function loadPermissoes(idEvento) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            type: "get",
            url: "ajax/eventoresp.php?videvento=" + idEvento,
            success: function(data) {
                
                let dataformat = data.replace(/\\/g, '');
                let permissoes = JSON.parse(data);
           
                if (permissoes !== undefined) {
                    resolve(permissoes);
                } else {
                    resolve([]);
                }
            }
        });
    });
}

function loadPessoas(idTipoPessoa) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            type: "get",
            url: "ajax/evento.php?vopcao=pessoas&vidtipopessoa=" + idTipoPessoa,
            success: function(data) {

                let dataformat = data.replace(/\\/g, '');
                let pessoas = JSON.parse(data);

                if (pessoas !== undefined) {
                    resolve(pessoas);
                } else {
                    reject("error_pessoas");
                }
            }
        });
    });
}

function getHash(str, idx, algo = "SHA-256") {

    let strBuf = new TextEncoder('utf-8').encode(str);

    return crypto.subtle.digest(algo, strBuf).then(hash => {

        window.hash = hash;

        let result = '';
        const view = new DataView(hash);

        for (let i = 0; i < hash.byteLength; i += 4) {
            result += ('00000000' + view.getUint32(i).toString(16)).slice(-8);
        }

        return [result, idx];

    }, (error) => {
        console.log(error);
    });
}

function criaInput(titulo, type, hashId) {

    let input = '<div class="row">\
                    <div class="col-md-12">\
                        ' + titulo + ':\
                    </div>\
                    <div class="col-md-10">\
                        <input style="width: fit-content;" onkeyup="setResultado(this);" id="' + hashId + '" type="' + type + '"></input>\
                    </div>\
                </div>';

    return input;
}

function criaSelect(titulo, options, hashId) {

    let opcoes = '<option selected="selected" disabled="disabled" value="">Selecionar</option>';

    let len = options.length, i = 0;

    for (i = 0; i < len; i++) {

        opcoes += '<option value="' + options[i].nome.toString() + '">' + options[i].nome.toString() + '</option>';

        $("#comboDocumentos > div > div > select").
            append("<option value='" + documentos[i].idsgdoc + "'>" + documentos[i].titulo + '</option>');
    }

    let input = '<div class="col-md-6">\
                    <div class="col-md-12" style="padding:0px;">\
                        ' + titulo + ':\
                    </div>\
                    <div class="col-md-12" style="padding:0px;">\
                        <select style="width: 100%;" onchange="setResultado(this);" id="' + hashId + '">' + opcoes + '</select>\
                    </div>\
                </div>';
    return input;
}

function criaTextarea(titulo, hashId) {

    let textarea = '<div class="row" style="margin-left:-7px;">\
                        <div class="col-md-3">\
                            ' + titulo + ':\
                        </div>\
                        <div class="col-md-10">\
                            <textarea onkeyup="setResultado(this);" id="' + hashId + '" style="height: 60px; width:100%; resize: none;"></textarea>\
                        </div>\
                    </div>';
    return textarea;
}

$("#adicionar").click(function(event) {

    var obs = $("#obs").val();

    $.ajax({
        type: "get",
        url: "ajax/eventoresp.php?vobs="+obs+"&vopcao=adicionar&vstatus=&videvento="+idevento,
        success: function(data) {

            let dataformat = data.replace(/\\/g, '');
            let permissions = JSON.parse(data);
            
            $("#status").val(permissions[0].statusevento);
            atualizaStatusEvento(permissions[0].statusevento);
           // atualizaPermissoes(permissions);
                    
            $("#obs").val('');
            
        },
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }
    });


});
function ocultaEvento(event) {

    if ($("#ocultar").text() == "Desocultar") {

        fetch('ajax/eventoresp.php?vopcao=desocultar&videvento='+idevento).then(function(response) {
            return response.json();
        }).then(function(data) {

            if (data && data.status && data.status === "success") {
                alertSalvo("Evento desocultado!");
                $("#ocultar").html('<i class="fa fa-eye-slash"></i>Ocultar');

            }

        }).catch(function(error) {
            console.log(error);
        });
        
    } else {

        fetch('ajax/eventoresp.php?vopcao=ocultar&videvento='+idevento).then(function(response) {
            return response.json();
        }).then(function(data) {

            if (data && data.status && data.status === "success") {
                alertSalvo("Evento ocultado!");
                $("#ocultar").html('<i class="fa fa-eye"></i>Desocultar');
            }

        }).catch(function(error) {
            console.log(error);
        });
        
    }
   
}
if (isDono()) {
$("#repetirlink").click(function(event) {

    if ($("#repetircheckbox").is(":checked") === true) {
        $("#repetircheckbox").removeAttr("checked");
        $("#repetircheckbox").change();
    } else {
        $("#repetircheckbox").prop("checked", true);
        $("#repetircheckbox").change();
    }
});

$("#repetircheckbox").change(function(event) {

    if ($("#repetircheckbox").is(":checked") === true) {

        $("#divrepetir").show();
        $("[name=_1_" + CB.acao + "_evento_periodicidade]").val("DIARIO");
        $("[name=_1_" + CB.acao + "_evento_fimsemana]").val("N");
        var now = new Date();
        var today = now.getDate() + '/' + (now.getMonth() + 1) + '/' + now.getFullYear();
        $("[name=_1_" + CB.acao + "_evento_repetirate]").val($("[name=_1_" + CB.acao + "_evento_inicio]").val());

    } else {

        $("#divrepetir").hide();
        $("[name=_1_" + CB.acao + "_evento_periodicidade]").val("");
        $("[name=_1_" + CB.acao + "_evento_repetirate]").val("");
        $("[name=_1_" + CB.acao + "_evento_fimsemana]").val("");

    }
});

$("#diainteirolink").click(function(event) {

    if ($("#diainteirocheckbox").is(":checked") === true) {
        $("#diainteirocheckbox").removeAttr("checked");
        $("#diainteirocheckbox").change();
    } else {
        $("#diainteirocheckbox").prop("checked", true);
        $("#diainteirocheckbox").change();
    }

});

// Ajusta layout caso evento seja por dia 
// Atualiza o valor da hora para null caso diainteiro==true
// e proximo intervalo de 30min exatos caso dia inteiro==false
$("#diainteirocheckbox").change(function(event) {

    if ($("#diainteirocheckbox").is(":checked") === false) {

        $("#timeinicio").show();
        $("#timefim").show();
        $("#datainicio").removeAttr("class", "col-md-4");
        $("#datafim").removeAttr("checked", "col-md-4");
        $("#datainicio").attr("class", "col-md-2");
        $("#datafim").attr("class", "col-md-2");

        let date = new Date();

        $("[name=_1_" + CB.acao + "_evento_iniciohms]").val(
            roundUpToMinuteInterval(date.getHours(), date.getMinutes()));

        $("[name=_1_" + CB.acao + "_evento_fimhms]").val(
            roundUpToMinuteInterval(date.getHours() + 1, date.getMinutes()));

    } else {

        $("#timeinicio").hide();
        $("#timefim").hide();

        $("#datainicio").removeAttr("class", "col-md-2");
        $("#datafim").removeAttr("checked", "col-md-2");
        $("#datainicio").attr("class", "col-md-4");
        $("#datafim").attr("class", "col-md-4");

        $("[name=_1_" + CB.acao + "_evento_iniciohms]").val("00:00:12");
        $("[name=_1_" + CB.acao + "_evento_fimhms]").val("00:00:12");
    }

});
}	
//recebe a hora e o minuto e devolve o proximo intervalo
function roundUpToMinuteInterval(hora, minuto, minuteInterval = 30) {

    if (minuto !== 0 && minuto !== 30) {
        if (minuto < 30) {
            minuto += (minuteInterval - minuto % 30);
        } else if (hora !== 23) {
            minuto = 0;
            hora++;
        } else {
            hora = 0;
            minuto = 0;
        }
    }

    let time = (hora < 10 ? "0" + hora : hora) + ':' + (minuto < 10 ? "0" + minuto : minuto);

    return time;
}

function criaCamposTabela(campo, objid, obj) {

    if (campo.tipo === 'numerico') {
        return '<td><input onkeyup="setResultado(this);" data-obj='+obj+' data-objid="'+objid+'" id="'+campo.id+'" data-campoid="'+campo.id+'xx'+objid+'" type="number"></input></td>';
    } else {
        if (campo.tipo === 'input') {
            return '<td><input onkeyup="setResultado(this);" data-obj='+obj+' data-objid="'+objid+'" id="'+campo.id+'" data-campoid="'+campo.id+'xx'+objid+'" type="text"></input></td>';
        } else {
            if (campo.tipo === 'data') {
                return '<td><input onkeyup="setResultado(this);" data-obj='+obj+' data-objid="'+objid+'" id="'+campo.id+'" data-campoid="'+campo.id+'xx'+objid+'" type="date"></input></td>';
            } else {
                if (campo.tipo === 'hora') {
                    return '<td><input onkeyup="setResultado(this);" data-obj='+obj+' data-objid="'+objid+'" id="'+campo.id+'" data-campoid="'+campo.id+'xx'+objid+'" type="time"></input></td>';
                } else {

                    if (campo.tipo === 'selecionavel') {

                        let options = [];
                        let len = jsonconfig.personalizados.length;
                        let opcoes = '<option selected="selected" disabled="disabled" value="">Selecionar</option>';

                        for (i = 0; i < len; i++) {

                            let o = jsonconfig.personalizados[i];

                            if (o.id == campo.id) {
                                options = o.options;
                                break;
                            }
                            
                        }

                        len = options.length, i = 0;

                        for (i = 0; i < len; i++) {
                            opcoes += '<option value="' + options[i].nome.toString() + '">' + options[i].nome.toString() + '</option>';
                        }

                        return '<td> <select onchange="setResultado(this);" data-obj='+obj+' data-objid="'+objid+'" id="'+campo.id+'" data-campoid="'+campo.id+'xx'+objid+'">' + opcoes + '</select></td>';
                        
                    } else {
                        if (campo.tipo === 'checkbox') {
                            return '<td><input onkeyup="setResultado(this);" data-obj='+obj+' data-objid="'+objid+'" id="'+campo.id+'" data-campoid="'+campo.id+'xx'+objid+'" type="checkbox"></input></td>';
                        } else {
                            if (campo.tipo === 'textarea') {
                                return '<td><textarea onkeyup="setResultado(this);" data-obj='+obj+' data-objid="'+objid+'" id="'+campo.id+'" data-campoid="'+campo.id+'xx'+objid+'" style="width:100%; height: 60px;"></textarea></td>';
                            }
                        }
                    }
                }
            }
        }
    }

}

function fnovornc(idevento) {
    
    let titulo              = $("#motivornc").val();
    let idsgdocdocumento    = $("#motivornc").attr("cbvalue");

    $.ajax({
        type: "get",
        url: "ajax/evento.php?vopcao=rnc&vtitulo=" + titulo + "&vidsgdocdocumento=" + idsgdocdocumento + "&videvento=" + idevento,
        success: function(data) {
            
            let resultRnc = JSON.parse(data);
            
            $("#rnc").show();
            $("#rncempty").hide();

            $("#rnc > div.col-md-7 > a").attr("href", "javascript:janelamodal('?_modulo=documento&_acao=u&idsgdoc="+resultRnc.lastinsert+"')");
            $("#rnc > div.col-md-7 > a").append(resultRnc.titulo);

            $("#rnc > div.col-md-3 > a").attr("href", "javascript:janelamodal('?_modulo=documento&_acao=u&idsgdoc="+resultRnc.lastinsert+"')");
            $("#rnc > div.col-md-3 > a").append(resultRnc.lastinsert);

        },
        error: function(objxml) {
            console.log("error");
            $("#rnc").hide();
            $("#rncempty").show();
        }
    });
    
  
}

//autocomplete de motivo
$("#motivornc").autocomplete({
    source: jsonMotivo
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }  
});


function excluiComentario(datahora){
	 $.ajax({
        type: "get",
        url: "ajax/eventoresp.php?vopcao=excluicomentario&vdatahora="+datahora+"&videvento="+idevento,
        success: function(data) {
				alertAtencao('Comentário Removido');
				CB.go('idevento='+idevento);
		},
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }	
			
			});
}

function AlteraStatusEventoResp(token){
	status = token.attributes.token.nodeValue;
	assina = token.attributes.assina.nodeValue;
	var obs = $("#obs").val();
	var versao = $("#versao").val();
	//console.log(token.attributes.token.nodeValue);
	//debugger;
    $.ajax({
        type: "get",
        url: "ajax/eventoresp.php?vobs="+obs+"&vopcao=change&vstatus="+status+"&vassina="+assina+"&videvento="+idevento+"&vversao="+versao,
        success: function(data) {
			if (data == 'pendente'){
				alertAtencao('Assinatura pendente');
			}else{
            let dataformat = data.replace(/\\/g, '');
            let permissions = JSON.parse(data);

            $("#status").val(permissions[0].statusevento);
			CB.go('idevento='+idevento);
            //atualizaStatusEvento(permissions[0].statusevento);
            //atualizaPermissoes(permissions);
            //window.location.reload();
			}
        },
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }
    });
}
function montaBotao(a){
	console.log('montaqui '+a);
	statuses = JSON.parse($("#jsonconfig").val()).statuses;
	console.log(statuses);
	$.each(statuses, function(i, v) {
		
		console.log(v.token.toUpperCase()+' * -- * '+a.toUpperCase());

		if (v.token.toUpperCase() == a.toUpperCase()) {
			if (v.color == '#f5f5f5'){
				v.color = '#ccc';
			}
			//	alert(a);
			console.log('is dono:'+v.dono);
			if (v.dono == true){
				if (isDono()){
					$( "#listabotoes" ).after( '<button onclick="AlteraStatusEventoResp(this)" assina="'+v.assina+'" token="'+v.token+'" type="button" style="margin-top: -2px; display: block;color:#fff;background:'+v.color+'" class="btn btn-xs fright"><i class="fa fa-refresh"></i>'+v.status+'</button>' );
				}
			}else if (v.ndono == true){
				if (!isDono()){
					$( "#listabotoes" ).after( '<button onclick="AlteraStatusEventoResp(this)" assina="'+v.assina+'" token="'+v.token+'" type="button" style="margin-top: -2px; display: block;color:#fff;background:'+v.color+'" class="btn btn-xs fright"><i class="fa fa-refresh"></i>'+v.status+'</button>' );
				}
			}else{
				$( "#listabotoes" ).after( '<button onclick="AlteraStatusEventoResp(this)" assina="'+v.assina+'" token="'+v.token+'" type="button" style="margin-top: -2px; display: block;color:#fff;background:'+v.color+'" class="btn btn-xs fright"><i class="fa fa-refresh"></i>'+v.status+'</button>' );
			}
			

			
		}
		
		
	});
	
	
	
	return;
	
	//
}
function criaPersonalizados(config) {

    if (config !== undefined) {

        jsonconfig = config;

        if (acao !== 'u') {
            jsonresultado.personalizados = [];
        }

       

        let loadResultado = '';

        if ($("#jsonresultado").val()) {
            loadResultado = JSON.parse($("#jsonresultado").val());
        }

        $("#jsonconfig").val("" + JSON.stringify(jsonconfig));

        $("#jsonresultado").val("" + JSON.stringify(jsonresultado));

        if (config.arquivo) {
            $('.upload').show();
        } else {
            $('.observacoes').removeClass('col-md-6');
            $('.observacoes').addClass('col-md-12');
        }
		
		if ($("[name=_1_u_evento_idevento]").val()){
		statuses = JSON.parse($("#jsonconfig").val()).statuses;


		if(statuses!== undefined) {
			$.each(statuses, function(i, v) {
				if($("#statusresp").val() !== undefined) {
					console.log(v.token.toUpperCase()+' <config -- resp> '+$("#statusresp").val().toUpperCase());
					if (v.token.toUpperCase() == $("#statusresp").val().toUpperCase()) {
						$.each(v.proxes, function(a, b) {
							montaBotao(a);
						});
					}
				}
			});
		}
		
		$( "#listabotoes" ).after( '<button  onclick="ocultaEvento(this)" id="ocultar" type="button" style="margin-top: -2px; display: none;" class="btn btn-secondary btn-xs fright" title="Ocultar"><i class="fa fa-eye"></i>Ocultar</button>' );
		}
		
        if (config.tags !== undefined && config.tags.length > 0) {

            loadTags(config.tags).then((tags) => {

                let len = tags.length,
                    i = 0;

                for (i = 0; i < len; i++) {
                    $("#comboEquipamentos > div > div > select").append(
                        "<option value='" + tags[i].idtag +"'>" + tags[i].tag + " - " + tags[i].descricao + '</option>');
                }

                $("#comboEquipamentos > div > div > select").selectpicker("refresh");
                $("#comboEquipamentos").show();

                if (acao == 'u') {

                    if (typeof(loadResultado) === 'object') {

                        if (loadResultado.tags) {

                            $("#comboEquipamentos > div > div > select").selectpicker('val', loadResultado.tags);
                            $("#comboEquipamentos > div > div > select").selectpicker("render");
                            $(".tagRow").show();
                        }

                        for (i = 0; i < loadResultado.tags.length; i++) {
                            
                            let val = $(".tagSelect option[value='"+loadResultado.tags[i]+"']").val();
                            let nome = $(".tagSelect option[value='"+loadResultado.tags[i]+"']").text();

                            let tds = "";
                            let j = 0;

                            for (j = 0; j < camposTag.length; j++) {

                                if (i == 0) {
                                    $(".tbTags>tbody>tr").append("<th>"+camposTag[j].titulo+"</th>");
                                    
                                    /*if (jsonconfig.rnc) {
                                        $(".tbTags>tbody>tr").append("<th>RNC</th>");
                                    }*/
                                }

                                tds += criaCamposTabela(camposTag[j], loadResultado.tags[i], 'tag');
                            }

                            /*if (jsonconfig.rnc) {
                                $('.tbTags').append('<tr data-tagid='+val+'><td>'+nome+'</td>'+tds+'<td><i id="novoteste" class="fa fa-plus-circle verde btn-lg pointer" onclick="fnovornc(<?//=$_1_u_evento_idevento?>, '+val+');" title="Criar novo RNC"></i></td></tr>');
                            } else {*/
                                $('.tbTags').append('<tr data-tagid='+val+'><td>'+nome+'</td>'+tds+'</tr>');
                            /*}*/

                        }

                        /*for (i = 0; i < loadResultado.tags.length; i++) {

                        }*/

                        if (loadResultado.tagsValores &&
                            loadResultado.tagsValores.length > 0) {
                            
                            let len = loadResultado.tagsValores.length;
                            let i = 0;

                        
                            for (i = 0; i < len; i++) {

                                let id = loadResultado.tagsValores[i].id.toString() ;
                                let objid = loadResultado.tagsValores[i].objid;

                                if (loadResultado.tagsValores[i].valor === true) {
                                    
                                    $('[data-campoid="'+id+'xx'+objid+'"]').prop("checked", true);
                                }

                                $('[data-campoid="'+id+'xx'+objid+'"]').val(loadResultado.tagsValores[i].valor);
                            }
                        }
                    }
                }
            });
        }
		console.log(4);
        if (config.pessoas !== undefined && config.pessoas.length > 0) {

            loadPessoas(config.pessoas).then((pessoas) => {

                let len = pessoas.length, i = 0;

                for (i = 0; i < len; i++) {
                    $("#comboPessoas > div > div > select").append(
                        "<option value='" + pessoas[i].idpessoa + "'>" + pessoas[i].nome + '</option>');
                }

                $("#comboPessoas > div > div > select").selectpicker("refresh");

                $("#comboPessoas").show();

                if (acao == 'u') {

                    if (typeof(loadResultado) === 'object') {

                        if (loadResultado.pessoas) {
                            $("#comboPessoas > div > div > select").selectpicker('val', loadResultado.pessoas);
                            $("#comboPessoas > div > div > select").selectpicker("render");
                            $(".pessoaRow").show();
                        }

                        for (i = 0; i < loadResultado.pessoas.length; i++) {
                            
                            let val = $(".pessoaSelect option[value='"+loadResultado.pessoas[i]+"']").val();
                            let nome = $(".pessoaSelect option[value='"+loadResultado.pessoas[i]+"']").text();
                            
                            let tds = "";
                            let j = 0;

                            for (j = 0; j < camposPessoa.length; j++) {
                                if (i == 0) {
                                    $(".tbPessoas>tbody>tr").append("<th>"+camposPessoa[j].titulo+"</th>");
                                }
                                tds += criaCamposTabela(camposPessoa[j], loadResultado.pessoas[i], 'pessoa');
                            }

                            $('.tbPessoas').append('<tr data-pessoaid='+val+'><td>'+nome+'</td>'+tds+'</tr>');
                        }

                        if (loadResultado.pessoasValores &&
                            loadResultado.pessoasValores.length > 0) {
                                
                            let len = loadResultado.pessoasValores.length;
                            let i = 0;

                        
                            for (i = 0; i < len; i++) {

                                let id = loadResultado.pessoasValores[i].id.toString() ;
                                let objid = loadResultado.pessoasValores[i].objid;

                                if (loadResultado.pessoasValores[i].valor === true) {
                                    
                                    $('[data-campoid="'+id+'xx'+objid+'"]').prop("checked", true);
                                }

                                $('[data-campoid="'+id+'xx'+objid+'"]').val(loadResultado.pessoasValores[i].valor);

                            }
                        }

                    }
                   
                }

            }, (error) => {
                console.log(error);
            });

        }
console.log(5);
        if (config.documentos !== undefined && config.documentos.length > 0) {

            loadDocumentos(config.documentos).then((documentos) => {

                let len = documentos.length, i = 0;

                for (i = 0; i < len; i++) {
                    $("#comboDocumentos > div > div > select").append(
                        "<option value='" + documentos[i].idsgdoc + "'>" + documentos[i].titulo + '</option>');
                }

                $("#comboDocumentos > div > div > select").selectpicker("refresh");

                $("#comboDocumentos").show();
                
                if (acao == 'u') {
                    

                    if (typeof(loadResultado) === 'object') {

                        if (loadResultado.documentos) {

                            $("#comboDocumentos > div > div > select").selectpicker('val', loadResultado.documentos);
                            $("#comboDocumentos > div > div > select").selectpicker("render");
                            $(".docRow").show();

                        }

                        for (i = 0; i < loadResultado.documentos.length; i++) {
                            
                            let val = $(".docSelect option[value='"+loadResultado.documentos[i]+"']").val();
                            let nome = $(".docSelect option[value='"+loadResultado.documentos[i]+"']").text();
                            
                            let tds = "";
                            let j = 0;

                            for (j = 0; j < camposDoc.length; j++) {

                                if (i == 0) {
                                    $(".tbDocs>tbody>tr").append("<th>"+camposDoc[j].titulo+"</th>");
                                }

                                tds += criaCamposTabela(camposDoc[j], loadResultado.documentos[i], 'doc');
                            }

                            $('.tbDocs').append('<tr data-documentoid='+val+'><td>'+nome+'</td>'+tds+'</tr>');
                        }

                        if (loadResultado.documentosValores &&
                            loadResultado.documentosValores.length > 0) {

                            let len = loadResultado.documentosValores.length;
                            let i = 0;

                            for (i = 0; i < len; i++) {

                                let id = loadResultado.documentosValores[i].id.toString() ;
                                let objid = loadResultado.documentosValores[i].objid;

                                if (loadResultado.documentosValores[i].valor === true) {
                                    
                                    $('[data-campoid="'+id+'xx'+objid+'"]').prop("checked", true);
                                }

                                $('[data-campoid="'+id+'xx'+objid+'"]').val(loadResultado.documentosValores[i].valor);

                            }
                        }
                    }
                }
            });
        }

        if (config && config != undefined &&
            config.personalizados) {

            var len = config.personalizados.length, idx = 0;

            for (i = 0; i < len; i++) {

                let o = config.personalizados[i];

                if (o.id) {
                    
                    jsonconfig.personalizados[i].id = o.id;
                    $("#jsonconfig").val("" + JSON.stringify(jsonconfig));

                    if (o.vinculo != "" && o.vinculo != "geral") {

                        if (o.vinculo == "tag") {
                            camposTag.push(o);
                        }
                        
                        if (o.vinculo == "sgdoc") {
                            camposDoc.push(o);
                        }

                        if (o.vinculo == "pessoa") {
                            camposPessoa.push(o);
                        }

                    } else {
                        if (o.tipo === 'numerico') {
                            $("#inputs").append(criaInput(o.titulo, 'number', o.id));
                        } else {
                            if (o.tipo === 'input') {
                                $("#inputs").append(criaInput(o.titulo, 'text', o.id));
                            } else {
                                if (o.tipo === 'data') {
                                    $("#inputs").append(criaInput(o.titulo, 'date', o.id));
                                } else {
                                    if (o.tipo === 'hora') {
                                        $("#inputs").append(criaInput(o.titulo, 'time', o.id));
                                    } else {
                                        if (o.tipo === 'selecionavel') {
                                            $("#inputs").append(criaSelect(o.titulo, o.options, o.id));
                                        } else {
                                            if (o.tipo === 'checkbox') {
                                                $("#inputs").append(criaInput(o.titulo, 'checkbox', o.id));
                                            } else {
                                                if (o.tipo === 'textarea') {
                                                    $("#texts").append(criaTextarea(o.titulo, o.id));
                                                } else {

                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                } else {
				
					getHash(o.titulo + (new Date().getTime()).toString(), idx).then(hash => {

						let hashId	= hash[0].substring(0, 16);
						let indice	= hash[1];
						let vinc = (o.vinculo != "geral") ? true : false;
							
						jsonconfig.personalizados[indice].id = hashId;
                        $("#jsonconfig").val("" + JSON.stringify(jsonconfig));
                        
						if (!vinc) {
                            if (o.tipo === 'numerico') {
                                $("#inputs").append(criaInput(o.titulo, 'number', hashId));
                            } else {
                                if (o.tipo === 'input') {
                                    $("#inputs").append(criaInput(o.titulo, 'text', hashId));
                                } else {
                                    if (o.tipo === 'data') {
                                        $("#inputs").append(criaInput(o.titulo, 'date', hashId));
                                    } else {
                                        if (o.tipo === 'hora') {
                                            $("#inputs").append(criaInput(o.titulo, 'time', hashId));
                                        } else {
                                            if (o.tipo === 'selecionavel') {
                                                $("#inputs").append(criaSelect(o.titulo, o.options, hashId));
                                            } else {
                                                if (o.tipo === 'checkbox') {
                                                    $("#inputs").append(criaInput(o.titulo, 'checkbox', hashId));
                                                } else {
                                                    if (o.tipo === 'textarea') {
                                                        $("#texts").append(criaTextarea(o.titulo, hashId));
                                                    } else {

                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
					}, (error) => {
						console.log(error);
					});
                }

                idx++;
            }
        }
    }
}

function setResultado(campo) {

    let value = undefined;

    if (campo.tagName === 'TEXTAREA') {
        value = $(campo).val();
    } else {
        if (campo.tagName === 'INPUT' && campo.type === 'checkbox') {
            value = campo.checked;
        } else if (campo.type === 'text' || campo.type === 'number' || campo.type === 'date'){
            value = $(campo).val();
        }else{
			 value = $(campo).find('option:selected').text();
		}
    }

    let resultado = {
        id: $(campo).attr('id'),
        valor: value
    };

    if ($(campo).data('objid') && $(campo).data('obj') && !isNaN($(campo).data('objid'))) {

        //Caso seja um campo vinculado a um objeto Tag, Pessoa, Documento.
        resultado.obj = $(campo).data('obj');
        resultado.objid = $(campo).data('objid');
        
        let position = -1;
        let existente = false;
        
        switch (resultado.obj) {

            case 'tag':

                for (var i = 0; i < jsonresultado.tagsValores.length; i++) {
                    if (jsonresultado.tagsValores[i].objid == resultado.objid &&
                        jsonresultado.tagsValores[i].id == resultado.id) {
                        existente = true
                        position = i;
                    }
                }

                if (existente) {
                    jsonresultado.tagsValores[position] = resultado;
                } else {
                    jsonresultado.tagsValores.push(resultado);
                }

                break;

            case 'pessoa':

                for (var i = 0; i < jsonresultado.pessoasValores.length; i++) {
                    if (jsonresultado.pessoasValores[i].objid == resultado.objid &&
                        jsonresultado.pessoasValores[i].id == resultado.id) {
                        existente = true
                        position = i;
                    }
                }

                if (existente) {
                    jsonresultado.pessoasValores[position] = resultado;
                } else {
                    jsonresultado.pessoasValores.push(resultado);
                }

                break;

            case 'doc':

                for (var i = 0; i < jsonresultado.documentosValores.length; i++) {
                    if (jsonresultado.documentosValores[i].objid == resultado.objid &&
                        jsonresultado.documentosValores[i].id == resultado.id) {
                        existente = true
                        position = i;
                    }
                }

                if (existente) {
                    jsonresultado.documentosValores[position] = resultado;
                } else {
                    jsonresultado.documentosValores.push(resultado);
                }

                break;
        }

    } else {

        //Caso seja um campo personalizado GERAL
        let position = -1;
        let existente = false;
        let len = jsonresultado.personalizados.length;

        for (var i = 0; i < len; i++) {
            if (jsonresultado.personalizados[i].id === resultado.id) {
                existente = true
                position = i;
            }
        }

        if (existente) {
            jsonresultado.personalizados[position] = resultado;

        } else {
            jsonresultado.personalizados.push(resultado);

        }
        
    }

    $("#jsonresultado").val("" + JSON.stringify(jsonresultado));

};

function adicionaisBody(event) {
    
    if (event.title === 'expandir') {
        $('#btnTabelaAdicionais').prop('title', 'minimizar');
        $("#tabelaAdicionais").hide();
    } else if(event.title === 'minimizar') {
        $('#btnTabelaAdicionais').prop('title', 'expandir');
        $("#tabelaAdicionais").show();
    }
}

function equipamentosBody(event) {

    if (event.title === 'expandir') {
        $('#btnTabelaEquipamento').prop('title', 'minimizar');
        $("#tabelaEquipamento").hide();
    } else if(event.title === 'minimizar') {
        $('#btnTabelaEquipamento').prop('title', 'expandir');
        $("#tabelaEquipamento").show();
    }
}

function historicoBody(event) {

    if (event.title === 'expandir') {
        $('#btnHistorico').prop('title', 'minimizar');
        $("#tblHistorico").hide();
    } else if(event.title === 'minimizar') {
        $('#btnHistorico').prop('title', 'expandir');
        $("#tblHistorico").show();
    }
}

function documentosBody(event) {

    if (event.title === 'expandir') {
        $('#btnTabelaDocumento').prop('title', 'minimizar');
        $("#tabelaDocumento").hide();
    } else if(event.title === 'minimizar') {
        $('#btnTabelaDocumento').prop('title', 'expandir');
        $("#tabelaDocumento").show();
    }
}

function pessoasBody(event) {

    if (event.title === 'expandir') {
        $('#btnTabelaPessoa').prop('title', 'minimizar');
        $("#tabelaPessoa").hide();
    } else if(event.title === 'minimizar') {
        $('#btnTabelaPessoa').prop('title', 'expandir');
        $("#tabelaPessoa").show();
    }
}

function atualizaHistorico(historicos) {
   
    if (historicos) {
        
        let i = 0;
        let len = historicos.length;
        let conteudo = '';
        $("#tblHistorico").empty();

        for (i = 0; i < len; i++) {
            
            let row = criaHistorico(historicos[i]);
           conteudo = row.concat(conteudo); 
           
        }
		 $('#tblHistorico').append(conteudo);

    }
}

$("#comboEquipamentos").change(function(event) {

    var tags = [];

    $.each($("#comboEquipamentos option:selected"), function() {
        
        if (!($('tr[data-tagid="'+$(this).val()+'"]').length)) {
           
            let tds = "";
            let j = 0;

            for (j = 0; j < camposTag.length; j++) {

                if (i == 0) {
                    $(".tbTags>tbody>tr").append("<th>"+camposTag[j].titulo+"</th>");
                }

                tds += criaCamposTabela(camposTag[j], $(this).val(), 'tag');
            }
            
            $('.tbTags').append('<tr data-tagid='+$(this).val()+'><td>'+$(this).text()+'</td>'+tds+'</tr>');
            
        }

        tags.push($(this).val());

    });
    
    $('[data-tagid]').each(function() {
        
        let tagID = $(this).data('tagid');
        let len = tags.length;
        let remove = true;
        let i = 0;

        for (i = 0; i < len; i++) {
            if (tags[i] == tagID) {
                remove = false;
                break;
            }
        }

        if (remove) {
            $('tr[data-tagid="'+tagID+'"]').remove();
        }

    });

    jsonresultado.tags = tags;

    $("#jsonresultado").val("" + JSON.stringify(jsonresultado));

});

$("#comboPessoas").change(function(event) {

    var pessoas = [];

    $.each($("#comboPessoas option:selected"), function() {
        
        if (!($('tr[data-pessoaid="'+$(this).val()+'"]').length)) {
           
            let tds = "";
            let j = 0;

            for (j = 0; j < camposPessoa.length; j++) {

                if (i == 0) {
                    $(".tbPessoas>tbody>tr").append("<th>"+camposPessoa[j].titulo+"</th>");
                }

                tds += criaCamposTabela(camposPessoa[j], $(this).val(), 'pessoa');
            }
            
            $('.tbPessoas').append('<tr data-pessoaid='+$(this).val()+'><td>'+$(this).text()+'</td>'+tds+'</tr>');
            
        }

        pessoas.push($(this).val());

    });
    
    $('[data-pessoaid]').each(function() {
        
        let pessoaID = $(this).data('pessoaid');
        let len = pessoas.length;
        let remove = true;
        let i = 0;

        for (i = 0; i < len; i++) {
            if (pessoas[i] == pessoaID) {
                remove = false;
                break;
            }
        }

        if (remove) {
            $('tr[data-pessoaid="'+pessoaID+'"]').remove();
        }

    });

    jsonresultado.pessoas = pessoas;
    $("#jsonresultado").val("" + JSON.stringify(jsonresultado));

});

$("#comboDocumentos").change(function(event) {

    var documentos = [];
	
    $.each($("#comboDocumentos option:selected"), function() {
        
        if (!($('tr[data-documentoid="'+$(this).val()+'"]').length)) {
           
            let tds = "";
            let j = 0;

            for (j = 0; j < camposDoc.length; j++) {

                if (i == 0) {
                    $(".tbDocs>tbody>tr").append("<th>"+camposDoc[j].titulo+"</th>");
                }

                tds += criaCamposTabela(camposDoc[j], $(this).val(), 'doc');
            }
            
            $('.tbDocs').append('<tr data-documentoid='+$(this).val()+'><td>'+$(this).text()+'</td>'+tds+'</tr>');
            
        }

        documentos.push($(this).val());

    });
    
    $('[data-documentoid]').each(function() {
        
        let documentoID = $(this).data('documentoid');
        let len = documentos.length;
        let remove = true;
        let i = 0;

        for (i = 0; i < len; i++) {
            if (documentos[i] == documentoID) {
                remove = false;
                break;
            }
        }

        if (remove) {
            $('tr[data-documentoid="'+documentoID+'"]').remove();
        }

    });

    jsonresultado.documentos = documentos;
    $("#jsonresultado").val("" + JSON.stringify(jsonresultado));

});

$("#comboDocumentos > div.col-md-10 > div > button").click(function() {
    $("#comboDocumentos > div.col-md-10 > div > div").css("width", "fit-content");
});

$("#selectTipoEvento").change(function(event) {

    $("#comboPessoas").hide();
    $("#comboPessoas > div > div > select").empty();
    $("#comboPessoas > div > div > select").selectpicker("refresh");
    $("#comboPessoas > div.col-md-10 > div > button").css("width", "35%");
    $("#comboPessoas > div.col-md-10 > div > button").css("max-width", "35%");

    $("#comboDocumentos").hide();
    $("#comboDocumentos > div > div > select").empty();
    $("#comboDocumentos > div > div > select").selectpicker("refresh");
    $("#comboDocumentos > div.col-md-10 > div > button").css("width", "35%");
    $("#comboDocumentos > div.col-md-10 > div > button").css("max-width", "35%");

    $("#comboEquipamentos").hide();
    $("#comboEquipamentos > div > div > select").empty();
    $("#comboEquipamentos > div > div > select").selectpicker("refresh");
    $("#comboEquipamentos > div.col-md-10 > div > button").css("width", "35%");
    $("#comboEquipamentos > div.col-md-10 > div > button").css("max-width", "35%");
    
    $("#texts").empty();
    $("#inputs").empty();

    $.ajax({
        type: "get",
        url: "ajax/evento.php?vopcao=eventotipo&videventotipo=" + $('#selectTipoEvento').val()+"&vversao=" + $('#versao').val(),
        success: function(data) {

            let dataformat = data.replace(/\\/g, '');
            let config = JSON.parse(dataformat);

            if (config.configprazo == true) {
                let today = new Date();
                let dd = today.getDate();
                let mm = today.getMonth() + 1; //January is 0!

                let yyyy = today.getFullYear();
                if (dd < 10) {
                dd = '0' + dd;
                } 
                if (mm < 10) {
                mm = '0' + mm;
                } 
                today = dd + '/' + mm + '/' + yyyy;

                $('#data').hide();
                $('#rowRepetition').hide();
                $('#prazo').show();
                if (acao == 'i') {
                  // $('input[name=_1_i_evento_inicio]').val(today);
                }
            } else {
                $('#data').show();
                $('#rowRepetition').show();
                $('#prazo').hide();
            }
            
            criaPersonalizados(config);
         //   criaParticipantes(config);

        },
        error: function(objxml) {
            document.body.style.cursor = "default";
            alert('Erro: ' + objxml.status);
        }
    });
});

function atualizaStatusEvento(status) {
    console.log();
	//montaBotao(status);
   

    let statusButton = '<span style="text-transform:uppercase;font-size: 11px;">'+statusevento+'</span>';
	$(".modal-header").css("background", "<?=$corstatus[$_1_u_evento_ideventotipo]?>");
    $("#statusButton").empty();
    $("#statusButton").append(statusButton);
	
}

$(document).ready(function() {
    
    let loadConfig = '';
    let loadResultado = '';

    $("#prazo").hide();
    atualizaStatusEvento();


    if (idsgdoc) {
        $("#rncempty").hide();
    } else {
        $("#rnc").hide();        
    }

    $("#comboPessoas > div.col-md-10 > div > button").css("max-width", "35%");
    $("#comboDocumentos > div.col-md-10 > div > button").css("max-width", "35%");
    $("#comboEquipamentos > div.col-md-10 > div > button").css("max-width", "35%");
    $("#comboPessoas > div.col-md-10 > div > button").css("width", "35%");
    $("#comboDocumentos > div.col-md-10 > div > button").css("width", "35%");
    $("#comboEquipamentos > div.col-md-10 > div > button").css("width", "35%");

    if (acao === 'u' || calendario) {
        
        let tipoEventoSpan = $("#selectTipoEvento option:selected").text();
        $("#tipoEventoSpan").text(tipoEventoSpan);
        $(".tipoEvento").hide();

        if (acao !== 'u') {
            $("#selectTipoEvento").change();
        }
    }

    if (acao === 'u') {

        if (isDono()) {
            $('#menuPermissoes').show();
        } else {


          //$(".tagSelect").prop("disabled", "disabled");
            $(".docSelect").prop("disabled", "disabled");
            //$(".pessoaSelect").prop("disabled", "disabled");
            $("#statusEvento").prop("disabled", "disabled");
            $("textarea[name=_1_u_evento_descricao]").prop("readonly", "readonly");
            $("input[name=_1_u_evento_inicio]").prop("readonly", "readonly");
            $("input[name=_1_u_evento_fim]").prop("readonly", "readonly");
            $(".calendario").removeClass('calendario');
            $("input[name=_1_u_evento_prazo").prop("readonly", "readonly");
            $("input[name=_1_u_evento_evento").prop("readonly", "readonly");
            $("#diveditor").attr("contenteditable", "false");
			$("input[name=_1_u_evento_iniciohms").prop("readonly", "readonly");
			$("input[name=_1_u_evento_fimhms").prop("readonly", "readonly");
			$("select[name=_1_u_evento_periodicidade").prop("disabled", "disabled");
			$("input[name=_1_u_evento_repetirate").prop("readonly", "readonly");
			$("select[name=_1_u_evento_fimsemana").prop("disabled", "disabled");
			$("input[name=_1_u_evento_intervalo").prop("readonly", "readonly");
			$("#diainteirocheckbox").prop("readonly", "readonly");
			$("#repetircheckbox").prop("readonly", "readonly");
        }

        jsonconfig = JSON.parse($("#jsonconfig").val());
		console.log('XXX');
        jsonresultado = JSON.parse($("#jsonresultado").val());

        if (jsonconfig.configprazo) {
            $('#data').hide();
            $('#rowRepetition').hide();
            $('#prazo').show();
        }


        loadHistorico(idevento).then((historicos) => {
            atualizaHistorico(historicos);
        }, (error) => {
            console.log(error);
        });

    } else {
        
        $("#idEventoTitulo").hide();
        $("#gerarRnc").hide();
      //  $("#ocultar").hide();
        $(".observacoes").hide();
        $(".divHistorico").hide();
        $("#statusButton").hide();
        $("#idpessoa").val(idPessoa);
        $('#menuPermissoes').show();
        


        jsonconfig.permissoes = { 
            "setores": [], 
            "funcionarios": []
        };
    
        $("#jsonconfig").val("" + JSON.stringify(jsonconfig));
		


    }

    if ($("#jsonconfig").val()) {
        loadConfig = JSON.parse($("#jsonconfig").val());
    }
console.log('pass');
    if ($("#jsonresultado").val()) {
        loadResultado = JSON.parse($("#jsonresultado").val());
    }
console.log(loadConfig);
console.log('pass2');
    if (typeof(loadConfig) === 'object') {
        criaPersonalizados(loadConfig);
    }
console.log('pass2');
    if (typeof(loadResultado) === 'object') {

        if (loadResultado.personalizados &&
            loadResultado.personalizados.length > 0) {

            let len = loadResultado.personalizados.length;
            let i = 0;

            for (i = 0; i < len; i++) {
                $("#" + loadResultado.personalizados[i].id.toString()).val(
                    loadResultado.personalizados[i].valor);
            }
        }
    }
console.log('pass3');
    if (iniciohms == '00:00:12') {
        $("#diainteirocheckbox").prop("checked", true);
        $("#diainteirocheckbox").change();
    }

    if (repetirate !== undefined && repetirate) {
        $("#repetircheckbox").prop("checked", true);
        $("#repetircheckbox").change();
        $('[name="_1_u_evento_repetirate"]').val(repetirate);
        $('[name="_1_u_evento_periodicidade"]').val(periodicidade);
        $('[name="_1_u_evento_fimsemana"]').val(fimsemana);
    }

    if (modulo !== undefined && modulo != '') {

        if (modulo == "true") {
            let sLink = window.parent.location.search;
            modulo = sLink;

            let urlSplit = sLink.split("&");
            
            for (let i = 0; i < urlSplit.length; i++) {
                
                if (urlSplit[i].includes("_modulo")) {

                    let modSplit = urlSplit[i].split("=");                    
                    modulo = modSplit[1];
                }
            }

            idmodulo = removerParametroGet("_modulo", sLink);
            idmodulo = removerParametroGet("_acao", idmodulo);
            idmodulo = idmodulo.replace(/^\?/, "");
            
            let idModSplit = idmodulo.split("=");            
            nomemodulo = idModSplit[0];            
            idmodulo = idModSplit[1];
            
            $("#modulo").attr("href", "javascript:janelamodal('?_modulo="+modulo+"&_acao=u&"+nomemodulo+"="+idmodulo+"')");
        } else {
            //nomemodulo = '';
            $("#modulo").attr("href", "javascript:janelamodal('?_modulo="+modulo+"&_acao=u&"+nomemodulo+"="+idmodulo+"')");
        }
            
        let valModulo = modulo + ": "+ nomemodulo + "=" + idmodulo;

        $("#modulo").text(valModulo);
        $("#inputmodulo").val(modulo);
        $("#inputidmodulo").val(idmodulo);
        $("#inputmodulo").hide();
        $("#inputidmodulo").hide();
    
    } else {
        $("#divmodulo").hide();
    }
console.log('pass3');
    window.setInterval(e=>$('.cbFecharForm').hide(), 1000);
    
    let sSeletor = '#diveditor';
    let oDescritivo = $("[name=_1_"+CB.acao+"_evento_descricao]");

    if (tinyMCE.editors["diveditor"]) {
        tinyMCE.editors["diveditor"].remove();
    }
        
    tinyMCE.init({
        selector: sSeletor,
        inline: true,
        height: '180px',
        toolbar: 'bold | subscript superscript | bullist numlist | table',
        menubar: false,
        plugins: ['table','autoresize'],
        setup: function (editor) {
            editor.on('init', function (e) {
                this.setContent(oDescritivo.val());
                if (!isDono()) {
                    $("div[id=diveditor]").prop("contenteditable", "false");
                }
            });
        },
        entity_encoding: 'raw'
    });
    
    CB.prePost = function() {
        if (tinyMCE.get('diveditor')) {
            oDescritivo.val(tinyMCE.get('diveditor').getContent());
			if (idevento){
				$("#adicionar").click();
			}
        }		
    };
    
});
//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>
