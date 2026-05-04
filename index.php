<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EmoEat &#127869; Nutrition &Eacute;motionnelle Intelligente</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>

<?php include('navbar.php'); ?>

<!-- �.��.� HERO �.��.� -->
<section class="hero" style="
    padding:100px 28px 90px;
    background: url('images/hero-tarts.jpg') center/cover no-repeat;
    position: relative;
    min-height: 520px;">
    <!-- Overlay léger pour lisibilité du texte -->
    <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(13,27,18,.68) 0%,rgba(45,106,79,.45) 60%,rgba(13,27,18,.35) 100%);pointer-events:none;"></div>
    <div style="max-width:1240px;margin:0 auto;position:relative;z-index:1;
                display:grid;grid-template-columns:1fr;gap:56px;align-items:center;text-align:center;">
        <!-- Texte centré -->
        <div style="max-width:680px;margin:0 auto;">
            <span class="hero-badge">&#127807; Nutrition &Eacute;motionnelle</span>
            <h1>Mangez selon ce que<br><em>vous ressentez</em></h1>
            <p class="hero-sub">EmoEat analyse vos émotions et vous propose des aliments qui soutiennent
            votre humeur, votre énergie et votre santé au quotidien.</p>
            <div class="hero-btns">
                <a href="register.php" class="btn btn-primary" style="font-size:16px;padding:15px 34px;">&#128640; Commencer gratuitement</a>
                <a href="login.php"    class="btn btn-outline"  style="font-size:16px;padding:15px 34px;">&#128273; Se connecter</a>
            </div>
            <div class="hero-stats" style="display:flex;gap:16px;margin-top:42px;flex-wrap:wrap;justify-content:center;">
                <div class="hero-stat-pill">
                    <div style="font-size:30px;font-weight:900;color:var(--accent);" data-count="18" data-suffix="+">0+</div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.7);margin-top:3px;">&Eacute;motions</div>
                </div>
                <div class="hero-stat-pill">
                    <div style="font-size:30px;font-weight:900;color:var(--accent);" data-count="100" data-suffix="+">0+</div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.7);margin-top:3px;">Aliments</div>
                </div>
                <div class="hero-stat-pill">
                    <div style="font-size:30px;font-weight:900;color:var(--accent);">ODD&nbsp;3</div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.7);margin-top:3px;">Bien-être</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- �.��.� GALERIE PHOTO �.��.� -->
<section class="section section-alt" style="padding-top:70px;padding-bottom:70px;">
    <div style="max-width:1240px;margin:0 auto;padding:0 28px;">
        <div class="section-title reveal">
            <h2 class="steps-title-anim" style="font-size:clamp(26px,4vw,40px);font-weight:900;background:linear-gradient(135deg,#1B4332,#52B788,#40916C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:-.5px;">&#127807; Une alimentation pour chaque émotion<span class="title-line"></span></h2>
            <p>Des aliments frais, colorés et nutritifs sélectionnés pour booster votre bien-être mental et physique.</p>
        </div>
        <div class="photo-gallery">
            <div class="photo-gallery-item span-rows reveal" style="transition-delay:.05s">
                <img src="images/hero-tarts.jpg" alt="Tartelettes aux fruits colorées">
                <div class="pg-label">&#127856; Douceurs & Plaisir</div>
            </div>
            <div class="photo-gallery-item reveal" style="transition-delay:.15s">
                <img src="https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?w=500&q=80" alt="Fruits frais variés">
                <div class="pg-label">&#127827; Fruits & Bonne humeur</div>
            </div>
            <div class="photo-gallery-item reveal" style="transition-delay:.25s">
                <img src="https://images.unsplash.com/photo-1490818387583-1baba5e638af?w=500&q=80" alt="Smoothie et jus">
                <div class="pg-label">&#129948; Smoothies anti-stress</div>
            </div>
            <div class="photo-gallery-item reveal" style="transition-delay:.35s">
                <img src="https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=500&q=80" alt="Plat protéiné">
                <div class="pg-label">&#129367; Protéines & &Eacute;nergie</div>
            </div>
            <div class="photo-gallery-item reveal" style="transition-delay:.45s">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&q=80" alt="Repas équilibré">
                <div class="pg-label">&#127869; Repas équilibré</div>
            </div>
        </div>
    </div>
</section>

<!-- �.��.� FEATURES �.��.� -->
<section class="section" style="padding-top:70px;padding-bottom:70px;">
    <div style="max-width:1240px;margin:0 auto;padding:0 28px;">
        <div class="section-title reveal">
            <h2 class="steps-title-anim" style="font-size:clamp(26px,4vw,40px);font-weight:900;background:linear-gradient(135deg,#1B4332,#52B788,#40916C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:-.5px;">&#11088; Pourquoi EmoEat ?<span class="title-line"></span></h2>
            <p>Une approche innovante qui combine nutrition, psychologie et base de données intelligente.</p>
        </div>
        <div class="grid-3">
            <div class="feature-card reveal" style="transition-delay:0s">
                <div style="height:160px;overflow:hidden;border-radius:12px;margin:-34px -28px 20px;flex-shrink:0;">
                    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=500&q=80"
                         alt="Intelligence émotionnelle" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="feature-icon">&#129504;</div>
                <h3>Intelligence &Eacute;motionnelle</h3>
                <p>Notre algorithme analyse votre état émotionnel pour proposer des aliments qui soutiennent votre humeur et votre énergie.</p>
            </div>
            <div class="feature-card reveal" style="transition-delay:.12s">
                <div style="height:160px;overflow:hidden;border-radius:12px;margin:-34px -28px 20px;">
                    <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&q=80"
                         alt="Nutrition personnalisée" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="feature-icon">&#129367;</div>
                <h3>Nutrition Personnalisée</h3>
                <p>Des recommandations adaptées à votre profil : poids, taille, allergies et objectifs nutritionnels.</p>
            </div>
            <div class="feature-card reveal" style="transition-delay:.24s">
                <div style="height:160px;overflow:hidden;border-radius:12px;margin:-34px -28px 20px;">
                    <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=500&q=80"
                         alt="Suivi et historique" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="feature-icon">&#128202;</div>
                <h3>Suivi &amp; Historique</h3>
                <p>Visualisez vos habitudes alimentaires et votre parcours émotionnel au fil du temps pour mieux vous connaître.</p>
            </div>
            <div class="feature-card reveal" style="transition-delay:.36s">
                <div class="feature-icon">&#128274;</div>
                <h3>Données Sécurisées</h3>
                <p>Vos informations personnelles et nutritionnelles sont protégées et confidentielles à chaque instant.</p>
            </div>
            <div class="feature-card reveal" style="transition-delay:.48s">
                <div class="feature-icon">&#9889;</div>
                <h3>Résultats Instantanés</h3>
                <p>Obtenez des recommandations alimentaires en quelques secondes, adaptées à votre humeur du moment.</p>
            </div>
            <div class="feature-card reveal" style="transition-delay:.6s">
                <div class="feature-icon">&#127807;</div>
                <h3>Bien-être Durable</h3>
                <p>Aligné avec les ODD 3 de l'ONU, EmoEat promeut une alimentation saine pour une santé à long terme.</p>
            </div>
        </div>
    </div>
</section>

<!-- �.��.� HOW IT WORKS �.��.� -->
<section class="section section-alt" style="padding-top:80px;padding-bottom:80px;">
    <div style="max-width:1240px;margin:0 auto;padding:0 28px;">
        <div class="section-title">
            <h2 class="steps-title-anim" style="font-size:clamp(28px,4.5vw,42px);font-weight:900;background:linear-gradient(135deg,#1B4332,#52B788,#40916C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:-.5px;">&#9881;&#65039; Comment ça fonctionne</h2>
            <p style="font-size:16px;color:var(--text-m);margin-top:10px;">Un processus simple en <strong style="color:var(--primary-d);">4 étapes</strong> pour une alimentation adaptée à vos émotions.</p>
        </div>
        <div class="steps-grid" style="gap:28px;">

            <!-- �?tape 1 -->
            <div class="step-card step-card-anim" style="border-top:4px solid #52B788;animation-delay:0s;">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
                    <div class="step-num" style="background:linear-gradient(135deg,#52B788,#2D6A4F);box-shadow:0 4px 14px rgba(82,183,136,.4);font-size:18px;width:46px;height:46px;">1</div>
                    <span style="font-size:30px;">&#128100;</span>
                </div>
                <h3 style="font-size:17px;font-weight:800;color:#1B4332;margin-bottom:10px;">Créez votre compte</h3>
                <p style="font-size:13px;color:var(--text-l);line-height:1.75;">Inscrivez-vous gratuitement et rejoignez la communauté EmoEat en quelques secondes.</p>
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid #B7E4C7;">
                    <span style="font-size:11px;font-weight:700;color:#52B788;text-transform:uppercase;letter-spacing:.8px;">&#9989; Gratuit &amp; rapide</span>
                </div>
            </div>

            <!-- �?tape 2 -->
            <div class="step-card step-card-anim" style="border-top:4px solid #74C69D;animation-delay:.15s;">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
                    <div class="step-num" style="background:linear-gradient(135deg,#74C69D,#40916C);box-shadow:0 4px 14px rgba(116,198,157,.4);font-size:18px;width:46px;height:46px;">2</div>
                    <span style="font-size:30px;">&#128203;</span>
                </div>
                <h3 style="font-size:17px;font-weight:800;color:#1B4332;margin-bottom:10px;">Complétez votre profil</h3>
                <p style="font-size:13px;color:var(--text-l);line-height:1.75;">Renseignez vos données nutritionnelles : poids, taille, allergies et objectifs de santé.</p>
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid #B7E4C7;">
                    <span style="font-size:11px;font-weight:700;color:#40916C;text-transform:uppercase;letter-spacing:.8px;">&#9989; Personnalisé</span>
                </div>
            </div>

            <!-- �?tape 3 -->
            <div class="step-card step-card-anim" style="border-top:4px solid #95D5B2;animation-delay:.3s;">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
                    <div class="step-num" style="background:linear-gradient(135deg,#95D5B2,#52B788);box-shadow:0 4px 14px rgba(149,213,178,.5);font-size:18px;width:46px;height:46px;">3</div>
                    <span style="font-size:30px;">&#128522;</span>
                </div>
                <h3 style="font-size:17px;font-weight:800;color:#1B4332;margin-bottom:10px;">Exprimez votre émotion</h3>
                <p style="font-size:13px;color:var(--text-l);line-height:1.75;">Sélectionnez votre humeur actuelle parmi notre palette d'émotions détaillée.</p>
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid #B7E4C7;">
                    <span style="font-size:11px;font-weight:700;color:#52B788;text-transform:uppercase;letter-spacing:.8px;">&#9989; 18+ émotions</span>
                </div>
            </div>

            <!-- �?tape 4 -->
            <div class="step-card step-card-anim" style="border-top:4px solid #2D6A4F;animation-delay:.45s;">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
                    <div class="step-num" style="background:linear-gradient(135deg,#2D6A4F,#1B4332);box-shadow:0 4px 14px rgba(45,106,79,.4);font-size:18px;width:46px;height:46px;">4</div>
                    <span style="font-size:30px;">&#127869;</span>
                </div>
                <h3 style="font-size:17px;font-weight:800;color:#1B4332;margin-bottom:10px;">Recevez vos repas</h3>
                <p style="font-size:13px;color:var(--text-l);line-height:1.75;">Découvrez des recommandations alimentaires intelligentes, filtrées selon votre profil.</p>
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid #B7E4C7;">
                    <span style="font-size:11px;font-weight:700;color:#2D6A4F;text-transform:uppercase;letter-spacing:.8px;">&#9989; IA nutritionnelle</span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- �.��.� SPLIT : image + texte �.��.� -->
<section class="section" style="padding-top:70px;padding-bottom:70px;">
    <div style="max-width:1240px;margin:0 auto;padding:0 28px;">
        <div class="split-section">
            <img src="images/strawberries-chocolat.jpg"
                 alt="Bouquet de fraises au chocolat" loading="lazy" class="reveal-left">
            <div class="reveal-right">
                <span style="display:inline-block;background:rgba(244,162,97,.15);color:var(--accent-d);
                             padding:5px 16px;border-radius:50px;font-size:11px;font-weight:700;
                             letter-spacing:1.2px;text-transform:uppercase;margin-bottom:16px;">
                    Votre bien-être, notre priorité
                </span>
                <h2 style="font-size:clamp(24px,3.5vw,36px);font-weight:900;color:var(--primary-d);
                            margin-bottom:18px;letter-spacing:-.5px;">
                    La nourriture comme soin émotionnel
                </h2>
                <p style="font-size:15px;color:var(--text-m);line-height:1.8;margin-bottom:14px;">
                    Des études montrent que l'alimentation influence directement notre état émotionnel.
                    EmoEat s'appuie sur ces données pour vous guider vers les aliments qui vous font du bien, au bon moment.
                </p>
                <p style="font-size:15px;color:var(--text-m);line-height:1.8;margin-bottom:28px;">
                    Que vous soyez stressé, triste, fatigué ou joyeux, notre système identifie les nutriments
                    clés adaptés à votre humeur et les aliments qui les contiennent.
                </p>
                <a href="register.php" class="btn btn-green" style="padding:14px 32px;font-size:15px;">
                    &#127807; Démarrer mon parcours
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ══ CTA ══ -->
<section style="background:linear-gradient(135deg,var(--primary-d),var(--primary));padding:80px 28px;text-align:center;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=1400&q=50') center/cover no-repeat;opacity:.1;pointer-events:none;"></div>
    <div style="position:relative;z-index:1;max-width:620px;margin:0 auto;">
        <h2 style="font-size:clamp(26px,4vw,42px);font-weight:900;color:#fff;margin-bottom:14px;letter-spacing:-.5px;">
            Prêt à transformer votre alimentation ?
        </h2>
        <p style="font-size:17px;color:rgba(255,255,255,.8);margin-bottom:36px;line-height:1.7;">
            Rejoignez EmoEat aujourd'hui et commencez à manger en harmonie avec vos émotions.
        </p>
    </div>
</section>

<?php include('footer.php'); ?>

<script>
/* �"?�"? Scroll reveal �"?�"? */
const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('visible');
            revealObs.unobserve(e.target);
        }
    });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .section-title').forEach(el => revealObs.observe(el));

/* �"?�"? Count-up for hero stats �"?�"? */
function countUp(el) {
    const to = +el.dataset.count, suf = el.dataset.suffix || '';
    let cur = 0;
    const step = Math.ceil(to / 55);
    const t = setInterval(() => {
        cur = Math.min(cur + step, to);
        el.textContent = cur + suf;
        if (cur >= to) clearInterval(t);
    }, 25);
}
const statsObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.querySelectorAll('[data-count]').forEach(countUp);
            statsObs.unobserve(e.target);
        }
    });
}, { threshold: 0.4 });
const heroStats = document.querySelector('.hero-stats');
if (heroStats) statsObs.observe(heroStats);
</script>
</body>
</html>


