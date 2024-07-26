<?php

namespace Database\Seeders;

use App\Models\WebRoute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WebRoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WebRoute::truncate();
        DB::table('web_routes')->insert([
            // pub owner routes
            [
                "title" => 'Home',
                "to" => '/dashboards/pub-owner',
                "href" => '/dashboards/pub-owner',
                "icon" => Storage::url('icons/home.svg'),
            ],
            [
                "title" => 'Activity Management',
                "to" => '/dashboards/game-setup',
                "href" => '/dashboards/game-setup',
                "icon" => Storage::url('icons/activity-management.svg'),
            ],
            [
                "title" => 'Organizers',
                "to" => '/dashboards/organizer',
                "href" => '/dashboards/organizer',
                "icon" => Storage::url('icons/organizer.svg'),
            ],
            [
                "title" => 'Subscription',
                "to" => '/dashboards/subscription',
                "href" => '/dashboards/subscription',
                "icon" => Storage::url('icons/subscription.svg'),
            ],
            [
                "title" => 'Game Library',
                "to" => '/dashboards/game-library',
                "href" => '/dashboards/game-library',
                "icon" => Storage::url('icons/game-library.svg'),
            ],
            [
                "title" => 'Sponsor Request',
                "to" => '/dashboards/sponsor-request',
                "href" => '/dashboards/sponsor-request',
                "icon" => Storage::url('icons/sponsor-request.svg'),
            ],
            [
                "title" => 'Rewards',
                "to" => '/dashboards/rewards',
                "href" => '/dashboards/rewards',
                "icon" => Storage::url('icons/subscription.svg'),
            ],
            [
                "title" => 'User Reward',
                "to" => '/dashboards/user-rewards',
                "href" => '/dashboards/user-rewards',
                "icon" => Storage::url('icons/app-user-list.svg'),
            ],
            [
                "title" => 'My Profile',
                "to" => '/dashboards/edit-profile',
                "href" => '/dashboards/edit-profile',
                "icon" => Storage::url('icons/my-profile.svg'),
            ],
            // Admin routes start from here
            [
                "title" => 'Dashboard',
                "to" => '/dashboards/master-admin',
                "href" => '/dashboards/master-admin',
                "icon" => Storage::url('icons/home.svg'),
            ],
            [
                "title" => 'App User List',
                "to" => '/dashboards/app-user-list',
                "href" => '/dashboards/app-user-list',
                "icon" => Storage::url('icons/app-user-list.svg'),
            ],
            [
                "title" => 'Pubs',
                "to" => '/dashboards/pubs-list',
                "href" => '/dashboards/pubs-list',
                "icon" => Storage::url('icons/pubs.svg'),
            ],
            [
                "title" => 'Sponsors',
                "to" => '/dashboards/sponsors-list',
                "href" => '/dashboards/sponsors-list',
                "icon" => Storage::url('icons/sponsor-request.svg'),
            ],
            [
                "title" => 'Subscriptions',
                "to" => '/dashboards/admin-subscription',
                "href" => '/dashboards/admin-subscription',
                "icon" => Storage::url('icons/subscription.svg'),
            ],
            [
                "title" => 'Game Settings',
                "to" => '/dashboards/game-setting',
                "href" => '/dashboards/game-setting',
                "icon" => Storage::url('icons/game-settings.svg'),
            ],
            [
                "title" => 'Report & Analytics',
                "to" => '/dashboards/reports-analytics',
                "href" => '/dashboards/reports-analytics',
                "icon" => Storage::url('icons/chart-2.svg'),
            ],
            // sponser routes starts from here
            [
                "title" => 'Home',
                "to" => '/dashboards/sponsor',
                "href" => '/dashboards/sponsor',
                "icon" => Storage::url('icons/home.svg'),
            ],
            [
                "title" => 'Sponsorship',
                "to" => '/dashboards/sponsor-management',
                "href" => '/dashboards/sponsor-management',
                "icon" => Storage::url('icons/activity-management.svg'),
            ],
            [
                "title" => 'My Profile',
                "to" => '/dashboards/edit-profile',
                "href" => '/dashboards/edit-profile',
                "icon" => Storage::url('icons/my-profile.svg'),
            ]
        ]);
    }
}
