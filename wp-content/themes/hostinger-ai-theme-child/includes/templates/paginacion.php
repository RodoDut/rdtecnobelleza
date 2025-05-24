<div class="finanzas-paginacion">
    <?php if ($query->query_vars['paged'] > 1) : ?>
        <a href="<?= add_query_arg('pagina', $query->query_vars['paged'] - 1) ?>" class="pagination-link">
            &laquo; <?= __('Anterior', 'rd-finanzas') ?>
        </a>
    <?php endif; ?>

    <span class="current-page">
        <?= sprintf(__('Página %d de %d', 'rd-finanzas'), $query->query_vars['paged'], $query->max_num_pages) ?>
    </span>

    <?php if ($query->query_vars['paged'] < $query->max_num_pages) : ?>
        <a href="<?= add_query_arg('pagina', $query->query_vars['paged'] + 1) ?>" class="pagination-link">
            <?= __('Siguiente', 'rd-finanzas') ?> &raquo;
        </a>
    <?php endif; ?>
</div>