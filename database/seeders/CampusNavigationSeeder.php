<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CampusNavigationSeeder extends Seeder
{
    /**
     * Seed the application's database with campus navigation data.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Truncate tables for a clean seed
        if (Schema::hasTable('navigation_history')) {
            DB::table('navigation_history')->truncate();
        }
        if (Schema::hasTable('departments')) {
            DB::table('departments')->truncate();
        }
        if (Schema::hasTable('rooms')) {
            DB::table('rooms')->truncate();
        }
        if (Schema::hasTable('facilities')) {
            DB::table('facilities')->truncate();
        }
        if (Schema::hasTable('buildings')) {
            DB::table('buildings')->truncate();
        }

        // 2. Seed Buildings
        $buildings = [
            [
                'building_id' => 1,
                'building_name' => 'Building 1 - First Building',
                'latitude' => 14.24101000,
                'longitude' => 120.97891500,
                'description' => 'TED Faculty Offices, University Clinic, Classrooms 111-126, Male & Female CRs'
            ],
            [
                'building_id' => 2,
                'building_name' => 'Building 2 - Second Building',
                'latitude' => 14.24093800,
                'longitude' => 120.97931000,
                'description' => 'Junior High Faculty, EBA, Diploma Storage, Classrooms 211-226, Male & Female CRs'
            ],
            [
                'building_id' => 3,
                'building_name' => 'Building 3 - Third Building',
                'latitude' => 14.24152500,
                'longitude' => 120.97911800,
                'description' => 'Registrar, OSAS, AVR, Computer Labs, Library, Psychology, Management, CSG, 5 Floors & 6 CRs'
            ],
            [
                'building_id' => 4,
                'building_name' => 'Hostel for HM & Tourism',
                'latitude' => 14.24146500,
                'longitude' => 120.97943000,
                'description' => 'Function Hall, Kitchen Lab, F&B Lab, Stock Room, CR Hallway'
            ],
            [
                'building_id' => 5,
                'building_name' => 'Campus Gymnasium (Back of Stage)',
                'latitude' => 14.24115500,
                'longitude' => 120.97946500,
                'description' => 'Covered Court, ID Picture Studio, Storage Room, and Back-of-Stage Office'
            ],
        ];
        DB::table('buildings')->insert($buildings);

        // 3. Seed Departments
        $departments = [
            ['building_id' => 1, 'department_name' => 'Teacher Education Department (TED)'],
            ['building_id' => 2, 'department_name' => 'Junior High School Department'],
            ['building_id' => 3, 'department_name' => 'Department of Technology'],
            ['building_id' => 3, 'department_name' => 'Department of Psychology'],
            ['building_id' => 3, 'department_name' => 'Department of Management'],
            ['building_id' => 4, 'department_name' => 'Hospitality Management & Tourism Department'],
        ];
        DB::table('departments')->insert($departments);

        // 4. Seed Rooms
        $rooms = [
            ['building_id' => 1, 'room_number' => '111', 'room_name' => 'TED Faculty (Higher Position)'],
            ['building_id' => 1, 'room_number' => '112', 'room_name' => 'TED Faculty (Instructors)'],
            ['building_id' => 1, 'room_number' => '113', 'room_name' => 'Lecture Room 113'],
            ['building_id' => 1, 'room_number' => '114', 'room_name' => 'Lecture Room 114'],
            ['building_id' => 1, 'room_number' => '115', 'room_name' => 'Lecture Room 115'],
            ['building_id' => 1, 'room_number' => '116', 'room_name' => 'Lecture Room 116'],
            ['building_id' => 1, 'room_number' => 'CLINIC', 'room_name' => 'University Clinic (1st Floor)'],
            ['building_id' => 1, 'room_number' => '1F-CRF', 'room_name' => 'Female CR (1st Floor Left)'],
            ['building_id' => 1, 'room_number' => '1F-CRM', 'room_name' => 'Male CR (1st Floor Right)'],
            ['building_id' => 1, 'room_number' => '121', 'room_name' => 'Lecture Room 121'],
            ['building_id' => 1, 'room_number' => '122', 'room_name' => 'Lecture Room 122'],
            ['building_id' => 1, 'room_number' => '123', 'room_name' => 'Lecture Room 123'],
            ['building_id' => 1, 'room_number' => '124', 'room_name' => 'Lecture Room 124'],
            ['building_id' => 1, 'room_number' => '125', 'room_name' => 'Lecture Room 125'],
            ['building_id' => 1, 'room_number' => '126', 'room_name' => 'Lecture Room 126'],

            ['building_id' => 2, 'room_number' => '211', 'room_name' => 'Junior High Faculty'],
            ['building_id' => 2, 'room_number' => '212', 'room_name' => 'Lecture Room 212'],
            ['building_id' => 2, 'room_number' => '213', 'room_name' => 'Lecture Room 213'],
            ['building_id' => 2, 'room_number' => '214', 'room_name' => 'Lecture Room 214'],
            ['building_id' => 2, 'room_number' => '215', 'room_name' => 'Lecture Room 215'],
            ['building_id' => 2, 'room_number' => '216', 'room_name' => 'Lecture Room 216'],
            ['building_id' => 2, 'room_number' => '2F-CRF', 'room_name' => 'Female CR (1st Floor Left)'],
            ['building_id' => 2, 'room_number' => '2F-CRM', 'room_name' => 'Male CR (1st Floor Right)'],
            ['building_id' => 2, 'room_number' => '221', 'room_name' => 'EBA Room'],
            ['building_id' => 2, 'room_number' => '222', 'room_name' => 'Storage of Diploma'],
            ['building_id' => 2, 'room_number' => '223', 'room_name' => 'Lecture Room 223'],
            ['building_id' => 2, 'room_number' => '224', 'room_name' => 'Lecture Room 224'],
            ['building_id' => 2, 'room_number' => '225', 'room_name' => 'Lecture Room 225'],
            ['building_id' => 2, 'room_number' => '226', 'room_name' => 'Lecture Room 226'],

            ['building_id' => 3, 'room_number' => '311', 'room_name' => 'Registrar Office'],
            ['building_id' => 3, 'room_number' => '312', 'room_name' => 'OSAS Office'],
            ['building_id' => 3, 'room_number' => '313', 'room_name' => 'AVR Room'],
            ['building_id' => 3, 'room_number' => '314', 'room_name' => 'Tourism Management Room'],
            ['building_id' => 3, 'room_number' => '315', 'room_name' => 'Room 315'],
            ['building_id' => 3, 'room_number' => '316', 'room_name' => 'Room 316'],
            ['building_id' => 3, 'room_number' => '3F1-CRF', 'room_name' => 'Female CR (1st Floor Left)'],
            ['building_id' => 3, 'room_number' => '3F1-CRM', 'room_name' => 'Male CR (1st Floor Right)'],
            ['building_id' => 3, 'room_number' => '321', 'room_name' => 'Department of Technology'],
            ['building_id' => 3, 'room_number' => '322', 'room_name' => 'Computer Lab 1 (CL1)'],
            ['building_id' => 3, 'room_number' => '323A', 'room_name' => 'Computer Lab 2 (CL2)'],
            ['building_id' => 3, 'room_number' => '323B', 'room_name' => 'Computer Lab 3 (CL3)'],
            ['building_id' => 3, 'room_number' => '324', 'room_name' => 'Computer Lab 4 (CL4)'],
            ['building_id' => 3, 'room_number' => '325', 'room_name' => 'University Library'],
            ['building_id' => 3, 'room_number' => '331', 'room_name' => 'Department of Psychology'],
            ['building_id' => 3, 'room_number' => '332', 'room_name' => 'Psychology Lab'],
            ['building_id' => 3, 'room_number' => '333A', 'room_name' => 'Lecture Room 333A'],
            ['building_id' => 3, 'room_number' => '333B', 'room_name' => 'Lecture Room 333B'],
            ['building_id' => 3, 'room_number' => '334A', 'room_name' => 'Lecture Room 334A'],
            ['building_id' => 3, 'room_number' => '334B', 'room_name' => 'Lecture Room 334B'],
            ['building_id' => 3, 'room_number' => '335', 'room_name' => 'Research and Extension Office'],
            ['building_id' => 3, 'room_number' => '3F3-CRF', 'room_name' => 'Female CR (3rd Floor East Wing)'],
            ['building_id' => 3, 'room_number' => '3F3-CRM', 'room_name' => 'Male CR (3rd Floor East Wing)'],
            ['building_id' => 3, 'room_number' => '341A', 'room_name' => 'Lecture Room 341A'],
            ['building_id' => 3, 'room_number' => '341B', 'room_name' => 'Lecture Room 341B'],
            ['building_id' => 3, 'room_number' => '342A', 'room_name' => 'Lecture Room 342A'],
            ['building_id' => 3, 'room_number' => '342B', 'room_name' => 'Lecture Room 342B'],
            ['building_id' => 3, 'room_number' => '343A', 'room_name' => 'Lecture Room 343A'],
            ['building_id' => 3, 'room_number' => '343B', 'room_name' => 'Lecture Room 343B'],
            ['building_id' => 3, 'room_number' => '344', 'room_name' => 'Department of Management'],
            ['building_id' => 3, 'room_number' => '345', 'room_name' => 'Lecture Room 345'],
            ['building_id' => 3, 'room_number' => '3F4-CRF', 'room_name' => 'Female CR (4th Floor East Wing)'],
            ['building_id' => 3, 'room_number' => '3F4-CRM', 'room_name' => 'Male CR (4th Floor East Wing)'],
            ['building_id' => 3, 'room_number' => '351', 'room_name' => 'Lecture Room 351'],
            ['building_id' => 3, 'room_number' => '352', 'room_name' => 'Lecture Room 352'],
            ['building_id' => 3, 'room_number' => '353', 'room_name' => 'Lecture Room 353'],
            ['building_id' => 3, 'room_number' => '354', 'room_name' => 'Lecture Room 354'],
            ['building_id' => 3, 'room_number' => '355', 'room_name' => 'Lecture Room 355'],
            ['building_id' => 3, 'room_number' => '356', 'room_name' => 'Lecture Room 356'],
            ['building_id' => 3, 'room_number' => '357', 'room_name' => 'Lecture Room 357'],
            ['building_id' => 3, 'room_number' => '358', 'room_name' => 'Lecture Room 358'],
            ['building_id' => 3, 'room_number' => '359', 'room_name' => 'Lecture Room 359'],
            ['building_id' => 3, 'room_number' => '360', 'room_name' => 'Central Student Government (CSG)'],

            ['building_id' => 4, 'room_number' => 'H-FH', 'room_name' => 'Function Hall (Entrance Left)'],
            ['building_id' => 4, 'room_number' => 'H-KL', 'room_name' => 'Kitchen Lab (Entrance Right - 1st Room)'],
            ['building_id' => 4, 'room_number' => 'H-FB', 'room_name' => 'F&B Lab (Entrance Right - 2nd Room)'],
            ['building_id' => 4, 'room_number' => 'H-SR', 'room_name' => 'Stock Room (Entrance Right - End Room)'],
            ['building_id' => 4, 'room_number' => 'H-CR', 'room_name' => 'Hostel CR Hallway'],

            ['building_id' => 5, 'room_number' => 'GYM-R', 'room_name' => 'Studio for ID Picture (Back of Stage - Right)'],
            ['building_id' => 5, 'room_number' => 'GYM-M', 'room_name' => 'Storage Room (Back of Stage - Middle)'],
            ['building_id' => 5, 'room_number' => 'GYM-L', 'room_name' => 'Campus Office (Back of Stage - Left)'],
        ];
        DB::table('rooms')->insert($rooms);

        // 5. Seed Facilities
        $facilities = [
            [
                'facility_name' => 'Guard House / Main Gate',
                'latitude' => 14.24089000,
                'longitude' => 120.97874500,
            ],
            [
                'facility_name' => 'University Clinic (Bldg 1 - 1st Floor)',
                'latitude' => 14.24098200,
                'longitude' => 120.97906500,
            ],
            [
                'facility_name' => 'Campus Gymnasium / Covered Court',
                'latitude' => 14.24121500,
                'longitude' => 120.97916800,
            ],
            [
                'facility_name' => 'Student Canteen',
                'latitude' => 14.24178500,
                'longitude' => 120.97920500,
            ],
            [
                'facility_name' => 'Kubo 1 (Resting Area - Bldg 1 Front)',
                'latitude' => 14.24094500,
                'longitude' => 120.97888000,
            ],
            [
                'facility_name' => 'Kubo 2 (Resting Area - Bldg 2 Front)',
                'latitude' => 14.24087000,
                'longitude' => 120.97928500,
            ],
            [
                'facility_name' => 'Kubo 3 (Resting Area - Back of Bldg 3)',
                'latitude' => 14.24165500,
                'longitude' => 120.97904500,
            ],
            [
                'facility_name' => 'Main Emergency Exit (Roundabout)',
                'latitude' => 14.24032000,
                'longitude' => 120.97856000,
            ],
            [
                'facility_name' => 'Evacuation Area (Silang Monitoring Center)',
                'latitude' => 14.24036000,
                'longitude' => 120.97914000,
            ],
        ];

        foreach ($facilities as $facility) {
            DB::table('facilities')->insert($facility);
        }

        Schema::enableForeignKeyConstraints();
    }
}
