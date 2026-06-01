<?php
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
if ($isLoggedIn):
  $appConfig = require __DIR__ . '/../../config/app.php';
  $appName = (string)($appConfig['name'] ?? 'Bimbel Orion');
  $appVersion = (string)($appConfig['version'] ?? '1.0.0');
?>
  <footer class="app-dashboard-footer">
    <div class="app-dashboard-footer__left">
      <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($appName) ?>. All rights reserved.</p>
      <span class="app-version-badge">Versi <?= htmlspecialchars($appVersion) ?></span>
    </div>
    <div class="app-dashboard-footer__right">
      <a href="#" aria-label="Instagram Bimbel Orion" title="Instagram">
        <i class="fab fa-instagram"></i>
      </a>
      <a href="#" aria-label="Facebook Bimbel Orion" title="Facebook">
        <i class="fab fa-facebook-f"></i>
      </a>
      <a href="#" aria-label="YouTube Bimbel Orion" title="YouTube">
        <i class="fab fa-youtube"></i>
      </a>
      <a href="#" aria-label="TikTok Bimbel Orion" title="TikTok">
        <i class="fab fa-tiktok"></i>
      </a>
    </div>
  </footer>
<?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="public/js/main.js"></script>
</body>
</html>
