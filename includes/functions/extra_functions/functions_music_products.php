<?php
/**
 * functions_music_products
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $ID: $
 */

/**
 * Update click counter for artist
 * @param int $artistId
 * @param int $languageId
 */
function zen_update_music_artist_clicked($artistId, $languageId)
{
  global $db;
  $sql = "UPDATE " . TABLE_RECORD_ARTISTS_INFO . " set url_clicked = url_clicked +1, date_last_click = NOW() WHERE artists_id = :artistId: AND languages_id = :languageId:";
  $sql = $db->bindVars($sql, ':artistId:', $artistId, 'integer');
  $sql = $db->bindVars($sql, ':languageId:', $languageId, 'integer');
  $db->execute($sql);
}

/**
 * Update click counter for record company
 * @param int $recordCompanyId
 * @param int $languageId
 */
function zen_update_record_company_clicked($recordCompanyId, $languageId)
{
  global $db;
  $sql = "UPDATE " . TABLE_RECORD_COMPANY_INFO . " set url_clicked = url_clicked +1, date_last_click = NOW() WHERE record_company_id = :rcId: AND languages_id = :languageId:";
  $sql = $db->bindVars($sql, ':rcId:', $recordCompanyId, 'integer');
  $sql = $db->bindVars($sql, ':languageId:', $languageId, 'integer');
  $db->execute($sql);
}
