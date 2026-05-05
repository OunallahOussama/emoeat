<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EmoEat'; ?></title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>

<?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

<?php echo $content ?? ''; ?>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>

</body>
</html>
