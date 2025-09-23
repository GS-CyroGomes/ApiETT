FROM amd64/mysql:8.0.36-debian

LABEL maintainer="CyroGomes"

# Instalação de pacotes úteis
RUN apt-get update && apt-get install -y --no-install-recommends \
    vim \
    mysql-client \
    --allow-unauthenticated \
    && rm -rf /var/lib/apt/lists/*

# Copia o arquivo de configuração
COPY ./config/my.cnf /etc/

# Copia os scripts de restore dos bancos
COPY ./scripts/*.sql /docker-entrypoint-initdb.d/

EXPOSE 3306
