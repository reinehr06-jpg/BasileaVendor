#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SSL_DIR="$DIR/../ssl"

mkdir -p "$SSL_DIR"

if [ -f "$SSL_DIR/cert.pem" ] && [ -f "$SSL_DIR/key.pem" ]; then
    echo "Certificados já existem em $SSL_DIR."
    exit 0
fi

echo "Gerando certificados self-signed para ambiente de desenvolvimento..."

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$SSL_DIR/key.pem" \
    -out "$SSL_DIR/cert.pem" \
    -subj "/C=BR/ST=SP/L=Sao Paulo/O=Basileia/CN=localhost"

echo "Certificados gerados com sucesso!"
