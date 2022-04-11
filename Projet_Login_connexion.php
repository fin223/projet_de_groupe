<?php 
    session_start(); // Démarrage de la session
    require_once 'Projet_Login_config.php'; // On inclut la connexion à la base de données

    if(!empty($_POST['email']) && !empty($_POST['password'])) // Si il existe les champs email, password et qu'il sont pas vident
    {
        // Patch XSS
        $email = htmlspecialchars($_POST['email']); 
        $password = htmlspecialchars($_POST['password']);
        
        $email = strtolower($email); // email transformé en minuscule
        
        /*"SELECT C.id_client, U.pseudo, U.email, U.password, U.token
        from mini_projet.comptes C, mini_projet.utilisateurs U
        where U.email = ? and U.id_client = ?
        */

        // On regarde si l'utilisateur est inscrit dans la table utilisateurs
        $check = $bdd->prepare('SELECT C.id_client, U.pseudo, U.email, U.password, U.token
        from mini_projet.comptes C, mini_projet.utilisateurs U
        where C.id_client = U.id_client and U.email = ?');
        $check->execute(array($email));
        $data = $check->fetch();
        $row = $check->rowCount();
        
        

        // Si > à 0 alors l'utilisateur existe
        if($row > 0)
        {
            // Si le mail est bon niveau format
            if(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                // Si le mot de passe est le bon
                if(password_verify($password, $data['password']))
                {
                    // On créer la session et on redirige sur landing.php
                    $_SESSION['user'] = $data['token'];
                    header("Location: Projet.php");
                    die();
                }else{ header('Location: Projet_Login_index.php?login_err=password'); die(); }
            }else{ header('Location: Projet_Login_index.php?login_err=email'); die(); }
        }else{ header('Location: Projet_Login_index.php?login_err=already'); die(); }
    }else{ header('Location: Projet_Login_index.php'); die();} // si le formulaire est envoyé sans aucune données