<?php
/**
 * Profile view
 * Variables: $profile, $userInfo, $message, $msg_type, $bmi, $bmi_label, $bmi_class, $name, $role
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil &#128100; EmoEat</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="page-wrap">

    <div class="profile-banner">
        <div class="profile-avatar">&#128100;</div>
        <div>
            <h2><?php echo htmlspecialchars($name); ?></h2>
            <p><?php echo htmlspecialchars($userInfo['EMAIL'] ?? ''); ?> &bull; <span class="tag <?php echo ($role === 'ADMIN') ? 'tag-r' : 'tag-g'; ?>"><?php echo htmlspecialchars($role); ?></span></p>
        </div>
    </div>

    <?php if($message): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if($bmi): ?>
    <div class="bmi-card <?php echo $bmi_class; ?>">
        <div class="bmi-val"><?php echo number_format($bmi, 1); ?></div>
        <div class="bmi-lbl"><?php echo htmlspecialchars($bmi_label); ?></div>
        <p style="font-size:12px;color:var(--text-l);margin-top:6px;">IMC = Poids(kg) / Taille(m)²</p>
    </div>
    <?php endif; ?>

    <div class="form-card" style="max-width:700px;">
        <h3 style="margin-bottom:16px;">&#127869; Informations nutritionnelles</h3>
        <form method="POST" action="/profile">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                <div class="form-group">
                    <label for="weight">Poids (kg)*</label>
                    <input type="number" id="weight" name="weight" class="form-control" required step="0.1" min="20" max="300"
                           value="<?php echo htmlspecialchars($profile['WEIGHT'] ?? ''); ?>" placeholder="Ex: 70">
                </div>
                <div class="form-group">
                    <label for="height">Taille (cm)*</label>
                    <input type="number" id="height" name="height" class="form-control" required step="0.1" min="50" max="250"
                           value="<?php echo htmlspecialchars($profile['HEIGHT'] ?? ''); ?>" placeholder="Ex: 175">
                </div>
                <div class="form-group">
                    <label for="goal">Objectif</label>
                    <select id="goal" name="goal" class="form-control">
                        <option value="">-- Aucun --</option>
                        <option value="Perte de poids" <?php echo (isset($profile['GOAL']) && $profile['GOAL'] === 'Perte de poids') ? 'selected' : ''; ?>>Perte de poids</option>
                        <option value="Prise de masse" <?php echo (isset($profile['GOAL']) && $profile['GOAL'] === 'Prise de masse') ? 'selected' : ''; ?>>Prise de masse</option>
                        <option value="Maintien" <?php echo (isset($profile['GOAL']) && $profile['GOAL'] === 'Maintien') ? 'selected' : ''; ?>>Maintien</option>
                        <option value="Bien-être" <?php echo (isset($profile['GOAL']) && $profile['GOAL'] === 'Bien-être') ? 'selected' : ''; ?>>Bien-être</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top:12px;">
                <label for="allergies">Allergies / Intolérances</label>
                <textarea id="allergies" name="allergies" class="form-control" rows="2"
                          placeholder="Ex: Lactose, Gluten, Arachides"><?php echo htmlspecialchars($profile['ALLERGIES'] ?? ''); ?></textarea>
            </div>
            <button type="submit" name="save_profile" class="btn btn-green btn-full" style="margin-top:12px;">
                &#128190; Enregistrer le profil
            </button>
        </form>
    </div>
</div>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
