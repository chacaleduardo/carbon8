<?

include 'vendor/autoload.php';
$cnabFactory = new Cnab\Factory();
$arquivo = $cnabFactory->createRetorno('CN01027A.RET');
$detalhes = $arquivo->listDetalhes();
foreach($detalhes as $detalhe) {
  //  if($detalhe->getValorRecebido() > 0) {
        $nossoNumero   = $detalhe->getNossoNumero();
		$NumeroDocumento   = $detalhe->getNumeroDocumento();
		
        $valorRecebido = $detalhe->getValorRecebido();
        $dataPagamento = $detalhe->getDataOcorrencia();
        $carteira      = $detalhe->getCarteira();
        // você já tem as informações, pode dar baixa no boleto aqui
	

		echo('N Documento='.$NumeroDocumento.' Nosso N='.$nossoNumero.'Valor = '.$valorRecebido.' data pagamento='.date_format($dataPagamento, 'd-m-Y').' Carteira='.$carteira);
		echo("<br>");
		
	//}
}
?>
