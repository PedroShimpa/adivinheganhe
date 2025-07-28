#!/bin/bash

# Atualiza o repositório git
echo "Fazendo git pull..."
git pull

# Executa as migrações do Laravel
echo "Executando migrações..."
php artisan migrate

# Instala dependências PHP com o Composer
echo "Instalando dependências com Composer..."
composer install --no-interaction --prefer-dist

# Limpa o cache da aplicação
echo "Limpando cache da aplicação..."
php artisan cache:clear

# Recria o cache das rotas
echo "Gerando cache de rotas..."
php artisan route:cache

# Reinicia o serviço do Laravel Octane
echo "Reiniciando o Octane..."
sudo systemctl restart octane

echo "✔️ Script concluído com sucesso."
