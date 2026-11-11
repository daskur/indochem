<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/functions.php';
$pageTitle = 'Industrial Calculators';
require dirname(__DIR__) . '/includes/header.php';
$calculators = [
    ['molarity.php','Molarity','M = moles / volume','Calculate solution concentration.'],
    ['normality.php','Normality','N = M × equivalents','Calculate reactive concentration.'],
    ['molar_mass.php','Molar Mass','Σ atomic mass × count','Calculate a formula mass.'],
    ['dilution.php','Dilution','C1V1 = C2V2','Calculate preparation volumes.'],
    ['ph.php','pH Calculator','pH = -log[H+]','Calculate hydrogen-ion pH.'],
    ['percentage_yield.php','Percentage Yield','Yield = actual / theoretical × 100','Evaluate reaction efficiency.'],
    ['density.php','Density','ρ = mass / volume','Calculate material density.'],
    ['unit_converter.php','Unit Converter','Mass, volume and length','Convert common laboratory units.'],
];
?>
<main id="main-content" class="container module-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= e(url('/index.php')) ?>">Home</a><span>/</span><span>Calculators</span></nav>
    <header class="module-heading"><div><span class="section-kicker">Industrial chemistry tools</span><h1>Industrial Calculators</h1><p>Perform common laboratory and process calculations.</p></div></header>
    <div class="calculator-grid">
        <?php foreach ($calculators as $calculator): ?>
            <article class="card calculator-card">
                <div class="card-body">
                    <span class="badge"><?= e($calculator[2]) ?></span>
                    <h2><a href="<?= e(url('/calculators/' . $calculator[0])) ?>"><?= e($calculator[1]) ?></a></h2>
                    <p><?= e($calculator[3]) ?></p>
                    <a class="text-link" href="<?= e(url('/calculators/' . $calculator[0])) ?>">Open calculator →</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
