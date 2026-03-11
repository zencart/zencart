<?php
/**
 * An API-data class for Countries used by the PayPalRestful (paypalr) Payment Module
 *
 * @copyright Copyright 2023 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 *
 * Last updated: v1.3.0
 */
namespace PayPalRestful\Api\Data;

// -----
// For reference, see https://developer.paypal.com/api/rest/reference/orders/v2/country-address-requirements/
//
// The following are different, but related, lists:
// https://developer.paypal.com/api/rest/reference/country-codes/
// https://developer.paypal.com/sdk/js/configuration/#locale
//
class CountryCodes
{
    protected static $countryCodes = [
        'AF',  //- Afghanistan
        'AX',  //- Aland Islands
        'AL',  //- Albania
        'DZ',  //- Algeria
        'AS',  //- American Samoa
        'AD',  //- Andorra
        'AO',  //- Angola
        'AI',  //- Anguilla
        'AQ',  //- Antarctica
        'AG',  //- Antigua and Barbuda
        'AR',  //- Argentina
        'AM',  //- Armenia
        'AW',  //- Aruba
        'AU',  //- Australia
        'AT',  //- Austria
        'AZ',  //- Azerbaijan
        'BS',  //- Bahamas
        'BH',  //- Bahrain
        'BD',  //- Bangladesh
        'BB',  //- Barbados
        'BY',  //- Belarus
        'BE',  //- Belgium
        'BZ',  //- Belize
        'BJ',  //- Benin
        'BM',  //- Bermuda
        'BT',  //- Bhutan
        'BO',  //- Bolivia
        'BA',  //- Bosnia and Herzegovina
        'BW',  //- Botswana
        'BV',  //- Bouvet Island
        'BR',  //- Brazil
        'IO',  //- British Indian Ocean Territory
        'BN',  //- Brunei Darussalam
        'BG',  //- Bulgaria
        'BF',  //- Burkina Faso
        'BI',  //- Burundi
        'KH',  //- Cambodia
        'CM',  //- Cameroon
        'CA',  //- Canada
        'CV',  //- Cape Verde Escudo
        'KY',  //- Cayman Islands
        'CF',  //- Central African Republic
        'TD',  //- Chad
        'CL',  //- Chile
        'C2',  //- China ... Zen Cart code is 'CH'
        'CN',  //- China
        'CX',  //- Christmas Island
        'CC',  //- Cocos Islands
        'CO',  //- Colombia
        'KM',  //- Comoros
//        'CD',  //- Congo Democratic Republic, not a Zen Cart country!
//        'ZR',  //- Congo Democratic Republic, not a Zen Cart country!
        'CG',  //- Congo Republic
        'CK',  //- Cook Islands
        'CR',  //- Costa Rica
        'CI',  //- Cote Divoire
        'HR',  //- Croatia
        'CU',  //- Cuba
        'CY',  //- Cyprus
        'CZ',  //- Czech Republic
        'DK',  //- Denmark
        'DJ',  //- Djibouti
        'DM',  //- Dominica
        'DO',  //- Dominican Republic
//        'TP',  //- East Timor, not a Zen Cart country!
        'EC',  //- Ecuador
        'EG',  //- Egypt
        'SV',  //- El Salvador
        'GQ',  //- Equatorial Guinea
        'ER',  //- Eritrea
        'EE',  //- Estonia
        'ET',  //- Ethiopia
        'FK',  //- Falkland Islands
        'FO',  //- Faroe Islands
        'FM',  //- Federated States Of Micronesia
        'FJ',  //- Fiji
        'FI',  //- Finland
        'FR',  //- France
        'GF',  //- French Guiana
        'PF',  //- French Polynesia
        'TF',  //- French Southern Territories
        'GA',  //- Gabon Republic
        'GM',  //- Gambia
        'GE',  //- Georgia Country
        'DE',  //- Germany
        'GH',  //- Ghana
        'GI',  //- Gibraltar
        'GR',  //- Greece
        'GL',  //- Greenland
        'GD',  //- Grenada
        'GP',  //- Guadeloupe
        'GU',  //- Guam
        'GT',  //- Guatemala
        'GG',  //- Guernsey
        'GN',  //- Guinea
        'GW',  //- Guinea Bissau
        'GY',  //- Guyana
        'HT',  //- Haiti
        'HM',  //- Heard and Mcdonald Islands
        'HN',  //- Honduras
        'HK',  //- Hong Kong
        'HU',  //- Hungary
        'IS',  //- Iceland
        'IN',  //- India
        'ID',  //- Indonesia
        'IR',  //- Iran
        'IQ',  //- Iraq
        'IE',  //- Ireland
        'IM',  //- Isle of Man
        'IL',  //- Israel
        'IT',  //- Italy
        'JM',  //- Jamaica
        'JP',  //- Japan
        'JE',  //- Jersey
        'JO',  //- Jordan
        'KZ',  //- Kazakhstan
        'KE',  //- Kenya
        'KI',  //- Kiribati
        'KW',  //- Kuwait
        'KG',  //- Kyrgyzstan
        'LA',  //- Laos
        'LV',  //- Latvia
        'LB',  //- Lebanon
        'LS',  //- Lesotho
        'LR',  //- Liberia
        'LY',  //- Libya
        'LI',  //- Liechtenstein
        'LT',  //- Lithuania
        'LU',  //- Luxembourg
        'MO',  //- Macau
        'MK',  //- Macedonia
        'MG',  //- Madagascar
        'MW',  //- Malawi
        'MY',  //- Malaysia
        'MV',  //- Maldives
        'ML',  //- Mali
        'MT',  //- Malta
        'MH',  //- Marshall Islands
        'MQ',  //- Martinique
        'MR',  //- Mauritania
        'MU',  //- Mauritius
        'YT',  //- Mayotte
        'MX',  //- Mexico
        'MD',  //- Moldova
        'MC',  //- Monaco
        'MN',  //- Mongolia
        'ME',  //- Montenegro
        'MS',  //- Montserrat
        'MA',  //- Morocco
        'MZ',  //- Mozambique
        'MM',  //- Myanmar
        'NA',  //- Namibia
        'NR',  //- Nauru
        'NP',  //- Nepal
        'NL',  //- Netherlands
//        'AN',  //- Netherlands Antilles, not a Zen Cart country!
        'NC',  //- New Caledonia
        'NZ',  //- New Zealand
        'NI',  //- Nicaragua
        'NE',  //- Niger
        'NG',  //- Nigeria
        'NU',  //- Niue
        'NF',  //- Norfolk Island
        'KP',  //- North Korea
        'MP',  //- Northern Mariana Islands
        'NO',  //- Norway
        'OM',  //- Oman
        'PK',  //- Pakistan
        'PW',  //- Palau
        'PS',  //- Palestinian Territory Occupied
        'PA',  //- Panama
        'PG',  //- Papua New Guinea
        'PY',  //- Paraguay
        'PE',  //- Peru
        'PH',  //- Philippines
        'PN',  //- Pitcairn Islands
        'PL',  //- Poland
        'PT',  //- Portugal
        'PR',  //- Puerto Rico
        'QA',  //- Qatar
        'RE',  //- Reunion
        'RO',  //- Romania
        'RU',  //- Russia
        'RW',  //- Rwanda
        'WS',  //- Samoa
        'SM',  //- San Marino
        'ST',  //- Sao Tome and Principe
        'SA',  //- Saudi Arabia
        'SN',  //- Senegal
        'RS',  //- Serbia
//        'CS',  //- Serbia and Montenegro, not a Zen Cart country!
        'SC',  //- Seychelles
        'SL',  //- Sierra Leone
        'SG',  //- Singapore
        'SK',  //- Slovakia
        'SI',  //- Slovenia
        'SB',  //- Solomon Islands
        'SO',  //- Somalia
        'ZA',  //- South Africa
        'GS',  //- South Georgia and the South Sandwich Islands
        'KR',  //- South Korea
        'ES',  //- Spain
        'LK',  //- Sri Lanka
        'SH',  //- St Helena
        'KN',  //- St Kitts and Nevis
        'LC',  //- St Lucia
        'PM',  //- St Pierre and Miquelon
        'VC',  //- St Vincent and the Grenadines
        'SD',  //- Sudan
        'SR',  //- Suriname
        'SJ',  //- Svalbard and Jan Mayen Islands
        'SZ',  //- Swaziland
        'SE',  //- Sweden
        'CH',  //- Switzerland
        'SY',  //- Syria
        'TW',  //- Taiwan
        'TJ',  //- Tajikistan
        'TZ',  //- Tanzania
        'TH',  //- Thailand
        'TL',  //- Timor Leste
        'TG',  //- Togo
        'TK',  //- Tokelau
        'TO',  //- Tonga
        'TT',  //- Trinidad and Tobago
        'TN',  //- Tunisia
        'TR',  //- Turkey
        'TM',  //- Turkmenistan
        'TC',  //- Turks and Caicos Islands
        'TV',  //- Tuvalu
        'UG',  //- Uganda
        'UA',  //- Ukraine
        'AE',  //- United Arab Emirates
        'GB',  //- United Kingdom
        'US',  //- United States
        'UY',  //- Uruguay
        'UM',  //- US Minor Outlying Islands
        'UZ',  //- Uzbekistan
        'VU',  //- Vanuatu
        'VA',  //- Vatican City State
        'VE',  //- Venezuela
        'VN',  //- Viet Nam
        'VG',  //- Virgin Islands British
        'VI',  //- Virgin Islands USA
        'WF',  //- Wallis and Futuna Islands
        'EH',  //- Western Sahara
        'YE',  //- Yemen
//        'YU',  //- Yugoslavia, not a Zen Cart country!
        'ZM',  //- Zambia
        'ZW',  //- Zimbabwe
    ];

    public static function convertCountryCode(string $country_code): string
    {
        if (in_array($country_code, self::$countryCodes)) {
            return $country_code;
        }
        return ($country_code === 'CH') ? 'C2' : '';
    }
}
