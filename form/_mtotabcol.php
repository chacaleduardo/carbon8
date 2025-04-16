<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

if(empty($_POST)){

	if(empty($_GET["PK"])){
		die("PK (schema.table_name) não informado");
	}

	$table_schema_table_name = $_GET["PK"];

	$infschema = explode(".",$table_schema_table_name);

	$table_schema=$infschema[0];
	$table_name=$infschema[1];

	//Atualiza retarraytabdef
	retarraytabdef($table_name,$table_schema);
}else{
	//print_r($_POST);
	$table_name=$_arrRamCache[1]['_mtotabcol']['tab'];
}

//?teste=10&TABLE_SCHEMA=frmwrk&TABLE_NAME=mtotabcol

/* if(empty($table_name)){
	die("TABLE_NAME não informado");
} */

/*
* A função getDbTabela() deve ser utilizada aqui, visto que o Sistema pode conter tabelas em 2 ou mais bancos distintos
* Essa informação é estruturada na constante _CUSTOMTABDB em appvar
*/
$sql = "SELECT 
if(concat(c.table_name,c.column_name)=concat(mtc.tab,mtc.col),'u','i')
as acao
,mtc.idmtotabcol
,c.table_name
,c.column_name
,mtc.tab
,mtc.col
,mtc.ftskey
,mtc.cardinality
,mtc.perfindice
,c.ordinal_position
,mtc.datatype
,mtc.primkey
,mtc.autoinc
,mtc.nullable
,mtc.dbdefault
,mtc.acsum
,mtc.rotcurto
,mtc.rotlongo
,mtc.rotpsq
,mtc.prompt
,mtc.default
,mtc.code
,mtc.codeeval
,mtc.dropsql
,mtc.promptativo
,mtc.auditar
,mtc.ramcache
,mtc.ramcachetmr
,replace(replace(instr(c.extra,'auto_increment') > 0,1,'Y'),0,'N') autoincori
,substr(c.is_nullable,1,1) nullableori
,replace(replace(instr(c.column_key,'PRI') > 0,1,'Y'),0,'N') primkeyori
,c.data_type datatypeori
,if(length(c.column_default)>0,'Y','N') dbdefaultori
,mtc.ordpos
FROM 
information_schema.columns c left join "._DBCARBON."._mtotabcol mtc 
on (c.table_schema = '".getDbTabela($table_name)."'
	and c.table_name = mtc.tab
	and c.column_name = mtc.col

)
WHERE c.table_schema = '".getDbTabela($table_name)."'
		and c.table_name = '".$table_name."'
UNION
SELECT
'd' as acao
,mtc.idmtotabcol
,mtc.tab
,mtc.col
,mtc.ftskey
,''
,''
,mtc.cardinality
,mtc.perfindice
,mtc.ordpos
,mtc.datatype
,mtc.primkey
,mtc.autoinc
,mtc.nullable
,mtc.dbdefault
,mtc.acsum
,mtc.rotcurto
,mtc.rotlongo
,mtc.rotpsq
,mtc.prompt
,mtc.default
,mtc.code
,mtc.codeeval
,mtc.dropsql
,mtc.promptativo
,mtc.auditar
,mtc.ramcache
,mtc.ramcachetmr
,'' inc
,'' nullable
,'' columnkey
,'' datatype
,'' dbdefaultori
,'' ordinal_position
FROM 
"._DBCARBON."._mtotabcol mtc 
WHERE mtc.tab = '".$table_name."'
and not exists (
	select 1 from information_schema.columns c
	where c.table_schema = '".getDbTabela($table_name)."'
	and c.table_name = mtc.tab
	and c.column_name = mtc.col
) order by case when ordpos is null then 2 else 1 end,CAST(ordpos as DECIMAL) asc,
prompt desc,ordinal_position asc";

//echo $sql; die;
$res = mysql_query($sql);

if(mysqli_num_rows($res)==0){
?>
	<div class='alert alert-danger'>Erro: Tabela não encontrada no Dicionário de Dados ou Information Schema.
		<?if(_DBAPP!==_DBCARBON){?>
				<br>
				Verifique se a tabela [<?=$table_name?>] existe no banco de dados e/ou está devidamente configurada no appvar.
		<?}?>
	</div>
<br>
<?
}

//Recuperar o table type para diferenciar views e tables
$stype = "select table_type, table_rows from information_schema.tables c 
		where c.table_schema = '".getDbTabela($table_name)."'
		and table_name = '".$table_name."'";

$restype = mysql_query($stype) or die("Erro ao recuperar table type: ".  mysql_error()."<br>Sql: ".$stype);
$rtype = mysql_fetch_assoc($restype);
$tbtype = $rtype["TABLE_TYPE"];
$tbrows= $rtype["TABLE_ROWS"];

if(!$res){
	die("Erro consulta mtotabcol:".mysql_error());	 
}

?>


	
	
	<div class="col-md-12 zeroauto">
		<span class="cinzaclaro bold fonte15">Dicion&aacute;rio para a tabela </span>
		<span class="bold fonte15"><?=$table_name?>&nbsp;&nbsp;&nbsp;</span>
		<span class="cinza fonte15">(<?=$tbrows?>) registros</span>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="cinzaclaro bold">Ignora Empresa?</span>
                <span class="cinza bold">                
                <?
                        $sqlu="select * from _passidempresa where tabela='".$table_name."'";
                            $resu = d::b()->query($sqlu) or die("A Consulta da Unidade falhou : " . mysqli_error() . "<p>SQL: $sqlu");
                            //echo($sqlu);
                            $qtdu=mysqli_num_rows($resu);
                            if($qtdu>0){
                                $rowu=mysqli_fetch_assoc($resu);
                            ?>
                                    <button id=""  type="button" class="btn btn-success btn-xs" onclick="CB.post({objetos:'_ajax_d__passidempresa_id_passidempresa=<?=$rowu["id_passidempresa"]?>'})">
                                        SIM
                                    </button>
                                           
                        <?
                            }else{		
                        ?>
                                    <button id=""  type="button" class="btn btn-primary btn-xs" onclick="CB.post({objetos:'_ajax_i__passidempresa_tabela=<?=$table_name?>'})">
                                        NÃO
                                    </button>
                         <? }?>
                </span>
    <br/><br>
	<table class="table-striped planilha grade">
	<tr>
	    <th>Column</th>
	    <th title="Performance do índice" align="center"><i class="fa fa-bolt"></i></th>
	    <th title="Cardinalidade do índice" align="center"><i class="fa fa-pie-chart"></th>
		<th>Ftskey</th>
	    <th>Aud</th>
        <th>Cache</th>
        <th>Cache ⏱️</th>
	    <th>Ord</th>
	    <th>Type</th>
	    <th>PKey</th>
	    <th>AInc</th>
	    <th>ANull</th>
		<th title="Valor Default na Tabela">VDef</th>
		<th>ACSum</th>
	    <th>R&oacute;t Curto</th>
	    <th>R&oacute;t Psq</th>
	    <th>Prompt</th>
	    <th>Code</th>
        <th>Prompt Drop</th>
	  </tr>
<?
$ln = 0;
while ($row = mysql_fetch_array($res)) {
	
	$ln++;
	
//	print_r($row); die;
	if($row["acao" ]=="d"){
		//$styledel = "class='bgred'";
		
	}

	switch ($row["acao" ]) {
		case "i":
			$icon = "<i class='fa fa-plus verde' title='Nova coluna'></i>";
		break;
	
		case "u":
			if($tbtype!="VIEW" && ($row["nullableori"]!=$row["nullable"] or $row["autoincori"]!=$row["autoinc"] or $row["primkeyori"]!=$row["primkey"] or $row["datatypeori"]!=$row["datatype"])){
			$icon = "<i class='fa fa-asterisk vermelho' title='Coluna alterada'></i>";
			}else{
				$icon = "";
			}
		break;
	
		case "d":
			$icon = "<i class='fa fa-trash' title='Coluna a ser excluída'>";
		break;
	
		default:
			$icon = "";
		break;
	
	}

	$icoperf="";
	switch (true) {
		case $row["perfindice"]!==0 and $row["perfindice"]=="":
			$icoperf="";
			break;
		case $row["primkey"]=="Y" and $row["perfindice"]!=="";
			$icoperf="<i class='fa fa-ban vermelhoescuro blink' title='Índice desnecessário iniciando pela Primary Key'></i>";
			break;
		case (int)$row["perfindice"]>=5:
			$icoperf="<i class='fa fa-bolt verde' title='Índice com boa performance'></i>";
			break;
		case (int)$row["perfindice"]<5:
			$icoperf="<i class='fa fa-bolt vermelhoescuro blink' title='Índice Ruim'></i>";
			break;
		default:
			break;
	}


?>
	  <tr>
	    <td>
	    	<?=$icon?>
			<input type="hidden" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_idmtotabcol" value="<?=$row["idmtotabcol"]?>">
	    	<input type="hidden" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_col" value="<?=$row["COLUMN_NAME"]?>">
			<input type="hidden" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_tab" value="<?=$row["TABLE_NAME"]?>">
			<?=$row["COLUMN_NAME"]?>
	    </td>
	    <td nowrap><?=$icoperf?> <?=$row["perfindice"]?></td>
	    <td><?=$row["cardinality"]?></td>
		 <td>
		    <select name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_ftskey" class="7preto <?=($row["ftskey"]!="Y")?"fade":"vermelho bold";?>">
				<?fillselect("select 'N','N' union select 'Y','Y'",$row["ftskey"]);?>
	        </select>
	    </td>
	    <td>
		    <select name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_auditar" class="7preto <?=($row["auditar"]!="Y")?"fade":"vermelho bold";?>">
				<?fillselect("select 'N','N' union select 'Y','Y'",$row["auditar"]);?>
	        </select>
	    </td>
		<td>
<?if($row["primkey"]!=="Y"){?>
		    <select name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_ramcache" class="7preto <?=($row["ramcache"]!="Y")?"fade":"verde bold";?>">
				<?fillselect(array('N'=>'N','Y'=>'Y'),$row["ramcache"]);?>
	        </select>
<?}?>
		</td>
		<td>
<?if($row["primkey"]!=="Y" and ($row["datatypeori"]=="date" or $row["datatypeori"]=="datetime" or $row["datatypeori"]=="timestamp")){?>
	        <select name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_ramcachetmr" class="7preto <?=($row["ramcachetmr"]!="Y")?"fade":"verde bold";?>">
				<?fillselect(array('N'=>'N','Y'=>'Y'),$row["ramcachetmr"]);?>
	        </select>
<?}?>
		</td>
	    <td>
	    	<input type="text" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_ordpos" value="<?=$row["ordpos"]?>" size="1">
	    </td>
	    <td>
			<input type="hidden" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_datatype" value="<?=$row["datatypeori"]?>"><?=$row["datatypeori"]?>
		    
	          <? /* Opcional: select para preencher drop down
	              * fillselect("select * from
								(select 'varchar' as col1 ,'varchar' as col2
								union select 'bigint','bigint'
								union select 'char','char'
								union select 'datetime','datetime'
								union select 'date','date'
								union select 'int','int'
								union select 'smallint','smallint'
								union select 'timestamp','timestamp'
								union select 'tinyint','tinyint'
								union select 'longtext','longtext'
								union select 'double','double'
								union select 'decimal','decimal') tipos order by col1",$row["datatypeori"]); */?>
	        
	    </td>
	    <td>
<?
//Mostrar drop em caso de views, para se selecionar alguma PK para realização do FTS
if($tbtype=="VIEW"){?>
			<select name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_primkey" class="<?=($row["primkey"]!="Y")?"fade":"vermelho bold";?>">
				<?fillselect(array("N"=>"N","Y"=>"Y"),$row["primkey"])?>
			</select>
<?}else{?>
	    	<input type="hidden" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_primkey" value="<?=$row["primkeyori"]?>"><?=$row["primkeyori"]?>
<?}?>
	    </td>
	    <td>
	    	<input type="hidden" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_autoinc" value="<?=$row["autoincori"]?>"><?=$row["autoincori"]?>
	    </td>
	    <td>
			<input type="hidden" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_nullable" value="<?=$row["nullableori"]?>"><?=$row["nullableori"]?>
	    </td>
		<td>
			<input type="hidden" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_dbdefault" value="<?=$row["dbdefaultori"]?>"><?=$row["dbdefaultori"]?>
	    </td>
		<td>
			<?if(in_array($row["datatypeori"], ["int", "bigint", "decimal", "double", "smallint", "float"])){?>
				<select name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_acsum" class="<?=($row["acsum"]!="Y")?"fade":"azul bold";?>">
					<?fillselect("select 'N','N' union select 'Y','Y'",$row["acsum"]);?>
				</select>
			<?}?>
		</td>
	    <td>
	    	<input type="text" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_rotcurto" value="<?=$row["rotcurto"]?>" size="10">
	    </td>
	    <td>
	    	<input type="text" name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_rotpsq" value="<?=$row["rotpsq"]?>" size="10">
	    </td>
	    <td>
		    <select name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_prompt">
				<option value=""></option>
				<?fillselect(
				    array('json'  => 'Json Tags','var'=>'Var Default','jsonpicker'=>'Json Picker')
				    ,$row["prompt"]);?>
	        </select>
        </td>
	    <td>
<?
//MAF: Testa o arquivo de code: se ele não tiver conteúdo, prevalece conteúdo do DB. Caso contrário, sistema de arquivos sobrepàµes DB.
$nomearquivo=_CARBON_ROOT."eventcode/mtotabcol/".$row["table_name"]."__".$row["column_name"]."__prompt.php";
$fg_code = file_get_contents($nomearquivo);
if($fg_code){
	if(mb_detect_encoding($fg_code, 'UTF-8', true)){
		$fg_code = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $fg_code);
		if(!$fg_code)echo "<span class='vermelho'>Erro ao traduzir caracteres para o arquivo ".$nomearquivo;
	}
	$row["code"] = $fg_code;
}
?>
	    	<textarea name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_code" rows="1"><?=$row["code"]?></textarea>
	    </td>
            <td>
                <textarea name="_<?=$ln?>_<?=$row["acao"]?>__mtotabcol_dropsql" rows="1"><?=$row["dropsql"]?></textarea>                
            </td>
	  </tr>
  
<?
}
?>
	  <tr><td colspan="100">&nbsp;</td></tr>
	</table>
<br>

<a href="geracodigopag.php?table_schema=<?=$table_schema?>&table_name=<?=$table_name?>" target="_blank">Gerar C&oacute;digo!</a>


	</div>
