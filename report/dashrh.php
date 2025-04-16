<?

	 
$dashrh = "

	select
		'dashrh' as panel_id,
		'col-md-12' as panel_class_col,
		'FUNCIONÁRIOS / PONTO' as panel_title,
		'dashrhponto' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat(
			'?_modulo=eventoponto&_acao=u&dataevento_1=',
			DATE_FORMAT(
				CURRENT_DATE - if(
					(6 + weekday(CURRENT_DATE)) % 7 > 4,
					(6 + weekday(CURRENT_DATE)) % 7 -3,
					1
				),
				'%d/%m/%Y'
			),
			'&dataevento_2=',
			DATE_FORMAT(
				CURRENT_DATE - if(
					(6 + weekday(@dat)) % 7 > 4,
					(6 + weekday(@dat)) % 7 -3,
					1
				),
				'%d/%m/%Y'
			),
			'&idpessoa=',
			group_concat(idpessoa separator ','),
			'&idsgsetor=null&novajanela=Y'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'irregular' as card_title,
		DATE_FORMAT(
			CURRENT_DATE - if(
				(6 + weekday(CURRENT_DATE)) % 7 > 4,
				(6 + weekday(CURRENT_DATE)) % 7 -3,
				1
			),
			'%d/%m/%Y'
		) as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'PONTOS - TOTAL' as card_title_modal,
		'_modulo=funcionario&_acao=u' as card_url_modal
	from(
			SELECT
				idpessoa
			FROM
				rhevento e
				LEFT JOIN rhtipoevento t ON(t.idrhtipoevento = e.idrhtipoevento)
			WHERE
				e.idrhtipoevento = 6
				and DATE_FORMAT(e.dataevento, '%Y-%m-%d') = DATE_FORMAT(
					CURRENT_DATE - if(
						(6 + weekday(CURRENT_DATE)) % 7 > 4,
						(6 + weekday(CURRENT_DATE)) % 7 -3,
						1
					),
					'%Y-%m-%d'
				)
				AND e.status = 'PENDENTE'
			group by
				idpessoa
			having
				sum(e.valor) <> 0
			ORDER BY
				e.dataevento desc,
				e.hora
		) a
		
	
UNION ALL


	SELECT
		'dashrhfuncionariosdepartamento' as panel_id,
		'col-md-12' as panel_class_col,
		'FUNCIONÁRIOS / DEPARTAMENTO' as panel_title,
		'dashprodservminimo' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat(
			'_modulo=funcionario&_pagina=0&_ordcol=nome&_orddir=asc&_filtrosrapidos={%22status%22:%22ATIVO%22}'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		'success' as card_color,
		'success' as card_border_color,
		'' as card_bg_class,
		'total' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'FUNCIONÁRIOS - TOTAL' as card_title_modal,
		'_modulo=funcionario&_acao=u' as card_url_modal
	from
		vwcadfuncionario a
	where
		status = 'ATIVO'
		and exists (
			select
				1
			from
				objempresa o
			where
				o.idobjeto = a.idpessoa
				and o.objeto = 'pessoa' 
				".getidempresa('o.idempresa','funcionario')."
		)
		
		
UNION ALL


	select
		*
	from
		(
			select
				CONCAT('dashrhfuncionariosdepartamento', a.idsgarea) as panel_id,
				'col-md-12' as panel_class_col,
				concat(area, ' / FUNCIONÁRIOS') as panel_title,
				concat(
					'dashrhfuncionarios',
					replace(trim(lower(idsgdepartamento)), ' ', '')
				) as card_id,
				'col-md-2 col-sm-2 col-xs-6' as card_class_col,
				concat(
					'_modulo=funcionario&_pagina=0&_ordcol=nome&_orddir=asc&idpessoa=[',
					group_concat(idpessoa separator ','),
					']'
				) as card_url,
				'fundovermelho' as card_notification_bg,
				'0' as card_notification,
				'secundary' as card_color,
				'secundary' as card_border_color,
				'' as card_bg_class,
				REPLACE(departamento, 'Departamento', '') as card_title,
				'' as card_title_sub,
				count(1) as card_value,
				'fa-print' as card_icon,
				concat('FUNCIONÁRIOS - ', upper(departamento)) as card_title_modal,
				'_modulo=funcionario&_acao=u' as card_url_modal
			from
				(
					SELECT
						d.idsgdepartamento,
						d.departamento,
						p.idpessoa,
						a.idsgarea,
						a.area
					FROM
						sgdepartamento d
						join sgarea a on a.idsgarea = d.idsgarea
						and a.status = 'ATIVO'
						JOIN pessoaobjeto po on po.idobjeto = d.idsgdepartamento
						and po.tipoobjeto = 'sgdepartamento'
						JOIN pessoa p on p.idpessoa = po.idpessoa
						and p.status = 'ATIVO'
					WHERE
						d.status = 'ATIVO'
						and exists (
							select
								1
							from
								objempresa o
							where
								o.idobjeto = p.idpessoa
								and o.objeto = 'pessoa' 
								".getidempresa('p.idempresa','funcionario')."
						)
					union
					SELECT
						d.idsgdepartamento,
						d.departamento,
						p.idpessoa,
						a.idsgarea,
						a.area
					FROM
						sgdepartamento d
						join sgarea a on a.idsgarea = d.idsgarea
						and a.status = 'ATIVO'
						JOIN sgsetor s on s.idsgdepartamento = d.idsgdepartamento
						and s.status = 'ATIVO'
						JOIN pessoaobjeto po on po.idobjeto = s.idsgsetor
						and po.tipoobjeto = 'sgsetor'
						JOIN pessoa p on p.idpessoa = po.idpessoa
						and p.status = 'ATIVO'
					WHERE
						d.status = 'ATIVO'
						and exists (
							select
								1
							from
								objempresa o
							where
								o.idobjeto = p.idpessoa
								and o.objeto = 'pessoa' 
								".getidempresa('p.idempresa','funcionario')."
						)
				) a
			group by
				idsgdepartamento
			order by
				area,
				REPLACE(departamento, 'Departamento', '')
		) a
		
	
UNION ALL


	SELECT
		'dashrhfuncionariosincompletos' as panel_id,
		'col-md-12' as panel_class_col,
		'FUNCIONÁRIOS SEM ALOCAÇÃO' as panel_title,
		'dashrhfuncionariosincompletoslista' as card_id,
		'col-md-2 col-sm-2 col-xs-6' as card_class_col,
		concat(
			'_modulo=funcionario&_pagina=0&_ordcol=nome&_orddir=asc&idpessoa=[',
			group_concat(idpessoa separator ','),
			']'
		) as card_url,
		'fundovermelho' as card_notification_bg,
		'0' as card_notification,
		if (count(1) > 0, 'danger', 'success') as card_color,
		if (count(1) > 0, 'danger', 'success') as card_border_color,
		'' as card_bg_class,
		'total' as card_title,
		'' as card_title_sub,
		count(1) as card_value,
		'fa-print' as card_icon,
		'FUNCIONÁRIOS - SEM ALOCAÇÃO' as card_title_modal,
		'_modulo=funcionario&_acao=u' as card_url_modal
	from
		pessoa p
	where
		p.idtipopessoa = 1
		and p.status = 'ATIVO'
		and exists (
			select
				1
			from
				objempresa o
			where
				o.idobjeto = p.idpessoa
				and o.objeto = 'pessoa' 
				".getidempresa('p.idempresa','funcionario')."
		)
		and not exists (
			select
				1
			from
				pessoaobjeto pop
			where
				pop.idpessoa = p.idpessoa
				and pop.tipoobjeto = 'sgsetor'
		)
		and not exists (
			select
				1
			from
				pessoaobjeto pop
			where
				pop.idpessoa = p.idpessoa
				and pop.tipoobjeto = 'sgdepartamento'
		)
		and not exists (
			select
				1
			from
				pessoaobjeto pop
			where
				pop.idpessoa = p.idpessoa
				and pop.tipoobjeto = 'sgarea'
		) 
		".getidempresa('p.idempresa','funcionario')."
 ";

?>