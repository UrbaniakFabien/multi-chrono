<?php

/* * ********************************************
 * Module de connexion à  la base tournoi
 *
 * Passage par le config.ini des parametres pour
 * accés à la base
 * FU
 * 12/2013
 *
 * FU
 * 19/1/2014
 * Mise en tableau des requetes        
 * ******************************************** */

function exec_commande($sql) {
    global $connect;
    try {
        $retour = mysqli_query($connect, $sql);
    } catch (Exception $e) {
        $f = fopen(__DIR__."/logs/sql_error_" . date("Ymd") . ".log", "a+");
        $ligne[] = str_repeat("=", 80);
        $ligne[] = date("d-m-Y h:i") . " : " . $e->getMessage();
        $ligne[] = $sql;
        $ligne[] = "Dans " . $_SERVER["PHP_SELF"];
        $ligne[] = str_repeat("=", 80);
        fwrite($f, implode("\n", $ligne) . "\n");
        fclose($f);
        $retour=false;
    }
    return $retour;
}
//Lecture du parametrage base de données
$tab_config = parse_ini_file("config.ini");
$serveur = $tab_config["nom_serveur_sql"];
$user = $tab_config["nom_utilisateur_sql"];
$password = $tab_config["mdp_sql"];
$db = $tab_config["nom_base_sql"];
$version = $tab_config["version"];

//Définition des sqls pour la création de la base et des tables
$sql = [];
$sql[] = 'CREATE DATABASE IF NOT EXISTS ' . $db . ';';

// et on créé les tables
//Structure de la table `titre` des échéanciers
$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`titre` (
                             `num_titre` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                             `lieu_date` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
                              decalage_horaire_convocation varchar(5)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";

// echeancier
//Informations extraites de l'échéancier exporté depuis Badplus

$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`echeancier` (`horaire` VARCHAR( 20 ) NOT NULL , 
                            `num_match` INT NOT NULL,
                            num_titre INT ,
                            `spe` VARCHAR( 50 ) NOT NULL , 
                            `tableau` VARCHAR( 50 ) NOT NULL ,
                            `terrain` INT NOT NULL,
                            `heure_debut` VARCHAR( 50 ) NOT NULL ,
                            `heure_fin` VARCHAR( 50 ) NOT NULL ,
                            `etat` INT NOT NULL) ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";

// `joueurs`
// Information extraites de la liste des joueurs export�e de Badplus

$sql[] = "CREATE TABLE  IF NOT EXISTS " . $db . ".`joueurs` (
                  `Num` int(11) NOT NULL AUTO_INCREMENT,
                  `Joueur` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
                  `Licences` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
                  `Matchs` varchar(80) CHARACTER SET utf8 DEFAULT NULL, 
                  `Salle` varchar(40) CHARACTER SET utf8 DEFAULT NULL,                                         
                  `Convoqué le` varchar(20) NOT NULL,                 
                  `etat` tinyint(1) DEFAULT 0,
                  num_titre INT(11),
                  commentaire varchar(80) NOT NULL,
                  PRIMARY KEY (`Num`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE utf8_general_ci AUTO_INCREMENT=0 ; ";



// `param`
// Contient les positions des éléments dans l'écran
$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`param` (
                                `num` int(11) NOT NULL DEFAULT '0',
                                `top` int(11) NOT NULL DEFAULT '0',
                                `left` int(11) NOT NULL DEFAULT '0',
                                `orientation` char(1) NOT NULL DEFAULT 'h',
                                num_titre INT(11)
                              ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";

//Structure de la table `tbl_config_chrono`
//Informations de la configuration des chronometres

$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`tbl_config_chrono` (
                                          `Conf_coul_libre` char(7) NOT NULL,
                                          `Conf_coul_occup` char(7) NOT NULL,
                                          `Conf_coul_neutre` char(7) NOT NULL,
                                          `Conf_coul_salle` char(7) NOT NULL,
                                          `Conf_tp1` varchar(5) NOT NULL,
                                          `Conf_tp2` varchar(5) NOT NULL,
                                          `Conf_sens` int(11) NOT NULL,
                                          `Conf_zoom` int(11) NOT NULL,
                                           Conf_son  int(11) NOT NULL,
                                           num_titre INT(11) NOT NULL,
                                           info_bulles tinyint(1) NOT NULL
                                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";


/* * **************************************************************************
 *   Structure de la table `pref_titre`
 *   informations description de la préférence
 *   FU
 *   05/2014            
 * ************************************************************************** */
$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`pref_titre` (
                             `num_titre` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                              pref_nom  varchar(20) NOT NULL,
                              pref_description varchar(100) CHARACTER SET utf8 DEFAULT NULL
                              ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";

/* * ***************************************************************************
 * Table des config préférées `pref_param`
 * Contient les positions des éléments dans l'écran
 *   FU
 *   05/2014            
 * *************************************************************************** */
$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`pref_param` (
                                `num` int(11) NOT NULL DEFAULT '0',
                                `top` int(11) NOT NULL DEFAULT '0',
                                `left` int(11) NOT NULL DEFAULT '0',
                                `orientation` char(1) NOT NULL DEFAULT 'h',
                                num_titre INT(11)
                              ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";

/* * ***************************************************************************
 *  Structure de la table `pref_tbl_config_chrono`
 * Informations de la configuration des chronometres
 *   FU
 *   05/2014            
 * *************************************************************************** */
$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`pref_tbl_config_chrono` (
                                          `Conf_coul_libre` char(7) NOT NULL,
                                          `Conf_coul_occup` char(7) NOT NULL,
                                          `Conf_coul_neutre` char(7) NOT NULL,
                                          `Conf_coul_salle` char(7) NOT NULL,
                                          `Conf_tp1` varchar(5) NOT NULL,
                                          `Conf_tp2` varchar(5) NOT NULL,
                                          `Conf_sens` int(11) NOT NULL,
                                          `Conf_zoom` int(11) NOT NULL,
                                           Conf_son  int(11) NOT NULL,
                                           num_titre INT(11) NOT NULL,
                                           info_bulles tinyint(1) NOT NULL
                                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";


/* * ****************************************************************************
 * Structure de la table tmp_joueurs
 * Sauvegarde de l'état des joueurs d'une liste en cas de ré import aprés le début du pointage
 * **************************************************************************** */
$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`tmp_joueurs` (
                                                          `tmp_id` int(11) NOT NULL,
                                                          `tmp_nom` varchar(50) NOT NULL,
                                                          `tmp_licence` varchar(20) NOT NULL,
                                                          `tmp_etat` int(11) NOT NULL,
                                                           tmp_commentaire  varchar(80) NOT NULL
                                                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";

/* * *****************************************************************************
 * Structure de la table `tbl_couleurs`
 * pour mise en couleur manuelle d'un echeancier
 * FU
 * 19/01/2014   
 * ***************************************************************************** */
$sql[] = "CREATE TABLE IF NOT EXISTS " . $db . ".`tbl_couleurs` (
                                                  `coul_id_titre` int(11) NOT NULL,
                                                  `coul_specialite` char(50) NOT NULL,
                                                  `coul_couleur` char(7) NOT NULL
                                                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
/* * **************************************************************************
 * Structure table des reglements clubs
 * FU
 * 04/07/2017
 */
$sql[] = "CREATE TABLE IF NOT EXISTS $db.`tbl_regl_clubs` ( 
       `reg_id_club` INT NOT NULL AUTO_INCREMENT , 
       `reg_club` VARCHAR(10) NOT NULL , 
        `reg_nbr_joueurs` INT NOT NULL , 
        `reg_total` FLOAT NOT NULL , 
        `reg_deja_regle` FLOAT NOT NULL , PRIMARY KEY (`reg_id_club`)
        ) ENGINE = MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";

$sql[] = "CREATE TABLE IF NOT EXISTS $db.`tbl_regl_joueurs` (
  `reg_joueurs_id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_joueurs_id_fk_club` int(11) NOT NULL,
  `reg_joueurs_nom` varchar(50) NOT NULL,
  `reg_joueurs_club` varchar(10) NOT NULL,
  `reg_joueurs_date` date NOT NULL,
  `reg_joueurs_montant` float NOT NULL,
  `reg_joueurs_regle` tinyint(1) NOT NULL DEFAULT '0',
  `reg_mode_reglement` smallint(6) DEFAULT '0',
  PRIMARY KEY (`reg_joueurs_id`),
  KEY `reg_joueurs_id_fk_club` (`reg_joueurs_id_fk_club`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
//Pour pouvoir gerer les erreurs SQL
mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
// on se connecte au serveur MySQL
$connect = mysqli_connect($serveur, $user, $password) or die('Erreur de connexion MySQL !<br />module connect.7.php<br/>Supprimez ou modifiez le fichier config.ini  ! <br />' . mysqli_error());
foreach ($sql as $e_sql) {
    $reponse = exec_commande($e_sql);
    if ($reponse ===false ) {
        die('Erreur SQL !<br>' . $e_sql . '<br>' . mysqli_error($connect));
    }
}
// on sélectionne la base
mysqli_select_db($connect, $db);

//
//Constantes pour gestion des class et des couleurs de l'échéancier
//
$encours = "90EE90"; //couleur pour match en cours
$termine = "FF7F50"; //Couleur match termine
$pause = "DCDCDC"; //Couleur Pause
$horaire = "CCFFFF";  // Couleur pour horaire
//table de conversion des signes pour le nom des classes
$sign = array(" ", "+", "-", "/", "(", ")", ",", ".", "é", "ç");
$chg_sign = array("_", "PLUS", 'MOINS', "_", "", "", "_", "_", "e", "c");


