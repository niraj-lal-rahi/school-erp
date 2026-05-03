import { Suspense } from 'react';
import { FullScreenLoader } from './FullScreenLoader';

export function LazyRoute({
  children,
  canLoad = true,
  fallback = <FullScreenLoader />,
  denied = null,
}) {
  if (!canLoad) {
    return denied;
  }

  return <Suspense fallback={fallback}>{children}</Suspense>;
}
