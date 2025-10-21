<div align="center">

[🇬🇧 English](README.md) | [🇹🇷 Türkçe](README.tr.md)

# 🌊 Zunvo

**Modern, Open Source Forum Software**

*Next-generation discussion platform for communities*

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Status](https://img.shields.io/badge/Status-Under%20Development-orange.svg)]()
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg?logo=php)]()
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)]()

[Features](#-features) • [Roadmap](#-roadmap) • [Installation](#-installation) • [Contributing](#-contributing) • [License](#-license)

---

### ⚠️ Under Active Development

Zunvo is currently under active development. The first stable release is coming soon!

**🎯 Estimated First Release:** Q2 2025

**⭐ Star the project to stay updated on the first release!**

</div>

---

## 📖 What is Zunvo?

Zunvo is a modern, completely open-source forum software built from scratch. Developed with pure PHP, it runs without any framework or composer dependencies.

### 🎯 Vision

Modern forum software is either too complex, too expensive, or restricts your freedom. Zunvo is designed to solve all three problems:

- ✅ **Simple & Powerful:** Ready to use without complex configuration
- ✅ **Completely Free:** No fees, no restrictions
- ✅ **Full Control:** Own 100% of your code

---

## ✨ Features

### 🎨 Modern User Experience
- **Responsive Design** - Works flawlessly on mobile, tablet, and desktop
- **Dark Mode** - Eye-friendly dark theme support
- **Upvote/Downvote System** - Reddit-style voting mechanism
- **Real-time Notifications** - AJAX-based instant updates
- **Rich Text Editor** - Advanced content creation tools

### 💪 Powerful Forum Features
- **Unlimited Categories & Subcategories** - Flexible organization structure
- **Tag System** - Organize topics with tags
- **Advanced Search** - Powerful search and filtering
- **Moderation Tools** - Comprehensive content management
- **User Roles** - Customizable permission system
- **Reputation System** - User credibility scores

### 🔌 Extensibility
- **Plugin System** - Hook-based modular architecture
- **Theme Engine** - Easy theme customization
- **RESTful API** - API for external integrations
- **Webhook Support** - Send notifications to external services

### 🔒 Security & Performance
- **CSRF Protection** - Cross-site request forgery prevention
- **XSS Filtering** - Cross-site scripting protection
- **SQL Injection Protection** - PDO prepared statements
- **Rate Limiting** - Spam and abuse prevention
- **Cache System** - Caching for high performance
- **Lazy Loading** - Fast page loads

### 🌍 International
- **Multi-language Support** - Easily localizable
- **RTL Support** - Right-to-left language support
- **Timezone Management** - Automatic time conversion

---

## 🗺️ Roadmap

### ✅ Phase 1: Core Infrastructure (Completed)
- [x] Project architecture design
- [x] MVC-like structure creation
- [x] Database schema design
- [x] Core system components
- [x] Routing system

### 🔄 Phase 2: User System (In Development)
- [ ] Registration and login system
- [ ] Email verification
- [ ] Password reset
- [ ] User profiles
- [ ] Avatar upload

### 📋 Phase 3: Forum Features (Planned)
- [ ] Category management
- [ ] Topic creation and viewing
- [ ] Comment system
- [ ] Upvote/Downvote mechanism
- [ ] Search functionality

### 🚀 Phase 4: Advanced Features (Planned)
- [ ] Notification system
- [ ] Tag system
- [ ] Moderation tools
- [ ] User badges
- [ ] Statistics and reports

### 🎨 Phase 5: Extensibility (Planned)
- [ ] Plugin API
- [ ] Theme system
- [ ] Widget system
- [ ] RESTful API
- [ ] Webhook integration

### 🔧 Phase 6: Optimization & Release (Planned)
- [ ] Performance optimization
- [ ] SEO improvements
- [ ] Security audit
- [ ] Documentation
- [ ] **v1.0.0 Release** 🎉

---

## 🚀 Quick Start

> **Note:** Zunvo is currently under development. The following instructions will be valid when the first version is released.

### System Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- 512 MB RAM (minimum)
- 100 MB disk space

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/zunvo.git

# 2. Navigate to Zunvo directory
cd zunvo

# 3. Create configuration file
cp config/config.sample.php config/config.php

# 4. Edit your database credentials
nano config/config.php

# 5. Run the installation wizard
# In your browser: http://yourdomain.com/install.php
```

### Docker Installation

```bash
# Quick setup with Docker Compose
docker-compose up -d
```

---

## 📚 Documentation

Detailed documentation coming soon:

- **Installation Guide** - Step-by-step installation instructions
- **User Manual** - Forum management and usage
- **Plugin Development** - Create your own plugins
- **Theme Development** - Design custom themes
- **API Reference** - RESTful API documentation

---

## 🤝 Contributing

Zunvo is an open-source project that grows with community contributions. We welcome all kinds of contributions!

### How Can You Contribute?

- 🐛 **Report Bugs** - Open issues for bug reports
- 💡 **Suggest Features** - Share new feature ideas
- 📝 **Documentation** - Improve documentation
- 🌍 **Translation** - Add new languages
- 💻 **Code Contribution** - Submit pull requests

### Development Environment Setup

```bash
# Fork and clone the repository
git clone https://github.com/yourusername/zunvo.git
cd zunvo

# Create a new branch
git checkout -b feature/amazing-feature

# Make your changes and commit
git commit -m "feat: amazing new feature"

# Push your branch
git push origin feature/amazing-feature

# Create a Pull Request
```

### Code Standards

- Use PSR-2 code style
- Write meaningful commit messages
- Write tests for each feature
- Keep documentation up to date

---

## 🎨 Plugin & Theme Development

One of Zunvo's most powerful features is its extensible architecture.

### Plugin Development

```php
// plugins/my-plugin/MyPlugin.php
class MyPlugin {
    public function __construct() {
        // Register hooks
        Hook::register('before_post_create', [$this, 'beforePostCreate']);
    }
    
    public function beforePostCreate($data) {
        // Perform your custom operations
        return $data;
    }
}
```

### Theme Development

```
themes/my-theme/
├── theme.json          # Theme information
├── style.css          # Main CSS
├── views/             # View files
│   ├── home.php
│   └── topic.php
└── assets/            # Visual assets
```

**For plugin and theme developers:**
- ✅ You can sell paid plugins/themes
- ✅ You can use your own license
- ✅ You can use in commercial projects

---

## 💬 Community & Support

- 💬 **Discussions:** [GitHub Discussions](https://github.com/yourusername/zunvo/discussions)
- 🐛 **Bug Reports:** [GitHub Issues](https://github.com/yourusername/zunvo/issues)
- 📧 **Email:** info@zunvo.org
- 🌐 **Website:** https://zunvo.org (coming soon)

---

## 📊 Project Statistics

```
Lines of Code:    ~15,000 (target)
Number of Files:  ~150+
Languages:        PHP, JavaScript, CSS
Development Time: 6+ months (ongoing)
Contributors:     Waiting...
```

---

## 🙏 Acknowledgments

Zunvo is inspired by these amazing open-source projects:

- **phpBB** - Open source forum pioneers
- **MyBB** - Flexible and customizable structure
- **Flarum** - Modern user experience
- **Discourse** - Advanced features

---

## 📜 License

Zunvo is licensed under the **GNU General Public License v3.0**.

### What Does This Mean?

✅ **Permissions:**
- Commercial use
- Modification
- Distribution
- Patent use
- Private use

❌ **Conditions:**
- Modified versions must also be licensed under GPL v3
- Source code must be open
- Changes must be documented
- You must use the same license

🔒 **Limitations:**
- No liability
- No warranty

**Important:** Zunvo itself is licensed under GPL v3, but **plugins and themes** you develop can use their own license and can be sold commercially. This is the same model used by WordPress, Joomla, and similar systems.

You can find the full text of the license in the [LICENSE](LICENSE) file.

---

## 🌟 Stargazers

Don't forget to give a ⭐ to support the project!

[![Stargazers over time](https://starchart.cc/bnsware/Zunvo.svg?variant=adaptive)](https://starchart.cc/bnsware/Zunvo)

---

<div align="center">

**Build powerful platforms for communities with Zunvo** 🚀

Made with ❤️ by the bnsware

[Website](https://zunvo.org) • [Twitter](https://twitter.com/zunvo) • [Discord](https://discord.gg/zunvo)

© 2025 Zunvo. GPL v3 License.

</div>
