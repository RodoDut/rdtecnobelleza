<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=finanzas-' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

// Encabezados
fputcsv($output, [
    __('ID', 'rd-finanzas'),
    __('Fecha', 'rd-finanzas'),
    __('Tipo', 'rd-finanzas'),
    __('Monto', 'rd-finanzas'),
    __('Descripción', 'rd-finanzas'),
    __('Disparos', 'rd-finanzas')
]);

// Datos
while ($query->have_posts()) {
    $query->the_post();
    fputcsv($output, [
        get_the_ID(),
        get_field('fecha'),
        get_field('tipo'),
        rd_limpiar_monto(get_field('monto')),
        get_field('descripcion'),
        get_field('disparos')
    ]);
}

fclose($output);
exit;
?>