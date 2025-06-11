<?php 
$pageTitle = "Dashboard";
$activePage = "home";
require __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-calendar me-1"></i><?= date('d/m/Y') ?>
            </button>
        </div>
    </div>
</div>

<!-- Cards de estatísticas rápidas -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
    <div class="col">
        <div class="card shadow-sm border-0 bg-primary text-white h-100 d-flex flex-column">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-0" id="total-products">--</h4>
                    <p class="card-text mb-0">Produtos</p>
                </div>
                <i class="fas fa-boxes fa-2x opacity-75"></i>
            </div>
            <div class="card-footer bg-primary bg-opacity-75 border-0">
                <small><i class="fas fa-info-circle me-1"></i>Total de produtos cadastrados</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0 bg-success text-white h-100 d-flex flex-column">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-0" id="total-orders">--</h4>
                    <p class="card-text mb-0">Pedidos</p>
                </div>
                <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
            </div>
            <div class="card-footer bg-success bg-opacity-75 border-0">
                <small><i class="fas fa-info-circle me-1"></i>Total de pedidos realizados</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0 bg-warning text-white h-100 d-flex flex-column">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-0" id="total-coupons">--</h4>
                    <p class="card-text mb-0">Cupons</p>
                </div>
                <i class="fas fa-tags fa-2x opacity-75"></i>
            </div>
            <div class="card-footer bg-warning bg-opacity-75 border-0">
                <small><i class="fas fa-info-circle me-1"></i>Cupons ativos disponíveis</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm border-0 bg-info text-white h-100 d-flex flex-column">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-0" id="cart-items">--</h4>
                    <p class="card-text mb-0">No Carrinho</p>
                </div>
                <i class="fas fa-shopping-basket fa-2x opacity-75"></i>
            </div>
            <div class="card-footer bg-info bg-opacity-75 border-0">
                <small><i class="fas fa-info-circle me-1"></i>Itens no carrinho atual</small>
            </div>
        </div>
    </div>
</div>

<!-- Seção de boas-vindas -->
<div class="row mt-4">
    <div class="col-md-12 text-center mb-4">
        <h2>Bem-vindo ao Mini ERP</h2>
        <p class="lead text-muted">Sistema completo de gestão de pedidos, produtos e estoque</p>
    </div>
</div>

<!-- Cards de navegação principal -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4 d-flex flex-column">
                <div class="mb-3">
                    <i class="fas fa-boxes fa-4x text-primary"></i>
                </div>
                <h4 class="card-title">Produtos</h4>
                <p class="card-text text-muted">Gerencie seu catálogo de produtos, controle de estoque e variações</p>
                <div class="mt-auto">
                    <div class="d-grid gap-2">
                        <a href="/products/list" class="btn btn-primary">
                            <i class="bi bi-box-seam me-1"></i> Gerenciar Produtos
                        </a>
                        <a href="/products/create" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i> Novo Produto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4 d-flex flex-column">
                <div class="mb-3">
                    <i class="fas fa-shopping-cart fa-4x text-success"></i>
                </div>
                <h4 class="card-title">Pedidos</h4>
                <p class="card-text text-muted">Visualize, gerencie e acompanhe todos os seus pedidos e vendas</p>
                <div class="mt-auto">
                    <div class="d-grid gap-2">
                        <a href="/orders/list" class="btn btn-success">
                            <i class="bi bi-cart me-1"></i> Ver Pedidos
                        </a>
                        <!-- Botão invisível para alinhar com os outros cards -->
                        <a class="btn btn-outline-success invisible">Placeholder</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4 d-flex flex-column">
                <div class="mb-3">
                    <i class="fas fa-tags fa-4x text-warning"></i>
                </div>
                <h4 class="card-title">Cupons</h4>
                <p class="card-text text-muted">Crie e gerencie cupons de desconto para suas promoções</p>
                <div class="mt-auto">
                    <div class="d-grid gap-2">
                        <a href="/coupons/list" class="btn btn-warning">
                            <i class="bi bi-ticket-perforated me-1"></i> Gerenciar Cupons
                        </a>
                        <a href="/coupons/create" class="btn btn-outline-warning">
                            <i class="bi bi-plus-circle me-1"></i> Novo Cupom
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seção de ações rápidas -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Ações Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3 mb-2">
                        <a href="/products/create" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-plus me-1"></i> Novo Produto
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/orders/list" class="btn btn-outline-success btn-sm w-100">
                            <i class="fas fa-list me-1"></i> Ver Pedidos
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/coupons/create" class="btn btn-outline-warning btn-sm w-100">
                            <i class="fas fa-plus me-1"></i> Novo Cupom
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="/orders/cart" class="btn btn-outline-info btn-sm w-100">
                            <i class="fas fa-shopping-cart me-1"></i> Ver Carrinho
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Carrega estatísticas do dashboard
    $.get('/api/products', function(response) {
        $('#total-products').text(response.total || response.data?.length || 0);
    });

    $.get('/api/orders', function(response) {
        $('#total-orders').text(response.total || response.data?.length || 0);
    });

    $.get('/api/coupons', function(response) {
        $('#total-coupons').text(response.total || response.data?.length || 0);
    });

    // Carrega itens do carrinho
    $.get('/api/cart', function(response) {
        let count = 0;
        if (response.data && response.data.items) {
            Object.values(response.data.items).forEach(item => {
                count += item.quantity ? parseInt(item.quantity) : 1;
            });
        }
        $('#cart-items').text(count);
    });
});
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
