<?php
$pageTitle = "Detalhes do Cupom";
$activePage = "coupons";
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';

$code = $params['code'] ?? '';
?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-eye me-2"></i>Detalhes do Cupom</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/coupons/list" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
            <a href="/coupons/edit/<?= urlencode($code) ?>" class="btn btn-sm btn-primary ms-2">
                <i class="fas fa-edit me-1"></i> Editar
            </a>
        </div>
    </div>

    <div id="coupon-details">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Carregando cupom...</p>
        </div>
    </div>
</div>

<script>
$(function() {
    const code = <?= json_encode($code) ?>;
    $.get(`/coupons/${encodeURIComponent(code)}`, function(response) {
        if(response.success && response.data) {
            const coupon = response.data;
            let html = `
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">${coupon.code}</h4>
                        <p><strong>Tipo:</strong> ${coupon.discount_type === 'percentage' ? 'Percentual (%)' : 'Valor Fixo (R$)'}</p>
                        <p><strong>Valor:</strong> ${coupon.discount_type === 'percentage' ? coupon.discount_value + '%' : 'R$ ' + parseFloat(coupon.discount_value).toFixed(2)}</p>
                        <p><strong>Valor Mínimo:</strong> R$ ${parseFloat(coupon.min_value).toFixed(2)}</p>
                        <p><strong>Validade:</strong> ${coupon.valid_until ? new Date(coupon.valid_until).toLocaleDateString('pt-BR') : '-'}</p>
                        <p><strong>Status:</strong> ${coupon.is_valid ? '<span class="badge bg-success">Válido</span>' : '<span class="badge bg-secondary">Expirado</span>'}</p>
                    </div>
                </div>
            `;
            $('#coupon-details').html(html);
        } else {
            $('#coupon-details').html('<div class="alert alert-danger">Cupom não encontrado.</div>');
        }
    }).fail(function() {
        $('#coupon-details').html('<div class="alert alert-danger">Erro ao carregar cupom.</div>');
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>