<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/functions.php';

$pageTitle = $calculatorTitle;
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container calculator-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span>/</span>
        <a href="<?= e(url('/calculators/index.php')) ?>">Calculators</a><span>/</span><span><?= e($calculatorTitle) ?></span>
    </nav>
    <div class="calculator-layout">
        <article class="card calculator-form-card">
            <div class="card-body">
                <span class="section-kicker">Calculator</span>
                <h1><?= e($calculatorTitle) ?></h1>
                <form data-calculator="<?= e($calculatorType) ?>">
                    <?php if ($calculatorId === 'molarity'): ?>
                        <div class="form-grid">
                            <label>Amount of solute (mol)<input class="form-control" name="moles" type="number" min="0" step="any" required></label>
                            <label>Solution volume (L)<input class="form-control" name="volume" type="number" min="0" step="any" required></label>
                        </div>
                    <?php elseif ($calculatorId === 'normality'): ?>
                        <div class="form-grid">
                            <label>Molarity (mol/L)<input class="form-control" name="molarity" type="number" min="0" step="any" required></label>
                            <label>Equivalents per mole<input class="form-control" name="equivalents" type="number" min="0" step="any" required></label>
                        </div>
                    <?php elseif ($calculatorId === 'molar-mass'): ?>
                        <label>Chemical formula<input class="form-control" name="formula" type="text" placeholder="e.g. CaCO3 or H2SO4" required></label>
                    <?php elseif ($calculatorId === 'dilution'): ?>
                        <div class="form-grid">
                            <label>Stock concentration<input class="form-control" name="c1" type="number" min="0" step="any" required></label>
                            <label>Target concentration<input class="form-control" name="c2" type="number" min="0" step="any" required></label>
                            <label>Final volume<input class="form-control" name="v2" type="number" min="0" step="any" required></label>
                            <label>Volume unit<select class="form-control" name="dilution_unit"><option>mL</option><option>L</option></select></label>
                        </div>
                        <p class="form-help">Calculates the stock volume required: V1 = C2 × V2 / C1.</p>
                    <?php elseif ($calculatorId === 'ph'): ?>
                        <label>Hydrogen-ion concentration [H+] (mol/L)<input class="form-control" name="hydrogen" type="number" min="0" step="any" required></label>
                    <?php elseif ($calculatorId === 'yield'): ?>
                        <div class="form-grid">
                            <label>Actual yield<input class="form-control" name="actual" type="number" min="0" step="any" required></label>
                            <label>Theoretical yield<input class="form-control" name="theoretical" type="number" min="0" step="any" required></label>
                        </div>
                    <?php elseif ($calculatorId === 'density'): ?>
                        <div class="form-grid">
                            <label>Mass<input class="form-control" name="mass" type="number" min="0" step="any" required></label>
                            <label>Volume<input class="form-control" name="volume" type="number" min="0" step="any" required></label>
                            <label>Density unit<select class="form-control" name="density_unit"><option>g/mL</option><option>kg/m³</option><option>g/cm³</option></select></label>
                        </div>
                    <?php elseif ($calculatorId === 'unit-converter'): ?>
                        <div class="form-grid">
                            <label>Value<input class="form-control" name="value" type="number" step="any" required></label>
                            <label>From<select class="form-control" name="from">
                                <option value="mg">Milligrams</option><option value="g" selected>Grams</option><option value="kg">Kilograms</option>
                                <option value="mL">Millilitres</option><option value="L">Litres</option>
                                <option value="mm">Millimetres</option><option value="m">Metres</option><option value="km">Kilometres</option>
                            </select></label>
                            <label>To<select class="form-control" name="to">
                                <option value="mg">Milligrams</option><option value="g" selected>Grams</option><option value="kg">Kilograms</option>
                                <option value="mL">Millilitres</option><option value="L">Litres</option>
                                <option value="mm">Millimetres</option><option value="m">Metres</option><option value="km">Kilometres</option>
                            </select></label>
                        </div>
                    <?php endif; ?>
                    <button class="button button-primary" type="submit">Calculate</button>
                </form>
                <output class="calculator-result" data-result aria-live="polite">Enter values to calculate.</output>
            </div>
        </article>
    </div>
</main>
<script src="<?= e(url('/assets/js/calculator.js')) ?>" defer></script>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
