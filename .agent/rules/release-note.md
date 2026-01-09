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
## 後端開發規範
- API 接口都需要撰寫 tests
- API 接口更新需要調整 backend/openapi.yaml 檔