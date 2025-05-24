<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= __('Métodos de Pago', 'rd-finanzas') ?></h3>
    </div>
    <div class="card-content">
        <div class="payment-methods">
            <?php 
            $metodos = [
                'tarjeta' => ['icon' => 'icon-card', 'porcentaje' => 65],
                'efectivo' => ['icon' => 'icon-cash', 'porcentaje' => 25],
                'transferencia' => ['icon' => 'icon-transfer', 'porcentaje' => 10]
            ];
            
            foreach ($metodos as $metodo => $data) : ?>
            <div class="payment-method">
                <div class="payment-info">
                    <div class="payment-icon">
                        <i class="<?= $data['icon'] ?>"></i>
                    </div>
                    <p class="payment-name">
                        <?= ucfirst(__($metodo, 'rd-finanzas')) ?>
                    </p>
                </div>
                <div class="payment-percentage">
                    <?= $data['porcentaje'] ?>%
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>