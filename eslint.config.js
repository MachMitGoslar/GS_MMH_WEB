const js = require('@eslint/js');

module.exports = [
  js.configs.recommended,
  {
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'script',
      globals: {
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        module: 'readonly',
        require: 'readonly',
        process: 'readonly',
        mapboxgl: 'readonly',
      },
    },
    rules: {
      'no-console': 'warn',
      'no-debugger': 'error',
      'no-unused-vars': 'warn',
      'prefer-const': 'error',
      'no-var': 'error',
    },
    ignores: [
      'node_modules/**',
      'vendor/**',
      'kirby/**',
      'storage/**',
      'public/media/**',
      'media/**',
      '*.min.js',
      '**/Tests/**/*.js',
      '**/tests/**/*.js',
      'site/plugins/**',
    ],
  },
];
