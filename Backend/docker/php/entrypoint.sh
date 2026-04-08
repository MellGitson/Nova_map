#!/bin/sh
set -e

# Génère les clés JWT si elles n'existent pas encore
if [ ! -f config/jwt/private.pem ]; then
    mkdir -p config/jwt
    openssl genpkey -algorithm RSA \
        -out config/jwt/private.pem \
        -pkeyopt rsa_keygen_bits:4096 \
        -pass pass:"${JWT_PASSPHRASE}"
    openssl rsa \
        -pubout \
        -in config/jwt/private.pem \
        -out config/jwt/public.pem \
        -passin pass:"${JWT_PASSPHRASE}"
    echo "[JWT] Clés générées."
fi

# Migrations Doctrine
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

exec "$@"
