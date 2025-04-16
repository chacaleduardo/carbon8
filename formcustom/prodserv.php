<?
require_once("../inc/php/functions.php");

class PRODSERV 
{    

    public $valoritem = 0;
    //Retorna Tipo ProdServ - Utilizado no Evento
    function getListaProdServ()
    {
        $sqlm = "SELECT idprodserv, descr
                   FROM prodserv
                  WHERE status = 'ATIVO'
                    ".getidempresa('idempresa','prodserv')."
                    AND comprado = 'Y'
               ORDER BY descr;";
        $resm =  d::b()->query($sqlm)  or die("Erro sgdoctipo campo Prompt Drop sql:".$sqlm);
        return $resm;
    }
    function getProdServ($tipoobj = null)
    {
        global $JSON;		
        $str = '';
        if($tipoobj){
            $tipoobj = explode(',',$tipoobj);
            $str = 'AND (';
            $or = '';
            foreach ($tipoobj as $key => $value) {
                $str .=$or.' '.$value.'="Y"';
                $or = ' OR';
            }
            $str .=')';
        }
        $sqlm = "SELECT idprodserv, descr
                   FROM prodserv
                  WHERE status = 'ATIVO'
                    ".getidempresa('idempresa','prodserv')."
                   ".$str."
               ORDER BY descr;";
        $resm =  d::b()->query($sqlm)  or die("Erro sgdoctipo campo Prompt Drop sql:".$sqlm);
        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($resm)) {
            $arrtmp[$i]["value"]=$r["idprodserv"];
            $arrtmp[$i]["label"]= $r["descr"];
            $i++;
        }
       return $JSON->encode($arrtmp);
    }
    
    function getUnEstoque($idprodserv,$idunidade,$converteest,$unpadrao,$unlote){
        $arrunori= getObjeto('unidade', $idunidade);
        $arrProdserv= getObjeto('prodserv', $idprodserv);
        if($converteest=='Y'){
            if($arrunori['convestoque']=='N'){
                $un=$unlote;           
            }else{
                $un=$unpadrao;           
            }
        }else{
           $un=$unpadrao;    
        }
        $vun=traduzid('unidadevolume','un','descr', $un);
        return $vun;
    }
    
    function getEstoqueLote($idlotefracao){        
        $arrlotefracao= getObjeto('lotefracao', $idlotefracao);
        $arrlote= getObjeto('lote', $arrlotefracao['idlote']);
        $arrunori= getObjeto('unidade', $arrlotefracao['idunidade']);
        $arrProdserv= getObjeto('prodserv', $arrlote['idprodserv']);
       
        if(/*$arrProdserv['uncptransf']=='Y' and */$arrunori['convestoque']=='N'){                                   
            $qtdfr=$arrlotefracao["qtd"]/$arrlote['valconvori'];
        }else{
            $qtdfr=$arrlotefracao["qtd"];
        }
        
        return $qtdfr;
    }
    
    function getEstoqueLoteReal($idlotefracao){        
        $arrlotefracao= getObjeto('lotefracao', $idlotefracao);
        $arrlote= getObjeto('lote', $arrlotefracao['idlote']);
        
        $qtdfr= number_format(tratanumero($arrlotefracao["qtd"]), 2, ',', '.').' - '.traduzid('unidadevolume','un','descr', $arrlote['unpadrao']);
        
        return $qtdfr;
    }
    function tipoalerta($idprodserv){

	$sql="select *
		from prodservtipoalerta
		where  idprodserv =".$idprodserv."
		order by tipoalerta ";  
        
	$res = d::b()->query($sql) or die("A consulta dos tipos de alerta falhou!!! : ". mysqli_error() . "<p>SQL: $sql");
        $qtdv=mysqli_num_rows($res);


	 while($row=mysqli_fetch_assoc($res)){         
            
		$title="Vinculado por: ".$row["criadopor"]." - ".dmahms($row["criadoem"],true);
                 echo "<a title='".$title."' href='javascript:void(0)' ><div class='opcoes'>".$row["tipoalerta"]."<i class='fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable' style='float:right' title='Excluir' idprodservtipoalerta='".$row["idprodservtipoalerta"]."' onclick='desvincularTipoalerta(this)'></i></div></a>";
              
	 }//while($row=mysqli_fetch_assoc($res)){


    }

    // retorna o historico de um consumo especifico
    function listalotecons($idlotecons){
       $s="select l.idlote,l.partida,l.exercicio from lotecons c join lote l on(l.idlote=c.idlote) where c.idlotecons=".$idlotecons;
       $r = d::b()->query($s) or die(" listalotecons - A consulta do lote falhou!!! : ". mysqli_error() . "<p>SQL: $s");
       $rw=mysqli_fetch_assoc($r);
?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
            <div class="panel-heading"> Partida: 
                <a  class="hoverazul pointer" onclick="janelamodal('?_modulo=lotealmoxarifado&_acao=u&idlote=<?=$rw['idlote']?>')" title="Lote">
                <?=$rw['partida']?>/<?=$rw['exercicio']?>
                </a>
            </div>
            <div class="panel-body" >		                           
            <table class="table table-striped planilha" >
        <?
        $sql=" select c.idlotecons,c.tipoobjetoconsumoespec,c.tipoobjeto,c.qtdsol,c.qtdsol_exp,c.qtdd,c.qtdd_exp,c.qtdc as qtdc,c.qtdc_exp as qtdc_exp ,c.obs,c.criadoem,c.criadopor,a.partida,a.idlote,a.exercicio,o.idobjeto,u.unidade as destino ,uori.unidade as origem,u.idunidade
		from lotecons c join lotefracao f
                        join lotefracao lf on (lf.idlotefracao=c.idobjeto  and c.tipoobjeto ='lotefracao' )
                        left join lote a on(a.idlote=lf.idlote)
						left join unidadeobjeto o on(o.tipoobjeto='modulo' 	and o.idunidade = lf.idunidade)	
                        left join "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.ready='FILTROS' and m.modulotipo = 'lote')	
                            join unidade u on(u.idunidade=lf.idunidade  and u.status='ATIVO' )
                            join unidade uori on(uori.idunidade=f.idunidade and uori.status='ATIVO')
		where c.idlotefracao=f.idlotefracao 
		and (c.qtdd > 0) 
        and c.status!='INATIVO'
        and c.idlotecons = ".$idlotecons."
		and  c.tipoobjeto = 'lotefracao'
        group by c.idlotecons";
            //echo($sql);  
	$res = d::b()->query($sql) or die(" listalotecons - A consulta dos consumos falhou!!! : ". mysqli_error() . "<p>SQL: $sql");
        $qtdv2=mysqli_num_rows($res);
        if($qtdv2>0){
            $mostroucab='Y';
        ?>

                <tr> 		
                    <th>Origem</th>
                    <th>Destino</th>			
					<th style="text-align: right !important;">Crédito</th>
                    <th style="text-align: right !important;">Débito</th>
                    <?if(!empty($unpdrado)){?>
                    <th>Un</th>
                    <?}?>
					<th>Obs</th>
					<th>Por</th>
                    <th>Em</th>
                    <th></th>
                </tr> 
    <?
            echo($tr);
	 while($row=mysqli_fetch_assoc($res)){
            $qtdd=$row["qtdd"]+ $qtdd;
            $qtdc=$row["qtdc"]+ $qtdc;
            if(empty($row['obs'])){$row['obs']=="Correção";}
            if($row['tipoobjeto']=='lote'){
                $destino=$row['partida'].'/'.$row['exercicio'];
            }else{
                $destino=$row['destino'];
            }
            //se o a unidade do modulo não tiver lote
            if(empty($row['idobjeto'])){
                $sqlmd="select o.idobjeto from unidadeobjeto o
                        join "._DBCARBON."._modulo m 
                        on (m.modulo = o.idobjeto 
                            and m.ready='FILTROS' 
                            and m.modulotipo = 'lote')
                        where (o.tipoobjeto='modulo' 	and o.idunidade =8)";

                $rmd = d::b()->query($sqlmd) or die("Falha ao link lote da unidade:".mysqli_error(d::b()));
                $rowmd=mysqli_fetch_assoc($rmd);
                $row_idobjeto=$rowmd['idobjeto'];

            }else{
                $row_idobjeto=$row['idobjeto'];
            }


?>
                <tr> 
                <?//DEBITO E O CONTRARIO DE CREDITO ORIGEM/DESTINO
                    if($row["qtdd"]>0){?>
                    <td><?=$row['origem']?></td>
                    <?if(!empty($row['idobjeto']) and $row['tipoobjeto']=='lote'){?>
                    <td onclick="janelamodal('?_modulo=<?= $row_idobjeto?>&_acao=u&idlote=<?=$row['idlote']?>');" style="cursor: pointer;">
                    <font color="blue">
                    <?}else{?>
                    <td onclick="janelamodal('?_modulo=unidade&_acao=u&idunidade=<?=$row['idunidade']?>');" style="cursor: pointer;">
                    <font color="blue">
                    <?}?>
                        <?=$destino?>  </font>	
                    </td>
                    <?}else{?>	
                       
                        <?if(!empty($row['idobjeto']) and $row['tipoobjeto']=='lote' ){?>
                                <td onclick="janelamodal('?_modulo=<?=$row['idobjeto']?>&_acao=u&idlote=<?=$row['idlote']?>');" style="cursor: pointer;">
                                <font color="blue">
                        <?}else{?>
                                <td>
                                <font>
                        <?}?>
                        <?=$destino?>  </font>	
                    </td>
                    <td><?=$row['origem']?></td>
                    <?}?>	
		            <td align="right">
                    <?
                    if($row["qtdc"]>0){
                        if(strpos(strtolower($row['qtdc_exp']),"d") 
                            or strpos(strtolower($row['qtdc_exp']),"e")){ 
                                echo recuperaExpoente(tratanumero($row["qtdc"]),$row['qtdc_exp']);
                        }else{
                                echo number_format(tratanumero($row["qtdc"]), 2, ',', '.');
                        }
                    }elseif($row["qtdsol"]>0){
                        if(strpos(strtolower($row['qtdsol_exp']),"d") 
                            or strpos(strtolower($row['qtdsol_exp']),"e")){ 
                                echo recuperaExpoente(tratanumero($row["qtdsol"]),$row['qtdsol_exp']);
                        }else{
                                echo number_format(tratanumero($row["qtdsol"]), 2, ',', '.');
                        }
                    }else{echo "";}
                       ?>
                    </td>
                    <td align="right">                    
                    <?
                    if($row["qtdd"]>0){
                        if(strpos(strtolower($row['qtdd_exp']),"d") 
                            or strpos(strtolower($row['qtdd_exp']),"e")){ 
                                echo recuperaExpoente(tratanumero($row["qtdd"]),$row['qtdd_exp']);
                        }else{
                                echo number_format(tratanumero($row["qtdd"]), 2, ',', '.');
                        }
                    }else{echo "";}
                       ?>
                    </td> 
                    <?if(!empty($unpdrado)){?>
                    <td><?=$unpdrado?></td>
                    <?}?>
                    <td ><?=$row['obs']?></td>
                    <td ><?=$row['criadopor']?></td> 
                    <td ><?=dmahms($row['criadoem'])?></td> 
                    <td>
                        <?/*if($_SESSION["SESSAO"]["USUARIO"]==$row['criadopor'] and empty($row['tipoobjetoconsumoespec'])){?>
                            <i class="fa fa-trash cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="(<?=$row['idlotecons']?>)" title="Excluir"></i>
                        <?}*/?>
                    </td>
                </tr>
<?
	    }
            
    }//if($qtdv2>0){
?>
            </table>
            </div>
            </div>
        </div>
    </div>
<?  
    }
    
    // retorna o historico dos consumos do lote total ou por lotefracao
    //lote.php - prodserv.php - pedido.php
    function historicolotecons($idlote,$idunidade=null){
        $qtdd=0;
        $qtdc=0;
        $vidprodserv= traduzid('lote', 'idlote', 'idprodserv', $idlote);
       

        //$sqlu=" select ifnull(un,'') as campo from prodserv where idprodserv=".$vidprodserv;

        $sqlu="select ifnull(unpadrao,'') as campo from lote where idlote=".$idlote;
        $resu = d::b()->query($sqlu) or die("Erro ao buscar unidade!!! : ". mysqli_error() . "<p>SQL: $sqlu");
        $rowu=mysqli_fetch_assoc($resu);
        $unpdrado = $rowu['campo'];
?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
            <div class="panel-heading">			                           
            <table class="table table-striped planilha" >
<?
    if(!empty($idunidade)){
        $strun=" and f.idunidade=".$idunidade." ";

        $idtipounidade= traduzid('unidade', 'idunidade', 'idtipounidade', $idunidade);

       // producao tipo 5 almf tipo 3
        if($idtipounidade==3){
            $sql1="select f.qtdini,f.qtdini_exp,f.idlote,f.idlotefracaoorigem,l.partida,l.exercicio,l.idnfitem,p.fabricado,p.comprado,lf.idunidade,u.unidade,lu.idunidade as idunidadelote,lu.unidade as unidadelote,n.idnf,n.nnfe,fo.idformalizacao,f.criadopor,dmahms(f.criadoem) as criadoem
            from lotefracao f join lote l on(l.idlote=f.idlote)
            join prodserv p on(p.idprodserv=l.idprodserv)
            left join lotefracao lf on(lf.idlotefracao=f.idlotefracaoorigem)
            left join nfitem i on(i.idnfitem=l.idnfitem)
            left join nf n on(n.idnf=i.idnf)
            left join unidade u on(u.idunidade=lf.idunidade)
            left join formalizacao fo on(fo.idlote = f.idlote)
            join unidade lu on(lu.idunidade=l.idunidade)
            join unidade fu on(fu.idunidade=f.idunidade /* and fu.idtipounidade=3*/)
            where f.idlote  = ".$idlote." and f.idunidade=".$idunidade." ";
    
            $res1 = d::b()->query($sql1) or die("A consulta da quantidade inicial falhou!!! : ". mysqli_error() . "<p>SQL: $sql1");
            $qtdv3=mysqli_num_rows($res1);
            if($qtdv3>0){
                $unidade= traduzid('unidade', 'idunidade', 'unidade', $idunidade);
                $row1=mysqli_fetch_assoc($res1);
                if(!empty($row1['idlotefracaoorigem'])){
                    if (!empty($row1['idformalizacao'])) {
                        $icone = ' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo=formalizacao&_acao=u&idformalizacao='.$row1['idformalizacao'].'\');">';
                    }else {
                        $icone = '';
                    }
                    $origem=$row1['unidade'].$icone;
                }elseif(!empty($row1['idnfitem'])){
                    $origem='Compras <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo=nfentrada&_acao=u&idnf='.$row1['idnf'].'\');"></i>';
                }else{
                    $sqllink="select o.idobjeto from  unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) 
                    JOIN "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote')
                     where (o.tipoobjeto = 'modulo' and m.status='ATIVO' AND  o.idunidade =".$row1['idunidadelote'].")";
                    $res3=d::b()->query($sqllink) or die("Erro ao buscar unidade origem3 sql=".$sqllink);
                    $rlink = mysqli_fetch_assoc($res3);
                    $origem=$row1['partida']."/".$row1['exercicio'].' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo='.$rlink['idobjeto'].'&_acao=u&idlote='.$idlote.'\');"></i>';
                }
                if(strpos(strtolower($row1['qtdini_exp']),"d") 
                    or strpos(strtolower($row1['qtdini_exp']),"e")){ 
                    $valor= recuperaExpoente(tratanumero($row1["qtdini"]),$row1['qtdini_exp']);
                    $qtdc=$qtdc+$valor;
                }else{
                    $valor= number_format(tratanumero($row1["qtdini"]), 2, ',', '.');
                    $qtdc=$qtdc+$valor;
                }
    
                $tr="<tr>
                        <td nowrap>".$origem."</td>
                        <td>".$unidade."</td>			
                        <td align='right'>".$valor."</td>
                        <td></td>";
                        if(!empty($unpdrado)){
                            $tr.="<td>".$unpdrado."</td>";
                        }
                        $tr.="<td></td>
                        <td>".$row1['criadopor']."</td>
                        <td>".dmahms($row1['criadoem'])."</td>
                        <td></td>
                    </tr>";
            }

        }else{

            $sql1="select lt.idunidade as idunidadelt,l.*,f.idformalizacao 
            from lotefracao l 
            left join formalizacao f on (l.idlote = f.idlote)
            join lote lt on(lt.idlote=l.idlote)
                where l.idlote= ".$idlote." and l.idunidade =".$idunidade;

                echo('<!--'. $sql1.'-->');
             
            $res1 = d::b()->query($sql1) or die("A consulta da quantidade inicial falhou!!! : ". mysqli_error() . "<p>SQL: $sql1");
            $qtdv3=mysqli_num_rows($res1);
            if($qtdv3>0){
               
                $o_idunidade=$idunidade;
                $row1=mysqli_fetch_assoc($res1);
                if(!empty($row1['idunidadelt'])){
                    $_idunidade=$row1['idunidadelt'];
                    $o_idunidade=$row1['idunidade'];
                }else{
                    $_idunidade=$o_idunidade;
                }
                $_idtipounidade= traduzid('unidade', 'idunidade', 'idtipounidade', $_idunidade);
                $unidade= traduzid('unidade', 'idunidade', 'unidade', $o_idunidade);
                $sqllink="select o.idobjeto from  unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) 
                JOIN "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.status='ATIVO' and m.modulotipo = 'lote')
                 where (o.tipoobjeto = 'modulo' AND  o.idunidade =".$o_idunidade.")";
                $res3=d::b()->query($sqllink) or die("Erro ao buscar unidade origem2 sql=".$sqllink);
                $rlink = mysqli_fetch_assoc($res3);

                if($_idtipounidade==8 and !empty($row1['idformalizacao'])){
                    $origem='Meios';
                    if (!empty($row1['idformalizacao'])) {
                        $icone = ' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo=formalizacao&_acao=u&idformalizacao='.$row1['idformalizacao'].'\');">';
                    }else {
                        $icone = '';
                    }
                    $origem .=$icone;
                }elseif($_idtipounidade==5){
                    if(empty($row1['idlotefracaoorigem'])){
                        $origem='Produção';
                        if (!empty($row1['idformalizacao'])) {
                            $icone = ' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo=formalizacao&_acao=u&idformalizacao='.$row1['idformalizacao'].'\');">';
                        }else {
                            $icone = '';
                        }
                    }else{
                        $sqlo="select * from lotefracao where idlotefracao =".$row1['idlotefracaoorigem'];
                        $reso=d::b()->query($sqlo) or die("Erro ao buscar fracao origem3 sql=".$sqlo);
                        $rowo=mysqli_fetch_assoc($reso);
                        $o_idunidade=$rowo['idunidade'];
                        $origem= traduzid('unidade', 'idunidade', 'unidade', $o_idunidade);
                        $sqllink="select o.idobjeto from  unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) 
                        JOIN "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.status='ATIVO' and m.modulotipo = 'lote')
                         where (o.tipoobjeto = 'modulo' AND  o.idunidade =".$o_idunidade.")";
                        $res3=d::b()->query($sqllink) or die("Erro ao buscar unidade origem 3 sql=".$sqllink);
                        $rlink = mysqli_fetch_assoc($res3);
                        if (!empty($rlink['idobjeto'])) {
                            $icone=' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo='.$rlink['idobjeto'].'&_acao=u&idlote='.$idlote.'\');"></i>';
                        }else{
                            $icone = '';
                        }
                    }

                   
                    $origem .=$icone;
                }else{
                    $origem='Deslocamento';
                }
                if(strpos(strtolower($row1['qtdini_exp']),"d") 
                    or strpos(strtolower($row1['qtdini_exp']),"e")){ 
                    $valor= recuperaExpoente(tratanumero($row1["qtdini"]),$row1['qtdini_exp']);
                    $qtdc=$qtdc+$valor;
                }else{
                    $valor= number_format(tratanumero($row1["qtdini"]), 2, ',', '.');
                    $qtdc=$qtdc+$valor;
                }
    
                $tr="<tr>
                        <td nowrap>".$origem."</td>
                        <td>".$unidade."</td>			
                        <td align='right'>".$valor."</td>
                        <td></td>";
                        if(!empty($unpdrado)){
                            $tr.="<td>".$unpdrado."</td>";
                        }
                        $tr.="<td></td>
                        <td>".$row1['criadopor']."</td>
                        <td>".dmahms($row1['criadoem'])."</td>
                        <td></td>
                    </tr>";
            }

        }


    }
        

	  $sql="select c.idlotecons,c.tipoobjetoconsumoespec,c.tipoobjeto,c.qtdsol,c.qtdsol_exp,c.qtdd,c.qtdd_exp,c.qtdc,c.qtdc_exp,c.obs,c.criadoem,c.criadopor,a.partida,ifnull(a.idloteorigem,a.idlote) as idlote,a.exercicio,o.idobjeto,u.unidade as destino,uori.unidade as origem,uori.idunidade,c.idtransacao,c.status,c.idobjetoconsumoespec
		from lotecons c 
			join lotefracao f
			left join lote a on (a.idlote=c.idobjeto  and c.tipoobjeto ='lote' )
                        left join unidadeobjeto o on(o.tipoobjeto='modulo' 
                                                    and o.idunidade = a.idunidade and o.idobjeto like 'lote%')	
                      left join "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.ready='FILTROS' and m.modulotipo = 'lote' and m.status='ATIVO')	
                        join unidade u on(u.idunidade=a.idunidade )
                        join unidade uori on(uori.idunidade=f.idunidade)
		where c.idlote=f.idlote ".$strun." and f.idlote  = ".$idlote."
                and c.idlotefracao = f.idlotefracao
		and( c.qtdd > 0 or qtdsol>0)
		and (c.tipoobjeto is null or c.tipoobjeto = 'lote')
		group by idlotecons
		union
            select c.idlotecons,c.tipoobjetoconsumoespec,c.tipoobjeto,c.qtdsol,c.qtdsol_exp,qtdd as qtdd, qtdd_exp as qtdd_exp,c.qtdc as qtdc,c.qtdc_exp as qtdc_exp ,c.obs,c.criadoem,c.criadopor,a.partida,a.idlote,a.exercicio,o.idobjeto,u.unidade as destino ,uori.unidade as origem,uori.idunidade,c.idtransacao,c.status,c.idobjetoconsumoespec
		from lotecons c join lotefracao f
                        join lotefracao lf on (lf.idlotefracao=c.idobjeto  and c.tipoobjeto ='lotefracao' )
                        left join lote a on(a.idlote=lf.idlote)
						left join unidadeobjeto o on(o.tipoobjeto='modulo' 	and o.idunidade = lf.idunidade  and o.idobjeto like 'lote%')	
                        left join "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.ready='FILTROS' and m.modulotipo = 'lote' and m.status='ATIVO')	
                            join unidade u on(u.idunidade=lf.idunidade  )
                            join unidade uori on(uori.idunidade=f.idunidade )
		where c.idlotefracao=f.idlotefracao  ".$strun." and f.idlote  = ".$idlote."
		and (c.qtdc > 0) 
		and  c.tipoobjeto = 'lotefracao'
		group by idlotecons
                union
                select c.idlotecons,c.tipoobjetoconsumoespec,c.tipoobjeto,c.qtdsol,c.qtdsol_exp,c.qtdd,c.qtdd_exp,c.qtdc as qtdc,c.qtdc_exp as qtdc_exp ,c.obs,c.criadoem,c.criadopor,a.partida,a.idlote,a.exercicio,o.idobjeto,u.unidade as destino ,uori.unidade as origem,uori.idunidade,c.idtransacao,c.status,c.idobjetoconsumoespec
		from lotecons c join lotefracao f
                        join lotefracao lf on (lf.idlotefracao=c.idobjeto  and c.tipoobjeto ='lotefracao' )
                        left join lote a on(a.idlote=lf.idlote)
						left join unidadeobjeto o on(o.tipoobjeto='modulo' 	and o.idunidade = lf.idunidade and o.idobjeto like 'lote%')	
                        left join "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.ready='FILTROS' and m.modulotipo = 'lote'  and m.status='ATIVO')	
                            join unidade u on(u.idunidade=lf.idunidade  )
                            join unidade uori on(uori.idunidade=f.idunidade)
		where c.idlotefracao=f.idlotefracao  ".$strun." and f.idlote  = ".$idlote."
		and (c.qtdd > 0) 
		and  c.tipoobjeto = 'lotefracao'
                group by idlotecons                
                union 
        select c.idlotecons,c.tipoobjetoconsumoespec,'adicao',c.qtdsol,c.qtdsol_exp,c.qtdd,c.qtdd_exp,c.qtdc,c.qtdc_exp ,c.obs,c.criadoem,c.criadopor,'' as partida, '' as idlote,'' as  exercicio,'' as  idobjeto,
                    CASE
                            WHEN c.qtdd > 0 THEN 'Retirada'   
                            ELSE 'Adição'
                    END as  destino,uori.unidade as origem,uori.idunidade,c.idtransacao,c.status,c.idobjetoconsumoespec
            from lotecons c 
                    join lotefracao f
                    join unidade uori on(uori.idunidade=f.idunidade   )
            where c.idlotefracao=f.idlotefracao ".$strun." and f.idlote =".$idlote."
            and (c.qtdd > 0 or qtdc>0) 
            and (c.tipoobjeto is null and  c.idobjeto is null)
        order by criadoem asc";
      //echo($sql);  
	$res = d::b()->query($sql) or die("A consulta dos consumos falhou!!! : ". mysqli_error() . "<p>SQL: $sql");
        $qtdv2=mysqli_num_rows($res);
        if($qtdv2>0 or  $qtdv3>0){
            $mostroucab='Y';
        ?>

                <tr> 		
                    <th>Origem</th>
                    <th>Destino</th>			
					<th style="text-align: right !important;">Crédito</th>
                    <th style="text-align: right !important;">Débito</th>
                    <?if(!empty($unpdrado)){?>
                    <th>Un</th>
                    <?}?>
					<th>Obs</th>
					<th>Por</th>
                    <th>Em</th>
                    <th></th>
                </tr> 
    <?
            echo($tr);
	 while($row=mysqli_fetch_assoc($res)){
            $qtdd=$row["qtdd"]+ $qtdd;
            $qtdc=$row["qtdc"]+ $qtdc;
            if(empty($row['obs'])){$row['obs']=="Correção";}
            if($row['obs'] == "Transferência na solicitacão de materiais" or $row['obs'] == "Lote Fracionado."){
                $sqls = "SELECT idsolmatitem,idsolmat from solmatitem where idsolmatitem =".$row['idobjetoconsumoespec'];
                $ress = d::b()->query($sqls);
                $rownn = mysqli_num_rows($ress);
                if($rownn>0){
                    $rows=mysqli_fetch_assoc($ress);
                    if(!empty($rows['idsolmat']) and $row['obs'] != null){
                        $row['obs'] = 'solmat='.$rows['idsolmat'];
                        $obs1 =$row['obs'];
                    }
                }
                
            }
            if($row['tipoobjeto']=='lote'){
                $destino=$row['partida'].'/'.$row['exercicio'];
            }else{
                $destino=$row['destino'];
            }
            //se o a unidade do modulo não tiver lote
            if(empty($row['idobjeto'])){
                $sqlmd="select o.idobjeto from unidadeobjeto o
                        join "._DBCARBON."._modulo m 
                        on (m.modulo = o.idobjeto
                         and m.status='ATIVO'
                            and m.ready='FILTROS' 
                            and m.modulotipo = 'lote')
                        where (o.tipoobjeto='modulo' 	and o.idunidade =8)";

                $rmd = d::b()->query($sqlmd) or die("Falha ao link lote da unidade:".mysqli_error(d::b()));
                $rowmd=mysqli_fetch_assoc($rmd);
                $row_idobjeto=$rowmd['idobjeto'];

            }else{
                $row_idobjeto=$row['idobjeto'];
            }

            if($row["status"] == 'ABERTO'){
                $title = 'ATIVO';
                $cor = 'background-color:';
            }elseif($row["status"] == 'ALIQUOTA'){
                $title = 'ALIQUOTA';
                $cor = 'background-color:';
            }else{
                $cor = 'background-color: #dcdcdc;opacity: 0.5;';
                $title = 'INATIVO';
            }
?>
                <tr style='<?=$cor?>' title="<?=$title?>"> 
                <?//DEBITO E O CONTRARIO DE CREDITO ORIGEM/DESTINO
                    if($row["qtdd"]>0){
                        
                    $sqllink="select o.idobjeto from  unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) 
                    JOIN "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote')
                     where (o.tipoobjeto = 'modulo' and m.status='ATIVO' AND  o.idunidade =".$row['idunidade'].")";
                    $res3=d::b()->query($sqllink) or die("Erro ao buscar unidade origem1 sql=".$sqllink);
                    $rlink = mysqli_fetch_assoc($res3);
                    if($row['tipoobjeto']=='lote'){
                        $icone='';
                    }else{
                        $icone = ' <i class="fa fa-bars pointer" style="color: #337ab7"  onclick="janelamodal(\'?_modulo='.$rlink['idobjeto'].'&_acao=u&idlote='.$row['idlote'].'\');"></i>';
                    }
                    ?>
                    <td nowrap><?=$row['origem'].$icone?></td>
                    <?if(!empty($row['idobjeto']) and $row['tipoobjeto']=='lote'){?>
                    <td onclick="janelamodal('?_modulo=<?= $row_idobjeto?>&_acao=u&idlote=<?=$row['idlote']?>');" style="cursor: pointer;">
                    <font color="blue">
                    <?}else{?>
                    <td>
                    <font>
                    <?}?>
                        <?=$destino?>  </font>	
                    </td>
                    <?}else{?>	
                       
                        <?if(!empty($row['idobjeto']) and $row['tipoobjeto']=='lote' ){?>
                                <td onclick="janelamodal('?_modulo=<?=$row['idobjeto']?>&_acao=u&idlote=<?=$row['idlote']?>');" style="cursor: pointer;">
                                <font color="blue">
                        <?}else{?>
                                <td>
                                <font>
                        <?}?>
                        <?=$destino?>  </font>	
                    </td>
                    <td><?=$row['origem']?></td>
                    <?}?>	
		            <td align="right">
                    <?
                    if($row["qtdc"]>0){
                        if(strpos(strtolower($row['qtdc_exp']),"d") 
                            or strpos(strtolower($row['qtdc_exp']),"e")){ 
                                echo recuperaExpoente(tratanumero($row["qtdc"]),$row['qtdc_exp']);
                        }else{
                                echo number_format(tratanumero($row["qtdc"]), 2, ',', '.');
                        }
                    }elseif($row["qtdsol"]>0){
                        if(strpos(strtolower($row['qtdsol_exp']),"d") 
                            or strpos(strtolower($row['qtdsol_exp']),"e")){ 
                                echo recuperaExpoente(tratanumero($row["qtdsol"]),$row['qtdsol_exp']);
                        }else{
                                echo number_format(tratanumero($row["qtdsol"]), 2, ',', '.');
                        }
                    }else{echo "";}
                       ?>
                    </td>
                    <td align="right">                    
                    <?
                    if($row["qtdd"]>0){
                        if(strpos(strtolower($row['qtdd_exp']),"d") 
                            or strpos(strtolower($row['qtdd_exp']),"e")){ 
                                echo recuperaExpoente(tratanumero($row["qtdd"]),$row['qtdd_exp']);
                        }else{
                                echo number_format(tratanumero($row["qtdd"]), 2, ',', '.');
                        }
                    }else{echo "";}
                       ?>
                    </td> 
                    <?if(!empty($unpdrado)){?>
                    <td><?=$unpdrado?></td>
                    <?}?>
                    <td 
                        <?if(!empty($rows['idsolmat']) and $row['obs'] == $obs1){?>
                        style="color: #337ab7;text-decoration: none;cursor: pointer;" 
                        onclick="janelamodal('?_modulo=solmat&_acao=u&idsolmat=<?=$rows['idsolmat']?>')"
                        <?}?>>
                        <?=$row['obs']?>
                    </td>
                    <td ><?=$row['criadopor']?></td> 
                    <td ><?=dmahms($row['criadoem'])?></td> 
                    <td>
                        
                        <?
                        if(!empty($row['idtransacao'])and $row["status"] == 'ABERTO'){
                            $sqlgs= "SELECT group_concat(c.idlotecons) as ids, f.idlotefracao from lotecons c left join lotefracao f on (c.idtransacao = f.idtransacao) where c.idtransacao =".$row['idtransacao'];
                            $rgs = d::b()->query($sqlgs);
                            $rowgs=mysqli_fetch_assoc($rgs);
                            $idtransacao = $rowgs['ids'];
                            if(!empty($rowgs['idlotefracao'])){
                                $idlotefracao = ','.$rowgs['idlotefracao'];
                            }else{
                                $idlotefracao = ',null';
                            }
                            ?>
                            <i class="fa fa-trash cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="excluirloteconst(<?=$idtransacao?><?=$idlotefracao?>)" title="Excluir"></i>
                        <?}elseif(array_key_exists("ajustelote", getModsUsr("MODULOS"))){?>
                            <i class="fa fa-trash cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="excluirlotecons(<?=$row['idlotecons']?>)" title="Excluir consumo"></i>
                        <?}?>
                    </td>
                </tr>
               
<?

	 }
            
        }//if($qtdv2>0){

?>
                <tr>
                    <td colspan="9"><hr></td>
                </tr> 

<?           

            $sql="select c.idlotecons,c.qtdd,c.qtdd_exp,c.qtdc,c.qtdc_exp,c.obs,c.criadoem,c.criadopor,c.idobjeto,c.tipoobjeto,u.unidade,f.idlote
                    from lotecons c join lotefracao f
                        left join unidadeobjeto o on(o.tipoobjeto='modulo' 
                        and o.idunidade = f.idunidade)	
                        join "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.ready='FILTROS' and m.modulotipo = 'lote')	
                        join unidade u on(u.idunidade=f.idunidade  and u.idtipounidade != 5)
                    where c.idlote= f.idlote
                        and c.idlotefracao=f.idlotefracao
                        and (c.qtdd > 0 or qtdc>0)
                        and c.tipoobjeto in ('nfitem', 'resultado')
                        ".$strun."
                        and f.idlote =".$idlote."
                    order by c.criadoem asc";  
                
            $res = d::b()->query($sql) or die("A consulta das vendas falhou!!! : ". mysqli_error() . "<p>SQL: $sql");
                $qtdv=mysqli_num_rows($res);
                if($qtdv>0){
                    if($mostroucab!='Y'){
                        $mostroucab='Y';
                        ?>                
                        <tr> 
                            <th>Origem</th>
                            <th>Destino</th>
                           
                            <th>Crédito</th>
                            <th>Débito</th>
                            <?if(!empty($unpdrado)){?>
                            <th>Un</th>
                            <?}?>
                            <th>Obs</th>
                            <th>Por</th>
                            <th>Em</th>
                            <th></th>
                        </tr> 
        <?
                         echo($tr);
                    }
        
        
             while($row=mysqli_fetch_assoc($res)){
                $qtdd=$row["qtdd"]+ $qtdd;
                $qtdc=$row["qtdc"]+ $qtdc;
                    if($row['tipoobjeto']=='nfitem'){
                     $_sql="select n.nnfe,n.idnf,p.nome
                                from nfitem i,nf n,pessoa p
                                where n.idnf = i.idnf 
                                and n.status  !='CANCELADO'
                                and n.idpessoa = p.idpessoa
                                and i.idnfitem =".$row['idobjeto'];
                     $_res=d::b()->query($_sql) or die("A consulta das vendas falhou!!! : ". mysqli_error() . "<p>SQL:".$_sql);
                     $_numrows= mysqli_num_rows($_res);
                    
                     $_row=mysqli_fetch_assoc($_res);
                     //$descr=$_row['nnfe'];
                     if(!empty($_row["nnfe"])){
                        $destino=$_row['nome']." NFe=".$_row['nnfe'];
                        $cortr = "";
                     }else{
                        $destino=$_row['nome']." Pedido=".$_row['idnf'];
                        $cortr= '#ffff7b';
                     }
                     
                     $id=$_row['idnf'];
                     $tab='pedido';
                     $title="NFe";
                     $obs="Venda";
                 }elseif($row['tipoobjeto'] == 'resultado'){ /** Acrescentar Mostrar o Consumo do Lote Tarefa nº 294246 em 08/01/2020 - Lidiane */
                     $_sql="SELECT A.idamostra, A.idunidade, concat('Registro: ', A.idregistro,'/', A.exercicio) AS descr 
                        FROM resultado R INNER JOIN amostra A ON R.idamostra = A.idamostra 
                        WHERE idresultado = ".$row['idobjeto'];
                     $_res=d::b()->query($_sql) or die("A consulta das vendas falhou!!! : ". mysqli_error() . "<p>SQL:".$_sql);
                     $_numrows= mysqli_num_rows($_res);
                     $_row=mysqli_fetch_assoc($_res);
                     //$descr=$_row['partida']."/".$_row['exercicio'];
                     $destino=$_row['descr'];
                     $id=$_row['idlote'];
                     $tab='lote';
                     $title="Lote";
                     $obs="";
                 } 
                 
                 if($_numrows>0){//vendas
        ?>
                        <tr style="background: <?=$cortr?>;"> 
                            <td ><?=$row['unidade']?></td> 
                            <td class="">
                            <? /* Valida se o Tipo Objeto é Resultado, caso seja, insere o link no Resultado */ 
                            if($row['tipoobjeto'] == 'resultado') { 
                                    //Recuperar o modulo de resultados associado conforme a unidade
                                    $modResultadosPadrao = getModuloResultadoPadrao($_row['idunidade']);
                            ?>
                                    
                                    <a href="#" onclick="janelamodal('?_modulo=<?php echo $modResultadosPadrao?>&_acao=u&idresultado=<?php echo $row['idobjeto'];?>')"><?=$destino?></a>
                                   
                            <? }elseif($row['tipoobjeto']=='nfitem'){
                                ?>
                                    
                                    <a href="#" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?echo $id;?>')"><?=$destino?></a>
                                    
                            <?
                                }else { ?>
                                    <?=$destino?>
                            <? } ?>
                            </td>
                           
                            <td align="right" >
                                <?
                                if($row['qtdc']>0){
                                    if(strpos(strtolower($row['qtdc_exp']),"d") 
                                        or strpos(strtolower($row['qtdc_exp']),"e")){ 
                                            echo recuperaExpoente(tratanumero($row["qtdc"]),$row['qtdc_exp']);
                                    }else{
                                            echo number_format(tratanumero($row["qtdc"]), 2, ',', '.');
                                    }
                                }else{echo "";}
                                ?>
                            </td> 
                            <td align="right">
                            <?
                            if($row['qtdd']>0){
                                if(strpos(strtolower($row['qtdd_exp']),"d") 
                                    or strpos(strtolower($row['qtdd_exp']),"e")){ 
                                        echo recuperaExpoente(tratanumero($row["qtdd"]),$row['qtdd_exp']);
                                }else{
                                        echo number_format(tratanumero($row["qtdd"]), 2, ',', '.');
                                }
                            }else{echo "";}
                            ?>
                            </td> 
                            <?if(!empty($unpdrado)){?>
                            <td><?=$unpdrado?></td>
                            <?}?>
                            <td ><?=$obs?></td>
                            <td ><?=$row['criadopor']?></td> 
                            <td ><?=dmahms($row['criadoem'])?></td> 
                        </tr>
        <?
                 }
             }//while($row=mysqli_fetch_assoc($res)){
                }//if($qtdv>0){

            $sqlr = "SELECT r.qtd, r.qtd_exp, r.criadopor, r.criadoem, u.unidade, n.nnfe, n.idnf, p.nome
                        FROM lotereserva r 
                            JOIN lote l ON (r.idlote = l.idlote) 
                            JOIN unidade u ON (l.idunidade = u.idunidade)
                            JOIN nfitem i ON (r.idobjeto = i.idnfitem)
                            JOIN nf n ON (i.idnf = n.idnf)
                            JOIN pessoa p ON (n.idpessoa = p.idpessoa)
                        WHERE r.status = 'PENDENTE' AND 
                            r.qtd > 0 AND 
                            r.tipoobjeto = 'nfitem' AND
                            r.idlote = ".$idlote;
                            
            $resr = d::b()->query($sqlr) or die("Erro ao cunsultar lotereserva [model]->prodserv: ".$sqlr);
            $qtdr = mysqli_num_rows($resr);
            if($qtdr > 0){
                if($mostroucab != 'Y'){?>
                    <tr> 
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Crédito</th>
                        <th>Débito</th>
                        <?if(!empty($unpdrado)){?>
                        <th>Un</th>
                        <?}?>
                        <th>Obs</th>
                        <th>Por</th>
                        <th>Em</th>
                        <th></th>
                    </tr> 
                <?}
                
                while($rr = mysqli_fetch_assoc($resr)){
                    $qtdd=$row["qtdd"]+ $qtdd;
                    $qtdc=$row["qtdc"]+ $qtdc;
                    ?>
                    
                    <tr style="background:#ffff7b;">
                        <td><?=$rr["unidade"]?></td>
                        <td>
                            <a href="#" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$rr['idnf']?>')">
                            <?
                            if(!empty($rr["nnfe"])){
                                echo $rr['nome']." NFe=".$rr['nnfe'];
                            }else{
                                echo $rr['nome']." Pedido=".$rr['idnf'];
                            }
                            ?>
                            </a>
                        </td>
                        <td align="right"></td>
                        <td align="right">
                        <?
                            if($rr['qtd']>0){
                                if(strpos(strtolower($rr['qtd_exp']),"d") 
                                    or strpos(strtolower($rr['qtd_exp']),"e")){ 
                                        echo recuperaExpoente(tratanumero($rr["qtd"]),$rr['qtd_exp']);
                                }else{
                                        echo number_format(tratanumero($rr["qtd"]), 2, ',', '.');
                                }
                            }else{echo "";}
                        ?>
                        </td>
                        <?if(!empty($unpdrado)){?>
                        <td><?=$unpdrado?></td>
                        <?}?>
                        <td>Reserva</td>
                        <td><?=$rr["criadopor"]?></td>
                        <td><?=dmahms($rr["criadoem"])?></td>
                    </tr>

                <?}
            }
            if(!empty($idlote) and !empty($idunidade)){
                
                $sqld="select sum(qtdini) as qtdc,sum(qtdd) as qtdd, sum(qtdd)-sum(qtdini) as qtddif,qtdprod_exp
                        from(
                            select qtdini,0 as qtdd,l.qtdprod_exp  from lotefracao lf join lote l on(lf.idlote=l.idlote) where lf.idlote=".$idlote." and lf.idunidade= ".$idunidade."
                            union all
                            select c.qtdc as qtdini,0 as qtdd,l.qtdprod_exp from lotefracao lf join lote l on(lf.idlote=l.idlote)
                             join lotecons c on(c.idlotefracao = lf.idlotefracao AND c.status = 'ABERTO')
                            where lf.idlote =".$idlote."  and lf.idunidade= ".$idunidade." and c.qtdc>0
                            union all
                            select 0 as qtdini, c.qtdd,l.qtdprod_exp  from lotefracao lf join lote l on(lf.idlote=l.idlote) join lotecons c on(c.idlotefracao = lf.idlotefracao and c.status = 'ABERTO')
                            where lf.idlote =".$idlote."  and lf.idunidade= ".$idunidade." and c.qtdd>0
                        ) as u";
                $resd = d::b()->query($sqld) or die("Erro ao cunsultar os debitos e creditos ".$sqld);
                $qtddeb = mysqli_num_rows($resd);

                if($qtddeb>0){
                    $rowd=mysqli_fetch_assoc($resd);
                    ?>
                    <tr>
                        <td><?echo("<!--". $sqld." -->");?></td>
                        <td></td>
                        <td style="background-color: #3fff0052;" title="Crédito" align="right" >
                        <?
                        if(strpos(strtolower($rowd['qtdprod_exp']),"d") 
                                    or strpos(strtolower($rowd['qtdprod_exp']),"e")){ 
                                        echo recuperaExpoente(tratanumero($rowd["qtdc"]),$rowd['qtdprod_exp']);
                                }else{
                                        echo number_format(tratanumero($rowd["qtdc"]), 2, ',', '.');
                                }
                        ?>
                        </td>
                        <td style="background-color: #ff000052;" title="Débito" align="right" >
                        <?
                        if(strpos(strtolower($rowd['qtdprod_exp']),"d") 
                                    or strpos(strtolower($rowd['qtdprod_exp']),"e")){ 
                                        echo recuperaExpoente(tratanumero($rowd["qtdd"]),$rowd['qtdprod_exp']);
                                }else{
                                        echo number_format(tratanumero($rowd["qtdd"]), 2, ',', '.');
                                }
                        ?>
                        
                    </td>
                        <td>
                        <?if($rowd['qtdd'] > $rowd['qtdc']){
                        
                            if(strpos(strtolower($rowd['qtdprod_exp']),"d") 
                                        or strpos(strtolower($rowd['qtdprod_exp']),"e")){ 
                                            $dif=recuperaExpoente(tratanumero($rowd["qtddif"]),$rowd['qtdprod_exp']);
                                    }else{
                                        $dif= number_format(tratanumero($rowd["qtddif"]), 2, ',', '.');
                                    }
                        ?>

                            <i title="Valor de <?=$dif?> maior que o crédito " class="fa fa-exclamation-triangle laranja btn-lg pointer"></i>
    
                            <spam style="color:red"> Valor de <?=$dif?> maior que o crédito.</spam>
                        <?}?>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>  
             <?
                }
            }
    ?>                
                
<?
        if($qtdv2==0 and $qtdv==0 and $qtdr == 0){
?>  
                <tr>
                    <td>Este Lote não possui consumo.</td>
                </tr>     
<?
        }
?>           
            </table>
            </div>	
            </div>
        </div>
    </div>
<?     
    }//function historicolotecons($idlote,$idunidade=null){
        function getMediadiaria($idprodserv,$idunidadeest,$idprodservformula=null,$consumodias=60){

            //trazer o valor de conversão
            $valconv=traduzid('prodserv','idprodserv','valconv',$idprodserv);
        
            ($valconv == 0)? $auxconv = 1: $auxconv = $valconv;
            //iniciar variaveis de total de calculo
            $tqtdd = 0;
            $tqtdc = 0;
            if(!empty($idprodservformula)){
                $in_str=" and l.idprodservformula=".$idprodservformula;
            }
            //pegar os o consumo
            $_sqlaux0 = "select 
                            c.idlotefracao, c.qtdd, c.qtdc
                        from 
                            lotefracao lf 
                        join
                            lote l on (lf.idlote = l.idlote)
                        join 
                            lotecons c on (lf.idlote = c.idlote and (c.qtdd>0 or c.qtdc>0) and c.idlotefracao=lf.idlotefracao and c.status='ABERTO')
                        where lf.idunidade = ".$idunidadeest."
                            ".$in_str."
                        and l.idprodserv = ".$idprodserv." 
                        and l.status not in ('CANCELADO','CANCELADA')
                        and c.criadoem > DATE_SUB(now(), INTERVAL ".$consumodias." DAY)";
            $_resaux0 = d::b()->query($_sqlaux0) or die("Erro ao consultar histórico de consumo do produto:".mysqli_error(d::b()) ." SQL: ".$_sqlaux0);
            if($_resaux0->num_rows > 0){
                while($_rowaux0 = mysqli_fetch_assoc($_resaux0)){
                    $sqlu = "SELECT u.convestoque 
                            from lotefracao l 
                            join unidade u on (l.idunidade = u.idunidade) 
                            where l.idlotefracao = ".$_rowaux0["idlotefracao"];
                    $resu = d::b()->query($sqlu) or die("Erro ao consultar idlotefracao:".mysqli_error(d::b()) );
                    $rowu = mysqli_fetch_assoc($resu);
                    if($rowu["convestoque"] == "Y"){
                        $aqtdd = $_rowaux0["qtdd"] / $auxconv;
                        $aqtdc = $_rowaux0["qtdc"] / $auxconv;
                    }else{
                        $aqtdd = $_rowaux0["qtdd"];
                        $aqtdc = $_rowaux0["qtdc"];
                    }
                    $tqtdd += $aqtdd;
                    $tqtdc += $aqtdc;
                }    
                //removido o credito do calculo de media diaria
                //$mediadiaria = ($tqtdd - $tqtdc)/60;
                $mediadiaria = ($tqtdd)/$consumodias;
                if($mediadiaria < 0){
                    $mediadiaria *= -1;
                }
            }
            else
                $mediadiaria = 0;
            return $mediadiaria;
        }
    
    //retorna o sql do produtos em alerta ou pedidos em alerta conforme o tipo
    // pedidoemalerta.php -produtoemalertar.php
function getProdservAlerta($idunidadepadrao, $tipo, $idcotacao = NULL, $idempresa = NULL, $idgrupoes = NULL, $idcontaitem = NULL, $whereOrc = NULL){
        
    if($tipo == 'PEDIDO')
	{
		$wherexx=" psa.status in('DIVERGENCIA','APROVADO')";
	}else{
		$wherexx=" ((psa.statusorc !='CONCLUIDO') OR (psa.statusorc ='CONCLUIDO' and  pedido_automatico > pedidoautomatico)) ";
	}
        
	if(!empty($idcotacao))
	{
		$joincontaitem = " AND EXISTS (SELECT 1 FROM prodservcontaitem pi JOIN objetovinculo ov ON ov.idobjetovinc = pi.idcontaitem AND ov.idobjeto = '$idcotacao' AND ov.tipoobjeto = 'cotacao' AND ov.tipoobjetovinc = 'contaitem'
										 JOIN prodserv p2 ON p2.idprodserv = pi.idprodserv AND p2.idtipoprodserv IN ($idcontaitem)
										WHERE pi.idprodserv = vp.idprodserv)";

		$joincontaitemu3 = " JOIN (SELECT p.idprodserv FROM prodservcontaitem pc JOIN prodserv p ON p.idprodserv = pc.idprodserv 
             					   WHERE pc.idprodserv = p.idprodserv AND pc.idcontaitem in ($idgrupoes) AND p.idtipoprodserv IN ($idcontaitem)) as u4 ON u4.idprodserv = i.idprodserv";
	}

	if(!empty($idempresa))
	{
		$empresanf = ' and n.idempresa = '.$idempresa;
		$empresaps = ' and vp.idempresa = '.$idempresa;
	}

	$vendas = " AND vp.comprado = 'Y'";
	$joinun = " JOIN unidadeobjeto o on (o.idobjeto = 'produtoemalerta' AND o.tipoobjeto = 'modulo' AND o.idunidade = vp.idunidadealerta)
			    join unidade u2 on (u2.idunidade = o.idunidade ".share::otipo('cb::usr')::produtoEmAlertaIdempresa("u2.idempresa")." )";

	$sql = "SELECT idprodserv,
				   codprodserv,
                   un,
				   tempocompra,
				   descr,
				   estmin,
				   estmin_exp,
				   pedidoautomatico,
				   pedido_automatico,
                   sugestaocompra,
                   destoque,
                   idprodservforn,
				   pedido,
				   total,
				   quar,	
				   entrada,
				   atrasado,
				   atrasadocot,
				   statusorc,
				   status,
				   idnf,
				   idcotacao,
				   CASE
                        WHEN psa.pedido_automatico > psa.pedidoautomatico AND psa.idcotacao > 1 THEN 2
                        WHEN  psa.status ='DIVERGENCIA' and psa.atrasado ='V' THEN 5
                        WHEN  psa.status ='APROVADO' and psa.atrasado ='V' THEN 5
                        WHEN  psa.status ='DIVERGENCIA' and psa.atrasado ='O' THEN 9
                        WHEN  psa.status ='APROVADO' and psa.atrasado ='O' THEN 9
                        WHEN  psa.statusorc ='INICIO' THEN 1
                        WHEN  psa.statusorc ='PREVISAO' THEN 1
                        WHEN  psa.statusorc ='SEMORCAMENTO' THEN 7          
                        WHEN  psa.statusorc = 'ENVIADO' and psa.atrasadocot ='V' THEN 3
                        WHEN  psa.statusorc = 'ENVIADO' and psa.atrasadocot ='O' THEN 4
                        WHEN  psa.statusorc = 'PENDENTE' and psa.atrasadocot ='V' THEN 3
                        WHEN  psa.statusorc = 'PENDENTE' and psa.atrasadocot ='O' THEN 4            
                        ELSE 10
                    END AS ordem,
                    CASE psa.entrada WHEN 'NORMAL' THEN 1 ELSE 2 END AS ordem2,
				   ultimoconsumo
			 FROM (SELECT c.idcotacao AS idprodserv,
			 			  '' AS codprodserv,
                          '' AS un,
						  0 AS tempocompra,
						  prodservdescr AS descr,
						  0 AS estmin,
						  '' AS estmin_exp,
						  0 AS pedidoautomatico,
						  0 AS pedido_automatico,
                          0 AS sugestaocompra,
                          0 AS destoque,
                          '' AS idprodservforn,
						  0 AS pedido,
						  0 AS total,
						  0 AS quar,
						  'MANUAL' AS entrada,
						  c.status AS statusorc,
						  n.status AS status,
						  n.idnf,
						  c.idcotacao,
						  IF(DATE_FORMAT(n.previsaoentrega,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V') AS atrasado,
						  IF(DATE_FORMAT(c.prazo,'%Y-%m-%d')>=DATE_FORMAT(now(),'%Y-%m-%d'),'O','V') AS atrasadocot,
						  c.alteradoem AS ultimoconsumo
					 FROM nfitem vp JOIN nf n ON vp.idnf = n.idnf JOIN cotacao c ON n.idobjetosolipor = c.idcotacao
					WHERE vp.idprodserv IS NULL AND n.status NOT IN ('CONCLUIDO', 'CANCELADO', 'REPROVADO')
					".share::otipo('cb::usr')::produtoEmAlertaIdempresa("c.idempresa")."
					AND vp.nfe = 'Y'
					AND n.tipoobjetosolipor = 'cotacao' 
					$empresanf
					$joincontaitem
			  UNION 
			  	SELECT vwp.idprodserv,
                        vwp.codprodserv,
                        vwp.un,
                        vwp.tempocompra,
                        vwp.descr,
                        vwp.estmin,
                        vwp.estmin_exp,
                        vwp.pedidoautomatico,
                        vwp.pedido_automatico,
                        vwp.sugestaocompra,
                        vwp.destoque,
                        vwp.idprodservforn,
                        if(vwp.pedidoautomatico > vwp.pedido_automatico, vwp.pedidoautomatico, vwp.pedido_automatico) as pedido,
                        vwp.qtdest AS total,
                        'NORMAL' AS entrada,
                        nci.prazo, 
                        CASE WHEN nci.statusorc is null THEN 'SEMORCAMENTO' ELSE nci.statusorc END AS statusorc,  
                        (SELECT n.status FROM nfitem i JOIN nf n ON i.idnf = n.idnf AND i.nfe = 'Y' 
                            WHERE i.idprodserv = vwp.idprodserv AND n.idobjetosolipor = nci.idcotacao 
                            AND n.tipoobjetosolipor = 'cotacao' 
                            AND n.status IN ('APROVADO','DIVERGENCIA') LIMIT 1 ) AS status, 
                        (SELECT n.idnf FROM nfitem i JOIN nf n ON i.idnf = n.idnf AND i.nfe='Y'
                            WHERE i.idprodserv = vwp.idprodserv
                            AND n.idobjetosolipor = nci.idcotacao  
                            AND n.tipoobjetosolipor = 'cotacao' 
                            AND n.status IN ('APROVADO','DIVERGENCIA') LIMIT 1) AS idnf,
                        nci.idcotacao,
                        (SELECT IF(DATE_FORMAT(n.previsaoentrega,'%Y-%m-%d') >= DATE_FORMAT(now(),'%Y-%m-%d'),'O','V')  
                            FROM nfitem i JOIN nf n ON i.idnf = n.idnf AND i.nfe = 'Y'
                            WHERE i.idprodserv = vwp.idprodserv
                                AND n.idobjetosolipor = nci.idcotacao 
                                AND n.tipoobjetosolipor = 'cotacao'
                                AND n.status IN ('APROVADO','DIVERGENCIA') LIMIT 1 ) AS atrasado,
                        IF(DATE_FORMAT(nci.prazo,'%Y-%m-%d') >= DATE_FORMAT(now(),'%Y-%m-%d'),'O','V') AS atrasadocot,
                        IFNULL((SELECT criadoem FROM prodcomprar pc WHERE pc.status = 'ATIVO' AND pc.idprodserv = vwp.idprodserv), sysdate()) AS ultimoconsumo
                    FROM (SELECT vp.idprodserv, vp.codprodserv, vp.tempocompra, vp.descr, vp.estmin, vp.estmin_exp, vp.pedidoautomatico, vp.pedido_automatico, vp.qtdest, 
                                 vp.status, vp.un, vp.sugestaocompra, vp.destoque, f.idprodservforn
                            FROM vw8prodestoque vp LEFT JOIN prodservforn f ON f.idprodserv = vp.idprodserv AND f.status = 'ATIVO' AND f.multiempresa = 'N'
                            $joinun
                           WHERE vp.tipo = 'PRODUTO' 
                           ".share::otipo('cb::usr')::produtoEmAlertaIdempresa("vp.idempresa")."
                            AND vp.status = 'ATIVO' AND vp.estmin IS NOT NULL AND vp.estmin != 0.00 
                            $empresaps
                            $joincontaitem
                            $vendas
                            GROUP BY vp.idprodserv) AS vwp
                    LEFT JOIN (SELECT MAX(c.prazo) AS prazo, 
                                    c.status AS statusorc, 
                                    i.idprodserv,
                                    c.idcotacao
                                FROM cotacao c JOIN nf n ON n.idobjetosolipor = c.idcotacao
                                JOIN nfitem i ON n.tipoobjetosolipor = 'cotacao' AND i.idnf = n.idnf AND i.nfe = 'Y' 
                                 AND n.status NOT IN ('CONCLUIDO', 'CANCELADO', 'REPROVADO') 
                                 $empresanf
                                 $joincontaitemu3
                            GROUP BY i.idprodserv) AS nci ON vwp.idprodserv = nci.idprodserv
                    WHERE (vwp.estmin > vwp.qtdest) or (vwp.pedido_automatico > vwp.pedidoautomatico)) AS psa
                    WHERE $wherexx $whereOrc
                 -- GROUP BY idprodserv, idcotacao 
                 ORDER BY ";

	    //echo '<pre>'.$sql.'</pre>';
        return $sql;
    }

    function busca_valor_formula($inidprodservformula,$percentagem = 1){
        // funcao para buscar o valor da formula e o valor de cada item da fórmula.

        //$valoritem=0;
      /*  $sql="select  i.qtdi,i.idprodserv,p.fabricado,p.descr,p.un,fi.idprodservformula,ifnull((i.qtdi/fi.qtdpadraof),1) as perc
                from prodservformula f 
                join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                join prodserv p on(p.idprodserv = i.idprodserv) 
                left join prodservformula fi on(fi.status='ATIVO' 
                                                and fi.idprodserv=i.idprodserv
                                                and( fi.idplantel=f.idplantel or fi.idplantel is null or fi.idplantel='') )
                where f.idprodservformula= ".$inidprodservformula." order by p.descr";
                */
                $sql="select * from (
                    select  i.qtdi,i.idprodserv,p.fabricado,p.descr,concat(fi.rotulo,' ',ifnull(fi.dose,' '),' ',p.conteudo,' ',' (',fi.volumeformula,' ',fi.un,')') as rotulo,p.un,fi.idprodservformula,ifnull(((i.qtdi/fi.qtdpadraof)*".$percentagem."),1) as perc
                             from prodservformula f 
                             join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                             join prodserv p on(p.idprodserv = i.idprodserv) 
                             join prodservformula fi on(fi.status='ATIVO' 
                                                             and fi.idprodserv=i.idprodserv
                                                             and( fi.idplantel=f.idplantel ) )
                                                             
                             where f.idprodservformula= ".$inidprodservformula."
                             union 
                             select  i.qtdi,i.idprodserv,p.fabricado,p.descr,concat(fi.rotulo,' ',ifnull(fi.dose,' '),' ',p.conteudo,' ',' (',fi.volumeformula,' ',fi.un,')') as rotulo,p.un,fi.idprodservformula,ifnull(((i.qtdi/fi.qtdpadraof)*".$percentagem."),1) as perc
                             from prodservformula f 
                             join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                             join prodserv p on(p.idprodserv = i.idprodserv) 
                              join prodservformula fi on(fi.status='ATIVO' 
                                                             and fi.idprodserv=i.idprodserv
                                                             and(  fi.idplantel is null or fi.idplantel='') )
                             where f.idprodservformula=  ".$inidprodservformula."
                             and not exists (select 1 from   prodservformula fi2 where fi2.status='ATIVO' 
                                                             and fi2.idprodserv=i.idprodserv
                                                             and(  fi2.idplantel is not null) )
                            union 
                            select  i.qtdi,i.idprodserv,p.fabricado,p.descr,'' as rotulo,p.un,null,ifnull(((i.qtdi/1)*".$percentagem."),1) as perc
                             from prodservformula f 
                             join  prodservformulains i on(f.idprodservformula=i.idprodservformula and  i.qtdi >0) 
                             join prodserv p on(p.idprodserv = i.idprodserv) 
                             where f.idprodservformula= ".$inidprodservformula." 
                             and not exists (select 1 from  prodservformula fi where fi.status='ATIVO' 
                                                             and fi.idprodserv=i.idprodserv)
                         
                              ) as u					 
                        group by u.idprodserv";
        $res= d::b()->query($sql);
    
        while($row=mysqli_fetch_assoc($res)){
            if($row['fabricado']=='Y' and !empty($row['idprodservformula'])){
                $this->busca_valor_formula($row['idprodservformula'],$row['perc']);
            }elseif($row['fabricado']=='N'){
                $valor=$this->busca_valor_item($row['idprodserv'],$row['qtdi']);
                $valor=$valor*$percentagem;
                
                $this->valoritem=$this->valoritem+$valor;
            }
        }//while($row=mysqli_fetch_assoc($res)){
        return round($this->valoritem,2);
    }
    
    function busca_valor_item($inidprodserv,$qtdi = 1){
      $sql="select ifnull(l.vlrlote,0) as valoritem,l.idlote 
      from lote l
      where l.idprodserv = ".$inidprodserv." order by idlote desc limit 1";
      $res= d::b()->query($sql);
      $row=mysqli_fetch_assoc($res);
      $valor=round(($qtdi*$row['valoritem']),2);
      return $valor;
    }
    
}
?>