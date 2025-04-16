<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
$pagvaltabela = "etiqueta";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idetiqueta" => "pk"
);
$pagsql = "select * from etiqueta where idetiqueta = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");


if($_1_u_etiqueta_idetiqueta){
    function listaimpressoras(){
        global $_1_u_etiqueta_idetiqueta, $_1_u_etiqueta_tipo;

        $sql = "SELECT CONCAT(e.sigla,'-',t.tag,' ',t.descricao) AS nome,t.idtag,t.fabricante 
                FROM tag t 
                    JOIN tagtipo tp ON (tp.idtagtipo = t.idtagtipo AND tp.tagtipo='IMPRESSORA') 
                    JOIN empresa e ON (e.idempresa=t.idempresa) 
                WHERE t.status not in ('INATIVO','DESAPARECIDO','ESTOQUE','MANUTENCAO')
                    AND t.linguagem = '".$_1_u_etiqueta_tipo."'
                    AND NOT EXISTS(
                        SELECT 1 
                        FROM objetovinculo ov 
                        WHERE ov.tipoobjeto='etiqueta' 
                            AND ov.idobjeto=$_1_u_etiqueta_idetiqueta 
                            AND ov.idobjetovinc=t.idtag 
                            AND ov.tipoobjetovinc='tag') -- ".getidempresa("t.idempresa","tag")."
                            ;";
        $res = d::b()->query($sql) or die("listaimpressoras: Erro: ".mysqli_error(d::b())."\n".$sql);
        
        $arrret=array();
        $i=0;
        while($r = mysqli_fetch_assoc($res)){
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$i]["nome"]=$r["nome"];
            $arrret[$i]["fabricante"]=$r["fabricante"];
            $arrret[$i]["idtag"]=$r["idtag"];
            $i++;
        }
        return $arrret;
    }

    function listaModulos(){
        global $_1_u_etiqueta_idetiqueta;

        $sqlet = "SELECT m.idmodulo, m.rotulomenu, m.modulo
            FROM carbonnovo._modulo m 
            WHERE m.status = 'ATIVO' AND 
                NOT EXISTS (SELECT 1 
                        FROM etiquetaobjeto eo 
                        WHERE eo.tipoobjeto = 'modulo' AND 
                            eo.idobjeto = m.idmodulo AND 
                            eo.idetiqueta = ".$_1_u_etiqueta_idetiqueta.")";
		$reszpl=d::b()->query($sqlet) or die("Erro ao etiquetas vinculadas: ".mysqli_error(d::b()));

        $arrret=array();
        $i=0;
        while($r = mysqli_fetch_assoc($reszpl)){
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$i]["idmodulo"]=$r["idmodulo"];
            $arrret[$i]["rotulo"]=$r["rotulomenu"];
            $arrret[$i]["modulo"]=$r["modulo"];
            $i++;
        }
        return $arrret;
    }

    $arrImp=listaimpressoras();
    $jImpressora=$JSON->encode($arrImp);

    $jModulo = $JSON->encode(listaModulos());
}
?>
<div class="row">
    <div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading col-md-12">
            <div class="col-md-1">
                <input type="hidden" name="_1_<?=$_acao?>_etiqueta_idetiqueta" value="<?=$_1_u_etiqueta_idetiqueta?>">
                ID: <label class="alert-warning"><?=$_1_u_etiqueta_idetiqueta?></label>
            </div>
            <?if($_acao == 'i'){?>
                <div class="col-md-3">
                    Nome: <input type="text" name="_1_<?=$_acao?>_etiqueta_nomeetiqueta" class="size25" value="<?=$_1_u_etiqueta_nomeetiqueta?>" vnulo>
                </div>
            <?}else{?>
                <div class="col-md-2">
                    Nome: <label class="alert-warning"><?=$_1_u_etiqueta_nomeetiqueta?></label>
                </div>
            <?}?>
            <div class="col-md-4">
                Rótulo: <input type="text" name="_1_<?=$_acao?>_etiqueta_rotuloetiqueta" class="size25" value="<?=$_1_u_etiqueta_rotuloetiqueta?>">
            </div>
            <div class="col-md-2">
                Tipo: <select name="_1_<?=$_acao?>_etiqueta_tipo" >
                            <?=fillselect("SELECT 'ESCPOS', 'ESC/POS' UNION SELECT 'ZPL', 'ZPL' UNION SELECT 'TSPL', 'TSPL'",$_1_u_etiqueta_tipo)?>
                        </select>
            </div>
            <div class="col-md-2">
                Status: <select name="_1_<?=$_acao?>_etiqueta_status" >
                            <?=fillselect("SELECT 'ATIVO' as ATIVO,  'ATIVO' as ATIVO UNION SELECT 'INATIVO' as INATIVO,  'INATIVO' as INATIVO ",$_1_u_etiqueta_status)?>
                        </select>
            </div>
        </div>
        <div class="panel-body">
            <div class="col-md-12">
                <?if($_1_u_etiqueta_idetiqueta){?>
                    <div class="row">
                        <div class="col-md-6">
                            <textarea id="zpl" cols="80" rows="25" name="_1_<?=$_acao?>_etiqueta_cod" spellcheck="false"><?if(empty($_1_u_etiqueta_cod)){echo '^XA^CF0,60^FO220,50^FDHello World!^FS^XZ';}else{echo $_1_u_etiqueta_cod;}?></textarea>
                            <div class="buttons">
                                <button type="button" class="btn btn-default" id="redraw"><i class="fa fa-pencil-square-o"></i> Desenhar</button>
                                <button type="button" class="btn btn-default" id="addImage"><i class="fa fa-picture-o"></i> Add imagem</button>
                                <button type="button" class="btn btn-default" id="rotate"><i class="fa fa-rotate-right"></i> Girar</button>
                                <button type="button" class="btn btn-default" id="openFile"><i class="fa fa-folder-open-o"></i> Abrir Arquivo</button>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading"  data-toggle="collapse" href="#controls">
                                    <i class="fa fa-gears"></i>  Config
                                </div>
                                <div id="controls" class="group real panel-body">
                                    <div class="col-md-12">
                                        <label for="density" class="aligned">Densidade:</label>
                                        <select name="_1_<?=$_acao?>_etiqueta_densidade"  id="density">
                                            <? fillselect("SELECT '6', '6 dpmm (152 dpi)'
                                                    UNION SELECT '8', '8 dpmm (203 dpi)'
                                                    UNION SELECT '12', '12 dpmm (300 dpi)'
                                                    UNION SELECT '24', '24 dpmm (600 dpi)'",$_1_u_etiqueta_densidade)?>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="quality" class="aligned">Qualidade:</label>
                                        <select name="_1_<?=$_acao?>_etiqueta_qualidade" id="quality">
                                        <? fillselect("SELECT 'grayscale', 'Grayscale'
                                                    UNION SELECT 'bitonal', 'bitonal'",$_1_u_etiqueta_qualidade)?>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="width" class="aligned">Label Size:</label>
                                        <input type="number" class="size10" placeholder="width" min="1" step="any" required="required" name="_1_<?=$_acao?>_etiqueta_largura" id="width" value="<?=number_format($_1_u_etiqueta_largura)?>">
                                        <span>x</span>
                                        <input type="number" class="size10" placeholder="height" min="1" step="any" required="required" name="_1_<?=$_acao?>_etiqueta_altura" id="height" value="<?=number_format($_1_u_etiqueta_altura)?>">
                                        <select name="_1_<?=$_acao?>_etiqueta_unmedida" class="size10" id="units">
                                        <? fillselect("SELECT 'inches', 'Pol'
                                                    UNION SELECT 'cm', 'Cm'
                                                    UNION SELECT 'mm', 'Mm'
                                                    ",$_1_u_etiqueta_unmedida)?>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="hidden" placeholder="index" min="0" max="0" step="1" required="required" name="index" id="index" value="0">
                                        <input type="hidden" name="total" id="total" value="1" disabled>
                                        <input type="hidden" name="remember" id="remember">
                                        <input type="hidden" id="rotation" value="0">
                                        <input type="file" id="imageFile" accept="image/*" class="hidden">
                                        <input type="file" id="zplFile" class="hidden">
                                    </div>
                                </div>
                            </div>
                        </div>        
                        <?if($_1_u_etiqueta_tipo == "ZPL"){?>
                        <div class="col-md-6">
                            <img id="label" alt="Label">
                            <div id="error" style="display:none"></div>
                            <div class="buttons">
                                <button type="button" class="btn btn-default" id="downloadZpl"><i class="fa fa-cloud-download"></i> ZPL</button>
                                <button type="button" class="btn btn-default" id="downloadPng"><i class="fa fa-cloud-download"></i> PNG</button>
                                <button type="button" class="btn btn-default" id="downloadPdf"><i class="fa fa-cloud-download"></i> PDF</button>
                                <button type="button" class="btn btn-default" id="downloadPdfAll"><i class="fa fa-cloud-download"></i> Multi-Label PDF</button>
                            </div>
                            <div id="warningsPlaceholder" class="group placeholder hidden">
                                <a class="hideShow" href="#" aria-label="Show linter warnings"><i class="fa fa-exclamation-triangle"></i> <i class="fa fa-lg fa-angle-double-down"></i></a>
                            </div>
                            <div id="warnings" class="group real">
                                <a class="hideShow" href="#" aria-label="Hide linter warnings"><i class="fa fa-exclamation-triangle"></i> <i class="fa fa-lg fa-angle-double-up"></i></a>
                                <label>Linter Warnings (0):</label>
                                <br>
                                <span class="note">None</span>
                            </div>
                            <div id="version"></div>
                        </div>
                        <?}?>
                    </div>
                    <?}?>
            </div>
        </div>
    </div>
    </div>
</div>
<?if($_1_u_etiqueta_idetiqueta){?>
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">Impressoras Vinculadas</div>
            <div class="panel-body" style="padding-top: 10px !important;">
                <input type="text" name="" id="busca_impressoras">
                <?
                $sqlimp='SELECT CONCAT(e.sigla,"-",t.tag," ",t.descricao) as nome,t.idtag,ov.idetiquetaobjeto , t.fabricante from tag t JOIN etiquetaobjeto ov on (ov.idobjeto=t.idtag and ov.tipoobjeto="tag") JOIN empresa e on (e.idempresa=t.idempresa)
                WHERE ov.idetiqueta='.$_1_u_etiqueta_idetiqueta.'';
                $res = d::b()->query($sqlimp) or die("Erro aos buscar impressora vinculadas: ".mysqli_error(d::b())."\n".$sqlimp);
                if (mysqli_num_rows($res) > 0) {?>
                    <hr>
                    <table style="width: 100%;">
                        <tr>
                            <td>Tag</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?while($row = mysqli_fetch_assoc($res)){?>
                            <tr>
                                <td>
                                    <?=$row['nome']?> - <?=$row['fabricante']?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="?_modulo=tag&_acao=u&idtag=<?=$row['idtag']?>" target="_blank" class="fa fa-pencil cinzaclaro hoverazul pointer"></a>
                                </td>
                                <td style="text-align: center;">
                                    <i onclick="desvincularobjeto(<?=$row['idetiquetaobjeto']?>)" class="fa fa-times vermelho fade"></i>
                                </td>
                            </tr>
                        <?}?>
                    </table>
                <?}?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">Módulos Vinculados</div>
            <div class="panel-body" style="padding-top: 10px !important;">
                <input type="text" name="" id="busca_modulos">
                <?
                $sqlimp="SELECT ov.idetiquetaobjeto, m.idmodulo, m.modulo, ov.grupo
                from etiquetaobjeto ov JOIN "._DBCARBON."._modulo m on (ov.idobjeto = m.idmodulo) 
                where ov.tipoobjeto='modulo' and ov.idetiqueta = ".$_1_u_etiqueta_idetiqueta;
                $res = d::b()->query($sqlimp) or die("Erro aos buscar módulos vinculados: ".mysqli_error(d::b())."\n".$sqlimp);
                if (mysqli_num_rows($res) > 0) {?>
                    <hr>
                    <table style="width: 100%;">
                        <tr>
                            <td>Módulo</td>
                            <td>Grupo</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?
                        $k = 1000;
                        while($row = mysqli_fetch_assoc($res)){?>
                            <tr>
                                <td>
                                    <?=$row['modulo']?>
                                </td>
                                <td style="width:20%">
                                    <input type="hidden" name="_<?=$k?>_u_etiquetaobjeto_idetiquetaobjeto" value="<?=$row['idetiquetaobjeto']?>">
                                    <input type="number" name="_<?=$k?>_u_etiquetaobjeto_grupo" value="<?=$row['grupo']?>" min="1">
                                </td>
                                <td style="text-align: center;">
                                    <a href="?_modulo=_modulo&_acao=u&idmodulo=<?=$row['idmodulo']?>" target="_blank" class="fa fa-pencil cinzaclaro hoverazul pointer"></a>
                                </td>
                                <td style="text-align: center;">
                                    <i onclick="desvincularobjeto(<?=$row['idetiquetaobjeto']?>)" class="fa fa-times vermelho fade"></i>
                                </td>
                            </tr>
                        <?$k++;
                        }?>
                    </table>
                <?}?>
            </div>
        </div>
    </div>
</div>
<?}?>
<?
if(!empty($_1_u_etiqueta_idetiqueta)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_etiqueta_idetiqueta; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "etiqueta"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<?if ($_1_u_etiqueta_idetiqueta) {?>
<script>
    
    const apiServer = 'https://api.labelary.com';
    const ERROR_DOWN_FOR_MAINTENANCE = "ERROR: Temporarily down for maintenance";

    const FACTORS = {
        inches: 1,
        cm: 0.393701,
        mm: 0.0393701
    };

    var debugOn = false;

function desvincularobjeto(inid){
	CB.post({
		objetos : {
			"_x_d_etiquetaobjeto_idetiquetaobjeto": inid
		}
		,parcial:true
	});
}
jImpressora = <?=$jImpressora?> || "";
$("#busca_impressoras").autocomplete({
        source: jImpressora
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.nome+" - <span class='cinzaclaro fonte08'>"+item.fabricante+"</span></a>").appendTo(ul);
            };
        }
        ,select: function(event, ui){
        CB.post({
            objetos : {
                "_x_i_etiquetaobjeto_idobjeto":ui.item.idtag
				,"_x_i_etiquetaobjeto_tipoobjeto":'tag'
				,"_x_i_etiquetaobjeto_idetiqueta":$("[name$=idetiqueta]").val()
            }
            ,parcial: true
        });
    }
});

jModulo = <?=$jModulo?> || "";
$("#busca_modulos").autocomplete({
        source: jModulo
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
            return $('<li>').append("<a>"+item.rotulo+" - <span class='cinzaclaro fonte08'>"+item.modulo+"</span></a>").appendTo(ul);
            };
        }
        ,select: function(event, ui){
        CB.post({
            objetos : {
                "_x_i_etiquetaobjeto_idobjeto":ui.item.idmodulo
				,"_x_i_etiquetaobjeto_tipoobjeto":'modulo'
				,"_x_i_etiquetaobjeto_idetiqueta":$("[name$=idetiqueta]").val()
            }
            ,parcial: true
        });
    }
});

$(".cbupload").dropzone({
    idObjeto: $("[name=_1_u_etiqueta_idetiqueta]").val()
    ,tipoObjeto: 'etiqueta'
    ,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
});

<?if($_1_u_etiqueta_tipo == 'ZPL'){?>
    function refreshVersion() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', apiServer + '/version');
        xhr.onload = function() { $id('version').textContent = 'Powered by Labelary API version ' + this.response };
        xhr.send();
    }

    function refreshLabelRotation() {
        // refresh the label rotation CSS transform and margins when the label size changes (necessary if rotation = 90 or 270)
        // we set duration = 0 because the user didn't explicitly rotate the label, so we don't want to animate it
        var rotation = parseInt($id('rotation').value);
        setRotation(rotation, 0);
    }

    function rotateLabel() {
        var rotation = parseInt($id('rotation').value);
        setRotation(rotation + 90);
    }

    function setRotation(rotation, duration) {

        if (duration == null) {
            duration = 0.5;
        }

        var label = $id('label');
        var maxWidth = (rotation % 180 == 0 ? 400 : 600);
        var maxHeight = (rotation % 180 == 0 ? 600 : 400);

        var x, y;
        if (rotation % 360 == 90) {
            x = '0';
            y = '-100%';
        } else if (rotation % 360 == 180) {
            x = '100%';
            y = '-100%';
        } else if (rotation % 360 == 270) {
            // this is the only case where the translation coordinates are based on the new image size computed
            // by the browser (based on the image max width and max height), rather than the old image size; note
            // also that the use of naturalWidth and naturalHeight requires that the image has already been
            // loaded before this method is called, or these two attributes will not be available
            var widthFactor = maxWidth / label.naturalWidth;
            var heightFactor = maxHeight / label.naturalHeight;
            var factor = Math.min(widthFactor, heightFactor);
            x = (label.naturalHeight * factor) + 'px';
            y = ((label.naturalWidth - label.naturalHeight) * factor) + 'px';
        } else {
            x = '0';
            y = '0';
        }

        // Using client-side CSS for label rotation is great, but there is a catch: it does not affect the size of the parent
        // element or the page layout. This means that the content immediately beneath the label is positioned as it would be
        // if the label were not rotated at all. If the label is naturally taller than it is wide, this leads to extra empty
        // space below the label when it is rotated by 90 or 270 degrees. If the label is naturally wider than it is tall,
        // this leads to the label overlapping the content below it when it is rotated by 90 or 270 degrees. So we adjust the
        // label bottom margin, in order to maintain the correct visual spacing.
        var margin;
        if (rotation % 360 == 90 || rotation % 360 == 270) {
            var widthFactor = maxWidth / label.naturalWidth;
            var heightFactor = maxHeight / label.naturalHeight;
            var factor = Math.min(widthFactor, heightFactor);
            margin = factor * (label.naturalWidth - label.naturalHeight);
        } else {
            margin = 0;
        }

        label.style.transition = duration + 's ease-in-out';
        label.style.transformOrigin = 'left bottom';
        label.style.transform = 'translateX(' + x + ') translateY(' + y + ') rotate(' + rotation + 'deg)';
        label.style.maxWidth = maxWidth + 'px';
        label.style.maxHeight = maxHeight + 'px';
        label.style.marginBottom = margin + 'px';

        var r = $id('rotation');
        r.value = rotation;
        r.dispatchEvent(new Event('change')); // hidden inputs don't automatically trigger change events
    }

    function refreshLabel() {
        submitLabelRequest(false, false, function(xhr) {
            if (xhr.readyState == 4) {
                var totalCountHeader = xhr.getResponseHeader('X-Total-Count'); // received with all 200s and with some 404s
                var warningsHeader = xhr.getResponseHeader('X-Warnings'); // received with some 200s
                if (xhr.status == 200) {
                    var label = $id('label');
                    var wurl = window.URL || window.webkitURL;
                    wurl.revokeObjectURL(label.src);
                    label.src = wurl.createObjectURL(xhr.response);
                    labelDone(totalCountHeader, warningsHeader);
                } else if (xhr.status >= 400 && xhr.status <= 599) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        labelDone(totalCountHeader, '', reader.result);
                    };
                    reader.readAsText(xhr.response);
                } else if (xhr.status == 0) {
                    // if the Labelary API server is down, the API gateway that sits in front of it will return a valid HTTP
                    // error code, e.g. 404; however, the API gateway isn't configured for CORS, which means that the HTTP
                    // error response comes back without the requisite "Access-Control-Allow-Origin" header, in which case
                    // from the perspective of this code we see a response with readyState = 4 but status = 0...
                    // http://broadcast.oreilly.com/2010/04/ajax-readystate-is-4-but-statu.html
                    // https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/status
                    labelDone(0, '', ERROR_DOWN_FOR_MAINTENANCE);
                }
            }
        });
    }

    function submitLabelRequest(pdf, all, orsc) {

        var zpl = $id('zpl').value;
        var uzpl = zpl.toUpperCase();
        if (uzpl.indexOf('^XA') == -1 && uzpl.indexOf('\x02') == -1 && uzpl.indexOf('~CC') == -1) {
            // no need to even hit the server to get the 404 error...
            labelDone('0', '', 'ERROR: The ZPL is missing a starting ^XA command.');
            return;
        } else if (uzpl.indexOf('^XZ') == -1 && uzpl.indexOf('\x03') == -1 && uzpl.indexOf('~CC') == -1) {
            // no need to even hit the server to get the 404 error...
            labelDone('0', '', 'ERROR: The ZPL is missing an ending ^XZ command.');
            return;
        }

        var units = $id('units').value;
        var factor = FACTORS[units];

        var density = $id('density').value;
        var quality = $id('quality').value;
        var width = $id('width').value * factor;
        var height = $id('height').value * factor;
        var index = $id('index').value;
        var baseUrl = apiServer + '/v1/printers/' + density + 'dpmm/labels/' + width + 'x' + height + '/';
        if (!pdf || !all) {
            baseUrl += index + '/';
        }

        // If we add the ZPL string directly to the form data then things generally work, but Firefox will treat newlines
        // in the ZPL string like "\n" (LF) in the clientside JavaScript and then send them as "\r\n" (CRLF) to the server.
        // We want both the client and the server to use the same newline character, so that warning indexes are accurate.
        // Wrapping the ZPL string in a Blob forces Firefox to send "\n" (LF) to the server.
        // Chrome does the right thing (LF across the board) regardless of whether we wrap the ZPL string in a Blob or not.
        var zplBlob = new Blob([zpl], { type: 'text/plain' });

        // Use a POST request so that the ZPL is never truncated, regardless of length.
        // The _charset_ field is special, can contain the form encoding (see WHATWG links below).
        // http://www.html5rocks.com/en/tutorials/file/xhr2/
        // http://www.henryalgus.com/reading-binary-files-using-jquery-ajax/
        // https://developer.mozilla.org/en-US/docs/Web/API/FormData
        // https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#form-submission-algorithm
        // https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#constructing-the-form-data-set
        // https://stackoverflow.com/a/17818574
        var formData = new FormData();
        formData.append('file', zplBlob);
        formData.append('_charset_', 'UTF-8');
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() { orsc(this) };
        xhr.open('POST', baseUrl);
        xhr.setRequestHeader('Accept', pdf ? 'application/pdf' : 'image/png');
        xhr.setRequestHeader('X-Quality', quality);
        xhr.setRequestHeader('X-Linter', 'On');
        xhr.responseType = 'blob';
        xhr.send(formData);
    }

    function labelDone(totalCountHeader, warningsHeader, errorMessage) {

        var error = $id('error');
        var label = $id('label');
        error.innerHTML = errorMessage;
        error.style.display = (errorMessage ? 'block' : 'none');
        label.style.display = (errorMessage ? 'none' : 'block');

        var index = $id('index');
        index.max = Math.max((totalCountHeader ? parseInt(totalCountHeader) - 1 : 0), 0);

        var total = $id('total');
        total.value = totalCountHeader;

        warningsHeader = warningsHeader || '';
        setWarnings(warningsHeader);
    }

    // function refreshPermalink() {
    //     var density = $id('density').value;
    //     var quality = $id('quality').value;
    //     var width = $id('width').value;
    //     var height = $id('height').value;
    //     var units = $id('units').value;
    //     var index = $id('index').value;
    //     var rotation = parseInt($id('rotation').value) % 360;
    //     var zpl = encodeURIComponent($id('zpl').value);
    //     $id('permalink').href = '?density=' + density +
    //                             '&quality=' + quality +
    //                             '&width=' + width +
    //                             '&height=' + height +
    //                             '&units=' + units +
    //                             '&index=' + index +
    //                             '&rotation=' + rotation +
    //                             '&zpl=' + zpl;
    // }

    function store(eventOrId) {
        var remember = $id('remember').checked;
        if (remember) {
            var input = /* event */ eventOrId.target || /* id */ $id(eventOrId);
            if (input.id == 'redraw') input = $id('zpl');
            var value = (input.type === 'checkbox' ? input.checked : input.value);
            if (input.id == 'rotation') value = parseInt(value) % 360;
            localStorage.setItem(input.id, value);
        }
    }

    function storeAll() {
        store('density');
        store('quality');
        store('width');
        store('height');
        store('units');
        store('index');
        store('rotation');
        store('zpl');
        store('remember');
    }

    function toggleStorage() {
        var action;
        var remember = $id('remember').checked;
        if (remember) {
            action = 'Enable Remember';
            storeAll();
        } else {
            action = 'Disable Remember';
            unstoreAll(function(key) { return !key.startsWith('layout.') }); // clear, but keep layout settings
        }
        track(action);
    }

    function unstoreAll(filter) {
        var matches = [];
        for (var i = 0; i < localStorage.length; i++) {
            var key = localStorage.key(i);
            if (filter.call(null, key)) matches.push(key);
        }
        for (var i = 0; i < matches.length; i++) {
            localStorage.removeItem(matches[i]);
        }
    }

    // http://stackoverflow.com/questions/166221/how-can-i-upload-files-asynchronously-with-jquery
    function uploadImage() {
        var file = $id('imageFile').files[0];
        if (!file) return; // e.g. file dialog cancelled
        var formData = new FormData();
        formData.append('file', file);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', apiServer + '/v1/graphics');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState != XMLHttpRequest.DONE) return;
            if (xhr.status == 200) {
                var data = JSON.parse(xhr.responseText);
                var zpl = $id('zpl').value;
                var index = zpl.toUpperCase().indexOf('^XA');
                var cmd =
                    '\n'
                    + '\n'
                    + '^FO50,50^GFA,' + data.totalBytes + ',' + data.totalBytes + ',' + data.rowBytes + ',' + data.data + '^FS\n'
                    + '\n'
                    + '\n';
                if (index == -1) {
                    zpl = cmd + zpl;
                } else {
                    zpl = zpl.substring(0, index + 3) + cmd + zpl.substring(index + 3);
                }
                $id('zpl').value = zpl;
                store('zpl');
                refreshLabel();
                //refreshPermalink();
            } else if (xhr.status == 0) {
                // see note in refreshLabel() for details on when this might happen
                alert(ERROR_DOWN_FOR_MAINTENANCE);
            } else {
                alert(xhr.responseText);
            }
        };
        xhr.send(formData);
    }

    function loadZpl() {
        var file = $id('zplFile').files[0];
        if (!file) return; // e.g. file dialog cancelled
        var reader = new FileReader();
        reader.onload = function(e) {
            $id('zpl').value = reader.result;
            store('zpl');
            refreshLabel();
            //refreshPermalink();
        };
        reader.readAsText(file);
    }

    function downloadZpl() {
        var zpl = $id('zpl');
        var wurl = window.URL || window.webkitURL;
        var blob = new Blob([ zpl.value ], { type: 'application/zpl' });
        var url = wurl.createObjectURL(blob);
        triggerDownload(url, 'label.zpl');
        wurl.revokeObjectURL(url);
    }

    function downloadPng() {
        var label = $id('label');
        if (label && label.src) {
            triggerDownload(label.src, 'label.png');
        }
    }

    function downloadPdf() {
        downloadPdfPlease(false);
    }

    function downloadPdfAll() {
        downloadPdfPlease(true);
    }

    function downloadPdfPlease(all) {
        submitLabelRequest(true, all, function(xhr) {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    var wurl = window.URL || window.webkitURL;
                    var url = wurl.createObjectURL(xhr.response);
                    var filename = all ? 'labels.pdf' : 'label.pdf';
                    triggerDownload(url, filename);
                    wurl.revokeObjectURL(url);
                } else if (xhr.status >= 400 && xhr.status <= 599) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        showDownloadError(reader.result);
                    };
                    reader.readAsText(xhr.response);
                } else if (xhr.status == 0) {
                    // see note in refreshLabel() for details on when this might happen
                    showDownloadError(ERROR_DOWN_FOR_MAINTENANCE);
                }
            }
        });
    }

    function showDownloadError(errorMessage) {
        if (errorMessage) {
            alert(errorMessage);
        }
    }

    function triggerDownload(url, filename) {
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.style = 'display: none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function hideShow(event) {
        var clicked = event.currentTarget.parentNode;
        var placeholder = clicked.id.endsWith('Placeholder');
        var other = placeholder ?
                    $id(clicked.id.slice(0, -11)) : // placeholder clicked: hide placeholder, show group
                    $id(clicked.id + 'Placeholder'); // group clicked: hide group, show placeholder
        clicked.classList.toggle('hidden');
        other.classList.toggle('hidden');
        var real = placeholder ? other : clicked;
        localStorage.setItem('layout.hide.' + real.id, real.id == clicked.id);
        event.preventDefault();
    }

    function track(action) {
        if (window.ga && ga.loaded) ga('send', 'event', 'Viewer', action);
    }

    function debug(s) {
        if (debugOn) console.log(s);
    }

    function $id(id) {
        return document.getElementById(id);
    }

    /************************************************ PAGE LOAD *************************************************/

    function initPage() {

        var zpl = readFromQueryStringOrLocalStorage('zpl');
        if (zpl) {
            // custom ZPL requested via query string or remembered from last visit
            $id('zpl').value = zpl;
        } else {
            // select recipient name in standard sample ZPL, if it's there
            var name = 'John Doe';
            var input = $id('zpl');
            var index = input.value.indexOf(name);
            if (index >= 0) {
                input.focus();
                input.setSelectionRange(index, index + name.length);
            }
        }

        var density = readFromQueryStringOrLocalStorage('density');
        if (density) {
            // custom print density requested via query string or remembered from last visit
            $id('density').value = density;
        }

        var quality = readFromQueryStringOrLocalStorage('quality');
        if (quality) {
            // custom print quality requested via query string or remembered from last visit
            $id('quality').value = quality;
        }

        var width = readFromQueryStringOrLocalStorage('width');
        if (width) {
            // custom width requested via query string or remembered from last visit
            $id('width').value = width;
        }

        var height = readFromQueryStringOrLocalStorage('height');
        if (height) {
            // custom height requested via query string or remembered from last visit
            $id('height').value = height;
        }

        var units = readFromQueryStringOrLocalStorage('units');
        if (units) {
            // custom label size units requested via query string or remembered from last visit
            $id('units').value = units;
        }

        var index = readFromQueryStringOrLocalStorage('index');
        if (index) {
            // custom label index requested via query string or remembered from last visit
            $id('index').value = index;
        }

        var rotation = readFromQueryStringOrLocalStorage('rotation');
        if (rotation) {
            // custom label rotation requested via query string or remembered from last visit
            setRotation(rotation, 0);
        }

        var remember = (localStorage.getItem('remember') == 'true');
        if (remember) {
            // user has previously asked us to remember their last label (CANNOT be toggled via query string!)
            $id('remember').checked = true;
        }

        var hideControls = (localStorage.getItem('layout.hide.controls') == 'true');
        if (hideControls) {
            // user has previously hidden the controls group (CANNOT be toggled via query string!)
            var a = document.querySelector('#controls > a');
            hideShow( { currentTarget: a, preventDefault: function(){} } );
        }

        var hideHelp = (localStorage.getItem('layout.hide.help') == 'true');
        if (hideHelp) {
            // user has previously hidden the help group (CANNOT be toggled via query string!)
            var a = document.querySelector('#help > a');
            hideShow( { currentTarget: a, preventDefault: function(){} } );
        }

        var hideWarnings = (localStorage.getItem('layout.hide.warnings') == 'true');
        if (hideWarnings) {
            // user has previously hidden the warnings group (CANNOT be toggled via query string!)
            var a = document.querySelector('#warnings > a');
            hideShow( { currentTarget: a, preventDefault: function(){} } );
        }

        storeAll();
        refreshLabel();
        //refreshPermalink();
        refreshVersion();
    }

    function readFromQueryStringOrLocalStorage(name) {
        return getParameterByName(name) || localStorage.getItem(name);
    }

    // http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
    function getParameterByName(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results == null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    /************************************************* WARNINGS *************************************************/

    var warnings = [];
    var zplSelection = {};
    var beforeInputData = null;
    var beforeInputZplLength = 0;

    function setWarnings(warningsHeader) {
        // new warnings from server contain byte indexes and sizes, we need to convert them to UTF-8 character indexes and sizes
        // we could use TextEncoder, but we don't actually need the byte values, just the byte lengths
        // https://developer.mozilla.org/en-US/docs/Web/API/TextEncoder (see the polyfill example)
        // https://stackoverflow.com/questions/6226189/how-to-convert-a-string-to-bytearray#answer-51904484
        // https://en.wikipedia.org/wiki/Comparison_of_Unicode_encodings#Eight-bit_environments
        var values = warningsHeader.split("|");
        var newWarnings = [];
        for (var i = 0; i + 4 < values.length; i += 5) {
            newWarnings.push({
                i: parseInt(values[i], 10),     // index (bytes, not chars)
                s: parseInt(values[i + 1], 10), // size (bytes, not chars)
                c: values[i + 2],               // command
                p: values[i + 3],               // parameter
                m: values[i + 4]                // message
            });
        }
        var zpl = $id('zpl').value;
        var updatedWarnings = [];
        for (var c = 0, b = 0; c < zpl.length && newWarnings.length > 0; ) {
            for (var w = 0; w < newWarnings.length; w++) {
                var warning = newWarnings[w];
                if (warning.i == b) {
                    warning.ic = c; // index (chars, not bytes)
                }
            }
            var point = zpl.charCodeAt(c);
            if (point <= 0x007F) b += 1;
            else if (point <= 0x07FF) b += 2;
            else if (point >= 0xD800 && point <= 0xDBFF) b+= 2; // high surrogate, 2 surrogates together should sum to 4 bytes
            else if (point >= 0xDC00 && point <= 0xDFFF) b+= 2; // low surrogate, 2 surrogates together should sum to 4 bytes
            else b += 3;
            c++;
            for (var w = 0; w < newWarnings.length; w++) {
                var warning = newWarnings[w];
                if (warning.i + warning.s == b) {
                    warning.s = c - warning.ic;
                    warning.i = warning.ic;
                    delete warning.ic;
                    updatedWarnings.push(warning); // add
                    newWarnings.splice(w, 1); // remove
                    w--; // stay at current index (due to removal)
                }
            }
        }
        // parameter warning ranges may contain newlines (and that's fine), but for presentation
        // purposes we don't want to include any newlines at the boundaries (leading or trailing)
        for (var i = 0; i < updatedWarnings.length; i++) {
            var warning = updatedWarnings[i];
            while (zpl[warning.i + warning.s - 1] == '\n' && warning.s > 0) {
                warning.s--;
            }
            while (zpl[warning.i] == '\n' && warning.s > 0) {
                warning.i++;
                warning.s--;
            }
        }
        // replace the warnings, now that the byte indexes and sizes have been converted to character indexes and sizes
        warnings = updatedWarnings;
        // rebuild the UI (i.e. the div that contains the list of warnings)
        var div = $id('warnings');
        while (div.children.length > 1) div.lastChild.remove(); // keep first child (show/hide icon)
        var count = warnings.length + (warnings.length == 20 ? '+' : ''); // 20 is max; if we got 20, there are probably more
        div.appendChild(createElement('label', 'Linter Warnings (' + count + '):'));
        div.appendChild(createElement('br'));
        if (warnings.length > 0) {
            for (var i = 0; i < warnings.length; i++) {
                var warning = warnings[i];
                var msg = (warning.c ? warning.c + ': ' : '') + warning.m;
                div.appendChild(createElement('i', '', { class: 'fa fa-warning' }));
                div.appendChild(createElement('a', msg, { href: '#', onclick: 'return highlightWarning(' + i + ')' }));
                div.appendChild(createElement('br'));
            }
        } else {
            div.appendChild(createElement('span', 'None', { class: 'note' }));
        }
    }

    function highlightWarning(i) {
        var zpl = $id('zpl');
        var warning = warnings[i];
        var start = warning.i;
        var end = warning.i + warning.s;
        // Firefox automatically scrolls the selection into view, but Chrome doesn't
        // https://bugs.chromium.org/p/chromium/issues/detail?id=331233
        // https://stackoverflow.com/questions/7464282/javascript-scroll-to-selection-after-using-textarea-setselectionrange-in-chrome
        // The workaround is to blur + focus the text area; Chrome will scroll the textarea to
        // the cursor when this happens, as long as the selection is collapsed at the time
        // https://stackoverflow.com/questions/29899364/how-do-you-scroll-to-the-position-of-the-cursor-in-a-textarea
        zpl.setSelectionRange(start, start); // collapse selection
        zpl.blur();
        zpl.focus();
        zpl.setSelectionRange(start, end); // expand selection
        return false;
    }

    function trackSelectionBeforeInput(event) {
        var zpl = $id('zpl');
        zplSelection.start = zpl.selectionStart;
        zplSelection.end = zpl.selectionEnd;
        debug('new selection: ' + zplSelection.start + '-' + zplSelection.end);
        // Chrome has a bug where it does not include data for some input events (insertFromPaste, insertFromDrop),
        // but does include this data for the corresponding beforeinput events... the (hopefully temporary) workaround
        // is to store the beforeinput event data here for later use, if needed
        beforeInputData = event.data;
        // when content is deleted via the Backspace or Delete keys (deleteContentBackward, deleteContentForward), the
        // event data is never populated; we could assume that one char was deleted and we would usually be right, but
        // we would be wrong for characters like emojis which use two chars (surrogate pairs); in order to cover this
        // corner case, we store the pre-input ZPL length and later compare it to the post-input ZPL length
        beforeInputZplLength = zpl.value.length;
    }

    function adjustWarningsOnInput(event) {
        // https://rawgit.com/w3c/input-events/v1/index.html#interface-InputEvent-Attributes
        var zpl = $id('zpl');
        var type = event.inputType;
        debug('adjusting warnings (trigger event type: ' + type +
            ', prior selection: ' + zplSelection.start + '-' + zplSelection.end +
            ', data: ' + event.data + ')');
        if (type.startsWith('insert')) {
            // insertText (user typed something)
            // insertLineBreak (user hit Enter)
            // insertFromPaste (user pasted text with Ctrl+V)
            // insertFromDrop (user selected text and dragged it here)
            var data  = (type != 'insertLineBreak' ? event.data || beforeInputData : '\n');
            handleDeleteAndInsert(warnings, zplSelection.start, zplSelection.end, data);
        } else if (type.startsWith('delete')) {
            // deleteContentBackward (user hit Backspace)
            // deleteContentForward (user hit Delete)
            // deleteByCut (user cut text with Ctrl+X)
            // deleteByDrag (user selected text and dragged it from here)
            var start = zplSelection.start;
            var end = zplSelection.end;
            if (start == end) {
                if (type == 'deleteContentBackward') start -= (beforeInputZplLength - zpl.value.length);
                if (type == 'deleteContentForward') end += (beforeInputZplLength - zpl.value.length);
            }
            handleDeleteAndInsert(warnings, start, end, '');
        } else if (type.startsWith('format')) {
            // no need to handle formatting changes (not even sure these are possible on a vanilla textarea...)
        } else {
            // historyUndo (Ctrl+Z)
            // historyRedo (Ctrl+Y)
            // unhandled, so we unlink the warnings rather than allow the links to select the wrong text
            var links = document.querySelectorAll('#warnings > a');
            for (var i = 0; i < links.length; i++) {
                var link = links[i];
                var span = createElement('span', link.text);
                link.parentNode.replaceChild(span, link);
            }
        }
    }

    function handleDeleteAndInsert(warnings, selectionStart, selectionEnd, insertedText) {
        for (var i = 0; i < warnings.length; i++) {
            var warning = warnings[i];
            // calculate overlap between warning range and selection range BEFORE we modify warning range
            var overlapStart = Math.max(selectionStart, warning.i);
            var overlapEnd   = Math.min(selectionEnd,   warning.i + warning.s);
            // check if selection range is fully inside warning range BEFORE we modify the warning range
            var fullyInside = selectionStart > warning.i && selectionEnd < warning.i + warning.s;
            // if part (or all) of the text left of the warning range was selected right before insert,
            // then the part that was selected has been deleted and the warning index needs to be updated
            if (selectionStart < warning.i) {
                var x1 = selectionStart;
                var x2 = Math.min(selectionEnd, warning.i);
                var size = x2 - x1;
                warning.i -= size;
                debug('warning ' + i + ': text left of warning was selected, warning.i -= ' + size);
            }
            // if part (or all) of the warning range was selected right before insert, then the
            // part that was selected has been deleted and the warning range size needs to be updated
            if (overlapStart < overlapEnd) {
                // there was overlap between the warning range and the selection range
                var size = overlapEnd - overlapStart;
                warning.s -= size;
                debug('warning ' + i + ': text inside warning was selected, warning.s -= ' + size);
            }
            // after any selected text was deleted, but before any new text was inserted, if the position of the cursor
            // was left of the start of the warning range, then any inserted text moved the warning range to the right
            if (insertedText && selectionStart <= warning.i) {
                var length = lengthWithNormalizedNewLines(insertedText);
                warning.i += length;
                debug('warning ' + i + ': text added left of warning, warning.i += ' + length);
            }
            // if inserted text was surrounded by warning text, then the inserted text becomes part of the warning
            if (insertedText && fullyInside) {
                var length = lengthWithNormalizedNewLines(insertedText);
                warning.s += length;
                debug('warning ' + i + ': text added inside warning, warning.s += ' + length);
            }
        }
    }

    function lengthWithNormalizedNewLines(s) {
        // Chrome has a bug (probably just on Windows) where input / beforeinput event data represents newlines as
        // "\r\n", but when it actually inserts the data it normalizes the newlines to "\n"... as a result, we need
        // to count newlines as a single character, even if the event data tries to represent them as two characters
        var rns = (s.match(/\r\n/g) || []).length;
        return s.length - rns;
    }

    function createElement(tag, text, attributes) {
        var e = document.createElement(tag);
        if (text) {
            e.appendChild(document.createTextNode(text));
        }
        if (attributes) {
            for (var name in attributes) {
                e.setAttribute(name, attributes[name]);
            }
        }
        return e;
    }

    /********************************************** EVENT BINDINGS **********************************************/

    // http://stackoverflow.com/questions/572768/styling-an-input-type-file-button

    //bind('#zpl',            'change keyup paste',   [refreshPermalink]);
    bind('#zpl',            'beforeinput',          [trackSelectionBeforeInput]);
    bind('#zpl',            'input',                [adjustWarningsOnInput]);
    bind('#zpl',            'keyup mouseup select', [adjustHelp]);
    bind('#label',          'load',                 [refreshLabelRotation]);
    bind('#addImage',       'click',                [function() { $id('imageFile').click() }]);
    bind('#openFile',       'click',                [function() { $id('zplFile').click() }]);
    bind('#imageFile',      'change',               [uploadImage],                           'Add Image');
    bind('#zplFile',        'change',               [loadZpl],                               'Open File');
    bind('#rotate',         'click',                [rotateLabel],                           'Rotate');
    bind('#redraw',         'click',                [store, refreshLabel],                   'Redraw');
    bind('#density',        'change',               [store, refreshLabel], 'Change Density');
    bind('#quality',        'change',               [store, refreshLabel], 'Change Quality');
    bind('#width',          'change',               [store, refreshLabel], 'Change Width');
    bind('#height',         'change',               [store, refreshLabel], 'Change Height');
    bind('#units',          'change',               [store, refreshLabel], 'Change Units');
    bind('#index',          'change',               [store, refreshLabel], 'Change Index');
    bind('#downloadZpl',    'click',                [downloadZpl],                           'Download ZPL');
    bind('#downloadPng',    'click',                [downloadPng],                           'Download PNG');
    bind('#downloadPdf',    'click',                [downloadPdf],                           'Download PDF');
    bind('#downloadPdfAll', 'click',                [downloadPdfAll],                        'Download PDF All');
    bind('#rotation',       'change',               [store]);
    bind('#remember',       'change',               [toggleStorage]);
    bind('.hideShow',       'click',                [hideShow]);

    onDocumentReady(initPage);

    function bind(selector, eventTypes, handlers, action) {
        eventTypes = eventTypes.split(' ');
        var elements = document.querySelectorAll(selector);
        for (var i = 0; i < elements.length; i++) {
            var element = elements[i];
            for (var j = 0; j < eventTypes.length; j++) {
                var eventType = eventTypes[j];
                for (var k = 0; k < handlers.length; k++) {
                    element.addEventListener(eventType, handlers[k]);
                }
                if (action) {
                    element.addEventListener(eventType, function() { track(action) });
                }
            }
        }
    }

    function onDocumentReady(handler) {
        if (document.readyState != 'loading') {
            handler();
        } else {
            document.addEventListener('DOMContentLoaded', handler);
        }
    }

    /************************************************* ZPL HELP *************************************************/

    function adjustHelp() {
        var e = $id('zpl');
        var zpl = e.value;
        var sel = e.selectionStart;
        if (!sel) return;
        sel -= 1; // if cursor is right before a command, select the command behind the cursor instead of the command in front of it
        var start = Math.max(zpl.lastIndexOf('^', sel), zpl.lastIndexOf('~', sel)); // not ^CC/^CT aware
        if (start != -1 && start + 2 < zpl.length) {
            var end = (zpl[start+1] == 'a' || zpl[start+1] == 'A') && zpl[start+2] != '@' ? start+2 : start+3;
            var cmdName = zpl.substring(start, end).toUpperCase();
            if (cmdName == currentCmdName) return; // we're already showing help for this command
            if (!/^[~^][a-zA-Z@]{1,2}$/.test(cmdName)) return; // no chance this is a valid command name (not ^CC/^CT aware)
            currentCmdName = cmdName; // we've found a new command to try to show help for!
            var cmd = COMMANDS[cmdName];
            var help = $id('help');
            while(help.children.length > 1) help.lastChild.remove(); // keep first child (show/hide icon)
            if (cmd && cmd.desc && cmd.commonExample && cmd.fullExample && cmd.params) {
                var signature = cmdName + ' ' + cmd.params.map(p => p.name).join(', ');
                help.appendChild(createElement('div', signature, { class: 'signature' }));
                help.appendChild(createElement('div', cmd.desc, { class: 'description' }));
                if (cmd.params.length > 0) {
                    var list = createElement('ul');
                    for (var i = 0; i < cmd.params.length; i++) {
                        var param = cmd.params[i];
                        var li = createElement('li');
                        li.appendChild(createElement('span', param.name, { class: 'paramName' }));
                        li.appendChild(createElement('span', ': ' + param.desc));
                        list.appendChild(li);
                    }
                    help.appendChild(createElement('div', 'Parameters:'));
                    help.appendChild(list);
                }
                var example1 = help.appendChild(createElement('div'));
                example1.appendChild(createElement('span', 'Example (common usage): '));
                example1.appendChild(createElement('span', cmd.commonExample, { class: 'note' }));
                var example2 = help.appendChild(createElement('div'));
                example2.appendChild(createElement('span', 'Example (full usage): '));
                example2.appendChild(createElement('span', cmd.fullExample, { class: 'note' }));
            } else {
                help.appendChild(createElement('span', 'No information available for command ' + cmdName + '.', { class: 'note' }));
                track('Missing help ' + cmdName);
            }
        }
    }

    var currentCmdName = '';

    const COMMANDS = {
        '^A': {
            desc: 'Sets the font for the current field, using a font name.',
            fullExample: '^A0N,30,30',
            commonExample: '^A0,30',
            params: [
                { name: 'font', desc: 'The font name and font orientation to use. Font names are either a capital letter (A-Z) or a number (0-9). Valid orientation values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default orientation is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The font height to use, in dots. Any number between 1 and 32,000 may be used. The default value depends on the font selected.' },
                { name: 'width', desc: 'The font width to use, in dots. Any number between 1 and 32,000 may be used. The default value depends on the font selected.' }
            ]
        },
        '^A@': {
            desc: 'Sets the font for the current field, using a font file path.',
            fullExample: '^A@N,30,30,Z:0.FNT',
            commonExample: '^A@,30,,Z:0.FNT',
            params: [
                { name: 'orientation', desc: 'The font orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The font height to use, in dots. The default value depends on the font selected.' },
                { name: 'width', desc: 'The font width to use, in dots. The default value depends on the font selected.' },
                { name: 'path', desc: 'The path to the font file.' }
            ]
        },
        '^B0': {
            desc: 'Configures the current field as an Aztec bar code.',
            alt: '^BO',
            fullExample: '^B0N,3,N,210,N,3,777',
            commonExample: '^B0,3,,210',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'magnification', desc: 'The bar code magnification to use. Any number between 1 and 10 may be used. The default value depends on the print density being used.' },
                { name: 'eci', desc: 'Whether or not the bar code data uses ECI (extended channel interpretation) to switch character sets. Valid values are Y and N. The default value is N (no ECI).' },
                { name: 'size', desc: 'The Aztec bar code size to use. Valid values are 101-104 (compact Aztec code sizes), 201-232 (full-range Aztec code sizes), 300 (Aztec runes), and 1-99 (dynamic sizing for a specific minimum error correction percentage). By default, the bar code is sized dynamically to fit the encoded data.' },
                { name: 'readerInit', desc: 'Whether or not the bar code is a reader initialization bar code. Valid values are Y and N. The default value is N (no reader initialization).' },
                { name: 'symbols', desc: 'If using structured append to split data across multiple bar codes, this parameter indicates the total number of bar codes being used. Any number between 1 and 26 may be used. The default value is 1, indicating that structured append is not being used.' },
                { name: 'id', desc: 'If using structured append to split data across multiple bar codes, this parameter indicates the message ID, which is used to correlate linked bar codes and reduce the likelihood of scanning issues. By default, no message ID is used.' }
            ]
        },
        '^B1': {
            desc: 'Configures the current field as a Code 11 bar code.',
            fullExample: '^B1N,Y,50,Y,N',
            commonExample: '^B1,,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'checkDigit', desc: 'Whether or not to add an extra check digit to the bar code. Valid values are Y and N. The default value is N (no extra check digit).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' }
            ]
        },
        '^B2': {
            desc: 'Configures the current field as an Interleaved 2 of 5 bar code.',
            fullExample: '^B2N,50,Y,N,N',
            commonExample: '^B2,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' },
                { name: 'checkDigit', desc: 'Whether or not to add a check digit to the bar code. Valid values are Y and N. The default value is N (no check digit).' }
            ]
        },
        '^B3': {
            desc: 'Configures the current field as a Code 39 bar code.',
            fullExample: '^B3N,N,50,Y,N',
            commonExample: '^B3,,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'checkDigit', desc: 'Whether or not to add a check digit to the bar code. Valid values are Y and N. The default value is N (no check digit).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' }
            ]
        },
        '^B4': {
            desc: 'Configures the current field as a Code 49 bar code.',
            fullExample: '^B4N,50,N,A',
            commonExample: '^B4,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'heightMultiplier', desc: 'The bar code row height multiplier. This number is multiplied by the module width specified in the ^BY command to determine the row height. Any number between 1 and the label height may be used. The default value is 1.' },
                { name: 'line', desc: 'The location of the human-readable text. Valid values are N (no human-readable text), A (above the bar code), and B (below the bar code). The default value is N (no human-readable text).' },
                { name: 'mode', desc: 'The encoding mode to use to encode the data. Valid values are 0 (regular alphanumeric mode), 1 (append mode), 2 (numeric mode), 3 (group alphanumeric mode), 4 (alphanumeric shift 1), 5 (alphanumeric shift 2), and A (automatic optimization). The default value is A (automatic optimization).' }
            ]
        },
        '^B5': {
            desc: 'Configures the current field as a USPS PLANET bar code.',
            fullExample: '^B5N,50,Y,N',
            commonExample: '^B5,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 9,999 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is N (no human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' }
            ]
        },
        '^B7': {
            desc: 'Configures the current field as a PDF417 bar code.',
            fullExample: '^B7N,5,4,5,,N',
            commonExample: '^B7,5,,5',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'rowHeight', desc: 'The bar code row height, in dots. Any number between 1 and the label height may be used. The default value is the row height necessary for the total bar code height to match the bar code height configured via the ^BY command.' },
                { name: 'security', desc: 'The level of error correction to apply. Any number between 0 and 8 may be used. The higher the number, the larger the generated bar code and the more resilient it is to scan errors. The default value is 0 (scan errors are detected but not corrected).' },
                { name: 'columns', desc: 'The number of data columns to encode. Any number between 1 and 30 may be used. This parameter can be used to control the bar code width. The default value depends on the amount of data encoded.' },
                { name: 'rows', desc: 'The number of rows to encode. Any number between 3 and 90 may be used. This parameter can be used to control the bar code height. The default value depends on the amount of data encoded.' },
                { name: 'truncate', desc: 'Whether or not to generate a truncated PDF417 bar code, also known as compact PDF417. Truncated PDF417 bar codes are narrower because they do not include right row indicators, but should only be used when label damage is unlikely. Valid values are Y and N. The default value is N (do not truncate).' }
            ]
        },
        '^B8': {
            desc: 'Configures the current field as an EAN-8 bar code.',
            fullExample: '^B8N,50,Y,N',
            commonExample: '^B8,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' }
            ]
        },
        '^B9': {
            desc: 'Configures the current field as a UPC-E bar code.',
            fullExample: '^B9N,50,Y,N,Y',
            commonExample: '^B9,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' },
                { name: 'checkDigit', desc: 'Whether or not to show the check digit in the human-readable text. Valid values are Y and N. The default value is Y (show check digit).' }
            ]
        },
        '^BA': {
            desc: 'Configures the current field as a Code 93 bar code.',
            fullExample: '^BAN,50,Y,N,N',
            commonExample: '^BA,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' },
                { name: 'checkDigit', desc: 'Whether or not to show the check digits in the human-readable text. Valid values are Y and N. The default value is N (check digits are not shown).' }
            ]
        },
        '^BB': {
            desc: 'Configures the current field as a Codablock bar code.'
        },
        '^BC': {
            desc: 'Configures the current field as a Code 128 bar code.',
            fullExample: '^BCN,50,Y,N,N,A',
            commonExample: '^BC,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code), except for mode U where the default is Y (if printed, text is placed above the bar code).' },
                { name: 'checkDigit', desc: 'Whether or not to calculate a GS1 (UCC) Mod 10 check digit. Valid values are Y and N. The default value is N (GS1 check digit is not calculated).' },
                { name: 'mode', desc: 'The mode to use to encode the bar code data. Valid values are N (no mode, subsets are specified explicitly as part of the field data), U (UCC case mode, field data must contain 19 digits), A (automatic mode, the ZPL engine automatically determines the subsets that are used to encode the data), and D (UCC/EAN mode, field data must contain GS1 numbers). The default value is N (no mode, subsets are specified explicitly as part of the field data).' }
            ]
        },
        '^BD': {
            desc: 'Configures the current field as a UPS MaxiCode bar code.',
            fullExample: '^BD4,1,1',
            commonExample: '^BD4',
            params: [
                { name: 'mode', desc: 'The mode to use to encode the bar code data. Valid values are 2 (numeric postal code), 3 (alphanumeric postal code), 4 (standard), 5 (full EEC), and 6 (reader programming). The default value is 2 (numeric postal code).' },
                { name: 'position', desc: 'If using structured append to split data across multiple bar codes, this parameter indicates the position of this bar code in the overall bar code sequence. The default value is 1 (assumes that structured append is not being used).' },
                { name: 'total', desc: 'If using structured append to split data across multiple bar codes, this parameter indicates the total number of bar codes which are in the bar code sequence. The default value is 1 (assumes that structured append is not being used).' }
            ]
        },
        '^BE': {
            desc: 'Configures the current field as an EAN-13 bar code.',
            fullExample: '^BEN,50,Y,N',
            commonExample: '^BE,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' }
            ]
        },
        '^BF': {
            desc: 'Configures the current field as a MicroPDF417 bar code.',
            fullExample: '^BFN,5,7',
            commonExample: '^BF,5,7',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'rowHeight', desc: 'The bar code row height, in dots. Any number between 1 and 9,999 may be used. The default value is the row height necessary for the total bar code height to match the bar code height configured via the ^BY command.' },
                { name: 'mode', desc: 'The MicroPDF417 mode (or variant) to use. Any number between 0 and 33 may be used. Modes 0-5 use 1 data column, modes 6-12 use 2 data columns, modes 13-22 use 3 data columns, and modes 23-33 use 4 data columns. The default value is mode 0 (1 data column and 11 data rows).' }
            ]
        },
        '^BI': {
            desc: 'Configures the current field as an Industrial 2 of 5 bar code.',
            fullExample: '^BIN,50,Y,N',
            commonExample: '^BI,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' }
            ]
        },
        '~BI': {
            desc: 'Simulates colored label stock by drawing a color image on the label background. A ~BI command without any parameters clears any previously registered background images. This is a Labelary-specific ZPL extension.',
            fullExample: '~BI50,50,1,iVBORw0KGg...',
            commonExample: '~BI50,50,1,iVBORw0KGg...',
            labelaryExtension: true,
            params: [
                { name: 'x', desc: 'The image position x-coordinate, in dots. Any number between 0 and the label width may be used. The default value is 0.' },
                { name: 'y', desc: 'The image position y-coordinate, in dots. Any number between 0 and the label height may be used. The default value is 0.' },
                { name: 'magnification', desc: 'The magnification factor to apply when drawing the image. Any number between 0.1 and 10 may be used. The default value is 1.' },
                { name: 'image', desc: 'The image file to draw (usually a PNG file), encoded using the Base64 encoding scheme. There is no default value.' }
            ]
        },
        '^BJ': {
            desc: 'Configures the current field as a Standard 2 of 5 bar code.',
            fullExample: '^BJN,50,Y,N',
            commonExample: '^BJ,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' }
            ]
        },
        '^BK': {
            desc: 'Configures the current field as an ANSI Codabar bar code.',
            fullExample: '^BKN,N,50,Y,N,C,D',
            commonExample: '^BK,,50,,,C,D',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'checkDigit', desc: 'Whether or not to add a check digit. This parameter is only available for backward compatibility purposes and can only be set to N (no check digit).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' },
                { name: 'startChar', desc: 'The Codabar start character to use. Valid values are A, B, C and D. The default value is A.' },
                { name: 'stopChar', desc: 'The Codabar stop character to use. Valid values are A, B, C and D. The default value is A.' }
            ]
        },
        '^BL': {
            desc: 'Configures the current field as a LOGMARS bar code.',
            fullExample: '^BLN,50,N',
            commonExample: '^BL,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (text is placed below the bar code).' }
            ]
        },
        '^BM': {
            desc: 'Configures the current field as a MSI bar code.'
        },
        '^BO': {
            desc: 'Configures the current field as an Aztec bar code.',
            alt: '^B0',
            fullExample: '^BON,3,N,210,N,3,777',
            commonExample: '^BO,3,,210',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'magnification', desc: 'The bar code magnification to use. Any number between 1 and 10 may be used. The default value depends on the print density being used.' },
                { name: 'eci', desc: 'Whether or not the bar code data uses ECI (extended channel interpretation) to switch character sets. Valid values are Y and N. The default value is N (no ECI).' },
                { name: 'size', desc: 'The Aztec bar code size to use. Valid values are 101-104 (compact Aztec code sizes), 201-232 (full-range Aztec code sizes), 300 (Aztec runes), and 1-99 (dynamic sizing for a specific minimum error correction percentage). By default, the bar code is sized dynamically to fit the encoded data.' },
                { name: 'readerInit', desc: 'Whether or not the bar code is a reader initialization bar code. Valid values are Y and N. The default value is N (no reader initialization).' },
                { name: 'symbols', desc: 'If using structured append to split data across multiple bar codes, this parameter indicates the total number of bar codes being used. Any number between 1 and 26 may be used. The default value is 1, indicating that structured append is not being used.' },
                { name: 'id', desc: 'If using structured append to split data across multiple bar codes, this parameter indicates the message ID, which is used to correlate linked bar codes and reduce the likelihood of scanning issues. By default, no message ID is used.' }
            ]
        },
        '^BP': {
            desc: 'Configures the current field as a Plessey bar code.'
        },
        '^BQ': {
            desc: 'Configures the current field as a QR Code bar code. Note that the data string in the corresponding ^FD command is expected to start with a bar code configuration prefix. For example, "^FDQA,12345" sets the error correction level to Q (high reliability), selects input mode A (automated encode mode switching) and encodes the data "12345" in the bar code.',
            fullExample: '^BQN,2,5,Q,7',
            commonExample: '^BQ,,5',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. This parameter is only available for backward compatibility purposes and can only be set to N (no rotation).' },
                { name: 'model', desc: 'The QR Code model to use. Valid values are 1 (original) and 2 (enhanced). The default value is model 2 (enhanced). Always use model 2.' },
                { name: 'magnification', desc: 'The bar code magnification to use. Any number between 1 and 10 may be used. The default value depends on the print density being used.' },
                { name: 'errorCorrection', desc: 'The level of error correction to apply. Valid values are H (highest reliability), Q (high reliability), M (medium reliability), and L (lower reliability). The default value is Q (high reliability).' },
                { name: 'mask', desc: 'The mask pattern to apply to the bar code. Any number between 0 and 7 may be used. The default value is 7.' }
            ]
        },
        '^BR': {
            desc: 'Configures the current field as a GS1 DataBar (RSS) bar code.'
        },
        '~BR': {
            desc: 'Simulates colored label stock by drawing a colored rectangle on the label background. A ~BR command without any parameters clears any previously registered background rectangles. This is a Labelary-specific ZPL extension.',
            fullExample: '~BR50,50,300,200,252,252,121',
            commonExample: '~BR50,50,300,200,252,252,121',
            labelaryExtension: true,
            params: [
                { name: 'x', desc: 'The rectangle position x-coordinate, in dots. Any number between 0 and the label width may be used. The default value is 0.' },
                { name: 'y', desc: 'The rectangle position y-coordinate, in dots. Any number between 0 and the label height may be used. The default value is 0.' },
                { name: 'width', desc: 'The width of the rectangle, in dots. Any number between 1 and the label width may be used. The default value is 1.' },
                { name: 'height', desc: 'The height of the rectangle, in dots. Any number between 1 and the label height may be used. The default value is 1.' },
                { name: 'r', desc: 'The R (red) component of the rectangle color. Any number between 0 and 255 may be used. The default value is 0.' },
                { name: 'g', desc: 'The G (green) component of the rectangle color. Any number between 0 and 255 may be used. The default value is 0.' },
                { name: 'b', desc: 'The B (blue) component of the rectangle color. Any number between 0 and 255 may be used. The default value is 0.' }
            ]
        },
        '^BS': {
            desc: 'Configures the current field as a UPC/EAN extension.',
            fullExample: '^BSN,50,Y,Y',
            commonExample: '^BS,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is Y (if printed, text is placed above the bar code).' }
            ]
        },
        '^BT': {
            desc: 'Configures the current field as a TLC 39 bar code.'
        },
        '^BU': {
            desc: 'Configures the current field as a UPC-A bar code.',
            fullExample: '^BUN,50,Y,N,Y',
            commonExample: '^BU,50',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 9,999 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is Y (include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' },
                { name: 'checkDigit', desc: 'Whether or not to show the check digit in the human-readable text. Valid values are Y and N. The default value is Y (show check digit).' }
            ]
        },
        '^BX': {
            desc: 'Configures the current field as a Data Matrix bar code.',
            fullExample: '^BXN,5,200,22,22,,~,1',
            commonExample: '^BX,5,200',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code element height, in dots. The individual elements are square, so the element height and width will be the same. Any number between 1 and the label width may be used. The default value is the element height necessary for the total bar code height to match the bar code height configured via the ^BY command.' },
                { name: 'quality', desc: 'The level of error correction to apply. Valid values are 0 (ECC 0), 50 (ECC 50), 80 (ECC 80), 100 (ECC 100), 140 (ECC 140) and 200 (ECC 200). The default value is 0 (scan errors are detected but not corrected). Always use quality level 200 (ECC 200).' },
                { name: 'columns', desc: 'The number of columns to encode. For ECC 200 bar codes, even numbers between 1 and 144 may be used. This parameter can be used to control the bar code width. The default value depends on the amount of data encoded.' },
                { name: 'rows', desc: 'The number of rows to encode. For ECC 200 bar codes, even numbers between 1 and 144 may be used. This parameter can be used to control the bar code height. The default value depends on the amount of data encoded.' },
                { name: 'format', desc: 'The type of data that needs to be encoded. Valid values are 1, 2, 3, 4, 5 and 6. The default value is 6. This parameter is ignored for ECC 200 bar codes (the recommended quality level).' },
                { name: 'escape', desc: 'The escape character used to escape control sequences in the field data. The default value is "~" (tilde).' },
                { name: 'ratio', desc: 'The desired aspect ratio, if any. Valid values are 1 (square) and 2 (rectangular).' }
            ]
        },
        '^BY': {
            desc: 'Configures the global bar code defaults.',
            fullExample: '^BY2,3,10',
            commonExample: '^BY2,3,10',
            params: [
                { name: 'width', desc: 'The default bar code module width. Any number between 1 and 100 may be used. The default value is the previously configured value, or 2 if no value has been set.' },
                { name: 'widthRatio', desc: 'The default ratio between wide bars and narrow bars. Any decimal number between 2 and 3 may be used. The number must be a multiple of 0.1 (i.e. 2.0, 2.1, 2.2, 2.3, ... , 2.9, 3.0). Larger numbers generally result in fewer bar code scan failures. The default value is the previously configured value, or 3 if no value has been set.' },
                { name: 'height', desc: 'The default bar code height. Any positive number may be used. The default value is the previously configured value, or 10 if no value has been set.' }
            ]
        },
        '^BZ': {
            desc: 'Configures the current field as a USPS postal bar code.',
            fullExample: '^BZN,50,N,N,3',
            commonExample: '^BZ,50,,,3',
            params: [
                { name: 'orientation', desc: 'The bar code orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The bar code height, in dots. Any number between 1 and 32,000 may be used. The default value is the bar code height configured via the ^BY command, which itself defaults to 10.' },
                { name: 'line', desc: 'Whether or not to include human-readable text with the bar code. Valid values are Y and N. The default value is N (do not include human-readable text).' },
                { name: 'lineAbove', desc: 'Whether or not to place the human-readable text above the bar code. Valid values are Y and N. The default value is N (if printed, text is placed below the bar code).' },
                { name: 'type', desc: 'The type of postal bar code to generate. Valid values are 0 (POSTNET bar code), 1 (PLANET bar code) and 3 (USPS Intelligent Mail bar code).' }
            ]
        },
        '^CC': {
            desc: 'Changes the caret character, which is used to start most commands.',
            alt: '~CC',
            fullExample: '^CC+',
            commonExample: '^CC+',
            params: [
                { name: 'caret', desc: 'The new caret character. Any ASCII character may be used. The default value is \'^\'.' }
            ]
        },
        '~CC': {
            desc: 'Changes the caret character, which is used to start most commands.',
            alt: '^CC',
            fullExample: '~CC+',
            commonExample: '~CC+',
            params: [
                { name: 'caret', desc: 'The new caret character. Any ASCII character may be used. The default value is \'^\'.' }
            ]
        },
        '^CD': {
            desc: 'Changes the parameter delimiter character.',
            alt: '~CD',
            fullExample: '^CD;',
            commonExample: '^CD;',
            params: [
                { name: 'delim', desc: 'The new parameter delimiter. Any ASCII character may be used. The default value is \',\'.' }
            ]
        },
        '~CD': {
            desc: 'Changes the parameter delimiter character.',
            alt: '^CD',
            fullExample: '~CD;',
            commonExample: '~CD;',
            params: [
                { name: 'delim', desc: 'The new parameter delimiter. Any ASCII character may be used. The default value is \',\'.' }
            ]
        },
        '^CF': {
            desc: 'Sets the default font. The default font is used by all subsequent text fields which do not specify a font using the ^A or ^A@ commands.',
            fullExample: '^CF0,50,50',
            commonExample: '^CF0,50',
            params: [
                { name: 'fontName', desc: 'The name of the new default font. Font names are either a capital letter (A-Z) or a number (0-9). The default value is the previously configured value, or A if no value has been set.' },
                { name: 'height', desc: 'The height of the new default font, in dots. The default value depends on the font selected.' },
                { name: 'width', desc: 'The width of the new default font, in dots. The default value depends on the font selected.' }
            ]
        },
        '^CI': {
            desc: 'Sets the current character set.',
            fullExample: '^CI0,34,67,89,61,129,232',
            commonExample: '^CI28',
            params: [
                { name: 'charset', desc: 'The character set to use. Any number between 0 and 36 may be used. The default value is 0 (Code Page 850). It is recommended that you always use value 28 (UTF-8).' },
                { name: 'customMappings', desc: 'Optional custom character mappings, specified as pairs of integers. Any numbers between 0 and 255 may be used. This parameter only affects Code Page 850 variants (charset values 0 - 13).' }
            ]
        },
        '^CM': {
            desc: 'Changes the memory device letter designations.',
            fullExample: '^CMA,R,E,B,M',
            commonExample: '^CMB,E,A,R',
            params: [
                { name: 'bAlias', desc: 'The new alias for the B: device. Valid values are B, E, R and A. The default value is B.' },
                { name: 'eAlias', desc: 'The new alias for the E: device. Valid values are B, E, R and A. The default value is E.' },
                { name: 'rAlias', desc: 'The new alias for the R: device. Valid values are B, E, R and A. The default value is R.' },
                { name: 'aAlias', desc: 'The new alias for the A: device. Valid values are B, E, R and A. The default value is A.' },
                { name: 'multiple', desc: 'Flag which, when set to the value M, allows E to be mapped to more than one device.' }
            ]
        },
        '^CN': {
            desc: 'Cycles the media cutter when in kiosk mode.',
            fullExample: '^CN1',
            commonExample: '^CN1',
            params: [
                { name: 'override', desc: 'Whether to use the default kiosk cut amount (from the ^KV command), or perform a full cut. Valid values are 0 (use the default cut amount) and 1 (perform a full cut). There is no default value.' }
            ]
        },
        '^CO': {
            desc: 'Configures the glyph cache, which is used internally to improve print speed when the same scalable font glyphs are used repeatedly.',
            fullExample: '^COY,100,0',
            commonExample: '^COY,100',
            params: [
                { name: 'on', desc: 'Whether or not the glyph cache is enabled. Valid values are Y (cache on) and N (cache off). The default value is Y (cache on).' },
                { name: 'kilobytes', desc: 'The amount of extra memory to allocate to the glyph cache, in kilobytes. Any number between 1 and 9,999 may be used. The default value is 40 kilobytes.' },
                { name: 'type', desc: 'The type of glyph cache being configured. Valid values are 0 (external cache for regular fonts) and 1 (internal cache for Asian fonts).' }
            ]
        },
        '^CP': {
            desc: 'Removes a printed label from the presenter area.',
            fullExample: '^CP0',
            commonExample: '^CP0',
            params: [
                { name: 'mode', desc: 'The label removal mode. Valid values are 0 (eject label), 1 (retract label), and 2 (use the mode specified via the ^KV command). There is no default value.' }
            ]
        },
        '^CT': {
            desc: 'Changes the tilde character, which is used to start control commands.',
            alt: '~CT',
            fullExample: '^CT+',
            commonExample: '^CT+',
            params: [
                { name: 'tilde', desc: 'The new tilde character. Any ASCII character may be used. The default value is \'~\'.' }
            ]
        },
        '~CT': {
            desc: 'Changes the tilde character, which is used to start control commands.',
            alt: '^CT',
            fullExample: '~CT+',
            commonExample: '~CT+',
            params: [
                { name: 'tilde', desc: 'The new tilde character. Any ASCII character may be used. The default value is \'~\'.' }
            ]
        },
        '^CV': {
            desc: 'Configures bar code data validation. By default ZPL is very lenient about bar code data errors, but this command can be used to enable stricter checks.',
            fullExample: '^CVY',
            commonExample: '^CVY',
            params: [
                { name: 'validate', desc: 'Whether or not to more strictly validate bar code data. Valid values are Y and N. The default value is N (no strict validation).' }
            ]
        },
        '^CW': {
            desc: 'Configures a font name alias, so that a particular font file can later be used by font name via the ^A and ^CF commands.',
            fullExample: '^CWK,R:ARIAL.TTF',
            commonExample: '^CWK,R:ARIAL.TTF',
            params: [
                { name: 'fontName', desc: 'The font name to use for the specified font file. Font names are either a capital letter (A-Z) or a number (0-9). If no value is specified, the command is ignored.' },
                { name: 'path', desc: 'The font file path that the specified font name should alias to. Any valid font path may be used. If the specified font file does not exist, the command is ignored.' }
            ]
        },
        '~DB': {
            desc: 'Uploads an embedded bitmap font, storing it at the specified file path.'
        },
        '~DE': {
            desc: 'Uploads a custom encoding table, storing it at the specified file path.'
        },
        '^DF': {
            desc: 'Saves the remainder of the label definition to a file for later use via ^XF.',
            fullExample: '^DFR:LABEL.ZPL',
            commonExample: '^DFR:LABEL.ZPL',
            params: [
                { name: 'path', desc: 'The file path to save the label definition to. The ^XF command can later be used to load the label definition from this path. The default value is R:UNKNOWN.ZPL.' }
            ]
        },
        '~DG': {
            desc: 'Uploads an embedded image, storing it at the specified file path.',
            fullExample: '~DGR:IMAGE.GRF,999000,999,ABCDEF01234...',
            commonExample: '~DGR:IMAGE.GRF,999000,999,ABCDEF01234...',
            params: [
                { name: 'path', desc: 'The file path to save the image to. The ^XG command can later be used to load the image from this path. The default value is R:UNKNOWN.GRF.' },
                { name: 'totalBytes', desc: 'The total number of bytes in the image. Because each pixel in the image uses 1 bit, this value should be the total number of pixels in the image, divided by 8 (since there are 8 bits per byte). There is no default value.' },
                { name: 'rowBytes', desc: 'The number of bytes per pixel row in the image. Because each pixel in the image uses 1 bit, this value should be the pixel width of the image, divided by 8 (since there are 8 bits per byte). The default value is 1, which is almost always incorrect.' },
                { name: 'data', desc: 'The image data, in hexadecimal format. There is no default value.' }
            ]
        },
        '~DN': {
            desc: 'Aborts an image upload (~DG) command.'
        },
        '~DS': {
            desc: 'Uploads an embedded scalable font, storing it at the specified file path. Note that most modern fonts should be uploaded using the ~DU command, rather than the ~DS command.',
            fullExample: '~DSR:FONT.FNT,999000,ABCDEF01234...',
            commonExample: '~DSR:FONT.FNT,999000,ABCDEF01234...',
            params: [
                { name: 'path', desc: 'The file path to save the font to. The default value is R:UNKNOWN.FNT.' },
                { name: 'size', desc: 'The total number of bytes in the font file. There is no default value.' },
                { name: 'data', desc: 'The font data, in hexadecimal format. There is no default value.' }
            ]
        },
        '~DT': {
            desc: 'Uploads an embedded TrueType font, storing it at the specified file path. Note that most modern fonts should be uploaded using the ~DU command, rather than the ~DT command.',
            fullExample: '~DTR:FONT.FNT,999000,ABCDEF01234...',
            commonExample: '~DTR:FONT.FNT,999000,ABCDEF01234...',
            params: [
                { name: 'path', desc: 'The file path to save the font to. The default value is R:UNKNOWN.FNT.' },
                { name: 'size', desc: 'The total number of bytes in the font file. There is no default value.' },
                { name: 'data', desc: 'The font data, in hexadecimal format. There is no default value.' }
            ]
        },
        '~DU': {
            desc: 'Uploads an embedded TrueType font, storing it at the specified file path.',
            fullExample: '~DUR:FONT.TTF,999000,ABCDEF01234...',
            commonExample: '~DUR:FONT.TTF,999000,ABCDEF01234...',
            params: [
                { name: 'path', desc: 'The file path to save the font to. The default value is R:UNKNOWN.FNT.' },
                { name: 'size', desc: 'The total number of bytes in the font file. There is no default value.' },
                { name: 'data', desc: 'The font data, in hexadecimal format. There is no default value.' }
            ]
        },
        '~DY': {
            desc: 'Uploads an embedded file, storing it at the specified file path. This command is most commonly used to upload images and fonts, although other file types can be uploaded as well. Because of the variety of supported file types, this command is fairly complex. It is recommended that the ~DU command be used instead when uploading fonts, and that the ~DG command be used instead when uploading images.',
            fullExample: '~DYR:IMAGE,A,G,999000,999,ABCDEF01234...',
            commonExample: '~DYR:IMAGE,A,G,999000,999,ABCDEF01234...',
            params: [
                { name: 'path', desc: 'The file path to save the file to. The file extension should be omitted, since it is determined based on the third (extension) parameter. The default value is R:UNKNOWN.' },
                { name: 'format', desc: 'The format of the data contained in the sixth parameter. Valid values are A (hexadecimal format), B (raw binary format), C (AR compressed), and P (hexadecimal format PNG data). There is no default value.' },
                { name: 'extension', desc: 'The file type to expect and the file name extension to use. There are many possible values, but some of the most common are G (GRF file), T (TTF file), and P (PNG file). The default value is G (GRF file).' },
                { name: 'totalBytes', desc: 'The total number of bytes in the file. There is no default value.' },
                { name: 'rowBytes', desc: 'The number of bytes per pixel row in the image, if the file is an image. This parameter is ignored if the extension parameter is not G (the file is not a GRF file). There is no default value.' },
                { name: 'data', desc: 'The file data, in the format specified in the second parameter. There is no default value.' }
            ]
        },
        '^EG': {
            desc: 'Erases all GRF image files from all writeable devices. Note that if specific files need to be deleted, the ^ID command can be used instead.',
            alt: '~EG',
            fullExample: '^EG',
            commonExample: '^EG',
            params: []
        },
        '~EG': {
            desc: 'Erases all GRF image files from all writeable devices. Note that if specific files need to be deleted, the ^ID command can be used instead.',
            alt: '^EG',
            fullExample: '~EG',
            commonExample: '~EG',
            params: []
        },
        '^FB': {
            desc: 'Formats the current field as a text block which wraps text across multiple lines.',
            fullExample: '^FB500,5,0,L,0',
            commonExample: '^FB500,5',
            params: [
                { name: 'maxWidth', desc: 'The maximum text block width, in dots. Text longer than this width is wrapped to another line. Any number between 0 and 9,999 may be used. The default value is 0, which is rarely useful.' },
                { name: 'maxLines', desc: 'The maximum number of text lines to allow. If the text does not fit on the specified number of lines, any remaining text is drawn over the previous text on the last line. Any number between 1 and 9,999 may be used. The default value is 1.' },
                { name: 'lineSpacing', desc: 'Extra spacing to add between lines, in dots. Positive numbers increase the distance between lines, negative numbers decrease the distance between lines. Any number between -9,999 and 9,999 may be used. The default value is 0.' },
                { name: 'alignment', desc: 'The text alignment to apply to the text block. Valid values are L (left), R (right), C (center) and J (justified). The default value is L (left).' },
                { name: 'hangingIndent', desc: 'The hanging indent to apply to all lines except the first line, in dots. Any number between 0 and 9,999 may be used. The default value is 0.' }
            ]
        },
        '^FC': {
            desc: 'Configures the clock value escape character(s), allowing the current field to embed dynamic time values in the field data. Note that this command requires that the printer or print engine have access to real-time clock hardware, which not all physical printers include.',
            fullExample: '^FC%,&,+',
            commonExample: '^FC%',
            params: [
                { name: 'indicator1', desc: 'The escape character to use for primary clock time values. Any ASCII character may be used. The default value is \'%\'.' },
                { name: 'indicator2', desc: 'The escape character to use for secondary clock time values. Any ASCII character may be used, except for the values used for the other two parameters. There is no default value.' },
                { name: 'indicator3', desc: 'The escape character to use for tertiary clock time values. Any ASCII character may be used, except for the values used for the other two parameters. There is no default value.' }
            ]
        },
        '^FD': {
            desc: 'Sets the current field\'s data string. This data is usually printed to the label as text or encoded as a bar code, depending on the field type.',
            fullExample: '^FDhello world',
            commonExample: '^FDhello world',
            params: [
                { name: 'data', desc: 'The data string. There is no default value.' }
            ]
        },
        '^FH': {
            desc: 'Configures the hexadecimal escape character, allowing the current field to embed character codes in the field data. This can be particularly useful when field data values may contain characters which would otherwise be interpreted as ZPL command or parameter delimiters.',
            fullExample: '^FH_',
            commonExample: '^FH_',
            params: [
                { name: 'hexIndicator', desc: 'The hexadecimal escape character. Any ASCII character may be used, except the lowercase letters a-z and the ZPL command and parameter delimiters.' }
            ]
        },
        '^FL': {
            desc: 'Links (or unlinks) one font to another, allowing the font engine to fall back from one font to another when a font is missing the glyph needed to render a character.',
            fullExample: '^FLR:NOTOCJK.TTF,R:NOTO.TTF,1',
            commonExample: '^FLR:NOTOCJK.TTF,R:NOTO.TTF,1',
            params: [
                { name: 'extensionPath', desc: 'The extension font file path. The font engine will fall back to this font if it encounters a missing glyph in the base font. There is no default value.' },
                { name: 'basePath', desc: 'The base font file path. If the font engine encounters a missing glyph in this font, it will fall back to the extension font. There is no default value.' },
                { name: 'link', desc: 'Whether to link or unlink the fonts. Valid values are 0 (unlink) and 1 (link). The default value is 0 (unlink).' }
            ]
        },
        '^FM': {
            desc: 'When used in conjunction with ^B7 or ^BF, this command configures multiple bar code positions for the current field, allowing the field data to be split across multiple bar codes using structured append.',
            fullExample: '^FM50,50,250,50,450,50,...',
            commonExample: '^FM50,50,250,50,450,50,...',
            params: [
                { name: 'positions', desc: 'Bar code positions, in dots, specified as pairs of integers (x and y values). Any numbers between 0 and 32,000 may be used. There are no default values.' }
            ]
        },
        '^FN': {
            desc: 'Sets the current field\'s field number. When using the ^DF command to save label definitions for later use, the ^FN command is used to define the field numbers available for later reference. When using the ^XF command to load previously saved label definitions, the ^FN command is used to reference the field numbers previously defined.',
            fullExample: '^FN1',
            commonExample: '^FN1',
            params: [
                { name: 'fieldNumber', desc: 'The current field\'s field number. Any number between 0 and 9,999 may be used. The default value is 0.' }
            ]
        },
        '^FO': {
            desc: 'Sets the position of the top left corner of the current field.',
            fullExample: '^FO50,50,0',
            commonExample: '^FO50,50',
            params: [
                { name: 'x', desc: 'The field position x-coordinate, in dots. Any number between 0 and 32,000 may be used. The default value is 0.' },
                { name: 'y', desc: 'The field position y-coordinate, in dots. Any number between 0 and 32,000 may be used. The default value is 0.' },
                { name: 'alignment', desc: 'The origin alignment to use. Valid values are 0 (left alignment), 1 (right alignment), and 2 (automatic alignment based on the direction of the field data text). The default value is the value previously configured via the ^FW command, if any.' }
            ]
        },
        '^FP': {
            desc: 'Configures the current field\'s print direction.',
            fullExample: '^FPV,10',
            commonExample: '^FPV',
            params: [
                { name: 'direction', desc: 'The print direction. Valid values are H (horizontal / left to right), V (vertical / top to bottom), and R (reverse / right to left). The default value is H (horizontal / left to right).' },
                { name: 'characterSpacing', desc: 'Extra spacing to add between characters, in dots. Positive numbers increase the distance between characters, negative numbers decrease the distance between characters. Any number between -10 and 9,999 may be used. The default value is 0.' }
            ]
        },
        '^FR': {
            desc: 'Configures the current field to reverse the background color, drawing black over white and white over black. This is also known as XOR mode drawing.',
            fullExample: '^FR',
            commonExample: '^FR',
            params: []
        },
        '^FS': {
            desc: 'Ends the current field and starts the next field.',
            alt: '0x0F',
            fullExample: '^FS',
            commonExample: '^FS',
            params: []
        },
        '^FT': {
            desc: 'Sets the position of the bottom left corner of the current field.',
            fullExample: '^FT50,50,0',
            commonExample: '^FT50,50',
            params: [
                { name: 'x', desc: 'The field position x-coordinate, in dots. Any number between 0 and 32,000 may be used. The default value is the right edge of the previous field.' },
                { name: 'y', desc: 'The field position y-coordinate, in dots. Any number between 0 and 32,000 may be used. The default value is the bottom edge of the previous field.' },
                { name: 'alignment', desc: 'The origin alignment to use. Valid values are 0 (left alignment), 1 (right alignment), and 2 (automatic alignment based on the direction of the field data text). The default value is the value previously configured via the ^FW command, if any.' }
            ]
        },
        '^FV': {
            desc: 'Sets the current field\'s data string. This command is an alternative to the ^FD command, and allows the use of variable data when the ^MC command is used to retain rendered content across multiple labels.',
            fullExample: '^FVhello world',
            commonExample: '^FVhello world',
            params: [
                { name: 'data', desc: 'The data string. There is no default value.' }
            ]
        },
        '^FW': {
            desc: 'Sets the default field orientation and alignment.',
            fullExample: '^FWR,0',
            commonExample: '^FWR',
            params: [
                { name: 'orientation', desc: 'The default field orientation. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is N (no rotation).' },
                { name: 'alignment', desc: 'The default field origin alignment. Valid values are 0 (left alignment), 1 (right alignment), and 2 (automatic alignment based on the direction of the field data text). The default value is 0 (left alignment).' }
            ]
        },
        '^FX': {
            desc: 'Adds a comment to the ZPL code. Comments do not affect the rendering of the label, and can be used to document assumptions, concepts or questions.',
            fullExample: '^FXthis is my comment',
            commonExample: '^FXthis is my comment',
            params: [
                { name: 'comment', desc: 'The informational comment.' }
            ]
        },
        '^GB': {
            desc: 'Configures the current field as a graphical box.',
            fullExample: '^GB100,50,3,B,0',
            commonExample: '^GB100,50,3',
            params: [
                { name: 'width', desc: 'The width of the box, in dots. Any number between the box line thickness and 32,000 may be used. The default value is the box line thickness.' },
                { name: 'height', desc: 'The height of the box, in dots. Any number between the box line thickness and 32,000 may be used. The default value is the box line thickness.' },
                { name: 'thickness', desc: 'The line thickness to use to draw the box, in dots. Any number between 1 and 32,000 may be used. The default value is 1.' },
                { name: 'color', desc: 'The line color to use to draw the box. Valid values are B (black) and W (white). The default value is B (black).' },
                { name: 'rounding', desc: 'The amount of rounding to apply to the box corners. Any number between 0 (no rounding) and 8 (maximum rounding) may be used. The default value is 0 (no rounding).' }
            ]
        },
        '^GC': {
            desc: 'Configures the current field as a graphical circle.',
            fullExample: '^GC100,3,B',
            commonExample: '^GC100,3',
            params: [
                { name: 'diameter', desc: 'The diameter of the circle, in dots. Any number between 3 and 4,095 may be used. The default value is 3.' },
                { name: 'thickness', desc: 'The line thickness to use to draw the circle, in dots. Any number between 1 and 4,095 may be used. The default value is 1.' },
                { name: 'color', desc: 'The line color to use to draw the circle. Valid values are B (black) and W (white). The default value is B (black).' }
            ]
        },
        '^GD': {
            desc: 'Configures the current field as a graphical diagonal line.',
            fullExample: '^GD100,50,3,B,R',
            commonExample: '^GD100,50,3',
            params: [
                { name: 'width', desc: 'The width of the rectangle which defines the diagonal line, in dots. Any number between 3 and 32,000 may be used. The default value is the line thickness.' },
                { name: 'height', desc: 'The height of the rectangle which defines the diagonal line, in dots. Any number between 3 and 32,000 may be used. The default value is the line thickness.' },
                { name: 'thickness', desc: 'The line thickness to use to draw the diagonal line, in dots. Any number between 1 and 32,000 may be used. The default value is 1.' },
                { name: 'color', desc: 'The line color to use to draw the diagonal line. Valid values are B (black) and W (white). The default value is B (black).' },
                { name: 'orientation', desc: 'The direction of the diagonal line. Valid values are R (bottom to top) and L (top to bottom). The default value is R (bottom to top).' }
            ]
        },
        '^GE': {
            desc: 'Configures the current field as a graphical ellipse.',
            fullExample: '^GE100,50,3,B',
            commonExample: '^GE100,50,3',
            params: [
                { name: 'width', desc: 'The width of the ellipse, in dots. Any number between 3 and 4,095 may be used. The default value is 3.' },
                { name: 'height', desc: 'The height of the ellipse, in dots. Any number between 3 and 4,095 may be used. The default value is 3.' },
                { name: 'thickness', desc: 'The line thickness to use to draw the ellipse, in dots. Any number between 1 and 4,095 may be used. The default value is 1.' },
                { name: 'color', desc: 'The line color to use to draw the ellipse. Valid values are B (black) and W (white). The default value is B (black).' }
            ]
        },
        '^GF': {
            desc: 'Configures the current field as an embedded image field.',
            fullExample: '^GFA,999000,999000,999,ABCDEF01234...',
            commonExample: '^GFA,999000,999000,999,ABCDEF01234...',
            params: [
                { name: 'format', desc: 'The format of the image data contained in the fifth parameter. Valid values are A (hexadecimal format), B (raw binary format), and C (AR compressed). There is no default value.' },
                { name: 'dataBytes', desc: 'The total number of data bytes in the fifth parameter. The value of this parameter is always the same as totalBytes, except in the case of format C (AR compressed) which is very rarely used.' },
                { name: 'totalBytes', desc: 'The total number of bytes in the image. Because each pixel in the image uses 1 bit, this value should be the total number of pixels in the image, divided by 8 (since there are 8 bits per byte). There is no default value.' },
                { name: 'rowBytes', desc: 'The number of bytes per pixel row in the image. Because each pixel in the image uses 1 bit, this value should be the pixel width of the image, divided by 8 (since there are 8 bits per byte). There is no default value.' },
                { name: 'data', desc: 'The image data, in the format specified in the first parameter. There is no default value.' }
            ]
        },
        '^GS': {
            desc: 'Configures the current field as a graphical symbol, mapping ASCII values in the field data to non-ASCII symbols (A = ®, B = ©, C = ™, D = Underwriter Labs approval symbol, E = Canadian Standards Association approval symbol).',
            fullExample: '^GSN,50,50',
            commonExample: '^GS,50',
            params: [
                { name: 'orientation', desc: 'The symbol orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^FW command, which itself defaults to N (no rotation).' },
                { name: 'height', desc: 'The symbol height to use, in dots. Any number between 1 and 32,000 may be used. The default value is the value previously configured via the ^CF command, if any.' },
                { name: 'width', desc: 'The symbol width to use, in dots. Any number between 1 and 32,000 may be used. The default value is the value previously configured via the ^CF command, if any.' }
            ]
        },
        '~HB': {
            desc: 'Requests a battery status report via the terminal.',
            fullExample: '~HB',
            commonExample: '~HB',
            params: []
        },
        '~HD': {
            desc: 'Requests a head diagnostic report via the terminal.',
            fullExample: '~HD',
            commonExample: '~HD',
            params: []
        },
        '^HF': {
            desc: 'Requests a label definition file via the terminal.',
            fullExample: '^HFR:LABEL.ZPL',
            commonExample: '^HFR:LABEL.ZPL',
            params: [
                { name: 'path', desc: 'The file path to read the label definition from. The default value is R:UNKNOWN.ZPL.' }
            ]
        },
        '^HG': {
            desc: 'Requests an image graphics file via the terminal.',
            fullExample: '^HGR:IMAGE.GRF',
            commonExample: '^HGR:IMAGE.GRF',
            params: [
                { name: 'path', desc: 'The file path to read the image graphics from. The default value is R:UNKNOWN.GRF.' }
            ]
        },
        '^HH': {
            desc: 'Requests a full configuration report via the terminal.',
            fullExample: '^HH',
            commonExample: '^HH',
            params: []
        },
        '~HI': {
            desc: 'Requests a basic configuration report via the terminal.',
            fullExample: '~HI',
            commonExample: '~HI',
            params: []
        },
        '^HL': {
            desc: 'Requests the RFID data log via the terminal.',
            alt: '~HL',
            fullExample: '^HL',
            commonExample: '^HL',
            params: []
        },
        '~HL': {
            desc: 'Requests the RFID data log via the terminal.',
            alt: '^HL',
            fullExample: '~HL',
            commonExample: '~HL',
            params: []
        },
        '~HM': {
            desc: 'Requests a RAM status report via the terminal.',
            fullExample: '~HM',
            commonExample: '~HM',
            params: []
        },
        '~HQ': {
            desc: 'Requests information via the terminal.',
            fullExample: '~HQES',
            commonExample: '~HQES',
            params: [
                { name: 'query', desc: 'The type of information being requested. Valid values are ES (error status), HA (hardware address), JT (printhead test results), MA (maintenance alert settings), MI (maintenance information), OD (odometer), PH (printhead life history), PP (plug and play string), SN (serial number), and UI (USB information). There is no default value.' }
            ]
        },
        '^HR': {
            desc: 'Calibrates the RFID tag position and sends the results via the terminal.',
            fullExample: '^HRstart,end,F0,F200,A',
            commonExample: '^HR',
            params: [
                { name: 'prefix', desc: 'Prefix text to send via the terminal before sending the calibration results. The default value is \'start\'.' },
                { name: 'suffix', desc: 'Suffix text to send via the terminal after sending the calibration results. The default value is \'end\'.' },
                { name: 'start', desc: 'The initial calibration position, in millimeters, relative to the leading print edge. Valid values are F0 - F999 and B0 - B30, where the letters represent the direction (F = forwards, B = backwards). The default value is hardware-dependent.' },
                { name: 'end', desc: 'The final calibration position, in millimeters, relative to the leading print edge. Valid values are F0 - F999 and B0 - B30, where the letters represent the direction (F = forwards, B = backwards). The default value is hardware-dependent.' },
                { name: 'antennaSelection', desc: 'Whether the RFID antenna selection should be manual or automatic. Valid values are M (manual) and A (automatic). The default value is A (automatic).' }
            ]
        },
        '~HS': {
            desc: 'Requests a hardware status report via the terminal.',
            fullExample: '~HS',
            commonExample: '~HS',
            params: []
        },
        '^HT': {
            desc: 'Requests a list of font links via the terminal.',
            fullExample: '^HT',
            commonExample: '^HT',
            params: []
        },
        '~HU': {
            desc: 'Requests an alert configuration report via the terminal.',
            fullExample: '~HU',
            commonExample: '~HU',
            params: []
        },
        '^HV': {
            desc: 'Requests the field data associated with a specific field via the terminal.',
            fullExample: '^HV1,8,<,>,F',
            commonExample: '^HV1,8',
            params: [
                { name: 'fieldNumber', desc: 'The number identifying the field whose field data is being requested. Any number between 0 and 9,999 may be used. The default value is 0.' },
                { name: 'bytes', desc: 'The number of bytes of data being requested. Any number between 1 and 256 may be specified. The default value is 64.' },
                { name: 'prefix', desc: 'Custom prefix to send via the terminal before sending the field data. There is no default value.' },
                { name: 'suffix', desc: 'Custom suffix to send via the terminal after sending the field data. There is no default value.' },
                { name: 'mode', desc: 'Whether to send field data for each label definition processed, or for for each label generated. Valid values are F (per label definition) and L (per label generated). The default value is F (per label definition).' }
            ]
        },
        '^HW': {
            desc: 'Requests a file listing via the terminal.',
            fullExample: '^HWR:*.*,D',
            commonExample: '^HWR:*.*',
            params: [
                { name: 'path', desc: 'The file path pattern used to search for files to include in the file listing. Wildcards (*, ?) are accepted. The default value is R:*.*.' },
                { name: 'format', desc: 'The format to use to send the file listing. Valid values are D (default) and C (columns). The default value is D (default).' }
            ]
        },
        '^HY': {
            desc: 'Requests an image graphics file via the terminal.',
            fullExample: '^HYR:IMAGE.GRF',
            commonExample: '^HYR:IMAGE.GRF',
            params: [
                { name: 'path', desc: 'The file path to read the image graphics from. The default value is R:UNKNOWN.GRF.' }
            ]
        },
        '^HZ': {
            desc: 'Requests printer information in XML format via the terminal.',
            fullExample: '^HZO,R:IMAGE.GRF,Y',
            commonExample: '^HZA',
            params: [
                { name: 'selector', desc: 'The type of information to be sent. Valid values are A (all information), F (format configuration), L (directory listing), O (individual object information) and R (printer status). There is no default value.' },
                { name: 'path', desc: 'The file path of the individual object whose information is being requested. The default value is R:UNKNOWN.GRF. Used only when requesting individual object information (selector = O).' },
                { name: 'longFilenames', desc: 'Whether or not to use long (16 character) filenames. Used only when requesting individual object information (selector = O).' }
            ]
        },
        '^ID': {
            desc: 'Deletes a file or set of files from printer memory.',
            fullExample: '^IDR:IMAGE.GRF',
            commonExample: '^IDR:IMAGE.GRF',
            params: [
                { name: 'path', desc: 'The file path pattern used to search for files to delete. Wildcards (*, ?) are accepted. The default value is R:UNKNOWN.GRF.' }
            ]
        },
        '^IL': {
            desc: 'Configures the current field as a label image field. The label image file is usually previously saved to memory using the ^IS command.',
            fullExample: '^ILR:LABEL.GRF',
            commonExample: '^ILR:LABEL.GRF',
            params: [
                { name: 'path', desc: 'The path to the label image file. The default value is R:UNKNOWN.GRF.' }
            ]
        },
        '^IM': {
            desc: 'Configures the current field as an image graphics field.',
            fullExample: '^IMR:IMAGE.GRF',
            commonExample: '^IMR:IMAGE.GRF',
            params: [
                { name: 'path', desc: 'The path to the image graphics file. The default value is R:UNKNOWN.GRF.' }
            ]
        },
        '^IS': {
            desc: 'Saves the current label definition to an image file for later use via ^IL.',
            fullExample: '^ISR:LABEL.GRF,N',
            commonExample: '^ISR:LABEL.GRF',
            params: [
                { name: 'path', desc: 'The file path to save the image to. The default value is R:UNKNOWN.GRF.' },
                { name: 'print', desc: 'Whether or not to continue printing the current label definition after saving it as an image file. Valid values are Y (print after saving) and N (do not print after saving). The default value is Y (print after saving).' }
            ]
        },
        // skip rarely-used J* commands
        '^JM': {
            desc: 'Halves the print density, effectively doubling the physical size of any label content.',
            fullExample: '^JMB',
            commonExample: '^JMB',
            params: [
                { name: 'adjustment', desc: 'Whether to use normal print density or half print density. Valid values are A (normal print density) and B (half print density). The default value is the previously configured value, or A (normal print density) if no value was previously configured.' }
            ]
        },
        // skip rarely-used J* commands
        '^JZ': {
            desc: 'Configures whether or not to reprint labels which were interrupted by an error condition as soon as the error is corrected.',
            fullExample: '^JZY',
            commonExample: '^JZY',
            params: [
                { name: 'reprint', desc: 'Whether or not to reprint labels which were interrupted by an error condition. Valid values are Y (reprint) and N (do not reprint). There is no default value.' }
            ]
        },
        // skip rarely-used K* commands
        '^LF': {
            desc: 'Generates a configuration label containing a list of font links. If the current label already contains content, the configuration label will be generated after the current label.',
            fullExample: '^LF',
            commonExample: '^LF',
            params: []
        },
        '^LH': {
            desc: 'Sets the label home position, which serves as the origin (0, 0) of the label coordinate system.',
            fullExample: '^LH10,10',
            commonExample: '^LH10,10',
            params: [
                { name: 'x', desc: 'The x-coordinate of the new origin, in dots, relative to the original origin (top left corner of the label). Any number between 0 and 32,000 may be used. The default value is the previously configured value, or 0 if no value was previously configured.' },
                { name: 'y', desc: 'The y-coordinate of the new origin, in dots, relative to the original origin (top left corner of the label). Any number between 0 and 32,000 may be used. The default value is the previously configured value, or 0 if no value was previously configured.' }
            ]
        },
        '^LL': {
            desc: 'Sets the label length (only necessary when using continuous media).',
            fullExample: '^LL1218',
            commonExample: '^LL1218',
            params: [
                { name: 'length', desc: 'The label length, in dots. Any number between 1 and 32,000 may be used. The default value depends on previous configuration.' }
            ]
        },
        '^LR': {
            desc: 'Configures all subsequent fields in the current label to reverse the background color, drawing black over white and white over black. This is also known as XOR mode drawing. This command is the global equivalent of the field-specific ^FR command.',
            fullExample: '^LRY',
            commonExample: '^LRY',
            params: [
                { name: 'reverse', desc: 'Whether or not to enable reverse mode. Valid values are Y (enable reverse mode) and N (disable reverse mode). The default value is N (disable reverse mode).' }
            ]
        },
        '^LS': {
            desc: 'Shifts all label content to the left or the right.',
            fullExample: '^LS20',
            commonExample: '^LS20',
            params: [
                { name: 'distance', desc: 'The distance to shift label content to the left, in dots. Any number between -9,999 and 9,999 may be used. Positive values shift label content to the left, negative values shift label content to the right. The default value is the previously configured value, or 0 if no value was previously configured.' }
            ]
        },
        '^LT': {
            desc: 'Shifts all label content up or down.',
            fullExample: '^LT20',
            commonExample: '^LT20',
            params: [
                { name: 'distance', desc: 'The distance to shift label content down, in dots. Any number between -120 and 120 may be used. Positive values shift label content down, negative values shift label content up. The default value is the previously configured value, or 0 if no value was previously configured.' }
            ]
        },
        '^MA': {
            desc: 'Configures the printing of maintenance alert labels. The alert messages can be configured using the ^MI command.',
            fullExample: '^MAR,Y,100,500,I',
            commonExample: '^MAC,N',
            params: [
                { name: 'type', desc: 'The type of maintenance alert to configure. Valid values are R (print head replacement alerts) and C (print head cleaning alerts). There is no default value.' },
                { name: 'print', desc: 'Whether or not to print a maintenance alert label for the specified alert type. Valid values are Y (print alert labels) and N (do not print alert labels). The default value is N (do not print alert labels).' },
                { name: 'start', desc: 'The initial label distance after which the first maintenance alert label should be printed. For alert type R, any number between 0 and 150 may be used; units are kilometers. For alert type C, any number between 0 and 2,000 may be used; units are meters. The default value is 50 km for alert type R, and 0 m for alert type C.' },
                { name: 'repeat', desc: 'The label distance after which the maintenance alert label should reprint, in meters. Any number between 0 and 2,000 may be used. The default value is 0.' },
                { name: 'units', desc: 'The units to use when reporting maintenance information. Valid values are C (centimeters), I (inches) and M (meters). The default value is I (inches).' }
            ]
        },
        '^MC': {
            desc: 'Allows the previous label output to be kept as part of the next label.',
            fullExample: '^MCN',
            commonExample: '^MCN',
            params: [
                { name: 'clear', desc: 'Whether or not to clear the previous label contents before drawing the next label. Valid values are Y (clear the previous label contents) and N (do not clear the previous label contents). The default value is Y (clear the previous label contents).' }
            ]
        },
        '^MD': {
            desc: 'Modifies the print darkness level, relative to the base print darkness configured with the ~SD command.',
            fullExample: '^MD5',
            commonExample: '^MD5',
            params: [
                { name: 'darknessModifier', desc: 'The amount to increase or decrease the darkness level. Any number between -30 and 30 may be used. Negative numbers result in lighter print output, while positive numbers result in darker print output. There is no default value.' }
            ]
        },
        '^MF': {
            desc: 'Configures the power-up and head-close media feed actions.',
            fullExample: '^MFN,C',
            commonExample: '^MFN,C',
            params: [
                { name: 'powerupAction', desc: 'The media feed action to take on power-up. Valid values are F (feed media to first inter-label mark), C (recalibrate media sensor), L (reset the label length), N (do nothing), and S (perform a short calibration). The default value is C (recalibrate media sensor).' },
                { name: 'closingAction', desc: 'The media feed action to take on head-close. Valid values are F (feed media to first inter-label mark), C (recalibrate media sensor), L (reset the label length), N (do nothing), and S (perform a short calibration). The default value is C (recalibrate media sensor).' }
            ]
        },
        '^MI': {
            desc: 'Configures the messages used on the maintenance alert labels. The frequency of the maintenance alert labels can be configured using the ^MA command.',
            fullExample: '^MIR,CALL EXT 1234 FOR REPLACEMENT',
            commonExample: '^MIR,CALL EXT 1234 FOR REPLACEMENT',
            params: [
                { name: 'type', desc: 'The type of maintenance alert to configure. Valid values are R (print head replacement alerts) and C (print head cleaning alerts). The default value is R (print head replacement alerts).' },
                { name: 'message', desc: 'The message to print on the maintenance alert label. Maximum length is 63 characters. The default value is "PLEASE REPLACE PRINT HEAD" for alert type R, and "PLEASE CLEAN PRINT HEAD" for alert type C.' }
            ]
        },
        '^ML': {
            desc: 'Configures the maximum label length.',
            fullExample: '^ML1421',
            commonExample: '^ML1421',
            params: [
                { name: 'maxLength', desc: 'The maximum label length, in dots. There is no default value.' }
            ]
        },
        '^MM': {
            desc: 'Configures the post-print action.',
            fullExample: '^MMT,N',
            commonExample: '^MMT',
            params: [
                { name: 'mode', desc: 'The post-print action to be taken by the printer after each label is printed. Valid values are T (advance label to tear-off bar for manual tear-off), P (advance label and wait for manual peel-off), R (rewind label on an external rewind device), A (advance label for applicator device), C (advance label for automated cutting), D (wait for delayed cut command), F (encode RFID data without backfeed), and K (present label for removal from kiosk). The default value is T (advance label to tear-off bar for manual tear-off).' },
                { name: 'prepeel', desc: 'Whether or not to pre-peel each label before printing. Valid values are Y (pre-peel labels) and N (do not pre-peel labels). The default value is N (do not pre-peel labels).' }
            ]
        },
        '^MN': {
            desc: 'Configures the label media type.',
            fullExample: '^MNM,50',
            commonExample: '^MNW',
            params: [
                { name: 'media', desc: 'The label media type being used. Valid values are N (continuous media), V (variable-length continuous media), W or Y (non-continuous web-sensing media), M (non-continuous mark-sensing media), and A (auto-detect media type during calibration). There is no default value.' },
                { name: 'offset', desc: 'The offset of the media mark relative to the actual label break, in dots. Valid values are hardware-specific, but values between -75 and 283 are usually valid. The default value is 0.' }
            ]
        },
        '^MP': {
            desc: 'Enables and disables control panel functionality.',
            fullExample: '^MPD',
            commonExample: '^MPD',
            params: [
                { name: 'function', desc: 'The control panel function to be disabled. Valid values are D (disable darkness mode), P (disable position mode), C (disable calibration mode), W (disable pause), F (disable feed), X (disable cancel), M (disable menu adjustments), S (disable mode saves), and E (re-enable all modes). There is no default value.' }
            ]
        },
        '^MT': {
            desc: 'Configures the label media type.',
            fullExample: '^MTD',
            commonExample: '^MTD',
            params: [
                { name: 'mediaType', desc: 'The label media type being used. Valid values are D (direct thermal media) and T (thermal transfer media). There is no default value.' }
            ]
        },
        '^MU': {
            desc: 'Customizes the units of the label coordinate system, so that commands which normally accept coordinates and distances in dots may instead use physical units like millimeters and inches. This command also optionally allows DPI conversion of label definitions.',
            fullExample: '^MUD,200,600',
            commonExample: '^MUM',
            params: [
                { name: 'units', desc: 'The units to use to specify label coordinates and distances. Valid values are D (dots), I (inches), and M (millimeters). There is no default value.' },
                { name: 'baseDpi', desc: 'The label definition base DPI. This parameter is ignored if units are not D (dots). Valid values are 150, 200, 300 and 600. There is no default value.' },
                { name: 'desiredDpi', desc: 'The label definition desired DPI. This parameter is ignored if units are not D (dots). Valid values are 150, 200, 300 and 600. This value must be a whole integer multiple of the base DPI. There is no default value.' }
            ]
        },
        // skip rarely-used M* and N* commands
        '^PA': {
            desc: 'Configures advanced text layout and font engine settings.',
            fullExample: '^PA1,1,1,1',
            commonExample: '^PA,,1',
            params: [
                { name: 'defaultGlyph', desc: 'Whether to use a space character or the .notdef glyph when a font is unable to display a character. The .notdef glyph is usually a blank vertical rectangle. Valid values are 0 (use a space character) and 1 (use the .notdef glyph). The default value is the previously configured value, or 0 (use a space character) if no value was previously configured.' },
                { name: 'bidi', desc: 'Whether or not to enable bidirectional text layout, which is important for Arabic and Hebrew scripts. Valid values are 0 (disable bidi layout) and 1 (enable bidi layout). The default value is the previously configured value, or 0 (disable bidi layout) if no value was previously configured.' },
                { name: 'charShaping', desc: 'Whether or not to enable character shaping, which is important for Arabic and Indic scripts. Valid values are 0 (disable character shaping) and 1 (enable character shaping). The default value is the previously configured value, or 0 (disable character shaping) if no value was previously configured.' },
                { name: 'openTypeSupport', desc: 'Whether or not to enable support for advanced OpenType font features, like ligatures. Valid values are 0 (disable OpenType features) and 1 (enable OpenType features). The default value is the previously configured value, or 0 (disable OpenType features) if no value was previously configured.' }
            ]
        },
        '^PF': {
            desc: 'Slews the label the specified number of dot rows, skipping all printing across those rows.',
            fullExample: '^PF100',
            commonExample: '^PF100',
            params: [
                { name: 'rows', desc: 'The number of dot rows (starting at the bottom of the label) to slew. Any number between 0 and 32,000 may be used. There is no default value.' }
            ]
        },
        '^PH': {
            desc: 'Triggers the generation of one blank label.',
            alt: '~PH',
            fullExample: '^PH',
            commonExample: '^PH',
            params: []
        },
        '~PH': {
            desc: 'Triggers the generation of one blank label.',
            alt: '^PH',
            fullExample: '~PH',
            commonExample: '~PH',
            params: []
        },
        '~PL': {
            desc: 'Increases the length of the label section ejected by the printer during a present cycle.',
            fullExample: '~PL10',
            commonExample: '~PL10',
            params: [
                { name: 'length', desc: 'The length of the extra label section to eject, in millimeters. Any number between 0 and 255 may be used. The default value is 0.' }
            ]
        },
        '^PM': {
            desc: 'Configures mirror mode, which mirrors label content across a vertical axis.',
            fullExample: '^PMY',
            commonExample: '^PMY',
            params: [
                { name: 'mirror', desc: 'Whether or not to mirror label content. Valid values are Y (mirror label content) and N (do not mirror label content). There is no default value.' }
            ]
        },
        '~PM': {
            desc: 'Triggers decommission mode.',
            fullExample: '~PM1234567890,1',
            commonExample: '~PM1234567890',
            params: [
                { name: 'serialNumber', desc: 'The printer serial number. The correct serial number must be used. There is no default value.' },
                { name: 'wipeCount', desc: 'The number of times to wipe printer memory. Any number between 0 and 3 may be used. The default value is 0.' }
            ]
        },
        '^PN': {
            desc: 'Triggers a present cycle, ejecting a section of the current label.',
            fullExample: '^PN10',
            commonExample: '^PN10',
            params: [
                { name: 'length', desc: 'The length of the label section to eject, in millimeters. Any number between 0 and 255 may be used. There is no default value.' }
            ]
        },
        '^PO': {
            desc: 'Configures inverted mode, which mirrors label content across a horizontal axis.',
            fullExample: '^POI',
            commonExample: '^POI',
            params: [
                { name: 'orientation', desc: 'Whether or not to invert label content. Valid values are I (invert label content) and N (do not invert label content). The default value is N (do not invert label content).' }
            ]
        },
        '^PP': {
            desc: 'Pauses printing until a ~PS command is received.',
            alt: '~PP',
            fullExample: '^PP',
            commonExample: '^PP',
            params: []
        },
        '~PP': {
            desc: 'Pauses printing until a ~PS command is received.',
            alt: '^PP',
            fullExample: '~PP',
            commonExample: '~PP',
            params: []
        },
        '^PQ': {
            desc: 'Configures the number of labels generated by a single label definition.',
            fullExample: '^PQ10,0,0,Y,N',
            commonExample: '^PQ10',
            params: [
                { name: 'labels', desc: 'The number of labels generated by the current label definition. Any number between 1 and 99,999,999 may be used. The default value is 1.' },
                { name: 'labelsBetweenPauses', desc: 'The number of labels between print pauses. Any number between 0 and 99,999,999 may be used. The default value is 0 (no pauses).' },
                { name: 'replicates', desc: 'The number of label replicates to generate for each serial number. Any number between 0 and 99,999,999 may be used. The default value is 0 (no replicates).' },
                { name: 'noPause', desc: 'Whether or not to prevent printer pauses. Valid values are Y (prevent pauses) and N (do not prevent pauses). The default value is N (do not prevent pauses).' },
                { name: 'cutOnError', desc: 'Whether or not to cut the labels after an error label. Valid values are Y (cut after error label) and N (do not cut after error label). The default value is Y (cut after error label).' }
            ]
        },
        '^PR': {
            desc: 'Configures the media speed during printing, slew and backfeed.',
            fullExample: '^PR4,4,4',
            commonExample: '^PR4',
            params: [
                { name: 'printSpeed', desc: 'The print speed, in inches per second. Any number between 1 and 14 may be used. The default value is 2.' },
                { name: 'slewSpeed', desc: 'The slew speed, in inches per second. Any number between 1 and 14 may be used. The default value is 6.' },
                { name: 'backfeedSpeed', desc: 'The backfeed speed, in inches per second. Any number between 1 and 14 may be used. The default value is 2.' }
            ]
        },
        '~PR': {
            desc: 'Triggers a reprint of the last label printed.',
            fullExample: '~PR',
            commonExample: '~PR',
            params: []
        },
        '~PS': {
            desc: 'Resumes printing after a printer has been paused via the ~PP command.',
            fullExample: '~PS',
            commonExample: '~PS',
            params: []
        },
        '^PW': {
            desc: 'Sets the label print width.',
            fullExample: '^PW812',
            commonExample: '^PW812',
            params: [
                { name: 'width', desc: 'The label print width, in dots. Any number between 2 and the label width may be used. The default value is the previously configured value, or the label width if no value was previously configured.' }
            ]
        },
        // skip rarely-used R* commands
        '^SC': {
            desc: 'Configures the serial communication parameters.',
            fullExample: '^SC19200,8,N,1,X,A',
            commonExample: '^SC19200,8,N,1,X,A',
            params: [
                { name: 'baud', desc: 'The baud rate to use, in bits per second. Valid values are 110, 300, 600, 1200, 2400, 4800, 9600, 14400, 19200, 28800, 38400, 57600, and 115200. There is no default value.' },
                { name: 'wordBits', desc: 'The number of data bits in each character. Valid values are 7 and 8. There is no default value.' },
                { name: 'parity', desc: 'The type of parity bit to use for error detection. Valid values are N (none), O (odd), and E (even). There is no default value.' },
                { name: 'stopBits', desc: 'The number of stop bits to send after each character. Valid values are 1 and 2. There is no default value.' },
                { name: 'mode', desc: 'The flow control mode to use during handshaking. Valid values are X (XON/XOFF), D (DTR/DSR), R (RTS), and M (DTR/DSR XON/XOFF). There is no default value.' },
                { name: 'protocol', desc: 'The communication protocol to use. Valid values are A (ACK/NAK), N (none), and Z (reserved). There is no default value.' }
            ]
        },
        '~SD': {
            desc: 'Configures the print darkness level. The darkness level can also be modified using the ^MD command.',
            fullExample: '~SD15',
            commonExample: '~SD15',
            params: [
                { name: 'darkness', desc: 'The print darkness level. Any number between 0 and 30 may be used. Higher numbers result in darker print output. The default value is the previously configured value.' }
            ]
        },
        '^SE': {
            desc: 'Selects a custom encoding table, required by some fonts in order to correctly map characters to glyphs.',
            fullExample: '^SER:CUSTOM.DAT',
            commonExample: '^SER:CUSTOM.DAT',
            params: [
                { name: 'path', desc: 'The file path to load the encoding table from. The file must use the .DAT extension. There is no default value.' }
            ]
        },
        '^SF': {
            desc: 'Configures the current field data to increment automatically. The number of labels (and increments) is controlled by the ^PQ command.',
            fullExample: '^SFDDD,1',
            commonExample: '^SFDDD',
            params: [
                { name: 'mask', desc: 'The mask to apply to the field data when determining how to increment it. Must match the starting value specified in the ^FD command. Valid characters are D (representing decimal numbers), H (representing hexadecimal numbers), O (representing octal numbers), A (representing ASCII letters), N (representing alphanumeric ASCII characters), and % (representing ignored characters). There is no default value.' },
                { name: 'increment', desc: 'The increment to apply to the field data for each additional label. The default value is 1.' }
            ]
        },
        '^SI': {
            desc: 'Configures the media sensor intensity.',
            fullExample: '^SI2,100',
            commonExample: '^SI2,100',
            params: [
                { name: 'setting', desc: 'The specific setting to configure. Valid values are 1 (brightness) and 2 (baseline). There is no default value.' },
                { name: 'value', desc: 'The intensity value. Any number between 0 and 196 may be used. There is no default value.' }
            ]
        },
        '^SL': {
            desc: 'Configures the real-time clock mode and language.',
            fullExample: '^SLT,2',
            commonExample: '^SLT,2',
            params: [
                { name: 'mode', desc: 'The clock mode. Valid values are S (use time at start of ZPL parsing), T (use time at start of print queueing), or any number between 1 and 999 (use time at last clock tick, using the specified clock resolution, in seconds).' },
                { name: 'language', desc: 'The language to use to localize date/time field data. Valid values are 1 (English), 2 (Spanish), 3 (French), 4 (German), 5 (Italian), 6 (Norwegian), 7 (Portuguese), 8 (Swedish), 9 (Danish), 10 (Spanish), 11 (Dutch), 12 (Finnish), 13 (Japanese), 14 (Korean), 15 (Simplified Chinese), 16 (Traditional Chinese), 17 (Russian), 18 (Polish), 19 (Czech), and 20 (Romanian). The default value is the previously configured value, or the control panel language if no value was previously configured.' }
            ]
        },
        '^SN': {
            desc: 'Configures the current field data as a serially-incrementing number. The number of labels (and increments) is controlled by the ^PQ command.',
            fullExample: '^SN0001,1,Y',
            commonExample: '^SN0001,1,Y',
            params: [
                { name: 'start', desc: 'The starting number. The default value is 1.' },
                { name: 'increment', desc: 'The increment (or decrement) to apply to the field data for each additional label. Any number, positive or negative, containing up to 12 digits, may be used. The default value is 1.' },
                { name: 'pad', desc: 'Whether or not to pad the numbers with zeroes to match the original start number width. Valid values are Y (pad with zeroes) and N (do not pad with zeroes. The default value is N (do not pad with zeroes).' }
            ]
        },
        // skip rarely-used S* commands
        '~TA': {
            desc: 'Adjusts the label tear-off position.',
            fullExample: '~TA50',
            commonExample: '~TA50',
            params: [
                { name: 'adjustment', desc: 'The tear-off position adjustment, in dots. Any number between -120 and 120 may be used. The default value is the previously configured value, or 0 if no value was previously configured.' }
            ]
        },
        '^TB': {
            desc: 'Formats the current field as a text block which wraps text across multiple lines.',
            fullExample: '^TBN,500,300',
            commonExample: '^TB,500,300',
            params: [
                { name: 'orientation', desc: 'The text block orientation to use. Valid values are N (no rotation), R (rotate 90° clockwise), I (rotate 180° clockwise), and B (rotate 270° clockwise). The default value is the orientation configured via the ^A or ^FW command, which itself defaults to N (no rotation).' },
                { name: 'maxWidth', desc: 'The maximum text block width, in dots. Text longer than this width is wrapped to another line. Any number between 1 and the label width may be used. The default value is 1, which is rarely useful.' },
                { name: 'maxHeight', desc: 'The maximum text block height, in dots. Any text which exceeds this limit is truncated. Any number between 1 and the label height may be used. The default value is 1, which is rarely useful.' }
            ]
        },
        '^TO': {
            desc: 'Copies one or more files in printer memory from one location to another.',
            fullExample: '^TOR:ORIG.GRF,B:NEW.GRF',
            commonExample: '^TOR:ORIG.GRF,B:NEW.GRF',
            params: [
                { name: 'from', desc: 'The file path(s) to copy the file(s) from. Wildcards (*, ?) are accepted.' },
                { name: 'to', desc: 'The file path(s) to copy the file(s) to. Wildcards (*, ?) are accepted.' }
            ]
        },
        // skip rarely-used W* commands
        '^XA': {
            desc: 'Begins a label definition. All label definitions must start with this command.',
            alt: '0x02',
            fullExample: '^XA',
            commonExample: '^XA',
            params: []
        },
        '^XB': {
            desc: 'Disables forward feed / back feed after the current label.',
            fullExample: '^XB',
            commonExample: '^XB',
            params: []
        },
        '^XF': {
            desc: 'Loads a label definition previously saved via the ^DF command. The ^FN command can then be used to add variable data to previously-defined fields, based on their field numbers.',
            fullExample: '^XFR:LABEL.ZPL',
            commonExample: '^XFR:LABEL.ZPL',
            params: [
                { name: 'path', desc: 'The file path to load the label definition from. The default value is R:UNKNOWN.ZPL.' }
            ]
        },
        '^XG': {
            desc: 'Configures the current field as an image graphics field.',
            fullExample: '^XGR:IMAGE.GRF,2,2',
            commonExample: '^XGR:IMAGE.GRF',
            params: [
                { name: 'path', desc: 'The path to the image graphics file. The default value is R:UNKNOWN.GRF.' },
                { name: 'magnificationX', desc: 'The horizontal magnification to apply to the image. Any number between 1 and 10 may be used. The default value is 1.' },
                { name: 'magnificationY', desc: 'The vertical magnification to apply to the image. Any number between 1 and 10 may be used. The default value is 1.' }
            ]
        },
        '^XS': {
            desc: 'Enables and disables dynamic media calibration.',
            fullExample: '^XSY,Y,Y',
            commonExample: '^XS',
            params: [
                { name: 'length', desc: 'Whether or not to dynamically calibrate label length. Valid values are Y (enable dynamic label length calibration) and N (disable dynamic label length calibration). The default value is Y (enable dynamic label length calibration).' },
                { name: 'threshold', desc: 'Whether or not to dynamically calibrate label threshold. Valid values are Y (enable dynamic label threshold calibration) and N (disable dynamic label threshold calibration). The default value is Y (enable dynamic label threshold calibration).' },
                { name: 'gain', desc: 'Whether or not to dynamically calibrate label gain. Valid values are Y (enable dynamic label gain calibration) and N (disable dynamic label gain calibration). The default value is Y (enable dynamic label gain calibration).' }
            ]
        },
        '^XZ': {
            desc: 'Ends a label definition. All label definitions must end with this command.',
            alt: '0x03',
            fullExample: '^XZ',
            commonExample: '^XZ',
            params: []
        }
        // skip rarely-used Z* commands
    };
    <?}?>
</script>
<?}?>
