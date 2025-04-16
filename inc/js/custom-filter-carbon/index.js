class CustomFilterCarbon {
    
    validateParam (item, type) {
        if (item && typeof item == type ) {
            return true;
        } else {
            return false;
        }
    }

    validateOptionsObrigatorios ( opt ) {
        let validations = Array();

        validations.push(
            this.validateParam(opt.label, "string"),
            this.validateParam(opt.col, "string"),
            this.validateParam(opt.tab, "string"),
            this.validateParam(opt.url, "string"),
        );

        if (validations.includes(false)) {
            return false;
        }else{
            return true;
        }
    }

    searchOriginalFilter ( opt ) {
        let originalFilter = $(`div.btn-group[cbtab="${opt.tab}"][cbcol="${opt.col}"]`);

        if(originalFilter.length == 0){
            return false;
        }
        
        originalFilter.remove();
        
        return true;
        
    }

    loadingPreferences ( opt, filterObject ) {

        if (!this.validateOptionsObrigatorios(opt)){
            console.warn("CustomFilterCarbon [loadingPreferences]: Parâmetros Inválidos.");
            return false;
        }

        let preferences = this.pref.get(opt.reference);

        // Caso encontre preferência, a função deve-se comportar igual ao clique no filtro
        if (preferences) {

            this.filterClick({data:{options: opt, preferences:preferences, element: filterObject}})

        }

    }

    create ( opt ) {
        if(opt.preCreate && typeof opt.preCreate == "function"){
            let ret = opt.preCreate(opt) || false;
            if(!ret){
                return false;
            }
        }

        if (!this.validateOptionsObrigatorios(opt)){
            console.warn("CustomFilterCarbon [create]: Parâmetros Inválidos.");
            return false;
        }

        if(!this.searchOriginalFilter(opt)){
            console.warn(`CustomFilterCarbon [create]: Filtro original da coluna [${opt.col}] da tabela [${opt.col} não encontrado.]`);
            return false;
        }

        let tmpFiltro = this.objFiltroRapidoPicker
            .replace(/\$rotPesquisa/g, opt.label)
            .replace(/\$col/g, opt.col)
            .replace(/\$tab/g, opt.tab);

		let $oF = $(tmpFiltro);

        $oF
            .on("click", {options:opt, preferences: false}, this.filterClick)
            .on("change", opt, this.filterChange);
        
        this.loadingPreferences(opt, $oF);
        
        this.elements.set(opt.reference,{options: opt, element: $oF});

        if(opt.posCreate && typeof opt.posCreate == "function"){
            opt.posCreate(opt, $oF);
        }
        
        $oF.insertBefore($("#cbFiltroRapido .btn-group").first());

        return true;
    }

    filterClick ( e ) {

        var $this;
        var preferences = e.data.preferences;
        if(preferences){
            $this=e.data.element;
        }else{
            $this=$(e.currentTarget);
        }

        var sCol = $this.attr("cbcol") || "";
        var sRot = $this.attr("cbrot") || "";
        var opt = e.data.options;

        if(opt.preFilterClick && typeof opt.preFilterClick == "function"){
            let ret = opt.preFilterClick(opt, $this) || false;
            if(!ret){
                return false;
            }
        }

        if($this.attr("estado") == "inicial"){
            $this.attr("estado","carregando");

            var $oDropOpt = $this.find(".selectpicker");

            var optRequest = {
                url: opt.url,
                type: (opt.type || "GET").toUpperCase(),
            }

            if ( opt.body && typeof opt.body == "object") {
                optRequest["data"] = opt.body;
            }

            $.ajax(optRequest).done(function(data){

                //precarregafiltro

                if(data===""){
                    console.warn("Js: json2Filtros: string json vazia: "+(this.url||""));
                }else{
                    data=jsonStr2Object(data);

                    if(!(data instanceof Array)){
                        console.log("O arquivo deve retornar Array de Javascript válido. Ex: [{},{}...]");
                        alertErro("Consulte o Log de erros.","Filtro Rápido inválido:");
                        return false;
                    }
                    
                    $.each(data, function(index, value) {
                        var id = Object.keyAt(value, 0);
                        var valor = value[id];
                        $oDropOpt.append(`<option value="${id}" col="${sCol}" rot="${sRot}">${valor}</option>`);
                    });
                    
                    $('#picker_'+opt.col)
                        .selectpicker({
                            title: opt.label,
                            selectAllText: '<span class="glyphicon glyphicon-check"></span>',
                            deselectAllText: '<span class="glyphicon glyphicon-remove"></span>'
                        })
                        .selectpicker('render');

                    if(!preferences){
                        $('#picker_'+opt.col).selectpicker('toggle')
                    }

                    if (preferences) {
                        preferences = preferences.split(",");
                        let arrtmp = new Array();
                        for(let v of preferences){
                            for(let d of data){
                                if(d.hasOwnProperty(v)){
                                    arrtmp.push(d);
                                    break;
                                }
                            }
                        }
                        if(arrtmp.length > 0){
                            CB.setFiltroRapidoPicker({col:sCol,valor:arrtmp,pref:true})
                        }
                    }

                    $this.attr("estado","carregado");

                    if($this.attr("cbId")){
                        let values = $this.attr("cbId").split(",");
                        $('#picker_'+opt.col).selectpicker("val",values);
                    }
                }

                if(opt.posFilterClick && typeof opt.posFilterClick == "function"){
                    opt.posFilterClick(data, opt, $this);
                }

            });

        }
    }

    filterChange ( e ) {
        var column = $(this).attr("cbcol");
        var selectedItem = $('#picker_'+e.data.col).val();
        CB.setFiltroRapidoPicker({col:column,valor:selectedItem});
    }

    constructor(){
        this.elements = new Map();
        this.pref = new Map();
        this.objFiltroRapidoPicker = `
            <div class='btn-group picker' role='group' cbTab='$tab' cbCol='$col' cbRot='$rotPesquisa' estado='inicial'>
                <span type='button' class='btn btn-default'>
                    <span class='txt'>$rotPesquisa</span>
                    <span class='caret'></span>
                    <span class='aguarde blink'><i class='fa fa-hourglass'></i></span>
                    <span class='fa fa-close' title='Limpar filtro' onclick='CB.resetFiltro(\"$col\");fim(event);'></span>
                </span>

                <select class='selectpicker' id='picker_$col' multiple data-live-search='true' data-actions-box="true" data-selected-text-format="count > 1" data-count-selected-text= "{0} Selecionados">
                        
                </select>
            </div>`;
    }

}


var CFC = new CustomFilterCarbon();

CB.on('preJson2Filtros', function (data){
    if(CB.jsonModulo.jsonpreferencias._filtrosrapidos["idempresa"]){
        CFC.pref.set("filtroempresa", CB.jsonModulo.jsonpreferencias._filtrosrapidos["idempresa"]);

        CB.jsonModulo.jsonpreferencias._filtrosrapidos["idempresa"] = undefined;
    }
});

CB.on('preValidaStatusAtivoJsonFiltro', function( jqXHR, settings ){
    if(!settings.url.includes('__idfluxostatus__prompt.php')) return;

    let ocultar = CB.jsonModulo.jsonpreferencias._filtrosrapidos["ocultar"] || 'N';
    settings.url += '&ocultar='+ocultar;
});

CB.on('preGetJsonFiltroPicker', function( jqXHR, settings ){
    if(!settings.url.includes('__idfluxostatus__prompt.php')) return;

    let ocultar = CB.oFiltroRapido.children('[cbcol="ocultar"]').attr('cbId') || 'N';
    settings.url += '&ocultar='+ocultar;
});

CB.on('posJson2Filtros', function (data){
    
    CFC.create({
        label: "Empresa",
        reference: "filtroempresa",
        col: "idempresa",
        tab: data.tabpesquisa,
        url: "inc/js/custom-filter-carbon/custom_filter_carbon_empresa.php",
        type: "POST",
        body:{
            tab: data.tabpesquisa, modulo:data.modulo
        },
        posCreate: async function (opt, element) {
            /*$.ajax({
                url: opt.url,
                type:"POST",
                data:{
                    tab: opt.tab,
                    verificaFiltro: 'Y'
                }
            }).done(function(data){
                data=jsonStr2Object(data);
                if(!data.message){
                    element.remove();
                }else{
                    CFC.filterClick({data:{options: opt, preferences:"[]", element: element}})
                }
            });*/

            CFC.filterClick({data:{options: opt, preferences:"[]", element: element}})
        },
        posFilterClick: function (d, opt, element) {
            let auxList = [];
            for(let i of d){
                auxList[Object.keyAt(i)] = i.sigla;
            }
            CFC.elements.get(opt.reference)["idempresaList"] = auxList;

            CB.on('posPesquisar', function (oConf,dados) {
                let col = CFC.elements.get("filtroempresa")["options"]["col"];
                let label = CFC.elements.get("filtroempresa")["options"]["label"];
                let selector = $(`#cbModuloResultados #restbl:not(.modificado) thead td[col="${col}"]`);
                let order = selector.children();
                selector.text(label).append(order);
                
                let columnIndex = selector.index() + 1;
                
                $('#cbModuloResultados #restbl tbody tr td:nth-child(' + columnIndex + '):not(.modificado)').each((i, o) => {
                    o.textContent = CFC.elements.get("filtroempresa")["idempresaList"][o.textContent];
                    $(o).addClass("modificado");
                });

            })
        }
    });

    
    if(data.colunas.ocultar && ["jsonpicker","json"].includes(data.colunas.ocultar.prompt)){
        let preferencia = CB.jsonModulo.jsonpreferencias._filtrosrapidos["ocultar"] || "N";
        let filtroAtivo = (preferencia == 'N') ? "" : "filtroAtivo";
        let tituloOculto = (preferencia == 'N') ? "Não mostrar ocultos" : "Mostrar Ocultos";

        let $oMostrarOcultos = $(`<button class="btn btn-secondary ${filtroAtivo}" title="${tituloOculto}" style="margin-left:5px;border: 1px solid #ccc;border-radius: 4px;" 
                                        type="button" cbCol="ocultar" cbId="${preferencia}">
                                    <span class="fa fa-eye-slash"></span>
                                </button>`);

        $oMostrarOcultos.on('click',function(){
            let vthis = $(this);
            if(vthis.is(".filtroAtivo")){
                vthis.attr({'cbId':'N', 'title':'Não mostrar ocultos'});
                vthis.removeClass('filtroAtivo');
            }else{
                vthis.attr({'cbId':'Y,N', 'title':'Mostrar ocultos'});
                vthis.addClass('filtroAtivo');
            }
            let filtroFluxostatus = CB.oFiltroRapido.children('[cbcol="idfluxostatus"]');

            if(filtroFluxostatus.length == 0) return;

            filtroFluxostatus.attr('estado', 'inicial').click();
            filtroFluxostatus.find(".selectpicker").html('');
        });

        $oMostrarOcultos.insertAfter($("#cbFiltroRapido .btn-group").last());

        $(`div.btn-group[cbtab="${data.tabpesquisa}"][cbcol="ocultar"]`).remove();
    }
    
});