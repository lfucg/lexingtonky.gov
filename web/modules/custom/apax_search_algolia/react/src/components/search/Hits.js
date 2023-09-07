import React from "react";
import { useInfiniteHits } from "react-instantsearch-hooks-web";

const Hits = ({ Component, refine, clear }) => {
  // loop through all results and use the passed in Component to render
  const { 
    hits,
    sendEvent,
    showPrevious,
    showMore,
    isFirstPage,
    isLastPage,
  } = useInfiniteHits();

  let renderedHits = hits.map((hit, idx) => (
    <Component key={hit.objectID} hit={hit} refine={refine} clear={clear} />
  ));

  return (
    <>
      <div className="search-page__results">{renderedHits}</div>
      <button
        onClick={showMore}
        disabled={isLastPage}
        className="btn btn-outline-secondary search-page__show-more"
      >
        Show more results
      </button>
    </>
  );
};

export default Hits;
