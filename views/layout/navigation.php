<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($activePage ?? '') === 'home' ? 'active' : '' ?>" href="/">
                    <i class="bi bi-house-door me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>" href="/products/list">
                    <i class="bi bi-box-seam me-2"></i> Produtos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($activePage ?? '') === 'orders' ? 'active' : '' ?>" href="/orders/list">
                    <i class="bi bi-cart me-2"></i> Pedidos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($activePage ?? '') === 'coupons' ? 'active' : '' ?>" href="/coupons/list">
                    <i class="bi bi-ticket-perforated me-2"></i> Cupons
                </a>
            </li>
        </ul>
        
        <hr>
        
        <div class="px-3">
            <div class="text-muted small">Mini ERP v1.0</div>
        </div>
    </div>
</div>
