#!/bin/bash

# ==============================================================================
# XTask 自動部署腳本
# 功能：自動打包前端 (Nuxt 4)、上傳後端 (Laravel) 與前端產物至伺服器
# ==============================================================================

# --- [設定區塊] 請根據您的伺服器環境修改 ---
SERVER_USER="root"                       # 伺服器使用者名稱
SERVER_HOST="123.123.123.123"            # 伺服器 IP 或域名
SERVER_PORT="22"                         # SSH 端口
REMOTE_PATH="/var/www/xtask"             # 伺服器上的部署路徑
# ------------------------------------------------------------------------------

# --- [變數定義] ---
LOCAL_ROOT=$(pwd)
TEMP_DIST="deploy_dist"
ARCHIVE_NAME="xtask_v_$(date +%Y%m%d_%H%M%S).tar.gz"

# 顏色定義
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}=== [1/5] 開始前端構建 (Frontend Build) ===${NC}"
cd "$LOCAL_ROOT/frontend"
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}偵測到未安裝依賴，正在執行 npm install...${NC}"
    npm install
fi
npm run build
if [ $? -ne 0 ]; then
    echo -e "${RED}前端構建失敗！${NC}"
    exit 1
fi

echo -e "${BLUE}=== [2/5] 準備部署包 (Packaging) ===${NC}"
cd "$LOCAL_ROOT"
mkdir -p "$TEMP_DIST"

# 複製前端編譯產物
echo "正在打包前端產物..."
cp -r frontend/.output "$TEMP_DIST/frontend_output"

# 複製後端檔案 (排除大型或敏感資料夾)
echo "正在打包後端原始碼..."
mkdir -p "$TEMP_DIST/backend"
rsync -av --progress backend/ "$TEMP_DIST/backend" \
    --exclude 'vendor' \
    --exclude 'node_modules' \
    --exclude '.git' \
    --exclude 'storage/logs/*' \
    --exclude 'storage/framework/cache/data/*' \
    --exclude 'storage/framework/sessions/*' \
    --exclude 'storage/framework/views/*.php' \
    --exclude '.env' \
    --exclude '.phpunit.result.cache'

# 執行打包壓縮
echo -e "${YELLOW}正在壓縮檔案: $ARCHIVE_NAME...${NC}"
tar -czf "$ARCHIVE_NAME" -C "$TEMP_DIST" .
rm -rf "$TEMP_DIST"

echo -e "${BLUE}=== [3/5] 上傳至伺服器 (Uploading) ===${NC}"
scp -P "$SERVER_PORT" "$ARCHIVE_NAME" "$SERVER_USER@$SERVER_HOST:/tmp/"
if [ $? -ne 0 ]; then
    echo -e "${RED}上傳失敗！${NC}"
    exit 1
fi

echo -e "${BLUE}=== [4/5] 遠端解壓與維護 (Remote Setup) ===${NC}"
ssh -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" << EOF
    echo "建立目錄: $REMOTE_PATH"
    mkdir -p "$REMOTE_PATH"
    
    echo "正在解壓檔案..."
    tar -xzf "/tmp/$ARCHIVE_NAME" -C "$REMOTE_PATH"
    rm "/tmp/$ARCHIVE_NAME"
    
    # 後端維護
    cd "$REMOTE_PATH/backend"
    if [ -f "composer.json" ]; then
        echo "執行 Composer Install..."
        composer install --no-dev --optimize-autoloader --no-interaction
        php artisan migrate --force
        php artisan cache:clear
        php artisan config:cache
        php artisan route:cache
    fi
    
    # 權限修正
    echo "修正權限..."
    chown -R www-data:www-data "$REMOTE_PATH"
    chmod -R 775 "$REMOTE_PATH/backend/storage" "$REMOTE_PATH/backend/bootstrap/cache"
    
    # 前端重啟 (範例：若使用 PM2 管理 Nuxt)
    # if command -v pm2 &> /dev/null; then
    #    pm2 restart xtask-frontend || pm2 start $REMOTE_PATH/frontend_output/server/index.mjs --name xtask-frontend
    # fi

    echo "遠端執行完畢！"
EOF

echo -e "${BLUE}=== [5/5] 清理本地檔案 (Cleanup) ===${NC}"
rm "$ARCHIVE_NAME"

echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}   部署成功！專案已更新至伺服器。${NC}"
echo -e "${GREEN}==========================================${NC}"
