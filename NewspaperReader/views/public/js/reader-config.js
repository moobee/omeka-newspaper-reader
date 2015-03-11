/** BMN Newspaper Reader - JS - Read newspaper articles and interact with them
*   File reader-config.js : global variables declaration 
*   Author : Valentin Kiffer
*   Modifications : 1.0 - 08/01/2014 - Rotation, zoom, move variables - VKIFFER
*                   1.1 - 14/01/2014 - Thumbnail, viewport variables - VKIFFER 
*                   1.2 - 15/01/2014 - Contrast and brightness variables - VKIFFER
*                   1.3 - 16/01/2014 - Cursor modes variables - VKIFFER
*                   1.4 - 03/02/2014 - Switch image/text mode variables - VKIFFER
*   Version : 1.4
*/

/** Variables declaration
*   Variables used for the rotation and the configuration
*/

var $  = jQuery;

var divCanvas,
  	context,
 	image,
 	imageData, 
 	imageDrawn;

var INITIAL_SCALE = 0.60,
	INITIAL_BRIGHTNESS = 1,
	INITIAL_CONTRAST = 1;

var ROTATION_TO_THE_RIGHT = 1,
	ROTATION_TO_THE_LEFT = 2,
	ROTATION_ANGLE_IN_DEGREES = 90,
	VERTICAL = 1,
	HORIZONTAL = 2,
	fullRotationsMade = 0;

var startX,
	startY,
	savedX,
	savedY,
	savedWidth,
	savedHeight;

var canvasWidth,
	canvasHeight;

//utilisée pour centrer l'image au chargement de la liseuse (désactivé pour l'instant)
var imageOriginX = 0;

var X_ORIGIN = 0,
	Y_ORIGIN = 0,
	xMotionLim1,
    xMotionLim2,
    yMotionLim1,
    yMotionLim2;

var scaleX, scaleY;

var viewportX,
    viewportY,
    viewportW,
    viewportH;

var thumbnailWidth,
	thumbnailHeight,
	thumbnailsToDisplay,
	thumbnailsIndex = 1;

//Objet jquery.animate utilisé pour le timer des outils
var toolTimerAnimations = [];

var mouseDown = false,
	actionDone = false;

var cursorMode = 1,   // cursor modes : 1 - Move 2 - Zoom 3 - Brightness 4 - Contrast  
	MOVE_MODE = 1,
	ZOOM_MODE = 2,
	BRIGHTNESS_MODE = 3,
	CONTRAST_MODE = 4;

var viewMode = 1,	 // view modes : 1 - Image 2 - Text  
	IMAGE_MODE = 1,
	TEXT_MODE = 2;

var IMAGE_MODE_TEXT = "image",
	TEXT_MODE_TEXT = "texte";

var currentView = 1,
	currentViewSrc,
	viewsCount;

var BORDER = 200,
	VIEWPORT_BORDER = 2;

var ZOOM_IN = 1,
	ZOOM_OUT = 2,
	ZOOM_PROGRESS = 100,
	ZOOM_DELTA = 0.1,
	ZOOM_MINI = 0.30,
	ZOOM_MAXI = 1.50,
	ZOOM_DIVIDING_FACTOR = 10;

var BRIGHTNESS_MINI = -0.5,
	BRIGHTNESS_MAXI = 0.5,
	BRIGHTNESS_DIVIDING_FACTOR = 500;

var CONTRAST_MINI =	-50,
	CONTRAST_MAXI =  50,
	CONTRAST_DIVIDING_FACTOR = 10;

/** Délai d'utilisation d'un outil en ms */
var TOOL_DELAY = 1200;

var searchText='';
var matchedWords = null;

var xhr = null;