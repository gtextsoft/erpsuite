<?php

namespace Modules\GoogleDrive\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\GoogleDrive\Entities\GoogleDriveSetting;
use Rawilk\Settings\Settings;
use Google\Client;
use Google\Service\Oauth2;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;


class GoogleDriveController extends Controller
{

    public function index(Request $request , $module , $folder_id='root', $view='')
    {
        
        if(\Auth::user()->isAbleTo('googledrive manage')){
            $company_settings   =    getCompanyAllSetting();
            if (
                !isset($company_settings['google_drive_token']) || 
                !isset($company_settings['google_drive_refresh_token']) || 
                $company_settings['google_drive_token'] == '' || 
                $company_settings['google_drive_refresh_token'] == '' || 
                !isset($company_settings['google_drive_json_file']) || 
                $company_settings['google_drive_json_file'] == ''
            ) {
                return redirect()->route('settings.index')->with('error', 'Please complete the synchronization process.');
            }

            session()->put($module, \URL::previous());

            if(array_key_exists($module.'_drive', $company_settings)){

                $google_drive_modules = GoogleDriveSetting::get();
                $parent_folder_id ='';
                $google_drive_files = '';

                if((array_key_exists('google_drive_token',$company_settings) && array_key_exists('google_drive_refresh_token',$company_settings)) ){

                    $parent_folder_id =  GoogleDriveSetting::get_parent_folder_Id( $module , $folder_id );
                    $folder_id = GoogleDriveSetting::get_folderId_by_name($module);
                    $google_drive_files = $this->getfiles($folder_id);

                    // if($google_drive_files == false){
                    //     return redirect(route('auth.google'));
                    // }
                }
                if(!empty($view) && $view == 'grid'){

                    return view('google-drive::layouts.google_drive_index_grid', compact('google_drive_modules','google_drive_files','parent_folder_id','folder_id','module'));
                }else{
                    return view('google-drive::layouts.google_drive_index', compact('google_drive_modules','google_drive_files','parent_folder_id','folder_id','module'));
                }
            }

            return redirect()->back()->with('error',__('Permission Denied!'));
        }
        else{
            return redirect()->back()->with('error',__('Permission Denied!'));
        }
    }

    public function getmodulefiles(Request $request , $module , $folder_id='root', $view='')
    {
        if(\Auth::user()->isAbleTo('googledrive manage')){
            if(company_setting($module.'_drive')){

                if(GoogleDriveSetting::is_folder_assigned())
                {
                    return redirect()->route('googledrive.index');
                }

                $google_drive_modules = GoogleDriveSetting::get();
                $google_drive_files = $this->getfiles($folder_id);
                $parent_folder_id =  GoogleDriveSetting::get_parent_folder_Id( $module , $folder_id );
                // $parent_folder_id = $parent_folder['id'];
                // $parent_folder_name = $parent_folder['name'];

                if(!empty($view) && $view == 'grid'){

                    return view('google-drive::layouts.google_drive_index_grid', compact('google_drive_modules','google_drive_files','parent_folder_id','folder_id','module'));

                }else{

                    return view('google-drive::layouts.google_drive_index', compact('google_drive_modules','google_drive_files','parent_folder_id','folder_id','module'));
                }
            }

            return redirect()->back()->with('error',__('permission Denied!'));

        }else{

            return redirect()->back()->with('error',__('permission Denied!'));
        }

    }

    public function getfiles($folder_id='')
    {
        try{
            if(!empty($folder_id)){

                $query = "'$folder_id' in parents";

            }else{

                $query = "'root' in parents";
            }

            $drive = $this->get_drive();

            if($drive){

                $files = $this->get_drive()->files->listFiles(['fields' => '*','q' => $query]);

            }else{

                return false;
            }
            return  $files->getFiles();
        }
        catch (\Exception $e)
        {
            return false;
            // return $error = $e->getMessage();
        }
    }

    // Get new access token
    public function getNewAccessToken()
    {
        $company_settings        = getCompanyAllSetting();
        $google_drive_json_file  = isset($company_settings['google_drive_json_file']) ? $company_settings['google_drive_json_file'] : '';
        $google_drive_token      = isset($company_settings['google_drive_token']) ? $company_settings['google_drive_token'] : '';

        try{
            $client = new Client();
            $client->setAuthConfig(base_path($google_drive_json_file));
            $newAccessToken = $client->fetchAccessTokenWithRefreshToken($google_drive_token);

            $post['google_drive_token']             = $newAccessToken;
            $post['google_drive_refresh_token']     = $newAccessToken['refresh_token'];

            $check = GoogleDriveSetting::saveSettings($post);

            if($check){
                return true;
            }else{
                return false;
            }

            return  true;

        } catch (\Exception $e) {

            return false;
        }
    }

    // check access token
    public function isGoogleTokenExpired()
    {

        $googleClient = new Client();
        $googleClient->setAccessToken(company_setting('google_drive_token'));

        if ($googleClient->isAccessTokenExpired()) {
            return true; // Token has expired
        }
        return false; // Token is still valid
    }

    // Get Google Drive with Client
    public function get_drive()
    {
        $company_settings        =    getCompanyAllSetting();
        $google_drive_json_file  = isset($company_settings['google_drive_json_file']) ? $company_settings['google_drive_json_file'] : '';
        $google_drive_token      = isset($company_settings['google_drive_token']) ? $company_settings['google_drive_token'] : '';

        try{
            // Create a new Google Client instance
            $client = new Client();
            $client->setAuthConfig(base_path($google_drive_json_file));
            if($this->isGoogleTokenExpired())
            {
                if($this->getNewAccessToken()){

                    $client->setAccessToken($google_drive_token); // Set the user's access token
                }

            }else{

                $client->setAccessToken($google_drive_token); // Set the user's access token
            }

            return  new Drive($client);

        } catch (\Exception $e) {

            return false;
        }
    }

    // Authenticate with google
    public function redirectToGoogle()
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
            return redirect(\Session::get('google_auth_back_url'))->with('error',__('Something Went Wrong!'));
        }
    }

    // Authenticate with google callback functon
    public function handleGoogleCallback(Request $request)
    {
        try {

            $client = new Client();
            $client->setAuthConfig(base_path(company_setting('google_drive_json_file')));
            $client->setRedirectUri(route('auth.google.callback'));

            $token = $client->fetchAccessTokenWithAuthCode($request->code);

            $post['google_drive_token']      = $token;

            if(isset($token['refresh_token']))
            {
                $post['google_drive_refresh_token']      = $token['refresh_token'];
            }

            $check = GoogleDriveSetting::saveSettings($post);
            if($check){
                return redirect(\Session::get('google_auth_back_url')); // Redirect to dashboard after successful login
            }else{
                return redirect()->back()->with('error','Something Went Wrong!');
            }

        } catch (\Exception $e) {

            return redirect(\Session::get('google_auth_back_url'))->with('error', __('Something Went Wrong!'));

        }
    }

    // Assign folder to the sub module
    public function assign_folder($module='')
    {
        $parent_module = GoogleDriveSetting::parent_module($module);
        $google_drive_modules = GoogleDriveSetting::where('module',$parent_module)->get();
        try {
            $files = $this->get_drive()->files->listFiles(['fields' => '*']);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 401);

        }
        $google_drive_files = $files->getFiles();
        return view('google-drive::folders.assign_folder',compact('google_drive_modules','google_drive_files','module'));
    }

    // Assign folder to the sub module
    public function assign_folder_store(Request $request , $module='')
    {
        GoogleDriveSetting::assign_folder_to_module($module , $request->parent_id);
        return redirect()->route('googledrive.module.index',[$module,$request->parent_id]);
    }

    // Create New folder in google Drive will be assigned automatically to the sub module
    public function create_folder($module , $folder_id='')
    {
        return view('google-drive::folders.create',compact('folder_id','module'));
    }

    // Create New folder in google Drive will be assigned automatically to the sub module
    public function store_folder(Request $request, $module , $folder_id='')
    {
        $createdFolder = GoogleDriveSetting::create_new_drive_folder($request->folder_name ,$module , $folder_id );
        return redirect()->back()->with('success',__('Folder Created Successfully'));
    }

    // Store sub module settings
    public function GoogleDriveSettingsStore(Request $request)
    {

        if($request->has('google_drive'))
        {
            foreach($request->google_drive as $key => $value)
            {
                $post[$key]          = $value;
            }
        }

        if($request->has('google_drive_json_file'))
        {
            $google_drive_json_file = time()."-google_drive_json_file." . $request->google_drive_json_file->getClientOriginalExtension();
            $path = upload_file($request,'google_drive_json_file',$google_drive_json_file,'google_drive_json',[]);
            if($path['flag']==0){
                return redirect()->back()->with('error', __($path['msg']));
            }

            $settings        =    getCompanyAllSetting();
            $google_drive_json_file  = isset($settings['google_drive_json_file']) ? $settings['google_drive_json_file'] : '';

            // old img delete
            if(!empty($google_drive_json_file) && strpos($google_drive_json_file,'avatar.png') == false && check_file($google_drive_json_file))
            {
                delete_file($google_drive_json_file);
            }
            $post['google_drive_json_file']         = $path['url'];
            $post['google_drive_token']             = '';
            $post['google_drive_refresh_token']     = '';

        }

        $check = GoogleDriveSetting::saveSettings($post);
        if($check){
            return redirect()->back()->with('success','Google Drive Setting saved sucessfully.');
        }else{
            return redirect()->back()->with('error','Something Went Wrong!');
        }
    }

    public function uploadfiles_create($module)
    {
        $folder_id = GoogleDriveSetting::get_folderId_by_name($module);
        return view('google-drive::folders.addfiles',compact('module','folder_id'));
    }

    public function uploadfiles_store(Request $request, $module)
    {
        if($request->hasFile('file'))
        {
            $file = $request->file('file');
            $filePath = $file->getPathname();
            $filename = $request->file('file')->getClientOriginalName();
            $FolderId = GoogleDriveSetting::get_folderId_by_name($module);
            $mimetype = GoogleDriveSetting::get_mimetype(mime_content_type($filePath));

            $fileMetadata = new DriveFile([
                'name' => $filename,
                'mimeType' => $mimetype,
                'parents' => [$FolderId], // Set the parent folder ID
            ]);

            $x = GoogleDriveSetting::upload_file($fileMetadata , $filePath);
            return $res = [
                'flag' => 1,
                'msg'  =>'File Uploaded Successfully',
            ];

        } else {

            return $res = [
                'flag' => 2,
                'msg'  =>'Something went wrong!',
            ];
        }
    }

    public function delete_file($folderId)
    {
        $this->get_drive()->files->delete($folderId);
        return redirect()->back()->with('success',__('File Deleted Successfully!'));
    }

}
