<?php
return [
    // Rotas da API (RESTful) - prefixo /api/ será adicionado automaticamente
    'api' => [
        'products' => [
            'methods' => ['GET', 'POST'],
            'controller' => 'controllers\\ProductController',
            'method' => 'handleProducts'
        ],
        'products/:id' => [
            'methods' => ['GET', 'PUT', 'DELETE'],
            'controller' => 'controllers\\ProductController',
            'method' => 'handleProduct'
        ],
        'cart' => [
            'methods' => ['GET', 'POST', 'DELETE'],
            'controller' => 'controllers\\OrderController',
            'method' => 'handleCart'
        ],
        'cart/add' => [
            'methods' => ['POST'],
            'controller' => 'controllers\\OrderController',
            'method' => 'addToCart'
        ],
        'cart/remove/:id' => [
            'methods' => ['DELETE'],
            'controller' => 'controllers\\OrderController',
            'method' => 'removeFromCart'
        ],
        'cart/update' => [
            'methods' => ['PUT'],
            'controller' => 'controllers\\OrderController',
            'method' => 'updateCart'
        ],
        'checkout' => [
            'methods' => ['POST'],
            'controller' => 'controllers\\OrderController',
            'method' => 'handleCheckout'
        ],
        'orders' => [
            'methods' => ['GET'],
            'controller' => 'controllers\\OrderController',
            'method' => 'handleOrders'
        ],
        'orders/:id' => [
            'methods' => ['GET', 'PUT'],
            'controller' => 'controllers\\OrderController',
            'method' => 'handleOrder'
        ],
        'coupons' => [
            'methods' => ['GET', 'POST'],
            'controller' => 'controllers\\CouponController',
            'method' => 'handleCoupons'
        ],
        'coupons/:code' => [
            'methods' => ['GET', 'DELETE'],
            'controller' => 'controllers\\CouponController',
            'method' => 'handleCoupon'
        ],
        'coupons/validate/:code' => [
            'methods' => ['POST'],
            'controller' => 'controllers\\CouponController',
            'method' => 'validateCoupon'
        ],
        'webhook/order' => [
            'methods' => ['POST'],
            'controller' => 'controllers\\OrderController',
            'method' => 'handleWebhook'
        ],
        'cep/:cep' => [
            'methods' => ['GET'],
            'controller' => 'controllers\\AddressController',
            'method' => 'getCep'
        ]
    ],
    
    // Rotas do Frontend (páginas HTML)
    'frontend' => [
        '/' => [
            'title' => 'Dashboard',
            'view' => 'home.php',
            'active' => 'home'
        ],
        '/products/list' => [
            'title' => 'Lista de Produtos',
            'view' => 'products/list.php',
            'active' => 'products',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Produtos', 'url' => '']
            ]
        ],
        '/products/create' => [
            'title' => 'Criar Produto',
            'view' => 'products/create.php',
            'active' => 'products',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Produtos', 'url' => '/products/list'],
                ['title' => 'Novo Produto', 'url' => '']
            ]
        ],
        '/products/edit/:id' => [
            'title' => 'Editar Produto',
            'view' => 'products/edit.php',
            'active' => 'products',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Produtos', 'url' => '/products/list'],
                ['title' => 'Editar Produto', 'url' => '']
            ]
        ],
        '/products/view/:id' => [
            'title' => 'Visualizar Produto',
            'view' => 'products/view.php',
            'active' => 'products',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Produtos', 'url' => '/products/list'],
                ['title' => 'Detalhes do Produto', 'url' => '']
            ]
        ],
        '/orders/cart' => [
            'title' => 'Carrinho de Compras',
            'view' => 'orders/cart.php',
            'active' => 'cart',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Carrinho', 'url' => '']
            ]
        ],
        '/orders/checkout' => [
            'title' => 'Finalizar Compra',
            'view' => 'orders/checkout.php',
            'active' => 'orders',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Carrinho', 'url' => '/orders/cart'],
                ['title' => 'Finalizar Compra', 'url' => '']
            ]
        ],
        '/orders/list' => [
            'title' => 'Lista de Pedidos',
            'view' => 'orders/list.php',
            'active' => 'orders',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Pedidos', 'url' => '']
            ]
        ],
        '/orders/view/:id' => [
            'title' => 'Detalhes do Pedido',
            'view' => 'orders/view.php',
            'active' => 'orders',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Pedidos', 'url' => '/orders/list'],
                ['title' => 'Detalhes do Pedido', 'url' => '']
            ]
        ],
        '/coupons/list' => [
            'title' => 'Lista de Cupons',
            'view' => 'coupons/list.php',
            'active' => 'coupons',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Cupons', 'url' => '']
            ]
        ],
        '/coupons/create' => [
            'title' => 'Criar Cupom',
            'view' => 'coupons/create.php',
            'active' => 'coupons',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/'],
                ['title' => 'Cupons', 'url' => '/coupons/list'],
                ['title' => 'Novo Cupom', 'url' => '']
            ]
        ]
    ]
];
