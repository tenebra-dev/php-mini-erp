<?php
$pageTitle = "Novo Cupom";
$activePage = "coupons";
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-plus me-2"></i>Novo Cupom</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/coupons/list" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="coupon-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Código do Cupom *</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="discount_type" class="form-label">Tipo de Desconto *</label>
                        <select class="form-select" id="discount_type" name="discount_type" required>
                            <option value="fixed">Valor Fixo (R$)</option>
                            <option value="percent">Percentual (%)</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="discount_value" class="form-label">Valor do Desconto *</label>
                        <input type="number" class="form-control" id="discount_value" name="discount_value" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="min_value" class="form-label">Valor Mínimo para Uso</label>
                        <input type="number" class="form-control" id="min_value" name="min_value" min="0" step="0.01">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="valid_until" class="form-label">Validade</label>
                        <input type="date" class="form-control" id="valid_until" name="valid_until">
                    </div>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <button type="reset" class="btn btn-secondary me-md-2">
                        <i class="fas fa-undo me-1"></i> Limpar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Salvar Cupom
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    $('#coupon-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: '/coupons',
            type: 'POST',
            data: formData,
            success: function(response) {
                showToast('success', 'Cupom criado com sucesso!');
                setTimeout(() => window.location.href = '/coupons/list', 1200);
            },
            error: function(xhr) {
                let msg = 'Erro ao criar cupom';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showToast('error', msg);
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>