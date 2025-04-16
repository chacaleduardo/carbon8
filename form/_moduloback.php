<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

//Parà¢metros mandatà³rios para o carbon
$pagvaltabela = "_modulo";
$pagvalcampos = array(
	"modulo"=>"pk"
);

//Select que inicializa as variáveis que preenchem os campos da tela em caso de update
$pagsql = "select * from "._DBCARBON."._modulo where modulo = '#pkid'";

//controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e criacao das variáveis 'variáveis' para a página
require_once("../inc/php/controlevariaveisgetpost.php");



/*
 * maf 051110: Caso a tabela informada na pagina de pesquisa seja alterada, é necessário excluir os relacionamentos antigos
* para não sobrar lixo na tabela
* @TODO: 170613: limpar campos existentes anteriormente
*/
/* $_sqldeltabant = "delete
	FROM "._DBCARBON."._modulofiltros
	where modulo = '".$_GET["modulo"]."'
	and tab != '".$_1_u__modulo_tab."'";
	
	$_resdeltabant = mysql_query($_sqldeltabant) or die("Erro ao apagar dados da tabela anterior".mysql_error());

*/

/*
 * maf 090128: o left join deve conter a comparacao de pagina de pesquina, porque as vezes mais de uma pagina de pesquisa utilizam a mesma tabela.
* and mpp.pagpsq = '".$pagpsq."'
*/
$_sqlitens = "SELECT
  if(concat(c.table_name,c.column_name)=concat(m.tab,mpp.col),'u','i')
  as acao
  ,mpp.idmodulofiltros
  ,c.table_name
  ,c.column_name
,mpp.psqkey
,mpp.psqreq
,mpp.psqreqdefault
,mpp.visres
FROM information_schema.columns c 
		left join "._DBCARBON."._modulofiltros mpp
			on (c.table_schema = '"._DBAPP."'
			      and c.table_name = m.tab
			      and c.column_name = mpp.col
			      and mpp.modulo = '".$_GET["modulo"]."')
		left join "._DBCARBON."._modulo m on (m.modulo = mpp.modulo)
where c.table_schema = '"._DBAPP."'
and c.table_name = '".$_1_u__modulo_tab."'
union

SELECT
  'd' as acao
  ,mpp.idmodulofiltros
  ,m.tab
  ,mpp.col
  ,mpp.psqkey
  ,mpp.psqreq
  ,mpp.psqreqdefault
  ,mpp.visres
FROM
  "._DBCARBON."._modulofiltros mpp 
	left join "._DBCARBON."._modulo m on (m.modulo = mpp.modulo) 
where mpp.modulo = '".$_GET["modulo"]."'
  and m.tab = '".$_1_u__modulo_tab."'
  and not exists (
    select 1 from information_schema.columns c
    where c.table_schema = '"._DBAPP."'
      and c.table_name = m.tab
      and c.column_name = mpp.col
 )";

##################### NOVO:
/*
SELECT
if(concat(c.table_name,c.column_name)=concat(m.tab,mpp.col),'u','i')
	as acao
	,mpp.idmodulofiltros
	,c.table_name
	,c.column_name
	,mpp.psqkey
	,mpp.psqreq
	,mpp.psqreqdefault
	,mpp.visres
	FROM information_schema.columns c
	left join "._DBCARBON."._modulofiltros mpp
	on (c.table_schema = 'carbon40'
			and c.table_name = m.tab
			and c.column_name = mpp.col
			and mpp.modulo = '_lp')
			left join "._DBCARBON."._modulo m on (m.modulo = mpp.modulo)
			where c.table_schema = 'carbon40'
			and c.table_name = '_lp'
			union

			SELECT
			'd' as acao
			,mpp.idmodulofiltros
			,m.tab
			,mpp.col
			,mpp.psqkey
			,mpp.psqreq
			,mpp.psqreqdefault
			,mpp.visres
			FROM
			"._DBCARBON."._modulofiltros mpp
			left join "._DBCARBON."._modulo m on (m.modulo = mpp.modulo)
			where mpp.modulo = '_lp'
					and m.tab = '_lp'
							and not exists (
							select 1 from information_schema.columns c
							where c.table_schema = 'carbon40'
									and c.table_name = m.tab
									and c.column_name = mpp.col
							)

die($_sqlitens);

$_resitens = mysql_query($_sqlitens);

if(!$_resitens){
	die("Erro consulta _sqlitens:".mysql_error());
}
*/

if($_1_u__modulo_tab){
	$sqlfiltros = "SELECT
			m.tab
			,mf.idmodulofiltros
			,mf.col
			,mf.psqkey
			,mf.psqreq
			,mf.psqreqdefault
			,mf.visres
			,mf.parget
			,mf.oculto
			,if(length(mtc.rotcurto)=0,mtc.col,mtc.rotcurto) as rotcurto
		FROM
			"._DBCARBON."._modulo m 
				join "._DBCARBON."._modulofiltros mf on (mf.modulo = m.modulo)
				left join "._DBCARBON."._mtotabcol mtc on (mtc.tab=m.tab and mtc.col = mf.col)
		where
			m.tab = '".$_1_u__modulo_tab."'
			and m.modulo = '".$_1_u__modulo_modulo."'
		order by mtc.ordpos";
	
	//die($sqlfiltros);
	
	$rfiltros = mysql_query($sqlfiltros) or die("Erro ao recuperar filtros: ".mysql_error());
}



?>
<script>
$("input#tab").autocomplete({
	source: [
<?
	//$sqltab = "select concat(table_schema,'.',table_name) as tab from information_schema.tables";
	$sqltab = "show tables";
	$rtab = mysql_query($sqltab) or die("Erro ao pesquisar information_schema: ".mysql_error());
	$virg="";
	while($rowtab=mysql_fetch_row($rtab)){
		echo "\n".$virg."'".$rowtab[0]."'";
		$virg = ",";
	}
?>
	       ]
});

$("input#tabrep").autocomplete({
	source: [
<?
	//$sqltab = "select concat(table_schema,'.',table_name) as tab from information_schema.tables";
	$sqltab = "show tables";
	$rtab = mysql_query($sqltab) or die("Erro ao pesquisar information_schema: ".mysql_error());
	$virg="";
	while($rowtab=mysql_fetch_row($rtab)){
		echo "\n".$virg."'".$rowtab[0]."'";
		$virg = ",";
	}
?>
	       ]
});

$("input#modulofiltronovo").autocomplete({
	minLength: 0
	,source: [
<?

	$sqlmf = "SELECT mtc.col, if(mtc.rotpsq > '',mtc.rotpsq,concat('[Ajustar rà³tulo: ',mtc.col,']'))
	FROM "._DBCARBON."._mtotabcol mtc
	where not exists (
		select 1 from "._DBCARBON."._modulofiltros mf join "._DBCARBON."._modulo m on (m.modulo = mf.modulo)
		where m.tab = mtc.tab
		and mf.col = mtc.col
		and m.modulo = '".$_1_u__modulo_modulo."'
	)
	and mtc.tab = '".$_1_u__modulo_tab."'
	order by mtc.ordpos";
//die($sqlmf);
	$rmf = mysql_query($sqlmf) or die("Erro ao pesquisar modulofiltros: ".mysql_error());
	$virg="";
	while($rowmf=mysql_fetch_row($rmf)){
		echo "\n".$virg."{'value': '".$rowmf[0]."', 'label':'".$rowmf[1]."'}";
		$virg = ",";
	}
?>
	]
	,select: function( event, ui ) {
		
		var strPost = "_ajax_i__modulofiltros_modulo=<?=$_1_u__modulo_modulo?>"+
			"&_ajax_i__modulofiltros_col="+ui.item.value;
		//Efetua post do valor selecionado
		cbpost({
			objetos: strPost
		});
	}
});

//adiciona permissao customizada para tabelas que nao estao explicitamente no corpo do formulario relacionado
$("input#permissaotabela").autocomplete({
	minLength: 0
	,source: [
<?

	$_sqljson = "select TABLE_NAME, TABLE_NAME
					from information_schema.tables t
					where TABLE_SCHEMA='laudo40'
					and not exists(
						select 1 
						from "._DBCARBON."._formobjetos o
						where o.objeto = t.TABLE_NAME
							and o.tipoobjeto = 'tabela'
							and o.modulo = '".$_1_u__modulo_modulo."'
					) 
 				ORDER BY 2";
	//die($_sqljson);
	$_rmjs = mysql_query($_sqljson) or die("Erro ao pesquisar tabelas: ".mysql_error());
	$virg="";
	while($rjs=mysql_fetch_row($_rmjs)){
		echo "\n".$virg."{'value': '".$rjs[0]."', 'label':'".$rjs[1]."'}";
		$virg = ",";
	}
?>
	]
	,select: function( event, ui ) {
		
		var strPost = "_ajax_i__formobjetos_modulo=<?=$_1_u__modulo_modulo?>" +
					"&_ajax_i__formobjetos_form=<?=$_1_u__modulo_urldestino?>"+
					"&_ajax_i__formobjetos_tipoobjeto=tabela"+
					"&_ajax_i__formobjetos_objeto="+ui.item.value +
					"&_ajax_i__formobjetos_inseridomanualmente=Y";

		//Efetua post do valor selecionado
		cbpost({
			objetos: strPost
		}); 
	}
});


</script>

  <table class="inlineblocktop">
  	<tr>
      <td></td>
      <td>
      <?
      if(empty($_1_u__modulo_modulo)){//NAO PERMITIR ALTERACOES POSTERIORES
      ?>
      <input type="text" name="_1_<?=$_acao?>__modulo_modulo" vnulo valfa value="<?=$_1_u__modulo_modulo?>" maxlength="45"><?
      }else{
      ?>
      <label class="idbox"><?=$_1_u__modulo_modulo?></label>
      <input type="hidden" name="_1_<?=$_acao?>__modulo_modulo" value="<?=$_1_u__modulo_modulo?>" vnulo>
      <?
      }
      ?>
      </td>
      <td>
      	Rà³tulo Menu:
      	<input type="text" name="_1_<?=$_acao?>__modulo_rotulomenu" vnulo value="<?=$_1_u__modulo_rotulomenu?>" maxlength="45" size="30"> 
      </td>
      <td>
      </td>
    </tr>
	<tr>
		<td colspan="100" style="vertical-align: top;">

			<div class="detail">
				<div class="cab">Comportamento/Aparàªncia</div>
				<div class="corpo">
					<table>
					<tr>
						<td align="right">
							Menu:
							<select name="_1_<?=$_acao?>__modulo_aparencia" vnulo>
<?
							fillselect(array('BTTXT'=>'Botão','BTTXTPAR'=>'Botão Pai','BTOPT'=>'àcone','BTINV'=>'Invisà­vel','BTHOME'=>'Homepage'),$_1_u__modulo_aparencia);
?>
							</select><br />
<?
if($_1_u__modulo_aparencia!="BTTXTPAR" and !empty($_1_u__modulo_modulo)){
?>
      	Menu Superior:<select name="_1_<?=$_acao?>__modulo_modulopar">
      		<option selected="selected"></option>
			<?
			fillselect("select modulo,rotulomenu from "._DBCARBON."._modulo where aparencia = 'BTTXTPAR' and modulo !='".$_1_u__modulo_modulo."'",$_1_u__modulo_modulopar);
			?>
			</select>
<?
}
?> 
				      	</td>
						<td>
<?
if($_1_u__modulo_aparencia != "BTTXTPAR" and !empty($_1_u__modulo_modulo)){
?>
							&nbsp;&nbsp;&nbsp;
							On Ready?
							<select name="_1_<?=$_acao?>__modulo_ready" vnulo>
								<option>[Selecionar]</option>
							<?						
							fillselect(array('FILTROS'=>'Filtros','URL'=>'Url'),$_1_u__modulo_ready);
							?>
							</select>
							<br />
<?
	if($_1_u__modulo_ready=="FILTROS"){
?>
							
							Rà³tulo Filtros:
      						<input type="text" name="_1_<?=$_acao?>__modulo_titulofiltros" value="<?=$_1_u__modulo_titulofiltros?>" maxlength="45" size="10" placeholder="[Titulo]"> 
<?
	}
}
?>
				      	</td>
				    </tr>
					<tr>
						<td align="right">
<?
if($_1_u__modulo_ready=="FILTROS" and $_1_u__modulo_aparencia!="BTTXTPAR"  and !empty($_1_u__modulo_modulo)){
?>
							&nbsp;&nbsp;&nbsp;
							Botão Novo?
							<select name="_1_<?=$_acao?>__modulo_btnovo" vnulo>
							<?
							fillselect("select 'Y','S' union select 'N','N'",$_1_u__modulo_btnovo);
							?>
							</select><br />
							Botão Imprimir?
							<select name="_1_<?=$_acao?>__modulo_btimprimir" vnulo>
							<?
							fillselect("select 'Y','S' union select 'N','N'",$_1_u__modulo_btimprimir);
							?>
							</select>
<?
}
?>
						</td>
						<td align="right">

				      	</td>
					</tr>
					</table>
				</div><!-- Detail Corpo -->
			</div><!-- Detail -->
<?
if($_1_u__modulo_aparencia!="BTTXTPAR"  and !empty($_1_u__modulo_modulo)){
?>		
			<div class="detail">
				<div class="cab">Formulário relacionado</div>
				<div class="corpo">
<?
//lista os arquivos existentes na pasta /form do carbon, excluindo arquivos ocultos '.' e '..'
$strpathforms = _CARBON_ROOT."form/";

$arrscan = preg_grep('/^([^.])/', scandir($strpathforms));

if(!$arrscan){
	echo "Carbon root não encontrado: ".$strpathforms;
}

$arrformularios=array();
foreach($arrscan as $itemscan){

	$arrformularios["form/".$itemscan] = "form/".$itemscan;

}

//lista os arquivos existentes na pasta /rep do carbon, excluindo arquivos ocultos '.' e '..'
$strpathrep = _CARBON_ROOT."rep/";
$arrscan = preg_grep('/^([^.])/', scandir($strpathrep));

foreach($arrscan as $itemscan){

	$arrformularios["rep/".$itemscan] = "rep/".$itemscan;

}

?>
					
					<select name="_1_<?=$_acao?>__modulo_urldestino">
		      		<option></option>
<?
					fillselect($arrformularios, $_1_u__modulo_urldestino);
?>
					</select>

<?

//Abre o formulario para extrair objetos de banco de dados e arquivos chamadas
$_arqdest = _CARBON_ROOT.$_1_u__modulo_urldestino; 
$contarq = file_get_contents($_arqdest);

if($contarq){
	$arrpalavras = preg_split('/\s+/',$contarq,-1,PREG_SPLIT_NO_EMPTY);
	
	//print_r($arrpalavras);
	
	$arrTabelas = array();
	$arrArqAjax = array();
	$strBulkInsert;
	$virg="";
	
	//Loop em cada palavra do arquivo:
	foreach($arrpalavras as $palavra){
		$strBName="";

		/*
		 * 1 - Extrai todas as tabelas do cà³digo: strings que contém o caractere '_'
		 */
		if(strpos($palavra, "_")>0){
			$arrObjDb = explode("_", $palavra);
	
			//objeto de banco de dados carbon encontrado!
			if(sizeof($arrObjDb)>4){
				if(!empty($arrObjDb[3])){
					//Como a indicacao da tabela aparece mais de uma vez, aramazenar somente ocorrencias distintas
					$arrTabelas[$arrObjDb[3]]=$arrObjDb[3];
				}
			}
		}

		/*
		 * 2 - Extrai os arquivos ajax do cà³digo: string entre (quaisquer)AJAX[quaisquerpalavras].php(quaisquer)
		*/
		
		$pattern = "#ajax[^.*?]*.php#";
		$iajax = preg_match($pattern, $palavra, $matches);
		
		if($iajax>0){
			//echo "\n".$palavra."\n";
			//Como a expressão acima retorna "AJAX/arquivo.php" ehe necessario extrair somente o nome do arquivo/;
			$strBName = pathinfo($matches[0], PATHINFO_BASENAME);
			$arrArqAjax[$strBName] = $strBName;
		
		}

	}//foreach($arrpalavras as $palavra){
	
	//print_r($arrTabelas);
	
	//Apaga registros antigos QUE NAO FORAM INSERIDOS PELO USUARIO
	mysql_query("delete from "._DBCARBON."._formobjetos where modulo = '".$_1_u__modulo_modulo."' and form = '".$_1_u__modulo_urldestino."' and inseridomanualmente = 'N'") or die("Erro ao apagar objetos relacionados: ".mysql_error()."\Nsql: ".$strbulk);
	
	//Caso algum objeto tenha sido encontrado no codigo da pagina, insere manualmente
	if(sizeof($arrTabelas)>0 or sizeof($arrArqAjax)>0){

		//Loop na tabelas para montar o insert em modo bulk
		foreach($arrTabelas as $tabela){

			$strBulkInsert .= $virg."('".$_1_u__modulo_modulo."','".$_1_u__modulo_urldestino."','tabela','".$tabela."','".$_SESSION["SESSAO"]["USUARIO"]."',now())";
			$virg=",";

		}
		
		//Loop nos arquivos ajax para montar o insert em modo bulk
		foreach($arrArqAjax as $arq){

			$strBulkInsert .= $virg."('".$_1_u__modulo_modulo."','".$_1_u__modulo_urldestino."','ajax','".$arq."','".$_SESSION["SESSAO"]["USUARIO"]."',now())";
			$virg=",";

		}

		//Insere no banco
		$strbulk = "insert ignore into "._DBCARBON."._formobjetos (modulo, form,tipoobjeto,objeto,criadopor,criadoem) values ".$strBulkInsert;
		mysql_query($strbulk) or die("Erro ao efetuar bulk insert para objetos relacionados: ".mysql_error()."\Nsql: ".$strbulk);

	}//if(sizeof($arrTabelas)>0){
	
	
	
	/*
	 * Lista as tabelas relacionadas
	 */
	$sqltabsdb = "SELECT * FROM "._DBCARBON."._formobjetos where modulo = '".$_1_u__modulo_modulo."' and form='".$_1_u__modulo_urldestino."' and tipoobjeto='tabela' order by inseridomanualmente, objeto;";
	$rtabs = mysql_query($sqltabsdb) or die("Erro ao recuperar tabelas relacionadas: ".mysql_error());
	
	if(mysql_num_rows($rtabs)){
?>
					<div class="detail">
					<div class="cab">Permissàµes para objetos de Banco de Dados:</div>
					<div class="corpo" style="padding-bottom:6px;">
						<i class="fa fa-database" style="position: absolute;right: 6px;margin-top: 0px;opacity: 0.6;"></i>
<?
		$br="";
		while($rtab=mysql_fetch_assoc($rtabs)){
			echo $br;
?>
	<i class="fa fa-table"></i>&nbsp;<?=$rtab["objeto"]?>
<?
			if($rtab["inseridomanualmente"]=="Y"){
?>
	<span onclick="cbpost({objetos:'_ajax_d__formobjetos_idformobjetos=<?=$rtab["idformobjetos"]?>'})" class="pointer" style="color:red;font-weight: bold;">x</span>
		
<?
			}
			$br="<br>";
		}
	}//if(mysql_num_rows($rtabs)){
?>
						<br><input type="text" id="permissaotabela" size="15" class="acinsert" autocomplete="off" style="margin-top: 8px">

						</div><!-- Detail Corpo -->
					</div><!-- Detail -->	
<?





	/*
	 * Lista os arquivos AJAX relacionados
	*/
	$sqlajax = "SELECT objeto FROM "._DBCARBON."._formobjetos where modulo = '".$_1_u__modulo_modulo."' and form='".$_1_u__modulo_urldestino."' and tipoobjeto='ajax' order by objeto;";
	$rajax = mysql_query($sqlajax) or die("Erro ao recuperar tabelas relacionadas: ".mysql_error());
	
	if(mysql_num_rows($rajax)){
?>
					<div class="detail">
					<div class="cab">Permissàµes para arquivos Ajax:</div>
					<div class="corpo" style="padding-bottom:6px;">
						<div class="icon20filetag" style="position: absolute;right: 6px;margin-top: 0px;opacity: 0.6;"></div>
<?
		$br="";
		while($ra=mysql_fetch_assoc($rajax)){
			echo $br;
?>
	<span class="icon10filetag"></span>&nbsp;<?=$ra["objeto"]?>
<?
			$br="<br>";
		}
?>


						</div><!-- Detail Corpo -->
					</div><!-- Detail -->	
<?
	}//if(mysql_num_rows($rtabs)){

}else{//if($contarq){
	echo('<br>Arquivo inexistente: '.$_arqdest);
}
?>

					
								
				</div><!-- Detail Corpo -->
			</div><!-- Detail -->

			<div class="detail">
				<div class="cab">Eventos</div>
				<div class="corpo">
<style>
.crosstab{
	border-collapse: collapse;
}
.crosstab td{
	border: 1px solid silver;
	background-color: white;
}
.crosstab tr:first-child td:first-child{
	border: none;
	background-color: transparent;
}
.crosstab td.cablateral{
	background-color: #efefef;
}
.crosstab td.cabsuperior{
	background-color: #efefef;
}
</style>
<?



/*
 * MAF: Testa os arquivos de evento: se ele não tiver conteúdo, prevalece conteúdo do DB.
 * Não é necessário verificar permissoes de escrita neste ponto. Estas estão sendo verificadas no cbpost
 */

//ini_set("display_errors","1");
//error_reporting(E_ALL);

//presave
$fgcevento_presave = file_get_contents(_CARBON_ROOT."eventcode/modulo/saveprechange__".$_1_u__modulo_modulo.".php");
if($fgcevento_presave){
	$fgcevento_presave = mb_convert_encoding($fgcevento_presave, "UTF-8", "ISO-8859-1");
	$_1_u__modulo_evento_saveprechange = $fgcevento_presave;
}
//possave
$fgcevento_possave = file_get_contents(_CARBON_ROOT."eventcode/modulo/saveposchange__".$_1_u__modulo_modulo.".php");
if($fgcevento_possave){
	$fgcevento_possave = mb_convert_encoding($fgcevento_possave, "UTF-8", "ISO-8859-1");
	$_1_u__modulo_evento_saveposchange = $fgcevento_possave;
}

//presearch
$fgcevento_presearch = file_get_contents(_CARBON_ROOT."eventcode/modulo/searchpre__".$_1_u__modulo_modulo.".php");
if($fgcevento_presearch){
	$fgcevento_presearch = mb_convert_encoding($fgcevento_presearch, "UTF-8", "ISO-8859-1");
	$_1_u__modulo_evento_presearch = $fgcevento_presearch;
}

//possearch
$fgcevento_possearch = file_get_contents(_CARBON_ROOT."eventcode/modulo/searchpos__".$_1_u__modulo_modulo.".php");
if($fgcevento_presearch){
	$fgcevento_possearch = mb_convert_encoding($fgcevento_possearch, "UTF-8", "ISO-8859-1");
	$_1_u__modulo_evento_possearch = $fgcevento_possearch;
}

$backcolor1 = !empty($_1_u__modulo_evento_saveprechange)?";background-color:#FFFF8C;":"";
$backcolor2 = !empty($_1_u__modulo_evento_saveposchange)?";background-color:#FFFF8C;":"";
$backcolor3 = !empty($_1_u__modulo_evento_presearch)?";background-color:#FFFF8C;":"";
$backcolor4 = !empty($_1_u__modulo_evento_possearch)?";background-color:#FFFF8C;":"";

//Em caso de existir valor no banco, mas o arquivo não ter retornado nada, colorir de vermelho
$backcolor1 = (!$fgcevento_presave and $backcolor1!="")?";background-color:red;":$backcolor1;
$backcolor2 = (!$fgcevento_possave and $backcolor2!="")?";background-color:red;":$backcolor2;
$backcolor3 = (!$fgcevento_presearch and $backcolor3!="")?";background-color:red;":$backcolor3;
$backcolor4 = (!$fgcevento_possearch and $backcolor4!="")?";background-color:red;":$backcolor4;

?>
					<Table class="crosstab">
					<tr>
						<td></td>
						<td class="cablateral" align="center">Pre</td>
						<td class="cablateral" align="center">Post</td>
					</tr>
					<tr>
						<td class="cablateral">SAVE</td>
						<td>
							<textarea rows="2" cols="30" style="font-family:monospace; font-size: 9px;border: 1px inset;<?=$backcolor1?>" name="_1_<?=$_acao?>__modulo_evento_saveprechange"><?=$_1_u__modulo_evento_saveprechange?></textarea>
						</td>
						<td>
							<textarea rows="2" cols="30" style="font-family:monospace; font-size: 9px;border: 1px inset;<?=$backcolor2?>" name="_1_<?=$_acao?>__modulo_evento_saveposchange"><?=$_1_u__modulo_evento_saveposchange?></textarea>
						</td>
					</tr><tr>
						<td class="cablateral">SEARCH</td>
						<td>
							<textarea rows="2" cols="30" style="font-family:monospace; font-size: 9px;border: 1px inset;<?=$backcolor3?>" name="_1_<?=$_acao?>__modulo_evento_presearch"><?=$_1_u__modulo_evento_presearch?></textarea>
						</td>
						<td>
							<textarea rows="2" cols="30" style="font-family:monospace; font-size: 9px;border: 1px inset;<?=$backcolor4?>" name="_1_<?=$_acao?>__modulo_evento_possearch"><?=$_1_u__modulo_evento_possearch?></textarea>
						</td>
					</tr>
					</Table>
				</div><!-- Detail Corpo -->
			</div><!-- Detail -->
<?}//if($_1_u__modulo_aparencia!="BTTXTPAR"  and !empty($_1_u__modulo_idmodulo)){?>
		</td><td style="vertical-align: top;">
			
<?
if($_1_u__modulo_ready=="FILTROS" and $_1_u__modulo_aparencia!="BTTXTPAR"){
?>
			<div class="detail inlineblocktop">
				<div class="cab">Tela de Pesquisa</div>
				<div class="corpo">
					<table>
					<tr>
						<td>Tabela p/ Pesquisa:</td>
						<td>
							<input type="text" name="_1_<?=$_acao?>__modulo_tab" id="tab" value="<?=$_1_u__modulo_tab?>" maxlength="45" size="33" class="acsearch" placeholder="[Selecionar Tabela]">
<?
if(!empty($_1_u__modulo_tab)){
?>
							<a href="?_modulo=_mtotabcol&acao=u&TABLE_NAME=<?=$_1_u__modulo_tab?>&_autoform=Y" style="font-size: 7px;" target="_blank">Abrir</a>
<?
}else{
?>
<span onclick="$.smallBox('Salve os dados para editar a tabela relacionada')">Abrir</span>
<?
}
?>
						</td>
				    </tr>
					<tr>
						<td>Ordenação Pesquisa:</td>
						<td>
							<input type="text" name="_1_<?=$_acao?>__modulo_orderby" value="<?=$_1_u__modulo_orderby?>" maxlength="45" size="41">							
						</td>
				    </tr>				    
				    <tr>
				    	<td colspan="10">
				    		<table class="grid noover">
				    		<tr>
				    			<th>Campos:</th>
				    			<th>Psq Key</th>
				    			<th>Psq Req</th>
				    			<th>Vis</th>
				   				<th>Par Get</th>
				    			<th>Filtro Oculto</th>
				    			<th></th>
				    		</tr>
<script> 
function toggle(inId,inCol,inChk){

	var vYN = (inChk.checked)?"Y":"N";
	
	var strPost = "_ajax_u__modulofiltros_idmodulofiltros="+inId
				+ "&_ajax_u__modulofiltros_"+inCol+"="+vYN;
	
	cbpost({
		objetos: strPost
	});

	//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
	//@ sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;  
}
function excluiitem(inId){
	
	var strPost = "_ajax_d__modulofiltros_idmodulofiltros="+inId;
	
	cbpost({
		objetos: strPost
	});

	//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
	//@ sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
}
</script>
<?
while($rwf = mysql_fetch_assoc($rfiltros)){
?>
							<tr>
				    			<td title="<?=$rwf["col"]?>"><?=$rwf["rotcurto"]?></td>
				    			<td><input type="checkbox" onclick="toggle(<?=$rwf["idmodulofiltros"]?>,'psqkey',this)" <?=($rwf["psqkey"]=="Y")?"checked":"";?>></td>
				    			<td><input type="checkbox" onclick="toggle(<?=$rwf["idmodulofiltros"]?>,'psqreq',this)" <?=($rwf["psqreq"]=="Y")?"checked":"";?>></td>
				    			<td><input type="checkbox" onclick="toggle(<?=$rwf["idmodulofiltros"]?>,'visres',this)" <?=($rwf["visres"]=="Y")?"checked":"";?>></td>
				    			<td><input type="checkbox" onclick="toggle(<?=$rwf["idmodulofiltros"]?>,'parget',this)" <?=($rwf["parget"]=="Y")?"checked":"";?>></td>
				    			<td><input type="checkbox" onclick="toggle(<?=$rwf["idmodulofiltros"]?>,'oculto',this)" <?=($rwf["oculto"]=="Y")?"checked":"";?>></td>
				    			<td onclick="excluiitem(<?=$rwf["idmodulofiltros"]?>)" class="pointer" style="color:red;font-weight: bold;">
                                    <i class="fa fa-times"></i>
				    			</td>
							</tr>
<?
}
?>
							<tr>
								<td>
									<input type="text" id="modulofiltronovo" size="15" class="acinsert">
								</td>
							</tr>
				    		</table>
				  		</td>
				    </tr>
					</table>
				</div><!-- Detail Corpo -->
			</div><!-- Detail -->
<?
}
?>
		</td>
		<td style="vertical-align: top;">
<?
if($_1_u__modulo_ready=="FILTROS" and $_1_u__modulo_aparencia!="BTTXTPAR"){
?>		

			<div class="detail inlineblocktop">
				<div class="cab">Modos de Impressão</div>
				<div class="corpo">
<?
$sqlrep="select * from "._DBCARBON."._mtorep where modulo='".$_1_u__modulo_modulo."'";
$resrep=mysql_query($sqlrep) or die("erro ao buscar modos de impressão sql=".$sqlrep);
$qtdrep=mysql_num_rows($resrep);
	if($qtdrep<1){
?>				
				<table>
				<tr>
					<td>
						<div class="abrenovo" title="Adicionar Modo" onclick="cbpost({objetos:'&_ajax_i__mtorep_modulo=<?=$_1_u__modulo_modulo?>'});"></div>
					</td>
				</tr>
				</table>
<?
	}else{
		$ln=1;
	 while($rowrep=mysql_fetch_assoc($resrep)){
		$ln=$ln+1;
?>	
	
				<table>
				<tr> 
					<td></td> 
					<td>
						<input 
							name="_<?=$ln?>_<?=$_acao?>__mtorep_idmtorep" 
							type="hidden" 
							value="<?=$rowrep['idmtorep']?>" 
							readonly='readonly'					>
					</td> 
				</tr>
				<tr> 
					<td>Cabeçalho</td> 
					<td>
						<input 
							name="_<?=$ln?>_<?=$_acao?>__mtorep_header" 
							type="text" 
							value="<?=$rowrep['header']?>" 
												>
					</td> 
				</tr>
				<tr> 
					<td>Rodapé</td> 
					<td>
						<input 
							name="_<?=$ln?>_<?=$_acao?>__mtorep_footer" 
							type="text" 
							value="<?=$rowrep['footer']?>" 
												>
					</td> 
				</tr>
				<tr> 
					<td>Mostra Contador?</td> 
					<td>
						<select name="_<?=$ln?>_<?=$_acao?>__mtorep_showtotalcounter">
							<?fillselect("select 'N','N' union select 'Y','Y'",$rowrep['showtotalcounter']);?>
						</select>
					</td> 
				</tr>
				<tr> 
					<td>Mostra Filtros?</td> 
					<td>
						<select name="_<?=$ln?>_<?=$_acao?>__mtorep_showfilters">
							<?fillselect("select 'Y','Y' union select 'N','N'",$rowrep['showfilters']);?>
						</select>
					</td> 
				</tr>
				<tr> 
					<td colspan="2">
			    	<div class="detail " >
			        <div class="cab">Quebras de Página</div>
			        <div class="corpo">
				    	<table>
				       	<tr>
				    		<td>Quebrar automat. apà³s</td>
						  	<td>
								<input 
									name="_<?=$ln?>_<?=$_acao?>__mtorep_pbauto" 
									type="text" 
									size="2"
									value="<?=$rowrep['pbauto']?>" 
														>&nbsp;Registros
							</td> 
				    	</tr>
				    	<tr>
				    		<td>Quebra Grupo</td>
				    		<td>
							<select name="_<?=$ln?>_<?=$_acao?>__mtorep_newgrouppagebreak">
								<?fillselect("select 'N','N' union select 'Y','Y'",$rowrep['newgrouppagebreak']);?>
							</select>
							</td>
						</tr>
						</table>
				    </div>
				    </div>
					</td>
				</tr>
				<tr> 
					<td>Tab/View</td> 
					<td>
							<input type="text" name="_<?=$ln?>_<?=$_acao?>__mtorep_tab" id="tabrep" value="<?=$rowrep['tab']?>" maxlength="45" size="33" class="acsearch" placeholder="[Selecionar Tabela]">
<?
if(!empty($_1_u__modulo_tab)){
?>
							<a href="?_modulo=_mtotabcol&acao=u&TABLE_NAME=<?=$rowrep['tab']?>&_autoform=Y" style="font-size: 7px;" target="_blank">Abrir</a>
<?
}else{
?>
<span onclick="$.smallBox('Salve os dados para editar a tabela relacionada')">Abrir</span>
<?
}
?>
					</td>
				</tr>
				</table>
				
<?

if($rowrep['idmtorep']){
	
	/*
	 * maf 081110: Caso a tabela informada na pagina de pesquisa seja alterada, é necessário excluir os relacionamentos antigos 
	 * para não sobrar lixo na tabela ou gerar informacoes de campos errados na tela
	 */
	if(!empty($rowrep['idmtorep'])  and !empty($rowrep['tab'])){

		$_sqldeltabant = "delete 
			FROM "._DBCARBON."._mtorepfld  
			where idmtorep = '".$rowrep['idmtorep']."'  
			and tab != '".$rowrep['tab']."'";
		//die($_sqldeltabant);
		mysql_query($_sqldeltabant) or die("Erro ao apagar dados relacionados da tabela anterior: ".mysql_error());
	
	}
	
	$sqlfld = "SELECT 
			tc.tab, 
			tc.col,
			tc.datatype, 
			tc.rotcurto, 
			f.idmtorepfld,
			f.idmtorep,
			f.visres,
			f.align,
			f.grp,
			f.ordseq,
			f.ordtype,
			f.tsum,
			f.tavg,
			f.hyperlink,
			(case when isnull(f.idmtorepfld) then 'i' else 'u' end) as act
		from ("._DBCARBON."._mtorep r join "._DBCARBON."._mtotabcol tc) 
			left join "._DBCARBON."._mtorepfld f on (f.idmtorep = r.idmtorep and f.tab = tc.tab and f.col = tc.col)
		where  r.tab = tc.tab
			and r.idmtorep = ".$rowrep['idmtorep']."
		order by tc.ordpos";
	
	$resfld = mysql_query($sqlfld);
	
	if(!$resfld){
		die("Erro consulta de Campos:".mysql_error());	 
	}
?>

<div class="detail inlineblocktop">
	<div class="cab">Campos do Relatà³rio</div>
	<div class="corpo">	
	<table>
	<tr>
		<td></td>
		<td>Table</td>
		<td>Coluna</td>
		<td nowrap style="background-color:#ccccff;"><img src="img/invert.png" border="0" class="cvisres" title="Inverter" onclick="invertdrop('cvisres','Y','N');">&nbsp;Vis?</td>
		<td nowrap>Align</td>
		<td nowrap><img src="img/invert.png" border="0" class="cgrp" title="Inverter" onclick="invertdrop('cgrp','Y','N');">&nbsp;Group?</td>
		<td nowrap><img src="img/invert.png" border="0" class="ctsum" title="Inverter" onclick="invertdrop('ctsum','Y','N');">&nbsp;Sum</td>
		<td nowrap><img src="img/invert.png" border="0" class="ctavg" title="Inverter" onclick="invertdrop('ctavg','Y','N');">&nbsp;Avg</td>
		<td nowrap>Order (+Pos)</td>
		<td>Hyperlink</td>
	</tr>
<?
	
	while ($rf = mysql_fetch_array($resfld)) {
	
		$ln++;

		switch ($rf["act"]) {
			case "i":
				$icon = "<img src='img/estrela16.png' border='0' alt='Novo Item'>";
			break;
			case "u":
				$icon = "<img src='img/estrela16br.png' border='0' alt='Alterar Item'>";
			break;
			
		}
		
		$trcolor = "";
		$trstyle = "";

		if($rf["visres"] == "Y")$trcolor = "#ccccff";
		if($rf["psqkey"] == "Y")$trcolor = "#99cc99";
		if($rf["psqreq"] == "Y")$trcolor = "#ff9933";

		$trstyle="style='background-color:".$trcolor.";'";
		
		//echo $trstyle;
?>
	<tr style="background-color:<? echo $trcolor;?>;">
		<td><?=$icon?>
			<input type="hidden" name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_idmtorepfld" value="<?=$rf["idmtorepfld"]?>">
			<input type="hidden" name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_idmtorep" value="<?=$rowrep['idmtorep']?>">
			<input type="hidden" name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_tab" value="<?=$rf["tab"]?>">
			<input type="hidden" name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_col" value="<?=$rf["col"]?>">
		</td>
		<td><?=$rf["tab"]?></td>
		<td title="<?=$rf["rotcurto"].": ".$rf["datatype"]?>" nowrap><?=$rf["rotcurto"]?></td>
	    <td>
		    <select name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_visres" style="font-size:9px;" class="cvisres">
				<?fillselect("select 'Y','Y' union select 'N','N'",$rf["visres"]);?>
	        </select>
	    </td>
	    <td>
		    <select name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_align" style="font-size:9px;" class="calign">
				<?fillselect("select 'left','L' union select 'center','C' union select 'right','R'",$rf["align"]);?>
	        </select>
	    </td>
	    <td>
		    <select name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_grp" style="font-size:9px;" class="cgrp">
				<?fillselect("select 'N','N' union select 'Y','Y'",$rf["grp"]);?>
	        </select>
	    </td>
	    <td>
		    <select name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_tsum" style="font-size:9px;" class="ctsum">
				<?fillselect("select 'N','N' union select 'Y','Y'",$rf["tsum"]);?>
	        </select>
	    </td>
	    <td>
		    <select name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_tavg" style="font-size:9px;" class="ctavg">
				<?fillselect("select 'N','N' union select 'Y','Y'",$rf["tavg"]);?>
	        </select>
	    </td>
	    <td>
		    <select name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_ordtype" style="font-size:9px;"class="cordtype">
				<?fillselect("select '','' union select 'asc','A' union select 'desc','D'",$rf["ordtype"]);?>
	        </select>
			<input 
				name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_ordseq"
				type="text" 
				value="<?=$rf["ordseq"]?>"
				style="font-size:10px;padding:0px;margin:0px;" 
				size=1>
	    </td>
	    <td>
		<input 
			name="_<?=$ln?>_<?=$rf["act"]?>__mtorepfld_hyperlink" 
			type="text" 
			value="<?=$rf["hyperlink"]?>" 
			style="font-size:10px;padding:0px;margin:0px;">
		</td> 

	  </tr>
<?
	}//while
?>

	</table>
</div>
</div>
<?
}//if $_1_u__mtorep_idmtorep

?>				
				
				
				
<?
		}
	}
?>				

				</div>
			</div>
<?
}
?>			
		</td>
    </tr>
  </table>
