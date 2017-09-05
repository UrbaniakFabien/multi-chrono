<?php
session_start();
/* * *************************************************************
 * Liste de pointage version Table de marque
 * Gestion de l'état 3 joueur absence autorisée
 * Gestion de la saisie d'uncommentaire sur absence autorisée 
 * FU
 * 04/2014  
 * ************************************************************* */
include("connect.7.php");
//Mise en place du tableau
// on fait une boucle qui va faire un tour pour chaque enregistrement
include ("liste_joueurs.7.php")
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">

        <title>Liste de pointage : table de marque</title>
          <link rel="stylesheet" type="text/css" href="css/menu_horiz.css" />
        <link rel="stylesheet" type="text/css" href="jquery/css/ColumnFilterWidgets.css" />
        <link rel="stylesheet" type="text/css" title="currentStyle" href="css/liste.css" />
        <link href="jquery/jquery-ui-1.10.2.custom/css/cupertino/jquery-ui-1.10.2.custom.min.css" rel="stylesheet" />
      
        <link rel="stylesheet" type="text/css" title="currentStyle" href="jquery/DataTables-1.9.0/media/css/demo_page.css" />
        <link rel="stylesheet" type="text/css" title="currentStyle" href="jquery/DataTables-1.9.0/media/css/demo_table.css" />
        <script type="text/javascript" src="jquery/jquery-2.1.3.js"></script>
        <script type="text/javascript" src="js/menu.js"></script>
        <script type="text/javascript" src="jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>

        <script type="text/javascript" src="jquery/DataTables-1.9.0/media/js/jquery.DataTables.min.js"></script>

        <script type="text/javascript" src="jquery/js/ColumnFilterWidgets.js" ></script>
        <script type="text/javascript" src="jquery/jquery.ui.menubar.js"></script>
        <script type="text/javascript">
            var oTable;
            /* Define two custom functions (asc and desc) for string sorting */
            jQuery.fn.dataTableExt.oSort['num_match-asc'] = function(p_x, p_y) {
                var t_x = p_x.split(",");
                var t_y = p_y.split(",");
                if (t_x.length > 0) {
                    x = parseInt(t_x[0], 10)
                } else {
                    x = parseInt(p_x, 10)
                }
                if (t_y.length > 0) {
                    y = parseInt(t_y[0], 10)
                } else {
                    y = parseInt(p_y, 10)
                }
                return ((x < y) ? -1 : ((x > y) ? 1 : 0));
            };

            jQuery.fn.dataTableExt.oSort['num_match-desc'] = function(p_x, p_y) {
                var t_x = p_x.split(",");
                var t_y = p_y.split(",");
                if (t_x.length > 0) {
                    x = parseInt(t_x[0], 10)
                } else {
                    x = parseInt(p_x, 10)
                }
                if (t_y.length > 0) {
                    y = parseInt(t_y[0], 10)
                } else {
                    y = parseInt(p_y, 10)
                }
                return ((x < y) ? 1 : ((x > y) ? -1 : 0));
            };

            $.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
                //redraw to account for filtering and sorting
                // concept here is that (for client side) there is a row got inserted at the end (for an add)
                // or when a record was modified it could be in the middle of the table
                // that is probably not supposed to be there - due to filtering / sorting
                // so we need to re process filtering and sorting
                // BUT - if it is server side - then this should be handled by the server - so skip this step
                if (oSettings.oFeatures.bServerSide === false) {
                    var before = oSettings._iDisplayStart;
                    oSettings.oApi._fnReDraw(oSettings);
                    //iDisplayStart has been reset to zero - so lets change it back
                    oSettings._iDisplayStart = before;
                    oSettings.oApi._fnCalculateEnd(oSettings);
                }

                //draw the 'current' page
                oSettings.oApi._fnDraw(oSettings);
            };
            function callComplete(reponse) {
                /*Mise à jour du tableau si modification de la base
                 reponse contient le Num et l'état des lignes modifiées
                 */

                for (i = 0; i < reponse.length; i++) {
                    var etat = reponse[i].etat;
                    //Pour forcer l'état à 0 si vide
                    if (etat == "") {
                        etat = "0"
                    }
                    // Bascule de la couleur d'arriére-plan en fonction de l'état        
                    $(oTable.fnGetNodes()).filter("#num" + reponse[i].num).toggleClass("etat1", reponse[i].etat == "1")
                                                                          .toggleClass("etat2", reponse[i].etat == "2")
                                                                          .toggleClass("etat3", reponse[i].etat == "3")
                                                                          ;
                    // mise a jour de la colonne etat sans redessiner le tableau           
                    oTable.fnUpdate(etat, i, 0, false, false);
                    //mise a jour de l'infobulle
                    if (reponse[i].etat=="3") {
                      $("#num"+reponse[i].num).attr('title',reponse[i].commentaire)
                                              .attr('commentaire',reponse[i].commentaire);
                    }
                }
                // re-dessine le tableau sans toucher l'affichage de la pagination en cours    
                oTable.fnStandingRedraw();
                // Relance la mise a jour 
                var t = setTimeout("connect();", $("#tempo").val() * 1000);   //Appel Temporisé
            }
            ;

            function connect() {
                // boucle infinie : demande de donnée toutes les 15s

                $.post('ajax/retourmaj.5.2.php', {}, callComplete, 'json');

            }
            ;
            // Initialisation du document
            $(document).ready(function() {
                $("tbody>tr").tooltip(
                );
                //Fonction sur clic de toutes les lignes du tableau
                $("tbody>tr").each(function() {
                    var $thisParagraph = $(this);
                    var count = 0;
                    $thisParagraph.dblclick(function() {
                        var aPos = oTable.fnGetPosition(this); //Indice de la ligne
                        var id =  $thisParagraph.attr("id") ;
                        // Valeur de l'état
                        count = oTable.fnGetData(aPos, 0);
                        count++;
                        $thisParagraph.toggleClass("etat1", count == 1)
                                      .toggleClass("etat2", count == 2)
                                      .toggleClass("etat3", count == 3)
                                      ;
                        if (count >= 4) {
                            count = 0
                        }
                        ;
                        // Mise a jour de l'état
                        oTable.fnUpdate(count, aPos, 0, false, false);
                        // re-dessine le tableau sans toucher l'affichage de la pagination en cours    
                        oTable.fnStandingRedraw();
                        if (count==3) {
                            $("#commentaire").val($thisParagraph.attr("commentaire"));
                            $("#id_lig").val(id);
                            $("#frm_commentaire").dialog("open");
                        }
                        //Mise à jour de la base 
                        $.ajax({
                            type: "POST",
                            url: "ajax/majliste.5.2.php",
                            data: {num :id,
                                   etat: count
                                  }
                        });

                    });
                });
                // Initialisation de l'affichage du tableau
                oTable = $('#liste').dataTable({
                    "sPaginationType": "full_numbers",
                    "oLanguage": {"sUrl": "jquery/DataTables-1.9.0/media/language/fr_FR.txt"},
                    "aoColumnDefs": [
                        {"bSortable": false, "bVisible": false, "aTargets": [0]}, //cache la colonne etat
                        {"sType": "num_match", "aTargets": [3]} //tri sur N° de match par fonction perso
                    ],
                    "sDom": 'W<"clear">lfrtip',
                    "oColumnFilterWidgets": {
                        "aiExclude": [0, 1, 2, 3]
                    },
                    "aaSorting": [[1, "asc"]], //Tri par défaut sur le nom

                    "fnInitComplete": function() {
                        connect()//Lancement de la boucle de  raffraichissment des données dés que le tableau est en place 
                    }
                });
                //Modification de la largeur occupée par le tableau
                $("#liste").attr("width", "90%");

                //Prend en charge le changement de valeur du filtre
                $('input[type=radio][name=filtre]').click(function() {
                    var filtre = $('input[type=radio][name=filtre]:checked').attr('value'); //Retourne la valeur du bouton radio selectionné
                    filtre_tableau(filtre);
                });
                $(function() {
                    $("#menuprinc").menubar({
                        autoExpand: true,
                        menuIcon: true,
                        buttons: true,
                        position: {at: "left bottom"}
                    });
                });
             /******************************************************************
              * Formulaire saisi du commentaire sur joueur état absence autorisée
              ******************************************************************/
              $("#frm_commentaire").dialog({
                    title:'Informations complémentaires',
                    width:'auto',
                    height:'auto',
                    modal:true,
                    autoOpen:false,
                     buttons: [
                        {
                            text: "Enregistre",
                            'click': function() {
                                                  var commentaire=  $("#commentaire").val() ;
                                                  var id=  $("#id_lig").val();
                                                  $.ajax({
                                                            type: "POST",
                                                            url: "ajax/majliste.5.2.php",
                                                            data: {num : id ,
                                                                   etat: 3,
                                                                   commentaire: commentaire
                                                                  }
                                                  });
                                                  $("#"+id).attr("title",commentaire)
                                                           .attr("commentaire",commentaire);
                                  
                                                   $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-disk'}},
                        {
                            text: "Quitter",
                            'click': function() {

                                $(this).dialog("close");
                            },
                            icons: {primary: 'ui-icon-close'}
                        }]
                    
              }); 
               //fonction pour tester si click droit
            function isRightClick(event) {
                return event.button == 2;
            }                             
              $(document).delegate("tbody>tr", "mousedown", function(event) {
                    var self = $(this);
                    event.stopPropagation(); // Stop it bubbling

                    // Make sure it needs to be shown
                    function showIt(event) {
                        return isRightClick(event) && $(event.target).closest('tbody>tr')[0] == self[0];
                    }

                    if (!showIt(event)) {
                        return true;
                    }
                    //Affichage du formulaire
                    $("#commentaire").val(self.attr('commentaire'));
                    $("#id_lig").val(self.attr('id'));
                    $("#frm_commentaire").dialog("open");

                })
                        // Little snippet that stops the regular right-click menu from appearing !contextmenu est un mot clef designant la fonctionnalité menu contextuel
                        .bind('contextmenu', function() {
                    return false;
                }); 
                 /***************************************************************
                 * Menu général
                 * *************************************************************/                                 
                $("#menuprinc").menu({ position: { using: positionnerSousMenu} });  
            }); //Fin document ready

            function filtre_tableau(filtre) {
                var test = (filtre != "99");
                if (!test) {
                    filtre = ""
                }
                oTable.fnFilter(filtre, 0);
            }


        </script>
        

    </head>
    <body>
<?php include ("menu.5.1.php"); ?>

        <div style="float:left;width:300px;">
            Délai de rafraichissement : 
            <select id="tempo">
                <option value='1'>1 s</option>
                <option value='5'>5 s</option>
                <option value='10' selected>10 s </option>
                <option value='15'>15 s </option>
            </select> &nbsp;
        </div>
        <div style="float:right;">
            Filtre : <input type="radio" id="filtre" name="filtre" value="99" checked/>Tous&nbsp;
            <input type="radio" id="filtre" name="filtre" value="1" /><span class="etat1">Présents&nbsp;</span>
            <input type="radio" id="filtre" name="filtre" value="0" />En attente&nbsp;
            <input type="radio" id="filtre" name="filtre" value="2" /><span class="etat2">Absents (WO)&nbsp;</span>
            <input type="radio" id="filtre" name="filtre" value="3" ><span class="etat3">Absents autorisés&nbsp;</span>
        </div>
        <br />

<?php
echo $entete . $corps;
?>
<!-- formulaire commentaire sur joueur état absent sur ok JA --> 
<?php 
echo $formulaire;
?>
    </body>
</html>