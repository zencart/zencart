/**
 * Zen Cart general.js
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Modified in v3.0 $
 */

'use strict';

/**
 * Clears a placeholder/format string from an input when focused, then selects it.
 * Used by search
 * @param {HTMLInputElement} targetElement
 * @param {string} formatString
 */
function RemoveFormatString(targetElement, formatString) {
  if (targetElement.value === formatString) {
    targetElement.value = '';
  }
  targetElement.select();
}

/**
 * Returns true if `from` date is <= `to` date.
 * Used by search
 * @param {HTMLInputElement} from
 * @param {HTMLInputElement} to
 * @returns {boolean}
 */
function CheckDateRange(from, to) {
  return Date.parse(from.value) <= Date.parse(to.value);
}

/**
 * Returns true if intYear is a leap year.
 * Used by search
 * @param {number} intYear
 * @returns {boolean}
 */
function IsLeapYear(intYear) {
  return (intYear % 400 === 0) || (intYear % 4 === 0 && intYear % 100 !== 0);
}

/**
 * Validates a date string against a format string (e.g. "mm/dd/yyyy").
 * Supports separators: - / . and space. Month can be numeric or 3-letter abbreviation.
 * Used by search
 * @param {string} dateToCheck
 * @param {string} formatString
 * @returns {boolean}
 */
function IsValidDate(dateToCheck, formatString) {
  const separators = ['-', ' ', '/', '.'];
  const monthNames = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
  const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

  const dateLower   = dateToCheck.toLowerCase();
  const formatLower = formatString.toLowerCase();

  if (dateLower.length !== formatLower.length) return false;

  const sepIdx    = separators.findIndex(s => formatLower.includes(s));
  const dateSepIdx = separators.findIndex(s => dateLower.includes(s));

  if (sepIdx !== dateSepIdx) return false;

  let strDay, strMonth, strYear;

  if (sepIdx !== -1) {
    const sep         = separators[sepIdx];
    const formatParts = formatLower.split(sep);
    const dateParts   = dateLower.split(sep);

    if (formatParts.length !== 3 || dateParts.length !== 3) return false;

    for (let i = 0; i < formatParts.length; i++) {
      if (formatParts[i] === 'mm' || formatParts[i] === 'mmm') strMonth = dateParts[i];
      if (formatParts[i] === 'dd')   strDay   = dateParts[i];
      if (formatParts[i] === 'yyyy') strYear  = dateParts[i];
    }
  } else {
    if (formatLower.length <= 7) return false;

    const mmIdx  = formatLower.includes('mmm') ? formatLower.indexOf('mmm') : formatLower.indexOf('mm');
    const mLen   = formatLower.includes('mmm') ? 3 : 2;
    strMonth = dateLower.substring(mmIdx, mmIdx + mLen);
    strDay   = dateLower.substring(formatLower.indexOf('dd'),   formatLower.indexOf('dd')   + 2);
    strYear  = dateLower.substring(formatLower.indexOf('yyyy'), formatLower.indexOf('yyyy') + 4);
  }

  if (!strYear || strYear.length !== 4) return false;

  const intYear = parseInt(strYear, 10);
  if (isNaN(intYear)) return false;

  let intMonth = parseInt(strMonth, 10);
  if (isNaN(intMonth)) {
    intMonth = monthNames.indexOf(strMonth) + 1;
    if (intMonth < 1) return false;
  }
  if (intMonth < 1 || intMonth > 12) return false;

  const intDay = parseInt(strDay, 10);
  if (isNaN(intDay) || intDay < 1) return false;

  const maxDays = [...daysInMonth];
  if (IsLeapYear(intYear)) maxDays[1] = 29;

  return intDay <= maxDays[intMonth - 1];
}

/**
 * @deprecated since v3.0 — not called by core since v1.5.
 * Retained here only for plugin compatibility.
 * Sets focus on the first interactive field of the named form (or forms[0]).
 * @param {string} [TargetFormName='']
 */
function SetFocus(TargetFormName) {
  let target = 0;
  if (TargetFormName) {
    for (let i = 0; i < document.forms.length; i++) {
      if (document.forms[i].name === TargetFormName) {
        target = i;
        break;
      }
    }
  }

  const form = document.forms[target];
  if (!form) return;

  for (let i = 0; i < form.length; i++) {
    const el = form.elements[i];
    if (!['image','hidden','reset','submit'].includes(el.type)) {
      el.focus();
      if (el.type === 'text' || el.type === 'password') el.select();
      break;
    }
  }
}
