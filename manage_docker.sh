#!/bin/bash

# XTask Docker 管理腳本 (Linux/macOS)
ENV=${1:-dev}

if [ "$ENV" == "dev" ]; then
    echo "[Docker] 正在啟動開發環境 (Development)..."
    export APP_ENV=development
    docker compose up -d --build
elif [ "$ENV" == "prod" ]; then
    echo "[Docker] 正在啟動生產環境 (Production)..."
    docker compose -f docker-compose.prod.yml up -d --build
else
    echo "使用方法: ./manage_docker.sh [dev|prod]"
    exit 1
fi
