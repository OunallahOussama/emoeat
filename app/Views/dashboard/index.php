<?php
/**
 * User Dashboard view
 * Variables: $name, $role, $cntRec, $cntEmo, $hasProfile, $recent
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord &#127869; EmoEat</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="page-wrap">

    <div style="background:linear-gradient(135deg,var(--primary-d),var(--primary-l));border-radius:var(--radius);padding:32px 36px;color:#fff;margin-bottom:32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;box-shadow:var(--shadow-lg);">
        <div>
            <p style="font-size:13px;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;">Bienvenue</p>
            <h1 style="font-size:clamp(22px,3.5vw,34px);font-weight:900;margin-bottom:8px;"><?php echo htmlspecialchars($name); ?> &#128075;</h1>
            <p style="font-size:15px;color:rgba(255,255,255,.8);">
                Rôle : <strong style="color:var(--accent);"><?php echo htmlspecialchars($role); ?></strong>
                &nbsp;&mdash;&nbsp; <?php echo date('l d F Y'); ?>
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="/recommendation" class="btn btn-primary" style="background-color: var(--accent); border: none;">&#127869; Nouvelle recommandation</a>
            <a href="/logout" class="btn btn-outline" style="border-radius:var(--radius-sm); background-color: #d9534f; color: white; border: none; padding: 10px 15px; text-decoration: none;">&#128682; Déconnexion</a>
        </div>
    </div>

    <?php if(!$hasProfile): ?>
    <div class="alert alert-warning" style="background-color:#fff3cd; color:#856404; padding:15px; border-radius:8px; margin-bottom:20px;">
        &#9888;&#65039; Votre profil nutritionnel est incomplet.
        <a href="/profile" style="font-weight:700;color:inherit;text-decoration:underline;">Complétez-le maintenant &#8594;</a>
    </div>
    <?php endif; ?>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon si-green">&#127869;</div>
            <div>
                <div class="stat-val"><?php echo $cntRec; ?></div>
                <div class="stat-lbl">Recommandations reçues</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-orange">&#128522;</div>
            <div>
                <div class="stat-val"><?php echo $cntEmo; ?></div>
                <div class="stat-lbl">&Eacute;motions enregistrées</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-blue">&#128100;</div>
            <div>
                <div class="stat-val"><?php echo $hasProfile ? '&#10003;' : '&#10007;'; ?></div>
                <div class="stat-lbl">Profil nutritionnel</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-purple">&#127807;</div>
            <div>
                <div class="stat-val">ODD 3</div>
                <div class="stat-lbl">Bonne Santé &amp; Bien-être</div>
            </div>
        </div>
    </div>

    <h2 style="font-size:18px;font-weight:800;color:var(--primary-d);margin-bottom:4px;margin-top:30px;">Accès rapide</h2>
    <p style="font-size:14px;color:var(--text-l);margin-bottom:15px;">Naviguez rapidement vers les fonctionnalités.</p>
    <div class="quick-grid">
        <a href="/recommendation" class="quick-card">
            <div class="qc-icon">&#127869;</div>
            <span>Recommandations</span>
        </a>
        <a href="/history" class="quick-card">
            <div class="qc-icon">&#128202;</div>
            <span>Mon historique</span>
        </a>
        <a href="/profile" class="quick-card">
            <div class="qc-icon">&#128100;</div>
            <span>Mon profil</span>
        </a>
        <?php if($role === 'ADMIN'): ?>
        <a href="/admin/dashboard" class="quick-card">
            <div class="qc-icon">&#9881;&#65039;</div>
            <span>Panneau Admin</span>
        </a>
        <?php endif; ?>
    </div>

    <?php if(!empty($recent)): ?>
    <div style="margin-top:40px;">
        <div class="table-wrap">
            <div class="table-head">
                <h3>&#128197; Activité récente</h3>
                <a href="/history" class="btn btn-sm btn-green" style="text-decoration:none; padding:5px 10px; background:#28a745; color:white; border-radius:5px;">Voir tout &#8594;</a>
            </div>
            <table class="data-table" style="width:100%; border-collapse:collapse; margin-top:15px;">
                <thead style="background:#f8f9fa; text-align:left;">
                    <tr>
                        <th style="padding:10px; border-bottom:2px solid #dee2e6;">Aliment</th>
                        <th style="padding:10px; border-bottom:2px solid #dee2e6;">&Eacute;motion</th>
                        <th style="padding:10px; border-bottom:2px solid #dee2e6;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent as $r): ?>
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid #eee;"><strong><?php echo htmlspecialchars($r['FOOD_NAME'] ?? 'Inconnu'); ?></strong></td>
                        <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($r['EMOTION_NAME'] ?? 'Inconnue'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($r['RECOMMENDATION_DATE'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div style="margin-top:40px; padding:30px; text-align:center; background:#f8f9fa; border-radius:8px; color:#6c757d;">
        Aucune activité récente. <br><a href="/recommendation" style="color:var(--primary-d); font-weight:bold;">Obtenez votre première recommandation !</a>
    </div>
    <?php endif; ?>

</div>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
