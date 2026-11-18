@extends('layouts.portal')

@section('title', 'Request Seminar / Training')
@section('page-title', 'Request Seminar / Training')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('portal.requests.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Requests</span>
        </a>
    </div>

    <!-- Notice Box -->
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="text-xs text-amber-900 dark:text-amber-200">
            <p class="font-bold">Post-Training Policy &amp; Seminar Report Notice:</p>
            <p class="mt-0.5">Upon approval and completion of any external seminar or training, staff are required to submit an official <strong>Seminar Report</strong> (Key Learnings, Action/Re-echo Plan, and Certificate of Attendance) into the HRIS portal within 5 business days.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('portal.requests.store-seminar') }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf

        <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Seminar &amp; Training Details</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Fill in the event specifics and estimated institutional investment.</p>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Seminar / Training Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="seminar_title" value="{{ old('seminar_title') }}" required placeholder="e.g. Philippine HR Congress 2026 / DRRM Advanced Incident Command Workshop"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Organizer / Sponsoring Body
                    </label>
                    <input type="text" name="organizer" value="{{ old('organizer') }}" placeholder="e.g. Caritas Philippines / PMAP / Civil Service"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Venue / Location
                    </label>
                    <input type="text" name="venue" value="{{ old('venue') }}" placeholder="e.g. SMX Convention Center, Pasay City / Zoom Virtual"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required min="{{ now()->toDateString() }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        End Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required min="{{ now()->toDateString() }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                        Estimated Budget (PHP)
                    </label>
                    <input type="number" step="0.01" name="estimated_budget" value="{{ old('estimated_budget', '0.00') }}" placeholder="0.00"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Justification &amp; Relevance to LASAC <span class="text-red-500">*</span>
                </label>
                <textarea name="justification" rows="4" required placeholder="Explain how this seminar benefits your role, departmental operations, and LASAC's social mission..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500">{{ old('justification') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Invitation Memo / Brochure / Program (Optional)
                </label>
                <input type="file" name="invitation_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-50 dark:file:bg-red-950/60 file:text-red-700 dark:file:text-red-400">
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Upload PDF, Word document, or image brochure up to 10MB.</p>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <a href="{{ route('portal.requests.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <span>Submit Seminar Request</span>
            </button>
        </div>
    </form>

</div>
@endsection
