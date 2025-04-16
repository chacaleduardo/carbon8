<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("controllers/formapagamento_controller.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "formapagamento";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idformapagamento" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "select * from formapagamento where idformapagamento = '#pkid'";

/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");

function getAgenciaContaPagar($idagencia = false){
    global $JSON;

    if($idagencia){
        $aux = $idagencia;
    }else{
        $aux = "a.idagencia";
    }
    $sql = "SELECT a.idagencia
            FROM agencia a
            WHERE 
                1 ".getidempresa('a.idempresa','agencia')."
                and status = 'ATIVO'
                and not exists (SELECT 1 FROM contapagar c WHERE c.idagencia = ".$aux.")
            ORDER BY ord";
    $res = d::b()-> query($sql) or die("Erro ao buscar conta a pagar da agência. SQL: ".$sql);
    $num = mysql_num_rows($res);
    if($num > 0){
        if($idagencia){
            return $idagencia;
        }else{
            $i = 0;
            $arrtmp = array();
            while($row = mysql_fetch_assoc($res)){
                $arrtmp[$i]["idagencia"] = $row["idagencia"];
                $i++;
            }
            return $JSON->encode($arrtmp);
        }
    }else{
        return $arrtmp = 0;
    }
}

function getjsonLp(){
	global $JSON, $_1_u_formapagamento_idformapagamento;

	$sq = "SELECT concat(e.sigla,' - ',lp.descricao) as sigla,
				lp.idlp,
				lp.descricao
			FROM carbonnovo._lp lp
				JOIN empresa e ON (lp.idempresa = e.idempresa)
			WHERE lp.status = 'ATIVO' 
				AND e.sigla is not null
				AND NOT EXISTS (SELECT 1 from objetovinculo ov WHERE ov.idobjeto = lp.idlp AND ov.tipoobjeto = '_lp' and ov.tipoobjetovinc = 'formapagamento' and ov.idobjetovinc = $_1_u_formapagamento_idformapagamento)
			ORDER BY e.idempresa";

	$rq = d::b()->query($sq);

	if(mysqli_num_rows($rq) > 0){
		$arr = array(); $i = 0;

		while($r = mysqli_fetch_assoc($rq)){
			$arr[$i]["idlp"] = $r["idlp"];
			$arr[$i]["descricao"] = $r["sigla"];
			$i++;
		}
		$arr = $JSON->encode($arr);
	}else{
		$arr = $JSON->encode([]);
	}

	return $arr;
}

if($_acao == 'i'){
    $jAgencia = getAgenciaContaPagar();
}else{
    $jAgencia = getAgenciaContaPagar($_1_u_formapagamento_idagencia);
}

?>
<div class="row">
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <table>
                <tr> 		    
                    <td>
                        <input name="_1_<?=$_acao?>_formapagamento_idformapagamento" type="hidden" value="<?=$_1_u_formapagamento_idformapagamento?>" readonly='readonly'>
                    </td> 
                    <td align="right">Parcela Automática:</td> 
                    <td style="width:400px">                        
                        <input name="_1_<?=$_acao?>_formapagamento_descricao" type="text" value="<?=$_1_u_formapagamento_descricao?>" class="size30">
                    </td>
                    <? if($_1_u_formapagamento_idformapagamento){ ?>
                        <td>
                            <? if($_1_u_formapagamento_credito=='Y'){
                                    $checked='checked';
                                    $vchecked='N';					
                            }else{
                                    $checked='';
                                    $vchecked='Y';
                            }				
                            ?>
                            <input title="Crédito" type="checkbox" <?=$checked?> name="namesped" onclick="altcheck('formapagamento','credito',<?=$_1_u_formapagamento_idformapagamento?>,'<?=$vchecked?>')">
                        </td>
                        <td style="width:100px">Crédito </td>
                        <td>
                            <? if($_1_u_formapagamento_debito=='Y'){
                                    $checked='checked';
                                    $vchecked='N';					
                            }else{
                                    $checked='';
                                    $vchecked='Y';
                            }				
                            ?>
                            <input title="Débito" type="checkbox" <?=$checked?> name="namesped" onclick="altcheck('formapagamento','debito',<?=$_1_u_formapagamento_idformapagamento?>,'<?=$vchecked?>')">
                        </td>
                        <td style="width:100px">Débito</td>
                        <td style="width:130px">Alterar Vencimento:</td>
                        <td style="width:100px">
                            <select name="_1_<?=$_acao?>_formapagamento_vencimento">
                                <?fillselect("SELECT 'N','Não' UNION SELECT 'Y','Sim'",$_1_u_formapagamento_vencimento);?>		
                            </select>
                        </td>
                      <?}?>
                    <td align="right">Status:</td> 
                    <td>
                        <?if($_1_u_formapagamento_status=="INATIVO"){?>
                            <label class="alert-warning">INATIVO</label>
                        <?}else{?>
                        <select name="_1_<?=$_acao?>_formapagamento_status">
                                <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_formapagamento_status);?>		
                        </select>
                        <?}?>
                    </td> 
                </tr>	    
            </table>
        </div>
        <div class="panel-body"> 
            <div class="row">
                <div class="col-md-4">
                <table >
                <tr> 
                    <td align="right">Tipo de Pagamento:</td> 
                    <td>
                        <?
                        if(empty($_1_u_formapagamento_formapagamento)){
                            $formapagto="";
                            $formapagtoclass = "";
                        }else{
                            $formapagto="disabled='disabled'";
                            $formapagtoclass = 'class="desabilitado"';
                        }
                        ?>
                        <select name="_1_<?=$_acao?>_formapagamento_formapagamento" <?=$formapagto?> <?=$formapagtoclass?>>
                            <option value=""></option>
                            <?fillselect("select 'BOLETO','Boleto' 
                                            union select 'C.CREDITO','Cart&atilde;o de Cr&eacute;dito'
                                            union select 'C.DEBITO','Cart&atilde;o de D&eacute;bito'
                                            union select 'PIX','PIX' 
                                            union select 'CHEQUE','Cheque' 
                                            union select 'DEPOSITO','Depósito'
                                            union select 'TRANSFERENCIA','Transfer&ecirc;ncia'
                                            ",$_1_u_formapagamento_formapagamento);?>		
                        </select> 
                    </td>

                </tr>		
                <tr id="_agencias"> 
                    <td align="right">Agência:</td> 
                    <td>
                        <?
                        if(empty($_1_u_formapagamento_idagencia)){
                            $formapagtoagencia="";
                            $formapagtoclass = "";
                        }else{
                            $formapagtoagencia="readonly='readonly'";
                            $formapagtoclass = 'class="desabilitado"';
                        }
                        ?>
                        <select name="_1_<?=$_acao?>_formapagamento_idagencia" <?=$formapagtoagencia?> <?=$formapagtoclass?> vnulo>
                            <option value=""></option>
                            <?fillselect("select idagencia,agencia 
                                    from agencia where 1 ".getidempresa('idempresa','agencia')."  and status = 'ATIVO' order by ord",$_1_u_formapagamento_idagencia);?>		
                        </select>
                    </td>
                    <?
                    if((!empty($_1_u_formapagamento_idformapagamento)) AND $jAgencia == $_1_u_formapagamento_idagencia){?>
                    <td class="saldo_agencia">
                        Saldo Agência:
                    </td>
                    <td class="saldo_agencia">
                        <input name="x_contasaldo" type="text" value="0,00"/>
                    </td>
                    <?}?>
                </tr>
                <tr> 
                    <td align="right">Tipo:</td> 
                    <td>
                        <?
                        if(empty($_1_u_formapagamento_tipoespecifico)){
                            $formapagto="";
                            $formapagtoclass = "";
                        }else{
                            $formapagto="disabled='disabled'";
                            $formapagtoclass = 'class="desabilitado"';
                        }
                        ?>
                        <select name="_1_<?=$_acao?>_formapagamento_tipoespecifico" <?=$formapagto?> <?=$formapagtoclass?>>
                            <option value=""></option>
                            <?fillselect("select 'AGRUPAMENTO','Agrupamento' 
                                            union select 'REPRESENTACAO','Comissão'
                                            union select 'IMPOSTO','Imposto'
                                            ",$_1_u_formapagamento_tipoespecifico);?>		
                        </select> 
                    </td>

                </tr>		
<?
                if($_1_u_formapagamento_formapagamento=='C.CREDITO'){
?>
                <tr> 
                    <td align="right">Final Cartão:</td> 
                    <td>
                       <input name="_1_<?=$_acao?>_formapagamento_ncartao" type="text" value="<?=$_1_u_formapagamento_ncartao?>" class="size8" >
                    </td>
                </tr>	
<?                    
                }
?>	
<?
                if($_1_u_formapagamento_agrupado=='Y'){
?>
                <tr> 
                    <td align="right">Dia Vencimento:</td> 
                    <td>
                       <input name="_1_<?=$_acao?>_formapagamento_diavenc" type="text" value="<?=$_1_u_formapagamento_diavenc?>" class="size3" >
                    </td>
                </tr>	
                <tr> 
                    <td align="right">Previsão R$:</td> 
                    <td>
                       <input name="_1_<?=$_acao?>_formapagamento_previsao" type="text" value="<?=$_1_u_formapagamento_previsao?>" class="size8" >
                    </td>
                </tr>
<?
                }
?> 

                <tr> 
                    <td align="right">1º Vencimento:</td> 
                    <td>
                       <input name="_1_<?=$_acao?>_formapagamento_diasentrada" type="text" value="<?=$_1_u_formapagamento_diasentrada?>" class="size3" vnulo>
                    </td>
                </tr>
                <?if($_1_u_formapagamento_formapagamento=='BOLETO'){?>
                <tr>
                    <td align="right">Gera Remessa:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_formapagamento_geraremessa">
                            <option value=""></option>
                            <?fillselect("select 'Y','Sim' union select 'N','Não'",$_1_u_formapagamento_geraremessa);?>		
                        </select>
                    </td>
                </tr>
                <?}?>
                 <?if($_1_u_formapagamento_formapagamento=='C.CREDITO'){?>
                <tr>
                    <td align="right">Responsável:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_formapagamento_idpessoa" vnulo>
                            <option value=""></option>
                            <?fillselect("SELECT idpessoa, concat(nome,' - ', p.status) as nome
                                            FROM pessoa p WHERE p.idtipopessoa IN (1) AND p.usuario IS NOT NULL  
                                        UNION 
                                          SELECT idpessoa, concat(nome,' - ', status) as nome
                                            FROM pessoa  WHERE faturaautomatica = 'S'                                                
                                        ORDER BY nome ",$_1_u_formapagamento_idpessoa);?>		
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="right">Unidade:</td> 
                    <td>
                    <select name="_1_<?=$_acao?>_formapagamento_idunidade" class="size25" vnulo>
                        <option value=""></option>
                        <?
                        fillselect("select idunidade,unidade from unidade where status = 'ATIVO' and idempresa =".cb::idempresa()." order by unidade",$_1_u_formapagamento_idunidade);
                        ?>
                        </select>
                    </td>
                </tr>
                <?}?>
                <tr> 
                    <td align="right">Ordem:</td> 
                    <td>
                       <input name="_1_<?=$_acao?>_formapagamento_ord" type="text" value="<?=$_1_u_formapagamento_ord?>" class="size3" vnulo>
                    </td>
                </tr>
                </table>
                 </div>
                 <div class="col-md-8">
                <? if($_1_u_formapagamento_idformapagamento){ ?>
                    <div class="panel panel-default">
                    <table class="table table-striped planilha">
                        <tr>    
                            <td colspan="6" align="center">  
                            
                                <? if($_1_u_formapagamento_agrupado=='Y'){
                                        $checked='checked';
                                        $vchecked='N';					
                                }else{
                                        $checked='';
                                        $vchecked='Y';
                                }				
                                ?>
                                <input title="Agrupado - Todas as faturas devem ter itens" disabled='disabled' type="checkbox" <?=$checked?> name="nameagrup" onclick="altcheck('formapagamento','agrupado',<?=$_1_u_formapagamento_idformapagamento?>,'<?=$vchecked?>')">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                Agrupado
                            </td>
                        </tr>
                        <tr>
                            <?if($_1_u_formapagamento_agrupado=='Y'){?>
                             <td>
                                <? if($_1_u_formapagamento_agrupfpagamento=='Y'){
                                        $checked='checked';
                                        $vchecked='N';					
                                }else{
                                        $checked='';
                                        $vchecked='Y';
                                }				
                                ?>
                                <input id="rdAgrupadoPorPagamento" title="Agrupa por forma de pagamento exemplo:(cartão de credito), cria uma Fatura em ABERTO no sistema e insere as parcelas na mesma conforme forem criadas as notas no sistema" type="radio" <?=$checked?> name="nameagrupf" onclick="altcheckAF(<?=$_1_u_formapagamento_idformapagamento?>,'<?=$vchecked?>')">
                            </td>
                            <td> Agrupado por Pagamento</td>
                            <td>
                                <? if($_1_u_formapagamento_agruppessoa=='Y'){
                                        $checked='checked';
                                        $vchecked='N';					
                                }else{
                                        $checked='';
                                        $vchecked='Y';
                                }				
                                ?>
                                <input  id="rdAgrupadoPorPessoa" title="Agrupa pelo CNPJ da pessoa, irá criar uma Fatura em ABERTO para a pessoa da nota, e inserir as parcelas na mesma. Exemplo CTE." type="radio" <?=$checked?> name="nameagrupp" onclick="altcheckAP(<?=$_1_u_formapagamento_idformapagamento?>,'<?=$vchecked?>')">
                            </td>
                            <td> Agrupado por CNPJ</td>
                            <td>
                                <? if($_1_u_formapagamento_agrupnota=='Y'){
                                        $checked='checked';
                                        $vchecked='N';					
                                }else{
                                        $checked='';
                                        $vchecked='Y';
                                }				
                                ?>
                                <input title="Agrupa por nota, cria uma Fatura PENDENTE no sistema por nota, e insere a parcela na mesma, para que os pagamentos tenham a mesma extrutura de dados Fatura e Parcelas" type="radio" <?=$checked?> name="nameagrupn" onclick="altcheckAN(<?=$_1_u_formapagamento_idformapagamento?>,'<?=$vchecked?>')">
                            </td>
                            <td> Agrupado por Nota</td>
                            
                            <?}?>
                            

                        </tr>
                    </table>
                 </div>     
                     <? } ?>

                <?
                if($_GET['_acao']=='u'){
                //autocomplete pessoa
                if((!empty($_1_u_formapagamento_idpessoa) and  $_1_u_formapagamento_agrupfpagamento=='Y' )){
                        $sql= "SELECT 
                                    p.idpessoa, p.nome
                                FROM
                                    pessoa p
                                WHERE
                                    p.idpessoa=".$_1_u_formapagamento_idpessoa;

                }else{
                    $sql= "SELECT 
                                p.idpessoa, p.nome
                            FROM
                                pessoa p
                            WHERE
                                p.status IN ('ATIVO')
                                AND p.idtipopessoa  IN (11,5)
                                and not exists(select 1 from formapagamentopessoa f where f.idformapagamento=".$_1_u_formapagamento_idformapagamento." and f.idpessoa=p.idpessoa)
                            ORDER BY p.nome";
                    
                }


                    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

                    $arrret=array();

                    while($r = mysqli_fetch_assoc($res)){
                        //monta 2 estruturas json para finalidades (loops) diferentes
                        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
                    }
                    $arrCli=$arrret;
                    $jCli=$JSON->encode($arrCli);

                //select lista pessoa/previsão
                    $sqlp="SELECT f.*,p.nome FROM formapagamentopessoa f LEFT JOIN pessoa p on (p.idpessoa=f.idpessoa) WHERE idformapagamento = ".$_GET['idformapagamento']."";
                    $resp = d::b()->query($sqlp) or die("getformapagamentos: Erro: ".mysqli_error(d::b())."\n".$sqlp);
                    $nrowfp = mysqli_num_rows($resp);
                }

                if( $_1_u_formapagamento_agruppessoa=='Y' or (!empty($_1_u_formapagamento_idpessoa) and  $_1_u_formapagamento_agrupfpagamento=='Y' ) or $_1_u_formapagamento_tipoespecifico =='IMPOSTO'){
                ?>

                <div id="previsaoPessoa" style="display: none;" class="panel panel-default">
                    <div class="panel-heading" data-toggle="collapse" href="#novaprevisao">
                        Previsão
                    </div>
                    <div id="novaprevisao" style="padding: 10px;" >
                        <?if($nrowfp >=1){?>
                        <div>
                            <!-- table style="width: 100%;">
                                <tr>
                                    <td><strong>Buscar nome:</strong> </td>
                                    <td style="width: 84%;"><input style="width:100%;height: 28px;" id="pesquisarprevisao" type="text"></td>
                                </tr>
                            </table -->
                            <table class="table table-striped">
                                <tr>
                                    <th style="width: 40%;">Nome</th>
                                    <th>Categoria</th>
                                    <th>Tipo</th>
                                    <th>Previsão</th>
                                    <th></th>
                                </tr>
                                <tbody  id="listaPrevisao">
                                <? 
                                $i=99;
                                while($rp = mysqli_fetch_assoc($resp)){  
                                    $i++;
                                ?>
                                    <tr>
                                        <td>
                                            <?=$rp['nome'] ?>
                                            <input name="_<?=$i?>_u_formapagamentopessoa_idformapagamentopessoa" size="8" type="hidden" value="<?=$rp["idformapagamentopessoa"]; ?>">
                                            <input name="_<?=$i?>_u_formapagamentopessoa_idformapagamento" size="8" type="hidden" value="<?= $_1_u_formapagamento_idformapagamento?>">
                                        </td>
                                        <td>
                                            <select id="idcontaitem<?=$rp["idformapagamentopessoa"] ?>" class='size15' name="_<?= $i ?>_u_formapagamentopessoa_idcontaitem" vnulo onchange="preencheti(<?= $rp['idformapagamentopessoa'] ?>)">
                                                <option value=""></option>
                                                <? fillselect(getContaItemSelect(), $rp['idcontaitem']); ?>
                                            </select>
                                        </td>
                                        <td>
                                        <?if ($rp['idcontaitem']) {
                                            $sqlit = "select e.idtipoprodserv,t.tipoprodserv
                                                        from contaitemtipoprodserv e 
                                                                join tipoprodserv t on(t.idtipoprodserv=e.idtipoprodserv )
                                                        where e.idcontaitem=" . $rp['idcontaitem'] . " order by t.tipoprodserv";
                                        ?>
                                        <select id="idtipoprodserv<?=$rp["idformapagamentopessoa"] ?>" class='size15' name="_<?= $i ?>_u_formapagamentopessoa_idtipoprodserv" vnulo>
                                            <option value=""></option>
                                            <? fillselect(FormaPagamentoController::buscarFormapagtoContaItemTipoProdservTipoProdServ($rp['idcontaitem']), $rp['idtipoprodserv']); ?>
                                        </select>
                                        <?
                                        } else {
                                            
                                            ?>
                                            <select id="idtipoprodserv<?=$rp["idformapagamentopessoa"] ?>" class='size15' name="_<?= $i ?>_u_formapagamentopessoa_idtipoprodserv" vnulo>
                                                <option value=""></option>
                                                <? fillselect(FormaPagamentoController::$ArrayVazioF, $rp['idtipoprodserv']); ?>
                                            </select>
                                            <?
                                        }
                                        ?>
                                        </td>
                                        <td>                                          
                                            <input  id="previsao_<?= $rp['idformapagamentopessoa'] ?>" name="_<?=$i?>_u_formapagamentopessoa_previsao" class="size7" type="text" value="<?=$rp["previsao"]; ?>">
                                        </td>
                                        <td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="deletarprevisao(<?= $rp['idformapagamentopessoa']?>)" title="Excluir!"></i></td>
                                    </tr>
                                <?}?>
                                </tbody>
                            </table>
                        </div>
                        <?} else { ?>
                            <table>
                                <tr>
                                    <th>
                                        Não existem Previsões Cadastradas para esta Forma de Pagamento.
                                    </th>
                                </tr>
                            </table>
                        <? } ?>                        
                        <table id="tableNovaPrevisao" style="display: none; margin-bottom: 10px;">
                            <tr>
                                <td>Nome:</td>
                                <td style="width: 50%;" >
                                <?if(!empty($_1_u_formapagamento_idpessoa) and $_1_u_formapagamento_agrupfpagamento=='Y' ){?>
                                    <input id="nomeprevisao" type="text" vnulo cbvalue="<?=$_1_u_formapagamento_idpessoa?>" value="<?=traduzid('pessoa','idpessoa','nome',$_1_u_formapagamento_idpessoa)?>">
                                <?}else{?>
                                <input id="nomeprevisao" type="text" vnulo cbvalue="" value="">
                                <?}?>
                                </td>
                                <td</td>
                                <td><input id="valorprevisao" value='0.00' step="0.01" type="hidden"></td>
                                <td><button  type="button" class="btn btn-success btn-xs" onclick="novocadastroprevisao()" title="Salvar"><i class="fa fa-circle"></i>Salvar</button></td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td><i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novaprevisao()" title="Inserir nova Previsão"></i></td>
                            </tr>
                        </table>

                    </div>
                </div>   
                <?}?>
                </div>
  
            </div>
            

        </div>
    </div>
</div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="panel panel-default" style="margin-top: 0px !important;">
            <div class="panel-heading">LPs</div>
            <div class="panel-body"> 
                <table class="table table-striped planilha">
                    <?$sql = "SELECT 
                                l.descricao,
                                e.empresa,
                                e.sigla,
                                l.idlp,
                                l.idempresa AS idempresa,
                                ov.*
                            FROM
                                carbonnovo._lp l
                                    JOIN
                                empresa e ON (l.idempresa = e.idempresa)
                                    JOIN
                                objetovinculo ov ON (ov.idobjeto = l.idlp
                                    AND ov.tipoobjeto = '_lp'
                                    AND ov.tipoobjetovinc = 'formapagamento')
                            WHERE
                                ov.idobjetovinc = ".$_1_u_formapagamento_idformapagamento."
                                    AND l.status = 'ATIVO'
                                    AND e.status = 'ATIVO'
                            ORDER BY l.descricao, l.idempresa;";
                        $empresa = "";
                    $res=d::b()->query($sql);

                        while($rw = mysqli_fetch_assoc($res)){
                            if($empresa != $rw['idlp']){
                                $empresa = $rw['idlp'];?>
                                <tr style="background-color: #cccccc;">
                                    <td colspan="3" style="font-weight: bold; text-align:center;">
                                    <a target="_blank" href="?_modulo=_lp&_acao=u&idlp=<?=$rw['idlp']?>"><?=$rw['empresa']?></a></td>
                                </tr>
                            <?}?>
                            <tr>
                                <td class="hoverazul" >
                                    <?=$rw['descricao']?>
                                </td>
                                <td class="hoverazul" >
                                    <?=$rw['sigla']?>
                                </td>
                                <td>
                                    <?if(array_key_exists("modulomaster", getModsUsr("MODULOS"))){?>
                                        <i class="fa fa-trash vermelho hoverpreto pointer" onclick="excluir('objetovinculo',<?=$rw['idobjetovinculo']?>)" style="margin-left: 6px; margin-right: 0px;"></i>
                                    <?}?>
                                </td>
                            </tr>
                        <?}?>
                        <?if(array_key_exists("modulomaster", getModsUsr("MODULOS"))){?>
                            <tr>
                                <td colspan="3">
                                    <input id='selectlps' style ="display:none" >
                                    <i class="fa fa-plus-circle verde pointer fa-lg" id="mais"></i>
                                </td>
                            </tr>
                        <?}?>
                    </table>
            </div>
        </div>
    </div>    
</div>
<?
if(!empty($_1_u_formapagamento_idformapagamento)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_formapagamento_idformapagamento; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "formapagamento"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

<?/*if(!empty($_1_u_formapagamento_idformapagamento)){?>
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
<?}*/?>
<!--div class="col-md-12">
    <?$tabaud = "formapagamento";?>
    <div class="panel panel-default">		
        <div class="panel-body">
            <div class="row col-md-12">		
                    <div class="col-md-1 nowrap">Criado Por:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadopor"}?></div>
                    <div class="col-md-1 nowrap">Criado Em:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_criadoem"}?></div>   
            </div>
            <div class="row col-md-12">          
                    <div class="col-md-1 nowrap">Alterado Por:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradopor"}?></div>
                    <div class="col-md-1 nowrap">Alterado Em:</div>     
                    <div class="col-md-5"><?=${"_1_u_".$tabaud."_alteradoem"}?></div>       
            </div>
        </div>
    </div>
</div-->

<style>
.desabilitado{
    background-color:#ece5e5 !important;
}    
</style>

<script>
    <?if($_acao == 'i'){?>
        var jAgencia = <?=$jAgencia?>;
        $("[name*=_formapagamento_idagencia]").on('change',function(){
            if(jAgencia !== 0){
                var vthis = $(this).val();
                if(jAgencia.find(o => o.idagencia === vthis)){
                    $(".saldo_agencia").remove();
                    $("#_agencias").append(`
                        <td class="saldo_agencia">
                            Saldo Agência:
                        </td>
                        <td class="saldo_agencia">
                            <input name="x_contasaldo" type="text" value="0,00"/>
                        </td>
                    `);
                }else{
                    $(".saldo_agencia").remove();
                }
            }
        });

    <?}?>
    $(document).ready(function(){
  $("#mais").click(function(){
    $("#selectlps").toggle();
  });
});
var $jLp = <?=getjsonLp( $_1_u_contaitem_idcontaitem );?>;

	$("#selectlps").autocomplete({
        source: $jLp
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.descricao+"</a>").appendTo(ul);
            };
        }
        ,select: function(event, ui){
            CB.post({
                objetos : {
                    "_x_i_objetovinculo_idobjeto":ui.item.idlp
                    ,"_x_i_objetovinculo_tipoobjeto": '_lp'
                    ,"_x_i_objetovinculo_tipoobjetovinc": 'formapagamento'
                    ,"_x_i_objetovinculo_idobjetovinc": $("[name='_1_u_formapagamento_idformapagamento']").val()
                }
                ,parcial: true
            });
        }
    });
    function excluir(tab,inid){
    if(confirm("Deseja retirar este?")){		
        CB.post({
        objetos: "_x_d_"+tab+"_id"+tab+"="+inid
        });
    }
    
}
    function altcheck(vtab,vcampo,vid,vcheck){
            CB.post({
                    objetos: "_x_u_"+vtab+"_id"+vtab+"="+vid+"&_x_u_"+vtab+"_"+vcampo+"="+vcheck        
            }); 
    } 
    
    function altcheckAP(vid,vcheck){
        
        if(vcheck =='Y'){
           var vcheck2='N'; 
           var vcheck1='N';
        }else{
           var vcheck2='N'; 
           var vcheck1='N';
        }
        CB.post({
                objetos: "_x_u_formapagamento_idformapagamento="+vid+"&_x_u_formapagamento_agruppessoa="+vcheck+"&_x_u_formapagamento_agrupfpagamento="+vcheck1+"&_x_u_formapagamento_agrupnota="+vcheck2        
        });
        mostrarPrevisaoPessoa() 
    }  
    
    function altcheckAN(vid,vcheck){
        
        if(vcheck =='Y'){
           var vcheck2='N';
           var vcheck1='N';
        }else{
           var vcheck2='N';
           var vcheck1='N';
        }
        
        CB.post({
                objetos: "_x_u_formapagamento_idformapagamento="+vid+"&_x_u_formapagamento_agrupnota="+vcheck+"&_x_u_formapagamento_agrupfpagamento="+vcheck1+"&_x_u_formapagamento_agruppessoa="+vcheck2        
        });
        mostrarPrevisaoPessoa() 
    }   
    
    function altcheckAF(vid,vcheck){
        
         if(vcheck =='Y'){
           var vcheck2='N'; 
           var vcheck1='N';
        }else{
            var vcheck2='N'; 
            var vcheck1='N';
        }
        
        CB.post({
                objetos: "_x_u_formapagamento_idformapagamento="+vid+"&_x_u_formapagamento_agrupfpagamento="+vcheck+"&_x_u_formapagamento_agrupnota="+vcheck1+"&_x_u_formapagamento_agruppessoa="+vcheck2        
        });
        mostrarPrevisaoPessoa() 
    }
    
if( $("[name=_1_u_formapagamento_idformapagamento]").val() ){
    $(".cbupload").dropzone({
        idObjeto: $("[name=_1_u_formapagamento_idformapagamento]").val()
        ,tipoObjeto: 'formapagamento'
    });
}

<?if($_GET['_acao']=='u'){?>
        jCli=<?=$jCli?>;// autocomplete cliente

        //mapear autocomplete de clientes
        jCli = jQuery.map(jCli, function(o, id) {
            return {"label": o.nome, value:id}
        }); 

        //autocomplete de clientes
        $("#nomeprevisao").autocomplete({
            source: jCli
            ,delay: 0
            ,create: function(){
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                    return $('<li>').append("<a>"+item.label).appendTo(ul);
                };
            }	
        });
<?}?>    

function novocadastroprevisao(){

    let idpessoa=$('#nomeprevisao').attr('cbvalue');
    let previsao=$('#valorprevisao').val();
    let idempresa='<?= cb::idempresa()?>';
    let idformapagamento='<?= $_GET['idformapagamento'] ?>';

    	CB.post({
		objetos:{
            "_1_i_formapagamentopessoa_idempresa":idempresa,
            "_1_i_formapagamentopessoa_idformapagamento":idformapagamento,
            "_1_i_formapagamentopessoa_idpessoa":idpessoa,
            "_1_i_formapagamentopessoa_previsao":previsao
            }
			,parcial:true
			,msgSalvo: "Previsão Cadastrada."
		})

}

function atualizaprevisao(id){
    let updPrevisao=$('#previsao_'+id).val();

        CB.post({
    objetos:{
        "_1_u_formapagamentopessoa_previsao":updPrevisao,
        "_1_u_formapagamentopessoa_idformapagamentopessoa":id
        }
        ,parcial:true
        ,msgSalvo: "Previsão Atualizada."
    })
}

function deletarprevisao(id){
    let updPrevisao=$('#previsao_'+id).val();

        CB.post({
    objetos:{
        "_1_d_formapagamentopessoa_idformapagamentopessoa":id
        }
        ,parcial:true
        ,msgSalvo: "Previsão Excluída."
    })
}

function novaprevisao(){
 $('#tableNovaPrevisao').css('display','')
}


//Filtro Tabela Previsão
$(document).ready(function(){
  $("#pesquisarprevisao").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#listaPrevisao tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

function preencheti(inidnf) {

$("#idtipoprodserv" + inidnf).html("<option value=''>Procurando....</option>");

$.ajax({
    type: "get",
    url: "ajax/buscacontaitem.php",
    data: {
        idcontaitem: $("#idcontaitem" + inidnf).val()
    },

    success: function(data) {
        $("#idtipoprodserv" + inidnf).html(data);
    },

    error: function(objxmlreq) {
        alert('Erro:<br>' + objxmlreq.status);

    }
}) //$.ajax

}

function mostrarPrevisaoPessoa(){
    let check=$('#rdAgrupadoPorPessoa').attr('checked');
    let check2=$('#rdAgrupadoPorPagamento').attr('checked');

    if (check=='checked' || check2=='checked') {
        $('#previsaoPessoa').css('display','')
    } else {
        $('#previsaoPessoa').css('display','none')
    }
}

$(document).ready(function(){
    mostrarPrevisaoPessoa()
});


</script>