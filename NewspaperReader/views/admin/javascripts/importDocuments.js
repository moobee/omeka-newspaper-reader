
$(function() {

	//Depliage de l'historique
	$("[data-js-action=expand-history]").on("click", function() {

		$this = $(this);
		$history = $this.parent().find(".history-lines");

		if($history.is(":visible")) {
			$history.slideUp(200);
			$this.html("+");
		}
		else {
			$history.slideDown(200);
			$this.html("-");
		}
	});

	//Depliage de l'historique
	$("[data-js-action=expand-all-histories]").on("click", function() {

		
		if($(this).attr("data-unfold") === "on") {
			$(".history-lines").slideUp(200);
			$("[data-js-action=expand-history]").html("+");
			$(this).attr("data-unfold", "off").html("Déplier tout l'historique");
		}
		else {
			$(".history-lines").slideDown(200);
			$("[data-js-action=expand-history]").html("-");
			$(this).attr("data-unfold", "on").html("Replier tout l'historique");;
		}
	});

	//Par défaut, on déplie le premier historique
	$("[data-js-action=expand-history]").first().click();

	//Traitement : submit du formulaire
	//Entré : Pas de paramètre
	//Sortie : Pas de paramètre

	 error = false;
	 nbImportItem = 0;

	$('#form-import-files').on("submit", function(event) { 

	    event.preventDefault();

	    opts = {
	      lines: 13,
	      length: 7,
	      width: 3,
	      radius: 8,
	      corners: 1,
	      rotate: 0,
	      direction: 1,
	      color: '#FFF',
	      speed: 1,
	      trail: 60,
	      shadow: false,
	      hwaccel: false,
	      className: 'spinner',
	      zIndex: 2e9,
	      top: '50%',
	      left: '17em'
	    };

	    $('.field').hide();
	    $('#form-import-files').hide();
	    $('#primary').append('<div id="error"></div>');
	    $('#error').append('<div id="import-en-cours"><p>Import en cours :  </p></div>')
	    clearInterval(intervalImportFolder);

	    //tableau d'option pour ajax submit
		var options = { 
			dataType: "json",
		    success: function(data) {
		    	documentData = data['csvContent'];

		    	spinner = new Spinner(opts).spin($('#import-en-cours').get(0));

		    	if( typeof data["error"] !== "undefined"){
		    		for (var key in data['error']){
		    		   
		    		   $('#primary').append('<p class="red explanation">'+data['error'][key]+'</p>');
		    		   spinner.stop();

		    		}
		    	}else{
		    		// console.log(data["nameCsv"]);
		    		if( typeof data["nameCsv"] !== "undefined"){
		    			// console.log(data["nameCsv"]);
		    			fileCsv = data["nameCsv"];
		    			importFile(0);
		    		}
		    	}
		    }
		}; 

		//Récupération des champs du formulaire pour l'import
		fieldDelimiterForm = $('#field-delimiter').val();
		newCompanyForm = $('#new-company').val();
		selectCompanyForm = $('#select-company').val();
		selectCollectionForm = $('#select-collection').val();

		if($('#documents-are-public').prop("checked") === true){

			documentsArePublicForm = 1;

		}else{

			documentsArePublicForm = 0;

		}

		if($('#documents-are-featured').prop("checked") === true){

			documentsAreFeaturedForm = 1;

		}else{

			documentsAreFeaturedForm = 0;

		}

	    // submit the form 
	    $(this).ajaxSubmit(options); 
	    $('#error').append("<h2 class='startImport'>Début de l'import</h2>");
	});
});

/*
*Fonction d'import d'un fascicule
*Entré : tableau à 2 dimenssion issu du csv
*Sortie : pas de valeur
*/
function importFile(i){
	if(documentData.length !== i){
		

		//Import d'un fascule
		$.ajax({
			url : baseUrl+"/newspaper-reader/index/import-files",
			type : "POST",
			dataType : "json",
			data: {
				fieldDelimiter: fieldDelimiterForm,
				newCompany: newCompanyForm,
				selectCompany: selectCompanyForm,
				selectCollection: selectCollectionForm,
				documentsArePublic: documentsArePublicForm,
				documentsAreFeatured: documentsAreFeaturedForm,
				itemToImport: documentData[i],
				nameCsv : fileCsv
			}
		}).done(function(data){
			
			$('#import-en-cours p').replaceWith('<p>Import en cours : ' + (i + 1) + '/' + (documentData.length) + '</p>');
			
			//spinner = new Spinner(opts).spin($('#import-en-cours'));
			
			$('#error').append('<p>'+documentData[i]['dc:identifier']+' : '+data["status"]+'</p>');
			
			if( typeof data["error"] !== "undefined"){
				$('#error').append('<p class="alinea red explanation">'+data['error']+'</p>');
				error = true;
			}
			else {
				//Si pas d'erreur, on incrémente le nombre de fascicules importés
				nbImportItem++;
			}

			for (var key in data["views"]){
				
				if( typeof data["views"][key]['error'] !== "undefined"){
			   		$('#error').append('<p class="alinea orange explanation">' + data['views'][key]['error'] + '</p>');
			   	}else{
			   		if( typeof data["views"][key]['success'] !== "undefined"){
			   			$('#error').append('<p class="alinea orange explanation">' + data['views'][key]['success'] + '</p>');
			   		}
				
					
				}

			}
			i++;
			importFile(i); 
		});

	}else{
		$.ajax({
			url : baseUrl+"/newspaper-reader/index/end-import?error="+error+"&nbImportItem="+nbImportItem,
			type : "GET",
			dataType : "json"
		}).done(function(data){
			console.log('end of import');
		});

		$('#import-en-cours p').replaceWith('<p>Import terminé</p>');
		$('#error').append('<h2>L\'ensemble du fichier csv a été traité</h2>');
		spinner.stop();

	}
}

/*
* 
*/
function FormatNumberLength(num, length) {
    var r = "" + num;
    while (r.length < length) {
        r = "0" + r;
    }
    return r;
}

// function importFile2(){
// Importajax.done(function(data){
// 	documentData.splice(0,1);
// 	$('#primary').append(data);
// 	importFile(documentData); 
// });
// }