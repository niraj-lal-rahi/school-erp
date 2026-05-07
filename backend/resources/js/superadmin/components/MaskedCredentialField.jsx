import React from 'react';

export default function MaskedCredentialField({ label, value, helperText }) {
  return (
    <div className="sa-field">
      <span className="sa-field__label">{label}</span>
      <code className="sa-field__value">{value || 'Not configured'}</code>
      {helperText ? <small className="sa-field__help">{helperText}</small> : null}
    </div>
  );
}
