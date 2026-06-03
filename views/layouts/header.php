<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    (function () {
      try {
        var theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.style.backgroundColor = theme === 'dark' ? '#1a1a2e' : '#f8f9fa';
        document.documentElement.style.colorScheme = theme;
      } catch (error) {
        document.documentElement.style.backgroundColor = '#f8f9fa';
      }
    })();
  </script>
  <link rel="icon" type="image/jpeg" href="public/image/logo-bimbel-orion.jpg">
  <title><?= htmlspecialchars($pageTitle ?? 'Bimbel Orion') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="public/css/main.css" rel="stylesheet">
</head>
<?php
$currentPage = (string)($_GET['page'] ?? '');
$isDashboardPage = preg_match('/^(admin|guru|siswa|wali)-/i', $currentPage) === 1;
$bodyClass = $isDashboardPage ? 'dashboard-page' : '';
?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
