<!DOCTYPE html>
<html>

<?php

// En premier lieu, nous démarrons la session.
session_start();

// Nous nous connectons à la base de donnée. 
$servname = "localhost";
$dbname = "colnet";
$user = "root";
$pass = "";

$dbco = new PDO("mysql:host=$servname;dbname=$dbname;port=3306", $user, $pass);
$dbco->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Si nous ne sommes pas connectés, on redirige l'utilisateur vers login.
if (!$_SESSION["isConnect"]) {
    header("Location: login.php");
}

//Déconnexion
if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
}


// On crée une fonction qui nous permettra d'ajouter un groupe. 
function ajoutGroupe($post)
{

    global $dbco;
        try {
           
            $sth = $dbco->prepare("INSERT INTO groupe (code, nom, type) VALUES (:code, :nom, :type)");

            $sth->bindParam("code", $post["code"]);
            $sth->bindParam("nom", $post["nom"]);
            $sth->bindParam("type", $post["typeGroupe"]);

            $sth->execute();
            return true;


        } catch (PDOException $e) {

            return "Erreur: " . $e->getMessage();
        }
}

//On crée une fonction qui nous permettra d'ajouter un étudiant. 
function ajoutEtudiant($post)
{
    global $dbco;
    try {
        $sth = $dbco->prepare("INSERT INTO etudiant (codePermanent, nomComplet, adresse, telephone, moyenne, codeGroupe) VALUES (:codePermanent, :nomComplet, :adresse, :telephone, :moyenne, :codeGroupe)");

        $sth->bindParam("codePermanent", $post["codePermanent"]);
        $sth->bindParam("nomComplet", $post["nomComplet"]);
        $sth->bindParam("adresse", $post["adresse"]);
        $sth->bindParam("telephone", $post["telephone"]);
        $sth->bindParam("moyenne", $post["moyenne"]);
        $sth->bindParam("codeGroupe", $post["codeGroupe"]);

        $sth->execute();

        return true;
    } catch (PDOException $e) {
        //Précision sur erreur connexion
        return "Erreur: " . $e->getMessage();
    }
}

/**
 * On crée une fonction qui nous permet d'afficher le titre de la page.
 * 
 * @return void
 */
function afficherTitre()
{
    $htmltitre = <<<EOT
        <h1>Colnet O'Sullivan</h1>
        <br>
        <img src="image/logo.jpg">
        <br><br>
        EOT;
    echo $htmltitre;
}

/**
 * On crée une fonction qui permet d'affficher les choix du menu.
 * @return void
 */
function afficherChoix()
{
    $htmlchoix = <<<EOT
    <h3 class="mb-4" >Veuillez choisir parmi les options suivantes </h3>
    
    <div class="menu mb-3">
        <a class="" href="index.php?menu=AjoutGroupe">Ajouter un groupe</a>
        <a class="" href="index.php?menu=AjoutEtudiant">Ajouter un étudiant</a>
        <a class="" href="index.php?menu=afficherdonnees">Afficher données</a>
        <a class="" href="index.php?menu=compiler">Compliler statistiques</a>
    </div>
    EOT;
    echo $htmlchoix;
}


//On crée une fonction qui nous permet de valider si nous sommes en contexte de post lors de la soumission du formulaire d'ajout de groupe. 
function premiereValidationGroupe($post)
{
    $errors = [];

    if (isset($post['code']) && isset($post['nom'])) {
        if (empty($post["code"])) {
            $errors[] = "Le code est requis";
        }
        if (empty($post["nom"])) {
            $errors[] = "Le nom est requis";
        }
    } else {
        $errors[] = "Plusieurs éléments sont manquants pour ce formulaire, veuillez le compléter et le soumettre de nouveau";
    }
    return $errors;
}
//On crée une fonction qui nous permet de valider si nous sommes en contexte de post lors de la soumission du formulaire d'ajout d'étudiant. 
function premiereValidationEtudiant($post)
{
    $errors = [];

    if (isset($post['codePermanent']) && isset($post['nomComplet']) && isset($post['adresse']) && isset($post['telephone']) && isset($post['moyenne']) && isset($post['codeGroupe'])) {
        if (empty($post["codePermanent"])) {
            $errors[] = "Le code permanent est requis";
        }
        if (empty($post["nomComplet"])) {
            $errors[] = "Le nom complet est requis";
        }
        if (empty($post["adresse"])) {
            $errors[] = "L'adresse est requis";
        }
        if (empty($post["telephone"])) {
            $errors[] = "Le téléphone est requis";
        }
        if (empty($post["moyenne"])) {
            $errors[] = "La moyenne est requis";
        }
        if (empty($post["codeGroupe"])) {
            $errors[] = "Le groupe est requis";
        }
    } else {
        $errors[] = "Plusieurs éléments sont manquants pour ce formulaire, veuillez le compléter et le soumettre de nouveau";
    }
    return $errors;
}

/**
 * Deuxieme validation
 * Cette validation permet de valider le format des éléments reçus dans le POST du formlulaire d'ajout de groupe. 
 * @param array $post
 * @return array
 */
function deuxiemeValidationGroupe($post)
{
    $errors = [];

    $tbl_regex = [
        "code" => "^([A-Z]{4}[0-9]{2}[A-Z]{1})$",
        "nom" => "^[a-zA-Z0-9\ \-\']{10,}$",
    ];

    $tbl_form = [
        "code" => "WEBH21C",
        "nom" => "caractères alphanumériques seulement"
    ];

    foreach ($post as $key => $value) {
        if ($key != "AjouterGroupe") {
            if (isset($tbl_regex["$key"]) && !preg_match("/" . $tbl_regex["$key"] . "/", $value)) {
                $errors[] = "<div class='warning'>Le paramètre <b>$key</b> ne semble pas être défini correctement. Veuillez utiliser la forme suivante: </div>" . $tbl_form["$key"];
            }
        }
    }

    return $errors;
}

/**
 * Deuxieme validation
 * Cette validation permet de valider que le format des éléments reçus dans le POST d'envoie du formulaire d'ajout d'étudiant est correct. 
 * @param array $post
 * @return array
 */
function deuxiemeValidationEtudiant($post)
{
    $errors = [];

    $tbl_regex = [
        "codePermanent" => "^[A-Z]{4}[0-9]{6}$", 
        "nomComplet" => "^[a-zA-Z\ \-\']{1,}$",  
        "adresse" => "^[a-zA-Z0-9\ \-\']{1,}$", 
        "telephone" => "^[0-9]{3}[\-][0-9]{3}[\-][0-9]{4}$", 
 
    ];

    $tbl_form = [
        "codePermanent" => "Seulement des lettres majuscules, 10 caractères et 4 lettres suivies de 6 chiffres comme par exemple JEDO123456",
        "nomComplet" => "Doit contenir que des lettres, des espaces, des traits d’unions et des apostrophes",
        "adresse" => "Doit contenir que des lettres, des espaces, des chiffres, des traits d’unions et des apostrophes",
        "telephone" => "Comme par exemple 418-111-5522",
    ];

    foreach ($post as $key => $value) {
        if ($key != "AjouterEtudiant") {
            if (isset($tbl_regex["$key"]) && !preg_match("/" . $tbl_regex["$key"] . "/", $value)) {
                $errors[] = "<div class='warning'>Le paramètre <b>$key</b> ne semble pas être défini correctement. Veuillez utiliser la forme suivante: </div>" . $tbl_form["$key"];
            }
        }
    }

    return $errors;
}

/**
 * Cette fonction permet d'afficher le formulaire d'ajout de groupe.
 * @return void
 */
function afficherAjouterGroupe()
{
    $htmlAjoutGroupe = <<<EOT
    <h3 class="mb-4" >Ajouter un groupe</h3>
    <form class="mb-4" id="formAjoutGroupe" method="post" action="index.php?menu=AjoutGroupe" autocomplete="off">
        <div class="row align-content-center block-item">
            <label class="col-12 col-sm-6" for="code">Code :</label>
            <input class="col-12 col-sm-6" type="text" name="code" id="code" required="true">
        </div>
        <div class="row align-content-center block-item">
            <label class="col-12 col-sm-6" for="nom">Nom :</label>
            <input class="col-12 col-sm-6" type="text" name="nom" id="nom" required="true">
        </div>
        <div class="row align-content-center block-item mb-4">
            <label class="col-12 col-sm-6" for="typeGroupe">Type :</label>
            <select class="col-12 col-sm-6" id="typeGroupe" name="typeGroupe">
                <option value="En ligne">En ligne</option>
                <option value="En classe">En classe</option>
                <option value="Hybride">Hybride</option>
              </select>
        </div>
        <div class="row justify-content-center block-item">
            <button class="align-content-center block-item" type="submit" name="AjouterGroupe">Ajouter</button>
        </div>
    </form>

    <h3 class="block-item">Revenir vers l'<a href="index.php">acceuil</a>.</h3><br>
    EOT;
    echo $htmlAjoutGroupe;
}

/**
 * Cette fonction permet d'afficher le formulaire d'ajout d'étudiant.
 * @return void
 */
function afficherAjouterEtudiant()
{
    global $dbco;

    /*Sélectionne les valeurs dans les colonnes prenom et mail de la table *users pour chaque entrée de la table*/
    $sth = $dbco->prepare("SELECT code FROM groupe");
    $sth->execute();
    /*Retourne un tableau associatif pour chaque entrée de notre table *avec le nom des colonnes sélectionnées en clefs*/
    $resultat = $sth->fetchAll(PDO::FETCH_ASSOC);
    $groupeOptions = "";
    foreach ($resultat as $ligne) {
        $code = $ligne['code'];
        $groupeOptions = $groupeOptions . '<option value="' . $code . '">' . $code . '</option>';
    }

    $htmlAjoutEtudiant = <<<EOT
    <h3 class="mb-4" >Ajouter un étudiant</h3>
    <form class="mb-4" id="formEtudiant" method="post" action="index.php?menu=AjoutEtudiant" autocomplete="off">
    <div class="row align-content-center block-item">
    <label class="col-12 col-sm-6" for="codePermanent">Code permanenant :</label>
    <input class="col-12 col-sm-6" type="text" name="codePermanent" id="codePermanent" required="true">
    </div>
    <div class="row align-content-center block-item">
    <label class="col-12 col-sm-6" for="nomComplet">Nom complet :</label>
    <input class="col-12 col-sm-6" type="text" name="nomComplet" id="nomComplet" required="true">
    </div>
    <div class="row align-content-center block-item">
    <label class="col-12 col-sm-6" for="adresse">Adresse :</label>
    <input class="col-12 col-sm-6" type="text" name="adresse" id="adresse" required="true">
    </div>
    <div class="row align-content-center block-item">
    <label class="col-12 col-sm-6" for="telephone">Téléphome :</label>
    <input class="col-12 col-sm-6" type="text" name="telephone" id="telephone" required="true">
    </div>
    <div class="row align-content-center block-item">
    <label class="col-12 col-sm-6" for="moyenne">Moyenne : </label>
    <input class="col-12 col-sm-6" type="number" name="moyenne" id="moyenne">
    </div>
    <div class="row align-content-center block-item">
    <label class="col-12 col-sm-6" for="type">Choisir un groupe : </label>
    <select class="col-12 col-sm-6" name="codeGroupe" id="codeGroupe">$groupeOptions
    </select>
    </div>
    <div class="row justify-content-center block-item">
    <button class="align-content-center block-item" type="submit" name="AjouterEtudiant">Ajouter</button>
    </div>
    </form>
    
    <h3 class="block-item">Revenir vers l'<a href="index.php">acceuil</a>.</h3><br>
    EOT;
    echo $htmlAjoutEtudiant;
}

//Cette fonction permet de préparer les requêtes pour l'affichage du tableau de statistiques. 

function executerRequeteStatistique($requete)
{
    global $dbco;
    /*Sélectionne les valeurs dans les colonnes prenom et mail de la table *users pour chaque entrée de la table*/
    $sth = $dbco->prepare($requete);
    $sth->execute();
    /*Retourne un tableau associatif pour chaque entrée de notre table *avec le nom des colonnes sélectionnées en clefs*/
    $resultat = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $resultat[0];
}

/**
 * Cette fonction permet d'afficher les statistiques.
 * @return void
 */
function afficherStatistiques()
{
    $nombreEtudiants = executerRequeteStatistique("SELECT count(*) as nombreEtudiants FROM etudiant")['nombreEtudiants'];
    $nombreEtudiantsReussite =  executerRequeteStatistique("SELECT count(*) as nombreEtudiantsReussite FROM etudiant WHERE moyenne >= 12")['nombreEtudiantsReussite'];
    $nombreEtudiantsEnLigne =  executerRequeteStatistique("SELECT COUNT(*) as nombreEtudiantsEnLigne FROM etudiant as E INNER JOIN groupe AS G ON G.code = E.codeGroupe WHERE G.type = \"En ligne\"")['nombreEtudiantsEnLigne'];
    $nombreEtudiantsEnClasse =  executerRequeteStatistique("SELECT COUNT(*) as nombreEtudiantsEnClasse FROM etudiant as E INNER JOIN groupe AS G ON G.code = E.codeGroupe WHERE G.type = \"En classe\"")['nombreEtudiantsEnClasse'];
    $nombreEtudiantsHybride =  executerRequeteStatistique("SELECT COUNT(*) as nombreEtudiantsHybride FROM etudiant as E INNER JOIN groupe AS G ON G.code = E.codeGroupe WHERE G.type = \"Hybride\"")['nombreEtudiantsHybride'];

    $nombreReussiteEnLigne =  executerRequeteStatistique("SELECT COUNT(*) as nombreReussiteEnLigne FROM etudiant as E INNER JOIN groupe AS G ON G.code = E.codeGroupe WHERE G.type = \"En ligne\" AND E.moyenne >= 12")['nombreReussiteEnLigne'];
    $nombreReussiteEnClasse =  executerRequeteStatistique("SELECT COUNT(*) as nombreReussiteEnClasse FROM etudiant as E INNER JOIN groupe AS G ON G.code = E.codeGroupe WHERE G.type = \"En classe\" AND E.moyenne >= 12")['nombreReussiteEnClasse'];
    $nombreReussiteHybride =  executerRequeteStatistique("SELECT COUNT(*) as nombreReussiteHybride FROM etudiant as E INNER JOIN groupe AS G ON G.code = E.codeGroupe WHERE G.type = \"Hybride\" AND E.moyenne >= 12")['nombreReussiteHybride'];

    if ($nombreEtudiantsEnLigne > 0) {
        $pourcentageReussiteEnLigne = round($nombreReussiteEnLigne / $nombreEtudiantsEnLigne * 100);
    } else {
        $pourcentageReussiteEnLigne = 0;
    }
    if ($nombreEtudiantsEnClasse > 0) {
        $pourcentageReussiteEnClasse = round($nombreReussiteEnClasse / $nombreEtudiantsEnClasse * 100);
    } else {
        $pourcentageReussiteEnLigne = 0;
    }
    if ($nombreEtudiantsHybride > 0) {
        $pourcentageReussiteHybride = round($nombreReussiteHybride / $nombreEtudiantsHybride * 100);
    } else {
        $pourcentageReussiteHybride = 0;
    }

    $htmlStatistiques = <<<EOT
    <h3 class="mb-4" >Veuillez consulter les statistiques des étudiants</h3>
    <div>$nombreEtudiants étudiants ont été évalués.</div><br/>
    <div>$nombreEtudiantsReussite  étudiants ont réussis.</div><br/>
    <div>Le taux de réussite en ligne est $pourcentageReussiteEnLigne%.</div><br/>
    <div>Le taux de réussite en classe est $pourcentageReussiteEnClasse%.</div><br/>
    <div>Le taux de réussite en hybride est $pourcentageReussiteHybride%.</div><br/>

    <h3 class="block-item">Revenir vers l'<a href="index.php">acceuil</a>.</h3><br>
    EOT;
    echo $htmlStatistiques;
}

/**
 * Cette fonction permet d'afficher les données. 
 * @return void
 */
function afficherDonnees()
{
    global $dbco;

    /*Sélectionne les valeurs dans les colonnes prenom et mail de la table *users pour chaque entrée de la table*/
    $sth = $dbco->prepare("SELECT code FROM groupe");
    $sth->execute();
    /*Retourne un tableau associatif pour chaque entrée de notre table *avec le nom des colonnes sélectionnées en clefs*/
    $resultat = $sth->fetchAll(PDO::FETCH_ASSOC);
    $groupeOptions = "";
    foreach ($resultat as $ligne) {
        $code = $ligne['code'];
        $groupeOptions = $groupeOptions . '<option value="' . $code . '">' . $code . '</option>';
    }

    $tableResultats = "";
    if (isset($_POST["groupe"]) && isset($_POST["triMoyenne"])) {
        try {
            if ($_POST["triMoyenne"] == "ASC") {
                $sth = $dbco->prepare("SELECT * FROM etudiant WHERE codeGroupe=:groupeParam ORDER BY moyenne ASC");
            } else {
                $sth = $dbco->prepare("SELECT * FROM etudiant WHERE codeGroupe=:groupeParam ORDER BY moyenne DESC");
            }
            $sth->bindParam("groupeParam", $_POST["groupe"]);
            $sth->execute();
            $resultatEtudiants = $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Erreur: " . $e->getMessage();
        }
        $lignesEtudiants = "";
        foreach ($resultatEtudiants as $etudiant) {
            $codePermanent = $etudiant['codePermanent'];
            $nomComplet = $etudiant['nomComplet'];
            $adresse = $etudiant['adresse'];
            $telephone = $etudiant['telephone'];
            $moyenne = $etudiant['moyenne'];
            $groupe = $etudiant['codeGroupe'];

            $lignesEtudiants = $lignesEtudiants . "<tr><td>" . $codePermanent . "</td><td>" . $nomComplet . "</td><td>" . $adresse . "</td><td>" . $telephone . "</td><td>" . $moyenne . "</td><td>" . $groupe . "</td></tr>";
        }

        $tableResultats = "
        <h2>Résultats</h2>
        <table>
            <tr>
                <th>Code permanent</th>
                <th>Nom complet</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th>Moyenne</th>
                <th>Groupe</th>
            </tr>
            " . $lignesEtudiants . "
        </table>";
    }

    $htmlDonnes = <<<EOT
        $tableResultats
        <h2 class="mb-4" >Veuillez appliquez vos filtres</h2>
        <form class="mb-4" id="formDonnees" method="post" action="index.php?menu=afficherdonnees" autocomplete="off">
        <div class="row align-content-center block-item mb-4">
            <label class="col-12 col-sm-6" for="groupe">Choisir un groupe: </label>
            <select class="col-12 col-sm-6 "name="groupe" id="groupe">
                $groupeOptions
            </select>
        </div>
        <div class="row align-content-center block-item mb-4">
            <label class="col-12 col-sm-6" for="triMoyenne">Tri sur la moyenne: </label>
            <select class="col-12 col-sm-6" name="triMoyenne" id="triMoyenne">
                <option value="ASC">Ascendant</option>
                <option value="DESC">Descendant</option>
            </select>
        </div>
        <div class="row justify-content-center block-item">
        <button class="align-content-center block-item" type="submit" name="AfficherDonnees">Afficher Résultats</button>
        </div>
        </form>
        <h3>Revenir vers l'<a href="index.php">accueil</a></h3><br>
        EOT;
    echo $htmlDonnes;
}

/**
 * Convertit en chaîne de caractères les erreurs en cours
 * @param array $errors
 * @return string
 */
function convertirEnChaineLesErreurs($errors)
{
    $erreur = "<div class='pre'>";

    foreach ($errors as $error) {
        $erreur .= "$error" . "<br/>";
    }

    $erreur .= "</div>";
    return $erreur;
}
?>

<head>
    <meta charset="UTF-8">
    <title>Colnet - Projet Final</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>


<body>

    <?php
    afficherTitre()
    ?>
    <?php

    # Sommes-nous dans un contexte de soumission de formulaire?
    $global_errors = [];
    $resultAjoutGroupe = false;
    // Si on vient d'envoyer le formulaire d'ajouter un groupe: faire sa validation.
    if (isset($_POST['AjouterGroupe'])) {
        # Validons d'abord si nous avons tous les champs postés.
        $errors = premiereValidationGroupe($_POST);
        if (!count($errors) || empty($errors) || is_null($errors)) {
            # Validons maintenant si les formes sont respectées.
            $second_errors = deuxiemeValidationGroupe($_POST);
            if (!count($second_errors) || empty($second_errors) || is_null($second_errors)) {
                // Ajouter le groupe.
                $resultAjoutGroupe = ajoutGroupe($_POST);
                if ($resultAjoutGroupe === true)
                    echo '<div class="succes">Le groupe ' . $_POST['code'] . ' a été ajouté avec succès.</div>';
                else
                    $global_errors[] = $resultAjoutGroupe;
            } else {
                # Le script a détecté des erreurs, merge les erreurs ensemble.
                $global_errors = array_merge($global_errors, $second_errors);
            }
        } else {
            # Le script a détecté des erreurs, merge les erreurs ensemble
            $global_errors = array_merge($global_errors, $errors);
        }
    }

    // Si on vient d'envoyer le formulaire d'ajouter un groupe, faire sa validation.
    if (isset($_POST['AjouterEtudiant'])) {
        # Validons d'abord si nous avons tous les champs postés.
        $errors = premiereValidationEtudiant($_POST);
        if (!count($errors) || empty($errors) || is_null($errors)) {
            # Validons maintenant si les formes sont respectées.
            $second_errors = deuxiemeValidationEtudiant($_POST);
            if (!count($second_errors) || empty($second_errors) || is_null($second_errors)) {
                // Ajouter l'étudiant.
                $resultAjoutEtudiant = ajoutEtudiant($_POST);
                if ($resultAjoutEtudiant === true)
                    echo '<div class="succes">L\'étudiant(e) ' . $_POST['nomComplet'] . ' a été ajouté(e) avec succès.</div>';
                else
                    $global_errors[] = $resultAjoutEtudiant;
            } else {
                # Le script a détecté des erreurs; merge les erreurs ensemble.
                $global_errors = array_merge($global_errors, $second_errors);
            }
        } else {
            # Le script a détecté des erreurs; merge les erreurs ensemble.
            $global_errors = array_merge($global_errors, $errors);
        }
    }

    if (count($global_errors)) {
        echo convertirEnChaineLesErreurs($global_errors);
    }
    ?>
    <?php
    # Testons si le paramètre de menu est présent.
    if (!isset($_GET["menu"])) {
        # S'il n'est pas présent; affiche la page des choix.
        afficherChoix();
    } else if ($_GET["menu"] == "AjoutGroupe") {
        # Afficher le formlulaire d'ajout de groupe.
        afficherAjouterGroupe();
    } else if ($_GET["menu"] == "AjoutEtudiant") {
        # Afficher le formulaire d'ajout d'étudiant".
        afficherAjouterEtudiant();
    } else if ($_GET["menu"] == "afficherdonnees") {
        # Afficher les données.
        afficherDonnees();
    } else if ($_GET["menu"] == "compiler") {
        # Afficher les statistiques.
        afficherStatistiques();
    }
    ?>

    <form method="post" action="index.php">
        <div class="row justify-content-center block-item">
            <button class="button align-content-center block-item" type="submit" name="logout">Se déconnecter</button>
            <div>
    </form>
    <p>©2022 - Collège O'Sullivan de Québec</p>

</body>

</html>