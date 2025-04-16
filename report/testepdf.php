<?php
    error_reporting( E_ALL );
?>
<?php 
require  '/var/www/carbon8/inc/mpdf60/vendor/autoload.php';


	$html = "
	 <fieldset>
	 	<h1>Recibo</h1>
	 	<p class='center sub-titulo'>
	 		Nº <strong>0001</strong> - 
	 		VALOR <strong>R$ 500,00</strong>
	 	</p>
	 	<p>Recebi(emos) de <strong>Carlos Domingues Neto</strong></p>
	 	<p>a quantia de <strong>Quinhentos Reais</strong></p>
	 	<p>Correspondente a <strong>Serviços prestados ..<strong></p>
	 	<p>e para clareza firmo(amos) o presente.</p>
	 	<p class='direita'>São Roque, 25 de Dezembro de 2015</p>
	 	<p>Assinatura ......................................................................................................................................</p>
	 	<p>Nome <strong>João da Silva Nogueira</strong> CPF/CNPJ: <strong>222.222.222-02</strong></p>
	 	<p>Endereço <strong>Rua da Penha, 200 - Jd. Alguma Coisa - São Paulo</strong></p>
	 </fieldset>";

$mpdf = new mPDF('c');

$mpdf->WriteHTML($html);
$mpdf->Output();
exit;
	 ?>
