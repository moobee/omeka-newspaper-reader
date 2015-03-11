/** BMN Newspaper Reader - JS - Read newspaper articles and interact with them
*   File newspaper-reader-import-config.js : initialization of the import config variables
*   Author : Valentin Kiffer
*   Modifications : 1.0 - 06/03/2014 - Variables for the selected file and errors - VKIFFER
* 					1.1 - 07/03/2014 - Variables for the csv allowed delimiters and errors - VKIFFER
*   Version : 1.1
*/

var $  = jQuery;

// Allowed extension and maximum size of the file to be uploaded
var FILE_EXTENSION = 'csv',
	FILE_MAX_SIZE = 5000000;

// Interval between each checking of the import folder
var CHECKING_INTERVAL = 5000;

// Valid files ready to be imported
var validFiles = 0;

// Variables and flags used to determine errors in the form
var NO_VALUE_SELECTED = 0,
	fileSelected = false,
	fileError = false,
	fileInfoMsg = '',
	textDelimiterError = false,
	textDelimiterInfoMsg = '',
	metadataDelimiterError = false,
	metadataDelimiterInfoMsg = '',
	fieldDelimiterError = false,
	fieldDelimiterInfoMsg = '',
	companySelected = false,
	companyInfoMsg = '',
	collectionSelected = false,
	collectionInfoMsg = '',
	documentsArePublic = false,
	documentsAreFeatured = false;

// Maximum size of the csv file delimiters and allowed characters to be selected as delimiters
var CSV_DELIMITERS_MAX_SIZE = 1,
	CSV_ALLOWED_TEXT_DELIMITERS = new Array('"', ''),
	CSV_ALLOWED_METADATA_DELIMITER = ':',
	CSV_ALLOWED_FIELD_DELIMITERS = new Array(',', ';');