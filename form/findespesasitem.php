<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}
################################################## Atribuindo o resultado do metodo GET
$vencimento_1       = $_GET["vencimento_1"];
$vencimento_2       = $_GET["vencimento_2"];
$idtipoprodserv     = $_GET["idtipoprodserv"];
$idcontaitem        = $_GET["idcontaitem"];
$pesquisa           = $_GET["pesquisa"];
$tiponf             = $_GET["tiponf"];
$status             = $_GET["status"];
$modo               = $_GET["modo"];
$idagencia          = $_GET["idagencia"];
$idunidade          = $_GET["idunidade"];

if (empty($_GET["idempresa"])) {
    $idempresa = $_SESSION['SESSAO']['IDEMPRESA'];
} else {
    $idempresa = $_GET["idempresa"];
}

$sql = " select * from pessoa where flgsocio='Y' and idpessoa=" . $_SESSION["SESSAO"]["IDPESSOA"];
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor = mysqli_num_rows($res);

?>
<style>
   button.btn.dropdown-toggle.btn-default{
        width:350px;
    } 
    @media print {
		.hideprint{
			display: none !important;
		}
	}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Filtros para Listagem </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12 ">
                        <table>
                       
                            <tr>
                                <td class="rotulo">Período entre:</td>
                                <td><input autocomplete="off" name="vencimento_1" vpar="" id="vencimento_1" class="calendario" size="10" style="width: 160px;" value="<?= $vencimento_1 ?>" autocomplete="off"></td>
                                <td>
                                    <font class="9graybold">&nbsp;e&nbsp;</font>
                                </td>
                                <td><input autocomplete="off" name="vencimento_2" vpar="" id="vencimento_2" class="calendario" size="10" style="width: 160px;" value="<?= $vencimento_2 ?>" autocomplete="off"></td>
                            </tr>
                                 <tr>
                                <td align="right" class="rotulo">Empresa:</td>
                              
                                <td colspan="3">
                                    <?
                                
                                    $arrvalor= explode(',',$idempresa);
                                    $sqlm = "SELECT e.idempresa as id, e.nomefantasia as valor
                                            FROM empresa as e 
                                            WHERE exists (SELECT 1 FROM matrizconf m WHERE m.idmatriz =".cb::idempresa()." AND m.idempresa=e.idempresa) 
                                            AND e.status='ATIVO'
                                            UNION
                                                (SELECT idempresa as id, nomefantasia as valor FROM empresa WHERE idempresa =".cb::idempresa().")
                                            ORDER BY valor";
                                    ?>
                                    <select style="width:350px" id="_empresa" class="selectpicker valoresselect" multiple="multiple" data-live-search="true">
                                        <?
                                        $resm =  d::b()->query($sqlm)  or die("Erro drompt Drop sql:" . $sqlm);
                                        while ($rowm = mysqli_fetch_assoc($resm)) {
                                            if (in_array($rowm['id'], $arrvalor)) {
                                                $selected = 'selected';
                                            } else {
                                                $selected = '';
                                            }
                                            echo '<option data-tokens="' . retira_acentos($rowm['valor']) . '" value="' . $rowm['id'] . '" ' . $selected . ' >' . $rowm['valor'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Agência:</td>
                              
                                <td colspan="3">

                                    <style type="text/css">
                                        .dropdown-menu{min-height:0 !important;}
                                        ul.dropdown-menu{min-height:0 !important;}
                                        .nomin{min-height:0 !important;}

                                    </style>
                                <?
                                    if(count(getModsUsr("AGENCIAS")) > 0) {$agencias = getModsUsr("AGENCIAS"); } else {$agencias = "''";}
                                    $sql = "
                                                select  idagencia, idempresa, agencia  From agencia a
                                        where status = 'ATIVO' and exists (Select 1 from objetovinculo o where o.tipoobjetovinc = 'agencia' and o.idobjetovinc = a.idagencia and o.tipoobjeto = '_lp' and o.idobjeto in 
                                        (".getModsUsr("LPS").")) order by agencia;";
                                    $resx = d::b()->query($sql) or die($sql1."Erro ao buscar AGENCIAS: <br>".mysqli_error() . "<p>SQL: $sql");
                                    
                                ?>
                                    <select style="width:350px" name="idagenciax" id="idagenciax" class="selectpicker" multiple>
                                    <?
                                        $arrvalor= explode(',',$idagencia);
                                        while ($rowx = mysqli_fetch_assoc($resx)){
                                            if (in_array($rowx['idagencia'], $arrvalor)) {
                                                $selected = 'selected';
                                            } else {
                                                $selected = '';
                                            } 
                                        ?>
                                        <option data-idempresa="<?=$rowx['idempresa']?>" value="<?=$rowx['idagencia']?>" class="<?=$rowx['idempresa']?> hider" <?=$selected?>><?=$rowx['agencia']?></option>  
                                    <? } ?>
                                    </select>
                                </td>
                            </tr>
                              <tr>
                                <td align="right">Unidade:</td>
                               
                                <td colspan="3">
                                    <select name="idunidade" id="idunidade" style="width:350px">
                                        <option value=""></option>
                                        <? fillselect("SELECT e.idunidade as id, e.unidade as valor
                                            FROM unidade as e 
                                            WHERE exists (SELECT 1 FROM matrizconf m WHERE m.idmatriz =".cb::idempresa()." AND m.idempresa=e.idempresa) 
                                            AND e.status='ATIVO'
                                            UNION
                                                (SELECT idunidade as id, unidade as valor FROM unidade WHERE idempresa =".cb::idempresa().")
                                            ORDER BY valor", $idunidade); ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Categoria:</td>
                               
                                <td colspan="3">
                                    <select style="width:350px" name="idcontaitem"  id="picker"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                                    <?$arcontaitem = explode(',',$idcontaitem);  

                                    
                                        $sqlm="SELECT distinct
                                        c.idcontaitem,c.contaitem
                                        FROM contaitem c
                                        JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND ov.tipoobjetovinc = 'contaitem' AND ov.idobjeto in (".getModsUsr("LPS").") AND ov.tipoobjeto = '_lp'
                                            WHERE c.status = 'ATIVO'
                                            ".share::otipo('cb::usr')::contaitemlp("c.idcontaitem")."
                                            order by contaitem";
                                        $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 1 sql:".$sqlm);
                                        while ($rowm = mysqli_fetch_assoc($resm)) {
                                            if (in_array($rowm['idcontaitem'],$arcontaitem)){
                                                    $selected= 'selected';
                                            }else{
                                                    $selected= '';
                                            }

                                            echo '<option data-tokens="'.retira_acentos($rowm['contaitem']).'" value="'.$rowm['idcontaitem'].'" '.$selected.' >'.$rowm['contaitem'].'</option>'; 
                                        }?>
                                    </select> 
                                </td>
                            </tr>
                            <!-- tr>
                                <td align="right"> Subcategoria:</td>
                                
                                <td colspan="3">
                                    <select style="width:350px" name="idtipoprodserv" id="idtipoprodserv">
                                        <option value=""></option>
                                        <? fillselect("select idtipoprodserv,tipoprodserv from tipoprodserv where status='ATIVO' order by tipoprodserv", $idtipoprodserv); ?>
                                    </select>
                                </td>
                            </tr -->
                          

                            <tr>
                                <td align="right">Modo:</td>
                            
                                <td colspan="3"><select style="width:350px" name="modo">
                                        <?
                                        $sql2 = " SELECT 'FL','Fluxo de Caixa' UNION SELECT 'FT','Faturamento' ";
                                        fillselect($sql2, $modo);
                                        ?>
                                    </select></td>
                            </tr>
                               <tr>
                                <td align="right">Tipo:</td>
                               
                                <td colspan="3"><select style="width:350px" name="tiponf">
                                        <?
                                        $sql2 = " SELECT '','- Todos - ' UNION 
                                        
                                        select 'R','RH' 
									union select 'D','Sócios' 
									union select 'C','Danfe'  
									union select 'S','Serviço'  
									union select 'T','CTe' 
									union select 'E','Concessionária' 
                                    union select 'M','Guia/Cupom'
                                    union select 'O','Outros'
                                    union select 'B','Recibo' ";
                                        fillselect($sql2, $tiponf);
                                        ?>
                                    </select></td>
                            </tr>
                            <tr>
                                <td align="right">Pesquisa:</td>
                              
                                <td colspan="3"><select style="width:350px" name="pesquisa">
                                        <?
                                        $sql2 = " SELECT 'simples','Simples' UNION SELECT 'detalhe','Detalhada' UNION SELECT 'detalheitem','Detalhada Itens' ";
                                        fillselect($sql2, $pesquisa);
                                        ?>
                                    </select></td>
                            </tr>
                        </table>
                    </div>    
                    <div class="col-md-12 text-right">
                        <button  class="btn btn-default btn-primary" onclick="imprimirResumo(this)">
                                <i class="fa fa-print"></i>Imprimir Resumo
                        </button>
                        <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
                                <i class="fa fa-search"></i>Pesquisar
                        </button>
                        <? if ($_GET and !empty($vencimento_1) and !empty($vencimento_2)) {
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
                            $full_url = $protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . "&csv=1";
                        ?>
                            <button class="btn btn-default btn-dark">
                                <a href="<?= $full_url ?>" title="Fazer download CSV">
                                    <i class="fa fa-file-excel-o"> Exportar CSV</i>
                                </a>
                            </button>
                        <? } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?
/*
 * colocar condição para executar select
 */
if ($_GET and !empty($vencimento_1) and !empty($vencimento_2)) {

    //die("Valores não escontrados nesta configuração");

    if ($modo == 'FT') {

        if (!empty($vencimento_1) or !empty($vencimento_2)) {
            $dataini = validadate($vencimento_1);
            $datafim = validadate($vencimento_2);

            if ($dataini and $datafim) {
                $clausulac .= " and (n.dtemissao  BETWEEN '" . $dataini . " 00:00:00' and '" . $datafim . " 23:59:59')" . "  ";
            } else {
                die("Datas n&atilde;o V&aacute;lidas!");
            }
        }

        if(!empty($idunidade)){
            $clausulaun .= " and n.idunidade=".$idunidade." ";
        }

        if (!empty($idcontaitem)) {
            $stridcontaitem = " and c.idcontaitem in (" . $idcontaitem . ") ";
        } else {
            $stridcontaitem = "";
        }
        
        if (!empty($tiponf)) {
            $strtiponf = " and tiponf = '" . $tiponf . "' ";
        } else {
            $strtiponf = "";
        }
      
/*
        if (!empty($idtipoprodserv)) {
            $stridtipoprodserv = " and p.idtipoprodserv =" . $idtipoprodserv . " ";
        } else {
            $stridtipoprodserv = "";
        }
        */
        /*Controlado pela LP se liberar o Grupo pode ser visto
        if ($flgdiretor < 1) {
            $viscontaitem = " and c.visualizarext='Y' ";
        } else {
            $viscontaitem = "";
        }
        */

        // nfitem descritivo e com idprodserv preenchido
        $sqlgrupo = "select u.contaitem,u.idcontaitem,u.cor,u.somarelatorio,u.previsao,sum(u.total) as somatotal,u.ordem,u.faturamento
                    from (
                            SELECT c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,i.total,c.ordem,c.faturamento
                                            FROM nf n 
                                            join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                                            join prodserv p on(p.idprodserv=i.idprodserv)
                                            join tipoprodserv tp on(tp.idtipoprodserv=p.idtipoprodserv)
                                            join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . "  )
                                            where n.status !='CANCELADO'
                                            and n.tiponf not in ('V')
                                            ".$clausulaun."
                                            " . $clausulac . " 
                                             
                                             and n.idempresa IN (" . $idempresa . ") 
                                            " . $stridcontaitem . "
                                            " . $stridtipoprodserv . "
                           union all
                           SELECT c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,i.total,c.ordem,c.faturamento
                                            FROM nf n 
                                            join nfitem i on(i.idnf=n.idnf  and i.nfe!='C' and i.total>0) 
                                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv)
                                            join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . "  )
                                            where n.status !='CANCELADO'
                                            and (i.idprodserv is null or i.idprodserv ='') 
                                            ".$clausulaun." 
                                            " . $clausulac . "
                                         
                                            and n.idempresa IN (" . $idempresa . ")
                                            " . $stridcontaitem . "
                                            " . $stridtipoprodserv . "
                        ) as u 
                        where 1   " . $strtiponf . " 
                        group by u.idcontaitem order by u.ordem";
        echo "<!--";
        echo $sqlgrupo;
        echo "-->";
        if (!empty($sqlgrupo)) {

            $resgrupo =  d::b()->query($sqlgrupo) or die("Falha ao pesquisar grupo de contas: " . mysqli_error() . "<p>SQL: $sqlgrupo");
            $ires = mysqli_num_rows($resgrupo);
            $saldototal = 0;
        }
    } else { //MODO FATURAMENTO
        if (!empty($vencimento_1) or !empty($vencimento_2)) {
            $dataini = validadate($vencimento_1);
            $datafim = validadate($vencimento_2);

            if ($dataini and $datafim) {
                $clausulac .= " and (cp.datareceb  BETWEEN '" . $dataini . "' and '" . $datafim . "')" . "  ";
            } else {
                die("Datas n&atilde;o V&aacute;lidas!");
            }
        }

        if (!empty($idcontaitem)) {
            $stridcontaitem = " and c.idcontaitem in (" . $idcontaitem . ") ";
        } else {
            $stridcontaitem = "";
        }

        if (!empty($idagencia)) {
            $clausulac .= " and cp.idagencia IN (" . $idagencia . ")  ";
        }
        
        
        if (!empty($tiponf)) {
            $strtiponf = " and tiponf = '" . $tiponf . "' ";
        } else {
            $strtiponf = "";
        }
      
        

        if(!empty($idunidade)){
            $clausulaun .= " and n.idunidade=".$idunidade." ";
        }
/*
        if (!empty($idtipoprodserv)) {
            $stridtipoprodserv = " and p.idtipoprodserv =" . $idtipoprodserv . " ";
        } else {
            $stridtipoprodserv = "";
        }
*/
        if ($flgdiretor < 1) {
            $viscontaitem = " and c.visualizarext='Y' ";
        } else {
            $viscontaitem = "";
        }

        $sqlgrupo = "select u.tiponf,u.contaitem,u.idcontaitem,u.cor,u.somarelatorio,u.previsao,sum(u.total) as somatotal,u.ordem,u.status,u.tipo,u.faturamento
                    from (
                        SELECT n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,                           
                        
                        round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor)*-1,2) as total
                        ,cp.status,cp.tipo,c.faturamento
                        ,c.ordem
                                FROM nf n 
                                join nfitem i on(i.idnf=n.idnf and i.nfe='Y' ) 
                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . "  )
                                join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                where cp.tipoespecifico!= 'AGRUPAMENTO'
                             
                                and cp.idempresa IN (" . $idempresa . ")  
                                ".$clausulaun."                               
                                " . $clausulac . " 
                                " . $stridcontaitem . "
                                " . $stridtipoprodserv . "  
                                
                                and cp.status !='INATIVO'
                                and cp.tipo = 'D'
                                and cp.valor>0
                                -- and i.total>0
                                and n.tiponf not in('S','R','O')
                       union all
                         SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                               
                                round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*ci.valor)*-1,2) as total
                                 ,cp.status,cp.tipo,c.faturamento
                                ,c.ordem
                                FROM contapagar cp
                                join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                            and ci.tipoobjetoorigem ='nf')
                                join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                join nfitem i on(i.idnf=n.idnf and i.nfe='Y')          
                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )               
                                where cp.tipoespecifico = 'AGRUPAMENTO'  
                                
                                and cp.idempresa  IN (" . $idempresa . ") 
                                ".$clausulaun."
                                " . $clausulac . " 
                                " . $stridcontaitem . "
                                " . $stridtipoprodserv . "
                              
                                and cp.status !='INATIVO'
                                and ci.status!='INATIVO'
                                and cp.tipo = 'D'
                                and cp.valor>0
                                -- and i.total>0
                                and n.tiponf not in('S','R','O')                                
                        union all
                            SELECT n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,                           
                            round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*cp.valor)*-1,2) as total  
                            ,cp.status,cp.tipo,c.faturamento
                            ,c.ordem
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y') 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . "  )
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'
                                   
                                    and cp.idempresa IN (" . $idempresa . ")   
                                    ".$clausulaun."                             
                                    " . $clausulac . " 
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "  
                                   
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                    -- and i.total>0
                                    and n.tiponf  in('S','R')
                           union all
                             SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    round(((((ifnull(i.total,0))*(n.total/n.subtotal))/n.total)*ci.valor)*-1,2) as total
                                     ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' )          
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO'  
                                   
                                    and cp.idempresa IN (" . $idempresa . ")
                                    ".$clausulaun."
                                    " . $clausulac . " 
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                 
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                    -- and i.total>0
                                    and n.tiponf in('S','R')
                                    union all
                            SELECT n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,                           
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0))/n.total)*cp.valor),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                                    END as total
                            ,cp.status,cp.tipo,c.faturamento
                            ,c.ordem
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0)          
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )  
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                    and i.idprodserv is null
                                    and cp.idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."                              
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                   
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C'
                           union all
                             SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0))/n.total)*ci.valor),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*ci.valor),2)
                                    END as total
                                     ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0)          
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )              
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                    and i.idprodserv is null
                                    and cp.idempresa IN (" . $idempresa . ") 
                                    ".$clausulaun." 
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                 
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C'
                            union all
                            SELECT n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,                           
                            CASE
                                WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0))/n.total)*cp.valor),2)  
                                ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                            END as total
                            ,cp.status,cp.tipo,c.faturamento
                            ,c.ordem
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0 ) 
                                   join prodserv ps on(ps.idprodserv =i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem  " . $viscontaitem . " )   
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                    
                                    and cp.idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."                             
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "  
                                   
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C'
                           union all
                             SELECT   n.tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0))/n.total)*ci.valor),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*ci.valor),2)
                                    END as total
                                    ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                                    join prodserv ps on(ps.idprodserv =i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                    
                                    and cp.idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                  
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C'
                                union all
                                    SELECT                                    
                                        'SV' as tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                       
                                        round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total
                                        ,cp.status,cp.tipo,c.faturamento
                                        ,c.ordem									
                                    FROM notafiscal n 
                                      join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                       join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem  " . $viscontaitem . ")   
                                    join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO' 
                                                                     
                                    and cp.idempresa IN (" . $idempresa . ")  
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . " 
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idprodserv,i.valor
                           union all
                             SELECT   
                                    'SV' as tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                    ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='notafiscal')
                                    join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                    join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                   
                                    and cp.idempresa IN (" . $idempresa . ") 
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idprodserv,i.valor

                                    union all
                                    SELECT 'SV' as tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                            round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total,
                                            cp.status,cp.tipo,c.faturamento
                                            ,c.ordem									
                                    FROM notafiscal n 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )   
                                    join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO' 
                                                           
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . " 
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idtipoprodserv,i.valor
                           union all
                             SELECT   
                                    'SV' as tiponf,c.contaitem,c.idcontaitem,c.cor,c.somarelatorio,c.previsao,
                                    
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                    ,cp.status,cp.tipo,c.faturamento
                                    ,c.ordem
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='notafiscal')
                                    join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )                
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                    
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridcontaitem . "
                                    " . $stridtipoprodserv . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idtipoprodserv,i.valor
                        ) as u             
                        where 1 " . $strtiponf . "
                        group by u.idcontaitem order by u.ordem";
        echo "<!-- grupo";
        echo $sqlgrupo;
        echo "-->";
        if (!empty($sqlgrupo)) {

            $resgrupo =  d::b()->query($sqlgrupo) or die("Falha ao pesquisar grupo de contas: " . mysqli_error() . "<p>SQL: $sqlgrupo");
            $ires = mysqli_num_rows($resgrupo);
            $saldototal = 0;
        }
    } //FIM DO MODO 
}
?>

<!-- Mostrar mensagem de Aguarde e bloquear tela  -->

<style>
    .backbranco {
        background-color: white !important;
    }
</style>
<div class="divbody">
    <?
    if ($_GET and $ires > 0) {

        $vlrcredito = 0;
        $vlrdebito = 0;
        $vlrpendcredito = 0;
        $vlrpenddebito = 0;
        $vlrquitadocredito = 0;
        $vlrquitadodebito = 0;
        $vlrprogramado = 0;
        $vlrprogramadopagar = 0;
        $vlrndescriminados = 0;
        $arrprogramado = array();

        if ($pesquisa == 'detalhe') {
            $collapse = "";
            $collapseitem = "collapse";
        } elseif ($pesquisa == 'detalheitem') {
            $collapse = "";
            $collapseitem = "";
        } else {
            $collapse = "collapse";
            $collapseitem = "collapse";
        }
        //die('pesq='.$pesquisa.' col='.$collapse);
        $vtotal = 0;
        $id = 0;
        $id2 = 0;
        if (!empty($_REQUEST['csv'])) {
            $conteudoexport = "CONTA ITEM;TIPO PRODUTO;NFe;QTD;ITEM;PARCELA;VALOR UN;TOTAL;\n";
        }
        $arrcontaitem = array();
        while ($row = mysqli_fetch_assoc($resgrupo)) {
            $id = $id + 1;
            if (!empty($row["idcontaitem"])  and $row["tiponf"] != "O") {

                if (!array_key_exists($row['idcontaitem'], $arrcontaitem)) {

                    $arrcontaitem[$row['idcontaitem']]['total'] =abs($row["somatotal"]);  ; 
                    $arrcontaitem[$row['idcontaitem']]['faturamento'] = $row["faturamento"]; 
                        

    ?>

        <div class="panel panel-default" style="background-color: <?= $row["cor"] ?>;">
            <div class="panel-body">
                <table style="width:100%" class="tbItem" idcontaitem="<?= $row["idcontaitem"] ?>">
                    <tr>
                     
                        <td class="nowrap" class="col-md-8">
                        <input name="_1_u_contaitem_cor" type="hidden" value="<?= $row["cor"] ?>" size="6" class="color" style="cursor:pointer;" onchange="cor(this,<?= $row["idcontaitem"] ?>);">
                            <?= $row["contaitem"] ?>
                        </td>
                      
                        <td align="right" class="col-md-1"> Previsão:
                            <input class="size6" name="_1_u_contaitem_previsao" onchange="previsao(this,<?= $row["idcontaitem"] ?>);" type="text" value="<?= $row['previsao'] ?>">
                        </td>
                        <td align="right" class="col-md-1"> 
                            <span class="idcontaitem_<?=$row["idcontaitem"]?>">
                            </span>
                        </td>
                      
                        <td align="right" class="col-md-1"> R$:
                            <label class="alert-warning"><?= number_format(tratanumero($row["somatotal"]), 2, ',', '.'); ?></label>
                        </td>
                        
                        <td  align="right"  class="col-md-1"><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar" data-toggle="collapse" href="#prodInfo<?= $id ?>" aria-expanded="false"></i></td>
                      
                      
                    </tr>
                </table>

            </div>
            <div class="panel-body">

                <div class="<?= $collapse ?>" id="prodInfo<?= $id ?>">
                    <table class="table table-striped planilha" style="width:100%">
                        <?
                        //if($pesquisa=="detalhe"){
                        if ($modo == 'FT') {
                            //itens descritivos ou com idprodserv           
                            $sql = "select u.tipoprodserv,u.idtipoprodserv,sum(u.total) as somatotal  
                        from(
                            SELECT tp.tipoprodserv,tp.idtipoprodserv,i.total, n.tiponf
                            FROM nf n 
                            join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                            join prodserv p on(p.idprodserv=i.idprodserv)
                            join tipoprodserv tp on(tp.idtipoprodserv=p.idtipoprodserv)
                            where n.status !='CANCELADO'
                            and n.tiponf not in ('V')
                            and i.total>0
                            " . $clausulac . "         
                            " . $stridtipoprodserv . "
                            and n.idempresa  IN (" . $idempresa . ") 
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                            union all
                            SELECT p.tipoprodserv,p.idtipoprodserv,i.total, n.tiponf
                            FROM nf n 
                            join nfitem i on(i.idnf=n.idnf and i.nfe!='C' and i.total>0)                        
                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv)
                            where n.status !='CANCELADO'     
                                                   
                            and (i.idprodserv is null or i.idprodserv ='')  
                            ".$clausulaun."  
                            " . $clausulac . "         
                            " . $stridtipoprodserv . "
                            and n.idempresa  IN (" . $idempresa . ") 
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                        ) as u        
                        where 1  " . $strtiponf . "
                        group by u.idtipoprodserv order by somatotal desc";

                                        echo "<!--";
                                        echo $sql;
                                        echo "-->";
                                    } else { //Faturamento
                                        //contapagar agrupado e normal
                                        $sql = "select u.tipoprodserv,u.idtipoprodserv,sum(u.total) as somatotal,u.somarelatorio,u.status,u.tipo  
                        from (
                            SELECT p.tipoprodserv,p.idtipoprodserv,
                            
                            round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor)*-1,2) as total
                            ,c.somarelatorio,cp.status,cp.tipo
                            ,n.tiponf
                            FROM nf n 
                            join nfitem i on(i.idnf=n.idnf and i.nfe='Y') 
                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                            join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )
                            join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                            where cp.tipoespecifico!= 'AGRUPAMENTO' 
                            
                            and cp .idempresa IN (" . $idempresa . ") 
                            ".$clausulaun."                                   
                            " . $clausulac . "                    
                            " . $stridtipoprodserv . "  
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                            and cp.status !='INATIVO'
                            and cp.tipo = 'D'
                            and cp.valor>0
                            -- and i.total>0
                            and n.tiponf not in('S','R','O')
                   union all
                    SELECT  p.tipoprodserv,p.idtipoprodserv,
                            
                            round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*sum(ci.valor))*-1,2) as total
                            ,c.somarelatorio,cp.status,cp.tipo
                            ,n.tiponf
                            FROM contapagar cp
                            join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                        and ci.tipoobjetoorigem ='nf')
                            join nf n on(ci.idobjetoorigem =n.idnf  ) 
                            join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' )               
                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . ")
                            join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )               
                            where cp.tipoespecifico = 'AGRUPAMENTO'  
                            
                            and cp .idempresa IN (" . $idempresa . ")  
                            ".$clausulaun."
                            " . $clausulac . "                     
                            " . $stridtipoprodserv . "
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                            and cp.status !='INATIVO'
                            and ci.status!='INATIVO'
                            and cp.tipo = 'D'
                            and cp.valor>0
                           --  and i.total>0
                            and n.tiponf not in('S','R','O')
                            group by cp.idcontapagar,i.idnfitem
                    union all
                            SELECT p.tipoprodserv,p.idtipoprodserv,
                                    round(((((ifnull(i.total,0))*(n.total/ifnull(n.subtotal,n.total)))/n.total)*cp.valor)*-1,2) as total
                                    ,c.somarelatorio,cp.status,cp.tipo
                                    ,n.tiponf
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y') 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO' 
                                  
                                    and cp .idempresa IN (" . $idempresa . ")    
                                    ".$clausulaun."                                
                                    " . $clausulac . "                    
                                    " . $stridtipoprodserv . "  
                                    and i.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                    -- and i.total>0
                                    and n.tiponf in('S','R')
                           union all
                            SELECT  p.tipoprodserv,p.idtipoprodserv,
                                    round(((((ifnull(i.total,0))*(n.total/ifnull(n.subtotal,n.total)))/n.total)*sum(`ci`.`valor`))*-1,2) as total
                                    ,c.somarelatorio,cp.status,cp.tipo
                                    ,n.tiponf
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' )               
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . ")
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO'  
                                   
                                    and cp .idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and i.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'D'
                                    and cp.valor>0
                                   -- and i.total>0
                                    and n.tiponf in('S','R')
                                    group by cp.idcontapagar,i.idnfitem
                                union all
                                    SELECT p.tipoprodserv,p.idtipoprodserv,
                                            CASE
                                                WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*cp.valor),2)  
                                                ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                                            END as total                                            
                                            ,c.somarelatorio,cp.status,cp.tipo
                                            ,n.tiponf
                                            FROM nf n 
                                            join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' and i.total>0 )               
                                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . ")
                                            join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )    
                                            join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                            where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                            and i.idprodserv is null
                                            and cp .idempresa IN (" . $idempresa . ") 
                                            ".$clausulaun."                                
                                            " . $clausulac . "                    
                                            " . $stridtipoprodserv . "  
                                            and c.idcontaitem = " . $row['idcontaitem'] . "
                                            and cp.status !='INATIVO'
                                            and cp.tipo = 'C'
                                   union all
                                    SELECT  p.tipoprodserv,p.idtipoprodserv,
                                            CASE
                                                WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*sum(ci.valor)),2)  
                                                ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*ci.valor),2)
                                            END as total
                                            ,c.somarelatorio,cp.status,cp.tipo
                                            ,n.tiponf
                                            FROM contapagar cp
                                            join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                        and ci.tipoobjetoorigem ='nf')
                                            join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                            join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' and i.total>0 )               
                                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . ")
                                            join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . " )              
                                            where cp.tipoespecifico = 'AGRUPAMENTO' 
                                            and i.idprodserv is null
                                            and cp .idempresa IN (" . $idempresa . ") 
                                            ".$clausulaun."  
                                            " . $clausulac . "                     
                                            " . $stridtipoprodserv . "
                                            and c.idcontaitem = " . $row['idcontaitem'] . "
                                            and cp.status !='INATIVO'
                                            and ci.status!='INATIVO'
                                            and cp.tipo = 'C'
                                            group by cp.idcontapagar,i.idnfitem
                            union all
                            SELECT p.tipoprodserv,p.idtipoprodserv,
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*cp.valor),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                                    END as total
                                    ,c.somarelatorio,cp.status,cp.tipo
                                    ,n.tiponf
                                    FROM nf n 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0 ) 
                                   join prodserv ps on(ps.idprodserv =i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . "  )   
                                    join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                    
                                    and cp .idempresa IN (" . $idempresa . ")  
                                    ".$clausulaun."                               
                                    " . $clausulac . "                    
                                    " . $stridtipoprodserv . "  
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C'
                           union all
                            SELECT  p.tipoprodserv,p.idtipoprodserv,
                                    CASE
                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*sum(ci.valor)),2)  
                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*sum(ci.valor)),2)
                                    END as total 
                                    ,c.somarelatorio,cp.status,cp.tipo
                                    ,n.tiponf
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='nf')
                                    join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                    join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                                    join prodserv ps on(ps.idprodserv =i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv " . $stridtipoprodserv . " )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . "  )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO' 
                                    
                                    and cp .idempresa IN (" . $idempresa . ") 
                                    ".$clausulaun."  
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C'
                                    group by cp.idcontapagar,i.idnfitem
                                union all
                                    SELECT 
                                    p.tipoprodserv,p.idtipoprodserv,
                                   
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total
                                    ,c.somarelatorio
                                    ,cp.status,cp.tipo	
                                    ,'S' as tiponf
                                    FROM notafiscal n 
                                      join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                      join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv " . $stridtipoprodserv . " )
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem  " . $viscontaitem . ")   
                                    join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                                                 
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idprodserv,i.valor
                           union all
                             SELECT   
                                    p.tipoprodserv,p.idtipoprodserv,
                                    
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                    ,c.somarelatorio
                                    ,cp.status,cp.tipo	     
                                    ,'S' as tiponf
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='notafiscal')
                                    join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                    join prodserv ps on(ps.idprodserv=i.idprodserv)
                                    join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv  " . $stridtipoprodserv . ")
                                    join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                    join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . " )               
                                    where cp.tipoespecifico = 'AGRUPAMENTO'  
                                    
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idprodserv,i.valor

                                    union all
                                    SELECT 
                                    p.tipoprodserv,p.idtipoprodserv,
                                   
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total
                                    ,c.somarelatorio
                                    ,cp.status,cp.tipo	
                                    ,'S' as tiponf
                                    FROM notafiscal n 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . " )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . ") 
                                    join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                    where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                                                
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idtipoprodserv,i.valor                                    
                           union all
                             SELECT   
                                    p.tipoprodserv,p.idtipoprodserv,
                                    
                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                    ,c.somarelatorio
                                    ,cp.status,cp.tipo
                                    ,'S' as tiponf
                                    FROM contapagar cp
                                    join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                and ci.tipoobjetoorigem ='notafiscal')
                                    join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                    join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . " )
                                    join contaitem c on(c.idcontaitem=i.idcontaitem  " . $viscontaitem . ")              
                                    where cp.tipoespecifico = 'AGRUPAMENTO'  
                                    
                                    and cp .idempresa IN (" . $idempresa . ")   
                                    " . $clausulac . "                     
                                    " . $stridtipoprodserv . "
                                    and c.idcontaitem = " . $row['idcontaitem'] . "
                                    and cp.status !='INATIVO'
                                    and ci.status!='INATIVO'
                                    and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idtipoprodserv,i.valor

                        ) as u
                        where 1  " . $strtiponf . "
                        group by u.idtipoprodserv order by somatotal desc";

                                        echo "<!--";
                                        echo $sql;
                                        echo "-->";
                                    }

                                    $res =  d::b()->query($sql) or die("Falha ao pesquisar tipoprodserv: " . mysqli_error() . "<p>SQL: $sql");

                                    while ($row2 = mysqli_fetch_assoc($res)) {
                                        $id2 = $id2 + 1;
                                        $percent =((abs($row2["somatotal"]) * 100)/ abs($row["somatotal"]));
                                    ?>

                                        <tr>
                                            <td class="nowrap" class="col-md-8">
                                                <?
                                                echo "<!-- soma total";
                                                echo $sql;
                                                echo "-->";
                                                ?><b><?= $row2["tipoprodserv"] ?></b>
                                            </td>
                                            <td align="right" class="col-md-1"></td>
                                            <td align="right" class="col-md-1">
                                                <span>                                              
                                                <b><?echo(number_format(tratanumero($percent), 2, ',', '.').'%');?></b>
                                                </span>                                            
                                            </td>
                                            <td align="right" class="col-md-1">
                                                <b>R$: <label class="alert-warning"><?= number_format(tratanumero($row2["somatotal"]), 2, ',', '.'); ?><label></b>
                                            </td>
                                           
                                            <td align="right" class="col-md-1"><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar" data-toggle="collapse" href="#nfInfo<?= $id2 ?>" aria-expanded="false"></i></td>
                                        </tr>

                                        <?
                                        if ($modo == 'FT') {
                                            //itens descritivos ou com idprodserv  
                                            $sql3 = "select u.descr,ifnull(u.nnfe,u.idnf) as nnfe,u.idnf,u.qtd,u.vlritem as vlritem ,sum(u.total) as total,u.tiponf,
                    u.idprodserv,
                    '' as parcela,
                    u.parcelas
                from (
                        SELECT p.descr,n.nnfe,n.idnf,i.qtd,i.vlritem,i.total,n.tiponf,i.idprodserv,n.parcelas
                            FROM nf n 
                            join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                            join prodserv p on(p.idprodserv=i.idprodserv)
                            join tipoprodserv tp on(tp.idtipoprodserv=p.idtipoprodserv)
                        where  n.status !='CANCELADO'
                            and n.tiponf not in ('V')
                            and n.idempresa  IN (" . $idempresa . ") 
                            ".$clausulaun."
                            " . $clausulac . "  
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                            and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                        union all
                        SELECT i.prodservdescr as descr,n.nnfe,n.idnf,i.qtd,i.vlritem,i.total,n.tiponf,i.idprodserv,n.parcelas
                            FROM nf n 
                            join nfitem i on(i.idnf=n.idnf and i.nfe!='C' and i.total>0) 	
                            join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv)
                        where  n.status !='CANCELADO'
                            and (i.idprodserv is null or i.idprodserv ='') 
                            and n.idempresa  IN (" . $idempresa . ") 
                            ".$clausulaun."       
                            " . $clausulac . "  
                            and i.idcontaitem = " . $row['idcontaitem'] . "
                            and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                    )as u where u.total > 0  " . $strtiponf . "
                    group by u.idnf,u.descr
                    order by u.descr";
                                            echo "<!--";
                                            echo $sql3;
                                            echo "-->";
                                        } else { //FATURAMENTO
                                            //contapagar agrupado e normal
                                            $sql3 = "select 
                                    u.descr,ifnull(u.nnfe,u.idnf) as nnfe,u.idnf,u.idcontapagar,sum(u.qtd) as qtd, (sum(u.total)/sum(u.qtd)) as vlritem,sum(u.total) as total,u.somarelatorio,u.tiponf,
                                    u.idprodserv,
                                    u.parcela,u.parcelas,u.status,u.tipo
                                from (
                                    SELECT IFNULL(ps.descr,i.prodservdescr) as descr,n.nnfe,n.idnf,cp.idcontapagar,i.qtd,
                                            round(i.vlritem*(cp.valor/n.total),2) as vlritem,                                       
                                        
                                        round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2) as total
                                        ,c.somarelatorio,cp.status,cp.tipo,
                                        n.tiponf,i.idprodserv,cp.parcela,cp.parcelas
                                                FROM nf n 
                                                join nfitem i on(i.idnf=n.idnf and i.nfe='Y') 
                                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                                join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )
                                                join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                                left join prodserv ps on(ps.idprodserv=i.idprodserv)
                                                where cp.tipoespecifico!= 'AGRUPAMENTO'  
                                                
                                                and cp .idempresa IN (" . $idempresa . ") 
                                                ".$clausulaun."                                  
                                                " . $clausulac . "                   
                                                and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                and i.idcontaitem = " . $row['idcontaitem'] . "
                                                and cp.status !='INATIVO'
                                                and cp.tipo = 'D'
                                                and cp.valor>0
                                                and n.tiponf not in('S','R','O')
                                        union all                                        
                                        SELECT  IFNULL(ps.descr,i.prodservdescr) as descr,n.nnfe,n.idnf,cp.idcontapagar,i.qtd,                                               
                                                round(i.vlritem,2) as vlritem,                                               
                                                round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*sum(ci.valor)),2) as total
                                                ,c.somarelatorio,cp.status,cp.tipo,
                                                n.tiponf,i.idprodserv
                                                ,ci.parcela,ci.parcelas
                                               FROM contapagar cp
                                               join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                           and ci.tipoobjetoorigem ='nf')
                                               join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                               join nfitem i on(i.idnf=n.idnf  and i.nfe='Y'  )               
                                               join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                               join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )   
                                               left join prodserv ps on(ps.idprodserv=i.idprodserv)
                                               where cp.tipoespecifico = 'AGRUPAMENTO' 
                                               
                                               and cp .idempresa IN (" . $idempresa . ") 
                                               ".$clausulaun." 
                                               " . $clausulac . "  
                                               and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                               and i.idcontaitem = " . $row['idcontaitem'] . "
                                               and cp.status !='INATIVO'
                                               and ci.status!='INATIVO'
                                               and cp.valor>0
                                               and cp.tipo = 'D'
                                               and n.tiponf not in('S','R','O')
                                               group by cp.idcontapagar,i.idnfitem
                                    union all
                                        SELECT IFNULL(ps.descr,i.prodservdescr) as descr,n.nnfe,n.idnf,cp.idcontapagar,i.qtd,
                                            round(i.vlritem*(cp.valor/n.total),2) as vlritem,                                       
                                            round(((((ifnull(i.total,0))*(n.total/ifnull(n.subtotal,n.total)))/n.total)*cp.valor),2) as total
                                            ,c.somarelatorio,cp.status,cp.tipo,
                                        n.tiponf,i.idprodserv,cp.parcela,cp.parcelas
                                                FROM nf n 
                                                join nfitem i on(i.idnf=n.idnf and i.nfe='Y'  ) 
                                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                                join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )
                                                join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                                left join prodserv ps on(ps.idprodserv=i.idprodserv)
                                                where cp.tipoespecifico!= 'AGRUPAMENTO'  
                                                
                                                and cp .idempresa IN (" . $idempresa . ")  
                                                ".$clausulaun."                                 
                                                " . $clausulac . "                   
                                                and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                and i.idcontaitem = " . $row['idcontaitem'] . "
                                                and cp.status !='INATIVO'
                                                and cp.tipo = 'D'
                                                and cp.valor>0                                                
                                                and n.tiponf in('S','R')
                                        union all                                        
                                        SELECT  IFNULL(ps.descr,i.prodservdescr) as descr,n.nnfe,n.idnf,cp.idcontapagar,i.qtd,                                               
                                                round(i.vlritem,2) as vlritem,
                                                round(((((ifnull(i.total,0))*(n.total/ifnull(n.subtotal,n.total)))/n.total)*sum(ci.valor)),2) as total                                               
                                                ,c.somarelatorio,cp.status,cp.tipo,
                                                n.tiponf,i.idprodserv
                                                ,ci.parcela,ci.parcelas
                                               FROM contapagar cp
                                               join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                           and ci.tipoobjetoorigem ='nf')
                                               join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                               join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' )               
                                               join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                               join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )   
                                               left join prodserv ps on(ps.idprodserv=i.idprodserv)
                                               where cp.tipoespecifico = 'AGRUPAMENTO' 
                                            
                                               and cp .idempresa IN (" . $idempresa . ") 
                                               ".$clausulaun." 
                                               " . $clausulac . "  
                                               and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                               and i.idcontaitem = " . $row['idcontaitem'] . "
                                               and cp.status !='INATIVO'
                                               and cp.valor>0
                                               and ci.status!='INATIVO'
                                               and cp.tipo = 'D'
                                               and n.tiponf in('S','R')
                                               group by cp.idcontapagar,i.idnfitem
                                               union all
                                               SELECT i.prodservdescr as descr,n.nnfe,n.idnf,cp.idcontapagar,i.qtd,
                                                               round(i.vlritem*(cp.valor/n.total),2) as vlritem,                                       
                                                            CASE
                                                               WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*cp.valor),2)  
                                                               ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                                                           END as total  
                                                           ,c.somarelatorio,cp.status,cp.tipo,
                                                           n.tiponf,i.idprodserv,cp.parcela,cp.parcelas
                                                    FROM nf n 
                                                    join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' and i.total>0 )               
                                                    join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                                    join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )  
                                                       join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                                       where cp.tipoespecifico!= 'AGRUPAMENTO'
                                                       and i.idprodserv is null
                                                       and cp .idempresa IN (" . $idempresa . ")  
                                                       ".$clausulaun."                                   
                                                       " . $clausulac . "                  
                                                       and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                       and c.idcontaitem = " . $row['idcontaitem'] . "
                                                       and cp.status !='INATIVO'
                                                       and cp.tipo = 'C'
                                           union all
                                               SELECT  i.prodservdescr as descr,n.nnfe,n.idnf,cp.idcontapagar,i.qtd,                                               
                                                                   round(i.vlritem,2) as vlritem,
                                                                    CASE
                                                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*sum(ci.valor)),2)  
                                                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*sum(ci.valor)),2)
                                                                    END as total
                                                                   ,c.somarelatorio,cp.status,cp.tipo,
                                                                   n.tiponf,i.idprodserv
                                                                   ,ci.parcela,ci.parcelas
                                                               FROM contapagar cp
                                                       join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                                   and ci.tipoobjetoorigem ='nf')
                                                       join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                                       join nfitem i on(i.idnf=n.idnf  and i.nfe='Y' and i.total>0 )               
                                                       join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv )
                                                       join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )                
                                                       where cp.tipoespecifico = 'AGRUPAMENTO' 
                                                       and i.idprodserv is null
                                                       and cp .idempresa IN (" . $idempresa . ") 
                                                       ".$clausulaun."  
                                                       " . $clausulac . "  
                                                       and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                       and c.idcontaitem = " . $row['idcontaitem'] . "
                                                       and cp.status !='INATIVO'
                                                       and ci.status!='INATIVO'
                                                       and cp.tipo = 'C'
                                                       group by cp.idcontapagar,i.idnfitem
                                    union all
                                        SELECT IFNULL(ps.descr,i.prodservdescr) as descr,n.nnfe,n.idnf,cp.idcontapagar,i.qtd,
                                                        round(i.vlritem*(cp.valor/n.total),2) as vlritem,                                       
                                                    CASE
                                                        WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*cp.valor),2)  
                                                        ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)*cp.valor),2)
                                                    END as total
                                                    ,c.somarelatorio,cp.status,cp.tipo,
                                                    n.tiponf,i.idprodserv,cp.parcela,cp.parcelas
                                                            FROM nf n 
                                                join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0 ) 
                                                join prodserv ps on(ps.idprodserv =i.idprodserv)
                                                join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                                join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                                join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . " )   
                                                join contapagar cp on(cp.idobjeto = n.idnf and cp.tipoobjeto ='nf')
                                                where cp.tipoespecifico!= 'AGRUPAMENTO'
                                                
                                                and cp .idempresa IN (" . $idempresa . ")  
                                                ".$clausulaun."                                   
                                                " . $clausulac . "                  
                                                and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                and c.idcontaitem = " . $row['idcontaitem'] . "
                                                and cp.status !='INATIVO'
                                                and cp.tipo = 'C'
                                    union all
                                        SELECT  IFNULL(ps.descr,i.prodservdescr) as descr,n.nnfe,n.idnf,cp.idcontapagar,i.qtd,                                               
                                                            round(i.vlritem,2) as vlritem,
                                                        CASE
                                                            WHEN n.tiponf ='V' THEN round((((i.total+ifnull(i.frete,0)+ IFNULL(`i`.`valipi`, 0))/n.total)*sum(ci.valor)),2)  
                                                            ELSE  round((((ifnull(i.total,0)+ifnull(valipi,0)+(((ifnull(i.total,0)+ifnull(valipi,0))/(n.total-n.frete))* (n.frete)))/n.total)* sum(ci.valor)),2)
                                                        END as total                                                      
                                                            ,c.somarelatorio,cp.status,cp.tipo,
                                                            n.tiponf,i.idprodserv
                                                            ,ci.parcela,ci.parcelas
                                                        FROM contapagar cp
                                                join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                            and ci.tipoobjetoorigem ='nf')
                                                join nf n on(ci.idobjetoorigem =n.idnf  ) 
                                                join nfitem i on(i.idnf=n.idnf and i.nfe='Y' and i.total>0) 
                                                join prodserv ps on(ps.idprodserv =i.idprodserv)
                                                join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv )
                                                join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                                join contaitem c on(c.idcontaitem=pc.idcontaitem  " . $viscontaitem . " )               
                                                where cp.tipoespecifico = 'AGRUPAMENTO' 
                                                
                                                and cp .idempresa IN (" . $idempresa . ") 
                                                ".$clausulaun."  
                                                " . $clausulac . "  
                                                and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                and c.idcontaitem = " . $row['idcontaitem'] . "
                                                and cp.status !='INATIVO'
                                                and ci.status!='INATIVO'
                                                and cp.tipo = 'C'
                                                group by cp.idcontapagar,i.idnfitem
                                        union all
                                                SELECT 
                                            
                                                ps.descr ,n.nnfe,n.idnotafiscal as idnf,cp.idcontapagar,sum(i.quantidade) as qtd,                                               
                                                round(i.valor,2) as vlritem,
                                                
                                                round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total,                                                
                                                c.somarelatorio,
                                                cp.status,cp.tipo,
                                                'SERVICO' AS tiponf,i.idprodserv
                                                ,cp.parcela,cp.parcelas								
                                                FROM notafiscal n 
                                                  join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                                  join prodserv ps on(ps.idprodserv=i.idprodserv)
                                                join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv " . $stridtipoprodserv . " )
                                                join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                                join contaitem c on(c.idcontaitem=pc.idcontaitem  " . $viscontaitem . ")   
                                                join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                                where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                                                         
                                                and cp .idempresa IN (" . $idempresa . ")                                     
                                                " . $clausulac . "                  
                                                and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                and c.idcontaitem = " . $row['idcontaitem'] . "
                                                and cp.status !='INATIVO'
                                                and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idprodserv,i.valor
                                       union all
                                         SELECT 
                                                    ps.descr,n.nnfe,n.idnotafiscal as idnf,cp.idcontapagar,sum(i.quantidade) as qtd,                                               
                                                    round(i.valor,2) as vlritem,
                                                    
                                                    round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total
                                                    ,c.somarelatorio
                                                    ,cp.status,cp.tipo,
                                                    'SERVICO' AS tiponf,i.idprodserv
                                                    ,ci.parcela,ci.parcelas                           
                                                FROM contapagar cp
                                                join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                            and ci.tipoobjetoorigem ='notafiscal')
                                                join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                                join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal  ) 
                                                join prodserv ps on(ps.idprodserv=i.idprodserv)
                                                join tipoprodserv p on(p.idtipoprodserv=ps.idtipoprodserv  " . $stridtipoprodserv . ")
                                                join prodservcontaitem pc on(pc.idprodserv=ps.idprodserv)
                                                join contaitem c on(c.idcontaitem=pc.idcontaitem " . $viscontaitem . " )               
                                                where cp.tipoespecifico = 'AGRUPAMENTO'  
                                                
                                                and cp .idempresa IN (" . $idempresa . ")   
                                                " . $clausulac . "  
                                                and p.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                and c.idcontaitem = " . $row['idcontaitem'] . "
                                                and cp.status !='INATIVO'
                                                and ci.status!='INATIVO'
                                                and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idprodserv,i.valor
                                        union all

                                                SELECT                                            
                                                i.descricao as descr ,n.nnfe,n.idnotafiscal as idnf,cp.idcontapagar,sum(i.quantidade) as qtd,                                               
                                                round(i.valor,2) as vlritem,
                                                
                                                round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(cp.valor/n.total),2) as total,
                                                c.somarelatorio,
                                                cp.status,cp.tipo,
                                                'SERVICO' AS tiponf,i.idtipoprodserv as idprodserv
                                                ,cp.parcela,cp.parcelas								
                                                FROM notafiscal n 
                                                join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . "  )
                                                join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )    
                                                join contapagar cp on(cp.idobjeto = n.idnotafiscal and cp.tipoobjeto ='notafiscal')
                                                where cp.tipoespecifico!= 'AGRUPAMENTO'    
                                                                           
                                                and cp .idempresa IN (" . $idempresa . ")                                     
                                                " . $clausulac . "                  
                                                and i.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                and c.idcontaitem = " . $row['idcontaitem'] . "
                                                and cp.status !='INATIVO'
                                                and cp.tipo = 'C' group by  i.idnotafiscal,cp.idcontapagar,i.idtipoprodserv,i.valor
                                       union all
                                         SELECT 
                                                i.descricao as descr,n.nnfe,n.idnotafiscal as idnf,cp.idcontapagar,sum(i.quantidade) as qtd,                                               
                                                round(i.valor,2) as vlritem,
                                                
                                                round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade) * (n.total/n.subtotal))*(ci.valor/n.total),2) as total,
                                                c.somarelatorio
                                                ,cp.status,cp.tipo,
                                                'SERVICO' AS tiponf,i.idtipoprodserv as idprodserv
                                                ,ci.parcela,ci.parcelas                           
                                                FROM contapagar cp
                                                join contapagaritem ci on(cp.idcontapagar =ci.idcontapagar 
                                                                            and ci.tipoobjetoorigem ='notafiscal')
                                                join notafiscal n on(ci.idobjetoorigem =n.idnotafiscal  ) 
                                                join notafiscalitens i on(i.idnotafiscal=n.idnotafiscal and i.idprodserv is null ) 
                                                join tipoprodserv p on(p.idtipoprodserv=i.idtipoprodserv " . $stridtipoprodserv . "  )
                                                join contaitem c on(c.idcontaitem=i.idcontaitem " . $viscontaitem . " )                                                   
                                                where cp.tipoespecifico = 'AGRUPAMENTO'  
                                                
                                                and cp .idempresa IN (" . $idempresa . ")   
                                                " . $clausulac . "  
                                                and i.idtipoprodserv= " . $row2["idtipoprodserv"] . "
                                                and c.idcontaitem = " . $row['idcontaitem'] . "
                                                and cp.status !='INATIVO'
                                                and ci.status!='INATIVO'
                                                and cp.tipo = 'C' group by  i.idnotafiscal,ci.idcontapagaritem,i.idtipoprodserv,i.valor
                                ) as u   
                                where u.qtd > 0  " . $strtiponf . "
                                group by u.idnf,u.descr,u.parcela,u.parcelas                   
                                order by u.descr";
                                            echo "<!--";
                                            echo $sql3;
                                            echo "-->";
                                        }

                                        $res3 =  d::b()->query($sql3) or die("Falha ao pesquisar produtos: " . mysqli_error() . "<p>SQL: $sql3");
                                        $qtd3 = mysqli_num_rows($res3);

                                        ?>
                                        <tr class="<?= $collapseitem ?>" id="nfInfo<?= $id2 ?>">
                                            <td colspan="6" class="col-md-12">

                                                <table style="width:100%; font-size: 15px;">
                                                    <tr><? //Atualzido a largura das Colunas para ficar padrão - Lidiane(10/06/2020)
                                                        ?>
                                                        <td class="col-md-1"><b>NFe</b></td>
                                                        <td class="col-md-1" align="right"><b>Qtd</b></td>
                                                        <td class="col-md-6"><b>Item</b></td>
                                                        <td class="col-md-1"><b>Parcela</b></td>
                                                        <td class="col-md-1" align="right"><b>Un R$</b></td>
                                                        <td class="col-md-1" align="right"><b>Total R$</b></td>
                                                        <td class="col-md-1" align="right"></td>
                                                    </tr>

                                                    <?
                                                    while ($row3 = mysqli_fetch_assoc($res3)) {
                                                        if ($row3["tipo"] == "C") {

                                                            // $vlrcredito =$vlrcredito+$row3["total"];
                                                            if ($row3["status"] == "QUITADO") {
                                                                $vlrquitadocredito = $vlrquitadocredito + $row3["total"];
                                                            } else {
                                                                $vlrpendcredito = $vlrpendcredito + $row3["total"];
                                                            }
                                                        } else {
                                                            //$vlrdebito =$vlrdebito+$row3["total"];
                                                            if ($row3["status"] == "QUITADO") {
                                                                $vlrquitadodebito = $vlrquitadodebito + $row3["total"];
                                                            } elseif ($row3["status"] != "ABERTO") {
                                                                $vlrpenddebito = $vlrpenddebito + $row3["total"];
                                                            }
                                                            if ($row3["status"] == "ABERTO") {
                                                                $vlrprogramado = $vlrprogramado + $row3["total"];
                                                                if (!empty($row3["idcontapagar"])) {
                                                                    $sqlpr = "select sum(valor) as valor from contapagaritem where status!='INATIVO' and idcontapagar=" . $row3["idcontapagar"];
                                                                    $respr = mysql_query($sqlpr) or die("Falha ao buscar contapagar programada " . mysql_error() . "<p>SQL: $sqlpr");
                                                                    $rowpr = mysqli_fetch_assoc($respr);
                                                                    if ($rowpr['valor'] > 0) {
                                                                        $arrprogramado[$row3["idcontapagar"]] =  $rowpr["valor"];
                                                                    }
                                                                }
                                                            }
                                                        }


                                                        //if ($_SESSION["SESSAO"]["IDEMPRESA"] != 1){
                                                        //	if($resf["tiponf"]=="V"){
                                                        //		$vtiponf = "Venda";  $modulov="pedido";
                                                        //	}else{
                                                        //Marcelo retornou o modulo para o nome de nfentrada por causa dos links que tem no sistema - Lidiane (08/06/2020)
                                                        //$vtiponf = "Compras Unificadas";  $modulov="comprasunificadas";
                                                        //	$vtiponf = "Compras Unificadas";  $modulov="nfentrada";
                                                        //	}
                                                        //}else{	
                                                        if ($row3["tiponf"] == 'V') {
                                                            $vtiponf = "Venda";
                                                            $modulov = "pedido";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'C') {
                                                            $vtiponf = "Compra";
                                                            $modulov = "nfentrada";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'O') {
                                                            $vtiponf = "Compra Outros";
                                                            $modulov = "nfentrada";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'S') {
                                                            $vtiponf = "Servi&ccedil;o";
                                                            $modulov = "nfentrada";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'T') {
                                                            $vtiponf = "Cte";
                                                            $modulov = "nfentrada";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'E') {
                                                            $vtiponf = "Consession&aacute;ria";
                                                            $modulov = "nfentrada";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'M') {
                                                            $vtiponf = "Manual/Cupom";
                                                            $modulov = "nfentrada";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'B') {
                                                            $vtiponf = "Recibo";
                                                            $modulov = "nfentrada";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'R') {
                                                            $vtiponf = "PJ";
                                                            $modulov = "comprasrh";
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'F') {
                                                            $vtiponf = "Fatura";
                                                            $modulov = "nfentrada";
                                                            $tipo = 'F';
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'D') {
                                                            $vtiponf = "Sócios";
                                                            $modulov = "comprassocios";
                                                            $tipo = 'D';
                                                            $modid = "idnf";
                                                        }
                                                        if ($row3["tiponf"] == 'SERVICO') {
                                                            $vtiponf = "Serviços";
                                                            $modulov = "nfs";
                                                            $tipo = 'D';
                                                            $modid = "idnotafiscal";
                                                        }
                                                        //}
                                                    ?>
                                                        <tr>
                                                            <td class="col-md-1">
                                                                <a class="pointer hoverazul" title="NFe" onclick="janelamodal('?_modulo=<?= $modulov ?>&_acao=u&<?= $modid ?>=<?= $row3["idnf"] ?>')">
                                                                    <b>
                                                                        <? if (!empty($row3["nnfe"])) {
                                                                            echo $row3["nnfe"];
                                                                        } else {
                                                                            echo $row3["idnf"];
                                                                        } ?>
                                                                    </b>
                                                                </a>
                                                            </td>


                                                            <td align="right" class="col-md-1">
                                                                <?= number_format(tratanumero($row3["qtd"]), 2, ',', '.'); ?>
                                                            </td>

                                                            <td class="col-md-6">
                                                                <?
                                                                    $descr = $row3["descr"];
                                                                    echo ($descr);
                                                                ?>
                                                            </td>
                                                            <td class="col-md-1">
                                                                <? if ($row3["parcela"]) {
                                                                    echo ($row3["parcela"] . "-" . $row3["parcelas"]);
                                                                } else {
                                                                    echo $row3["parcelas"];
                                                                } ?>
                                                            </td>

                                                            <td align="right" class="col-md-1">
                                                                <? if ($row3["tipo"] == "D" and $row3["vlritem"] > 0) {
                                                                    echo ("-");                                                                    
                                                                }?>
                                                                <?=number_format(tratanumero(abs($row3["vlritem"])), 2, ',', '.'); ?>
                                                            </td>

                                                            <td align="right" class="col-md-1">
                                                                <? if ($row3["tipo"] == "D" and $row3["total"] > 0 ) {
                                                                    echo ("-");
                                                                } ?>
                                                                <?= number_format(tratanumero(abs($row3["total"])), 2, ',', '.'); ?>
                                                            </td>
                                                            <td class="col-md-1" align="right">
                                                            </td>
                                                        </tr>

                                                        <? if (!empty($_REQUEST['csv'])) {
                                                            (!empty($row3["nnfe"])) ? $_nnfe = $row3["nnfe"] : $_nnfe = $row3["idnf"];
                                                            ($row3["parcela"])      ? $_parcela = $row3["parcela"] . "-" . $row3["parcelas"] : $_parcela = $row3["parcelas"];

                                                            $conteudoexport .= $row["contaitem"] . ";" . $row2["tipoprodserv"] . ";" . $_nnfe . ";" .number_format($row3["qtd"],2,',','.'). ";" . $descr . ";" . $_parcela . ";" . number_format(tratanumero($row3["vlritem"]), 2, ',', '.') . ";" . number_format(tratanumero($row3["total"]), 2, ',', '.') . ";\n";
                                                        } ?>
                                                    <?
                                                    } //while ($row3 = mysqli_fetch_assoc($res3)){
                                                    ?>

                                                </table>

                                            </td>
                                        </tr>

                                    <?

                                    } //while ($row2 = mysqli_fetch_assoc($res)){
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>

        <?

                } //$arrcontaitem
            } else {
                //if($row["tipo"]=="D"){
                //    $vlrndescriminados= $vlrndescriminados-$row["somatotal"];
                // }else{
                $vlrndescriminados = $vlrndescriminados + $row["somatotal"];
                // }

            }
        } //while ($row = mysqli_fetch_assoc($res)){
        //$vlrprogramado=0;
        foreach ($arrprogramado as $idcontapagar => $valor) {
            $vlrprogramadopagar = $vlrprogramadopagar + $valor;
        }

        $vlrcredito = $vlrquitadocredito + $vlrpendcredito;

        $vlrdebito = $vlrquitadodebito + $vlrpenddebito;

        $somatotais = $vlrcredito - $vlrdebito; //a soma do total

        $vlrprogramadox = $vlrprogramado - $vlrprogramadopagar;

        $somatotais2=$somatotais-$vlrprogramadopagar-$vlrprogramadox;

        $vlrtotal = $somatotais - $vlrprogramadox - $vlrprogramadopagar + $vlrndescriminados;

        /*
		credito recebido= todas as quitadas

		credito a receber = todas a receber

		credito total =credito recebido+credito a receber

		despesa paga= todas as pagas  quitada
		despesa  a pagar= todas despepesas não quitada status diferente aberto

		total despesa=despesa paga+despesa  a pagar

		somavalores=credito total - despesa total

		total= somavalores - despesa programada -despesa programada sem item + valoresnão descri
	*/
        ?>
</div> <!-- divbody -->
<? if ($modo != 'FT') { ?>
    <div class="panel panel-default">
        <div class="panel-heading">Despesas de <label class="alert-warning"> <?= dma($dataini) ?></label> á <label class="alert-warning"> <?= dma($datafim) ?></label></div>
        <div class="panel-body">
            <br>
            <table class="table table-striped planilha">
               
                <tr>
                    <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#c8d0ff">Crédido Recebido</td>
                        <td style="text-align: right;" bgcolor="#c8d0ff"><?= number_format(tratanumero($vlrquitadocredito), 2, ',', '.'); ?></td>
                    <? } ?>
                    <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#f0bfbf"> Despesa Paga</td>
                        <td style="text-align: right;" bgcolor="#f0bfbf"><?= number_format(tratanumero($vlrquitadodebito), 2, ',', '.'); ?></td>
                    <? } ?>
                    <?
                    if ($flgdiretor > 0) {              

                        if ($somatotais > 0) {
                            $corvalor = '#c8d0ff';
                        } else {
                            $corvalor = '#f0bfbf';
                        }
                        ?>
                        <td bgcolor="<?= $corvalor ?>">Credito Total  - Despesa Total:</td>
                        <td style="text-align: right;" bgcolor="<?= $corvalor ?>"><?= number_format(tratanumero($somatotais), 2, ',', '.'); ?> </td>       
                    <? } ?>        
                </tr>
               

                <tr>
                <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#98FB98">Crédito a Receber</td>
                        <td style="text-align: right;" bgcolor="#98FB98"><?= number_format(tratanumero($vlrpendcredito), 2, ',', '.'); ?></td>
                    <? } ?>
                    <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#FFFF00">Despesa a Pagar</td>
                        <td style="text-align: right;" bgcolor="#FFFF00"><?= number_format(tratanumero($vlrpenddebito), 2, ',', '.'); ?></td>
                        <? } ?>
                    <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="#FF8C00" title="Débito aberto com itens na fatura">Despesa Programada</td>
                        <td style="text-align: right;" bgcolor="#FF8C00" title="Débito aberto com itens na fatura"><?= number_format(tratanumero($vlrprogramadopagar), 2, ',', '.'); ?> </td>
                    <? } ?>
                </tr>
                <tr>
                    <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="">Crédito Total</td>
                        <td style="text-align: right;" bgcolor=""><b><?= number_format(tratanumero($vlrcredito), 2, ',', '.'); ?></b></td>
                    <? } ?>
                    <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                        <td bgcolor="">Despesa Total</td>
                        <td style="text-align: right;" bgcolor=""><b><?= number_format(tratanumero($vlrdebito), 2, ',', '.'); ?></b></td>
                    <? }
                  
                     if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
                         <td bgcolor="#FF8C00" style="border-bottom: 1px solid #666;" title="Diferença entre valor provisionado e item lançado, ex fat cartão credito">Despesa Provisionada</td>
                        <td style="text-align: right; border-bottom: 1px solid #666;" bgcolor="#FF8C00" title="Diferença entre valor provisionado e item lançado, ex fat cartão credito"><?= number_format(tratanumero($vlrprogramadox), 2, ',', '.'); ?> </td>
                    <? } ?>
                </tr>
                <tr>
                <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    <?
                    if ($flgdiretor > 0) {

                        if ($somatotais > 0) {
                            $corvalor = '#c8d0ff';
                        } else {
                            $corvalor = '#f0bfbf';
                        }                        
                    ?>
                        <td bgcolor="<?= $corvalor ?>"><b>Soma dos valores</b></td>
                        <td style="text-align: right;" bgcolor="<?= $corvalor ?>"><b><?= number_format(tratanumero($somatotais2), 2, ',', '.'); ?></b> </td>
                    <? } ?>
                </tr>
                <? if ($flgdiretor > 0) { ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td bgcolor="" style="border-bottom: 1px solid #666;" title="Transação de valores entre contas">Valores não Discriminados</td>
                        <td style="text-align: right; border-bottom: 1px solid #666;" bgcolor="" title="Transação de valores entre contas"><b><?= number_format(tratanumero($vlrndescriminados), 2, ',', '.'); ?></b></td>
                    </tr>
                    <?
                    if ($vlrtotal > 0) {
                        $corvalor = '#c8d0ff';
                    } else {
                        $corvalor = '#f0bfbf';
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td bgcolor="<?= $corvalor ?>" title="Soma dos valores - Despesa Programada + Valores não Descriminados"><b>Total</b></td>
                        <td style="text-align: right;" bgcolor="<?= $corvalor ?>" title="Soma dos valores-Despesa Programada+Valores não Descriminados">
                            <b><?= number_format(tratanumero($vlrtotal), 2, ',', '.'); ?></b>
                        </td>
                    </tr>
                <? } ?>
            </table>

            <hr>
            <?           
            foreach ($arrcontaitem as $idcontaitem => $v) {
               // echo [$idcontaitem]."=>".$v['faturamento']."= >".$v['total']."\n";
                if($v['faturamento']=='C'){
                    $percentual=(($v['total']*100)/$vlrcredito);
                }else{
                    $percentual=(($v['total']*100)/$vlrdebito);
                }
                
?>
                <span class="pcontaitem hidden" id='idcontaitem_<?=$idcontaitem?>'>
                     <b><?= number_format(tratanumero($percentual), 2, ',', '.'); ?>%</b>
                </span>
<?

            }
            ?>


          
            <!-- table class="table table-striped planilha" >
            <tr>
                <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
            <td bgcolor="#c8d0ff">CRÉDITO</td>
            <td bgcolor="#c8d0ff"><?= number_format(tratanumero($vlrcredito), 2, ',', '.'); ?> </td>
                <? } ?>
                <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
            <td bgcolor="#f0bfbf">DÉBITO</td>
            <td bgcolor="#f0bfbf"><?= number_format(tratanumero($vlrdebito), 2, ',', '.'); ?></td>
                <?
                }
                if ($flgdiretor > 0) {
                ?>
            <td bgcolor="<?= $cortrfim ?>">SOMA VALORES</td>
            <td bgcolor="<?= $cortrfim ?>"><?= number_format(tratanumero($somatotais), 2, ',', '.'); ?></td>
                <? } ?>
            </tr>
            <tr>
                <? if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
            <td bgcolor="#98FB98">CRÉDITO PENDENTE</td>
            <td bgcolor="#98FB98"><?= number_format(tratanumero($vlrpendcredito), 2, ',', '.'); ?></td>
                <? } ?>
                <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) {
                    $debpend = $vlrpenddebito - $vprogramado;
                ?>
            <td bgcolor="#FFFF00">DÉBITO PENDENTE</td>
            <td bgcolor="#FFFF00"><?= number_format(tratanumero($debpend), 2, ',', '.'); ?></td>
                <?
                }
                if ($flgdiretor > 0) { ?>
            <td bgcolor="#FFFACD">SOMA PENDENTES</td>
            <td bgcolor="#FFFACD"><?= number_format(tratanumero($vlrpendentefim), 2, ',', '.'); ?></td>
                <? } ?>
            </tr>

            <tr>
                <? if (array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor > 0) { ?>
            <td bgcolor="#FF8C00">PROGRAMADO</td>
            <td bgcolor="#FF8C00" title="Debito Programado/Aberto"><?= number_format(tratanumero($vlrprogramado), 2, ',', '.'); ?></td>
                <? } ?>
            </tr>
        </table>
            <br>
	    <table class="table table-striped planilha">	
		    <tr style="height: 5px;"></tr>
		    <tr>
			    <td colspan='2'><font size="2">Previsão</font></td>
			    <td align="right"><font size="2"><?= number_format(tratanumero($previsao), 2, ',', '.'); ?></font></td>
		    </tr>	
		    <tr>
			    <td colspan='2'><font size="2">Total</font></td>
			    <td align="right"><font size="2"><?= number_format(tratanumero($vtotal), 2, ',', '.'); ?></font></td>
		    </tr>
	    </table -->
        </div>
    </div>
<? } else {
?>
    <br>
    <p></p>
    <br>
<? } 

    } elseif ($ires < 1 and !empty($vencimento_1) and !empty($vencimento_2)) {
        echo '
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center" style="color:red;">Não foram encontrados valores com estas configurações.</h2>
            </div>
        </div>
        ';
    }
?>
<script>
    $('.selectpicker').selectpicker('render');
    $('#idagenciax').prop('disabled',true);
    $('#idagenciax').find('.hider').hide();
    //pega o idempresa do value do option selecinado no select de empresa
    idempresas = $('#_empresa').selectpicker('val');
    //mostra os options do select de agencias da empesa selecionada
    if(idempresas){
        idempresas.map((idempresa)=>{
            $('#idagenciax').find('option.'+idempresa).show();
            $('#idagenciax').selectpicker('refresh');
        });
        $('#idagenciax').prop('disabled',false);
        //atualiza o select de agencias
        $('#idagenciax').selectpicker('refresh');
    }
    
    

    function previsao(vthis, vidcontaitem) {
        CB.post({
            objetos: "_1_u_contaitem_idcontaitem=" + vidcontaitem + "&_1_u_contaitem_previsao=" + $(vthis).val(),
            parcial: true,
            refresh: false,
            msgSalvo: "Previsão alterada."
        });
    }

    function cor(vthis, vidcontaitem) {
        CB.post({
            objetos: "_1_u_contaitem_idcontaitem=" + vidcontaitem + "&_1_u_contaitem_cor=" + $(vthis).val(),
            parcial: true,
            refresh: true,
            msgSalvo: "cor alterada."
        });
    }
    let fired = false;
    function pesquisar(vthis) {
        event.preventDefault();
        var idagencia = $("#idagenciax").selectpicker('val');
        var idcontaitem = $("[name=idcontaitem]").val();
        if(idagencia && idcontaitem){
            var vencimento_1 = $("[name=vencimento_1]").val();
            var vencimento_2 = $("[name=vencimento_2]").val();
            var idtipoprodserv = $("[name=idtipoprodserv]").val();
            var idunidade = $("[name=idunidade]").val();
            var pesquisa = $("[name=pesquisa]").val();
            var modo = $("[name=modo]").val();
            var tiponf = $("[name=tiponf]").val();
            var idempresa = $("#_empresa").selectpicker('val');
            var str = "vencimento_1=" + vencimento_1 + "&modo=" + modo + "&idunidade="+idunidade+"&idempresa=" + idempresa + "&vencimento_2=" + vencimento_2 + "&idtipoprodserv=" + idtipoprodserv + "&idagencia=" + idagencia + "&idcontaitem=" + idcontaitem + "&pesquisa=" + pesquisa + "&tiponf=" + tiponf;
        
            $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
            CB.go(str);
        }else{
            if(!idagencia)
                alert('Campo agencia não pode estar vazio!');
            
            if(!idcontaitem)
                alert('Campo Categoria não pode estar vazia!');
        }
    }
    function imprimirResumo(vthis) {
        event.preventDefault();
        janelamodal('/report/repdespesasitem.php'+window.location.search);
    }
       // code block to exclude 'fired' from global scope
     document.addEventListener('keydown', (e) => {
            // only accept key down when was released
            if(!fired) {
                fired = true;
                // check what key pressed...
                if (e.keyCode === 13) {
                    console.log('enter');
                    $('#cbPesquisar').html('<span class="fa fa-spinner fa-pulse"></span>');
                    pesquisar();
                }
            }
        });

        document.addEventListener('keyup', (e) => {
            fired = false;
        });
    
    // document.addEventListener('keydown', (event) => {
    //     const keyName = event.key;
    //     if (keyName == "Enter") {
    //         event.preventDefault();
    //         console.log(keyName);
    //         $('#cbPesquisar').html('<span class="fa fa-spinner fa-pulse"></span>');
    //         pesquisar();
    //     }
    // });
    // $(document).keyup(function (e) {
    //     console.log(e)
    //     if (e.which == 13) {
    //         $('#cbPesquisar').html('<span class="fa fa-spinner fa-pulse"></span>');
    //         pesquisar();
    //     }
    // });

    function altflag(inid, inval) {
        CB.post({
            objetos: "_x_u_contaitem_idcontaitem=" + inid + "&_x_u_contaitem_somarelatorio=" + inval,
            parcial: true
        });
    }

    function ordenaItens() {
        $.each($(".divbody ta").find("tr"), function(i, otr) {
            //Recupera objetos de update e de insert
            $(this).find(":input[name*=nfitem_ord],:input[name*=ord]").val(i);
        })
    }

    function atualizapagamento(valor) {
        var idempresa = $("[name=_empresa]").val();
        var str = "idempresa=" + idempresa;
        CB.go(str);
    }

    //sempre que o select de empresa sofrer alteração
    $('#_empresa').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        //console.log('alterando empresa',clickedIndex, isSelected, previousValue);
        //pega o idempresa do value do option selecinado no select de empresa
        idempresa = e.currentTarget[clickedIndex].value;
        
        //mostra os options do select de agencias da empesa selecionada
        if(!previousValue){
            $('#idagenciax').find('option.'+idempresa).show();
        }else{
            $('#idagenciax').find('option.'+idempresa).hide();
            
            //deselecionar agencias junto com a deseleção de empresa
            valoresAgencia = $('#idagenciax').selectpicker('val');
            //console.log(valoresAgencia);
            if(valoresAgencia){
                $('#idagenciax').find('option.'+idempresa).each((index, el)=>{
                    let removerValor = valoresAgencia.indexOf(el.value);
                    if (removerValor > -1) {
                        valoresAgencia.splice(removerValor, 1);
                    }
                });
                $('#idagenciax').selectpicker('val', valoresAgencia);
            }
        }
        
        //testa se existe pelo menos uma empresa selecionada
        if($('#_empresa').selectpicker('val')) 
            $('#idagenciax').prop('disabled',false); //desabilita agencias
        else
            $('#idagenciax').prop('disabled',true); //habilita agencias
        
        //atualiza o select de agencias
        $('#idagenciax').selectpicker('refresh');
    });


    // copiar o texto no cabecalho
$(document).ready(function(){ 
    
    var arrayIdD = $.map($(".pcontaitem"), function(n, i){
      return n.id;
    });
    
    jQuery.each( arrayIdD, function( i, val ) {
        var texto = $( "#" + val ).html();
        $( "." + val ).html(texto);
    });
    
    
});

    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>
<?
if (!empty($_REQUEST['csv'])) {
    ob_end_clean();
    /* Gerar o nome do arquivo para exportar
     * Substitui qualquer caractere estranho pelo sinal de '_'
     * Caracteres que NAO SERAO substituidos:
     *   - qualquer caractere de A a Z (maiusculos)
     *   - qualquer caracteres de a a z (minusculos)
     *   - qualquer caractere de 0 a 9
     *   - e pontos '.'
     */
    $infilename = 'despesasitem_' . date('dmY');
    //gera o csv
    header('Content-Encoding: UTF-8');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=" . $infilename . ".csv");
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo "\xEF\xBB\xBF";

    echo $conteudoexport;
}


die; ?>
