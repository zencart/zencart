# This SQL script upgrades converts pages entered for the "infopages" mod into the new EZ-Pages format for Zen Cart v1.3.0
#
# $Id: convert_infopages_to_ezpages.sql 4243 2006-08-24 10:55:28Z drbyte $

## Add EZ-Pages table:
CREATE TABLE ezpages (
  pages_id int(11) NOT NULL auto_increment,
  languages_id int(11) NOT NULL default '1',
  pages_title varchar(64) NOT NULL default '',
  alt_url varchar(255) NOT NULL default '',
  alt_url_external varchar(255) NOT NULL default '',
  pages_html_text text,
  status_header int(1) NOT NULL default '1',
  status_sidebox int(1) NOT NULL default '1',
  status_footer int(1) NOT NULL default '1',
  status_toc int(1) NOT NULL default '1',
  header_sort_order int(3) NOT NULL default '0',
  sidebox_sort_order int(3) NOT NULL default '0',
  footer_sort_order int(3) NOT NULL default '0',
  toc_sort_order int(3) NOT NULL default '0',
  page_open_new_window int(1) NOT NULL default '0',
  page_is_ssl int(1) NOT NULL default '0',
  toc_chapter int(11) NOT NULL default '0',
  PRIMARY KEY  (pages_id),
  KEY idx_lang_id_zen (languages_id),
  KEY idx_ezp_status_header_zen (status_header),
  KEY idx_ezp_status_sidebox_zen (status_sidebox),
  KEY idx_ezp_status_footer_zen (status_footer),
  KEY idx_ezp_status_toc_zen (status_toc)
);



## The following can be used to import data from the infopages contrib if it was used:
TRUNCATE ezpages;

INSERT INTO ezpages (pages_id, pages_title, alt_url, pages_html_text, status_sidebox, sidebox_sort_order, footer_sort_order)
SELECT pages_id, pages_title, alt_url, pages_html_text, status, vertical_sort_order, horizontal_sort_order
FROM infopages;

