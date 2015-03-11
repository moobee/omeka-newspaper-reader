/** BMN Newspaper Reader - JS - Read newspaper articles and interact with them
*   File newspaper-reader-import.js : import files from the import folder
*   Author : Valentin Kiffer
*   Modifications : 1.0 - 06/03/2014 - Checking the content of the import folder  - VKIFFER
* 					1.1 - 07/03/2014 - Form verifications, displaying form errors - VKIFFER
* 					1.2 - 19/03/2014 - Removing imports - VKIFFER
*
*   Version : 1.2
*/

/** Member function
*   Event handling function : document ready -> calling checking and initializing functions
*/

$(document).ready(function(){
	// Checks the content of the import folder
	getImportFolderContent();

    // Checks the content of the import folder at every CHECKING_INTERVAL
    intervalImportFolder = setInterval(getImportFolderContent, CHECKING_INTERVAL);

    // Initiates the upload form
    initForm();

    // Checks if there are errors in the form
    verifForm();
});

/** Member function
*   Display function : retrieves the content of the import folder and displays it
*/
function getImportFolderContent () {
	// Page different from history
	if(document.URL.indexOf("/index/history") < 0 && document.URL.indexOf("/index/import-files") === -1)
	{
		// Ajax get call to importFolderContentAction() from IndexController
	    $.ajax({
	            type: 'GET',
	            url: baseUrl + '/newspaper-reader/index/import-folder-content',
	            data: {
            },
            success: function (data) {
            	// Ajax response containing an error
            	if(data.indexOf("ERREUR") >= 0)
            	{
            		// Set the text color to red
            		$('p#content-folder').addClass('red');
            		$("p#recap-content-folder").addClass('red');

            		// Retrieves the error
            		data = data.replace('"', '');
            		data = data.replace('t"', 't')

            		// Sets the text to be displayed
            		$('p#content-folder').html(data);
            		$("p#recap-content-folder").html(data);
            	}
            	// No error in the Ajax response
            	else
            	{
            		// Set the text color to black
            		$('p#content-folder').removeClass('red');
            		$('p#recap-content-folder').removeClass('red');

            		// Retrieves the data and converts it to JSON
            		data = data.split('&');
            		var filesTypes = $.parseJSON(data[0]),
            		files = $.parseJSON(data[1]);

            		// Sets the text to be displayed
            		var contentFolder = '<span>'+files['number']+' fichiers dont :</span>';
            			contentFolder += '<span>- '+filesTypes['.gif']+' gif</span>';
            			contentFolder += '<span>- '+filesTypes['.jpeg']+' jpeg</span>';
            			contentFolder += '<span>- '+filesTypes['.jpg']+' jpg</span>';
            			contentFolder += '<span>- '+filesTypes['.pdf']+' pdf</span>';
            			contentFolder += '<span>- '+filesTypes['.png']+' png</span>';
            			contentFolder += '<span>- '+files['alto']+' alto</span>';

            		var recapContentFolder = files['to_be_imported']+' fichiers sont valides pour être importés.'
            		$('p#content-folder').html(contentFolder);
            		$("p#recap-content-folder").html(recapContentFolder);
            	}
            }
        });
	}
}

/** Member function
*   Initialization function : init the initial display of the form
*/
function initForm () {
	// Set the info messages to their initial values
	fileInfoMsg = 'Aucun fichier sélectionné',
	fieldDelimiterInfoMsg = 'Séparateur de champ correct',
	companyInfoMsg = 'Aucun titre sélectionné',
	collectionInfoMsg = 'Aucune collection sélectionnée';

	// Displays the initial info messages in the form
    displayFormInfos();

    /** Member function
    *   Event handling function : click on a link -> prevents redirection
    */
    $("a[data-js]").click(function (event) {
        event.preventDefault();
    });

    /** Member function
    *   Event handling function : when the form is submitted -> parses the csv file and import the files
    */
    /*$('#form-import-files').submit(function() {
    	// Submit the form
        $(this).ajaxSubmit();
    });*/

    /** Member function
    *   Event handling function : click on a delete import link -> deletes the records into the DB
    */
    $("a[class='delete-import-link']").on('click', function (){
    	// Retrieves the file identifier
    	var data = $(this).attr('data-js');

    	// Ajax get call to deleteImportedFile() from IndexController
        $.ajax({
                type: 'GET',
                url: '../index/delete-imported-file',
                data: {
               		identifier: data
                },
                success: function(data){
                	alert(data);
                }
            });
    });
}

/** Member function
*   Checking function : checks if there are any errors in the form and displays them
*/
function verifForm(){

	// Checks if the file to be uploaded is correct
	verifFormFile();

	// Checks if the text delimiter is correct
	verifFormDelimiters();

	// Checks if the chosen company and title are correct
	verifFormCompanyAndTitle();

	// Checks if options are selected or not
	verifFormOptions();
}

/** Member function
*   Checking function : checks if there are any errors with the file to be uploaded and displays them
*/
function verifFormFile () {
	// When a file is choosen
	$('#user-file').on('change', function() {
		// Changes the selected flag
		fileSelected = true;

		// Size of the file
		var inputValue = $('#user-file').val(),
		    file = document.getElementById('user-file').files[0];

		// Good file extension & file size
		if(inputValue.indexOf(FILE_EXTENSION) >= 0 && file.size < FILE_MAX_SIZE)
		{
			// Changes the info message and flag
			fileInfoMsg = 'Taille et format du fichier correct';
			fileError = false;
		}

		// Wrong file extension or file size
		else
		{
			// The file is too bulky
			if(file.size > FILE_MAX_SIZE)
			{
				// Changes the info message and flag
				fileInfoMsg = 'Fichier trop volumineux';
				fileError = true;
			}

			// The file extension is wrong
			else
			{
				// Changes the info message and flag
				fileInfoMsg = 'Format de fichier incorrect';
				fileError = true;
			}
		}

		// Displays the info messages in the form
		displayFormInfos();
	});
}

/** Member function
*   Checking function : checks if there are any errors with the delimiters and displays them
*/
function verifFormDelimiters () {


	// When a field delimiter is chosen
	$('#field-delimiter').change(function() {
		// Size of the chosen delimiter
		var inputValue = $('#field-delimiter').val();

		// The size of the chosen delimiter is wrong
		if(inputValue.length > CSV_DELIMITERS_MAX_SIZE)
		{
			// Changes the info message and flag
			fieldDelimiterError = true;
			fieldDelimiterInfoMsg = 'Le séparateur de champ doit être un caractère unique';
		}

		else
		{
			// Browses the allowed delimiters
			for(var i = 0; i < CSV_ALLOWED_FIELD_DELIMITERS.length; i++)
			{
				// The chosen delimiter is allowed
				if(inputValue === CSV_ALLOWED_FIELD_DELIMITERS[i])
				{
					// Changes the info message and flag and breaks the loop
					fieldDelimiterError = false;
					fieldDelimiterInfoMsg = 'Séparateur de champ correct';
					break;
				}

				else
				{
					// Changes the info message and flag
					fieldDelimiterError = true;
					fieldDelimiterInfoMsg = 'Le séparateur de champ peut prendre les valeurs '+CSV_ALLOWED_FIELD_DELIMITERS.join(" ");
				}
			}
		}

		displayFormInfos();
	});
}

/** Member function
*   Checking function : checks if there are any errors with the chosen company and title and displays them
*/
function verifFormCompanyAndTitle () {
	// When a company is choosen
	$('#select-company').on('change', function() {
		// Value of the chosen company
		var inputValue = $('#select-company').val();

		if(inputValue > NO_VALUE_SELECTED)
		{
			// Changes the info message and flag
			companySelected = true;
			companyInfoMsg = 'Titre correct';
		}
		else
		{
			// Changes the info message and flag
			companySelected = false;
			companyInfoMsg = 'Aucun titre sélectionné';
		}

		// Reset the new company input
		$('#new-company').val('');

		// Displays the info messages in the form
		displayFormInfos();
	});

	// When a new company is choosen
	$('#new-company').on('change', function() {
		// Value of the chosen new company
		var inputValue = $('#new-company').val();

		if(inputValue.length > NO_VALUE_SELECTED)
		{
			// Reset the select valueto its initial value
			$('#select-company').val(NO_VALUE_SELECTED);

			// Changes the info message and flag
			companySelected = true;
			companyInfoMsg = 'Titre correct';
		}
		else
		{
			// Changes the info message and flag
			companySelected = false;
			companyInfoMsg = 'Aucun titre sélectionné';
		}

		// Displays the info messages in the form
		displayFormInfos();
	});

	// When a collection is choosen
	$('#select-collection').on('change', function() {
		// Value of the chosen collection
		var inputValue = $('#select-collection').val();

		if(inputValue > NO_VALUE_SELECTED)
		{
			// Changes the info message and flag
			collectionSelected = true;
			collectionInfoMsg = 'Collection correcte';
		}
		else
		{
			// Changes the info message and flag
			collectionSelected = false;
			collectionInfoMsg = 'Aucune collection sélectionnée';
		}

		// Displays the info messages in the form
		displayFormInfos();
	});
}

/** Member function
*   Checking function : checks if there are any options chosen
*/
function verifFormOptions () {
	// When the public option has been modified
	$('#documents-are-public').on('change', function() {
		// Value of the public option
		var inputValue = $('#documents-are-public').val();

		// Inverts the flag value
		documentsArePublic = (documentsArePublic === false) ? true : false;
	});

	// When the featured option has been modified
	$('#documents-are-featured').on('change', function() {
		// Value of the featured option
		var inputValue = $('#documents-are-featured').val();

		// Inverts the flag value
		documentsAreFeatured = (documentsAreFeatured === false) ? true : false;

	});
}

/** Member function
*   Displaying function : displays the info messages in the form and if they are no errors in it displays the submit button
*/
function displayFormInfos(){
	// Error with the file to be uploaded or no file selected -> set the info message color to red
	if(fileError || !fileSelected)
	{
		$("#file-info-message").addClass('red');
	}
	// No error with the file to be uploaded -> set the info message color to black
	else
	{
		$("#file-info-message").removeClass('red');
	}

	// Error with the field delimiter -> set the info message color to red
	if(fieldDelimiterError)
	{
		$("#field-delimiter-info-message").addClass('red');
	}
	// No error with the field delimiter -> set the info message color to black
	else
	{
		$("#field-delimiter-info-message").removeClass('red');
	}

	// No company chosen -> set the info message color to red
	if(!companySelected)
	{
		$("#company-info-message").addClass('red');
	}
	// A company has been chosen -> set the info message color to black
	else
	{
		$("#company-info-message").removeClass('red');
	}

	// No collection is selected -> set the info message color to red
	if(!collectionSelected)
	{
		$("#collection-info-message").addClass('red');
	}
	// A collection is selected -> set the info message color to black
	else
	{
		$("#collection-info-message").removeClass('red');
	}

	// Displays the info messages
	$("#file-info-message").html(fileInfoMsg);
	$("#field-delimiter-info-message").html(fieldDelimiterInfoMsg);
	$("#company-info-message").html(companyInfoMsg);
	$("#collection-info-message").html(collectionInfoMsg);

	// No errors were found in the form
	if(fileSelected && !fileError && !fieldDelimiterError && companySelected && collectionSelected)
	{
		// The submit button isn't visible
		if(!$('#submit-button').is(':visible'))
		{
			// Displays the submit button
			$('#submit-button').toggle();
		}
	}

	// Errors found in the form
	else
	{
		// The submit button is visible
		if($('#submit-button').is(':visible'))
		{
			// Hides the submit button
			$('#submit-button').toggle();
		}
	}
}

