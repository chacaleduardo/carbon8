<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
	include_once("../inc/php/cbpost.php");
}

//Parametros mandatarios para o carbon
$pagvaltabela = "sgdoctipodocumento";
$pagvalcampos = array(
	"idsgdoctipodocumento" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from sgdoctipodocumento where  idsgdoctipodocumento = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
?>
<style>
	#editor1Container{
		height: 90vh;
	}
	#editor1{
		height: 90vh;
		width: 100%;
		overflow-y: scroll;
		background-color: white;
	}
	
	.transparente{
		opacity: 0;
		transition: opacity .25s ease-in-out;
		-moz-transition: opacity .25s ease-in-out;
		-webkit-transition: opacity .25s ease-in-out;
	}
	.opaco{
		opacity: 1;
		transition: opacity .25s ease-in-out;
		-moz-transition: opacity .25s ease-in-out;
		-webkit-transition: opacity .25s ease-in-out;
	}

    .opcoes{
    background: #eee;
    padding: 4px;
    border: 1px solid #ccc;
    margin-top: 2px;
    border-radius: 4px;
    float: left;
    margin-right: 4px;
}

.config-status{
        display: flex;
}
.panel-status{
        height: auto;
        padding: 5px;
        flex-direction: column;
}

</style>

<div class="row">
<div class="col-md-12" >
<div class="panel panel-default" >
    <div class="panel-heading">
	<table>
	    <tr> 
	    <td></td> 
	    <td>
		<input name="_1_<?=$_acao?>_sgdoctipodocumento_idsgdoctipodocumento" type="hidden" value="<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>" readonly="readonly">
	    </td> 
	    <td>Tipo:</td>
	    <td>
		<select name="_1_<?=$_acao?>_sgdoctipodocumento_idsgdoctipo" vnulo>
		    <option></option>
		<?fillselect("select idsgdoctipo,rotulo 
				from sgdoctipo 
				where status='ATIVO' order by idsgdoctipo ",$_1_u_sgdoctipodocumento_idsgdoctipo);?>		
		</select>
	    </td>
	    <td>Classificação:</td> 
	    <td><input name="_1_<?=$_acao?>_sgdoctipodocumento_tipodocumento" type="text" value="<?=$_1_u_sgdoctipodocumento_tipodocumento?>" size="50"></td> 

	    <td>Status:</td> 
	    <td>	
	    <select name="_1_<?=$_acao?>_sgdoctipodocumento_status" id="status">
		    <?fillselect("select 'ATIVO','ATIVO' union select 'INATIVO','INATIVO'",$_1_u_sgdoctipodocumento_status);?>
	    </select>
	    </td> 
	    <td>Conferencia:</td> 
	    <td>	
	    <select name="_1_<?=$_acao?>_sgdoctipodocumento_conferencia" id="conferencia">
		    <?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_sgdoctipodocumento_conferencia);?>
	    </select>
	    </td> 
	</tr> 
	</table>
    
    </div>
    <div class="panel-body" > 
        <div class='row'>
            <div class="col-md-12">           
                <div class="col-md-2">
                    <div class="input-group" style="margin-top: 5px;">
                        <span class="input-group-addon size10">Vencimento:</span>
                        <select  class="size10" <?=$disabled?> name="_1_<?=$_acao?>_sgdoctipodocumento_vencimento">
                            <option></option>
                            <?fillselect("SELECT '30','Mensal' union SELECT '60','Bimestral' union SELECT '90','Trimestral' union SELECT '182','Semestral' union select '365','Anual' union select '730','Bianual' union select '1095','Trianual'",$_1_u_sgdoctipodocumento_vencimento);?>		
                        </select>
                    </div>

                    <div class="input-group" style="margin-top: 5px;">
                        <span class="input-group-addon size10">Prioridade:</span>
                        <select class="size10"  name="_1_<?=$_acao?>_sgdoctipodocumento_prioridade">
                            <option></option>
                            <?fillselect("select 'BAIXA','Baixa'	
                                    union select 'MEDIA','Média'
                                    union select 'ALTA','Alta'",$_1_u_sgdoctipodocumento_prioridade);?>		
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div style="display: flex;flex-direction: row;justify-content: space-around;" class="col-md-12">
                        <?if($_1_u_sgdoctipodocumento_pessoarel=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                        <div class="input-group" style="margin-top: 5px;">
                                <span class="input-group-addon">
                                        <input title="Pessoa para Avaliar" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','pessoarel');" <?=$ck?>>
                                </span>
                                <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Avaliado" readonly="">
                            <?if($_1_u_sgdoctipodocumento_pessoarel=='Y'){?>
                            <select class="size18" <?=$disabled?> name="_1_<?=$_acao?>_sgdoctipodocumento_idtipopessoarel">
                                <option></option>
                                <?fillselect("select idtipopessoa,tipopessoa from tipopessoa where idtipopessoa in (1,2,5) order by tipopessoa",$_1_u_sgdoctipodocumento_idtipopessoarel);?>		
                            </select>
                            <?}?>
                        </div>
                        <?if($_1_u_sgdoctipodocumento_periodo=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                        <div class="input-group" style="margin-top: 5px;">
                                <span class="input-group-addon">
                                        <input title="Início/Fim" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','periodo');" <?=$ck?>>
                                </span>
                                <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Início/Fim" readonly="">
                        </div>
                    </div>
                    <div style="display: flex;flex-direction: row;justify-content: space-around;" class="col-md-12">
                        <?if($_1_u_sgdoctipodocumento_responsavel=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                        <div class="input-group" style="margin-top: 5px;">
                            <span class="input-group-addon">
                                <input title="Executor / Avaliador" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','responsavel');" <?=$ck?>>
                            </span>
                            <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Executor / Avaliador" readonly="">
                        </div>
                        <?if($_1_u_sgdoctipodocumento_flresultado=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                        <div class="input-group" style="margin-top: 5px;">
                            <span class="input-group-addon">
                                <input title="Resultado" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','flresultado');" <?=$ck?>>
                            </span>
                            <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Resultado" readonly="">
                        </div>
                    </div>
                    <div style="display: flex;flex-direction: row;justify-content: space-around;" class="col-md-12">
                        <?if($_1_u_sgdoctipodocumento_flnota=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                        <div class="input-group" style="margin-top: 5px;">
                            <span class="input-group-addon">
                                <input title="Nota" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','flnota');" <?=$ck?>>
                            </span>
                            <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Nota" readonly="">
                        </div>
                        <?if($_1_u_sgdoctipodocumento_flobsquestionario=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                        <div class="input-group" style="margin-top: 5px;">
                            <span class="input-group-addon">
                                <input title="Observação" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','flobsquestionario');" <?=$ck?>>
                            </span>
                            <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Observação" readonly="">
                        </div>
                    </div>
                    <div style="display: flex;flex-direction: row;justify-content: space-around;" class="col-md-12">
                        <?if($_1_u_sgdoctipodocumento_fldatavencimento=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                        <div class="input-group" style="margin-top: 5px;">
                            <span class="input-group-addon">
                                <input title="Data Vencimento" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','fldatavencimento');" <?=$ck?>>
                            </span>
                            <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Data Vencimento" readonly="">
                        </div>                                   
                    </div>                                   
                </div>

                <div class="col-md-7">
                    <?if ($_1_u_sgdoctipodocumento_idsgdoctipo) 
                    {
                        $sqlfluxo='select * from fluxo where idobjeto="'.$_1_u_sgdoctipodocumento_idsgdoctipo.'" and tipoobjeto="idsgdoctipo" and modulo="documento"';
                        $rts = d::b()->query($sqlfluxo) or die("Consulta do fluxo: ". mysql_error(d::b()));
                        $numfluxo = mysqli_num_rows($rts);
                        if($numfluxo > 0) {?>
                            <div class="panel-body" style="margin-left: 0px;margin-top: -20px;">
                                <div class=" panel-status participantes">                                                            
                                    <table style="display: flex;justify-content: space-around;">
                                        <tr id="menuPermissoes">
                                            <td>Criador:</td>
                                            <td id="tdfuncionario2">
                                                <input id="eventoresp2" class="compacto" type="text" cbvalue placeholder="Selecione">
                                            </td>
                                            <td id="tdsgsetor2">
                                                <input id="sgsetorvinc2" class="compacto" type="text" cbvalue placeholder="Selecione">
                                            </td>
                                            <td class="nowrap" style="width: 110px">
                                                <div class="btn-group nowrap" role="group" aria-label="...">
                                                    <button onclick="showfuncionario2()" type="button" class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright selecionado" title="Selecionar Funcionário" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
                                                    <button onclick="showsgsetor2()" type="button" class=" btn btn-default fa fa-users hoverlaranja pointer floatright " title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>										
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class='table table-striped planilha'>
                                        <?=listaobjetoTipoDocumento($_1_u_sgdoctipodocumento_idsgdoctipo)?>							
                                    </table>								
                                </div>
                            </div>
                        <?}else {?>
                            <div class="panel-body" style="margin-left: 0px;">
                                <div class=" panel-status participantes">                                                            
                                    <table>
                                        <tr id="menuPermissoes">
                                            <td>
                                                Nenhum fluxo cadastrado
                                            </td>
                                        </tr>
                                    </table>								
                                </div>
                            </div>
                        <?}?>
                    <?}?>  
                </div>
            </div>
            <div  class="col-lg-12">
                <div class=" panel-status">
                    <div class="panel-default" >
                    <?if($_1_u_sgdoctipodocumento_fleditor == 'N'){?>
                        <?if($_1_u_sgdoctipodocumento_flquestionario=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                        <div class="panel-heading">                           
                            Questionário
                            <input title="Questionário" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','flquestionario');" <?=$ck?>>
                        </div> 
                 <?
        $sqli="INSERT INTO sgdoctipodocumentocampos
                (col,visivel,idempresa,idsgdoctipodocumento,tabela,criadopor,criadoem,alteradopor,alteradoem)
                (select distinct(mtc.col),case when mtc.col = 'descricao' then 'Y' else 'N'end as vis, ".cb::idempresa().",".$_1_u_sgdoctipodocumento_idsgdoctipodocumento.",mtc.tab,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate()
                    from "._DBCARBON."._mtotabcol mtc 
                    where mtc.tab= 'sgdocpag'
                    and rotcurto is not null and rotcurto !=''
                    and not exists (select 1 from sgdoctipodocumentocampos c where c.idsgdoctipodocumento=".$_1_u_sgdoctipodocumento_idsgdoctipodocumento." and c.col=mtc.col and mtc.tab=c.tabela)
                )";
        d::b()->query($sqli) or die("Erro ao inserir campos tipodocumento para configuracao: ".mysqli_error()."\n".$sqli);
                                  
                 ?>        
                  
   <?
                     if($_1_u_sgdoctipodocumento_flquestionario=='Y'){
                         ?>
                        <div class="panel-body">                          
<?   							
                        $sqlfiltros = "SELECT distinct(mtc.col) as col,mtc.rotpsq,mf.idsgdoctipodocumentocampos,mf.ord,mf.visivel,mf.editavel, mtc.datatype, mf.code, mf.prompt,mtc.dropsql
                            from "._DBCARBON."._mtotabcol mtc 
                            join sgdoctipodocumentocampos mf on ( mf.col = mtc.col and mf.tabela=mtc.tab and mf.idsgdoctipodocumento=".$_1_u_sgdoctipodocumento_idsgdoctipodocumento.")
                                where mtc.tab= 'sgdocpag' and mf.visivel = 'Y'
                            order by case when mf.ord is null then 999 else mf.ord end,col";
                        $rfiltros = d::b()->query($sqlfiltros) or die("Erro ao recuperar filtros: ".mysqli_error());?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Colunas do Formulário
                            </div>
                            <div class="panel-body">
                                <div class="col-md-12">
                                    Adicionar nova Coluna: <input id="addcampodoc" class="size30">
                                </div>
                                <br>
                                <table class="planilha grade compacto" id="tblCols" style="width: 100%;">
                                    <tr> 
                                        <!-- <th class="center"></th>	 -->
                                        <th class="center">Rótulo</th>	
                                        <th class="center">Tipo</th>
                                        <th class="center">Descritivo</th>								
                                        <th class="center"><i class="fa fa-pencil" title="Coluna editável no Documento"></th>
                                        <th class="center"><i class="fa fa-trash" title="Excluir" ></i></th>
                                    </tr>
                                    <?$if=1;		
                                    while($rwf = mysql_fetch_assoc($rfiltros)){
                                        $if++;
                                        //$estadov=($rwf['visivel']=='Y')?"checked='checked'":"";
                                        $estadoe=($rwf['editavel']=='Y')?"checked='checked'":"";
                                    // $validanulo=($rwf['visivel']=='Y')?"vnulo":"";?>
                                        <tr>
                                            <!-- <td class="center"></td> -->
                                            <td> <i class="fa fa-arrows cinzaclaro hover move"></i> <?=$rwf["rotpsq"]?></td>
                                            <td>
                                                <?if($rwf['datatype'] == 'longtext' and empty($rwf['dropsql'])) {?>
                                                    <select vnulo onchange="inserePrompt(this, <?=$rwf["idsgdoctipodocumentocampos"]?>)">
                                                        <option value=''></option>
                                                        <?fillselect("SELECT 'select','Seletivo' UNION SELECT 'text', 'Texto'", $rwf["prompt"]);?>		
                                                    </select>
                                                <?}?>
                                            </td>
                                            <td>
                                                <? if($rwf['datatype'] == 'longtext' and empty($rwf['dropsql'])) {
                                                    if($rwf["prompt"] == 'select'){?>
                                                        <div class="col-sm-12 "  >
                                                            <div class="col-md-6">
                                                                <input type="text" class="compacto ui-autocomplete-input"style="border: 1px solid #cccccc;" onblur="insereCodeRotulo(this, <?=$rwf["idsgdoctipodocumentocampos"]?>, '<?=$rwf["col"]?>');">
                                                            </div>
                                                            <div class="col-md-6">
                                                            <?
                                                                $sqlRotulo = "SELECT rotulodescritivo, idsgdoctipodocumentoopcao, criadopor, criadoem
                                                                                FROM sgdoctipodocumentoopcao sto
                                                                            WHERE idsgdoctipodocumentocampos = '".$rwf["idsgdoctipodocumentocampos"]."'
                                                                            ORDER BY rotulodescritivo";
                    
                                                                $rtRotulo = d::b()->query($sqlRotulo) or die("listaSgsetorVinculados: ". mysqli_error(d::b()));

                                                                while ($rowRotulo = mysqli_fetch_assoc($rtRotulo)) {
                                                                    $title = "Vinculado por: ".$rowRotulo["criadopor"]." - ".dmahms($rowRotulo["criadoem"],true);
                                                                    echo "<div><a title='".$title."' href='javascript:void(0)'><div class='opcoes'>".$rowRotulo["rotulodescritivo"]."<i class='fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable' style='float:right' title='Desvincular' idsgdoctipodocumentoopcao='".$rowRotulo["idsgdoctipodocumentoopcao"]."' onclick='desvincularOpcao(this)'></i></div></a></div>";
                                                                }?>
                                                            </div>    
                                                           
                                                        </div>
                                                    <?}else{?>
                                                        <div class="papel transparente tiny" style="width: 100%; min-height: 150px; max-width: 900px;"></div>
                                                        <textarea class="hidden" tinydisabled style="margin: 0px; height: 50px; width: 200px;" id="sgdoctipodocumentocampos_<?=$rwf["col"]?>" name="_<?=$if?>_u_sgdoctipodocumentocampos_code"><?=$rwf["code"]?></textarea>
                                                    <?}
                                                }?>
                                            </td>
                                            <td class="center">
                                                <input type="checkbox" col='editavel' onclick="toggle(<?=$rwf["idsgdoctipodocumentocampos"]?>,this)" <?=$estadoe?>>
                                            </td>
                                            <td class="center">
                                                <i onclick="invisivel(<?=$rwf['idsgdoctipodocumentocampos']?>)" col='visivel' class="fa fa-trash cinza hoververmelho pointer"></i>
                                                <input name="_<?=$if?>_u_sgdoctipodocumentocampos_idsgdoctipodocumentocampos" type="hidden"	value="<?=$rwf["idsgdoctipodocumentocampos"]?>"	readonly='readonly'>	
                                                <input name="_<?=$if?>_u_sgdoctipodocumentocampos_ord" class="size2" type="hidden" value="<?=$rwf["ord"]?>" <?=$validanulo?>>	
                                            </td>
                                        </tr>
                                    <?}?>
                                </table> 
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Montar Formulário?&nbsp;&nbsp;&nbsp;  
                                <select name="_1_<?=$_acao?>_sgdoctipodocumento_fltemplate" >
                                    <?fillselect("SELECT 'Y','Sim' union SELECT 'N','Não'",$_1_u_sgdoctipodocumento_fltemplate)?>
                                </select>                    
                            </div>
                            <?if ($_1_u_sgdoctipodocumento_fltemplate == "Y") {?>
                                <div class="panel-body">

                                    <div class="panel-default" >
                                        <div class="panel-heading col-md-12">
                                            <div class="col-md-6">
                                                Linhas
                                            </div>
                                            <div class="col-md-6">
                                                Orientação do Template&nbsp;&nbsp;&nbsp;  
                                                <select name="_1_<?=$_acao?>_sgdoctipodocumento_tipotemplate" >
                                                    <?fillselect("SELECT 'horizontal','Horizontal' union SELECT 'vertical','Vertical'",$_1_u_sgdoctipodocumento_tipotemplate)?>
                                                </select>
                                                <input name="tipotemplate_old" type="hidden" value="<?=$_1_u_sgdoctipodocumento_tipotemplate?>" >
                                            </div>
                                        </div>
                                        <div class="panel-body"> 
                                        <br>
                                            <table style="width: 100%;" class="table table-striped planilha">
                                                <tr><!-- QST. DESCRICAO  CLASSIFICACAO  OBSERVACAO  CONCLUSAO-->
                                                    <th>Qst.</th>
                                                    <?
                                                    $sqlp="select c.col, tc.rotpsq as rotcurto, tc.dropsql as code, tc.datatype, c.code AS texto, c.prompt, c.editavel, c.idsgdoctipodocumentocampos
                                                                from sgdoctipodocumentocampos c 
                                                                    join carbonnovo._mtotabcol tc on (tc.tab = c.tabela and tc.col=c.col) 
                                                                where c.idsgdoctipodocumento=".$_1_u_sgdoctipodocumento_idsgdoctipodocumento." and c.visivel = 'Y' order by case when c.ord is null then 999 else c.ord end";
                                                    $resp=d::b()->query($sqlp) or die("Erro ao buscar questões sql".$sqlp);
                                                    $qtd=mysqli_num_rows($resp);
                                                    $col = array();
                                                    $rotcurto = array();
                                                    $code = array();
                                                    $datatype = array();
                                                    $editavel = array();
                                                    $prompt = array();
                                                    $idsgdoctipodocumentocampos = array();
                                                    $texto = array();
                                                    if($_1_u_sgdoctipodocumento_tipotemplate == 'vertical'){
                                                        while ($rowp =mysql_fetch_assoc($resp)){
                                                            array_push($col, $rowp["col"]);
                                                            array_push($rotcurto, $rowp["rotcurto"]);
                                                            array_push($code, $rowp["code"]);
                                                            array_push($editavel, $rowp["editavel"]);
                                                            array_push($datatype, $rowp["datatype"]);
                                                            array_push($prompt, $rowp["prompt"]);
                                                            array_push($idsgdoctipodocumentocampos, $rowp["idsgdoctipodocumentocampos"]);
                                                            array_push($texto, $rowp["texto"]);
                                                            ?>
                                                            <!-- <th><?=$rowp["rotcurto"]?></th> -->
                                                        <?}?>
                                                        <th colspan="3">Formulário</th>
                                                        <th><i class="fa fa-trash fa-2x cinza" title="Excluir" ></i></th>
                                                    </tr>
                                                    <?
                                                    $sqlp2="SELECT * from sgdocpagtemplate where idsgdoctipodocumento=".$_1_u_sgdoctipodocumento_idsgdoctipodocumento." order by pagina asc";
                                                    $rest2=d::b()->query($sqlp2) or die("Erro ao buscar questões sql".$sqlp2);
                                                    $qtdpag2=mysqli_num_rows($rest2);
                                                    $vqtdpag2=$qtdpag2+1;
                                                    $li=99;
                                                    if($qtdpag2 > 0)
                                                    {
                                                        while($rowp2 =mysql_fetch_assoc($rest2))
                                                        {
                                                            $li++;
                                                            $i = 0;
                                                            $sqltc='SELECT * from sgdocpagtemplatecampos where idsgdocpagtemplate = '.$rowp2['idsgdocpagtemplate'];
                                                            $restc=d::b()->query($sqltc) or die("Erro ao buscar configuração da linha. SLQ -><br> ".$sqltc);
                                                            $rowtc=mysqli_fetch_assoc($restc);
                                                            //var_dump($rowtc);
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden"  name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_idsgdocpagtemplate"  value="<?=$rowp2["idsgdocpagtemplate"]?>" >
                                                                    <input type="text" class="size3"  name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_pagina" onchange="alterapag(this,<?=$rowp2['idsgdocpagtemplate']?>,'<?=$rowp2['pagina']?>')"  value="<?=$rowp2["pagina"]?>" >
                                                                </td>
                                                                <td colspan="3">
                                                                    <div class="col-md-12">
                                                                        <? 
                                                                        $pergunta = '';
                                                                        foreach($rowtc as $k => $v){
                                                                            if($v == "P"){
                                                                                $pergunta = $k;
                                                                            }
                                                                        }
                                                                        if(!$pergunta){?>
                                                                            Adicionar Campo de Pergunta:&nbsp;&nbsp;&nbsp;
                                                                            <input type="text"class='size40' idsgdocpagtemplatecampos='<?=$rowtc['idsgdocpagtemplatecampos']?>' id="autocomplete_pergunta_<?=$rowp2["idsgdocpagtemplate"]?>">
                                                                        <?}else{
                                                                            $needleP = array_search($pergunta,$col)
                                                                            ?>
                                                                            <div class="papel transparente tiny mceNonEditable" style="width: 100%; min-height: 150px;" id="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$pergunta?>"></div>
                                                                            <textarea class="hidden flquestionario" tinydisabled cols="40" rows="6" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$pergunta?>">
                                                                            <?if(empty($rowp2[$pergunta])){
                                                                                $desc = $texto[$needleP];
                                                                            } else {
                                                                                $desc = $rowp2[$pergunta];
                                                                            }
                                                                            
                                                                            echo $desc;
                                                                            ?>
                                                                            </textarea>
                                                                        <?}?>
                                                                        
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <?
                                                                        $resposta = '';
                                                                        foreach($rowtc as $k => $v){
                                                                            if($v == "R"){
                                                                                $resposta = $k;
                                                                            }
                                                                        }
                                                                        if(!$resposta){?>
                                                                            Adicionar Campo de Resposta:&nbsp;&nbsp;&nbsp;
                                                                            <input type="text"class='size40' idsgdocpagtemplatecampos='<?=$rowtc['idsgdocpagtemplatecampos']?>' id="autocomplete_resposta_<?=$rowp2["idsgdocpagtemplate"]?>">
                                                                        <?}else{
                                                                            $needleR = array_search($resposta,$col);
                                                                            ?>
                                                                            <h3><?=$rotcurto[$needleR]?>&nbsp;&nbsp;&nbsp;<i class="fa fa-refresh hoververmelho pointer" onclick="limparesp('<?=$rowp2['idsgdocpagtemplate']?>','<?=$resposta?>')"  title="Limpar Resposta"></i>&nbsp;&nbsp;&nbsp;<i class="fa fa-trash hoververmelho pointer" onclick="alteravisivel('<?=$rowtc['idsgdocpagtemplatecampos']?>','<?=$resposta?>','<?=$rowtc[$resposta]?>')"  title="Trocar coluna"></i></h3>
                                                                            <?
                                                                            if(empty($code[$needleR]) and $datatype[$needleR] == "varchar"){?>
                                                                                <input type="text" class="size10" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$resposta?>" value="<?=$rowp2[$resposta]?>">
                                                                                <?}
                                                                            if(!empty($code[$needleR])){?>
                                                                                <!-- <select class="size25" style="width: 100%;/*max-width: 15vw;*/" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$resposta?>">
                                                                                    <option></option>
                                                                                    <?
                                                                                        fillselect($code[$needleR],$rowp2[$resposta]);
                                                                                    ?>
                                                                                </select> -->
                                                                                <?=desenharadio($code[$needleR],"_".$li."_".$_acao."_sgdocpagtemplate_".$resposta."",$rowp2[$resposta])?>
                                                                            <?}
                                                                            if($datatype[$needleR] == "longtext" and empty($code[$needleR])){
                                                                                if($prompt[$needleR] == 'select')
                                                                                {
                                                                                    ?>
                                                                                    <!-- <select style="width: 100%;max-width: 15vw;" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$resposta?>">
                                                                                        <option></option>
                                                                                        <?
                                                                                            $sqlOpcoes = "SELECT rotulo, rotulodescritivo FROM sgdoctipodocumentoopcao WHERE idsgdoctipodocumentocampos = ".$idsgdoctipodocumentocampos[$needleR].";";
                                                                                            //fillselect($sqlOpcoes,$rowp2[$resposta]);
                                                                                            
                                                                                        ?>
                                                                                    </select> -->
                                                                                    <?=desenharadio($sqlOpcoes,"_".$li."_".$_acao."_sgdocpagtemplate_".$resposta."",$rowp2[$resposta]);?>
                                                                                    <?
                                                                                } else {
                                                                                    ?>
                                                                                    <div class="papel transparente tiny mceNonEditable" style="width: 100%; min-height: 150px;" id="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$resposta?>" editavel='<?=$editavel[$needleR]?>'></div>
                                                                                    <textarea class="hidden flquestionario" tinydisabled cols="40" rows="6" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$resposta?>">
                                                                                        <?
                                                                                        if(empty($rowp2[$resposta])){
                                                                                            $desc = $texto[$needleR];
                                                                                        } else {
                                                                                            $desc = $rowp2[$resposta];
                                                                                        }
                                                                                        echo $desc;
                                                                                        ?>
                                                                                    </textarea>
                                                                                    <?
                                                                                }
                                                                            }
                                                                            ?>
                                                                        <?}?>
                                                                    </div>
                                                                    <div style="height: 15px;" class="col-md-12"></div>
                                                                </td>
                                                                <td><a class="fa fa-trash fa-2x  fade hoververmelho" title="Excluir" idunidadeobjeto="" onclick="excluirpagina(<?=$rowp2['idsgdocpagtemplate']?>)"></a></td>
                                                            </tr>
                                                            <script>
                                                                JcampoPergunta_<?=$rowp2["idsgdocpagtemplate"]?> = <?=getCamposPerg($rowp2["idsgdocpagtemplate"])?>

                                                                $('input[id="autocomplete_pergunta_<?=$rowp2["idsgdocpagtemplate"]?>"]' ).autocomplete({
                                                                    source: JcampoPergunta_<?=$rowp2["idsgdocpagtemplate"]?>,
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
                                                                        idsgdocpagtemplatecampos = $(this).attr("idsgdocpagtemplatecampos")
                                                                        campoupd = "_x_u_sgdocpagtemplatecampos_"+ui.item.id+"=";
                                                                            CB.post({
                                                                            objetos : "_x_u_sgdocpagtemplatecampos_idsgdocpagtemplatecampos="+idsgdocpagtemplatecampos+"&"+campoupd+'P'
                                                                            ,parcial: true
                                                                        });
                                                                    }
                                                                });

                                                                JcampoResp_<?=$rowp2["idsgdocpagtemplate"]?> = <?=getCamposResp($rowp2["idsgdocpagtemplate"])?>

                                                                $('input[id="autocomplete_resposta_<?=$rowp2["idsgdocpagtemplate"]?>"]' ).autocomplete({
                                                                    source: JcampoResp_<?=$rowp2["idsgdocpagtemplate"]?>,
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
                                                                        idsgdocpagtemplatecampos = $(this).attr("idsgdocpagtemplatecampos")
                                                                        campoupd = "_x_u_sgdocpagtemplatecampos_"+ui.item.id+"=";
                                                                            CB.post({
                                                                            objetos : "_x_u_sgdocpagtemplatecampos_idsgdocpagtemplatecampos="+idsgdocpagtemplatecampos+"&"+campoupd+'R'
                                                                            ,parcial: true
                                                                        });
                                                                    }
                                                                });
                                                            </script>
                                                        <?}
                                                    }
                                                }else{
                                                    while ($rowp =mysql_fetch_assoc($resp)){
                                                        array_push($col, $rowp["col"]);
                                                        array_push($rotcurto, $rowp["rotcurto"]);
                                                        array_push($code, $rowp["code"]);
                                                        array_push($editavel, $rowp["editavel"]);
                                                        array_push($datatype, $rowp["datatype"]);
                                                        array_push($prompt, $rowp["prompt"]);
                                                        array_push($idsgdoctipodocumentocampos, $rowp["idsgdoctipodocumentocampos"]);
                                                        array_push($texto, $rowp["texto"]);
                                                        ?>
                                                        <th><?=$rowp["rotcurto"]?></th>
                                                    <?}?>
                                                    <th><i class="fa fa-trash fa-2x cinza" title="Excluir" ></th>
                                                </tr>
                                                <?
                                                $sqlp2="SELECT * from sgdocpagtemplate where idsgdoctipodocumento=".$_1_u_sgdoctipodocumento_idsgdoctipodocumento." order by pagina asc";
                                                $rest2=d::b()->query($sqlp2) or die("Erro ao buscar questões sql".$sqlp2);
                                                $qtdpag2=mysqli_num_rows($rest2);
                                                $vqtdpag2=$qtdpag2+1;
                                                $li=99;
                                                if($qtdpag2 > 0)
                                                {
                                                    while($rowp2 =mysql_fetch_assoc($rest2))
                                                    {
                                                        $li++;
                                                        $i = 0;
                                                        $sqltc='SELECT * from sgdocpagtemplatecampos where idsgdocpagtemplate = '.$rowp2['idsgdocpagtemplate'];
                                                        $restc=d::b()->query($sqltc) or die("Erro ao buscar configuração da linha. SLQ -><br> ".$sqltc);
                                                        $rowtc=mysqli_fetch_assoc($restc);
                                                        //var_dump($rowtc);
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <input type="hidden"  name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_idsgdocpagtemplate"  value="<?=$rowp2["idsgdocpagtemplate"]?>" >
                                                                <input type="text" class="size3"  name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_pagina" onchange="alterapag(this,<?=$rowp2['idsgdocpagtemplate']?>,'<?=$rowp2['pagina']?>')"    value="<?=$rowp2["pagina"]?>" >
                                                            </td>
                                                                <?while($i < $qtd){?>
                                                                    <td style="vertical-align: baseline;">
                                                                        <div>
                                                                            <?if ($rowtc[$col[$i]] == "Y" || $rowtc[$col[$i]] == "P" || $rowtc[$col[$i]] == "R") {?>
                                                                                 Visível: <i class="fa fa-eye floatright" onclick="alteravisivel('<?=$rowtc['idsgdocpagtemplatecampos']?>','<?=$col[$i]?>','<?=$rowtc[$col[$i]]?>')" title="Visível no Documento"></i> 
                                                                            <?}else {?>
                                                                                 Invisível: <i class="fa fa-eye-slash floatright" onclick="alteravisivel('<?=$rowtc['idsgdocpagtemplatecampos']?>','<?=$col[$i]?>','<?=$rowtc[$col[$i]]?>')" title="Invisível no Documento"></i> 
                                                                            <?}?>
                                                                        </div>
                                                                        <?
                                                                        if(empty($code[$i]) and $datatype[$i] == "varchar"){?>
                                                                             <input type="text" class="size10" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$col[$i]?>" value="<?=$rowp2[$col[$i]]?>"> 
                                                                        <?}
                                                                        if(!empty($code[$i]) and $datatype[$i] == "varchar"){?>
                                                                             <!-- <select class="size5" style="width: 100%;/*max-width: 15vw;*/" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$col[$i]?>">
                                                                                <option></option>
                                                                                <?
                                                                                     fillselect($code[$i],$rowp2[$col[$i]]);
                                                                                ?>
                                                                            </select>  -->
                                                                            <?=desenharadio($code[$i],"_".$li."_".$_acao."_sgdocpagtemplate_".$col[$i]."",$rowp2[$col[$i]], "{$rowp2["pagina"]}-$i")?>
                                                                        <?}elseif(!empty($code[$i])){
                                                                            desenharadio($code[$i],"_".$li."_".$_acao."_sgdocpagtemplate_".$col[$i]."",$rowp2[$col[$i]], "{$rowp2["pagina"]}-$i");
                                                                        }
                                                                        if($datatype[$i] == "longtext" and empty($code[$i])){
                                                                            if($prompt[$i] == 'select')
                                                                            {
                                                                                ?>
                                                                                 <!-- <select style="width: 100%;max-width: 15vw;" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$col[$i]?>">
                                                                                    <option></option>
                                                                                    <?
                                                                                         $sqlOpcoes = "SELECT rotulo, rotulodescritivo FROM sgdoctipodocumentoopcao WHERE idsgdoctipodocumentocampos = ".$idsgdoctipodocumentocampos[$i].";";
                                                                                         fillselect($sqlOpcoes,$rowp2[$col[$i]]);
                                                                                    ?>
                                                                                </select>  -->
                                                                                <?=desenharadio($sqlOpcoes,"_".$li."_".$_acao."_sgdocpagtemplate_".$col[$i]."",$rowp2[$col[$i]], "{$rowp2["pagina"]}-$i")?>
                                                                                <?
                                                                            } else {
                                                                                ?>
                                                                                 <div class="papel transparente tiny mceNonEditable" style="width: 100%; min-height: 150px; max-width: 700px;" id="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$col[$i]?>" editavel='<?=$editavel[$i]?>'></div>
                                                                                <textarea class="hidden flquestionario" tinydisabled cols="40" rows="6" name="_<?=$li?>_<?=$_acao?>_sgdocpagtemplate_<?=$col[$i]?>"> 
                                                                                    <?
                                                                                    if(empty($rowp2[$col[$i]])){
                                                                                         $desc = $texto[$i];
                                                                                    } else {
                                                                                         $desc = $rowp2[$col[$i]];
                                                                                    }
                                                                                     echo $desc;
                                                                                    ?>
                                                                                 </textarea>								 
                                                                                <?
                                                                            }
                                                                        }
                                                                        ?>
                                                                     </td> 
                                                                <?$i++;
                                                               }?>
                                                            <td><a class="fa fa-trash fa-2x vermelho fade hoververmelho" title="Excluir" idunidadeobjeto="" onclick="excluirpagina(<?=$rowp2['idsgdocpagtemplate']?>)"></a></td>
                                                          <?}
                                                        }?>
                                                <?}?>
                                                <tr>
                                                    <td colspan="4">
                                                        <a class="fa fa-plus-circle fa-2x cinzaclaro hoververde pointer" onclick="novapagina(<?=$vqtdpag2?>)" title="Adicionar Questão"></a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?}?>
                         <?}//if($_1_u_sgdoctipodocumento_flquestionario=='Y')
                         ?>
                        </div> 
                    <?}?>
                    <?if($_1_u_sgdoctipodocumento_flquestionario == 'N'){?>
                    <div class="panel-default" >
                        <div class="panel-heading"> 
                            <?if($_1_u_sgdoctipodocumento_fleditor=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                            Editor
                            <input title="Editor" id="rnc" type="checkbox" aria-label="..." value="" onchange="alteraflg(<?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>,'<?=$valp?>','fleditor');" <?=$ck?>>                          
                        </div> 
                   
                    <?
                    
                    if($_1_u_sgdoctipodocumento_fleditor=='Y'){?>
                        <div class="panel-body">
                            <div id="editor1Container" class="carregando" style="margin-top: 5px;">
                                <input type="hidden" name="_1_<?=$_acao?>_sgdoctipodocumento_scrolleditor" value="<?=$_1_u_sgdoctipodocumento_scrolleditor?>">
                                <div id="editor1" class="papel transparente"></div>
                                <textarea  name="_1_<?=$_acao?>_sgdoctipodocumento_template" class="hidden"><?=$_1_u_sgdoctipodocumento_template?></textarea>
                            </div>
                        </div>
                    <?}?>
                    </div>
                    <?}?>
                </div>
            </div>
        </div>
    </div>
</div>
    <?
    if(!empty($_1_u_sgdoctipodocumento_idsgdoctipodocumento)){
        $sqlt="SELECT d.idsgdoc,d.idregistro,d.titulo,d.status,upper(d.idsgdoctipo) as tipo,d.alteradoem,e.sigla
                    from sgdoc d JOIN empresa e on (e.idempresa = d.idempresa)
                    where idsgdoctipodocumento=".$_1_u_sgdoctipodocumento_idsgdoctipodocumento." order by titulo";
        $rest= d::b()->query($sqlt) or die("Erro ao buscar documentos da classificacao :".mysqli_error(d::b())."<br>Sql:".$sqlt);
        $qtdt= mysqli_num_rows($rest);
        if($qtdt>0){
        ?>
        <div class="row">
            <div class="col-md-12" >
                    <div class="panel panel-default" >
                    <div class="panel-heading">Documentos</div>
                    <div class="panel-body">
                        <table class="table table-striped planilha" >
                <tr >
                            <th align="center">ID</th>
                            <th align="center">Título</th>
                            <th align="center">Tipo</th>
                            <th align="center">Status</th>
                            <th align="center">Alterado em</th>
                            <th></th>
                        </tr>
                        <?while($rowt=mysqli_fetch_assoc($rest)){?>
                        <tr>
                            <td><?=$rowt['sigla'].' - '.$rowt["idregistro"]?></td>                   
                            <td><?=$rowt["titulo"]?></td>
                            <td><?=$rowt["tipo"]?></td>
                            <td><?=$rowt["status"]?></td>
                            <td><?=dmahms($rowt["alteradoem"])?></td>
                            <td><a class="fa fa-bars pointer hoverazul" title="Ver documento" onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$rowt["idsgdoc"]?>')"></a></td>
                        </tr>
                        <?}?>
                        </table>
                    </div>
                    </div>
            </div>
        </div>
    <?
        }
    }
    ?>
</div>
</div>

<?

function getJSetorvinc() 
{
	global $JSON,$_1_u_sgdoctipodocumento_idsgdoctipodocumento;
	$sql = "SELECT i.idimgrupo, concat(e.sigla,' - ',i.grupo) as grupo
                FROM imgrupo i
                JOIN empresa e on (e.idempresa = i.idempresa)
                WHERE i.idempresa = ".idempresa()." 
                AND i.status='ATIVO'
                AND NOT EXISTS(SELECT 1 FROM objetovinculo ov
                                WHERE ov.idobjeto = '".$_1_u_sgdoctipodocumento_idsgdoctipodocumento."' AND ov.tipoobjeto = 'tipodocumento'
                                    AND ov.tipoobjetovinc ='imgrupo'
                                    AND i.idimgrupo = ov.idobjetovinc)
            ORDER BY grupo ASC";

	$rts = d::b()->query($sql) or die("getJSetorvinc: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idimgrupo"];
		$arrtmp[$i]["label"] = $r["grupo"];
		$arrtmp[$i]["fluxo"] = $r["idfluxo"];
		$i++;
	}

	return $JSON->encode($arrtmp);
}

function getJfuncionario() 
{	
	global $JSON, $_1_u_sgdoctipodocumento_idsgdoctipodocumento,$_1_u_sgdoctipodocumento_idsgdoctipo;
	
	$sql = "SELECT p.idpessoa, concat(e.sigla, ' - ',ifnull(p.nomecurto,p.nome)) as nomecurto
			  FROM pessoa p 
              JOIN objempresa oe on oe.idobjeto = p.idpessoa 
              JOIN empresa e on (e.idempresa = p.idempresa)
			 WHERE p.status ='ATIVO'
			".getidempresa('oe.empresa', 'pessoa')."
			   AND (p.idtipopessoa = 1)
               AND NOT p.usuario is null
               AND NOT EXISTS(SELECT 1 FROM objetovinculo ov
                               WHERE ov.idobjeto = '".$_1_u_sgdoctipodocumento_idsgdoctipodocumento."' AND ov.tipoobjeto = 'tipodocumento'
                                 AND ov.tipoobjetovinc ='pessoa'
                                 AND p.idpessoa = ov.idobjetovinc)
			UNION
                SELECT p.idpessoa, concat(e.sigla, ' - ',ifnull(p.nomecurto,p.nome)) as nomecurto
				  FROM pessoa p
                  JOIN empresa e on (e.idempresa = p.idempresa)
				 WHERE p.status ='ATIVO'
				".getidempresa('p.idempresa','pessoa')."
				  AND p.idtipopessoa in (15, 16, 113)
				  AND NOT p.usuario is null
				  AND NOT EXISTS(SELECT 1 FROM objetovinculo ov
                               WHERE ov.idobjeto = '".$_1_u_sgdoctipodocumento_idsgdoctipodocumento."' AND ov.tipoobjeto = 'tipodocumento'
                                 AND ov.tipoobjetovinc ='pessoa'
                                 AND p.idpessoa = ov.idobjetovinc)						
			ORDER BY nomecurto asc";

	$rts = d::b()->query($sql) or die("oioi: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"]=$r["idpessoa"];
		$arrtmp[$i]["label"]= $r["nomecurto"];
		$arrtmp[$i]["fluxo"] = $r["idfluxo"];
		$i++;
	}
	
	return $JSON->encode($arrtmp);    
}

function getCamposDoc() 
{	
	global $JSON, $_1_u_sgdoctipodocumento_idsgdoctipodocumento,$_1_u_sgdoctipodocumento_idsgdoctipo;
	
	$sql = "SELECT 
        distinct(mtc.col) as col,mtc.rotpsq,mf.idsgdoctipodocumentocampos,mf.ord,mf.visivel,mf.editavel, mtc.datatype, mf.code, mf.prompt
    from "._DBCARBON."._mtotabcol mtc 
    join sgdoctipodocumentocampos mf on ( mf.col = mtc.col and mf.tabela=mtc.tab and mf.idsgdoctipodocumento=".$_1_u_sgdoctipodocumento_idsgdoctipodocumento.")
    where mtc.tab= 'sgdocpag' and mf.visivel='N'
    order by mf.ord,col";

	$rts = d::b()->query($sql) or die("oioi: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["id"]=$r["idsgdoctipodocumentocampos"];
		$arrtmp[$i]["label"]= $r["rotpsq"];
		$arrtmp[$i]["descr"] = $r["col"];
		$i++;
	}
	
	return $JSON->encode($arrtmp);    
}

function desenharadio($tmpsql_arr,$name,$tmpintselected = '', $index = '') 
{	


	$booencontrou=false;

	if(is_array($tmpsql_arr)){

		while (list($key, $vlr) = each($tmpsql_arr)){
			if(empty($tmpintselected)){
				//echo '<option value='" . $key . "'>" . $vlr . "</option>\n';
				echo '<div>
                    <input onclick="toggleRadio(this,`'.$key.'`, `checkbox-dinamico-'.$index.'`, !this.checked,`remover-'.$index.')" class="checkbox-dinamico-'.$index.'" type="radio" id="'.$key.'" name="'.$name.'" value="'.$vlr.'" data-value="'.$vlr.'">
                        <label style="font-weight:initial !important" for="'.$key.'">'.$key.'</label><br>
                      </div>';
			}else{
				if($key==$tmpintselected){
					$booencontrou=true;
					echo '<div>
                            <input onclick="toggleRadio(this,`'.$key.'`, `checkbox-dinamico-'.$index.'`, this.checked, `remover-'.$index.')" class="checkbox-dinamico-'.$index.'" type="radio" id="'.$key.'" name="'.$name.'" value="'.$vlr.'" data-value="'.$vlr.'" checked>
                                <label style="font-weight:initial !important" for="'.$key.'">'.$key.'</label>
                            </div>';
				}else{
					echo '<div>
                    <input onclick="toggleRadio(this,`'.$key.'`, `checkbox-dinamico-'.$index.'`, !this.checked,`remover-'.$index.')" class="checkbox-dinamico-'.$index.'" type="radio" id="'.$key.'" name="'.$name.'" value="'.$vlr.'" data-value="'.$vlr.'">
                        <label style="font-weight:initial !important" for="'.$key.'">'.$key.'</label>
                      </div>';
				}
			}
		}

        echo '<input type="radio" id="remover-'.$index.'" name="'.$name.'" value="" class="hide">';
	}else{

		//echo($tmpsql_arr);
		$result = d::b()->query($tmpsql_arr);
		if (!$result){
		 echo("ERRO Desenha Radio \n<!-- ".  mysqli_error(d::b()) . " -->\n");
		 return;
		 }
	
		while ($row = mysqli_fetch_array($result,MYSQLI_NUM)){
			if(empty($tmpintselected)){
				//echo "<option value='" . $row[0] . "'>" . $row[1] . "</option>\n";
                echo '
                    <input onclick="toggleRadio(this,`'.$row[1].'`, `checkbox-dinamico-'.$index.'`, !this.checked, `remover-'.$index.'`)" class="checkbox-dinamico-'.$index.'" type="radio" id="'.$row[1]."-".$index.'" name="'.$name.'" value="'.$row[0].'" data-value="'.$row[0].'">
                        <label style="font-weight:initial !important" for="'.$row[1]."-".$index.'">'.$row[1].'</label><br>
                      ';
			}else{
				if($row[0]==$tmpintselected){
					$booencontrou=true;
                    echo '
                            <input onclick="toggleRadio(this,`'.$row[1].'`, `checkbox-dinamico-'.$index.'`, this.checked, `remover-'.$index.'`)" class="checkbox-dinamico-'.$index.'" type="radio" id="'.$row[1]."-".$index.'" name="'.$name.'" value="'.$row[0].'" data-value="'.$row[0].'" checked>
                                <label  style="font-weight:initial !important" for="'.$row[1]."-".$index.'">'.$row[1].'</label><br>
                            ';
				}else{
					echo '
                    <input onclick="toggleRadio(this,`'.$row[1].'`, `checkbox-dinamico-'.$index.'`, !this.checked, `remover-'.$index.'`)" class="checkbox-dinamico-'.$index.'" type="radio" id="'.$row[1]."-".$index.'" name="'.$name.'" value="'.$row[0].'" data-value="'.$row[0].'">
                        <label style="font-weight:initial !important" for="'.$row[1]."-".$index.'">'.$row[1].'</label><br>
                      ';
				}
			}
		}

        echo '<input type="radio" id="remover-'.$index.'" name="'.$name.'" value="" class="hide">';
	}

	//maf150513: Caso o valor do DB nao seja encontrado, colocar aviso para o usuario no final
	if(!empty($tmpintselected) and $booencontrou==false){
		echo "<div value='" . $tmpintselected . "'>* ERRO: VALOR [" . $tmpintselected . "] NÃO EXISTENTE! *</div>\n";
	}

}


function getCamposPerg($idsgdocpagtemplate) 
{	
	global $JSON, $_1_u_sgdoctipodocumento_idsgdoctipodocumento,$_1_u_sgdoctipodocumento_idsgdoctipo;
	
    $sqlp="SELECT * from sgdocpagtemplatecampos where idsgdocpagtemplate=".$idsgdocpagtemplate;

	$rt = d::b()->query($sqlp) or die("oioi: ". mysql_error(d::b()));

    $row = mysqli_fetch_assoc($rt);

    $virg= '';
    $in= '';
    foreach($row as $k => $v){
        if ($v == 'N') {
            $in .= $virg.'"'.$k.'"';
            $virg=",";
        }
    }

    if (!empty($in)) {
        $in = 'and c.col in ('.$in.')';
    }



	$sql = "SELECT c.col, tc.rotpsq
    from sgdoctipodocumentocampos c 
    join carbonnovo._mtotabcol tc on (tc.tab = c.tabela and tc.col=c.col) 
    where c.idsgdoctipodocumento=$_1_u_sgdoctipodocumento_idsgdoctipodocumento
    and c.visivel = 'Y'
    $in
    and c.prompt = 'text'
    order by case when c.ord is null then 999 else c.ord end";

	$rts = d::b()->query($sql) or die("oioi: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["id"]=$r["col"];
		$arrtmp[$i]["label"]= $r["rotpsq"];
		$i++;
	}
	
	return $JSON->encode($arrtmp);    
}

function getCamposResp($idsgdocpagtemplate) 
{	
	global $JSON, $_1_u_sgdoctipodocumento_idsgdoctipodocumento,$_1_u_sgdoctipodocumento_idsgdoctipo;

    $sqlp="SELECT * from sgdocpagtemplatecampos where idsgdocpagtemplate=".$idsgdocpagtemplate;

	$rt = d::b()->query($sqlp) or die("oioi: ". mysql_error(d::b()));

    $row = mysqli_fetch_assoc($rt);

    $virg= '';
    $in= '';
    foreach($row as $k => $v){
        if ($v == 'N' || $v == 'Y') {
            $in .= $virg.'"'.$k.'"';
            $virg=",";
        }
    }

    if (!empty($in)) {
        $in = 'and c.col in ('.$in.')';
    }

	
	$sql = "SELECT c.col, tc.rotpsq
    from sgdoctipodocumentocampos c 
        join carbonnovo._mtotabcol tc on (tc.tab = c.tabela and tc.col=c.col) 
    where c.idsgdoctipodocumento=$_1_u_sgdoctipodocumento_idsgdoctipodocumento
    and c.visivel = 'Y'
    $in
    -- and c.prompt in ('text','select')
    and c.editavel = 'Y'
    order by case when c.ord is null then 999 else c.ord end";

	$rts = d::b()->query($sql) or die("oioi: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["id"]=$r["col"];
		$arrtmp[$i]["label"]= $r["rotpsq"];
		$i++;
	}
	
	return $JSON->encode($arrtmp);    
}

function listaobjetoTipoDocumento($idsgdoctipo)
{   
    global $_1_u_sgdoctipodocumento_idsgdoctipodocumento;
    $s = "SELECT ov.idobjetovinculo,
                 CASE WHEN ov.tipoobjetovinc = 'imgrupo' THEN concat(ee.sigla,' - ',g.grupo)
                      ELSE concat(e.sigla,' - ',p.nomecurto) END AS nome,       
				 gp.idimgrupo,
				 ov.criadopor,
				 ov.criadoem
			FROM objetovinculo ov JOIN sgdoctipodocumento st ON st.idsgdoctipodocumento = ov.idobjeto AND ov.tipoobjeto = 'tipodocumento'
	        JOIN imgrupopessoa gp on((gp.idimgrupo = ov.idobjetovinc and ov.tipoobjetovinc = 'imgrupo') or (gp.idpessoa = ov.idobjetovinc and ov.tipoobjetovinc = 'pessoa'))
	        JOIN pessoa p on (p.idpessoa = gp.idpessoa)
            JOIN imgrupo g ON (g.idimgrupo = gp.idimgrupo)
            LEFT JOIN empresa e on (e.idempresa = p.idempresa)
            LEFT JOIN empresa ee on (ee.idempresa = g.idempresa)
	       WHERE st.idsgdoctipo ='$idsgdoctipo' AND ov.idobjeto = '$_1_u_sgdoctipodocumento_idsgdoctipodocumento'
		GROUP BY ov.idobjetovinc";

    $rts = d::b()->query($s) or die("listaPessoa: ". mysql_error(d::b()));
   
    while ($r = mysqli_fetch_assoc($rts)) 
    {
       
		$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='retiraeventotiporesp(".$r["idobjetovinculo"].")'></i>";   
        $title='Vinculado por: '.$r["criadopor"].' - '.dmahms($r["criadoem"],true);    
        
        echo "<tr id=".$r["idobjetovinculo"]."  title='".$title."'> 
                <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td>".$r["nome"]."</td><td>".$botao."</td>
            </tr>";                                                                
    }
}

if(!empty($_1_u_sgdoctipodocumento_idsgdoctipodocumento)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_sgdoctipodocumento_idsgdoctipodocumento; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "sgdoctipodocumento"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<script >
    <? if($_1_u_sgdoctipodocumento_idsgdoctipodocumento){
        $jSgsetorvinc 		= getJSetorvinc();
        $jFuncionario   	= getJfuncionario();
        ?>

    $('#tdsgsetor2').hide();
    $('#tdfuncionario2').show();

    jSgsetorvinc    	= <?=$jSgsetorvinc?>;
    jFuncionario    	= <?= $jFuncionario ?>;
//Autocomplete de Setores vinculados
$("#eventoresp2").autocomplete({
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
		CB.post({
			objetos: {
				"_x_i_objetovinculo_idobjeto": <?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>
				,"_x_i_objetovinculo_tipoobjeto": 'tipodocumento'
				,"_x_i_objetovinculo_idobjetovinc": funcionario.item.value
				,"_x_i_objetovinculo_tipoobjetovinc": 'pessoa'
			}
			,parcial: true
		});
	}
});

Jcampos = <?=getCamposDoc();?>

$("#addcampodoc").autocomplete({
	source: Jcampos,
	delay: 0,
	create: function() {
		$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
			lbItem = item.label;
			
			return $('<li>')
				.append(`<a>${lbItem} - <span class='cinzaclaro fonte08'>${item.descr}</span></a>`)
				.appendTo(ul);
		};
	},
	select: function(event, ui){
		CB.post({
			objetos: {
				"_ajax_u_sgdoctipodocumentocampos_idsgdoctipodocumentocampos":ui.item.id,
				"_ajax_u_sgdoctipodocumentocampos_visivel":'Y'
			}
			,parcial: true
		});
	}
});

function toggleRadio(elemento, id, classe, checked, idRemover) {
    const elementJQ = $(elemento);
    const radios = document.querySelectorAll(`.${classe}:not(#${id})`);
    // Se o radio clicado estava originalmente marcado, não o marque novamente
    if (checked) {
        elemento.checked = false;
        $(`#${idRemover}`).get(0).checked = true
    } else {
        elementJQ.val(elementJQ.data('value'));
    }

    for (var i = 0; i < radios.length; i++) {
        $(radios[i]).attr('onclick', `toggleRadio(this,'${id}', '${classe}',false)`)
    }

    elementJQ.attr('onclick', `toggleRadio(this,'${id}','${classe}',${elemento.checked})`)
}

function sortableEvent()
{
	let $tBody = $("#tblCols tbody");
	//Permitir ordenar/arrastar os TR de insumos
	$tBody.sortable({
		update: function(event, objUi){
			ordenaCols();
		},
		stop: function(event, ui){
			$(this).sortable("disable");
		}
	});

	$tBody.sortable('enable');
}

function ordenaCols(){
	$.each($("#tblCols tbody").find("tr"), function(i,otr){
		$(this).find(":input[name*=ord]").val(i);
	});
}

$("#tblCols tr .move").on('mousedown', sortableEvent);

function invisivel(idsgdoccampos){
    CB.post({
			objetos: {
				"_ajax_u_sgdoctipodocumentocampos_idsgdoctipodocumentocampos":idsgdoccampos,
				"_ajax_u_sgdoctipodocumentocampos_visivel":'N'
			}
			,parcial: true
		});
}

function limparesp(id,campo){
    CB.post({
			objetos: "_ajax_u_sgdocpagtemplate_idsgdocpagtemplate="+id+"&_ajax_u_sgdocpagtemplate_"+campo+"=''"
			,parcial: true
		});
}

$("#sgsetorvinc2").autocomplete({
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
		
			CB.post({
				objetos: {
					"_x_i_objetovinculo_idobjeto": <?=$_1_u_sgdoctipodocumento_idsgdoctipodocumento?>
					,"_x_i_objetovinculo_tipoobjeto": 'tipodocumento'
					,"_x_i_objetovinculo_idobjetovinc": setor.item.value
					,"_x_i_objetovinculo_tipoobjetovinc": 'imgrupo'
				}
			,parcial: true
		});
	}
});
function showfuncionario2() {
    $('#tdsgsetor2').hide();
    $('#tdfuncionario2').show();
}

function showsgsetor2() {
	$('#tdsgsetor2').show();
	$('#tdfuncionario2').hide();
}

function retiraeventotiporesp(inid){
	CB.post({
		objetos: {
			"_x_d_objetovinculo_idobjetovinculo":inid
		}
		,parcial: true
	});
}
<?}?>
function novapagina(vp){
    CB.post({
        objetos: {
            "_x_i_sgdocpagtemplate_idsgdoctipodocumento":$(":input[name=_1_"+CB.acao+"_sgdoctipodocumento_idsgdoctipodocumento]").val()
            ,"_x_i_sgdocpagtemplate_pagina":vp
        }
        ,parcial: true
    });
}
function alterapag(vthis,idtemplate,old){
    CB.post({
        objetos: {
            "att":idtemplate
            ,"new_pagina": $(vthis).val()
            ,"old_pagina":old
        }
        ,refresh:false
        ,msgSalvo:false
    });
}
function excluirpagina(vidsgdocpag){
    CB.post({
        objetos: {
            "_x_d_sgdocpagtemplate_idsgdocpagtemplate":vidsgdocpag           
        }
        ,parcial: true
    });
}
$editor1=$("#editor1");
//Resetar o objeto tinymce para não ficar desabilitado no refresh/reload
if(tinyMCE.editors["editor1"])tinyMCE.editors["editor1"].remove();

//Inicializa Editor
tinymce.init({
	selector: "#editor1"
	,language: 'pt_BR'
	,inline: true /* não usar iframe */
	,toolbar: 'formatselect | removeformat | fontsizeselect | bold | subscript superscript | bullist numlist | table'
	,menubar: false
	,plugins: ['table']
	,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
	,setup: function (editor) {
		editor.on('init', function (e) {
			//Recupera o conteudo do DB
			this.setContent($(":input[name=_1_"+CB.acao+"_sgdoctipodocumento_template]").val());
			setTimeout(function(){
				$editor1.removeClass("tranparente").addClass("opaco");
				$editor1.scrollTop($(":input[name=_1_"+CB.acao+"_sgdoctipodocumento_scrolleditor]").val());
			}, 1000);

		});
	}

});

//Controla o evento scroll para que ele não seja executado imediatamente. Isto evita alteraçàµes oriundas da renderização dos elementos na tela
var scrollWait,
  scrollFinished = () => console.log('finished');
  window.onscroll = () => {
    clearTimeout(scrollWait);
    scrollWait = setTimeout(scrollFinished,500);
  }

//Armazena o scroll vertical do editor wysiwyg
$editor1.on("scroll", function(){

	$(":input[name=_1_"+CB.acao+"_sgdoctipodocumento_scrolleditor]").val($editor1.scrollTop());	
	console.log($editor1.scrollTop());

});

//Antes de salvar atualiza o textarea
CB.prePost = function(){
	var $editor=tinyMCE.get('editor1');
	if($editor){
		//falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
		$(":input[name=_1_"+CB.acao+"_sgdoctipodocumento_template]").val($editor.getContent());
	}
}   

function alteraflg(inid,invalor,incampo){
    CB.post({
        objetos: "_x_u_sgdoctipodocumento_idsgdoctipodocumento="+inid+"&_x_u_sgdoctipodocumento_"+incampo+"="+invalor        
    });
}
  

function toggle(inId,inChk){

	var vYN = (inChk.checked)?"Y":"N";
	
	var strPost = "_ajax_u_sgdoctipodocumentocampos_idsgdoctipodocumentocampos="+inId
				+ "&_ajax_u_sgdoctipodocumentocampos_"+$(inChk).attr('col')+"="+vYN;

	CB.post({
		objetos: strPost
	});
}

function inserePrompt(valor, idsgdoctipodocumentocampos)
{
    CB.post({
        objetos: "_x_u_sgdoctipodocumentocampos_idsgdoctipodocumentocampos="+idsgdoctipodocumentocampos+"&_x_u_sgdoctipodocumentocampos_prompt="+valor.value,
        parcial: true
    });
}

function insereCode(valor, idsgdoctipodocumentocampos)
{
    CB.post({
        objetos: "_x_u_sgdoctipodocumentocampos_idsgdoctipodocumentocampos="+idsgdoctipodocumentocampos+"&code="+valor.value,
        parcial: true
    });
}

function insereCodeRotulo(valor, idsgdoctipodocumentocampos)
{
    if(valor.value)
    {
        CB.post({
            objetos: "_xr_i_sgdoctipodocumentoopcao_idsgdoctipodocumentocampos="+idsgdoctipodocumentocampos+"&_xr_i_sgdoctipodocumentoopcao_rotulodescritivo="+valor.value,
            parcial: true
        });
    }
}

function desvincularOpcao(id)
{
    if(confirm("Deseja retirar este valor?"))
    {		
        var id = $(id).attr("idsgdoctipodocumentoopcao");
        CB.post({
            objetos: {
                "_x_d_sgdoctipodocumentoopcao_idsgdoctipodocumentoopcao":id
            }
            ,parcial: true	
        });
    }
}
function alteravisivel(inid,col,val){
    val = (val == "Y" || val == "P" || val == "R")?"N":"Y"
CB.post({
    objetos:'_xx_u_sgdocpagtemplatecampos_idsgdocpagtemplatecampos='+
    inid+'&_xx_u_sgdocpagtemplatecampos_'+col+'='+val
    ,parcial:true
})
}

//-------------------- Inicializa o Editor para o Campo Observação ---------------------------------------
//Resetar o objeto tinymce para não ficar desabilitado no refresh/reload
$(".flquestionario").each((i,e)=>{
	if(tinyMCE.editors[$(e).attr('name')]){
		tinyMCE.editors[$(e).attr('name')].remove()
	}
});

//Inicializa Editor
tinymce.init({
	selector: ".tiny"
	,language: 'pt_BR'
	,inline: true /* não usar iframe */
	,body_class: 'mult_editor'
	,toolbar: 'formatselect | removeformat | fontsizeselect | forecolor backcolor | bold | subscript superscript | bullist numlist '
	,menubar: false
	,plugins: []
	,width:400
	,max_widht:400
	,resize: "both"
	,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
	,content_style: "html body .mce-content-body {color:black;}"
	,setup: function (editor) {
		editor.on('init', function (e) {
			//Recupera o conteudo do DB
			this.setContent($(this.bodyElement).siblings('textarea').val());
			setTimeout(function(prop){
				$(prop.bodyElement).removeClass("tranparente").addClass("opaco");
			}, 1000,this);

		});
        editor.on('input', atualizaConteudo);
        editor.on('Change', atualizaConteudo);
	}
});

function atualizaConteudo()
{
    if(!($(this.bodyElement).siblings('textarea').val() == this.getContent())){
        $(this.bodyElement).siblings('textarea').val(this.getContent())
    }
}
//-------------------- Inicializa o Editor para o Campo Observação ---------------------------------------

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape	
</script>
