<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$featuredProcesses = [];
$latestNews = [];

try {
    $featuredProcesses = $pdo->query(
        'SELECT id,title,slug,category,short_description,featured_image
         FROM processes
         WHERE status = "published"
         ORDER BY id DESC
         LIMIT 6'
    )->fetchAll();

    $newsTable = $pdo->query("SHOW TABLES LIKE 'news'")->fetch();
    if ($newsTable) {
        $latestNews = $pdo->query(
            'SELECT id,title,content,image,category,published_at
             FROM news
             WHERE status = "published"
             ORDER BY published_at DESC, id DESC
             LIMIT 3'
        )->fetchAll();
    }
} catch (PDOException $exception) {
    error_log('IPLA home page query failed: ' . $exception->getMessage());
}

$pageTitle = 'Industrial Process Learning';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="home-hero">
        <div class="container home-hero-grid">
            <div class="hero-copy">
                <span class="hero-kicker">Industrial science education</span>
                <h1>Learn Industrial Processes Step by Step</h1>
                <p>Explore how industrial products are manufactured, understand the equipment and chemicals involved, and test your knowledge.</p>
                <form class="search-bar" method="get" action="<?= e(url('/search/index.php')) ?>" role="search">
                    <label class="sr-only" for="global-search">Search industrial processes</label>
                    <input class="form-control" id="global-search" name="q" type="search" placeholder="Search processes, equipment or chemicals..." autocomplete="off">
                    <button class="button button-primary" type="submit">Search</button>
                </form>
            </div>
            <div class="hero-panel" aria-label="IPLA learning overview">
                <div>
                    <strong>11</strong>
                    <span>Industrial processes</span>
                </div>
                <div>
                    <strong>Step-by-step</strong>
                    <span>Visual learning</span>
                </div>
                <div>
                    <strong>Safety-first</strong>
                    <span>Quality-focused</span>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Start learning</span>
                    <h2>Featured industrial processes</h2>
                </div>
                <a class="button button-secondary button-small" href="<?= e(url('/processes/index.php')) ?>">View all processes</a>
            </div>

            <?php if ($featuredProcesses): ?>
                <div class="process-card-grid">
                    <?php foreach ($featuredProcesses as $process): ?>
                        <article class="card process-card">
                            <div class="process-card-visual" aria-hidden="true">
                                <span><?= e(substr($process['title'], 0, 1)) ?></span>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($process['category'])): ?>
                                    <span class="badge"><?= e($process['category']) ?></span>
                                <?php endif; ?>
                                <h3><a href="<?= e(url('/processes/view.php?id=' . $process['id'])) ?>"><?= e($process['title']) ?></a></h3>
                                <p><?= e($process['short_description'] ?: 'Explore this industrial production process step by step.') ?></p>
                                <a class="text-link" href="<?= e(url('/processes/view.php?id=' . $process['id'])) ?>">Explore process <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Processes are being prepared</h3>
                    <p>Published learning content will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="home-section quick-navigation-section">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Learning resources</span>
                    <h2>Explore IPLA</h2>
                </div>
            </div>
            <div class="quick-grid">
                <a class="quick-card" href="<?= e(url('/equipment/index.php')) ?>">
                    <span class="quick-icon" aria-hidden="true">EQ</span>
                    <strong>Equipment Library</strong>
                    <small>Machines, parts and principles</small>
                </a>
                <a class="quick-card" href="<?= e(url('/chemicals/index.php')) ?>">
                    <span class="quick-icon" aria-hidden="true">CH</span>
                    <strong>Chemical Library</strong>
                    <small>Properties, uses and hazards</small>
                </a>
                <a class="quick-card" href="<?= e(url('/calculators/index.php')) ?>">
                    <span class="quick-icon" aria-hidden="true">+</span>
                    <strong>Calculators</strong>
                    <small>Concentration, yield and density</small>
                </a>
                <a class="quick-card" href="<?= e(url('/safety/index.php')) ?>">
                    <span class="quick-icon" aria-hidden="true">!</span>
                    <strong>Laboratory Safety</strong>
                    <small>PPE, hazards and first aid</small>
                </a>
                <a class="quick-card" href="<?= e(url('/quizzes/index.php')) ?>">
                    <span class="quick-icon" aria-hidden="true">Q</span>
                    <strong>Quiz & Practice</strong>
                    <small>Check your understanding</small>
                </a>
                <a class="quick-card" href="<?= e(url('/glossary/index.php')) ?>">
                    <span class="quick-icon" aria-hidden="true">G</span>
                    <strong>Glossary</strong>
                    <small>Industrial terminology</small>
                </a>
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Stay informed</span>
                    <h2>Latest updates</h2>
                </div>
                <a class="button button-secondary button-small" href="<?= e(url('/news/index.php')) ?>">View news</a>
            </div>
            <?php if ($latestNews): ?>
                <div class="news-grid">
                    <?php foreach ($latestNews as $news): ?>
                        <article class="card news-card">
                            <div class="card-body">
                                <?php if (!empty($news['category'])): ?>
                                    <span class="badge"><?= e($news['category']) ?></span>
                                <?php endif; ?>
                                <h3><a href="<?= e(url('/news/view.php?id=' . $news['id'])) ?>"><?= e($news['title']) ?></a></h3>
                                <?php if (!empty($news['published_at'])): ?>
                                    <small><?= e(date('F j, Y', strtotime($news['published_at']))) ?></small>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state compact">
                    <p>Industry news and platform updates will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
