<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$ler=true;//Gerar logs de erro
$rid="\n".rand()." - Sislaudo: ";
if($ler)error_log($rid.basename(__FILE__, '.php'));

session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	$prefu="stdin_";
	include_once("/var/www/carbon8/inc/php/functions.php");
	include_once("/var/www/carbon8/inc/php/cmd.php");
}else{//se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
	include_once("../inc/php/cmd.php");
}

$_inspecionar_sql = ($_GET["_inspecionar_sql"]=="Y")?true:false;

$_idlp = $_REQUEST['idlp'];
$_sincronizalp = $_REQUEST['sincronizalp'];
$_sincronizadash = $_REQUEST['sincronizadash'];
$_sincronizaconfig = $_REQUEST['sincronizaconfig'];
$_sincronizaparticipantes = $_REQUEST['sincronizaparticipantes'];

if ($_idlp == ''){
    die('LP Origem vazia');
}

$_idlpdestino = $_REQUEST['idlpdestino'];

if ($_idlpdestino == ''){
    die('LP Destino vazia');
}

$row_lp['idobjeto'] = $_idlp;
$row_lp['idobjetovinc'] = $_idlpdestino;

$grupo = rstr(8);

//re::dis()->hMSet('cron:sincronizalp',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'cron', 'sincronizalp', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


if($_inspecionar_sql){
    echo "Início: ".date("d/m/Y H:i:s", time()).'<br>'; 
}
$sessionid = session_id();//PEGA A SESSÃO 

$sqln = "select 
				l.idempresa, 
				ldestino.idempresa as idempresadestino 
		from
			carbonnovo._lp l
		join 
			carbonnovo._lp ldestino on ldestino.idlp = '".$_idlpdestino."'
		where
			l.idlp = '".$_idlp."'";

$resn = mysql_query($sqln) or die("Falha ao selecionar nucleos do cliente: " . mysql_error() . "<p>SQL: " . $sqln);

while ($rown = mysql_fetch_array($resn)) {
	$idempresa = $rown['idempresa'];
	$idempresadestino = $rown['idempresadestino'];
}
	


//TABELA carbonnovo._lp
//ALVO: DASHBOARD
if ($_sincronizadash == 'Y'){
    $sql_u_dashboard = "
    					update								
    						carbonnovo._lp lporigem
    					join
    						carbonnovo._lp lpdestino on lpdestino.idlp = '".$_idlpdestino."'
    					set
    						lpdestino.jsondashboardconf = lporigem.jsondashboardconf
    					where
    						lporigem.idlp = '".$_idlp."';";
    
    	if($_inspecionar_sql){
            echo "<pre>".$sql_u_dashboard."</pre>";
    	}
    	d::b()->query($sql_u_dashboard);

}

if ($_sincronizaconfig == 'Y'){
    	
    //TARGET: objetovinculo
    //ALVO: formapagamento, contaitem, empresa, unidade, tipopessoa,_lp,agencia
    $sql_i_objetovinculo = "insert into objetovinculo
    	(idobjetovinculo,
    	idobjeto,
    	tipoobjeto,
    	idobjetovinc,
    	tipoobjetovinc,
    	criadopor,
    	criadoem,
    	alteradopor,
    	alteradoem)
    select 
    	null, ".$row_lp['idobjetovinc'].", tipoobjeto, idobjetovinc, tipoobjetovinc, 'sincronizalp',now(),'sincronizalp',now() 
    from
    	objetovinculo lo
    where 
    	idobjeto = ".$row_lp['idobjeto']." 
    	and lo.tipoobjeto = '_lp'
    	and not lo.tipoobjetovinc in ('formapagamento', 'agencia','empresa')
    	and not exists (
    		select 
    			1 
    		from 
    			objetovinculo lov 
    		where 
    			lov.idobjeto = ".$row_lp['idobjetovinc']."
    			and lov.tipoobjeto = lo.tipoobjeto
    			and lov.tipoobjetovinc = lo.tipoobjetovinc
    			and lov.idobjetovinc = lo.idobjetovinc
    	);";
    
    if($_inspecionar_sql){
        echo "<pre>".$sql_i_objetovinculo."</pre>";
    }
    d::b()->query($sql_i_objetovinculo);
    
    
    //TARGET: plantelobjeto
    //ALVO: PLANTEL
    $sql_i_plantelobjeto = "insert into plantelobjeto
    (
    	idplantelobjeto,
    	idempresa,
    	idplantel,
    	idobjeto,
    	tipoobjeto,
    	criadopor,
    	criadoem,
    	alteradopor,
    	alteradoem)
    select 
    	null, ".$idempresadestino.", idplantel, ".$row_lp['idobjetovinc'].", tipoobjeto, 'sincronizalp',now(),'sincronizalp',now() 
    from
    	plantelobjeto lo
    where 
    	idobjeto = ".$row_lp['idobjeto']." 
    	and lo.tipoobjeto = 'lp'
    	and not exists (
    		select 1 
    		from plantelobjeto lov 
    		where lov.idobjeto = ".$row_lp['idobjetovinc']."
    		and lov.tipoobjeto = lo.tipoobjeto
    		and lov.idplantel = lo.idplantel
    	)
    ;";
    
    if($_inspecionar_sql){
        echo "<pre>".$sql_i_plantelobjeto."</pre>";
    }
    d::b()->query($sql_i_plantelobjeto);
    
    
    //TARGET: _lpobjeto
    //ALVO: _snippet, dashboard, empresa, lpgrupo, sgdepartamento
    $sql_i__lpobjeto = "insert into carbonnovo._lpobjeto
    (idlpobjeto,
    idlp,
    idobjeto,
    tipoobjeto,
    criadopor,
    criadoem,
    alteradopor,
    alteradoem)
    select 
    	null, ".$row_lp['idobjetovinc'].", idobjeto, tipoobjeto, 'sincronizalp',now(),'sincronizalp',now() 
    from
    	carbonnovo._lpobjeto lo
    where 
    	idlp = ".$row_lp['idobjeto']." 
    	and lo.tipoobjeto in ('dashboard','_snippet')
    	and not exists (
    		select 1 
    		from carbonnovo._lpobjeto lov 
    		where lov.idlp = ".$row_lp['idobjetovinc']."
    		and lov.tipoobjeto = lo.tipoobjeto
    		and lov.idobjeto = lo.idobjeto
    	)
    ;";
    
    if($_inspecionar_sql){
        echo "<pre>".$sql_i__lpobjeto."</pre>";
    }
    d::b()->query($sql_i__lpobjeto);
    
}
if ($_sincronizalp == 'Y'){
    //TABELA carbonnovo._lpmodulo
    //ALVO: Módulos
    	$sql_i_snippet = "insert into carbonnovo._lpmodulo
    						(
    						idlpmodulo,
    						idlp,
    						modulo,
    						permissao,
    						solassinatura,
    						rotulo,
    						cssicone,
    						ord)
    						select 
    							null, ".$row_lp['idobjetovinc'].", modulo, permissao,solassinatura,rotulo,cssicone,ord
    						from
    							carbonnovo._lpmodulo lo
    						where 
    							idlp = ".$row_lp['idobjeto']." 
    							and not exists (
    								select 1 
    								from carbonnovo._lpmodulo lov 
    								where lov.idlp = ".$row_lp['idobjetovinc']."
    								and lov.modulo = lo.modulo
    							)
    					;";
    
    if($_inspecionar_sql){
        echo "<pre>".$sql_i_snippet."</pre>";
    }
    	d::b()->query($sql_i_snippet);
    /*
    //TABELA lpobjeto
    //ALVO: pessoa, sgarea, sgconselho, sgdepartamento, sgsetor, 
    $sql_i_lpobjeto = "insert into lpobjeto
    (
    	idlpobjeto,
    	idempresa,
    	idobjeto,
    	idlp,
    	tipoobjeto,
    	idlpold,
    	criadopor,
    	criadoem,
    	alteradopor,
    	alteradoem)
    select 
    	null, ".$idempresadestino.", idobjeto, ".$row_lp['idobjetovinc'].", tipoobjeto, idlpold, 'sincronizalp',now(),'sincronizalp',now() 
    from
    	lpobjeto lo
    where 
    	lo.idlp = ".$row_lp['idobjeto']." 
    	and not exists (
    		select 1 
    		from lpobjeto lov 
    		where lov.idlp = ".$row_lp['idobjetovinc']."
    		and lov.idobjeto = lo.idobjeto
    		and lov.tipoobjeto = lo.tipoobjeto
    	)
    ;";
    
    echo "<pre>".$sql_i_lpobjeto."</pre>";
    d::b()->query($sql_i_lpobjeto);
    */
    	$sql_u_modulo = "update								
    							carbonnovo._lpmodulo lmv
    						join
    							carbonnovo._lpmodulo lm on lm.idlp = ".$row_lp['idobjeto']." and lm.modulo = lmv.modulo
    						set
    							lmv.permissao = lm.permissao
    						where
    							lmv.idlp = ".$row_lp['idobjetovinc']." and lm.permissao != lmv.permissao
    							;";
    
    if($_inspecionar_sql){
       	echo "<pre>".$sql_u_modulo."</pre>";
    }
    	d::b()->query($sql_u_modulo);
    
    
    
    
    	$sql_i_rep = "insert into carbonnovo._lprep 
    						(
    							idlprep,
    							idlp,
    							idrep,
    							flgunidade
    
    						)
    						select 
    							null, ".$row_lp['idobjetovinc'].", idrep, flgunidade
    						from
    							carbonnovo._lprep lr
    						where 
    							lr.idlp = ".$row_lp['idobjeto']."
    							and not exists (
    								select 1 
    								from carbonnovo._lprep lrv
    								where lrv.idlp = ".$row_lp['idobjetovinc']."
    								and lrv.idrep = lr.idrep
    							)
    					;";
    
    if($_inspecionar_sql){
        echo "<pre>".$sql_i_rep."</pre>";
    }
    	d::b()->query($sql_i_rep);
	
}	

if ($_sincronizaparticipantes == 'Y'){

    	$sql_i_acessos = "insert into lpobjeto
    						(idlpobjeto,
    						idempresa,
    						idobjeto,
    						idlp,
    						tipoobjeto,
    						criadopor,
    						criadoem,
    						alteradopor,
    						alteradoem)
    						select 
    							null, ".$idempresadestino.", idobjeto, ".$row_lp['idobjetovinc'].", tipoobjeto, 'sincronizalp',now(),'sincronizalp',now() 
    						from
    							lpobjeto lo
    						where 
    							idlp = ".$row_lp['idobjeto']." 
    							and not exists (
    								select 1 
    								from lpobjeto lov 
    								where lov.idlp = ".$row_lp['idobjetovinc']."
    								and lov.tipoobjeto = lo.tipoobjeto
    								and lov.idobjeto = lo.idobjeto
    							)
    					;";
    
    if($_inspecionar_sql){
        echo "<pre>".$sql_i_acessos."</pre>";
    }
    	d::b()->query($sql_i_acessos);
    
}  
    
if($_inspecionar_sql){
    echo "Fim: ".date("d/m/Y H:i:s", time()).'<br>'; 
}
//re::dis()->hMSet('cron:sincronizalp',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`,`sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'cron', 'sincronizalp', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

?>