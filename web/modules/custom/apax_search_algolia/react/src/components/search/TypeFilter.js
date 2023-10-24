import React from 'react';
import { useStats } from "react-instantsearch-hooks";
import { RefinementList } from "react-instantsearch-hooks-web";

function TypeFilter() {
  const { nbHits } = useStats();
  if (nbHits === 0) return null;

  return (
    <>
      <div className="h4 mb-3">Filter by Type</div>
      <RefinementList attribute="type" sortBy={["name"]} limit={999} />
    </>
  );
}

export default TypeFilter;
