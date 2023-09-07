/*
  The variables/functions in this file are meant
  to be helpers throughout the react apps to cut down
  on repeated code.
*/

// Capitalize a string
export const capitalize = (key) =>
  key.length === 0 ? '' : `${key[0].toUpperCase()}${key.slice(1)}`;

// ensure the items provided are organized into an array
export const toArray = (items) => {
  if (Array.isArray(items)) {
    return items;
  }
  if (items === null) {
    return [];
  }
  return [items];
};

// Checks browser is running on a native mobile device
export const isDevice = () => {
  const userAgent = navigator.userAgent || navigator.vendor || window.opera;

  if (
    /windows phone/i.test(userAgent) ||
    /android/i.test(userAgent) ||
    (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream)
  ) {
    return true;
  }

  return false;
};

export const debouncePromise = (fn, time) => {
  let timerId = undefined;

  return function debounced(...args) {
    if (timerId) {
      clearTimeout(timerId);
    }

    return new Promise((resolve) => {
      timerId = setTimeout(() => resolve(fn(...args)), time);
    });
  };
}

export const debounced = debouncePromise((items) => Promise.resolve(items), 400);

export const customFormatDate = (timestamp, allDay = false) => {
  const months = [
    "Jan.",
    "Feb.",
    "March",
    "April",
    "May",
    "June",
    "July",
    "Aug.",
    "Sept.",
    "Oct.",
    "Nov.",
    "Dec."
  ];

  const days = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
  ];

  const date = new Date(timestamp * 1000);

  const dayOfWeek = days[date.getDay()];
  const month = months[date.getMonth()];
  const day = date.getDate();
  const year = date.getFullYear();

  if (allDay) {
    return `${dayOfWeek}, ${month} ${day}, ${year}`;
  }

  let hours = date.getHours();
  const minutes = date.getMinutes();
  let amPm = hours >= 12 ? "p.m." : "a.m.";

  // Handle midnight and noon
  if (hours === 0 && minutes === 0) {
    amPm = "midnight";
  } else if (hours === 12 && minutes === 0) {
    amPm = "noon";
  } else if (hours > 12) {
    hours -= 12;
    amPm = "p.m.";
  } else if (hours === 0) {
    hours = 12;
    amPm = "a.m.";
  }

  // Format hours and minutes with leading zeros if necessary
  const hourString = hours.toString();
  const minuteString = minutes === 0 ? "" : (minutes < 10 ? `:0${minutes}` : `:${minutes}`);
  const timeString = `${hourString}${minuteString} ${amPm}`;

  return `${dayOfWeek}, ${month} ${day}, ${year} - ${timeString}`;
}
