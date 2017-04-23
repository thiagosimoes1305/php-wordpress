<?php
/**
* Retira a barra do WP no site.
*/
add_filter('show_admin_bar', '__return_false');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'start_post_rel_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link');
remove_action('wp_head', 'jquery');
remove_action('wp_head', 'dns-prefetch');
remove_action('wp_head', 'wp_resource_hints', 2);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('template_redirect', 'rest_output_link_header', 11, 0);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical');


/**
 * Thumbs para o WordPress.
 */
if (function_exists('add_theme_support')){
	add_theme_support('post-thumbnails');
	set_post_thumbnail_size(330, 450, false);
	//add_image_size('thumb436x220', 436, 220, true);
}


/**
 * 
 * Retorna só o ID de um vídeo do YouTube.
 *
 * @param string $url: passando o link do vídeo
 * @return string
 */
function getYoutubeIdFromUrl($url){
    $parts = parse_url($url);
    if (isset($parts['query'])){
        parse_str($parts['query'], $qs);
        if (isset($qs['v'])){
            return $qs['v'];
        }
        else if (isset($qs['vi'])){
            return $qs['vi'];
        }
    }
    if (isset($parts['path'])){
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path)-1];
    }
    return false;
}


/**
 * 
 * Retorna a URL da imagem baseada no ID do post e no tamanho do thumb.
 *
 * @param integer $post_ID: passa o ID do post para retornar a imagem
 * @param string $thumb: tamanho da imagem baseado no "add_theme_support"
 * @return string
 */
function retorno_url_thumbnail($post_ID, $thumb){
	$large_image_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_ID), $thumb);
	return ($large_image_url[0] != null) ? $large_image_url[0] : '';
}


/**
 * 
 * Gerando um CPT dinamicamente.
 * Forma criada para melhor geração de CPT.
 * Funciona na versão 7.0+ do PHP
 * Exemplo de uso:
 * retorno_gerar_cpt('Linha do Tempo', 'linha-do-tempo', 23, array('title', 'thumbnail', 'editor', 'excerpt'), true, true, 'Anos');
 *
 * @param String 	$nome: 		 Passando o nome do CTP
 * @param String 	$post_type:  Tipo do Post Type
 * @param Integer 	$posicao: 	 Posição do CTP no Menu do WordPress
 * @param Array 	$tipo: 		 Array com os tipos de campo para mostrar
 * @param Boolean 	$mostrar:  	 Mostrar o CPT no menu (true/false) por padrão "true"
 * @param Boolean 	$cat:  		 Ativando a catergoria (taxonomy) no CTP.
 * @param String 	$cat_nome:   Apesar do padrão ser um Boleano (falso) caso ative a categoria você pode
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


/**
 * Remover parâmetros fixos de largura e altura das imagens.
 */
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10, 3);
function remove_thumbnail_dimensions($html, $post_id, $post_image_id){
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
    return $html;
}


/**
 * Customizando a marca no login do WordPress.
 */
//add_action('login_enqueue_scripts', 'my_login_logo');
function my_login_logo(){
?>
<style type="text/css">
    body.login div#login h1 a {
        background-image: url(<?php echo get_bloginfo('template_url') ?>/global/imgs/marca-topo.png);
        width: 232px !important;
		height: 60px !important;
        background-size: 94% !important;
        background-position: 4px 4px !important;
    }
</style>
<?php
}


/**
 * Removendo Menus descessários ao cliente.
 * Só é mostrado para o Administrador
 */
add_action('admin_menu', 'removendo_links_do_menu', 999);
function removendo_links_do_menu(){
	remove_menu_page('edit-comments.php');
	if (!current_user_can('administrator')){
		remove_menu_page('upload.php');
		remove_menu_page('edit.php');
		remove_menu_page('edit.php?post_type=page');
		remove_menu_page('plugins.php');
		remove_menu_page('themes.php');
		remove_menu_page('tools.php');
		remove_menu_page('edit.php?post_type=acf');
	}
}


/**
 * Anotação da Área Princiapal do WordPress.
 */
add_action('wp_dashboard_setup', 'add_custom_dashboard_widgetALC');
function custom_dashboard_widgetALC(){
	$user_ID = get_current_user_id();
	echo '<script type="text/javascript">
			jQuery(function(){
				jQuery("#postbox-container-1").removeClass("postbox-container").addClass("welcome-panel").removeAttr("id");
			});
		</script>
		<p>Ao lado segue o menu com as principais funcionalidades do seu Site.</p>
		<p>Tema Principal.</p>';
}

function add_custom_dashboard_widgetALC(){
	wp_add_dashboard_widget('custom_dashboard_widgetALC', 'Bem vindo ao Gerenciador', 'custom_dashboard_widgetALC');
}


/**
 * Cortar Texto do front.
 */
function cortar_texto($texto, $limite){
	if (strlen($texto) <= $limite) return $texto;
	return array_shift(explode('||', wordwrap($texto, $limite, '||'))) . "...";
}


/**
 * 
 * Adicionar novos Menus a Sidebar do Admin no WordPress.
 *
 * @param não passa parâmetro
 * @return retorna as configurações para o Menu.
 */
//add_action('admin_menu', 'register_custom_menu_page');
function register_custom_menu_page(){

	// Home
	//add_menu_page('Home', 'Home', 'edit_posts', '#home', '', '', 16);
		//add_submenu_page('#home', 'Redes Sociais', '<a href="post.php?post=64&action=edit">Redes Sociais</a>', 1, '', 'mm');
		//add_submenu_page('#home', 'Banner da Home', '<a href="edit.php?post_type=banner-home">Banner da Home</a>', 1, '', 'mm');
		//add_submenu_page('#home', 'Slide da Home', '<a href="edit.php?post_type=slide-home">Slide da Home</a>', 1, '', 'mm');

	// Apresentação
	//add_menu_page('Apresentação', 'Apresentação', 'edit_posts', 'post.php?post=9&action=edit', '', '', 22);

	// Rodapé
	//add_menu_page('Rodapé', 'Rodapé', 'edit_posts', 'post.php?post=14&action=edit', '', '', 26);
}


/**
 * 
 * Removendo dados inrregulares do Menu criado na função acima.
 *
 * @param não passa parâmetro
 * @return limpa usando jQuery e CSS as saídas.
 */
//add_action('admin_footer', 'wpse_76048_script');
function wpse_76048_script(){
?>
<style type="text/css">

</style>
<script type="text/javascript">
	/*jQuery(function($){
		$("a[href='admin.php?page']").remove();
		$("a.wp-first-item[href='#cartao']").remove();
		$("a[href='post.php?post=29&action=edit&page']").remove();
	});*/
</script>
<?php
}
?>
