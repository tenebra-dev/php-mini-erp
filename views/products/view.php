<?php 
require __DIR__ . '/../layout/header.php';

$productId = isset($params['id']) ? $params['id'] : null;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-eye me-2"></i>Detalhes do Produto</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/products/list" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        <a href="/products/edit/<?= $productId ?>" class="btn btn-sm btn-primary me-2">
            <i class="fas fa-edit me-1"></i> Editar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="product-image" src="" class="img-fluid rounded mb-3" style="max-height: 200px;">
                    </div>
                    <div class="col-md-8">
                        <h3 id="product-name" class="card-title"></h3>
                        <div class="d-flex align-items-center mb-2">
                            <h4 class="mb-0 text-primary" id="product-price"></h4>
                        </div>
                        <p class="card-text" id="product-description"></p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>SKU:</strong> <span id="product-sku"></span></p>
                                <p><strong>Criado em:</strong> <span id="created-at"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Atualizado em:</strong> <span id="updated-at"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Seção de Variações -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Variações e Estoque
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="variations-table">
                        <thead>
                            <tr>
                                <th>Variação</th>
                                <th>Valor</th>
                                <th>Estoque</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Variações serão carregadas aqui -->
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p class="mt-2">Carregando variações...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/products/edit/<?= $productId ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Editar Produto
                    </a>
                    <button class="btn btn-outline-danger" id="delete-btn">
                        <i class="fas fa-trash me-1"></i> Excluir Produto
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Estatísticas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="mb-1"><strong>Total em Estoque:</strong></p>
                    <p id="total-stock">0</p>
                </div>
                <div>
                    <p class="mb-1"><strong>Vendas Totais:</strong></p>
                    <p id="total-sales">0</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este produto?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Carrega os dados do produto
    const productId = <?= $productId ?>;
    let totalStock = 0;
    
    $.get(`/api/products/${productId}`, function(product) {
        // Preenche os dados do produto
        $('#product-name').text(product.name);
        $('#product-description').text(product.description || 'Nenhuma descrição fornecida');
        $('#product-price').text('R$ ' + parseFloat(product.price).toFixed(2));
        $('#product-sku').text(product.sku || 'N/A');
        $('#created-at').text(formatDate(product.created_at));
        $('#updated-at').text(formatDate(product.updated_at));
        
        // Imagem
        if(product.image) {
            $('#product-image').attr('src', product.image);
        } else {
            $('#product-image').attr('src', '/assets/img/no-image.png');
        }
        
        // Carrega as variações
        return $.get(`/api/products/${productId}/variations`);
    }).then(function(variations) {
        const tableBody = $('#variations-table tbody');
        tableBody.empty();
        
        if(variations.length > 0) {
            variations.forEach(variation => {
                totalStock += parseInt(variation.quantity || 0);
                
                tableBody.append(`
                    <tr>
                        <td>${variation.variation_name}</td>
                        <td>${variation.variation_value}</td>
                        <td>${variation.quantity}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary update-stock" 
                                    data-id="${variation.id}" 
                                    data-quantity="${variation.quantity}">
                                <i class="fas fa-edit"></i> Atualizar
                            </button>
                        </td>
                    </tr>
                `);
            });
            
            $('#total-stock').text(totalStock);
        } else {
            tableBody.append(`
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        Nenhuma variação cadastrada
                    </td>
                </tr>
            `);
        }
    }).fail(function() {
        showToast('error', 'Erro ao carregar produto');
        setTimeout(() => window.location.href = '/products/list', 1500);
    });
    
    // Configura o botão de exclusão
    $('#delete-btn').on('click', function() {
        $('#confirmModal').modal('show');
    });
    
    $('#confirm-delete').on('click', function() {
        $.ajax({
            url: `/api/products/${productId}`,
            type: 'DELETE',
            success: function() {
                $('#confirmModal').modal('hide');
                showToast('success', 'Produto excluído com sucesso!');
                setTimeout(() => {
                    window.location.href = '/products/list';
                }, 1500);
            },
            error: function() {
                showToast('error', 'Erro ao excluir produto');
            }
        });
    });
    
    // Atualizar estoque (simplificado - você pode implementar um modal para isso)
    $(document).on('click', '.update-stock', function() {
        const variationId = $(this).data('id');
        const currentQuantity = $(this).data('quantity');
        
        const newQuantity = prompt("Digite a nova quantidade em estoque:", currentQuantity);
        
        if(newQuantity !== null && !isNaN(newQuantity)) {
            $.ajax({
                url: `/api/products/variations/${variationId}/stock`,
                type: 'PUT',
                data: { quantity: newQuantity },
                success: function() {
                    showToast('success', 'Estoque atualizado com sucesso!');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function() {
                    showToast('error', 'Erro ao atualizar estoque');
                }
            });
        }
    });
    
    // Funções auxiliares
    function formatDate(dateString) {
        if(!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR');
    }
    
    function showToast(type, message) {
        const toast = `
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
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
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
