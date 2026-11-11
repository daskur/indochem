<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/functions.php';
$pageTitle='Laboratory Safety';
require dirname(__DIR__) . '/includes/header.php';
$topics=[
    ['ppe.php','Personal Protective Equipment','PPE selection and safe use','Eye, skin, respiratory and body protection.'],
    ['hazards.php','Laboratory Hazards','Risk recognition','Chemical, physical, biological and electrical hazards.'],
    ['first_aid.php','First Aid','Emergency response','Immediate actions for chemical and laboratory incidents.'],
    ['fire_safety.php','Fire Safety','Prevention and response','Fire classes, extinguishers, alarms and evacuation.'],
];
?>
<main id="main-content" class="container module-page safety-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= e(url('/index.php')) ?>">Home</a><span>/</span><span>Laboratory Safety</span></nav>
    <header class="module-heading"><div><span class="section-kicker">Safety first</span><h1>Laboratory Safety</h1><p>Learn protective practices for laboratory and industrial environments.</p></div></header>
    <div class="alert alert-error safety-alert"><strong>Emergency reminder:</strong> Follow the site emergency plan and alert trained responders. Do not experiment or provide aid beyond your training.</div>
    <div class="safety-grid">
        <?php foreach($topics as $topic): ?>
            <article class="card safety-topic-card">
                <div class="card-body"><span class="badge"><?= e($topic[2]) ?></span><h2><a href="<?= e(url('/safety/'.$topic[0])) ?>"><?= e($topic[1]) ?></a></h2><p><?= e($topic[3]) ?></p><a class="text-link" href="<?= e(url('/safety/'.$topic[0])) ?>">Open guidance →</a></div>
            </article>
        <?php endforeach; ?>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
