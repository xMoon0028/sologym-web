@extends('layouts.portal')

@section('title', 'Profile Not Found')
@section('page-title', 'My Portal')

@section('content')
<div class="max-w-md mx-auto my-12 bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-2xl border border-gray-200 dark:border-slate-800 text-center space-y-4 shadow-sm">
    <span class="text-4xl block">👤</span>
    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-slate-100">Account Not Linked to Employee Profile</h2>
    <p class="text-xs text-gray-500 dark:text-slate-400">
        Your user account has been created, but HR has not yet connected it to your Employee record. Please contact the HR department to link your profile.
    </p>
</div>
@endsection
