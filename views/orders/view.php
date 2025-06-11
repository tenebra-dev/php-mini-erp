<?php 
require __DIR__ . '/../layout/header.php';

$orderId = isset($params['id']) ? $params['id'] : null;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-eye me-2"></i>Detalhes do Pedido</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/orders/list" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informações do Pedido</h5>
            </div>
            <div class="card-body" id="order-details">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Ações</h5>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="/orders/list" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i> Ver todos os pedidos
                </a>
                <!-- Adicione outras ações se necessário -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const orderId = <?= json_encode($orderId) ?>;
    $.get(`/api/orders/${orderId}`, function(response) {
        if (!response.success || !response.data) {
            $('#order-details').html('<div class="alert alert-danger">Erro ao carregar pedido.</div>');
            return;
        }
        const order = response.data;
        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>ID:</strong> ${order.id}</p>
                    <p><strong>Cliente:</strong> ${order.customer_name}</p>
                    <p><strong>Status:</strong> ${order.status}</p>
                    <p><strong>Data:</strong> ${order.created_at ? new Date(order.created_at).toLocaleString('pt-BR') : '-'}</p>
                    <p><strong>E-mail:</strong> ${order.customer_email}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total:</strong> R$ ${parseFloat(order.total).toFixed(2)}</p>
                    <p><strong>Endereço:</strong> ${order.customer_address}, ${order.customer_neighborhood || ''} ${order.customer_complement || ''} - ${order.customer_city}/${order.customer_state}</p>
                    <p><strong>CEP:</strong> ${order.customer_cep}</p>
                    <p><strong>Cupom:</strong> ${order.coupon_code || '-'}</p>
                    <p><strong>Desconto:</strong> R$ ${parseFloat(order.discount).toFixed(2)}</p>
                    <p><strong>Frete:</strong> R$ ${parseFloat(order.shipping).toFixed(2)}</p>
                </div>
            </div>
            <h5 class="mt-4">Itens do Pedido</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        (order.items || []).forEach(item => {
            html += `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.variation_name || '-'}</td>
                    <td>${item.quantity}</td>
                    <td>R$ ${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>R$ ${(item.unit_price * item.quantity).toFixed(2)}</td>
                </tr>
            `;
        });
        html += `
                    </tbody>
                </table>
            </div>
        `;
        $('#order-details').html(html);
    }).fail(function() {
        $('#order-details').html('<div class="alert alert-danger">Erro ao carregar pedido.</div>');
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>