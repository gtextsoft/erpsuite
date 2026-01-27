<?php

namespace Modules\ToDoReport\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\ToDo\Entities\ToDo;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $users = User::where('workspace_id', getActiveWorkSpace())->get();
        return view('todoreport::index', compact('users'));
    }

    public function generate(Request $request)
    {
        $userId = $request->user_id;

        $todos = ToDo::where('assigned_to', 'LIKE', "%$userId%")
            ->whereDate('start_date', now()->toDateString())
            ->get();

        $prompt = "Generate a daily summary for the following to-dos:\n";
        foreach ($todos as $todo) {
            $status = $todo->status == 'completed' ? 'Completed' : 'Pending';
            $prompt .= "- {$todo->title}, status: {$status}, priority: {$todo->priority}\n";
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a project assistant generating daily reports.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        return response()->json([
            'report' => $response->json('choices.0.message.content')
        ]);
    }
}
