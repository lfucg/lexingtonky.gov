import React from "react";
import {
  useClearRefinements,
  useCurrentRefinements,
  useSearchBox,
  useStats,
} from "react-instantsearch-hooks-web";

const CustomStats = () => {
  const { items } = useCurrentRefinements();
  const { refine } = useClearRefinements();
  const { clear } = useSearchBox();
  const { nbHits, query } = useStats();

  return (
    <div className="search-results__stats mb-3">
      <em>
        {nbHits.toLocaleString()} results{query && <> for <q>{query}</q></>}.{' '}
      </em>
      {(query || items.length > 0) && (
        <button
          className="btn btn-outline-secondary"
          onClick={() => {
            refine();
            clear();
          }}
          style={{ marginLeft: "12px" }}
        >
          Reset Search
          <i
            className="fa fa-times lex-icon"
            aria-hidden="true"
            style={{ marginLeft: "12px" }}
          ></i>
        </button>
      )}
    </div>
  );
};

export default CustomStats;
