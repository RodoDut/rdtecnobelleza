<div class="chart-container">
    <div class="card full-width">
        <div class="card-header">
            <h3 class="card-title"><?= __('Ingresos vs Egresos', 'rd-finanzas') ?></h3>
            <select class="chart-type-selector" data-target="ingresos-egresos">
                <option value="bar"><?= __('Barras', 'rd-finanzas') ?></option>
                <option value="line"><?= __('Líneas', 'rd-finanzas') ?></option>
            </select>
        </div>
        <div class="card-content">
            <canvas id="ingresos-egresos-<?= esc_attr($unique_id) ?>" 
                    height="300"
                    data-chart-data="<?= esc_attr(json_encode([
                        'labels' => array_column($stats['meses'], 'etiqueta'),
                        'datasets' => [
                            [
                                'label' => __('Ingresos', 'rd-finanzas'),
                                'data' => array_column($stats['meses'], 'ingresos'),
                                'backgroundColor' => 'rgba(75, 192, 192, 0.7)'
                            ],
                            [
                                'label' => __('Egresos', 'rd-finanzas'),
                                'data' => array_column($stats['meses'], 'egresos'),
                                'backgroundColor' => 'rgba(255, 99, 132, 0.7)'
                            ]
                        ]
                    ])) ?>">
            </canvas>
        </div>
    </div>
</div>