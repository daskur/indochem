<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/functions.php';
$pageTitle=$safetyTitle;
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container safety-detail">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span>/</span>
        <a href="<?= e(url('/safety/index.php')) ?>">Laboratory Safety</a><span>/</span>
        <span><?= e($safetyTitle) ?></span>
    </nav>
    <div class="equipment-layout">
        <aside class="equipment-sidebar"><nav><?= $safetyNavigation ?></nav></aside>
        <article class="card safety-document">
            <div class="card-body">
                <span class="section-kicker">Laboratory safety guidance</span>
                <h1><?= e($safetyTitle) ?></h1>
                <?= $safetyContent ?>
            </div>
        </article>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
