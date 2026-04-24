import DownloadOutlinedIcon from '@mui/icons-material/DownloadOutlined';
import { IconButton } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { financeApi } from '../services/financeApi';
import { fetchReceipts } from '../store/financeSlice';

export function ReceiptsPage() {
  const dispatch = useAppDispatch();
  const { receipts, receiptsPagination, loading } = useAppSelector((state) => state.finance);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchReceipts({
      page,
      search,
      per_page: 12,
    }));
  }, [dispatch, page, search]);

  async function handleDownload(receiptId) {
    const response = await financeApi.downloadReceipt(receiptId);
    const downloadUrl = response.data?.data?.download_url;
    if (downloadUrl) {
      window.open(downloadUrl, '_blank', 'noopener,noreferrer');
    }
  }

  return (
    <AppDataTable
      title="Receipts"
      columns={[
        { key: 'receipt_no', header: 'Receipt No' },
        { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
        { key: 'payment', header: 'Payment', render: (row) => row.payment?.payment_no || 'N/A' },
        { key: 'receipt_date', header: 'Receipt Date' },
        { key: 'amount', header: 'Amount' },
        {
          key: 'actions',
          header: 'Actions',
          render: (row) => (
            <IconButton color="primary" onClick={() => handleDownload(row.id)}>
              <DownloadOutlinedIcon />
            </IconButton>
          ),
        },
      ]}
      rows={receipts}
      loading={loading}
      searchValue={search}
      onSearchChange={(value) => {
        setSearch(value);
        setPage(1);
      }}
      pagination={{
        page,
        totalPages: receiptsPagination.totalPages,
        onPageChange: setPage,
      }}
      emptyState="No receipts found."
    />
  );
}
