<?php

namespace Modules\GoogleDrive\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Google\Client;
use Google\Service\Oauth2;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use App\Models\Setting;


class GoogleDriveSetting extends Model
{
    use HasFactory;

    protected $table = 'google_drive_settings';

    protected $fillable = [
        'module',
        'status',
        'workspace_id',
        'type',
        'value',
        'name',
    ];

    // Authenticate with google
    public static function redirectToGoogle()
    {
        session()->put('google_auth_back_url' , \URL::previous());

        try {
            $client = new Client();
            $client->setAuthConfig(base_path(company_setting('google_drive_json_file')));
            $client->setRedirectUri(route('auth.google.callback'));
            $client->addScope('https://www.googleapis.com/auth/drive');
            $client->setAccessType('offline');
            return redirect($client->createAuthUrl());

        } catch (\Exception $e) {
            return false;
        }
    }

    // Authenticate with google callback functon
    public static function handleGoogleCallback($code)
    {

            $client = new Client();
            $client->setAuthConfig(base_path(company_setting('google_drive_json_file')));
            $client->setRedirectUri(route('auth.google.callback'));

            $token = $client->fetchAccessTokenWithAuthCode($code);

            $post['google_drive_token']      = $token;

            if(isset($token['refresh_token']))
            {
                $post['google_drive_refresh_token']      = $token['refresh_token'];
            }
            $check = GoogleDriveSetting::saveSettings($post);

    }


    // Upload the file
    public static function saveSettings($post)
    {
        try {

            $getActiveWorkSpace = getActiveWorkSpace();
            $creatorId = creatorId();

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'workspace' => $getActiveWorkSpace,
                    'created_by' => $creatorId,
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();

            return true;

        } catch (\Exception $e) {
            return false;
        }

    }
    // Get parent Module
    public static function parent_module($module='')
    {
        $drive_module = GoogleDriveSetting::where('name',$module)->first();
        return $drive_module->module;
    }

    //Get All modules from DB has file upload
    public static function get_modules()
    {
        $settings_modules = GoogleDriveSetting::get();

        // $data = [];
        // foreach($settings_modules as $key => $module){

        //     $sub_modules = GoogleDriveSetting::where('module',$module->module)->pluck('name');
        //     $data[$module->module]  = $sub_modules;
        // }

        $groupedSettings = $settings_modules->groupBy('module');

        $data = [];

        // Iterate through each module and extract sub-modules
        foreach ($groupedSettings as $module => $subModules) {
            $data[$module] = $subModules->pluck('name')->toArray();
        }


        return $data ;
    }

    // Check if folder is assigned to the sub module
    public static function is_folder_assigned($module = '' , $record_id = '')
    {
        $google_drive_modules = GoogleDriveSetting::where('workspace_id' , getActiveWorkSpace())->get();

        foreach ($google_drive_modules as $row) {
            $data[$row->name] = $row->value;
        }

        if(isset($data[$module]) || !empty($data[$module]) )
        {
            $folder = json_decode($data[$module]);

            if (!isset($folder[0]->$module) || empty($folder[0]->$module)) {

                return false;

            }else{

                return true;
            }
        }else{

            return false;

        }
    }

    // Get assigned folder Id form sub module
    public static function get_folderId_by_name($module = '' , $record_id = '')
    {
        $google_drive_modules = GoogleDriveSetting::where('name',$module)->where('workspace_id' , getActiveWorkSpace())->first();

        if(isset($google_drive_modules->value) && !empty($google_drive_modules->value) && $google_drive_modules->value != '')
        {
            $folder = json_decode($google_drive_modules->value);

            if (!isset($folder[0]->$module) || empty($folder[0]->$module)) {

                return '';
            }else{

                return $folder[0]->$module;
            }
        }else{

            return '';
        }
    }

    public static function assign_folder_to_module($module = '' , $folder_id , $record_id = '')
    {
        $datas[$module]= $folder_id;
        $data[] = $datas;
        $data = json_encode($data);

        GoogleDriveSetting::updateOrCreate(['name' =>  $module ,'workspace_id' => getActiveWorkSpace()],['value' => $data]);
    }

    // get Parent folder of the Google Drive by module
    public static function get_parent_folder_Id($module,$folder_id)
    {
        $company_settings        = getCompanyAllSetting();
        $google_drive_json_file  = isset($company_settings['google_drive_json_file']) ? $company_settings['google_drive_json_file'] : '';
        $google_drive_token      = isset($company_settings['google_drive_token']) ? $company_settings['google_drive_token'] : '';

        try
        {
            $client = new Client();
            $client->setAuthConfig(base_path($google_drive_json_file));
            $client->setAccessToken($google_drive_token); // Set the user's access token

            $drive =  new Drive($client);
            $folder = $drive->files->get($folder_id, ['fields' => 'id,name, parents']);
            // $x['id'] = $folder->getParents()[0];
            // $x['name'] = $folder->name;
            return  $folder->getParents()[0];
        }
        catch (\Exception $e)
        {
            return false;
        }

    }

    // Get Mimetype of the file
    public static function get_mimetype($type='')
    {
        $mimeTypesToConvert = [
            'application/msword' => 'application/vnd.google-apps.document', // Word Document (DOC)
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'application/vnd.google-apps.document', // Word Document (DOCX)
            'application/vnd.ms-excel' => 'application/vnd.google-apps.spreadsheet', // Excel Spreadsheet (XLS)
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'application/vnd.google-apps.spreadsheet', // Excel Spreadsheet (XLSX)
            'application/vnd.ms-powerpoint' => 'application/vnd.google-apps.presentation', // PowerPoint Presentation (PPT)
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'application/vnd.google-apps.presentation', // PowerPoint Presentation (PPTX)
            'text/plain' => 'application/vnd.google-apps.document', // Plain Text
            'text/csv' => 'application/vnd.google-apps.spreadsheet', // CSV File
            // 'image/jpeg' => 'application/vnd.google-apps.photo', // JPEG Image
            // 'image/png' => 'application/vnd.google-apps.photo', // PNG Image
            'application/pdf' => 'application/vnd.google-apps.document', // PDF Document
            // 'application/zip' => 'application/vnd.google-apps.document', // ZIP Archive
            // Add more mappings as needed
        ];

        $data = [];
        foreach($mimeTypesToConvert as $key => $value)
        {
            $data[$key]  = $value;
        }

        if(isset($data[$type]))
        {
            return $data[$type];

        }else{

            return $type;
        }
    }

    // Upload the file
    public static function upload_file($fileMetadata, $filePath='')
    {
        $company_settings        = getCompanyAllSetting();
        $google_drive_json_file  = isset($company_settings['google_drive_json_file']) ? $company_settings['google_drive_json_file'] : '';
        $google_drive_token      = isset($company_settings['google_drive_token']) ? $company_settings['google_drive_token'] : '';


        GoogleDriveSetting::get_mimetype(mime_content_type($filePath));
        $client = new Client();
        $client->setAuthConfig(base_path($google_drive_json_file));
        $client->setAccessToken($google_drive_token); // Set the user's access token

        $drive =  new Drive($client);

        return $drive->files->create($fileMetadata, [
            'data' => file_get_contents($filePath),
            'uploadType' => 'multipart',
            'mimeType' => mime_content_type($filePath) , // Set the correct MIME type dynamically
        ]);

    }

    // Create New folder
    public static function create_new_drive_folder($folder_name ,$module='', $folder_id='')
    {
        $company_settings        = getCompanyAllSetting();
        $google_drive_json_file  = isset($company_settings['google_drive_json_file']) ? $company_settings['google_drive_json_file'] : '';
        $google_drive_token      = isset($company_settings['google_drive_token']) ? $company_settings['google_drive_token'] : '';


        $parentFolderId = !empty($folder_id) ? $folder_id : 'root' ;

        // Create metadata for the new folder
        $folderMetadata = new DriveFile([
            'name' => $folder_name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentFolderId], // Set the parent folder ID
        ]);

        $client = new Client();
        $client->setAuthConfig(base_path($google_drive_json_file));
        $client->setAccessToken($google_drive_token); // Set the user's access token

        $drive =  new Drive($client);
        $createdFolder = $drive->files->create($folderMetadata);

        if(!empty($module) &&  $module != ''){

            GoogleDriveSetting::assign_folder_to_module($module , $createdFolder->id);
        }

        return $createdFolder;
    }

    //get views data via submodule name
    public static function get_view_to_stack_hook()
    {
        $views = [
            'Account'                       => ['sales::salesaccount.index','sales::salesaccount.grid'],
            'Accounts'                      => ['account::bankAccount.index'],
            'Assets'                        => ['assets::index'],
            'Bill'                          => ['account::bill.index','account::bill.grid'],
            'Bug'                           => ['taskly::projects.bug_report','taskly::projects.bug_report_list'],
            'Cases'                         => ['sales::commoncase.index','sales::commoncase.grid'],
            'Contracts'                     => ['contract::contracts.index','contract::contracts.grid'],
            'Customer'                      => ['account::customer.index','account::customer.grid'],
            'Deal'                          => ['lead::deals.index','lead::deals.list'],
            'Document'                      => ['hrm::document.index'],
            'Documents'                     => ['aidocument::document.index'],
            'Employee'                      => ['hrm::employee.index','hrm::employee.grid'],
            'Event'                         => ['hrm::event.index'],
            'Interview Schedule'            => ['recruitment::interviewSchedule.index'],
            'Invoice'                       => ['invoice.index','invoice.grid'],
            'Job Application'               => ['recruitment::jobApplication.index','recruitment::jobApplication.list'],
            'Jobs'                          => ['recruitment::job.index','recruitment::job.grid'],
            'Knowledge'                     => ['support-ticket::knowledge.index'],
            'Lead'                          => ['lead::leads.index','lead::leads.list'],
            'Leave'                         => ['hrm::leave.index'],
            'Meeting'                       => ['sales::meeting.index','sales::meeting.grid'],
            'Notes'                         => ['notes::index'],
            'Opportunities'                 => ['sales::opportunities.index','sales::opportunities.grid'],
            'Payslip'                       => ['hrm::payslip.index'],
            'POS Order'                     => ['pos::pos.report','pos::pos.grid'],
            'Products'                      => ['product-service::index','product-service::grid'],
            'Projects'                      => ['taskly::projects.show'],
            'Proposal '                     => ['proposal.index','proposal.grid'],
            'Purchase'                      => ['purchases.index','purchases.grid'],
            'Retainers'                     => ['retainer::retainer.index','retainer::retainer.grid'],
            'Revenue'                       => ['account::revenue.index'],
            'Sales Document'                => ['sales::document.index','sales::document.grid'],
            'Sales Invoice'                 => ['sales::salesinvoice.index','sales::salesinvoice.grid'],
            'Task'                          => ['taskly::projects.taskboard','taskly::projects.tasklist'],
            'Tickets'                       => ['support-ticket::ticket.index','support-ticket::ticket.grid'],
            'Transaction'                   => ['account::transaction.index'],
            'Vender'                        => ['account::vendor.index','account::vendor.grid'],
            'Warehouse'                     => ['warehouses.index'],
            'Quotation'                     => ['quotation::quotation.index'],
            'JournalEntry'                  => ['double-entry::journalEntry.index'],
            'Ledger'                        => ['double-entry::report.ledger'],
            'BalanceSheet'                  => ['double-entry::report.balance_sheet','double-entry::report.balance_sheet_horizontal'],
            'Business'                      => ['vcard::business.index','vcard::business.grid'],
            'Appointments'                  => ['vcard::appointments.index','vcard::appointments.calender'],
            'Course'                        => ['lms::course.index'],
            'Custom Page'                   => ['lms::pageoption.index'],
            'Course Order'                  => ['lms::course_orders.index','lms::course_orders.corse_order_index'],
            'LMS Student'                   => ['lms::student.index'],
            'PlayList'                      => ['tvstudio::playlist.index'],
            'Content'                       => ['tvstudio::content.index'],
            'Custom Pages'                  => ['tvstudio::custom_page.index'],
            'Blog'                          => ['tvstudio::blog.index'],
            'Cast'                          => ['movie-show-booking-system::moviecast.index'],
            'Order'                         => ['movie-show-booking-system::movieorder.index'],
            'Booking'                       => ['holidayz::booking.index','holidayz::booking.calender'],
            'Customers'                     => ['holidayz::hotel_customer.customers.index'],
            'BankTransfer'                  => ['holidayz::roomBookingBankTransfer.index'],
            'Services'                      => ['holidayz::services.index'],
            'Admission'                     => ['school::admission.index'],
            'School Student'                => ['school::student.index'],
            'Teacher Timetable'             => ['school::teacher-timetable.index'],
            'Timetable'                     => ['school::timetable.index'],
            'Homework'                      => ['school::homework.index'],
            'Trainer'                       => ['gym-management::trainer.index'],
            'Workout Plan'                  => ['gym-management::workout-plan.index'],
            'Music Student'                 => ['music-institute::music-student.index','music-institute::music-student.grid'],
            'Instrument'                    => ['music-institute::music-instrument.index'],
            'Music Lesson'                  => ['music-institute::music-lesson.index'],
            'Inquiry'                       => ['childcare-management::inquiry.index'],
            'Childs'                        => ['childcare-management::childs.index'],
            'Driving Student'               => ['driving-school::student.index'],
            'Driving Lesson'                => ['driving-school::lesson.index'],
            'Driving Invoice'               => ['driving-school::invoice.index'],
            'Fleet Driver'                  => ['fleet::driver.index','fleet::driver.grid'],
            'Fleet Booking'                 => ['fleet::booking.index'],
            'Insurance'                     => ['fleet::insurance.index'],
            'Maintenance'                   => ['fleet::maintenance.index'],
            'Vehicle Booking'               => ['vehicle-booking-management::booking.index'],
            'RouteManage'                   => ['vehicle-booking-management::routemanage.index'],
            'CarDealership Product'         => ['car-dealership::product.index'],
            'CarDealership Purchase'        => ['car-dealership::purchase.index','car-dealership::purchase.grid'],
            'AgricultureFleet'              => ['agriculture-management::agriculturefleet.index'],
            'Agriculture Equipment'         => ['agriculture-management::agricultureequipment.index'],
            'Agriculture Office'            => ['agriculture-management::agricultureoffice.index'],
            'Agriculture ServiceProduct'    => ['agriculture-management::agricultureserviceproduct.index'],
            'Agriculture Service'           => ['agriculture-management::agricultureservices.index'],
            'Advocate'                      => ['legal-case-management::advocate.index'],
            'Fee Receive'                   => ['legal-case-management::fee-receive.index'],
            'Case Expense'                  => ['legal-case-management::case-expense.index'],
            'Tour'                          => ['tour-travel-management::tour.index'],
            'Tour Booking Detail'           => ['tour-travel-management::tour_bookings_details.index'],
            'Policies'                      => ['insurance-management::policies.index'],
            'Claim'                         => ['insurance-management::claim.index'],
            'Pending Courier'               => ['courier-management::pending_courier.index'],
            'Payment Information'           => ['courier-management::payment_information.index'],
            'Freight Customer'              => ['freight-management-system::customer.index'],
            'Freight BookingRequest'        => ['freight-management-system::booking-request.index'],
            'Freight Invoice'               => ['freight-management-system::invoice.index'],
            'Garage Vehicle'                => ['garage-management::vehicle.index'],
            'Garage Jobcard'                => ['garage-management::jobcard.index'],
            'Distributions'                 => ['newspaper::distributions.index'],
            'Journalist'                    => ['newspaper::journalist.index'],
            'Journalist Info'               => ['newspaper::journalist_info.index'],
            'Journalist Ads'                => ['newspaper::ads.index'],
            'Property'                      => ['property-management::property.index'],
            'Tenant'                        => ['property-management::tenant.index','property-management::tenant.grid'],
            'Property Invoice'              => ['property-management::propertyinvoice.index'],
            'Maintenance Request'           => ['property-management::maintenance-request.index'],
            'Doctor'                        => ['hospital-management::doctor.index'],
            'Hospital Appointment'          => ['hospital-management::appointment.index'],
            'Hospital MedicalRecord'        => ['hospital-management::medical-record.index'],
            'Medicallab Appointment'        => ['medical-lab-management::appointment.index'],
            'Medicallab Patients'           => ['medical-lab-management::patients.index'],
            'Creativity'                    => ['innovation-center::creativity.index','innovation-center::creativity.grid','innovation-center::creativity.treeview','innovation-center::creativity.kanban_view'],
            'Challenges'                    => ['innovation-center::challenges.index'],
            'RoadMap Ideas'                 => ['road-map-central::ideas.index'],
            'RoadMap'                       => ['road-map-central::roadmap.index'],
            'Newsletter'                    => ['newsletter::index'],
            'Workload'                      => ['team-workload::workload.index'],
            'ToDo'                          => ['to-do::todo.index','to-do::todo.board'],
            'ToDo Calendar'                 => ['to-do::todo.calendar'],
            'Visitors'                      => ['visitor-management::visitors.index'],
            'Visitors Reports'              => ['visitor-management::reports.index'],
            'Consignment Product'           => ['consignment-management::product.index'],
            'Consignment'                   => ['consignment-management::consignment.index'],
        ];

        return $views;
    }

}
