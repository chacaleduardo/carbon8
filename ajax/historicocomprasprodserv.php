<?
require_once("../inc/php/functions.php");

require_once(__DIR__."/../form/controllers/cotacao_controller.php");

$idprodserv= $_GET['idprodserv']; 

if(empty($idprodserv)){
	die("Identificação de Produto/Serviço não enviada");
}

$historico = CotacaoController::listarNfitemsxmlPorIdprodserv($idprodserv);
if(count($historico) > 0){?>

    <table class="table table-striped planilha">
        <tr>
            <th>Cotação</th>
            <th>Empresa</th>
            <th>Fornecedor</th>
            <th>Qtd</th>
            <th>UN</th>
            <th>Valor UN</th>
            <th>Emissão</th>
        </tr>                                                            
        <?
        foreach($historico as $htitem)
        {
            ?>
            <tr>
                <td><a class="pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$htitem["idnf"]?>')"><?=$htitem['idnf']?></a></td>
                <td><?=$htitem['sigla']?></td>
                <td><?=$htitem['nome']?></td>
                <td><?=$htitem['qtd']?></td>
                <td><?=$htitem['un']?></td>
                <td><b>R$ <?=number_format(tratanumero($htitem['valorun']), 2, ',', '.')?></b></td>
                <td><?=dma($htitem['dtemissao'])?></td>
                
            </tr>   
        <?
        }
        ?> 
    </table>
<?}else{?>
    Nenhuma compra encontrada
<?}