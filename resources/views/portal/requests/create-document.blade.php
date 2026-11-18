@extends('layouts.portal')

@section('title', 'Request Document')
@section('page-title', 'Request Official Document')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('portal.requests.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Requests</span>
        </a>
    </div>

    <form method="POST" action="{{ route('portal.requests.store-document') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf

        <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Document Request Details</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Request certificates, employment records, or official certifications from HR.</p>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Document Type <span class="text-red-500">*</span>
                </label>
                <select name="document_type" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">
                    <option value="">Select Document Type...</option>
                    <option value="Certificate of Employment (COE)" {{ old('document_type') == 'Certificate of Employment (COE)' ? 'selected' : '' }}>Certificate of Employment (COE)</option>
                    <option value="Certificate of Employment with Compensation (COEC)" {{ old('document_type') == 'Certificate of Employment with Compensation (COEC)' ? 'selected' : '' }}>Certificate of Employment with Compensation (COEC)</option>
                    <option value="Service Record" {{ old('document_type') == 'Service Record' ? 'selected' : '' }}>Service Record</option>
                    <option value="PhilHealth / SSS / Pag-IBIG Certification / Clearance" {{ old('document_type') == 'PhilHealth / SSS / Pag-IBIG Certification / Clearance' ? 'selected' : '' }}>PhilHealth / SSS / Pag-IBIG Certification / Clearance</option>
                    <option value="Loan Clearance / Endorsement" {{ old('document_type') == 'Loan Clearance / Endorsement' ? 'selected' : '' }}>Loan Clearance / Endorsement</option>
                    <option value="Other Official Document" {{ old('document_type') == 'Other Official Document' ? 'selected' : '' }}>Other Official Document</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Date Needed By
                </label>
                <input type="date" name="date_needed" value="{{ old('date_needed') }}" min="{{ now()->toDateString() }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Standard HR processing takes 2 to 3 working days.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Purpose of Request <span class="text-red-500">*</span>
                </label>
                <textarea name="purpose" rows="4" required placeholder="Specify why this document is needed (e.g. Bank loan application, Visa application, Scholarship, Government requirement)..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">{{ old('purpose') }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('portal.requests.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-black dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <span>Submit Document Request</span>
            </button>
        </div>
    </form>

</div>
@endsection
