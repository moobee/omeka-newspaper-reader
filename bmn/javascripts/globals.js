if (!Omeka) {
    var Omeka = {};
}

var supportLocalStorage = function() {
    mod = 'supportLocalStorage';
    try {
        localStorage.setItem(mod, mod);
        localStorage.removeItem(mod);
        return true;
    } catch(e) {
        return false;
    }
}

if (typeof console == "undefined" || typeof console.log == "undefined") {
    window.console = {
        log: function () {}
    };
}

// Force les guillemets dans les termes de recherche
function ajouteGuillemetsRecherche(terme) {
  return "\"" + terme.replace(/"/g, "") + "\"";
}

/**
 * Effectue une recherche de fascicules sur le site.
 * Construit l'URL et redirige l'utilisateur vers la page de résultats.
 * Les paramètres sont tous optionnels
 */
function rechercherDocument(expression, collections, periodeDebut, periodeFin, date) {

  //récupère la base de l'url
  var baseUrl = window.location.host;
  expression = expression || "";
  collections = collections || [];
  periodeDebut = periodeDebut || "";
  periodeFin = periodeFin || "";
  date = date || "";

  //créer la nouvelle URL
  var url = "http://" + baseUrl + "/solr-search/results/index?q=" + expression;

  // On ajoute le tri par date par défaut
  url += "&sort=date+asc";

  // On ajoute les collections
  if (collections.length > 0) {
    url += "&fq=id_collection:(" + collections.join(" OR ") + ")";
  }

  //On formate la recherche par URL (pas de recherche par date si une période est présente)
  if (periodeDebut !== "" && periodeFin !== "") {
    url += "&fq=date:[" + periodeDebut + " TO " + periodeFin + "]";
  }
  else if (date !== "") {
    url += "&fq=date:\"" + date + "\"";
  }

  window.location = url;
}

jQuery(function() {

    $ = jQuery;

    // On prérempli les champs de recherche s'ils sont dans le localStorage
    if (supportLocalStorage) {

        $('#keyword-search').val(localStorage.getItem('search.expression') || '');
        $('#date-search').val(localStorage.getItem('search.dateSearch') || '');
        $('#periode-start').val(localStorage.getItem('search.periodeDebut') || '');
        $('#periode-end').val(localStorage.getItem('search.periodeFin') || '');
    }


    /**
    * Desactivation des liens disabled
    */
    $("a.disabled").on("click", function(event) {
      event.preventDefault();
    });

    /**
     * Desativation des liens de la page d'accueil
     */
     $("#documents .text-item a").on("click", function(event) {
       event.preventDefault();
     });

    //Ajout d'un placeholder au champ de recherche (pas possible en PHP)
    $("#query").attr("placeholder", "Rechercher");


    //Gestion des onglets de la recherche avancée
    $("#recherche-globale").on("click", function() {

        if(!$(this).hasClass("active")) {

            $("#recherche-fascicule").removeClass("active");
            $("#recherche-globale").addClass("active");

            $('#search-Views').hide();
            $('#search-omeka').fadeIn();
        }
    });

    $("#recherche-fascicule").on("click", function() {

        if(!$(this).hasClass("active")) {

            $("#recherche-globale").removeClass("active");
            $("#recherche-fascicule").addClass("active");

            $('#search-omeka').hide();
            $('#search-Views').fadeIn();
        }
    });

    //Préselection d'un onglet
    if($("#recherche-fascicule").hasClass("to-activate")) {
      $("#recherche-fascicule").click();
    }
    else {
      $("#recherche-globale").click();
    }

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

    //Fallback placeholder
    if(!Modernizr.input.placeholder) {

      $('[placeholder]').focus(function() {
        var input = $(this);
        if (input.val() == input.attr('placeholder')) {
          input.val('');
          input.removeClass('placeholder');
        }
      }).blur(function() {
        var input = $(this);
        if (input.val() == '' || input.val() == input.attr('placeholder')) {
          input.addClass('placeholder');
          input.val(input.attr('placeholder'));
        }
      }).blur();

      $('[placeholder]').parents('form').submit(function() {
        $(this).find('[placeholder]').each(function() {
          var input = $(this);
          if (input.val() == input.attr('placeholder')) {
            input.val('');
          }
        })
      });
    }

    // Recherche Solr (formulaire de gauche dans les résultats)
    $('#solr-search-form').on("submit", function(event) {
      event.preventDefault();
      var expression = ajouteGuillemetsRecherche($("#solr-search-form input[type='text']").val());
      rechercherDocument(expression);

    });

    // Recherche de base (formulaire en haut à droite)
    $('#search-form').on("submit", function(event){
      event.preventDefault();

      //vérifie que les champs sont saisie et correcte
      if( $('#query').val() === '' || $('#query').val().length < 3 ){
        return;
      }
      else {

        // On force les guillemets dans la recherche
        var expression = ajouteGuillemetsRecherche($("#query").val());

        // On affiche le loader
        opts = {
          lines: 13,
          length: 3,
          width: 2,
          radius: 4,
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
          left: '-12px'
        };

        spinner = new Spinner(opts).spin($('#spinner-global').get(0));

        $("#search-infos").fadeIn(200, function() {
          rechercherDocument(expression);
        });

      }

    });

    //Affichage du loader et du pop up pour la recherche par fascicule
    $('#advanced-search-form-fascicule').on("submit", function(event)
    {

      event.preventDefault();

      // On force les guillemets
      $("#advanced-search-form-fascicule #keyword-search").val(ajouteGuillemetsRecherche($("#advanced-search-form-fascicule #keyword-search").val()));

      // On force la vérification des champs
      $("#date-search, #periode-start, #periode-end").focus();
      $("#periode-end").blur();

      // En cas d'erreur, on ne poste pas
      if ($("#advanced-search-form-fascicule .errorDate").length > 0) {
        return;
      }

      // On affiche un loader et on grise les boutons de recherche
      opts = {
        lines: 13,
        length: 4,
        width: 2,
        radius: 5,
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
        top: '32px',
        left: '835px'
      };

      jQuery(".errorSpan").remove();

      //grise le button et le fait loader
      $('#advanced-search-form-fascicule #submit_search_advanced_fascicule').addClass('loader').attr("disabled", "disabled");
      $('#advanced-search-form-fascicule #submit_search_advanced_fascicule').parent().css("position", "relative");

      loaderAdvanced = new Spinner(opts).spin($('#advanced-search-form-fascicule #submit_search_advanced_fascicule').parent().get(0));

      jQuery('#submit_search_advanced_fascicule').addClass('no-error');

      // On récupère les paramètres de recherche
      var expression = $('#keyword-search').val();
      var dateSearch = $('#date-search').val();
      var periodeDebut = $('#periode-start').val();
      var periodeFin = $('#periode-end').val();

      var collections = [];
      $("[name^='collection[']").each(function() {
        var idCollection = $(this).val();
        if (idCollection != '') {
          collections.push($(this).val());
        }
      });

      // On enregistre les différents champs dans le localStorage si possible
      if (supportLocalStorage) {
          localStorage.setItem('search.expression', expression);
          localStorage.setItem('search.dateSearch', dateSearch);
          localStorage.setItem('search.periodeDebut', periodeDebut);
          localStorage.setItem('search.periodeFin', periodeFin);
      }

      var formateDate = function(date) {
          // https://regex101.com/r/dH1iS4/1
          if (!date.match(/^\d{2}[\/-]\d{2}[\/-]\d{4}$/)) {
              return '';
          }

          return date.substr(6, 4) + "-" + date.substr(3, 2) + "-" + date.substr(0, 2) + "T00:00:00Z";
      }

      //reformate la date
      var dateSearchFormate = formateDate(dateSearch);

      //Reformate les périodes
      var periodeDebutFormate = formateDate(periodeDebut);
      var periodeFinFormate = formateDate(periodeFin);

      // On affiche l'encart de recherche en cours
      $("#advanced-search-infos-fascicule").fadeIn(200, function() {
        // On redirige l'utilisateur vers la recherche
        rechercherDocument(expression, collections, periodeDebutFormate, periodeFinFormate, dateSearchFormate);
      });


    });

});


//vérification du formulaire de recherche avancée
jQuery(function() {

  var $ = jQuery;

  /******************************Variables******************************/
  //periode de 4 ans
  var periodeJour = 366*4;

  //Formulaire
  var $advancedSearchFormFascicule = jQuery('#advanced-search-form-fascicule');
  var $advancedSearchFormInterval = jQuery('#advanced-search-form-interval');
  var $advancedSearchFormFocus = jQuery('#advanced-search-form-focus');

  //récupération des champs input
  var $searchKeywords = jQuery('#keyword-search');
  var $collectionSearch = jQuery('#collection-search');
  var $date = jQuery('#date-search');
  var $periodeStart = jQuery('#periode-start');
  var $periodeEnd = jQuery('#periode-end');
  var $searchByRange = jQuery('#range');
  var $expositionSearch = jQuery('#exposition-search');

  //Focus sur le premier élement
  jQuery('#keyword-search').focus();

  //Ajout d'un champs collection
  jQuery('#addCollection').on("click", function(event) {

  var select ="<div> <select name='collection[" + nbCollection + "]'>"+
                    "<option label='Toutes les collections' value=''>Toutes les collections</option>";

    delete tabCollection[""];

    for (var i in tabCollection) {
      select += '<option label="' + tabCollection[i] + '" value="' + i + '">' +tabCollection[i] + '</option>';
    }

    select += "</select> <a  class='remove-line'>-</a> </div>"+
          "";

    jQuery('#collection .inputs').append(select);

    jQuery('#collection .inputs :last-child').focus();

    nbCollection++;
  });

  //Supression d'un champs collection
  jQuery('body').on("click", ".remove-line", function(event) {

    jQuery(this).parent().remove();

  });

  /******************************Recherche par journal******************************/


  /**
  * Vérifie le bon formatage d'une date.
  * Formats tolérés : JJ/MM/AAAA et JJ-MM-AAAA
  * return true si la date est bien formatée, false sinon.
  */
  function verifFormatDate(dateString) {

    //Valeur du split
    var splitValue = '-';
    if(dateString !== ""){

      var tabDate = dateString.split('-');

      //S'il n'y a qu'une valeur dans le tableau, "-" n'est pas le séparateur de date
      if(tabDate.length === 1) {

        //Donc on test avec l'autre séparateur ("/")
        splitValue = '/';
        tabDate = dateString.split('/');
      }

      var verStr=navigator.appVersion;
      var app=navigator.appName;
      var version = parseFloat(verStr);

      //si la date passée est en trois parties
      if(tabDate.length === 3 && tabDate[0].length === 2 && tabDate[1].length === 2 && tabDate[2].length === 4) {

        //On teste le bon formattage de la date
        var timestamp = Date.parse(tabDate[2] + "-" + tabDate[1] + "-" + tabDate[0]);

        // La date est mal formatée ou n'existe pas (exemple : 30 février)
        if (isNaN(timestamp) === true) {
          //si le navigateur est ie, on vérifie juste la taille des champs et les valeurs jour <=31 et mois <=12
            if(navigator.appName === 'Microsoft Internet Explorer' && parseInt(tabDate[0]) <= 31 && parseInt(tabDate[1]) <= 12){
              return true;
            }
            else{
              return false;
            }
        }
        else {
          return true;
        }

      }
      else {
        return false;
      }
    }
    else{
      return false
    }
  }

  /*
  * Vérfie que periode (en jours) est inféireur a la différence entre les deux dates
  * @return false si la période
  */
  function verifPeriode(start, end, periode){

    //Si les deux date sont valide
    if ( verifFormatDate(start) && verifFormatDate(end) ) {

      var tabDateStart = start.split('-');
      var tabDateEnd = end.split('-');

    //S'il n'y a qu'une valeur dans le tableau, "-" n'est pas le séparateur de date
      if(tabDateStart.length === 1){

        //Donc on test avec l'autre séparateur ("/")
        tabDateStart = start.split('/');

      }

      //S'il n'y a qu'une valeur dans le tableau, "-" n'est pas le séparateur de date
      if(tabDateEnd.length === 1){

        //Donc on test avec l'autre séparateur ("/")
        tabDateEnd = end.split('/');

      }

      if(navigator.appName === 'Microsoft Internet Explorer'){
        //Si la différence entre l'année de fin et de début est supérieur à 4 ans on retourne faux
        if(tabDateEnd[2]-tabDateStart[2] >= 4){
          //Si la différence est égale à 4 ans, on regarde si
          if(tabDateEnd[2]-tabDateStart[2] === 4){
            //la différence entre le mois de fin et de début est supérieur à 0
            if(tabDateEnd[1]-tabDateStart[1] <= 0){
              //si la différence entre le jour de fin et de début est supérieur à 0
              if(tabDateEnd[0]-tabDateStart[0] <= 0){
                return true;
              }else{
                return false;
              }
            }else{
              return false;
            }

          }else{
            return false
          }
        }else{
          return true;
        }
      }else{
        //Création de deux objets date
        var dateStart = new Date(tabDateStart[2] + "-" + tabDateStart[1] + "-" + tabDateStart[0]);
        var dateEnd = new Date(tabDateEnd[2] + "-" + tabDateEnd[1] + "-" + tabDateEnd[0]);
      }
      if(((dateEnd-dateStart) / 1000 / 60 / 60 / 24) < periode){
        return true;

      }else{

        return false;

      }

    }else{

      return false;

    }
  }

  /*
  * Retourne un objet span jquery avec la class
  *et le message passer en paramètre
  */
  function addErrorMessage(classNameErreur, messageErreur){
    var $errorDate = jQuery('<span class="'+classNameErreur+' errorDate">'+messageErreur+'</span>');
    return $errorDate;

  }

  //Vérification de la date
  $date.on("blur", function(event){
    //si le champs n'est pas vide
    if($date.val() !== ""){

      var verifdate = verifFormatDate($date.val());
      //si la date est mal formaté
      if( verifdate === false){

        $date.after(addErrorMessage('errorDates', 'Erreur dans le champs Date.').fadeIn(300));

      }

    }
  });



  //Vérification du début de periode
  $periodeStart.on("blur", function(event){

    //si le champs n'est pas vide
    if($periodeStart.val() !== ""){

      var verifdate = verifFormatDate($periodeStart.val());

      //si la période est mal formaté
      if(verifdate === false){

          $periodeStart.after(addErrorMessage('errorPeriodeStart','Erreur dans le champs Période.').fadeIn(300));

      }
    }
  });

  //Vérification de fin de fin de periode
  $periodeEnd.on("blur", function(event){

    //si le champs n'est pas vide
    if($periodeEnd.val() !== ""){

      //si la période est mal formaté
      if(!verifFormatDate($periodeEnd.val())){
        $periodeEnd.after(addErrorMessage('errorPeriodeEnd','Erreur dans le champs Période.').fadeIn(300));

      }
    }
  });

  $date.on("focus", function(event){
      jQuery(".errorDates").remove();
      jQuery(".errorSpan").remove();
      jQuery('#submit_search_advanced_fascicule').addClass('no-error');
  });

  $periodeStart.on("focus", function(event){
      jQuery(".errorPeriodeStart").remove();
      jQuery(".errorPeriode").remove();
      jQuery(".errorSpan").remove();
      jQuery('#submit_search_advanced_fascicule').addClass('no-error');
  });

  $periodeEnd.on("focus", function(event){
      jQuery(".errorPeriodeEnd").remove();
      jQuery(".errorPeriode").remove();
      jQuery(".errorSpan").remove();
      jQuery('#submit_search_advanced_fascicule').addClass('no-error');
  });

  // VIde les champs de recherche et le préremplissage dans le localStorage
  $("[data-js-action='clearForm']").on("click", function(event) {
    event.preventDefault();

    // focus/blur permettent de supprimer les champs d'erreur automatiquement
    $('#keyword-search').val('').focus().blur();
    $('#date-search').val('').focus().blur();
    $('#periode-start').val('').focus().blur();
    $('#periode-end').val('').focus().blur();
    $('#collection .remove-line').click();

    if (supportLocalStorage) {
        localStorage.setItem('search.expression', '');
        localStorage.setItem('search.dateSearch', '');
        localStorage.setItem('search.periodeDebut', '');
        localStorage.setItem('search.periodeFin', '');
    }
  });

  /******************************Recherche par intervalle******************************/

  //verifie qu'une interval est correctement formaté
  function verifInterval(interval){

    var tabInt = interval.split('-');

    //si il n'y a pas au moin un '-' dans l'interval
    if(tabInt.length === 1){

      return false;

    } //Sinon on parcours le tableau
    else{
      for (var keyInt in tabInt){

        //On vérifie que chaque valeur est un entier
        if (isNaN(parseInt(tabInt[keyInt]))){
          return false;
        }
      }
      return true;
    }
  }

  //Enleve le message d'erreur sous le submit quand un input ou un select est focus
  jQuery('#advanced-search-form-interval input').on("focus", function(event){

    jQuery(".errorSpanInterval").remove();

  });

  /******************************Recherche par Focus******************************/

  //Enleve le message d'erreur sous le submit quand un input ou un select est focus
  jQuery('#advanced-search-form-focus input').on("focus", function(event){

    jQuery(".errorSpanFocus").remove();

  });

  //Affichage du loader et du pop up pour la recherche par focus
  jQuery('#advanced-search-form-focus').on("submit", function(event) {

    jQuery(".errorSpanFocus").remove();
    //Si aucun champs du fomulaire est rempli on affiche un message d'erreur
    if($expositionSearch.val() === '') {

      jQuery('#button-form-focus').after(addErrorMessage('errorSpanFocus', 'Vous devez indiquer au minimum un intervalle ou un identifiant').fadeIn(300));
      event.preventDefault();

    }else{

      opts = {
        lines: 13,
        length: 4,
        width: 2,
        radius: 5,
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
        top: '23px',
        left: '865px'
      };



      $('#advanced-search-form-focus #submit_search_advanced_focus').addClass('loader').attr("disabled", "disabled");
      $('#advanced-search-form-focus #submit_search_advanced_focus').parent().css("position", "relative");

      loaderAdvanced = new Spinner(opts).spin($('#advanced-search-form-focus #submit_search_advanced_focus').parent().get(0));

      $("#advanced-search-infos-focus").fadeIn();
    }
  });

});
