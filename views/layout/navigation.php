<div class="sidebar d-flex flex-column">
    <div class="sidebar-header text-center py-3 border-bottom pt-4">
        <span class="fw-bold fs-5 text-primary "><i class="fas fa-cubes me-2"></i>Menu</span>
    </div>
    <ul class="nav flex-column mt-3">
        <li class="nav-item mb-1">
            <a class="nav-link <?= ($activePage ?? '') === 'home' ? 'active' : '' ?>" href="/">
                <i class="bi bi-house-door fs-5 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>" href="/products/list">
                <i class="bi bi-box-seam fs-5 me-2"></i> Produtos
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link <?= ($activePage ?? '') === 'orders' ? 'active' : '' ?>" href="/orders/list">
                <i class="bi bi-cart fs-5 me-2"></i> Pedidos
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link <?= ($activePage ?? '') === 'coupons' ? 'active' : '' ?>" href="/coupons/list">
                <i class="bi bi-ticket-perforated fs-5 me-2"></i> Cupons
            </a>
        </li>
    </ul>
    <div class="mt-auto px-3 pb-3">
        <hr>
        <div class="text-muted small text-center">Mini ERP v1.0</div>
    </div>
</div>
