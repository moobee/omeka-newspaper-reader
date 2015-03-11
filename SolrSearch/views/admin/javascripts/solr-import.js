(function($) {
'use strict';

var INDEX_CHECKING_INTERVAL = 4000;
var intervalImport = null;
var isIndexing = false;

$(document).ready(function(){
	$('#flash').hide();
	checkIndexStatus();
    intervalImport = setInterval(checkIndexStatus, INDEX_CHECKING_INTERVAL);
});

/**
 * Récupère le status de l'indexation de Solr
 * et met à jour la vue.
 */
function checkIndexStatus () {
	$.getJSON('get-index-status', function(data) {

		switch (data.status) {
			case 'busy' :
				// Début de l'indexation
				if (!isIndexing) {
					addIndexCount();
					isIndexing = true;
				}
				// Si un nombre de fascicules est récupéré, on l'indique
				if (data.statusMessages['Total Documents Processed']) {
					var currentCount = parseInt(data.statusMessages['Total Documents Processed'])
					updateIndexCount(currentCount);
				}
				break;
			case 'idle' :
				// FIn de l'indexation
				if (isIndexing) {
					var totalCount = null;
					if (data.statusMessages['Total Documents Processed']) {
						totalCount = parseInt(data.statusMessages['Total Documents Processed']);
					}
					endIndexCount(totalCount);
					isIndexing = false;
				}
				break;
		}
	});



}

/**
 * Ajoute l'indicateur de progression de l'indexation.
 */
function addIndexCount() {

	var $loader = $('#index-en-cours');

	if ($loader.length === 0) {
		$loader = $('<div>', {
			style: 'display: none;',
			id: 'index-en-cours',
		});

		$('body').append($loader);
	}

	$loader
		.html('\
			<span class="loading-label">Indexation en cours...</span>\
			<span class="indexing-progress"></span>\
		')
		.fadeIn()
	;

    var opts = {
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

	var spinner = new Spinner(opts).spin($loader.get(0));

	$('#flash')
		.fadeIn()
		.find('li')
			.removeClass('success')
			.addClass('info')
			.html('Indexation en cours...')
	;

	// On masque le bouton d'indexation
	$('#submit').hide();
}

function updateIndexCount(newCount) {
	$('#index-en-cours .loading-label').html(newCount + ' fascicules indexés...');
	setProgress(newCount, totalDocumentCount);
}

function endIndexCount(totalCount) {

	var endMessage = totalCount === null ?
		'Indexation terminée !' :
		'Indexation terminée : ' + totalCount + ' fascicules indexés !';

	$('#index-en-cours').fadeOut();
	$('#flash li')
		.removeClass('info')
		.addClass('success')
		.html(endMessage)
	;

	// On réaffiche le bouton d'indexation
	$('#submit').fadeIn();

}

function setProgress(currentCount, totalCount) {
	var maxWidth = $('#index-en-cours').width();
	var currentWith = Math.min((currentCount / totalCount), 1) * maxWidth;
	$('#index-en-cours .indexing-progress').css('width', currentWith + 'px');
}

})(jQuery);
