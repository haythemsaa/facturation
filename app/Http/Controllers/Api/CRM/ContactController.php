<?php
namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('assignedTo', 'customer');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('company', 'like', "%{$request->search}%");
            });
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $contacts = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json($contacts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'type' => 'required|in:lead,prospect,customer',
            'source' => 'nullable|in:website,referral,cold_call,social_media,event,other',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $contact = Contact::create($validated);

        return response()->json($contact->load('assignedTo'), 201);
    }

    public function show(Contact $contact)
    {
        return response()->json($contact->load('assignedTo', 'customer', 'opportunities', 'activities'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'type' => 'sometimes|in:lead,prospect,customer',
            'source' => 'nullable|in:website,referral,cold_call,social_media,event,other',
            'assigned_to' => 'nullable|exists:users,id',
            'score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);

        return response()->json($contact->load('assignedTo'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(['message' => 'Contact supprimé avec succès']);
    }
}
