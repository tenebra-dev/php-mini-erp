<?php 
// As variáveis $pageTitle, $activePage, $breadcrumbs já foram definidas no index.php
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
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0" id="total-products">--</h4>
                        <p class="card-text">Produtos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-boxes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-primary bg-opacity-75">
                <small><i class="fas fa-info-circle me-1"></i>Total de produtos cadastrados</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0" id="total-orders">--</h4>
                        <p class="card-text">Pedidos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-success bg-opacity-75">
                <small><i class="fas fa-info-circle me-1"></i>Total de pedidos realizados</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0" id="total-coupons">--</h4>
                        <p class="card-text">Cupons</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-tags fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-warning bg-opacity-75">
                <small><i class="fas fa-info-circle me-1"></i>Cupons ativos disponíveis</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0" id="cart-items"><?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?></h4>
                        <p class="card-text">No Carrinho</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-basket fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-info bg-opacity-75">
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
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-boxes fa-4x text-primary"></i>
                </div>
                <h4 class="card-title">Produtos</h4>
                <p class="card-text text-muted">Gerencie seu catálogo de produtos, controle de estoque e variações</p>
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
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-shopping-cart fa-4x text-success"></i>
                </div>
                <h4 class="card-title">Pedidos</h4>
                <p class="card-text text-muted">Visualize, gerencie e acompanhe todos os seus pedidos e vendas</p>
                <div class="d-grid gap-2">
                    <a href="/orders/list" class="btn btn-success">
                        <i class="bi bi-cart me-1"></i> Ver Pedidos
                    </a>
                    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                    <a href="/orders/cart" class="btn btn-outline-success">
                        <i class="bi bi-cart-check me-1"></i> Carrinho (<?= count($_SESSION['cart']) ?>)
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-tags fa-4x text-warning"></i>
                </div>
                <h4 class="card-title">Cupons</h4>
                <p class="card-text text-muted">Crie e gerencie cupons de desconto para suas promoções</p>
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

<!-- Seção de ações rápidas -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Ações Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="/products/create" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-plus me-1"></i> Novo Produto
                        </a>
                    </div>
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

<?php require __DIR__ . '/layout/footer.php'; ?>
