<?php

namespace Modules\Account\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Account\Entities\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Modules\Account\Events\CreateCustomer;
use Modules\Account\Events\DestroyCustomer;
use Modules\Account\Events\UpdateCustomer;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('customer manage'))
        {
            $customers = User::where('workspace_id',getActiveWorkSpace())
                        ->leftjoin('customers', 'users.id', '=', 'customers.user_id')
                        ->where('users.type', 'Client')
                        ->select('users.*','customers.*', 'users.name as name', 'users.email as email', 'users.id as id','users.mobile_no as contact')
                        ->get();
            return view('account::customer.index', compact('customers'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if(\Auth::user()->isAbleTo('customer create'))
        {
            if(module_is_active('CustomField')){
                $customFields =  \Modules\CustomField\Entities\CustomField::where('workspace_id',getActiveWorkSpace())->where('module', '=', 'Account')->where('sub_module','Customer')->get();
            }else{
                $customFields = null;
            }

            return view('account::customer.create',compact('customFields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('customer create'))
        {
            $canUse =  PlanCheck('User', Auth::user()->id);
            if ($canUse == false) {
                return redirect()->back()->with('error', 'You have maxed out the total number of customer allowed on your current plan');
            }
            $rules = [
                'name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'billing_name' => 'required',
                'billing_phone' => 'required',
                'billing_address' => 'required',
                'billing_city' => 'required',
                'billing_state' => 'required',
                'billing_country' => 'required',
                'billing_zip' => 'nullable',
            ];
            $validator = \Validator::make($request->all(), $rules);
            if(empty($request->user_id))
            {
                $rules = [
                    'email' => 'required|email|unique:users',
                    'password' => 'required',
                    'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                ];
                $validator = \Validator::make($request->all(), $rules);
            }

            if ($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('customer.index')->with('error', $messages->first());
            }
            $roles = Role::where('name','client')->where('guard_name','web')->where('created_by',creatorId())->first();
            if(empty($roles))
            {
                return redirect()->back()->with('error', __('Cilent Role Not found !'));
            }
            if(!empty($request->user_id))
            {
                $user = User::find($request->user_id);

                if(empty($user))
                {
                    return redirect()->back()->with('error', __('Something went wrong please try again.'));
                }
                if($user->name != $request->name)
                {
                    $user->name = $request->name;
                    $user->save();
                }
                if($user->mobile_no != $request->contact)
                {
                    $user->mobile_no = $request->contact;
                    $user->save();
                }
            }
            else
            {
                $user = User::create(
                    [
                        'name' => $request['name'],
                        'email' => $request['email'],
                        'mobile_no' => $request['contact'],
                        'password' => Hash::make($request['password']),
                        'email_verified_at' => date('Y-m-d h:i:s'),
                        'type' => $roles->name,
                        'lang' => 'en',
                        'workspace_id' => getActiveWorkSpace(),
                        'active_workspace' =>getActiveWorkSpace(),
                        'created_by' => creatorId(),
                        ]
                    );
                    $user->save();
                    $user->addRole($roles);
            }
                $customer                  = new Customer();
                $customer->user_id         = $user->id;
                $customer->customer_id     = $this->customerNumber();
                $customer->name            = !empty($request->name) ? $request->name : null;
                $customer->contact         = !empty($request->contact) ? $request->contact : null;
                $customer->email           = !empty($user->email) ? $user->email : null;
                $customer->tax_number      = !empty($request->tax_number) ? $request->tax_number : null;
                $customer->password        = null;
                $customer->billing_name    = !empty($request->billing_name) ? $request->billing_name : null;
                $customer->billing_country = !empty($request->billing_country) ? $request->billing_country : null;
                $customer->billing_state   = !empty($request->billing_state) ? $request->billing_state : null;
                $customer->billing_city    = !empty($request->billing_city) ? $request->billing_city : null;
                $customer->billing_phone   = !empty($request->billing_phone) ? $request->billing_phone : null;
                $customer->billing_zip     = !empty($request->billing_zip) ? $request->billing_zip : null;
                $customer->billing_address = !empty($request->billing_address) ? $request->billing_address : null;

                $customer->shipping_name    = !empty($request->shipping_name) ? $request->shipping_name : null;
                $customer->shipping_country = !empty($request->shipping_country) ? $request->shipping_country : null;
                $customer->shipping_state   = !empty($request->shipping_state) ? $request->shipping_state : null;
                $customer->shipping_city    = !empty($request->shipping_city) ? $request->shipping_city : null;
                $customer->shipping_phone   = !empty($request->shipping_phone) ? $request->shipping_phone : null;
                $customer->shipping_zip     = !empty($request->shipping_zip) ? $request->shipping_zip : null;
                $customer->shipping_address = !empty($request->shipping_address) ? $request->shipping_address : null;
                $customer->lang             = !empty($user->lang) ? $user->lang : '';

                $customer->workspace        = getActiveWorkSpace();
                $customer->created_by      = \Auth::user()->id;

                $customer->save();


                if(module_is_active('CustomField'))
                {
                    \Modules\CustomField\Entities\CustomField::saveData($customer, $request->customField);
                }
                event(new CreateCustomer($request,$customer));

            return redirect()->back()->with('success', __('Customer details successfully saved.'));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $e_id)
    {
        if (Auth::user()->isAbleTo('customer show'))
        {
            $id       = \Crypt::decrypt($e_id);
            $customer = Customer::where('user_id',$id)->first();
            if(module_is_active('CustomField')){
                $customer->customField = \Modules\CustomField\Entities\CustomField::getData($customer, 'Account','Customer');
                $customFields             = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'Account')->where('sub_module','Customer','customFields')->get();
            }else{
                $customFields = null;
            }
            $invoice   = Invoice::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->where('customer_id', '=', $customer->id)->get()->pluck('id');

            $invoice_payment=InvoicePayment::whereIn('invoice_id',$invoice);

            $data['from_date']  = date('Y-m-1');
            $data['until_date'] = date('Y-m-t');

            $invoice_payment->whereBetween('date',  [$data['from_date'], $data['until_date']]);
            $invoice_payment=$invoice_payment->get();

            return view('account::customer.show', compact('customer','customFields','invoice_payment','data'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Auth::user()->isAbleTo('customer edit'))
        {
            $user         = User::where('id',$id)->where('workspace_id',getActiveWorkSpace())->first();
            $customer     = Customer::where('user_id',$id)->where('workspace',getActiveWorkSpace())->first();
            if(!empty($customer)){

                if(module_is_active('CustomField')){
                    $customer->customField = \Modules\CustomField\Entities\CustomField::getData($customer, 'Account','Customer');
                    $customFields             = \Modules\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'Account')->where('sub_module','Customer')->get();
                }else{
                    $customFields = null;
                }
            }else{
                if(module_is_active('CustomField')){
                    $customFields =  \Modules\CustomField\Entities\CustomField::where('workspace_id',getActiveWorkSpace())->where('module', '=', 'Account')->where('sub_module','Customer')->get();
                }else{
                    $customFields = null;
                }
            }

            return view('account::customer.edit', compact('customer', 'user','customFields'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('customer edit'))
        {
            $rules = [
                'name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'billing_name' => 'required',
                'billing_phone' => 'required',
                'billing_address' => 'required',
                'billing_city' => 'required',
                'billing_state' => 'required',
                'billing_country' => 'required',
                'billing_zip' => 'nullable',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $user = User::where('id',$request->user_id)->first();
            if(empty($user))
            {
                return redirect()->back()->with('error', __('Something went wrong please try again.'));
            }
            if($user->name != $request->name)
            {
                $user->name = $request->name;
                $user->save();
            }
            if($user->mobile_no != $request->contact)
            {
                $user->mobile_no = $request->contact;
                $user->save();
            }

            $customer                   = Customer::find($id);
            $customer->name             = $request->name;
            $customer->contact          = $request->contact;
            $customer->tax_number       = $request->tax_number;
            $customer->billing_name     = $request->billing_name;
            $customer->billing_country  = $request->billing_country;
            $customer->billing_state    = $request->billing_state;
            $customer->billing_city     = $request->billing_city;
            $customer->billing_phone    = $request->billing_phone;
            $customer->billing_zip      = $request->billing_zip;
            $customer->billing_address  = $request->billing_address;
            $customer->shipping_name    = $request->shipping_name;
            $customer->shipping_country = $request->shipping_country;
            $customer->shipping_state   = $request->shipping_state;
            $customer->shipping_city    = $request->shipping_city;
            $customer->shipping_phone   = $request->shipping_phone;
            $customer->shipping_zip     = $request->shipping_zip;
            $customer->shipping_address = $request->shipping_address;
            $customer->save();

            if(module_is_active('CustomField'))
            {
                \Modules\CustomField\Entities\CustomField::saveData($customer, $request->customField);
            }
            event(new UpdateCustomer($request,$customer));

            return redirect()->back()->with('success', __('Customer successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $customer = Customer::where('user_id',$id)->first();
        if (Auth::user()->isAbleTo('customer delete'))
        {
            if($customer->workspace == getActiveWorkSpace())
            {
                if(module_is_active('CustomField')){
                    $customFields = \Modules\CustomField\Entities\CustomField::where('module','Account')->where('sub_module','Customer')->get();
                    foreach($customFields as $customField)
                    {
                        $value = \Modules\CustomField\Entities\CustomFieldValue::where('record_id', '=', $customer->id)->where('field_id',$customField->id)->first();
                        if(!empty($value)){
                            $value->delete();
                        }
                    }
                }
                event(new DestroyCustomer($customer));
                $customer->delete();
                return redirect()->route('customer.index')->with('success', __('Customer successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    function customerNumber()
    {
        $latest = Customer::where('workspace',getActiveWorkSpace())->latest()->first();
        if (!$latest)
        {
            return 1;
        }

        return $latest->customer_id + 1;
    }
    public function statement(Request $request, $id)
    {
        $customer = Customer::find($id);
        $invoice   = Invoice::where('created_by', '=', creatorId())->where('workspace',getActiveWorkSpace())->where('customer_id', '=', $customer->id)->get()->pluck('id');
        $invoice_payment=InvoicePayment::whereIn('invoice_id',$invoice);
        if(!empty($request->from_date)&& !empty($request->until_date))
        {
            $invoice_payment->whereBetween('date',  [$request->from_date, $request->until_date]);

            $data['from_date']  = $request->from_date;
            $data['until_date'] = $request->until_date;
        }
        else
        {
            $data['from_date']  = date('Y-m-1');
            $data['until_date'] = date('Y-m-t');
            $invoice_payment->whereBetween('date',  [$data['from_date'], $data['until_date']]);
        }
        $invoice_payment=$invoice_payment->get();
        $currencySymbol = !empty(company_setting('defult_currancy_symbol')) ? company_setting('defult_currancy_symbol') : '$' ;
        $responseData = [
            'data' => $invoice_payment,
            'currencySymbol' => $currencySymbol,
        ];

        return response()->json($responseData);
    }
    public function grid()
    {
        if(\Auth::user()->isAbleTo('customer manage'))
        {
            $customers = User::where('workspace_id',getActiveWorkSpace())
                        ->leftjoin('customers', 'users.id', '=', 'customers.user_id')
                        ->where('users.type', 'Client')
                        ->select('users.*','customers.*', 'users.name as name', 'users.email as email', 'users.id as id')
                        ->get();
            return view('account::customer.grid', compact('customers'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fileImportExport()
    {
        if(Auth::user()->isAbleTo('customer import'))
        {
            return view('account::customer.import');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if(Auth::user()->isAbleTo('customer import'))
        {
            session_start();

            $error = '';

            $html = '';

            if ($request->file->getClientOriginalName() != '') {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);
                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++) {
                        $html .= '
                                <th>
                                    <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                    <option value="">Set Count Data</option>
                                    <option value="name">Name</option>
                                    <option value="email">Email</option>
                                    <option value="password">Password</option>
                                    <option value="contact">Contact</option>
                                    <option value="billing_name">Billing Name</option>
                                    <option value="billing_country">Billing Country</option>
                                    <option value="billing_state">Billing State</option>
                                    <option value="billing_city">Billing City</option>
                                    <option value="billing_phone">Billing Phone</option>
                                    <option value="billing_zip">Billing Zip</option>
                                    <option value="billing_address">Billing Address</option>
                                    <option value="shipping_name">Shipping Name</option>
                                    <option value="shipping_country">Shipping Country</option>
                                    <option value="shipping_state">Shipping State</option>
                                    <option value="shipping_city">Shipping City</option>
                                    <option value="shipping_phone">Shipping Phone</option>
                                    <option value="shipping_zip">Shipping Zip</option>
                                    <option value="shipping_address">Shipping Address</option>
                                    </select>
                                </th>
                                ';

                    }
                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }

                        $html .= '</tr>';

                        $temp_data[] = $row;

                    }
                    $_SESSION['file_data'] = $temp_data;
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {

                $error = 'Please Select CSV File';
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );


            echo json_encode($output);
        }
        else
        {
            return redirect()->back()->with('error', 'permission Denied');
        }

    }

    public function fileImportModal()
    {
        if(Auth::user()->isAbleTo('customer import'))
        {
            return view('account::customer.import_modal');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function customerImportdata(Request $request)
    {
        if(Auth::user()->isAbleTo('customer import'))
        {
            session_start();
            $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];

            unset($_SESSION['file_data']);

            $user = \Auth::user();

            $roles            = Role::where('created_by',creatorId())->where('name','client')->first();
            foreach ($file_data as $row) {

                $customer = Customer::where('created_by',creatorId())->where('workspace',getActiveWorkSpace())->Where('email', 'like',$row[$request->email])->get();

                if($customer->isEmpty()){
                    try {
                        $user = User::create(
                            [
                                'name' =>$row[$request->name],
                                'email' => $row[$request->email],
                                'mobile_no' => $row[$request->contact],
                                'password' => Hash::make($row[$request->password]),
                                'email_verified_at' => date('Y-m-d h:i:s'),
                                'type' => !empty($roles->name)?$roles->name:'client',
                                'lang' => 'en',
                                'workspace_id' => getActiveWorkSpace(),
                                'active_workspace' =>getActiveWorkSpace(),
                                'created_by' => creatorId(),
                                ]
                            );
                            $user->addRole($roles->id);

                        Customer::create([
                            'customer_id' => $this->customerNumber(),
                            'user_id' => $user->id,
                            'name' => $row[$request->name],
                            'email' => $row[$request->email],
                            'password' => $row[$request->password],
                            'contact' => $row[$request->contact],
                            'billing_name' => $row[$request->billing_name],
                            'billing_country' => $row[$request->billing_country],
                            'billing_state' => $row[$request->billing_state],
                            'billing_city' => $row[$request->billing_city],
                            'billing_phone' => $row[$request->billing_phone],
                            'billing_zip' => $row[$request->billing_zip],
                            'billing_address' => $row[$request->billing_address],
                            'shipping_name' => $row[$request->shipping_name],
                            'shipping_country' => $row[$request->shipping_country],
                            'shipping_state' => $row[$request->shipping_state],
                            'shipping_city' => $row[$request->shipping_city],
                            'shipping_phone' => $row[$request->shipping_phone],
                            'shipping_zip' => $row[$request->shipping_zip],
                            'shipping_address' => $row[$request->shipping_address],
                            'created_by' => creatorId(),
                            'workspace' => getActiveWorkSpace(),
                        ]);



                    }
                    catch (\Exception $e)
                    {
                        $flag = 1;
                        $html .= '<tr>';

                        $html .= '<td>' . $row[$request->name] . '</td>';
                        $html .= '<td>' . $row[$request->email] . '</td>';
                        $html .= '<td>' . $row[$request->password] . '</td>';
                        $html .= '<td>' . $row[$request->contact] . '</td>';
                        $html .= '<td>' . $row[$request->billing_name] . '</td>';
                        $html .= '<td>' . $row[$request->billing_country] . '</td>';
                        $html .= '<td>' . $row[$request->billing_state] . '</td>';
                        $html .= '<td>' . $row[$request->billing_city] . '</td>';
                        $html .= '<td>' . $row[$request->billing_phone] . '</td>';
                        $html .= '<td>' . $row[$request->billing_zip] . '</td>';
                        $html .= '<td>' . $row[$request->billing_address] . '</td>';
                        $html .= '<td>' . $row[$request->shipping_name] . '</td>';
                        $html .= '<td>' . $row[$request->shipping_country] . '</td>';
                        $html .= '<td>' . $row[$request->shipping_state] . '</td>';
                        $html .= '<td>' . $row[$request->shipping_city] . '</td>';
                        $html .= '<td>' . $row[$request->shipping_phone] . '</td>';
                        $html .= '<td>' . $row[$request->shipping_zip] . '</td>';
                        $html .= '<td>' . $row[$request->shipping_address] . '</td>';

                        $html .= '</tr>';
                    }
                }
                else
                {
                    $flag = 1;
                    $html .= '<tr>';

                    $html .= '<td>' . $row[$request->name] . '</td>';
                    $html .= '<td>' . $row[$request->email] . '</td>';
                    $html .= '<td>' . $row[$request->password] . '</td>';
                    $html .= '<td>' . $row[$request->contact] . '</td>';
                    $html .= '<td>' . $row[$request->billing_name] . '</td>';
                    $html .= '<td>' . $row[$request->billing_country] . '</td>';
                    $html .= '<td>' . $row[$request->billing_state] . '</td>';
                    $html .= '<td>' . $row[$request->billing_city] . '</td>';
                    $html .= '<td>' . $row[$request->billing_phone] . '</td>';
                    $html .= '<td>' . $row[$request->billing_zip] . '</td>';
                    $html .= '<td>' . $row[$request->billing_address] . '</td>';
                    $html .= '<td>' . $row[$request->shipping_name] . '</td>';
                    $html .= '<td>' . $row[$request->shipping_country] . '</td>';
                    $html .= '<td>' . $row[$request->shipping_state] . '</td>';
                    $html .= '<td>' . $row[$request->shipping_city] . '</td>';
                    $html .= '<td>' . $row[$request->shipping_phone] . '</td>';
                    $html .= '<td>' . $row[$request->shipping_zip] . '</td>';
                    $html .= '<td>' . $row[$request->shipping_address] . '</td>';

                    $html .= '</tr>';
                }
            }

            $html .= '
                            </table>
                            <br />
                            ';
            if ($flag == 1)
            {

                return response()->json([
                            'html' => true,
                    'response' => $html,
                ]);
            } else {
                return response()->json([
                    'html' => false,
                    'response' => 'Data Imported Successfully',
                ]);
            }
        }
        else
        {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }
    
    
     /**
     * Bulk update customers via API using JSON input
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function bulkUpdate(Request $request)
{
    // Validation (unchanged)
    $validator = \Validator::make($request->all(), [
        'customers' => 'required|array',
        'customers.*.email' => 'required|email',
        'customers.*.name' => 'required|string',
        'customers.*.contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
        'customers.*.billing_name' => 'required|string',
        'customers.*.billing_phone' => 'required|string',
        'customers.*.billing_address' => 'nullable|string',
        'customers.*.billing_city' => 'nullable|string',
        'customers.*.billing_state' => 'nullable|string',
        'customers.*.billing_country' => 'nullable|string',
        'customers.*.billing_zip' => 'nullable|string',
        'customers.*.shipping_name' => 'nullable|string',
        'customers.*.shipping_phone' => 'nullable|string',
        'customers.*.shipping_address' => 'nullable|string',
        'customers.*.shipping_city' => 'nullable|string',
        'customers.*.shipping_state' => 'nullable|string',
        'customers.*.shipping_country' => 'nullable|string',
        'customers.*.shipping_zip' => 'nullable|string',
        'customers.*.tax_number' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()->all()
        ], 422);
    }

    $updated = [];
    $failed = [];
    $customers = $request->input('customers');

    // Enable query logging for debugging
    \DB::enableQueryLog();

    foreach ($customers as $customerData) {
        try {
            // Log the email and workspace for debugging
            \Log::info('Processing email: ' . $customerData['email'] . ', Workspace: ' . getActiveWorkSpace());

            // Find user by email (case-insensitive)
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($customerData['email'])])
                ->where('workspace_id', getActiveWorkSpace())
                ->first();

            if (!$user) {
                $failed[] = [
                    'email' => $customerData['email'],
                    'error' => 'User not found'
                ];
                \Log::warning('User not found for email: ' . $customerData['email']);
                continue;
            }

            // Find corresponding customer record
            $customer = Customer::where('user_id', $user->id)
                ->where('workspace', getActiveWorkSpace())
                ->first();

            if (!$customer) {
                $failed[] = [
                    'email' => $customerData['email'],
                    'error' => 'Customer record not found'
                ];
                \Log::warning('Customer not found for user ID: ' . $user->id);
                continue;
            }

            // Update User model
            $user->name = $customerData['name'];
            $user->mobile_no = $customerData['contact'];
            $user->save();

            // Update Customer model with default address if not provided
            $customer->name = $customerData['name'];
            $customer->contact = $customerData['contact'];
            $customer->email = $customerData['email'];
            $customer->tax_number = $customerData['tax_number'] ?? null;
            $customer->billing_name = $customerData['billing_name'];
            $customer->billing_country = $customerData['billing_country'] ?? 'Nigeria';
            $customer->billing_state = $customerData['billing_state'] ?? 'Lagos';
            $customer->billing_city = $customerData['billing_city'] ?? 'Ojodu';
            $customer->billing_phone = $customerData['billing_phone'];
            $customer->billing_zip = $customerData['billing_zip'] ?? '101233';
            $customer->billing_address = $customerData['billing_address'] ?? '25 Lola Holloway St, Omole Phase 1';
            $customer->shipping_name = $customerData['shipping_name'] ?? null;
            $customer->shipping_country = $customerData['shipping_country'] ?? null;
            $customer->shipping_state = $customerData['shipping_state'] ?? null;
            $customer->shipping_city = $customerData['shipping_city'] ?? null;
            $customer->shipping_phone = $customerData['shipping_phone'] ?? null;
            $customer->shipping_zip = $customerData['shipping_zip'] ?? null;
            $customer->shipping_address = $customerData['shipping_address'] ?? null;
            $customer->save();

            // Handle custom fields if module is active
            if (module_is_active('CustomField') && isset($customerData['customField'])) {
                \Modules\CustomField\Entities\CustomField::saveData($customer, $customerData['customField']);
            }

            // Trigger update event
            event(new UpdateCustomer($customerData, $customer));

            $updated[] = $customerData['email'];
            \Log::info('Successfully updated customer: ' . $customerData['email']);

        } catch (\Exception $e) {
            $failed[] = [
                'email' => $customerData['email'],
                'error' => $e->getMessage()
            ];
            \Log::error('Failed to update customer: ' . $customerData['email'] . ' - ' . $e->getMessage());
        }
    }

    // Log queries for debugging
    \Log::info('Queries executed: ' . json_encode(\DB::getQueryLog()));

    return response()->json([
        'success' => true,
        'message' => 'Bulk update completed',
        'updated' => $updated,
        'failed' => $failed
    ], 200);
}

 public function bulkCreate(Request $request)
    {
        // if(Auth::user()->isAbleTo('customer import')) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => __('Permission denied.'),
        //     ], 403);
        // }
       
       
       // Validate the JSON payload
        $validator = \Validator::make($request->all(), [
            'customers' => 'required|array',
            'customers.*.email' => 'required|email',
            'customers.*.name' => 'required|string',
            'customers.*.contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'customers.*.billing_name' => 'required|string',
            'customers.*.billing_phone' => 'required|string',
            'customers.*.billing_address' => 'nullable|string',
            'customers.*.billing_city' => 'nullable|string',
            'customers.*.billing_state' => 'nullable|string',
            'customers.*.billing_country' => 'nullable|string',
            'customers.*.billing_zip' => 'nullable|string',
            'customers.*.shipping_name' => 'nullable|string',
            'customers.*.shipping_phone' => 'nullable|string',
            'customers.*.shipping_address' => 'nullable|string',
            'customers.*.shipping_city' => 'nullable|string',
            'customers.*.shipping_state' => 'nullable|string',
            'customers.*.shipping_country' => 'nullable|string',
            'customers.*.shipping_zip' => 'nullable|string',
            'customers.*.tax_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $created = [];
        $failed = [];
        $customers = $request->input('customers');

        // Enable query logging for debugging
        DB::enableQueryLog();

        // Check if the client role exists
        $role = Role::where('name', 'client')
            ->where('guard_name', 'web')
            ->where('created_by', creatorId() ?? $user->id) // Fallback to user ID
            ->first();

        if (!$role) {
            \Log::error('Client role not found for bulkCreate', ['created_by' => creatorId()]);
            return response()->json([
                'success' => false,
                'message' => __('Client Role Not found!')
            ], 500);
        }

        // Get workspace ID
        $workspaceId = getActiveWorkSpace();
        if (is_null($workspaceId)) {
            \Log::error('No active workspace found for user', ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => __('No active workspace found.'),
            ], 500);
        }

        foreach ($customers as $customerData) {
            try {
                // Check if user already exists
                $existingUser = User::whereRaw('LOWER(email) = ?', [strtolower($customerData['email'])])
                    ->where('workspace_id', $workspaceId)
                    ->first();

                if ($existingUser) {
                    // Check if customer record exists for this user
                    $existingCustomer = Customer::where('user_id', $existingUser->id)
                        ->where('workspace', $workspaceId)
                        ->first();

                    if ($existingCustomer) {
                        // Both user and customer exist, skip
                        $failed[] = [
                            'email' => $customerData['email'],
                            'error' => 'Customer already exists'
                        ];
                        \Log::warning('Customer already exists for email: ' . $customerData['email']);
                        continue;
                    }

                    // User exists but no customer, proceed to create customer
                    $user = $existingUser;
                    \Log::info('Found existing user without customer for email: ' . $customerData['email']);
                } else {
                    // Check plan limits for new user creation
                    $canUse = PlanCheck('User', $user->id);
                    if ($canUse == false) {
                        $failed[] = [
                            'email' => $customerData['email'],
                            'error' => 'Maximum number of customers allowed on current plan reached'
                        ];
                        \Log::warning('Plan limit reached for email: ' . $customerData['email']);
                        continue;
                    }

                    // Create new user with default password GTEXT002
                    $user = User::create([
                        'name' => $customerData['name'],
                        'email' => $customerData['email'],
                        'mobile_no' => $customerData['contact'],
                        'password' => Hash::make('GTEXT002'),
                        'email_verified_at' => now(),
                        'type' => 'client',
                        'lang' => 'en',
                        'workspace_id' => $workspaceId,
                        'active_workspace' => $workspaceId,
                        'created_by' => creatorId() ?? $user->id, // Fallback to user ID
                    ]);

                    // Assign client role
                    $user->addRole($role);

                    \Log::info('Created new user: ' . $customerData['email']);
                }

                // Create customer record
                $customer = Customer::create([
                    'customer_id' => $this->customerNumber(),
                    'user_id' => $user->id,
                    'name' => $customerData['name'],
                    'contact' => $customerData['contact'],
                    'email' => $customerData['email'],
                    'tax_number' => $customerData['tax_number'] ?? null,
                    'billing_name' => $customerData['billing_name'],
                    'billing_country' => $customerData['billing_country'] ?? 'Nigeria',
                    'billing_state' => $customerData['billing_state'] ?? 'Lagos',
                    'billing_city' => $customerData['billing_city'] ?? 'Ojodu',
                    'billing_phone' => $customerData['billing_phone'],
                    'billing_zip' => $customerData['billing_zip'] ?? '101233',
                    'billing_address' => $customerData['billing_address'] ?? '25 Lola Holloway St, Omole Phase 1',
                    'shipping_name' => $customerData['shipping_name'] ?? null,
                    'shipping_country' => $customerData['shipping_country'] ?? null,
                    'shipping_state' => $customerData['shipping_state'] ?? null,
                    'shipping_city' => $customerData['shipping_city'] ?? null,
                    'shipping_phone' => $customerData['shipping_phone'] ?? null,
                    'shipping_zip' => $customerData['shipping_zip'] ?? null,
                    'shipping_address' => $customerData['shipping_address'] ?? null,
                    'lang' => 'en',
                    'workspace' => $workspaceId,
                    'created_by' => $user->id,
                ]);

                // Handle custom fields if module is active
                if (module_is_active('CustomField') && isset($customerData['customField'])) {
                    \Modules\CustomField\Entities\CustomField::saveData($customer, $customerData['customField']);
                }

                // Trigger create event
                event(new CreateCustomer($customerData, $customer));

                $created[] = $customerData['email'];
                \Log::info('Successfully created customer: ' . $customerData['email']);

            } catch (\Exception $e) {
                $failed[] = [
                    'email' => $customerData['email'],
                    'error' => $e->getMessage()
                ];
                \Log::error('Failed to create customer: ' . $customerData['email'] . ' - ' . $e->getMessage());
            }
        }

        // Log queries for debugging
        \Log::info('Queries executed: ' . json_encode(DB::getQueryLog()));

        return response()->json([
            'success' => true,
            'message' => 'Bulk create completed',
            'created' => $created,
            'failed' => $failed
        ], 200);
    }
    
    
    
     public function bulkUpdateCustomerAddress(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            \Log::error('No authenticated user for bulkUpdateCustomerAddress request', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => __('Unauthenticated. Please provide a valid authentication token.'),
            ], 401);
        }

        if (!$user->isAbleTo('customer edit')) {
            \Log::warning('User lacks permission for bulkUpdateCustomerAddress', ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => __('Permission denied.'),
            ], 403);
        }

        // Validate the JSON payload
        $validator = \Validator::make($request->all(), [
            'customers' => 'required|array',
            'customers.*.email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $updated = [];
        $failed = [];
        $customers = $request->input('customers');

        // Enable query logging for debugging
        DB::enableQueryLog();

        // Get workspace ID
        $workspaceId = getActiveWorkSpace();
        if (is_null($workspaceId)) {
            \Log::error('No active workspace found for user', ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => __('No active workspace found.'),
            ], 500);
        }

        // Check if the client role exists (for creating new customers, if needed)
        $role = Role::where('name', 'client')
            ->where('guard_name', 'web')
            ->where('created_by', creatorId() ?? $user->id)
            ->first();

        if (!$role) {
            \Log::error('Client role not found for bulkUpdateCustomerAddress', ['created_by' => creatorId()]);
            return response()->json([
                'success' => false,
                'message' => __('Client Role Not found!')
            ], 500);
        }

        foreach ($customers as $customerData) {
            try {
                // Find user by email in the current workspace
                $existingUser = User::whereRaw('LOWER(email) = ?', [strtolower($customerData['email'])])
                    ->where('workspace_id', $workspaceId)
                    ->first();

                if (!$existingUser) {
                    $failed[] = [
                        'email' => $customerData['email'],
                        'error' => 'User not found'
                    ];
                    \Log::warning('User not found for email: ' . $customerData['email']);
                    continue;
                }

                // Check if customer record exists
                $customer = Customer::where('user_id', $existingUser->id)
                    ->where('workspace', $workspaceId)
                    ->first();

                if (!$customer) {
                    // Create a new customer record if none exists
                    $customer = Customer::create([
                        'customer_id' => $this->customerNumber(),
                        'user_id' => $existingUser->id,
                        'name' => $existingUser->name ?? 'Unknown',
                        'contact' => $existingUser->mobile_no ?? '',
                        'email' => $existingUser->email,
                        'tax_number' => null,
                        'billing_name' => $existingUser->name ?? 'Unknown',
                        'billing_country' => 'Nigeria',
                        'billing_state' => 'Lagos',
                        'billing_city' => 'Ojodu',
                        'billing_phone' => $existingUser->mobile_no ?? '',
                        'billing_zip' => '101233',
                        'billing_address' => 'GText and Associates, 25 Lola Holloway St, Omole Phase 1',
                        'shipping_name' => null,
                        'shipping_country' => null,
                        'shipping_state' => null,
                        'shipping_city' => null,
                        'shipping_phone' => null,
                        'shipping_zip' => null,
                        'shipping_address' => null,
                        'lang' => 'en',
                        'workspace' => $workspaceId,
                        'created_by' => $user->id,
                    ]);

                    event(new CreateCustomer($customerData, $customer));
                    \Log::info('Created new customer for existing user: ' . $customerData['email']);
                } else {
                    // Update existing customer address if different
                    if (
                        $customer->billing_address !== 'GText and Associates, 25 Lola Holloway St, Omole Phase 1' ||
                        $customer->billing_city !== 'Ojodu' ||
                        $customer->billing_state !== 'Lagos' ||
                        $customer->billing_country !== 'Nigeria' ||
                        $customer->billing_zip !== '101233'
                    ) {
                        $customer->billing_address = 'GText and Associates, 25 Lola Holloway St, Omole Phase 1';
                        $customer->billing_city = 'Ojodu';
                        $customer->billing_state = 'Lagos';
                        $customer->billing_country = 'Nigeria';
                        $customer->billing_zip = '101233';
                        $customer->save();

                        event(new UpdateCustomer($customerData, $customer));
                        \Log::info('Updated customer address for email: ' . $customerData['email']);
                    } else {
                        \Log::info('Customer address already up-to-date for email: ' . $customerData['email']);
                        $updated[] = $customerData['email'];
                        continue;
                    }
                }

                $updated[] = $customerData['email'];

            } catch (\Exception $e) {
                $failed[] = [
                    'email' => $customerData['email'],
                    'error' => $e->getMessage()
                ];
                \Log::error('Failed to update customer address: ' . $customerData['email'] . ' - ' . $e->getMessage());
            }
        }

        // Log queries for debugging
        \Log::info('Queries executed: ' . json_encode(DB::getQueryLog()));

        return response()->json([
            'success' => true,
            'message' => 'Bulk address update completed',
            'updated' => $updated,
            'failed' => $failed
        ], 200);
    }
    
    
}
