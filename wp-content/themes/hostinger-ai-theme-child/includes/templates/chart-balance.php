<div class="chart-container">
    <div class="card full-width">
        <div class="card-header">
            <h3 class="card-title"><?= __('Balance Mensual', 'rd-finanzas') ?></h3>
        </div>
        <div class="card-content">
            <canvas id="balance-chart-<?= esc_attr($unique_id) ?>" 
                    height="300"
                    data-chart-data="<?= esc_attr(json_encode([
                        'labels' => array_column($stats['meses'], 'etiqueta'),
                        'datasets' => [[
                            'label' => __('Balance', 'rd-finanzas'),
                            'data' => array_map(function($mes) {
                                return $mes['ingresos'] - $mes['egresos'];
                            }, $stats['meses']),
                            'borderColor' => 'rgba(54, 162, 235, 0.8)',
                            'fill' => false
                        ]]
                    ])) ?>">
            </canvas>
        </div>
    </div>
</div>