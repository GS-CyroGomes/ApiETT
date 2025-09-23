#!/bin/bash

RESTORE='\033[0m'
RED='\033[00;31m'
GREEN='\033[00;32m'
YELLOW='\e[0;33m'
BLUE='\033[00;34m'

export PROJ_BASE=$(pwd)
export ROOTDIR=$PROJ_BASE

function showHelp() {
    construirAmbiente
    echo -e "${BLUE}showHelp${RESTORE}          ${YELLOW}Esta função exibe uma lista de comandos disponíveis no script.${RESTORE}"
    echo -e "${GREEN}initEnviroment${RESTORE}   ${YELLOW}Inicializa o ambiente, configura e verifica status e permissões.${RESTORE}"
    echo -e "${GREEN}gitChekcout${RESTORE}      ${YELLOW}Verifica e clona repositórios Git.${RESTORE}"
    echo -e "${GREEN}commitChanges${RESTORE}    ${YELLOW}Comita alterações não salvas.${RESTORE}"
    echo -e "${GREEN}bashContainer${RESTORE}    ${YELLOW}Acessa um container em execução via bash.${RESTORE}"
    echo -e "${GREEN}logsContainer${RESTORE}    ${YELLOW}Exibe logs de um container em execução.${RESTORE}"
    echo -e "${GREEN}attachContainer${RESTORE}  ${YELLOW}Anexa ao terminal de um container em execução.${RESTORE}"
    
}

function initEnviroment() {
    echo -e "${YELLOW}Inicializando o ambiente${RESTORE}"
    configurarPermissoes
    compactarScriptsSql
    checkGitCli
    dkCheckStatus
    showHelp
}

function configurarPermissoes() {
    echo -e "${YELLOW}Configurando permissões de acesso${RESTORE}"
    cd $ROOTDIR
    sudo chmod -R 777 $ROOTDIR
    sudo chown -R $USER:$USER $ROOTDIR
    sudo chgrp -R $USER $ROOTDIR
    sudo chown -R $USER $ROOTDIR
}

function construirAmbiente() {
    dirFolders=("$ROOTDIR/logs/mysql" "$ROOTDIR/logs/apache2")

    for sub in "${dirFolders[@]}"; do
        if [ ! -d "$dir$sub" ]; then
            mkdir -p $dir$sub
            echo -e "${YELLOW}Diretório criado: $sub${RESTORE}"
        fi
    done
}

function checkGitCli () {
    echo -e "${YELLOW}Verificando Git CLI${RESTORE}"
    if ! command -v git &> /dev/null; then
        echo -e "${YELLOW}Git não encontrado. Instalando${RESTORE}"
        if [ -f /etc/debian_version ]; then
            sudo apt update && sudo apt install -y git

            # Configurações do Git
            echo -e "${YELLOW}Por favor, forneça suas configurações do Git.${RESTORE}"
            
            read -p "Digite seu nome de usuário do Git: " git_username
            git config --global user.name "$git_username"
            
            read -p "Digite seu e-mail do Git: " git_email
            git config --global user.email "$git_email"

            echo -e "${GREEN}Configurações do Git definidas:${RESTORE}"
            echo -e "${GREEN}Nome de usuário: $git_username${RESTORE}"
            echo -e "${GREEN}E-mail: $git_email${RESTORE}"

            # Configuração da chave SSH
            echo -e "${YELLOW}Verificando se uma chave SSH já existe${RESTORE}"
            if [ ! -f "$HOME/.ssh/id_rsa.pub" ]; then
                echo -e "${YELLOW}Nenhuma chave SSH encontrada. Gerando uma nova chave SSH${RESTORE}"
                ssh-keygen -t rsa -b 4096 -C "$git_email" -N "" -f "$HOME/.ssh/id_rsa"
                echo -e "${GREEN}Chave SSH gerada com sucesso.${RESTORE}"
            else
                echo -e "${GREEN}Chave SSH já existe.${RESTORE}"
            fi

            # Exibir a chave pública para o usuário
            echo -e "${YELLOW}Aqui está sua chave pública SSH. Você pode adicioná-la à sua conta do GitHub ou GitLab:${RESTORE}"
            cat "$HOME/.ssh/id_rsa.pub"
            
            echo -e "${YELLOW}Certifique-se de adicionar a chave SSH ao seu serviço de Git (GitHub, GitLab, etc.).${RESTORE}"
        fi
    else
        echo -e "${GREEN}Git encontrado.${RESTORE}"
        gitChekcout
    fi
}

function gitSelectivePull() {
    local branch_base="$1"
    local branch_target="$2"

    if [ -z "$branch_base" ] || [ -z "$branch_target" ]; then
        echo "Uso: git_selective_pull <branch_base> <branch_target>"
        return 1
    fi

    echo "🔍 Verificando diferenças entre '$branch_base' e '$branch_target'..."
    local files
    files=$(git diff --name-only "$branch_base" "$branch_target")

    if [ -z "$files" ]; then
        echo "✅ Nenhuma diferença encontrada entre as branches."
        return 0
    fi

    echo "🗂️ Arquivos diferentes encontrados:"
    local i=1
    declare -A file_map
    while IFS= read -r file; do
        echo "  [$i] $file"
        file_map[$i]="$file"
        ((i++))
    done <<< "$files"

    echo ""
    read -p "Digite os números dos arquivos que deseja importar da '$branch_target' (separados por espaço): " -a choices

    for index in "${choices[@]}"; do
        file="${file_map[$index]}"
        if [ -n "$file" ]; then
            echo "📥 Puxando '$file' de '$branch_target'..."
            git checkout "$branch_target" -- "$file"
        else
            echo "⚠️ Índice inválido: $index"
        fi
    done

    echo "✅ Arquivos selecionados foram puxados com sucesso."
}

function gitChekcout() {
    local currentDir=$(pwd)
    local repos=("gfw" "gadmin" "webcfc" "wms")

    mkdir -p "$ROOTDIR/php56"

    for repo in "${repos[@]}"; do
        echo "Clonando/atualizando repositório: $repo"
        
        gitClone "$repo"

        local repoDir="$ROOTDIR/php56/$repo"
        if [ -d "$repoDir/.git" ]; then
            cd "$repoDir" || return
            branch=$(git remote show origin | grep 'HEAD branch' | awk '{print $NF}')
            echo -e "${YELLOW}Atualizando repositório: $repo na branch $branch${RESTORE}"
            git pull origin "$branch"
            cd "$currentDir"
        else
            echo "Repositório $repo não encontrado. Verifique se foi clonado corretamente."
        fi
    done
}

function gitClone() {
    local respositorio="$1"
    gitCloneSsh $respositorio
    if [ $? -ne 0 ]; then
        gitCloneHttps $repositorio
    fi
}

function gitCloneHttps() {
    local httpsLink="https://github.com/giusoft/"
    local repositorio="$1"

    cd "$ROOTDIR/php56/"
    if [ -d "$repositorio" ] && [ -z "$(ls -A "$repositorio")" ]; then
        echo -e "${YELLOW}Clone by HTTPS: $httpsLink$repositorio.git${RESTORE}"
        git clone "$httpsLink$repositorio.git"
    fi
}

function gitCloneSsh() {
    local sshLink="git@github.com:giusoft/"
    local repositorio="$1"

    cd "$ROOTDIR/php56/"
    if [ -d "$repositorio" ] && [ -z "$(ls -A "$repositorio")" ]; then
        echo -e "${YELLOW}Clone by SSH: $sshLink$repositorio.git${RESTORE}"
        git clone "$sshLink$repositorio.git"
    fi
}

function dkCheckStatus() {
    echo -e "${YELLOW}Verificando status do Docker${RESTORE}"
    dkCheckContainers
}

function dkCheckContainers() {
    local containers=("php56_apache2" "mysql")

    for container in "${containers[@]}"; do
        if ! docker ps --format '{{.Names}}' | grep -qw "$container"; then
            echo -e "${YELLOW}Container não encontrado: $container. Iniciando${RESTORE}"
            docker compose -f "$compose_file" up -d
        else
            echo -e "${GREEN}Container em execução: $container.${RESTORE}"
        fi
    done
}

function dkCheckFile() {
    local dockerfilePath="$1"
    if [ ! -f "$dockerfilePath" ]; then
        echo -e "${RED}Arquivo Dockerfile não encontrado: $dockerfilePath${RESTORE}"
        return 1
    fi
    return 0
}

function dkCheckHash() {
    local dockerfile_path="$1"
    sha256sum "$dockerfile_path" | awk '{print $1}'
}

function dkRemoveLatestImage() {
    local service="$1"
    local hashFile="$2"

    if [ -f "$hashFile" ]; then
        local oldHash
        oldHash=$(cat "$hashFile")
        local oldImage="${service}:${oldHash}"
        if docker image inspect "$oldImage" > /dev/null 2>&1; then
            echo -e "${YELLOW}Removendo imagem antiga: $oldImage${RESTORE}"
            docker rmi "$oldImage"
        fi
    fi
}

function dbBuildImageWithArgs() {
    local service="$1"
    local dockerfile_path="$2"
    local build_dir="$3"
    local current_hash="$4"
    local hash_file="$5"
    shift 5
    local image_tag="${service}:${current_hash}"

    if ! docker image inspect "$image_tag" > /dev/null 2>&1; then
        echo -e "${YELLOW}Construindo imagem: $image_tag${RESTORE}"
        dkRemoveLatestImage "$service" "$hash_file"
        docker build -t "$image_tag" -f "$dockerfile_path" "$@" "$build_dir" || return 1
        echo "$current_hash" > "$hash_file"
    else
        echo -e "${GREEN}Imagem já existe: $image_tag.${RESTORE}"
    fi
}

function removeSqlZipped() {
    local dir="$ROOTDIR/mysql/scripts"
    echo -e "${YELLOW}Removendo arquivos SQL .gz antigos...${RESTORE}"
    find "$dir" -type f -name "*.sql.gz" -exec rm -f {} \;
}

function removeSqlZipped() {
    local dir="$ROOTDIR/mysql/scripts"

    if [ -d "$dir" ]; then
        for arquivo in "$dir"/*.gz; do
            if [ -f "$arquivo" ]; then
                echo -e "${YELLOW}Removendo arquivo SQL compactado: $arquivo${RESTORE}"
                rm "$arquivo"
            fi
        done
    fi
}

function commitChanges() {
    echo -e "${YELLOW}Verificando arquivos não comitados${RESTORE}"
    uncommitted_files=$(git status --porcelain --untracked-files=normal)

    if [ -z "$uncommitted_files" ]; then
        echo "Não há alterações não comitadas."
        return
    fi

    echo "Arquivos disponíveis para commit:"
    counter=1
    file_list=()

    while IFS= read -r line; do
        file_name=$(echo "$line" | awk '{print $2}')

        if git check-ignore -q "$file_name"; then
            continue
        fi

        depth=$(echo "$file_name" | awk -F'/' '{print NF-1}')

        if [ "$depth" -gt 2 ]; then
            continue
        fi

        file_list+=("$file_name")

        indent=""
        if [ "$depth" -ge 1 ]; then
            indent="  "
        fi
        if [ "$depth" -ge 2 ]; then
            indent="    "
        fi
        echo "${indent}${counter}) $file_name"

        ((counter++))
    done <<< "$uncommitted_files"

    if [ ${#file_list[@]} -eq 0 ]; then
        echo "Nenhum arquivo disponível para commit."
        return
    fi

    echo "Selecione os arquivos para commit (ex: 1 2 5-7 10):"
    read -r selected_input

    selected_files_expanded=()
    for token in $selected_input; do
        if [[ "$token" =~ ^[0-9]+-[0-9]+$ ]]; then
            start=$(echo "$token" | cut -d'-' -f1)
            end=$(echo "$token" | cut -d'-' -f2)
            for ((i=start; i<=end; i++)); do
                selected_files_expanded+=("$i")
            done
        elif [[ "$token" =~ ^[0-9]+$ ]]; then
            selected_files_expanded+=("$token")
        fi
    done

    files_to_commit=()
    for file_number in "${selected_files_expanded[@]}"; do
        files_to_commit+=("${file_list[$file_number-1]}")
    done

    echo "Digite a mensagem do commit:"
    read -r commit_message

    git add "${files_to_commit[@]}"
    git commit -m "$commit_message"

    current_branch=$(git branch --show-current)
    git push origin "$current_branch"

    echo "Alterações comitadas e enviadas para a branch '$current_branch'."
}

function selecionarContainer() {
    local containers=($(docker ps --format '{{.Names}}'))
    if [ ${#containers[@]} -eq 0 ]; then
        echo "Nenhum container em execução."
        return 1
    fi

    echo "Containers em execução:"
    local i=1
    for container in "${containers[@]}"; do
        echo "  $i) $container"
        ((i++))
    done

    echo "Selecione o número do container:"
    read -r num

    if [[ "$num" =~ ^[0-9]+$ ]] && [ "$num" -le "${#containers[@]}" ] && [ "$num" -gt 0 ]; then
        echo "${containers[$((num-1))]}"
    else
        echo "Seleção inválida."
        return 1
    fi
}

function bashContainer() {
    local container
    container=$(selecionarContainer) || return 1

    echo "Tentando acessar o container: $container"
    if docker exec -it "$container" bash 2>/dev/null; then
        return
    elif docker exec -it "$container" sh 2>/dev/null; then
        return
    else
        echo "Não foi possível acessar o container com bash ou sh."
    fi
}

function logsContainer() {
    local container
    container=$(selecionarContainer) || return 1

    echo "Mostrando logs do container: $container"
    docker logs -f "$container"
}

function attachContainer() {
    local container
    container=$(selecionarContainer) || return 1

    echo "Anexando ao container: $container"
    echo "Use Ctrl-P + Ctrl-Q para sair sem parar o container."
    docker attach "$container"
}

showHelp