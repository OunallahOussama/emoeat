<?php
/* ================================================
   admin_foods.php -?" Gestion des aliments
   L'admin peut ajouter, modifier et supprimer
   les aliments de la base de données.
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

/* -"?-"? Ajout d'un nouvel aliment -"?-"? */
if(isset($_POST['add_food'])) {
    $fname    = trim($_POST['food_name']);
    $cat      = trim($_POST['category']);
    $calories = (int)$_POST['calories'];
    $protein  = (float)$_POST['protein'];
    $carbs    = (float)$_POST['carbs'];
    $fat      = (float)$_POST['fat'];
    $desc     = trim($_POST['description']);

    /* Vérification que les champs obligatoires sont remplis */
    if(empty($fname) || empty($cat)) {
        $msg = "Le nom et la catégorie sont obligatoires.";
        $msg_type = 'danger';
    } else {
        try {
            /* Insertion du nouvel aliment avec un identifiant généré automatiquement */
            $stmt = $conn->prepare(
                "INSERT INTO FOODS (ID_FOOD, FOOD_NAME, CATEGORY, CALORIES, PROTEIN, CARBS, FAT, DESCRIPTION)
                 VALUES (SEQ_FOODS.NEXTVAL, :name, :cat, :cal, :prot, :carbs, :fat, :desc)"
            );
            $stmt->bindParam(':name',  $fname);
            $stmt->bindParam(':cat',   $cat);
            $stmt->bindParam(':cal',   $calories, PDO::PARAM_INT);
            $stmt->bindParam(':prot',  $protein);
            $stmt->bindParam(':carbs', $carbs);
            $stmt->bindParam(':fat',   $fat);
            $stmt->bindParam(':desc',  $desc);
            $stmt->execute();
            logActivity($conn, $admin_id, 'ADMIN_ADD_FOOD_' . $fname);
            $msg = "Aliment \"$fname\" ajouté avec succès.";
        } catch(PDOException $e) {
            $msg = "Erreur : " . htmlspecialchars($e->getMessage());
            $msg_type = 'danger';
        }
    }
}

/* -"?-"? Suppression d'un aliment -"?-"? */
if(isset($_POST['delete_food'])) {
    $del_id = (int)$_POST['del_id'];
    try {
        // Supprimer les règles et recommandations associées
        foreach([
            "DELETE FROM EMOTION_FOOD   WHERE ID_FOOD = :id",
            "DELETE FROM RECOMMENDATIONS WHERE ID_FOOD = :id",
            "DELETE FROM FOODS          WHERE ID_FOOD = :id",
        ] as $sql) {
            $st = $conn->prepare($sql);
            $st->bindParam(':id', $del_id, PDO::PARAM_INT);
            $st->execute();
        }
        logActivity($conn, $admin_id, 'ADMIN_DELETE_FOOD_' . $del_id);
        $msg = "Aliment #$del_id supprimé.";
    } catch(PDOException $e) {
        $msg = "Erreur : " . htmlspecialchars($e->getMessage());
        $msg_type = 'danger';
    }
}

/* -"?-"? Chargement de la liste -"?-"? */
$search = trim($_GET['q'] ?? '');
$foods  = [];
try {
    if($search !== '') {
        $stmt = $conn->prepare(
            "SELECT ID_FOOD, FOOD_NAME, CATEGORY, CALORIES, PROTEIN, CARBS, FAT, DESCRIPTION
             FROM FOODS WHERE LOWER(FOOD_NAME) LIKE :q OR LOWER(CATEGORY) LIKE :q
             ORDER BY FOOD_NAME"
        );
        $like = '%' . strtolower($search) . '%';
        $stmt->bindParam(':q', $like);
    } else {
        $stmt = $conn->prepare(
            "SELECT ID_FOOD, FOOD_NAME, CATEGORY, CALORIES, PROTEIN, CARBS, FAT, DESCRIPTION
             FROM FOODS ORDER BY FOOD_NAME"
        );
    }
    $stmt->execute();
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $msg = "Erreur de chargement."; $msg_type = 'danger';
}

$categories = ['Fruit','Vegetable','Grain','Protein','Dairy','Dessert','Beverage','Legume','Nut','Other'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les aliments &#9881;&#65039; EmoEat Admin</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="page-wrap">

    <div class="breadcrumb">
        <a href="dashboard_admin.php">&#9881;&#65039; Admin</a>  &rsaquo; Aliments
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#129367; Gestion des Aliments</h1>
            <p>Ajouter, consulter ou supprimer des aliments (table FOODS).</p>
        </div>
        <a href="dashboard_admin.php" class="btn btn-outline"> &rsaquo; Retour Admin</a>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <!-- Formulaire d'ajout -->
    <div class="form-card" style="margin-bottom:32px;">
        <h3 style="margin-bottom:16px;">&#10133; Ajouter un aliment</h3>
        <form method="POST">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="food_name" class="form-control" placeholder="ex : Banane" required>
                </div>
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="category" class="form-control" required>
                        <option value="">-- Choisir ?"</option>
                        <?php foreach($categories as $c): ?>
                        <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Calories (kcal)</label>
                    <input type="number" name="calories" class="form-control" placeholder="0" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Protéines (g)</label>
                    <input type="number" step="0.1" name="protein" class="form-control" placeholder="0.0" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Glucides (g)</label>
                    <input type="number" step="0.1" name="carbs" class="form-control" placeholder="0.0" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Lipides (g)</label>
                    <input type="number" step="0.1" name="fat" class="form-control" placeholder="0.0" min="0" value="0">
                </div>
            </div>
            <div class="form-group" style="margin-top:8px;">
                <label>Description / Bénéfice</label>
                <input type="text" name="description" class="form-control" placeholder="ex : Riche en potassium, favorise la bonne humeur">
            </div>
            <button type="submit" name="add_food" class="btn btn-green" style="margin-top:8px;">&#10133; Ajouter l'aliment</button>
        </form>
    </div>

    <!-- Recherche + liste -->
    <form method="GET" style="margin-bottom:16px;display:flex;gap:10px;">
        <input type="text" name="q" class="form-control" placeholder="Rechercher un aliment?"
               value="<?php echo htmlspecialchars($search); ?>" style="max-width:380px;">
        <button type="submit" class="btn btn-green">Y"</button>
        <?php if($search): ?><a href="admin_foods.php" class="btn btn-outline">o.</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <div class="table-head">
            <h3>Liste des aliments</h3>
            <span class="tag tag-g"><?php echo count($foods); ?> aliment(s)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Nom</th><th>Catégorie</th><th>Kcal</th><th>P/G/L (g)</th><th>Description</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php if(empty($foods)): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-l);">Aucun aliment trouvé.</td></tr>
            <?php else: ?>
            <?php foreach($foods as $f): ?>
            <tr>
                <td><?php echo (int)$f['ID_FOOD']; ?></td>
                <td><strong><?php echo htmlspecialchars($f['FOOD_NAME'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($f['CATEGORY'] ?? ''); ?></td>
                <td><?php echo (int)($f['CALORIES'] ?? 0); ?></td>
                <td><?php echo number_format((float)($f['PROTEIN']??0),1).'/'.number_format((float)($f['CARBS']??0),1).'/'.number_format((float)($f['FAT']??0),1); ?></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                    title="<?php echo htmlspecialchars($f['DESCRIPTION']??''); ?>">
                    <?php echo htmlspecialchars(mb_strimwidth($f['DESCRIPTION'] ?? ''...', 0, 60, ''&#8212;')); ?>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="del_id" value="<?php echo (int)$f['ID_FOOD']; ?>">
                        <button type="submit" name="delete_food"
                                class="btn" style="padding:4px 10px;font-size:12px;background:#d9534f;color:#fff;border:none;border-radius:6px;"
                                onclick="return confirm('Supprimer cet aliment et ses règles associées ?');">
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

</div>

<?php include('footer.php'); ?>
</body>
</html>


