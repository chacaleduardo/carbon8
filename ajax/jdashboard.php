<?

require_once("../inc/php/validaacesso.php");

if(empty($_SESSION["SESSAO"]["STRCONTATOCLIENTE"])){
	die("<div class='alert alert-warning' role='alert'>UsuÃ¡rio nÃ£o configurado.<br>Entre em contato com o responsÃ¡vel pelo email resultados@laudolab.com.br</div>");
}

/*
 * Clientes ATIVOS configurados para o contato (usuÃ¡rio) logado
 */
$sqlcli = "select p.idpessoa, p.nome
	from pessoa p 
	where p.status = 'ATIVO'
		and p.idtipopessoa = 2
		and p.idpessoa in (".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")
	order by p.nome";

/*
 * Restringe os nÃºcleos de usuÃ¡rios OFICIAIS
 */
if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 4) {//contato oficial
    
	//maf150513: caso o dashboard tenha sido preenchido com resultados nÃ£o-oficiais, restringe para evitar o erro de mostrar novos/alertas nÃ£o-oficiais
	//$strwsecr = " and r.idsecretaria in (".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].") ";

	//maf150513: mostra somente nucleos que possuem algum teste daquela secretaria. obs: deve-se relacionar o idpessoa, para o caso de amostras com idnucleo=0
	$strwnucsecr = " 
		where exists (
			select 1 
			from amostra a, resultado r
			where a.idnucleo = n2.idnucleo
				and a.idpessoa = n2.idpessoa
				and r.idamostra = a.idamostra 
				and r.idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].")
		) ";

	//Mostrar somente itens de dashboard que pertecem a resultados oficiais
	$strressecr = " join resultado r on (r.idresultado=dnp.idobjeto and dnp.tipoobjeto = 'resultado' and r.idsecretaria in (".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"]."))";
	
}


//die($sqlcli);
if($inspecionarConsultas){
	echo "<!-- sqlcli: ".$sqlcli." -->";
}

//Consulta os clientes do contato
$rescli = mysql_query($sqlcli) or die("[f:" . __FILE__ . "][l:" . __LINE__ . "]: Erro ao recuperar lista de Clientes.<!-- " . mysql_error() . " -->");

if (mysql_num_rows($rescli) == 0) {
	echo "<!-- sqlcli: ".$sqlcli." -->";
    die("Nenhum cliente configurado para este contato: [" . $_SESSION["SESSAO"]["IDPESSOA"] . "][" . $_SESSION["SESSAO"]["NOME"] . "]");
    
} else {
 
	$aD=array();

    while ($rcli = mysql_fetch_assoc($rescli)) {
    	//print_r($rcli); //idpessoa,nome

        $sqln = "select * 
		from(
			select n.idpessoa, n.idnucleo, if(ifnull(n.lote,'')='',n.nucleo,concat(n.lote,' - ',n.nucleo)) as nucleo, n.idnucleotipo, n.lote
			from nucleo n
			where 
				n.idpessoa = " . $rcli["idpessoa"] . "
				and n.situacao = 'ATIVO'
			union
			select " . $rcli["idpessoa"] . ",0, 'Outros Resultados', 'G',''
		) n2
		".$strwnucsecr."
		order by
			if(n2.idnucleo=0,'',ifnull(n2.idnucleotipo,'G')) desc -- [G]ranjas primeiro, [F]abricantes depois e [idnucleo=0] Outros depois
			,if(n2.idnucleo=0,'ZZZZZ','') -- Mostrar amostras sem nucleos ou lotes no final da listagem
			,n2.nucleo";
        
        if($inspecionarConsultas){
        	echo ("<!-- sqln: " . $sqln . " -->");
        }

        $resn = mysql_query($sqln) or die("Falha ao selecionar nucleos do cliente: " . mysql_error() . "<p>SQL: " . $sqln);
        
        while ($rown = mysql_fetch_assoc($resn)) {
        
        	//print_r($rown);//idpessoa, idnucleo, idnucleotipo, lote

	       	//maf200216: As inconsistencias de dashboard X idpessoa da amostra serao tratadas em outro lugar. Para facilitar o rastreamento de falhas, neste ponto teremos somente a tabela de dashboard
            $sqld = "select 
					ifnull(sum(if(dnp.alerta=0,dnp.nvisualizado,0)),0) as sumnvisualizado -- cada linha de resultado colocada no dashboard, vem com o valor '1', para ser somado aqui. Se houver alerta, nao somara aqui.
					,ifnull(sum(dnp.alerta),0) as sumalerta
					,ifnull(sum(if(dnp.alerta=1 and dnp.nvisualizado=1,dnp.nvisualizado,0)),0) as sumalertanovo -- Alertas novos nao esta sendo utilizado. utilizar em caso de divergencia de cores dos alertas
				from dashboardnucleopessoa dnp ".$strressecr."
				where dnp.idnucleo = " . $rown["idnucleo"] . "
					and dnp.idcliente = " . $rown["idpessoa"] . "
					and dnp.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"]
					. $strwsecr;

            if($inspecionarConsultas){
            	echo "<!-- sqld: ".$sqld." -->";
            }
            
            $resd = mysql_query($sqld) or die("Falha ao verificar dashboard do UsuÃ¡rio: " . mysql_error() . "<p>SQL: " . $sqld);
            
            $rowd = mysql_fetch_assoc($resd);

            if($rowd["sumnvisualizado"]>0 or $rowd["sumalertanovo"]>0){
	            $aD[$rcli["idpessoa"]]["nm"]=$rcli["nome"];
	            $aD[$rcli["idpessoa"]]["ns"][$rown["idnucleo"]]["t"]=$rown["idnucleotipo"];
	            $aD[$rcli["idpessoa"]]["ns"][$rown["idnucleo"]]["n"]=$rown["nucleo"];
	            $aD[$rcli["idpessoa"]]["ns"][$rown["idnucleo"]]["l"]=$rown["lote"];
	        	$aD[$rcli["idpessoa"]]["ns"][$rown["idnucleo"]]["snv"]=$rowd["sumnvisualizado"];
	        	$aD[$rcli["idpessoa"]]["ns"][$rown["idnucleo"]]["sal"]=$rowd["sumalerta"];
	        	$aD[$rcli["idpessoa"]]["ns"][$rown["idnucleo"]]["saln"]=$rowd["sumalertanovo"];
	        }
        }
    }
}

if(count($aD)>0){
	echo $JSON->encode($aD);
}