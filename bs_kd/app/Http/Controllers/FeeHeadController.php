<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeeHead;
use Exception;

class FeeHeadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Accountant|Admin']);
    }

    public function index()
    {
        $feeHeads = FeeHead::all();
        return view('accounting.fees.heads.index', compact('feeHeads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:fee_heads,name|max:255',
        ]);

        try {
            FeeHead::create([
                'name' => $request->name
            ]);
            return redirect()->back()->with('success', 'Fee Head created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error creating fee head: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $feeHead = FeeHead::findOrFail($id);
            $feeHead->delete();
            return redirect()->back()->with('success', 'Fee Head deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error deleting fee head: ' . $e->getMessage());
        }
    }
}
