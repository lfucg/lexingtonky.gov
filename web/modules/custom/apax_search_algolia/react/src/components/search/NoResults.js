import React from 'react';
import { useInstantSearch } from 'react-instantsearch-hooks-web';

export const NoResultsBoundary = ({ children, fallback }) => {
  const { results } = useInstantSearch();

  // The `__isArtificial` flag makes sure not to display the No Results message
  // when no hits have been returned.
  if (!results.__isArtificial && results.nbHits === 0) {
    return (
      <>
        {fallback}
        <div hidden>{children}</div>
      </>
    );
  }

  return children;
};

export const NoResults = () => {
  const { indexUiState } = useInstantSearch();

  return (
    <div>
      <p>
        Your search for "<strong>{indexUiState.query}</strong>" produced no
        results. Please try searching again using a different term.
      </p>
    </div>
  );
};
