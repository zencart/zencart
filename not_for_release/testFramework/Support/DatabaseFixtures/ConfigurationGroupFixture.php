<?php

namespace Tests\Support\DatabaseFixtures;

class ConfigurationGroupFixture extends DatabaseFixture implements FixtureContract
{
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS  configuration_group (
          configuration_group_id int(11) NOT NULL auto_increment,
          configuration_group_title varchar(64) NOT NULL default '',
          configuration_group_description varchar(255) NOT NULL default '',
          sort_order int(5) default NULL,
          visible int(1) default '1',
          PRIMARY KEY  (configuration_group_id),
          KEY idx_visible_zen (visible)
        ) ENGINE=MyISAM;";


        $this->connection->query($sql);
    }

    public function seeder()
    {
        $sql = "INSERT INTO configuration_group (configuration_group_title, configuration_group_description, sort_order, visible) values('test-group-title', 'test-group-description', 1, 1)";
        $this->connection->query($sql);
    }
}
