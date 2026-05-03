import { useMemo, useState } from 'react';
import { Box, Paper, Stack, Typography } from '@mui/material';

export function VirtualizedTable({
  title,
  columns,
  rows,
  height = 420,
  rowHeight = 52,
  overscan = 6,
  emptyState = 'No records found.',
}) {
  const [scrollTop, setScrollTop] = useState(0);

  const visibleRange = useMemo(() => {
    const viewportRows = Math.ceil(height / rowHeight);
    const startIndex = Math.max(0, Math.floor(scrollTop / rowHeight) - overscan);
    const endIndex = Math.min(rows.length, startIndex + viewportRows + (overscan * 2));

    return { startIndex, endIndex };
  }, [height, overscan, rowHeight, rows.length, scrollTop]);

  const visibleRows = rows.slice(visibleRange.startIndex, visibleRange.endIndex);
  const topSpacerHeight = visibleRange.startIndex * rowHeight;
  const bottomSpacerHeight = Math.max(0, (rows.length - visibleRange.endIndex) * rowHeight);

  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Typography variant="h5" mb={2}>{title}</Typography>

      <Box
        sx={{ border: '1px solid rgba(20,33,61,0.08)', borderRadius: 2, overflow: 'hidden' }}
      >
        <Box
          sx={{
            display: 'grid',
            gridTemplateColumns: `repeat(${columns.length}, minmax(0, 1fr))`,
            px: 2,
            py: 1.5,
            bgcolor: 'rgba(20,33,61,0.04)',
            fontWeight: 700,
          }}
        >
          {columns.map((column) => (
            <Box key={column.key}>{column.header}</Box>
          ))}
        </Box>

        <Box
          sx={{ height, overflowY: 'auto' }}
          onScroll={(event) => setScrollTop(event.currentTarget.scrollTop)}
        >
          {!rows.length ? (
            <Typography py={6} textAlign="center" color="text.secondary">
              {emptyState}
            </Typography>
          ) : (
            <Box>
              <Box sx={{ height: topSpacerHeight }} />
              {visibleRows.map((row) => (
                <Stack
                  key={row.id}
                  direction="row"
                  sx={{
                    display: 'grid',
                    gridTemplateColumns: `repeat(${columns.length}, minmax(0, 1fr))`,
                    px: 2,
                    alignItems: 'center',
                    minHeight: rowHeight,
                    borderTop: '1px solid rgba(20,33,61,0.06)',
                  }}
                >
                  {columns.map((column) => (
                    <Box key={column.key}>
                      {column.render ? column.render(row) : row[column.key]}
                    </Box>
                  ))}
                </Stack>
              ))}
              <Box sx={{ height: bottomSpacerHeight }} />
            </Box>
          )}
        </Box>
      </Box>
    </Paper>
  );
}
