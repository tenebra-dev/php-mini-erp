<?php 
$pageTitle = "Lista de Pedidos";
$activePage = "orders";
require __DIR__ . '/../layout/header.php';
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-shopping-cart me-2"></i>Lista de Pedidos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <!-- Adicione botões de ação se necessário -->
    </div>
</div>

<!-- Filtros e Busca -->
<div class="card mb-4">
    <div class="card-body">
        <form id="search-form">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" class="form-control" placeholder="Buscar por cliente..." name="search">
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select" name="status">
                        <option value="">Todos status</option>
                        <option value="pending">Pendente</option>
                        <option value="paid">Pago</option>
                        <option value="shipped">Enviado</option>
                        <option value="delivered">Entregue</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="date" class="form-control" name="date">
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

<!-- Tabela de Pedidos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center">Carregando pedidos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </nav>
    </div>
</div>

<script>
$(document).ready(function() {
    function loadOrders(page = 1) {
        const formData = $('#search-form').serialize() + '&page=' + page;
        $.ajax({
            url: '/orders',
            type: 'GET',
            data: formData,
            success: function(response) {
                let html = '';
                if(response.data.length > 0) {
                    response.data.forEach(order => {
                        html += `
                        <tr>
                            <td>${order.id}</td>
                            <td>${order.customer}</td>
                            <td>${order.date}</td>
                            <td>${order.status}</td>
                            <td>R$ ${parseFloat(order.total).toFixed(2)}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/orders/view/${order.id}" class="btn btn-outline-info" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="6" class="text-center">Nenhum pedido encontrado</td></tr>';
                }
                $('#orders-table tbody').html(html);

                // Paginação (igual ao padrão de products)
                if(response.pagination) {
                    let paginationHtml = '';
                    const pagination = response.pagination;
                    if(pagination.current_page > 1) {
                        paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${pagination.current_page - 1}">&laquo; Anterior</a>
                        </li>`;
                    }
                    for(let i = 1; i <= pagination.last_page; i++) {
                        paginationHtml += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`;
                    }
                    if(pagination.current_page < pagination.last_page) {
                        paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Próxima &raquo;</a>
                        </li>`;
                    }
                    $('#pagination').html(paginationHtml);
                }
            }
        });
    }

    loadOrders();

    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        loadOrders();
    });

    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadOrders(page);
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>