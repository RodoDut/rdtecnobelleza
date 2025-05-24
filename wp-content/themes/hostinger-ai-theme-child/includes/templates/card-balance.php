<div class="card stat-card">
    <div class="card-header">
        <h3 class="card-title"><?= __('Balance Neto', 'rd-finanzas') ?></h3>
        <i class="icon-balance"></i>
    </div>
    <div class="card-content">
        <div class="stat-value <?= $stats['totales']['balance'] >= 0 ? 'positive' : 'negative' ?>">
            <?= number_format($stats['totales']['balance'], 2) ?>
        </div>
        <div class="stat-change <?= ($stats['cambios']['balance'] ?? '0%')[0] === '+' ? 'positive' : 'negative' ?>">
            <?= $stats['cambios']['balance'] ?? '0%' ?>
            <?= __('vs mes anterior', 'rd-finanzas') ?>
        </div>
    </div>
</div>