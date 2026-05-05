<?php
/**
 * Recommendation view
 * Variables: $csrf_token, $profile, $emotions, $results, $selected_emotion_id,
 *            $selected_emotion_nm, $filter_info, $db_error, $save_success
 *
 * Uses static helpers: RecommendationController::emoEmoji(), emoLabel(), foodEmoji(), getFoodImage()
 */
use App\Controllers\RecommendationController;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommandations &mdash; EmoEat</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="page-wrap page-wrap-md" style="max-width:980px; margin: auto; padding: 20px;">

    <div class="breadcrumb" style="margin-bottom: 20px; color: #666;">
        <a href="/dashboard" style="color: #2D5A27; text-decoration: none;">&#127968; Tableau de bord</a> &rsaquo; Recommandations
    </div>

    <div class="page-header" style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #5C4033;">&#127869; Recommandation Intelligente</h1>
        <p>Sélectionnez votre émotion actuelle et recevez des suggestions alimentaires personnalisées.</p>
    </div>

    <?php if(!$profile): ?>
    <div class="alert alert-warning" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        &#9888;&#65039; Votre profil nutritionnel est incomplet &mdash; les filtres (allergies, objectif) ne seront pas appliqués.
        <a href="/profile" style="font-weight:700; text-decoration:underline; color: #856404;">Compléter mon profil ?</a>
    </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom:32px;">
        <form method="POST" action="/recommendation">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="emotion-section">
                <h3 style="color: #2D5A27; margin-bottom: 15px;">Comment vous sentez-vous en ce moment ?</h3>

                <?php if(empty($emotions)): ?>
                    <div class="alert alert-info">Aucune émotion disponible dans la base de données.</div>
                <?php else: ?>
                <div class="emotion-grid" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
                    <?php foreach($emotions as $em): ?>
                    <label class="emotion-item" style="cursor: pointer; padding: 10px 15px; border: 1px solid #ccc; border-radius: 20px;">
                        <input type="radio" name="emotion" value="<?php echo (int)$em['ID_EMOTION']; ?>" <?php echo ($selected_emotion_id === (int)$em['ID_EMOTION']) ? 'checked' : ''; ?>>
                        <span class="emotion-label">
                            <span class="emotion-emoji"><?php echo RecommendationController::emoEmoji($em['EMOTION_NAME']); ?></span>
                            <span class="emotion-name"><?php echo htmlspecialchars(RecommendationController::emoLabel($em['EMOTION_NAME'])); ?></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <button type="submit" name="get_reco" class="btn btn-green btn-full" style="background: #2D5A27; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%;">
                &#127869; Obtenir mes recommandations
            </button>
        </form>
    </div>

    <?php if(!empty($db_error)): ?>
    <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #f5c6cb;">
        &#9888;&#65039; <?php echo $db_error; ?>
    </div>
    <?php endif; ?>

    <?php if(!empty($results) || $selected_emotion_id !== null): ?>

        <?php if(!empty($filter_info)): ?>
        <div class="alert alert-info" style="background: #e2e3e5; padding: 10px; border-radius: 5px; margin-bottom: 20px;">&#8505;&#65039; <?php echo htmlspecialchars($filter_info); ?></div>
        <?php endif; ?>

        <?php if(!empty($results)): ?>
        <div style="margin-bottom:20px;">
            <h2 style="color: #2D5A27;">
                <?php echo RecommendationController::emoEmoji($selected_emotion_nm); ?> Recommandations pour &laquo; <?php echo htmlspecialchars(RecommendationController::emoLabel($selected_emotion_nm)); ?> &raquo;
            </h2>
            <?php if(!$save_success): ?>
            <p style="color:#666; margin:4px 0 0;">Cochez les aliments souhaités puis confirmez votre sélection.</p>
            <?php endif; ?>
        </div>

        <?php if($save_success): ?>
        <div style="background:#d4edda;color:#155724;padding:14px 18px;border-radius:8px;margin-bottom:20px;border:1px solid #c3e6cb;font-weight:600;">
            &#10004; Votre sélection a été enregistrée dans votre historique !
        </div>
        <?php endif; ?>

        <form method="POST" action="/recommendation" id="selectionForm">
            <input type="hidden" name="csrf_token"  value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="emotion_id"  value="<?php echo (int)$selected_emotion_id; ?>">

            <div class="food-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;margin-bottom:24px;">
            <?php foreach($results as $row):
                $fid  = (int)($row['ID_FOOD']   ?? $row['id_food']   ?? 0);
                $fnm  = $row['FOOD_NAME']  ?? $row['food_name']  ?? '';
                $fcat = $row['CATEGORY']   ?? $row['category']   ?? '';
                $fcal = (int)($row['CALORIES']  ?? $row['calories']  ?? 0);
                $fprt = (int)($row['PROTEIN']   ?? $row['protein']   ?? 0);
                $fcrb = (int)($row['CARBS']     ?? $row['carbs']     ?? 0);
                $ffat = (int)($row['FAT']       ?? $row['fat']       ?? 0);
                $fben = $row['BENEFIT']    ?? $row['benefit']    ?? '';
                $femo = RecommendationController::foodEmoji($fnm, $fcat);
                $fimg = RecommendationController::getFoodImage($fnm, $fcat);
            ?>
            <label for="food_<?php echo $fid; ?>" class="food-card-with-photo" id="lbl_<?php echo $fid; ?>">
                <img src="<?php echo $fimg; ?>"
                     alt="<?php echo htmlspecialchars($fnm); ?>"
                     class="food-card-photo"
                     loading="lazy"
                     onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=75'">
                <div class="food-card-body-inner">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                        <div>
                            <span style="font-size:22px;"><?php echo $femo; ?></span>
                            <h4 style="margin:3px 0 0;color:#5C4033;font-size:15px;"><?php echo htmlspecialchars($fnm); ?></h4>
                            <small style="color:#888;"><?php echo htmlspecialchars(ucfirst(strtolower($fcat))); ?></small>
                        </div>
                        <input type="checkbox" id="food_<?php echo $fid; ?>" name="selected_foods[]" value="<?php echo $fid; ?>"
                               style="width:22px;height:22px;cursor:pointer;accent-color:#2D5A27;flex-shrink:0;"
                               data-name="<?php echo htmlspecialchars($fnm); ?>"
                               data-emoji="<?php echo $femo; ?>"
                               data-cal="<?php echo $fcal; ?>" data-prot="<?php echo $fprt; ?>"
                               data-carb="<?php echo $fcrb; ?>" data-fat="<?php echo $ffat; ?>"
                               <?php echo $save_success ? 'disabled checked' : ''; ?>>
                    </div>
                    <div style="border-top:1px solid #eee;padding-top:8px;">
                        <p style="margin:3px 0;font-size:13px;">&#128293; <strong><?php echo $fcal ?: '&mdash;'; ?></strong> cal
                        <?php if($fprt): ?> &nbsp;|&nbsp; &#128170; <strong><?php echo $fprt; ?>g</strong><?php endif; ?></p>
                        <?php if(!empty($fben)): ?>
                        <p style="margin-top:5px;color:#666;font-size:12px;line-height:1.5;"><?php echo htmlspecialchars($fben); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </label>
            <?php endforeach; ?>
            </div>

            <!-- LIVE SUMMARY BOARD -->
            <div id="summaryBoard" style="margin-bottom:24px;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);overflow:hidden;display:none;">
                <div style="background:#2D5A27;color:#fff;padding:14px 20px;display:flex;align-items:center;gap:10px;">
                    <span style="font-size:18px;">&#128722;</span>
                    <h3 style="margin:0;font-size:16px;">Ma sélection</h3>
                    <span id="selCount" style="margin-left:auto;background:rgba(255,255,255,.2);border-radius:20px;padding:2px 12px;font-size:13px;">0 aliment</span>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                        <thead>
                            <tr style="background:#f5f9f5;border-bottom:2px solid #e0ede0;">
                                <th style="padding:10px 14px;text-align:left;color:#2D5A27;">Aliment</th>
                                <th style="padding:10px 14px;text-align:center;color:#2D5A27;">&#128293; Cal</th>
                                <th style="padding:10px 14px;text-align:center;color:#2D5A27;">&#128170; Prot</th>
                                <th style="padding:10px 14px;text-align:center;color:#2D5A27;">&#127806; Gluc</th>
                                <th style="padding:10px 14px;text-align:center;color:#2D5A27;">&#129480; Lip</th>
                            </tr>
                        </thead>
                        <tbody id="summaryBody"></tbody>
                        <tfoot>
                            <tr style="background:#f5f9f5;font-weight:700;border-top:2px solid #2D5A27;">
                                <td style="padding:10px 14px;color:#2D5A27;">Total</td>
                                <td id="tCal"  style="padding:10px 14px;text-align:center;color:#2D5A27;">&mdash;</td>
                                <td id="tProt" style="padding:10px 14px;text-align:center;color:#2D5A27;">&mdash;</td>
                                <td id="tCarb" style="padding:10px 14px;text-align:center;color:#2D5A27;">&mdash;</td>
                                <td id="tFat"  style="padding:10px 14px;text-align:center;color:#2D5A27;">&mdash;</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <?php if(!$save_success): ?>
            <button type="submit" name="save_selection"
                    id="confirmBtn" disabled
                    style="background:#aaa;color:#fff;padding:13px 24px;border:none;border-radius:8px;font-size:15px;cursor:not-allowed;width:100%;transition:background .2s;">
                &#10004; Confirmer ma sélection
            </button>
            <?php endif; ?>
        </form>

        <script>
        (function(){
            const cbs    = document.querySelectorAll('input[name="selected_foods[]"]');
            const board  = document.getElementById('summaryBoard');
            const sbody  = document.getElementById('summaryBody');
            const count  = document.getElementById('selCount');
            const tCal   = document.getElementById('tCal');
            const tProt  = document.getElementById('tProt');
            const tCarb  = document.getElementById('tCarb');
            const tFat   = document.getElementById('tFat');
            const btn    = document.getElementById('confirmBtn');

            cbs.forEach(cb => {
                const card = cb.closest('label');
                cb.addEventListener('change', function() {
                    if(this.checked) {
                        card.classList.add('checked');
                    } else {
                        card.classList.remove('checked');
                    }
                    update();
                });
            });

            function update() {
                const checked = [...cbs].filter(c => c.checked);
                sbody.innerHTML = '';
                let tC=0,tP=0,tG=0,tL=0;

                checked.forEach((c, i) => {
                    const cal=+c.dataset.cal||0, prot=+c.dataset.prot||0,
                          carb=+c.dataset.carb||0, fat=+c.dataset.fat||0;
                    tC+=cal; tP+=prot; tG+=carb; tL+=fat;
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #eee';
                    if(i%2===1) tr.style.background='#fafff9';
                    tr.innerHTML = `
                        <td style="padding:9px 14px;font-weight:600;">${c.dataset.emoji} ${c.dataset.name}</td>
                        <td style="padding:9px 14px;text-align:center;">${cal||'\u2014'}</td>
                        <td style="padding:9px 14px;text-align:center;">${prot?prot+'g':'\u2014'}</td>
                        <td style="padding:9px 14px;text-align:center;">${carb?carb+'g':'\u2014'}</td>
                        <td style="padding:9px 14px;text-align:center;">${fat?fat+'g':'\u2014'}</td>`;
                    sbody.appendChild(tr);
                });

                board.style.display = checked.length ? 'block' : 'none';
                count.textContent = checked.length + ' aliment' + (checked.length>1?'s':'');
                tCal.textContent  = tC || '\u2014';
                tProt.textContent = tP ? tP+'g' : '\u2014';
                tCarb.textContent = tG ? tG+'g' : '\u2014';
                tFat.textContent  = tL ? tL+'g' : '\u2014';

                if(btn) {
                    btn.disabled = checked.length === 0;
                    btn.style.background = checked.length ? '#2D5A27' : '#aaa';
                    btn.style.cursor     = checked.length ? 'pointer'  : 'not-allowed';
                }
            }
        })();
        </script>

        <?php else: ?>
        <div class="empty-state" style="text-align: center; padding: 40px; background: #fff; border-radius: 8px;">
            <h3>Aucun résultat</h3>
            <p>Aucun aliment ne correspond à votre émotion avec votre profil actuel (filtres restrictifs).</p>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
