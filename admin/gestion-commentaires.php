<?php 
require_once("../include/init.php");
require_once("../include/fonctions.php");

// Vérifie Admin
admin_connecte();

// Supprimer commentaire
if (!empty($_GET['supprimer'])) {
    $pdo_statement = $pdo_object->prepare("DELETE FROM commentaire WHERE id_commentaire = :id_commentaire");
    $pdo_statement->bindValue(":id_commentaire", $_GET['supprimer'], PDO::PARAM_INT);
    $pdo_statement->execute();
    header("Location:" . URL . "/admin/gestion-commentaires");
}

// Gestion commentaire
$pdo_statement = $pdo_object->prepare("SELECT * FROM commentaire");
$pdo_statement->execute();

require_once("../include/header-admin.php");
?>

                <!-- style -->
                <link rel="stylesheet" href="<?= URL ?>/include/css/style_cadre.css">

                <div class="block_admin_davy">
                    <h1 class="h1_moyen_davy">Liste des commentaires</h1>
                    <hr class="anime_scroll_davy">
                    <p class="color_red_davy"><?= $erreur ?><?= $notification ?></p>
                    <div class="table_responsive_davy">
                        <table>
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Client</th>
                                    <th>Commentaire</th>
                                    <th>Note</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pdo_statement->rowCount() > 0) : ?>
                                <?php while ($commentaire_array = $pdo_statement->fetch(PDO::FETCH_ASSOC)) : ?>
                                <?php
                                // Article
                                $pdo_statement_2 = $pdo_object->prepare("SELECT * FROM article WHERE id_article = :id_article");
                                $pdo_statement_2->bindValue(':id_article', $commentaire_array['produit_id'], PDO::PARAM_INT);
                                $pdo_statement_2->execute();
                                $article_array = $pdo_statement_2->fetch(PDO::FETCH_ASSOC);
                                // Membre
                                $pdo_statement_3 = $pdo_object->prepare("SELECT * FROM membre WHERE id_membre = :id_membre");
                                $pdo_statement_3->bindValue(':id_membre', $commentaire_array['membre_id'], PDO::PARAM_INT);
                                $pdo_statement_3->execute();
                                $membre_array = $pdo_statement_3->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <tr>
                                    <td><?= $article_array['titre'] ?></td>
                                    <td><?= $membre_array['prenom'] ?></td>
                                    <td><?= $commentaire_array['message'] ?></td>
                                    <td>
                                        <?php for ($i = 0; $i < $commentaire_array['note']; $i++) : ?>
                                        <img src="<?= URL ?>/images/icon-coeur-min.png" class="image_cadre_note_davy" alt="Icon coeur">
                                        <?php endfor; ?>
                                        <?php for ($i = 0; $i < (5 - $commentaire_array['note']); $i++) : ?>
                                        <img src="<?= URL ?>/images/icon-coeur-vide-min.png" class="image_cadre_note_davy" alt="Icon coeur">
                                        <?php endfor; ?>
                                    </td>
                                    <td>
                                        <a href="<?= URL ?>/admin/details-commentaire=<?= $commentaire_array['id_commentaire'] ?>" title="Voir">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="136pt" version="1.1" viewBox="-1 0 136 136.21852" width="136pt" class="icon_gestion_admin"><path fill="currentColor" d="M 93.148438 80.832031 C 109.5 57.742188 104.03125 25.769531 80.941406 9.421875 C 57.851562 -6.925781 25.878906 -1.460938 9.53125 21.632812 C -6.816406 44.722656 -1.351562 76.691406 21.742188 93.039062 C 38.222656 104.707031 60.011719 105.605469 77.394531 95.339844 L 115.164062 132.882812 C 119.242188 137.175781 126.027344 137.347656 130.320312 133.269531 C 134.613281 129.195312 134.785156 122.410156 130.710938 118.117188 C 130.582031 117.980469 130.457031 117.855469 130.320312 117.726562 Z M 51.308594 84.332031 C 33.0625 84.335938 18.269531 69.554688 18.257812 51.308594 C 18.253906 33.0625 33.035156 18.269531 51.285156 18.261719 C 69.507812 18.253906 84.292969 33.011719 84.328125 51.234375 C 84.359375 69.484375 69.585938 84.300781 51.332031 84.332031 C 51.324219 84.332031 51.320312 84.332031 51.308594 84.332031 Z M 51.308594 84.332031"/></svg>
                                        </a>
                                        <a href="<?= URL ?>/admin/gestion-commentaires?supprimer=<?= $commentaire_array['id_commentaire'] ?>" onclick="return(confirm('Souhaitez-vous supprimer ?'))" title="Supprimer">
                                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash-alt" class="icon_gestion_admin svg-inline--fa fa-trash-alt fa-w-14 " role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text_center_davy">Aucun commentaire</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- script -->
                    <script src="<?= URL ?>/include/js/script_nav_lien.js"></script>
                    <script>nav_lien_active("lien_gestion_commentaire");</script>
                </div>
<?php
require_once("../include/footer-admin.php");
?>