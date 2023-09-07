import React, { useMemo } from "react";
// package functions and components
import {
  Configure,
  InstantSearch,
} from "react-instantsearch-hooks-web";

// helpers
import { searchClient } from "../utils/searchClient";

// custom Components
import { INDEX_ID } from "../utils/constants";
import Global from "./results/Global";
import CustomStats from "./search/CustomStats";
import Hits from "./search/Hits";
import { NoResults, NoResultsBoundary } from "./search/NoResults";

import SearchBlock from "./search/SearchBlock";
import TypeFilter from "./search/TypeFilter";

const App = () => {
  const currentTimestamp = useMemo(() => {
    let currentDate = new Date();
    currentDate.setHours(0, 0, 0, 0);
    return currentDate.getTime() / 1000;
  }, []);

  return (
    <InstantSearch searchClient={searchClient} indexName={INDEX_ID} routing>
      <Configure
        hitsPerPage={25}
        filters={`status:true AND date_filter > ${currentTimestamp}`}
      />
      <SearchBlock placeholder="Keyword Search" />
      <div className="search-results">
        <div className="search-results__main">
          <CustomStats />
          <NoResultsBoundary fallback={<NoResults />}>
            <Hits Component={Global} />
          </NoResultsBoundary>
        </div>
        <div className="search-results__facets">
          <TypeFilter />
        </div>
      </div>
    </InstantSearch>
  );
};

export default App;
