import React from "react";
import { useEffect, useRef } from "react";
import { useSearchBox } from "react-instantsearch-hooks-web";

const SearchBlock = ({ placeholder }) => {
  const { refine, query } = useSearchBox();
  const searchEl = useRef(null);

  useEffect(() => {
    if (searchEl.current) {
      searchEl.current.value = query || ""; // Update the value when query changes
    }
  }, [query]);

  const handleFormSubmit = (e) => {
    e.preventDefault();
    refine(searchEl.current.value);
  };

  const handleClearSearch = (e) => {
    e.preventDefault();
    refine("");
    searchEl.current.focus();
  };

  return (
    <div className="search-page__search">
      <form
        onSubmit={handleFormSubmit}
        className="usa-search lex-search"
        role="search"
      >
        <div className="input-group mb-3">
          <label htmlFor="search" className="visually-hidden">
            Search
          </label>
          <input
            title="Enter the terms you wish to search for."
            id="searchInput"
            type="search"
            aria-autocomplete="both"
            aria-labelledby="autocomplete-1-label"
            enterKeyHint="search"
            spellCheck="false"
            placeholder="Search for..."
            className="form-search"
            ref={searchEl}
          />
          <div className="input-group-append">
            <button
              type="submit"
              className="btn"
              aria-label="Search"
            >
              Search
            </button>
          </div>
        </div>
      </form>
    </div>
  );
};

export default SearchBlock;
