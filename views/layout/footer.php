</main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-5 bg-light border-top" style="margin-left: var(--sidebar-width);">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <p class="mb-1 text-muted">&copy; <?= date('Y') ?> <b>Mini ERP</b>. Todos os direitos reservados.</p>
                    <p class="mb-0 small text-muted">
                        Desenvolvido com <i class="text-danger fas fa-heart"></i> para gestão empresarial
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (opcional, caso precise) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Função para exibir notificações toast
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            
            const toastElement = document.createElement('div');
            toastElement.className = `toast align-items-center text-bg-${type} border-0`;
            toastElement.setAttribute('role', 'alert');
            toastElement.setAttribute('aria-live', 'assertive');
            toastElement.setAttribute('aria-atomic', 'true');
            
            toastElement.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toastElement);
            
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Remove o elemento após ser ocultado
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }
        
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
            return container;
        }
        
        // Função para confirmar ações perigosas
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
        
        // Função para formatar valores monetários
        function formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        }
        
        // Função para validar CEP
        function validateCEP(cep) {
            const cleanCEP = cep.replace(/\D/g, '');
            return cleanCEP.length === 8;
        }
        
        // Função para buscar CEP
        async function fetchCEP(cep) {
            const cleanCEP = cep.replace(/\D/g, '');
            
            if (!validateCEP(cleanCEP)) {
                throw new Error('CEP inválido');
            }
            
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cleanCEP}/json/`);
                const data = await response.json();
                
                if (data.erro) {
                    throw new Error('CEP não encontrado');
                }
                
                return data;
            } catch (error) {
                throw new Error('Erro ao buscar CEP: ' + error.message);
            }
        }
        
        // Máscaras para inputs
        function applyCEPMask(input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            });
        }
        
        function applyPhoneMask(input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            });
        }
        
        function applyCurrencyMask(input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = (value / 100).toFixed(2);
                value = value.replace('.', ',');
                value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
                e.target.value = 'R$ ' + value;
            });
        }
        
        // Aplicar máscaras automaticamente
        document.addEventListener('DOMContentLoaded', function() {
            // CEP
            document.querySelectorAll('input[data-mask="cep"]').forEach(input => {
                applyCEPMask(input);
            });
            
            // Telefone
            document.querySelectorAll('input[data-mask="phone"]').forEach(input => {
                applyPhoneMask(input);
            });
            
            // Moeda
            document.querySelectorAll('input[data-mask="currency"]').forEach(input => {
                applyCurrencyMask(input);
            });
            
            // Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
        
        // Função para responsividade do sidebar
        function handleResize() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        }
        
        window.addEventListener('resize', handleResize);
        
        // Função para destacar o item ativo do menu
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
    
    <!-- Scripts específicos da página -->
    <?php if (isset($pageScripts) && !empty($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Scripts -->
    <?php if (isset($inlineScripts) && !empty($inlineScripts)): ?>
        <script>
            <?= $inlineScripts ?>
        </script>
    <?php endif; ?>
    
    <!-- CSS customizado da página -->
    <?php if (isset($pageStyles) && !empty($pageStyles)): ?>
        <style>
            <?= $pageStyles ?>
        </style>
    <?php endif; ?>

    <style>
        @media (max-width: 768px) {
            footer {
                margin-left: 0 !important;
            }
        }
    </style>
</body>
</html>
