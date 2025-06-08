<?php 
require __DIR__ . '/../layout/header.php';
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-boxes me-2"></i>Lista de Produtos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/products/create" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus me-1"></i> Novo Produto
        </a>
    </div>
</div>

<!-- Filtros e Busca -->
<div class="card mb-4">
    <div class="card-body">
        <form id="search-form">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" class="form-control" placeholder="Buscar por nome..." name="search">
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select" name="category">
                        <option value="">Todas categorias</option>
                        <option value="eletronicos">Eletrônicos</option>
                        <option value="vestuario">Vestuário</option>
                        <option value="alimentos">Alimentos</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select" name="has_stock">
                        <option value="">Todos</option>
                        <option value="1">Com estoque</option>
                        <option value="0">Sem estoque</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Produtos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Estoque Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dados serão carregados via AJAX -->
                    <tr>
                        <td colspan="6" class="text-center">Carregando produtos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center" id="pagination">
                <!-- Paginação será carregada via AJAX -->
            </ul>
        </nav>
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
    // Carrega os produtos via AJAX
    function loadProducts(page = 1) {
        const formData = $('#search-form').serialize() + '&page=' + page;
        
        $.ajax({
            url: '/products',
            type: 'GET',
            data: formData,
            success: function(response) {
                // Preenche a tabela
                let html = '';
                if(response.data.length > 0) {
                    response.data.forEach(product => {
                        html += `
                        <tr>
                            <td>${product.id}</td>
                            <td>
                                <img src="${product.image || '/assets/no-image.png'}" 
                                     alt="${product.name}" 
                                     class="img-thumbnail" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>${product.name}</td>
                            <td>R$ ${parseFloat(product.price).toFixed(2)}</td>
                            <td>${product.total_stock || 0}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/products/view/${product.id}" class="btn btn-outline-info" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/products/edit/${product.id}" class="btn btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-danger delete-btn" 
                                            title="Excluir" 
                                            data-id="${product.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="6" class="text-center">Nenhum produto encontrado</td></tr>';
                }
                $('#products-table tbody').html(html);
                
                // Configura a paginação
                if(response.pagination) {
                    let paginationHtml = '';
                    const pagination = response.pagination;
                    
                    if(pagination.current_page > 1) {
                        paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${pagination.current_page - 1}">
                                &laquo; Anterior
                            </a>
                        </li>
                        `;
                    }
                    
                    for(let i = 1; i <= pagination.last_page; i++) {
                        paginationHtml += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                        `;
                    }
                    
                    if(pagination.current_page < pagination.last_page) {
                        paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${pagination.current_page + 1}">
                                Próxima &raquo;
                            </a>
                        </li>
                        `;
                    }
                    
                    $('#pagination').html(paginationHtml);
                }
            }
        });
    }
    
    // Carrega os produtos inicialmente
    loadProducts();
    
    // Filtra produtos ao enviar o formulário
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        loadProducts();
    });
    
    // Paginação
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadProducts(page);
    });
    
    // Configura o modal de exclusão
    let productIdToDelete;
    $(document).on('click', '.delete-btn', function() {
        productIdToDelete = $(this).data('id');
        $('#confirmModal').modal('show');
    });
    
    $('#confirm-delete').on('click', function() {
        if(productIdToDelete) {
            $.ajax({
                url: `/api/products/${productIdToDelete}`,
                type: 'DELETE',
                success: function() {
                    $('#confirmModal').modal('hide');
                    loadProducts();
                    showToast('success', 'Produto excluído com sucesso!');
                },
                error: function() {
                    showToast('error', 'Erro ao excluir produto');
                }
            });
        }
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
