<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TransportPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Transport route permissions
            'transport_routes.view',
            'transport_routes.create',
            'transport_routes.edit',
            'transport_routes.delete',
            'transport_routes.manage',

            // Fleet bus permissions
            'fleet_buses.view',
            'fleet_buses.create',
            'fleet_buses.edit',
            'fleet_buses.delete',
            'fleet_buses.manage',
            'fleet_buses.assign_routes',

            // Bus stop permissions
            'bus_stops.view',
            'bus_stops.create',
            'bus_stops.edit',
            'bus_stops.delete',
            'bus_stops.manage',

            // Student transport permissions
            'student_transport.view',
            'student_transport.create',
            'student_transport.edit',
            'student_transport.delete',
            'student_transport.manage',
            'student_transport.subscribe',
            'student_transport.unsubscribe',

            // Transport tracking permissions
            'transport_tracking.view',
            'transport_tracking.manage',
            'transport_tracking.reports',

            // Transport incidents permissions
            'transport_incidents.view',
            'transport_incidents.create',
            'transport_incidents.edit',
            'transport_incidents.delete',
            'transport_incidents.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'api'],
                ['category' => 'transport']
            );
        }
    }
}
