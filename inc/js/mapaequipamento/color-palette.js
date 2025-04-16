function loadColorPalette(corPadrao = false, corPadraoParaSemCor = '#b4c8ff')
{
    let JQcolorPalette = $('.color-palette'),
        JQcolorElement = '';

    if(JQcolorPalette.children().length)
    {
        return false;
    }

    for(let i in getColors())
    {
        JQcolorElement += ` <li class="item-color ${corPadrao && corPadrao == getColors()[i].value ? 'active' : ''}" title="${getColors()[i].name}" data-color="${getColors()[i].value}" style="color: ${getColors()[i].value};background-color: ${getColors()[i].value};"></li>`;
    }

    JQcolorElement += ` <li class="item-color ${!corPadrao ? 'active' : ''} d-flex" title="Sem cor" data-color="${corPadraoParaSemCor}" style="color: #000;background-color: #FFF;">
                            <i class="fa fa-remove" style="margin:auto;"></i>
                        </li>`;

    JQcolorPalette.append(JQcolorElement);
}

function getColors()
{
    return [
        {
            name:'Blue',
            value: '#0000FF'
        },
        {
            name: 'Black',
            value: '#000000'
        },	
        {
            name: 'Brown',
            value: '#A52A2A'
        },
        {
            name:'Cyan',
            value: '#00FFFF'
        },
        {
            name:'Purple',
            value: '#800080'
        }
    ];
}