<?php

namespace Modules\ProductService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ProductService\Entities\GtexthomesClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class GtexthomesClientController extends Controller
{
    public function import(Request $request)
    {
        // Validate the incoming JSON data
        $validator = Validator::make($request->all(), [
            'clients' => 'required|array',
            'clients.*.serial_number' => 'required|string',
            'clients.*.name' => 'required|string',
            'clients.*.house' => 'required|string',
            'clients.*.amount_agreed' => 'required|numeric',
            'clients.*.amount_paid' => 'required|numeric',
            'clients.*.land_rec' => 'required|numeric',
            'clients.*.email' => 'nullable|email',
            'clients.*.phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            foreach ($request->clients as $clientData) {
                GtexthomesClient::create([
                    'serial_number' => $clientData['serial_number'],
                    'name' => $clientData['name'],
                    'house' => $clientData['house'],
                    'amount_agreed' => $clientData['amount_agreed'],
                    'amount_paid' => $clientData['amount_paid'],
                    'land_rec' => $clientData['land_rec'],
                    'email' => $clientData['email'] ?? null,
                    'phone_number' => $clientData['phone_number'],
                    'workspace' => $clientData['workspace'] ?? 10,
                    'currency' => $clientData['currency'] ?? 'NGN',
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Clients imported successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import clients: ' . $e->getMessage(),
            ], 500);
        }
    }

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

    // Super Admin: See all GtexthomesClients
    if ($user->type === 'super admin') {
        $clients = GtexthomesClient::all();

    // Workspace Manager: See all GtexthomesClients in the workspace
    } elseif ($user->isAbleTo('user manage') && $user->isAbleTo('workspace manage')) {
        $clients = GtexthomesClient::where('workspace', getActiveWorkSpace())->get();

    // Regular user: Handle based on user type
    } else {
        if ($user->type === 'client') {
            // Clients: See all GtexthomesClients with the same email, no workspace restriction
            $clients = GtexthomesClient::whereIn('email', function ($query) use ($user) {
                $query->select('email')
                      ->from('users')
                      ->where('email', $user->email);
            })->get();
        } else {
            // Other regular users: See GtexthomesClients with the same email in the workspace
            $clients = GtexthomesClient::where('workspace', getActiveWorkSpace())
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
            'data' => $clients,
        ], 200);
    }

    return view('productservice::clients.index', compact('clients'));
}
}