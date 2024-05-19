<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 New in v2.0.1 $
 *
 *
 * PCI Settings
 *
 * PCI DSS standards require certain limits be enforced
 *
 * PCI rules apply if your store handles payment-processing of any kind.
 *
 * When payments are handled by 3rd-party providers,
 * and when Admins have no access to customer card data or tokens,
 * then one might be able to assert that their PCI requirements are less onerous.
 * ... BUT ...
 * BEFORE CHANGING ANY OF THESE VALUES, BE SURE TO CHECK WITH YOUR MERCHANT-ACCOUNT (PAYMENT) PROVIDERS.
 *
 * Additional PA-DSS settings can be controlled via Admin Configuration: My Store
 *
 * PCI 3 rules expire March 31, 2024 (and some merchants may have until March 2025 to switch to PCI 4)
 * PCI 4 rules begin March 31, 2024, and will be required by all merchants by March 31, 2025.
 *
 *
 * DEV NOTE: This file does not comply with the internal "overrides" capability. All edits must be made to this file directly.
 */

// Lockout threshold: number of (admin) failed-login attempts allowed before lockout is triggered
// PCI3 had at lockout threshold of 6; PCI4 allows 10
zen_define_default('ADMIN_LOGIN_LOCKOUT_LIMIT', 10);

// Lockout duration: 30 minutes (30 * 60 seconds)
zen_define_default('ADMIN_LOGIN_LOCKOUT_TIMER', (30 * 60));

// Password length, for Admin users
// PCI3 rules specify min length 7; PCI4 requires 12 (or at least 8 if 12 isn't possible) after 2025/03/31
zen_define_default('ADMIN_PASSWORD_MIN_LENGTH', 8);

// Password Rotation Cycle: 90 days
zen_define_default('ADMIN_PASSWORD_EXPIRES_INTERVAL', strtotime('- 90 day'));

