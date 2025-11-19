#!/bin/bash
# ============================================
# deploy.sh - Script de despliegue a Cloud Run
# ============================================

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables
PROJECT_ID="tu-proyecto-gcp"
SERVICE_NAME="dubss-laravel"
REGION="us-central1"
IMAGE_NAME="gcr.io/${PROJECT_ID}/${SERVICE_NAME}"
BUILD_DATE=$(date -u +'%Y-%m-%dT%H:%M:%SZ')
VCS_REF=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")

echo -e "${GREEN}🚀 Deploying DUBSS Laravel to Cloud Run${NC}"
echo "================================================"
echo "Project ID: $PROJECT_ID"
echo "Service: $SERVICE_NAME"
echo "Region: $REGION"
echo "Image: $IMAGE_NAME:$VCS_REF"
echo "================================================"

# Verificar que gcloud esté instalado
if ! command -v gcloud &> /dev/null; then
    echo -e "${RED}❌ gcloud CLI not found. Please install it first.${NC}"
    exit 1
fi

# Verificar que estemos logueados en gcloud
echo -e "${YELLOW}🔐 Checking gcloud authentication...${NC}"
if ! gcloud auth list --filter=status:ACTIVE --format="value(account)" | grep -q "@"; then
    echo -e "${RED}❌ Not authenticated. Run: gcloud auth login${NC}"
    exit 1
fi

# Configurar proyecto
echo -e "${YELLOW}⚙️  Setting GCP project...${NC}"
gcloud config set project $PROJECT_ID

# Habilitar APIs necesarias
echo -e "${YELLOW}🔧 Enabling required APIs...${NC}"
gcloud services enable \
    cloudbuild.googleapis.com \
    run.googleapis.com \
    containerregistry.googleapis.com \
    sqladmin.googleapis.com

# Build de la imagen con Cloud Build
echo -e "${YELLOW}🏗️  Building Docker image...${NC}"
gcloud builds submit \
    --tag $IMAGE_NAME:$VCS_REF \
    --tag $IMAGE_NAME:latest \
    --build-arg BUILD_DATE=$BUILD_DATE \
    --build-arg VCS_REF=$VCS_REF \
    .

# Desplegar a Cloud Run
echo -e "${YELLOW}☁️  Deploying to Cloud Run...${NC}"
gcloud run deploy $SERVICE_NAME \
    --image $IMAGE_NAME:$VCS_REF \
    --region $REGION \
    --platform managed \
    --allow-unauthenticated \
    --port 8080 \
    --memory 512Mi \
    --cpu 1 \
    --min-instances 0 \
    --max-instances 10 \
    --timeout 300 \
    --set-env-vars "APP_ENV=production" \
    --set-env-vars "APP_DEBUG=false" \
    --set-cloudsql-instances "${PROJECT_ID}:${REGION}:dubss-postgres" \
    --update-secrets APP_KEY=laravel-app-key:latest \
    --update-secrets DB_PASSWORD=db-password:latest

# Obtener URL del servicio
SERVICE_URL=$(gcloud run services describe $SERVICE_NAME \
    --region $REGION \
    --format 'value(status.url)')

echo -e "${GREEN}✅ Deployment completed!${NC}"
echo "================================================"
echo "Service URL: $SERVICE_URL"
echo "================================================"
