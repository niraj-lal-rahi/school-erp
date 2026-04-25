import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  build: {
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (!id.includes('node_modules')) {
            return undefined;
          }

          if (id.includes('/node_modules/@babel/')) {
            return 'react-vendor';
          }

          if (id.includes('@mui/icons-material')) {
            return 'mui-icons';
          }

          if (id.includes('@mui/material') || id.includes('@emotion')) {
            return 'mui-core';
          }

          if (id.includes('@reduxjs/toolkit') || id.includes('react-redux')) {
            return 'redux';
          }

          if (id.includes('react-router') || id.includes('@remix-run')) {
            return 'router';
          }

          if (id.includes('axios')) {
            return 'axios';
          }

          if (id.includes('react') || id.includes('scheduler')) {
            return 'react-vendor';
          }

          return undefined;
        },
      },
    },
  },
  server: {
    port: 5173,
  },
  resolve: {
    alias: {
      '@': '/src',
    },
  },
});
