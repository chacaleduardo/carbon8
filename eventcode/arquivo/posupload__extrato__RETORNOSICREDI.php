<?
// lê o arquivo que foi enviado
$lastid = d::b()->insert_id;
echo $lastid;

//$arq = fopen($arq_final,'r');


/**
* @ Autor: Whilton Reis
* @ Data : 14/06/2016
* @ Retorno Sicredi Cnab400
*/
$g = 0;
$n = -1;
Class RetornoSicredi{
	
	private $taxaDoBoleto = '1,89';
	
	public function __construct($SetArquivo){
		global $_idobjeto;
		$_idempresa=traduzid('retornoremessa', 'idretornoremessa', 'idempresa',  $_idobjeto);

		$arr = array();
		//Caminho do arquivo
		$retorno = file($SetArquivo);
		//Conta as linhas do arquivo
		$linhas  = count($retorno);
		echo("linhas".$linhas);
		//Informações da carteira
		$linha[0] = substr($retorno[0],26, -371);
		//Titulos
		for($i = 1;$i < $linhas-1;$i++){
		   
			//Numero do Boleto
			$linha[1] = (substr($retorno[$i],49, -347));
		    //Data de Pagamento
		    $linha[2] = intval(substr($retorno[$i],328, -9));
		    //Valor do Boleto
		    $linha[3] = intval(substr($retorno[$i], 152, -235));
		    //Valor Pago
		    $linha[4] = intval(substr($retorno[$i], 253, -136));  
		    //Verifica se é linha de titulo ou tarifa
			$linha[5] = trim(substr($retorno[$i], 126, -270));
			 //Numero da nf
			$linha[6] = (substr($retorno[$i],117, -276));	

			//Cria a variavel para guardar o resultado
		    $html = '';
		    //Taxa do boleto sem pontos e virgula
		    $taxa = self::limpaCaracteres($this->taxaDoBoleto);
		    //Processamento dos dados         
		    if(!empty($linha[5]) && $linha[4] > 0 && $linha[4] != $taxa ){
				$g++;
			//Numero do Boleto
		        $html.= $linha[1]                     . '<br>';
				$arr[$i]['nossonumero']= $linha[1];
			//Data de Pagamento
		        $html.= self::formataData($linha[2])  . '<br>';
				$arr[$i]['datapagamento']= self::formataData($linha[2]);
			//Valor do Boleto
		        $html.= self::formataValor($linha[3]) . '<br>';
				$arr[$i]['valorbol']= self::formataValor($linha[3]);
			//Valor Pago
		        $html.= self::formataValor($linha[4]) . '<hr>';
				$arr[$i]['valpagamento']= self::formataValor($linha[4]);

				$html.= $linha[6];
				$arr[$i]['nnfe']= $linha[6];

				
				$html.= $linha[5];
				$arr[$i]['compensado']= $linha[5];
				
				/*if($arr[$i]['nossonumero']> 200000 ){
					$sql="SELECT p.nome,c.idcontapagar,n.nnfe 
					from nf n 
					join contapagar c on(c.idobjeto=n.idnf 
					and c.tipoobjeto = 'nf'				
					and RIGHT(c.idcontapagar,6) =".$arr[$i]['nossonumero']." )
					join pessoa p on(p.idpessoa = n.idpessoa)
					where n.tiponf ='V' 
					and n.idempresa=".$_idempresa."
					and n.nnfe = ".$linha[6]."
					union 
					SELECT p.nome,c.idcontapagar,n.nnfe 
					from notafiscal n 
					join contapagar c on(c.idobjeto=n.idnotafiscal
					and c.tipoobjeto = 'notafiscal'				
					and RIGHT(c.idcontapagar,5) =".$arr[$i]['nossonumero']." )
					join pessoa p on(p.idpessoa = n.idpessoa)
					where n.idempresa=".$_idempresa."
					and n.nnfe = ".$linha[6]."
					";
				}else{*/

					$sql="SELECT p.nome,c.idcontapagar,n.nnfe 
							from nf n 
							join contapagar c on(c.idobjeto=n.idnf 
							and c.tipoobjeto = 'nf'				
							and n.controle  = left(".$arr[$i]['nossonumero'].",5)
							and c.parcela= REPLACE('".$arr[$i]['nossonumero']."', left(".$arr[$i]['nossonumero'].",5), '')
							)
							join pessoa p on(p.idpessoa = n.idpessoa)
							where n.tiponf ='V' 
							and n.idempresa=".$_idempresa."
							and n.nnfe = ".$linha[6]."
							union 
							SELECT p.nome,c.idcontapagar,n.nnfe 
							from notafiscal n 
							join contapagar c on(c.idobjeto=n.idnotafiscal
							and c.tipoobjeto = 'notafiscal'				
							and n.controle  = left(".$arr[$i]['nossonumero'].",5)
							and c.parcela= REPLACE('".$arr[$i]['nossonumero']."', left(".$arr[$i]['nossonumero'].",5), '')
							)
							join pessoa p on(p.idpessoa = n.idpessoa)
							where n.idempresa=".$_idempresa."
							and n.nnfe = ".$linha[6]."
							";

				//}				

				$res = d::b()->query($sql);
				$row = mysqli_fetch_assoc($res);
				$nome = $row['nome'];
				if(empty($row['idcontapagar'])){
					$row['idcontapagar'] = $arr[$i]['nossonumero'];
				}
				if($i> -1){
					//manda as informações do arquivo para tabela retornoremessaitem
					$v1 = str_replace(',00','',($linha[4] / 100));
					$v = str_replace('.','',$v1);
					$inretorno  = new Insert();
					$inretorno->setTable("retornoremessaitem");
					$inretorno->pagador = $nome;
				//	$inretorno->vencimento = validadate($arr[$i]['valpagamento']);
					$inretorno->datapagamento = validadate($arr[$i]['datapagamento']);
					$inretorno->valor =  str_replace(',','.',$v1);
					$inretorno->idcontapagar = $row['idcontapagar'];
					$inretorno->seunumero = $row['nnfe'];
					$inretorno->idretornoremessa = $_idobjeto;
					$save = $inretorno->save();
					
				   // unset($inretorno);
				}
				$n++;
		    }            
            //Imprime o resultado
           print_r($html);
        }
		print_r($arr);
    }
	
    public function formataValor($set){
        //Formata valor em real
        $valor = self::limpaCaracteres($set);
        $valor = ltrim($valor, "0");
        $valor = $valor / 100;
        $valor = number_format($valor, 2, ',', '.');
        return $valor;
    }
	
    public function limpaCaracteres($set){
        //Limpa caracteres especiais
        $caracter = str_replace('.','',$set);
        $caracter = str_replace(',','',$caracter);
        return $caracter;
    }
	
    public function formataData($set){
    	return date('d/m/Y',strtotime($set));
    }
}
## EXEMPLO DE USO
new RetornoSicredi($arq_final);


?>