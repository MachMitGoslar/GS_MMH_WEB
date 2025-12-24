# PHP Debugging Setup for Kirby Application

This document describes the complete debugging setup for your Kirby application using Xdebug and VS Code.

## ✅ Setup Complete

The debugging environment has been successfully configured with:

### Installed Components
- **PHP 8.4.15** (via Homebrew)
- **Xdebug 3.4.7** (PHP debugging extension)
- **VS Code PHP Debug extension** (xdebug.php-debug)

### Configuration Files

#### 1. VS Code Launch Configuration (`.vscode/launch.json`)
```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "log": true,
            "pathMappings": {
                "/Users/stuff/Documents/projects/mmh_neu/GS_MMH_WEB": "${workspaceFolder}"
            }
        },
        {
            "name": "Launch currently open script",
            "type": "php",
            "request": "launch",
            "program": "${file}",
            "cwd": "${fileDirname}",
            "port": 0,
            "runtimeArgs": [
                "-dxdebug.mode=debug",
                "-dxdebug.start_with_request=yes",
                "-dxdebug.client_port=9003"
            ],
            "env": {
                "XDEBUG_CONFIG": "idekey=VSCODE"
            }
        },
        {
            "name": "Launch Built-in web server",
            "type": "php",
            "request": "launch",
            "runtimeArgs": [
                "-dxdebug.mode=debug",
                "-dxdebug.start_with_request=yes",
                "-dxdebug.client_port=9003",
                "-S",
                "localhost:8000",
                "-t",
                "${workspaceRoot}"
            ],
            "program": "",
            "cwd": "${workspaceRoot}",
            "port": 9003,
            "serverReadyAction": {
                "pattern": "Development Server \\(http://localhost:([0-9]+)\\) started",
                "uriFormat": "http://localhost:%s",
                "action": "openExternally"
            }
        }
    ]
}
```

#### 2. VS Code Settings (`.vscode/settings.json`)
```json
{
    "php.executablePath": "/opt/homebrew/opt/php@8.4/bin/php",
    "php.validate.executablePath": "/opt/homebrew/opt/php@8.4/bin/php",
    "php.debug.executablePath": "/opt/homebrew/opt/php@8.4/bin/php"
}
```

#### 3. Xdebug Configuration (in `/opt/homebrew/etc/php/8.4/php.ini`)
```ini
; Xdebug Extension
zend_extension=xdebug.so

; Xdebug Configuration for Step Debugging
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=127.0.0.1
xdebug.idekey=VSCODE
xdebug.log=/tmp/xdebug.log

; Additional development settings
xdebug.var_display_max_depth=3
xdebug.var_display_max_children=128
xdebug.var_display_max_data=512
```

## 🚀 How to Use

### 1. **Script Debugging**
1. Open any PHP file in VS Code
2. Set breakpoints by clicking in the left gutter
3. Press `F5` or go to **Run and Debug** → **Launch currently open script**
4. The debugger will stop at your breakpoints

### 2. **Web Application Debugging**
1. Set breakpoints in your PHP files
2. Go to **Run and Debug** → **Listen for Xdebug**
3. Start the built-in server: **Run and Debug** → **Launch Built-in web server**
4. Access your Kirby app at `http://localhost:8000`
5. The debugger will stop at breakpoints when pages are loaded

### 3. **Kirby Application Debugging**
1. Set breakpoints in your Kirby templates, controllers, or models
2. Start "Listen for Xdebug" configuration
3. Access your Kirby site via web browser
4. Debug through page rendering, data processing, etc.

## 🔧 Troubleshooting

### Check if Xdebug is loaded:
```bash
/opt/homebrew/opt/php@8.4/bin/php -m | grep xdebug
```

### Check Xdebug configuration:
```bash
/opt/homebrew/opt/php@8.4/bin/php -i | grep xdebug
```

### View Xdebug log:
```bash
tail -f /tmp/xdebug.log
```

## 📂 Test Script

A test script is available at `debug_test.php` to verify the setup is working correctly.

## ⚡ Performance Notes

- Xdebug can slow down PHP execution
- Only enable debugging mode when actively debugging
- Consider using development-only Xdebug configuration

## 🔄 Alternative Debugging Options

If you prefer other debugging methods:

1. **Error logging**: Configure `error_log` in php.ini
2. **var_dump() debugging**: Use with output buffering
3. **Kirby's debug mode**: Enable in Kirby config for enhanced error display

Your Kirby application debugging environment is now fully configured and ready to use!