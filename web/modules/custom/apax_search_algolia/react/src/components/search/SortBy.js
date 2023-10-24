import { useSortBy } from 'react-instantsearch-hooks-web';

export const SortBy = ({ items }) => {
  const { refine } = useSortBy();
  
  return (
    <div className="l-box--md c-form__element">
      <select
        className="c-form__select"
        onChange={event => refine(event.currentTarget.value)}
      >
        {items.map(item => (
          <option key={item.value} value={item.value}>
            {`Sort by: ${item.label}`}
          </option>
        ))}
      </select>
    </div>
  )
};


