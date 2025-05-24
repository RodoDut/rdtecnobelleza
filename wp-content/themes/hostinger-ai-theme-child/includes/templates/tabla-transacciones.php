<?php
/*
Desde la función: "rd_render_tabla_transacciones()" dentro de finanzas-shortcode.php se incluye el siguiente template.
EL arreglo $transacciones, está declarado en dicha función, pero si no lo estuviere, debo asegurarme de que no apunte a null.
*/

if (!isset($transacciones) || !is_array($transacciones)) {
    $transacciones = [];
}
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= __('Transacciones Recientes', 'rd-finanzas') ?></h3>
    </div>
    <div class="card-content">
        <table class="finanzas-table">
            <thead>
                <tr>
                    <th><?= __('Fecha', 'rd-finanzas') ?></th>
                    <th><?= __('Tipo', 'rd-finanzas') ?></th>
                    <th><?= __('Monto', 'rd-finanzas') ?></th>
                    <th><?= __('Descripción', 'rd-finanzas') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transacciones as $trans) : ?>
                <tr>
                    <td><?= esc_html($trans['fecha']) ?></td>
                    <td><?= ucfirst(esc_html($trans['tipo'])) ?></td>
                    <td class="<?= $trans['tipo'] === 'ingreso' ? 'income' : 'expense' ?>">
                        <?= number_format($trans['monto'], 2) ?>
                    </td>
                    <td><?= esc_html($trans['descripcion']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?= $stats['paginacion'] ?? '' ?>
    </div>
</div>