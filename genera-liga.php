<?php
/*
	Plugin Name: DF GENERADOR DE LIGAS
	Plugin URI: http://www.jj.com/
	description: Plugin realizado por Joser
	Version: 1.2
	Author: Joser
	Author URI: http://www.jj.com/
	License: GPL2
*/
function sanear_string2($string){
 
    $string = trim($string);
 
    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
        array("\\", "¨", "º", "-", "~",
             "#", "@", "|", "!", '"',
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",
             "."),
        '',
        $string
    );
    return $string;
}


  add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
  wp_enqueue_style( 'relacion', plugins_url( '/css/styles.css', __FILE__ ) );
  wp_enqueue_style( 'relacion' );
}
wp_enqueue_style( 'relacion', plugins_url( '/css/styles.css', __FILE__ ) );
wp_enqueue_style( 'relacion' );


//PRIMERO CARGAMOS LA HOJA PARA LOS JS DEL CLIENTE
wp_enqueue_script('scrip-approval', plugins_url( '/js/main.js', __FILE__ ), array('jquery'));
wp_localize_script('scrip-approval', 'myScript', array(
    'pluginsUrl' => plugins_url(),
));

//add_action( 'wp_ajax_foobar', 'my_ajax_foobar_handler' );

add_action( 'wp_enqueue_scripts', 'ajax_scripts' );
function ajax_scripts() {
    wp_register_script( 'main-ajax', get_template_directory_uri() . '/assets/js/main-ajax.js', array(), '', true );
    $arr = array(
        'ajaxurl' => admin_url('admin-ajax.php')
    );
    wp_localize_script('main-ajax','obj',$arr );
    wp_enqueue_script('main-ajax');
}
add_action('wp_ajax_my_product_ajaxss', 'my_action_productos_v');
add_action('wp_ajax_nopriv_my_product_ajaxss', 'my_action_productos_v');

//INCLUIMOS EL ARCHIVO ADMINISTRATIVO DE LA WEB
//include("df-admin.php");
//INCLUIMOS EL ARCHIVO DE LAS VISTAS
//include("df-view.php");

/** Step 2 (from text above). */
add_action( 'admin_menu', 'menu_g_form_user_destination_ligas' );

/** Step 1. */
function menu_g_form_user_destination_ligas() {
  //add_options_page( 'Gf user Destination', 'GF usuarios', 'manage_options', 'my-unique-identifier', 'generador_de_ligas' );
  
  //ESTE ES PARA CREAR UN MENU SECUNDARIO
  //add_options_page( 'Approval Certificates', 'Approval Certificates', 'manage_options', 'dashboard_certificados', 'generador_de_ligas' );
  //ESTE DE ABAJO ES PARA CREAR UN MENU PRINCIPAL
  add_menu_page( 'Generar Liga', 'Generar Liga', 'manage_options', 'generar-liga', 'generador_de_ligas', 'dashicons-editor-paste-text', '35' );
  add_submenu_page( 'generar-liga', 'Mis cosas', 'Mis cosas', 'manage_options', 'mis-cosas', 'generador_de_ligas33' );
}

/** Step 3. */
function generador_de_ligas() {
  global $wpdb;

  $url_web_creada = "";

  if(isset($_POST['crear_liga_nueva'])){
    $current_user = wp_get_current_user();

    //echo "<h1>Crear nueva liga</h1>";
    $formulario_inscripcion = $_POST['formulario_inscripcion'];
    $titulo_liga            = $_POST['titulo_liga'];
    $sp_season              = $_POST['sp_season'];
    $sp_season_nuevo        = $_POST['sp_season_nuevo'];
    $sp_league              = $_POST['sp_league'];
    $sp_league_nuevo        = $_POST['sp_league_nuevo'];
    $nueva_categoria        = $_POST['nueva_categoria'];
    $sp_categoria_nueva     = $_POST['sp_categoria_nueva'];
    $total_equipos          = $_POST['total_equipos'];

    $season_nuevo         = [];
    $competiciones_nuevo  = [];

    //CREAREMOS LA TABLAS DE LIGA
    $nombre     = $titulo_liga;
    $nombreFinal  = sanear_string2($nombre);
    $nombreFinal  = str_replace(array("  ", " "), "-", $nombreFinal);
    $nombreFinal  = strtolower($nombreFinal);

    //PRIMERO DEBEMOS SABER SI EXISTEN LEAGUE, SEASON O CATEGORIAS NUEVAS
    if($sp_season_nuevo != ""){
      //PRIMERO CONSULTAMOS SI ESTE YA NO EXISTE
      $existe = 0;
      $exs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}terms WHERE name = {$sp_season_nuevo} ");
      foreach ($exs as $key) {
        $existe++;
      }

      if($existe == 0){
        $sp_season_nuevo_slug = strtolower($sp_season_nuevo);

        $sp_season_nuevo_slug = str_replace(" ", "-", $sp_season_nuevo_slug);
        //PROCEDEMOS A CREAR EL SEASON
        $wpdb->insert( 
          $wpdb->prefix."terms", 
            array( 
              'name'        => $sp_season_nuevo,
              'slug'        => $sp_season_nuevo_slug,
              'term_group'  => '0'
            ) 
          );

        $id_season = $wpdb->insert_id;

        //AHORA PROCEDEMOS A CREAR ESTO EN LOS TERMS PARA QUE SE SEPA QUE ES UN SP_SEASON
        $wpdb->insert( 
          $wpdb->prefix."term_taxonomy", 
            array( 
              'term_id'     =>  $id_season,
              'taxonomy'    =>  "sp_season",
              'description' =>  "",
              'parent'      =>  "0",
              'count'       =>  "0"
            ) 
          ); 

        //COLOCAMOS EL SEASON QUE SI ES
        $sp_season = $id_season;     
      }

      array_push($season_nuevo, $sp_season);
    }else{
      array_push($season_nuevo, $sp_season);
    }

    //HACEMOS LO MISMO CON LA LIGA
    if($sp_league_nuevo != ""){
      //PRIMERO CONSULTAMOS SI ESTE YA NO EXISTE
      $existe = 0;
      $exs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}terms WHERE name = {$sp_league_nuevo} ");
      foreach ($exs as $key) {
        $existe++;
      }

      if($existe == 0){
        $sp_league_nuevo_slug = strtolower($sp_league_nuevo);

        $sp_league_nuevo_slug = str_replace(" ", "-", $sp_league_nuevo_slug);
        //PROCEDEMOS A CREAR EL SEASON
        $wpdb->insert( 
          $wpdb->prefix."terms", 
            array( 
              'name'        => $sp_league_nuevo,
              'slug'        => $sp_league_nuevo_slug,
              'term_group'  => '0'
            ) 
          );

        $id_league = $wpdb->insert_id;

        //AHORA PROCEDEMOS A CREAR ESTO EN LOS TERMS PARA QUE SE SEPA QUE ES UN SP_SEASON
        $wpdb->insert( 
          $wpdb->prefix."term_taxonomy", 
            array( 
              'term_id'     =>  $id_league,
              'taxonomy'    =>  "sp_league",
              'description' =>  "",
              'parent'      =>  "0",
              'count'       =>  "0"
            ) 
          ); 

        //COLOCAMOS EL SEASON QUE SI ES
        $sp_league = $id_league;     
      }

      array_push($competiciones_nuevo, $sp_league);
    }else{
      //array_push($competiciones_nuevo, $sp_league);
    }

    //HACEMOS LO MISMO CON LA LIGA
    if($sp_categoria_nueva != ""){
      //PRIMERO CONSULTAMOS SI ESTE YA NO EXISTE
      $existe = 0;
      $exs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}terms WHERE name = {$sp_categoria_nueva} ");
      foreach ($exs as $key) {
        $existe++;
      }

      if($existe == 0){
        $sp_categoria_nueva_slug = strtolower($sp_categoria_nueva);

        $sp_categoria_nueva_slug = str_replace(" ", "-", $sp_categoria_nueva_slug);
        //PROCEDEMOS A CREAR EL SEASON
        $wpdb->insert( 
          $wpdb->prefix."terms", 
            array( 
              'name'        => $sp_categoria_nueva,
              'slug'        => $sp_categoria_nueva_slug,
              'term_group'  => '0'
            ) 
          );

        $id_categoria = $wpdb->insert_id;

        //AHORA PROCEDEMOS A CREAR ESTO EN LOS TERMS PARA QUE SE SEPA QUE ES UN SP_SEASON
        $wpdb->insert( 
          $wpdb->prefix."term_taxonomy", 
            array( 
              'term_id'     =>  $id_categoria,
              'taxonomy'    =>  "sp_league",
              'description' =>  "",
              'parent'      =>  "0",
              'count'       =>  "0"
            ) 
          ); 

        //COLOCAMOS EL SEASON QUE SI ES
        $nueva_categoria = $id_categoria;     
      }

      array_push($competiciones_nuevo, $nueva_categoria);
    }else{
      //array_push($competiciones_nuevo, $nueva_categoria);
    }


    //VARIABLES QUE UTILIZAREMOS MAS ADELANTE
    $competiciones = [];
    $season = [];
    $jugadoresArray = [];
    

    for ($i=0; $i <= count($total_equipos); $i++) { 
      //echo "<h1>".$total_equipos[$i]."</h1>";

      //AHORA CON LAS COMPETICIONES Y TODO PROCEDEMOS A REVISAR SI ESTOS EQUIPOS YA ESTAN RELACIONADOS CON ESTAS COMPETICIONES
      //SI NO ESTAN RELACIONADOS PROCEDEMOS A RELACIONARLOS

      //VARIABLES
      //$sp_season
      //$sp_league
      //$nueva_categoria

      //SEASON 
      $conmp = $wpdb->get_results(" SELECT r.term_taxonomy_id FROM {$wpdb->prefix}term_relationships AS r WHERE r.object_id = {$total_equipos[$i]} AND (SELECT tx.taxonomy FROM {$wpdb->prefix}term_taxonomy AS tx WHERE tx.term_id =  r.term_taxonomy_id) = 'sp_season' AND r.term_taxonomy_id = '{$sp_season}' ");
      $hay_season = 0;
      foreach ($conmp as $key) {
        $hay_season++;
      }

      if($hay_season == 0){
        //SI ESTE EQUIPO NO CUENTA CON ESTE SEASON PROCEDEMOS A RELAICONARLO
        $wpdb->insert( 
          $wpdb->prefix."term_relationships", 
            array( 
              'object_id'          =>  $total_equipos[$i],
              'term_taxonomy_id'   =>  $sp_season,
              'term_order'         =>  "0"
            ) 
          ); 
      }

      array_push($season, $sp_season);

      //COMPETICION
      $conmp = $wpdb->get_results(" SELECT r.term_taxonomy_id FROM {$wpdb->prefix}term_relationships AS r WHERE r.object_id = {$total_equipos[$i]} AND (SELECT tx.taxonomy FROM {$wpdb->prefix}term_taxonomy AS tx WHERE tx.term_id =  r.term_taxonomy_id) = 'sp_league' AND r.term_taxonomy_id = '{$sp_league}' ");
      $hay_season = 0;
      foreach ($conmp as $key) {
        $hay_season++;
      }

      if($hay_season == 0){
        //SI ESTE EQUIPO NO CUENTA CON ESTE SEASON PROCEDEMOS A RELAICONARLO
        $wpdb->insert( 
          $wpdb->prefix."term_relationships", 
            array( 
              'object_id'          =>  $total_equipos[$i],
              'term_taxonomy_id'   =>  $sp_league,
              'term_order'         =>  "0"
            ) 
          ); 
      }

      //NUEVA COMPETICION
      $conmp = $wpdb->get_results(" SELECT r.term_taxonomy_id FROM {$wpdb->prefix}term_relationships AS r WHERE r.object_id = {$total_equipos[$i]} AND (SELECT tx.taxonomy FROM {$wpdb->prefix}term_taxonomy AS tx WHERE tx.term_id =  r.term_taxonomy_id) = 'sp_league' AND r.term_taxonomy_id = '{$nueva_categoria}' ");
      $hay_season = 0;
      foreach ($conmp as $key) {
        $hay_season++;
      }

      if($hay_season == 0){
        //SI ESTE EQUIPO NO CUENTA CON ESTE SEASON PROCEDEMOS A RELAICONARLO
        $wpdb->insert( 
          $wpdb->prefix."term_relationships", 
            array( 
              'object_id'          =>  $total_equipos[$i],
              'term_taxonomy_id'   =>  $nueva_categoria,
              'term_order'         =>  "0"
            ) 
          ); 
      }

      //AHORA SOLO DEBEMOS AGARRAR LOS JUGADORES DE CADA EQUIPO Y RELACIONARLOS CON ESTAS METRICAS
      array_push($competiciones, $sp_league);
      array_push($competiciones, $nueva_categoria);
    
      //CONSULTAMOS LOS JUGADORES DE ESTE EQUIPO
      //$jugador = $wpdb->get_results("SELECT p.* FROM {$wpdb->prefix}posts AS p WHERE p.ID IN(SELECT m.post_id FROM {$wpdb->prefix}postmeta AS m WHERE m.meta_value = {$total_equipos[$i]} AND m.meta_key = 'sp_team') AND p.post_type = 'sp_player' ");//OCULTAMOS ESTE YA QUE SOLO NECESITAMOS LOS JUGADORES INSCRITOS
      $jugador = $wpdb->get_results("SELECT p.* FROM wp_9_posts AS p WHERE p.ID IN(SELECT m.post_id FROM wp_9_postmeta AS m WHERE m.meta_value = {$total_equipos[$i]} AND m.meta_key = 'sp_team') AND p.post_type = 'sp_player' AND p.post_author = (SELECT u.ID FROM wp_users AS u WHERE u.ID = p.post_author AND u.user_email = (SELECT f.value FROM wp_rg_lead_detail AS f WHERE f.value = u.user_email AND f.form_id = {$formulario_inscripcion} LIMIT 1) )");

      foreach ($jugador as $jkey) {
        //GUARDAMOS EL JUGADOR EN EL ARREGLO
        array_push($jugadoresArray, $jkey->ID);

        //echo "<span>{$jkey->ID} {$jkey->post_title}</span><br />";

        //CONSULTAMOS SI ESTE JUGADOR ESTA EN ALGUNA DE LAS COMPETICIONES EN DONDE ESTA EL EQUIPOS
        for ($i=0; $i <= count($competiciones); $i++) { 

          //echo "<h1>Competicion -> {$competiciones[$i]}</h1>";
          //AQUI RELACIONAMOS
          $activo = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}term_relationships WHERE object_id = {$jkey->ID} AND term_taxonomy_id = {$competiciones[$i]} ");

          if($activo <= 0){//SI NO EXISTE ESTA TAXONOMIA LA CREAMOS
            $wpdb->insert( 
              $wpdb->prefix."term_relationships", 
              array( 
                'object_id'     => $jkey->ID,
                'term_taxonomy_id'  => $competiciones[$i],
                'term_order'    => '0'
              ) 
            );

            //echo "relacionando";
          }else{
            //echo "<br> Existe {$jkey->ID} {$competiciones[$i]}<br>";
          }
        }

        for ($i=0; $i <= count($season); $i++) { 
          //echo "<h1>Competicion -> {$competiciones[$i]}</h1>";
          //AQUI RELACIONAMOS
          $activo = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}term_relationships WHERE object_id = {$jkey->ID} AND term_taxonomy_id = {$season[$i]} ");
          if($activo <= 0){//SI NO EXISTE ESTA TAXONOMIA LA CREAMOS
            $wpdb->insert( 
              $wpdb->prefix."term_relationships", 
              array( 
                'object_id'         => $jkey->ID,
                'term_taxonomy_id'  => $season[$i],
                'term_order'        => '0'
              ) 
            );
            //echo "relacionando";
          }else{
            //echo "<br> Existe {$jkey->ID} {$competiciones[$i]}<br>";
          }
        }
      }

      //BACIAMOS EL ARREGLO DE COMPETICIONES
      $competiciones = [];
      $season = [];
      $jugadoresArray = [];


    }

    //ESTE ES EL ARREGLO DE LOS ID QUE DEBEMOS RELACIONAR POSTERIORMENTE
    $arreglos_id = [];

    /*
    **
      //ID DE LA TABLA DE LIGA QUE COPIAREMOS
      //8271
    **
    */

    //YA CON LAS VARIABLES PROCEDEMOS A INSERTAR TODO EN LA DB

    $diaYhora = date("Y-m-d H:i:s");
    $current_user = wp_get_current_user();

    //INSERTAMOS EN EL POST
    $wpdb->insert(
              "wp_9_posts",
              array(
                  'post_author'           =>  $current_user->ID,
                  'post_date'             =>  $diaYhora,
                  'post_date_gmt'         =>  $diaYhora,
                  'post_content'          =>  '',
                  'post_title'            =>  $nombre,
                  'post_excerpt'          =>  '',
                  'post_status'           =>  'publish',
                  'comment_status'        =>  'open',
                  'ping_status'           =>  'closed',
                  'post_password'         =>  '',
                  'post_name'             =>  $nombreFinal,
                  'to_ping'               =>  '',
                  'pinged'                =>  '',
                  'post_modified'         =>  $diaYhora,
                  'post_modified_gmt'     =>  $diaYhora,
                  'post_content_filtered' =>  '',
                  'post_parent'           =>  0,
                  'menu_order'            =>  0,
                  'post_type'             =>  'sp_table',
                  'post_mime_type'        =>  '',
                  'comment_count'         =>  0
                  )
              );

    $idTabla = $wpdb->insert_id;
    array_push($arreglos_id, $idTabla);
    //echo "<h1>{$idTabla} ".$wpdb->print_error()."</h1>";
    //MODIFICAMOS EL PORDUCTO CREADO PARA PODER AGREGARLE LA RUTA SEGUN SU ID
    $wpdb->update( 
                "wp_9_posts", 
                array( 
                  'guid' => get_site_url()."/table/".$nombreFinal
                ), 
                array( 
                  'ID' => $idTabla
                  ) 
              );

    //YA CON LA TABLA CREADA PROCEDEMOS A IGUALAR EL POST_META DE UNA TABLA CREADA
    $tabla_creada = $wpdb->get_results("SELECT * FROM wp_9_postmeta WHERE post_id = 3694");
    foreach ($tabla_creada as $key) {
      //ESTRUCTURA DE LA TABLA
      //post_id
      //meta_key
      //meta_value

      $wpdb->insert(
              "wp_9_postmeta",
              array(
                  'post_id'           =>  $idTabla,
                  'meta_key'          =>  $key->meta_key,
                  'meta_value'        =>  $key->meta_value
                )
              );

    }

    //MANDAMOS A GUARDAR EL SHORDCODE DE ESTA TABLA EN ESPECIFICO
    $indice_tabla = genera_tabla_liga($idTabla, $nombre);


    /*
    **
      INSERTAMOS CALENDARIOS PUBLICADOS
    **
    */
    $nombre_calendario    = "Calendario - ".$nombre." - Publicado";
    $nombre_calendario_f  = "Calendario-".$nombreFinal."-Publicado";
    //INSERTAMOS EN EL POST
    $wpdb->insert(
              "wp_9_posts",
              array(
                  'post_author'           =>  $current_user->ID,
                  'post_date'             =>  $diaYhora,
                  'post_date_gmt'         =>  $diaYhora,
                  'post_content'          =>  '',
                  'post_title'            =>  $nombre_calendario,
                  'post_excerpt'          =>  '',
                  'post_status'           =>  'publish',
                  'comment_status'        =>  'open',
                  'ping_status'           =>  'closed',
                  'post_password'         =>  '',
                  'post_name'             =>  $nombre_calendario_f,
                  'to_ping'               =>  '',
                  'pinged'                =>  '',
                  'post_modified'         =>  $diaYhora,
                  'post_modified_gmt'     =>  $diaYhora,
                  'post_content_filtered' =>  '',
                  'post_parent'           =>  0,
                  'menu_order'            =>  0,
                  'post_type'             =>  'sp_calendar',
                  'post_mime_type'        =>  '',
                  'comment_count'         =>  0
                  )
              );

    $idCalendario1 = $wpdb->insert_id;
    array_push($arreglos_id, $idCalendario1);
    //echo "<h1>{$idCalendario1} ".$wpdb->print_error()."</h1>";
    //MODIFICAMOS EL PORDUCTO CREADO PARA PODER AGREGARLE LA RUTA SEGUN SU ID
    $wpdb->update( 
                "wp_9_posts", 
                array( 
                  'guid' => get_site_url()."/calendar/".$nombre_calendario_f
                ), 
                array( 
                  'ID' => $idCalendario1
                  ) 
              );

    //YA CON LA TABLA CREADA PROCEDEMOS A IGUALAR EL POST_META DE UNA TABLA CREADA
    $tabla_creada = $wpdb->get_results("SELECT * FROM wp_9_postmeta WHERE post_id = 8293");
    foreach ($tabla_creada as $key) {
      //ESTRUCTURA DE LA TABLA
      //post_id
      //meta_key
      //meta_value

      $wpdb->insert(
              "wp_9_postmeta",
              array(
                  'post_id'           =>  $idCalendario1,
                  'meta_key'          =>  $key->meta_key,
                  'meta_value'        =>  $key->meta_value
                )
              );

    }

    $indice_calendario1 = genera_proximos($idCalendario1, $nombre);

    /*
    **
      INSERTAMOS CALENDARIOS PROGAMADOS
    **
    */
    $nombre_calendario    = "Calendario - ".$nombre." - Publicado";
    $nombre_calendario_f  = "Calendario-".$nombreFinal."-Publicado";
    //INSERTAMOS EN EL POST
    $wpdb->insert(
              "wp_9_posts",
              array(
                  'post_author'           =>  $current_user->ID,
                  'post_date'             =>  $diaYhora,
                  'post_date_gmt'         =>  $diaYhora,
                  'post_content'          =>  '',
                  'post_title'            =>  $nombre_calendario,
                  'post_excerpt'          =>  '',
                  'post_status'           =>  'publish',
                  'comment_status'        =>  'open',
                  'ping_status'           =>  'closed',
                  'post_password'         =>  '',
                  'post_name'             =>  $nombre_calendario_f,
                  'to_ping'               =>  '',
                  'pinged'                =>  '',
                  'post_modified'         =>  $diaYhora,
                  'post_modified_gmt'     =>  $diaYhora,
                  'post_content_filtered' =>  '',
                  'post_parent'           =>  0,
                  'menu_order'            =>  0,
                  'post_type'             =>  'sp_calendar',
                  'post_mime_type'        =>  '',
                  'comment_count'         =>  0
                  )
              );

    $idCalendario2 = $wpdb->insert_id;
    array_push($arreglos_id, $idCalendario2);
    //echo "<h1>{$idCalendario2} ".$wpdb->print_error()."</h1>";
    //MODIFICAMOS EL PORDUCTO CREADO PARA PODER AGREGARLE LA RUTA SEGUN SU ID
    $wpdb->update( 
                "wp_9_posts", 
                array( 
                  'guid' => get_site_url()."/calendar/".$nombre_calendario_f
                ), 
                array( 
                  'ID' => $idCalendario2
                  ) 
              );

    //YA CON LA TABLA CREADA PROCEDEMOS A IGUALAR EL POST_META DE UNA TABLA CREADA
    $tabla_creada = $wpdb->get_results("SELECT * FROM wp_9_postmeta WHERE post_id = 8296");
    foreach ($tabla_creada as $key) {
      //ESTRUCTURA DE LA TABLA
      //post_id
      //meta_key
      //meta_value

      $wpdb->insert(
              "wp_9_postmeta",
              array(
                  'post_id'           =>  $idCalendario2,
                  'meta_key'          =>  $key->meta_key,
                  'meta_value'        =>  $key->meta_value
                )
              );

    }

    $indice_calendario2 = genera_proximos2($idCalendario2, $nombre);


    /*
    **
      SECCION LISTAS
    **
    */
    //A ESTA TABLA QUE CREAMOS DEBEMOS PROCEDER A RELACIONARLE TODOS LOS EQUIPOS
    for ($i=0; $i <= count($total_equipos); $i++){
      //DEBEMOS BUSCAR LA LISTA ACTUAL DE ESTE EQUIPO Y VER CUAL ES LA TOP Y CUAL ES LA NORMAL
      //LAS LISTAS QUE SON GLOBALES TIENEN EL sp_team = 0

      $id_list_equipo = 0;
      //CONSULTAMOS LAS LISTAS DEL EQUIPO
      $list = $wpdb->get_results(" SELECT m.* FROM wp_9_postmeta AS m WHERE m.post_id = {$total_equipos[$i]} AND m.meta_key = 'sp_list' AND m.meta_value = (SELECT l.post_id FROM wp_9_postmeta AS l WHERE l.post_id = m.meta_value AND l.meta_key = 'sp_team' AND l.meta_value != '0') ");
      foreach ($list as $keys) {
        //ESTOS SON LOS DATOS DE LA LISTA
        $id_list_equipo = $keys->meta_value;
        //LA GUARDAMOS EN EL ARREGLO QUE RELACIONARA TODAS LAS METRICAS
        array_push($arreglos_id, $id_list_equipo);
      }


      $id_list_top = 0;
      //CONSULTAMOS LAS LISTAS TOP
      $list = $wpdb->get_results(" SELECT m.* FROM wp_9_postmeta AS m WHERE m.post_id = {$total_equipos[$i]} AND m.meta_key = 'sp_list' AND m.meta_value = (SELECT l.post_id FROM wp_9_postmeta AS l WHERE l.post_id = m.meta_value AND l.meta_key = 'sp_team' AND l.meta_value = '0') ");
      foreach ($list as $keys) {
        //ESTOS SON LOS DATOS DE LA LISTA
        $id_list_top = $keys->meta_value;
      }
      

    }

    /*
    **
      SECCION DONDE CREAREMOS UNA LISTA NUEVA
    **
    */

    //PROCEDEMOS A CREAR UNA LISTA NUEVA DE LOS TOP GOLES PARA PODER ASOCIAR LUEGO CON TODAS LAS 8324
    //INSERTAMOS EN EL POST
    $nombre_list    = "Lista - ".$nombre." - Publicado";
    $nombre_list_f  = "Lista-".$nombreFinal."-Publicado";
    //INSERTAMOS EN EL POST
    $wpdb->insert(
              "wp_9_posts",
              array(
                  'post_author'           =>  $current_user->ID,
                  'post_date'             =>  $diaYhora,
                  'post_date_gmt'         =>  $diaYhora,
                  'post_content'          =>  '',
                  'post_title'            =>  $nombre_list,
                  'post_excerpt'          =>  '',
                  'post_status'           =>  'publish',
                  'comment_status'        =>  'open',
                  'ping_status'           =>  'closed',
                  'post_password'         =>  '',
                  'post_name'             =>  $nombre_list_f,
                  'to_ping'               =>  '',
                  'pinged'                =>  '',
                  'post_modified'         =>  $diaYhora,
                  'post_modified_gmt'     =>  $diaYhora,
                  'post_content_filtered' =>  '',
                  'post_parent'           =>  0,
                  'menu_order'            =>  0,
                  'post_type'             =>  'sp_list',
                  'post_mime_type'        =>  '',
                  'comment_count'         =>  0
                  )
              );

    $idList = $wpdb->insert_id;
    array_push($arreglos_id, $idList);
    //echo "<h1>{$idList} ".$wpdb->print_error()."</h1>";
    //MODIFICAMOS EL PORDUCTO CREADO PARA PODER AGREGARLE LA RUTA SEGUN SU ID
    $wpdb->update( 
                "wp_9_posts", 
                array( 
                  'guid' => get_site_url()."/list/".$nombre_list_f
                ), 
                array( 
                  'ID' => $idList
                  ) 
              );

    //YA CON LA TABLA CREADA PROCEDEMOS A IGUALAR EL POST_META DE UNA TABLA CREADA
    $tabla_creada = $wpdb->get_results("SELECT * FROM wp_9_postmeta WHERE post_id = 8324");
    foreach ($tabla_creada as $key) {
      //ESTRUCTURA DE LA TABLA
      //post_id
      //meta_key
      //meta_value

      $wpdb->insert(
              "wp_9_postmeta",
              array(
                  'post_id'           =>  $idList,
                  'meta_key'          =>  $key->meta_key,
                  'meta_value'        =>  $key->meta_value
                )
              );

    }

    $indice_lista = genera_tops_10($idList, $nombre);


    //EN ESTE PUNTO DEBEMOS RECORRER UN ARREGLO CON TODOS LOS ID CREADOS PARA RELACIONAR LAS COMPETICIONES ARREGLOS_ID[]
    for ($zz=0; $zz <= count($arreglos_id); $zz++) { 
      //YA CON EL POST META DUPLICADO ADECUADAMENTE PROCEDEMOS A RELACIONAR LA TABLA CON TAS METRICAS
      for ($i=0; $i <= count($season_nuevo); $i++) { 
        //echo "<h1>Competicion -> {$competiciones[$i]}</h1>";
        //AQUI RELACIONAMOS
        $activo = $wpdb->get_var("SELECT COUNT(*) FROM wp_9_term_relationships WHERE object_id = {$arreglos_id[$zz]} AND term_taxonomy_id = {$season_nuevo[$i]} ");
        if($activo <= 0){//SI NO EXISTE ESTA TAXONOMIA LA CREAMOS
          $wpdb->insert( 
            "wp_9_term_relationships", 
            array( 
              'object_id'         => $arreglos_id[$zz],
              'term_taxonomy_id'  => $season_nuevo[$i],
              'term_order'        => '0'
            ) 
          );
          //echo "relacionando";
        }else{
          //echo "<br> Existe {$jkey->ID} {$competiciones[$i]}<br>";
        }
      }


      //VAMOS CON LAS COMPETICIONES
      for ($i=0; $i <= count($competiciones_nuevo); $i++) { 
        //echo "<h1>Competicion -> {$competiciones[$i]}</h1>";
        //AQUI RELACIONAMOS
        $activo = $wpdb->get_var("SELECT COUNT(*) FROM wp_9_term_relationships WHERE object_id = {$arreglos_id[$zz]} AND term_taxonomy_id = {$competiciones_nuevo[$i]} ");
        if($activo <= 0){//SI NO EXISTE ESTA TAXONOMIA LA CREAMOS
          $wpdb->insert( 
            "wp_9_term_relationships", 
            array( 
              'object_id'         => $arreglos_id[$zz],
              'term_taxonomy_id'  => $competiciones_nuevo[$i],
              'term_order'        => '0'
            ) 
          );
          //echo "relacionando";
        }else{
          //echo "<br> Existe {$jkey->ID} {$competiciones[$i]}<br>";
        }
      }

      /*
      **
        LUEGO DE ESTO REVISAMOS SI ESTE EQUIPO CUENTA CON LA LISTA DE TOP GOLEADORES DE ESTAS METRICAS
      **
      */
      $activo = $wpdb->get_var("SELECT COUNT(*) FROM wp_9_postmeta WHERE post_id = {$arreglos_id[$zz]} AND meta_key = 'sp_list' AND meta_value = {$idList} ");
        if($activo <= 0){//SI NO EXISTE ESTA TAXONOMIA LA CREAMOS
          $wpdb->insert( 
            "wp_9_postmeta", 
            array( 
              'post_id'       => $arreglos_id[$zz],
              'meta_key'      => 'sp_list',
              'meta_value'    => $idList
            ) 
          );
          //echo "relacionando";
        }else{
          //echo "<br> Existe {$jkey->ID} {$competiciones[$i]}<br>";
        }
    }

    /*
    **
      AL FINALIZAR TODAS LAS MODIFICACIONES PRECEDEMOS A DUBLICAR LA PAGINA QUE QUEREMOS CREAR
    **
    */

    $id_pagina_nueva = duplicar_pagina_demo();

    //MODIFICAMOS LA PAGINA CREADA PARA AGREGAR SOLO EL SHORCODE QUE QUEREMOS
    $pagina_creada = $wpdb->get_results("SELECT * FROM wp_9_posts WHERE ID = {$id_pagina_nueva}");
    foreach ($pagina_creada as $key){
      $contenido_pagina = $key->post_content;
      $url_web_creada   = "<h1> Esta es la nueva web <a href='".$key->guid."' target='_blank'> click aqui </a></h1> ";

      //REEMPLAZAMOS LA INFORMACION ACTUAL POR LA INFORMACION NUEVA
      $contenido_pagina = str_replace("[do_widget id=sportspress-league-table-5]", "[do_widget id=sportspress-league-table-".$indice_tabla."]", $contenido_pagina);

      //HACEMOS LO MISMO CON LA LISTA
      $contenido_pagina = str_replace("[do_widget id=sportspress-player-list-5]", "[do_widget id=sportspress-player-list-".$indice_lista."]", $contenido_pagina);
      //$indice_tabla

      //HACEMOS LO MISMO CON BLOQUES DE EVENTOS
      $contenido_pagina = str_replace("[do_widget id=sportspress-event-blocks-16]", "[do_widget id=sportspress-event-blocks-".$indice_calendario2."]", $contenido_pagina);

      //HACEMOS LO MISMO CON BLOQUES DE EVENTOS
      $contenido_pagina = str_replace("[do_widget id=sportspress-event-blocks-14]", "[do_widget id=sportspress-event-blocks-".$indice_calendario1."]", $contenido_pagina);

      //nombre
      //nombreFinal

      $wpdb->update( 
        'wp_9_posts', 
        array( 
          'post_content'  => $contenido_pagina,
          'post_title'    => $nombre,
          'post_name'     => $nombreFinal
        ), 
        array( 'ID' => $id_pagina_nueva ) 
      );

      //[do_widget id=sportspress-league-table-5]
    }

    //DESPUES DE TODO ESTO MANDAMOS A MODIFICAR TODOS LOS MENUS DE LA WEB
    $mis_paginas = $wpdb->get_results("SELECT * FROM wp_blogs ");
    foreach ($mis_paginas as $key){
      if($key->blog_id == "1"){
        $numero_red = "wp_";
      }else{
        $numero_red = "wp_".$key->blog_id."_";
      }

      //SELECCIONAMOS LA CANTIDAD DE ITEMS CREADOS EN EL MENU DE CADA PAGINA
      $cantidad_menu = $wpdb->get_var("SELECT COUNT(*) FROM ".$numero_red."postmeta WHERE meta_key = '_menu_item_url' ");
      $cantidad_menu++;
      
      //LUEGO DE ESTO PROCEDEMOS A CREAR ESTE ITEMS COMO MENU
      $nombre_list    = $nombre;
      $nombre_list_f  = $nombreFinal;
      //INSERTAMOS EN EL POST
      $wpdb->insert(
                $numero_red."posts",
                array(
                    'post_author'           =>  $current_user->ID,
                    'post_date'             =>  $diaYhora,
                    'post_date_gmt'         =>  $diaYhora,
                    'post_content'          =>  '',
                    'post_title'            =>  $nombre_list,
                    'post_excerpt'          =>  '',
                    'post_status'           =>  'publish',
                    'comment_status'        =>  'open',
                    'ping_status'           =>  'closed',
                    'post_password'         =>  '',
                    'post_name'             =>  $nombre_list_f,
                    'to_ping'               =>  '',
                    'pinged'                =>  '',
                    'post_modified'         =>  $diaYhora,
                    'post_modified_gmt'     =>  $diaYhora,
                    'post_content_filtered' =>  '',
                    'post_parent'           =>  0,
                    'menu_order'            =>  $cantidad_menu,
                    'post_type'             =>  'nav_menu_item',
                    'post_mime_type'        =>  '',
                    'comment_count'         =>  0
                    )
                );

      $id_menu = $wpdb->insert_id;
      $id_mega_menu = '218';

      //ID MEGA MENU
      $mega_menu = $wpdb->get_results("SELECT p.* FROM ".$numero_red."posts AS p WHERE p.ID = (SELECT t.object_id FROM ".$numero_red."term_relationships AS t WHERE t.object_id = p.ID and t.term_taxonomy_id = (SELECT te.term_taxonomy_id FROM ".$numero_red."term_taxonomy AS te WHERE te.taxonomy = 'nav_menu' )) AND p.post_title = 'RESULTADOS' ");
      foreach ($mega_menu as $keys) {
        $id_mega_menu = $keys->ID;
      }

      //CONSULTAMOS DE NUEVO EL ID DEL TERM TAXONOMI
      $taxonomia_menu = $wpdb->get_results("SELECT te.term_taxonomy_id FROM ".$numero_red."term_taxonomy AS te WHERE te.taxonomy = 'nav_menu' ");
      foreach ($taxonomia_menu as $valores) {
        $term_taxonomy_id = $valores->term_taxonomy_id;
      }
      
      $url_nuevo = "https://".$key->domain.$key->path."?page_id=".$id_menu;
      $url_nuevo = get_site_url()."?page_id=".$id_pagina_nueva;

      //echo "<h1>{$url_nuevo}</h1>";

      //MODIFICAMOS EL PORDUCTO CREADO PARA PODER AGREGARLE LA RUTA SEGUN SU ID
      $wpdb->update( 
                  $numero_red."posts", 
                  array( 
                    'guid' => $url_nuevo
                  ), 
                  array( 
                    'ID' => $id_menu
                    ) 
                );

      //YA CON EL ITEM CREADO PROCEDEMOS A AGREGARLO EN POSTMETA
      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu_item_type',
                  'meta_value'        =>  'custom'
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu_item_menu_item_parent',
                  'meta_value'        =>  $id_mega_menu
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu_item_object_id',
                  'meta_value'        =>  $id_menu
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu_item_object',
                  'meta_value'        =>  'custom'
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu_item_target',
                  'meta_value'        =>  ''
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu_item_classes',
                  'meta_value'        =>  'a:1:{i:0;s:0:"";}'
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu_item_xfn',
                  'meta_value'        =>  ''
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu_item_url',
                  'meta_value'        =>  $url_nuevo
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu-item-avia-megamenu',
                  'meta_value'        =>  'active'
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu-item-avia-division',
                  'meta_value'        =>  ''
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu-item-avia-textarea',
                  'meta_value'        =>  ''
                )
              );

      $wpdb->insert(
              $numero_red."postmeta",
              array(
                  'post_id'           =>  $id_menu,
                  'meta_key'          =>  '_menu-item-avia-style',
                  'meta_value'        =>  ''
                )
              );


      //ADEMAS DE LAS CONFIGURACIONES BASICAS DEBEMOS RELACIONAR ESTE ENLACE CON EL 8 QUE SIGNIFICA MENU (nav_menu)
      $wpdb->insert(
              $numero_red."term_relationships",
              array(
                  'object_id'           =>  $id_menu,
                  'term_taxonomy_id'    =>  $term_taxonomy_id,
                  'term_order'          =>  '0'
                )
              );
    }
  }
?>

  <h1>Crear nueva liga:</h1>
  <?php echo $url_web_creada; ?>
  <div class="formularios-approval quarterWidth formulario_generador_liga">
    <form method="post" action="">
      <input type="text" name="crear_liga_nueva" value="1" required style="display: none;" />

      <!-- TITULO DEL FORMULARIO -->
      <div class="form-group">
        <label for="inputTitle">Formulario de inscripcion</label>
        <!--<input type="text" class="form-control" name="titulo" id="inputTitle" placeholder="Enter Title">-->
        <select class="form-control" name="formulario_inscripcion">
          <option value="">Seleccione</option>
        <?php
          $equipos = $wpdb->get_results("SELECT * FROM wp_rg_form WHERE is_active = '1' ");

          foreach ($equipos as $key) {
            echo "<option value='{$key->id}'>{$key->title}</option>";
          }
        ?>
        </select>
        <small id="title" class="form-text text-muted">Selecciona el formulario de inscripcion deceado.</small>
      </div>
      <!-- TITULO DE LA LIGA NUEVA -->
      <div class="form-group">
        <label for="inputTitle">Titulo</label>
        <input type="text" class="form-control" name="titulo_liga" id="inputTitle" placeholder="Enter Title">
        <small id="title" class="form-text text-muted">Titulo de la liga.</small>
      </div>

      <!-- TEXTO DEL CERTIFICADP -->
      <?php
        /*
      ?>
      <div class="form-group">
        <label for="inputTitle">Liga</label>
        <select class="form-control">
          <option value="">Seleccione</option>
        <?php
          $equipos = $wpdb->get_results("SELECT * FROM wp_blogs ");

          foreach ($equipos as $key) {
            echo "<option value='{$key->blog_id}'>{$key->path}</option>";
          }
        ?>
        </select>
        <small id="title" class="form-text text-muted">Seccion de la pagina en donde crearemos todo.</small>
      </div>
      <?php
        */
      ?>

      <!-- TEXTO DEL CERTIFICADP -->
      <div class="form-group sp_season">
        <label for="inputTitle">Año</label>
        <?php  /*echo "<h1>SELECT t.* FROM wp_9_terms AS t WHERE t.term_id = (SELECT s.term_id FROM wp_9_term_taxonomy AS s WHERE s.taxonomy = 'sp_season'</h1>";*/ ?>
        <!--<input type="text" class="form-control" name="titulo" id="inputTitle" placeholder="Enter Title">-->
        <select class="form-control" name="sp_season">
          <option value="">Seleccione</option>
        <?php
          $equipos = $wpdb->get_results("SELECT s.term_id AS term_id, (SELECT t.name FROM wp_9_terms AS t WHERE t.term_id = s.term_id) AS name FROM wp_9_term_taxonomy AS s WHERE s.taxonomy = 'sp_season' ");

          foreach ($equipos as $key) {
            echo "<option value='{$key->term_id}'>{$key->name}</option>";
          }
        ?>
          <option value="otro">Otro</option>
        </select>
        <input type="hidden" class="form-control" name="sp_season_nuevo" placeholder="Año nuevo" />
        <small id="title" class="form-text text-muted">Año en que se hace la liga.</small>
      </div>

      <!-- IMAGEN DE LA FIRMA DEL INSTRUCTOR -->
      <div class="form-group sp_league">
        <label for="inputTitle">Competicion</label>
        <!--<input type="text" class="form-control" name="titulo" id="inputTitle" placeholder="Enter Title">-->
        <select class="form-control" name="sp_league">
          <option value="">Seleccione</option>
       <?php
          $equipos = $wpdb->get_results("SELECT s.term_id AS term_id, (SELECT t.name FROM wp_9_terms AS t WHERE t.term_id = s.term_id) AS name FROM wp_9_term_taxonomy AS s WHERE s.taxonomy = 'sp_league' ");

          foreach ($equipos as $key) {
            echo "<option value='{$key->term_id}'>{$key->name}</option>";
          }
        ?>
          <option value="otro">Otro</option>
        </select>
        <input type="hidden" class="form-control" name="sp_league_nuevo" placeholder="Competicion nueva" />
        <small id="title" class="form-text text-muted">Competicion (Apertura - Cierre).</small>
      </div>

      <!-- COMPETICION NUEVA -->
      <div class="form-group nueva_categoria">
        <label for="inputTitle">Competicion</label>
        <!--<input type="text" class="form-control" name="titulo" id="inputTitle" placeholder="Enter Title">-->
        <select class="form-control" name="nueva_categoria">
          <option value="">Seleccione</option>
       <?php
          $equipos = $wpdb->get_results("SELECT s.term_id AS term_id, (SELECT t.name FROM wp_9_terms AS t WHERE t.term_id = s.term_id) AS name FROM wp_9_term_taxonomy AS s WHERE s.taxonomy = 'sp_league' ");

          foreach ($equipos as $key) {
            echo "<option value='{$key->term_id}'>{$key->name}</option>";
          }
        ?>
          <option value="otro">Otro</option>
        </select>
        <input type="hidden" class="form-control" name="sp_categoria_nueva" placeholder="Categoria nueva" />
        <small id="title" class="form-text text-muted">Competicion (Masculino A - Masculino B).</small>
      </div>


      <!-- SECCION PARA SELECCIONAR LOS EQUIPOS -->
      <div class="form-group">
        <table class="form-table">
          <tbody>
            <tr>
            <th>Selecciona equipos:</th>
              <td>
                <ul>
                <?php
                  $equipos = $wpdb->get_results("SELECT p.* FROM {$wpdb->prefix}posts AS p WHERE p.post_type = 'sp_team' ");

                  foreach ($equipos as $key) {
                ?>
                    <li>
                      <label>
                        <input type="checkbox" name="total_equipos[]" value="<?php echo $key->ID; ?>">
                        <?php echo $key->post_title; ?>             
                      </label>
                    </li>
                <?php
                    // /echo "<option value='{$key->ID}'>{$key->post_title}</option>";
                  }
                ?>
                
                </ul>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="clear"></div>
        
        <?php submit_button("Crear Liga"); ?>
    </form>
  </div>
<?php
}

function generador_de_ligas33(){

}