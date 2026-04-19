# School ERP Admin

React + Vite admin panel for the Student module.

## Folder Structure

```text
admin/
  src/
    api/
    app/
    components/
      common/
      layout/
    features/
      auth/
      students/
        components/
        pages/
        services/
        store/
    hooks/
    layouts/
    routes/
    theme/
    utils/
```

## Features Included

- छात्र सूची with pagination, search, and lifecycle filter
- Add and edit student form
- Student profile page
- Document upload panel
- Role-based rendering through `PermissionGate`
- Redux Toolkit state management
- Axios API service layer
- Reusable table component

## Environment

Create `.env`:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1
```

## Suggested Install

```bash
npm install
npm run dev
```
