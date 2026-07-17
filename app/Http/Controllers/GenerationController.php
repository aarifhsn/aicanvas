<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use Illuminate\Http\Request;

class GenerationController extends Controller
{
    public function index(Request $request)
    {
        $generations = $request->user()->generations()->latest()->paginate(10);

        return view('history.index', compact('generations'));
    }

    public function destroy(Generation $generation)
    {
        abort_unless($generation->user_id === auth()->id(), 403);

        $generation->delete();

        return back()->with('status', 'Generation deleted.');
    }
}