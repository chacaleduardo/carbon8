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
$pagvaltabela = "bioensaio";
$pagvalcampos = array(
	"idbioensaio" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from bioensaio where idbioensaio = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if(empty($_1_u_bioensaio_exercicio)){
    $_1_u_bioensaio_exercicio= date("Y");
}

if(empty($_1_u_bioensaio_idbioensaiocp) and !empty($_GET['idbioensaiocp'])){
	$_1_u_bioensaio_idbioensaiocp=$_GET['idbioensaiocp'];
}
if($_acao=='i' and !empty($_1_u_bioensaio_idbioensaiocp)){	
    $bioensaiocp="Y";
    $sql="select 
            qtd,tipo,estudo,partida,idpessoa,idficharep,status,granja,dma(inicio) as inicio,obs,doses,volume,via,aviario,coranilha,datadzero,idbioterioanalise
            from bioensaio where idbioensaio = ".$_1_u_bioensaio_idbioensaiocp;
    $res=d::b()->query($sql) or die("Erro ao buscar biensaio de origem sql=".$sql);
    $row=mysqli_fetch_assoc($res);
    
    $_1_u_bioensaio_doses=$row['doses'];
    $_1_u_bioensaio_volume=$row['volume'];
    $_1_u_bioensaio_tipo=$row['tipo'];
    $_1_u_bioensaio_via=$row['via'];
    $_1_u_bioensaio_coranilha=$row['coranilha'];
    $_1_u_bioensaio_aviario=$row['aviario'];
    $_1_u_bioensaio_estudo=$row['estudo'];
    $_1_u_bioensaio_partida=$row['partida'];
    $_1_u_bioensaio_idpessoa=$row['idpessoa'];
    $_1_u_bioensaio_idficharep=$row['idficharep'];
    $_1_u_bioensaio_status=$row['status'];
    $_1_u_bioensaio_granja=$row['granja'];
    $_1_u_bioensaio_inicio=$row['inicio'];
    $_1_u_bioensaio_obs=$row['obs'];
}
if(!empty($_1_u_bioensaio_idficharep)){
        $idespeciefinalidade=traduzid("ficharep","idficharep","idespeciefinalidade",$_1_u_bioensaio_idficharep);
        $especie=traduzid("especiefinalidade","idespeciefinalidade","especie",$idespeciefinalidade);
    }

 if($especie=="Aves"){
        $fase1='Incubadora';
        $fase2='Pinteiro';
        $fase3='Biobox';
        $taloj='GAIOLA';        
        
    }else{
        $fase1='Reprodução';
        $fase2='Cria';
        $fase3='Biobox';
        $taloj='CAIXA';
   }

if(!empty($_1_u_bioensaio_idpessoa)){
    function getNucleoBioterio(){
        global $_1_u_bioensaio_idpessoa;
        $sql= "SELECT idnucleo,		
                LEFT((case situacao when 'INATIVO' then concat('Abatidas => ',nucleo) else nucleo end),100) as nucleo,lote
                        from nucleo where idpessoa = ".$_1_u_bioensaio_idpessoa."
                        and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                        order by situacao asc, nucleo asc ";

        $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

        $arrret=array();
        while($r = mysqli_fetch_assoc($res)){
                //monta 2 estruturas json para finalidades (loops) diferentes
                $arrret[$r["idnucleo"]]["nucleo"]=(($r["nucleo"]));
                $arrret[$r["idnucleo"]]["lote"]=(($r["lote"]));
        }
        return $arrret;
    }
    //Recupera os produtos a serem selecionados para uma nova Formalização
    $arrNucleo=getNucleoBioterio();
    //print_r($arrCli); die;
    $jNucleo=$JSON->encode($arrNucleo);
}
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
       align-items: center;
       display: flex;
       flex-direction: row;
       flex-wrap: wrap;
       justify-content: center;
    }
    .servico{      
        border: none;
        width:170px;
        min-height: 40px; 
         *background-color :#FF7F50;  
        float:left;  
        border-radius: 10px;    
        margin:2px;  
        display:flex;
        justify-content: center;
        align-items: center;
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
                    <?if($bioensaiocp=="Y"){?>
			<input name="_1_<?=$_acao?>_bioensaio_idbioensaiocp" type="hidden"	value="<?=$_1_u_bioensaio_idbioensaiocp?>" readonly='readonly'>
			<?}?>
                        <input  name="_1_<?=$_acao?>_bioensaio_idregistro" type="hidden" value="<?=$_1_u_bioensaio_idregistro?>" readonly='readonly'>
                        <input  name="_1_<?=$_acao?>_bioensaio_exercicio" type="hidden" value="<?=$_1_u_bioensaio_exercicio?>" readonly='readonly'>
			<input id="idbioensaio" name="_1_<?=$_acao?>_bioensaio_idbioensaio" type="hidden"	value="<?=$_1_u_bioensaio_idbioensaio?>" readonly='readonly'>
		    </td>
                    <td  align="right">Estudo:</td>
                    <td><input 	name="_1_<?=$_acao?>_bioensaio_estudo" size="30" type="text" value="<?=$_1_u_bioensaio_estudo?>" vnulo></td>
                    <td  align="right">Partida:</td>
                    <td ><input name="_1_<?=$_acao?>_bioensaio_partida" size="10" type="text" value="<?=$_1_u_bioensaio_partida?>"></td>
                    <td align="right">Ficha Rep.:</td> 
                    <td>
                        <select   name="_1_<?=$_acao?>_bioensaio_idficharep" vnulo>
                            <option value=""></option>
                            <?fillselect("select f.idficharep,concat(l.partida,'/',l.exercicio,'-',e.especie,'-[',f.idficharep,']') as especiefinalidade 
                                            from ficharep f,especiefinalidade e,lote l, unidadeobjeto o,prodserv p
                                            where e.idespeciefinalidade = f.idespeciefinalidade
                                            and o.tipoobjeto = 'prodserv'
                                            and o.idobjeto = p.idprodserv 
                                            and p.tipo = 'PRODUTO'
                                            and o.idunidade =4
                                            and l.idprodserv = p.idprodserv
                                            -- and l.exercicio = year(now())
                                            and f.idlote = l.idlote order  by idficharep desc",$_1_u_bioensaio_idficharep);?>		
                        </select>
                    </td>
                    <td>
                        <?if(!empty($_1_u_bioensaio_idficharep)){?>
                         <a class="fa fa-bars pointer hoverazul" title="Ficha de Reprodução/Inc." onclick="janelamodal('?_modulo=ficharep&_acao=u&idficharep=<?=$_1_u_bioensaio_idficharep?>')"></a>
                        <?}?>
                    </td>
                    <td  align="right">Qtd.:</td>
                    <td ><input name="_1_<?=$_acao?>_bioensaio_qtd" size="5" type="text" value="<?=$_1_u_bioensaio_qtd?>"></td>
                    <td align="right">Status:</td> 
                    <td>
                        <select   name="_1_<?=$_acao?>_bioensaio_status">
                            <?fillselect("select 'ATIVO','Ativo' union select 'DISPONIVEL','Disponà­vel' union select 'RESERVADO','Reservado' union select 'FINALIZADO','Finalizado' union select 'CANCELADO','Cancelado'",$_1_u_bioensaio_status);?>		
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div class="panel-body"> 
 <?
if(!empty($_1_u_bioensaio_estudo)){
 ?>
        <div class="row">
            <div class="row col-md-12">
            <div class="col-md-4">
                <table>
                    <tr>
                        <td align="right">Cliente:</td>
                        <td colspan="5">
                            <select  name="_1_<?=$_acao?>_bioensaio_idpessoa" vnulo>
                                <option value=""></option>
                                <?fillselect("SELECT c.idpessoa, c.nome FROM pessoa c where status = 'ATIVO' and idtipopessoa = 2 and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]." ORDER BY 2",$_1_u_bioensaio_idpessoa); ?>
                            </select>
                        </td>			
                    </tr>
                    <tr>
			<td align="right" norwrap>Núcleo:</td>
                        <td nowrap="nowrap">
                            <input type="text" name="_1_<?=$_acao?>_bioensaio_idnucleo" cbvalue="<?=$_1_u_bioensaio_idnucleo?>" value="<?=$arrNucleo[$_1_u_bioensaio_idnucleo]["nucleo"]?>" style="width: 27em;">
                        </td>
                        <td>

 <?
  if(!empty($_1_u_bioensaio_idnucleo)){
 ?>
       <a class="fa fa-bars pointer hoverazul" title="Núcleo" onclick="janelamodal('?_modulo=nucleo&_acao=u&idnucleo=<?=$_1_u_bioensaio_idnucleo?>')"></a>   
 <?
  }else{
 ?>
       <a class="fa fa-plus-circle pointer fade hoververde fa-1x" title="Novo Núcleo" onclick="janelamodal('?_modulo=nucleo&_acao=i&idpessoa=<?=$_1_u_bioensaio_idpessoa?>')"></a>                 
              
<?}?>
                       </td>
                    </tr>
                    <tr>                        
                        <td align="right">Análise:</td>
                        <td>
                        <select  name="_1_<?=$_acao?>_bioensaio_idbioterioanalise"  >
                            <option value=""></option>
                            <?fillselect("SELECT idbioterioanalise, tipoanalise FROM bioterioanalise where status = 'ATIVO' and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]." ORDER BY tipoanalise",$_1_u_bioensaio_idbioterioanalise); ?>
                        </select>  
                        </td>
                        <td>
                            <?if(!empty($_1_u_bioensaio_idbioterioanalise)){?>
                            <a class="fa fa-bars pointer hoverazul" title="Análise do Biotério" onclick="janelamodal('?_modulo=bioterioanalise&_acao=u&idbioterioanalise=<?=$_1_u_bioensaio_idbioterioanalise?>')"></a>
                            <?}?>
                        </td>	
                    </tr>
                    <tr>
                        <td align="right">Data D0:</td>
                        <td nowrap>
                            <input  name="_1_<?=$_acao?>_bioensaio_datadzero"  class="calendario" type="text" size ="8" value="<?=$_1_u_bioensaio_datadzero?>" >
                            <input  name="olddatadzero"   type="hidden"   value="<?=$_1_u_bioensaio_datadzero?>" >
                        </td>		
                    </tr>
		    <tr>
                        <td align="right">Antigeno:</td>
                        <td  colspan="3">
                        <textarea name="_1_<?=$_acao?>_bioensaio_antigeno"  style="width: 350px; height: 40px;" ><?=$_1_u_bioensaio_antigeno?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Obs:</td>
                        <td  colspan="5">
                            <textarea name="_1_<?=$_acao?>_bioensaio_obs"  style="width: 350px; height: 40px;" ><?=$_1_u_bioensaio_obs?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-4">
                <table>
                    <tr>
                        <td  align="right" nowrap>Produto:</td>
                        <td ><input <?//=$readonly2?>	name="_1_<?=$_acao?>_bioensaio_formulacao" type="text" value="<?=$_1_u_bioensaio_formulacao?>" ></td>
                        <td  align="right" nowrap>Apresentação:</td>
                        <td ><input <?//=$readonly2?>	name="_1_<?=$_acao?>_bioensaio_apresentacao" type="text" value="<?=$_1_u_bioensaio_apresentacao?>" ></td>
                    </tr>
                    <tr>
                        <td  align="right" nowrap>Nº Doses:</td>
                        <td><input name="_1_<?=$_acao?>_bioensaio_doses"  type="text" value="<?=$_1_u_bioensaio_doses?>" ></td>
                        <td  align="right" nowrap>Vol. Aplic.:</td>
                        <td ><input name="_1_<?=$_acao?>_bioensaio_volume"  type="text" value="<?=$_1_u_bioensaio_volume?>" ></td>		
                    </tr>
                    <tr>
                        <td  align="right" nowrap>Via:</td>
                        <td ><input name="_1_<?=$_acao?>_bioensaio_via"  type="text" value="<?=$_1_u_bioensaio_via?>" ></td>
                        <td  align="right" nowrap>Aviário:</td>
                        <td><input name="_1_<?=$_acao?>_bioensaio_aviario"  type="text" value="<?=$_1_u_bioensaio_aviario?>" ></td>
                    </tr>
                    <tr>
                        <td align="right">Tipo:</td> 
                        <td>
                            <select <?=$disabled2?>  name="_1_<?=$_acao?>_bioensaio_tipo" vnulo>
                                <option value=""></option>
                                <?fillselect("select 'SPF','SPF' union select 'CTR','Controlado' union  select 'CORTE','CORTE' ",$_1_u_bioensaio_tipo);?></select>
                        </td>                        
                        <td align="right">Cor da Anilha:</td> 
                        <td><input <?=$readonly2?>	name="_1_<?=$_acao?>_bioensaio_coranilha" size ="10" type="text" value="<?=$_1_u_bioensaio_coranilha?>" ></td>
		    </tr>
		    <tr>
			<td  align="right" nowrap>Enviado por:</td>
			<td><input <?//=$readonly2?>	name="_1_<?=$_acao?>_bioensaio_respenvio" size ="25" type="text" value="<?=$_1_u_bioensaio_respenvio?>"></td>
			<td  align="right" nowrap>Responsável:</td>
			<td><input <?//=$readonly2?>	name="_1_<?=$_acao?>_bioensaio_recebidopor" size ="15" type="text" value="<?=$_1_u_bioensaio_recebidopor?>"></td>	
		    </tr>
		    <tr>
			<td  align="right" nowrap> Envio:</td>
			<td>
			    <input class="calendario" name="_1_<?=$_acao?>_bioensaio_dataenvio" id ="fdata2" type="text" size ="6" value="<?=$_1_u_bioensaio_dataenvio?>">		
			</td>
			<td align="right">Recebimento:</td> 
			<td>
			    <input class="calendario"  name="_1_<?=$_acao?>_bioensaio_datareceb" id ="fdata3" type="text" size ="6" value="<?=$_1_u_bioensaio_datareceb?>">		
			</td>
		    </tr>
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
			<tr>
			    <td align="right">Certificado:</td>
			    <td colspan="4">					
				    <select  name="bioensaiosgdoc_idsgdoc"  onchange="bioensaiosgdoc(this);">
				    <option value=""></option>
				    <?fillselect("select idsgdoc,titulo from sgdoc
						    where idsgtipodoc = 56  
						    and idsgdocstatus = 'APROVADO' 
						    and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." ORDER BY titulo");?>		
				    </select>
			    </td>
			</tr>
			<?
			}else{
					$d=77;
				while($rowd=mysqli_fetch_assoc($resd)){
					$d=$d+1;
?>			
			<tr >
				<td align="right"> Certificado:</td>
				 <td colspan="4">
				    <a class="pointer" onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$rowd['idsgdoc']?>')" >
					<?=$rowd['titulo']?>-<?=$rowd['versao']?>.<?=$rowd['revisao']?>
				    </a>				    
				    <i class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer" idcontapagaritem="<?=$rowp['idcontapagaritem']?>" onclick="dbioensaiosgdoc(<?=$rowd["idbioensaiosgdoc"]?>)" title="Retirar"></i>
				</td>	
			</tr>
<?			
				}
				
			}
		}
?>
                </table>
            </div>
            <div class="col-md-2">
<?
        if(!empty($_1_u_bioensaio_idpessoa) and !empty($_1_u_bioensaio_idbioensaio)){
            $sqlc="select * from bioensaio where idbioensaioctr=".$_1_u_bioensaio_idbioensaio;
            $resc=d::b()->query($sqlc) or die("Erro ao buscar controle sql".$sqlc);
            $econtrole=mysqli_num_rows($resc);
	
            IF($econtrole<1){
                if($_1_u_bioensaio_status!='FINALIZADO' and $_1_u_bioensaio_status!='CANCELADO'){
                    $sqlctr="select b.idbioensaio,concat('B',b.idregistro) as registro
                                from bioensaio b
                                where  b.idpessoa = ".$_1_u_bioensaio_idpessoa."
                                and b.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                                and b.status not in ('CANCELADO','FINALIZADO')
                                and b.idbioensaio!=".$_1_u_bioensaio_idbioensaio." order by b.idregistro";
                }else{
                    $sqlctr="select b.idbioensaio,concat('B',b.idregistro) as registro
                                from bioensaio b
                                where  b.idpessoa = ".$_1_u_bioensaio_idpessoa."
                                and b.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                                and b.idbioensaio!=".$_1_u_bioensaio_idbioensaio." order by b.idregistro";
                }
	?>
	
            <table>
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
                <td align="right">Controle:</td>
                <td class="nowrap">
                    <select id="idbioensaioctr" <?=$disabled2?> name="_1_<?=$_acao?>_bioensaio_idbioensaioctr">
                        <option value="0" selected></option>
                        <?
                        fillselect($sqlctr,$_1_u_bioensaio_idbioensaioctr);
                        ?>
                    </select>	
		    <a class="fa fa-bars pointer hoverazul" title="Bioensaio Ctr" onclick="janelamodal('?_modulo=bioensaio&_acao=u&idbioensaio=<?=$_1_u_bioensaio_idbioensaioctr?>')"></a>
		</td>
            </tr>
	    <tr>
                <td align="right">Documento:</td>
                <td>
                    <select id="idsgdoc" <?=$disabled2?> name="_1_<?=$_acao?>_bioensaio_idsgdoc">
                        <option value="0" selected></option>
                        <?
                        fillselect("select idsgdoc,concat(idsgdoc,'-',titulo) 
				from sgdoc d where d.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
				 and d.idsgtipodoc = 58 order by idsgdoc",$_1_u_bioensaio_idsgdoc);
                        ?>
                    </select>	
		    <?if($_1_u_bioensaio_idsgdoc){?>
		    <a class="fa fa-bars pointer hoverazul" title="Documento" onclick="janelamodal('report/sgdocprint.php?acao=u&idsgdoc=<?=$_1_u_bioensaio_idsgdoc?>')"></a>
		    <?}?>
		</td>
	
            </tr>
            </table>	
	<?
            if(!empty($_1_u_bioensaio_idbioensaioctr)){
                $sqlcx="select * from bioensaio where idbioensaioctr=".$_1_u_bioensaio_idbioensaioctr;
                $rescx=d::b()->query($sqlcx) or die("Erro ao buscar controle x sql".$sqlcx);
                $qtdcx=mysqli_num_rows($rescx);
                if($qtdcx>0){
                ?>
                <table class="table table-striped planilha">
                    <tr >
                        <th  align="center">Bioensaio(s) do Ctr.</th>
                    </tr>

                    <?	
                    while($rowcx=mysqli_fetch_assoc($rescx)){
                    ?>
                    <tr >
                        <td align="center">
                            <a title="Editar Bioensaio" href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&idbioensaio=<?=$rowcx['idbioensaio']?>')">
                                B<?=$rowcx['idregistro']?>
                            </a>
                        </td>
                    </tr>
                    <?		}
                    ?>
                </table>
                    <?
                 }//if($qtdcx>0){
            }//if(!empty($_1_u_bioensaio_idbioensaioctr)){

            }else{//IF($econtrole<1){
	?>
                <table class="table table-striped planilha">
                    <tr >
                        <td align="center"><label  class="alert-warning">CTR-B<?=$_1_u_bioensaio_idregistro?></label></td>
                    </tr>

	<?	
                while($rowc=mysqli_fetch_assoc($resc)){
	?>
                    <tr >
                        <td align="center">
                            <a title="Editar Bioensaio" href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&idbioensaio=<?=$rowc['idbioensaio']?>')">	
                                B<?=$rowc['idregistro']?>
                            </a>
                        </td>
                    </tr>
	<?  
                }//while($rowc=mysqli_fetch_assoc($resc)){
	?>
		</table>
	<?
            }//IF($econtrole<1){
        }//if(!empty($_1_u_bioensaio_idpessoa)){
        ?>
            </div>
            <div class="col-md-2">
<?if( !empty($_1_u_bioensaio_idbioensaio)){?>
                <div class="panel panel-default">
                <div class="panel-heading">Desenho Experimental</div>
                    <div class="panel-body">
<?

		if($_1_u_bioensaio_agrupar=='Y'){
                    $sqlin="select
                                0 as idbioensaiodes,b.estudo,b.idbioensaio,b.qtd,concat('B',b.idregistro) as registro,b.idbioensaio as idbioensaioc
                            from bioensaio b 
                            where b.idbioensaio =  ".$_1_u_bioensaio_idbioensaio." union all ";
		}else{
                    $sqlin="select
                                0 as idbioensaiodes,b.estudo,b.idbioensaio,b.qtd,concat('B',b.idregistro) as registro,b.idbioensaio as idbioensaioc
                            from bioensaio b ,bioensaiodes d
                            where b.idbioensaio =  d.idbioensaio
                            and d.idbioensaioc =  ".$_1_u_bioensaio_idbioensaio." union all ";

		}
		
		$sqldes=$sqlin."select 
					d.idbioensaiodes,b.estudo,b.idbioensaio,b.qtd,concat('B',b.idregistro) as registro,d.idbioensaioc
				from bioensaiodes d,bioensaio b 
				where b.idbioensaio = d.idbioensaioc
				and d.idbioensaio = ".$_1_u_bioensaio_idbioensaio."
				union all
				select
					d.idbioensaiodes,b.estudo,b.idbioensaio,b.qtd,concat('B',b.idregistro) as registro,d.idbioensaioc
				from bioensaiodes d,bioensaio b
				where b.idbioensaio = d.idbioensaioc
				and exists  
				(select 1 from bioensaiodes dd 
				where d.idbioensaio = dd.idbioensaio 
				and dd.idbioensaioc = ".$_1_u_bioensaio_idbioensaio.")";
		$resdes=d::b()->query($sqldes) or die("Erro ao buscar desenho experimental sql=".$sqldes);
                $nrowdes=mysqli_num_rows($resdes);
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
			}
?>
                    </table>
<?
		}
?>	
                    <table>		
                        <tr>	
                            <?if($_1_u_bioensaio_agrupar=='Y'){?>
                            <td nowrap>Vincular Registro:</td>
                            <td>				
                            <select name="" onchange="iubioensaiodes('i',this);">
                            <option value="0" selected></option>
                            <?
                            fillselect("select b.idbioensaio,concat('B',b.idregistro) as registro
                                                    from bioensaio b
                                                    where  b.idpessoa = ".$_1_u_bioensaio_idpessoa."
                                                    and b.agrupar ='N'
                                                    and b.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                                                    and not exists(select 1 from bioensaiodes d where d.idbioensaioc = b.idbioensaio)
                                                    and not exists(select 1 from bioensaiodes dd where dd.idbioensaio = b.idbioensaio) order by b.idregistro");
                            ?>
                            </select>
                            </td>
                            <?}?>
                        </tr>
		<?if($nrowdes < 2){?>
                        <tr>
                                <TD align="right">
                                Agrupar?
                                </TD>
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
                        </tr>
		<?}?>
                    </table>
     
                    </div>
                </div>
<?
}
?>
            </div>
            </div>
        </div>
        
 <?
    if(!empty($_1_u_bioensaio_idficharep) and !empty($_1_u_bioensaio_idbioensaio)){   
        //BIOBOX SELECIONADO
        $sqlb="select concat(l.tipo,' ',right(l.local, 2)) as rot,
                l.idlocal,l.lotacao,l.multiensaio,l.tempo,
                le.idlocalensaio,l.local,le.ensaio,le.obs,le.gaiola
                from localensaio le left join local l on (l.tipo IN ('BIOBOX','AUTOGENA','TERCEIRO') and le.idlocal=l.idlocal)
                where  (le.idlocal > 3 OR le.idlocal IS NULL OR le.idlocal=0)
                and  le.idbioensaio = ".$_1_u_bioensaio_idbioensaio;
        $resb= d::b()->query($sqlb) or die("Erro ao buscar biobox sql=".$sqlb);
        $rowb= mysqli_fetch_assoc($resb);
	
	$sqlind="select * from  bioterioind where idbioensaio=".$_1_u_bioensaio_idbioensaio." order by identificacao";
	$resind=d::b()->query($sqlind) or die("Erro ao buscar animais sql=".$sqlind);
	$qtdind= mysqli_num_rows($resind);
	$collapse="collapse";
	if($qtdind>0){
	    $collapse="collapse-in";
	}
	$i=555;	
 ?>
	<div class="row">
            <div class="col-md-12" >
	    <div class="panel panel-default">
		<div class="panel-heading"  data-toggle="collapse" href="#localInfo1">  
		 <table>
		    <tr>
			 <td>ANIMAIS DO ESTUDO </td>
		    </tr>
		</table>
		</div>
		<div class="panel-body <?=$collapse?>" id="localInfo1">
		<table>	
		<tr>
	<?	
		while($rowind=mysqli_fetch_assoc($resind)){
			$i=$i+1;
	?>
		
		    <td>
			<input <?=$readonly2?>	name="_<?=$i?>_u_bioterioind_idbioterioind" size ="10" type="hidden" value="<?=$rowind['idbioterioind']?>" >
			<input <?=$readonly2?>	name="_<?=$i?>_u_bioterioind_identificacao"  size ="5" type="text" value="<?=$rowind['identificacao']?>" >
		    </td>
		    <td>
			<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="dbioterioind(<?=$rowind['idbioterioind']?>)" title="Excluir animal"></i>
		    </td>		
	<?
	
		}
	?>
		     <td>		  
			<i class="fa fa-plus-circle fa-1x  verde pointer" onclick="ibioterioind(<?=$_1_u_bioensaio_idbioensaio?>)" title="Inserir Animail"></i>
		    </td>
		
		</tr>
		</table>
		  
		</div>
	    </div>
	    </div>
	</div>
	    
	    
	    
    <div class="row">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-12" >
                <div class="panel panel-default">
                    <div class="panel-heading"  data-toggle="collapse" href="#localInfo">
                        <table>
                        <tr>
                            <td></td>
                            <td>
                                <?if(empty($rowb['rot'])){?>
                                <label class="alert-warning">Favor selecionar local do Alojamento.</label>
                                <?}else{?>
                                <?=strtoupper($rowb['rot']);?>
                                <?}?>
                            </td>
                            <td ><?=$taloj?> </td> 
                            <td>
                                <input name="_99_u_localensaio_idlocalensaio" type="hidden"  value="<?=$rowb['idlocalensaio']?>" >
                                <input name="_99_u_localensaio_gaiola" type="text"    	size="1" value="<?=$rowb['gaiola']?>" >
                            </td> 
                            <?
                            if(!empty($_1_u_bioensaio_idficharep)){
                                $sqldias="SELECT (DATEDIFF(curdate(),fim)) AS diasvida
                                        from ficharep
                                 where idficharep = ".$_1_u_bioensaio_idficharep;
                                $redias= d::b()->query($sqldias) or die("Erro ao buscar os dias de vida");
                                $rowdia=mysqli_fetch_assoc($redias);
                                if($rowdia['diasvida']>=0){
                                        $diasvida=$rowdia['diasvida'];
                                        ?>
                                                <td  nowrap><font color="red"> <?=$diasvida?> Dias de vida </font></td>	
                                                <?
                                                        }
                                }
                            ?>                           
                  
                        </tr>                        
                        </table>
                    </div>
                    <?listalocal($_1_u_bioensaio_idbioensaio);?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4" >
 <?
       listaservicoficha($_1_u_bioensaio_idbioensaio);
 ?>
            </div>
            <div class="col-md-8" >
 <?
       listaservicoesp($_1_u_bioensaio_idbioensaio);
?>
            </div>
        </div>  
        <div class="row">
            <div class="col-md-12" >
<?
        listaservicoest($_1_u_bioensaio_idbioensaio);
?>
            </div>
        </div>  
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
		<div class="panel-heading">Testes </div>
		<div class="panel-body">
<?
 if(!empty($_1_u_bioensaio_idnucleo)){
?>
                    <table id="tbTestes" class="table table-striped planilha">
                    <thead>
                    <tr>                           
                        <th colspan="6">Teste</th>                          
                    </tr>
                    </thead>
                    <tbody>
                            <?listaTestes()?>
                    </tbody>
                    </table>
                    <table class="hidden" id="modeloNovoTeste">
			<tr >
                            <td>
                                <input type="hidden" name="#nameidresultado">
                                <input type="hidden" name="#nameord" value="">
                                <input style=" border: 1px solid silver;" class="idprodserv" id="quantidaderes" name="#namequantidade" title="Qtd"  placeholder="Qtd" type="text" size="2" >	
                            </td>
                            <td>
                                <select id="idtipoteste" class="idprodserv" style="font-size: 9px;" name="#nameidtipoteste" >
                                <option value="0" selected></option>
                                <?
                                fillselect("select idprodserv as idtipoteste,codprodserv 
                                            from prodserv t 
                                            where status = 'ATIVO' 
                                            and tipo='SERVICO' 
                                            and exists (select 1 from unidadeobjeto p 
                                                            where p.idunidade in (2,4)
                                                            and p.tipoobjeto= 'prodserv'
                                                            and p.idobjeto=t.idprodserv)
                                            and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." order by codprodserv");
                                ?>
                                </select>
                            </td>
                            <td>
                                <select id="dropservico" style="font-size: 9px;" name="#nameidservicoensaio" >
                                    <option></option>
                                    <?
                                    fillselect("select s.idservicoensaio,concat(if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)),if(bc.idregistro is null,' ',concat(' - B',bc.idregistro))) as rotulo
                                                from servicobioterio sb ,servicoensaio s
                                                left join servicoensaio sc on(sc.idservicoensaio = s.idservicoensaioctr)
                                                left join bioensaio bc on(sc.idobjeto = bc.idbioensaio)
                                                where sb.geraamostra='S'
                                                and sb.servico = s.servico
                                                and s.status!='OFFLINE'
                                                and s.tipoobjeto='bioensaio'
                                                and s.idobjeto= ".$_1_u_bioensaio_idbioensaio." order by s.data,sb.ordem")
                                    ?>
                                </select>                               	
                             </td>
                             <td><i class="fa fa-arrows cinzaclaro hover move"></i></td>
                        </tr>
			</table>
                    	<div>
				<i id="novoteste" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoTeste()" title="Inserir novo teste"></i>
				<i class="fa fa-trash fa-2x cinzaclaro hoververmelho btn-lg pointer" id="excluirTeste" title="Arraste o teste até aqui para excluir"></i>
			</div>
<?
 }
?>
                </div>
        </div>
    </div>
    </div>
      <div class="panel panel-default">
       <div class="panel-heading">Arquivos Anexos</div>
       <div class="panel-body">
	    <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
		    <i class="fa fa-cloud-upload fonte18"></i>
	    </div>
	</div> 
      </div>
       
    <div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">		
            <div class="panel-body">    
               
               <table class="audit">
		<tr>
                    <td rowspan="3">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <i title="Etiqueta Estudo" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="imprimeEtiqueta(<?=$_1_u_bioensaio_idbioensaio?>,'bioensaio')">  &nbsp;&nbsp;Etiqueta</i>                     
                    </td>     
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
    </div>
    <div class="row ">
    <div class="col-md-12 container-fluid">
         <?$tabaud = "bioensaio";?>
        <div class="panel panel-default">		
            <div class="panel-body">
                <div class="row col-md-12">		
                    <div class="col-md-1">Criado Por:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
                    <div class="col-md-1">Criado Em:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadoem"}?></div>   
                </div>
                <div class="row col-md-12">            
                    <div class="col-md-1">Alterado Por:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
                    <div class="col-md-1">Alterado Em:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>       
                </div>
            </div>
        </div>
    </div>
    </div>
       
<?
     }//if(!empty($_1_u_bioensaio_idficharep)){  
}//if(!empty($_1_u_bioensaio_idbioensaio)){
?>
    
    </div>
    </div>
</div>

<div id="servico" style="display: none">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
            <div class="panel-heading">			                           
            <table>
                <tr> 
                    <td align="right">
                        <input  id="idservicoensaio"	name="" type="hidden" value="" >
                        <input  id="idobjeto"	name="" type="hidden" value="" >
                        <input  id="tipoobjeto"	name="" type="hidden" value="" >
                    </td>
                    <td align="right">Inà­cio:</td>
                    <td nowrap>
                        <input  name="" id ="fdata" class="calendario"  type="text" size ="6" value="" onchange="calculDiff();">
                        <input  id ="fdata2" type="hidden"  type="text" size ="6" value="" >
                    </td>
                    <td align="right"></td>
                    <td nowrap> 
                        <select name=""  id="ndropservico" value="">
                              <?fillselect("select servico,rotulo from  servicobioterio where status = 'ATIVO' and idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."  order by ordem",$_1_u_servicoensaio_servico); ?>
                        </select>
                        <input  name="" id ="dia"   type="hidden" size ="6" value="" >
                    </td>
                    <td align="right">Status:</td> 
                    <td>	
                    <select name="" id="status"  vnulo>
                        <?fillselect("SELECT 'PENDENTE','Pendente' union select 'CONCLUIDO','Concluido' union select 'INATIVO','Inativo'",$_1_u_servicoensaio_status); ?>
                    </select>	
                    </td>
                    <td>
                        <font color="red"><div id="obsdias"></div></font>                                    
                    </td>
                </tr>                
                <tr> 
                    <td align="right">Descr.:</td> 
                    <td colspan="5"><textarea name="" id="observ" style="width: 300px; height: 30px;"></textarea></td>		
                </tr>
            </table>
            </div>	
            </div>
        </div>
    </div>
</div>
<?
/*
* LISTA SERVIàOS DA FICHADE REPRODUààO
*/
function listaservicoficha($inidbio){ 
    global $fase1;
         //busca os servicos 
         $sqls="select s.idservicoensaio,s.servico,s.data,dma(s.data) as dmadata,s.dia,s.obs,s.status,sb.ordem,sb.rotulo,s.diazero,DATEDIFF(s.data,f.fim) AS difdias
           from servicoensaio s,servicobioterio sb,bioensaio b,ficharep f
           where b.idbioensaio =".$inidbio."
           and f.idficharep = b.idficharep          
           and sb.servico = s.servico
           and s.idobjeto = b.idbioensaio
           and s.status!='OFFLINE'
           and s.tipoobjeto = 'bioensaio'
           and s.data <=f.fim            
           order by s.data,sb.ordem";
                 
          /*       
                 "select s.idservicoensaio,s.servico,s.data,dma(s.data) as dmadata,s.dia,s.obs,s.status,sb.ordem,sb.rotulo,
                DATEDIFF(s.data,f.fim) AS difdias
                from bioensaio b,ficharep f, servicoensaio s,servicobioterio sb force index(servico)
                  where sb.servico = s.servico 
                  and f.idficharep = b.idficharep
                  AND b.idbioensaio = ".$inidbio."
                  and s.idobjeto = ".$inid."
                  and s.tipoobjeto ='".$inobj."' 
                  order by s.data,sb.ordem";
         */
         $ress=d::b()->query($sqls) or die("Erro ao buscar os serviços " . mysqli_error(d::b()) . "<p>SQL: ".$sqls);
         $qtdrows=mysqli_num_rows($ress);
         if($qtdrows>0){
         ?>
        <div class="panel panel-default">
            <div class="panel-heading">Serviços - <?=$fase1?></div>
            <div class="panel-body">
                <div class="linhacab1" >
		<div class="conteudo"> 
            <?
            while($rows=mysqli_fetch_assoc($ress)){
                if($rows['status']=="CONCLUIDO"){
                        $cor="#90EE90";
                }elseif($rows['status']=="INATIVO"){				
                        $cor="#DCDCDC";
                }else{
                        $cor="#FF7F50";
                }
            ?>
                <div style="background-color:<?=$cor?>; "  class="servico" title="<?=$rows['obs']?>" style="text-align: left;"  >
                <table>
		<tr>			
                    <td align="center" >
                        <a class="pointer" tservico="<?=$rows['servico']?>" dmadata="<?=$rows['dmadata']?>" dia="<?=$rows['dia']?>" difdias="<?=$rows['difdias']?> dias" status="<?=$rows['status']?>" observ="<?=$rows['obs']?>" onClick="uservico(this,<?=$rows['idservicoensaio']?>);">
                        <?echo($rows['rotulo']);?>
                        </a>
                    </td>
		</tr>
		<tr>
                    <td align="center" nowrap>
                    <font color="black"> 
                        <?echo($rows['dmadata']);?> <?=$rows['difdias']?> dias
                    </font>
                    </td>			
		</tr>  
		</table>
                </div>
<?
            }// while($rows=mysqli_fetch_assoc($ress)){
?>
                </div>
                </div>
            </div>
        </div>
<?
         }//if($qtdrows>0){// FIM DA FICHA DE REPRODUCAO
}//function listaservicoficha($inidbio,$inid,$inobj){ 

function listaservicoesp($inidbio){     
     global $fase2;
    //busca os servicos 
    $sqls="select s.idservicoensaio,s.servico,s.data,dma(s.data) as dmadata,s.dia,s.obs,s.status,sb.ordem,sb.rotulo,s.diazero,DATEDIFF(s.data,f.fim) AS difdias
           from servicoensaio s,servicobioterio sb,bioensaio b,ficharep f,servicoensaio as aloj
           where b.idbioensaio =".$inidbio."
           and f.idficharep = b.idficharep
           and aloj.idobjeto = b.idbioensaio
           and aloj.tipoobjeto = 'bioensaio'
           and aloj.servico='ALOJAMENTO'
           and sb.servico = s.servico
           and s.status!='OFFLINE'
           and s.idobjeto = b.idbioensaio
           and s.tipoobjeto = 'bioensaio'
           and s.data >f.fim
           and s.data <= aloj.data                
           order by s.data,sb.ordem";
    $ress=d::b()->query($sqls) or die("Erro ao buscar os serviços especificos " . mysqli_error(d::b()) . "<p>SQL: ".$sqls);
    $qtdrows=mysqli_num_rows($ress);
    if($qtdrows>0){
    ?>
   <div class="panel panel-default">
       <div class="panel-heading">Serviços - <?=$fase2?> </div>
       <div class="panel-body">
           <div class="linhacab2">
           <div class="conteudo"> 
       <?
       while($rows=mysqli_fetch_assoc($ress)){
           if($rows['status']=="CONCLUIDO"){
                   $cor="#90EE90";
           }elseif($rows['status']=="INATIVO"){				
                   $cor="#DCDCDC";
           }else{
                   $cor="#FF7F50";
           }
       ?>
           <div style="background-color:<?=$cor?>; "  class="servico" title="<?=$rows['obs']?>" style="text-align: left;" >
           <table>
           <tr>			
               <td align="center" >               
               <a class="pointer" tservico="<?=$rows['servico']?>" dmadata="<?=$rows['dmadata']?>" dia="<?=$rows['dia']?>" difdias="<?=$rows['difdias']?> dias" status="<?=$rows['status']?>" observ="<?=$rows['obs']?>" onClick="uservico(this,<?=$rows['idservicoensaio']?>);" >
                   <?echo($rows['rotulo']);?>
               </a>               
               </td>
                <td align="center">
                <?if($rows['diazero']=='N'){
                        $cor='cinza';
                        $diazero='Y';
                        $checked="";
                }else{
                        $cor='verde';
                        $diazero='N';
                        $checked="checked";
                }				
                ?>
                    <input title="Dia Zero?" type="checkbox" <?=$checked?> name="checkdzero"  onclick="flgdiazero(<?=$rows['idservicoensaio']?>,'<?=$diazero?>');">
                </td>
           </tr>
           <tr>
               <td align="center" nowrap>
               <font color="black"> 
                   <?echo($rows['dmadata']);?> <?=$rows['difdias']?> dias
               </font>
               </td>	
               <td align="center">
                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="altservico(<?=$rows['idservicoensaio']?>,'<?=$rows['servico']?>')" title="Excluir servico"></i>
               </td>
           </tr>
           </table>
           </div>
<?
       }// while($rows=mysqli_fetch_assoc($ress)){
?>
           </div>
           </div>
       </div>
   </div>
<?
         }//if($qtdrows>0){// FIM DA FICHA DE REPRODUCAO
}

function listaservicoest($inidbio){     
     global $fase3;
    //busca os servicos 
    $sqls="select s.idservicoensaio,s.servico,s.data,dma(s.data) as dmadata,s.dia,s.diazero,s.obs,s.status,sb.ordem,sb.rotulo,s.diazero,DATEDIFF(s.data,f.fim) AS difdias
           from servicoensaio s,servicobioterio sb,bioensaio b,ficharep f,servicoensaio as aloj
           where b.idbioensaio =".$inidbio."
           and f.idficharep = b.idficharep
           and aloj.idobjeto = b.idbioensaio
           and aloj.tipoobjeto = 'bioensaio'
           and aloj.servico='ALOJAMENTO'
           and sb.servico = s.servico
           and s.idobjeto = b.idbioensaio
           and s.status!='OFFLINE'
           and s.tipoobjeto = 'bioensaio'
           and s.servico !='ALOJAMENTO'
           and s.data >= aloj.data                
           order by s.data,sb.ordem";
    $ress=d::b()->query($sqls) or die("Erro ao buscar os serviços especificos " . mysqli_error(d::b()) . "<p>SQL: ".$sqls);
    $qtdrows=mysqli_num_rows($ress);
?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fa-plus-circle fa-1x  verde pointer" onclick="iservico(<?=$inidbio?>,'bioensaio')" title="Inserir Serviço"></i>
            Serviços -  <?=$fase3?> 
        </div>
        <div class="panel-body">
            <div class="linhacab3" >
            <div class="conteudo"> 
            <?
    while($rows=mysqli_fetch_assoc($ress)){
        if($rows['status']=="CONCLUIDO"){
                $cor="#90EE90";
        }elseif($rows['status']=="INATIVO"){				
                $cor="#DCDCDC";
        }else{
                $cor="#FF7F50";
        }
        if($rows['diazero']=="Y"){
                $exitedz="Y";
        }
            ?>
            <div  style="background-color:<?=$cor?>; "  class="servico"  title="<?=$rows['obs']?>" style="text-align: left;">
            <table>
            <tr>			
                <td align="center" >
                    <a  class="pointer" tservico="<?=$rows['servico']?>" dmadata="<?=$rows['dmadata']?>" dia="<?=$rows['dia']?>" difdias="<?=$rows['difdias']?> dias" status="<?=$rows['status']?>" observ="<?=$rows['obs']?>" onClick="uservico(this,<?=$rows['idservicoensaio']?>);">
                    <?echo($rows['rotulo']);?> <?if($exitedz==Y){?> (D <?=$rows['dia']?>) <?}?>
                    </a>                
                </td>
                <td align="center">
                <?if($rows['diazero']=='N'){
                        $cor='cinza';
                        $diazero='Y';
                        $checked="";
                }else{
                        $cor='verde';
                        $diazero='N';
                        $checked="checked";
                }				
                ?>
                    
                    <input title="Dia Zero?" type="checkbox" <?=$checked?> name="checkdzero"  onclick="flgdiazero(<?=$rows['idservicoensaio']?>,'<?=$diazero?>');">
                    
                </td>
            </tr>
            <tr>
                <td align="center" nowrap>
                <font color="black"> 
                    <?echo($rows['dmadata']);?> <?=$rows['difdias']?> dias
                </font>
                </td>
                <td align="center">
                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="altservico(<?=$rows['idservicoensaio']?>,'<?=$rows['servico']?>')" title="Excluir servico"></i>
                </td>
            </tr>
            </table>
            </div>
<?
             //opservicos($rows);
    }// while($rows=mysqli_fetch_assoc($ress)){
?>
            </div>
            </div>
        </div>
    </div>
<?
}//function listaservicoest($inidbio){ 

?>

<? 
function listalocal($_1_u_bioensaio_idbioensaio){ 
    global $_1_u_bioensaio_qtd;
 ?>
<div class="panel-body collapse" id="localInfo">
 <?
    $sqlp="select idlocalensaio,idlocal,idbioensaio,ensaio
             from localensaio le where le.idbioensaio = ".$_1_u_bioensaio_idbioensaio;
    $resp=d::b()->query($sqlp) or die("Erro ao buscar biobox PENDENTE");
    $qtdp=mysqli_num_rows($resp);
    if($qtdp>0){
        $rowp=mysqli_fetch_assoc($resp);					
                    //BUSCAR O FIM DA INCUBATàRIO
        $sqlf3="select dma(data) as dmadata,dma(DATE_ADD(data, INTERVAL 1 DAY)) as inicio,DATE_ADD(data, INTERVAL 1 DAY) as data,idservicoensaio from servicoensaio where tipoobjeto='bioensaio' and idobjeto = ".$_1_u_bioensaio_idbioensaio." and servico = 'ALOJAMENTO'";
        $resf3=d::b()->query($sqlf3) or die("Erro ao buscar o fim do incubatorio sql=".$sqlf3);
        $qtdf3=mysqli_num_rows($resf3);
        $rowf3=mysqli_fetch_assoc($resf3);

        //BUSCAR O FIM DA biobox
        $sqlfb="select dma(data) as dmadata,dma(DATE_ADD(data, INTERVAL 1 DAY)) as inicio,data,idservicoensaio from servicoensaio where tipoobjeto='bioensaio' and idobjeto = ".$_1_u_bioensaio_idbioensaio." and servico = 'ABATE'";
        $resfb=d::b()->query($sqlfb) or die("Erro ao buscar o fim do BIOBOX sql=".$sqlfb);
        $qtdfb=mysqli_num_rows($resfb);
        $rowfb=mysqli_fetch_assoc($resfb);

        $resper=d::b()->query("select (DATEDIFF('".$rowfb['data']."','".$rowf3['data']."')+1) AS diasper") or die("Erro ao buscar dias de permanàªncia");
        $rowper=mysqli_fetch_assoc($resper);	

?>		
    <div align="center" >
        Perà­odo - <?=$rowf3['inicio']?> à  <?=$rowfb['dmadata']?> - <?=$rowper['diasper']?> Dias 
    </div> 
    <hr>
<?
        $b=0;

        $sql1x="select ifnull(sum(r.qtd),0) as ocup,ifnull(l.lotacao,0) as lotacao,l.idlocal,l.local,l.tempo,concat(l.tipo,' ',right(l.local, 2)) as rot
                from local l left join localensaio e on(e.status IN ('AGENDADO','ATIVO') and e.idlocal = l.idlocal ) 
                left join vwreservabioensaio r 
                on(e.idbioensaio = r.idbioensaio
                and e.status !='FINALIZADO' 
                and(
                        (
                            if(r.iniciobio<='".$rowf3['data']."','".$rowf3['data']."',r.iniciobio) = '".$rowf3['data']."'
                             and 
                            if(r.fimbio>='".$rowfb['data']."','".$rowfb['data']."',r.fimbio )= '".$rowfb['data']."'
                            )
                            or
                            (
                            (r.iniciobio between '".$rowf3['data']."' and '".$rowfb['data']."' or  r.fimbio  between '".$rowf3['data']."' and '".$rowfb['data']."')
                            )
                        )						
                )
                where l.tipo IN ('BIOBOX','AUTOGENA','TERCEIRO')
                group by l.idlocal";
				
				//echo($sql1x);
		$res1x=d::b()->query($sql1x) or die("Erro ao buscar intervalo da ordem 1 BIOBOX sql=".$sql1x);
                                ?> 
    <div class="linhacab3">
        <div class="conteudo"> 
                <?
        while($row1x=mysqli_fetch_assoc($res1x)){
            //echo("ocup=".$row1x['ocup']."+".$_1_u_bioensaio_qtd." <= ".$row1x['lotacao']);
            if((($row1x['ocup']+$_1_u_bioensaio_qtd)<=($row1x['lotacao'])) or($rowp['idlocal']==$row1x['idlocal'])){
                $disp=$row1x['lotacao']-$row1x['ocup'];

                if($b==8){
                        echo("</tr>");
                        echo("<tr>");
                        $b=0;
                }

                if($rowp['idlocal']==$row1x['idlocal']){
                        $cor="#90EE90";
                }else{
                        $cor=" ";
                }

                $sqlt="select sum(r.qtd) as qtd,dma(fimbio) fimbio,r.especie							
                                from localensaio e,vwreservabioensaio r 
                                where  e.idlocal = ".$row1x['idlocal']."
                                and r.idbioensaio = e.idbioensaio group by fimbio,especie";

                $rest=d::b()->query($sqlt) or die("Erro ao buscar previsàµes sql=".$sqlt);
                $qtdt=mysqli_num_rows($rest);												
?>					
						
                
            <div  style="background-color:<?=$cor?>; cursor: pointer;" class="servico" title="" onclick="uilocal(this,8711);">
                <table>
                    <tr>
                        <td nowrap>
                        <?if($disp>=0){?>
                            <i class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="iulocalensaio(<?=$row1x['idlocal']?>,<?=$rowp['idlocalensaio']?>)" alt="Inserir no local"></i>
                        <?}else{ ?>
                            <img id="imgteste" style="display:inline;cursor:pointer;" src="../img/erro16.png" title="INDISPONIVEL" >	
                        <?}?>
                        </td>
                        <td nowrap> <font style="color: black"><?=$row1x['rot']?></font></td>
                    </tr>
                    <tr>
                        <td style="color: red; font-size: 10px;" align="center"  colspan="2">Disp.:<?=$disp?></td>
                    </tr>
                    <tr>
                        <td style="color: red; font-size: 8px; vertical-align: top;" align="center"  colspan="2" nowrap>
                        <?if($qtdt>0){
                                while($rowt=mysqli_fetch_assoc($rest)){
                                echo($rowt['qtd']." ".$rowt['especie']." ".$rowt['fimbio']."<br>");
                                }
                        }?>
                        </td>
                    </tr>
                </table>							
             </div>
								
<?	
		$b=$b+1;
            }//if((($row1x['ocup']+$_1_u_bioensaio_qtd)<=($row1x['lotacao'])) or($rowp['idlocal']==$row1x['idlocal'])){
					
	}//while($row1x=mysqli_fetch_assoc($res1x)){
?>	
        </div>
    </div>
    <hr>
<?
    }// if($qtdp>0){
?>		
</div>
<?
}//function listalocal($_1_u_bioensaio_idbioensaio){ 

function listatestes(){
    global $_1_u_bioensaio_idbioensaio,$_1_u_bioensaio_idpessoa,$_1_u_bioensaio_idbioterioanalise;

    $sqlt = "select s.idservicoensaio
		    ,s.dia
                ,p.sigla
                ,p.tipoteste
                ,r.quantidade
                ,r.status
                ,r.idamostra
                ,r.idresultado
                ,r.idservicoensaio
                ,r.ord
                ,r.idtipoteste
                ,s.dia
                ,if(s.dia is null,sb.rotulo,concat(sb.rotulo,' D',s.dia)) as rotulo,
                left(dma(s.data),5) as dataserv
            from bioensaio b,resultado r,vwtipoteste p,servicobioterio sb,servicoensaio s
           
            where sb.servico = s.servico
            and p.idtipoteste  = r.idtipoteste 
            and r.status !='OFFLINE'
            and r.idservicoensaio=s.idservicoensaio
            and s.tipoobjeto='bioensaio'
            and s.idobjeto =b.idbioensaio
            and b.idbioensaio =".$_1_u_bioensaio_idbioensaio." order by s.data";

    $i=10;
    $rest = d::b()->query($sqlt)or die("Erro ao recuperar resultados: \n".mysqli_error(d::b())."\n".$sqlt);
    $qtdres=mysqli_num_rows($rest);	
    if($qtdres>0){

        while($r=mysqli_fetch_assoc($rest)){
            if($r['status']=='FECHADO'){
                $cor="#B0E2FF";
            }elseif($r['status']=='ASSINADO'){
                 $cor="#90EE90";                
            }else{
                 $cor="white";     
            }
            
            $classDrag = ($r["status"]=="ABERTO"  or $r["status"]=="AGUARDANDO")?"dragExcluir":"";
            $disableteste = ($r["status"]=="ABERTO" or $r["status"]=="AGUARDANDO")?"":"readonly='readonly'";
?>
    <tr style=" background-color:<?=$cor?>;" class="<?=$classDrag?>" idresultado="<?=$r["idresultado"]?>">
        <td >
            <i title="Etiqueta do Serviço" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="imprimeEtiqueta(<?=$r['idservicoensaio']?>,'servicoensaio')"></i>                     
        </td> 
        <td style="white-space: nowrap;">
            <input type="hidden" name="_<?=$i?>_u_resultado_ord" value="<?=$r["ord"]?>">
            <input type="text" name="_<?=$i?>_u_resultado_quantidade" value="<?=$r["quantidade"]?>" style="width:30px" placeholder="Quant." vnulo vnumero>
        </td>
	<td style="white-space: nowrap;">
            <input type="hidden" name="_<?=$i?>_u_resultado_idresultado" value="<?=$r["idresultado"]?>">
            <input type="hidden" name="_<?=$i?>_u_resultado_idtipoteste" class="idprodserv" value="<?=$r["idtipoteste"]?>">
            <a href="?_modulo=resultaves&_acao=u&idresultado=<?=$r["idresultado"]?>" target="_blank"><?=$r["sigla"]?>-<?=$r["rotulo"]?> <?=$r["dataserv"]?></a>
	</td>	
	<td>
	    <a title="Etiqueta Com número dos animais" class="fa fa-print pull-right fa-lg azulclaro pointer hoverazul" onclick="imprimeEtiquetasoro(<?=$r['idservicoensaio']?>)"></a>
	</td>
	<td>
	    <?
            $hidemove="";
            if($r["status"]!=="ABERTO" AND $r["status"]!=="AGUARDANDO"){
                $hidemove="hidden";
            }
?>
            <i class="fa fa-arrows cinzaclaro hover move <?=$hidemove?>" title="Excluir teste"></i>
	</td>
    </tr>
<?
			$i++;
        }//while($r=  mysqli_fetch_assoc($rest)){
    }else{//if($qtdres>0){
?>                
    <tr id="copiarde">    
        <td  > Copiar de:</td>
        <td>
            <select  name="idcopiarde" id="idcopiarde" onchange="CB.post()();" style="font-size: 10px">
            <option value=""></option>
            <?fillselect("SELECT idbioensaio,concat(idregistro,'-',estudo) as estexterno 
                    FROM bioensaio where  idpessoa = ".$_1_u_bioensaio_idpessoa." 
                    and exercicio >= YEAR(CURDATE()) and idbioensaio !=".$_1_u_bioensaio_idbioensaio."  ORDER BY idregistro"); ?>
            </select>
        </td>	
    </tr>
<?
        if(!empty($_1_u_bioensaio_idbioterioanalise)){
?>
    <tr id="gerarde">    
        <td  > Gerar de:</td>
        <td>
            <select  name="idgerabioterioanalise" id="idgerabioterioanalise" onchange="CB.post()();" style="font-size: 10px">
            <option value=""></option>
            <?fillselect("select idbioterioanalise,tipoanalise
                        from bioterioanalise where idbioterioanalise = ".$_1_u_bioensaio_idbioterioanalise); ?>
            </select>
        </td>	
    </tr>
<?
        }
    }//if($qtdres>0){
}//function listatestes(){


?>
<script>
<?
        if(!empty($_1_u_bioensaio_idbioensaio)){
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
			return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.lote+"</span></a>").appendTo(ul);
                        
		};
	}
	
});
<?
        }
?>
function uservico(vthis,inidservicoensaio ){
    var strCabecalho = "</strong>SERVIàO <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";
    $("#cbModalTitulo").html((strCabecalho));

    var  htmloriginal =$("#servico").html();
    var objfrm= $(htmloriginal);

    objfrm.find("#idservicoensaio").attr("name", "_999_u_servicoensaio_idservicoensaio");
    objfrm.find("#idservicoensaio").attr("value", inidservicoensaio);

    objfrm.find("#fdata").attr("name", "_999_u_servicoensaio_data");
    objfrm.find("#fdata").attr("value",  $(vthis).attr('dmadata'));

    objfrm.find("#fdata2").attr("name", "old_servicoensaio_data");
    objfrm.find("#fdata2").attr("value",  $(vthis).attr('dmadata'));

    objfrm.find("#status").attr("name", "_999_u_servicoensaio_status");
    objfrm.find("#status option[value='"+$(vthis).attr('status')+"']").attr("selected", "selected");

    objfrm.find("#dia").attr("name", "_999_u_servicoensaio_dia");
    objfrm.find("#dia").attr("value",  $(vthis).attr('dia'));    
    
    objfrm.find("#ndropservico").attr("name", "_999_u_servicoensaio_servico");
   // objfrm.find("#ndropservico option[value='TRANSFERENCIA']").attr("selected", "selected");
    objfrm.find("#ndropservico option[value='"+$(vthis).attr('tservico')+"']").attr("selected", "selected");
 

    objfrm.find("#obsdias").text($(vthis).attr('difdias'));               

    objfrm.find("#observ").attr("name", "_999_u_servicoensaio_obs");
    objfrm.find("textarea#observ").text($(vthis).attr('observ'));

    $("#cbModalCorpo").html(objfrm.html());
    $('#cbModal').modal('show');
		
}

function novoTeste(){
    oTbTestes = $("#tbTestes tbody");
    iNovoTeste = (oTbTestes.find("input.idprodserv").length + 11);
    htmlTrModelo = $("#modeloNovoTeste").html();
    
    htmlTrModelo = htmlTrModelo.replace("#nameidresultado", "_"+iNovoTeste+"#idresultado");
    htmlTrModelo = htmlTrModelo.replace("#nameord", "_"+iNovoTeste+"#ord");
    htmlTrModelo = htmlTrModelo.replace("#nameidtipoteste", "_"+iNovoTeste+"#idtipoteste");
    htmlTrModelo = htmlTrModelo.replace("#namequantidade", "_"+iNovoTeste+"#quantidade");
    htmlTrModelo = htmlTrModelo.replace("#nameidservicoensaio", "_"+iNovoTeste+"#idservicoensaio");

    htmlTrModelo = htmlTrModelo.replace(/#irow/g, iNovoTeste);

    novoTr = "<tr class='dragExcluir'>"+htmlTrModelo+"</tr>";
    oTbTestes.append(novoTr);
        
        
    $("#gerarde").addClass("hidden");
    $("#copiarde").addClass("hidden");	
		
}

function iservico(inidobjeto,intipoobejto ){
    var strCabecalho = "</strong>SERVIàO <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";
    $("#cbModalTitulo").html((strCabecalho));

    var  htmloriginal =$("#servico").html();
    var objfrm= $(htmloriginal);

    objfrm.find("#idservicoensaio").attr("name", "_999_i_servicoensaio_idservicoensaio");
    objfrm.find("#idobjeto").attr("name", "_999_i_servicoensaio_idobjeto");
    objfrm.find("#idobjeto").attr("value", inidobjeto);
    objfrm.find("#tipoobjeto").attr("name", "_999_i_servicoensaio_tipoobjeto");
    objfrm.find("#tipoobjeto").attr("value", intipoobejto);
    objfrm.find("#ndropservico").attr("name", "_999_i_servicoensaio_servico");                
    objfrm.find("#fdata").attr("name", "_999_i_servicoensaio_data");	                
    objfrm.find("#status").attr("name", "_999_i_servicoensaio_status");                
    objfrm.find("#dia").attr("name", "_999_i_servicoensaio_dia");
    objfrm.find("#observ").attr("name", "_999_i_servicoensaio_obs");

    $("#cbModalCorpo").html(objfrm.html());
    $('#cbModal').modal('show');		
}

function flgdiazero(vidservico,vdiazero){
    
    CB.post({
        objetos: "_x_u_servicoensaio_idservicoensaio="+vidservico+"&_x_u_servicoensaio_diazero="+vdiazero
        ,refresh:"refresh"
    });
		
}

function altservico(vidservico,vservico){
    
    if(vservico=="TRANSFERENCIA"  || vservico=="ALOJAMENTO"){
        CB.post({
        objetos: "_x_u_servicoensaio_idservicoensaio="+vidservico+"&_x_u_servicoensaio_status=OFFLINE"
        ,refresh:"refresh"
        });
        
    }else{
    
    CB.post({
        objetos: "_x_d_servicoensaio_idservicoensaio="+vidservico
        ,refresh:"refresh"
        });
    }
}
function dbioterioind(vidbioterioind){
    CB.post({
        objetos: "_x_d_bioterioind_idbioterioind="+vidbioterioind
        ,refresh:"refresh"
    });
}

function ibioterioind(vidbioensaio){
    CB.post({
        objetos: "_x_i_bioterioind_idbioensaio="+vidbioensaio
        ,refresh:"refresh"
    });
}

function flgagrupar(vidbioensaio,vagrupar){
	
    CB.post({
        objetos: "_x_u_bioensaio_idbioensaio="+vidbioensaio+"&_x_u_bioensaio_agrupar="+vagrupar
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

function iulocalensaio(inidlocal,inidlocalensaio){
 
    $_post="_x_u_localensaio_idlocalensaio="+inidlocalensaio+"&_x_u_localensaio_idlocal="+inidlocal+"&&_x_u_localensaio_status=AGENDADO";
    
    CB.post({
        objetos: $_post
        ,refresh:"refresh"
    });
}

function ordenaTestes(){
    $.each($("#tbTestes tbody").find("tr"), function(i,otr){
        //Recupera objetos de update e de insert
        $(this).find(":input[name*=resultado_ord],:input[name*=ord]").val(i);
    })
}

$("#tbTestes tbody").sortable({
    update: function(event, objUi){
        ordenaTestes();
    }
});

$("#excluirTeste").droppable({
    accept: ".dragExcluir"
    ,drop: function( event, ui ) {
        //verifica se existe o idresultado em mode de update. caso positivo, alternar para excluir
        $idres = $(ui.draggable).attr("idresultado");
        if(parseInt($idres) && CB.acao!=="i"){
            if(confirm("Deseja realmente excluir o teste selecionado?")){
                ui.draggable.remove();
                CB.post({"objetos":"_x_d_resultado_idresultado="+$idres});
            }
        }else{
            if($(ui.draggable).find(":input[name*=#idresultado]").length==1){//Modo de inclusão
                ui.draggable.remove();
            }
        }		
    }
});

/*
 * Duplicar bioensaio [ctrl]+[d]
 */
$(document).keydown(function(event) {

    if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;

	if(!teclaLiberada(event)) return;//Evitar repetição do comando abaixo

	janelamodal('?_modulo=bioensaio&_acao=i&idbioensaiocp=<?=$_1_u_bioensaio_idbioensaio?>');

    return false;
});

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

if( $("[name=_1_u_bioensaio_idbioensaio]").val() ){
	$(".cbupload").dropzone({
		idObjeto: $("[name=_1_u_bioensaio_idbioensaio]").val()
		,tipoObjeto: 'bioensaio'
	});
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
