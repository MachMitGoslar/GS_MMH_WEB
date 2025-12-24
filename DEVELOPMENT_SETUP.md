# Development Setup Guide

## Current Working Setup

### PHP Development Server (Currently Active)
The project is currently running with a local PHP development server:
- **Server**: http://localhost:8000  
- **PHP Version**: 8.4.15 (via Homebrew)
- **Xdebug**: Enabled with step debugging
- **Command**: `composer start`

### Debugging Environment
✅ **Fully Configured**
- Xdebug 3.4.7 installed and configured
- VS Code PHP Debug extension installed
- Launch configurations available in `.vscode/launch.json`
- Test script available at `debug_test.php`

### Current Development Workflow
1. Start server: `composer start`
2. Access site: http://localhost:8000
3. Debug: Use VS Code debug panel with configured launch settings

## DDEV Setup (Future Enhancement)

### Status
🔄 **Partially Configured** - Installation complete but Docker compatibility issues

### What's Ready
- DDEV v1.24.10 installed via Homebrew
- Project configuration created (`.ddev/config.yaml`)
- PHP 8.4 and Xdebug enabled in config

### Current Blocker
Docker buildx version compatibility issue:
- Current: buildx v0.11.2 (too old)
- Required: buildx v0.17+
- Docker Desktop: v4.22.1 (needs update)

### DDEV Configuration
```yaml
name: GS-MMH-WEB
type: php
docroot: .
php_version: "8.4"
webserver_type: nginx-fpm
xdebug_enabled: true
database:
    type: mariadb
    version: "10.11"
disable_host_ssh_agent: true
```

### Resolving DDEV Setup

#### Option 1: Update Docker Desktop
1. Update Docker Desktop to latest version (v4.25+)
2. Restart Docker
3. Run `ddev start`

#### Option 2: Alternative Installation
1. Use Docker Desktop alternatives (OrbStack, Colima)
2. Install via package manager with newer Docker

### Benefits of DDEV (Once Working)
- Containerized environment with consistent PHP/MySQL versions
- Isolated from host system PHP
- Easy environment reset and sharing
- Built-in development tools (Mailpit, database management)
- HTTPS support with automatic certificates

## Database Considerations

### Current Setup
The Kirby CMS is currently file-based (no database required for basic operation).

### With DDEV
When DDEV is working, you'll have:
- MariaDB 10.11 available
- Database accessible at `ddev.site:3306` 
- Web interface via `ddev describe`

## Project Structure Notes

### Content Issues
The site currently shows "home page does not exist" - this indicates:
- Missing `content/home/` directory structure, or
- Incorrect Kirby content organization

### File Structure
```
content/
├── home/           # Main homepage content
├── 1_projects/     # Projects section
├── 2_wie-funktioniert-machmit/
├── 3_uber-uns/
└── 4_notes/
```

## Commands Reference

### Current Development
```bash
# Start development server
composer start

# Stop server (Ctrl+C in terminal)

# Debug test
/opt/homebrew/opt/php@8.4/bin/php debug_test.php
```

### Future DDEV Commands
```bash
# Start DDEV environment
ddev start

# Stop DDEV
ddev stop

# View project info
ddev describe

# Open project in browser
ddev launch

# Access database
ddev mysql

# SSH into web container
ddev ssh

# Update configuration
ddev config

# View logs
ddev logs
```

## Troubleshooting

### Current Issues
1. **Xdebug "already loaded" warning**: Cosmetic only, debugging works
2. **Composer deprecation notices**: Cosmetic only, functionality unaffected
3. **Kirby homepage error**: Content structure configuration needed

### DDEV Issues
1. **Docker buildx version**: Update Docker Desktop to resolve
2. **SSH agent errors**: Currently configured to omit SSH agent container

## Next Steps

### Immediate (Current Setup)
1. ✅ Development server working
2. ✅ Debugging environment ready
3. 🔄 Fix Kirby content structure for homepage

### Future (DDEV)
1. Update Docker Desktop
2. Test DDEV startup
3. Migrate development workflow to DDEV
4. Configure project-specific database if needed

## Files Created/Modified

### New Files
- `.vscode/launch.json` - VS Code debugging configurations
- `.vscode/settings.json` - VS Code PHP debugging settings
- `debug_test.php` - Debugging test script
- `DEBUG_SETUP.md` - Debugging setup documentation
- `.ddev/config.yaml` - DDEV project configuration
- `DEVELOPMENT_SETUP.md` - This file

### Modified Files
- `composer.json` - Updated start script with correct PHP path
- `site/snippets/components/ferienpass/csv_helper.php` - Removed Kirby Remote dependency