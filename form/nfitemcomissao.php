<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}



function jsonPessoa(){
   
    $sql = "select * from (
            select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome
                from pessoa p
                where p.idtipopessoa in(1,12) and (p.status='ATIVO' OR (p.status='INATIVO' && p.comissaoinativo = 'Y'))
                ".share::otipo('cb::usr')::nfitemcomissaopessoas("p.idpessoa")."
                and exists(select 1 from pessoacontato c join pessoa p2 on(p2.idpessoa=c.idpessoa and p2.idtipopessoa=2) where c.idcontato=p.idpessoa)
            union all
            select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome
                from pessoa p
                where p.idtipopessoa in(1,12) and (p.status='ATIVO' OR (p.status='INATIVO' && p.comissaoinativo = 'Y'))
                ".share::otipo('cb::usr')::nfitemcomissaopessoas("p.idpessoa")."
                and exists(select 1 from divisao c where c.idpessoa=p.idpessoa)
                ) as u group by u.idpessoa order by nome ";

    $res = d::b()->query($sql);

    $arrtmp=array();
    $i=0;
    while ($r = mysqli_fetch_assoc($res)) {
        $arrtmp[$i]["value"]=$r["idpessoa"];
        $arrtmp[$i]["label"]= $r["nome"];
        $i++;
    }
    return $arrtmp;
}


//Recupera os produtos a serem selecionados para uma nova Formalização
$arrP=jsonPessoa();
//print_r($arrCli); die;
$jsonP=$JSON->encode($arrP);
?>
<script>
jsonP = <?=$jsonP?>;//// autocomplete produto
</script>
<?

$idnfitem=$_GET['idnfitem'];
$idnf=$_GET['idnf'];

if(!empty($idnfitem)){
    $str="i.idnfitem=".$idnfitem;
}else{
    $str="i.idnf=".$idnf;
}
$vidnotafiscalitens=$_GET['vidnotafiscalitens'];

if(empty($vidnotafiscalitens) ){//produto

    $sq="select ifnull(descrcurta,p.descr) as descr ,i.* 
    from nfitem i join prodserv p on(p.idprodserv=i.idprodserv and p.comissionado ='Y')
    where i.nfe = 'Y'
    and ".$str." order by p.descr";
    $i=0;
    $re=d::b()->query($sq) or die("erro ao buscar informações do item da comissao sql=".$sq);
    while($ro=mysqli_fetch_assoc($re)){
    ?>
    <div class="row">
        <div class="col-md-12" >
        <div class="panel panel-default" >
            <div class="panel-heading">
                
                <div class="row">
                    <div class="col-md-11">
                    <?=$ro['qtd']?> - <?=$ro['descr']?> R$ <?=$ro['total']?>
                    </div>
                    <div class="col-md-1" style="text-align-last: end;">
                    <?if(!empty($idnf)){?>
                        <a class="fa fa-refresh verde pointer hoverazul" title="Atualizar valores de comissão para os outros itens conforme este." onclick="atualizarcomissao(<?=$ro['idnfitem']?>);"></a>
                    <?}?>
                    </div>
                </div>
            </div
            <div  class="panel-body">	
            <table class="table table-striped">
                <tr>
                    <th>Comissionado</th>
                    <th>Percentual</th>
                    <th></th>
                </tr>
                <? 
                $sql="select ifnull(p.nomecurto,p.nome) as nome ,c.* 
                from nfitemcomissao c join pessoa p on(p.idpessoa=c.idpessoa)
                where c.idnfitem = ".$ro['idnfitem']." order by nome";
                $res=d::b()->query($sql) or die("erro ao buscar informações da comissao sql=".$sql);
                
                while($row=mysqli_fetch_assoc($res)){
                    $i=$i+1;
                    ?>
                <tr>
                    <td class="col-md-8"><?=$row['nome']?></td>
                    <td class="col-md-2">
                        <input name="_<?=$i?>_u_nfitemcomissao_idnfitemcomissao" type="hidden"	value="<?=$row['idnfitemcomissao']?>">
                        <input class='size8' name="_<?=$i?>_u_nfitemcomissao_pcomissao" type="text"	value="<?=$row['pcomissao']?>">              
                    </td>
                    <td class="col-md-2">
                        <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('nfitemcomissao',<?=$row['idnfitemcomissao']?>)" alt="Excluir"></i>
                    </td>
                </tr>
                <?}?>
                <tr>
                    <td>
                        <input  type="text" idnfitem="<?=$ro['idnfitem']?>" id="<?=$i?>icomissao"  cbvalue="" value="" style="width: 35em;" >                    
                    </td>
                </tr>
            </table>
            <script>
            
            $("#<?=$i?>icomissao").autocomplete({
                source: jsonP,
                delay: 0
                ,select: function(){
                    console.log($(this).cbval());
                    icomissao($(this).cbval(),$(this).attr('idnfitem'));
                }
            });
            </script>
            </div>
        </div>
        </div> 
    </div>
<?
    }
}elseif(!empty($vidnotafiscalitens)){//serviços
    

    
    $sq="select i.idempresa,
            i.idnotafiscal,
            sum(i.quantidade) AS quantidade,
            i.descricao,
            i.idresultado,
            round(i.valor, 2) AS valorunitario,
            round(sum((i.valor * i.quantidade)),2) AS subtotal,
            i.desconto,
            round(((i.valor - round((i.valor * (i.desconto / 100)),2)) * sum(i.quantidade)),2)  AS total,
            isnull(max(i.idresultado)) AS complemento,
            if(r.status='ASSINADO',2,1) as defcor,
            p.comissionado,
            i.idnotafiscalitens
        from notafiscalitens i left join resultado r on(r.idresultado=i.idresultado)
        left join prodserv p on(p.idprodserv=r.idtipoteste)
        where i.idnotafiscalitens in (".$vidnotafiscalitens.")  group by  i.idnotafiscalitens order by descricao";

   // echo($sq);
    $i=0;
    $re=d::b()->query($sq) or die("erro ao buscar informações do item da comissao sql=".$sq);
    while($ro=mysqli_fetch_assoc($re)){
        $i=$i+1;
    ?>
    <div class="row">
        <div class="col-md-12" >
        <div class="panel panel-default" >
            <div class="panel-heading">
                

                <div class="row">
                    <div class="col-md-11">
                    ID: <?=$ro['idnotafiscalitens']?> - Quantidade: <?=$ro['quantidade']?> - <?=$ro['descricao']?> - R$ <?=$ro['total']?>
                    </div>
                    <div class="col-md-1" style="text-align-last: end;">
                    <?if(!empty($ro['idnotafiscalitens'])){?>
                        <a class="fa fa-refresh verde pointer hoverazul" title="Atualizar valores de comissão para os outros itens conforme este." onclick="atualizarcomissaonfs(<?=$ro['idnotafiscalitens']?>,<?=$ro['idnotafiscal']?>);"></a>
                    <?}?>
                    </div>
                </div>

            </div
            <div  class="panel-body">	
            <table class="table table-striped">
                <tr>
                    <th>Comissionado</th>
                    <th>Percentual</th>
                    <th></th>
                </tr>
                <? 
                $sql="select ifnull(p.nomecurto,p.nome) as nome ,c.* 
                from notafiscalitenscomissao c join pessoa p on(p.idpessoa=c.idpessoa)
                where c.idnotafiscalitens = ".$ro['idnotafiscalitens']." order by nome";
                $res=d::b()->query($sql) or die("erro ao buscar informações da comissao sql=".$sql);
                
                while($row=mysqli_fetch_assoc($res)){
                    $y=$y+1;
                    ?>
                <tr>
                    <td class="col-md-8"><?=$row['nome']?></td>
                    <td class="col-md-2">
                        <input name="_<?=$y?>_u_notafiscalitenscomissao_idnotafiscalitenscomissao" type="hidden"	value="<?=$row['idnotafiscalitenscomissao']?>">
                        <input class='size8' name="_<?=$y?>_u_notafiscalitenscomissao_pcomissao" type="text"	value="<?=$row['pcomissao']?>">              
                    </td>
                    <td class="col-md-2">
                        <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('notafiscalitenscomissao',<?=$row['idnotafiscalitenscomissao']?>)" alt="Excluir"></i>
                    </td>
                </tr>
                <?}?>
                <tr>
                    <td>
                        <input  type="text" idnotafiscalitens="<?=$ro['idnotafiscalitens']?>" id="<?=$i?>icomissao"  cbvalue="" value="" style="width: 35em;" >                    
                    </td>
                </tr>
            </table>
            <script>
            
            $("#<?=$i?>icomissao").autocomplete({
                source: jsonP,
                delay: 0
                ,select: function(){
                    console.log($(this).cbval());
                    icomissaoserv($(this).cbval(),$(this).attr('idnotafiscalitens'));
                }
            });
            </script>
            </div>
        </div>
        </div> 
    </div>
<?
    }

}
?>
<script>
function excluir(tab,inid){
    if(confirm("Deseja retirar este?")){		
        CB.post({
		objetos: "_x_d_"+tab+"_id"+tab+"="+inid
		,parcial:true
        });
    }
    
}
function icomissao(vidpessoa,idnfitem){
	CB.post({
        objetos: "_x_i_nfitemcomissao_idnfitem="+idnfitem+"&_x_i_nfitemcomissao_idpessoa="+vidpessoa
        ,parcial: true        
    })
}

function icomissaoserv(vidpessoa,idnotafiscalitens){
	CB.post({
        objetos: "_x_i_notafiscalitenscomissao_idnotafiscalitens="+idnotafiscalitens+"&_x_i_notafiscalitenscomissao_idpessoa="+vidpessoa
        ,parcial: true        
    })
}

function atualizarcomissao(idnfitem){
    
    CB.post({
        objetos: {
            "_com_u_nfitem_idnfitem":idnfitem
            ,"_com_u_nfitem_nfe":'Y'
        }
        ,parcial: true        
    });
}


function atualizarcomissaonfs(idnfitem,idnf){
    
    CB.post({
        objetos: {
            "_com_u_notafiscalitens_idnotafiscalitens":idnfitem
            ,"_com_u_notafiscalitens_idnotafiscal":idnf
        }
        ,parcial: true        
    });
}

</script>