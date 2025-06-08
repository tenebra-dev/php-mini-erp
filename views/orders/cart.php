<?php 
$pageTitle = "Carrinho de Compras";
$activePage = "orders";
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/navigation.php';
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-shopping-cart me-2"></i>Carrinho de Compras</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Itens no Carrinho</h5>
                </div>
                <div class="card-body" id="cartItems">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Resumo do Pedido</h5>
                </div>
                <div class="card-body" id="orderSummary">
                    <!-- Resumo será carregado aqui -->
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Cupom de Desconto</h5>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="couponCode" placeholder="Código do cupom">
                        <button class="btn btn-outline-primary" type="button" id="applyCoupon">Aplicar</button>
                    </div>
                    <div id="couponMessage"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12">
            <a href="/orders/checkout" class="btn btn-primary btn-lg float-end">
                <i class="fas fa-credit-card me-1"></i> Finalizar Compra
            </a>
        </div>
    </div>
</div>

<script>
function showToast(type, message) {
    const toast = `
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">${type === 'success' ? 'Sucesso' : 'Erro'}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    </div>
    `;
    $('body').append(toast);
    setTimeout(() => $('.toast').remove(), 3000);
}

function makeRequest(url, method = 'GET', data = null) {
    return $.ajax({
        url:  url,
        method: method,
        contentType: 'application/json',
        data: data ? JSON.stringify(data) : undefined,
        dataType: 'json'
    });
}

function renderCartItems(items) {
    const $cartItems = $('#cartItems');
    if (!items || Object.keys(items).length === 0) {
        $cartItems.html(`
            <div class="alert alert-info">
                Seu carrinho está vazio. <a href="/products/list">Adicione produtos</a> para continuar.
            </div>
        `);
        return;
    }
    let html = '';
    $.each(items, function(_, item) {
        html += `
            <div class="row mb-3 align-items-center" data-item-key="${item.product_id}_${item.variation_id}">
                <div class="col-md-2">
                    <img src="${item.image || 'https://via.placeholder.com/80'}" class="img-fluid rounded" alt="${item.product_name}">
                </div>
                <div class="col-md-5">
                    <h6 class="mb-1">${item.product_name}</h6>
                    <small class="text-muted">${item.variation_name || 'Sem variação'}</small>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary decrease-qty" type="button">-</button>
                        <input type="number" class="form-control text-center quantity-input" value="${item.quantity}" min="1">
                        <button class="btn btn-outline-secondary increase-qty" type="button">+</button>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <span class="fw-bold">R$ ${(item.unit_price * item.quantity).toFixed(2)}</span>
                    <button class="btn btn-sm btn-outline-danger ms-2 remove-item" title="Remover">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <hr>
        `;
    });
    $cartItems.html(html);
}

function renderOrderSummary(cart) {
    const $orderSummary = $('#orderSummary');
    let html = `
        <div class="d-flex justify-content-between mb-2">
            <span>Subtotal:</span>
            <span>R$ ${cart.subtotal?.toFixed(2) || '0.00'}</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span>Frete:</span>
            <span>R$ ${cart.shipping?.toFixed(2) || '0.00'}</span>
        </div>
    `;
    if (cart.discount > 0) {
        html += `
            <div class="d-flex justify-content-between mb-2 text-success">
                <span>Desconto:</span>
                <span>- R$ ${cart.discount.toFixed(2)}</span>
            </div>
        `;
    }
    html += `
        <hr>
        <div class="d-flex justify-content-between fw-bold fs-5">
            <span>Total:</span>
            <span>R$ ${cart.total?.toFixed(2) || '0.00'}</span>
        </div>
    `;
    $orderSummary.html(html);
}

function loadCart() {
    makeRequest('/cart')
        .done(function(cartResponse) {
            // Suporte para resposta {success, data: {cart...}} ou {success, cart: {...}}
            const cart = cartResponse.cart || cartResponse.data || {};
            if (cartResponse.success) {
                renderCartItems(cart.items || {});
                renderOrderSummary(cart);
            } else {
                showToast('danger', cartResponse.error || 'Falha ao carregar carrinho');
            }
        })
        .fail(function(xhr) {
            showToast('danger', 'Falha na comunicação com o servidor');
        });
}

$(document).ready(function() {
    loadCart();

    // Aplica cupom
    $('#applyCoupon').on('click', function() {
        const couponCode = $('#couponCode').val().trim();
        if (!couponCode) {
            showToast('warning', 'Por favor, insira um código de cupom');
            return;
        }
        makeRequest('/cart', 'POST', { coupon_code: couponCode })
            .done(function(response) {
                if (response.success) {
                    $('#couponMessage').html(`
                        <div class="alert alert-success">
                            Cupom aplicado com sucesso!
                        </div>
                    `);
                    loadCart();
                } else {
                    $('#couponMessage').html(`
                        <div class="alert alert-danger">
                            ${response.error || 'Falha ao aplicar cupom'}
                        </div>
                    `);
                }
            });
    });

    // Manipulação de quantidade e remoção de itens
    $('#cartItems').on('click', '.decrease-qty', function() {
        const $row = $(this).closest('.row[data-item-key]');
        const [product_id, variation_id] = $row.data('item-key').split('_');
        const $qtyInput = $row.find('.quantity-input');
        let newQty = parseInt($qtyInput.val());
        if (newQty > 1) {
            newQty--;
            $qtyInput.val(newQty);
            updateCart(product_id, variation_id, newQty);
        }
    });

    $('#cartItems').on('click', '.increase-qty', function() {
        const $row = $(this).closest('.row[data-item-key]');
        const [product_id, variation_id] = $row.data('item-key').split('_');
        const $qtyInput = $row.find('.quantity-input');
        let newQty = parseInt($qtyInput.val());
        newQty++;
        $qtyInput.val(newQty);
        updateCart(product_id, variation_id, newQty);
    });

    $('#cartItems').on('click', '.remove-item', function() {
        const $row = $(this).closest('.row[data-item-key]');
        const [product_id, variation_id] = $row.data('item-key').split('_');
        if (confirm('Remover este item do carrinho?')) {
            removeFromCart(product_id, variation_id);
        }
    });

    // Atualiza quantidade ao editar input manualmente
    $('#cartItems').on('change', '.quantity-input', function() {
        const $row = $(this).closest('.row[data-item-key]');
        const [product_id, variation_id] = $row.data('item-key').split('_');
        let newQty = parseInt($(this).val());
        if (isNaN(newQty) || newQty < 1) newQty = 1;
        $(this).val(newQty);
        updateCart(product_id, variation_id, newQty);
    });

    function updateCart(product_id, variation_id, quantity) {
        makeRequest('/cart/update', 'PUT', { product_id, variation_id, quantity })
            .done(loadCart);
    }

    function removeFromCart(product_id, variation_id) {
        makeRequest(`/cart/remove/${product_id}_${variation_id}`, 'DELETE')
            .done(loadCart);
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
