<!DOCTYPE html>
<html>

<?php
/**
 * Initialiser une connexion à une Base de Données
 * 
 * @return mixed (PDO or PDOException)
 */
session_start();


//Ici, on a la fonction qui nous pemettre de nous connecter à la base de donnée et de démarrer la session à condition de trouver l'utilisateur dans la base de donnée.
function validationUtilisateur()
{
    try {
        $servname = "localhost";
        $dbname = "colnet";
        $user = "root";
        $pass = "";

        $dbco = new PDO("mysql:host=$servname;dbname=$dbname;port=3306", $user, $pass);
        $dbco->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sth = $dbco->prepare("SELECT * FROM utilisateur
            WHERE username = :username
            AND   motDePasse = :motDePasse");

        $sth->bindParam('username', $_POST["username"]);
        $sth->bindParam('motDePasse', $_POST["motDePasse"]);

        $sth->execute();
        $resultat = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (count($resultat) != 0) {
            $_SESSION['username'] = $resultat['username'];
            $_SESSION["isConnect"] = true;
            header("Location:index.php");
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

//Ici, on a la fonction qui nous permettra d'afficher le formulaire de connexion. 
function formlogin()
{
    $login = <<<EOT
<h1>Colnet O'Sullivan</h1>
<br>
<img src="image/logo.jpg">
<br><br>
<h3>Veuillez vous connecter</h3>
<form id="form_login" method="post" action="login.php" autocomplete="off">
<div class="row align-content-center block-item">
<label class="col-12 col-sm-6">Nom d'utilisateur : </label>
<input class="col-12 col-sm-6" type="text" name="username" required="true">
</div>
<div class="row align-content-center block-item">
<label class="col-12 col-sm-6">Mot de passe : </label>
<input class="col-12 col-sm-6" type="password" name="motDePasse" required="true">
</div>
<div class="form-row block-item">
<button class="col-12 col-sm-6 " type="submit" name="connexion">Connexion</button>
<button class="col-12 col-sm-6 " method="post" type="submit" onclick="location.href='signup.php'" name="signup">Créer un compte</button>
</div>
</form>
EOT;
    echo $login;
}

//Ici, on a la fonction qui nous permet d'afficher le message d'erreur en cas d'échec de connexion. 
function messageDechec()
{
    $echecconnexion = <<<EOT
            <h4 class="warning block-item">Une erreur est survenue. Veuillez réessayer ou vous créer un compte.</h4>
            EOT;
    echo $echecconnexion;
}

//function messageSucces()
//{
//$succesconnexion = <<<EOT
//<h4 class="succes block-item">Vous êtes maintenant connecté.<br> 🥳🥳🥳<br> Accèdez à l'accueil 👉 <a href="index.php">ici</a>.</h4>
//EOT;
//echo $succesconnexion;
//}

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
    //On commence par envoyer le formulaire. 
    formlogin();
    //Ensuite on valide l'identité de l'utilisateur. Et on le redirige directement vers la page index.php en cas de succès. 
    validationUtilisateur();
    //On indique à l'utiliser que la connexion a écoué en cas d'erreur de saisi ou autre. 
    if (isset($_POST["motDePasse"]) || isset($_POST["username"])) {
        messageDechec();
    }
    ?>

    <p>©2022 - Collège O'Sullivan de Québec</p>


</body>

</html>