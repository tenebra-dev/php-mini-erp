<?php 
$pageTitle = "Finalizar Compra";
$activePage = "orders";
include 'layout/header.php';
include 'layout/navigation.php';
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
    // Carrega o resumo do pedido
    document.addEventListener('DOMContentLoaded', async () => {
        const response = await makeRequest('/api/cart');
        
        if (response.success) {
            renderCheckoutSummary(response.cart);
        } else {
            showAlert('error', 'Erro', response.error || 'Falha ao carregar carrinho');
        }
    });
    
    function renderCheckoutSummary(cart) {
        const summaryContainer = document.getElementById('checkoutSummary');
        
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
        
        summaryContainer.innerHTML = html;
    }
    
    // Busca CEP
    document.getElementById('searchCep').addEventListener('click', async () => {
        const cep = document.getElementById('customer_cep').value.replace(/\D/g, '');
        
        if (cep.length !== 8) {
            showAlert('warning', 'Atenção', 'CEP deve conter 8 dígitos');
            return;
        }
        
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (data.erro) {
                throw new Error('CEP não encontrado');
            }
            
            // Preenche os campos com os dados do CEP
            document.getElementById('customer_address').value = data.logradouro || '';
            document.getElementById('customer_neighborhood').value = data.bairro || '';
            document.getElementById('customer_city').value = data.localidade || '';
            document.getElementById('customer_state').value = data.uf || '';
            
            // Foca no campo número
            document.getElementById('customer_number').focus();
            
        } catch (error) {
            showAlert('error', 'Erro', 'CEP não encontrado ou serviço indisponível');
        }
    });
    
    // Envio do formulário de checkout
    document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = {
            customer_name: document.getElementById('customer_name').value,
            customer_email: document.getElementById('customer_email').value,
            customer_cep: document.getElementById('customer_cep').value,
            customer_address: `${document.getElementById('customer_address').value}, ${document.getElementById('customer_number').value}`,
            customer_complement: document.getElementById('customer_complement').value,
            customer_neighborhood: document.getElementById('customer_neighborhood').value,
            customer_city: document.getElementById('customer_city').value,
            customer_state: document.getElementById('customer_state').value
        };
        
        try {
            const response = await makeRequest('/api/checkout', 'POST', formData);
            
            if (response.success) {
                showAlert('success', 'Pedido realizado!', `Seu pedido #${response.order_id} foi criado com sucesso.`);
                setTimeout(() => window.location.href = '/orders', 2000);
            } else {
                showAlert('error', 'Erro', response.error || 'Falha ao finalizar pedido');
            }
        } catch (error) {
            showAlert('error', 'Erro', 'Falha na comunicação com o servidor');
        }
    });
</script>

<?php include 'layout/footer.php'; ?>
