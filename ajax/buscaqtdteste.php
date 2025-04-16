<?
require_once("../inc/php/functions.php");
require_once("../form/controllers/inclusaoresultado_controller.php");

$idprodserv= $_GET['idprodserv']; 
$idregi= $_GET['idregi']; 
$idregf= $_GET['idregf']; 
$exercicio=$_GET['exercicio'];

if(empty($exercicio)){
    echo("Favor informar o exercicio.");
}elseif(empty($idprodserv)){
    echo("Favor informar o tipo do teste.");
}elseif(empty($idregi)){
    echo("Favor informar o inicio do registro desejado.");
}elseif(empty($idregf)){
    echo("Favor informar o fim do registro desejado.");
}else{

    $sql= "select 
            count(*) as qtdteste,IFNULL(sum(quantidade),0) as qtd 
        from resultado r,amostra a
        where r.status in ('ABERTO')
        and a.status not in ('PROVISORIO', 'CANCELADO')
        and r.idtipoteste=".$idprodserv."
        and a.idamostra = r.idamostra
 	and a.idunidade=1
        and a.exercicio = ".$exercicio."
        and a.idregistro between ".$idregi." and ".$idregf;
    $res = d::b()->query($sql) or die("Erro ao buscar quantidade de testes: ".mysqli_error());

    $r = mysqli_fetch_array($res);

            //echo ("<div class='alert alert-warning'>Nº de Registros:".$r['qtdteste']." <br> Nº de Testes:".$r['qtd']."</div>");        
   $sqlf1 = "select *
                    from prodservformula
                    where idempresa = ".cb::idempresa()."
                            and idprodserv=".$idprodserv."
                    order by ordem,idprodservformula asc";

    $resf1 = d::b()->query($sqlf1) or die("E ao buscas as fazes do servico \n".mysqli_error(d::b())."\n".$sqlf1);

    $l=$idprodserv;
     while($rowf1=mysqli_fetch_assoc($resf1)){      
        $sqlf="select p.descr,i.qtdi,qtdi_exp,i.idprodserv
                from prodserv p,prodservformula f, prodservformulains i 
                  where p.idprodserv = i.idprodserv
                and f.idprodserv = ".$idprodserv."
                and i.idprodservformula = f.idprodservformula
                and f.idprodservformula=".$rowf1["idprodservformula"]."
                and f.status = 'ATIVO'
                and i.status='ATIVO'";            

        $resf =  d::b()->query($sqlf) or die("Erro ao buscar produtos  do teste:".mysqli_error(d::b())."sql=".$sqlf);
        $qtdf= mysqli_num_rows($resf);

        if($qtdf>0){
            if($rowf1["ordem"] == 1){
                $checked = "checked";
            }else{
                $checked = "";
            }
             $texto.="<div class='row'>
                <div class='col-md-12'>
                    <div class='panel panel-default' >
                        <div class='panel-heading'>Fase:".$rowf1["ordem"]." Insumos para ".$r['qtdteste']." Registros com ".$r['qtd']." Testes <input type='checkbox' class='fases' fase='#fase_".$rowf1["ordem"]."' ".$checked."></div>
                        <div class='panel-body' id='fase_".$rowf1["ordem"]."'>";
             
            $texto.="  <table class='table table-striped planilha' >
                        <tr>
                            <th>Utilizar</th>
                            <th>Produto</th>
                            <th>Lotes</th>
                            <th>Utilizando</th>
                            <th>Restante</th>
                        </tr>";
                
                while($rowf= mysqli_fetch_assoc($resf)){
                    $sqlc="SELECT l.partida,l.exercicio,l.idlote,f.idlotefracao,f.qtd as qtddisp,f.qtd_exp as qtddisp_exp,l.idprodserv, m.modulo
                             FROM lote l JOIN lotefracao f ON l.idlote=f.idlote
                             JOIN unidade u ON f.idunidade = u.idunidade
                             JOIN unidadeobjeto uo ON l.idunidade = uo.idunidade AND uo.tipoobjeto = 'modulo'
                             JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'lote' 
                            WHERE l.idprodserv =".$rowf["idprodserv"]." 
                              AND f.status='DISPONIVEL'
                              AND u.idtipounidade= 1
                              AND l.idempresa = ".cb::idempresa()."
                              AND l.status ='APROVADO'
                         ORDER BY l.idlote";

                        $resc =  d::b()->query($sqlc) or die("Erro ao buscar atribuicoes dos lotes:".mysqli_error(d::b())."sql=".$sqlc);
                        $qtdc= mysqli_num_rows($resc);
                        $qtdutilizar=$rowf['qtdi']*$r['qtd'];
                        $qtdimput=$rowf['qtdi']*$r['qtd'];

                  $texto.="<tr class='trInsumo'>
                            <td  align='right'><span class='badge sQtdpadrao'>".tratanumero($qtdutilizar)."</span></td>
                            <td class='nowrap'>".$rowf['descr']."<a title='Abrir cadastro produto' class='fa fa-bars fade pointer hoverazul' href='?_acao=u&_modulo=prodserv&_acao=u&idprodserv=".$rowf['idprodserv']."' target='_blank'></a></td>";

                            if($qtdc<1){
                            $texto.=`<td>Não foi encontrado lote disponivel!!!</td>`;

                            }else{
                               $texto.="<td >"; 


                            $qtdusando=0;
                            while($rowc=mysqli_fetch_assoc($resc)){
                                $l=$l+1;
                                $act=$l;

                                if($rowc['qtddisp']>0 and $qtdimput >0 and empty($rowc['qtdd'])){
                                    if($rowc['qtddisp']<$qtdimput){
                                        $rowc['qtdd']=$rowc['qtddisp'];
                                        $qtdimput=$qtdimput-$rowc['qtddisp'];
                                        $act='_'.$l.'_i_lotecons_';
                                    }else{                                      
                                        $rowc['qtdd']=$qtdimput;                                    
                                        $qtdimput=0;
                                       $act='_'.$l.'_i_lotecons_';
                                    }

                                }
                                $qtdusando=$rowc['qtdd']+$qtdusando;

                            $texto.="<span class='label label-primary fonte10 itemestoque' qtddisp='".tratanumero($rowc['qtddisp'])."'  idlote='".$rowc['idlote']."' data-toggle='tooltip' title='' data-original-title='".$rowc['partida']."'>
                                            <a class='branco hoverbranco' href='?_modulo=".$rowc['modulo']."&_acao=u&idlote=".$rowc['idlote']."' target='_blank'>".$rowc['partida']."/".$rowc['exercicio']."</a>
                                            <span class='badge pointer screen'  onclick=\"janelamodal('?_modulo=lote&_acao=u&idlote=".$rowc['idlote']."')\">".tratanumero($rowc['qtddisp'])."</span>";
                                            if($rowca['status']!='ESGOTADO'){ 
                                            $texto.="<a class='fa fa-minus-circle pointer branco hoververmelho fa-1x' onclick='esgotarlote(".$rowc['idlotefracao'].")' ></a>";
                                            }
                                       $texto.=" <input type='hidden' fase='".$rowf1["ordem"]."' name='".$act."idlote' value='".$rowc['idlote']."'>
                                                <input type='hidden' fase='".$rowf1["ordem"]."' name='".$act."idlotefracao' value='".$rowc['idlotefracao']."'>
                                                <input type='hidden' fase='".$rowf1["ordem"]."' name='".$act."ordem' value='".$rowf1["ordem"]."'>
                                                <input type='hidden' fase='".$rowf1["ordem"]."' name='".$act."idprodserv' value='".$rowc['idprodserv']."'>
                                                <input type='hidden' fase='".$rowf1["ordem"]."' name='".$act."qtdteste' value='".$r['qtd']."'>   
                                                <input type='text' fase='".$rowf1["ordem"]."' name='".$act."qtdd' value='".$rowc['qtdd']."' class='reset screen' cbqtddispexp='' style='width: 80px !important; background-color: white;' onkeyup='mostraConsumo(this)' onchange='atualizainput(this,".$l.")' >
                                              </span>";



                                }//
                                $restante=$qtdutilizar-$qtdusando;
                                if($restante>0){$fundo="fundolaranja";}else{$fundo="fundoverde";}

                            $texto.="</td>";
                            $texto.="<td colspan='2'>
                                    <span class='badge  sUtilizando ".$fundo.">".$qtdusando."</span>
                                </td>
                                <td>
                                    <span class='badge sRestante ".$fundo.">".$restante."</span>
                                </td>";
                           }//if($qtdc<1)

                        $texto.="</tr>";  


                }

           $texto.="</table>";
            
            
                    
                    $texto .="<div class='panel panel-default'>";
                    
                    $texto .="<div class='panel-heading'>Tags Vinculadas</div>";
                    
                    $texto .="<div class='panel-body'>";
                    $tagsVinculadas = InclusaoResultadoController::buscarTagsVinculadasAoTesteAgrupado($idprodserv);
                    foreach($tagsVinculadas as $k => $tag){
                        $texto .='<div class="panel panel-default">';
                        $texto .=' <div class="panel-heading">';
                            $texto .=$k; 
                        $texto .='</div>';
                        $texto .='<div class="panel-body" style="padding-top: 10px !important;">
                                    <table class="table table-striped planilha">';
                                    $texto .='<tr>
                                            <th style="width: 80%;">Tag</th>
                                            <th style="text-align: center;width: 20%;">
                                               Vincular
                                            </th>
                                        </tr>';
                                    
                                    foreach($tag as $t => $tagVinculada){
                                        $texto .='<tr>';
                                        $texto .=' <td style="width: 80%;">';
                                        $texto .=$tagVinculada['descr'];
                                        $texto .='</td>';
                                        $texto .='<td align="center" style="width: 20%;">';
                                                
                                                $checked = '';
                                                if(
                                                    !empty($tagVinculada['idobjetovinculo']) ||
                                                    (empty($tagVinculada['idobjetovinculo']) && in_array($tagVinculada['idtag'], array_map(function($item) {return $item['idTag'];},$tag['tagsVinculadas'])))
                                                ){
                                                    $checked = 'checked';
                                                }
                                                $texto .='<input type="checkbox" class="vincular-tag" idobjetovinculo="'.$tagVinculada['idobjetovinculo'].'" idtag="'.$tagVinculada['idtag'].'"  '.$checked.'  >';
                                                $texto .='</td>';
                                                $texto .='</tr>';
                                        }
                                        $texto .='</table>';
                                        $texto .='</div>';
                                        $texto .='</div>';
                    }

                    $texto .="</div>";
                    
                    $texto .="</div>";
                $texto .= "</div>";
                $texto .="</div>";
                $texto.="</div>";
            $texto.="</div>";

        }
       
     }//while($rowf1=mysqli_fetch_assoc($resf1))
echo($texto);    
 
}     
?>