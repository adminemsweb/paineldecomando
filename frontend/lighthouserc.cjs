module.exports = {
  ci: {
    collect: {
      startServerCommand: 'npm run preview:ci',
      startServerReadyPattern: 'Local:',
      startServerReadyTimeout: 30000,
      numberOfRuns: 3,
      url: [
        'http://127.0.0.1:4173/',
        'http://127.0.0.1:4173/produtos',
        'http://127.0.0.1:4173/produtos/painel-inversor-cfw300-1cv-220v-mono',
      ],
      settings: {
        chromeFlags: '--headless --no-sandbox --disable-dev-shm-usage',
      },
    },
    assert: {
      assertions: {
        'categories:performance': ['warn', { minScore: 0.7 }],
        'categories:accessibility': ['warn', { minScore: 0.85 }],
        'categories:best-practices': ['warn', { minScore: 0.85 }],
        'categories:seo': ['warn', { minScore: 0.9 }],
        'largest-contentful-paint': ['warn', { maxNumericValue: 4000 }],
        'cumulative-layout-shift': ['warn', { maxNumericValue: 0.25 }],
        'total-blocking-time': ['warn', { maxNumericValue: 600 }],
      },
    },
    upload: {
      target: 'filesystem',
      outputDir: './reports/lighthouse',
    },
  },
};
