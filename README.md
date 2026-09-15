# 🏢 CADFEM Asset Management System

> **Enterprise-grade Asset Management Platform** - Modern, Professional IT Asset Tracking System built on Laravel 12

[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL%203.0-blue.svg)](https://www.gnu.org/licenses/agpl-3.0) [![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com) [![PHP 8.5+](https://img.shields.io/badge/PHP-8.5+-777BB4?logo=php&logoColor=white)](https://www.php.net) [![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)

---

## 📋 Overview

**CADFEM Asset Management** is a modern, open-source IT asset management system designed for enterprises. Track equipment, manage depreciation, handle software licenses, and maintain complete IT inventory with professional dashboards and real-time insights.

### ✨ Key Features

- **🎯 Equipment Dashboard** - Real-time KPI metrics with interactive ApexCharts visualizations
- **📊 Asset Analytics** - Comprehensive asset distribution by category, status, and location
- **💰 Depreciation Tracking** - Automatic depreciation calculations and financial reporting
- **📄 Software Licensing** - License management, compliance tracking, and renewal alerts
- **🏢 Multi-Location Support** - Manage assets across multiple offices and departments
- **👥 User Management** - Role-based access control and team permissions
- **📱 Responsive Design** - Works seamlessly on desktop, tablet, and mobile
- **🔍 Advanced Search** - Powerful filtering and search capabilities
- **📈 Reports** - Generate detailed asset and financial reports

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.5.10+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js & npm

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/PrasanthDudala03/cadfem-asset-management.git
cd cadfem-asset-management
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
npm run build
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Setup database**
```bash
# Create MySQL database
mysql -u root -p -e "CREATE DATABASE snipeit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Update .env with database credentials
# DB_DATABASE=snipeit
# DB_USERNAME=root
# DB_PASSWORD=your_password

php artisan migrate
php artisan db:seed
```

6. **Start the server**
```bash
php artisan serve --host=localhost --port=8000
```

Access the application at: **http://localhost:8000**

---

## 📊 Dashboard Features

### Equipment Dashboard
- **Total Assets** - Complete inventory count
- **Active Assets** - Currently deployed equipment
- **In Maintenance** - Assets undergoing repairs
- **Retired Assets** - Decommissioned equipment

### Visualizations
- **Asset Status Distribution** - Donut chart showing deployment status
- **Assets by Category** - Bar chart breakdown by equipment type
- **Recent Assets** - Latest deployments with full details
- **Location Analytics** - Asset distribution by office/location

---

## 🏗️ Project Structure

```
cadfem-asset-management/
├── app/
│   ├── Http/Controllers/EquipmentDashboardController.php
│   ├── Models/
│   └── Services/
├── database/
│   ├── migrations/
│   ├── seeders/
│   │   ├── ImportSimpleAssetsSeeder.php
│   │   └── SettingsSeeder.php
│   └── deployed_assets_2026.csv
├── resources/
│   ├── views/
│   │   ├── dashboard/
│   │   │   └── equipment.blade.php
│   │   └── layouts/
│   ├── css/
│   │   └── cadfem-branding.css
│   └── js/
├── public/
│   ├── css/
│   ├── img/
│   │   ├── cadfem-logo.svg
│   │   └── cadfem-logo-full.svg
│   └── js/
├── routes/
│   └── web.php
└── storage/
    └── deployed_assets_2026.csv
```

---

## 🎨 Branding & Customization

CADFEM Asset Management uses custom branding throughout:

- **Primary Color**: #003366 (Professional Navy Blue)
- **Secondary Colors**: #28a745 (Green), #ffc107 (Gold), #dc3545 (Red)
- **Logo**: Custom CADFEM SVG logos included
- **Styling**: Modern gradient backgrounds, rounded cards, professional shadows

All branding can be customized in:
- `public/css/cadfem-branding.css` - Color variables and styles
- `public/img/` - Logo files
- `config/app.php` - Application name and settings
- `.env` - Site configuration

---

## 📦 Asset Import

Import assets from CSV file with automated seeder:

```bash
# Place CSV file at: storage/app/deployed_assets_2026.csv
# Run seeder
php artisan db:seed --class=ImportSimpleAssetsSeeder
```

**CSV Format:**
```csv
Asset Tag,Serial,Model,Manufacturer,Category,Location,Notes
FN-E16-02,SN123456,LENOVO E16G1,Lenovo,Laptop,Hyderabad,Active deployment
CFD-3680-04,SN789012,Dell T3680,Dell,CPU,Hyderabad,Server deployment
```

---

## 🔐 Security

- **AGPL-3.0 License** - Open source, respecting community principles
- **Laravel Security** - Built-in CSRF protection, SQL injection prevention
- **Authentication** - Secure user login with email verification
- **Authorization** - Role-based access control (RBAC)
- **Encryption** - Sensitive data encrypted at rest

---

## 📝 API Endpoints

```
GET  /api/dashboard/data                    - Dashboard KPI metrics
GET  /api/dashboard/status-distribution     - Asset status breakdown
GET  /api/dashboard/category-distribution   - Assets by category
```

---

## 🛠️ Configuration

### Environment Variables (.env)

```env
APP_NAME="CADFEM Asset Management"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=snipeit
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_FROM_ADDRESS=prashanth.d@cadfem.ai
MAIL_FROM_NAME="CADFEM Asset Management"

# Branding
SITE_NAME="CADFEM Asset Management"
HEADER_COLOR=#003366
```

---

## 📚 Documentation

- **[Installation Guide](./docs/INSTALLATION.md)** - Detailed setup instructions
- **[User Manual](./docs/USER_MANUAL.md)** - Complete feature documentation
- **[API Documentation](./docs/API.md)** - REST API reference
- **[Troubleshooting](./docs/TROUBLESHOOTING.md)** - Common issues & solutions

---

## 🐛 Bug Reports & Support

Found a bug? Have a feature request?

- **GitHub Issues**: https://github.com/PrasanthDudala03/cadfem-asset-management/issues
- **Email Support**: prashanth.d@cadfem.ai
- **Documentation**: Check the docs/ folder first

---

## 📄 License

This project is licensed under the **AGPL-3.0 License** - see the [LICENSE](LICENSE) file for details.

### Attribution

This project is based on [Snipe-IT](https://github.com/grokability/snipe-it), an excellent open-source asset management system. We maintain full compliance with the AGPL-3.0 license and contribute improvements back to the community.

---

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📊 Technology Stack

| Technology | Purpose |
|-----------|---------|
| **Laravel 12** | Backend framework |
| **PHP 8.5** | Server-side language |
| **MySQL 5.7+** | Database |
| **Bootstrap 5** | Frontend framework |
| **ApexCharts** | Data visualization |
| **Blade** | Template engine |
| **Composer** | PHP package manager |

---

## 🎯 Roadmap

- [ ] Mobile app (iOS/Android)
- [ ] Advanced analytics & reporting
- [ ] Integration with CMDB
- [ ] Barcode/QR code scanning
- [ ] Automated depreciation calculations
- [ ] Multi-language support
- [ ] Dark mode theme

---

## 👥 Authors & Acknowledgments

- **CADFEM Development Team** - Customization & maintenance
- **Grokability** - Original Snipe-IT creators
- **Laravel Community** - Framework & ecosystem

---

## 📞 Contact

**CADFEM Asset Management Team**
- Email: prashanth.d@cadfem.ai
- Website: www.cadfem.ai
- GitHub: https://github.com/PrasanthDudala03/cadfem-asset-management

---

## 📌 Latest Updates

### v2026.1.0 - Dashboard Modernization
- ✨ New modern equipment dashboard with ApexCharts
- 🎨 Professional UI redesign with gradient backgrounds
- 📊 Interactive KPI cards with real-time data
- 🚀 Asset import with 85 sample deployments
- 🏢 Full CADFEM branding throughout

---

**Made with ❤️ by CADFEM**

*Last Updated: September 15, 2026*
