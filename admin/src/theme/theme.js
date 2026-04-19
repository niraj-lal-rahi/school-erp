import { createTheme } from '@mui/material/styles';

export const theme = createTheme({
  palette: {
    primary: {
      main: '#0b6e4f',
    },
    secondary: {
      main: '#f4a261',
    },
    background: {
      default: '#eef3f8',
      paper: '#ffffff',
    },
    success: {
      main: '#2a9d8f',
    },
    warning: {
      main: '#e9c46a',
    },
    error: {
      main: '#d62828',
    },
  },
  shape: {
    borderRadius: 18,
  },
  typography: {
    fontFamily: '"Segoe UI", "Helvetica Neue", sans-serif',
    h4: {
      fontWeight: 700,
    },
    h5: {
      fontWeight: 700,
    },
    button: {
      textTransform: 'none',
      fontWeight: 600,
    },
  },
  components: {
    MuiPaper: {
      styleOverrides: {
        root: {
          borderRadius: 18,
        },
      },
    },
    MuiButton: {
      styleOverrides: {
        root: {
          borderRadius: 14,
          paddingInline: 16,
        },
      },
    },
  },
});
