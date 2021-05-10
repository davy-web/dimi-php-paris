<?php
// Connexion
function membre_connecte() {
    if (isset($_SESSION['membre'])) {
        return true;
    }
    else {
        header("Location:" . URL . "/connexion");
        return false;
    }
}
function admin_connecte() {
    if (isset($_SESSION['membre']) && $_SESSION['membre']['statut'] == 2) {
        return true;
    }
    else {
        header("Location:" . URL . "/connexion");
        return false;
    }
}
function deconnexion() {
    unset($_SESSION["membre"]);
    header("Location:" . URL . "/connexion");
}

// Panier
function ajouter_produit_panier($id_produit, $titre, $photo, $prix, $quantite) {
    // création du panier
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = array();
        $_SESSION['panier']['id_produit'] = array();
        $_SESSION['panier']['titre'] = array();
        $_SESSION['panier']['photo'] = array();
        $_SESSION['panier']['prix'] = array();
        $_SESSION['panier']['quantite'] = array();
    }
    // verifier et ajouter dans le panier
    $position_produit = array_search($id_produit, $_SESSION['panier']['id_produit']);
    if ($position_produit !== false) {
        $_SESSION['panier']['quantite'][$position_produit] += $quantite;
    }
    else {
        $_SESSION['panier']['id_produit'][] = $id_produit;
        $_SESSION['panier']['titre'][] = $titre;
        $_SESSION['panier']['photo'][] = $photo;
        $_SESSION['panier']['prix'][] = $prix;
        $_SESSION['panier']['quantite'][] = $quantite;
    }
}
function retirer_produit_panier($id_produit) {
    // retirer dans le panier
    $position_produit =  array_search($id_produit, $_SESSION['panier']['id_produit']);
    if ($position_produit !== false) {
        array_splice($_SESSION['panier']['id_produit'], $position_produit, 1);
        array_splice($_SESSION['panier']['titre'], $position_produit, 1);
        array_splice($_SESSION['panier']['photo'], $position_produit, 1);
        array_splice($_SESSION['panier']['prix'], $position_produit, 1);
        array_splice($_SESSION['panier']['quantite'], $position_produit, 1);
    }
}
function augmenter_produit_panier($id_produit, $stock) {
    // augmenter dans le panier
    if (isset($_SESSION['panier'])) {
        $position_produit = array_search($id_produit, $_SESSION['panier']['id_produit']);
        if ($position_produit !== false && $_SESSION['panier']['quantite'][$position_produit] < $stock) {
            $_SESSION['panier']['quantite'][$position_produit]++;
        }
    }
}
function diminuer_produit_panier($id_produit, $stock) {
    // diminuer dans le panier
    if (isset($_SESSION['panier'])) {
        $position_produit = array_search($id_produit, $_SESSION['panier']['id_produit']);
        if ($position_produit !== false && $_SESSION['panier']['quantite'][$position_produit] > 1) {
            $_SESSION['panier']['quantite'][$position_produit]--;
        }
    }
}
function sauvegarder_produits_panier($pdo_object, $etat, $prix_total) {
    // sauvegarder le panier
    if (isset($_SESSION['panier']) && isset($_SESSION['membre'])) {
        // création de la commande
        $pdo_statement = $pdo_object->prepare("SELECT * FROM commande WHERE membre_id = :membre_id");
        $pdo_statement->bindValue(':membre_id', $_SESSION['membre']['id_membre'], PDO::PARAM_STR);
        $pdo_statement->execute();
        $commande_array = $pdo_statement->fetch(PDO::FETCH_ASSOC);
        if (empty($commande_array)) {
            // ajouter
            $pdo_statement_2 = $pdo_object->prepare("INSERT INTO commande (membre_id, prix_total, date, etat) VALUES (:membre_id, :prix_total, :date, :etat)");
        }
        else {
            // mettre à jour
            $pdo_statement_2 = $pdo_object->prepare("UPDATE commande SET membre_id = :membre_id, prix_total = :prix_total, date = :date, etat = :etat WHERE id_commande = :id_commande");
            $pdo_statement_2->bindValue(':id_commande', $commande_array['id_commande'], PDO::PARAM_INT);
        }
        $pdo_statement_2->bindValue(':membre_id', $_SESSION['membre']['id_membre'], PDO::PARAM_INT);
        $pdo_statement_2->bindValue(':prix_total', $prix_total, PDO::PARAM_STR);
        $pdo_statement_2->bindValue(':date', date("Y-m-d"), PDO::PARAM_STR);
        $pdo_statement_2->bindValue(':etat', $etat, PDO::PARAM_STR);
        $pdo_statement_2->execute();

        // création des détails de la commande
        $pdo_statement = $pdo_object->prepare("SELECT * FROM commande WHERE membre_id = :membre_id");
        $pdo_statement->bindValue(':membre_id', $_SESSION['membre']['id_membre'], PDO::PARAM_STR);
        $pdo_statement->execute();
        $commande_array = $pdo_statement->fetch(PDO::FETCH_ASSOC);
        for($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) {
            $pdo_statement_2 = $pdo_object->prepare("SELECT * FROM details_commande WHERE commande_id = :commande_id AND produit_id = :produit_id");
            $pdo_statement_2->bindValue(':commande_id', $commande_array['id_commande'], PDO::PARAM_INT);
            $pdo_statement_2->bindValue(':produit_id', $_SESSION['panier']['id_produit'][$i], PDO::PARAM_INT);
            $pdo_statement_2->execute();
            $details_commande_array = $pdo_statement_2->fetch(PDO::FETCH_ASSOC);
            if (empty($details_commande_array)) {
                // ajouter
                $pdo_statement_3 = $pdo_object->prepare("INSERT INTO details_commande (commande_id, produit_id, quantite, prix) VALUES (:commande_id, :produit_id, :quantite, :prix)");
            }
            else {
                // mettre à jour
                $pdo_statement_3 = $pdo_object->prepare("UPDATE details_commande SET commande_id = :commande_id, produit_id = :produit_id, quantite = :quantite, prix = :prix WHERE id_details_commande = :id_details_commande");
                $pdo_statement_3->bindValue(':id_details_commande', $details_commande_array['id_details_commande'], PDO::PARAM_INT);
            }
            $pdo_statement_3->bindValue(':commande_id', $commande_array['id_commande'], PDO::PARAM_INT);
            $pdo_statement_3->bindValue(':produit_id', $_SESSION['panier']['id_produit'][$i], PDO::PARAM_INT);
            $pdo_statement_3->bindValue(':quantite', $_SESSION['panier']['quantite'][$i], PDO::PARAM_INT);
            $pdo_statement_3->bindValue(':prix', $_SESSION['panier']['prix'][$i], PDO::PARAM_INT);
            $pdo_statement_3->execute();
        }
    }
}
?>