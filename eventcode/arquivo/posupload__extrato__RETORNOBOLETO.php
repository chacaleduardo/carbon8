<?

// lê o arquivo que foi enviado
$lastid = d::b()->insert_id;
echo $lastid;

$_idobjeto;
$arq = fopen($arq_final,'r');
$arr = array();
//$cabecalho = ['pagador','vencimento','datapagamento','valor','carteira','nossonumero','seunumero'];
$cabecalho = ['carteira','pagador','cpfcnpj','tipo','nossonumero','seunumero','emissao','vencimento','datapagamento','baixa','valor','valorpago','status'];

$numcolunas = count($cabecalho);



//echo('colunas'.$numcolunas );

$i = -1;
while(!feof($arq)){
    if($i > -1){
        $linha = fgets($arq);
        $linha = str_replace('"', '', $linha);
        $colunas = explode(",",$linha); 
        //print_r( $colunas );
        foreach($cabecalho as $k => $v){
            $arr[$i][$v] = $colunas[$k];
            
        }
       
    }else{
        fgets($arq);
    }
   // print_r($arr);

    if($i> -1){

      //  if($arr[$i]['nossonumero']< 200000 ){

        if(!empty($arr[$i]['nossonumero'])){

            $arr[$i]['nossonumero']= ltrim($arr[$i]['nossonumero'], '0');// retirar zero a esquerda

            $sql="SELECT p.nome,c.idcontapagar,n.nnfe 
            from nf n 
            join contapagar c on(c.idobjeto=n.idnf 
            and c.tipoobjeto = 'nf'	         
            and c.parcela=  SUBSTRING('".$arr[$i]['nossonumero']."', 6, 1)
            )
            join pessoa p on(p.idpessoa = n.idpessoa)
            where n.tiponf ='V' 
            and n.controle  = left(".$arr[$i]['nossonumero'].",5)
            union 
            SELECT p.nome,c.idcontapagar,n.nnfe 
            from notafiscal n 
            join contapagar c on(c.idobjeto=n.idnotafiscal
            and c.tipoobjeto = 'notafiscal'				
        
            and c.parcela=  SUBSTRING('".$arr[$i]['nossonumero']."', 6, 1)
            )
            join pessoa p on(p.idpessoa = n.idpessoa)
            where n.controle  = left(".$arr[$i]['nossonumero'].",5)";

           // die($sql);

            $res = d::b()->query($sql);
            $row = mysqli_fetch_assoc($res);
            $nome = $row['nome'];
            if(empty($row['idcontapagar'])){
                $row['idcontapagar'] = $arr[$i]['nossonumero'];
            }
      //  }else{
       //     $row['idcontapagar'] = $arr[$i]['nossonumero'];
      //  }

        //manda as informações do arquivo para tabela retornoremessaitem
        //   $v1 = str_replace(',00','',$arr[$i]['valor']);
       // $v = str_replace('.','',$v1);

        
        $inretorno  = new Insert();
        $inretorno->setTable("retornoremessaitem");
        $inretorno->pagador = $arr[$i]['pagador'];
        $inretorno->vencimento = validadate($arr[$i]['vencimento']);
        $inretorno->datapagamento = validadate($arr[$i]['datapagamento']);
       // $inretorno->valor =  str_replace(',','.',$v);
       $inretorno->valor = convertToMoneyFormat($arr[$i]['valorpago']);
        $inretorno->carteira = $arr[$i]['carteira'];
        $inretorno->idcontapagar = $row['idcontapagar'] ;
        $inretorno->seunumero = $arr[$i]['seunumero'];
        $inretorno->idretornoremessa = $_idobjeto;
        $save = $inretorno->save();
      }
       // unset($inretorno);
    }
    $i++;

}

//print_r($arr);
fclose($arq);
print_r ($arr);


function convertToMoneyFormat($value) {
    // Ensure the value is treated as a string
    $value = (string)$value;
    
    // Check if the string length is less than 3 (e.g., "5" should become "0.05")
    if (strlen($value) < 3) {
        $value = str_pad($value, 3, "0", STR_PAD_LEFT);
    }
    
    // Insert the decimal point before the last two characters
    $formattedValue = substr($value, 0, -2) . '.' . substr($value, -2);
    
    return $formattedValue;
}

?>
