/** BMN Newspaper Reader - JS - Read newspaper articles and interact with them
*   File reader.js : rotation, zoom and filters application on newspaper articles
*   Author : Valentin Kiffer
*   Modifications : 1.0 - 08/01/2014 - Rotation, zoom, move - VKIFFER
*                   1.1 - 14/01/2014 - Thumbnail, viewport - VKIFFER
*                   1.2 - 15/01/2014 - Reset, contrast and brightness filters - VKIFFER
*                   1.3 - 16/01/2014 - Alto parser js - VKIFFER
*                   1.4 - 03/02/2014 - Switch image/text mode - VKIFFER
*                   1.5 - 04/02/2014 - Navigation through images / texts, dynamic loading - VKIFFER
*                   1.6 - 05/02/2014 - Dynamic dimensions / displaying - VKIFFER
                    1.7 - 06/02/2014 - Thumbnails navigation, viewport corresponding to the current view - VKIFFER
                    1.8 - 07/02/2014 - Page layout, timeout on tools - VKIFFER
                    1.9 - 13/02/2014 - Progressbar, zoom with mouse postion - VKIFFER
                    2.0 - 17/02/2014 -
*   Version : 2.0
*/


/** Data structure declaration
*   Object used to hold data for all drawn images
*/

function oImage(){
    // Image's width and height
    this.width = 0;
    this.height = 0;
    // Image's coordinates : top-left corner (x,y)
    this.x1 = 0;
    this.y1 = 0;
    // Image's coordinates : top-right corner (x)
    this.x2 = 0;
    // Image's coordinates : bottom-left corner (y)
    this.y2 = 0;
    // Image's scale, angle, brightness and contrast
    this.currentScale = INITIAL_SCALE;
    this.currentAngle = 0;
    this.currentBrightness = INITIAL_BRIGHTNESS;
    this.brightnessModif = 0;
    this.currentContrast = INITIAL_CONTRAST;
    this.contrastModif = 0;
    this.orientation = VERTICAL;
}

/** Member function
*   Event handling function : document ready -> init the canvas, the data and the layout
*/

$(document).ready(function(){

    // Variable used to hold the canvas instance
    var canvas;
    // Data and layout initialization
    init();

});

/** Member function
*   Initialization function : sets the starting data and layout
*/

function init() {

    //On prepare le loader
    pageSpinnerOpts = {
      lines: 14,
        length: 15,
        width: 4,
        radius: 17,
        corners: 0,
        rotate: 0,
        direction: 1,
        color: '#FFF',
        speed: 1,
        trail: 60,
        shadow: false,
        hwaccel: false,
        className: 'page-spinner',
        zIndex: 2e9,
        top: '50%',
        left: '50%'
    };

    var pageSpinner = new Spinner(pageSpinnerOpts).spin($('#loader-overlay').get(0));

    //Effet de hover sur les boutons
    $("img.hi-button").each(function() {

      var src = $(this).attr("src");
      var ext = "." + src.split('.').pop();
      var off = src.substr(0, src.lastIndexOf('.'));
      var on = off + "-hi";

      $(this).attr("data-hi-off", off + ext);
      $(this).attr("data-hi-on", on + ext);
      $(this).attr("data-hi-current", "off");

      $(this)
        .on("mouseenter", function() {

          $(this).attr("data-hi-current", "on");
          $(this).attr("src", $(this).attr("data-hi-on"));
        })
        .on("mouseout", function() {

          $(this).attr("data-hi-current", "off");
          $(this).attr("src", $(this).attr("data-hi-off"));
        });

    });

    // Hides the text mode windows (summary, navigation and textview)
    $('#div-summary').toggle();
    $("#div-text").toggle();
    $("#div-text-pagination").toggle();
    $('#img-mode').toggle();

    // Div used to change the cursor icon
    divCanvas = document.getElementById("div-canvas");
    changeCursorMode(MOVE_MODE);

    // Assignation of the variables used to hold the canvas instance and its context
    if(typeof G_vmlCanvasManager !== "undefined") {
        var canvas = $("#canvas");
        // console.log(document);
        canvas = G_vmlCanvasManager.initElement(canvas);
        context = canvas.getContext("2d");
    }
    else {
        canvas = $("#canvas").get(0);

        context = canvas.getContext("2d");
    }

    // Layout variables
    var headerHeight = $('#div-header').outerHeight(),
        canvasMarginLeft = parseInt($('#div-canvas').css('margin-left').replace('px', '')),
        canvasMarginRight = parseInt($('#div-canvas').css('margin-right').replace('px', '')),
        canvasMarginBottom = parseInt($('#div-canvas').css('margin-bottom').replace('px', '')),
        canvasMarginTop = parseInt($('#div-canvas').css('margin-top').replace('px', '')),
        toolsMarginLeft = parseInt($('#tools-wrapper').css('margin-left').replace('px', '')),
        toolsMarginRight = parseInt($('#tools-wrapper').css('margin-right').replace('px', '')),
        toolsWidth = $('#tools-wrapper').outerWidth(),
        summaryMarginLeft = parseInt($('#div-summary').css('margin-left').replace('px', '')),
        summaryWidth = $('#div-summary').outerWidth(),
        thumbnailsHeight = $('#div-thumbnails').outerHeight(),
        bodyWidth = $(window).width(),
        bodyHeight = $(window).height();

    // Thumbnails size
    thumbnailWidth = $('.thumbnail').width();
    thumbnailHeight = $('.thumbnail').height();

    // Canvas size (on retire 20px pour l'éventuelle barre de scroll)
    canvasWidth = bodyWidth - canvasMarginLeft - canvasMarginRight;
    // canvasWidth = bodyWidth - toolsWidth - canvasMarginLeft - canvasMarginRight - toolsMarginLeft - toolsMarginRight - 20;
    canvasHeight = bodyHeight - thumbnailsHeight - headerHeight - canvasMarginBottom - canvasMarginTop;

    // Sets the size of the div containing the canvas
    $('#div-canvas').width(canvasWidth);
    $('#div-canvas').height(canvasHeight);

    // Sets the canvas size
    $('#canvas').attr('width', canvasWidth);
    $('#canvas').attr('height', canvasHeight);

    // Sets the motion limits
    xMotionLim1 = X_ORIGIN;
    xMotionLim2 = canvasWidth;
    yMotionLim1 = Y_ORIGIN;
    yMotionLim2 = canvasHeight;

    // Sets the textview size
    // $('#div-text').css('width', canvasWidth - 20);
    $('#div-text').css('height', canvasHeight);

    // Sets the first page of the document as the image to be displayed in the canvas
    image = new Image();

    loadCurrentViewSrc(1);

    // Adds the views' count to the displayed informations
    viewsCount = $('.thumbnail').length;

    //Calcul des vignettes à afficher
    var thumbnailsBarWidth = $("#thumbnails").width();
    var realThumbnailWidth = $('.thumbnail').first().outerWidth(true); //Largeur avec les marges

    //Nombre de vignettes max dans la barre
    var maxThumbnailCount = Math.floor(thumbnailsBarWidth / realThumbnailWidth);
    var mustScrollThumbnails = false;

    //Si besoin, on cache les vignettes en trop
    if(viewsCount > maxThumbnailCount) {
        mustScrollThumbnails = true;
        $(".thumbnail").slice(maxThumbnailCount - viewsCount).hide();
    }

    // When the image is loaded (premier chargement)
    image.onload = function ()
    {
        // Initialization of the data structure metrics
        initImageSize();

        //Ne fonctionne pas correctement, casse le déplacement (censé centrer l'image au lancement du reader)
        // imageOriginX = ((canvasWidth / 2) - (imageDrawn.width / 2)) / imageDrawn.currentScale;

        // Draws it on the canvas
        drawImage();

        // Sets the viewport's coordinates and draws it
        viewportX = -imageDrawn.x1 * scaleX + VIEWPORT_BORDER;
        viewportY = -imageDrawn.y1 * scaleY + VIEWPORT_BORDER;
        viewportW = (canvasWidth * scaleX);
        viewportH = (canvasHeight * scaleY);
        drawViewPort();
        $("#loader-overlay").hide();


        // SI le paramètre query est set, on recherche directement
        if(query !== undefined) {

            query = query.replace(/"/g, "");

            // Pas de recherche vide (surligne tout le document)
            if (query !== "") {
                $('#input-searchbar').val(query);
                searchText = query;
                //tableau de page objet réinitialisé
                //
                rechercheDocument(searchText, idItem, function(pageResult) {
                    // On va directement à la page de résultats
                    loadView(pageResult, true);
                    // Evite les problèmes quand la page n'est pas chargée
                    resetImage();
                });
            }
        }

    };

    // Initialization of the data structure representing the image drawn
    imageDrawn = new oImage();

    // Loads the text corresponding to the current view
    loadText();

    // Adds the viewport to the current view's thumbnail
    for(var i = 1; i < viewsCount+1; i++)
    {
        if(i == currentView)
        {
            $('#div-thumbnail-'+i).append($('#div-viewport'));
            $('#thumbnail-number-'+i).addClass('current-thumbnail');
        }
    }

    /** Member function
    *   Event handling function : click on a input -> prevents redirection
    */
    $("input[data-js-action]").click(function (event) {
        event.preventDefault();
    });

    /** Member function
    *   Event handling function : click on a link -> prevents redirection
    */
    $("a[data-js-action]").click(function (event) {
        event.preventDefault();
    });

    /** Member function
    *   Event handling function : click on rotate right icon -> rotates the image to the right
    */

    $("a[data-js-action='rotateRight']").click(function () {
        rotateImage(ROTATION_TO_THE_RIGHT);
    });

    /** Member function
    *   Event handling function : click on zoom icon -> changes the cursor mode to zoom mode
    */

    $("a[data-js-action='zoom']").on("mousedown", function () {
        changeCursorMode(ZOOM_MODE);
    });

    /** Member function
    *   Event handling function : click on square icon -> resets the cursor mode and the zoom
    */

    $("a[data-js-action='zoomFit']").click(function () {
        resetImageScale();
    });

    /** Member function
    *   Event handling function : click on cursor icon -> changes the cursor mode to move mode
    */

    $("a[data-js-action='cursor']").on("mousedown", function () {
        changeCursorMode(MOVE_MODE);
    });

    /** Member function
    *   Event handling function : click on brightness icon -> changes the cursor mode to brightness mode
    */

    $("a[data-js-action='brightness']").on("mousedown", function () {
        changeCursorMode(BRIGHTNESS_MODE);
    });

    /** Member function
    *   Event handling function : click on contrast icon -> changes the cursor mode to contrast mode
    */

    $("a[data-js-action='contrast']").on("mousedown", function () {
        changeCursorMode(CONTRAST_MODE);
    });

    /** Member function
    *   Event handling function : click on back icon -> resets the image and its data to their initial states
    */

    $("a[data-js-action='reset']").click(function () {
        resetImage();
    });

    /** Member function
    *   Event handling function : click on change mode -> switches text / image mode
    */

    $("a[data-js-action='changeMode']").click(function () {
        changeDisplayMode();
    });

    /** Member function
    *   Event handling function : click on close button -> closes the reader
    */

    $("a[data-js-action='closeReader']").click(function () {
        if($(".fancybox-inner").length === 0) {
            window.location = baseUrl+"/items/show/"+idItem
        }else{
            fancybox.close();
        }
    });

    /** Pliage / Dépliage du champ de recherche */
    $("#input-searchbar").on("focus", function() {
        $("#div-searchbar-navigation").animate({
            height: "83px"
        }, 200);
    })
    .on("blur", function() {
        $("#div-searchbar-navigation").animate({
            height: "0px"
        }, 200);
    });

    /** Member function
    *   Event handling function : click on an image / page link -> changes the document's image / text
    */

    $("div[data-js-action='changeImage'], a[data-js-action='changeText']").click(function () {
        // Removes the border on the current thumbnail
        $('#thumbnail-number-'+currentView).removeClass('current-thumbnail');
        // Retrieves the view's number and loads it
        $('#div-page-links #page-link-'+currentView).prop( "style", null );
        currentView = parseInt($(this).attr('data'));

        $('#div-page-links a').removeClass("current");
        $('#div-page-links #page-link-' + currentView).addClass("current");

        loadView();
        viewSearchText(searchText);
    });

    /** Member function
    *   Event handling function : click on the download link of the page ->  displays a download window for the current page
    */

    $("a[js-action='downloadPage']").click(function () {

        // Href and download attributes of the <a> element
        var href = $(this).attr('href'),
            download = $(this).attr('download'),
            // Location of the _ in the 2 attributes
            indexHref = href.lastIndexOf('_'),
            indexDownload = download.lastIndexOf('_'),
            hrefLength = href.length;

            // Attributes without the end of the path (_.format)
            format = href.slice(indexHref, hrefLength);
            formatStr = format.split('.');
            format = formatStr[1];
            href = href.slice(0, indexHref);
            download = download.slice(0, indexDownload);

        // Changes href and download variables with a current view smaller than 10
        if(currentView < 10)
        {
            href+='_000'+currentView+'.'+format;
            download+='_000'+currentView+'.'+format;
        }

       // Changes href and download variables with a current view smaller than 100
        else if(currentView < 100)
        {
            href+='_00'+currentView+'.'+format;
            download+='_00'+currentView+'.'+format;
        }

        // Changes href and download variables with a current view smaller than 1000
        else if(currentView < 1000)
        {
            href+='_0'+currentView+'.'+format;
            download+='_0'+currentView+'.'+format;
        }

        // Changes href and download variables with a current view greater than 1000
        else if(currentView >= 1000)
        {
            href+='_'+currentView+'.jpg';
            download+='_'+currentView+'.jpg';
        }

        // Updates the attributes of the <a> element
        $(this).attr('href', currentViewSrc);
        $(this).attr('download', download);
    });

    /** Member function
    *   Event handling function : click on previous button -> changes the visible thumbnails
    */

    $("a[data-js-action='previousImage']").click(function () {

        // Current view different from the first one
        if((currentView - 1) >= 1)
        {
            //Si on n'est pas au bout des miniatures, on fait slider la barre
            if(!$(".thumbnail:first").is(":visible")) {
                var $lastThumbnailVisible = $(".thumbnail:visible:last");
                var firstIndexVisible = parseInt($(".thumbnail:visible:first").attr("data"));
                $lastThumbnailVisible.hide();
                $(".thumbnail[data=" + (firstIndexVisible - 1) + "]").show();
            }

            // Removes the border on the current thumbnail
            $('#thumbnail-number-'+currentView).removeClass('current-thumbnail');

            // Previous view loaded
            currentView--;
            loadView();
        }
    });

    /** Member function
    *   Event handling function : click on next button -> changes the visible thumbnails
    */

    $("a[data-js-action='nextImage']").click(function () {

        // Current view different from the last one
        if((currentView + 1) <= viewsCount) {

            //Si on n'est pas au bout des miniatures, on fait slider la barre
            if(!$(".thumbnail:last").is(":visible")) {
                var $firstThumbnailVisible = $(".thumbnail:visible:first");
                var lastIndexVisible = parseInt($(".thumbnail:visible:last").attr("data"));
                $firstThumbnailVisible.hide();
                $(".thumbnail[data=" + (lastIndexVisible + 1) + "]").show();
            }


            // Removes the border on the current thumbnail
            $('#thumbnail-number-'+currentView).removeClass('current-thumbnail');

            // Next view loaded
            currentView++;
            loadView();
        }
    });

    $("a[data-js-action='firstImage']").click(function () {

        // Removes the border on the current thumbnail
        $('.thumbnail-number').removeClass('current-thumbnail');

        //On décale la liste des miniatures à gauche
        $(".thumbnail").hide();
        $(".thumbnail").slice(0, maxThumbnailCount).show();


        // Next view loaded
        currentView = 1;
        loadView();
    });

    $("a[data-js-action='lastImage']").click(function () {

        // Removes the border on the current thumbnail
        $('.thumbnail-number').removeClass('current-thumbnail');

        //On décale la liste des miniatures à droite
        $(".thumbnail").hide();
        $(".thumbnail").slice(-maxThumbnailCount).show();

        // Next view loaded
        currentView = viewsCount;
        loadView();
    });

    $("a[data-js-action='previousSearchResult']").click(function(){
        // moveImage(matchedWords[0][0].x, matchedWords[0][0].y);
    });

    $("a[data-js-action='nextSearchResult']").click(function(){
        // alert("next");
    });

    /** Member function
    *   Event handling function : click on search icon -> parses alto file to look for data
    */

    $('#searchbar').on("submit", function(event) {
        event.preventDefault();

        //tableau de page objet réinitialisé
        matchedWords=null;
        //terme de la recherche précédente réinitialisé
        searchText='';

        // $('#div-text p span').removeClass('hilight-text');
        $('.thumbnail').removeClass('hilight');
        $('.hilight-text').contents().unwrap();

        drawImage();



        // Retrieves the search text
        searchText = $('#input-searchbar').val();

        // At least one character is searched
        if(searchText != "" && searchText.length >= 3){

            if(xhr !== null){
                xhr.abort();
                spinner.stop();
            }
            rechercheDocument(searchText, idItem, function(pageResult) {
                loadView(pageResult, true);
            });
        }
    });

    /**
     * Passe l'index de la première page sur laquelle il y a des résultats
     * (ou 0 s'il n'y en a pas) au callback
     * @param  {[type]} searchText [description]
     * @param  {[type]} idItem     [description]
     * @return {[type]}            [description]
     */
    function rechercheDocument(searchText, idItem, callback){

        searchText = searchText.toLowerCase().trim();

        opts = {
          lines: 13,
          length: 4,
          width: 2,
          radius: 5,
          corners: 1,
          rotate: 0,
          direction: 1,
          color: '#333',
          speed: 1,
          trail: 60,
          shadow: false,
          hwaccel: false,
          className: 'spinner',
          zIndex: 2e9,
          top: '12px',
          left: '8px'
        };
        spinner = new Spinner(opts).spin($('#spinner').get(0));

        xhr = $.ajax({

            type: 'GET',
            url: '../../newspaper-reader/index/search',
            data: {
                'searchText': searchText, // On recherche en minuscule
                'scope' : 'currentDoc',
                'doc' : idItem
            },
            dataType : 'json',
            success: function(data){
                // Retrieves words matching the search
                matchedWords = getSearchResult(data, searchText.split(' '));

                var tabViews = new Array();
                // Pages sur lesquelles il y a des résultats
                var resultPages = [];

                // Draws a shape on top of each result
                for(var i = 0; i < matchedWords.length; i++){
                    tabViews[matchedWords[i][0].view]=matchedWords[i][0].view;
                }

                //On surligne les miniatures contenant le terme recherché
                for (var j=1 ; j<=tabViews.length; j++) {
                    if(tabViews[j] !== undefined) {
                        resultPages.push(parseInt(tabViews[j]));
                        $('#div-thumbnail-'+tabViews[j]).addClass("hilight");
                    }
                };

                //On surligne le texte sur l'image
                hilightText();
                $('#div-searchbar-navigation #results-number').html('Occurence : '+ matchedWords.length);
                if(matchedWords.length > 1){
                    //On surligne le texte dans le mode texte
                    viewSearchText(searchText);
                }
                //On arrête le loader
                spinner.stop();
                callback(resultPages.length > 0 ? Math.min.apply(Math, resultPages) : 0);
            },
            error: function(error) {
                //On arrête le loader
                spinner.stop();
                callback(0);
            }

        });
    }

    /** Member function
    *   Event handling function : mouse pressed down -> retrieves mouse coordinates and changes the flags' state
    *   @param e the event raised
    */
    canvas.onmousedown = function (e) {

        //On resélectionne l'outil (pour recommencer le timer depuis le début)
        changeCursorMode(cursorMode);

        // Prevents from modifying the cursor appearance in Google Chrome
        e.preventDefault();
        // Retrieves mouse coordinates and stores them
        var pos = getMousePos(canvas, e);
        startX = pos.x;
        startY = pos.y;
        savedX = startX;
        savedY = startY;

        // Changes the flags' state : mouse pressed and user's action not finished
        mouseDown = true;
    }

    /** Member function
    *   Event handling function : mouse moved -> applies current cursor mode : moveImage / zoom +- / brightness +- / contrast +-
    *   @param e the event raised
    */
    canvas.onmousemove = function (e) {
        // Mouse pressed
        if (mouseDown == true) {
            // Retrieves mouse coordinates and stores them
            var pos = getMousePos(canvas, e),
                x = pos.x,
                y = pos.y;

            // Cursor mode 1 : moveImage
            if(cursorMode == MOVE_MODE)
            {
                moveImage(x, y);
            }

            // Cursor mode 2 : zoom
            else if(cursorMode == ZOOM_MODE)
            {
                zoomImage(x, y);
            }

            // Cursor mode 3 : brightness
            else if(cursorMode == BRIGHTNESS_MODE)
            {
                imageDrawn.currentBrightness = INITIAL_BRIGHTNESS;

                // Mouse moved up : brightness +
                if(startY > y && imageDrawn.brightnessModif < BRIGHTNESS_MAXI)
                {
                    imageDrawn.currentBrightness += ((startY - y) / BRIGHTNESS_DIVIDING_FACTOR);
                    imageDrawn.brightnessModif += ((startY - y) / BRIGHTNESS_DIVIDING_FACTOR);
                }

                // Mouse moved down : brightness -
                else if(startY < y && imageDrawn.brightnessModif > BRIGHTNESS_MINI)
                {
                    imageDrawn.currentBrightness -= ((y - startY) / BRIGHTNESS_DIVIDING_FACTOR);
                    imageDrawn.brightnessModif -= ((y - startY) / BRIGHTNESS_DIVIDING_FACTOR);
                }

                brightnessAdjust();
            }

            // Cursor mode 4 : contrast
            else if(cursorMode == CONTRAST_MODE)
            {
                // Mouse moved up : contrast +
                if(startY > y && imageDrawn.contrastModif < CONTRAST_MAXI)
                {
                    imageDrawn.currentContrast = INITIAL_CONTRAST;
                    imageDrawn.currentContrast += ((startY - y) / CONTRAST_DIVIDING_FACTOR);
                    imageDrawn.contrastModif += ((startY - y) / CONTRAST_DIVIDING_FACTOR);
                    contrastAdjust();
                }

                // Mouse moved down : contrast -
                else if(startY < y && imageDrawn.contrastModif > CONTRAST_MINI)
                {
                    imageDrawn.currentContrast = INITIAL_CONTRAST - 1;
                    imageDrawn.currentContrast -= ((y - startY) / CONTRAST_DIVIDING_FACTOR);
                    imageDrawn.contrastModif -= ((y - startY) / CONTRAST_DIVIDING_FACTOR);
                    contrastAdjust();
                }
            }

            startX = x;
            startY = y;
        }
    }

    /** Member function
    *   Event handling function : mouse released -> checks if the action performed by the user is finished and changes the flag's state
    */
    canvas.onmouseup = function () {

        // User's action finished ?
        endAction();

        // Apply filters again after motion or zoom
        if(cursorMode == MOVE_MODE || cursorMode == ZOOM_MODE)
        {
            imageDrawn.currentBrightness = INITIAL_BRIGHTNESS + imageDrawn.brightnessModif;
            imageDrawn.currentContrast = INITIAL_CONTRAST + imageDrawn.contrastModif;
            drawImage();
            brightnessAdjust();
            contrastAdjust();
        }
    }

    /** Member function
    *   Event handling function : mouse out of the canvas -> checks if the action performed by the user is finished and changes the flag's state
    */
    canvas.onmouseout = function () {
        // Mouse pressed
        if(mouseDown)
        {
            endAction();
        }
    }
}


/** Member function
*   Initialization function : set the image drawn properties and coordinates
*/
function initImageSize(){
    // Origin in 0,0
    imageDrawn.x1 = 0;
    imageDrawn.y1 = 0;
    updateImageSize();
}

/** Member function
*   Updating function : refreshes the image drawn properties and coordinates
*/

function updateImageSize(){
    // Image's width and height = initial ones * scale
    imageDrawn.width = image.width * imageDrawn.currentScale;
    imageDrawn.height = image.height * imageDrawn.currentScale;

    checkImageOrientation();

    // The image is vertical
    if(imageDrawn.orientation == VERTICAL)
    {
        // Top-right corner x = top-left corner x + width
        imageDrawn.x2 = imageDrawn.x1 + imageDrawn.width;
        // Bottom-left corner y = top-left corner y + height
        imageDrawn.y2 = imageDrawn.y1 + imageDrawn.height;
    }

    // The image is horizontal
    if(imageDrawn.orientation == HORIZONTAL)
    {
        // Top-right corner x = top-left corner x + height
        imageDrawn.x2 = imageDrawn.x1 + imageDrawn.height;
        // Bottom-left corner y = top-left corner y + width
        imageDrawn.y2 = imageDrawn.y1 + imageDrawn.width;
    }

    // Horizontal scale for the thumbnails
    scaleX = thumbnailWidth / imageDrawn.width;

    // Vertical scale for the thumbnails
    scaleY = thumbnailHeight / imageDrawn.height;
}

/** Member function
*   Motion function : moves the image to the target location
*   @param x the horizontal coordinate of the target location
*   @param y the vertical coordinate of the target location
*/

function moveImage(x, y) {

    // Click on the image
    if(x >= X_ORIGIN && x <= imageDrawn.x2 && y >= Y_ORIGIN && y <= imageDrawn.y2)
    {
        // Horizontal and vertical motions variables for the image and the thumbnail
        var motionX = x - startX,
            motionY = y - startY,
            motionThumbX = (startX - x) * scaleX,
            motionThumbY = (startY - y) * scaleY;

        //Vrai si l'image est bloquée (on ne peut pas la déplacer)
        var cantMove = false;


        // Motion from the right to the left
        if(x < startX)
        {
            if(imageDrawn.x2 + motionX >= xMotionLim1 + (BORDER * imageDrawn.currentScale))
            {
                moveX(motionX, motionThumbX);
            }
            else {
                cantMove = true;
            }
        }

        // Motion from the left to the right
        if(x > startX)
        {
            if(imageDrawn.x1 + motionX <= xMotionLim2 - (BORDER * imageDrawn.currentScale))
            {
                moveX(motionX, motionThumbX);
            }
            else {
                cantMove = true;
            }
        }

        // Motion from the bottom to the top
        if(y < startY)
        {
            if(imageDrawn.y2 + motionY >= yMotionLim1 + (BORDER * imageDrawn.currentScale))
            {
                moveY(motionY, motionThumbY);
            }
            else {
                cantMove = true;
            }
        }

        // Motion from the top to the bottom
        if(y > startY)
        {
            if(imageDrawn.y1 + motionY <= yMotionLim2 - (BORDER * imageDrawn.currentScale))
            {
                moveY(motionY, motionThumbY);
            }
            else {
                cantMove = true;
            }
        }

        // Updates the data structure's coordinates then draws the image and the viewport
        updateImageSize();
        drawImage();
        drawViewPort();
    }
}

/** Member function
*   Motion function : moves the image horizontally
*   @param xI the horizontal coordinate to which the image is moved
*   @param xT the horizontal coordinate to which the thumbnail is moved
*/

function moveX(xI, xT) {
    // If xT is not set, sets its value to 0
    xT = xT || 0;

    // Moves the drawing context and updates the data structure's horizontal coordinate
    context.translate(xI, 0);
    imageDrawn.x1 += xI;

    // Updates the viewport's horizontal coordinate
    viewportX += xT;
}

/** Member function
*   Motion function : moves the image vertically
*   @param yI the vertical coordinate to which the image is moved
*   @param yT the vertical coordinate to which the thumbnail is moved
*/

function moveY(yI, yT){

    // If yT is not set, sets its value to 0
    yT = yT || 0;

    // Moves the drawing context and updates the data structure's vertical coordinate
    context.translate(0, yI);
    imageDrawn.y1 += yI;

    // Updates the viewport's vertical coordinate
    viewportY += yT;
}

/** Member function
*   Zoom function : changes the image resolution according to the vertical motion
*   @param x the horizontal coordinate
*   @param y the vertical coordinate
*/

function zoomImage (x, y) {

    savedWidth = imageDrawn.width;
    savedHeight = imageDrawn.height;

    var xMotion = 0,
        yMotion = 0;

    // Mouse moved up : zoom in
    if(startY > y)
    {
        // Current zoom smaller than the maximum zoom
        if(imageDrawn.currentScale + ((startY - y) / ZOOM_DIVIDING_FACTOR) * ZOOM_DELTA <= ZOOM_MAXI)
        {
            // Updates the data structure's scale then updates its coordinates
            imageDrawn.currentScale += ((startY - y) / ZOOM_DIVIDING_FACTOR) * ZOOM_DELTA;
            updateImageSize();
            // Crops the image according to the clicked position
            cropImage(x, y);
        }
    }

    // Mouse moved down : zoom out
    if(startY < y)
    {
        // Current zoom greater than the maximum zoom
        if(imageDrawn.currentScale - ((y - startY) / ZOOM_DIVIDING_FACTOR) * ZOOM_DELTA >= ZOOM_MINI)
        {
            // Updates the data structure's scale then updates its coordinates
            imageDrawn.currentScale -= ((y - startY) / ZOOM_DIVIDING_FACTOR) * ZOOM_DELTA;
            updateImageSize();
            // Crops the image according to the clicked position
            cropImage(x, y);
        }
    }
}

// Permet la suppression des accents dans les chaînes de caractères
var defaultDiacriticsRemovalMap = [
    {'base':'A', 'letters':'\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F'},
    {'base':'AA','letters':'\uA732'},
    {'base':'AE','letters':'\u00C6\u01FC\u01E2'},
    {'base':'AO','letters':'\uA734'},
    {'base':'AU','letters':'\uA736'},
    {'base':'AV','letters':'\uA738\uA73A'},
    {'base':'AY','letters':'\uA73C'},
    {'base':'B', 'letters':'\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181'},
    {'base':'C', 'letters':'\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E'},
    {'base':'D', 'letters':'\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779'},
    {'base':'DZ','letters':'\u01F1\u01C4'},
    {'base':'Dz','letters':'\u01F2\u01C5'},
    {'base':'E', 'letters':'\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E'},
    {'base':'F', 'letters':'\u0046\u24BB\uFF26\u1E1E\u0191\uA77B'},
    {'base':'G', 'letters':'\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E'},
    {'base':'H', 'letters':'\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D'},
    {'base':'I', 'letters':'\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197'},
    {'base':'J', 'letters':'\u004A\u24BF\uFF2A\u0134\u0248'},
    {'base':'K', 'letters':'\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2'},
    {'base':'L', 'letters':'\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780'},
    {'base':'LJ','letters':'\u01C7'},
    {'base':'Lj','letters':'\u01C8'},
    {'base':'M', 'letters':'\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C'},
    {'base':'N', 'letters':'\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4'},
    {'base':'NJ','letters':'\u01CA'},
    {'base':'Nj','letters':'\u01CB'},
    {'base':'O', 'letters':'\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C'},
    {'base':'OI','letters':'\u01A2'},
    {'base':'OO','letters':'\uA74E'},
    {'base':'OU','letters':'\u0222'},
    {'base':'OE','letters':'\u008C\u0152'},
    {'base':'oe','letters':'\u009C\u0153'},
    {'base':'P', 'letters':'\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754'},
    {'base':'Q', 'letters':'\u0051\u24C6\uFF31\uA756\uA758\u024A'},
    {'base':'R', 'letters':'\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782'},
    {'base':'S', 'letters':'\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784'},
    {'base':'T', 'letters':'\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786'},
    {'base':'TZ','letters':'\uA728'},
    {'base':'U', 'letters':'\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244'},
    {'base':'V', 'letters':'\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245'},
    {'base':'VY','letters':'\uA760'},
    {'base':'W', 'letters':'\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72'},
    {'base':'X', 'letters':'\u0058\u24CD\uFF38\u1E8A\u1E8C'},
    {'base':'Y', 'letters':'\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE'},
    {'base':'Z', 'letters':'\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762'},
    {'base':'a', 'letters':'\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250'},
    {'base':'aa','letters':'\uA733'},
    {'base':'ae','letters':'\u00E6\u01FD\u01E3'},
    {'base':'ao','letters':'\uA735'},
    {'base':'au','letters':'\uA737'},
    {'base':'av','letters':'\uA739\uA73B'},
    {'base':'ay','letters':'\uA73D'},
    {'base':'b', 'letters':'\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253'},
    {'base':'c', 'letters':'\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184'},
    {'base':'d', 'letters':'\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A'},
    {'base':'dz','letters':'\u01F3\u01C6'},
    {'base':'e', 'letters':'\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD'},
    {'base':'f', 'letters':'\u0066\u24D5\uFF46\u1E1F\u0192\uA77C'},
    {'base':'g', 'letters':'\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F'},
    {'base':'h', 'letters':'\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265'},
    {'base':'hv','letters':'\u0195'},
    {'base':'i', 'letters':'\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131'},
    {'base':'j', 'letters':'\u006A\u24D9\uFF4A\u0135\u01F0\u0249'},
    {'base':'k', 'letters':'\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3'},
    {'base':'l', 'letters':'\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747'},
    {'base':'lj','letters':'\u01C9'},
    {'base':'m', 'letters':'\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F'},
    {'base':'n', 'letters':'\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5'},
    {'base':'nj','letters':'\u01CC'},
    {'base':'o', 'letters':'\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275'},
    {'base':'oi','letters':'\u01A3'},
    {'base':'ou','letters':'\u0223'},
    {'base':'oo','letters':'\uA74F'},
    {'base':'p','letters':'\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755'},
    {'base':'q','letters':'\u0071\u24E0\uFF51\u024B\uA757\uA759'},
    {'base':'r','letters':'\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783'},
    {'base':'s','letters':'\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B'},
    {'base':'t','letters':'\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787'},
    {'base':'tz','letters':'\uA729'},
    {'base':'u','letters': '\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289'},
    {'base':'v','letters':'\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C'},
    {'base':'vy','letters':'\uA761'},
    {'base':'w','letters':'\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73'},
    {'base':'x','letters':'\u0078\u24E7\uFF58\u1E8B\u1E8D'},
    {'base':'y','letters':'\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF'},
    {'base':'z','letters':'\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763'}
];

var diacriticsMap = {};
for (var i=0; i < defaultDiacriticsRemovalMap.length; i++){
    var letters = defaultDiacriticsRemovalMap[i].letters.split("");
    for (var j=0; j < letters.length ; j++){
        diacriticsMap[letters[j]] = defaultDiacriticsRemovalMap[i].base;
    }
}

function removeDiacritics (str) {
    return str.replace(/[^\u0000-\u007E]/g, function(a){
       return diacriticsMap[a] || a;
    });
}

/**
* Surligne le texte recherché sur l'image
*/
function hilightText(){
    if(matchedWords !== null) {
        for(var i = 0 ; i < matchedWords.length ; i++){
            for(var j = 0 ; j < matchedWords[i].length ; j++){
                if(currentView === matchedWords[i][j].view){
                    var mWord = matchedWords[i][j];
                    drawShape(mWord.x, mWord.y, mWord.w, mWord.h);
                }
            }
        }
    }
}

/** Member function
*   Drawing function : clears the screen then draws the image
*/

function drawImage() {
    clearCanvas();
    context.save();
    context.scale(imageDrawn.currentScale, imageDrawn.currentScale);
    context.rotate(imageDrawn.currentAngle * Math.PI / 180);
    context.drawImage(image, imageOriginX, 0);  // Origin in 0,0
    context.restore();

    hilightText();
}

/** Member function
*   Cropping function : crops the image according to the zoom scale, the coordinates of the image after the zoom and the clicked positon
*   @param x the horizontal coordinate
*   @param y the vertical coordinate
*/

function cropImage(x, y){

    var percentMotionX = (savedX - imageDrawn.x1) / savedWidth,
        percentMotionY = (savedY - imageDrawn.y1) / savedHeight,
        diffWidth =  (savedWidth - imageDrawn.width),
        diffHeigth =  (savedHeight - imageDrawn.height),
        motionX = diffWidth * percentMotionX,
        motionY = diffHeigth * percentMotionY;

    moveX(motionX);
    moveY(motionY);
    updateImageSize();
    drawImage();
}

/** Member function
*   Rotation function : rotates the image to the left / right and updates its data structure coordinates
*   @param direction the direction of the rotation
*/

function rotateImage(direction){

    var xMotion = 0,
        yMotion = 0;

    if(direction == ROTATION_TO_THE_LEFT)
    {
        imageDrawn.currentAngle -= ROTATION_ANGLE_IN_DEGREES;
        updateImageSize();

        switch(imageDrawn.currentAngle)
        {
            case (0 + (fullRotationsMade * 360)):
                moveX(-imageDrawn.height);
                xMotion = -imageDrawn.height;
            break;

            case (-90 + (fullRotationsMade * 360)):
                moveY(imageDrawn.width);
                yMotion = imageDrawn.width;
            break;

            case (90 + (fullRotationsMade * 360)):
                moveX(imageDrawn.height - imageDrawn.width);
                moveY(-imageDrawn.height);
                xMotion = imageDrawn.height - imageDrawn.width;
                yMotion = -imageDrawn.height;
            break;

            case (-180 + (fullRotationsMade * 360)):
            case (180 + (fullRotationsMade * 360)):
                moveX(imageDrawn.width);
                moveY(imageDrawn.height - imageDrawn.width);
                xMotion = imageDrawn.width;
                yMotion = imageDrawn.height - imageDrawn.width;
            break;

            case (-270 + (fullRotationsMade * 360)):
                moveX(imageDrawn.height - imageDrawn.width);
                moveY(-imageDrawn.height);
                xMotion = imageDrawn.height - imageDrawn.width;
                yMotion = -imageDrawn.height;
            break;

            case (-360 + (fullRotationsMade * 360)):
                moveX(-imageDrawn.height);
                xMotion = -imageDrawn.height;
                fullRotationsMade--;
            break;

            default:
            break;
        }
    }

    else if(direction == ROTATION_TO_THE_RIGHT)
    {
        imageDrawn.currentAngle += ROTATION_ANGLE_IN_DEGREES;
        updateImageSize();

        switch(imageDrawn.currentAngle)
        {
            case (0 + (fullRotationsMade * 360)):
                moveY(-imageDrawn.width);
                yMotion = -imageDrawn.width;
            break;

            case (-90 + (fullRotationsMade * 360)):
                moveX(-imageDrawn.width);
                moveY(imageDrawn.width - imageDrawn.height);
                xMotion = -imageDrawn.width;
                yMotion = imageDrawn.width - imageDrawn.height;
            break;

            case (90 + (fullRotationsMade * 360)):
                moveX(imageDrawn.height);
                xMotion = imageDrawn.height;
            break;

            case (-180 + (fullRotationsMade * 360)):
            case (180 + (fullRotationsMade * 360)):
                moveX(imageDrawn.width - imageDrawn.height);
                moveY(imageDrawn.height);
                xMotion = imageDrawn.width - imageDrawn.height;
                yMotion = imageDrawn.height;
            break;

            case (270 + (fullRotationsMade * 360)):
                moveX(-imageDrawn.width);
                moveY(imageDrawn.width - imageDrawn.height);
                xMotion = -imageDrawn.width;
                yMotion = imageDrawn.width - imageDrawn.height;
            break;

            case (360 + (fullRotationsMade * 360)):
                moveY(-imageDrawn.width);
                yMotion = -imageDrawn.width;
                fullRotationsMade++;
            break;

            default:
            break;
        }
    }

    updateMotionLimits(xMotion, yMotion);
    updateImageSize();
    drawImage();

    // Apply filters again after rotation
    imageDrawn.currentBrightness = INITIAL_BRIGHTNESS + imageDrawn.brightnessModif;
    brightnessAdjust();
    imageDrawn.currentContrast = INITIAL_CONTRAST + imageDrawn.contrastModif;
    contrastAdjust();
}

/** Member function
*   Updating function : refreshes the motion limits coordinates
*   @param xMotion the motion of the horizontal limit coordinates
*   @param yMotion the motion of the vertical limit coordinates
*/

function updateMotionLimits(xMotion, yMotion) {
    // Updates the motion limits coordinates
    xMotionLim1 += xMotion;
    xMotionLim2 += xMotion;
    yMotionLim1 += yMotion;
    yMotionLim2 += yMotion;
}

/** Member function
*   Reset function : resets the image scale to its initial state
*/

function resetImageScale(){
    imageDrawn.currentScale = INITIAL_SCALE;
    changeCursorMode(MOVE_MODE);
    updateImageSize();
    drawImage();
    drawViewPort();
}

/** Member function
*   Reset function : resets the image to its initial state
*/

function resetImage(){
    changeCursorMode(MOVE_MODE);
    moveX(-imageDrawn.x1, - viewportX + VIEWPORT_BORDER);
    moveY(-imageDrawn.y1, - viewportY + VIEWPORT_BORDER);
    imageDrawn = new oImage();
    xMotionLim1 = X_ORIGIN;
    xMotionLim2 = canvasWidth;
    yMotionLim1 = Y_ORIGIN;
    yMotionLim2 = canvasHeight;
    initImageSize();
    drawImage();
    drawViewPort();
}

/** Member function
*   Drawing function : draws the viewport on the thumbnail of the current image
*/

function drawViewPort() {

    // console.log(imageDrawn);
    // console.log(thumbnailWidth);
    // console.log(thumbnailHeight);
    // console.log($("#canvas").width());
    // console.log($("#canvas").height());
    // console.log(imageDrawn.currentScale);

    viewportW = ($("#canvas").width() * imageDrawn.currentScale) / (imageDrawn.width * imageDrawn.currentScale) * thumbnailWidth;
    viewportH = ($("#canvas").height() * imageDrawn.currentScale) / (imageDrawn.height * imageDrawn.currentScale) * thumbnailHeight;

    $('#div-viewport').css({
        top: viewportY - VIEWPORT_BORDER + 'px',
        left: viewportX - VIEWPORT_BORDER + 'px',
        width: viewportW + 'px',
        height: viewportH + 'px'
    });
}

/**
*   Fonction de recherche de mot dans le mode texte
*/
function viewSearchText(searchText){
    if(searchText) {
        tabSearch = searchText.split(' ');
        string = "(\\b[\\w]*?";

        for(var i = 0; i < tabSearch.length ; i++) {

            if(i !== tabSearch.length - 1) {
                string += tabSearch[i] + '[\\w]*?\\b ';
            }
            else {
                string += tabSearch[i];
            }
        }

        string += "[\\w]*?\\b)";

        var theRegEx = new RegExp(string, "igm");
        $('#div-text').html($('#div-text').html().replace(theRegEx ,"<span class='hilight-text' >$1</span>"));
    }
}

function getSearchResult (searchResultJSON, searchText) {
    //tableau de résultat contennt des tableaux pages object
    var searchResult = new Array();

    for(var nbResults = 0; nbResults < searchResultJSON.length; nbResults++){

        for(var i = 0; i < searchResultJSON[nbResults].data.words.length ; i++){

            //tableau contenant les page objet résultant de la recherche
            var word = new Array();

            word[0] = searchResultJSON[nbResults].data.words[i];
            word[0].view = searchResultJSON[nbResults].view;

            //recherche si la chaine contient une occurence du mot recherché
            var pos = removeDiacritics(word[0].content.toLowerCase()).indexOf(removeDiacritics(searchText[0]));
            var pos1 = -1;
            //si il y à plusieurs mot à rechercher
            if(searchText.length !== 1 && pos > -1){
                //Pour tous les mots recherchés
                for(var j=1; j < searchText.length ; j++){
                    if(i !== searchResultJSON[nbResults].data.words.length - (searchText.length-1)){
                        word[j] = searchResultJSON[nbResults].data.words[i+j]
                        pos1 = removeDiacritics(word[j].content.toLowerCase()).indexOf(removeDiacritics(searchText[j]));
                        if(pos1 === -1){
                            break;
                        }
                    }
                }

            }
            if((pos > -1 && searchText.length === 1) || pos1 > -1)
            {
                searchResult.push(word);

            }
        }
        // for(var word in searchResultJSON[nbResults].data.words)
        // {
            // word = searchResultJSON[nbResults].data.words[word];
            // word.view = searchResultJSON[nbResults].view;
            // var pos = word.content.toLowerCase().indexOf(searchText[0]);


            // if(pos > -1)
            // {
            //     searchResult.push(word);

            // }
        //     for(var word in searchResultJSON[nbResults].data.blocks[block].words)
        //     {
        //         word = searchResultJSON[nbResults].data.blocks[block].words[word];
        //         var pos = word.content.toLowerCase().indexOf(searchText);

        //         if(pos > -1)
        //         {
        //             searchResult.push(word);
        //         }
        //     }
        // }
    }

    return searchResult;
}

/** Member function
*   Drawing function : draws a rectangular shape on a word of the current image
*   @param x the horizontal coordinate of the shape's top-left corner
*   @param y the vertical coordinate of the shape's top-left corner
*   @param w the width of the shape
*   @param h the height of the shape
*/

function drawShape(x, y, w, h){

    var reductionFactor = PERCENTAGE_REDUCTION / 100;
    // console.log(reductionFactor);

    x *= reductionFactor * imageDrawn.currentScale;
    y *= reductionFactor * imageDrawn.currentScale;
    w *= reductionFactor * imageDrawn.currentScale;
    h *= reductionFactor * imageDrawn.currentScale;

    // y *= PERCENTAGE_REDUCTION/100;
    // x *= PERCENTAGE_REDUCTION/100;
    // w *= PERCENTAGE_REDUCTION/100;
    // h *= PERCENTAGE_REDUCTION/100;

    // y *= imageDrawn.currentScale;
    // x *= imageDrawn.currentScale;
    // w *= imageDrawn.currentScale;
    // h *= imageDrawn.currentScale;

    context.fillStyle = "#F6FF00";
    context.globalAlpha = 0.35;
    context.fillRect(x, y, w, h);
    context.globalAlpha = 1;
}

/** Member function
*   Clearing function : clears the screen
*/

function clearCanvas() {
    context.clearRect(-imageDrawn.x1, -imageDrawn.y1, imageDrawn.width + canvasWidth, imageDrawn.height + canvasHeight);
    // context.clearRect(0, 0, context.width, context.height);
}

function checkImageOrientation(){
    switch(imageDrawn.currentAngle)
    {
        // The image is vertical
        case (0 + (fullRotationsMade * 360)):
        case (-180 + (fullRotationsMade * 360)):
        case (180 + (fullRotationsMade * 360)):
        case (-360 + (fullRotationsMade * 360)):
        case (360 + (fullRotationsMade * 360)):
            imageDrawn.orientation = VERTICAL;
        break;

        // The image is horizontal
        case (-90 + (fullRotationsMade * 360)):
        case (90 + (fullRotationsMade * 360)):
        case (-270 + (fullRotationsMade * 360)):
        case (270 + (fullRotationsMade * 360)):
            imageDrawn.orientation = HORIZONTAL;
        break;

        default:
        break;
    }
}

/** Member function
*   Event handling function : retrieves mouse coordinates when evt is raised
*   @param canvas the canvas in which the mouse is
*   @param evt the event raised
*   @return The mouse coordinates : x,y
*/

function getMousePos(canvas, evt) {
    var rect = canvas.getBoundingClientRect();
    return {
        x: evt.clientX - rect.left,
        y: evt.clientY - rect.top
    };
}

/** Member function
*   Adjustment function : adjusts the contrast of the image
*/

function contrastAdjust(){
    imageData = context.getImageData(0, 0, canvasWidth, canvasHeight);
    var data = imageData.data;
    var contrastFactor = (100 + imageDrawn.currentContrast) / 100;
    contrastFactor *= contrastFactor;

    var newValue = 0;

    for(var i = 0; i < data.length; i++)
    {
        newValue = data[i];

        newValue /= 255;
        newValue -= 0.5;
        newValue *= contrastFactor;
        newValue += 0.5;
        newValue *= 255;

        if(newValue > 255)
        {
            newValue = 255;
        }

        else if(newValue < 0)
        {
            newValue = 0;
        }

        data[i] = newValue;
     }

    context.putImageData(imageData, 0, 0);
}

/** Member function
*   Adjustment function : adjusts the brightness of the image
*/

function brightnessAdjust(){

    imageData = context.getImageData(0, 0, canvasWidth, canvasHeight);
    var data = imageData.data;

    var r, g, b = 0;

    for(var i = 0; i < data.length; i += 4)
    {

        r = data[i];
        g = data[i+1];
        b = data[i+2];

        r *= imageDrawn.currentBrightness;
        g *= imageDrawn.currentBrightness;
        b *= imageDrawn.currentBrightness;

        if(r > 255)
        {
            r = 255;
        }

        else if(r < 0)
        {
            r = 0;
        }

        if(g > 255)
        {
            g = 255;
        }

        else if(g < 0)
        {
            g = 0;
        }

        if(b > 255)
        {
            b = 255;
        }

        else if(b < 0)
        {
            b = 0;
        }

        data[i] = r;
        data[i+1] = g;
        data[i+2] = b;
     }

    context.putImageData(imageData, 0, 0);
}


function loadCurrentViewSrc(index) {
    image.src = getViewImage(index);
    currentViewSrc = image.src;
}

/**
 * Retourne le chemin vers l'image de la vue d'index passé en paramètre
 */
function getViewImage(index) {

    //Si la page a été numérisée
    if(viewImages[index] !== null) {
        return viewImages[index];
    }
    else {
        return baseUrl +  '/themes/bmn/images/missing-image.jpg';
    }
}

/** Member function
*   Loading function : loads the image to be displayed in the canvas
*/
function loadImage() {

    loadCurrentViewSrc(currentView);

    //On affiche un loader tant que la page n'est pas chargée
    $("#loader-overlay").show();

    image.onload = function() {
        drawImage(0, 0);
        //On retire le loader
        $("#loader-overlay").hide();
    };

}

/** Member function
*   Loading function : loads the text to be displayed in the text area
*/

function loadText() {
    //Si la page a été numérisée
    if($.inArray(currentView, existingViews) != -1) {
        $.ajax({
            type: 'GET',
            url: '../../newspaper-reader/index/view-text',
            data: {
                'doc': idDoc,
                'view': currentView,
            },
            success: function(data){
                //évite que certain morceau du texte soit considérer comme des balises
                if(data == ''){
                    $("#div-text").html('Pas de texte pour cette vue');
                }else{
                    var theRegEx = new RegExp("<\(\[^p|^b\] | </\[^p|^b\]\)", "igm");
                    var txt = data.replace(theRegEx ," $1");

                    $("#div-text").html(txt);
                    // $("#div-text").html(data);
                    if(searchText!==""){
                        viewSearchText(searchText);
                    }
                }
            }
        });

    }
    else {
        $("#div-text").html("Cette page du fascicule n'a pas été numérisée.");
    }

}

/** Member function
*   Loading function : loads the image and text to be displayed in the view and refreshes its infos
*   Si dontReload est à true et que la page courante est la même que la page cible, rien ne se passe
*/

function loadView(view, dontReload) {

    if (dontReload && view === currentView) {
        return;
    }

    if (view < 1 || view > viewsCount) {
        return;
    }

    if (typeof view !== "undefined") {
        currentView = view;
    }

    loadImage();
    loadText();

    //Ajoute le viewport à la vignette de la page
    $('#div-thumbnail-'+currentView).append($('#div-viewport'));

    //Met le focus sur la vignette de la page
    $('#thumbnail-number-'+currentView).addClass('current-thumbnail');
}

/** Member function
*   Display function : displays the text / image depending on the current view mode
*/

function changeDisplayMode() {
    if(viewMode == IMAGE_MODE)
    {
        viewMode = TEXT_MODE;
        $('#img-mode').toggle();
        $('#text-mode').toggle();
        $('#div-page-links a').removeClass("current");
        $('#div-page-links #page-link-' + currentView).addClass("current");

    }

    else
    {
        viewMode = IMAGE_MODE;
        $('#img-mode').toggle();
        $('#text-mode').toggle();
    }

    $('#div-toolbar').toggle();
    $('#div-canvas').toggle();
    $('#div-summary').toggle();
    $("#div-text").toggle();
    $("#div-text-pagination").toggle();
    $("#div-href-img").toggle();
}

/** Member function
*   Display function : changes the cursor mode and displays it
*   @param mode the cursor mode to be set
*/

function changeCursorMode (mode) {

    //On stoppe les éventuelles animations
    for (var i = 0; i < toolTimerAnimations.length; i++){

        toolTimerAnimations[i].stop();
    }

    //On vide le tableau
    toolTimerAnimations.length = 0;

    //On supprime toutes les classes de mode du canvas
    $(divCanvas).removeClass("move-mode zoom-mode brightness-mode contrast-mode");

    //On rétabli la couleur de tous les outils et on supprime la classe "used"
    $("#bt-move, #bt-zoom, #bt-brightness, #bt-contrast").css("background-color", "#F6F6F6").removeClass("used");

    //On rend tous les icons actifs transparents
    $("#bt-move .ico-active, #bt-zoom .ico-active, #bt-brightness .ico-active, #bt-contrast .ico-active").css("opacity", "0");

    switch(mode)
    {
        default:
        case MOVE_MODE :
            $(divCanvas).addClass("move-mode");
            cursorMode = MOVE_MODE;
            $("#bt-move").css("background-color", "#2383C4").addClass("used");
            $("#bt-move .ico-active").css("opacity", 1);

            //On retire le loader pour le curseur de déplacement
            $("#tool-loader").css("width", "0");
        break;

        case ZOOM_MODE :
            $(divCanvas).addClass("zoom-mode");
            cursorMode = ZOOM_MODE;
            $("#bt-zoom").css("background-color", "#2383C4").addClass("used");
            $("#bt-zoom .ico-active").css("opacity", 1);

            //On met la taille du loader à 100%
            $("#tool-loader").css("width", "100%");
        break;

        case BRIGHTNESS_MODE :
            $(divCanvas).addClass("brightness-mode");
            cursorMode = BRIGHTNESS_MODE;
            $("#bt-brightness").css("background-color", "#2383C4").addClass("used");
            $("#bt-brightness .ico-active").css("opacity", 1);

            //On met la taille du loader à 100%
            $("#tool-loader").css("width", "100%");
        break;

        case CONTRAST_MODE :
            $(divCanvas).addClass("contrast-mode");
            cursorMode = CONTRAST_MODE;
            $("#bt-contrast").css("background-color", "#2383C4").addClass("used");
            $("#bt-contrast .ico-active").css("opacity", 1);

            //On met la taille du loader à 100%
            $("#tool-loader").css("width", "100%");
        break;

    }
}

/**
 * Termine une action, lance le timer d'outil si nécessaire
 */
function endAction() {

    // Mouse no longer pressed
    mouseDown = false;

    //Vrai si on doit lancer le timer
    var startToolTimer = false;

    switch(cursorMode)
    {
        default:
        case MOVE_MODE:
            startToolTimer = false;
        break;

        case ZOOM_MODE :
        case BRIGHTNESS_MODE :
        case CONTRAST_MODE :
            startToolTimer = true;
        break;
    }

    if(startToolTimer) {

        //On resélectionne l'outil (pour recommencer le timer depuis le début)
        changeCursorMode(cursorMode);

        //On lance l'animation pour la couleur de fond
        toolTimerAnimations.push($(".tool.used, .not-tool.used").animate({
            backgroundColor: "#F6F6F6"
        }, TOOL_DELAY, function() {
            changeCursorMode(MOVE_MODE);
        }));

        //Et pour les icones
        toolTimerAnimations.push($(".tool.used .ico-active, .not-tool.used .ico-active").animate({
            opacity: 0
        }, TOOL_DELAY));

        //Enfin pour le loader
        toolTimerAnimations.push($("#tool-loader").animate({
            width: 0
        }, TOOL_DELAY));


    }
}
