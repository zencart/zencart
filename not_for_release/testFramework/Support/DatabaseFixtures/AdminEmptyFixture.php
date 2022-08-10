<?php

namespace Tests\Support\DatabaseFixtures;

class AdminEmptyFixture implements FixtureContract
{

    protected $table = TABLE_ADMIN;

    public function createTable($connection)
    {
        $sql = "CREATE TABLE IF NOT EXISTS admin (
              admin_id int(11) NOT NULL auto_increment,
              admin_name varchar(32) NOT NULL default '',
              admin_email varchar(96) NOT NULL default '',
              admin_profile int(11) NOT NULL default '0',
              admin_pass varchar(255) NOT NULL default '',
              prev_pass1 varchar(255) NOT NULL default '',
              prev_pass2 varchar(255) NOT NULL default '',
              prev_pass3 varchar(255) NOT NULL default '',
              pwd_last_change_date datetime NOT NULL default '0001-01-01 00:00:00',
              reset_token varchar(255) NOT NULL default '',
              last_modified datetime NOT NULL default '0001-01-01 00:00:00',
              last_login_date datetime NOT NULL default '0001-01-01 00:00:00',
              last_login_ip varchar(45) NOT NULL default '',
              failed_logins smallint(4) unsigned NOT NULL default '0',
              lockout_expires int(11) NOT NULL default '0',
              last_failed_attempt datetime NOT NULL default '0001-01-01 00:00:00',
              last_failed_ip varchar(45) NOT NULL default '',
              PRIMARY KEY  (admin_id),
              KEY idx_admin_name_zen (admin_name),
              KEY idx_admin_email_zen (admin_email),
              KEY idx_admin_profile_zen (admin_profile)
            ) ENGINE=MyISAM;";

        $connection->query($sql);
    }

    public function seeder()
    {

    }
}
