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
<button
    class="molecule-launcher"
    id="molecule-open"
    type="button"
    aria-haspopup="dialog"
    aria-controls="molecule-viewer"
    aria-expanded="false"
>
    <svg aria-hidden="true" viewBox="0 0 32 32">
        <line x1="9" y1="16" x2="22" y2="10"></line>
        <line x1="9" y1="16" x2="22" y2="23"></line>
        <circle cx="8" cy="16" r="4"></circle>
        <circle cx="23" cy="9" r="4"></circle>
        <circle cx="23" cy="24" r="4"></circle>
    </svg>
    <span>3D molecule</span>
</button>
<div class="molecule-backdrop" id="molecule-viewer" hidden>
    <section
        class="molecule-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="molecule-title"
        aria-describedby="molecule-help"
    >
        <div class="molecule-panel-heading">
            <div>
                <span class="section-kicker">Interactive model</span>
                <h2 id="molecule-title">Methane (CH<sub>4</sub>)</h2>
            </div>
            <button class="molecule-close" type="button" aria-label="Close molecule viewer">
                &times;
            </button>
        </div>
        <canvas
            class="molecule-canvas"
            id="molecule-canvas"
            tabindex="0"
            aria-label="Rotatable three-dimensional ball-and-stick model of methane, CH4"
        ></canvas>
        <p id="molecule-help">The model rotates slowly on its own. Drag to rotate it, or use the arrow keys; press Home to reset.</p>
        <div class="molecule-legend" aria-label="Atom key">
            <span><i class="molecule-atom molecule-atom-carbon"></i>Carbon (C)</span>
            <span><i class="molecule-atom molecule-atom-hydrogen"></i>Hydrogen (H)</span>
            <span>Bond angle: 109.5&deg;</span>
        </div>
    </section>
</div>
<script src="<?= e(url('/assets/js/app.js?v=20261111-molecule')) ?>" defer></script>
</body>
</html>
