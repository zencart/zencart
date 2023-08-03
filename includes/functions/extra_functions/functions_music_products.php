<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Aug 09 New in v1.5.8-alpha $
 */

function zen_update_music_artist_clicked($artistId, $languageId)
{
    global $db;
    $sql = "UPDATE " . TABLE_RECORD_ARTISTS_INFO . " SET url_clicked = url_clicked +1, date_last_click = NOW() WHERE artists_id = :artistId: AND languages_id = :languageId:";
    $sql = $db->bindVars($sql, ':artistId:', $artistId, 'integer');
    $sql = $db->bindVars($sql, ':languageId:', $languageId, 'integer');
    $db->execute($sql);
}

function zen_update_record_company_clicked($recordCompanyId, $languageId)
{
    global $db;
    $sql = "UPDATE " . TABLE_RECORD_COMPANY_INFO . " SET url_clicked = url_clicked +1, date_last_click = NOW() WHERE record_company_id = :rcId: AND languages_id = :languageId:";
    $sql = $db->bindVars($sql, ':rcId:', $recordCompanyId, 'integer');
    $sql = $db->bindVars($sql, ':languageId:', $languageId, 'integer');
    $db->execute($sql);
}
