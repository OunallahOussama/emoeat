<?php
$_cur = basename($_SERVER['REQUEST_URI']);
$_logged = isset($_SESSION['user_id']);
$_uname = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$_urole = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<nav class="navbar">
    <div class="nav-container">

        <a href="<?php echo $_logged ? '/dashboard' : '/'; ?>" class="nav-logo">
            <span class="logo-icon">&#127869;</span>
            <span>Emo<span class="logo-accent">Eat</span></span>
        </a>

        <div class="nav-right">
        <div class="nav-links" id="navLinks">
            <?php if($_logged): ?>
                <a href="/dashboard" class="<?php echo strpos($_cur, 'dashboard') !== false ? 'active':''; ?>">&#127968; Tableau de bord</a>
                <a href="/recommendation" class="<?php echo strpos($_cur, 'recommendation') !== false ? 'active':''; ?>">&#127869; Recommandations</a>
                <a href="/history" class="<?php echo strpos($_cur, 'history') !== false ? 'active':''; ?>">&#128202; Historique</a>
                <a href="/profile" class="<?php echo strpos($_cur, 'profile') !== false ? 'active':''; ?>">&#128100; Profil</a>
                <?php if($_urole === 'ADMIN'): ?>
                    <a href="/admin/dashboard" class="admin-link <?php echo strpos($_cur, 'admin') !== false ? 'active':''; ?>">&#9881;&#65039; Admin</a>
                <?php endif; ?>
                <a href="/logout" class="nav-btn logout-btn">&#128682; D&eacute;connexion</a>
            <?php else: ?>
                <a href="/" class="<?php echo $_cur === '' || $_cur === '/' ? 'active':''; ?>">Accueil</a>
                <a href="/register" class="<?php echo strpos($_cur, 'register') !== false ? 'active':''; ?>">S'inscrire</a>
                <a href="/login" class="nav-btn">&#128273; Se connecter</a>
            <?php endif; ?>
        </div>
        <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Mode nuit / jour">&#127769;</button>
        <button class="hamburger" onclick="document.getElementById('navLinks').classList.toggle('open')">&#9776;</button>
        </div>

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
    var root = document.documentElement;
    var isDark = root.getAttribute('data-theme') === 'dark';
    var next = isDark ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    localStorage.setItem('emoeat_theme', next);
    document.getElementById('themeToggle').textContent = next === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
}
</script>
