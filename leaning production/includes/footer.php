<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <strong><?= e(IPLA_APP_NAME) ?></strong>
            <p>Learn industrial chemistry and manufacturing processes step by step.</p>
        </div>
        <nav aria-label="Footer navigation">
            <a href="<?= e(url('/processes/index.php')) ?>">Processes</a>
            <a href="<?= e(url('/safety/index.php')) ?>">Safety</a>
            <a href="<?= e(url('/glossary/index.php')) ?>">Glossary</a>
        </nav>
    </div>
    <div class="container footer-bottom">
        <small>&copy; <?= date('Y') ?> <?= e(IPLA_APP_SHORT_NAME) ?>. Educational platform.</small>
    </div>
</footer>
<script src="<?= e(url('/assets/js/app.js')) ?>" defer></script>
</body>
</html>
