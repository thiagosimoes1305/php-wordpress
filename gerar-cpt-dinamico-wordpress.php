/**
 * 
 * Gerando um CPT dinamicamente.
 * Forma criada para melhor geração de CPT.
 * Exemplo de uso:
 * retorno_gerar_cpt('Linha do Tempo', 'linha-do-tempo', 23, array('title', 'thumbnail', 'editor', 'excerpt'), true, true, 'Anos');
 *
 * @param String 	$nome: 		 Passando o nome do CTP
 * @param String 	$post_type:      Tipo do Post Type
 * @param Integer 	$posicao: 	 Posição do CTP no Menu do WordPress
 * @param Array 	$tipo: 		 Array com os tipos de campo para mostrar
 * @param Boolean 	$mostrar:  	 Mostrar o CPT no menu (true/false) por padrão "true"
 * @param Boolean 	$cat:  		 Ativando a catergoria (taxonomy) no CTP.
 * @param String 	$cat_nome:       Apesar do padrão ser um Boleano (falso) caso ative a categoria você pode
 					 passar um nome para ela, caso nao deseje ativa o nome padrão "Categoria."
 * @return true
 */
function retorno_gerar_cpt($nome, $post_type, $posicao, $tipo, $mostrar = true, $cat = false, $cat_nome = false){
	add_action('init', function() use ($nome, $post_type, $posicao, $tipo, $mostrar, $cat, $cat_nome){
		$labels = array( 
			'name' => _x($nome, $post_type),
			'singular_name' => _x($nome, $post_type),
			'add_new' => _x('Cadastrar', $post_type),
			'add_new_item' => _x('Cadastrar', $post_type),
			'edit_item' => _x('Editar', $post_type),
			'new_item' => _x('Novo', $post_type),
			'view_item' => _x('Ver', $post_type),
			'search_items' => _x('Procurar', $post_type),
			'not_found' => _x('Nenhum registro encontrado', $post_type),
			'not_found_in_trash' => _x('Nenhum registro encontrado na lixeira', $post_type),
			'parent_item_colon' => _x('Parent:', $post_type),
			'menu_name' => _x($nome, $post_type)
		);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,        
			'supports' => $tipo,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => $mostrar,       
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'has_archive' => true,
			'query_var' => true,
			'can_export' => true,
			'capability_type' => 'post',
			'menu_position' => $posicao
		);

		register_post_type($post_type, $args);
		flush_rewrite_rules();
	});

	if ($cat){
		$novo_nome = str_replace('-', '_', $post_type);
		register_taxonomy('cat-'.$post_type, $post_type, 
			array(            
		      	'label' => ($cat_nome == false) ? 'Categorias' : $cat_nome, 
		        'singular_label' => ($cat_nome == false) ? 'Categorias' : $cat_nome, 
		        'rewrite' => true,
		        'hierarchical' => true
			)
		);

		add_filter('manage_taxonomies_for_'.$novo_nome.'_columns', $novo_nome.'_type_columns');
		${$novo_nome.'_type_columns'} = function($taxonomies){
			$taxonomies[] = 'cat-'.$post_type;
			return $taxonomies;
		};

		${$novo_nome.'_type_columns'}($taxonomies);

	}
}
