<?php
/* ================================================
   admin_emotions.php -?" Gestion des émotions
   L'admin peut ajouter et supprimer les émotions
   disponibles pour les recommandations alimentaires.
   ================================================ */
session_start();
include("connexion.php");

/* Protection : réservé aux administrateurs */
if(!isset($_SESSION['user_id']) || strtoupper(trim($_SESSION['role'])) !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$admin_id = (int)$_SESSION['user_id'];
$msg = ''; $msg_type = 'success';

/* -"?-"? Ajout d'une nouvelle émotion -"?-"? */
if(isset($_POST['add_emotion'])) {
    $ename = trim($_POST['emotion_name']);
    $edesc = trim($_POST['description']);
    /* Le nom de l'émotion est obligatoire */
    if(empty($ename)) {
        $msg = "Le nom de l'émotion est obligatoire.";
        $msg_type = 'danger';
    } else {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO EMOTIONS (EMOTION_NAME, DESCRIPTION)
                 VALUES (:name, :desc)"
            );
            $stmt->bindParam(':name', $ename);
            $stmt->bindParam(':desc', $edesc);
            $stmt->execute();
            logActivity($conn, $admin_id, 'ADMIN_ADD_EMOTION_' . $ename);
            $msg = "?motion \"$ename\" ajoutée avec succès.";
        } catch(PDOException $e) {
            $msg = "Erreur : " . htmlspecialchars($e->getMessage());
            $msg_type = 'danger';
        }
    }
}

/* -"?-"? Suppression d'une émotion -"?-"? */
if(isset($_POST['delete_emotion'])) {
    $del_id = (int)$_POST['del_id'];
    try {
        foreach([
            "DELETE FROM EMOTION_FOOD    WHERE ID_EMOTION = :id",
            "DELETE FROM USER_EMOTIONS   WHERE ID_EMOTION = :id",
            "DELETE FROM RECOMMENDATIONS WHERE ID_EMOTION = :id",
            "DELETE FROM EMOTIONS        WHERE ID_EMOTION = :id",
        ] as $sql) {
            $st = $conn->prepare($sql);
            $st->bindParam(':id', $del_id, PDO::PARAM_INT);
            $st->execute();
        }
        logActivity($conn, $admin_id, 'ADMIN_DELETE_EMOTION_' . $del_id);
        $msg = "&Eacute;motion #$del_id supprimée.";
    } catch(PDOException $e) {
        $msg = "Erreur : " . htmlspecialchars($e->getMessage());
        $msg_type = 'danger';
    }
}

/* -"?-"? Ajout d'une règle EMOTION_FOOD -"?-"? */
if(isset($_POST['add_rule'])) {
    $r_emo  = (int)$_POST['r_emotion_id'];
    $r_food = (int)$_POST['r_food_id'];
    $r_int  = max(1, min(10, (int)$_POST['r_intensity']));
    if($r_emo > 0 && $r_food > 0) {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY)
                 VALUES (:e, :f, :i)"
            );
            $stmt->bindParam(':e', $r_emo,  PDO::PARAM_INT);
            $stmt->bindParam(':f', $r_food, PDO::PARAM_INT);
            $stmt->bindParam(':i', $r_int,  PDO::PARAM_INT);
            $stmt->execute();
            logActivity($conn, $admin_id, 'ADMIN_ADD_RULE_E' . $r_emo . '_F' . $r_food);
            $msg = "Règle ajoutée (émotion #$r_emo ?' aliment #$r_food, intensité $r_int).";
        } catch(PDOException $e) {
            $msg = "Erreur : " . htmlspecialchars($e->getMessage());
            $msg_type = 'danger';
        }
    } else {
        $msg = "Veuillez sélectionner une émotion et un aliment.";
        $msg_type = 'danger';
    }
}

/* -"?-"? Chargement des données -"?-"? */
$emotions = []; $foods = []; $rules = [];
try {
    $eStmt = $conn->prepare("SELECT ID_EMOTION, EMOTION_NAME, DESCRIPTION FROM EMOTIONS ORDER BY EMOTION_NAME");
    $eStmt->execute();
    $emotions = $eStmt->fetchAll(PDO::FETCH_ASSOC);

    $fStmt = $conn->prepare("SELECT ID_FOOD, FOOD_NAME, CATEGORY FROM FOODS ORDER BY FOOD_NAME");
    $fStmt->execute();
    $foods = $fStmt->fetchAll(PDO::FETCH_ASSOC);

    $rStmt = $conn->prepare(
        "SELECT ef.ID_RULE, ef.INTENSITY, e.EMOTION_NAME, f.FOOD_NAME
         FROM EMOTION_FOOD ef
         JOIN EMOTIONS e ON e.ID_EMOTION = ef.ID_EMOTION
         JOIN FOODS    f ON f.ID_FOOD    = ef.ID_FOOD
         ORDER BY ef.INTENSITY DESC"
    );
    $rStmt->execute();
    $rules = $rStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $msg = "Erreur de chargement."; $msg_type = 'danger';
}

function adminEmoEmoji(string $name): string {
    $map = ['happy'=>'&#128522;','sad'=>'&#128546;','angry'=>'&#128544;','stress'=>'&#128560;','stressed'=>'&#128560;',
            'excited'=>'&#129321;','anxious'=>'&#128543;','calm'=>'&#128524;','tired'=>'&#128564;','fear'=>'&#128561;','joy'=>'&#128516;'];
    return $map[strtolower(trim($name))] ?? '&#128578;';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les émotions &#9881;&#65039; EmoEat Admin</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="page-wrap">

    <div class="breadcrumb">
        <a href="dashboard_admin.php">&#9881;&#65039; Admin</a>  &rsaquo; &Eacute;motions
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#128522; Gestion des &Eacute;motions</h1>
            <p>Gérer les émotions (table EMOTIONS) et les règles émotion-aliment (table EMOTION_FOOD).</p>
        </div>
        <a href="dashboard_admin.php" class="btn btn-outline"> &rsaquo; Retour Admin</a>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px;">

        <!-- Ajout émotion -->
        <div class="form-card">
            <h3 style="margin-bottom:16px;">&#10133; Ajouter une émotion</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Nom de l'émotion * <small>(en anglais : happy, sad?)</small></label>
                    <input type="text" name="emotion_name" class="form-control" placeholder="ex : happy" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" placeholder="ex : Sentiment de joie et de bonheur">
                </div>
                <button type="submit" name="add_emotion" class="btn btn-green">&#10133; Ajouter</button>
            </form>
        </div>

        <!-- Ajout règle EMOTION_FOOD -->
        <div class="form-card">
            <h3 style="margin-bottom:16px;">&#128279; Associer émotion ?" aliment</h3>
            <form method="POST">
                <div class="form-group">
                    <label>&Eacute;motion *</label>
                    <select name="r_emotion_id" class="form-control" required>
                        <option value="">?" Choisir une émotion </option>
                        <?php foreach($emotions as $e): ?>
                        <option value="<?php echo (int)$e['ID_EMOTION']; ?>">
                            <?php echo adminEmoEmoji($e['EMOTION_NAME']); ?>
                            <?php echo htmlspecialchars($e['EMOTION_NAME']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Aliment *</label>
                    <select name="r_food_id" class="form-control" required>
                        <option value="">?" Choisir un aliment </option>
                        <?php foreach($foods as $f): ?>
                        <option value="<?php echo (int)$f['ID_FOOD']; ?>">
                            <?php echo htmlspecialchars($f['FOOD_NAME']); ?>
                            (<?php echo htmlspecialchars($f['CATEGORY']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Intensité (1 = faible, 10 = fort)</label>
                    <input type="number" name="r_intensity" class="form-control" min="1" max="10" value="5" required>
                </div>
                <button type="submit" name="add_rule" class="btn btn-green">&#128279; Associer</button>
            </form>
        </div>
    </div>

    <!-- Liste des émotions -->
    <div class="table-wrap" style="margin-bottom:32px;">
        <div class="table-head">
            <h3>Liste des émotions</h3>
            <span class="tag tag-o"><?php echo count($emotions); ?> émotion(s)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Nom</th><th>Description</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php if(empty($emotions)): ?>
                <tr><td colspan="4" style="text-align:center;padding:30px;">Aucune émotion.</td></tr>
            <?php else: ?>
            <?php foreach($emotions as $e): ?>
            <tr>
                <td><?php echo (int)$e['ID_EMOTION']; ?></td>
                <td><?php echo adminEmoEmoji($e['EMOTION_NAME']); ?> <strong><?php echo htmlspecialchars($e['EMOTION_NAME']); ?></strong></td>
                <td><?php echo htmlspecialchars($e['DESCRIPTION'] ?? '?"'); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="del_id" value="<?php echo (int)$e['ID_EMOTION']; ?>">
                        <button type="submit" name="delete_emotion"
                                class="btn" style="padding:4px 10px;font-size:12px;background:#d9534f;color:#fff;border:none;border-radius:6px;"
                                onclick="return confirm('Supprimer cette émotion et toutes ses règles associées ?');">
                            Y-'
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Liste des règles EMOTION_FOOD -->
    <div class="table-wrap">
        <div class="table-head">
            <h3>&#128279; Règles émotion ?' aliment (EMOTION_FOOD)</h3>
            <span class="tag tag-g"><?php echo count($rules); ?> règle(s)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>&Eacute;motion</th><th>Aliment recommandé</th><th>Intensité</th></tr>
            </thead>
            <tbody>
            <?php if(empty($rules)): ?>
                <tr><td colspan="4" style="text-align:center;padding:30px;">Aucune règle définie.</td></tr>
            <?php else: ?>
            <?php foreach($rules as $r): ?>
            <tr>
                <td><?php echo (int)$r['ID_RULE']; ?></td>
                <td><?php echo adminEmoEmoji($r['EMOTION_NAME']); ?> <?php echo htmlspecialchars($r['EMOTION_NAME']); ?></td>
                <td><?php echo htmlspecialchars($r['FOOD_NAME']); ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:<?php echo ((int)$r['INTENSITY'] * 10); ?>px;height:8px;background:var(--primary-l);border-radius:4px;"></div>
                        <strong><?php echo (int)$r['INTENSITY']; ?>/10</strong>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include('footer.php'); ?>
</body>
</html>


