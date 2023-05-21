<?php

namespace Seeders\InitialSeeders;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;

class ProjectVersionHistoryTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        Capsule::table('project_version_history')->truncate();

        Capsule::table('project_version_history')->insert(array(
            0 =>
                array(
                    'project_version_comment' => 'New Installation-v158',
                    'project_version_date_applied' => '2023-06-29 12:14:04',
                    'project_version_id' => 1,
                    'project_version_key' => 'Zen-Cart Main',
                    'project_version_major' => '1',
                    'project_version_minor' => '5.8a',
                    'project_version_patch' => '',
                ),
            1 =>
                array(
                    'project_version_comment' => 'New Installation-v158',
                    'project_version_date_applied' => '2023-06-29 12:14:04',
                    'project_version_id' => 2,
                    'project_version_key' => 'Zen-Cart Database',
                    'project_version_major' => '1',
                    'project_version_minor' => '5.8',
                    'project_version_patch' => '',
                ),
        ));


    }
}
