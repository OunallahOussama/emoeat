<?php
/* ================================================
   navbar.php - Barre de navigation commune
   Incluse sur toutes les pages.
   Affiche le logo a gauche et les liens a droite.
   Si l'utilisateur est connecte, on affiche le menu complet.
   Sinon, on affiche uniquement Accueil, S'inscrire, Se connecter.
   ================================================ */
$_cur  = basename($_SERVER['PHP_SELF']);
$_logged = isset($_SESSION['user_id']);
$_uname  = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$_urole  = isset($_SESSION['role'])      ? $_SESSION['role']      : '';
?>
<nav class="navbar">
    <div class="nav-container">

        <a href="<?php echo $_logged ? 'dashboard.php' : 'index.php'; ?>" class="nav-logo">
            <span class="logo-icon">&#127869;</span>
            <span>Emo<span class="logo-accent">Eat</span></span>
        </a>

        <div class="nav-right">
        <div class="nav-links" id="navLinks">
            <?php if($_logged): ?>
                <a href="dashboard.php"      class="<?php echo $_cur==='dashboard.php'      ? 'active':''; ?>">&#127968; Tableau de bord</a>
                <a href="recommandation.php" class="<?php echo $_cur==='recommandation.php' ? 'active':''; ?>">&#127869; Recommandations</a>
                <a href="historique.php"     class="<?php echo $_cur==='historique.php'     ? 'active':''; ?>">&#128202; Historique</a>
                <a href="profile.php"        class="<?php echo $_cur==='profile.php'        ? 'active':''; ?>">&#128100; Profil</a>
                <?php if($_urole === 'ADMIN'): ?>
                    <a href="dashboard_admin.php" class="admin-link <?php echo $_cur==='dashboard_admin.php'?'active':''; ?>">&#9881;&#65039; Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-btn logout-btn">&#128682; D&eacute;connexion</a>
            <?php else: ?>
                <a href="index.php"    class="<?php echo $_cur==='index.php'    ? 'active':''; ?>">Accueil</a>
                <a href="register.php" class="<?php echo $_cur==='register.php' ? 'active':''; ?>">S'inscrire</a>
                <a href="login.php"    class="nav-btn">&#128273; Se connecter</a>
            <?php endif; ?>
        </div>
        <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Mode nuit / jour">&#127769;</button>
        <button class="hamburger" onclick="document.getElementById('navLinks').classList.toggle('open')">&#9776;</button>
        </div><!-- /.nav-right -->

    </div>
</nav>
<script>
(function(){
    var saved = localStorage.getItem('emoeat_theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    var btn = document.getElementById('themeToggle');
    if(btn) btn.textContent = saved === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
})();
function toggleTheme(){
    var root  = document.documentElement;
    var isDark = root.getAttribute('data-theme') === 'dark';
    var next  = isDark ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    localStorage.setItem('emoeat_theme', next);
    document.getElementById('themeToggle').textContent = next === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
}
</script>