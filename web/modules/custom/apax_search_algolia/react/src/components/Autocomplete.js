import React from "react";

// helpers
import { searchClient } from "../utils/searchClient";

// custom Components
import { INDEX_ID } from "../utils/constants";
import { useEffect, useMemo, useRef, useState } from "react";
import { createAutocomplete } from "@algolia/autocomplete-core";
import { getAlgoliaResults } from "@algolia/autocomplete-preset-algolia";
import { debounced } from "../utils/helpers";

const Autocomplete = (props) => {
  const [autocompleteState, setAutocompleteState] = useState({
    collections: [],
    completion: null,
    context: {},
    isOpen: false,
    query: "",
    activeItemId: null,
    status: "idle",
  });

  const autocomplete = useMemo(
    () =>
      createAutocomplete({
        onStateChange({ state }) {
          setAutocompleteState(state);
        },
        insights: true,
        getSources() {
          return debounced([
            {
              sourceId: "all",
              getItems({ query }) {
                return getAlgoliaResults({
                  searchClient,
                  queries: [
                    {
                      indexName: INDEX_ID,
                      query,
                      params: {
                        hitsPerPage: 3,
                      },
                    },
                  ],
                });
              },
              getItemUrl({ item }) {
                return item.path;
              },
            },
          ]);
        },
        ...props,
      }),
    [props]
  );
  const inputRef = useRef(null);
  const formRef = useRef(null);
  const panelRef = useRef(null);
  const { getEnvironmentProps } = autocomplete;

  useEffect(() => {
    if (!formRef.current || !panelRef.current || !inputRef.current) {
      return undefined;
    }

    const { onTouchStart, onTouchMove, onMouseDown } = getEnvironmentProps({
      formElement: formRef.current,
      inputElement: inputRef.current,
      panelElement: panelRef.current,
    });

    window.addEventListener("mousedown", onMouseDown);
    window.addEventListener("touchstart", onTouchStart);
    // window.addEventListener("touchmove", onTouchMove);

    return () => {
      window.removeEventListener("mousedown", onMouseDown);
      window.removeEventListener("touchstart", onTouchStart);
      // window.removeEventListener("touchmove", onTouchMove);
    };
  }, [getEnvironmentProps, autocompleteState.isOpen]);

  const handleSubmit = (e) => {
    e.preventDefault();
    window.location.href = `/search?${INDEX_ID}[query]=${inputRef.current.value}`;
  };

  const handleSearchFocus = () => {
    if (window.innerWidth <= 768) {
      inputRef.current.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  };

  return (
    <div {...autocomplete.getRootProps({})}>
      <form
        className="usa-search lex-search"
        ref={formRef}
        {...autocomplete.getFormProps({ inputElement: inputRef.current })}
        onSubmit={handleSubmit}
      >
        <div className="input-group mb-3">
          <label htmlFor="search" className="visually-hidden">
            Search
          </label>
          <input
            title="Enter the terms you wish to search for."
            id="search"
            type="search"
            ref={inputRef}
            {...autocomplete.getInputProps({ inputElement: inputRef.current })}
            className="form-search"
            placeholder="Search for..."
            onFocus={handleSearchFocus}
          />
          <div className="input-group-append">
            <button className="btn btn-outline-secondary" type="submit">
              <span className="visually-hidden">Search</span>
              <i className="fa fa-search lex-icon"></i>
            </button>
          </div>
        </div>
      </form>
      <div
        ref={panelRef}
        className={`autocomplete-wrapper ${
          autocompleteState.isOpen ? "open" : "closed"
        }`}
        {...autocomplete.getPanelProps({})}
      >
        <div className="autocomplete__stats">
          <div className="ais-Stats-text">
            Showing {autocompleteState?.collections?.[0]?.items.length} results
          </div>
          <p>
            Click the search icon or 'view more results' to see all results.
          </p>
        </div>
        {autocompleteState.collections.map((collection, index) => {
          const { source, items } = collection;

          return (
            <div key={`source-${index}`} className="autocomplete__block">
              {items.length > 0 ? (
                <ul
                  className="autocomplete__list"
                  {...autocomplete.getListProps()}
                >
                  {items.map((item) => {
                    return (
                      <li
                        key={item.objectID}
                        role="option"
                        className="autocomplete__item"
                        {...autocomplete.getItemProps({ item, source })}
                      >
                        <a
                          className="autocomplete__item__title"
                          href={item.url}
                        >
                          {item.title}
                        </a>
                        <div>{item.type}</div>
                      </li>
                    );
                  })}
                </ul>
              ) : (
                <p className="header-search__no-results">
                  Sorry, no items match your request. Please try another
                  keyword.
                </p>
              )}
            </div>
          );
        })}
        <button className="autocomplete__button" onClick={handleSubmit}>
          View more results
        </button>
      </div>
    </div>
  );
};

export default Autocomplete;
