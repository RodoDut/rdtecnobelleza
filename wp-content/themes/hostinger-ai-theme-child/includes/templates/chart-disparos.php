<div class="chart-container">
    <div class="card full-width">
        <div class="card-header">
            <h3 class="card-title"><?= __('Disparos Mensuales', 'rd-finanzas') ?></h3>
        </div>
        <div class="card-content">
            <canvas id="disparos-chart-<?= esc_attr($unique_id) ?>" 
                    height="300"
                    data-chart-data="<?= esc_attr(json_encode([
                        'labels' => array_column($stats['meses'], 'etiqueta'),
                        'datasets' => [[
                            'label' => __('Disparos', 'rd-finanzas'),
                            'data' => array_column($stats['meses'], 'disparos'),
                            'backgroundColor' => 'rgba(153, 102, 255, 0.7)'
                        ]]
                    ])) ?>">
            </canvas>
        </div>
    </div>
</div>