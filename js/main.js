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