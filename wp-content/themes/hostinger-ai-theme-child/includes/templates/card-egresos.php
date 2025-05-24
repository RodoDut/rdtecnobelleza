<div class="card stat-card">
    <div class="card-header">
        <h3 class="card-title"><?= __('Egresos Totales', 'rd-finanzas') ?></h3>
        <i class="icon-egreso"></i>
    </div>
    <div class="card-content">
        <div class="stat-value">
            <?= number_format($stats['totales']['egresos'], 2) ?>
        </div>
        <div class="stat-change <?= ($stats['cambios']['egresos'] ?? '0%')[0] === '+' ? 'positive' : 'negative' ?>">
            <?= $stats['cambios']['egresos'] ?? '0%' ?> 
            <?= __('vs mes anterior', 'rd-finanzas') ?>
        </div>
    </div>
</div>