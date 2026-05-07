import React from 'react';

function formatTime(value) {
  if (!value) {
    return 'Unknown time';
  }

  try {
    return new Date(value).toLocaleString();
  } catch (error) {
    return value;
  }
}

export default function AuditTimeline({ items = [] }) {
  if (!items.length) {
    return (
      <div className="sa-card">
        <h3>Audit Timeline</h3>
        <p>No audit activity recorded yet.</p>
      </div>
    );
  }

  return (
    <div className="sa-card">
      <h3>Audit Timeline</h3>
      <div className="sa-timeline">
        {items.map((item) => (
          <article key={item.id ?? `${item.action}-${item.created_at}`} className="sa-timeline-item">
            <div className="sa-timeline-item__header">
              <strong>{item.action ?? 'Action'}</strong>
              <span>{formatTime(item.created_at)}</span>
            </div>
            <p>{item.description ?? 'No description provided.'}</p>
            {item.module ? <small>Module: {item.module}</small> : null}
          </article>
        ))}
      </div>
    </div>
  );
}
