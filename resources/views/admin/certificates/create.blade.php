@extends('layouts.admin')

@section('title', 'Add Certificate / Training')
@section('page-title', 'Add Certificate/Training: ' . $employee->name)

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <a href="{{ route('admin.employees.profile', $employee) }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-red-700 dark:hover:text-red-400 flex items-center gap-1.5 transition">
        &larr; Back to {{ $employee->name }}'s Profile
    </a>

    <form method="POST" action="{{ route('admin.employees.certificates.store', $employee) }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Record Type *</label>
            <select name="type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                <option value="certificate">Certificate</option>
                <option value="training">Training</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Title / Course Name *</label>
            <input type="text" name="title" required value="{{ old('title') }}" placeholder="e.g. Basic Life Support, Project Management"
                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Issuer / Training Provider</label>
            <input type="text" name="issuer" value="{{ old('issuer') }}" placeholder="e.g. Red Cross, TESDA, DOLE"
                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Date Issued</label>
                <input type="date" name="date_issued" value="{{ old('date_issued') }}"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Expiry Date (if applicable)</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">System warns 30 days before this date.</p>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Upload Certificate File (PDF/Image)</label>
            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png"
                   class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-red-50 dark:file:bg-red-950/60 file:text-red-700 dark:file:text-red-400 file:font-semibold">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Notes / Remarks</label>
            <textarea name="notes" rows="2" placeholder="Optional notes..."
                      class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">{{ old('notes') }}</textarea>
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.employees.profile', $employee) }}" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-center transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-xs text-center">Save Record</button>
        </div>
    </form>
</div>
@endsection
