<?php
$pageTitle = "Lista de Cupons";
$activePage = "coupons";
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-tags me-2"></i>Lista de Cupons</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/coupons/create" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-plus me-1"></i> Novo Cupom
            </a>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="search-form">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <input type="text" class="form-control" placeholder="Buscar por código..." name="search">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-select" name="status">
                            <option value="">Todos status</option>
                            <option value="valid">Válido</option>
                            <option value="expired">Expirado</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Cupons -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="coupons-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Mínimo</th>
                            <th>Validade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center">Carregando cupons...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function loadCoupons() {
        $.get('/coupons', $('#search-form').serialize(), function(response) {
            let html = '';
            if(response.data && response.data.length > 0) {
                response.data.forEach(coupon => {
                    html += `
                        <tr>
                            <td>${coupon.code}</td>
                            <td>${coupon.discount_type === 'percent' ? 'Percentual' : 'Fixo'}</td>
                            <td>${coupon.discount_type === 'percent' ? coupon.discount_value + '%' : 'R$ ' + parseFloat(coupon.discount_value).toFixed(2)}</td>
                            <td>R$ ${parseFloat(coupon.min_value).toFixed(2)}</td>
                            <td>${coupon.valid_until ? new Date(coupon.valid_until).toLocaleDateString('pt-BR') : '-'}</td>
                            <td>
                                ${coupon.is_valid ? '<span class="badge bg-success">Válido</span>' : '<span class="badge bg-secondary">Expirado</span>'}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/coupons/view/${coupon.code}" class="btn btn-outline-info" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/coupons/edit/${coupon.code}" class="btn btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-danger delete-btn" data-code="${coupon.code}" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">Nenhum cupom encontrado</td></tr>';
            }
            $('#coupons-table tbody').html(html);
        });
    }

    loadCoupons();

    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        loadCoupons();
    });

    // Excluir cupom
    $(document).on('click', '.delete-btn', function() {
        const code = $(this).data('code');
        if(confirm('Tem certeza que deseja excluir este cupom?')) {
            $.ajax({
                url: `/coupons/${code}`,
                type: 'DELETE',
                success: function() {
                    loadCoupons();
                    showToast('success', 'Cupom excluído com sucesso!');
                },
                error: function() {
                    showToast('error', 'Erro ao excluir cupom');
                }
            });
        }
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>