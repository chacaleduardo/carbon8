<?
require_once("../inc/php/validaacesso.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}

$idobjeto = $_GET['idobjeto'];
$objeto = $_GET['objeto'];
$idevento = $_GET['idevento'];
$idtipoacaoevento = $_GET['idtipoacao'];
$dataevento = $_GET['dataevento'];

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "acao";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
    "idacao" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".acao where idacao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

if($_1_u_acao_status=="CONCLUIDA" or $_1_u_acao_status=="CANCELADA"){
    $disabled = " disabled='disabled' ";
    $readonly=" readonly='readonly' ";
}	

if (empty($_1_u_acao_exercicio)){
    $_1_u_acao_exercicio= date("Y");
}

if(!empty($objeto) or !empty($_1_u_acao_objeto)){
    if(!empty($_1_u_acao_objeto)){
        $slqsel="SELECT idtipoacao,concat(tipoacao,' - ',vinculo)
                    FROM tipoacao 
                    where idcadtipoacao in (1,2,3,6)
                    and status = 'ATIVO' 
                    
                    and vinculo='".$_1_u_acao_objeto."'";
    }else{
        $slqsel="SELECT idtipoacao,concat(tipoacao,' - ',vinculo)
                    FROM tipoacao 
                    where idcadtipoacao in (1,2,3,6)
                    and status = 'ATIVO' 
                    
                    and vinculo='".$objeto."'";
    }
}else{
	$slqsel= "SELECT idtipoacao,concat(tipoacao,' - ',vinculo)
			FROM tipoacao 
			where idcadtipoacao in (1,2,3,6)
			
			and  status = 'ATIVO'";
}

function getJSgdoc(){	
    $s = "select 
                a.idsgdoc
                , concat(a.idregistro,'-',a.titulo,'-(',a.idsgdoctipo,')') as  titulo				
                from sgdoc a
                where 1  ".getidempresa('a.idempresa','documento')."                         
                order by titulo";

    $rts = d::b()->query($s) or die("getJSetorvinc: ". mysqli_error(d::b()));
    $arrtmp=array();
    $i=0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"]=$r["idsgdoc"];
        $arrtmp[$i]["label"]= $r["titulo"];
        $i++;
    }
    return $arrtmp;    
    //return $JSON->encode($arrtmp);    
}

$jSgdoc="null";

if(!empty($_1_u_acao_idacao)){
    $arrSgdoc=getJSgdoc();
    $jSgdoc=$JSON->encode($arrSgdoc);
}
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
	
</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
        <table>
        <tr> 
            <td align="right">ID:</td> 
            <td><label class="idbox"><?=$_1_u_acao_idacao?></label>
                <input name="_1_<?=$_acao?>_acao_idacao"	type="hidden" value="<?=$_1_u_acao_idacao?>" readonly='readonly'>
                <input name="_1_<?=$_acao?>_acao_exercicio"	type="hidden" value="<?=$_1_u_acao_exercicio?>" >
    <?if(!empty($idevento) and $_acao == "i"){//coloca o id do evento na ação?>
                <input name="_1_<?=$_acao?>_acao_idevento" <?=$readonlyobj?> type="hidden"  readonly='readonly' value="<?=$idevento?>" >	
    <?}?>
            </td>
            <td align="right">Titulo:</td>
            <td><input class="size30" <?=$readonly?> name="_1_<?=$_acao?>_acao_titulo"	type="text" value="<?=$_1_u_acao_titulo?>" vnulo></td>
            <td align="right">Tipo:</td>
            <td>
<?
    if(empty($_1_u_acao_idtipoacao) and empty($idtipoacaoevento) and $_acao == "i"){
?>
		<select name="_1_<?=$_acao?>_acao_idtipoacao"  vnulo>
		<option value=""></option>
			<?fillselect($slqsel,$_1_u_acao_idtipoacao);?>
                </select>
<?
    }elseif(!empty($idtipoacaoevento) and empty($_1_u_acao_idtipoacao) and $_acao == "i"){
                echo(traduzid("tipoacao","idtipoacao","tipoacao",$idtipoacaoevento));
?>
                <input name="_1_<?=$_acao?>_acao_idtipoacao"  type="hidden" value="<?=$idtipoacaoevento?>">	
                <input name="_1_<?=$_acao?>_acao_objeto" type="hidden" value="<?=$objeto?>">
                <input name="_1_<?=$_acao?>_acao_idobjeto" type="hidden" value="<?=$idobjeto?>">
                <input name="_1_<?=$_acao?>_acao_dataevento" type="hidden" value="<?=$dataevento?>">
<?	
    }else{	
?>	
            <?echo traduzid("tipoacao","idtipoacao","tipoacao",$_1_u_acao_idtipoacao);?>		
<?
    }
?>		
            </td> 
 
            <td align="right">Status:</td> 
            <td>
                <select name="_1_<?=$_acao?>_acao_status"  <?=$disabled?>>
                    <?fillselect("SELECT 'PENDENTE','Pendente' union select 'CONCLUIDA','Concluida' union select 'CANCELADA','Cancelada'",$_1_u_acao_status);?>		
                </select>
            </td> 
        
        </tr>
        </table>            
        </div>
        <div class="panel-body"> 
            <table>
                <tr>
                    <td>Data:</td>
    <?
            if (empty($_1_u_acao_data)){
                $_1_u_acao_data= date("d/m/Y H:i:s");
            }	
    ?>	
                    <td>
                        <input name="_1_<?=$_acao?>_acao_data" <?=$readonly?>  class="calendario"  size="16" type="text" value="<?=$_1_u_acao_data?>">			
                    </td> 	
                    <td align="right"><font color="#CD0000">Prazo:</font></td> 
                    <td>
    <?
            if (empty($_1_u_acao_prazo)){
                $_1_u_acao_prazo= date("d/m/Y");
            }	
    ?>											
                        <input name="_1_<?=$_acao?>_acao_prazo" <?=$readonly?> class="calendario" type="text" size ="8" value="<?=$_1_u_acao_prazo?>" vnulo>
                    </td>            
                    <td align="right">Responsável:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_acao_idpessoa" vnulo  <?=$disabled?>>
                            <option value=""></option>
                            <?fillselect("select idpessoa,usuario from pessoa where idtipopessoa = 1 and status = 'ATIVO'  ".getidempresa('idempresa','pessoa')." order by usuario",$_1_u_acao_idpessoa);?>		
                        </select>
                    </td> 
                </tr>
<?
if(!empty($_1_u_acao_idtipoacao)){
$sql1="select * from tipoacao where idtipoacao =".$_1_u_acao_idtipoacao;
$res1 = d::b()->query($sql1) or die("A Consulta dos tipos de ação falhou :".mysqli_error(d::b())."<br>Sql:".$sql1);
$row1 = mysqli_fetch_assoc($res1);
//echo $sql1;
	if(!empty($row1["vinculo"]) and empty($objeto) and empty($idobjeto) and empty($_1_u_acao_objeto)  and empty($_1_u_acao_idobjeto)){
		$_1_u_acao_objeto= $row1["vinculo"];
		
		if($_1_u_acao_objeto=='EQUIPAMENTO'){
                    $rot="TAG: ";
                    $sql2="select e.idtag,concat(e.tag,' - ',e.descricao)
                                from tag e
                                where e.status = 'ATIVO'  ".getidempresa('e.idempresa','tag')." order by tag";
		}elseif($_1_u_acao_objeto=='SGDOC'){
                     $rot="Documento: ";
			$sql2="select idsgdoc,titulo from sgdoc where 1 ".getidempresa('idempresa','documento');
		}elseif($_1_u_acao_objeto=='PESSOA'){
                     $rot="Fornecedor: ";
			$sql2="select idpessoa,nome from pessoa where idtipopessoa=5 and status='ATIVO' ".getidempresa('idempresa','pessoa')."";
		}
?>			
                <tr> 
                    <td></td>		
                    <td>
                        <input name="_1_<?=$_acao?>_acao_objeto" <?=$readonlyobj?> type="hidden"  readonly='readonly' value="<?=$_1_u_acao_objeto?>">
                    </td> 
                </tr>
                <tr> 
                    <td align="right"><?=$rot?></td> 
                    <td>
                        <select name="_1_<?=$_acao?>_acao_idobjeto"> <?=$disabledobj?>
                        <option value=""></option>
                            <?fillselect($sql2,$_1_u_acao_idobjeto);?>
                        </select>
                    </td>
                </tr>
                <?               
                if($row1["idcadtipoacao"]==6){  
                ?>
                <tr> 
                    <td align="right">Layout:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_acao_idsgdoc"> <?=$disabledobj?>
                        <option value=""></option>
                            <?fillselect("select idsgdoc,concat(idregistro,'-',titulo,'-(',idsgdoctipo,')') as titulo from sgdoc where 1 ".getidempresa('idempresa','documento')." order by titulo",$_1_u_acao_idsgdoc);?>
                        </select>
                    </td>
                </tr>
<?	
                }//if($row1["idcadtipoacao"]==6){ 
	}elseif((!empty($objeto) and !empty($idobjeto))  or (!empty($_1_u_acao_objeto)  and !empty($_1_u_acao_idobjeto))){		               

		if($_1_u_acao_objeto=='EQUIPAMENTO'){
                    $rot="TAG: ";
                    $urlf="?_modulo=tag&_acao=u&idtag=".$_1_u_acao_idobjeto;
                    $sql3="select e.idtag,concat(e.tag,' - ',e.descricao) as idobj
                                    from tag e
                                    where  e.idtag = ".$_1_u_acao_idobjeto;                                        
                          
		}elseif( $_1_u_acao_objeto=='SGDOC' ){
                    $rot="Documento: ";
                    $urlf="?_modulo=documento&_acao=u&idsgdoc=".$_1_u_acao_idobjeto;

                    $sql3="select concat(idregistro,'-',titulo,'-(',idsgdoctipo,')') as idobj from sgdoc where idsgdoc=".$_1_u_acao_idobjeto."  ".getidempresa('idempresa','documento');
		}elseif( $_1_u_acao_objeto=='PESSOA' ){
                    $rot="Fornecedor: ";
                    $urlf="?_modulo=pessoa&_acao=u&idpessoa=".$_1_u_acao_idobjeto;

                    $sql3="select nome as idobj from pessoa where idpessoa=".$_1_u_acao_idobjeto." ".getidempresa('idempresa','pessoa');
		}
		$res3 = d::b()->query($sql3) or die("A Consulta dos objetos falhou :".mysqli_error(d::b())."<br>Sql:".$sql3);
		$row3 = mysqli_fetch_assoc($res3);
?>		
                    <tr>
                        <td><?=$rot?></td>
                        <td><?=$row3["idobj"]?>
                            <a class="fa fa-bars pointer hoverazul" title="Editar" onclick="janelamodal('<?=$urlf?>')"></a>
                        </td>
                    </tr>
<?
                if($row1["idcadtipoacao"]==6){//leituras
?>                    
                    <tr> 
                        <td align="right">Layout:</td> 
                        <td>
                           <?if(empty($_1_u_acao_idsgdoc)){?>
                            <input id="sgdoclayout" type="text" name="_1_<?=$_acao?>_acao_idsgdoc" cbvalue="<?=$_1_u_acao_idsgdoc?>" value="" style="width: 20em;">
                           <?}else{
                                $sqld="select concat(idregistro,'-',titulo) as titulo from sgdoc where idsgdoc=".$_1_u_acao_idsgdoc;
                                $resd = d::b()->query($sqld) or die("A Consulta do layout falhou :".mysqli_error(d::b())."<br>Sql:".$sqld);
                                $row3d = mysqli_fetch_assoc($resd);
                                echo($row3d['titulo']);
                             ?>
                            <a class="fa fa-bars pointer hoverazul" title="Documento" onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_acao_idsgdoc?>')"></a>
                           <?}?>
                        </td>
                    </tr>
<?	
               }// if($row1["idcadtipoacao"]!=1){
	}//}elseif((!empty($objeto) and !empty($idobjeto))  or (!empty($_1_u_acao_objeto)  and !empty($_1_u_acao_idobjeto))){
}//if(!empty($_1_u_acao_idtipoacao)){
?>
            </table>
        </div>
    </div>
    </div>
</div>
<?
if(!empty($_1_u_acao_idacao) and ($_1_u_acao_idtipoacao)){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-body"> 
<?
if($row1["idcadtipoacao"]==1){//leituras
?>
            <table>
            <tr> 
                <td align="center">ATUAL:
                    <input name="_1_<?=$_acao?>_acao_atual"	size= "4" type="text" value="<?=$_1_u_acao_atual?>" <?=$readonly?>>
                </td> 
                <td align="center">PADRàO:
                    <input name="_1_<?=$_acao?>_acao_padrao" size= "4" type="text" value="<?=$_1_u_acao_padrao?>" <?=$readonly?>>
                </td> 
                <td align="center">ERRO:
                    <input name="_1_<?=$_acao?>_acao_erro" size= "4"	type="text" value="<?=$_1_u_acao_erro?>" <?=$readonly?>>
                </td> 
            </tr>
            <tr>
                <td>Obs:</td>
            </tr>
            <tr>
                <td colspan="5"><textarea  <?=$readonly?> name="_1_<?=$_acao?>_acao_descr" rows="6"	cols="65"><?=$_1_u_acao_descr?></textarea></td>
            </tr>
            </table>
<?
}//if($row1["idcadtipoacao"]==1){//leituras
elseif($row1["idcadtipoacao"]==6){//if tipo  e 3 OU 2
    if(!empty($_1_u_acao_idsgdoc) and empty($_1_u_acao_descr)){
        $sqls="select conteudo from sgdoc where idsgdoc =".$_1_u_acao_idsgdoc;
        $ress= d::b()->query($sqls) or die("Erro ao buscar template na sgtipodoc : ". mysqli_error(d::b()));
        $rows=mysqli_fetch_assoc($ress);
        $template = $rows["conteudo"];		
        if(!empty($template)){//atribui o template no conteudo caso exista
            $_1_u_acao_descr=$template;
        }
    }    
?>
                    <div id="editor1Container" class="col-md-9 carregando">            
                        <!-- Armazenar a posição vertical do editor -->
                        <input type="hidden" name="_1_<?=$_acao?>_acao_scrolleditor" value="<?=$_1_u_acao_scrolleditor?>">
                        <div id="editor1" class="papel transparente"></div>
                        <textarea  <?=$disabled?>  name="_1_<?=$_acao?>_acao_descr" class="hidden"><?=$_1_u_acao_descr?></textarea>
                        <div style=" padding: 50px; "></div>
                    </div>
       
<?
    }else{//if($row1["idcadtipoacao"] == 1){//if tipo  e 2 OU 6
?>
                <div id="editor1Container" class="col-md-9 carregando">            
                    <!-- Armazenar a posição vertical do editor -->
                    <input type="hidden" name="_1_<?=$_acao?>_acao_scrolleditor" value="<?=$_1_u_acao_scrolleditor?>">
                    <div id="editor1" class="papel transparente"></div>
                    <textarea  <?=$disabled?>  name="_1_<?=$_acao?>_acao_descr" class="hidden"><?=$_1_u_acao_descr?></textarea>
                    <div style=" padding: 50px; "></div>
                </div>      
            
    <?}?>
        </div>
    </div>
    </div>        
</div>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Conclusão</div>
        <div class="panel-body"> 
            <?=$_1_u_acao_conclusao?>
        </div>
    </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Apontamento</div>
        <div class="panel-body"> 
            <table class="table table-striped planilha"> 
<?
                //while APONTAMENTO inico lista os apontamentos
                $sql2 = "SELECT a.*,dmahms(a.criadoem) as dmacriadoem FROM `apontamentoobj` a where a.tipoobjeto='acao' and a.idobjeto = ".$_1_u_acao_idacao."   order by a.idapontamentoobj asc";
                $y=2;
                $qr2= d::b()->query($sql2) or die("Erro os apontamentos da acão:".mysqli_error(d::b()));
                $qtdrow= mysqli_num_rows($qr2);
                if($qtdrow > 0){
?> 	
		<tr>			
                    <th>Descrição</th>
                    <th>Criado Por</th>
                    <th>Criado Em</th>						
		</tr>
<?
                    while ($res2 = mysqli_fetch_array($qr2)){//while APONTAMENTO 
                        $y = $y+1;	
                        if(!empty($res2["apontamento"]) and $_1_u_acao_status =='CONCLUIDA'){
                            $strreadonly= "readonly='readonly'";
                        }else{
                            $strreadonly="";
                        }					
?>	
		<tr>			
                    <td>
                        <input name="_<?=$y?>_u_apontamentoobj_idapontamentoobj" type="hidden" value="<?=$res2["idapontamentoobj"]?>">
                        <textarea name="_<?=$y?>_u_apontamentoobj_apontamento" <?=$strreadonly?> rows="2"	cols="36"><?=$res2["apontamento"]?></textarea>
                    </td>
                    <td><label><?=$res2["criadopor"]?></label></td>
                    <td><label><?=$res2["dmacriadoem"]?></label></td>	
                </tr>
			
<?
                    }//while APONTAMENTO fim
                }///fim if($qtdrow > 0) 
?>
                <tr>
                    <td colspan="3">
                        <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novoapontamento()" alt="Inserir novo!"></i>
                    </td>
                </tr>
	</table>
        </div>
    </div>
    </div>
</div>
<!--div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default">
       <div class="panel-heading">Arquivos Anexos</div>
       <div class="panel-body">
	    <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
                <i class="fa fa-cloud-upload fonte18"></i>
	    </div>
	</div> 
    </div>
    </div>
</div-->

<?

    $sql = "select p.idpessoa
                ,p.nome 
                ,CASE
                    WHEN c.status ='ATIVO' THEN dma(c.alteradoem)
                    ELSE ''
                END as dataassinatura 
                ,CASE
                    WHEN c.status ='ATIVO' THEN 'ASSINADO'
                    ELSE 'PENDENTE'
                END as status
            from carrimbo c ,pessoa p 
            where c.idpessoa = p.idpessoa
            and c.status IN ('ATIVO','PENDENTE')
            and c.tipoobjeto in('acao')
            and c.idobjeto =".$_1_u_acao_idacao."  order by nome";

    $res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 
    $existe = mysqli_num_rows($res);
        if($existe>0){
?>

<?
        }//if($existe>0){ 

    }//if(!empty($_1_u_acao_idacao) and ($_1_u_acao_idtipoacao)){
?><?
if(!empty($_1_u_acao_idacao)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_acao_idacao; // trocar p/ cada tela o id da tabela
	require '../form/viewAssinaturas.php';
}
	$tabaud = "acao"; //pegar a tabela do criado/alterado em antigo
	require '../form/viewCriadoAlterado.php';
?>

<script>
    
$editor1=$("#editor1");
//Resetar o objeto tinymce para não ficar desabilitado no refresh/reload
if(tinyMCE.editors["editor1"])tinyMCE.editors["editor1"].remove();

//Inicializa Editor
tinymce.init({
	selector: "#editor1"
	,language: 'pt_BR'
	,inline: true /* não usar iframe */
	,toolbar: 'formatselect | removeformat | fontsizeselect | forecolor backcolor | bold | alignleft aligncenter alignright alignjustify | subscript superscript | bullist numlist | table | pagebreak'
	,menubar: false
	,plugins: ['table','pagebreak','textcolor']
	,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
	,content_style: "html body .mce-content-body {color:black;}"
	//,pagebreak_separator: "<div style='page-break-before: always;clear:both;'></div>"
	,setup: function (editor) {
            editor.on('init', function (e) {
                //Recupera o conteudo do DB
                this.setContent($(":input[name=_1_"+CB.acao+"_acao_descr]").val());
                setTimeout(function(){
                    $editor1.removeClass("tranparente").addClass("opaco");
                    $editor1.scrollTop($(":input[name=_1_"+CB.acao+"_acao_scrolleditor]").val());
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
    $(":input[name=_1_"+CB.acao+"_acao_scrolleditor]").val($editor1.scrollTop());	
    console.log($editor1.scrollTop());

});

//Antes de salvar atualiza o textarea
CB.prePost = function(){
    var $editor=tinyMCE.get('editor1');
    if($editor){
        //falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
        $(":input[name=_1_"+CB.acao+"_acao_descr]").val($editor.getContent());
    }
}

JSgdoc=<?=$jSgdoc?>;
$("#sgdoclayout").autocomplete({
    source: JSgdoc
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            lbItem = item.label;
            return $('<li>')
                .append('<a>' + lbItem + '</a>')
                .appendTo(ul);
        };
    }
});

function novoapontamento(){
   CB.post({
        objetos: "_x_i_apontamentoobj_idobjeto="+$("[name=_1_u_acao_idacao]").val()+"&_x_i_apontamentoobj_tipoobjeto=acao"
        ,parcial:true
        ,refresh:"refresh"
    });
}
if( $("[name=_1_u_acao_idacao]").val() ){
    $(".cbupload").dropzone({
        idObjeto: $("[name=_1_u_acao_idacao]").val()
        ,tipoObjeto: 'acao'
		,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
    });
}

<?
if(!empty($_1_u_acao_idacao)){
    $sqla="select * from carrimbo 
	    where status='PENDENTE' 
	    and idobjeto = ".$_1_u_acao_idacao." 
	    and tipoobjeto in ('acao')
	    and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
    $resa=d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
    $qtda= mysqli_num_rows($resa);
    if($qtda>0){
	 $rowa=mysqli_fetch_assoc($resa);

?>    

<?	    

    }// if($qtda>0){
}//if(!empty($_1_u_sgdoc_idsgdoc)){
?>

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>