/**
 * Zen Cart admin/includes/general.js
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Modified in v3.0 $
 *
 * NOTE: This file is not auto-loaded. Pages or plugins that need these
 * functions must explicitly include it.
 */

'use strict';

/**
 * @deprecated since v3.0 — row hover is handled by CSS (.dataTableRow:hover).
 * Retained as a no-op for plugin backward compatibility.
 */
function rowOverEffect(object) {}
function rowOutEffect(object) {}

/**
 * @deprecated since v3.0 — not called by core. Retained for plugin compatibility.
 * Sets focus on the first interactive field of the first form.
 */
function SetFocus() {
  if (document.forms.length === 0) return;
  const form = document.forms[0];
  for (let i = 0; i < form.length; i++) {
    const el = form.elements[i];
    if (!['image', 'hidden', 'reset', 'submit'].includes(el.type)) {
      el.focus();
      if (el.type === 'text' || el.type === 'password') el.select();
      break;
    }
  }
}
