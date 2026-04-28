<?php

namespace Database\Seeders;

use App\Models\PlatformUpdate;
use Illuminate\Database\Seeder;

class PlatformUpdateSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: skip if any rows already exist.
        if (PlatformUpdate::query()->exists()) {
            return;
        }

        $rows = [
            [
                'type'      => PlatformUpdate::TYPE_MAINTENANCE,
                'title'     => 'Platform Maintenance Scheduled',
                'body'      => 'We will be performing scheduled maintenance on Saturday, January 4th from 02:00 - 04:00 GMT. Some services may be temporarily unavailable.',
                'posted_at' => now()->subDays(3)->setTime(14, 30),
                'link_url'  => null,
            ],
            [
                'type'      => PlatformUpdate::TYPE_FEATURE,
                'title'     => 'New RCS Features Available',
                'body'      => 'Check out our new RCS carousel templates and rich card builder in the Templates section.',
                'posted_at' => now()->subDays(11)->setTime(9, 15),
                'link_url'  => null,
            ],
            [
                'type'      => PlatformUpdate::TYPE_UPDATE,
                'title'     => 'Improved Reporting Dashboard',
                'body'      => 'The reporting dashboard now loads up to 4× faster and includes new drill-down options for campaign delivery and supplier performance.',
                'posted_at' => now()->subDays(18)->setTime(11, 0),
                'link_url'  => null,
            ],
            [
                'type'      => PlatformUpdate::TYPE_FEATURE,
                'title'     => 'Auto Top-Up for Prepay Accounts',
                'body'      => 'Prepay customers can now configure automatic balance top-ups via Stripe. Enable it from Payments → Auto Top-Up.',
                'posted_at' => now()->subDays(25)->setTime(16, 45),
                'link_url'  => null,
            ],
            [
                'type'      => PlatformUpdate::TYPE_UPDATE,
                'title'     => 'New countries added to messaging coverage',
                'body'      => 'We have expanded SMS delivery coverage across additional EMEA and APAC routes. Check the latest pricing in the Purchase section.',
                'posted_at' => now()->subDays(35)->setTime(10, 20),
                'link_url'  => null,
            ],
        ];

        foreach ($rows as $row) {
            PlatformUpdate::create(array_merge($row, ['published' => true]));
        }
    }
}
