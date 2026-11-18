<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resource Development', 'code' => 'HRD'],
            ['name' => 'Finance Management Office', 'code' => 'FMO'],
            ['name' => 'General Services Office', 'code' => 'GSO'],
            ['name' => 'Alay Kapwa Programs', 'code' => 'AKP'],
            ['name' => 'Socio-Economic Development Program', 'code' => 'SEDP'],
            ['name' => 'Relief and Disaster Preparedness', 'code' => 'RDP'],
            ['name' => 'LAFORCE', 'code' => 'LAFORCE'],
            ['name' => 'Executive Office', 'code' => 'EO'],
            ['name' => 'Apostolic', 'code' => 'Apostolic'],
            ['name' => 'Administrative Services Section', 'code' => 'ADMIN'],
            ['name' => 'Mission Sustainability Section', 'code' => 'MSS'],
            ['name' => 'Resource Development Office', 'code' => 'RDO'],
        ];

        foreach ($departments as $dept) {
            $department = Department::firstOrCreate(['code' => $dept['code']], $dept);

            $positions = match($dept['code']) {
                'EO' => ['Executive Director', 'Executive Assistant', 'Corporate Secretary', 'Program Director'],
                'HRD', 'ADMIN' => ['Administrative Officer', 'HR Officer', 'HR Assistant', 'IT Specialist'],
                'GSO' => ['General Services Officer', 'General Services Staff', 'Driver', 'Utility Staff'],
                'FMO' => ['Finance Manager', 'Finance Officer', 'Accountant', 'Bookkeeper', 'Cashier', 'Disbursement Officer'],
                'AKP' => ['Program Coordinator', 'Program Specialist I', 'Program Specialist II', 'Community Organizer', 'Project Officer'],
                'SEDP' => ['SEDP Coordinator', 'Livelihood Specialist', 'Project Officer'],
                'RDP' => ['Disaster Risk Reduction Specialist', 'Relief Coordinator', 'Field Officer'],
                'LAFORCE' => ['LAFORCE Coordinator', 'Operations Staff'],
                'Apostolic', 'MSS' => ['Sustainability Officer', 'Monitoring & Evaluation Officer', 'Advocacy Specialist', 'Research Officer'],
                'RDO' => ['Resource Development Officer', 'Communications Specialist', 'Partnership Associate', 'Fundraising Officer', 'Donor Relations Officer'],
                default => [],
            };

            foreach ($positions as $pos) {
                Position::firstOrCreate(['department_id' => $department->id, 'name' => $pos]);
            }
        }
    }
}
