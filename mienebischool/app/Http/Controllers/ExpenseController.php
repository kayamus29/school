<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Exception;

use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:create expenses')->only(['store', 'myExpenses']);
        $this->middleware('can:manage expenses')->only(['index', 'updateStatus', 'correct']);
    }

    public function index()
    {
        $expenses = Expense::with(['requester', 'approver'])->latest('expense_date')->paginate(20);
        return view('accounting.expenses.index', compact('expenses'));
    }

    public function myExpenses()
    {
        $expenses = Expense::where('user_id', Auth::id())->latest('expense_date')->paginate(20);
        return view('accounting.expenses.my_expenses', compact('expenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            Expense::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'amount' => $request->amount,
                'description' => $request->description,
                'initial_amount' => $request->amount,
                'initial_description' => $request->description,
                'expense_date' => $request->expense_date,
                'status' => 'pending'
            ]);
            return redirect()->back()->with('success', 'Expense request submitted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error submitting expense: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string'
        ]);

        try {
            $expense = Expense::findOrFail($id);
            $expense->update([
                'status' => $request->status,
                'approver_id' => Auth::id(),
                'approver_notes' => $request->notes
            ]);
            return redirect()->back()->with('success', 'Expense status updated to ' . $request->status);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    public function correct(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            $expense = Expense::findOrFail($id);
            $expense->update([
                'amount' => $request->amount,
                'description' => $request->description,
                'approver_id' => Auth::id(),
                'approver_notes' => $request->notes,
                'status' => 'corrected'
            ]);
            return redirect()->back()->with('success', 'Expense approved with corrections.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error processing correction: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $user = Auth::user();
            $isAdminOrAccountant = $user->hasAnyRole(['Accountant', 'Admin']);

            // If not admin/accountant, must be owner
            if (!$isAdminOrAccountant && $expense->user_id !== $user->id) {
                return redirect()->back()->with('error', 'You can only delete your own expenses.');
            }

            // FIX-010: Only allow deletion of pending expenses.
            // Approved/Rejected expenses are immutable financial records.
            if ($expense->status !== 'pending') {
                return redirect()->back()->with('error', 'Cannot delete ' . $expense->status . ' expenses. Only pending expenses can be deleted.');
            }

            $expense->delete();
            return redirect()->back()->with('success', 'Expense deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error deleting expense: ' . $e->getMessage());
        }
    }
}
