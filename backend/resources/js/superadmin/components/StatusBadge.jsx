import React from 'react';

const toneMap = {
  active: 'success',
  connected: 'success',
  paid: 'success',
  trial: 'info',
  queued: 'info',
  processing: 'info',
  suspended: 'warning',
  failed: 'danger',
  cancelled: 'danger',
  expired: 'danger',
};

export default function StatusBadge({ status }) {
  const tone = toneMap[String(status || '').toLowerCase()] || 'neutral';

  return (
    <span className={`sa-badge sa-badge--${tone}`}>
      {status || 'unknown'}
    </span>
  );
}
