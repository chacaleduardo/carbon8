<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");

$opcao      	= filter_input(INPUT_GET, "vopcao");
$vfiltro      	= filter_input(INPUT_GET, "vfiltro");

/*
 * Lista os Tipos de Eventos para selecionar e filtrar as buscas
 * Lidiane (14-02-2020)
 */
if($opcao == 'menu')
{
	$sql = "SELECT t.eventotipo,
				   t.ideventotipo
			  FROM evento e, pessoa p, eventotipo t
			 WHERE p.idpessoa = e.idpessoa 
			   AND t.ideventotipo = e.ideventotipo
			   AND t.dashboard = 'Y'
			   AND e.idevento IN (
				 		  SELECT er.idmodulo 
				   			FROM fluxostatuspessoa er
				  		   WHERE er.idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."'
							 AND er.tipoobjeto = 'pessoa'
							 AND er.idmodulo = e.idevento
							 AND er.modulo = 'evento'
							 AND IF (e.ideventopai, e.inicio <= date_format(now(), '%Y-%m-%d'), 1) = 1
							 AND repetirate is null
							 " . $filtro . "
							 AND er.oculto != 1)
           GROUP BY t.ideventotipo";

	$rec = d::b()->query($sql) or die("saveposchange: ao verificar nessecidade de enviar uma nova mensagem:" . mysqli_error(d::b()) . "");	
	while ($r = mysqli_fetch_assoc($rec)) 
	{		
		$return_arr[] = array(
			"ideventotipo" 	=> $r['ideventotipo'],
			"eventotipo" 	=> $r['eventotipo']
		);
	}
	
	echo json_encode($return_arr);

}

?>
