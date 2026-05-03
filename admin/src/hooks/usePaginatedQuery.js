import { startTransition, useDeferredValue, useEffect, useMemo } from 'react';
import { useAppDispatch } from './redux';

export function usePaginatedQuery({
  queryAction,
  params = {},
  page = 1,
  perPage = 20,
  enabled = true,
}) {
  const dispatch = useAppDispatch();
  const deferredParams = useDeferredValue(params);
  const paramsSignature = useMemo(() => JSON.stringify(deferredParams), [deferredParams]);

  useEffect(() => {
    if (!enabled) {
      return;
    }

    dispatch(queryAction({
      ...deferredParams,
      page,
      per_page: perPage,
    }));
  }, [dispatch, enabled, page, perPage, paramsSignature, queryAction, deferredParams]);

  function requestPage(nextPage) {
    startTransition(() => {
      dispatch(queryAction({
        ...deferredParams,
        page: nextPage,
        per_page: perPage,
      }));
    });
  }

  return {
    requestPage,
  };
}
