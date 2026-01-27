<?php

namespace Modules\ProductService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\ProductService\Imports\InvestmentsImport;
use Modules\ProductService\Entities\Investment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InvestmentController extends Controller
{
    /**
     * Display a listing of investments.
     *
     * @param Request $request
     * @return \Illuminate\View\View|JsonResponse
     */
public function index(Request $request)
{
    $user = Auth::user();

    if (!$user->isAbleTo('user profile manage')) {
        return $request->expectsJson()
            ? response()->json([
                'status' => 'error',
                'message' => __('Permission denied.'),
            ], 403)
            : redirect()->back()->with('error', __('Permission denied.'));
    }

    // Super Admin: See all investments
    if ($user->type === 'super admin') {
        $investments = Investment::all();

    // Workspace Manager: See all investments in the workspace
    } elseif ($user->isAbleTo('user manage') && $user->isAbleTo('workspace manage')) {
        $investments = Investment::where('workspace', getActiveWorkSpace())->get();

    // Regular user: Handle based on user type
    } else {
        if ($user->type === 'client') {
            // Clients: See all investments with the same email, no workspace restriction
            $investments = Investment::whereIn('email', function ($query) use ($user) {
                $query->select('email')
                      ->from('users')
                      ->where('email', $user->email);
            })->get();
        } else {
            // Other regular users: See investments with the same email in the workspace
            $investments = Investment::where('workspace', getActiveWorkSpace())
                ->whereIn('email', function ($query) use ($user) {
                    $query->select('email')
                          ->from('users')
                          ->where('email', $user->email);
                })
                ->get();
        }
    }

    if ($request->expectsJson()) {
        return response()->json([
            'status' => 'success',
            'data' => $investments,
        ], 200);
    }

    return view('productservice::investments.index', compact('investments'));
}
    /**
     * Show the form for creating a new investment.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('investment create')) {
            return view('productservice::investments.create');
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Store a newly created investment.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('investment create')) {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone_number' => 'nullable|string|max:20',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|in:NGN,USD',
                'duration_years' => 'required|integer|min:1',
                'interest_rate' => 'required|numeric|min:0',
                'investment_date' => 'required|date_format:Y-m-d',
                'due_date' => 'required|date_format:Y-m-d|after:investment_date',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return redirect()->route('investments.create')
                    ->with('error', $validator->getMessageBag()->first());
            }

            $investment = new Investment();
            $investment->name = $request->name;
            $investment->email = $request->email;
            $investment->phone_number = $request->phone_number;
            $investment->amount = $request->amount;
            $investment->currency = $request->currency;
            $investment->workspace = getActiveWorkSpace();
            $investment->duration_years = $request->duration_years;
            $investment->interest_rate = $request->interest_rate;
            $investment->investment_date = $request->investment_date;
            $investment->due_date = $request->due_date;
            $investment->save();

            return redirect()->route('investments.index')
                ->with('success', __('Investment successfully created.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Display the import form.
     *
     * @return \Illuminate\View\View
     */
    public function importForm()
    {
        if (Auth::user()->isAbleTo('user profile manage')) {
            return view('productservice::investments.import-form');
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
 * Import investments from JSON or Excel.
 *
 * @param Request $request
 * @return JsonResponse
 */
/**
 * Import investments from JSON or Excel.
 *
 * @param Request $request
 * @return JsonResponse
 */
public function import(Request $request): JsonResponse
{
    try {
        if ($request->hasFile('file')) {
            // Handle Excel file import
            $request->validate([
                'file' => 'required|mimes:xlsx,csv',
            ]);

            Excel::import(new InvestmentsImport, $request->file('file'));

            return response()->json([
                'status' => 'success',
                'message' => 'Investments imported successfully from Excel.',
            ], 200);
        } elseif ($request->isJson()) {
            // Handle JSON import
            $data = $request->validate([
                'investments' => 'required|array',
                'investments.*.name' => 'nullable|string|max:255',
                'investments.*.email' => 'nullable|email|max:255',
                'investments.*.phone_number' => 'nullable|string|max:20',
                'investments.*.amount' => 'required|numeric|min:0',
                'investments.*.currency' => 'nullable|in:NGN,USD',
                'investments.*.duration_years' => 'required|integer|min:1',
                'investments.*.interest_rate' => 'required|numeric|min:0',
                'investments.*.investment_date' => 'required|date_format:Y-m-d',
                'investments.*.due_date' => 'required|date_format:Y-m-d|after:investment_date',
            ])['investments'];

            foreach ($data as $investmentData) {
                // Fill default currency if not provided
                if (empty($investmentData['currency'])) {
                    $investmentData['currency'] = 'NGN';
                }

                Investment::create($investmentData);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Investments imported and saved successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid request. Provide either an Excel file or JSON data.',
        ], 400);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to import investments: ' . $e->getMessage(),
        ], 500);
    }
}
}