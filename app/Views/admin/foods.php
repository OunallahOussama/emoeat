<?php
/**
 * Admin Foods view
 * Variables: $foods, $search, $msg, $msg_type, $categories
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les aliments &#9881;&#65039; EmoEat Admin</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="page-wrap">
    <div class="breadcrumb">
        <a href="/admin/dashboard">&#9881;&#65039; Admin</a> &rsaquo; Aliments
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#129367; Gestion des Aliments</h1>
            <p>Ajouter, rechercher ou supprimer des aliments de la base.</p>
        </div>
        <a href="/admin/dashboard" class="btn btn-outline">&rsaquo; Retour Admin</a>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="form-card" style="max-width:100%;margin-bottom:25px;">
        <h3 style="margin-bottom:12px;">&#10010; Ajouter un aliment</h3>
        <form method="POST" action="/admin/foods" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div style="flex:1;min-width:160px;">
                <label>Nom*</label>
                <input type="text" name="food_name" class="form-control" required placeholder="Ex: Avocat">
            </div>
            <div style="flex:1;min-width:120px;">
                <label>Catégorie*</label>
                <select name="food_category" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;min-width:80px;">
                <label>Calories</label>
                <input type="number" name="food_cal" class="form-control" step="1" placeholder="kcal">
            </div>
            <div style="flex:1;min-width:80px;">
                <label>Protéines</label>
                <input type="number" name="food_prot" class="form-control" step="1" placeholder="g">
            </div>
            <div style="flex:1;min-width:80px;">
                <label>Glucides</label>
                <input type="number" name="food_carb" class="form-control" step="1" placeholder="g">
            </div>
            <div style="flex:1;min-width:80px;">
                <label>Lipides</label>
                <input type="number" name="food_fat" class="form-control" step="1" placeholder="g">
            </div>
            <div style="flex:2;min-width:140px;">
                <label>Description</label>
                <input type="text" name="food_desc" class="form-control" placeholder="Bienfaits...">
            </div>
            <button type="submit" name="add_food" class="btn btn-green" style="height:42px;">&#10010; Ajouter</button>
        </form>
    </div>

    <form method="GET" action="/admin/foods" style="margin-bottom:20px;display:flex;gap:10px;">
        <input type="text" name="q" class="form-control" placeholder="Rechercher un aliment &#128269;"
               value="<?php echo htmlspecialchars($search); ?>" style="max-width:380px;">
        <button type="submit" class="btn btn-green">&#128269; Chercher</button>
        <?php if($search): ?><a href="/admin/foods" class="btn btn-outline">&#215; Effacer</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <div class="table-head">
            <h3>Liste des aliments</h3>
            <span class="tag tag-g"><?php echo count($foods); ?> résultat(s)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Nom</th><th>Catégorie</th><th>Calories</th><th>Prot.</th><th>Gluc.</th><th>Lip.</th><th>Description</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if(empty($foods)): ?>
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-l);">Aucun aliment trouvé.</td></tr>
            <?php else: ?>
            <?php foreach($foods as $f): ?>
            <tr>
                <td><?php echo (int)$f['ID_FOOD']; ?></td>
                <td><strong><?php echo htmlspecialchars($f['FOOD_NAME'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($f['CATEGORY'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($f['CALORIES'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($f['PROTEIN'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($f['CARBS'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($f['FAT'] ?? '-'); ?></td>
                <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($f['DESCRIPTION'] ?? '-'); ?></td>
                <td>
                    <form method="POST" action="/admin/foods" style="display:inline;">
                        <input type="hidden" name="del_id" value="<?php echo (int)$f['ID_FOOD']; ?>">
                        <button type="submit" name="delete_food" class="btn" style="padding:4px 10px;font-size:12px;background:#d9534f;color:#fff;border:none;border-radius:6px;"
                                onclick="return confirm('Supprimer cet aliment ?');">
                            &#128465; Supprimer
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

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
