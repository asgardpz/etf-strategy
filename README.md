# ETF Strategy｜Laravel + TWSE Real Data

> **ETF 配息策略監控系統**

![ETF Strategy Dashboard](https://github.com/asgardpz/etf-strategy/blob/main/messageImage_1789016191008.jpg)


# ETF Strategy｜Laravel + TWSE Real Data

> **ETF 配息策略監控系統**
>
> A Laravel-based ETF dividend strategy monitoring system integrating real-time Taiwan Stock Exchange (TWSE) data with server-side data collection, MySQL persistence, automated scheduling, and investment strategy calculation.

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TWSE](https://img.shields.io/badge/Data-TWSE-0066CC?style=for-the-badge)
![Scheduler](https://img.shields.io/badge/Scheduler-5%20Minutes-198754?style=for-the-badge)

---

## 📌 Project Overview

**ETF Strategy** 是一套以 Laravel 12 開發的 ETF 配息策略監控系統。

系統透過 Server-side HTTP Request 取得臺灣證券交易所（TWSE）公開 ETF 配息與行情資料，經由 Laravel Service 層進行資料整理、保存及策略計算，最後透過 Web Dashboard 呈現目前可關注的 ETF 與策略結果。

本專案的重點並非自動交易，而是展示完整的：

```text
External API Integration
        ↓
Server-side Data Collection
        ↓
Data Processing
        ↓
MySQL Persistence
        ↓
Strategy Calculation
        ↓
Web Dashboard
        ↓
Automated Scheduler
```

專案不依賴 Python，也不由瀏覽器直接呼叫 TWSE API，因此避免前端直接存取外部 API 時常見的 CORS 問題。

---

## 🎯 Project Goals

本專案主要展示以下後端開發能力：

- REST / HTTP API 整合
- 第三方公開資料來源整合
- Server-side API Request
- 資料清洗與標準化
- MySQL 資料持久化
- Laravel Service Layer 設計
- Investment Strategy Rule Engine
- Laravel Scheduler
- Web Dashboard
- Windows 本機部署
- 自動化資料更新
- 基於真實市場資料的計算與分析

---

## 🚀 Core Features

### 1. TWSE ETF Dividend Data

取得 TWSE ETF e添富公開配息資料，並保存至 MySQL。

系統可取得：

- 證券代號
- 證券簡稱
- 除息交易日
- 收益分配基準日
- 收益分配發放日
- 收益分配金額

資料來源為臺灣證券交易所官方公開資訊。

---

### 2. TWSE Market Quote

透過 Laravel Server-side HTTP Request 取得 ETF 行情資料。

資料流程：

```text
Laravel
   │
   │ HTTP Request
   ▼
TWSE Market API
   │
   │ JSON
   ▼
TwseService
   │
   ▼
ETF Data Processing
   │
   ▼
MySQL
```

前端 Dashboard 不需要直接向 TWSE 發送 Request。

---

### 3. Dividend ETF Screening

系統會依據目前日期與除息日期，自動篩選：

- 本月即將除息 ETF
- 尚未除息 ETF
- 尚未超過最後買進日 ETF
- 帳戶資金可以購買至少一張的 ETF

---

### 4. Last Buy Date

目前策略以：

> **除息日前一個交易日**

作為最後買進日。

目前版本使用週末排除方式計算交易日。

```text
除息日
  │
  └── Previous Trading Day
          │
          ▼
      Last Buy Date
```

> 若未來需要處理臺灣證券交易所特殊休市日，可進一步整合 TWSE Holiday Schedule API。

---

### 5. Account Amount

Dashboard 提供可投入帳戶金額設定。

預設：

```text
NT$800,000
```

系統會依帳戶金額計算：

- 可購買張數
- 可投入金額
- 剩餘資金
- 預估配息

例如：

```text
帳戶金額
   ↓
ETF 股價
   ↓
計算可購買整張數
   ↓
計算實際投入金額
   ↓
計算預估配息
```

---

### 6. Strategy Calculation

目前系統主要提供兩種策略結果。

#### BUY Candidate

符合以下條件：

```text
除息日在今天或之後
        AND
今天不晚於最後買進日
        AND
帳戶至少可以購買 1 張
```

符合條件時，列為可關注的 ETF。

---

#### SELL Alert

對已建立的 `portfolios` 持倉資料進行分析。

股價價差：

```text
(Current Price - Purchase Price) × Shares
```

總報酬：

```text
Price Gain + Received Dividend
```

當：

```text
Dividend Received = true
AND
Price Gain > Received Dividend
```

則產生：

```text
SELL_ALERT
```

此條件代表：

> 持倉在已取得股利後，如果目前股價產生的價差已經超過已取得的股利金額，系統提出賣出提醒。

---

## 🏗️ System Architecture

```text
┌──────────────────────────────┐
│            TWSE              │
│                              │
│ ETF Dividend Data            │
│ Market Quote                 │
└──────────────┬───────────────┘
               │
               │ HTTPS / JSON
               ▼
┌──────────────────────────────┐
│       Laravel Application    │
│                              │
│ TwseService                  │
│ EtfDataService               │
│ StrategyService              │
└──────────────┬───────────────┘
               │
               │ Eloquent ORM
               ▼
┌──────────────────────────────┐
│            MySQL             │
│                              │
│ etfs                         │
│ etf_dividends                │
│ etf_quotes                   │
│ strategy_results             │
│ portfolios                   │
│ app_settings                 │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│         Web Dashboard        │
│                              │
│ ETF Dividend Monitor         │
│ Quote                        │
│ Investment Calculation       │
│ Strategy Result              │
└──────────────────────────────┘


Laravel Scheduler
       │
       │ Every 5 Minutes
       ▼
php artisan etf:fetch
       │
       └──────────────► TWSE Data Update
```

---

## 🧩 Application Architecture

主要程式結構：

```text
app/
├── Console/
│   └── Commands/
│       └── FetchEtfData.php
│
├── Http/
│   └── Controllers/
│       ├── DashboardController.php
│       └── EtfController.php
│
├── Models/
│   ├── Etf.php
│   ├── EtfDividend.php
│   ├── EtfQuote.php
│   ├── StrategyResult.php
│   ├── Portfolio.php
│   └── AppSetting.php
│
└── Services/
    ├── TwseService.php
    ├── EtfDataService.php
    └── StrategyService.php
```

### Service Responsibilities

#### `TwseService`

負責與 TWSE 外部資料來源溝通。

```text
TWSE API
   ↓
TwseService
   ↓
Standardized Data
```

將外部 API 與系統內部邏輯隔離。

---

#### `EtfDataService`

負責：

- ETF 資料同步
- 配息資料同步
- 行情資料保存
- ETF 基礎資料更新

---

#### `StrategyService`

負責：

- 最後買進日計算
- ETF 候選篩選
- 張數計算
- 投入金額計算
- 預估配息
- 持倉報酬計算
- `SELL_ALERT` 判斷

---

#### `FetchEtfData`

Laravel Artisan Command：

```bash
php artisan etf:fetch
```

負責執行完整 ETF 資料更新流程。

---

## 🗄️ Database

系統使用 MySQL 保存資料。

主要資料表：

| Table | Purpose |
|---|---|
| `etfs` | ETF 基本資料 |
| `etf_dividends` | ETF 配息資料 |
| `etf_quotes` | ETF 行情資料 |
| `strategy_results` | 策略分析結果 |
| `portfolios` | 使用者持倉 |
| `app_settings` | 系統設定 |

資料流程：

```text
TWSE
 ↓
API Response
 ↓
Laravel Service
 ↓
MySQL
 ↓
Strategy Engine
 ↓
Dashboard
```

---

## 🔄 Automated Data Update

系統使用 Laravel Scheduler 每 5 分鐘執行：

```bash
php artisan etf:fetch
```

`routes/console.php`：

```php
Schedule::command('etf:fetch')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
```

確認 Scheduler：

```bash
php artisan schedule:list
```

預期：

```text
*/5 * * * *  php artisan etf:fetch
```

Windows 開發環境可以使用：

```bash
php artisan schedule:work
```

正式 Windows 環境則建議使用 **Windows Task Scheduler** 每分鐘執行：

```bash
php artisan schedule:run
```

Laravel Scheduler 本身會判斷目前是否為 `etf:fetch` 的執行時間。

---

## 📡 Official Data Sources

本專案使用臺灣證券交易所官方公開資料。

### TWSE ETF e添富

[TWSE ETF e添富－配息資訊](https://www.twse.com.tw/zh/ETFortune/dividendList?utm_source=chatgpt.com)

主要取得 ETF 配息相關資訊：

- 證券代號
- 證券簡稱
- 除息交易日
- 收益分配基準日
- 收益分配發放日
- 收益分配金額

---

### TWSE OpenAPI

[TWSE OpenAPI](https://openapi.twse.com.tw/v1?utm_source=chatgpt.com)

用於取得 TWSE 公開市場資料。

---

### TWSE Market Information

[TWSE Market Information API](https://mis.twse.com.tw/stock/api/getStockInfo.jsp?utm_source=chatgpt.com)

用於 Server-side ETF 行情資料取得。

> 實際資料內容與欄位格式應以 TWSE 當期公開服務為準。

---

## 💻 Technology Stack

| Category | Technology |
|---|---|
| Backend | Laravel 12 |
| Language | PHP 8.2+ |
| Database | MySQL / MariaDB |
| ORM | Laravel Eloquent |
| HTTP Client | Guzzle |
| Frontend | Blade + HTML/CSS/JavaScript |
| Scheduler | Laravel Scheduler |
| Data Source | Taiwan Stock Exchange (TWSE) |
| Runtime | Windows / XAMPP |
| Version Control | Git / GitHub |

---

## 🖥️ Installation

### Requirements

- Windows
- PHP 8.2+
- Composer
- MySQL / MariaDB
- XAMPP（可選 Apache；Laravel 可使用 Artisan Server）
- Git（如果從 GitHub Clone）

確認環境：

```bash
php -v
composer -V
```

---

### 1. Clone Repository

```bash
git clone https://github.com/YOUR_USERNAME/etf-strategy.git
cd etf-strategy
```

如果使用 ZIP：

```text
C:\xampp\htdocs\etf-strategy
```

---

### 2. Install Dependencies

```bash
composer install
```

---

### 3. Create Environment File

Windows：

```bash
copy .env.example .env
```

產生 Application Key：

```bash
php artisan key:generate
```

---

### 4. Create MySQL Database

使用 phpMyAdmin 或 MySQL CLI：

```sql
CREATE DATABASE etf_strategy
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

---

### 5. Configure `.env`

```env
APP_NAME="ETF Strategy"
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=etf_strategy
DB_USERNAME=root
DB_PASSWORD=

TWSE_BASE_URL=https://www.twse.com.tw
TWSE_OPENAPI_URL=https://openapi.twse.com.tw/v1
TWSE_QUOTE_URL=https://mis.twse.com.tw/stock/api/getStockInfo.jsp

TWSE_TIMEOUT=20
TWSE_USER_AGENT="ETFStrategy/1.0"

ETF_ACCOUNT_AMOUNT=800000
ETF_REFRESH_MINUTES=5
```

> 請勿將 `.env`、資料庫密碼或其他敏感設定提交至 GitHub。

---

### 6. Run Migration

```bash
php artisan migrate
```

---

### 7. Start Laravel

```bash
php artisan serve
```

瀏覽：

```text
http://127.0.0.1:8000
```

---

## ⚙️ Manual Data Update

可以直接執行：

```bash
php artisan etf:fetch
```

這是最直接的方式確認：

```text
Laravel
 ↓
TWSE
 ↓
Data Processing
 ↓
MySQL
```

是否正常。

---

## 💰 Account Configuration

預設帳戶金額：

```env
ETF_ACCOUNT_AMOUNT=800000
```

也可以直接在 Dashboard 修改。

系統會依目前帳戶金額重新計算：

```text
Available Amount
      ↓
ETF Price
      ↓
Maximum Whole Lots
      ↓
Investment Amount
      ↓
Estimated Dividend
```

目前版本定位為：

> **單一使用者、本機使用的策略分析工具**

若未來改為多使用者 Web System，則可將帳戶設定、使用者與持倉資料進一步拆分。

---

## 📊 Portfolio

目前系統**不會自動建立假持倉資料**。

若需要測試 SELL Alert，可以在 MySQL `portfolios` 建立自己的真實或明確標示為測試用途的持倉。

例如：

```sql
INSERT INTO portfolios
(
    etf_id,
    shares,
    purchase_price,
    purchase_date,
    dividend_received,
    dividend_received_amount,
    status,
    created_at,
    updated_at
)
VALUES
(
    1,
    34000,
    23.50,
    '2026-09-10',
    1,
    18360,
    'holding',
    NOW(),
    NOW()
);
```

> `etf_id` 必須替換成資料庫中實際存在的 ETF ID。  
> 上述數值僅作為資料格式示例，不代表任何實際投資建議。

---

## 🧮 Strategy Example

假設：

```text
Purchase Price = 23.50
Current Price  = 25.00
Shares         = 34,000
Received Dividend = NT$18,360
```

股價價差：

```text
(25.00 - 23.50) × 34,000
= NT$51,000
```

因為：

```text
Price Gain = NT$51,000
Dividend   = NT$18,360
```

符合：

```text
Price Gain > Received Dividend
```

因此系統產生：

```text
SELL_ALERT
```

---

## ⚠️ Strategy Limitations

目前策略尚未納入：

- 券商手續費
- 證券交易稅
- 滑價
- 融資利息
- ETF 管理費
- 配息稅務差異
- 特殊交易限制

因此目前的：

```text
SELL_ALERT
```

是**程式化策略條件提醒**，不是實際交易建議。

如果要作為正式投資決策工具，應進一步建立完整的交易成本模型。

---

## ⚠️ Data Limitations

### TWSE API

TWSE 為外部服務，資料格式、欄位或服務政策可能變更。

因此本專案將 TWSE 存取集中於：

```text
app/Services/TwseService.php
```

降低外部 API 變更對其他系統模組的影響。

---

### Non-Trading Hours

非交易時間取得的行情資料可能代表：

- 最近成交價
- 參考價格
- 前一交易日資料

因此不能將非交易時間取得的數值直接視為即時成交價。

---

### Trading Calendar

目前最後買進日主要以週末排除方式計算。

特殊休市日尚未完全納入。

未來可整合 TWSE Holiday Schedule，提升交易日判斷準確度。

---

## 🔐 Security

請確認以下內容**不要提交到 GitHub**：

```text
.env
資料庫帳號密碼
API Token
公司內部 IP
公司內部 API
公司資料
個人投資帳戶資料
券商帳號
其他機密資訊
```

建議：

```text
.env
.env.example
   │
   ├── .env → 真實設定，不上 GitHub
   │
   └── .env.example → 公開範例設定
```

---

## 🧪 Testing

手動測試：

```bash
php artisan etf:fetch
```

查看 Scheduler：

```bash
php artisan schedule:list
```

啟動 Scheduler：

```bash
php artisan schedule:work
```

執行測試：

```bash
php artisan test
```

---

## 📁 Project Structure

```text
etf-strategy/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── FetchEtfData.php
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       ├── DashboardController.php
│   │       └── EtfController.php
│   │
│   ├── Models/
│   │   ├── Etf.php
│   │   ├── EtfDividend.php
│   │   ├── EtfQuote.php
│   │   ├── StrategyResult.php
│   │   ├── Portfolio.php
│   │   └── AppSetting.php
│   │
│   └── Services/
│       ├── TwseService.php
│       ├── EtfDataService.php
│       └── StrategyService.php
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   └── views/
│       ├── layouts/
│       └── dashboard.blade.php
│
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
│
├── config/
│   └── etf.php
│
├── tests/
│   └── Unit/
│
├── .env.example
├── artisan
├── composer.json
├── phpunit.xml
└── README.md
```

---

## 🔄 Data Flow

完整資料處理流程：

```text
                    TWSE
                     │
          ┌──────────┴──────────┐
          │                     │
    ETF Dividend            Market Quote
          │                     │
          └──────────┬──────────┘
                     ▼
              TwseService
                     │
                     ▼
             EtfDataService
                     │
                     ▼
                  MySQL
                     │
                     ▼
             StrategyService
                     │
          ┌──────────┴──────────┐
          │                     │
     BUY Candidate          SELL Alert
          │                     │
          └──────────┬──────────┘
                     ▼
                Dashboard
```

---

## 🛠️ Future Improvements

以下項目為未來可擴充方向，目前**不代表已完成**：

- [ ] TWSE 完整交易日 / 休市日整合
- [ ] 歷史行情分析
- [ ] 歷史策略結果統計
- [ ] 交易成本模型
- [ ] ETF 配息殖利率趨勢
- [ ] Strategy Performance Backtesting
- [ ] 多使用者帳戶
- [ ] 使用者持倉管理
- [ ] Dashboard Chart
- [ ] API Rate Limit / Retry Policy
- [ ] Queue-based data processing
- [ ] Docker deployment
- [ ] Production Linux deployment

---

## 🚫 Out of Scope

本專案目前刻意不包含：

```text
❌ 自動下單
❌ 券商帳戶登入
❌ 自動買進
❌ 自動賣出
❌ 自動轉帳
❌ 保證獲利
❌ 投資報酬保證
```

本系統僅提供：

> **公開市場資料整理、策略計算與資訊監控。**

---

## 📌 Disclaimer

本專案為個人軟體開發與資料分析作品。

系統提供之 ETF 資料、行情與策略計算僅供研究、程式設計展示及資訊整理用途，不構成任何投資建議、買賣推薦或報酬保證。

實際 ETF 配息、除息日期、行情及相關資訊，應以臺灣證券交易所、ETF 發行公司及券商正式公告為準。

---

## 👨‍💻 About This Project

此專案以 **Backend Development / API Integration / Data Processing / Automation** 為主要展示方向。

透過 Laravel 建立完整的 Server-side 資料處理流程：

```text
External API
     ↓
Laravel Service
     ↓
Database
     ↓
Business Logic
     ↓
Scheduled Job
     ↓
Web Dashboard
```

核心技術：

**Laravel 12 + PHP 8.2 + MySQL + TWSE API + Scheduler**

---

## ⭐ Project Highlights

如果從 Backend Engineer 的角度來看，本專案主要展示：

| Capability | Implementation |
|---|---|
| API Integration | TWSE Public API |
| Backend | Laravel 12 |
| Data Processing | Service Layer |
| Database | MySQL + Eloquent |
| Business Logic | StrategyService |
| Automation | Laravel Scheduler |
| Web UI | Blade Dashboard |
| External Service Isolation | TwseService |
| Data Persistence | ETF / Dividend / Quote / Strategy |
| Deployment | Windows + XAMPP |
| Version Control | Git / GitHub |

---

## 📄 License

This project is for personal portfolio and software development demonstration purposes.

Please review the terms and usage policies of the respective TWSE public data services before deploying the system for production or commercial use.
