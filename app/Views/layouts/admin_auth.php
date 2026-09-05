<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — NAZO Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { amber: { 500:'#f59e0b', 600:'#d97706' }, dark: { 900:'#0a0a0a', 800:'#111111', 700:'#1a1a1a' } }, fontFamily: { condensed:['Barlow Condensed','sans-serif'] } } }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-dark-900 min-h-screen flex items-center justify-center font-['Inter']">
    <?php echo $content; ?>
    <script>lucide.createIcons();</script>
</body>
</html>
