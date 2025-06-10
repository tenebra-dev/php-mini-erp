<?php 
$pageTitle = "Finalizar Compra";
$activePage = "orders";
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/navigation.php';
?>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Finalizar Compra</h1>
    </div>

    <form id="checkoutForm">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informações de Entrega</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="customer_cep" class="form-label">CEP</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="customer_cep" name="customer_cep" required>
                                    <button class="btn btn-outline-secondary" type="button" id="searchCep">Buscar</button>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="customer_address" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="customer_address" name="customer_address" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="customer_number" class="form-label">Número</label>
                                <input type="text" class="form-control" id="customer_number" name="customer_number" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="customer_complement" class="form-label">Complemento</label>
                                <input type="text" class="form-control" id="customer_complement" name="customer_complement">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="customer_neighborhood" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="customer_neighborhood" name="customer_neighborhood" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="customer_city" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="customer_city" name="customer_city" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="customer_state" class="form-label">UF</label>
                                <input type="text" class="form-control" id="customer_state" name="customer_state" required>
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
                    <div class="card-body" id="checkoutSummary">
                        <!-- Resumo será carregado aqui -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Finalizar Pedido</button>
                    <a href="/cart" class="btn btn-outline-secondary">Voltar ao Carrinho</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(function() {
    // Carrega o resumo do pedido
    function loadCheckoutSummary() {
        apiClient.get('/cart')
            .then(function(response) {
                const cart = response.data || response.cart || {};
                if (response.success) {
                    renderCheckoutSummary(cart);
                } else {
                    showToast('error', response.error || 'Falha ao carregar carrinho');
                }
            })
            .catch(function() {
                showToast('error', 'Falha ao carregar carrinho');
            });
    }

    function renderCheckoutSummary(cart) {
        let html = `
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span>R$ ${(parseFloat(cart.subtotal) || 0).toFixed(2)}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Frete:</span>
                <span>R$ ${(parseFloat(cart.shipping) || 0).toFixed(2)}</span>
            </div>
        `;
        const discount = parseFloat(cart.discount) || 0;
        if (discount > 0) {
            html += `
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Desconto:</span>
                    <span>- R$ ${discount.toFixed(2)}</span>
                </div>
            `;
        }
        html += `
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5">
                <span>Total:</span>
                <span>R$ ${(parseFloat(cart.total) || 0).toFixed(2)}</span>
            </div>
        `;
        $('#checkoutSummary').html(html);
    }

    // Busca CEP via ViaCEP
    $('#searchCep').on('click', function() {
        const cep = $('#customer_cep').val().replace(/\D/g, '');
        if (cep.length !== 8) {
            showToast('warning', 'CEP deve conter 8 dígitos');
            return;
        }
        $.getJSON(`https://viacep.com.br/ws/${cep}/json/`)
            .done(function(data) {
                if (data.erro) {
                    showToast('error', 'CEP não encontrado');
                    return;
                }
                $('#customer_address').val(data.logradouro || '');
                $('#customer_neighborhood').val(data.bairro || '');
                $('#customer_city').val(data.localidade || '');
                $('#customer_state').val(data.uf || '');
                $('#customer_number').focus();
            })
            .fail(function() {
                showToast('error', 'CEP não encontrado ou serviço indisponível');
            });
    });

    // Envio do formulário de checkout
    $('#checkoutForm').on('submit', function(e) {
        e.preventDefault();
        const formData = {
            customer_name: $('#customer_name').val(),
            customer_email: $('#customer_email').val(),
            customer_cep: $('#customer_cep').val(),
            customer_address: `${$('#customer_address').val()}, ${$('#customer_number').val()}`,
            customer_complement: $('#customer_complement').val(),
            customer_neighborhood: $('#customer_neighborhood').val(),
            customer_city: $('#customer_city').val(),
            customer_state: $('#customer_state').val()
        };
        apiClient.post('/checkout', formData)
            .then(function(response) {
                if (response.success) {
                    showToast('success', `Pedido realizado! Seu pedido #${response.order_id} foi criado com sucesso.`);
                    setTimeout(() => window.location.href = '/orders', 2000);
                } else {
                    showToast('error', response.error || 'Falha ao finalizar pedido');
                }
            })
            .catch(function() {
                showToast('error', 'Falha na comunicação com o servidor');
            });
    });

    // Inicialização
    loadCheckoutSummary();
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
