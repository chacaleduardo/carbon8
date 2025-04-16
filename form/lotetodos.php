<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__ . "/controllers/lote_controller.php");


if ($_POST) {
	include_once("../inc/php/cbpost.php");
}


$_idprodserv=$_GET['idprodserv'];
if(empty($_idprodserv)){ die('É necessário informar o produto.');}
?>

<?

 $sql="select distinct o.idobjeto as modulo,f.idlotefracao,l.unpadrao as un,l.unlote,ps.unconv,fo.rotulo,p.nome,f.idlotefracao,f.idunidade,u.unidade,u.convestoque,f.qtd,f.qtd_exp,f.status as statusfr,
    l.idlote,l.partida,l.exercicio,l.fabricacao,l.vencimento,l.status,l.qtdpedida,l.qtdpedida_exp,
    l.qtdprod,l.qtdprod_exp,f.criadoem,f.criadopor,uu.unidade as origem,uu.idunidade as idorigem
                 from lote l 
                 join prodserv ps on(ps.idprodserv=l.idprodserv)
                join lotefracao f on(f.idlote=l.idlote)
                join unidade u on(u.idunidade=f.idunidade)
                join unidade uu on(uu.idunidade = l.idunidade)
                join unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
                join "._DBCARBON."._modulo m on m.modulo = o.idobjeto and m.modulotipo = 'lote'
                    left join prodservformula fo on(l.idprodservformula=fo.idprodservformula)
                    left join pessoa p on(p.idpessoa = l.idpessoa)
                where l.idprodserv=".$_idprodserv." order by l.idlote asc";

 $res=d::b()->query($sql) or die("Erro ao buscar lotes sql=".$sql);
 $qtd=mysqli_num_rows($res);
 if($qtd>0){
?>
<div class="row">
<div class="col-md-12">
<div class="panel panel-default">
    <div class="panel-heading">	
        <table>
            <tr>
                <td>Produto:</td>
                <td><?= traduzid('prodserv', 'idprodserv', 'descr', $_idprodserv)?></td>
                <td>Exercicio:</td>
                <td>
                    <select class='size7' id='vexercicio' name="vexercicio" onchange="filtrar(this)">
                        <option></option>
                        <?fillselect(" SELECT YEAR(NOW()), YEAR(NOW())
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 1 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 1 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 2 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 2 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 3 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 3 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 4 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 4 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 5 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 5 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 6 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 6 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 7 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 7 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 8 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 8 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 9 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 9 YEAR))
                                        UNION
                                        SELECT YEAR(DATE_SUB(NOW(), INTERVAL 10 YEAR)),YEAR(DATE_SUB(NOW(), INTERVAL 10 YEAR))                   
                            ");?>		
                    </select>                    
                </td>
                 <td>Lote:</td>
                <td>
                    <select  name="vstatus" id='vstatus'  onchange="filtrar(this)">
                        <option></option>
                        <?fillselect("select 'ESGOTADO','Esgotado' union select 'DISPONIVEL','Disponivel' ");?>		
                    </select>                    
                </td>
                <td>Unidade:</td>
                <td>
                    <select  name="vidunidade" id='vidunidade'  onchange="filtrar(this)">
                       <option value=""></option>
                        <?fillselect("select f.idunidade,u.unidade
                            from lote l 
                            join prodserv ps on(ps.idprodserv=l.idprodserv)
                            join lotefracao f on(f.idlote=l.idlote)
                            join unidade u on(u.idunidade=f.idunidade)
                            where l.idprodserv=".$_idprodserv." group by idunidade order by unidade desc");?>	
                    </select>                    
                </td>
            </tr>            
        </table>
                 
    
          
    </div>
    <div class="panel-body">
    <table class="table table-striped planilha">
    <tr>        
        <th>Partida</th>
        <th>Origem</th>	
        <th style="display: none;">Formula</th>
        <th>Cliente</th>
        <th>Unidade</th>
        <th>Fabricação</th>
        <th>Vencimento</th>
        <th>Produzido</th>
        <th>Estoque</th>         	
        <th>Lote</th>
        <th>Status</th>	
        <th></th>	
    </tr>
    <?
  while($row=mysqli_fetch_assoc($res)){



    if (
        strpos(strtolower($row['qtd_exp']), "d")
        or strpos(strtolower($row['qtd_exp']), "e")
    ) {
        $vlst = recuperaExpoente(tratanumero($row["qtd"]), $row['qtd_exp']);
        $nund = explode("d", $vlst);
        $nune = explode("e", $vlst);
        if (!empty($nund[1])) {
            $vlfim = $nund[0];
            $vlfim1 = "d" . $nund[1];
        } else {
            $vlfim = $nune[0];
            $vlfim1 = "e" . $nune[1];
        }
    } else {
        $qtdfr = $row["qtd"];
        if ($qtdfr < 0) {
            $qtdfr = 0;
        }
        $vlfim = $qtdfr;
    }
      
      ?>
    <tr class='trlote' exercicio="<?=$row['exercicio']?>" status="<?=$row['statusfr']?>" idunidade='<?=$row['idunidade']?>' idorigem='<?=$row['idorigem']?>'>  
    <td >        
            <a class="fa hoverazul pointer" onclick="janelamodal('?_modulo=<?=$row['modulo']?>&_acao=u&idlote=<?=$row['idlote']?>');">
            <?=$row['partida']?>/<?=$row['exercicio']?>
        </a>
    </td>
        <td nowrap>
            <?
                if ($row['idunidade'] == $row['idorigem']) {
                    $sqlori = 'SELECT n.nnfe,n.idnf from nf n join nfitem ni on (ni.idnf = n.idnf) join lote l on (l.idnfitem=ni.idnfitem) where l.idlote='.$row['idlote'];
                    $res1=d::b()->query($sqlori) or die("Erro ao buscar nnfe sql=".$sqlori);
                    if(mysqli_num_rows($res1) > 0){
                        $rwnf = mysqli_fetch_assoc($res1);
                        ?>
                        Compras <i class="fa fa-bars azul pointer"  onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$rwnf['idnf']?>');"></i>
                    <?
                    }else {
                        $sqlori1 = 'SELECT idformalizacao from formalizacao where idlote='.$row['idlote'];
                        $res2=d::b()->query($sqlori1) or die("Erro ao buscar formalizacao sql=".$sqlori1);
                        if(mysqli_num_rows($res2) > 0){
                            $rwfor = mysqli_fetch_assoc($res2);
                            //$rwnf['nnfe'];
                            ?>
                        Ordem de Produção <i class="fa fa-bars azul pointer"  onclick="janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$rwfor['idformalizacao']?>');"></i>
                            <?
                        }else {
                            echo $row['origem'];
                        }
                    }
                }else {
                    echo $row['origem'];
                }
            
            ?>
        </td>
        <td style="display: none;">
            <?=$row['rotulo']?>         
        </td>
        <td>
            <?=$row['nome']?>         
        </td>
        <td>
            <?=$row['unidade']?>         
        </td>
        <td>
            <?=dma($row['fabricacao'])?>
        </td> 
        <td>
            <?=dma($row['vencimento'])?>
        </td>      
        <td>
            <?
            if(strpos(strtolower($row['qtdprod_exp']),"d") 
                or strpos(strtolower($row['qtdprod_exp']),"e")){ 
                    echo recuperaExpoente(tratanumero($row["qtdprod"]),$row['qtdprod_exp']);
            }else{
                    echo number_format(tratanumero($row["qtdprod"]), 2, ',', '.');
            }
           ?>
            <?
                echo $row['unlote'];  
            ?>
        </td>  
        <td>           
            <?
            if(strpos(strtolower($row['qtd_exp']),"d") 
                or strpos(strtolower($row['qtd_exp']),"e")){ 
                    echo recuperaExpoente(tratanumero($row["qtd"]),$row['qtd_exp']);
            }else{
                    echo number_format(tratanumero($row["qtd"]), 2, ',', '.');
            }
           ?>
            <?
                echo $row['un'];  
            ?>
        </td>        
        <td>
            <?=$row['statusfr']?>
        </td>
        <td>
            <?=$row['status']?>
        </td>
        <td>
            <?if($row['statusfr']=='DISPONIVEL'){?>
                    <a class="fa fa-arrow-down btn-lg vermelho pointer " title="Retirar" onClick="ajustaest('sub',<?=$row['idlote']  ?>,<?= $row['idlotefracao'] ?>,<?= $vlfim ?>,<?= "'$vlfim1'" ?>);""></a>


                <div id="ajustaestdeb<?=$row['idlote']?>" style="display: none;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <table>
                                        <tr>
                                            <td align="right" id='ajustaestrotulo'></td>
                                            <td nowrap>
                                                <input name="" id="ajutaestidlote" type="hidden" size="6" value="">
                                                <input name="" id="ajutaestidlotefracao" type="hidden" size="6" value="">
                                                <input name="" id="ajutaestqtd" sQtddisp_exp="<?= $row['qtd_exp'] ?>" sQtddisp="<?= $row['qtd'] ?>" type="text" size="6" value="<?=recuperaExpoente(tratanumero($row['qtd'] ), $row['qtd_exp'])?>" onkeyup="mostraConsumo(this)" onchange="verificadiluicao(this)">
                                                <input name="" id="ajutaestqtc" sQtddisp_exp="<?= $row['qtd_exp'] ?>" sQtddisp="<?= $row['qtd'] ?>" type="text" size="6" value="<?=recuperaExpoente(tratanumero($row['qtd'] ), $row['qtd_exp'])?>" onkeyup="mostraConsumoCred(this)" onchange="verificadiluicao(this)">
                                            </td>
                                            <td>
                                                <label class="alert-warning">
                                                   <?
                                                        echo $row['un'];
                                                   ?>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="right">Descr.:</td>
                                            <td colspan="5">
                                                <!-- textarea name="" id="observ" style="width: 300px; height: 30px;"></textarea -->
                                                <select id="ndroptipo" class="size10" name="#name_campo">
                                                    <option value=""></option>
                                                    <? fillselect(LoteController::$statusEstoque); ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?}?>
        </td>								
    </tr>
<?



 }
?>
    </table>
    </div>
 </div>
 </div>
 </div>
<?
 }// if($qtd>0)
?>
<script>
function esgotarlote(inIdlotefracao){
    if(confirm("Deseja realmente esgotar o lote?")){
	CB.post({
	    objetos:"_x_u_lotefracao_idlotefracao="+inIdlotefracao+"&_x_u_lotefracao_status=ESGOTADO&_x_u_lotefracao_qtd=0&_x_u_lotefracao_qtd_exp=0"
	    ,parcial:true
       });
          }   
}    
    
function filtrar(){
  
       $(".trlote").find("td").parent().show();  
    
    var idunidade=$("#vidunidade").val();
    var status=$("#vstatus").val();
    var exercicio=$('#vexercicio').val();
   
    if(idunidade!=''){        
         $(".trlote").not("[idunidade="+idunidade+"]").find("td").parent().hide();
    }
    
   
    if(status!=''){
        $(".trlote").not("[status="+status+"]").find("td").parent().hide();
    }
    

    if(exercicio!=''){      
        $(".trlote").not("[exercicio="+exercicio+"]").find("td").parent().hide();
    }
}

function filtrastatus(vthis){
    
    $(".trlote").find("td").parent().show();  
    
    var status=$(vthis).val();
    $("#exercicio").find('option').attr("selected",false) ;
    $("#idunidade").find('option').attr("selected",false) ;
    
    
    if(status==''){
       $(".trlote").find("td").parent().show();  
    }else{
        $(".trlote").not("[status="+status+"]").find("td").parent().hide();
    }
}

function filtraunidade(vthis){
     debugger;
    $(".trlote").find("td").parent().show(); 
    
    var unidade=$(vthis).val();
    
    $("#exercicio").find('option').attr("selected",false) ;
    $("#status").find('option').attr("selected",false) ;
    
    if(unidade==''){
       $(".trlote").find("td").parent().show();  
    }else{
        $(".trlote").not("[idunidade="+unidade+"]").find("td").parent().hide();
    }
}

function ajustaest(vop, inidlote, inidlotefracao, vlfim, vlfim1) {

var strCabecalho = `</strong>Ajustar estoque <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick="alteraest(` + vlfim + `,'` + vlfim1 + `','` + vop + `');"><i class='fa fa-circle'></i>Salvar</button></strong>`;
//$("#cbModalTitulo").html((strCabecalho));



if (vop == "add") {
    var htmloriginal = $("#ajustaest").html();
    var objfrm = $(htmloriginal);
    objfrm.find("#ajutaestidlote").attr("name", "_999_i_lotecons_idlote");
    objfrm.find("#ajutaestidlote").attr("value", inidlote);
    objfrm.find("#ajutaestidlotefracao").attr("name", "_999_i_lotecons_idlotefracao");
    objfrm.find("#ajutaestidlotefracao").attr("value", inidlotefracao);
    objfrm.find("#ajutaestqtc").attr("value", "");
    objfrm.find("#ajutaestqtc").attr("name", "_999_i_lotecons_qtdc");
    objfrm.find("#ajutaestqtd").attr("type", "hidden");
    objfrm.find("#ajutaestqtd").attr("name", "_999_i_lotecons_qtdd");
    objfrm.find("#ajustaestrotulo").html("Qtd. Adicionar:");
} else {
    var htmloriginal = $("#ajustaestdeb"+inidlote).html();
    var objfrm = $(htmloriginal);
    objfrm.find("#ajutaestidlote").attr("name", "_999_i_lotecons_idlote");
    objfrm.find("#ajutaestidlote").attr("value", inidlote);
    objfrm.find("#ajutaestidlotefracao").attr("name", "_999_i_lotecons_idlotefracao");
    objfrm.find("#ajutaestidlotefracao").attr("value", inidlotefracao);
    //objfrm.find("#ajutaestqtd").attr("value", "");
    objfrm.find("#ajutaestqtc").attr("value", "");
    objfrm.find("#ajutaestqtd").attr("name", "_999_i_lotecons_qtdd");
    objfrm.find("#ajutaestqtc").attr("type", "hidden");
    objfrm.find("#ajutaestqtc").attr("name", "_999_i_lotecons_qtdc");
    objfrm.find("#ajustaestrotulo").html("Qtd. Retirar:");
}

objfrm.find("#ndroptipo").attr("name", "_999_i_lotecons_obs");

//Chamada para limpar os campos quando aplica o comando ctrl+s, pois estava salvando a mesma informação mais de uma vez. Lidiane(02/06/2020)
//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=326318
//O modo abaixo só abre o Modal, mas não faz todo o processo que precisa. O certo é chamar o CB.Modal
//$("#cbModalCorpo").html(objfrm.html());
//$('#cbModal').modal('show');
CB.modal({
    titulo: strCabecalho,
    corpo: objfrm.html()
});
}

function alteraest(vlfim, vlfim1, vop) {
var valid = vlfim + vlfim1;
var resud = valid.split("d", 2);
var resue = valid.split("e", 2);
if (resud[1] != null) {
    resultvali = resud[1];
    var ress = $("[name=_999_i_lotecons_qtdd]").val().split("d", 2);
} else {
    resultvali = resue[1];
    var ress = $("[name=_999_i_lotecons_qtdd]").val().split("e", 2);
}

if (resultvali != ress[1] && vop != "add") {
    alert('Qtd solicitada esta incorreta');
} else {
    if (ress[0] > vlfim && vop != "add") {
        alert('Qtd solicitada maior que a disponível');

    } else {
        var str = {
            '_x_i_lotecons_idlote': $("[name=_999_i_lotecons_idlote]").val(),
            '_x_i_lotecons_idlotefracao': $("[name=_999_i_lotecons_idlotefracao]").val(),
            '_x_i_lotecons_obs': $("[name=_999_i_lotecons_obs]").val(),
            '_x_i_lotecons_qtdc': $("[name=_999_i_lotecons_qtdc]").val(),
            '_x_i_lotecons_qtdd': $("[name=_999_i_lotecons_qtdd]").val()
        };

        $("[name=_999_i_lotecons_idlote]").attr("name", "");
        $("[name=_999_i_lotecons_idlotefracao]").attr("name", "");
        $("[name=_999_i_lotecons_obs]").attr("name", "");
        $("[name=_999_i_lotecons_qtdc]").attr("name", "");
        $("[name=_999_i_lotecons_qtdd]").attr("name", "");

        CB.post({
            objetos: str,
            parcial: true,
            posPost: function(resp, status, ajax) {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    alert(resp);
                }
            }
        });
    }
}

}



function verificadiluicao(vthis) {
		if (vthis.classList.contains('diluicaoerrada')) {
			// Se possuir, faça algo aqui
			alert("A diluição " + $(vthis).val() + " não é válida!");
			vthis.value = "";
		}
		if (vthis.classList.contains('consumoerrado')) {
			// Se possuir, faça algo aqui
			alert("Não é permitido consumir mais do que o estoque disponível do Lote!");
			vthis.value = "";
		}
		if (vthis.classList.contains('infdiluicao')) {
			// Se possuir, faça algo aqui

			alert("Valor inválido. Inserir diluição.");
			vthis.value = "";
		}
	}



	function mostraConsumo(inOConsumo) {
		inOConsumo.style.backgroundColor = "";
		inOConsumo.classList.remove('diluicaoerrada');
		inOConsumo.classList.remove('consumoerrado');
		inOConsumo.classList.remove('infdiluicao');


		debugger;
		$o = $(inOConsumo);



		//$sQtddisp_exp=$o.attr("sQtddisp_exp");
		somaUtilizacao = 0;

		if ($o.val()) {

			if ($o.attr("sQtddisp_exp") != "" && ($o.val().toLowerCase().indexOf("e") <= 0 && $o.val().toLowerCase().indexOf("d") <= 0)) {
				inOConsumo.classList.add('infdiluicao');
				inOConsumo.style.backgroundColor = "#ffff0075";
				//alertAtencao("Valor inválido. <br> Inserir e ou d.");
				return false;
			} else if ($o.attr("sQtddisp_exp") != "" && ($o.val().toLowerCase().indexOf("e") >= 0 || $o.val().toLowerCase().indexOf("d") >= 0)) {
				sQtddisp_exp = $o.attr("sQtddisp_exp");
				var stringOriginal = $o.val();
				var matches_orig = sQtddisp_exp.match(/^([\d.]+)([a-zA-Z]+)(\d+)$/);
				var matches = stringOriginal.match(/^([\d.]+)([a-zA-Z]+)(\d*)$/);

				// Verificando se a string foi dividida corretamente
				if (matches) {

					// Valor2 é a sequência de letras após os números
					var valor1 = matches_orig[2] + (matches_orig[3] || ""); // Concatenando a letra ("d" ou "e") com os números seguintes
					var valor2 = matches[2] + (matches[3] || ""); // Concatenando a letra ("d" ou "e") com os números seguintes
					if (valor1 != valor2) {
						inOConsumo.style.backgroundColor = "#ff00003b";
						inOConsumo.classList.add('diluicaoerrada');

						//alertAtencao("Valor inválido. <br> Diluição inválida");
						return false;


					}

				} else {
					alertAtencao("Valor inválido. <br> Diluição inválida");
					return false;
				}
			}

			valor = $o.val().replace(/,/g, '.');
			valor = normalizaQtd(valor);

			somaUtilizacao = valor;
		}

		sQtddisp = normalizaQtd($o.attr("sQtddisp"));

		if (somaUtilizacao > 0) {
			if (somaUtilizacao > sQtddisp) {
				inOConsumo.classList.add('consumoerrado');
				inOConsumo.style.backgroundColor = "#ff00003b";
				//alertAtencao("Valor inválido. <br> O consumo é maior que a quantidade disponível.");
				return false;
			}
		}

	}

	function mostraConsumoCred(inOConsumo) {
		inOConsumo.style.backgroundColor = "";
		inOConsumo.classList.remove('diluicaoerrada');
		inOConsumo.classList.remove('consumoerrado');
		inOConsumo.classList.remove('infdiluicao');


		debugger;
		$o = $(inOConsumo);



		//$sQtddisp_exp=$o.attr("sQtddisp_exp");
		somaUtilizacao = 0;

		if ($o.val()) {

			if ($o.attr("sQtddisp_exp") != "" && ($o.val().toLowerCase().indexOf("e") <= 0 && $o.val().toLowerCase().indexOf("d") <= 0)) {
				inOConsumo.classList.add('infdiluicao');
				inOConsumo.style.backgroundColor = "#ffff0075";
				//alertAtencao("Valor inválido. <br> Inserir e ou d.");
				return false;
			} else if ($o.attr("sQtddisp_exp") != "" && ($o.val().toLowerCase().indexOf("e") >= 0 || $o.val().toLowerCase().indexOf("d") >= 0)) {
				sQtddisp_exp = $o.attr("sQtddisp_exp");
				var stringOriginal = $o.val();
				var matches_orig = sQtddisp_exp.match(/^([\d.]+)([a-zA-Z]+)(\d+)$/);
				var matches = stringOriginal.match(/^([\d.]+)([a-zA-Z]+)(\d*)$/);

				// Verificando se a string foi dividida corretamente
				if (matches) {

					// Valor2 é a sequência de letras após os números
					var valor1 = matches_orig[2] + (matches_orig[3] || ""); // Concatenando a letra ("d" ou "e") com os números seguintes
					var valor2 = matches[2] + (matches[3] || ""); // Concatenando a letra ("d" ou "e") com os números seguintes
					if (valor1 != valor2) {
						inOConsumo.style.backgroundColor = "#ff00003b";
						inOConsumo.classList.add('diluicaoerrada');

						//alertAtencao("Valor inválido. <br> Diluição inválida");
						return false;


					}

				} else {
					alertAtencao("Valor inválido. <br> Diluição inválida");
					return false;
				}
			}

			valor = $o.val().replace(/,/g, '.');
			valor = normalizaQtd(valor);

			somaUtilizacao = valor;
		}



	}


	function normalizaQtd(inValor) {
		var sVlr = "" + inValor;
		var $arrExp;
		var fVlr;
		if (sVlr.toLowerCase().indexOf("d") > -1) {
			$arrExp = sVlr.toLowerCase().split('d');
			fVlr = (parseFloat($arrExp[0]) * parseFloat($arrExp[1])).toFixed(2);
			fVlr = parseFloat(fVlr);
		} else if (sVlr.toLowerCase().indexOf("e") > -1) {
			$arrExp = sVlr.toLowerCase().split('e');
			fVlr = $arrExp[0] * Math.pow(10, $arrExp[1]);
		} else {
			fVlr = parseFloat(sVlr).toFixed(2);
		}

		return parseFloat(fVlr);
	}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>