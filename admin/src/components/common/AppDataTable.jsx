import {
  Box,
  CircularProgress,
  MenuItem,
  Pagination,
  Paper,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TextField,
  Typography,
} from '@mui/material';

export function AppDataTable({
  title,
  columns,
  rows,
  loading,
  searchValue,
  onSearchChange,
  filters = [],
  pagination,
  emptyState = 'No records found.',
}) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack direction={{ xs: 'column', lg: 'row' }} spacing={2} justifyContent="space-between" mb={3}>
        <Box>
          <Typography variant="h5">{title}</Typography>
          <Typography variant="body2" color="text.secondary">
            Search, filter, and page through records in a reusable table shell.
          </Typography>
        </Box>

        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
          <TextField
            size="small"
            label="Search"
            value={searchValue}
            onChange={(event) => onSearchChange(event.target.value)}
            sx={{ minWidth: 220 }}
          />

          {filters.map((filter) => (
            <TextField
              key={filter.key}
              select
              size="small"
              label={filter.label}
              value={filter.value}
              onChange={(event) => filter.onChange(event.target.value)}
              sx={{ minWidth: 180 }}
            >
              {filter.options.map((option) => (
                <MenuItem key={option.value} value={option.value}>
                  {option.label}
                </MenuItem>
              ))}
            </TextField>
          ))}
        </Stack>
      </Stack>

      <TableContainer>
        <Table>
          <TableHead>
            <TableRow>
              {columns.map((column) => (
                <TableCell key={column.key}>{column.header}</TableCell>
              ))}
            </TableRow>
          </TableHead>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={columns.length}>
                  <Stack alignItems="center" py={4}>
                    <CircularProgress size={28} />
                  </Stack>
                </TableCell>
              </TableRow>
            ) : rows.length ? (
              rows.map((row) => (
                <TableRow hover key={row.id}>
                  {columns.map((column) => (
                    <TableCell key={column.key}>
                      {column.render ? column.render(row) : row[column.key]}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={columns.length}>
                  <Typography py={4} textAlign="center" color="text.secondary">
                    {emptyState}
                  </Typography>
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>

      {pagination ? (
        <Stack mt={3} direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" alignItems="center" spacing={2}>
          <Typography variant="body2" color="text.secondary">
            Showing page {pagination.page} of {pagination.totalPages || 1}
          </Typography>
          <Pagination
            page={pagination.page}
            count={pagination.totalPages || 1}
            onChange={(_, page) => pagination.onPageChange(page)}
            color="primary"
          />
        </Stack>
      ) : null}
    </Paper>
  );
}
