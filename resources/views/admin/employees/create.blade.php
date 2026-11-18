@extends('layouts.admin')

@section('title', 'Add Employee')
@section('page-title', 'Add New Employee')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            &larr; Back to Employees
        </a>
    </div>

    <form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 space-y-8">
        @csrf

        <!-- Section 1: Basic & Employment Info -->
        <div>
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                <span>👤</span> Basic &amp; Employment Details
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" required value="{{ old('name') }}" placeholder="Lastname, Firstname Middlename"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">ID Number / Employee No</label>
                    <input type="text" name="employee_no" value="{{ old('employee_no') }}" placeholder="e.g. 251-25"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-red-900 uppercase mb-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Official Work Email (@lasaclipa.org)
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="username@lasaclipa.org"
                           class="w-full px-3 py-2 border border-red-200 bg-red-50/20 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-medium text-gray-800 placeholder-gray-400">
                    <p class="text-[11px] text-gray-500 mt-1">Staff will use this official @lasaclipa.org Google account to sign in and view their attendance, leave credits, and payslips.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Position / Designation</label>
                    <input type="text" name="position" value="{{ old('position') }}" placeholder="e.g. Program Specialist I"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Salary Grade</label>
                    <input type="text" name="salary_grade" value="{{ old('salary_grade') }}" placeholder="e.g. Grade 5"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Employment Status</label>
                    <select name="employment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="Regular" {{ old('employment_status') == 'Regular' ? 'selected' : '' }}>Regular</option>
                        <option value="Probationary" {{ old('employment_status') == 'Probationary' ? 'selected' : '' }}>Probationary</option>
                        <option value="Contractual" {{ old('employment_status') == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                        <option value="Project-based" {{ old('employment_status') == 'Project-based' ? 'selected' : '' }}>Project-based</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date Hired</label>
                    <input type="date" name="date_hired" value="{{ old('date_hired') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Birthdate</label>
                    <input type="date" name="birthdate" value="{{ old('birthdate') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sex</label>
                    <select name="sex" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Select --</option>
                        <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Civil Status</label>
                    <select name="civil_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Select --</option>
                        <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                        <option value="Separated" {{ old('civil_status') == 'Separated' ? 'selected' : '' }}>Separated</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Photo</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700">
                </div>
            </div>
        </div>

        <!-- Section 2: Addresses (PSGC Cascading Aligned) -->
        <div x-data="psgcAddressSelector({
            region: '{{ old('region', '') }}',
            province: '{{ old('province', '') }}',
            city: '{{ old('municipality_city', '') }}',
            barangay: '{{ old('barangay', '') }}'
        })" x-init="init()">
            <div class="flex items-center justify-between border-b border-gray-200 pb-2 mb-4">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span>📍</span> Address &amp; Geographic Location
                </h3>
                <span class="text-xs text-slate-400 font-medium hidden sm:inline">Each level unlocks sequentially</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                <!-- 1. Region -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase">1. Region *</label>
                        <span x-show="loadingRegions" class="text-[10px] text-red-600 font-semibold animate-pulse">Loading...</span>
                    </div>
                    <select name="region" x-model="selectedRegion" @change="onRegionChange()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 bg-white transition shadow-2xs">
                        <option value="">-- Select Region --</option>
                        <template x-for="r in regions" :key="r.code">
                            <option :value="r.name" x-text="r.name" :selected="r.name === selectedRegion"></option>
                        </template>
                    </select>
                </div>

                <!-- 2. Province (Unlocked after Region) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase">2. Province *</label>
                        <span x-show="loadingProvinces" class="text-[10px] text-red-600 font-semibold animate-pulse">Loading...</span>
                    </div>
                    <select name="province" x-model="selectedProvince" @change="onProvinceChange()" :disabled="!selectedRegion || loadingProvinces"
                            class="w-full px-3 py-2 border rounded-lg text-sm transition shadow-2xs border-gray-300 bg-white focus:ring-red-500 focus:border-red-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                        <option value="" x-text="selectedRegion ? '-- Select Province --' : 'Select Region first'"></option>
                        <template x-for="p in provinces" :key="p.name">
                            <option :value="p.name" x-text="p.name" :selected="p.name === selectedProvince"></option>
                        </template>
                    </select>
                </div>

                <!-- 3. City / Municipality (Unlocked after Province) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase">3. City / Municipality *</label>
                        <span x-show="loadingCities" class="text-[10px] text-red-600 font-semibold animate-pulse">Loading...</span>
                    </div>
                    <select name="municipality_city" x-model="selectedCity" @change="onCityChange()" :disabled="!selectedProvince || loadingCities"
                            class="w-full px-3 py-2 border rounded-lg text-sm transition shadow-2xs border-gray-300 bg-white focus:ring-red-500 focus:border-red-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                        <option value="" x-text="selectedProvince ? '-- Select City / Municipality --' : 'Select Province first'"></option>
                        <template x-for="c in cities" :key="c.name">
                            <option :value="c.name" x-text="c.name" :selected="c.name === selectedCity"></option>
                        </template>
                    </select>
                </div>

                <!-- 4. Barangay (Unlocked after City) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase">4. Barangay *</label>
                        <span x-show="loadingBarangays" class="text-[10px] text-red-600 font-semibold animate-pulse">Loading...</span>
                    </div>
                    <select name="barangay" x-model="selectedBarangay" :disabled="!selectedCity || loadingBarangays"
                            class="w-full px-3 py-2 border rounded-lg text-sm transition shadow-2xs border-gray-300 bg-white focus:ring-red-500 focus:border-red-500 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed">
                        <option value="" x-text="selectedCity ? '-- Select Barangay --' : 'Select City first'"></option>
                        <template x-for="b in barangays" :key="b">
                            <option :value="b" x-text="b" :selected="b === selectedBarangay"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Permanent Street Address</label>
                    <input type="text" name="permanent_address" value="{{ old('permanent_address') }}" placeholder="House/Unit No., Street, Subdivision"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Current Address (if different)</label>
                    <input type="text" name="current_address" value="{{ old('current_address') }}" placeholder="Current residence address"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">ZIP Code</label>
                    <input type="text" name="zip_code" value="{{ old('zip_code') }}" placeholder="e.g. 4217"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Section 3: Compensation & Fixed Deductions -->
        <div x-data="{
            basic: {{ old('basic_salary_80', 0) }},
            allowance: {{ old('allowance_20', 0) }},
            total: {{ old('total_compensation', 0) }},
            calc() {
                this.total = (parseFloat(this.basic) || 0) + (parseFloat(this.allowance) || 0);
            }
        }">
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                <span>💰</span> Compensation Package &amp; Standard Deductions
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Basic Salary (80%)</label>
                    <input type="number" step="0.01" name="basic_salary_80" x-model="basic" @input="calc()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Allowances (20%)</label>
                    <input type="number" step="0.01" name="allowance_20" x-model="allowance" @input="calc()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Total Compensation (Monthly)</label>
                    <input type="number" step="0.01" name="total_compensation" x-model="total"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-bold bg-gray-50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">SSS Deduction (Semi-monthly)</label>
                    <input type="number" step="0.01" name="sss_deduction" value="{{ old('sss_deduction', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">PhilHealth Deduction</label>
                    <input type="number" step="0.01" name="ph_deduction" value="{{ old('ph_deduction', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pag-IBIG Deduction</label>
                    <input type="number" step="0.01" name="pi_deduction" value="{{ old('pi_deduction', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Section 4: Government IDs -->
        <div>
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                <span>🏛️</span> Government IDs
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">SSS Number</label>
                    <input type="text" name="sss_no" value="{{ old('sss_no') }}" placeholder="XX-XXXXXXX-X"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pag-IBIG Number</label>
                    <input type="text" name="pagibig_no" value="{{ old('pagibig_no') }}" placeholder="XXXX-XXXX-XXXX"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">PhilHealth Number</label>
                    <input type="text" name="philhealth_no" value="{{ old('philhealth_no') }}" placeholder="XX-XXXXXXXXX-X"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">TIN</label>
                    <input type="text" name="tin_no" value="{{ old('tin_no') }}" placeholder="XXX-XXX-XXX"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>
            </div>
        </div>

        <!-- Section 5: Education & IQ Results -->
        <div>
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                <span>🎓</span> Educational Background &amp; IQ Test Results
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Primary School</label>
                    <input type="text" name="primary_school" value="{{ old('primary_school') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Secondary / College</label>
                    <input type="text" name="secondary_school" value="{{ old('secondary_school') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Total Stanine</label>
                    <input type="text" name="total_stanine" value="{{ old('total_stanine') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Verbal</label>
                    <input type="text" name="verbal" value="{{ old('verbal') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Non-Verbal</label>
                    <input type="text" name="non_verbal" value="{{ old('non_verbal') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Holland Personality Job Fit</label>
                    <input type="text" name="holland_personality" value="{{ old('holland_personality') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Section 6: Last Employment & Family -->
        <div>
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                <span>💼</span> Previous Employment &amp; Family Info
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Previous Company</label>
                    <input type="text" name="prev_company" value="{{ old('prev_company') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Previous Position</label>
                    <input type="text" name="prev_position" value="{{ old('prev_position') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Last Salary</label>
                    <input type="number" step="0.01" name="last_salary" value="{{ old('last_salary') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Reason for Leaving</label>
                    <input type="text" name="reason_for_leaving" value="{{ old('reason_for_leaving') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Marriage Type / Civil Detail</label>
                    <select name="marriage_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- None / Single --</option>
                        <option value="civil" {{ old('marriage_type') == 'civil' ? 'selected' : '' }}>Civil Wedding</option>
                        <option value="church" {{ old('marriage_type') == 'church' ? 'selected' : '' }}>Church Wedding</option>
                        <option value="live_in" {{ old('marriage_type') == 'live_in' ? 'selected' : '' }}>Live-In</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Single Status Detail</label>
                    <select name="single_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- N/A --</option>
                        <option value="student" {{ old('single_status') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="non_student" {{ old('single_status') == 'non_student' ? 'selected' : '' }}>Non-Student</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Spouse Occupation</label>
                    <input type="text" name="spouse_occupation" value="{{ old('spouse_occupation') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Number of Children</label>
                    <input type="number" name="no_of_children" value="{{ old('no_of_children', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Number of Siblings</label>
                    <input type="number" name="no_of_siblings" value="{{ old('no_of_siblings', 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dependents Info</label>
                    <input type="text" name="dependents" value="{{ old('dependents') }}" placeholder="Names / Relationship of dependents"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Section 7: Admin Controls & Leave Allocations (Admin Exclusive) -->
        <div class="p-5 rounded-2xl bg-amber-50/50 border border-amber-200/80 shadow-xs">
            <div class="flex items-center justify-between border-b border-amber-200/60 pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="p-1 rounded-md bg-amber-100 text-amber-800 text-xs">🔒 Admin Only</span>
                        <span>Approval Body Role &amp; Leave Balances</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Visible and configurable strictly by HR Admins. Sets signing authority and leave credits.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 uppercase tracking-wider">Restricted</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <!-- Approval Body Role -->
                <div class="sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Approval Body Role *</label>
                    <select name="approval_role" class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white focus:ring-amber-500 focus:border-amber-500 font-medium">
                        <option value="none" {{ old('approval_role', 'none') == 'none' ? 'selected' : '' }}>None (Regular Staff)</option>
                        <option value="section_head" {{ old('approval_role') == 'section_head' ? 'selected' : '' }}>Section Head (Stage 1 Approver)</option>
                        <option value="hr_officer" {{ old('approval_role') == 'hr_officer' ? 'selected' : '' }}>HR Officer (Records & Email Monitor + Admin)</option>
                        <option value="it_officer" {{ old('approval_role') == 'it_officer' ? 'selected' : '' }}>IT Administrator / System Admin (Admin Access)</option>
                        <option value="executive_office" {{ old('approval_role') == 'executive_office' ? 'selected' : '' }}>Executive Office (Stage 2 Final Approver + Admin)</option>
                        <option value="executive_director" {{ old('approval_role') == 'executive_director' ? 'selected' : '' }}>Executive Director (Approves Exec Office + Admin)</option>
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">Selecting <strong>IT Administrator</strong>, <strong>HR Officer</strong>, <strong>Executive Office</strong>, or <strong>Executive Director</strong> automatically grants full HR/Admin system access.</p>
                </div>

                <!-- Vacation Leave Credits -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Vacation Leave (VL) Credits</label>
                    <div class="relative">
                        <input type="number" step="0.5" min="0" name="vacation_leave_credits" value="{{ old('vacation_leave_credits', 0) }}"
                               class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white focus:ring-amber-500 focus:border-amber-500 font-semibold text-slate-800 pr-12">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-bold text-slate-400">days</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Standard annual allotment</p>
                </div>

                <!-- Sick Leave Credits -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sick Leave (SL) Credits</label>
                    <div class="relative">
                        <input type="number" step="0.5" min="0" name="sick_leave_credits" value="{{ old('sick_leave_credits', 0) }}"
                               class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white focus:ring-amber-500 focus:border-amber-500 font-semibold text-slate-800 pr-12">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-bold text-slate-400">days</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Medical & sick leave bank</p>
                </div>

                <!-- Emergency Leave Credits -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Emergency Leave (EL) Credits</label>
                    <div class="relative">
                        <input type="number" step="0.5" min="0" name="emergency_leave_credits" value="{{ old('emergency_leave_credits', 0) }}"
                               class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white focus:ring-amber-500 focus:border-amber-500 font-semibold text-slate-800 pr-12">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-bold text-slate-400">days</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Urgent compassionate leave</p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.employees.index') }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-red-700 text-white text-sm font-bold hover:bg-red-800 shadow-sm transition">
                Save Employee Record
            </button>
        </div>
    </form>

</div>
@endsection
