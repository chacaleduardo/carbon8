<?
include_once("../inc/php/validaacesso.php");
$idobjetojson = $_GET['idobjetojson'];
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
        <link href="../inc/css/carbon.css" media="all" rel="stylesheet" type="text/css" />
        <link href="../inc/bootstrap/css/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" />
        <link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
        <script src="/inc/js/jquery/jquery-1.11.2.min.js"></script>
        <style type="text/css">
            * {
                text-shadow: none !important;
                filter:none !important;
                -ms-filter:none !important;
                font-family: Helvetica, Arial;
                font-size: 11px;
                -webkit-box-sizing: border-box; 
                -moz-box-sizing: border-box;    
                box-sizing: border-box; 
            }
            html{
                background-color: silver;
            }
            body {
                line-height: 1.4em;
                background-color: white;
            }
            table{width: 100%;}
            .descricaoTabela{width: 10%;}
            .detalheTabela{width: 50%;}
            th{text-align: start;}
            @media print {
                body {-webkit-print-color-adjust: exact;margin: 0cm;}
                html{background-color: transparent;}
	            .quebrapagina{ page-break-before:always; }
	            .rot{color: #777777;}
            }
            .titulodoc{
                height: inherit;
                line-height: inherit;
                font-size: 12px;
                font-weight: bold;
                color: #666;
            }
            @media screen{
                body {
                    margin: auto;
                    margin-top: 0.2cm;
                    margin-bottom: 1cm;
                    padding: 3mm 10mm;
                    width: 29cm;
                }
                .quebrapagina{
                    page-break-before:always;
                    border: 2px solid #c0c0c0;
                    width: 120%;
                    margin: 1.5cm -1.5cm;
                }
                .rot{
                    color: gray;
                }
            }
        </style>
    </head>
    <body>
        <pagina>
            <?
            if (!empty($_GET)){
                $sqlCotacao = "SELECT jobjeto FROM objetojson WHERE idobjetojson =".$idobjetojson;
                $resCotacao = d::b()->query($sqlCotacao) or die("Erro ao recuperar Objeto: ".mysql_error());
                $rowCotacao = mysqli_fetch_assoc($resCotacao);

                //Recupera os dados congelados da Cotação para montar o PDF
                $cotacao = unserialize(base64_decode($rowCotacao["jobjeto"]));
                ?>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <table>
                                <tr style="height: 20px;">
                                    <td><span class="titulodoc">ID:</span> <?=$cotacao['cotacao']['res']['idnf']?></td>
                                    <td><span class="titulodoc">Orçamento de Compra:</span> <?=$cotacao['orcamento']['res']['idcotacao']?></td>
                                    <td><span class="titulodoc">Número NF:</span> <? if(!empty($cotacao['cotacao']['res']['nnfe'])){echo $cotacao['cotacao']['res']['nnfe'];} else {echo '-';}?></td>
                                    <td><span class="titulodoc">Série:</span> <? if(!empty($cotacao['cotacao']['res']['serie'])){echo $cotacao['cotacao']['res']['serie'];} else {echo '-';}?></td>
                                    <td><span class="titulodoc">Emitente:</span> <?=$cotacao['fornecedor']['res']['nomecurto']?></td>
                                    <?if(!empty($cotacao['orcamento']['res']['tiponf'])){?>
                                        <td><span class="titulodoc">Tipo: </span>
                                            <? 
                                            if($cotacao['orcamento']['res']['tiponf'] == "C"){
                                                echo('<label class="alert-warning">Danfe</label>');
                                            }elseif($cotacao['orcamento']['res']['tiponf'] == "M"){
                                                echo('<label class="alert-warning">Guia/Cupom</label>');
                                            }elseif($cotacao['orcamento']['res']['tiponf'] == "B"){
                                                echo('<label class="alert-warning">Recibo</label>');
                                            }else{
                                                echo('<label class="alert-warning">Serviço</label>');
                                            }
                                            ?>
                                        </td> 
                                    <? } ?>
                                    
                                    <td><span class="titulodoc">Prazo:</span> <?=dma($cotacao['orcamento']['res']['prazo'])?></td> 
                                </tr>   
                                <tr>
                                    <td colspan="3" style="padding-bottom: 15px;"><span class="titulodoc">Responsável:</span> <?=$cotacao['responsavel']['res']['nomecurto']?></td>
                                    <td colspan="4"><span class="titulodoc">Título:</span> <?=$cotacao['orcamento']['res']['titulo']?></td>
                                </tr>             
                            </table> 
                        </div>
                        <div class="panel-body">
                            <table>
                                <tr>
                                    <td style="width:100%">
                                        <b>Fornecedor:</b>
                                        <br />
                                        <?=$cotacao['fornecedor']['res']['razaosocial']?>
                                        <br />							
                                        CNPJ: <?=formatarCPF_CNPJ($cotacao['fornecedor']['res']['cpfcnpj'], true);?> | I.E: <?=$cotacao['fornecedor']['res']['inscrest'] ?><br />
                                        <? echo $cotacao['fornecedor']['res']['logradouro']." ".$cotacao['fornecedor']['res']['endereco'].", ".$cotacao['fornecedor']['res']['numero']." - ".$cotacao['fornecedor']['res']['bairro'] ?>
                                        <? if(!empty($cotacao['fornecedor']['res']['complemento'])){ echo " - ".$cotacao['fornecedor']['res']['complemento'];}?> 
                                        <br />
                                        CEP: <? echo $cotacao['fornecedor']['res']['cep']." - ".$cotacao['fornecedor']['res']['cidade']."/".$cotacao['fornecedor']['res']['uf']." - (".$cotacao['fornecedor']['res']['dddfixo'].") ".$cotacao['fornecedor']['res']['telfixo']?> <br />
                                    </td>	 						
                                </tr>
                            </table>
                            <hr style="border:1px solid;color: #e6e6e6">
                            <table class="padding0" >
                                <tr>
                                    <td><b>Solicitante</b> (Dados para faturamento, cobrança e entrega)</td>
                                </tr>
                            </table>	
                            <table class="padding0">
                                <tr>
                                    <td style="width: 50%"><?=nl2br($cotacao['empresa']['res']["infosolicitante"])?></td>
                                    <td valign=bottom>
                                        <font color="red">OBS:</font><br/>
                                        <?=nl2br($cotacao['empresa']['res']["rodapecotacao"])?>
                                    </td>
                                </tr>
                                <tr>
                                <td><br></td></tr>						
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <table>
                            <tbody>
                                <tr>
                                    <td align="right" class="descricaoTabela">Nº Orçamento:</td>
                                    <td><?=$cotacao['transporte']['res']['pedidoext']?></td>				
                                    <td align="right" class="descricaoTabela">Vendedor(a):</td>
                                    <td><?=$cotacao['transporte']['res']['aoscuidados']?></td>				
                                    <td align="right" class="descricaoTabela">Telefone:</td>
                                    <td><?=$cotacao['transporte']['res']['telefone']?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-default">    
                        <div class="panel-heading">Itens</div>                                                                               
                        <table class="table table-striped" >
                            <tbody>
                                <tr>
                                    <th>Qtd</th>
                                    <th>Un</th>
                                    <th>Descrição</th> 
                                    <th>Moeda</th>                     
                                    <th>Valor Un</th>
                                    <th>Desc R$</th>
                                    <th>ICMS R$</th>
                                    <th>IPI R$</th>
                                    <th>Total</th>
                                    <th>Validade Produto</th>
                                    <th>Previsão de Entrega</th>
                                    <th>Obs</th>
                                </tr>  
                                <?
                                $itens = $cotacao['nfitem']['res'];                                
                                foreach($itens AS $_itens) 
                                {
                                    ?>          
                                    <tr>
                                        <td><?=$_itens['qtdsol']?></td>
                                        <td><?=$_itens['unidade']?></td>
                                        <td><?=$_itens['codforn']?></td> 
                                        <td><?=$_itens['moeda']?></td>
                                        <td><?=$_itens['vlritem']?></td>
                                        <td><?=$_itens['des']?></td>
                                        <td><?=$_itens['aliqicms']?></td>
                                        <td><?=$_itens['valipi']?></td>
                                        <td><?=$_itens['total']?></td>
                                        <td><?=$_itens['validade']?></td>
                                        <td><?=$_itens['previsaoentrega']?></td>
                                        <td><?=$_itens['obs']?></td>
                                    </tr>
                                    <? 
                                    if($_itens['moeda']=="BRL"){
                                        $total = $total + $_itens['total'] + $_itens['valipi'] - $_itens['des'];
                                        $moeda= $_itens['moeda'];
                                    }else{
                                        $total = $total + $_itens['totalext'];
                                        $moeda = $_itens['moeda'];
                                    }
                                } 
                                ?>
                                <tr>
                                    <td colspan="7"></td>
                                    <td colspan="10"></td>
                                </tr>                                
                                <tr>
                                    <td align="right" colspan="9">Sub-Total::<b><?=$moeda?> </b></td>
                                    <td align="right"><b><?=number_format(tratanumero($total), 2, ',', '.');?></b></td>
                                    <td colspan="7"></td>
                                </tr>
                                <tr>
                                    <td align="right" colspan="9">Total:<b><?=$moeda?></b></td>
                                    <td align="right"><b><?=number_format(tratanumero($total+$cotacao['transporte']['res']['frete']), 2, ',', '.');?></b></td>
                                    <td colspan="7"></td>
                                </tr>
                            </tbody>
                        </table> 
                        <table >
                            <tr>
                                <th>*Moeda</th>
                            </tr>
                            <tr>
                                <td><b>BRL:</b> Real Brasileiro / <b>USD:</b> Dólar dos Estados Unidos / <b>EUR:</b> Zona Euro</td>
                            </tr>
                        </table>
                        <br>                      
                    </div>
                </div>
                    <div class="col-md-6" style="width: 50%; float:left">
                        <div class="panel panel-default">    
                            <div class="panel-heading">Transporte</div>
                            <table id="transporte92">
                                <tbody>
                                    <tr>
                                        <td>Transportadora: <?=$cotacao['transportadora']['res']['nome']?></td> 
                                        <td>FRETE:
                                            <? if($cotacao['transporte']['res']['modfrete'] == 0){
                                                echo 'CIF';
                                            } elseif($cotacao['transporte']['res']['modfrete'] == 1) {
                                                echo 'FOB';
                                            }
                                            ?>
                                        </td> 
                                    </tr> 
                                    <tr>
                                        <td>OBS: <?=$cotacao['transporte']['res']['obs']?></td> 
                                        <td>FRETE(RS): <?=number_format(tratanumero($cotacao['transporte']['res']['frete']), 2, ',', '.');?></td> 
                                    </tr>
                                </tbody>
                            </table>	
                        </div>
                    </div>
                    <div class="col-md-6" style="width: 50%; float:right">
                        <div class="panel panel-default">    
                            <div class="panel-heading">Pagamento</div>
                            <table id="transporte92">
                                <tbody>
                                    <tr>
                                        <td>Pagamento: <?=$cotacao['pagamento']['res']['descricao']?></td> 
                                        <td>1º Vencimento: <?=$cotacao['transporte']['res']['diasentrada']?> Dias</td> 
                                    </tr> 
                                    <tr>
                                        <td>Parcelas: <?=$cotacao['transporte']['res']['parcelas']?></td> 
                                        <td>Intervalo Parcelas: <?=$cotacao['transporte']['res']['intervalo']?> Dias</td> 
                                    </tr> 
                                </tbody>
                            </table>	
                        </div>
                    </div>

                <br /><br /><br /><br /><br />
                <div class="col-md-12">
                    <div class="panel panel-default">    
                        <div class="panel-heading">Observações do Solicitante</div>
                        <table>
                            <tr>
                                <td>
                                    <?=nl2br($cotacao['empresa']['res']["obssolicitante"])?>
                                </td>
                            </tr>
                        </table>	
                    </div>
                </div>                                 
            <?
            }
            ?>
        </pagina>
    </body>
</html>