<?php
/**
 * Admin Emotions view
 * Variables: $emotions, $foods, $rules, $msg, $msg_type
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les émotions &#9881;&#65039; EmoEat Admin</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="page-wrap">
    <div class="breadcrumb">
        <a href="/admin/dashboard">&#9881;&#65039; Admin</a> &rsaquo; &Eacute;motions
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#128522; Gestion des &Eacute;motions &amp; Règles</h1>
            <p>Ajouter des émotions et configurer les associations Émotion&rarr;Aliment.</p>
        </div>
        <a href="/admin/dashboard" class="btn btn-outline">&rsaquo; Retour Admin</a>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(350px,1fr));gap:24px;margin-bottom:30px;">
        <div class="form-card">
            <h3 style="margin-bottom:12px;">&#10010; Ajouter une émotion</h3>
            <form method="POST" action="/admin/emotions">
                <div class="form-group">
                    <label>Nom de l'émotion*</label>
                    <input type="text" name="emo_name" class="form-control" required placeholder="Ex: Joie">
                </div>
                <button type="submit" name="add_emotion" class="btn btn-green btn-full">&#10010; Ajouter</button>
            </form>
        </div>

        <div class="form-card">
            <h3 style="margin-bottom:12px;">&#128279; Ajouter une règle (Émotion &rarr; Aliment)</h3>
            <form method="POST" action="/admin/emotions">
                <div class="form-group">
                    <label>&Eacute;motion*</label>
                    <select name="rule_emo" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($emotions as $e): ?>
                        <option value="<?php echo (int)$e['ID_EMOTION']; ?>"><?php echo htmlspecialchars($e['EMOTION_NAME']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Aliment*</label>
                    <select name="rule_food" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($foods as $f): ?>
                        <option value="<?php echo (int)$f['ID_FOOD']; ?>"><?php echo htmlspecialchars($f['FOOD_NAME']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="add_rule" class="btn btn-green btn-full">&#128279; Associer</button>
            </form>
        </div>
    </div>

    <div class="table-wrap" style="margin-bottom:30px;">
        <div class="table-head">
            <h3>&Eacute;motions existantes</h3>
            <span class="tag tag-g"><?php echo count($emotions); ?></span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Nom</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if(empty($emotions)): ?>
                <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-l);">Aucune émotion.</td></tr>
            <?php else: ?>
            <?php foreach($emotions as $e): ?>
            <tr>
                <td><?php echo (int)$e['ID_EMOTION']; ?></td>
                <td><strong><?php echo htmlspecialchars($e['EMOTION_NAME']); ?></strong></td>
                <td>
                    <form method="POST" action="/admin/emotions" style="display:inline;">
                        <input type="hidden" name="del_emo" value="<?php echo (int)$e['ID_EMOTION']; ?>">
                        <button type="submit" name="delete_emotion" class="btn" style="padding:4px 10px;font-size:12px;background:#d9534f;color:#fff;border:none;border-radius:6px;"
                                onclick="return confirm('Supprimer cette émotion ?');">
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

    <div class="table-wrap">
        <div class="table-head">
            <h3>&#128279; Règles (EMOTION_FOOD)</h3>
            <span class="tag tag-g"><?php echo count($rules); ?></span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>&Eacute;motion</th><th>Aliment</th></tr>
            </thead>
            <tbody>
            <?php if(empty($rules)): ?>
                <tr><td colspan="2" style="text-align:center;padding:20px;color:var(--text-l);">Aucune règle définie.</td></tr>
            <?php else: ?>
            <?php foreach($rules as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['EMOTION_NAME'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($r['FOOD_NAME'] ?? ''); ?></td>
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
