jQuery(function(){
	console.log("pasamos por aqui");
	jQuery(".formulario_generador_liga select").change(function(){
		var valor = jQuery(this).val();
		if(valor == "otro"){
			var atributo = jQuery(this).attr("name");

			jQuery("."+atributo+" select").hide();
			jQuery("."+atributo+" input").attr("type", "text");
		}
	});
});

//BOTON ACCION CLICK ENVIAR
/*
jQuery(function(){
	jQuery(".creador_de_ligas_j input[type='submit']").click(function(){
		//alert('click');
		var formulario_inscripcion 	= jQuery("select[name='formulario_inscripcion']").val();
		var titulo_liga 			= jQuery("input[name='titulo_liga']").val();
		var sp_season 				= jQuery("select[name='sp_season']").val();
		var sp_season_nuevo 		= jQuery("input[name='sp_season_nuevo']").val();
		var sp_league 				= jQuery("select[name='sp_league']").val();
		var sp_league_nuevo 		= jQuery("input[name='sp_league_nuevo']").val();
		var nueva_categoria 		= jQuery("select[name='nueva_categoria']").val();
		var sp_categoria_nueva 		= jQuery("input[name='sp_categoria_nueva']").val();
		var total_equipos 			= jQuery("input[name='total_equipos[]']").val();
		var nueva_tabla 			= jQuery("select[name='nueva_tabla']").val();
		var nueva_lista 			= jQuery("select[name='nueva_lista']").val();
		var ultimos_juegos 			= jQuery("select[name='ultimos_juegos']").val();
		var proximos_juegos 		= jQuery("select[name='proximos_juegos']").val();

		total_equipos = [];
		jQuery("input[name='total_equipos[]']:checked").each(function(){
			total_equipos.push(jQuery(this).val());
		});
		//alert(total_equipos);

		//enviamos estos datos
		var data = {
	        'action':           		'my_action_ligasss',
	        'acciones':           		'ligas',
	        'formulario_inscripcion': 	formulario_inscripcion,
			'titulo_liga': 				titulo_liga,
			'sp_season': 				sp_season,
			'sp_season_nuevo': 			sp_season_nuevo,
			'sp_league': 				sp_league,
			'sp_league_nuevo': 			sp_league_nuevo,
			'nueva_categoria': 			nueva_categoria,
			'sp_categoria_nueva': 		sp_categoria_nueva,
			'total_equipos': 			total_equipos,
			'nueva_tabla': 				nueva_tabla,
			'nueva_lista': 				nueva_lista,
			'ultimos_juegos': 			ultimos_juegos,
			'proximos_juegos': 			proximos_juegos
	    };

		jQuery.post(ajaxurl, data, function(response){
	        console.log(response);
	        recorrer_todos_equipos(response, 1);
	    });

	});
});
*/


//SECCION QUE RECIBE LO QUE SE ENVIA DE LAS LIGAS
function recorrer_todos_equipos(variables, posicion = "1"){
    //console.log(variables);
    var variables_f = [];

    //total_equipos
    //sp_season
    //sp_league
    //nueva_categoria
    //competiciones

    obj = variables;
	
	obj.equipos.map(equipos => {
    	//alert(equipos);
    	console.log(equipos);
    });

    console.log("Esta es la sp_season->"+obj.sp_season);
    console.log("Esta es la sp_league->"+obj.sp_league);
    console.log("Esta es la nueva_categoria->"+obj.nueva_categoria);
    console.log("Esta es la competiciones->"+obj.competiciones);

    var total_equipos 			= obj.equipos;
    var sp_season				= obj.sp_season;
    var sp_league				= obj.sp_league;
    var nueva_categoria			= obj.nueva_categoria;
    var competiciones			= obj.competiciones;
    var formulario_inscripcion 	= obj.formulario_inscripcion;


    var equipo = "";
    var cual = 0;

    for(var i = 0; i <= total_equipos.length; i++){
    	cual++;
        if(cual == posicion){
        	equipo = total_equipos[i];
        }
    }

    //
    var data = {
        'action':           		'my_action_ligasss',
        'acciones': 				'equipos',
        'total_equipos':    		equipo,
        'sp_season':        		sp_season,
        'sp_league':        		sp_league,
        'nueva_categoria':  		nueva_categoria,
        'competiciones':    		competiciones,
        'formulario_inscripcion': 	formulario_inscripcion
    };
    jQuery.post(ajaxurl, data, function(response){
        console.log('Este pasa 11: ('+posicion+')');
		//jQuery(".categorias").html(resultado);

		if(posicion <= total_equipos.length){
            posicion++;

            recorrer_todos_equipos(variables, posicion);
        }
        console.log(response);

        jQuery(".respuesta_solicitud").html("Equipo agregado: <strong>"+response+"</strong>");
    });


    /*
    var href = myScript.pluginsUrl + '/genera-ligas/';
    jQuery.ajax({
		data: data,
		type: "POST",
		url: href+"movimiento_ajax.php",
		beforeSend: function(){

		},
		success: function(resultado){
			console.log('Este pasa 11: ('+posicion+')');
			//jQuery(".categorias").html(resultado);

			if(posicion <= total_equipos.length){
	            posicion++;

	            recorrer_todos_equipos(variables, posicion);
	        }
	        console.log(resultado);
		}
	});
	*/

	/*
    jQuery.post(obj.ajax_url, data, function(response){
        //alert('Got this from the server: ' + response);
        if(posicion <= total_equipos.length){
            posicion++;

            recorrer_todos_equipos(variables, posicion);
        }
        console.log(response);

    });
    */

}

//FUNCTION DE PRUEBA
function pruebas_ht(variables, posicion = 1){

	obj = variables;

	obj.equipos.map(equipos => {
    	alert(equipos);
    });

}