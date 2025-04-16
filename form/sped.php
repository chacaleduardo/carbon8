<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");


if($_POST){
    include_once("../inc/php/cbpost.php");
}
?>
<style>

i.tip:hover {
    cursor: hand;
    position: relative
}
i.tip span {
    display: none
}
i.tip:hover span {
    border: #c0c0c0 1px dotted;
    padding: 5px 20px 5px 5px;
    display: block;
    z-index: 100;
    background: #f0f0f0 no-repeat 100% 5%;
    left: 0px;
    margin: 10px;
    width: 580px;
    position: absolute;
    top: 10px;
    text-decoration: none
}

i.tip2:hover {
    cursor: hand;
    position: relative
}
i.tip2 span {
    display: none
}
i.tip2:hover span {
    border: #c0c0c0 1px dotted;
    padding: 5px 20px 5px 5px;
    display: block;
    z-index: 100;
    background: #f0f0f0 no-repeat 100% 5%;
    left: -200px;
    margin: 10px;
    width: 580px;
    position: absolute;
    top: 10px;
    text-decoration: none
}


</style>

<?
if(!empty($_GET['idspedc100'])){

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "spedc100";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idspedc100" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from spedc100 where idspedc100 = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


?>


<?
$_1_u_spedc100_statusbkp=$_1_u_spedc100_status;
if($_1_u_spedc100_status=='CORRIGIDO'){
    $background="background-color: #89cb89 !important";
?>
<script>
$("#cbModuloForm").find('input').not('[name*="namesped"]').prop( "disabled", true );
$("#cbModuloForm").find("select" ).prop( "disabled", true );
$("#cbModuloForm").find("textarea").prop( "disabled", true );
$("#cbSalvar").addClass('hide');
</script>
<?
}
//$_1_u_spedc100_status='CORRIGIDO';

$idfinalidadeprodserv=traduzid('nf', 'idnf', 'idfinalidadeprodserv', $_1_u_spedc100_idnf);
$nnfe=traduzid('nf', 'idnf', 'nnfe', $_1_u_spedc100_idnf);


?>
       <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default" >
                    <div class="panel-heading" style="<?=$background?>"> 
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group col-xs-3 col-md-3">
                                    <label class="text-white">Informações Sped Fiscal - NFe:</label>
                                    <label class="alert-warning d-flex align-items-center form-control"><?=$nnfe?></label>                               
                                </div>
                                <div class="form-group col-xs-3 col-md-3">
                                <label class="text-white">NF:</label>
                                    <label class="alert-warning d-flex align-items-center form-control flex-between">
                                        <?=$_1_u_spedc100_idnf?>
                                        <a title="NFe" class="fa fa-bars fade pointer hoverazul" href="?_modulo=nfentrada&_acao=u&idnf=<?= $_1_u_spedc100_idnf ?>" target="_blank"></a>
                                    </label>
                                </div>
                                <div class="form-group col-xs-1 col-md-1">
                                    <a class="fa fa-print btn-lg pointer hoverazul" title="Danfe"  onclick="janelamodal('../inc/nfe/sefaz4/func/printDANFE.php?idnotafiscal=<?=$_1_u_spedc100_idnf?>')"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body" >
                        <table>
                            <tr>
                                <!-- td align="right">Finalidade:</td>
                                <td colspan="4">
                                    <input name="idnf" id="idnf"	type="hidden" value="<?=$_1_u_spedc100_idnf?>"	readonly='readonly'>
                                    <select id="idfinalidadeprodserv" class='size30' vnulo <?if($_1_u_spedc100_statusbkp!='CORRIGIDO'){?> onchange="atualizafinalidade(this)"<?}?>>
                                        <option value=""></option>
                                        <?fillselect("select c.idfinalidadeprodserv,c.finalidadeprodserv
                                                from finalidadeprodserv c
                                                where c.status='ATIVO'
                                                ".getidempresa('c.idempresa','finalidadeprodserv')."
                                                order by c.finalidadeprodserv",$idfinalidadeprodserv);?>
                                    </select>
                                </td !-->
                                <td align="right">Conferido (Contabilidade):</td>
                                <td>
                                    <?
                                    if($_1_u_spedc100_status=='CORRIGIDO'){
                                        $checked='checked';
                                        $vchecked='ATIVO';					    
                                    }else{
                                        $checked='';
                                        $vchecked='CORRIGIDO';
                                    }				
                                    ?>
                                    <input title="Validado" type="checkbox" <?=$checked?> name="namesped" onclick="altcheck('spedc100','status',<?=$_1_u_spedc100_idspedc100?>,'<?=$vchecked?>')">
                                </td>
                            </tr>
                        </table>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default" >
                                <div class="panel-heading" style="<?=$background?>">Nota Fiscal - C100</div>
                                <div class="panel-body" >
                                <table  class="table table-striped planilha">
                                    <tr>
                                        <th>Emissão</th>
                                        <th>Entrada</th>
                                        <th>Valor Documento</th>
                                        <th>Valor Desconto</th>
                                        <th>Valor Mercadorias</th>
                                        <th class="nowrap">Tipo Frete
                                            <i class="fa fa-info-circle azul pointer hoverpreto tip" >
                                                <span>
                                                    <ul>
                                                    <li>0-Contratação do frete por conta do Remetente (CIF)
                                                    <li>1-Contratação do frete por conta do Destinatário (FOB)
                                                    <li>2-Contratação do Frete por conta de terceiros
                                                    <li>3-Transporte Próprio por conta do Rementente
                                                    <li>4-Transporte Próprio por conta do Destinatário
                                                    <li>9-Sem Ocorrência de Transporte
                                                    </ul>
                                                </span>
                                            </i>
                                        </th>
                                        <th>Frete</th>
                                        <th>Seguro</th>
                                        <th>Outros</th>
                                        <th>Base Calculo</th>
                                        <th>ICMS</th>
                                        <th>IPI</th></th>
                                        <th>PIS</th>
                                        <th>Cofins</th>
                                        <th></th>
                                    </tr>

                                    <tr>
                                        <td>
                                            <input name="_1_u_spedc100_vdemi" size= "8" type="text" value="<?=$_1_u_spedc100_vdemi?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vdentrada" size= "8" type="text" value="<?=$_1_u_spedc100_vdentrada?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_idspedc100" size= "8" type="hidden" value="<?=$_1_u_spedc100_idspedc100?>">
                                            <input name="_1_u_spedc100_vvnf" size= "8" type="text" value="<?=$_1_u_spedc100_vvnf?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvdesc" size= "8" type="text" value="<?=$_1_u_spedc100_vvdesc?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvprod" size= "8" type="text" value="<?=$_1_u_spedc100_vvprod?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vmodfrete" size= "8" type="text" value="<?=$_1_u_spedc100_vmodfrete?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvfrete" size= "8" type="text" value="<?=$_1_u_spedc100_vvfrete?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvseg" size= "8" type="text" value="<?=$_1_u_spedc100_vvseg?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvoutro" size= "8" type="text" value="<?=$_1_u_spedc100_vvoutro?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvbc" size= "8" type="text" value="<?=$_1_u_spedc100_vvbc?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvticms" size= "8" type="text" value="<?=$_1_u_spedc100_vvticms?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvipi" size= "8" type="text" value="<?=$_1_u_spedc100_vvipi?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvpis" size= "8" type="text" value="<?=$_1_u_spedc100_vvpis?>">
                                        </td>
                                        <td>
                                            <input name="_1_u_spedc100_vvcofins" size= "8" type="text" value="<?=$_1_u_spedc100_vvcofins?>">
                                        </td>
                                      
                                    </tr>
                    
                                </table> 
                                </div>
                                </div>
                            </div>
                        </div>
<?
            $sqlsc170="select * from spedc170 where idnf = ".$_1_u_spedc100_idnf." and status='ATIVO'";
            $resc170=d::b()->query($sqlsc170) or die($sqlsc170." erro ao buscar informações do bloco C170".mysqli_error());
            $qtdc170=mysqli_num_rows($resc170);

            $sqlsc190="select * from spedc190 where idnf = ".$_1_u_spedc100_idnf." and status='ATIVO'";
            $resc190=d::b()->query($sqlsc190) or die($sqlsc170." erro ao buscar informações do bloco C190".mysqli_error());
            $qtdc190=mysqli_num_rows($resc190);

            $sqlsc101="select * from spedc101 where idnf = ".$_1_u_spedc100_idnf." and status='ATIVO'";
            $resc101=d::b()->query($sqlsc101) or die($sqlsc101." erro ao buscar informações do bloco C101".mysqli_error());
            $qtdc101=mysqli_num_rows($resc101);

?>
            <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default" >
                    <div class="panel-heading">Itens -C170</div>
                    <div class="panel-body" >
                    <table  class="table table-striped planilha">
                        <tr>
                            <th>Descrição</th>
                            <th>NCM</th>
                            <th>Qtd</th>
                            <th>Valor</th>
                            <th>Desc</th>                            
                            <th>CST/ICMS</th>
                            <th>CFOP</th>
                            <th>BC ICMS</th>
                            <th>Aliq ICMS</th>
                            <th>ICMS</th>
                            <th class="nowrap">CST/IPI                                
                                <i class="fa fa-info-circle azul pointer hoverpreto tip" >
                                    <span>
                                        <ul>
                                       <li>00-Entrada com recuperação de crédito
                                       <li>01-Entrada tributada com alíquota zero
                                       <li>02-Entrada isenta
                                       <li>03-Entrada não tributada
                                       <li>04-Entrada imune
                                       <li>05-Entrada com suspensão
                                       <li>49-Outras Entradas
                                       <li>50-Saída tributada
                                       <li>51-Saída tributável com alíquota zero
                                       <li>52-Saída isenta
                                       <li>53-Saída não tributada
                                       <li>54-Saída imune
                                       <li>55-Saída com suspensão
                                       <li>99-Outras saídas
                                        </ul>
                                    </span>
                                </i>
                            </th>
                            <th>Aliq IPI</th>
                            <th>BC IPI</th>
                            <th>IPI</th>
                            <th class="nowrap">CST/PIS
                            <i class="fa fa-info-circle azul pointer hoverpreto tip" >
                                    <span>
                                        <ul>
                                            <li>01-Op. Tributável com Alíquota Básica
                                            <li>02-Op. Tributável com Alíquota Diferenciada
                                            <li>03-Op. Tributável com Alíquota por Unidade de Medida de Produto
                                            <li>04-Op. Tributável Monofásica - Revenda a Alíquota Zero
                                            <li>05-Op. Tributável por Substituição Tributária
                                            <li>06-Op. Tributável a Alíquota Zero
                                            <li>07-Op. Isenta da Contribuição
                                            <li>08-Op. sem Incidência da Contribuição
                                            <li>09-Op. com Suspensão da Contribuição
                                            <li>49-Outras Operações de Saída
                                            <li>50-Op. com Direito a Crédito - Vinc. Excl. a Receita Trib. no Mercado Interno
                                            <li>51-Op. com Direito a Crédito – Vinc. Excl. a Receita Não Trib. no Mercado Interno
                                            <li>52-Op. com Direito a Crédito - Vinc. Excl. a Receita de Exportação
                                            <li>53-Op. com Direito a Crédito - Vinc. a Receitas Trib. e Não-Trib. no Mercado Interno
                                            <li>54-Op. com Direito a Crédito - Vinc. a Receitas Trib. no Mercado Interno e de Exportação
                                            <li>55-Op. com Direito a Crédito - Vinc. a Receitas Não-Trib. no Mercado Interno e de Exportação
                                            <li>56-Op. com Direito a Crédito - Vinc. a Receitas Trib. e Não-Trib. no Mercado Interno, e de Exp.
                                            <li>60-Crédito Presumido - Aquisição Vinc. Excl. a Receita Trib. no Mercado Interno
                                            <li>61-Crédito Presumido - Aquisição Vinc. Excl. a Receita Não-Trib. no Mercado Interno
                                            <li>62-Crédito Presumido - Aquisição Vinc. Excl. a Receita de Exportação
                                            <li>63-Crédito Presumido - Aquisição Vinc. a Receitas Trib. e Não-Trib. no Mercado Interno
                                            <li>64-Crédito Presumido - Aquisição Vinc. a Receitas Trib. no Mercado Interno e de Exportação
                                            <li>65-Crédito Presumido - Aquisição Vinc. a Receitas Não-Trib. no Mercado Interno e de Exportação
                                            <li>66-Crédito Presumido - Aquisição Vinc. a Receitas Trib. e Não-Trib. no Mercado Interno, e de Exp.
                                            <li>67-Crédito Presumido - Outras Operações
                                            <li>70-Op. de Aquisição sem Direito a Crédito
                                            <li>71-Op. de Aquisição com Isenção
                                            <li>72-Op. de Aquisição com Suspensão
                                            <li>73-Op. de Aquisição a Alíquota Zero
                                            <li>74-Op. de Aquisição sem Incidência da Contribuição
                                            <li>75-Op. de Aquisição por Substituição Tributária
                                            <li>98-Outras Operações de Entrada
                                            <li>99-Outras Operações
                                        </ul>
                                    </span>
                            
                                </i>

                            </th>
                            <th>BC PIS</th>
                            <th>Aliq PIS</th>
                            <th>PIS</th>
                            <th class='nowrap'>CST/Cofins
                            <i class="fa fa-info-circle azul pointer hoverpreto tip2" >
                                    <span>
                                        <ul>
                                            <li>01-Operação Tributável com Alíquota Básica
                                            <li>02-Operação Tributável com Alíquota Diferenciada
                                            <li>03-Operação Tributável com Alíquota por Unidade de Medida de Produto
                                            <li>04-Operação Tributável Monofásica - Revenda a Alíquota Zero
                                            <li>05-Operação Tributável por Substituição Tributária
                                            <li>06-Operação Tributável a Alíquota Zero
                                            <li>07-Operação Isenta da Contribuição
                                            <li>08-Operação sem Incidência da Contribuição
                                            <li>09-Operação com Suspensão da Contribuição
                                            <li>49-Outras Operações de Saída
                                            <li>50-Op. com Direito a Créd. - Vinc. Excl. a Rec. Trib. no Merc. Int.
                                            <li>51-Op. com Direito a Créd. - Vinc. Excl. a Rec. Não-Trib. no Merc. Int.
                                            <li>52-Op. com Direito a Créd. - Vinc. Excl. a Rec. de Exportação
                                            <li>53-Op. com Direito a Créd. - Vinc. a Rec. Trib. e Não-Trib. no Merc. Int.
                                            <li>54-Op. com Direito a Créd. - Vinc. a Rec. Trib. no Merc. Int. e de Exp.
                                            <li>55-Op. com Direito a Créd. - Vinc. a Rec. Não Trib. no Merc. Int. e de Exp.
                                            <li>56-Op. com Direito a Créd. - Vinc. a Rec. Trib. e Não-Trib. no Merc. Int. e de Exp.
                                            <li>60-Créd. Presumido - Aq. Vinc. Excl. a Rec. Trib. no Merc. Int.
                                            <li>61-Créd. Presumido - Aq. Vinc. Excl. a Rec. Não-Trib. no Merc. Int.
                                            <li>62-Créd. Presumido - Aq. Vinc. Excl. a Rec. de Exp.
                                            <li>63-Créd. Presumido - Aq. Vinc. a Rec. Trib. e Não-Trib. no Merc. Int.
                                            <li>64-Créd. Presumido - Aq. Vinc. a Rec. Trib. no Merc. Int. e de Exp.
                                            <li>65-Créd. Presumido - Aq. Vinc. a Rec. Não-Trib. no Merc. Int. e de Exp.
                                            <li>66-Créd. Presumido - Aq. Vinc. a Rec. Trib. e Não-Trib. no Merc. Int. e de Exp.
                                            <li>67-Créd. Presumido - Outras Operações
                                            <li>70-Operação de Aq. sem Direito a Créd.
                                            <li>71-Operação de Aq. com Isenção
                                            <li>72-Operação de Aq. com Suspensão
                                            <li>73-Operação de Aq. a Alíquota Zero
                                            <li>74-Operação de Aq. sem Incidência da Contribuição
                                            <li>75-Operação de Aq. por Substituição Tributária
                                            <li>98-Outras Operações de Entrada
                                            <li>99-Outras Operações
                                        </ul>
                                    </span>
                            
                                </i>

                            </th>
                            <th>BC/Cofins</th>
                            <th>Aliq/Cofins</th>
                            <th>Cofins</th>                            
                        </tr>
                        <?
                        $i=1;
                        while($rowc170=mysqli_fetch_assoc($resc170)){
                            $i=$i+1;    
                        ?>
                            <tr>
                                <td><?=$rowc170['vxprod']?></td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_ncm" size= "8" type="text" value="<?=$rowc170['ncm']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_idspedc170" size= "8" type="hidden" value="<?=$rowc170['idspedc170']?>">
                                    <input name="_<?=$i?>_u_spedc170_vqcom" size= "8" type="text" value="<?=$rowc170['vqcom']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vlrtotalitem" size= "8" type="text" value="<?=$rowc170['vlrtotalitem']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vvdesc" size= "8" type="text" value="<?=$rowc170['vvdesc']?>">
                                </td>
                               
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_csticms" size= "8" type="text" value="<?=$rowc170['csticms']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vcfop" size= "8" type="text" value="<?=$rowc170['vcfop']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vvbcicms" size= "8" type="text" value="<?=$rowc170['vvbcicms']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vpicms" size= "8" type="text" value="<?=$rowc170['vpicms']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vvicms" size= "8" type="text" value="<?=$rowc170['vvicms']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vcstipi" size= "8" type="text" value="<?=$rowc170['vcstipi']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vpipi" size= "8" type="text" value="<?=$rowc170['vpipi']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vbcipi" size= "8" type="text" value="<?=$rowc170['vbcipi']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vipi" size= "8" type="text" value="<?=$rowc170['vipi']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vcstpis" size= "8" type="text" value="<?=$rowc170['vcstpis']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vvbcpis" size= "8" type="text" value="<?=$rowc170['vvbcpis']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vppis" size= "8" type="text" value="<?=$rowc170['vppis']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vvpisitem" size= "8" type="text" value="<?=$rowc170['vvpisitem']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vcstcofins" size= "8" type="text" value="<?=$rowc170['vcstcofins']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vvbccofins" size= "8" type="text" value="<?=$rowc170['vvbccofins']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vpcofins" size= "8" type="text" value="<?=$rowc170['vpcofins']?>">
                                </td>
                                <td>
                                    <input name="_<?=$i?>_u_spedc170_vvcofinsitem" size= "8" type="text" value="<?=$rowc170['vvcofinsitem']?>">
                                </td>                                
                            </tr>
                        <?}?>
                    </table>
                    </div>
                </div>
            </div>
            </div>
            <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default" >
                    <div class="panel-heading">Analítico - C190</div>
                    <div class="panel-body" >
                    <table  class="table table-striped planilha">
                    <tr>
                        <th>CST</th>
                        <th>CFOP</th>
                        <th>Aliq ICMS</th>
                        <th>Valor</th>
                        <th>Bc ICMS</th>
                        <th>ICMS</th>
                        <th>Red BC</th>
                        <th>IPI</th>
                    </tr>
                    <?while($rowc190=mysqli_fetch_assoc($resc190)){
                        $i=$i+1;    
                    ?>
                        <tr>
                            <td>
                                <input name="_<?=$i?>_u_spedc190_idspedc190" size= "8" type="hidden" value="<?=$rowc190['idspedc190']?>">
                                <input name="_<?=$i?>_u_spedc190_st" size= "8" type="text" value="<?=$rowc190['st']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc190_cfop" size= "8" type="text" value="<?=$rowc190['cfop']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc190_aliqicms" size= "8" type="text" value="<?=$rowc190['aliqicms']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc190_vlopr" size= "8" type="text" value="<?=$rowc190['vlopr']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc190_vlbcicms" size= "8" type="text" value="<?=$rowc190['vlbcicms']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc190_vlicms" size= "8" type="text" value="<?=$rowc190['vlicms']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc190_vlredbc" size= "8" type="text" value="<?=$rowc190['vlredbc']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc190_vlipi" size= "8" type="text" value="<?=$rowc190['vlipi']?>">                             
                            </td>
                        </tr>
                    <?}?>
                    </table>
                    </div>
                </div>
            </div>
            <?if($qtdc101>0){?>
            <div class="col-md-6">
                <div class="panel panel-default" >
                    <div class="panel-heading">Diferencial de aliquota - C101</div>
                    <div class="panel-body" >
                    <table  class="table table-striped planilha">
                    <tr>
                        <th>Fundo à Pobreza</th>
                        <th>ICMS UF Destino</th>
                        <th>ICMS UF Remetente</th>                   
                    </tr>
                    <?while($rowc101=mysqli_fetch_assoc($resc101)){
                        $i=$i+1;    
                    ?>
                        <tr>
                            <td>
                                <input name="_<?=$i?>_u_spedc101_idspedc101" size= "8" type="hidden" value="<?=$rowc101['idspedc101']?>">
                                <input name="_<?=$i?>_u_spedc101_vvfcpufdest" size= "8" type="text" value="<?=$rowc101['vvfcpufdest']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc101_vvicmsufdest" size= "8" type="text" value="<?=$rowc101['vvicmsufdest']?>">                             
                            </td>
                            <td>
                                <input name="_<?=$i?>_u_spedc101_vvicmsufremet" size= "8" type="text" value="<?=$rowc101['vvicmsufremet']?>">                             
                            </td>                            
                        </tr>
                    <?}?>
                    </table>
                    </div>
                </div>
            </div>
                    <?}?>
            </div>

                    </div>
                </div>
            </div>   
        </div> 
<?
    $tabaud = "spedc100"; //pegar a tabela do criado/alterado em antigo
    require 'viewCriadoAlterado.php';
}else{

    
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "spedd100";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idspedd100" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from spedd100 where idspedd100 = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$i=1;
?>


<?
$_1_u_spedd100_statusbkp=$_1_u_spedd100_status;
if($_1_u_spedd100_status=='CORRIGIDO'){
    $background="background-color: #89cb89 !important";
}
//$_1_u_spedc100_status='CORRIGIDO';


$nnfe=traduzid('nf', 'idnf', 'nnfe', $_1_u_spedd100_idnf);


?>
       <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default" >
                    <div class="panel-heading" style="<?=$background?>">Informações Sped Fiscal - CTe <label class="alert-warning"><?=$nnfe?></label>
                        &nbsp; &nbsp; &nbsp; <a class="fa fa-print btn-lg pointer hoverazul" title="Danfe"  onclick="janelamodal('../inc/cte/vendor/nfephp-org/sped-da/functions/printcte.php?idnf=<?=$_1_u_spedd100_idnf?>')"></a>
                    </div>
                    <div class="panel-body" >
                        <table>
                            <tr>
                              
                                <td align="right">Validado:</td>
                                <td>
                                    <?
                                    if($_1_u_spedd100_status=='CORRIGIDO'){
                                        $checked='checked';
                                        $vchecked='ATIVO';					    
                                    }else{
                                        $checked='';
                                        $vchecked='CORRIGIDO';
                                    }				
                                    ?>
                                    <input title="Validado" type="checkbox" <?=$checked?> name="namesped" onclick="altcheck('spedd100','status',<?=$_1_u_spedd100_idspedd100?>,'<?=$vchecked?>')">
                                </td>
                            </tr>
                        </table>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default" >
                                <div class="panel-heading" style="<?=$background?>">CTE - D100</div>
                                <div class="panel-body" >
                                <table  class="table table-striped planilha">
                                <tr>
                                    <th>CTe</th>
                                    <th>Chave</th>
                                    <th>Valor</th>
                                    <th>BC ICMS</th>
                                    <th>ICMS</th>
                                    <th>Red BC</th>
                                    <th>Municipio Ini</th>
                                    <th>Municipio Fim</th>
                                </tr>
                                <tr>
                                    <td>
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_idspedd100" size= "8" type="hidden" value="<?=$_1_u_spedd100_idspedd100?>">
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_vnct" class="size5" type="text" value="<?=$_1_u_spedd100_vnct?>">
                                    </td>
                                    <td>
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_vchcte" class="size30" type="text" value="<?=$_1_u_spedd100_vchcte?>">
                                    </td>
                                    <td>
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_vvtprest" class="size5" type="text" value="<?=$_1_u_spedd100_vvtprest?>">
                                    </td>
                                    <td>
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_vvbcicms" class="size5" type="text" value="<?=$_1_u_spedd100_vvbcicms?>">
                                    </td>
                                    <td>
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_vvbcicms" class="size5" type="text" value="<?=$_1_u_spedd100_vvbcicms?>">
                                    </td>
                                    <td>
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_vlredbc" class="size5" type="text" value="<?=$_1_u_spedd100_vlredbc?>">
                                    </td>
                                    <td>
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_vcmunini" class="size5" type="text" value="<?=$_1_u_spedd100_vcmunini?>">
                                    </td>
                                    <td>
                                        <input <?=$readonly?> name="_<?=$i?>_u_spedd100_vcmunfim" class="size5" type="text" value="<?=$_1_u_spedd100_vcmunfim?>">
                                    </td>
                                </tr>
                                </table>
                                </div>
                                </div>
                            </div>
                        </div>

                <?
                    $sqsped190="SELECT * FROM spedd190 where idnf=".$_1_u_spedd100_idnf." and status in ('ATIVO')";
                    $resped190=d::b()->query($sqsped190) or die("Falha ao buscar registro do sped d100 sql=".$sqsped190);
                    $qtdd190=mysqli_num_rows($resped190);
                    if($qtdd190>0){
                        $rd190=mysqli_fetch_assoc($resped190);
                        $i=$i+1;
                    ?>
                    <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default" >
                                <div class="panel-heading" style="<?=$background?>">CTE - D190</div>
                                <div class="panel-body" >
                                    <table  class="table table-striped planilha">                           
                                    <tr>
                                        <th>CST</th>
                                        <th>CFOP</th>
                                        <th>Valor</th>
                                        <th>Aliq ICMS</th>                            
                                        <th>BC ICMS</th>
                                        <th>ICMS</th>
                                        <th>Red BC</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input <?=$readonly?> name="_<?=$i?>_u_spedd190_idspedd190" size= "8" type="hidden" value="<?=$rd190["idspedd190"];?>">
                                            <input <?=$readonly?> name="_<?=$i?>_u_spedd190_csticms" class="size5" type="text" value="<?=$rd190["csticms"];?>">
                                        </td>
                                        <td><input <?=$readonly?> name="_<?=$i?>_u_spedd190_vcfop" class="size5" type="text" value="<?=$rd190["vcfop"];?>"></td>
                                        <td><input <?=$readonly?> name="_<?=$i?>_u_spedd190_vvtprest" class="size5" type="text" value="<?=$rd190["vvtprest"];?>"></td>
                                        <td><input <?=$readonly?> name="_<?=$i?>_u_spedd190_vpicms" class="size5" type="text" value="<?=$rd190["vpicms"];?>"></td>
                                        <td><input <?=$readonly?> name="_<?=$i?>_u_spedd190_vvbcicms" class="size5" type="text" value="<?=$rd190["vvbcicms"];?>"></td>
                                        <td><input <?=$readonly?> name="_<?=$i?>_u_spedd190_vvicms" class="size5" type="text" value="<?=$rd190["vvicms"];?>"></td>
                                        <td><input <?=$readonly?> name="_<?=$i?>_u_spedd190_vlredbc" class="size5" type="text" value="<?=$rd190["vlredbc"];?>"></td>
                                    </tr>
                                    </table>
                                </div>
                                </div>
                            </div>
                    </div>

                    <?
                    }
                    ?>



                    </div>
                </div>
            </div>
       </div>
<?
    $tabaud = "spedD100"; //pegar a tabela do criado/alterado em antigo
    require 'viewCriadoAlterado.php';
    }

?>     

<script>

            
function atualizafinalidade(vthis){
  debugger;           
    CB.post({
        objetos: "_atf_u_nf_idnf="+$("#idnf").val()+"&_atf_u_nf_idfinalidadeprodserv="+$(vthis).val()
        ,parcial:true  
        
        ,posPost: function(resp,status,ajax){
            gerainfsped();
        }
    });
}

function gerainfsped(){

    var idnotafiscal = $("#idnf").val();

    vurl = "inc/php/gerainfsped.php?idnf="+idnotafiscal;
    
    $.ajax({
        type: "get",
        url : vurl,
        success: function(data){
            //alert(data);
            //document.location.reload();
            gerainfspedfiscal()
        },
        error: function(objxmlreq){
            alert('Erro:\n'+objxmlreq.status); 
        }
    })//$.ajax
}

function gerainfspedfiscal(){

    var idnotafiscal = $("#idnf").val();

    vurl = "inc/php/gerainfspedfiscal.php?idnf="+idnotafiscal;

    $.ajax({
        type: "get",
        url : vurl,
        success: function(data){
            alert(data);
            document.location.reload();
        },
        error: function(objxmlreq){
            alert('Erro:\n'+objxmlreq.status); 
        }
    })//$.ajax

}
function altcheck(vtab,vcampo,vid,vcheck){
    CB.post({
        objetos: "_x_u_"+vtab+"_id"+vtab+"="+vid+"&_x_u_"+vtab+"_"+vcampo+"="+vcheck        
    }); 
}

</script>