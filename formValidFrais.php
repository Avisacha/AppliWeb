<?php
    $repInclude = './include/';
    require($repInclude . "_coDb.inc.php");
    require($repInclude . "_entete.inc.html");
    require($repInclude . "_menu.inc.php");
    ?>

            <?php
                if( !isset($_POST['confMois'])){
            ?>

            <!-- 1er formulaire -->
            <form method="post" action="formValidFrais.php">
                <h1> Validation des frais par visiteur </h1>
                <label class="titre">Mois :</label>
                <select name="mois" class="zone">
                
                    <?php
                        $req = $bdd->prepare("SELECT DISTINCT fichefrais.mois as mois
                            FROM fichefrais
                            WHERE fichefrais.idEtat = 'CL'
                            ORDER BY fichefrais.mois DESC");
                        $req->execute();
                        $resultat = $req->fetchAll();
                        
                        foreach ($resultat as $ligne) {
                            $mois = $ligne['mois'];
                            $noMois = intval(substr($mois, 4, 2));
                            $annee = intval(substr($mois, 0, 4));
                            echo "<option value='".$mois."'>".$noMois."/".$annee."</option>";
                        }
                    ?>
                
                <input class='zone' type='submit' name='confMois'>
            </form>

            <?php
                }
            ?>

            <!-- 2e formulaire -->
            
            <?php
                if( isset($_POST['confMois']) & !isset($_POST['confVisiteur'])){
                    $moisPremierForm = $_POST["mois"];
                    $noMois = intval(substr($moisPremierForm, 4, 2));
                    $annee = intval(substr($moisPremierForm, 0, 4));
                    $sql = $bdd->prepare(" SELECT DISTINCT nom
                        FROM visiteur V, fichefrais FF, etat E
                        WHERE FF.idVisiteur = V.id
                        AND E.id = FF.idEtat
                        AND ".$moisPremierForm." = FF.mois");
                    $sql->execute();
                    echo $noMois." / ".$annee;
            ?>

            <form method="post" action="formValidFrais.php">
                <label class="titre">Choisir le visiteur :</label>
                <select name="visiteur" class="zone">

                    <?php
                        while($visiteur = $sql->fetch()){
                            echo "<option value='".$visiteur['nom']."'>".$visiteur['nom']."</option>";
                        }
                    ?>
            
                </select>
                <input type="hidden" name="mois" value="<?php echo $moisPremierForm; ?>"/>
                <input class='zone' type='submit' name='confVisiteur'/>
            </form>
            
            <?php
                }
            ?>

            <!-- 3e formulaire -->
            
            <?php
                if( isset($_POST['confVisiteur'])) {

                    echo $_POST['visiteur'];
                    $nomVisiteur = $_POST['visiteur'];

                    $sql = $bdd->prepare("SELECT visiteur.id
                        FROM visiteur
                        WHERE visiteur.nom = :nomVisiteur ");
                    $sql->bindValue(":nomVisiteur", $nomVisiteur, PDO::PARAM_STR);
                    $sql->execute();

                    $resultat = $sql->fetchAll();
                    foreach ($resultat as $key) {
                        $idVisiteur = $key['id'];
                    }

                    $moisPremierForm = $_POST["mois"];
                    $noMois = intval(substr($moisPremierForm, 4, 2));
                    $annee = intval(substr($moisPremierForm, 0, 4));
            ?>

            <p class="titre" />
            <div style="clear:left;">
                <h2>Frais au forfait </h2>
            </div>
                    <?php
                        echo $noMois." / ".$annee;

                        $sql = $bdd->prepare(" SELECT (montant*quantite) as prix
                            FROM lignefraisforfait LFF, fraisforfait FF
                            WHERE LFF.idFraisForfait = FF.id
                            AND LFF.mois = :moisPremierForm
                            AND LFF.idVisiteur = :idVisiteur");
                        $sql->bindValue(":moisPremierForm", $moisPremierForm, PDO::PARAM_STR);
                        $sql->bindValue(":idVisiteur", $idVisiteur, PDO::PARAM_STR);
                        $sql->execute();

                        $valeur = $sql->fetchAll();
                        if (!empty($valeur)){
                        ?>
            <form method="post" action="formValidFrais.php">
                <table style="color:black;" border="1">
                    <tr>
                        <th>Repas midi</th>
                        <th>Nuitée </th>
                        <th>Etape</th>
                        <th>Km </th>
                        <th>Situation</th>
                    </tr>

                        <?php
                        echo "<tr align='center'>";
                        foreach ($valeur as $resultat) {
                            echo "<td width='80'> <input type='text' size='3' name='etape' value=".$resultat['prix']." /></td>";
                        }
                    ?>

                    <td width='80'>
                        <select size='3' name='situ'>
                            <option value='E'>Enregistré</option>
                            <option value='V'>Validé</option>
                            <option value='R'>Remboursé</option>
                        </select>
                    </td>
                </tr>
                </table>
                <input type="hidden" name="mois" value="<?php echo $moisPremierForm; ?>"/>
                <input type="hidden" name="visiteur" value="<?php echo $nomVisiteur; ?>"/>
                <input class='zone' type='submit' name='confForfait'/>
            </form>

            <?php
                }
                else{
                    echo "<br>";
                    echo "<br>";
                    echo "Pas de fiche de frais forfaitisé pour ce mois.";
                    }
                }
                if(isset($_POST['confVisiteur']) && !isset($_POST['confMois'])){
            ?>

            <p class="titre" />
            <div style="clear:left;">
                <h2>Hors Forfait</h2>  
            </div>

            <?php
                echo $noMois." / ".$annee;
                
                $sql = $bdd->prepare(" SELECT *
                            FROM lignefraishorsforfait LFHF
                            WHERE LFHF.mois = :moisPremierForm
                            AND LFHF.idVisiteur = :idVisiteur");
                        $sql->bindValue(":moisPremierForm", $moisPremierForm, PDO::PARAM_STR);
                        $sql->bindValue(":idVisiteur", $idVisiteur, PDO::PARAM_STR);
                        $sql->execute();

                        $valeur = $sql->fetchAll();

                        if (!empty($valeur)){
            ?>

            <form method="post" action="formValidFrais.php">
                <table style="color:black;" border="1">
                    <tr>
                        <th>Date</th>
                        <th>Libellé </th>
                        <th>Montant</th>
                        <th>Situation</th>
                    </tr>
                    <tr align="center">
                    
                    <?php
                    foreach ($valeur as $key) {
                        echo "<td width='80'> <input type='text' size='3' name='etape' value=".$key['date']." /></td>";
                        echo "<td width='80'> <input type='text' size='3' name='etape' value=".$key['libelle']." /></td>";
                        echo "<td width='80'> <input type='text' size='3' name='etape' value=".$key['montant']." /></td>";
                    }

                    ?>
                    <td width='80'>
                        <select size="3" name="hfSitu">
                            <option value="E">Enregistré</option>
                            <option value="V">Validé</option>
                            <option value="R">Remboursé</option>
                        </select>
                    </td>
                </tr>
            </table>
            <input class='zone' type='submit' name='confHorsforfait'/>
        </form>

    <?php
        }
        else{
            echo "<br>";
            echo "<br>";
            echo "Pas de fiche de frais hors forfait pour ce mois.";
        }
    }

    if(isset($_POST['confForfait'])){
        $moisPremierForm = $_POST["mois"];

        $nomVisiteur = $_POST['visiteur'];

        $sql = $bdd->prepare("SELECT visiteur.id
                        FROM visiteur
                        WHERE visiteur.nom = :nomVisiteur ");
        $sql->bindValue(":nomVisiteur", $nomVisiteur, PDO::PARAM_STR);
        $sql->execute();

        $resultat = $sql->fetchAll();
        foreach ($resultat as $key) {
            $idVisiteur = $key['id'];
        }

        switch ($_POST['situ']) {
            case 'E':
                $etat = 'CR';
                break;
            case 'V':
                $etat = 'VA';
                break;
            case 'R':
                $etat = 'RB';
                break;
        }

        $sql = $bdd->prepare("  UPDATE fichefrais FF
                                SET idEtat = '".$etat."'
                                WHERE FF.mois = :moisPremierForm
                                AND FF.idVisiteur = :idVisiteur ");
        $sql->bindValue(":moisPremierForm", $moisPremierForm, PDO::PARAM_STR);
        $sql->bindValue(":idVisiteur", $idVisiteur, PDO::PARAM_STR);
        $sql->execute();
    }

    if(isset($_POST['confHorsforfait'])){
        // echo "confHorsforfait";

        $moisPremierForm = $_POST["mois"];

        $nomVisiteur = $_POST['visiteur'];

        $sql = $bdd->prepare("SELECT visiteur.id
                        FROM visiteur
                        WHERE visiteur.nom = :nomVisiteur ");
        $sql->bindValue(":nomVisiteur", $nomVisiteur, PDO::PARAM_STR);
        $sql->execute();

        $resultat = $sql->fetchAll();
        foreach ($resultat as $key) {
            $idVisiteur = $key['id'];
        }

        switch ($_POST['hfSitu']) {
            case 'E':
                $etat = 'CR';
                break;
            case 'V':
                $etat = 'VA';
                break;
            case 'R':
                $etat = 'RB';
                break;
        }

        $sql = $bdd->prepare("  UPDATE lignefraishorsforfait LFHF
                                SET etat = '".$etat."'
                                WHERE LFHF.mois = :moisPremierForm
                                AND LFHF.idVisiteur = :idVisiteur ");
        $sql->bindValue(":moisPremierForm", $moisPremierForm, PDO::PARAM_STR);
        $sql->bindValue(":idVisiteur", $idVisiteur, PDO::PARAM_STR);
        $sql->execute();
    }
    ?>
</form>
</div>
</div>
</body>
</html>
