@extends('layouts.admin')

@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee: ' . $employee->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ separateModal: false }">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.employees.profile', $employee) }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            &larr; Back to Profile
        </a>
    </div>

    <form method="POST" action="{{ route('admin.employees.update', $employee) }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6 space-y-8">
        @csrf
        @method('PUT')

        <!-- Section 1: Basic & Employment Info -->
        <div>
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4 flex items-center gap-2">
                <span>👤</span> Basic &amp; Employment Details
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $employee->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">ID Number / Employee No</label>
                    <input type="text" name="employee_no" value="{{ old('employee_no', $employee->employee_no) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-red-900 uppercase mb-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Official Work Email (@lasaclipa.org)
                    </label>
                    <input type="email" name="email" value="{{ old('email', $employee->email ?? optional($employee->user)->email) }}" placeholder="username@lasaclipa.org"
                           class="w-full px-3 py-2 border border-red-200 bg-red-50/20 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-medium text-gray-800 placeholder-gray-400">
                    <p class="text-[11px] text-gray-500 mt-1">Staff will use this official @lasaclipa.org Google account to sign in and view their attendance, leave credits, and payslips.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ old('department_id', $employee->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Position / Designation</label>
                    <input type="text" name="position" value="{{ old('position', $employee->position) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Salary Grade</label>
                    <input type="text" name="salary_grade" value="{{ old('salary_grade', $employee->salary_grade) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Employment Status</label>
                    <select name="employment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        @foreach(['Regular', 'Probationary', 'Contractual', 'Project-based'] as $st)
                            <option value="{{ $st }}" {{ old('employment_status', $employee->employment_status) == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date Hired</label>
                    <input type="date" name="date_hired" value="{{ old('date_hired', optional($employee->date_hired)->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Birthdate</label>
                    <input type="date" name="birthdate" value="{{ old('birthdate', optional($employee->birthdate)->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sex</label>
                    <select name="sex" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Select --</option>
                        <option value="Male" {{ old('sex', $employee->sex) == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('sex', $employee->sex) == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Civil Status</label>
                    <select name="civil_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Select --</option>
                        @foreach(['Single', 'Married', 'Widowed', 'Separated'] as $cs)
                            <option value="{{ $cs }}" {{ old('civil_status', $employee->civil_status) == $cs ? 'selected' : '' }}>{{ $cs }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Change Photo</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700">
                </div>
            </div>
        </div>

        <!-- Section 2: Addresses (PSGC Cascading Aligned) -->
        <div x-data="psgcAddressSelector({
            region: '{{ addslashes(old('region', optional($employee->address)->region ?? '')) }}',
            province: '{{ addslashes(old('province', optional($employee->address)->province ?? '')) }}',
            city: '{{ addslashes(old('municipality_city', optional($employee->address)->municipality_city ?? '')) }}',
            barangay: '{{ addslashes(old('barangay', optional($employee->address)->barangay ?? '')) }}'
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
                    <input type="text" name="permanent_address" value="{{ old('permanent_address', optional($employee->address)->permanent_address) }}" placeholder="House/Unit No., Street, Subdivision"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Current Address (if different)</label>
                    <input type="text" name="current_address" value="{{ old('current_address', optional($employee->address)->current_address) }}" placeholder="Current residence address"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">ZIP Code</label>
                    <input type="text" name="zip_code" value="{{ old('zip_code', optional($employee->address)->zip_code) }}" placeholder="e.g. 4217"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Section 3: Compensation & Fixed Deductions -->
        <div x-data="{
            basic: {{ old('basic_salary_80', optional($employee->compensation)->basic_salary_80 ?? 0) }},
            allowance: {{ old('allowance_20', optional($employee->compensation)->allowance_20 ?? 0) }},
            total: {{ old('total_compensation', optional($employee->compensation)->total_compensation ?? 0) }},
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
                    <input type="number" step="0.01" name="sss_deduction" value="{{ old('sss_deduction', optional($employee->compensation)->sss_deduction ?? 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">PhilHealth Deduction</label>
                    <input type="number" step="0.01" name="ph_deduction" value="{{ old('ph_deduction', optional($employee->compensation)->ph_deduction ?? 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pag-IBIG Deduction</label>
                    <input type="number" step="0.01" name="pi_deduction" value="{{ old('pi_deduction', optional($employee->compensation)->pi_deduction ?? 0) }}"
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
                    <input type="text" name="sss_no" value="{{ old('sss_no', optional($employee->governmentIds)->sss_no) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pag-IBIG Number</label>
                    <input type="text" name="pagibig_no" value="{{ old('pagibig_no', optional($employee->governmentIds)->pagibig_no) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">PhilHealth Number</label>
                    <input type="text" name="philhealth_no" value="{{ old('philhealth_no', optional($employee->governmentIds)->philhealth_no) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">TIN</label>
                    <input type="text" name="tin_no" value="{{ old('tin_no', optional($employee->governmentIds)->tin_no) }}"
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
                    <input type="text" name="primary_school" value="{{ old('primary_school', optional($employee->education)->primary_school) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Secondary / College</label>
                    <input type="text" name="secondary_school" value="{{ old('secondary_school', optional($employee->education)->secondary_school) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Total Stanine</label>
                    <input type="text" name="total_stanine" value="{{ old('total_stanine', optional($employee->iq)->total_stanine) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Verbal</label>
                    <input type="text" name="verbal" value="{{ old('verbal', optional($employee->iq)->verbal) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Non-Verbal</label>
                    <input type="text" name="non_verbal" value="{{ old('non_verbal', optional($employee->iq)->non_verbal) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Holland Personality</label>
                    <input type="text" name="holland_personality" value="{{ old('holland_personality', optional($employee->iq)->holland_personality) }}"
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
                    <input type="text" name="prev_company" value="{{ old('prev_company', optional($employee->lastEmployment)->company) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Previous Position</label>
                    <input type="text" name="prev_position" value="{{ old('prev_position', optional($employee->lastEmployment)->position) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Last Salary</label>
                    <input type="number" step="0.01" name="last_salary" value="{{ old('last_salary', optional($employee->lastEmployment)->last_salary) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Reason for Leaving</label>
                    <input type="text" name="reason_for_leaving" value="{{ old('reason_for_leaving', optional($employee->lastEmployment)->reason_for_leaving) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Marriage Type</label>
                    <select name="marriage_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- None / Single --</option>
                        @foreach(['civil' => 'Civil Wedding', 'church' => 'Church Wedding', 'live_in' => 'Live-In'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('marriage_type', optional($employee->family)->marriage_type) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Single Status Detail</label>
                    <select name="single_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">-- N/A --</option>
                        <option value="student" {{ old('single_status', optional($employee->family)->single_status) == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="non_student" {{ old('single_status', optional($employee->family)->single_status) == 'non_student' ? 'selected' : '' }}>Non-Student</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Spouse Occupation</label>
                    <input type="text" name="spouse_occupation" value="{{ old('spouse_occupation', optional($employee->family)->spouse_occupation) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Number of Children</label>
                    <input type="number" name="no_of_children" value="{{ old('no_of_children', optional($employee->family)->no_of_children ?? 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Number of Siblings</label>
                    <input type="number" name="no_of_siblings" value="{{ old('no_of_siblings', optional($employee->family)->no_of_siblings ?? 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dependents Info</label>
                    <input type="text" name="dependents" value="{{ old('dependents', optional($employee->family)->dependents) }}"
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
                        <option value="none" {{ old('approval_role', $employee->approval_role ?? 'none') == 'none' ? 'selected' : '' }}>None (Regular Staff)</option>
                        <option value="section_head" {{ old('approval_role', $employee->approval_role) == 'section_head' ? 'selected' : '' }}>Section Head (Stage 1 Approver)</option>
                        <option value="hr_officer" {{ old('approval_role', $employee->approval_role) == 'hr_officer' ? 'selected' : '' }}>HR Officer (Records & Email Monitor + Admin)</option>
                        <option value="it_officer" {{ old('approval_role', $employee->approval_role) == 'it_officer' ? 'selected' : '' }}>IT Administrator / System Admin (Admin Access)</option>
                        <option value="executive_office" {{ old('approval_role', $employee->approval_role) == 'executive_office' ? 'selected' : '' }}>Executive Office (Stage 2 Final Approver + Admin)</option>
                        <option value="executive_director" {{ old('approval_role', $employee->approval_role) == 'executive_director' ? 'selected' : '' }}>Executive Director (Approves Exec Office + Admin)</option>
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">Selecting <strong>IT Administrator</strong>, <strong>HR Officer</strong>, <strong>Executive Office</strong>, or <strong>Executive Director</strong> automatically grants full HR/Admin system access.</p>
                </div>

                <!-- Vacation Leave Credits -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Vacation Leave (VL) Credits</label>
                    <div class="relative">
                        <input type="number" step="0.5" min="0" name="vacation_leave_credits" value="{{ old('vacation_leave_credits', $employee->vacation_leave_credits ?? 0) }}"
                               class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white focus:ring-amber-500 focus:border-amber-500 font-semibold text-slate-800 pr-12">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-bold text-slate-400">days</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Available balance: <strong class="text-slate-800">{{ $employee->remaining_vacation_leave ?? 0 }} days</strong></p>
                </div>

                <!-- Sick Leave Credits -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sick Leave (SL) Credits</label>
                    <div class="relative">
                        <input type="number" step="0.5" min="0" name="sick_leave_credits" value="{{ old('sick_leave_credits', $employee->sick_leave_credits ?? 0) }}"
                               class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white focus:ring-amber-500 focus:border-amber-500 font-semibold text-slate-800 pr-12">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-bold text-slate-400">days</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Available balance: <strong class="text-slate-800">{{ $employee->remaining_sick_leave ?? 0 }} days</strong></p>
                </div>

                <!-- Emergency Leave Credits -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Emergency Leave (EL) Credits</label>
                    <div class="relative">
                        <input type="number" step="0.5" min="0" name="emergency_leave_credits" value="{{ old('emergency_leave_credits', $employee->emergency_leave_credits ?? 0) }}"
                               class="w-full px-3 py-2 border border-amber-300 rounded-lg text-sm bg-white focus:ring-amber-500 focus:border-amber-500 font-semibold text-slate-800 pr-12">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-bold text-slate-400">days</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Available balance: <strong class="text-slate-800">{{ $employee->remaining_emergency_leave ?? 0 }} days</strong></p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-4 border-t border-gray-200">
            @if($employee->is_active)
                <button type="button" @click="separateModal = true" class="text-xs font-bold text-rose-600 hover:text-rose-800 text-center sm:text-left py-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Mark as Resigned / Inactive</span>
                </button>
            @else
                <button type="button" onclick="if(confirm('Reactivate {{ addslashes($employee->name) }} to active staff?')) document.getElementById('reactivate-form').submit();" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 text-center sm:text-left py-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reactivate Employee</span>
                </button>
            @endif

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3">
                <a href="{{ route('admin.employees.profile', $employee) }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-red-700 text-white text-sm font-bold hover:bg-red-800 shadow-sm transition">
                    Update Employee Record
                </button>
            </div>
        </div>
    </form>

    @if(!$employee->is_active)
        <form id="reactivate-form" method="POST" action="{{ route('admin.employees.reactivate', $employee) }}" class="hidden">
            @csrf
        </form>
    @endif

    <!-- Separate / Mark Resigned Modal -->
    <div x-show="separateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="separateModal = false"></div>

            <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-gray-200 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    @method('DELETE')
                    <div class="bg-white p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center font-bold flex-shrink-0 border border-rose-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Record Employee Resignation</h3>
                                <p class="text-xs text-gray-400">{{ $employee->name }} (ID: {{ $employee->employee_no ?? '—' }})</p>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800">
                            <strong>Data Preservation Guarantee:</strong> This employee's complete profile, historical attendance, compensations, certificates, and past payslips will be retained 100% in the archives. They will simply be excluded from future payroll runs.
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Effective Separation Date</label>
                            <input type="date" name="separation_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-rose-600/20 focus:border-rose-600 transition">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Separation Reason</label>
                            <select name="separation_reason" required
                                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-rose-600/20 focus:border-rose-600 transition">
                                <option value="Resigned" selected>Resigned (Voluntary)</option>
                                <option value="End of Contract">End of Contract</option>
                                <option value="Terminated">Terminated</option>
                                <option value="Retired">Retired</option>
                                <option value="Personal Reasons">Personal / Health Reasons</option>
                                <option value="Other">Other / Transferred</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1.5">Notes / Clearance Remarks (Optional)</label>
                            <textarea name="separation_notes" rows="2" placeholder="e.g. Exit clearance completed, turnover done"
                                      class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-rose-600/20 focus:border-rose-600 transition"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="separateModal = false" class="w-full sm:w-auto px-4 py-2 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 transition">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-700 text-white rounded-xl text-xs font-bold hover:bg-rose-800 transition shadow-sm">Mark Resigned / Inactive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
