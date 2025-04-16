<?
require_once("../inc/php/functions.php");
require_once(__DIR__."/querys/_iquery.php");
require_once(__DIR__."/querys/objetojson_query.php");

$idlotecons= $_GET["idlotecons"];
$idlote=$_GET["idlote"];
$idnf=$_GET["idnf"];

if($_POST){
	include_once("../inc/php/cbpost.php");
}
if(empty($idlote)){
    $sql='select * from loterotulo where idlotecons='.$idlotecons;
    $idlote= traduzid('lotecons', 'idlotecons', 'idlote', $idlotecons);
}else{
    $sql='select * from loterotulo where idlote='.$idlote;     
}
$res = d::b()->query($sql) or die("erro ao buscar loterotulo" . mysqli_error(d::b()) . "<p>SQL: ".$sql);
$qtdr= mysqli_num_rows($res);
$row=mysqli_fetch_assoc($res);
		

/*
 * Cria automaticamente as variaveis conforme os campos que retornarem do banco
 */
$_arrtabdef = retarraytabdef('loterotulo');

while(list($campo,$valor)=each($row)){

        $fldvar = "_1_u_loterotulo_".$campo;

        $_fldtype = $_arrtabdef[$campo]["type"];

        //Reconstruir a representação da coluna double conforme coluna associada "_exp" (registro do expoente)
        if($_fldtype=="double"){
                if(array_key_exists($campo."_exp", $_arrtabdef) and !empty($row[$campo."_exp"])){
                        //$arrExp=explode('e',$row[$campo."_exp"]);
                        //$valor=recuperaExpoente($row[$campo],$row[$campo."_exp"]);
                        $valor=$valor;
                }
        }

        /*
         * executa pre tratamento para formatacao de informacoes
         */
        $$fldvar = formatastringvisualizacao($valor,$_fldtype);
}


$sqlx="select
fr.status,
fr.idformularotulo,
fr.idprodservformularotulo,
       r.titulo,
       concat('PART.:',lpad(l.npartida, 3, '0'),'/',SUBSTRING(l.exercicio,3)) as partida,
                       upper(concat('FABR.:',LEFT(DATE_FORMAT(l.fabricacao, '%M'),3),'/',right(DATE_FORMAT(l.fabricacao, '%Y'),2))) as fabricacao,
                       upper(concat('VENC.:',LEFT(DATE_FORMAT(l.vencimento, '%M'),3),'/',right(DATE_FORMAT(l.vencimento, '%Y'),2))) as vencimento,
                       IF(cs.tipobotao = 'FIM', lr.indicacao,  IFNULL(NULLIF(fr.indicacao, ''),  r.indicacao)) as indicacao,
                       IF(cs.tipobotao = 'FIM', lr.formula,  IFNULL(NULLIF(fr.formula, ''),  r.formula)) as formula,
                       r.idprodservformula,
                       IF(cs.tipobotao = 'FIM', lr.cepas,  IFNULL(NULLIF(fr.cepas, ''),  r.cepas)) as cepas,
                       IF(cs.tipobotao = 'FIM', lr.modousar, r.modousar) as modousar, 
                       IF(cs.tipobotao = 'FIM', lr.programa, r.programa) as programa, 
                       r.idfrasco,
                       IF(cs.tipobotao = 'FIM', lr.descricao, r.descricao) as descricao, 
                      IF(cs.tipobotao = 'FIM', lr.conteudo, r.conteudo) as conteudo
from lote l 
left join loterotulo lr on lr.idlote = l.idlote
left join fluxostatus fs on (fs.idfluxostatus = l.idfluxostatus)
left join carbonnovo._status cs on cs.idstatus = fs.idstatus
           left join formularotulo fr on fr.idprodservformula = l.idprodservformula
           left join prodservformularotulo r on (fr.idprodservformularotulo=r.idprodservformularotulo)
        where l.idlote =".$idlote;
$resx = d::b()->query($sqlx) or die("erro ao buscar prodservformularotulo" . mysqli_error(d::b()) . "<p>SQL: ".$sql);
$rowx=mysqli_fetch_assoc($resx);

if($rowx['status'] !== 'APROVADO') {
    $resx = SQL::ini(ObjetoJsonQuery::buscarObjetoPorTipoObjeto(), [
        'idobjeto' => $rowx['idformularotulo'],
        'tipoobjeto' => 'formularotulo'
    ])::exec();

    $rowx = unserialize(base64_decode($resx->data[0]['jobjeto'])) ?? [];
} 


if($qtdr<1){
    $_1_u_loterotulo_idfrasco=$rowx['idfrasco'];
    $_acao='i';
}else{
    
    $_acao='u';

    $arrCamposModificados = [];

    $_1_u_loterotulo_idloterotulo=$row['idloterotulo'];
    $_1_u_loterotulo_criadopor=$row['criadopor'];
    $_1_u_loterotulo_criadoem=dmahms($row['criadoem']);
    $_1_u_loterotulo_alteradopor=$row['alteradopor'];
    $_1_u_loterotulo_alteradoem=dmahms($row['alteradoem']);
 
    // if(empty($_1_u_loterotulo_idprodservformula)){
        $_1_u_loterotulo_idprodservformula=$rowx['idprodservformula'] ?? '';
    // }
    
    if(empty($_1_u_loterotulo_titulo)){
        $_1_u_loterotulo_titulo=$rowx['titulo'] ?? '';
    }
    // if(empty($_1_u_loterotulo_indicacao)) {
        $arrCamposModificados['indicacao'] = true;
        $_1_u_loterotulo_indicacao=$rowx['indicacao'] ?? '';
    // }

    $arrCamposModificados['formula'] = strcmp(preg_replace('/\r|\n/', '', $_1_u_loterotulo_formula), preg_replace('/\r|\n/', '', $rowx['formula']));
    if(empty($_1_u_loterotulo_formula)) {
        $_1_u_loterotulo_formula=$rowx['formula'] ?? '';
    }

    $arrCamposModificados['cepas'] = strcmp(preg_replace('/\r|\n/', '', str_replace(' ', '', $_1_u_loterotulo_cepas)), preg_replace('/\r|\n/', '', str_replace(' ', '', $rowx['cepas'])));
    if(empty($_1_u_loterotulo_cepas)) {
        $_1_u_loterotulo_cepas=$rowx['cepas'] ?? '';
    }

    // if(empty($_1_u_loterotulo_descricao)) {
        $_1_u_loterotulo_descricao=$rowx['descricao'] ?? '';
    // } 
    
    // if(empty($_1_u_loterotulo_conteudo)) {
        $_1_u_loterotulo_conteudo=$rowx['conteudo'] ?? '';
    // }
    // if(empty($_1_u_loterotulo_modousar)) {
        $_1_u_loterotulo_modousar=$rowx['modousar'] ?? '';
    // }
    // if(empty($_1_u_loterotulo_programa)) {
        $_1_u_loterotulo_programa=$rowx['programa'] ?? '';
    // }

    // $arrCamposModificados['endereco'] = strcmp(preg_replace('/\r|\n/', '', $_1_u_loterotulo_formula), preg_replace('/\r|\n/', '', $rowx['endereco']));
    if(empty($_1_u_loterotulo_endereco)) {
        $_1_u_loterotulo_endereco=$rowx['endereco'] ?? '';
    }
    $arrCamposModificados['partida'] = strcmp(preg_replace('/\r|\n/', '', str_replace(' ', '', $_1_u_loterotulo_partida)), preg_replace('/\r|\n/', '', str_replace(' ', '', $rowx['partida']."\n".$rowx['fabricacao']."\n".$rowx['vencimento'])));
    if(empty($_1_u_loterotulo_partida)) {
        $_1_u_loterotulo_partida=$rowx['partida']."
    ".$rowx['fabricacao']."
    ".$rowx['vencimento'];
    }
}

if(empty($_1_u_loterotulo_idlotecons)){
    $_1_u_loterotulo_idlotecons=$idlotecons;
}
if(empty($_1_u_loterotulo_idlote)){
    $_1_u_loterotulo_idlote=$idlote;
}
?>
<style>
    .row{margin: 1rem 0 !important;}
    p, strong{font-size: 1.2rem;}
    textarea{
        max-width: 100%;
        width: 100%;
        min-height: 150px;
    }
</style>
<div class="row ">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Configurações do Rótulo </div>
        <div  class="panel-body">	
        <table>
            <tr>
                <td>Frasco:</td>
                <td>
                    <select name="_1_<?=$_acao?>_loterotulo_idfrasco" vnulo>
                        <option value=""></option>
                        <?fillselect("select idfrasco,frasco from frasco order by frasco",$_1_u_loterotulo_idfrasco);?>
                    </select>
                    <input name="_1_<?=$_acao?>_loterotulo_idloterotulo" type="hidden" readonly value="<?=$_1_u_loterotulo_idloterotulo?>">
                    <input name="_1_<?=$_acao?>_loterotulo_idlotecons" type="hidden" readonly value="<?=$_1_u_loterotulo_idlotecons?>">
                    <input name="_1_<?=$_acao?>_loterotulo_idlote" type="hidden" readonly value="<?=$_1_u_loterotulo_idlote?>">
                    <input name="_1_<?=$_acao?>_loterotulo_idprodservformula" type="hidden" readonly value="<?=$_1_u_loterotulo_idprodservformula?>">
   
                </td>
                <td>Destinatário:</td>
                <?
                if(!empty($idnf)){
                    
                    $sqlsel="select idpessoa,nome from pessoa p where exists
                                            (select 1 from nf n
                                            where p.idpessoa = n.idpessoa
                              
                                            and n.idnf = ".$idnf.") 
                                        or exists 
                                            (select 1 from nf n
                                            where p.idpessoa = n.idpessoafat
                                           
                                            and n.idnf = ".$idnf.")";
                    
                }else{
                    $sqlsel="select p.idpessoa,p.nome from lote l,pessoa p where l.idlote=".$_1_u_loterotulo_idlote." and p.idpessoa = l.idpessoa";
                    
                }
                
                ?>
                <td class="nowrap">
                    <select id="idpessoa" name="_1_<?=$_acao?>_loterotulo_idpessoa" vnulo>
                        <option value=""></option>
                        <?fillselect($sqlsel,$_1_u_loterotulo_idpessoa);?>
                    </select>  
                    <?if(!empty($_1_u_loterotulo_idpessoa)){
?>                        
                        <a class="fa fa-bars pointer hoverazul" title="Cadastro de  Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$_1_u_loterotulo_idpessoa?>')"></a>
<?                    
                    }
?>
                </td>
            </tr>
        </table>
<?
if($_1_u_loterotulo_idfrasco and $_1_u_loterotulo_idloterotulo and $_1_u_loterotulo_idprodservformula){
    
    
    $sql="select 
                arotulo,aind,aformula,amodousar,adescricao,fdescricao,acepas,accepas,linf,lesp,lcepas,espsobpart,esppospart,ftitulo,ftexto,fdescricao,ftextocepas,fpartida
            from loterotulo l 
            where idprodservformula = ".$_1_u_loterotulo_idprodservformula." 
            and idloterotulo != ".$_1_u_loterotulo_idloterotulo." order by alteradoem desc limit 1;";
    
    $res = d::b()->query($sql) or die("erro ao buscar a ultima configuração para impressao" . mysqli_error(d::b()) . "<p>SQL: ".$sql);
    $qtdant= mysqli_num_rows($res);
    
    if($qtdant<1 or $_1_u_loterotulo_ultimor=='Y'){
    
        $sql="select 
                f.* 
                from  frasco f 
                where f.idfrasco =".$_1_u_loterotulo_idfrasco;
        $res = d::b()->query($sql) or die("erro ao buscar frasco" . mysqli_error(d::b()) . "<p>SQL: ".$sql);
    }
    $row=mysqli_fetch_assoc($res);

    $b_arotulo=$row['arotulo'];
    $b_aind=$row['aind'];
    $b_aformula=$row['aformula'];
    $b_amodousar=$row['amodousar'];
    $b_linf=$row['linf'];
    $b_lcepas=$row['lcepas'];
    $b_fdescricao=$row['fdescricao'];
    $b_adescricao=$row['adescricao'];
    $b_lesp=$row['lesp'];
    $b_espsobpart=$row['espsobpart'];
    $b_esppospart=$row['esppospart'];
    $b_ftitulo=$row['ftitulo'];
    $b_ftexto=$row['ftexto'];
    $b_ftextocepas=$row['ftextocepas'];
    $b_fpartida=$row['fpartida'];
    $b_acepas=$row['acepas'];
    $b_accepas=$row['accepas'];

  
if($_1_u_loterotulo_fdescricao){    
    $row['fdescricao']=$_1_u_loterotulo_fdescricao; 
}
if($_1_u_loterotulo_adescricao){    
    $row['adescricao']=$_1_u_loterotulo_adescricao; 
}
    
if($_1_u_loterotulo_arotulo){    
    $row['arotulo']=$_1_u_loterotulo_arotulo; 
}
if($_1_u_loterotulo_aind){    
    $row['aind']=$_1_u_loterotulo_aind; 
}
if($_1_u_loterotulo_aformula){
    $row['aformula']=$_1_u_loterotulo_aformula; 
}
if($_1_u_loterotulo_amodousar){
    $row['amodousar']=$_1_u_loterotulo_amodousar; 
}
if($_1_u_loterotulo_acepas){
    $row['acepas']=$_1_u_loterotulo_acepas; 
}
if($_1_u_loterotulo_accepas){
    $row['accepas']=$_1_u_loterotulo_accepas; 
}

if($_1_u_loterotulo_linf){
    $row['linf']=$_1_u_loterotulo_linf; 
}
if($_1_u_loterotulo_lcepas){    
    $row['lcepas']=$_1_u_loterotulo_lcepas; 
}
if($_1_u_loterotulo_lesp){   
    $row['lesp']=$_1_u_loterotulo_lesp; 
}
if($_1_u_loterotulo_espsobpart){
    $row['espsobpart']=$_1_u_loterotulo_espsobpart; 
}
if($_1_u_loterotulo_esppospart){
    $row['esppospart']=$_1_u_loterotulo_esppospart; 
}

if($_1_u_loterotulo_ftitulo){    
    $row['ftitulo']=$_1_u_loterotulo_ftitulo; 
}
if($_1_u_loterotulo_ftexto){
    $row['ftexto']=$_1_u_loterotulo_ftexto; 
}
if($_1_u_loterotulo_ftextocepas){
    $row['ftextocepas']=$_1_u_loterotulo_ftextocepas; 
}
if($_1_u_loterotulo_fpartida){
    $row['fpartida']=$_1_u_loterotulo_fpartida; 
}

$alt_rotulo=$row['arotulo']; //altura do rotulo
$alt_ind=$row['aind'];// altura indicacoes
$alt_formula=$row['aformula'];//altura para formula
$alt_modusar=$row['amodousar'];//altura modo de usar
$alt_cepas=$row['acepas'];//altura modo de usar
$alt_acepas=$row['accepas'];

$larg_inf=$row['linf']; //largura do informaçàµes

$alt_descricao=$row['adescricao'];
$larg_cepas=$row['lcepas'];//largura do espaço para cepas
$larg_esp=$row['lesp'];//largura espaço vazio
$esp_sob_partida=$row['espsobpart'];// espaço acima da partida
$esp_pos_partida= $row['esppospart'];//padding apos a partida da borda do rotulo

$f_texto_descricao=$row['fdescricao'];
$f_titulo=$row['ftitulo'];//fonte titulo
$f_texto=$row['ftexto'];//fonte do texto
$f_texto_cepas=$row['ftextocepas'];//fonte do texto das cepas
$f_partida=$row['fpartida'];//fonte para partida
?>

                    <table>
                        <tr>
                            <td>Altura do rótulo</td>
                            <td><input class="size4" name="_1_<?=$_acao?>_loterotulo_arotulo" type="text"  value="<?=$alt_rotulo?>" ></td>
                            <td><font style="color:red;"><?=$b_arotulo?></font></td>              
                    
                            <td>Altura das Indicações</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_aind" type="text"  value="<?=$alt_ind?>" ></td> 
                            <td><font style="color:red;"><?=$b_aind?></font></td>  
                     
                            <td>Altura da formula</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_aformula" type="text"  value="<?=$alt_formula?>" ></td>              
                            <td><font style="color:red;"><?=$b_aformula?></font></td>  
                            <td>Altura do modo de usar</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_amodousar" type="text"  value="<?=$alt_modusar?>" ></td>  
                            <td><font style="color:red;"><?=$b_amodousar?></font></td>  
                        </tr>
                        <tr>
                            <td>Largura das informações</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_linf" type="text"  value="<?=$larg_inf?>" ></td>
                            <td><font style="color:red;"><?=$b_linf?></font></td> 
                       
                            <td>Largura dos espaço cepas</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_lcepas" type="text"  value="<?=$larg_cepas?>" ></td>  
                            <td><font style="color:red;"><?=$b_lcepas?></font></td>
                        
                            <td>Largura do espaço vazio</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_lesp" type="text"  value="<?=$larg_esp?>" ></td> 
                            <td><font style="color:red;"><?=$b_lesp?></font></td>
                        </tr>  
                        <tr>
                             <td>Altura acima das Cepas </td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_accepas" type="text"  value="<?=$alt_acepas?>" ></td>  
                            <td><font style="color:red;"><?=$b_accepas?></font></td>  
                            
                            <td>Altura entre Cepas e Descrição</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_acepas" type="text"  value="<?=$alt_cepas?>" ></td>  
                            <td><font style="color:red;"><?=$b_acepas?></font></td>  
                            <td>Altura da Descrição</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_adescricao" type="text"  value="<?=$alt_descricao?>" ></td>  
                            <td><font style="color:red;"><?=$b_adescricao?></font></td>  
                            
                            <td>Espaço acima da partida</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_espsobpart" type="text"  value="<?=$esp_sob_partida?>" ></td> 
                            <td><font style="color:red;"><?=$b_espsobpart?></font></td>

                       <!--
                            <td>Espaço apà³s a partida borda</td>
                       -->
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_esppospart" type="hidden"  value="<?=$esp_pos_partida?>" ></td>              
                       
                       </tr>
                        <tr>
                            <td>Fonte dos títulos</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_ftitulo" type="text"  value="<?=$f_titulo?>" ></td> 
                            <td><font style="color:red;"><?=$b_ftitulo?></font></td>
                       
                            <td>Fonte dos textos</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_ftexto" type="text"  value="<?=$f_texto?>" ></td> 
                            <td><font style="color:red;"><?=$b_ftexto?></font></td>
                        
                            <td>Fonte dos texto cepas</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_ftextocepas" type="text"  value="<?=$f_texto_cepas?>" ></td>              
                            <td><font style="color:red;"><?=$b_ftextocepas?></font></td>
                        
                            <td>Fonte dos texto partida</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_fpartida" type="text"  value="<?=$f_partida?>" ></td>    
                            <td><font style="color:red;"><?=$b_fpartida?></font></td>
                            
                             <td>Fonte dos texto descrição</td>
                            <td><input class="size4"  name="_1_<?=$_acao?>_loterotulo_fdescricao" type="text"  value="<?=$f_texto_descricao?>" ></td>    
                            <td><font style="color:red;"><?=$b_fdescricao?></font></td>
                          
                        </tr>
                        <tr>
                            <td>Utilizar padrão do frasco?</td>
                           <td align="center">
                            <?  if($_1_u_loterotulo_ultimor=='Y'){
                                    $checked='checked';
                                    $vchecked='N';	                                   
                                }else{
                                    $checked='';
                                    $vchecked='Y';
                                }				
                            ?>
                            <input title="Padrão pelo frasco" type="checkbox" <?=$nfdesabled?> <?=$checked?> name="ultimor"  onclick="altflag(<?=$_1_u_loterotulo_idloterotulo?>,'loterotulo','ultimor','<?=$vchecked?>')">
                            </td>
                        </tr>                        

                    </table>
        </div>
    </div>
    </div> 
    <div class="row ">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Texto<a title="Imprimir" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('form/rotulovac.php?acao=u&idloterotulo=<?=$_1_u_loterotulo_idloterotulo?>')"></a></div>
        <div class="panel-body">
            <table>
            <div class="row">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="col-xs-2"><strong>Indicações: </strong></div>
                        <div class="col-xs-6">
                            <p><?= nl2br($_1_u_loterotulo_indicacao) ?></p>
                        </div>
                        <input readonly name="_1_<?=$_acao?>_loterotulo_indicacao" type="hidden" value="<?=$_1_u_loterotulo_indicacao ?>">
                    </div>
                </div>
                <div class="col-xs-12 col-lg-6">
                   <div class="row">
                        <!-- Formula -->
                        <div class="col-xs-2"><strong>Formula:</strong></div>
                        <div class="col-xs-6">
                            <textarea  name="_1_<?=$_acao?>_loterotulo_formula" style="height: 128px; margin: 0px; width: 366px;" ><?=$_1_u_loterotulo_formula?></textarea>
                            <? if($arrCamposModificados['formula']) { ?>
                                <div class="alert alert-warning d-flex justify-between">
                                    <span>Este campo foi modificado manualmente, não seguindo o padrão da formulá vinculada.</span>
                                    <button class="btn btn-primary uppercase" onclick="resetarCampo(this)" data-value="<?= addslashes($rowx['formula']) ?>">Sincronizar</button>
                                </div>
                            <?}?>
                        </div>
                   </div>
                   <div class="row">
                        <!-- Modo de usar -->
                        <div class="col-xs-2"><strong>Modo de usar:</strong></div>
                        <div class="col-xs-6">
                            <p><?= nl2br($_1_u_loterotulo_modousar) ?></p>
                        </div>
                        <textarea readonly name="_1_<?=$_acao?>_loterotulo_modousar" class="hidden">
                            <?= ($_1_u_loterotulo_modousar) ?>
                        </textarea>
                   </div>
                   <div class="row">
                        <!-- Programa de utilização: -->
                        <div class="col-xs-2"><strong>Programa de utilização:</strong></div>
                        <div class="col-xs-6">
                            <?= nl2br($_1_u_loterotulo_programa) ?>
                        </div>
                        <textarea readonly name="_1_<?=$_acao?>_loterotulo_programa" class="hidden">
                            <?= ($_1_u_loterotulo_programa) ?>
                        </textarea>
                   </div>
                   <div class="row">
                    <?
                    //mcc 07/08/2019 - à pedido do Roberto, alteramos o codigo para buscar o endereço somente do idtipoendereco = 6 (propriedade) e não mais também da tabela pessoa.
                    //de p.nome,p.cpfcnpj,p.inscrest para e.nomepropriedade,e.cnpjend,e.inscest
                    if(!empty($_1_u_loterotulo_idpessoa)){
                        $sqle="	SELECT
                                        e.nomepropriedade,e.cnpjend,e.inscest,c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf
                                    FROM 
                                        pessoa p 
                                    JOIN 
                                        endereco e on e.idpessoa = p.idpessoa and e.idtipoendereco = 6 and e.status='ATIVO'
                                    LEFT JOIN 
                                        nfscidadesiaf c on(c.codcidade = e.codcidade)
                                WHERE
                                        p.idpessoa = ".$_1_u_loterotulo_idpessoa."
        "; 
                        $rese = d::b()->query($sqle) or die("erro ao buscar destinatario" . mysqli_error(d::b()) . "<p>SQL: ".$sqle);
                        $rowe=mysqli_fetch_assoc($rese);
                        
                        $str=$rowe['nomepropriedade']." ";
                        if($rowe['cnpjend']){
                            $str1=" CNPJ:".formatarCPF_CNPJ($rowe['cnpjend'],true);
                        }
                        if($rowe['inscest']){
                            $str2=" I.E.:".$rowe['inscest'];
                        }
                        $tre=" ".$rowe['logradouro'].":".$rowe['endereco']." ".$rowe['numero']." ".$rowe['complemento'];
                        $tr2=" BAIRRO:".$rowe['bairro']." CEP:".$rowe['cep']." ".$rowe['cidade']."-".$rowe['uf'];

                        if(empty($_1_u_loterotulo_endereco)){
                            $_1_u_loterotulo_endereco=$str.""
                                    . "".$str1."".$str2.""
                                    . "".$tre.""
                                    . "".$tr2;
                            
                        }?>  
                        <!-- Destinário: -->
                        <div class="col-xs-2"><strong>Destinário: </strong></div>
                        <div class="col-xs-6">
                            <textarea  name="_1_<?=$_acao?>_loterotulo_endereco" style="height: 128px; margin: 0px; width: 366px;" ><?=$_1_u_loterotulo_endereco?></textarea>
                        </div>
                   </div>
                </div>
                <div class="col-xs-12 col-lg-6">
                    <div class="row">
                        <!-- Cepas -->
                        <div class="col-xs-2"><strong>Cepas: </strong></div>
                        <div class="col-xs-8">
                            <textarea  name="_1_<?=$_acao?>_loterotulo_cepas" style=" height: 138px;" ><?=$_1_u_loterotulo_cepas?></textarea>
                            <? if($arrCamposModificados['cepas']) { ?>
                                <div class="alert alert-warning d-flex justify-between">
                                    <span>Este campo foi modificado manualmente, não seguindo o padrão da formulá vinculada.</span>
                                    <button class="btn btn-primary uppercase" onclick="resetarCampo(this)" data-value="<?= addslashes($rowx['cepas']) ?>">Sincronizar</button>
                                </div>
                            <?}?>
                        </div>
                   </div>
                    <div class="row">
                        <!-- Descrição -->
                        <div class="col-xs-2"><strong>Descrição:</strong></div>
                        <div class="col-xs-6">
                            <?= nl2br($_1_u_loterotulo_descricao) ?>
                        </div>
                        <textarea readonly name="_1_<?=$_acao?>_loterotulo_descricao" class="hidden">
                                <?=  ($_1_u_loterotulo_descricao) ?>
                        </textarea>
                   </div>
                    <div class="row">
                        <!-- Conteúdo -->
                        <div class="col-xs-2"><strong>Conteúdo:</strong></div>
                        <div class="col-xs-6">
                            <?= nl2br($_1_u_loterotulo_conteudo) ?>
                        </div>
                        <textarea readonly name="_1_<?=$_acao?>_loterotulo_conteudo" class="hidden">
                            <?= ($_1_u_loterotulo_conteudo)  ?>
                        </textarea>
                   </div>
                    <div class="row">
                        <!-- Partida -->
                        <div class="col-xs-2"><strong>Partida</strong></div>
                            <div class="col-xs-8">
                                <textarea  name="_1_<?=$_acao?>_loterotulo_partida" style="height: 138px;" ><?=$_1_u_loterotulo_partida?></textarea>
                                <? if($arrCamposModificados['partida']) { ?>
                                    <div class="alert alert-warning d-flex justify-between">
                                        <span>Este campo foi modificado manualmente, não seguindo o padrão da formulá vinculada.</span>
                                        <button class="btn btn-primary uppercase" onclick="resetarCampo(this)" data-value="<?= addslashes($rowx['partida']."\n".$rowx['fabricacao']."\n".$rowx['vencimento']) ?>">Sincronizar</button>
                                    </div>
                                <?}?>
                            </div>
                        </div>
                   </div>
                </div>
            </div>
 <?
            }
} elseif(empty($_1_u_loterotulo_idprodservformula) && !empty($_1_u_loterotulo_idpessoa)){
    ?>
    <tr>
        <td> 
            <br />
            <span style="padding: 20px; color:red;">Favor verificar se a Fórmula está ativa.</span>
        </td>
    </tr>
    <?
}
 ?>          
        </table>
        </div>
    </div>
    </div>

<div class="row ">
    <div class="col-md-12 container-fluid">
         <?$tabaud = "loterotulo";?>
        <div class="panel panel-default">		
            <div class="panel-body">
                <div class="row col-md-12">		
                    <div class="col-md-2">Criado Por:</div>     
                    <div class="col-md-4"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
                    <div class="col-md-2">Criado Em:</div>     
                    <div class="col-md-4"><?=${"_1_u_".$tabaud."_criadoem"}?></div>   
                </div>
                <div class="row col-md-12">            
                    <div class="col-md-2">Alterado Por:</div>     
                    <div class="col-md-4"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
                    <div class="col-md-2">Alterado Em:</div>     
                    <div class="col-md-4"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>       
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function altflag(inid,intab,incol,inval){
    CB.post({
                objetos: "_x_u_"+intab+"_id"+intab+"="+inid+"&_x_u_"+intab+"_"+incol+"="+inval
            });
    }

    <?if($_POST['_1_i_loterotulo_idfrasco']){
        echo "CB.post({objetos:{},msgSalvo:false,refresh:false})";
    }?>

    function resetarCampo(input) {
        $(input).parent().parent().find('textarea').val($(input).data('value'));

        CB.post()
    }
</script>