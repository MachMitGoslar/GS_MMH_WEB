# Development Setup Guide

## Quick Start

### DDEV (Recommended)
```bash
# Start the development environment
ddev start

# Open in browser
ddev launch

# Stop when done
ddev stop
```

### Alternative: Local PHP Server
```bash
composer start
# Access at http://localhost:8000
```

---

## DDEV Setup

### Overview
This project uses [DDEV](https://ddev.com/) for local development, providing a containerized environment with PHP 8.4, Nginx, and MariaDB.

### Prerequisites
- **Docker Desktop** (v4.25+) or compatible container runtime (OrbStack, Colima)
- **DDEV** v1.24+ installed via Homebrew: `brew install ddev/ddev/ddev`

### Configuration
The DDEV configuration is in `.ddev/config.yaml`:

| Setting | Value |
|---------|-------|
| Project Name | `GS-MMH-WEB` |
| Type | PHP |
| Docroot | `./public` |
| PHP Version | 8.4 |
| Webserver | nginx-fpm |
| Database | MariaDB 10.11 |
| Xdebug | Enabled |

### URLs
- **Site**: https://gs-mmh-web.ddev.site
- **Mailpit**: https://gs-mmh-web.ddev.site:8026
- **PHPMyAdmin**: `ddev launch -p` (or use TablePlus/other client)

### Common Commands

```bash
# Start/stop environment
ddev start
ddev stop
ddev restart

# Open project in browser
ddev launch

# View project info and URLs
ddev describe

# SSH into web container
ddev ssh

# Run Composer commands
ddev composer install
ddev composer update

# Database access
ddev mysql                    # MySQL CLI
ddev export-db > backup.sql   # Export database
ddev import-db < backup.sql   # Import database

# Xdebug control (toggle for performance)
ddev xdebug on               # Enable Xdebug
ddev xdebug off              # Disable Xdebug
ddev xdebug status           # Check status

# View logs
ddev logs                    # All logs
ddev logs -f                 # Follow logs
ddev logs -s web             # Web container only

# Refresh/rebuild
ddev restart                 # Restart containers
ddev debug rebuild           # Rebuild images (after config changes)
```

### Xdebug Configuration
Xdebug is pre-configured and enabled by default. For VS Code:

1. Install the **PHP Debug** extension
2. Use the existing `.vscode/launch.json` configuration
3. Set breakpoints and start debugging

**Performance tip**: Disable Xdebug when not debugging:
```bash
ddev xdebug off
```

### Custom PHP Settings
Add custom PHP settings in `.ddev/php/`:
```bash
# Create custom ini file
echo "memory_limit = 512M" > .ddev/php/custom.ini
ddev restart
```

### Environment Variables
Add environment variables in `.ddev/config.yaml`:
```yaml
web_environment:
  - KIRBY_DEBUG=true
  - MY_API_KEY=secretvalue
```

Or create a `.ddev/.env` file for sensitive values (add to `.gitignore`).

---

## Local PHP Server (Alternative)

If you prefer not to use Docker/DDEV, you can use the local PHP development server.

### Prerequisites
- PHP 8.4 installed (via Homebrew: `brew install php@8.4`)
- Composer installed

### Setup
```bash
# Install dependencies
composer install

# Start development server
composer start

# Access at http://localhost:8000
```

### Xdebug with Local PHP
Xdebug is configured in the Homebrew PHP installation:
- VS Code launch configurations available in `.vscode/launch.json`
- Test with: `/opt/homebrew/opt/php@8.4/bin/php debug_test.php`

---

## Project Structure

```
GS_MMH_WEB/
├── .ddev/                  # DDEV configuration
│   ├── config.yaml         # Main DDEV config
│   ├── php/                # Custom PHP ini files
│   └── nginx_full/         # Custom Nginx config
├── .vscode/                # VS Code settings
│   ├── launch.json         # Debug configurations
│   └── settings.json       # PHP settings
├── content/                # Kirby content (file-based CMS)
├── public/                 # Web root (docroot)
│   ├── assets/             # Static assets (CSS, JS, images)
│   └── index.php           # Entry point
├── site/                   # Kirby site code
│   ├── blueprints/         # Panel blueprints
│   ├── config/             # Kirby configuration
│   ├── controllers/        # Page controllers
│   ├── plugins/            # Kirby plugins
│   ├── snippets/           # Reusable snippets
│   └── templates/          # Page templates
├── storage/                # Kirby storage (cache, sessions)
├── vendor/                 # Composer dependencies
├── composer.json           # PHP dependencies
└── kirby/                  # Kirby CMS core
```

---

## Database

### Kirby CMS
Kirby is a **file-based CMS** and does not require a database for content storage. All content is stored in the `content/` directory as text files.

### MariaDB (via DDEV)
A MariaDB database is available if needed for custom functionality:

```bash
# Access database CLI
ddev mysql

# Connection details (for external tools)
Host: 127.0.0.1
Port: Run `ddev describe` to see the port
Database: db
Username: db
Password: db
```

---

## Debugging

### VS Code Setup
1. Install **PHP Debug** extension
2. Open the Debug panel (Cmd+Shift+D)
3. Select "Listen for Xdebug" configuration
4. Start debugging (F5)
5. Set breakpoints in your PHP files
6. Refresh the browser to trigger breakpoints

### Launch Configurations
Available in `.vscode/launch.json`:
- **Listen for Xdebug**: Standard debugging
- **Launch currently open script**: Debug current file directly

### Troubleshooting Xdebug
```bash
# Check Xdebug status
ddev xdebug status

# Enable if disabled
ddev xdebug on

# Check PHP info
ddev exec php -i | grep xdebug

# View Xdebug logs
ddev logs -s web | grep -i xdebug
```

---

## Troubleshooting

### DDEV Issues

**Container won't start**
```bash
ddev poweroff          # Stop all DDEV projects
ddev start             # Try again
```

**Port conflicts**
```bash
ddev describe          # Check current ports
# Edit .ddev/config.yaml to change ports if needed
```

**Rebuild after config changes**
```bash
ddev debug rebuild
ddev start
```

**Docker issues**
```bash
# Restart Docker Desktop
# Then:
ddev restart
```

### Kirby Issues

**"Home page does not exist"**
- Ensure `content/home/` directory exists
- Check file permissions in `content/`

**Panel access issues**
- Access panel at `/panel`
- Check `site/config/config.php` for panel settings

### Performance

**Slow file operations (macOS)**
DDEV uses Mutagen for file sync on macOS. If experiencing issues:
```bash
ddev mutagen status    # Check sync status
ddev mutagen sync      # Force sync
```

---

## Additional Tools

### Mailpit (Email Testing)
DDEV includes Mailpit for catching outgoing emails:
- Access at: https://gs-mmh-web.ddev.site:8026
- All emails sent from PHP are captured here

### Database Management
```bash
# Export database
ddev export-db > backup.sql.gz

# Import database
ddev import-db < backup.sql.gz

# Use phpMyAdmin
ddev launch -p
```

### Running Tests
```bash
# Run PHPUnit tests (if configured)
ddev exec vendor/bin/phpunit

# Run specific test
ddev exec vendor/bin/phpunit tests/MyTest.php
```

---

## Files Reference

### Configuration Files
| File | Purpose |
|------|---------|
| `.ddev/config.yaml` | DDEV project configuration |
| `.vscode/launch.json` | VS Code debug configurations |
| `.vscode/settings.json` | VS Code PHP settings |
| `composer.json` | PHP dependencies and scripts |
| `site/config/config.php` | Kirby CMS configuration |

### Useful Scripts
```bash
# Start local PHP server
composer start

# Clear Kirby cache
rm -rf storage/cache/*
```
