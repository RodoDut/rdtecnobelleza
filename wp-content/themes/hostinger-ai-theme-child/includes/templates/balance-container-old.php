<div id="<?php echo esc_attr($unique_id); ?>" class="balance-page" data-nonce="<?php echo esc_attr(wp_create_nonce('rd_balances_nonce')); ?>">
    <!-- Filtros de fecha -->
    <div class="page-header">
        <h2 class="page-title"><?= __('Balances', 'rd-finanzas') ?></h2>
        <div class="date-filter">
            <div class="date-range-picker">
                <input type="text" id="date-range-<?php echo esc_attr($unique_id); ?>" class="date-input" placeholder="<?= __('Seleccionar rango de fechas', 'rd-finanzas') ?>">
                <i class="icon-calendar"></i>
                <button class="filter-button">
                <i class="icon-filter"></i>
                <?= __('Filtrar', 'rd-finanzas') ?>
            </button>
            </div>
        </div>
    </div>

    <!-- Pestañas -->
    <div class="tabs">
        <div class="tabs-list">
            <button class="tab-trigger active" data-tab="gestionar">
                <?= __('Gestionar Finanzas', 'rd-finanzas') ?>
            </button>
            <button class="tab-trigger" data-tab="general">
                <?= __('Balance General', 'rd-finanzas') ?>
            </button>
            <button class="tab-trigger" data-tab="estadisticas">
                <?= __('Estadísticas', 'rd-finanzas') ?>
            </button>
        </div>
        
        <!-- Contenido Gestionar Finanzas (nuevo) -->
        <div class="tab-content active" id="gestionar-tab">
            <?php include __DIR__ . '/form-finanzas.php'; ?>
        </div>

        <!-- Contenido General -->
        <div class="tab-content" id="general-tab">
            <!-- Tarjetas de Estadísticas -->
            <div class="stats-cards">
                <?php include __DIR__ . '/card-ingresos.php'; ?>
                <?php include __DIR__ . '/card-egresos.php'; ?>
                <?php include __DIR__ . '/card-balance.php'; ?>
                <?php include __DIR__ . '/card-disparos.php'; ?>
            </div>

            <!-- Tabla y Métodos de Pago -->
            <div class="detail-cards">
                <?= rd_render_tabla_transacciones($stats['transacciones'] ?? []) ?>
                
                <?php if (isset($atts['mostrar_pagos']) && $atts['mostrar_pagos'] === 'si') : ?>
                    <?php include __DIR__ . '/card-metodos-pago.php'; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contenido de Estadísticas -->
        <div class="tab-content" id="estadisticas-tab">
            <div class="chart-section">
                <h3><?= __('Ingresos y Egresos Mensuales', 'rd-finanzas') ?></h3>
                <div class="chart-container">
                    <canvas id="income-chart-<?php echo esc_attr($unique_id); ?>"></canvas>
                </div>
            </div>
            
            <div class="chart-section">
                <h3><?= __('Gastos Mensuales', 'rd-finanzas') ?></h3>
                <div class="chart-container">
                    <canvas id="expenses-chart-<?php echo esc_attr($unique_id); ?>"></canvas>
                </div>
            </div>
            
            <div class="chart-section">
                <h3><?= __('Comparación con Período Anterior', 'rd-finanzas') ?></h3>
                <div class="chart-container">
                    <canvas id="comparison-chart-<?php echo esc_attr($unique_id); ?>"></canvas>
                </div>
            </div>
            
            <?php 
            // Incluir archivos de gráficos si existen
            $chart_files = [
                'chart-ingresos-egresos.php',
                'chart-balance.php',
                'chart-disparos.php'
            ];
            
            foreach ($chart_files as $chart_file) {
                $chart_path = __DIR__ . '/' . $chart_file;
                if (file_exists($chart_path)) {
                    include $chart_path;
                }
            }
            ?>
        </div>
    </div>

    <!-- Botón Exportar -->
    <a href="<?= admin_url('admin-post.php?action=rd_export_csv&nonce=' . wp_create_nonce('rd_export_csv')) ?>" 
       class="export-button">
        <i class="icon-download"></i>
        <?= __('Exportar CSV', 'rd-finanzas') ?>
    </a>
</div>