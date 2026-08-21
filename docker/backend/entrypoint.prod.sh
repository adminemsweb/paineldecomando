#!/bin/sh
set -eu

if [ -n "${DB_PASSWORD_FILE:-}" ]; then
  DB_PASSWORD="$(cat "$DB_PASSWORD_FILE")"
  export DB_PASSWORD
fi

migration_attempt=1
migration_max_attempts=30

until php /var/www/html/bin/migrate.php; do
  if [ "$migration_attempt" -ge "$migration_max_attempts" ]; then
    echo "Banco de dados indisponível após ${migration_max_attempts} tentativas; abortando inicialização da API." >&2
    exit 1
  fi

  echo "Banco de dados ainda não está pronto; nova tentativa em 2 segundos (${migration_attempt}/${migration_max_attempts})." >&2
  migration_attempt=$((migration_attempt + 1))
  sleep 2
done

exec apache2-foreground
