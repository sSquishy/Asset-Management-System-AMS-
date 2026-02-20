<?php

namespace Database\Seeders;

use App\Models\Statuslabel;
use Illuminate\Database\Seeder;

class StatuslabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Mapping of new labels to their status type: deployable, pending, undeployable, archived
        $mapping = [
            'Deployed' => 'deployable',
            'In Store Available' => 'deployable',
            'In Store Reserved' => 'pending',
            'In Transit' => 'deployable',
            'In Store Repairable' => 'pending',
            'In Store Unrepairable' => 'undeployable',
            'In Store Obsolete' => 'archived',
            'Lost/Stolen' => 'undeployable',
            'Tech Refresh' => 'deployable',
            'Scrapped' => 'archived',
            'Donated' => 'archived',
        ];

        foreach ($mapping as $name => $type) {
            $attrs = Statuslabel::getStatuslabelTypesForDB($type);

            // Use firstOrCreate to avoid duplicates (respects existing records)
            Statuslabel::firstOrCreate(
                ['name' => $name],
                array_merge([
                    // set a default admin creator where available
                    'created_by' => 1,
                    'notes' => null,
                    'color' => null,
                    'show_in_nav' => 0,
                    'default_label' => 0,
                ], $attrs)
            );
        }
    }
}

