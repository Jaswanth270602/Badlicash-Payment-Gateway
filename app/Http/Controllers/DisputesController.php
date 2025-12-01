<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputesController extends Controller
{
    public function index(): View
    {
        return view('merchant.disputes.index');
    }

    public function getData(Request $request)
    {
        $merchant = $request->user()->merchant;
        $query = Dispute::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $merchant = $request->user()->merchant;
        $validated = $request->validate([
            'transaction_id' => 'nullable|integer',
            'reason' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $dispute = Dispute::create(array_merge($validated, [
            'merchant_id' => $merchant->id,
            'status' => 'open',
        ]));

        return response()->json(['success' => true, 'data' => $dispute]);
    }

    public function indexAdmin(): View
    {
        return view('admin.disputes.index');
    }

    public function getDataAdmin(Request $request)
    {
        $query = Dispute::query()->orderByDesc('created_at');
        if ($request->merchant_id) {
            $query->where('merchant_id', $request->merchant_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,needs_evidence,won,lost,closed',
            'evidence_url' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        $dispute = Dispute::findOrFail($id);
        $dispute->fill($validated);
        $dispute->save();

        return response()->json(['success' => true, 'data' => $dispute]);
    }
}


