<div class="card stat-card">
    <div class="card-header">
        <h3 class="card-title"><?= __('Disparos Totales', 'rd-finanzas') ?></h3>
        <i class="icon-disparo"></i>
    </div>
    <div class="card-content">
        <div class="stat-value">
            <?= number_format($stats['totales']['disparos'], 0) ?>
        </div>
        <div class="stat-change <?= ($stats['cambios']['disparos'] ?? '0%')[0] === '+' ? 'positive' : 'negative' ?>">
            <?= $stats['cambios']['disparos'] ?? '0%' ?>
            <?= __('vs mes anterior', 'rd-finanzas') ?>
        </div>
    </div>
</div>