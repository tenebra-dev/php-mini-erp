#!/bin/bash

# Espera o banco de dados ficar pronto
wait-for-it db:3306 -t 30

# Instala dependências do composer
if [ -f "/var/www/html/composer.json" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Configura permissões
chown -R www-data:www-data /var/www/html/public
chmod -R 755 /var/www/html/public

# Roda as migrations (se necessário)
if [ -f "/var/www/html/public/migrate.php" ]; then
    php /var/www/html/public/migrate.php
fi

# Inicia o Apache
exec apache2-foreground
