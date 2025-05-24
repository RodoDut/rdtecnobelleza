<?php
/**
 * Formulario para gestionar posts de tipo finanzas
 */

// Obtener listado de posts de finanzas
$finanzas_posts = rd_get_finanzas_posts();
$nonce = wp_create_nonce('rd_finanzas_crud_nonce');
?>

<div class="finanzas-manager">
    <div class="finanzas-list-container">
        <div class="finanzas-header">
            <h3><?= __('Registros de Finanzas', 'rd-finanzas') ?></h3>
            <button type="button" class="add-new-button" id="add-new-finanza">
                <i class="icon-plus"></i> <?= __('Añadir Nuevo', 'rd-finanzas') ?>
            </button>
        </div>
        
        <div class="finanzas-filters">
            <div class="search-box">
                <i class="icon-search"></i>
                <input type="search" id="search-finanzas" placeholder="<?= __('Buscar...', 'rd-finanzas') ?>">
            </div>
            <div class="date-range-wrapper">
                <input type="text" id="date-input-form-finanzas" class="date-input" placeholder="<?= __('Seleccionar rango de fechas', 'rd-finanzas') ?>">
            </div>
            <div class="select-wrapper">
                <select id="filter-finanzas-type">
                    <option value=""><?= __('Todos los tipos', 'rd-finanzas') ?></option>
                    <option value="Ingreso"><?= __('Ingresos', 'rd-finanzas') ?></option>
                    <option value="Egreso"><?= __('Egresos', 'rd-finanzas') ?></option>
                </select>
            </div>
        </div>
        
        <div class="finanzas-list" id="finanzas-list">
            <!-- La tabla y paginación se cargan por AJAX -->
        </div>
    </div>
    
    <div class="finanzas-form-container" id="finanzas-form-container" style="display: none;">
        <div class="form-header">
            <h3 id="form-title"><?= __('Añadir Nuevo Registro', 'rd-finanzas') ?></h3>
            <button type="button" class="close-form" id="close-form">
                <i class="icon-x"></i>
            </button>
        </div>
        
        <form id="finanzas-form" class="finanzas-form">
            <input type="hidden" id="post-id" name="post_id" value="0">
            <input type="hidden" name="nonce" value="<?= esc_attr($nonce) ?>">

            
            <div class="form-group">
                <label for="concepto"><?= __('Concepto', 'rd-finanzas') ?></label>
                <input type="text" id="concepto" name="concepto" required>
            </div>
            
            <div class="form-group">
                <label for="fecha"><?= __('Fecha', 'rd-finanzas') ?></label>
                <input type="date" id="fecha" name="fecha" required>
            </div>
            
            <div class="form-group">
                <label for="tipo"><?= __('Tipo', 'rd-finanzas') ?></label>
                <select id="tipo" name="tipo" required>
                    <option value=""><?= __('Seleccionar...', 'rd-finanzas') ?></option>
                    <option value="ingreso"><?= __('Ingreso', 'rd-finanzas') ?></option>
                    <option value="egreso"><?= __('Egreso', 'rd-finanzas') ?></option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="monto"><?= __('Monto', 'rd-finanzas') ?></label>
                <div class="input-with-icon">
                    <span class="input-icon">$</span>
                    <input type="number" id="monto" name="monto" step="0.01" min="0" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="categoria"><?= __('Categoría', 'rd-finanzas') ?></label>
                <select id="categoria" name="categoria">
                    <option value=""><?= __('Sin categoría', 'rd-finanzas') ?></option>
                    <?php 
                    $categories = rd_get_finanzas_categories();
                    foreach ($categories as $cat) : 
                    ?>
                        <option value="<?= esc_html($cat->name) ?>"><?= esc_html($cat->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="metodo_pago"><?= __('Método de Pago', 'rd-finanzas') ?></label>
                <select id="metodo_pago" name="metodo_pago">
                    <option value=""><?= __('Seleccionar...', 'rd-finanzas') ?></option>
                    <option value="transferencia"><?= __('Transferencia', 'rd-finanzas') ?></option>
                    <option value="efectivo"><?= __('Efectivo', 'rd-finanzas') ?></option>
                    <option value="debito"><?= __('Debito', 'rd-finanzas') ?></option>
                    <option value="tarjeta"><?= __('Tarjeta', 'rd-finanzas') ?></option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="descripcion"><?= __('Descripcion', 'rd-finanzas') ?></label>
                <textarea id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="disparos"><?= __('Disparos', 'rd-finanzas') ?></label>
                <input type="number" id="disparos" name="disparos">
            </div>
            
            <div class="form-actions">
                <button type="button" id="cancel-form" class="cancel-button">
                    <?= __('Cancelar', 'rd-finanzas') ?>
                </button>
                <button type="submit" class="submit-button">
                    <?= __('Guardar', 'rd-finanzas') ?>
                </button>
            </div>
        </form>
    </div>
</div>