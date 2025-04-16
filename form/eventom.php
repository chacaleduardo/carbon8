<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "immsgconf";
$pagvalcampos = array(
	"idimmsgconf" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from immsgconf where idimmsgconf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

function getModulo(){

    $sql= "select 
            m.modulo,m.rotulomenu,m.tab
        from "._DBCARBON."._modulo m,"._DBCARBON."._mtotabcol tc
        where tc.primkey ='Y'
        and exists (select 1 from "._DBCARBON."._mtotabcol t where t.tab = m.tab and col='alteradoem' )
        and tc.tab = m.tab order by m.modulo";
    $res = d::b()->query($sql) or die("getModulo: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["modulo"]]["modulo"]=$r["modulo"];
        $arrret[$r["modulo"]]["tab"]=$r["tab"];
        $arrret[$r["modulo"]]["rotulomenu"]=$r["rotulomenu"];
    }
    return $arrret;
}
//Recupera os modulos a serem selecionados
$arrmodulo=getModulo();
//print_r($arrCli); die;
$jModulo=$JSON->encode($arrmodulo); 

function getModulodest(){

    $sql= "select 
            m.modulo,m.rotulomenu,m.tab
        from "._DBCARBON."._modulo m order by m.modulo";
    $res = d::b()->query($sql) or die("getModulo: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["modulo"]]["modulo"]=$r["modulo"];
        $arrret[$r["modulo"]]["tab"]=$r["tab"];
        $arrret[$r["modulo"]]["rotulomenu"]=$r["rotulomenu"];
    }
    return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrmodulodest=getModulodest();
//print_r($arrCli); die;
$jModulodest=$JSON->encode($arrmodulodest);    
    
function getSetor(){
   global $JSON,$_1_u_immsgconf_idimmsgconf,$_1_u_immsgconf_tipo;
    $sql="select s.idimgrupo,s.grupo
        from  imgrupo s 
        where s.status='ATIVO' 
        
            and not exists(
                    SELECT 1
                    FROM immsgconfdest v
                    where  v.idimmsgconf= ".$_1_u_immsgconf_idimmsgconf." 
                        and v.objeto ='imgrupo'
                        and s.idimgrupo = v.idobjeto				
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
    global $_1_u_immsgconf_idimmsgconf;
    $s = "select d.idimmsgconfdest,s.grupo,s.idimgrupo,d.criadopor,d.criadoem,s.status
            from immsgconfdest d,imgrupo s
                where s.idimgrupo = d.idobjeto
                and d.objeto ='imgrupo'
                and d.idimmsgconf = ".$_1_u_immsgconf_idimmsgconf." order by s.grupo";

    $rts = d::b()->query($s) or die("listaSgsetor: ". mysqli_error(d::b()));

    echo "<table class='table-hover'><tbody>";
    while ($r = mysqli_fetch_assoc($rts)) {
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
        if ($r["status"] == 'ATIVO'){ $cor = 'verde hoververde'; }else{ $cor = 'vermelho hoververmelho';}
        echo "<tr><td title='".$title."'>".$r["grupo"]."</td><td><i class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"retirasgsetor(".$r['idimmsgconfdest'].")\" title='Excluir!'></i></td><td><a class='fa fa-bars pointer hoverazul' title='Grupo' onclick=\"janelamodal('?_modulo=imgrupo&_acao=u&idimgrupo=".$r["idimgrupo"]."')\"></a></td></tr>";
    }
    echo "</tbody></table>";
}

function listaPessoa(){
    
    global $_1_u_immsgconf_idimmsgconf;
    $s = "select d.idimmsgconfdest,s.nome,s.idpessoa,d.inseridomanualmente,d.criadopor,d.criadoem,d.status,s.idtipopessoa
            from immsgconfdest d,pessoa s
                where s.idpessoa = d.idobjeto
                and d.objeto ='pessoa'
                and d.idimmsgconf = ".$_1_u_immsgconf_idimmsgconf." order by s.nome";

    $rts = d::b()->query($s) or die("listaPessoa: ". mysqli_error(d::b()));

    echo "<table class='table-hover'><tbody>";
    while ($r = mysqli_fetch_assoc($rts)) {
        if($r['idtipopessoa']==1){
            $mod='funcionario';
        }else{
            $mod='pessoa';
        }
        if ($r["status"] == 'ATIVO'){ $opacity = ''; $cor = 'verde hoververde'; }else{ $opacity = 'opacity'; $cor = 'vermelho hoververmelho ';}
        if($r['inseridomanualmente']=='N'){
            $botao="<i class='fa fa-check-circle-o  fa-1x ".$cor." btn-lg pointer ui-droppable' status='".$r["status"]."' idimmsgconfdest='".$r["idimmsgconfdest"]."'  onclick='AlteraStatus(this)'></i>";
        }else{
            $botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' status='".$r["status"]."' idimmsgconfdest='".$r["idimmsgconfdest"]."'  onclick='excluircom(".$r["idimmsgconfdest"].")'></i>";
        }
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
        
        echo "<tr id=".$r["idimmsgconfdest"]." class='".$opacity."'><td>".$r["nome"]."</td><td>".$botao."</td> <td><a class='fa fa-bars pointer hoverazul' title='Setor' onclick=\"janelamodal('?_modulo=".$mod."&_acao=u&idpessoa=".$r["idpessoa"]."')\"></a></td></tr>";

    }
    echo "</tbody></table>";
    
}

function getJpessoa(){
    global $JSON, $_1_u_immsgconf_idimmsgconf,$_1_u_immsgconf_tipo;
    if($_1_u_immsgconf_tipo=='E' or $_1_u_immsgconf_tipo=='ET' or  $_1_u_immsgconf_tipo=='EP'){
        $andtppes=" ";
    }else{
        $andtppes=" and a.idtipopessoa =1 ";
    }
    $s = "select 
                a.idpessoa
                ,concat(a.nome,'-',t.tipopessoa) as nome
            from pessoa a join tipopessoa t on (t.idtipopessoa=a.idtipopessoa)
            where a.idempresa =".idempresa()." 
                and a.status ='ATIVO'
                 ".$andtppes."
                    and not exists(
                            SELECT 1
                            FROM immsgconfdest v
                            where  v.idimmsgconf= ".$_1_u_immsgconf_idimmsgconf." 
                                and v.objeto ='pessoa'
                                and a.idpessoa = v.idobjeto				
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

/* *********************************** Tabelas dos databases configurados ****************************** */
function jsonTabelasCarbonApp(){
	$sql = "select table_schema as db, table_name as tab from information_schema.tables 
			where table_schema='"._DBAPP."'
			union all
			select table_schema,table_name as tab from information_schema.tables 
			where table_schema='"._DBCARBON."'";

	$res = d::b()->query($sql);	
	
	$arrtmp=array();
	$i=0;
	while ($r = mysqli_fetch_assoc($res)) {
        $arrtmp[$i]["value"]=$r["tab"];
		$arrtmp[$i]["label"]= $r["tab"];
		$arrtmp[$i]["db"]= $r["db"];
		$i++;
    }

    $json = new Services_JSON();
	return $json->encode($arrtmp);
}
?>
<style>
.diveditor2 {
    border: 1px solid #ccc;
    background-color: white;
    color: black;
    font-family: Arial,Verdana,sans-serif;
    font-size: 10pt;
    font-weight: normal;
    width: 695px;
    height: 98%;
    word-wrap: break-word;
    overflow: auto;
    padding: 5px;
}
</style>
<div class="col-md-12">
<div class="panel panel-default">
    <div class="panel-heading">
        <table>
            <tr>
                <td align="right">Tà­tulo:</td>
                <td>
                    <input id="idimmsgconf" name="_1_<?=$_acao?>_immsgconf_idimmsgconf" type="hidden" value="<?=$_1_u_immsgconf_idimmsgconf?>" readonly='readonly'>
                    <input name="_1_<?=$_acao?>_immsgconf_titulo" type="text" class="size50" value="<?=$_1_u_immsgconf_titulo?>">
                </td>
                
                <td align="right" class="nowrap">Reenviar a cada:</td>
                <td>
                    <select name="_1_<?=$_acao?>_immsgconf_multiplo" vnulo>
                        <?fillselect("
						select '1 HOUR','1 Hora' union 
						select '2 HOUR','2 Horas' union 
						select '3 HOUR','3 Horas' union 
						select '4 HOUR','4 Horas' union 
						select '5 HOUR','5 Horas' union 
						select '6 HOUR','6 Horas' union 
						select '7 HOUR','7 Horas'  union 
						select '8 HOUR','8 Horas'  union 
						select '9 HOUR','9 Horas'  union 
						select '10 HOUR','10 Horas'  union 
						select '11 HOUR','11 Horas'  union 
						select '12 HOUR','12 Horas'  union 
						select '1 DAY','1 Dia' union 
						select '2 DAY','2 Dias' union 
						select '3 DAY','3 Dias' union 
						select '4 DAY','4 Dias' union 
						select '5 DAY','5 Dias' union 
						select '6 DAY','6 Dias'  union 
						select '1 WEEK','1 Semana'  union 
						select '2 WEEK','2 Semanas'  union 
						select '3 WEEK','3 Semanas'  union 
						select '4 WEEK','4 Semanas'  union 
						select '1 MONTH','1 Mes'  union 
						select '2 MONTH','2 Meses'  union 
						select '3 MONTH','3 Meses'  union 
						select '4 MONTH','4 Meses'  union 
						select '5 MONTH','5 Meses'  union 
						select '6 MONTH','6 Meses'  union 
						select '1 YEAR','1 Ano'",$_1_u_immsgconf_multiplo);?>
                    </select>                             
                </td>
                
                <td align="right" class="nowrap">A partir De:</td>
                <td>
                    <input name="_1_<?=$_acao?>_immsgconf_apartirde" class="calendario" type="text"  value="<?=$_1_u_immsgconf_apartirde?>" vnulo>            
                </td>
                <td align="right">Status:</td>
                <td>
                    <select name="_1_<?=$_acao?>_immsgconf_status">
                        <?fillselect("select 'PENDENTE','Pendente' union  select 'ATIVO','Ativo' union select 'INATIVO','Inativo' ",$_1_u_immsgconf_status);?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Tà­tulo Curto:</td>
                <td>                    
                    <input name="_1_<?=$_acao?>_immsgconf_titulocurto" type="text" class="size20" value="<?=$_1_u_immsgconf_titulocurto?>">
                </td>
            </tr>
        </table>        
    </div>
    <div class="panel-body">
        <?
        if(!empty($_1_u_immsgconf_idimmsgconf)){
        ?>
        <div class="col-md-6">
        <table>
             <tr>
                <td align="right" >Modulo:</td>
                <td nowrap="nowrap">
                    <input <?=$readonly?> type="text" name="_1_<?=$_acao?>_immsgconf_modulo" cbvalue="<?=$_1_u_immsgconf_modulo?>" value="<?=$arrmodulo[$_1_u_immsgconf_modulo]["modulo"]?>" style="width: 20em;" vnulo>
                </td>
                <td>
                    <?if($_1_u_immsgconf_modulo){?>
                    <a class="fa fa-bars pointer hoverazul" title="Mà³dulo" onclick="janelamodal('?_modulo=_modulo&_acao=u&modulo=<?=$_1_u_immsgconf_modulo?>')"></a>
                    <?}?>
                </td>
                <td align="right">Tipo Mens.:</td>
                <td>
                    <select name="_1_<?=$_acao?>_immsgconf_tipo" class="size15" vnulo>                       
                        <?fillselect("select 'T','Tarefa' union select 'M','Mensagem' union select 'A','Assinatura' ",$_1_u_immsgconf_tipo);?>
                    </select>                   
                </td>
            </tr>
           
            <?
            if($_1_u_immsgconf_tipo=='E' or $_1_u_immsgconf_tipo =='ET' or $_1_u_immsgconf_tipo=='EP'){
            ?>
             <tr>
                <td align="right" >Tabela:</td>
                <td nowrap="nowrap">
                    <input <?=$readonly?> type="text" name="_1_<?=$_acao?>_immsgconf_tabela" cbvalue="<?=$_1_u_immsgconf_tabela?>" value="<?=$_1_u_immsgconf_tabela?>" style="width: 20em;" vnulo>
                </td>
                <td>
                    <?if($_1_u_immsgconf_tabela){?>
                    <a class="fa fa-bars pointer hoverazul" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?=_DBAPP.".".$_1_u_immsgconf_tabela?>')" ></a>
                    <?}?>
                </td>
  
                <td></td><td></td>

                </tr>
             <tr>
                <td align="right">Assunto:</td>
                <td colspan="4">
                    <input name="_1_<?=$_acao?>_immsgconf_assunto" type="text"  value="<?=$_1_u_immsgconf_assunto?>" vnulo>
                </td>
             </tr>
            <?
            }
            ?>
            <tr>
                <td class="lbr" nowrap>Mensagem:</td>
                 <td colspan="4">
                    <div id="diveditor2" 
                        class="diveditor2"
                        onkeypress="pageStateChanged=true;"
                        style="width: 100%;height: 200px;"><?=$_1_u_immsgconf_mensagem?></div>
							  
                    <textarea 
                          style="display: none;"
                          name="_1_<?=$_acao?>_immsgconf_mensagem"><?=$_1_u_immsgconf_mensagem?></textarea>               
                
                </td>	
            </tr>
        </table>
        </div>
        <div class="col-md-6">
 <?
        if($_1_u_immsgconf_tipo=='E' or $_1_u_immsgconf_tipo=='ET' or  $_1_u_immsgconf_tipo=='EP'){
 ?>
            <table>
                 <tr>
                    <td align="right" class="nowrap">Token Expira em:</td>
                    <td class="nowrap">                        
                        <input name="_1_<?=$_acao?>_immsgconf_expiraem" type="text" class="size5" value="<?=$_1_u_immsgconf_expiraem?>"> Dias apà³s envio.
                    </td>
                </tr>
                <tr>
                    <td align="right" class="nowrap">Email Teste:</td>
                    <td>                        
                        <input name="_1_<?=$_acao?>_immsgconf_emailteste" type="text" class="size30" value="<?=$_1_u_immsgconf_emailteste?>">
                    </td>
                </tr>
                <tr>
                    <td align="right" class="nowrap">Nome From:</td>
                    <td>                        
                        <input name="_1_<?=$_acao?>_immsgconf_rotulofrom" type="text" class="size30" value="<?=$_1_u_immsgconf_rotulofrom?>">
                    </td>
                </tr>
                <tr>
                    <td align="right" class="nowrap">Email From:</td>
                    <td>                        
                        <input name="_1_<?=$_acao?>_immsgconf_emailfrom" type="text" class="size30" value="<?=$_1_u_immsgconf_emailfrom?>">
                    </td>
                </tr>
                <tr>
                    <td align="right" class="nowrap">Email Cà³pia:</td>
                    <td>                        
                        <input name="_1_<?=$_acao?>_immsgconf_emailcco" type="text" class="size30" value="<?=$_1_u_immsgconf_emailcco?>">
                    </td>
                </tr>
                <tr>
                    <td align="right" class="nowrap">Rodapé Email:</td>
                    <td>  
                        <select name="_1_<?=$_acao?>_immsgconf_idrodapeemail"> 
                            <option></option>
                            <?fillselect("select idrodapeemail,rotulo from rodapeemail where status='ativo' order by rotulo",$_1_u_immsgconf_idrodapeemail);?>
                        </select>
                    </td>
                </tr>
                <?if($_1_u_immsgconf_tipo=='ET' or  $_1_u_immsgconf_tipo=='EP'){?>
                <tr>
                    <td align="right" >Modulo Token:</td>
                    <td nowrap="nowrap">
                        <input type="text" name="_1_<?=$_acao?>_immsgconf_modulodest" cbvalue="<?=$_1_u_immsgconf_modulodest?>" value="<?=$arrmodulodest[$_1_u_immsgconf_modulodest]["modulo"]?>" style="width: 27em;" >
                    </td>
                    <td>
                    <?if($_1_u_immsgconf_modulodest){?>
                    <a class="fa fa-bars pointer hoverazul" title="Mà³dulo Destino" onclick="janelamodal('?_modulo=_modulo&_acao=u&modulo=<?=$_1_u_immsgconf_modulodest?>')"></a>
                    <?}?>
                    </td>
                </tr>
                <?
                    if(empty($_1_u_immsgconf_modulodest) and !empty($_1_u_immsgconf_modulo)){
                        $urldest=traduzid("carbonnovo._modulo","modulo","urldestino",$_1_u_immsgconf_modulo);
                ?>
                 <tr>
                    <td align="right" >Url Token:</td>
                    <td nowrap="nowrap">
                    <?=$urldest?>
                    </td>
                 </tr>
                <?
                    }
                }//if($_1_u_immsgconf_tipo=='ET' or  $_1_u_immsgconf_tipo=='EP'){
                ?>
                
            </table>
<?
        }
?>
        </div>
        <?
        }//if(!empty($_1_u_immsgconf_idimmsgconf)){
        ?>
    </div>
</div>
</div>
<?
if(!empty($_1_u_immsgconf_idrodapeemail)){
    $sqlr="select * from rodapeemail where idrodapeemail=".$_1_u_immsgconf_idrodapeemail;
    $resr= d::b()->query($sqlr) or die("Erro ao buscar rodape de email : " . mysql_error() . "<p>SQL:".$sqlrql);
    $rowr=mysqli_fetch_assoc($resr);
?>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading" data-toggle="collapse" href="#localInfo2">Rodapé de email</div>
        <div class="panel-body"> 
           
                         <?echo($rowr["valor"])?>
                        
        </div>
        <div class="panel-body collapse"  id="localInfo2">
            <table>
                <tr>
                    <th>Rodapé Html</th>
                </tr>
                <tr>
                    <td>
                        <input name="_2_u_rodapeemail_idrodapeemail" type="hidden" class="size30" value="<?=$rowr["idrodapeemail"]?>">
                        <textarea  style="width: 710px; height: 352px;"  name="_2_u_rodapeemail_valor" onchange="CB.Post()" ><?=$rowr["valor"]?></textarea>
                    </td>
                </tr>
            </table>

        </div> 
    </div>
</div>
<?
}//if(!empty($_1_u_immsgconf_idrodapeemail)){
if($_1_u_immsgconf_modulo){
?>
<div class="col-md-5">
    <div class="panel panel-default">
        <div class="panel-heading" data-toggle="collapse" href="#localInfo1" >Destinatários</div>
        <div class="panel-body  collapse"  id="localInfo1"> 
            <table>
            <tr>
                <td id="tdfuncionario"><input id="pessoavinc" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                <td id="tdsgsetor"><input id="sgsetorvinc" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                <td class="nowrap" style="width: 110px">  
                    <div class="btn-group nowrap" role="group" aria-label="..."> 
                        <button onclick="showfuncionario()" type="button" class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright " title="Selecionar Funcionário" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>		
                        <button onclick="showsgsetor()" type="button" class=" btn btn-default fa fa-users hoverlaranja pointer floatright selecionado" title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>  
                    </div>
                </td>
            </tr>
            </table>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <?=listaSgsetor()?> 
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <?=listaPessoa()?>
                </div>
            </div>
        </div>
    </div>
</div>
<?    
    $sqlm="select tab from "._DBCARBON."._modulo where modulo ='".$_1_u_immsgconf_modulo."'";    
    $resm = d::b()->query($sqlm) or die("Erro aos buscar dados do modulo: ".mysqli_error(d::b())."\n".$sqlm);
    $rowm=mysqli_fetch_assoc($resm);   
    
    if(empty($rowm['tab'])){
?>
<div class="col-md-7">
    <div class="panel panel-default">
        <div class="panel-heading"> <span class="alert-error">Modulo não possui tabela vinculada.</span></div>
    </div>
</div>
<?
    }else{
        $sqlma="select * from "._DBCARBON."._mtotabcol where tab ='".$rowm['tab']."' and col='alteradoem'";    
        $resma = d::b()->query($sqlma) or die("Erro aos buscar alteradopor do modulo: ".mysqli_error(d::b())."\n".$sqlma);
        $qtdma=mysqli_num_rows($resma);      
    
        if($qtdma<1){
?>
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading"> <span class="alert-error">Modulo [<a class="pointer" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?=_DBAPP?>.<?=$rowm['tab']?>')"><?=$rowm['tab']?></a>] não possui campo alteradopor. <br> Favor informar ao setor de TI para criação do mesmo.</span></div>
        </div>
    </div>
<?    
        
        }else{
           
            d::b()->query("delete f.* from immsgconffiltros f 
                            where  not exists ( 
                                                select 1  from "._DBCARBON."._mtotabcol tc 
                                                where  tc.tab='".$rowm['tab']."' and tc.col=f.col and tc.rotcurto is not null and tc.rotcurto!=' ' 
                                              )
                                and f.idimmsgconf = ".$_1_u_immsgconf_idimmsgconf."") or die("Erro ao apagar objetos relacionados: ".mysqli_error(d::b())."\Nsql: ");

            $sqlin="INSERT INTO immsgconffiltros
                            (idempresa,idimmsgconf,col,criadopor,criadoem,alteradopor,alteradoem)
                            (select 
                            ".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_1_u_immsgconf_idimmsgconf.", col,'".$_SESSION["SESSAO"]["USUARIO"]."',NOW(),'".$_SESSION["SESSAO"]["USUARIO"]."',NOW()
                                from "._DBCARBON."._mtotabcol tc  
                                where tc.tab='".$rowm['tab']."' and tc.rotcurto is not null and tc.rotcurto!=' ' 
                                and not exists (select 1 from immsgconffiltros mf where mf.idimmsgconf =".$_1_u_immsgconf_idimmsgconf." and  tc.col = mf.col))";
            //die($sqlin);
            $resin = d::b()->query($sqlin) or die("Erro ao inserir objetos relacionados: ".mysqli_error(d::b())."\Nsql:".$sqlin);

            $sql="select mf.*,tc.datatype,tc.col as colf,tc.rotcurto,tc.dropsql
                from "._DBCARBON."._modulo f 
                        join "._DBCARBON."._mtotabcol tc on(tc.tab=f.tab)
                        join immsgconffiltros mf on( mf.idimmsgconf =".$_1_u_immsgconf_idimmsgconf." and tc.col = mf.col)                         
                where  f.modulo='".$_1_u_immsgconf_modulo."' order by  colf";
            $res = d::b()->query($sql) or die("Erro aos buscar filtros: ".mysqli_error(d::b())."\n".$sql);
            $qtd= mysqli_num_rows($res);
?>
<div class="col-md-7">
    <div class="panel panel-default">
        <div class="panel-heading">Filtros Tabela            
            <a class="pointer" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?=_DBAPP?>.<?=$rowm['tab']?>')"><?=$rowm['tab']?></a>
       </div>
        <div class="panel-body">
            <?if($qtd>0){?>
            <table class="planilha grade compacto">
                <tr>
                    <th>Rà³tulo</th>

                    <th>Tipo</th>
                    <th>Where</th>
                    <th>Valor</th>
<?
                    if($_1_u_immsgconf_tipo=='E' or $_1_u_immsgconf_tipo=='ET' or $_1_u_immsgconf_tipo=='EP'){
?>                  
                    <th>Substituir no texto</th>
                    <th>Substituir no Titulo</th>
                    <th>Anexo</th>
                    <th>Extensão</th>
<?
                    }
?>
                </tr>
                <?
                $l=99;
              
                while($row=mysqli_fetch_assoc($res)){
                    $l=$l+1;
                   
                    if(!empty($row["valor"])){
                        $bcolor="#99cc99";
                    }else{
                        $bcolor="white";  
                    }
                    
                ?>
                <tr style="background-color:<?=$bcolor?>;">
                  
                    <td><?=$row["rotcurto"]?>
                     <input id="idimmsgconf" name="_<?=$l?>_u_immsgconffiltros_idimmsgconffiltros" type="hidden" value="<?=$row["idimmsgconffiltros"]?>">
                    </td>     
                    <td><?=$row["datatype"]?></td>                
                                        
                    <?if(!empty($row['dropsql'])){
                    
                        $arrvalor= explode(',', $row["valor"]);                                     
                        $sqlm= str_replace( "sessao_idempresa"," idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"],$row['dropsql']);                        
                        //echo($sqlm);
                   ?>
                    <td align="center"> 
                        <input name="_<?=$l?>_u_immsgconffiltros_sinal" type="hidden" value="in"> in
                    </td>
                    <td>
                        <select class="selectpicker valoresselect" multiple="multiple" data-live-search="true" onchange="atualizavalor(this,<?=$row["idimmsgconffiltros"]?>);">
<?                         
                        $resm =  d::b()->query($sqlm)  or die("Erro configura _mtotabcol campo 	Prompt Drop sql:".$sqlm);
                        while ($rowm = mysqli_fetch_assoc($resm)) {
                            if (in_array($rowm['id'],$arrvalor)){
                                $selected= 'selected';
                            }else{
                                $selected= '';
                            }
                            echo '<option data-tokens="'.retira_acentos($rowm['valor']).'" value="'.$rowm['id'].'" '.$selected.' >'.$rowm['valor'].'</option>'; 
                        }		
		?>
                    </select> 
                    </td> 
                    <?      
                    }elseif($row["datatype"]=='date' or $row["datatype"]=='datetime'){?> 
                    <td>                    
                        <select   name="_<?=$l?>_u_immsgconffiltros_sinal" vnulo>
                            <?fillselect("select '=','Igual' union select '>','Maior que'  union select '<','Menor que' 
                                  union select 'like','Like'",$row["sinal"]);?>		
                        </select>
                    </td>
                    <td class="nowrap"> 
                        <select class="size5"  name="_<?=$l?>_u_immsgconffiltros_valor">
                            <option value=''></option>
                            <?fillselect("select 'now','Now' ",$row["valor"]);?>		
                        </select>
                        <input class="size5" placeholder="Dia" name="_<?=$l?>_u_immsgconffiltros_nowdias" type="text" value="<?=$row["nowdias"]?>"> Dias
                    </td>
 <?                             
                    }else{ 
                        ?> 
                    <td>                    
                        <select   name="_<?=$l?>_u_immsgconffiltros_sinal" vnulo>
                            <?fillselect("select '=','Igual' union select '>','Maior que'  union select '<','Menor que' 
                                  union select 'like','Like'",$row["sinal"]);?>		
                        </select>
                    </td>
                    <td>
                        <input name="_<?=$l?>_u_immsgconffiltros_valor" type="text" value="<?=$row["valor"]?>">  
                    </td>
 <?              }//if(!empty($row['dropsql'])){

            if($_1_u_immsgconf_tipo=='E' or $_1_u_immsgconf_tipo=='ET' or $_1_u_immsgconf_tipo=='EP'){   
?>
                    <td>
                        <input name="_<?=$l?>_u_immsgconffiltros_substituir" type="text" value="<?=$row["substituir"]?>">  
                    </td>
                    <td>
                        <input name="_<?=$l?>_u_immsgconffiltros_substituirtit" type="text" value="<?=$row["substituirtit"]?>">  
                    </td>
                    <td>
                        <input name="_<?=$l?>_u_immsgconffiltros_nomearq" type="text" value="<?=$row["nomearq"]?>">  
                    </td>
                    <td>
                        <input name="_<?=$l?>_u_immsgconffiltros_extensaoarq" type="text" value="<?=$row["extensaoarq"]?>">  
                    </td>
<?
            }//if($_1_u_immsgconf_tipo!='E'){
?>
                </tr>
                <?
               
                            }//while($row=mysqli_fetch_assoc($res)){
               
                ?>
            </table>
            <?}else{?>
            <?}//if($qtd>0){?>
        </div>
    </div>
</div>
<?

        }//if($qtdma<1){ se tem alteradopor
    }//if(empty($rowm['tab'])){
    
    ## mostrar dados que seriam enviados caso ative a configuraçaàµ
    if($_1_u_immsgconf_status!='ATIVO'){   
    //busca  as configuraçàµes para envio da mensagem
        $sql="select 
		m.tab,m.modulo,m.rotulomenu,tc.col,ic.idimmsgconf,ic.titulo,ic.tipo,ic.code,ic.mensagem,ic.apartirde
            from "._DBCARBON."._modulo m,immsgconf ic,"._DBCARBON."._mtotabcol tc
            where m.modulo =ic.modulo 
                and ic.idimmsgconf=".$_1_u_immsgconf_idimmsgconf."
                and tc.primkey ='Y'         
                and tc.tab = m.tab          
                and exists (select 1 from immsgconffiltros f where f.valor!=' ' and f.valor is not null and f.idimmsgconf = ic.idimmsgconf)";
        
        //echo($sql);
        $res=d::b()->query($sql) or die("A Consulta na immsgconf falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
        

        while($row=mysqli_fetch_assoc($res)){
            //busca os filtros para seleção
            $sqlf="select col,sinal,valor,nowdias,idimmsgconffiltros from immsgconffiltros where valor!='' and valor!=' ' and valor!='null' and valor is not null and idimmsgconf =".$row["idimmsgconf"];
            $resf=d::b()->query($sqlf) or die("A Consulta na immsgconffiltros falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlf");
            $qtdf=mysqli_num_rows($resf);
            $and=" ";
            if($qtdf>0){
                while($rowf=mysqli_fetch_assoc($resf)){
                    if($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!=''){
                        if($rowf["valor"]=='now'){
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
                        }else{
                             $clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." '".$valor."'";
                        }
                        $and=" and ";
                    }
                }
                // busca na tabela configurada os ids
                $sqlx="SELECT distinct
                        a.".$row['col']." AS idpk, 1029 as idpessoa
                    FROM
                        ".$row["tab"]." a 
                    WHERE
                        ".$clausula."
                            and a.alteradoem > '".$row['apartirde']."'
                            AND NOT EXISTS(  SELECT 1 
					FROM immsgconflog l
					JOIN immsgconf m on m.idimmsgconf = l.idimmsgconf
					WHERE l.idpk = a.".$row['col']."
					AND l.modulo = '".$row['modulo']."'
					AND l.idimmsgconf = ".$row['idimmsgconf']."
					AND CASE
						  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Minute' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) MINUTE)
						  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Hour' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) HOUR)
						  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Year' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) YEAR)
						  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Month' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) MONTH)
						  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Day' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) DAY)
						  WHEN SUBSTR(multiplo, CHAR_LENGTH(CAST(multiplo AS UNSIGNED))+2) ='Week' THEN DATE_ADD(l.criadoem, INTERVAL CAST(multiplo AS UNSIGNED) WEEK) 
						END > NOW())
					ORDER BY
						a.".$row['col']." DESC";
                // echo($sqlx);
                $resx=d::b()->query($sqlx) or die("A Consulta na tabela de origem dos dados falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlx");
                $qtdc=mysqli_num_rows($resx);
				//die;
                if($qtdc>0){
?>
<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">[<?=$qtdc?>] - Registros serão enviados caso ATIVE este Alerta.</div>
        <div class="panel-body">
             <table class="planilha grade compacto">
                <tr>
                    <th>ID</th>
                    <th>Modulo</th>
                    <th>Destinário</th>
                </tr>
<?

                    while($rowx=mysqli_fetch_assoc($resx)){  
					
										
			if($row['tipo']=="A"){
				
				$cl = " AND NOT EXISTS (SELECT 1 FROM carrimbo ca where ca.idpessoa = p.idpessoa and ca.idempresa = 1 and ca.idobjeto = '".$rowx['idpk']."' and status = 'ATIVO' and tipoobjeto = '".$row['modulo']."' ) ";
			}
			
                 $sqlc="select distinct(idpessoa) as idpessoa, nome 
                        from (	
                                    SELECT 
                                            p.idpessoa, p.nome
                                    FROM
                                            pessoa p,
                                            immsgconfdest c
                                    WHERE
                                            c.objeto = 'pessoa'
                                            AND c.status='ATIVO'
                                            AND p.idpessoa = c.idobjeto
                                            AND c.idimmsgconf = ".$row['idimmsgconf']."
                                            AND p.status = 'ATIVO'
											AND NOT EXISTS (SELECT 1 FROM immsgbody mb
													join immsg m on m.idimmsgbody = mb.idimmsgbody where m.idpessoa = p.idpessoa and modulopk = '".$rowx['idpk']."' and  modulo = '".$row['modulo']."' and statustarefa = 'A'
													)
											AND EXISTS (select 1 from ".$row["tab"]." a where a.".$row['col']." = '".$rowx['idpk']."' and a.alteradoem > p.criadoem)	
											".$cl."
                                ) as u order by u.nome";
                    $resc=d::b()->query($sqlc) or die("A busca dos contatos falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlc");


                        while($rowc=mysqli_fetch_assoc($resc)){
                            $link="?_modulo=".$row['modulo']."&_acao=u&".$row['col']."=".$rowx['idpk'];
?>                
                <tr>
                    <td>
                        <a class="pointer" onclick="janelamodal('<?=$link?>')"><?=$rowx['idpk']?></a>                
                    </td>
                    <td><?=$row['modulo']?></td>
                    <td><?=$rowc['nome']?></td>
                </tr>
<?
                        }//while($rowc=mysqli_fetch_assoc($resc)){
                    }//  while($rowx=mysqli_fetch_assoc($resx)){ 
    ?>
                </table>
        </div>
    </div>
</div>
<?
                }//if($qtdc>0){
            }////if($qtdf>0){
        }//while($row=mysqli_fetch_assoc($res)){
    }else{//if($_1_u_immsgconf_status!='ATIVO'){ // fim motrar dados que seriam enviado caso processo fosse ativado
        $sqllog="select l.idpk,l.modulo,mf.col,l.status as statuslog, l.criadoem,e.evento,r.visualizado ,p.idpessoa,p.nomecurto
            from  immsgconflog l 
            left join evento e on (e.idevento = l.idimmsgbody)
            left join eventoresp r on (e.idevento = r.idevento)
            left join pessoa p on (r.idobjeto = p.idpessoa and r.tipoobjeto = 'pessoa')
            left join carbonnovo._modulofiltros mf on( l.modulo = mf.modulo and mf.parget ='Y' )
            where l.idimmsgconf =".$_1_u_immsgconf_idimmsgconf." order by l.criadoem desc, l.idpk desc, p.nomecurto  limit 100";
        $r=d::b()->query($sqllog) or die("Erro ao buscar log de envio : " . mysqli_error(d::b()) . "<p>SQL: $sqllog");
        $qtd=mysqli_num_rows($r);
        if($qtd>0){
?>
<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">Log de registros enviados por este Alerta.</div>
        <div class="panel-body">
             <table class="planilha grade compacto">
                <tr>
                    <th>ID</th>
                    <th>Modulo</th>
                    <th>Destinário</th>
                    <th>Data</th>
                    <th>Status Envio</th>
                    <th>Status Mens.</th>
                </tr>
<?
        while($ro=mysqli_fetch_assoc($r)){
            $link="?_modulo=".$ro['modulo']."&_acao=u&".$ro['col']."=".$ro['idpk'];
?>    
                <tr>
                    <td>
                        <a class="pointer" onclick="janelamodal('<?=$link?>')"><?=$ro['idpk']?></a>                
                    </td>
                    <td><?=$ro['modulo']?></td>
                    <td><?=$ro['nomecurto']?></td>
                    <td><?=dmahms($ro['criadoem'])?></td>
                    <td><?=$ro['statuslog']?></td>
                    <td><?=$ro['visualizado']?></td>
                </tr>
<?
        }//while($ro=mysqli_fetch_assoc($r)){
?>
             </table>
        </div>
    </div>
</div>
<? 
        }// if($qtd>0){
    }//}else{//if($_1_u_immsgconf_status!='ATIVO')
}//if($_1_u_immsgconf_idimmsgconf){
?>
    <div class="col-md-12 ">
	 <?$tabaud = "immsgconf";?>
	<div class="panel panel-default">		
	    <div class="panel-body">
		<div class="row col-md-12">		
		    <div class="col-md-2 nowrap" >Criado Por:</div>     
		    <div class="col-md-4"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
		    <div class="col-md-2 nowrap">Criado Em:</div>     
		    <div class="col-md-4"><?=${"_1_u_".$tabaud."_criadoem"}?></div>   
		</div>
		<div class="row col-md-12">            
		    <div class="col-md-2 nowrap">Alterado Por:</div>     
		    <div class="col-md-4"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
		    <div class="col-md-2 nowrap" >Alterado Em:</div>     
		    <div class="col-md-4"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>       
		</div>
	    </div>
	</div>
    </div>  
<?
$jFuncionario="null";
if(!empty($_1_u_immsgconf_idimmsgconf)){    
    $jFuncionario= getJpessoa();
}

$jSetor="null";
if(!empty($_1_u_immsgconf_idimmsgconf)){    
    $jSetor=getSetor();
} 
?>  
<script>
<?if(!empty($_1_u_immsgconf_idimmsgconf)){?>
$('#tdsgsetor').show();
$('#tdfuncionario').hide();    
<?}?>  
  
jModulo=<?=$jModulo?>;
jModulo = jQuery.map(jModulo, function(o, id) {
	return {"label": o.modulo, value:id+"","rotulomenu":o.rotulomenu }
});

$("[name*=_immsgconf_modulo]").autocomplete({
    source: jModulo
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.rotulomenu+"</span></a>").appendTo(ul);
        };
    }	
});

jModulodest=<?=$jModulodest?>;
jModulodest = jQuery.map(jModulodest, function(o, id) {
	return {"label": o.modulo, value:id+"","rotulomenu":o.rotulomenu }
});
$("[name*=_immsgconf_modulodest]").autocomplete({
    source: jModulodest
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.rotulomenu+"</span></a>").appendTo(ul);
        };
    }	
});

jSetor=<?=$jSetor?>;


$("#sgsetorvinc").autocomplete({
	source: jSetor
	,delay: 0
	,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);

            };
	}
        ,select: function(event, ui){
            CB.post({
                objetos: {
                    "_x_i_immsgconfdest_idimmsgconf":$(":input[name=_1_"+CB.acao+"_immsgconf_idimmsgconf]").val()
                   ,"_x_i_immsgconfdest_idobjeto": ui.item.value
                   ,"_x_i_immsgconfdest_objeto": 'imgrupo'
                }
                ,parcial: true
            });
        }
	
});

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
                "_x_i_immsgconfdest_idimmsgconf":$(":input[name=_1_"+CB.acao+"_immsgconf_idimmsgconf]").val()
               ,"_x_i_immsgconfdest_idobjeto": ui.item.value
               ,"_x_i_immsgconfdest_objeto": 'pessoa'
               ,"_x_i_immsgconfdest_inseridomanualmente":'Y'
            }
            ,parcial: true
        });
    }
});

function showfuncionario(){
    $('#tdsgsetor').hide();
    $('#tdfuncionario').show(); 
}
function showsgsetor(){
    $('#tdsgsetor').show();
    $('#tdfuncionario').hide();      
}
function retirasgsetor(inid){
    CB.post({
        objetos: {
            "_x_d_immsgconfdest_idimmsgconfdest":inid
        }
        ,parcial: true
    });
}

function excluircom(inid){
    CB.post({
        objetos: {
            "_x_d_immsgconfdest_idimmsgconfdest":inid
        }
        ,parcial: true
    });
}

$('.selectpicker').selectpicker('render');

function atualizavalor(vthis,idimmsgconffiltros){
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_u_immsgconffiltros_idimmsgconffiltros":idimmsgconffiltros
            ,"_x_u_immsgconffiltros_valor":strval
        }
        ,parcial: true
        ,refresh:false
    });
}


function AlteraStatus(vthis){	

	var idimmsgconfdest  = $(vthis).attr('idimmsgconfdest');
	var status = $(vthis).attr('status');	
	var  cor, novacor;  

        if (status == 'ATIVO'){
            cor = 'verde hoververde';
            novacor = 'vermelho hoververmelho';
            CB.post({
                    objetos: "_x_u_immsgconfdest_idimmsgconfdest="+idimmsgconfdest+"&_x_u_immsgconfdest_status=INATIVO"
                    ,parcial:true
                    ,msgSalvo: "Status Alterado"
                    ,posPost: function(){
                        $(vthis).removeClass(cor);
                        $(vthis).addClass(novacor);
                    } 
                });

        }else{

            cor = 'vermelho hoververmelho';
            novacor = 'verde hoververde';
            CB.post({
                        objetos: "_x_u_immsgconfdest_idimmsgconfdest="+idimmsgconfdest+"&_x_u_immsgconfdest_status=ATIVO"
                        ,parcial:true
                        ,msgSalvo: "Status Alterado"
                        ,posPost: function(){
                            $(vthis).removeClass(cor);
                            $(vthis).addClass(novacor);
                        } 
                    });
        }
    }
   

    //editor2
    sSeletor2 = '#diveditor2';
    oMensagem = $("[name=_1_"+CB.acao+"_immsgconf_mensagem]");

    if(tinyMCE.editors["diveditor2"]){
        tinyMCE.editors["diveditor2"].remove();
    }
    //Inicializa Editor 2
    tinymce.init({
            selector: sSeletor2
            ,language: 'pt_BR'
            ,inline: true /* não usar iframe */
            ,toolbar: 'removeformat | fontsizeselect | bold | subscript superscript | bullist numlist | table'
            ,menubar: false
            ,plugins: ['table']
            ,fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
            ,removeformat: [
                    {selector: 'b,strong,em,i,font,u,strike', remove : 'all', split : true, expand : false, block_expand: true, deep : true},
                    {selector: 'span', attributes : ['style', 'class'], remove : 'empty', split : true, expand : false, deep : true},
                    {selector: '*', attributes : ['style', 'class'], split : false, expand : false, deep : true}
            ]
            ,setup: function (editor) {
                    editor.on('init', function (e) {
                            this.setContent(oDescritivo2.val());
                    });
            }
    });
//Antes de salvar atualiza o textarea
    CB.prePost = function(){
        if(tinyMCE.get('diveditor2')){//editor2
                oMensagem.val( tinyMCE.get('diveditor2').getContent() );
        }		
    }

 
jsonTabCarbonApp = <?=jsonTabelasCarbonApp()?>;


//Autocomplete de Tabelas
$(":input[name=_1_"+CB.acao+"_immsgconf_tabela]").autocomplete({
	source: jsonTabCarbonApp
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
			vitem = "<span class='cinzaclaro'>"+item.db+".</span>" + item.value;
			return $('<li>')
				.append('<a>' + vitem + '</a>')
				.appendTo(ul);
		};
	}/*,
	select: function(event, ui){
		mostraDetalhesCliente();
		preencheDropNucleos(ui.item.value);
	},
	create: function( event, ui ) {
		mostraDetalhesCliente();
	}*/
});
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>