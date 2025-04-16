<?
require_once("../inc/php/validaacesso.php");

if (!empty($_GET["reportexport"])) {
    ob_start(); //não envia nada para o browser antes do termino do processamento
}


$_1_u_contapagar_idcontapagar = $_GET['idcontapagar'];

$_header = "Itens da Fatura"

?>
<html>

<head>
    <title><?= $_header ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
    <link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
    <script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
    <script src="../inc/js/moment/moment.min.js"></script>


        <style type="text/css">
        table {
            page-break-inside: auto
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto
        }

        thead {
            display: table-header-group
        }

        tfoot {
            display: table-footer-group
        }
    </style>
</head>

<body>
    <div class="row">
        <?

        $sqlp = "select  n.tiponf,p.cpfcnpj,c.tipoobjetoorigem,f.idformapagamento,c.idcontapagaritem,c.idcontapagar,c.status,c.datapagto,c.valor,cp.valor as valor2,n.total,ifnull(cli.razaosocial,cli.nome ) as nome,p.nome as pessoa,n.dtemissao,f.descricao as formapgto,n.nnfe,n.idnf,'idnf' as par,'pedido' as modulo, c.parcela, c.parcelas,cp.datareceb
            from contapagaritem c join pessoa p join contapagar cp join nf n join pessoa cli
            left join formapagamento f on(f.idformapagamento=c.idformapagamento)
            where c.idcontapagar =" . $_1_u_contapagar_idcontapagar . "
            and n.idnf =cp.idobjeto
            and cli.idpessoa = n.idpessoa
            and cp.tipoobjeto = 'nf'
            and cp.idcontapagar = c.idobjetoorigem
            and c.status!='INATIVO'
            and c.tipoobjetoorigem  = 'contapagar'
            and c.idpessoa = p.idpessoa 
            union 
            select n.tiponf,p.cpfcnpj,c.tipoobjetoorigem,f.idformapagamento,c.idcontapagaritem,c.idcontapagar,c.status,c.datapagto,c.valor,cp.valor as valor2,n.total,ifnull(p.razaosocial,p.nome ) as nome,ps.nome as pessoa,n.dtemissao,f.descricao as formapgto,n.nnfe,n.idnf,'idnf' as par,'nfentrada' as modulo, c.parcela, c.parcelas,cp.datareceb
            from pessoa p join contapagaritem c join nf n  join pessoa ps on(c.idpessoa = ps.idpessoa )
            left join formapagamento f on(f.idformapagamento=c.idformapagamento) left join contapagar cp on (cp.idcontapagar = c.idcontapagar)
            where c.idcontapagar =" . $_1_u_contapagar_idcontapagar . "	
                and n.idnf= c.idobjetoorigem 
                and c.tipoobjetoorigem  = 'nf'
                and c.status!='INATIVO'			   
                and n.idpessoa = p.idpessoa 
            union
            select 'S' as tiponf,p.cpfcnpj,c.tipoobjetoorigem ,f.idformapagamento,c.idcontapagaritem,c.idcontapagar,c.status,c.datapagto,c.valor,cp.valor as valor2,n.total,ifnull(p.razaosocial,p.nome ) as nome,ps.nome as pessoa,n.emissao as dtemissao,f.descricao as formapgto,n.nnfe,n.idnotafiscal as idnf,'idnotafiscal' as par,'nfs' as modulo, c.parcela, c.parcelas,cp.datareceb
            from pessoa p join contapagaritem c join notafiscal n  join pessoa ps on(c.idpessoa = ps.idpessoa )
                    left join formapagamento f on(f.idformapagamento=c.idformapagamento) left join contapagar cp on (cp.idcontapagar = c.idcontapagar)
            where c.idcontapagar =" . $_1_u_contapagar_idcontapagar . "	
                and n.idnotafiscal= c.idobjetoorigem 
                and c.tipoobjetoorigem  = 'notafiscal'
                and c.status!='INATIVO'			   
                and n.idpessoa = p.idpessoa  order by dtemissao";



        $resp = d::b()->query($sqlp) or die("Erro ao buscar outras parcelas de comissao sql=" . $sqlp);

        $_numcolunas = mysql_num_fields($resp);
        $_ipagpsqres = mysql_num_rows($resp);
        if ($_ipagpsqres == 1) {
            $strs = $_ipagpsqres . " Registro encontrado";
        } elseif ($_ipagpsqres > 1) {
            $strs = $_ipagpsqres . " Registros encontrados";
        } else {
            $strs = "Nenhum Registro encontrado";
        }
        $_nomeimpressao = "[" . md5(date('dmYHis')) . "] gerada em [" . date(" d/m/Y H:i:s") . "]";


        // GVT - 17/04/2020 - Altera logo do relatório, utiliza a imagem cadastrada na empresa.
        $sqlfig = "select logosis from empresa where idempresa =" . $_SESSION["SESSAO"]["IDEMPRESA"];
        $resfig = d::b()->query($sqlfig) or die("Erro ao recuperar figura para cabeçalho do relatório: " . mysql_error());
        $figrel = mysqli_fetch_assoc($resfig);

        //$figurarelatorio = (empty($figrel["figrelatorio"]))?"../inc/img/repheader.png":$figrel["figrelatorio"];
        //$figurarelatorio = "../inc/img/repheader.png";
        $figurarelatorio = $figrel["logosis"];

        ?>
        <table style="width: 100%;" class="tbrepheader">
            <tr>
                <td rowspan="3" style="width:50px;"><img style="width:100%;" src="<?= $figurarelatorio ?>"></td>
                <td class="header">Itens da Fatura</td>
                <td><a class="btbr20" href="<?= $_SERVER['REQUEST_URI'] ?>&reportexport=csv" target="_blank">Exportar .csv</a></td>
            </tr>
            <tr>
                <td class="subheader">
                    <h2><?= $_rep; ?></h2>
                    (<?= $strs ?>)
                </td>
            </tr>
        </table>
        <br>
        <fieldset class="fldsheader">
            <legend>Início da Impressão <?= $_nomeimpressao ?></legend>
        </fieldset>
        <table id="restbl" style="width: 100%;  font-size: 10px;" class="normal">
        <thead>
            <tr class='header'>
                <? $rowp = mysqli_fetch_assoc($resp);
                if ($rowp['tipoobjetoorigem'] == 'nf' and !empty($rowp['idnf'])) { ?>
                    <td>Nº Doc</td>
                    <td>Tipo Doc</td>
                    <td>Emiss&atilde;o</td>
                    <td>Data Pagamento</td>
                    <td>Forma Pagamento</td>
                    <td>Razão Social</td>
                    <td>CNPJ</td>
                    <td>Item</td>
                    <td>Categoria</td>
                    <td> Subcategoria</td>
                    <td>Conta</td>
                    <td>Valor Total Item</td>
                    <td>Valor Parcela</td>
                    <td>Parcela</td>
                    <td>Valor NF</td>
            </tr>
        <? } else { ?>
            <td>Nº Doc</td>
            <td>Emiss&atilde;o</td>
            <td>Data Pagamento</td>
            <td>Forma Pagamento</td>
            <td>Razão Social</td>
            <td>CNPJ</td>
            <td>Valor NF</td>
            <td>Valor parcela(NF)</td>
            <td>Parcela</td>
            <td>Comissão</td>
            </tr>
        <? } ?>
        </thead>
            <?
            $conteudoexport; // guarda o conteudo para exportar para csv
            $conteudoexport = '"N Doc";"Tipo Doc";"Emissao";"Data Pagamento";"Forma Pagamento";"Razão Social";"CNPJ";"Item";"Categoria";"Tipo  Item";"Conta";"Valor Total Item";"Valor Parcela";"Parcela";"Valor NF"';
            $conteudoexport .= "\n"; //QUEBRA DE LINHA NO CONTEUDO CSV
            $arrSomatoriaPorTipoES = array();
            $i = -1;
            $gpes = "";
            $arr = array();
            mysqli_data_seek($resp, 0);
            while ($rowp = mysqli_fetch_assoc($resp)) {

                if (empty($rowp['nnfe'])) {
                    $nnfe = $rowp['idnf'];
                } else {
                    $nnfe = $rowp['nnfe'];
                }

                if ($rowp["tiponf"] == "C") {
                    $tiponf = "DANFE";
                }
                if ($rowp["tiponf"] == "V") {
                    $tiponf = "SAIDA";
                }
                if ($rowp["tiponf"] == 'S') {
                    $tiponf = "SERVICO";
                }
                if ($rowp["tiponf"] == 'T') {
                    $tiponf = "CTE";
                }
                if ($rowp["tiponf"] == 'E') {
                    $tiponf = "CONCESSIONÁRIA";
                }
                if ($rowp["tiponf"] == 'M') {
                    $tiponf = "GUIA/CUPOM";
                }
                if ($rowp["tiponf"] == 'R') {
                    $tiponf = "PJ";
                }
                if ($resf["tiponf"] == 'B') {
                    $tiponf = "RECIBO";
                }

                $nfType = $rowp['tipoobjetoorigem'];

                if ($rowp['tipoobjetoorigem'] == 'nf' and !empty($rowp['idnf'])) {
                    $sqli = "select round(i.qtd,2) as qtd,ifnull(p.descr,i.prodservdescr) as item,
                            ci.contaitem,tp.tipoprodserv,i.total as vlritem, tp.conta
                        from nfitem i left join prodserv p on(p.idprodserv=i.idprodserv)
                        left join contaitem ci on(ci.idcontaitem = i.idcontaitem)
                        left join  tipoprodserv tp on(tp.idtipoprodserv=i.idtipoprodserv)
                    where i.nfe='Y' and i.idnf =" . $rowp['idnf'];
                    $resi = d::b()->query($sqli) or die("Erro ao buscar itens da parcela: " . mysql_error());


                    while ($rowi = mysqli_fetch_assoc($resi)) {
            ?>
        <tr class="respreto">
            <td dataType="varchar"><?= $nnfe ?></td>
            <td dataType="varchar"><?= $tiponf ?></td>
            <td dataType="varchar"><?= dma($rowp['dtemissao']) ?></td>
            <td dataType="varchar"><?= dma($rowp['datareceb']) ?></td>
            <td dataType="varchar"><?= $rowp['formapgto']; ?></td>
            <td dataType="varchar"><?= $rowp['nome'] ?></td>
            <td dataType="varchar"><?= formatCnpjCpf($rowp['cpfcnpj']) ?></td>
            <td dataType="varchar"><?= $rowi['item'] ?></td>
            <td dataType="varchar"><?= $rowi['contaitem'] ?></td>
            <td dataType="varchar"><?= $rowi['tipoprodserv'] ?></td>
            <td dataType="varchar" style="text-align: end;"><?= $rowi['conta'] ?></td>
            <!--conta-->
            <td dataType="decimal" style="text-align: end;">R$ <?= number_format(tratanumero($rowi['vlritem']), 2, ',', '.'); ?></td>
            <td dataType="decimal" style="text-align: end;">R$ <?= number_format(tratanumero($rowp['valor']), 2, ',', '.'); ?></td>
            <td dataType="varchar" style="text-align: end;"><?= $rowp['parcela'] ?>/<?= $rowp['parcelas'] ?></td>
            <td dataType="decimal" style="text-align: end;">R$ <?= number_format(tratanumero($rowp['total']), 2, ',', '.'); ?></td>
        </tr>
    <?

                        $smvlritem += $rowi['vlritem'];
                        $arrSomatoriaPorTipoES[$rowi['contaitem']]['grupoes'] = $rowi['contaitem'];
                        $arrSomatoriaPorTipoES[$rowi['contaitem']]['vlritem'] += $rowi['vlritem'];

                        if(in_array($nnfe, $arr)){
                            continue;
                        } else {
                            $smvalor += $rowp['valor'];
                            $smtotal += $rowp['total'];
                            $arrSomatoriaPorTipoES[$rowi['contaitem']]['valor'] += $rowp['valor'];
                            $arrSomatoriaPorTipoES[$rowi['contaitem']]['total'] += $rowp['total'];
                            $arr[]=$nnfe;
                        }

                        $conteudoexport .= '"' . $nnfe . '";"' . $tiponf . '";"' . dma($rowp['dtemissao']) . '";"' . dma($rowp['datareceb']) . '";"' . $rowp['formapgto'] . '";"' . $rowp['nome'] . '";"' . $rowp['cpfcnpj'] . '";"' . $rowi['item'] . '";"' . $rowi['contaitem'] . '";"' . $rowi['tipoprodserv'] . '";"' . number_format(tratanumero($rowi['vlritem']), 2, ',', '.') . '";"' . number_format(tratanumero($rowp['valor']), 2, ',', '.') . '";"' . $rowp['parcela'] . '/' . $rowp['parcelas'] . '";"' . number_format(tratanumero($rowp['total']), 2, ',', '.') . '"';
                        $conteudoexport .= "\r\n"; //QUEBRA DE LINHA NO CONTEUDO CSV
                    }
                } else {
    ?>
    <tr class="respreto">
        <td><?= $nnfe ?></td>
        <td><?= dma($rowp['dtemissao']) ?></td>
        <td><?= dma($rowp['datareceb']) ?></td>
        <td><?= $rowp['formapgto']; ?></td>
        <td><?= $rowp['nome'] ?></td>
        <td><?= formatCnpjCpf($rowp['cpfcnpj']) ?></td>
        <td style="text-align: end;">R$ <?= number_format(tratanumero($rowp['total']), 2, ',', '.'); ?></td>
        <td style="text-align: end;">R$ <?= number_format(tratanumero($rowp['valor2']), 2, ',', '.'); ?></td>
        <td style="text-align: center;"><?= $rowp['parcela'] ?> / <?= $rowp['parcelas'] ?></td>
        <td style="text-align: end;">R$ <?= number_format(tratanumero($rowp['valor']), 2, ',', '.'); ?></td>
    </tr>



<?
                    $sValor += $rowp['valor'];
                    $sVal += $rowp['valor2'];
                    $sTotal += $rowp['total'];
                    $conteudoexport .= '"' . $nnfe . '";"' . $tiponf . '";"' . dma($rowp['dtemissao']) . '";"' . dma($rowp['datareceb']) . '";"' . $rowp['formapgto'] . '";"' . $rowp['nome'] . '";"' . $rowp['cpfcnpj'] . '";"";"";"";"";"' . number_format(tratanumero($rowp['valor']), 2, ',', '.') . '";"' . $rowp['parcela'] . '/' . $rowp['parcelas'] . '";"' . number_format(tratanumero($rowp['total']), 2, ',', '.') . '"';
                    $conteudoexport .= "\r\n"; //QUEBRA DE LINHA NO CONTEUDO CSV
                }
            }

            if ($nfType != 'nf') { ?>
<tr>
    <td colspan="20">&nbsp</td>
</tr>
<tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td style="text-align: end;  padding: 3px;"><strong>R$ <?= number_format(tratanumero($sTotal), 2, ',', '.'); ?></strong></td>
    <td style="text-align: end;  padding: 3px;"><strong>R$ <?= number_format(tratanumero($sVal), 2, ',', '.'); ?></strong></td>
    <td></td>
    <td style="text-align: end;  padding: 3px;"><strong>R$ <?= number_format(tratanumero($sValor), 2, ',', '.');  ?></strong></td>
</tr>
<? } ?>

        </table>


        <? if ($nfType == 'nf') { ?>
            <table class="normal" style="width: 100%; font-size: 10px; margin-top: 30px;">
                <tr class='header'>
                    <td style="padding: 3px;">Grupo ES</td>
                    <td style="text-align: end; padding: 3px;">Valor Total Itens</td>
                    <td style="text-align: end; padding: 3px;">Valor Total Parcelas</td>
                    <td style="text-align: end; padding: 3px;">Valor Total NF's</td>
                </tr>



                <? foreach ($arrSomatoriaPorTipoES as $key => $value) { ?>
                    <tr>
                        <td style="padding: 3px;"><?= $value['grupoes'] ?></td>
                        <td style="text-align: end; padding: 3px;">R$ <?= number_format(tratanumero($value['vlritem']), 2, ',', '.') ?></td>
                        <td style="text-align: end; padding: 3px;">R$ <?= number_format(tratanumero($value['valor']), 2, ',', '.') ?></td>
                        <td style="text-align: end; padding: 3px;">R$ <?= number_format(tratanumero($value['total']), 2, ',', '.') ?></td>
                    </tr>
                <?
                    $sumVlrItem += $value['vlritem'];
                    $sumValor += $value['valor'];
                    $sumTotal += $value['total'];
                } ?>
                <tr>
                    <td colspan="20">&nbsp</td>
                </tr>
                <tr>
                    <td style="text-align: end; padding: 3px;"><strong>Total</strong></td>
                    <td style="text-align: end; padding: 3px;"><strong>R$ <?= number_format(tratanumero($sumVlrItem), 2, ',', '.') ?></strong></td>
                    <td style="text-align: end; padding: 3px;"><strong>R$ <?= number_format(tratanumero($sumValor), 2, ',', '.') ?></strong></td>
                    <td style="text-align: end; padding: 3px;"><strong>R$ <?= number_format(tratanumero($sumTotal), 2, ',', '.') ?></strong></td>
                </tr>
            </table>
        <? } ?>
    </div>
</body>

</html>
<?
if (!empty($_GET["reportexport"])) {
    ob_end_clean(); //não envia nada para o browser antes do termino do processamento

    /* Gerar o nome do arquivo para exportar
	 * Substitui qualquer caractere estranho pelo sinal de '_'
	 * Caracteres que NAO SERAO substituidos:
	 *   - qualquer caractere de A a Z (maiusculos)
	 *   - qualquer caracteres de a a z (minusculos)
	 *   - qualquer caractere de 0 a 9
	 *   - e pontos '.'
	 */
    $infilename = empty($_header) ? $_rep : $_header;
    $infilename = preg_replace("/[^A-Za-z0-9s.]/", "", $infilename);
    //gera o csv
    header("Content-type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=" . $infilename . ".csv");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo utf8_decode($conteudoexport);
}
?>

<script>
    $('#restbl  thead td').each((j, k) => {        
            $(k).append(`<br>&nbsp;<i class='fa fa-arrow-down pointer' title='Ordenar Crescente' style='font-size: 0.8em; opacity: 0;' attr='desc'></i>&nbsp;<i class='fa fa-arrow-up pointer' title='Ordenar Decrescente' style='font-size: 0.8em; opacity: 0;' attr='asc'></i>`)
        })

        function sortTable(e) {
		var th = e.target.parentElement;
		$(e.target).addClass("azul");
		$(th).addClass("ativo");
		$(e.target).siblings().removeClass("azul");
		$(th).siblings().removeClass("ativo");
		$(e.target.parentElement).siblings().each((e,o)=>{
			$(o).children().removeClass('azul').css('opacity','0')
		})
		var ordenacao = $(e.target).attr("attr");
		switch (ordenacao) {
			case 'asc':
				colunas = -1;
				break;
			case 'desc':
				colunas =  1 ;
				break;
		
			default:
			colunas =  1
				break;
		}

		var n = 0; while (th.parentNode.cells[n] != th) ++n;
		var order = th.order || 1;
		//th.order = -order;
		var t = this.closest("thead").nextElementSibling;

		t.innerHTML = Object.keys($(t.rows))
			.filter(k => !isNaN(k))
			.map(k => t.rows[k])
			.sort((a, b) => order * (isNaN(typed(a))&&isNaN(typed(b))) ? ((typed(a).localeCompare(typed(b)) > 0) ? colunas : -colunas):(typed(a) > typed(b) ? colunas : -colunas))
			.map(r => r.outerHTML)
			.join('')

		function typed(tr) {
			try {
                var s = tr.cells[n].innerText;
            } catch (error) {
                debugger
            }
				
				var dataType = tr.cells[n].attributes.datatype.value;
				
				if(dataType == 'varchar'){
					
					if(!s || /^\s*$/.test(s)){
						s = 'zzzzzzzzzzz';
					}

				} else if(dataType == 'decimal' || dataType == 'int') {
					//trata números	
					if(typeof s == 'string'){
						s = s.replace('R$','').trim()
						s = s.replaceAll('.','').replaceAll(',','.')
					}

					if(!s || /^\s*$/.test(s)){
						s = '9999999999999';
					}

				}

			if (s.match(",")) {
				isNaN(s.replaceAll(",","."))?s = s.toString():s = s.replaceAll(",",".")
			}
			if (isNaN(s) && s.match(/^[a-zA-Z]+/)) {
				var d = s;
				var date = d;
			}else{
				if (s.match("/") && s.match(/^[a-zA-Z]+/) == null) {
					
					var d = mda(s);
					var date = Date.parse(d);
				}else{
					var d = s;
					var date = d;
				}

			}
			if (!isNaN(date)) {
				return isNaN(date) ? s.toLowerCase() : Number(date);
			}else{
				if (!isNaN(s.replaceAll(",",'.'))) {
					return  Number(s.replaceAll(",",'.'));
				}else{

					return s.toLowerCase();
				}
			}
		}

	}

    function mda(inDatetime){
        if(inDatetime && inDatetime!==""){
            return moment(inDatetime,["DD/MM/YYYY","YYYY/MM/DD","DD/MM/YYYY HH:mm:ss"]).format("MM/DD/YYYY");
        }else{
            return "";
        }
    }





    $(document).ready(function(){
        console.log('Ready disparado');

        $('#restbl thead td i').on('click', sortTable);                                

        $('#restbl thead td').mouseover(function(){
            $(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e,o)=>{
            $(o).css("opacity","1").addClass('hoverazul')
            })
        });

        $('#restbl thead td').mouseout(function(){
            $(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e,o)=>{
                if (!$(o).hasClass('azul')) {
                    $(o).css("opacity","0").removeClass('hoverazul')
                }
            })
        });

    });  


</script>