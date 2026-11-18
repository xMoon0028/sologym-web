<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTraining;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function create(Employee $employee)
    {
        return view('admin.certificates.create', compact('employee'));
    }

    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'type' => 'required|in:certificate,training',
            'title' => 'required|string|max:255',
            'date_issued' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:date_issued',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('certificates', 'public');
        }

        $cert = $employee->certificates()->create([
            'type' => $request->type,
            'title' => $request->title,
            'issuer' => $request->issuer,
            'date_issued' => $request->date_issued,
            'expiry_date' => $request->expiry_date,
            'file_path' => $filePath,
            'notes' => $request->notes,
        ]);

        if ($cert->is_expiring) {
            app(\App\Services\NotificationService::class)->notifyCertificateExpiring($cert);
        }

        return redirect()->route('admin.employees.profile', $employee)->with('success', 'Certificate/Training record added.');
    }

    public function edit(CertificateTraining $certificate)
    {
        return view('admin.certificates.edit', compact('certificate'));
    }

    public function update(Request $request, CertificateTraining $certificate)
    {
        $request->validate([
            'type' => 'required|in:certificate,training',
            'title' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $filePath = $certificate->file_path;
        if ($request->hasFile('file')) {
            if ($filePath) Storage::disk('public')->delete($filePath);
            $filePath = $request->file('file')->store('certificates', 'public');
        }

        $certificate->update([
            'type' => $request->type,
            'title' => $request->title,
            'issuer' => $request->issuer,
            'date_issued' => $request->date_issued,
            'expiry_date' => $request->expiry_date,
            'file_path' => $filePath,
            'notes' => $request->notes,
        ]);

        if ($certificate->fresh()->is_expiring) {
            app(\App\Services\NotificationService::class)->notifyCertificateExpiring($certificate->fresh());
        }

        return redirect()->route('admin.employees.profile', $certificate->employee_id)->with('success', 'Record updated.');
    }

    public function destroy(CertificateTraining $certificate)
    {
        $employeeId = $certificate->employee_id;
        if ($certificate->file_path) Storage::disk('public')->delete($certificate->file_path);
        $certificate->delete();
        return redirect()->route('admin.employees.profile', $employeeId)->with('success', 'Record deleted.');
    }
}
