FROM php:8.4-cli

# Instalar dependências essenciais e extensões do PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar arquivos do projeto e instalar dependências no build
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Copiar o restante do projeto
COPY . .

# Rodar scripts do composer que dependem do artisan
RUN composer dump-autoload --optimize

# Expor porta do artisan serve
EXPOSE 8000

# Configurações PHP para produção
RUN echo "max_execution_time=600" > /usr/local/etc/php/conf.d/custom.ini && \
    echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/custom.ini

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
