<?
$_idprodservformula = $_SESSION['arrpostbuffer']['1']['u']['prodservformula']['idprodservformula'];
$_idprodserv = $_SESSION['arrpostbuffer']['1']['u']['prodserv']['idprodserv'];

if(empty($_idprodservformula) && !empty($_idprodserv))
{ 
    $_lsitarNaoFormulados = CalculoEstoqueController::buscarNaoFormuladosPorIdProdserv($_idprodserv, 'PRODUTO');
    $su = "";
    $ins = "";
    $su1 = ""; // somente para print das querys
    $minimoauto = 0;

    foreach($_lsitarNaoFormulados as $naoFormulados)
    {
        $tqtdd = 0;
        $tqtdc = 0;

        (!empty($naoFormulados["temporeposicao"])) ? $temporeposicao = $naoFormulados["temporeposicao"] : $temporeposicao = 0;
        (!empty($naoFormulados["estoqueseguranca"])) ? $estoqueseguranca = $naoFormulados["estoqueseguranca"] : $estoqueseguranca = 0;
        (!empty($naoFormulados["tempocompra"])) ? $tempocompra = $naoFormulados["tempocompra"] : $tempocompra = 0;
        (!empty($naoFormulados["qtdest"])) ? $qtdest = $naoFormulados["qtdest"] : $qtdest = 0;
        ($naoFormulados["valconv"] == 0) ? $auxconv = 1 : $auxconv = $naoFormulados["valconv"];
                
        $_rowpa = CalculoEstoqueController::buscarQtdpa($naoFormulados["idprodserv"])['dados'];
        $_rowaux0 = CalculoEstoqueController::buscarConsumoIntervalo60diasPorIdUnidadeEstIdProdservIdProdservFormula($naoFormulados["idunidadeest"], $naoFormulados["idprodserv"]);
        foreach($_rowaux0 as $consumo)
        {
            $rowu = CalculoEstoqueController::buscarConvEstoque($consumo["idlotefracao"]);
            if($rowu["convestoque"] == "Y"){
                $aqtdd = $consumo["qtdd"] / $auxconv;
                $aqtdc = $consumo["qtdc"] / $auxconv;
            }else{
                $aqtdd = $consumo["qtdd"];
                $aqtdc = $consumo["qtdc"];
            }
            if($consumo['status'] != 'INATIVO' and $consumo['status'] != 'DEVOLUCAO'){
                $tqtdd += $aqtdd;
                $tqtdc += $aqtdc;
            }
        }

        $mediadiaria = ($tqtdd) / 60;
        if($mediadiaria < 0){
            $mediadiaria *= -1;
        }

        $minimoauto = (($mediadiaria * $temporeposicao) + ($mediadiaria * $estoqueseguranca));
        $pedidoauto = (($mediadiaria * $tempocompra));
        $pedido_auto = ($temporeposicao * $mediadiaria) + ($minimoauto-$qtdest) - ($_rowpa['qtdpa']);           
        $sugestaoCompra2 = tratanumerovisualizacao((($mediadiaria * $tempocompra) + ($naoFormulados['estmin'] - $naoFormulados['qtdest']))) - $_rowpa['qtdpa'];

        if($qtdest > 0 && $mediadiaria > 0){
            $diasestoque = $qtdest / $mediadiaria;
        }else{
            $diasestoque = 0;
        }	
            
        if($pedido_auto < 0){
            $pedido_auto = 0;
        }
        if($diasestoque < 0 || !is_numeric($diasestoque)){
            $diasestoque = 0;
        }
        
        $rowultimoOrcamento = CalculoEstoqueController::buscarCotacaoNfitem($_idprodserv);
        $arrayAtualizaProdserv = [
            "qtdest" => $qtdest,
            "diasestoque" => $diasestoque,
            "mediadiaria" => $mediadiaria,
            "minimoauto" => $minimoauto,
            "pedidoauto" => $pedidoauto,
            "pedido_auto" => $pedido_auto,
            "sugestaoCompra2" => $sugestaoCompra2,
            "ultimoorcamento" => empty($rowultimoOrcamento['idcotacao']) ? 0 : $rowultimoOrcamento['idcotacao'],
            "idprodserv" => $naoFormulados["idprodserv"],
            "usuario" => $_SESSION["SESSAO"]["USUARIO"],
            "alteradoem" => 'now()'
        ];

        //@523299 - MÓDULO FILTRO PESQUISA: CALCULO DE ESTOQUE
        //adicionado mediadiaria na prodserv para o modulo filtro calculoestoque
        CalculoEstoqueController::atualizarValoresCalculoEstoqueProdserv($arrayAtualizaProdserv); 
    }
}//if(empty($_idprodservformula) and !empty($_idprodserv)){
      
if(!empty($_idprodservformula))
{
    // RODA PARA OS FORMULADOS
    $_listarFormulados = CalculoEstoqueController::buscarFormuladosPorIdProdservFormula($_idprodservformula);
    $minimoauto = 0;

    foreach($_listarFormulados as $formulados)
    {
        $tqtdd = 0;
        $tqtdc = 0;
        
        (!empty($formulados["temporeposicao"])) ? $temporeposicao = $formulados["temporeposicao"] : $temporeposicao = 0;
        (!empty($formulados["estoqueseguranca"])) ? $estoqueseguranca = $formulados["estoqueseguranca"] : $estoqueseguranca = 0;
        (!empty($formulados["tempocompra"])) ? $tempocompra = $formulados["tempocompra"] : $tempocompra = 0;
        (!empty($formulados["qtdest"])) ? $qtdest = $formulados["qtdest"] : $qtdest = 0;
        ($formulados["valconv"] == 0)? $auxconv = 1: $auxconv = $formulados["valconv"];
            
        $_rowpa = CalculoEstoqueController::buscarQtdpaComFormula($formulados["idprodserv"], $formulados["idprodservformula"])['dados'];        
        $_resaux1 = CalculoEstoqueController::buscarConsumoIntervalo60diasPorIdUnidadeEstIdProdservIdProdservFormula($formulados["idunidadeest"], $formulados["idprodserv"], $formulados["idprodservformula"]);
        foreach($_resaux1 as $_rowaux1)
        {
            $rowu1 = CalculoEstoqueController::buscarConvEstoque($_rowaux1["idlotefracao"]);
            if($rowu1["convestoque"] == "Y"){
                $aqtdd = $_rowaux1["qtdd"] / $auxconv;
                $aqtdc = $_rowaux1["qtdc"] / $auxconv;
            }else{
                $aqtdd = $_rowaux1["qtdd"];
                $aqtdc = $_rowaux1["qtdc"];
            }
            if($_rowaux1['status'] != 'INATIVO' and $_rowaux1['status'] != 'DEVOLUCAO'){
                $tqtdd += $aqtdd;
                $tqtdc += $aqtdc;
            }
        }

        $mediadiaria = ($tqtdd) / 60;        
        $minimoauto = (($mediadiaria * $temporeposicao) + ($mediadiaria * $estoqueseguranca));
        $pedidoauto = (($mediadiaria * $tempocompra));
        $pedido_auto = ($temporeposicao * $mediadiaria) + ($minimoauto - $qtdest) - ($_rowpa['qtdpa']);	        
        $sugestaoCompra2 = tratanumerovisualizacao((($mediadiaria * $tempocompra) + ($formulados['estmin'] - $formulados['qtdest']))) - $_rowpa['qtdpa'];

        if($qtdest > 0 && $mediadiaria > 0){
            $diasestoque = $qtdest / $mediadiaria;
        }else{
            $diasestoque = 0;
        }	
        
        if($pedido_auto < 0){
            $pedido_auto = 0;
        }
        if($diasestoque < 0 || !is_numeric($diasestoque)){
            $diasestoque = 0;
        }

        //@523299 - MÓDULO FILTRO PESQUISA: CALCULO DE ESTOQUE
        //adicionado mediadiaria na prodserv para o modulo filtro calculoestoque
        $arrayAtualizaProdservFormula = [
            "qtdest" => $qtdest,
            "destoque" => $diasestoque,
            "mediadiaria" => $mediadiaria,
            "estminautomatico" => $minimoauto,
            "pedidoautomatico" => $pedidoauto,
            "pedido_automatico" => $pedido_auto,
            "sugestaocompra2" => $sugestaoCompra2,
            "idprodservformula" => $formulados["idprodservformula"],
            "usuario" => $_SESSION["SESSAO"]["USUARIO"],
            "alteradoem" => 'now()'
        ];
        
        CalculoEstoqueController::atualizarCalculoEstoqueProdservFormula($arrayAtualizaProdservFormula); 
    }
}//$_idprodservformula
?>
