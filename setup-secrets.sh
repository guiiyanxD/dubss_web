!/bin/bash
# Script para configurar secrets en Secret Manager

set -e

PROJECT_ID="tu-proyecto-gcp"

echo "🔐 Setting up secrets..."

# Generar APP_KEY de Laravel
APP_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")

# Guardar en Secret Manager
echo -n "$APP_KEY" | gcloud secrets create laravel-app-key \
    --data-file=- \
    --replication-policy="automatic"

echo "✅ Secrets configured!"
