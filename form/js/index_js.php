<script type="text/javascript">
    let delayHover;

    if ((parseInt(gIdtipopessoa) != 1)) {
        montaMenuCarbon(false, true, false);
    } else {
        montaMenuCarbon();
    }

    CB.on('posLoadUrl', function() {

        if (CB.logado) {
            let idempresa = getUrlParameter("_idempresa") || "<?= $_SESSION["SESSAO"]["IDEMPRESA"] ?>";
            if ($("#cbMenuSuperior").attr('idempresa') != idempresa) {
                montaMenuCarbon();
            }

            if (CB.jsonModulo.jsonpreferencias.botaoflutuante == 'N') {
                $("#cbPanelBotaoFlutuante").css("height", "auto");
                $("#cbPanelBotaoFlutuante").css("overflow-y", "hidden");
            }
        }

    });
    CB.on('posFecharForm', function() {

        if (CB.logado) {
            let _idempresa = sessionStorage.getItem('_idempresa');
            if (_idempresa) {
                window.history.pushState(null, window.document.title, "?_modulo=" + CB.modulo + "&_idempresa=" + _idempresa);
            }
            let idempresa = getUrlParameter("_idempresa") || "<?= $_SESSION["SESSAO"]["IDEMPRESA"] ?>";
            if ($("#cbMenuSuperior").attr('idempresa') != idempresa) {
                montaMenuCarbon();
            }
        }

    });

    function multiEmpresaModal(empobj) { //@487013 - MULTI EMPRESA

        var div = '';
        empobj.forEach((e, o) => {
            div += '<div class="col-md-3" style="text-align:center;"><img src="' + e.iconemodal + '" onclick="CB.novo(' + e.idempresa + ')" style="height:96px;width:96px;"></div>'
        });
        $("#cbModalTitulo").html("EMPRESA DESTINO");
        $("#cbModalCorpo").html('<div class="col-md-12">\
                        ' + div + '\
                        </div>');
        $("#cbModal").modal("show");
    }

    function alteraAltura(mostrar) {
        if (mostrar == 'Y') {
            $("#cbPanelBotaoFlutuante").css("height", "80%");
            $("#cbPanelBotaoFlutuante").css("width", "33%");
            $("#cbPanelBotaoFlutuante").css("overflow-y", "scroll");
            $("#cbPanelBotaoFlutuanteFechar").show();
            $("#cbPanelBotaoFlutuanteAbrir").hide();
            $('#cbSalvarComentario').show();
        } else {
            $("#cbPanelBotaoFlutuante").css("height", "auto");
            $("#cbPanelBotaoFlutuante").css("width", "auto");
            $("#cbPanelBotaoFlutuante").css("overflow-y", "hidden");
            $("#cbPanelBotaoFlutuanteFechar").hide();
            $("#cbPanelBotaoFlutuanteAbrir").show();
            $('#cbSalvarComentario').hide();
        }
    }
    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>