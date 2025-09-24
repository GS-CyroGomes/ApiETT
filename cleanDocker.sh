#!/bin/bash

# Script para limpar completamente o Docker
# ATENÇÃO: Isto irá remover todos containers, imagens, volumes e redes Docker

echo "=============================="
echo "LIMPEZA COMPLETA DO DOCKER"
echo "=============================="

read -p "Tem certeza que deseja remover TODOS os containers, imagens, volumes e redes? [s/N]: " confirm

if [[ "$confirm" != "s" && "$confirm" != "S" ]]; then
    echo "Operação cancelada."
    exit 0
fi

echo "Parando todos os containers..."
docker ps -q | xargs -r docker stop

echo "Removendo todos os containers..."
docker ps -aq | xargs -r docker rm -f

echo "Removendo todas as imagens..."
docker images -aq | xargs -r docker rmi -f

echo "Removendo todos os volumes..."
docker volume ls -q | xargs -r docker volume rm -f

echo "Removendo todas as redes não utilizadas..."
docker network prune -f

echo "Limpeza completa do Docker concluída!"
