
<script>
    function flgdiazero(vidservicobioterioconf, vdiazero) {
        CB.post({
            objetos: "_x_u_servicobioterioconf_idservicobioterioconf=" + vidservicobioterioconf + "&_x_u_servicobioterioconf_diazero=" + vdiazero,
            refresh: "refresh"
        });

    }

    function novoservico() {
        CB.post({
            objetos: "_x_i_servicobioterioconf_idobjeto=" + $("[name=_1_u_bioterioanalise_idbioterioanalise]").val() + "&_x_i_servicobioterioconf_tipoobjeto=bioterioanalise",
            refresh: "refresh"
        });
    }

    function novoteste(inidservicobioterioconf) {
        CB.post({
            objetos: "_x_i_bioterioanaliseteste_idservicobioterioconf=" + inidservicobioterioconf,
            refresh: "refresh"
        });
    }

    function excluirservico(inid) {
        if (confirm("Deseja retirar o serviço?")) {
            CB.post({
                objetos: "_x_d_servicobioterioconf_idservicobioterioconf=" + inid
            });
        }
    }

    function excluirteste(inid) {
        if (confirm("Deseja retirar o Teste?")) {
            CB.post({
                objetos: "_x_d_bioterioanaliseteste_idbioterioanaliseteste=" + inid
            });
        }
    }

    function altcheck(vtab, vcampo, vid, vcheck) {
        CB.post({
            objetos: "_x_u_" + vtab + "_id" + vtab + "=" + vid + "&_x_u_" + vtab + "_" + vcampo + "=" + vcheck
        });
    }
</script>