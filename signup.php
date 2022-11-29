<!DOCTYPE html>
<html>

<?php
/**
 * initialiser une connexion à une Base de Données
 * 
 * @return mixed (PDO or PDOException)
 */
session_start();

// Créer la connection à la base de données
$servname = "localhost";
$dbname = "colnet";
$user = "root";
$pass = "";

$dbco = new PDO("mysql:host=$servname;dbname=$dbname;port=3306", $user, $pass);
$dbco->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);




//On créee la fonction qui servira à enregistrer le nouvelle utilisateur dans la base de donnée. 
function enregistrement($post)
{

    global $dbco;

    try {

        $sth = $dbco->prepare("INSERT INTO utilisateur (nomComplet, username, codePostal, email, motDePasse) VALUES (:nomComplet, :username, :codePostal, :email, :motDePasse)");

        $sth->bindParam('nomComplet', $post["nomComplet"]);
        $sth->bindParam('username', $post["username"]);
        $sth->bindParam('codePostal', $post["codePostal"]);
        $sth->bindParam('email', $post["email"]);
        $sth->bindParam('motDePasse', $post["motDePasse"]);

        $sth->execute();
        return true;
    } catch (PDOException $e) {

        return "Erreur: " . $e->getMessage();
    }
}


//Ici, on valide qu'on est bel et bien dans un contexte de post.
function premiereValidationSignup($post)
{

    $errors = [];

    if (isset($post['nomComplet']) && isset($post['username']) && isset($post['codePostal']) && isset($post['email']) && isset($post['motDePasse'])) {
        if (empty($post["nomComplet"])) {
            $errors[] = "Le nom complet est requis";
        }
        if (empty($post["username"])) {
            $errors[] = "Le nom d'utilisateur est requis";
        }
        if (empty($post["codePostal"])) {
            $errors[] = "Le code postal est requis";
        }
        if (empty($post["email"])) {
            $errors[] = "Le courriel est requis";
        }
        if (empty($post["motDePasse"])) {
            $errors[] = "Le mot de passe est requis";
        }
    } else {
        $errors[] = "Plusieurs éléments sont manquants pour ce formulaire, veuillez le compléter et le soumettre de nouveau";
    }
    return $errors;
}

//Ici on valide que les champs du formulaire d'incription sont adéquatements remplis. 
function deuxiemeValidationSignup($post)
{
    $errors = [];

    $tbl_regex = [
        "nomComplet" => "^[A-Z]{1}([a-zA-Z\ ]){12,}$",
        "username" => "^[a-zA-Z]{8,}$",
        "codePostal" => "^[A-Z][0-9][A-Z][\ \-]?[0-9][A-Z][0-9]$",
        "email" => "^[a-zA-Z0-9\.\-\_]{4,50}\@[a-zA-Z0-9\.\-\_]{4,64}\.[a-zA-Z]{2,4}$",
        "motDePasse" => "^(?!(soleil|motdepasse|password))(?=.{6,18}$)(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\!\$\@\#\%\_\-]).*$"
    ];

    $tbl_form = [
        "nomComplet" => "Taille minimale 12 caractères, minimalement 1 espace et que la première lettre soit en majuscule",
        "username" => "Minimalement 8 caractères, aucun espace, aucun chiffre",
        "codePostal" => "Aucune lettre minuscule comme par exemple Q1Q 1Q1",
        "email" => "info@examenfinal.ca",
        "motDePasse" => "<ul><li>Doit compter 6 à 18 caractères</li><li>Minimalement une lettre minuscule</li><li>Minimalement une lettre majuscule</li><li>Minimalement un chiffre</li><li>Minimalement un caractère entre !$@#%_-</li><li>Ne doit pas comporter les mots suivants: Soleil, Mot de passe ou password.</li></ul>",
    ];

    foreach ($post as $key => $value) {
        if ($key != "subscribe") {
            if (isset($tbl_regex["$key"]) && !preg_match("/" . $tbl_regex["$key"] . "/", $value)) {
                $errors[] = "Le paramètre <b>$key</b> ne semble pas être défini correctement. Veuillez utiliser la forme suivante: " . $tbl_form["$key"];
            }
        }
    }
    return $errors;
}


// Ici, on a la fonction qui permettra d'afficher le formulaire en tant que tel. 
function formulairesignup()
{
    $signup = <<<EOT
    <h1>Colnet O'Sullivan</h1>
    <br>
    <img src="image/logo.jpg">
    <br><br>

    <h3 class="mb-4">Veuillez créer un compte</h3>

    <form method="post" action="signup.php" autocomplete="off">
        <div class="row align-content-center block-item">
            <label class="col-12 col-sm-6">Nom complet :</label>
            <input class="col-12 col-sm-6" type="text" name="nomComplet" required="true">
        </div>
        <div class="row align-content-center block-item">
            <label class="col-12 col-sm-6">Nom d'utilisateur :</label>
            <input class="col-12 col-sm-6" type="text" name="username" required="true">
        </div>
        <div class="row align-content-center block-item">
            <label class="col-12 col-sm-6">Code postal :</label>
            <input class="col-12 col-sm-6" type="text" name="codePostal" required="true">
        </div>
        <div class="row align-content-center block-item">
            <label class="col-12 col-sm-6">Courriel :</label>
            <input class="col-12 col-sm-6" type="email" name="email" required="true">
        </div>
        <div class="row align-content-center block-item mb-5">
            <label class="col-12 col-sm-6">Mot de passe :</label>
            <input class="col-12 col-sm-6" type="password" name="motDePasse" required="true">
        </div>
        <div class="row justify-content-center block-item">
            <button class="align-content-center block-item" type="submit" name="subscribe">S'enregistrer</button>
        </div>
    </form>
    EOT;
    echo $signup;
}

//Ici on a la fonction qui permet de convertir en chaine de caractère les erreurs qu'on aura stocké dans le tableau. 
function convertirEnChaineLesErreurs($errors)
{
    $erreur = "<div class='pre'>";

    foreach ($errors as $error) {
        $erreur .= "$error" . "<br/>";
    }

    $erreur .= "</div>";
    return $erreur;
}

//Ici, on a la fonction qui permet d'afficher le message annoncant que le nouvel utilisateur a bien été enregistré. 
function messageSucces()
{
    $succesconnexion = <<<EOT
    <h4 class="succes block-item">Votre compte a été créé avec succès!<br> 🥳🥳🥳<br> Veuillez vous connecter 👉 <a href="login.php">ici</a>. <br></h4>
    EOT;
    echo $succesconnexion;
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

    formulairesignup();

    # Sommes-nous dans un contexte de soumission de formulaire
    $global_errors = [];
    $resultSignup = false;
    // Si on vient d'envoyer le formulaire d'ajouter un groupe, faire sa validation
    if (isset($_POST['subscribe'])) {
        # Validons d'abord si nous avons tous les champs postés
        $errors = premiereValidationSignup($_POST);

        if (!count($errors) || empty($errors) || is_null($errors)) {

            # Validons maintenant si les formes sont respectées
            $second_errors = deuxiemeValidationSignup($_POST);
            if (!count($second_errors) || empty($second_errors) || is_null($second_errors)) {
                // ajouter le groupe

                $resultSignup = enregistrement($_POST);
                if ($resultSignup === true)
                    messageSucces();
                else
                    $global_errors[] = $$resultSignup;
            } else {
                # le script a détecté des erreurs, merge les erreurs ensemble
                $global_errors = array_merge($global_errors, $second_errors);
            }
        } else {
            # le script a détecté des erreurs, merge les erreurs ensemble
            $global_errors = array_merge($global_errors, $errors);
        }
    }
    if (count($global_errors)) {
        echo convertirEnChaineLesErreurs($global_errors);
    }

    ?>

    <p>©2022 - Collège O'Sullivan de Québec</p>


</body>

</html>