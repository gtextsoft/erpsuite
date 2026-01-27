<?php

namespace Modules\GoogleDrive\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\GoogleDrive\Entities\GoogleDriveSetting;

class GoogleDriveModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $google_drive_modules = [

            'Account'       => ['Customer','Vender','Accounts','Revenue','Bill','Transaction'] ,
            'AIImage'       => ['Generated Image'] ,
            'General'       => ['Invoice','Proposal '] ,
            'Contract'      => ['Contracts'] ,
            'Hrm'           => ['Employee','Payslip','Event','Leave','Event','Document'] ,
            'Lead'          => ['Lead','Deal'] ,
            'Pos'           => ['Warehouse','Purchase','POS Order'] ,                  
            'ProductService'=> ["Products"] ,
            'Recruitment'   => ['Jobs','Job Application','Interview Schedule'] ,
            'Retainer'      => ['Retainers'] ,
            'Sales'         => ['Account','Opportunities','Sales Invoice','Cases','Sales Document','Meeting'] ,
            'SupportTicket' => ['Tickets','Knowledge'] ,
            'Taskly'        => ['Projects','Task','Bug'] ,
            'AIDocument'    => ['Documents'],
            'Notes'         => ['Notes'],
            'Assets'        => ['Assets'],
            'Quotation'     => ['Quotation'],
            'DoubleEntry'   => ['JournalEntry','Ledger','BalanceSheet'],
            'VCard'         => ['Business','Appointments'],
            'LMS'           => ['Course','Custom Page','Course Order','LMS Student'],
            'TVStudio'      => ['PlayList','Content','Custom Pages','Blog'],
            'MovieShowBookingSystem' => ['Cast','Order'],
            'Holidayz'      => ['Booking','Customers','BankTransfer','Services'],
            'School'        => ['Admission','School Student','Teacher Timetable','Timetable','Homework'],
            'GymManagement' => ['Trainer','Workout Plan'],
            'MusicInstitute'=> ['Music Student','Instrument','Music Lesson'],
            'ChildcareManagement'=> ['Inquiry','Childs'],
            'DrivingSchool' => ['Driving Student','Driving Lesson','Driving Invoice'],
            'Fleet'         => ['Fleet Driver','Fleet Booking','Insurance','Maintenance'],
            'VehicleBookingManagement' => ['Vehicle Booking','RouteManage'],
            'CarDealership' => ['CarDealership Product','CarDealership Purchase'],
            'AgricultureManagement'   => ['AgricultureFleet','Agriculture Equipment','Agriculture Office','Agriculture ServiceProduct','Agriculture Service'],
            'LegalCaseManagement'     => ['Advocate','Fee Receive','Case Expense'],
            'TourTravelManagement'    => ['Tour','Tour Booking Detail'],
            'InsuranceManagement'     => ['Policies','Claim'],
            'CourierManagement'       => ['Pending Courier','Payment Information'],
            'FreightManagementSystem' => ['Freight Customer','Freight BookingRequest','Freight Invoice'],
            'GarageManagement'        => ['Garage Vehicle','Garage Jobcard'],
            'Newspaper'               => ['Distributions','Journalist','Journalist Info','Journalist Ads'],
            'PropertyManagement'      => ['Property','Tenant','Property Invoice','Maintenance Request'],
            'HospitalManagement'      => ['Doctor','Hospital Appointment','Hospital MedicalRecord'],
            'MedicalLabManagement'    => ['Medicallab Appointment','Medicallab Patients'],
            'InnovationCenter'        => ['Creativity','Challenges'],
            'RoadMapCentral'          => ['RoadMap Ideas','RoadMap'],
            'Newsletter'              => ['Newsletter'],
            'TeamWorkload'            => ['Workload'],
            'ToDo'                    => ['ToDo','ToDo Calendar'],
            'VisitorManagement'       => ['Visitors','Visitors Reports'],
            'ConsignmentManagement'   => ['Consignment Product','Consignment'],
        ];

        foreach($google_drive_modules as $key=> $module)
        {
            foreach($module as $key1 => $sub_module)
            {
                if ($sub_module == 'Custom Pages' && $key == 'TVStudio') {
                    $ntfy = GoogleDriveSetting::where('name','Custom Page')->where('module',$key)->count();
                    if($ntfy != 0){
                        $update = GoogleDriveSetting::where('name','Custom Page')->where('module',$key)->first();
                        $update->name = $sub_module;
                        $update->value = '';
                        $update->module = $key;
                        $update->save();
                    } else {
                        $new_one = GoogleDriveSetting::where('name',$sub_module)->where('module',$key)->count();
                        if($new_one == 0){
                            $new = new GoogleDriveSetting();
                            $new->name = $sub_module;
                            $new->value = '';
                            $new->module = $key;
                            $new->save();
                        }
                    }
                } else {
                    $ntfy = GoogleDriveSetting::where('name',$sub_module)->where('module',$key)->count();
                    if($ntfy == 0){
                        $new = new GoogleDriveSetting();
                        $new->name = $sub_module;
                        $new->value = '';
                        $new->module = $key;
                        $new->save();
                    }
                }
            }
        }
    }
}
