import React from "react";
import { Snippet } from "react-instantsearch-hooks-web";
import { customFormatDate } from "../../utils/helpers";

const DATE_PREFIXES = {
  'Page': 'Updated on ',
  'Boards and Commissions': 'Updated on ',
  'News': 'Published on ',
  'Meeting': 'Starts on ',
  'Event': 'Starts on ',
}

const Global = ({ hit }) => {
  const { url = "#", title, type, display_date, all_day = false } = hit;

  let date = ''
  if (display_date && !all_day) {
    date = `${DATE_PREFIXES[type]}${customFormatDate(display_date)}`;
  } else if (all_day) {
    date = `All Day on ${customFormatDate(display_date, all_day)}`;
  }

  return (
    <article className="search-results__item">
      <a className="search-results__link" href={url} />
      <div className="search-results__info row">
        <div className="search-results__meta col-9 mb-3">
          <div className="search-results__title">{title}</div>
          <div className="search-results__date">{date}</div>
        </div>
        <div className="search-results__type col-3">{type}</div>
      </div>
      <Snippet hit={hit} attribute="custom_rendered_item" />
    </article>
  );
};

export default Global;
