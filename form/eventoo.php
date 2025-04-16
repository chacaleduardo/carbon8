<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
$_acao=$_GET['_acao'];

if($_POST){
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "evento";
$pagvalcampos = array(
    "idevento" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from evento where idevento = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$inicio= $_GET['inicio']; 
$fim= $_GET['fim'];
$dataclick =str_replace("/","-",$_GET['dataclick']) ;

if(!empty($inicio) and !empty($fim)){
    $_1_u_evento_inicio=$inicio;
    $_1_u_evento_fim=$fim;
}

$idevento = (empty($_1_u_evento_idevento)) ? 'undefined' : $_1_u_evento_idevento;  
$inicioformatado = validadatetime($_1_u_evento_inicio);
$fimformatado = validadatetime($_1_u_evento_fim);
$evento =traduzid("eventotipo","ideventotipo","eventotipo",$_1_u_evento_ideventotipo);

function getJfuncionario(){
    global $JSON, $_1_u_evento_idevento;
    $s = "select 
                a.idpessoa
                ,a.nomecurto				
            from pessoa a
            where a.idempresa =".idempresa()." 
                and a.status in ('ATIVO','AFASTADO')
                and a.idtipopessoa =1
                    and not exists(
                            SELECT 1
                            FROM eventoresp v
                            where  v.idevento= ".$_1_u_evento_idevento." 
                                and v.idempresa =".idempresa()."
                                and a.idpessoa = v.idpessoa				
                    )
            order by a.nomecurto asc";

    $rts = d::b()->query($s) or die("getJSetorvinc: ". mysqli_error(d::b()));

    $arrtmp=array();
    $i=0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"]=$r["idpessoa"];
        $arrtmp[$i]["label"]= $r["nomecurto"];
        $i++;
    }
    return $JSON->encode($arrtmp);    
}

function getJSetorvinc(){
    global $JSON, $_1_u_evento_idevento;
    $s = "select 
                a.idsgsetor
                ,a.setor				
                from sgsetor a
                where a.idempresa =".idempresa()." 
                    and not exists(
                        SELECT 1
                        FROM eventoresp v
                        where v.idempresa=a.idempresa
                            and v.idevento= ".$_1_u_evento_idevento." 
                                and v.tipoobjeto = 'sgsetor'
                            and v.idobjeto=a.idsgsetor										
                    )
                order by a.setor desc";

    $rts = d::b()->query($s) or die("getJSetorvinc: ". mysqli_error(d::b()));

    $arrtmp=array();
    $i=0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"]=$r["idsgsetor"];
        $arrtmp[$i]["label"]= $r["setor"];
        $i++;
    }
    return $JSON->encode($arrtmp);    
}

$jSgsetorvinc="null";

if(!empty($_1_u_evento_idevento)){
    $jSgsetorvinc=getJSetorvinc();
}
function getJSgdoc(){
    global $JSON, $_1_u_evento_idevento;
    $s = "select 
                a.idsgdoc
                , concat(a.idregistro,'-',a.titulo,'-(',a.idsgdoctipo,')') as  titulo				
                from sgdoc a
                where a.idempresa =".idempresa()." 
                    and not exists(
                        SELECT 1
                        FROM eventoobj v
                        where v.idempresa=a.idempresa
                            and v.idevento= ".$_1_u_evento_idevento." 
                             and v.objeto = 'SGDOC'
                            and v.idobjeto=a.idsgdoc										
                    )
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

if(!empty($_1_u_evento_idevento)){
    $arrSgdoc=getJSgdoc();
    $jSgdoc=$JSON->encode($arrSgdoc);
}
function getJfornecedor(){
       $s = "select 
                a.idpessoa
                ,a.nome				
            from pessoa a
            where a.idempresa =".idempresa()." 
                and a.status ='ATIVO'
                and a.idtipopessoa =5                   
            order by a.nome asc";

    $rts = d::b()->query($s) or die("getJSetorvinc: ". mysqli_error(d::b()));

    $arrtmp=array();
    $i=0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"]=$r["idpessoa"];
        $arrtmp[$i]["label"]= $r["nome"];
        $i++;
    }
    return $arrtmp;     
}

$jSfornecedor="null";

if(!empty($_1_u_evento_idevento)){
    $arrforn=getJfornecedor();
    $jSfornecedor=$JSON->encode($arrforn);
}
//print_r($arrforn); die;
function getjEquipamento(){
    global $JSON, $_1_u_evento_idevento;
    $s = "select 
        a.idtag
        ,concat(a.tag,' - ',a.descricao) as descricao			
        from tag a
        where a.idempresa =".idempresa()." 
            and not exists(
                SELECT 1
                FROM eventoobj v
                where v.idempresa=a.idempresa
                    and v.idevento= ".$_1_u_evento_idevento." 
                    and v.objeto = 'EQUIPAMENTO'
                    and v.idobjeto=a.idtag										
            )
        order by a.tag";

    $rts = d::b()->query($s) or die("getJSetorvinc: ". mysqli_error(d::b()));

    $arrtmp=array();
    $i=0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"]=$r["idtag"];
        $arrtmp[$i]["label"]= $r["descricao"];
        $i++;
    }
    return $JSON->encode($arrtmp);    
}

$jEquipamento="null";

if(!empty($_1_u_evento_idevento)){
    $jEquipamento=getjEquipamento();
}
if(!empty($_1_u_evento_idevento)){
    $disabled = "disabled='disabled' ";
}
?>
<div onload="fecharpag(<?=$idevento?>,'<?=$evento?>','<?=$inicioformatado?>','<?=$fimformatado?>','<?=$dataclick?>')">

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-10" >Evento</div>            
            </div>            
        </div>
        <div class="panel-body"> 
            <div class="row">
                <div class="col-md-2" >Evento:</div>
                <div class="col-md-4" >
                     <input name="_1_<?=$_acao?>_evento_evento" id="idevento" type="text" value="<?=$_1_u_evento_evento?>" >            
                </div>
                 <div class="col-md-2" >Status:</div>
                <div class="col-md-4" >
                    <select name="_1_<?=$_acao?>_evento_status">
                        <?fillselect("SELECT 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_evento_status);?>		
                    </select>                
                </div>                
            </div>
            <div class="row">
                <div class="col-md-2" >Tipo Ação:</div>
                <div class="col-md-8" >
                    <input name="_1_<?=$_acao?>_evento_idevento" id="idevento" type="hidden" value="<?=$_1_u_evento_idevento?>" readonly='readonly'>
                    <select name="_1_<?=$_acao?>_evento_idtipoacao" <?=$disabled?> vnulo>
                        <?fillselect("select idtipoacao,tipoacao from tipoacao where status='ATIVO' and idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]." order by tipoacao",$_1_u_evento_idtipoacao);?>		
                    </select>
                </div>
                <div class="col-md-2" >   
                    <?if(!empty($_1_u_evento_idevento)){?>
                    <a class="fa fa-bars pointer hoverazul" title="Tipo Ação" onclick="janelamodal('?_modulo=tipoacao&_acao=u&idtipoacao=<?=$_1_u_evento_idtipoacao?>')"></a>
                    <?}?>
                </div>               
            </div>
            <div class="row">
                <div class="col-md-2" >Inà­cio:</div>
                <div class="col-md-4" >
                    <input <?=$disabled?> name="_1_<?=$_acao?>_evento_inicio" class="calendario" type="text" size ="15" value="<?=$_1_u_evento_inicio?>" vnulo>
                </div>
                <div class="col-md-2" >Fim:</div>
                <div class="col-md-4" >
                   <input <?=$disabled?> name="_1_<?=$_acao?>_evento_fim" class="calendario" type="text" size ="15" value="<?=$_1_u_evento_fim?>" vnulo>            
                </div>
            </div>
            <div class="row">
                <div class="col-md-2" >Repetir até:</div>
                <div class="col-md-4" >
                    <input name="_1_<?=$_acao?>_evento_repetirate" class="calendario" type="text" size ="8" value="<?=$_1_u_evento_repetirate?>" vnulo>			
                </div>
                <div class="col-md-2" >Periodicidade:</div>
                <div class="col-md-4" >
                    <select <?=$disabled?> name="_1_<?=$_acao?>_evento_periodicidade">
                        <?fillselect("SELECT 'DIARIO','Diario' union select 'SEMANAL','Semanal' union SELECT 'MENSAL','Mensal' union SELECT 'BIMESTRAL','Bimestral' union SELECT 'TRIMESTRAL','Trimestral' union SELECT 'SEMESTRAL','Semestral' union select 'ANUAL','Anual' union select 'BIANUAL','Bianual' union select 'TRIANUAL','Trianual'",$_1_u_evento_periodicidade);?>		
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2" >Fim de Semana:</div>
                <div class="col-md-4" >
                    <select <?=$disabled?> name="_1_<?=$_acao?>_evento_fimsemana">
                        <?fillselect("SELECT 'Y','Sim' union select 'N','Não'",$_1_u_evento_fimsemana);?>		
                    </select>		
                </div>
                <div class="col-md-2"></div>
                <div class="col-md-4"></div>
            </div>

            <div class="row">
                <div class="col-md-12" >
                    <textarea  name="_1_<?=$_acao?>_evento_obs" ><?=$_1_u_evento_obs?></textarea>
                </div>
            </div>      
        </div>
    </div>
    </div>
</div>
            <?
	if(!empty($_1_u_evento_idevento)){//mostra funcionarios responsaveis
?>   
<div class="row">    
    <div class="col-md-4" >

        <div class="panel panel-default" >
        <div class="panel-heading">Responsáveis</div>
        <div class="panel-body">     

        <table>
            <tr>
                <td id="tdfuncionario"><input id="eventoresp" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                <td id="tdsgsetor"><input id="sgsetorvinc" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                <td class="nowrap" style="width: 110px">  
                    <div class="btn-group nowrap" role="group" aria-label="..."> 
                        <button onclick="showfuncionario()" type="button" class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright selecionado" title="Selecionar Funcionário" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>		
                        <button onclick="showsgsetor()" type="button" class=" btn btn-default fa fa-users hoverlaranja pointer floatright " title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>  
                    </div>
                </td>
            </tr>
        </table>           
	<table class="table table-striped planilha"> 		
<?
//documentos
                $sqls ="select r.ideventoresp, p.usuario,p.nomecurto,p.idpessoa
                                from eventoresp r,pessoa p
                                where p.idpessoa = r.idobjeto
                                and r.tipoobjeto = 'pessoa'
                                and r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." 
                                and r.idevento =".$_1_u_evento_idevento;

                        $ress = d::b()->query($sqls) or die("A Consulta das pessoas falhou :".mysqli_error(d::b())."<br>Sql:".$sqls); 
                        $qtdrows= mysqli_num_rows($ress);
                        if($qtdrows> 0){
                            $y=9999;
                            while($rows = mysqli_fetch_array($ress)){	
                                $y=$y+1;
?>						
		<tr >
                    <td align="center"><?=$rows["nomecurto"]?></td>
                    <td>
                        <a class="fa fa-bars fa-1x pointer hoverazul" title="Funcionário" onclick="janelamodal('?_modulo=funcionario&_acao=u&idpessoa=<?=$rows['idpessoa']?>')"></a>
                    </td>
                    <td align="center">
                        <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="deventoresp(<?=$rows["ideventoresp"]?>)" title="Excluir"></a>
                    </td>
		</tr>
<?
                            }//while($rows = mysqli_fetch_array($ress)){	
                        }//if($qtdrows> 0){
                                
			$sqls ="select r.ideventoresp, p.setor,p.idsgsetor
					from eventoresp r,sgsetor p
					where p.idsgsetor = r.idobjeto
                                        and r.tipoobjeto = 'sgsetor'
					and r.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." 
					and r.idevento =".$_1_u_evento_idevento;
						
			  	$ress = d::b()->query($sqls) or die("A Consulta dos setores falhou :".mysqli_error(d::b())."<br>Sql:".$sqls); 
				$qtdrows= mysqli_num_rows($ress);				
				if($qtdrows> 0){
                                    $y=9999;
                                    while($rows = mysqli_fetch_array($ress)){	
                                        $y=$y+1;
?>						
		<tr >
                    <td align="center"><?=$rows["setor"]?></td>
                    <td>
                        <a class="fa fa-bars fa-1x pointer hoverazul" title="Setor" onclick="janelamodal('?_modulo=sgsetor&_acao=u&idsgsetor=<?=$rows['idsgsetor']?>')"></a>
                    </td>
                    <td align="center">
                        <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="deventoresp(<?=$rows["ideventoresp"]?>)" title="Excluir"></a>
                    </td>
		</tr>
<?
                                    }//while($rows = mysqli_fetch_array($ress)){
				}//if($qtdrows> 0){
?>		
	</table>       

        </div>
        </div>

    </div>
            <?   
                if($_1_u_evento_idtipoacao){
                    $idcadtipoacao= traduzid('tipoacao', 'idtipoacao', 'idcadtipoacao', $_1_u_evento_idtipoacao);         
                }
            if($idcadtipoacao==6){    
                if(!empty($_1_u_evento_idsgdoc)){
                    $sqll="select idsgdoc,concat(idregistro,'-',titulo,'-(',idsgdoctipo,')') as titulo from sgdoc where idsgdoc=".$_1_u_evento_idsgdoc;
                    $res1l = d::b()->query($sqll) or die("A Consulta do layout documento falhou :".mysqli_error(d::b())."<br>Sql:".$sqll);
                    $row1l = mysqli_fetch_assoc($res1l);
                }
            ?>
            <div class="col-md-6" >
                <div class="panel panel-default" >
                <div class="panel-heading">Layout Documento</div>
                <div class="panel-body">  
                    <table>
                        <tr>
                            <td>
                                <input id="sgdoclayout" type="text" name="_1_<?=$_acao?>_evento_idsgdoc" cbvalue="<?=$_1_u_evento_idsgdoc?>" value="<?=$row1l["titulo"]?>" style="width: 20em;">
                            </td>
                             <?if(!empty($_1_u_evento_idsgdoc)){?>
                            <td>
                                <a class="fa fa-bars pointer hoverazul" title="Documento" onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_evento_idsgdoc?>')" onchange="CB.post();"></a>
                            </td>
                             <?}?>
                        </tr>
                    </table>		               
                </div>
                </div>
            </div>
           <?
            }////if($idcadtipoacao==6){
           ?>
</div>
<div class="row">
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
</div>
 <?
     if($_1_u_evento_idtipoacao){
        $tipoacao= traduzid('tipoacao', 'idtipoacao', 'vinculo', $_1_u_evento_idtipoacao);        
        $I= strtotime($dataclick);        
        $II= strtotime(date("d-m-Y"));// trabalhando a segunda data
        if($I >= $II){
            $permitealt="S";		
        }elseif($II > $I){
            $permitealt="N";		
        }
        $permitealt="S";//desbloqueia os eventos 
        acao($_1_u_evento_idevento, $tipoacao, $dataclick, $permitealt,$_1_u_evento_idsgdoc,$_1_u_evento_evento); 
    }
 }//if(!empty($_1_u_evento_idevento)){

function acao($inidevento,$ineventotipo,$indataclick,$permitealt,$idsgdoclayout,$_1_u_evento_evento){
    $idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;	
 ?> 
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
            <div class="row" >
                                
                <?if($ineventotipo=='EQUIPAMENTO'){?>
                <div class="col-md-1" >
                    TAG:
                </div>
                <div class="col-md-8"  id="tdequipamento">
                    <input id="equipamento" class="compacto" type="text" cbvalue placeholder="Selecione uma tag">                
                </div>
                <?}elseif($ineventotipo=='SGDOC'){?>
                <div class="col-md-2" >Documento:</div>
                <div class="col-md-8"  id="tdsgdoc">
                    <input id="sgdoc" class="compacto" type="text" cbvalue placeholder="Selecione um documento">                    
                </div>
                <?}else{?>
                <div class="col-md-2" >Fornecedor:</div>
                <div class="col-md-8"  id="tdfornecedor">
                    <input id="fornecedor" class="compacto" type="text" cbvalue placeholder="Selecione um fornecedor">                    
                </div>
                <?}?>
              
                <div class="col-md-2" >
                <?if($permitealt=='S'){?>
                <button id="cdGerar" type="button" class="btn btn-danger btn-xs" onclick="geracao(this,'tbacao');" title="Salvar">
                    <i class="fa fa-circle"></i>Salvar
                </button>
                <?}else{
                    $vdisabled = "disabled".$y;
                    ${$vdisabled} = "readonly='readonly'";					
                }
                ?>
                </div>
            </div>
        </div>
        <div class="panel-body">            
<?	
	$sqlta="SELECT t.idcadtipoacao,t.idtipoacao 
				FROM evento e,tipoacao t
				where t.idtipoacao = e.idtipoacao 
				and e.idevento = ".$inidevento." 
				and e.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"];
	$reslta = d::b()->query($sqlta) or die("A Consulta da acao na eventoobj falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlta");
        
	//die($sqlta);
	while ($rowat = mysqli_fetch_assoc($reslta)){
	
            if($rowat["idcadtipoacao"]==1 and $ineventotipo=='EQUIPAMENTO'){//leitura
	
            $sqlleit="select  o.ideventoobj,e.equipamento,e.idequipamento,e.tag,e.padraotempmin,e.padraotempmax,a.idacao,a.padrao,a.erro,a.atual,a.min,a.max,a.idtipoacao,a.descr,a.status
                                    from equipamentobkp e,eventoobj o left join acao a on( o.idevento = a.idevento and o.idobjeto = a.idobjeto and a.objeto='EQUIPAMENTO' and a.dataevento=STR_TO_DATE('".$indataclick."','%d-%m-%Y') and a.idtipoacao= ".$rowat["idtipoacao"].")
                                    where e.idequipamento = o.idobjeto
                                    and o.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
                                    and o.objeto = '".$ineventotipo."'
                                    and o.idevento =".$inidevento;	
            //DIE($sqlleit);
            $resleit = d::b()->query($sqlleit) or die("A Consulta na na eventoobj falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlleit");
            $qtdl= mysqli_num_rows($resleit);
                if($qtdl>0){
?>		
             <table class="table table-striped planilha" id="tbacao">
		<tr >
                    <th align="center">Ação</th>
                    <th align="center">Tag</th>
                    <th align="center">Descrição</th>
                    <th align="center">Atual</th>
                    <th align="center">Min.</th>			
                    <th align="center">Max.</th>
                    <th align="center">Tolerà¢ncia <br>
                        Min. | Max.</th>	
                    <!-- <td align="center">Erro</td> -->
                    <th >Obs.</th>
                    <th align="center">Status</th>
                    <th align="center"></th>                        
		</tr>
<?	
                $y=99;
	  	while ($rowleit = mysqli_fetch_assoc($resleit)){
                    $y=$y+1;
                    $data=date("d/m/Y H:i:s");
                    if(empty($rowleit["idacao"])){
                        $idtipoacao=$rowat["idtipoacao"];
                        $_acao="i";
                    }else{
                        $idtipoacao=$rowleit["idtipoacao"];
                        $_acao="u";
                        if($rowleit["status"]=="CONCLUIDA"){
                            $vdisabled = "disabled".$y;
                            ${$vdisabled} = "disabled='disabled'";

                            $vreadonly = "readonly".$y;
                            ${$vreadonly} = "readonly='readonly'";
                        }				
                    }	  		
	  		
                    if((($rowleit["atual"]<$rowleit['padraotempmin']) or ($rowleit["atual"] >$rowleit['padraotempmax'])) 
                            and !empty($rowleit['padraotempmax']) and !empty($rowleit['padraotempmin']) and !empty($rowleit['atual'])){
                            $cor="red";
                    }else{
                            $cor="";
                    }
?>  	
  		<tr id="tr<?=$y?>" class="respreto" style="background-color:<?=$cor?>">
                    <td align="center"><a onclick="janelamodal('?_modulo=acao&_acao=u&idacao=<?=$rowleit["idacao"]?>')"><font color='Blue' style='font-weight: bold;'><?=$rowleit["idacao"]?></font></a></td>
                    <td align="center"><?=$rowleit["tag"]?>
                        <input name="_<?=$y?>_<?=$_acao?>_acao_idacao" type="hidden" value="<?=$rowleit["idacao"]?>">
                        <input name="_<?=$y?>_<?=$_acao?>_acao_titulo" type="hidden" value="<?=$_1_u_evento_evento?>">
                        <input name="_<?=$y?>_<?=$_acao?>_acao_idevento" type="hidden" value="<?=$inidevento?>">
                        <input name="_<?=$y?>_<?=$_acao?>_acao_idobjeto" type="hidden" value="<?=$rowleit["idequipamento"]?>">
                        <input name="_<?=$y?>_<?=$_acao?>_acao_objeto" type="hidden" value="EQUIPAMENTO">
                        <input name="_<?=$y?>_<?=$_acao?>_acao_idpessoa" type="hidden" value="<?=$idusuario?>">
                        <input name="_<?=$y?>_<?=$_acao?>_acao_dataevento" type="hidden" value="<?=$indataclick?>">
                        <input name="_<?=$y?>_<?=$_acao?>_acao_data" type="hidden" value="<?=$data?>">
                        <input name="_<?=$y?>_<?=$_acao?>_acao_idtipoacao" type="hidden" value="<?=$idtipoacao?>">							  			
                    </td>
                    <td align="left"><?=$rowleit["equipamento"]?></td>  			
                    <td align="center"><input <?=${"disabled".$y}?>  name="_<?=$y?>_<?=$_acao?>_acao_atual" size ="6" type="text" value="<?=$rowleit["atual"]?>"></td>
                    <td align="center"><input <?=${"disabled".$y}?>  name="_<?=$y?>_<?=$_acao?>_acao_min" size ="6" type="text" value="<?=$rowleit["min"]?>" ></td>
                    <td align="center"><input <?=${"disabled".$y}?>  name="_<?=$y?>_<?=$_acao?>_acao_max" size ="6" type="text" value="<?=$rowleit["max"]?>" ></td>
                    <td align="center">
                        <?=$rowleit['padraotempmin']?> | <?=$rowleit['padraotempmax']?>
                    </td>			
                    <td align="center"><input <?=${"disabled".$y}?>  name="_<?=$y?>_<?=$_acao?>_acao_descr" size ="30" type="text" value="<?=$rowleit["descr"]?>" ></td>
                    <td align="left">
                        <select <?=${"disabled".$y}?> name="_<?=$y?>_<?=$_acao?>_acao_status" >
                            <?fillselect("select 'PENDENTE','Pendente' union select 'CONCLUIDA','Concluida'",$rowleit["status"]);?>
                        </select>
                    </td>
                    <td align="center">
                        <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="deventoobj(<?=$rowleit["ideventoobj"]?>)" title="Excluir"></a>
                    </td>
		</tr>
<?	
  		}//while ($rowleit = mysqli_fetch_assoc($resleit)){
?>
	</table>
<?	
                }//if($qtdl>0){
            }elseif($rowat["idcadtipoacao"]==6){ //if($rowat["idcadtipoacao"]==1 and $ineventotipo=='EQUIPAMENTO'){ e manutencao
            
            if($ineventotipo=='EQUIPAMENTO'){
		$sqlm="select o.ideventoobj,e.equipamento as titulox,e.idequipamento as id,e.tag as idregistro,a.*
				from equipamentobkp e,eventoobj o left join acao a on( o.idevento = a.idevento and o.idobjeto = a.idobjeto and a.objeto='EQUIPAMENTO' and a.dataevento=STR_TO_DATE('".$indataclick."','%d-%m-%Y') and a.idtipoacao= ".$rowat["idtipoacao"].")
				where e.idequipamento = o.idobjeto
				and o.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and o.objeto = '".$ineventotipo."'
				and o.idevento =".$inidevento;	
            }elseif($ineventotipo=='SGDOC'){
                $sqlm="select o.ideventoobj,e.titulo as titulox,e.idsgdoc as id,e.idregistro,a.*
				from sgdoc e,eventoobj o left join acao a on( o.idevento = a.idevento and o.idobjeto = a.idobjeto and a.objeto IN ('SGDOC','PROCESSO') and a.dataevento=STR_TO_DATE('".$indataclick."','%d-%m-%Y') and a.idtipoacao= ".$rowat["idtipoacao"].")
				where e.idsgdoc = o.idobjeto
				and o.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and o.objeto = '".$ineventotipo."'
				and o.idevento =".$inidevento;	
            }elseif($ineventotipo=='PESSOA'){
                $sqlm="select o.ideventoobj,e.nome as titulox,e.idpessoa as id,e.idpessoa as idregistro,a.*
				from pessoa e,eventoobj o left join acao a on( o.idevento = a.idevento and o.idobjeto = a.idobjeto and a.objeto='PESSOA' and a.dataevento=STR_TO_DATE('".$indataclick."','%d-%m-%Y') and a.idtipoacao= ".$rowat["idtipoacao"].")
				where e.idpessoa = o.idobjeto
				and o.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and o.objeto = '".$ineventotipo."'
				and o.idevento =".$inidevento;	
            }
            //DIE($sqlm);
		$resm = d::b()->query($sqlm) or die("A Consulta na M eventoobj falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlm");
?> 
		<table class="table table-striped planilha" id="tbacao"> 
                    <tr>	
                        <th align="center">Ação</th>
                        <th align="center">Identificação</th>
                        <th align="center">Descrição</th>
                        <th align="center">Status</th>                            
                    </tr>
<?	
                $y=99;
	  	while ($rowm = mysqli_fetch_assoc($resm)){
                    $y=$y+1;
                    $data=date("d/m/Y H:i:s");
                    if(empty($rowm["idacao"])){
                        $idtipoacao=$rowat["idtipoacao"];
                        $_acao="i";
                    }else{
                        $idtipoacao=$rowm["idtipoacao"];
                        $_acao="u";
                        if($rowm["status"]=="CONCLUIDA"){
                            $vdisabled = "disabled".$y;
                            ${$vdisabled} = "disabled='disabled'";
                            $vreadonly = "readonly".$y;
                            ${$vreadonly} = "readonly='readonly'";
                        }
                    }	  				
?>
                    <tr class="respreto">	
                        <td align="center"><a onclick="janelamodal('?_modulo=acao&_acao=u&idacao=<?=$rowm["idacao"]?>')"><font color='Blue' style='font-weight: bold;'><?=$rowm["idacao"]?></font></a></td>
                        <td align="center"><?=$rowm["idregistro"]?>                                
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idacao" type="hidden" value="<?=$rowm["idacao"]?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idevento" type="hidden" value="<?=$inidevento?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_titulo" type="hidden" value="<?=$_1_u_evento_evento?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idobjeto" type="hidden" value="<?=$rowm["id"]?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_objeto" type="hidden" value="<?=$ineventotipo?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idsgdoc" type="hidden" value="<?=$idsgdoclayout?>">                                
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idpessoa" type="hidden" value="<?=$idusuario?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_dataevento" type="hidden" value="<?=$indataclick?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_data" type="hidden" value="<?=$data?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idtipoacao" type="hidden" value="<?=$idtipoacao?>">
                        </td>
                        <td><?=$rowm["titulox"]?></td>
                        <td align="left">
                            <select <?=${"disabled".$y}?> name="_<?=$y?>_<?=$_acao?>_acao_status" >
                                <?fillselect("select 'PENDENTE','Pendente' union select 'CONCLUIDA','Concluida'",$rowm["status"]);?>
                            </select>
                        </td>
                        <td align="center">
                            <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="deventoobj(<?=$rowm["ideventoobj"]?>)" title="Excluir"></a>
                        </td>
                    </tr>  		
<?		
  			}//while ($rowm = mysqli_fetch_assoc($resm)){
?>
		</table>
<?
            }else{//e manutencao
            
            if($ineventotipo=='EQUIPAMENTO'){
		$sqlm="select o.ideventoobj,e.equipamento as titulox,e.idequipamento as id,e.tag as idregistro,a.*
				from equipamentobkp e,eventoobj o left join acao a on( o.idevento = a.idevento and o.idobjeto = a.idobjeto and a.objeto='EQUIPAMENTO' and a.dataevento=STR_TO_DATE('".$indataclick."','%d-%m-%Y') and a.idtipoacao= ".$rowat["idtipoacao"].")
				where e.idequipamento = o.idobjeto
				and o.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and o.objeto = '".$ineventotipo."'
				and o.idevento =".$inidevento;	
            }elseif($ineventotipo=='SGDOC'){
                $sqlm="select o.ideventoobj,e.titulo as titulox,e.idsgdoc as id,e.idregistro,a.*
				from sgdoc e,eventoobj o left join acao a on( o.idevento = a.idevento and o.idobjeto = a.idobjeto and a.objeto IN ('SGDOC','PROCESSO') and a.dataevento=STR_TO_DATE('".$indataclick."','%d-%m-%Y') and a.idtipoacao= ".$rowat["idtipoacao"].")
				where e.idsgdoc = o.idobjeto
				and o.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and o.objeto = '".$ineventotipo."'
				and o.idevento =".$inidevento;	
            }elseif($ineventotipo=='PESSOA'){
                $sqlm="select o.ideventoobj,e.nome as titulox,e.idpessoa as id,e.idpessoa as idregistro,a.*
				from pessoa e,eventoobj o left join acao a on( o.idevento = a.idevento and o.idobjeto = a.idobjeto and a.objeto='PESSOA' and a.dataevento=STR_TO_DATE('".$indataclick."','%d-%m-%Y') and a.idtipoacao= ".$rowat["idtipoacao"].")
				where e.idpessoa = o.idobjeto
				and o.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and o.objeto = '".$ineventotipo."'
				and o.idevento =".$inidevento;	
            }
            //DIE($sqlm);
		$resm = d::b()->query($sqlm) or die("A Consulta na M eventoobj falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlm");
?> 
		<table class="table table-striped planilha" id="tbacao"> 
                    <tr>	
                        <th align="center">Ação</th>
                        <th align="center">Identificação</th>
                        <th align="center">Descrição</th>
                        <th align="center">Status</th>                            
                    </tr>
<?	
                $y=99;
	  	while ($rowm = mysqli_fetch_assoc($resm)){
                    $y=$y+1;
                    $data=date("d/m/Y H:i:s");
                    if(empty($rowm["idacao"])){
                        $idtipoacao=$rowat["idtipoacao"];
                        $_acao="i";
                    }else{
                        $idtipoacao=$rowm["idtipoacao"];
                        $_acao="u";
                        if($rowm["status"]=="CONCLUIDA"){
                            $vdisabled = "disabled".$y;
                            ${$vdisabled} = "disabled='disabled'";
                            $vreadonly = "readonly".$y;
                            ${$vreadonly} = "readonly='readonly'";
                        }
                    }	  				
?>
                    <tr class="respreto">	
                        <td align="center"><a onclick="janelamodal('?_modulo=acao&_acao=u&idacao=<?=$rowm["idacao"]?>')"><font color='Blue' style='font-weight: bold;'><?=$rowm["idacao"]?></font></a></td>
                        <td align="center"><?=$rowm["idregistro"]?> - <?=$rowm["titulox"]?>                              
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idacao" type="hidden" value="<?=$rowm["idacao"]?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idevento" type="hidden" value="<?=$inidevento?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_titulo" type="hidden" value="<?=$_1_u_evento_evento?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idobjeto" type="hidden" value="<?=$rowm["id"]?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_objeto" type="hidden" value="<?=$ineventotipo?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idsgdoc" type="hidden" value="<?=$idsgdoclayout?>">                                
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idpessoa" type="hidden" value="<?=$idusuario?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_dataevento" type="hidden" value="<?=$indataclick?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_data" type="hidden" value="<?=$data?>">
                            <input name="_<?=$y?>_<?=$_acao?>_acao_idtipoacao" type="hidden" value="<?=$idtipoacao?>">
                        </td>
                          <td><textarea  name="_<?=$y?>_<?=$_acao?>_acao_descr" ><?=$rowm["descr"]?></textarea></td>
                        <td align="left">
                            <select <?=${"disabled".$y}?> name="_<?=$y?>_<?=$_acao?>_acao_status" >
                                <?fillselect("select 'PENDENTE','Pendente' union select 'CONCLUIDA','Concluida'",$rowm["status"]);?>
                            </select>
                        </td>
                        <td align="center">
                            <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="deventoobj(<?=$rowm["ideventoobj"]?>)" title="Excluir"></a>
                        </td>
                    </tr>  		
<?		
  			}//while ($rowm = mysqli_fetch_assoc($resm)){
?>
		</table>
<?
            }
	}//while ($rowat = mysqli_fetch_assoc($reslta)){
?>
	</div>
    </div>
    </div>
    </div>
<?
}//function acao(){
?> 
<div class="row ">
    <div class="col-md-12 container-fluid">
        <?$tabaud = "evento";?>
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
<?
$jFuncionario="null";
if(!empty($_1_u_evento_idevento)){    
    $jFuncionario= getJfuncionario();
}
?>        
<script language="javascript">   
$('#tdsgsetor').hide();
$('#tdfuncionario').show();
      
jFuncionario = <?=$jFuncionario?>;

//Autocomplete de Setores vinculados
$("#eventoresp").autocomplete({
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
                "_x_i_eventoresp_idevento":$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
                ,"_x_i_eventoresp_tipoobjeto":'pessoa'
                ,"_x_i_eventoresp_idobjeto":ui.item.value
            }
            ,parcial: true
        });
    }
});

jSgsetorvinc = <?=$jSgsetorvinc?>;

//Autocomplete de Setores vinculados
$("#sgsetorvinc").autocomplete({
    source: jSgsetorvinc
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
                "_x_i_eventoresp_idevento":	$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
                ,"_x_i_eventoresp_tipoobjeto":'sgsetor'
                ,"_x_i_eventoresp_idobjeto": ui.item.value
            }
            ,parcial: true
        });
    }
});

JSfornecedor=<?=$jSfornecedor?>
//Autocomplete de documentos vinculados
$("#fornecedor").autocomplete({
    source: JSfornecedor
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
                "_x_i_eventoobj_idevento":	$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
                ,"_x_i_eventoobj_objeto":'PESSOA'
                ,"_x_i_eventoobj_idobjeto": ui.item.value
            }
            ,parcial: true
        });
    }
});


JSgdoc=<?=$jSgdoc?>
//Autocomplete de documentos vinculados
$("#sgdoc").autocomplete({
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
    ,select: function(event, ui){
        CB.post({
            objetos: {
                "_x_i_eventoobj_idevento":	$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
                ,"_x_i_eventoobj_objeto":'SGDOC'
                ,"_x_i_eventoobj_idobjeto": ui.item.value
            }
            ,parcial: true
        });
    }
});

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

JEquipamento=<?=$jEquipamento?>
//Autocomplete de documentos vinculados
$("#equipamento").autocomplete({
    source: JEquipamento
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
                "_x_i_eventoobj_idevento":	$(":input[name=_1_"+CB.acao+"_evento_idevento]").val()
                ,"_x_i_eventoobj_objeto":'EQUIPAMENTO'
                ,"_x_i_eventoobj_idobjeto": ui.item.value
            }
            ,parcial: true
        });
    }
});
function geracao(vthis,inid){
    //pega todos os tds 	
    var inputprenchido= $("#"+inid).children();
    //pega todos os inputs 	
    var vsubmit= decodeURIComponent($(inputprenchido).find("input:text, input:hidden,select,textarea").serialize());
    vsubmit=vsubmit.concat("&insert=acao"); 
    CB.post({
	objetos: vsubmit		
	,parcial:true
    })
}
function showfuncionario(){
    $('#tdsgsetor').hide();
    $('#tdfuncionario').show(); 
}
function showsgsetor(){
    $('#tdsgsetor').show();
    $('#tdfuncionario').hide();      
}
function deventoresp(inid){
    CB.post({
        objetos: "_x_d_eventoresp_ideventoresp="+inid
        ,refresh:"refresh"
    });
}
function deventoobj(inid){
    CB.post({
        objetos: "_x_d_eventoobj_ideventoobj="+inid
        ,refresh:"refresh"
    });
}
//funcão que insere ou retira o objeto da eventoobj e troca o objeto de select :)
function salvaritem(idevento,idobjeto,objeto,opcao,removerde,inserien){
	//pegar do array para enviar como inteiro
	var idobj =idobjeto[0];
        $.ajax({
            type: "get",
            url : "ajax/eventoobj.php",
            data: {videvento : idevento,
                    vidobjeto : idobj,
                    vobjeto : objeto,
                    vopcao : opcao},
            success: function(data){// retorno 200 do servidor apache
                vdata = data.replace(/(\r\n|\r|\n)/g, "");
                if(vdata=="OK"){
                    //troca o objeto de select do 1 para o 2 ou do 2 para o 1
                     $('#'+removerde+' option:selected').remove().appendTo('#'+inserien);
                    //return !$('#select1 option:selected').remove().appendTo('#select2');
                }else{
                    alert(data);
                    document.body.style.cursor = "default";
                }
            },
            error: function(objxml){ // nao retornou com sucesso do apache
                document.body.style.cursor = "default";
                alert('Erro: '+objxml.status);
            }
        })//$.ajax	
}
//função para buscar itens de multiselects
function buscaitem(inobjselect,inidimput){	
    //se o texto do imput não for vazio executa senão mostra todos os options
    if($('#'+inidimput).val()!=""){
            //pega o que o que e escrito no imput para busca
            vtextobusca = $('#'+inidimput).val();
            //maf instancia um objeto "Expressao Regular" concatenado com opcoes de busca Insensitiva (/string/i) a partir do texto que o usuario digitou
            var vexprreg = new RegExp(vtextobusca,"i");		
            //maf: efetua loop em todos os objetos OPTION do SELECT informado
            $('#'+inobjselect+' option').each(function(i) {
                //pega o conteudo text da drop
                var vtextoopt = "";
                vtextoopt = $(this).attr("text");
                //compara o texto do imput com o texto do option se tiver alguma letra igual mostra senão esconde
                if(vtextoopt.search(vexprreg)>=0){				
                    $(this).css("display", "");				
                }else{
                    $(this).css("display", "none"); 
                }			    
            });
    }else{
        //mostra todas os option
        $('#'+inobjselect+' option').css("display", "");
    }
}
if( $("[name=_1_u_evento_idevento]").val() ){
    $(".cbupload").dropzone({
        idObjeto: $("[name=_1_u_evento_idevento]").val()
        ,tipoObjeto: 'evento'
    });
}

/*
$( window ).unload(function() {
  window.opener.location.reload();
});
*/
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>