<?php
/**
 * Template part for displaying the finanzas table.
 *
 * Expects $finanzas_posts to be available.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Use $posts if $finanzas_posts is not set (for compatibility with AJAX handler)
if (!isset($finanzas_posts) && isset($posts)) {
    $finanzas_posts = $posts;
}

?>
<table class="finanzas-table">
    <thead>
        <tr>
            <th><?= __('Fecha', 'rd-finanzas') ?></th>
            <th><?= __('Concepto', 'rd-finanzas') ?></th>
            <th><?= __('Tipo', 'rd-finanzas') ?></th>
            <th><?= __('Categoría', 'rd-finanzas') ?></th>
            <th><?= __('Monto', 'rd-finanzas') ?></th>
            <th><?= __('Acciones', 'rd-finanzas') ?></th>
            <th><?= __('Disparos', 'rd-finanzas') ?></th>
            <th><?= __('Método Pago', 'rd-finanzas') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($finanzas_posts)) : ?>
            <tr>
                <td colspan="8" class="empty-state">
                    <p><?= __('No hay registros que coincidan con los filtros.', 'rd-finanzas') ?></p>
                </td>
            </tr>
        <?php else : ?>
            <?php foreach ($finanzas_posts as $post) :
                $meta = rd_get_finanzas_meta($post->ID);
                $tipo = isset($meta['tipo']) ? $meta['tipo'] : '';
                $monto = isset($meta['monto']) ? $meta['monto'] : 0;
                $fecha = isset($meta['fecha']) ? $meta['fecha'] : '';
                $concepto = isset($meta['concepto']) ? $meta['concepto'] : $post->post_title; // Use title as fallback
                $disparos = isset($meta['disparos']) ? floatval($meta['disparos']) : 0;
                $metodo_pago = isset($meta['metodo_pago']) ? $meta['metodo_pago'] : '';
                $categoria = isset($meta['categoria']) ? $meta['categoria'] : ''; // Get category from meta first

                // Fallback: Get category term if meta is empty
                if (empty($categoria)) {
                    $terms = wp_get_object_terms($post->ID, 'categoria_finanza');
                    if (!is_wp_error($terms) && !empty($terms)) {
                        $categoria = $terms[0]->name;
                    }
                }

            ?>
                <tr data-id="<?= esc_attr($post->ID) ?>">
                    <td><?= esc_html($fecha) ?></td>
                    <td><?= esc_html($concepto) ?></td>
                    <td>
                        <span class="badge badge-<?= $tipo === 'ingreso' ? 'success' : 'danger' ?>">
                            <?= strtolower($tipo) === 'ingreso' ? __('Ingreso', 'rd-finanzas') : __('Egreso', 'rd-finanzas') ?>
                        </span>
                    </td>
                    <td><?= esc_html($categoria) ?></td>
                    <td class="amount <?= $tipo === 'ingreso' ? 'positive' : 'negative' ?>">
                        <?= strtolower($tipo) === 'ingreso' ? '+' : '-' ?> $<?= number_format(floatval($monto), 2) ?>
                    </td>
                    <td class="actions">
                        <button type="button" class="edit-finanza" data-id="<?= esc_attr($post->ID) ?>">
                            <i class="icon-edit"></i>Editar
                        </button>
                        <button type="button" class="delete-finanza" data-id="<?= esc_attr($post->ID) ?>">
                            <i class="icon-trash"></i>Borrar
                        </button>
                    </td>
                    <td><?= esc_html($disparos) ?></td>
                    <td><?= esc_html($metodo_pago) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php
// Paginador
$current_page = isset($current_page) ? (int)$current_page : 1;
$total_pages = isset($total_pages) ? (int)$total_pages : 1;
if ($total_pages > 1): ?>
<nav class="finanzas-pagination" aria-label="Paginación de finanzas">
    <ul class="pagination-list">
        <li>
            <button class="pagination-btn first-page" data-page="1" <?= $current_page === 1 ? 'disabled' : '' ?>>« <?= __('Primera', 'rd-finanzas') ?></button>
        </li>
        <li>
            <button class="pagination-btn prev-page" data-page="<?= max(1, $current_page - 1) ?>" <?= $current_page === 1 ? 'disabled' : '' ?>>‹ <?= __('Anterior', 'rd-finanzas') ?></button>
        </li>
        <li class="pagination-info">
            <?= sprintf(__('Página %d de %d', 'rd-finanzas'), $current_page, $total_pages) ?>
        </li>
        <li>
            <button class="pagination-btn next-page" data-page="<?= min($total_pages, $current_page + 1) ?>" <?= $current_page === $total_pages ? 'disabled' : '' ?>><?= __('Siguiente', 'rd-finanzas') ?> ›</button>
        </li>
        <li>
            <button class="pagination-btn last-page" data-page="<?= $total_pages ?>" <?= $current_page === $total_pages ? 'disabled' : '' ?>><?= __('Última', 'rd-finanzas') ?> »</button>
        </li>
    </ul>
</nav>
<?php endif; ?>
