!/bin/bash
# Este script configura Cloud SQL para PostgreSQL

set -e

PROJECT_ID="tu-proyecto-gcp"
INSTANCE_NAME="dubss-postgres"
REGION="us-central1"
DATABASE_VERSION="POSTGRES_16"
TIER="db-f1-micro"  # Cambiar según necesidades
DB_NAME="dubss"
DB_USER="dubss_user"

echo "🗄️  Setting up Cloud SQL PostgreSQL..."

# Crear instancia de Cloud SQL
gcloud sql instances create $INSTANCE_NAME \
    --database-version=$DATABASE_VERSION \
    --tier=$TIER \
    --region=$REGION \
    --root-password=$(openssl rand -base64 32) \
    --backup \
    --backup-start-time=03:00 \
    --enable-bin-log

# Crear base de datos
gcloud sql databases create $DB_NAME \
    --instance=$INSTANCE_NAME

# Crear usuario
DB_PASSWORD=$(openssl rand -base64 32)
gcloud sql users create $DB_USER \
    --instance=$INSTANCE_NAME \
    --password=$DB_PASSWORD

echo "✅ Cloud SQL setup completed!"
echo "Save this password: $DB_PASSWORD"

# Guardar password en Secret Manager
gcloud secrets create db-password \
    --data-file=<(echo -n "$DB_PASSWORD") \
    --replication-policy="automatic"
