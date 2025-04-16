<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "bioensaio";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
    "idbioensaio" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
 // GVT - 28/01/2020 - comentado a parte de idempresa para não parar as atividades no bioensaio
 // voltar idempresa posteriormente.
$pagsql = "select * from bioensaio where idbioensaio = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if(!empty($_1_u_bioensaio_idpessoa)){
    function getNucleoBioterio(){
        global $_1_u_bioensaio_idpessoa;
        $sql= "SELECT idnucleo,		
                LEFT((case situacao when 'INATIVO' then concat('Abatidas => ',nucleo) else nucleo end),100) as nucleo,lote
                        from nucleo where idpessoa = ".$_1_u_bioensaio_idpessoa."
                              order by situacao asc, nucleo asc ";

        $res = d::b()->query($sql) or die("getNucleoBioterio: Erro: ".mysqli_error(d::b())."\n".$sql);

        $arrret=array();
        while($r = mysqli_fetch_assoc($res)){
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["idnucleo"]]["nucleo"]=(($r["nucleo"]));
        }
        return $arrret;
    }
    //Recupera os produtos a serem selecionados para uma nova Formalização
    $arrNucleo=getNucleoBioterio();
    //print_r($arrCli); die;
    $jNucleo=$JSON->encode($arrNucleo);
}

function getClientesEst(){

    $sql= "SELECT c.idpessoa, c.nome FROM pessoa c 
            where status = 'ATIVO' and idtipopessoa = 2 ".getidempresa('idempresa','pessoa')." ORDER BY 2";

    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
    }
    return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli=getClientesEst();
$jCli=$JSON->encode($arrCli);


function listateste($inidloteativ){  

    $sqlt = "select a.idamostra
                ,a.idregistro
                ,p.codprodserv as sigla
                ,p.descr as tipoteste
                ,r.quantidade
                ,r.status
                ,r.idamostra
                ,r.idresultado
                ,la.idloteativ
                ,r.ord
                ,r.idtipoteste
                ,la.dia
                ,if(la.dia is null,p.descr,concat(p.descr,' D',la.dia)) as rotulo,
                left(dma(la.execucao),5) as dataserv
            from amostra a ,resultado r,prodserv p ,loteativ la      
            where p.idprodserv  = r.idtipoteste 
            and r.status !='OFFLINE'
            and r.idamostra=a.idamostra
            and a.idobjetosolipor = la.idloteativ and a.tipoobjetosolipor='loteativ' and la.idloteativ =".$inidloteativ;

    //$i=9999;
    $rest = d::b()->query($sqlt)or die("Erro ao recuperar resultados: \n".mysqli_error(d::b())."\n".$sqlt);
    $qtdres=mysqli_num_rows($rest);	
    if($qtdres>0){

        while($r=mysqli_fetch_assoc($rest)){
            if($r['status']=='FECHADO'){
                $cor="#B0E2FF";
            }elseif($r['status']=='ASSINADO'){
                $cor="#00ff004d";                
            }else{
                $cor="#c4c5b47a";     
            }
            
            $classDrag = ($r["status"]=="ABERTO"  or $r["status"]=="AGUARDANDO")?"dragExcluir":"";
            $disableteste = ($r["status"]=="ABERTO" or $r["status"]=="AGUARDANDO")?"":"readonly='readonly'";
?>
    <tr  style=" background-color:<?=$cor?>;" class="<?=$classDrag?>" idresultado="<?=$r["idresultado"]?>">
        <td >
            <i title="Etiqueta da Atividade" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="imprimeEtiqueta(<?=$r['idloteativ']?>,'servicoensaio')"></i>                     
        </td> 
        <td style="white-space: nowrap;">
            <input type="hidden" name="_<?=$r["idresultado"]?>_u_resultado_ord" value="<?=$r["ord"]?>">
            <input type="text" name="_<?=$r["idresultado"]?>_u_resultado_quantidade" value="<?=$r["quantidade"]?>" style="width:30px" placeholder="Quant." vnulo vnumero>
        </td>
	<td style="white-space: nowrap;">
            <input type="hidden" name="_<?=$r["idresultado"]?>_u_resultado_idresultado" value="<?=$r["idresultado"]?>">
            <input type="hidden" name="_<?=$r["idresultado"]?>_u_resultado_idtipoteste" class="idprodserv" value="<?=$r["idtipoteste"]?>">
            <a href="?_modulo=resultprod&_acao=u&idresultado=<?=$r["idresultado"]?>" target="_blank"><?=$r["sigla"]?>-<?=$r["rotulo"]?> <?=$r["dataserv"]?></a>
	</td>	
	<td>
	    <a title="Etiqueta Com número dos animais" class="fa fa-print pull-right fa-lg azulclaro pointer hoverazul" onclick="imprimeEtiquetasoro(<?=$r['idloteativ']?>)"></a>
	</td>
	<td>
	    <?
            $hidemove="";
            if($r["status"]!=="ABERTO" AND $r["status"]!=="AGUARDANDO"){
                $hidemove="hidden";
            }
?>
            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable <?=$hidemove?>" onclick="delresultado(<?=$r["idresultado"]?>)" title="Excluir Resultado"></i>
	</td>
    </tr>
<?
			//$i++;
        }//while($r=  mysqli_fetch_assoc($rest)){
    }//if($qtdres>0){
?>                
    <tr>
        <td></td>
    </tr>
<?
}//function listateste(){
?>

<style>
    .linhacab1{  
        padding:0;  
        clear:both;            
        display: inline-block;
        vertical-align: top;
        margin: 0px;
        background-color :white;
        min-height: 75px;
        width: 100%;
        font-size:10px;
        color: gray;
        align-items: center;
        text-align: center;
    } 
        
    .linhacab2{  
        padding:0;  
        clear:both;            
        display: inline-block;
        vertical-align: top;
        margin: 0px;
        background-color :white;
        width: 100%;			
        min-height: 20px;
        align-items: center;
        text-align: center;
    } 
        
    .linhacab3{  
        padding:0;  
        clear:both;            
        display: inline-block;
        vertical-align: top;
        margin: 0px;
        background-color :white;
        width: 100%;		
        min-height: 150px;           
        align-items: center;
        text-align: center;
    } 
    
    .conteudo{
      /* align-items: center;*/
       display: flex;
       flex-direction: row;
       flex-wrap: wrap;
       /* justify-content: center; */
    }
    .servico{      
        border: none;
        /* min-width:170px;
        min-height: 40px; */
         *background-color :#FF7F50;  
        float:left;  
        border-radius: 10px;    
        margin:2px;  
        display:flex;
        justify-content: center;
       /* align-items: center;*/
        vertical-align: top;
    }
    .trteste{ 
        background-color: white;  
    }
    
</style>
<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading">
            <table>
                <tr>
                    <td><strong>Reg.:</strong></td>
                    <td>
                    <?if(!empty($_1_u_bioensaio_idregistro)){?>
                        <label class="alert-warning">   B<?=$_1_u_bioensaio_idregistro?> - <?=$_1_u_bioensaio_exercicio?></label>
                    <?}?>
                        <input  name="_1_<?=$_acao?>_bioensaio_idregistro" type="hidden" value="<?=$_1_u_bioensaio_idregistro?>" readonly='readonly'>
                        <input  name="_1_<?=$_acao?>_bioensaio_exercicio" type="hidden" value="<?=$_1_u_bioensaio_exercicio?>" readonly='readonly'>
			<input id="idbioensaio" name="_1_<?=$_acao?>_bioensaio_idbioensaio" type="hidden"	value="<?=$_1_u_bioensaio_idbioensaio?>" readonly='readonly'>
		    </td>
                    <td align="right">Cliente:</td>
                    <td colspan="5">
                        <input id="idpessoa"  type="text" name="_1_<?=$_acao?>_bioensaio_idpessoa" vnulo cbvalue="<?=$_1_u_bioensaio_idpessoa?>" value="<?=$arrCli[$_1_u_bioensaio_idpessoa]["nome"]?>" style="width: 17em;" vnulo>
                        <input  name="bioensaio_idpessoa" type="hidden" value="<?=$_1_u_bioensaio_idpessoa?>" readonly='readonly'>
                    </td>
                    <?if(!empty($_1_u_bioensaio_idpessoa)){?>
                    <td align="right" norwrap>Estudo:</td>
                    <td nowrap="nowrap">
                        <input type="text" name="_1_<?=$_acao?>_bioensaio_idnucleo" cbvalue="<?=$_1_u_bioensaio_idnucleo?>" value="<?=$arrNucleo[$_1_u_bioensaio_idnucleo]["nucleo"]?>" style="width: 25em;" vnulo>
                    </td>
                    <?}?>
                    <td>
 <?
                    if(!empty($_1_u_bioensaio_idnucleo)){
 ?>
                        <a class="fa fa-bars pointer hoverazul" title="Núcleo" onclick="janelamodal('?_modulo=nucleocq&_acao=u&idnucleo=<?=$_1_u_bioensaio_idnucleo?>')"></a>   
 <?
                    }
 ?>
                    </td>
                    <td  align="right">Qtd.:</td>
                    <td ><input name="_1_<?=$_acao?>_bioensaio_qtd" size="10" type="text" value="<?=$_1_u_bioensaio_qtd?>" vnulo></td>    
					<? if (empty($_1_u_bioensaio_idunidade)){
						$_1_u_bioensaio_idunidade = 4;
					} ?>
<td>
                                            <select name="_1_<?=$_acao?>_bioensaio_idunidade" vnulo>
                                                <option value=""></option>
                                                <?fillselect("select idunidade,unidade 
                                                            from unidade 
                                                            where status='ATIVO' order by unidade",$_1_u_bioensaio_idunidade);?>		
                                            </select>
                                        </td>						
                    <td align="right" >Status:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_bioensaio_status" vnulo>
                                <option></option>
                                <?fillselect(" select 'DISPONIVEL','Disponível' union select 'RESERVADO','Reservado' union select 'ATIVO','Ativo'  union select 'FINALIZADO','Finalizado' union select 'CANCELADO','Cancelado'",$_1_u_bioensaio_status);?>		
                        </select>
                    </td> 
                </tr>
            </table>
        </div>
    </div>
</div>
        
<?
if(!empty($_1_u_bioensaio_idnucleo)){
?>

            <div class="col-md-5">
                <div class="panel panel-default" >
                    <div class="panel-heading">Descrição dos Animais</div>
                <div class="panel-body"> 
                <table>
                    <tr>
                         <td align="right" >Espécie/Finalidade:</td> 
                         <td >
                            <select name="_1_<?=$_acao?>_bioensaio_idespeciefinalidade" vnulo>
                                    <option></option>
                                    <?fillselect("select idespeciefinalidade,concat(p.plantel,'-',e.finalidade) as especiefinalidade 
					from especiefinalidade e join unidadeobjeto u join plantel p
					where u.idunidade = 4
                                        and p.idplantel = e.idplantel
                                        and u.tipoobjeto = 'especiefinalidade'
					and u.idobjeto = e.idespeciefinalidade
                                        and e.status ='A'
					order by especiefinalidade ",$_1_u_bioensaio_idespeciefinalidade);?>		
                            </select>
                        </td>                                          
                        <td align="right">Cor da Anilha:</td> 
                        <td colspan="3"><input <?=$readonly2?>	name="_1_<?=$_acao?>_bioensaio_coranilha" class='size12' type="text" value="<?=$_1_u_bioensaio_coranilha?>" ></td>
                    </tr>
                    <tr>    
                        <td  align="right" nowrap>Nascimento:</td>
                        <td><input name="_1_<?=$_acao?>_bioensaio_nascimento" class="calendario"  class='size10' type="text" value="<?=$_1_u_bioensaio_nascimento?>" vnulo></td>                       
                        <td  align="right" nowrap>Alojamento:</td>
                        <td><input name="_1_<?=$_acao?>_bioensaio_alojamento" class="calendario"  class='size10' type="text" value="<?=$_1_u_bioensaio_alojamento?>" vnulo></td>                  
                    </tr>                                

                    <tr>
<?
		if($_1_u_bioensaio_idbioensaio){
			$sqld="select d.titulo,a.idsgdoc,a.idbioensaiosgdoc,a.versao,a.revisao
					 from sgdoc d,bioensaiosgdoc a 
					where d.idsgdoc = a.idsgdoc
					and a.idbioensaio= ".$_1_u_bioensaio_idbioensaio." order by d.titulo";
			$resd=d::b()->query($sqld) or die("Erro ao buscar documento vinculado sql:".$sqld);
			$qtdd=mysqli_num_rows($resd);		
?>

			<?if($qtdd <1){?>
			
                        <td align="right" style="vertical-align: top;">Certificado:</td>
                        <td  style="vertical-align: top;" colspan="3">					
                            <select  name="bioensaiosgdoc_idsgdoc" class="" colspan="3" onchange="bioensaiosgdoc(this);">
                            <option value=""></option>
                            <?fillselect("select idsgdoc,titulo from sgdoc
                                            where idsgtipodoc = 56  
                                            and idsgdocstatus = 'APROVADO' 
                                            ".getidempresa('idempresa','documento')." ORDER BY titulo");?>		
                            </select>
                        </td>
			
			<?
			}else{
                                $d=77;
				while($rowd=mysqli_fetch_assoc($resd)){
                                    $d=$d+1;
?>		
				<td align="right" style="vertical-align: top;"> Certificado:</td>
				 <td  style="vertical-align: top;" colspan="3" class="size30"  >
				    <a class="pointer" onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$rowd['idsgdoc']?>')" >
					<?=$rowd['titulo']?>-<?=$rowd['versao']?>.<?=$rowd['revisao']?>
				    </a>				    
				    <i class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer" idcontapagaritem="<?=$rowp['idcontapagaritem']?>" onclick="dbioensaiosgdoc(<?=$rowd["idbioensaiosgdoc"]?>)" title="Retirar"></i>
				</td>	
			
<?			
				}//while($rowd=mysqli_fetch_assoc($resd)){				
			}//if($qtdd <1){
		}//if($_1_u_bioensaio_idbioensaio){
?>           
                    </tr>
                    <tr>
                        <td align="right" style="vertical-align: top;">Obs:</td>
                        <td colspan="3">
                            <textarea name="_1_<?=$_acao?>_bioensaio_obs"  style="width: 100%; height: 114px;" ><?=$_1_u_bioensaio_obs?></textarea>                        
                        </td>                       
                    </tr>                  
                </table>
                </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="panel panel-default" >
                <div class="panel-heading">Descrição do Produto</div>
            <div class="panel-body"> 
                <table>
                    <tr >
                        <td align="right">Produto:</td> 
                        <td  colspan="3"><input name="_1_<?=$_acao?>_bioensaio_produto" class="size20" type="text"	Value="<?=$_1_u_bioensaio_produto?>" ></td> 
                    </tr>
                    <tr>   
                        <td align="right">Partida:</td> 
                        <td><input name="_1_<?=$_acao?>_bioensaio_partida" class='size10' type="text"	Value="<?=$_1_u_bioensaio_partida?>" ></td> 
                        <td align="right">Vol. Aplic.:</td> 
                        <td><input name="_1_<?=$_acao?>_bioensaio_volume" class='size6' type="text"	Value="<?=$_1_u_bioensaio_volume?>" ></td>                      
                    </tr>
                    <tr>
                        <td align="right">Via:</td> 
                        <td>
                            <select   name="_1_<?=$_acao?>_bioensaio_via">
                                <option value=""></option>
                                <?fillselect("select 'INTRA-MUSCULAR-COXA','Int.-Musc. Coxa' 
                                                union select 'INTRA-MUSCULAR-PEITO','Int. Musc. Peito' 
                                                union select 'SUBCUTANEA','Subcutânea' 
                                                union select 'INTRA-PERITONEAL','Int. Peritoneal'
                                                union select 'INTRA-MUSCULAR','Int. Muscular'
                                                union select 'INTRA-OCULAR','Int. Ocular'
                                                union select 'INTRA-OVO','Int. Ovo'
                                                union select 'INTRA-VENOSA','Int. Venosa'
                                                union select 'ORAL','Oral'
                                                union select 'NASAL','Nasal'
                                        ",$_1_u_bioensaio_via);?>		
                            </select>
                        </td>
                        <td align="right" class='nowrap'>Nº Doses:</td> 
                        <td><input name="_1_<?=$_acao?>_bioensaio_doses" class='size6' type="text" Value="<?=$_1_u_bioensaio_doses?>" ></td>
                    </tr>
                    <tr>
			<td  align="right" nowrap>Enviado por:</td>
			<td><input <?//=$readonly2?>	name="_1_<?=$_acao?>_bioensaio_respenvio" class="" type="text" value="<?=$_1_u_bioensaio_respenvio?>"></td>
			<td  align="right" nowrap> Envio:</td>
			<td>
			    <input class="calendario" name="_1_<?=$_acao?>_bioensaio_dataenvio" id ="fdata2" type="text" size ="6" value="<?=$_1_u_bioensaio_dataenvio?>">		
			</td>			
                    </tr>
                    <tr>
                        <td  align="right" nowrap>Responsável:</td>
			<td><input <?//=$readonly2?>	name="_1_<?=$_acao?>_bioensaio_recebidopor" size ="15" type="text" value="<?=$_1_u_bioensaio_recebidopor?>"></td>	
                        <td align="right" style="vertical-align: top;">Recebimento:</td> 
			<td style="vertical-align: top;">
			    <input class="calendario"  name="_1_<?=$_acao?>_bioensaio_datareceb" id ="fdata3" type="text" size ="6" value="<?=$_1_u_bioensaio_datareceb?>">		
			</td>
                    </tr>
                    <tr>
                        <td align="right" style="vertical-align: top;">Antigeno:</td>
                        <td colspan="3">
                            <textarea name="_1_<?=$_acao?>_bioensaio_antigeno"  style="width: 100%; height: 51px;" ><?=$_1_u_bioensaio_antigeno?></textarea>
                        </td>
                    </tr>                 
                </table>
            </div>
            </div>
            </div>
            <div class="col-md-2">
<?
        if(!empty($_1_u_bioensaio_idpessoa) and !empty($_1_u_bioensaio_idbioensaio)){
?>
            <div class="panel panel-default" >
                <div class="panel-heading">Inter-relações</div>
            <div class="panel-body">	
            <table>
                <?
                if(!empty($_1_u_bioensaio_idficharep)){
                    $sqlf="select f.idficharep,concat(f.idficharep,'-',l.partida,'/',l.exercicio) as ficharep
                            from ficharep f,lote l 
                            where f.idficharep = ".$_1_u_bioensaio_idficharep."
                            and f.idlote = l.idlote";
                    $resf=d::b()->query($sqlf) or die("Erro ao informaçàµes da ficha de reproducao sql:".$sqlf);
                    $rowf=mysqli_fetch_assoc($resf);
                ?>
                <tr>
                    <td>Ficha Rep.:</td>
                    <td>
                        <a title="Ver Ficha de Reproducao" href="javascript:janelamodal('?_modulo=ficharep&_acao=u&idficharep=<?=$rowf["idficharep"]?>')">
                        <?=$rowf["ficharep"]?>
                        </a>                   
                    </td>
                </tr>
                <?
                }//if(!empty($_1_u_bioensaio_idficharep)){
                ?>
                <tr>
                    <td align="right">Formalização:</td>
                    <?
                        $sqlativ="select la.idloteativ, concat(l.partida,'/',l.exercicio) as partida
                                from loteativ la,loteobj o,lote l
                                where l.idlote = la.idlote
                                and o.idloteativ = la.idloteativ 
                                and not exists (select 1 from bioensaio b where b.idloteativ = la.idloteativ and b.idbioensaio!=".$_1_u_bioensaio_idbioensaio.")
                                and o.idobjeto = 5 order by partida";
                    ?>
                    <td>
                        <select <?=$disabled2?>  name="_1_<?=$_acao?>_bioensaio_idloteativ" >
                            <option value="0" selected></option>
                            <?
                            fillselect($sqlativ,$_1_u_bioensaio_idloteativ);
                            ?>
                        </select>		
                    </td>
                </tr>
                <tr>
                    <td align="right">Documento:</td>
                    <td>
                        <select id="idsgdoc" <?=$disabled2?> name="_1_<?=$_acao?>_bioensaio_idsgdoc">
                            <option value="0" selected></option>
                            <?
                            fillselect("select idsgdoc,concat(idsgdoc,'-',titulo) 
                                    from sgdoc d where  ".getidempresa('idempresa','documento')."
                                     and d.idsgtipodoc = 58 order by idsgdoc",$_1_u_bioensaio_idsgdoc);
                            ?>
                        </select>	
                        <?if($_1_u_bioensaio_idsgdoc){?>
                        <a class="fa fa-bars pointer hoverazul" title="Documento" onclick="janelamodal('report/sgdocprint.php?acao=u&idsgdoc=<?=$_1_u_bioensaio_idsgdoc?>')"></a>
                        <?}?>
                    </td>
                </tr>
            </table>
            </div>
            </div>
<?

		if($_1_u_bioensaio_agrupar=='Y'){
                    $sqlin="select
                                0 as idbioensaiodes,b.idbioensaio,b.qtd,concat('B',b.idregistro,'/',b.exercicio) as registro,b.idbioensaio as idbioensaioc
                            from bioensaio b 
                            where b.idbioensaio =  ".$_1_u_bioensaio_idbioensaio." union all ";
		}else{
                    $sqlin="select
                                0 as idbioensaiodes,b.idbioensaio,b.qtd,concat('B',b.idregistro,'/',b.exercicio) as registro,b.idbioensaio as idbioensaioc
                            from bioensaio b ,bioensaiodes d
                            where b.idbioensaio =  d.idbioensaio
                            and d.idbioensaioc =  ".$_1_u_bioensaio_idbioensaio." union all ";
		}
		
		$sqldes=$sqlin."select 
					d.idbioensaiodes,b.idbioensaio,b.qtd,concat('B',b.idregistro,'/',b.exercicio) as registro,d.idbioensaioc
				from bioensaiodes d,bioensaio b 
				where b.idbioensaio = d.idbioensaioc
				and d.idbioensaio = ".$_1_u_bioensaio_idbioensaio."
				union all
				select
					d.idbioensaiodes,b.idbioensaio,b.qtd,concat('B',b.idregistro,'/',b.exercicio) as registro,d.idbioensaioc
				from bioensaiodes d,bioensaio b
				where b.idbioensaio = d.idbioensaioc
				and exists  
				(select 1 from bioensaiodes dd 
				where d.idbioensaio = dd.idbioensaio 
				and dd.idbioensaioc = ".$_1_u_bioensaio_idbioensaio.")";
		$resdes=d::b()->query($sqldes) or die("Erro ao buscar desenho experimental sql=".$sqldes);
                $nrowdes=mysqli_num_rows($resdes);
?>                
            <div class="panel panel-default">
                <div class="panel-heading">
                    <table>
                         <tr>
                            <Td>Desenho Experimental</Td>
                    <?if($nrowdes < 2){?>                   

                        <td align="left" nowrap>
                                <?if($_1_u_bioensaio_agrupar=='N'){
                                    $checked='';
                                    $agrupar='Y';					
                                }else{
                                    $checked='checked';
                                    $agrupar='N';
                                }				
                                ?>
                            <input title="Agrupar" type="checkbox" <?=$checked?> name="nameagrupar" onclick="flgagrupar(<?=$_1_u_bioensaio_idbioensaio?>,'<?=$agrupar?>');">
                         </td>                    
                    <?}?>
                         </tr>
                    </table>
                </div>
            <div class="panel-body">
<?
		if($nrowdes>0){
?>
                <table class="table table-striped planilha">
                    <tr >
                        <th>Reg.</th>
                        <th>Nº Animais</th>
                    </tr>
<?
			while($rowdes=mysqli_fetch_assoc($resdes)){
				if(empty($rowdes['idbioensaiodes'])){
				 $colorir="style='background-color :orange;'";
				}else{
					$colorir="";
				}
?>		
                    <tr  <?=$colorir?>>
                        <td >
                        <a title="Editar Produto" href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&idbioensaio=<?=$rowdes['idbioensaioc']?>')">
                        <?=$rowdes['registro']?>
                        </a>
                        </td>
                        <td ><?=$rowdes['qtd']?></td>
                        <td align="center">
                        <?if(!empty($rowdes['idbioensaiodes'])){?>
                             <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="iubioensaiodes('d',<?=$rowdes['idbioensaiodes']?>)" alt="Desvincular Experimento"></i>
                       <?}?>
                        </td>
                    </tr>
<?
			}//while($rowdes=mysqli_fetch_assoc($resdes)){
?>
                </table>
<?
		}//if($nrowdes>0){
?>	
                <table>		
                    <tr>	
                        <?if($_1_u_bioensaio_agrupar=='Y'){?>
                        <td nowrap>Vincular Registro:</td>
                        <td>				
                        <select name="" onchange="iubioensaiodes('i',this);">
                        <option value="0" selected></option>
                        <?
                        fillselect("select b.idbioensaio,concat('B',b.idregistro,'/',b.exercicio) as registro
                                                from bioensaio b
                                                where  b.idpessoa = ".$_1_u_bioensaio_idpessoa."
                                                and b.agrupar ='N'
                                                ".getidempresa('b.idempresa','bioensaio')."
                                                and not exists(select 1 from bioensaiodes d where d.idbioensaioc = b.idbioensaio)
                                                and not exists(select 1 from bioensaiodes dd where dd.idbioensaio = b.idbioensaio) order by b.idregistro");
                        ?>
                        </select>
                        </td>
                        <?}?>
                    </tr>	
                </table>     
            </div>
            </div>
<?
        }//if(!empty($_1_u_bioensaio_idpessoa)){
?> 
           </div>      
        
<?
}//if($_1_u_bioensaio_idbioensaio){


if(!empty($_1_u_bioensaio_idbioensaio)){
    
    

 if(!empty($_1_u_bioensaio_idnucleo)){
?>
            <?
                $sql="select p.descr,r.proc,l.* from lote l 
                    join prodserv p on(p.idprodserv=l.idprodserv)
                    join prproc r on(r.idprproc=l.idprproc )
                    where l.idobjetoprodpara = ".$_1_u_bioensaio_idbioensaio." 
                    and l.tipoobjetoprodpara ='bioensaio' and l.status !='CANCELADO'";
                //die($sql);
                $res= d::b()->query($sql) or die("Erro ao buscar analises do bioensaio sql=".$sql);
                $i=1;
                while($row=mysqli_fetch_assoc($res)){
                    $i=$i+1;

?>
<div class="col-md-12" >
    <div class="panel panel-default" > 
        <div class="panel-heading">
            Protocolo:<label class="alert-warning"><?=$row['proc']?></label>&nbsp;&nbsp;&nbsp; <a class="fa fa-bars pointer hoverazul" title="Protocolo" onclick="janelamodal('?_modulo=formalizacao&_acao=u&idlote=<?=$row['idlote']?>')"></a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <i class="fa fa-plus-circle  pull-right fa-lg cinza hoververde pointer" onclick="iservico(<?=$row["idlote"]?>)" title="Inserir nova Atividade"> &nbsp;&nbsp;Atividade</i>
                     
        </div> 
        <div class="panel-body"> 
                     <?$sq="select l.* from loteativ l where l.idlote=".$row['idlote']."
                            order by l.dia"; 
                     //die($sq);
                    echo "<!-- " . $sq . "  -->";
                    $ress=d::b()->query($sq) or die("Erro ao buscar atividades sql=".$sq);
                      ?>
                    
        <div class="conteudo"> 
            <?
            while($rows=mysqli_fetch_assoc($ress)){
               
                if($rows['status']=="CONCLUIDO"){
                        $cor="#00ff004d";
                }elseif($rows['status']=="INATIVO"){				
                        $cor="#DCDCDC";
                }else{
                        $cor="#ff634747";
                }
       
                $datetime1 = date_create(validadate($_1_u_bioensaio_nascimento));
                $datetime2 = date_create(validadate(dma($rows['execucao'])));
                $interval = date_diff($datetime1, $datetime2);
               
                

            ?>
           <div style="background-color:<?=$cor?>; "  class="servico" title="<?=$rows['obs']?>" style="text-align: left;" >
           <table>
           <tr>			
               <td align="center" colspan="5"  class="nowrap">               
                <a class="pointer" tanalise="<?=$rows['idanalise']?>" tservico="<?=$rows['idservicobioterio']?>" dmadata="<?=$rows['dmadata']?>" dia="<?=$rows['dia']?>" difdias="<?=$rows['difdias']?> dias" status="<?=$rows['status']?>" observ="<?=$rows['obs']?>" onClick="formalizacaoloteativ(<?=$rows['idlote']?>,<?=$rows['idloteativ']?>);" >
                    <?echo($rows['ativ']);?>
                </a>               

                     <a title="Bioensaio Ctr" onclick="janelamodal('?_modulo=bioensaio&_acao=u&idbioensaio=<?=$rows["idbioensaio"]?>')"><?=$rows["registro"]?></a>
                 <?
                                       

                     echo(" (D ".$rows["dia"].")");
               
                 ?>             
                </td>
           </tr>
           <tr>
               <td align="center" nowrap colspan="4">
              
               <?if(empty($rows['execucao'])){?>
                    <font color="red">Início à definir
                    </font>
               <?}else{?>
                    <font color="black"> 
                   <?echo(dma($rows['execucao']));?> <?echo $interval->format('%R%a');?> dias
                   </font>
               <?}?>
               
               </td>	
               <td align="center">
                   <?if(empty($rows["idamostra"]) or $rows['status']=="INATIVO"){?>
                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="altservico(<?=$rows['idloteativ']?>)" title="Excluir atividade"></i>
                   <?}?>
               </td>
            </tr>
            <tr>
               <td colspan="5"><hr style="border-color: #83887c; "></td>
            </tr>
            <?listateste($rows['idloteativ']);?>  
            <tr>
                <td style="padding-bottom: 10px">       
                    <i id="novoteste" class="fa fa-plus-circle pull-right fa-lg cinzaclaro hoververde pointer" onclick="atnvteste(<?=$rows['idloteativ']?>)"  title="Inserir novo teste"></i>
                </td>
                <td class="nowrap">
                    <input name="qtd" id="qtdteste<?=$rows['idloteativ']?>" type="text" placeholder="qtd" value='' class='size2 hidden'> 
                </td>
                <td>
                    <select id="idtipoteste<?=$rows['idloteativ']?>" class="idprodserv size15 hidden"  style="font-size: 9px;" name="#nameidtipoteste" onchange="novoTeste(<?=$rows["idloteativ"]?>)" >
                    <option value="0" selected></option>
                    <?
                    fillselect("select idprodserv as idtipoteste,codprodserv 
                                from prodserv t join unidadeobjeto p 
                                where t.status = 'ATIVO' 
                                and t.tipo='SERVICO' 
                                and p.idunidade in (2,4)
                                and p.tipoobjeto= 'prodserv'
                                and p.idobjeto=t.idprodserv
                                ".getidempresa('t.idempresa','prodserv')."  order by codprodserv");
                     
               
                    ?>
                    </select>
                    <input id="idservico<?=$rows['idloteativ']?>" value="<?=$rows["idloteativ"]?>" type="hidden" style="font-size: 9px;" name="#nameidservicoensaio" >
                </td>
            </tr>
            </table>
           </div>                   
<?         

            }//while($rows=mysqli_fetch_assoc($ress)){
?>
        </div> 
<? 
        $sqlind="select * from  loteind where idlote=".$row['idlote']." order by identificacao";
	$resind=d::b()->query($sqlind) or die("Erro ao buscar identificação sql=".$sqlind);
	$qtdind= mysqli_num_rows($resind);

	$i=555;	
 ?>
            <div class="col-md-12" >
	    <div class="panel panel-default">
		
		<div class="panel-body ">
                    <div>Identificadores</div>
                    <hr>
		<table>	
		<tr>
	<?	
                $lin=0;
		while($rowind=mysqli_fetch_assoc($resind)){
                    $i=$i+1;                    
                    if($lin==10){
                        echo('</tr><tr>');
                        $lin=0;
                    }
                    $lin=$lin+1;
	?>
		
		    <td>
			<input <?=$readonly2?>	name="_<?=$i?>_u_loteind_idloteind" size ="10" type="hidden" value="<?=$rowind['idloteind']?>" >
			<input <?=$readonly2?>	name="_<?=$i?>_u_loteind_identificacao"  size ="5" type="text" value="<?=$rowind['identificacao']?>" >
		    </td>
		    <td>
			<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="dbioterioind(<?=$rowind['idloteind']?>)" title="Excluir Identificacao"></i>
		    </td>		
	<?	
		}
                if($lin>0){
                    echo('</tr>');
                }
	?>
		     <td>		  
			<i class="fa fa-plus-circle fa-1x  verde pointer" onclick="ibioterioind(<?=$row['idlote']?>)" title="Inserir Identificador"></i>
		    </td>		
		</tr>
		</table>		  
		</div>
	    </div>
	    </div>
        </div>
  
    </div>
</div>
     <?
                }//while($row=mysqli_fetch_assoc($res)){
?>    

    <div class="col-md-12" >
        <div class="panel panel-default" >      
            <div class="panel-body">             
            <div class="agrupamento novo">
               Novo Protocolo:  <select <?=$desabledan?> <?=$desabledct?> class="size30" id="idbioterioanalise<?=$i?>"  name="analise_idbioterioanalise" onchange="gerarFormalizacao(this,<?=$i?>);" >
                                <option value=""></option>
                                <?fillselect("select p.idprodserv,c.proc 
                                                    from prodserv p 
                                                            join unidadeobjeto o on(o.idobjeto=p.idprodserv and o.tipoobjeto='prodserv')
                                                            join unidade u on(u.idunidade=o.idunidade and u.idtipounidade=12) 
                                            Join prodservprproc pc on(p.idprodserv=pc.idprodserv)
                                            join prproc c on(c.idprproc=pc.idprproc and c.tipo='SERVICO')
                                                where p.tipo='SERVICO' 
                                                ".getidempresa('p.idempresa','prodserv')."
                                                and p.status='ATIVO' 
                                                and exists (select 1 from prodservformula f where f.status='ATIVO' and f.idprodserv = p.idprodserv) order by p.descr");?>		
                            </select>  
            </div>
            </div>
        </div>
    </div>
<?
   
 }//if(!empty($_1_u_bioensaio_idnucleo))
?>
<div id="servico" style="display: none">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
            <div class="panel-heading">			                           
            <table>
                <tr> 
                    <td align="right">
                        <input  id="idloteativ" name="" type="hidden" value="" >
                    </td>
                    <td align="right">Dia:</td>
                    <td nowrap>
                        <input  name="" id ="dia"   type="text" size ="6" value="" >
                        <input  name="" id ="idlote"   type="hidden" size ="6" value="" >                  
                    </td>
                    <td align="right"></td>
                    <td nowrap>
                        <select name=""  id="ndropprativ" value="">
                              <?fillselect("select idprativ,ativ from prativ where status='APROVADO' ".getidempresa('t.idempresa','prodserv')." order by ativ"); ?>
                        </select>                       
                    </td>                   
                </tr>
                
            
            </table>
            </div>	
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">Arquivos Anexos</div>
      <div class="panel-body">
           <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
                   <i class="fa fa-cloud-upload fonte18"></i>
           </div>
       </div> 
     </div>
</div>    
<?
if($_1_u_bioensaio_idbioensaio){
?>
    <div class="col-md-12">
        <div class="panel panel-default">		
            <div class="panel-body">   
               
               <table class="audit">
		<tr>                        
                    <td rowspan="3">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <i title="Registro Operacional" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relbioensaio.php?acao=i&idbioensaio=<?=$_1_u_bioensaio_idbioensaio?>')">  &nbsp;&nbsp;Operacional</i>                     
                    </td>
                    <td rowspan="3">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <i title="Relatà³rio do Biensaio" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relbioensaioc.php?acao=i&incresult=Y&idbioensaio=<?=$_1_u_bioensaio_idbioensaio?>')">  &nbsp;&nbsp;Bioensaio</i>                        
                    </td>
                    <td rowspan="3">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <i title="Impressão dos resultados" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/emissaoresultado.php?exerciciobiot=<?=$_1_u_bioensaio_exercicio?>&idregistrob=<?=$_1_u_bioensaio_idregistro?>')">  &nbsp;&nbsp;Resultados</i>                             
                    </td>                    
		</tr>
			
		</table>
            </div>
        </div>
    </div>   
   <?
if(!empty($_1_u_bioensaio_idbioensaio)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_bioensaio_idbioensaio; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "bioensaio"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<?
}
?>

<?
}//if(!empty($_1_u_bioensaio_idbioensaio)){
?>
 
<script>
<?
if(!empty($_1_u_bioensaio_idpessoa)){
?>
jNucleo=<?=$jNucleo?>;
jNucleo = jQuery.map(jNucleo, function(o, id) {
    return {"label": o.nucleo, value:id+"" ,"lote":o.lote}
});

$("[name*=_bioensaio_idnucleo]").autocomplete({
    source: jNucleo
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }
    ,noMatch: function(objAc){
	
        console.log("Executei callback");
        CB.post({
            objetos: "_x_i_nucleo_idunidade=2&_x_i_nucleo_situacao=ATIVO&_x_i_nucleo_nucleo="+objAc.term+"&_x_i_nucleo_idpessoa="+$("[name=_1_u_bioensaio_idpessoa]").attr("cbvalue")
            ,refresh: false
            ,msgSalvo: "Nucleo criado"
            ,posPost: function(data, textStatus, jqXHR){
                //Atualiza source json
                $("[name*=_bioensaio_idnucleo]").data('uiAutocomplete').options.source.push({
                        label: $("[name*=_bioensaio_idnucleo]").val()
                        ,value: CB.lastInsertId
                        ,idnucleo: CB.lastInsertId
                });
                //Atualiza o objeto DATA associado ao input
               // $("[name*=_atendimento_nome]").data("nucleos")[CB.lastInsertId]={"nucleo":$oIdcontato.val()};
                //Mostra a nova opção
                $("[name*=_bioensaio_idnucleo]").autocomplete( "search", $("[name*=_bioensaio_idnucleo]").val());
            }
        });
    }	
});

<?
}//if(!empty($_1_u_bioensaio_idpessoa)){
?>
jCli=<?=$jCli?>;// autocomplete cliente


//mapear autocomplete de clientes
jCli = jQuery.map(jCli, function(o, id) {
    return {"label": o.nome, value:id}
});
//autocomplete de clientes
$("[name*=_bioensaio_idpessoa]").autocomplete({
    source: jCli
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});

function novaanalise(){
    CB.post({
        objetos: "_x_i_analise_idobjeto="+$("[name=_1_u_bioensaio_idbioensaio]").val()+"&_x_i_analise_objeto=bioensaio"      
    });
}

function setanalise(inI,inidlote){

        CB.post({
            objetos: "_x_u_analise_idanalise="+$('#idanalise'+inI).val()+"&_x_u_analise_idlote="+inidlote
            ,parcial:true
            ,refresh:false
        });    
}


 function gerarFormalizacao(vthis,inI){

        inIdprodserv =$(vthis).val();

	strobjetos="_x_i_lote_tipoobjetoprodpara=bioensaio&_x_i_lote_idobjetoprodpara="+$("[name=_1_u_bioensaio_idbioensaio]").val()+"&_x_i_lote_idprodserv="+inIdprodserv+"&_x_i_lote_tipo=SERVICO&_x_i_lote_status=TRIAGEM&_x_i_lote_idpessoa="+$("[name=_1_u_bioensaio_idpessoa]").attr('cbvalue');

	if(confirm("Deseja gerar um NOVO PROTOCOLO ")){

		CB.post({
			objetos: strobjetos
			,parcial: true
			,posPost: function(data, textStatus, jqXHR){
				if(jqXHR.getResponseHeader("X-CB-PKID")
						&&jqXHR.getResponseHeader("X-CB-PKFLD")=="idlote"){
                                        CB.modal({
                                            url: "?_modulo=formalizacao&_acao=u&idlote="+jqXHR.getResponseHeader("X-CB-PKID")
                                            ,header:"Gerência de Produto"
                                        });
                                }else{
					alert("js: gerarFormalizacao: A resposta de inserção não retornou a coluna `idlote` ou Autoincremento.");
				}
			}
		})
	}
}  


function setanalisedt(inI,indate){       
    CB.post({
            objetos: "_x_u_analise_idanalise="+$('#idanalise'+inI).val()+"&_x_u_analise_datadzero="+indate+"&datadzeroold="+$("[name=datadzeroold"+inI+"]").val()+"&_x_u_analise_idbioterioanalise="+$('#idbioterioanalise'+inI).val()
            ,parcial:true
            
    });   
}

function formalizacaoloteativ(inidlote,inidloteativ){
       
    CB.modal({
        url: "?_modulo=formalizacao&_acao=u&idlote="+inidlote+"&idloteativ="+inidloteativ
        ,header:"Gerência de Produto"
    });
}

/*
function uservico(vthis,inidservicoensaio ){
    var strCabecalho = "</strong>SERVIÇO <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";
    $("#cbModalTitulo").html((strCabecalho));

    var  htmloriginal =$("#servico").html();
    var objfrm= $(htmloriginal);

    objfrm.find("#idservicoensaio").attr("name", "_999_u_servicoensaio_idservicoensaio");
    objfrm.find("#idservicoensaio").attr("value", inidservicoensaio);


    objfrm.find("#status").attr("name", "_999_u_servicoensaio_status");
    objfrm.find("#status option[value='"+$(vthis).attr('status')+"']").attr("selected", "selected");

    objfrm.find("#dia").attr("name", "_999_u_servicoensaio_dia");
    objfrm.find("#dia").attr("value",  $(vthis).attr('dia')); 
    
    objfrm.find("#ndropservico").attr("name", "_999_u_servicoensaio_idservicobioterio");
   // objfrm.find("#ndropservico option[value='TRANSFERENCIA']").attr("selected", "selected");
    objfrm.find("#ndropservico option[value='"+$(vthis).attr('tservico')+"']").attr("selected", "selected");
    
    objfrm.find("#tipoobjeto").attr("name", "_999_u_servicoensaio_tipoobjeto");    
        
    objfrm.find("#ndropanalise").attr("name", "_999_u_servicoensaio_idobjeto");
    objfrm.find("#ndropanalise option[value='"+$(vthis).attr('tanalise')+"']").attr("selected", "selected"); 

    objfrm.find("#obsdias").text($(vthis).attr('difdias')); 

    objfrm.find("#observ").attr("name", "_999_u_servicoensaio_obs");
    objfrm.find("textarea#observ").text($(vthis).attr('observ'));

    $("#cbModalCorpo").html(objfrm.html());
    $('#cbModal').modal('show');
		
}  
*/
   
function iservico(inidlote){
    var strCabecalho = "</strong>SERVIÇO <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='geraativ();'><i class='fa fa-circle'></i>Salvar</button></strong>";
    $("#cbModalTitulo").html((strCabecalho));

    var  htmloriginal =$("#servico").html();
    var objfrm= $(htmloriginal);
   
    objfrm.find("#idloteativ").attr("name", "_999_i_loteativ_idloteativ");
    objfrm.find("#idlote").attr("name", "_999_i_loteativ_idlote");
     objfrm.find("#idlote").attr("value", inidlote);
    objfrm.find("#ndropprativ").attr("name", "_999_i_loteativ_idprativ");
    //objfrm.find("#ndropprativ option[value='"+inidlote+"']").attr("selected", "selected");
    objfrm.find("#dia").attr("name", "_999_i_loteativ_dia");

    $("#cbModalCorpo").html(objfrm.html());
    $('#cbModal').modal('show');		
}

function geraativ(){
    
     var str="_999_i_loteativ_idloteativ="+$("[name=_999_i_loteativ_idloteativ]").val()+
            "&_999_i_loteativ_idlote="+$("[name=_999_i_loteativ_idlote]").val()+
            "&_999_i_loteativ_idprativ="+$("[name=_999_i_loteativ_idprativ]").val()+
            "&_999_i_loteativ_dia="+$("[name=_999_i_loteativ_dia]").val();
      
       CB.post({
               objetos: str
               ,parcial:true
               ,posPost: function(resp,status,ajax){
                   if(status="success"){
                       $("#cbModalCorpo").html("");
                       $('#cbModal').modal('hide');
                   }else{
                       alert(resp);
                   }
               }
           });
     }

function novoTeste(inidloteativ){     
    CB.post({
        objetos: "i_teste_idloteativ="+$('#idservico'+inidloteativ).val()+"&i_teste_idtipoteste="+$('#idtipoteste'+inidloteativ).val()+"&i_teste_qtdteste="+$('#qtdteste'+inidloteativ).val()   
    });
}
function atnvteste(inidservicoensaio){
    $('#qtdteste'+inidservicoensaio).removeClass("hidden");
    $('#idtipoteste'+inidservicoensaio).removeClass("hidden");
}

function altservico(vidservico){    
    
    CB.post({
        objetos: "_x_d_loteativ_idloteativ="+vidservico
        ,refresh:"refresh"
    });

}

function delresultado(vidresultado){    
    
    CB.post({
        objetos: "_x_d_resultado_idresultado="+vidresultado
        ,refresh:"refresh"
        ,parcial:true
    });

}

function setcontroleanalise(vthis,inidanalise){
    if(confirm("Deseja inserir o controle para esta analise?")){
      CB.post({
        objetos: "_setcontrole_u_analise_idanalise="+inidanalise+"&_setcontrole_u_analise_idbioensaioctr="+$(vthis).val()+"&idbioensaioant="+$(vthis).attr("idbioensaioctr")
        ,parcial:true
      });
    }
}
function resetcontroleanalise(vthis,inidanalise){
    if(confirm("Deseja retirar o controle para esta analise?")){
      CB.post({
        objetos: "_setcontrole_u_analise_idanalise="+inidanalise+"&_setcontrole_u_analise_idbioensaioctr=&idbioensaioant="+$(vthis).attr("idbioensaioctr")
        ,parcial:true
      });
    }
}

function dbioterioind(vidloteind){
    CB.post({
        objetos: "_x_d_loteind_idloteind="+vidloteind
        ,refresh:"refresh"
    });
}

function ibioterioind(vidlote){
    CB.post({
        objetos: "_x_i_loteind_idlote="+vidlote
        ,refresh:"refresh"
    });
}

function iulocalensaio(inidlocal,inidlocalensaio){
 
    $_post="_x_u_localensaio_idlocalensaio="+inidlocalensaio+"&_x_u_localensaio_idlocal="+inidlocal+"&&_x_u_localensaio_status=AGENDADO";
    
    CB.post({
        objetos: $_post
        ,refresh:"refresh"
    });
}
function flgagrupar(vidbioensaio,vagrupar){
	
    CB.post({
        objetos: "_x_u_bioensaio_idbioensaio="+vidbioensaio+"&_x_u_bioensaio_agrupar="+vagrupar
        ,refresh:"refresh"
    });
}

function bioensaiosgdoc(vthis){       
    CB.post({
        objetos: "_x_i_bioensaiosgdoc_idbioensaio="+$("[name=_1_u_bioensaio_idbioensaio]").val()+"&_x_i_bioensaiosgdoc_idsgdoc="+$(vthis).val()
       
    });    
}
function dbioensaiosgdoc(vidbioensaiosgdoc){
    CB.post({
        objetos: "_x_d_bioensaiosgdoc_idbioensaiosgdoc="+vidbioensaiosgdoc
        ,refresh:"refresh"
    });
}

function iubioensaiodes(inid,vthis){
    
    if(inid==='d'){
        $_post="_x_d_bioensaiodes_idbioensaiodes="+vthis;
    }else{          
        $_post="_x_i_bioensaiodes_idbioensaioc="+$(vthis).val()+"&_x_i_bioensaiodes_idbioensaio="+$("[name=_1_u_bioensaio_idbioensaio]").val();
    }
    
    CB.post({
        objetos: $_post
        ,refresh:"refresh"
    });
}	

if( $("[name=_1_u_bioensaio_idbioensaio]").val() ){
    $(".cbupload").dropzone({
        idObjeto: $("[name=_1_u_bioensaio_idbioensaio]").val()
        ,tipoObjeto: 'bioensaio'
    });
}

function imprimeEtiqueta(inIdobjeto,inTipoobjeto){
    var imprimir=true;
    CB.imprimindo=true;

    if(!confirm("Deseja realmente enviar para a impressora?")){
        imprimir=false;
    }

    if(imprimir){
        $.ajax({
            type: "get",
            url : "ajax/impetiquetabioensaio.php?idobjeto="+inIdobjeto+"&tipoobjeto="+inTipoobjeto,
            success: function(data){
                console.log(data);
                alertAzul("Enviado para impressão","",1000);

            }
        });
    }
}

function imprimeEtiquetasoro(inidservicoensaio){
    var imprimir=true;
    CB.imprimindo=true;

    if(!confirm("Deseja realmente enviar para a impressora?")){
        imprimir=false;
    }

    if(imprimir){
        $.ajax({
            type: "get",
            url : "ajax/impetiquetabioensaiosoro.php?idservicoensaio="+inidservicoensaio,
            success: function(data){
                console.log(data);
                alertAzul("Enviado para impressão","",1000);

            }
        });
    }
}
 //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape

</script>