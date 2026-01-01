# Pre-commit Setup Complete

## Overview
A comprehensive pre-commit system has been successfully implemented for the MachMit!Haus Goslar website project. The system automatically formats and lints all source files before every commit.

## Tools Configured

### JavaScript & JSON
- **Prettier** (v3.1.1): Automatic code formatting
- **ESLint** (v8.55.0): JavaScript linting with CommonJS configuration

### CSS/SCSS
- **Prettier**: Code formatting  
- **Stylelint**: CSS linting and style enforcement

### PHP
- **PHP-CS-Fixer**: PSR-2/PSR-12 code formatting
- **PHPCS**: Code style checking

## Available NPM Scripts

```bash
# Format all files
npm run format

# Format specific file types
npm run format:js    # JavaScript/TypeScript/JSON
npm run format:css   # CSS/SCSS 
npm run format:php   # PHP files

# Lint all files
npm run lint

# Lint specific file types
npm run lint:js      # JavaScript/TypeScript
npm run lint:css     # CSS/SCSS
npm run lint:php     # PHP files

# Run pre-commit manually (format + lint)
npm run pre-commit
```

## Available Composer Scripts

```bash
# Format PHP files
composer run format

# Check PHP format (dry run)
composer run format:check

# Lint PHP files
composer run lint

# Fix PHP lint issues
composer run lint:fix
```

## Git Integration

Pre-commit hooks are automatically installed when you run:
```bash
npm install
```

The hooks will automatically run on files you stage for commit, ensuring code quality standards are maintained.

### What Gets Checked

- **site/**/*.{js,ts}**: JavaScript/TypeScript files in site directory
- **public/**/*.{js,ts}**: JavaScript/TypeScript files in public directory  
- **public/**/*.{css,scss}**: Stylesheets in public directory
- **site/**/*.php**: PHP files in site directory
- **\*.json**: JSON configuration files

### Ignored Directories

The following directories are excluded from linting/formatting:
- `node_modules/`
- `vendor/`
- `kirby/`
- `storage/`
- `public/media/` and `media/`
- Test files (`**/Tests/**`, `**/tests/**`)
- Minified files (`*.min.js`, `*.min.css`)

## Configuration Files

- `.prettierrc` - Prettier formatting rules
- `.stylelintrc.json` - CSS linting rules  
- `eslint.config.js` - JavaScript linting rules
- `.php-cs-fixer.php` - PHP formatting rules

## Troubleshooting

If the pre-commit hook fails, you can:

1. Run formatting manually: `npm run format`
2. Check specific issues: `npm run lint`
3. Fix issues and re-commit

To bypass hooks (not recommended):
```bash
git commit --no-verify
```