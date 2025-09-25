FROM amd64/php:5.6-apache-stretch

LABEL maintainer="CyroGomes"

ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Corrige os repositórios para o archive
RUN sed -i -e 's/deb.debian.org/archive.debian.org/g' \
           -e 's|security.debian.org|archive.debian.org/|g' \
           -e '/stretch-updates/d' /etc/apt/sources.list

# Instala dependências (forçando instalação sem autenticação)
RUN apt-get update && apt-get install -y --no-install-recommends \
    vim \
    curl \
    unzip \
    libmemcached-dev \
    libxml2-dev \
    libpq-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libssl-dev \
    libmcrypt-dev \
    mysql-client \
    iputils-ping \
    net-tools \
    --allow-unauthenticated \
    && rm -rf /var/lib/apt/lists/*

# Extensões PHP
RUN docker-php-ext-install -j$(nproc) gd \
    pdo_mysql \
    mysql \
    mysqli \
    opcache \
    iconv \
    mbstring \
    mcrypt \
    soap \
    sockets \
    zip

# Habilita o mod_rewrite
RUN a2enmod rewrite

# Xdebug
RUN pecl install xdebug-2.5.5 && docker-php-ext-enable xdebug

# Cria o diretório para as sessões do PHP e define as permissões
RUN mkdir -p /tmp/php-sessions && \
    chown -R www-data:www-data /tmp/php-sessions

# Cria o arquivo de log do PHP e define as permissões
RUN touch /var/log/php_errors.log && \
    chown www-data:www-data /var/log/php_errors.log

# Copia os arquivos de configuração
COPY ./config/xdebug.ini /usr/local/etc/php/conf.d/
COPY ./config/php.ini /usr/local/etc/php/php.ini
COPY ./config/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./config/apache2.conf /etc/apache2/apache2.conf
COPY ./config/apiTemoraETT.conf /etc/apache2/sites-available/apiTemoraETT.conf

# Copia a aplicação
COPY ./src/apiTemoraETT /var/www/html/

# Instala Composer global
RUN curl -sS https://getcomposer.org/installer | php -- --2.2 --install-dir=/usr/bin --filename=composer

# Ajusta permissões, se necessário
RUN chown -R www-data:www-data /var/www/html

# Instala dependências do projeto
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copia as dependências do projeto
COPY ./src/apiTemoraETT/vendor /var/www/html/vendor

# Ajusta o arquivo de configuração do Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
