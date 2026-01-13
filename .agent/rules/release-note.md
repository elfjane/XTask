---
trigger: always_on
---

# 全域規範
- release.md 英文更新訊息
- release_tw.md 中文更新訊息
- README.md 英文說明訊息
- README_tw.md 中文說明訊息
- 更新的功能都要自動更新 release.md 跟 release_tw.md
- 更新的文件功能摘要，都要寫到 README.md 跟 README_tw.md
## 前端開發規範
- 有前端界面部份，都要自動支援多國語系。
- 前端採用 nuxt4
## 後端開發規範
- API 接口都需要撰寫 tests
- API 接口更新需要調整 backend/openapi.yaml 檔
- 使用 PHP Laravel 12 以上
- 後端只開發 API，使用 OpenAPI 格式
## 資料庫規範
- 使用 MySql 8.x
- 語法限制使用 MySql 5.x 或 MySql 8.x 語法
## 多國語系
- 支援 zh-TW 跟 en