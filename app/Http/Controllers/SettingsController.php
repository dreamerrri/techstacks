<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;   // ← add this line here

class SettingsController extends Controller
{
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => ['required', 'string', Rule::in([
                'light','dark','black','claude','corporate','ghibli','gourmet',
                'luxury','mintlify','pastel','perplexity','shadcn','slack',
                'soft','spotify','valorant','vscode','techstacks', 'techstacks-light'
            ])],
        ]);
        $request->user()->update(['theme' => $request->theme]);
        return response()->json(['status' => 'ok']);
    }
}