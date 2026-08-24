#!/usr/bin/env bash
set -Eeuo pipefail

release_sha="${1:?Informe o SHA da release.}"
image_tag="${release_sha:0:12}"
release_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
production_root="/opt/paineldecomando"
stack_name="paineldecomando"
deployed=0

rollback() {
  if [[ "$deployed" == "1" ]]; then
    echo "Falha após o deploy; solicitando rollback dos serviços da aplicação."
    docker service rollback "${stack_name}_frontend" || true
    docker service rollback "${stack_name}_api" || true
  fi
}
trap rollback ERR

[[ "$(docker info --format '{{.Swarm.LocalNodeState}}')" == "active" ]]
[[ "$(docker info --format '{{.Swarm.ControlAvailable}}')" == "true" ]]
docker network inspect traefik_public >/dev/null
docker secret inspect paineldecomando_db_password >/dev/null
docker secret inspect paineldecomando_db_root_password >/dev/null

cd "$release_dir"

docker build \
  -f docker/frontend/Dockerfile.prod \
  -t "paineldecomando-frontend:${image_tag}" \
  --build-arg VITE_API_URL=/api/v1 \
  --build-arg "VITE_COMPANY_NAME=Painel de Comando" \
  --build-arg VITE_COMPANY_SHORT_NAME=PDC \
  --build-arg "VITE_COMPANY_LEGAL_NAME=SMARTFLOW TECNOLOGIA EIRELI" \
  --build-arg VITE_COMPANY_CNPJ=19.252.656/0001-20 \
  --build-arg "VITE_COMPANY_PHONE=+55 11 96919-5102" \
  --build-arg VITE_COMPANY_WHATSAPP=5511969195102 \
  --build-arg "VITE_COMPANY_WHATSAPP_LABEL=+55 11 96919-5102" \
  --build-arg VITE_COMPANY_EMAIL=contato@paineldecomando.com.br \
  --build-arg "VITE_COMPANY_ADDRESS=Rua Cabreúva, Sorocaba - SP, CEP 18085-340" \
  --build-arg "VITE_COMPANY_HOURS=Atendimento em horário comercial" \
  --build-arg "VITE_COMPANY_DELIVERY_NOTICE=Entregas exclusivamente em território brasileiro." \
  .

docker build \
  -f docker/backend/Dockerfile.prod \
  -t "paineldecomando-api:${image_tag}" \
  .

IMAGE_TAG="$image_tag" docker stack deploy \
  --detach=false \
  --resolve-image never \
  --compose-file docker-stack.prod.yml \
  "$stack_name"
deployed=1

wait_for_service() {
  local service="$1"
  local expected_image="$2"
  local current_replicas current_image

  for _ in $(seq 1 36); do
    current_replicas="$(docker service ls --filter "name=${stack_name}_${service}" --format '{{.Replicas}}')"
    current_image="$(docker service inspect "${stack_name}_${service}" --format '{{.Spec.TaskTemplate.ContainerSpec.Image}}')"
    if [[ "$current_replicas" == "1/1" && "$current_image" == "$expected_image" ]]; then
      return 0
    fi
    sleep 5
  done

  docker service ps "${stack_name}_${service}" --no-trunc
  return 1
}

wait_for_service frontend "paineldecomando-frontend:${image_tag}"
wait_for_service api "paineldecomando-api:${image_tag}"
wait_for_service db "mariadb:11.4"

api_container="$(docker ps \
  --filter "label=com.docker.swarm.service.name=${stack_name}_api" \
  --filter status=running \
  --format '{{.ID}}' | head -n 1)"
[[ -n "$api_container" ]]
docker exec "$api_container" sh -lc \
  'export DB_PASSWORD="$(cat "$DB_PASSWORD_FILE")"; php /var/www/html/bin/sync-production-catalog.php'

curl --fail --silent --show-error --retry 12 --retry-delay 5 \
  https://paineldecomando.com.br/api/v1/health >/dev/null
curl --fail --silent --show-error --retry 6 --retry-delay 3 \
  https://paineldecomando.com.br/produtos/painel-estrela-triangulo >/dev/null

ln -sfn "$release_dir" "${production_root}/current"
deployed=0
trap - ERR

echo "Release ${release_sha} publicada com sucesso."
