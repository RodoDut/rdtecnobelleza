<div class="card stat-card">
    <div class="card-header">
        <h3 class="card-title"><?= __('Ingresos Totales', 'rd-finanzas') ?></h3>
        <i class="icon-dollar"></i>
    </div>
    <div class="card-content">
        <div class="stat-value">
            <?= number_format($stats['totales']['ingresos'], 2) ?>
        </div>
        <div class="stat-change">
            <?= $stats['cambios']['ingresos'] ?? '0%' ?> 
            <?= __('vs mes anterior', 'rd-finanzas') ?>
        </div>
    </div>
</div>