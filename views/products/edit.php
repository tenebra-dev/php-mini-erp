<?php 
require __DIR__ . '/../layout/header.php';

$productId = isset($params['id']) ? $params['id'] : null;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-edit me-2"></i>Editar Produto</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/products/list" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form id="product-form" enctype="multipart/form-data">
            <input type="hidden" id="product-id" value="<?= $productId ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Produto *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Preço *</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sku" class="form-label">SKU (Código do Produto)</label>
                            <input type="text" class="form-control" id="sku" name="sku">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Imagem do Produto</label>
                        <div class="border p-3 text-center mb-2" style="height: 200px; background-color: #f8f9fa;" id="image-preview">
                            <i class="fas fa-image fa-4x text-muted mt-4"></i>
                            <p class="text-muted mt-2">Nenhuma imagem selecionada</p>
                        </div>
                        <input class="form-control" type="file" id="image" name="image" accept="image/*">
                        <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                    </div>
                </div>
            </div>
            
            <!-- Seção de Variações -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Variações do Produto
                    </h5>
                </div>
                <div class="card-body">
                    <div id="variations-container">
                        <!-- Variações serão carregadas aqui -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="mt-2">Carregando variações...</p>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="add-variation">
                        <i class="fas fa-plus me-1"></i> Adicionar Variação
                    </button>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                <button type="reset" class="btn btn-secondary me-md-2">
                    <i class="fas fa-undo me-1"></i> Limpar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Template para nova variação (hidden) -->
<div id="variation-template" class="d-none">
    <div class="variation-item card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Nome da Variação *</label>
                        <input type="text" class="form-control variation-name" name="variations[][name]" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Valor da Variação *</label>
                        <input type="text" class="form-control variation-value" name="variations[][value]" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Estoque *</label>
                        <input type="number" class="form-control variation-quantity" name="variations[][quantity]" min="0" value="0" required>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-variation">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Preview da imagem
    $('#image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').html(`<img src="${e.target.result}" class="img-fluid" style="max-height: 180px;">`);
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Contador para variações
    let variationCount = 0;
    
    // Carrega os dados do produto
    const productId = $('#product-id').val();
    
    $.get(`/api/products/${productId}`, function(product) {
        $('#name').val(product.name);
        $('#description').val(product.description || '');
        $('#price').val(product.price);
        $('#sku').val(product.sku || '');
        
        if(product.image) {
            $('#image-preview').html(`<img src="${product.image}" class="img-fluid" style="max-height: 180px;">`);
        }
        
        // Carrega as variações
        return $.get(`/api/products/${productId}/variations`);
    }).then(function(variations) {
        const container = $('#variations-container');
        container.empty();
        
        if(variations.length > 0) {
            variations.forEach((variation, index) => {
                const template = $('#variation-template').html();
                const newVariation = $(template.replace(/variations\[\]/g, `variations[${index}]`));
                
                newVariation.find('.variation-name').val(variation.variation_name);
                newVariation.find('.variation-value').val(variation.variation_value);
                newVariation.find('.variation-quantity').val(variation.quantity);
                
                container.append(newVariation);
                variationCount++;
            });
        } else {
            container.html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Adicione variações como tamanho, cor, etc. Se não precisar, deixe em branco.
                </div>
            `);
        }
    }).fail(function() {
        showToast('error', 'Erro ao carregar produto');
        setTimeout(() => window.location.href = '/products/list', 1500);
    });
    
    // Adiciona nova variação
    $('#add-variation').click(function() {
        const template = $('#variation-template').html();
        const newVariation = $(template.replace(/variations\[\]/g, `variations[${variationCount}]`));
        
        $('#variations-container .alert').remove();
        $('#variations-container').append(newVariation);
        variationCount++;
    });
    
    // Remove variação
    $(document).on('click', '.remove-variation', function() {
        $(this).closest('.variation-item').remove();
        if($('#variations-container .variation-item').length === 0) {
            $('#variations-container').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Adicione variações como tamanho, cor, etc. Se não precisar, deixe em branco.
                </div>
            `);
        }
    });
    
    // Validação e envio do formulário
    $('#product-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: `/api/products/${productId}`,
            type: 'PUT',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showToast('success', 'Produto atualizado com sucesso!');
                setTimeout(() => {
                    window.location.href = `/products/view/${productId}`;
                }, 1500);
            },
            error: function(xhr) {
                let errorMessage = 'Erro ao atualizar produto';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast('error', errorMessage);
            }
        });
    });
    
    // Função para exibir notificações
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
