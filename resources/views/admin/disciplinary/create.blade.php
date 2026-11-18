@extends('layouts.admin')

@section('title', 'Issue Disciplinary Notice - Admin')
@section('page-title', 'Issue Disciplinary Notice')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    catalog: {{ json_encode($catalog) }},
    classification: 'minor',
    selectedCategory: '',
    selectedCategoryName: '',
    selectedOffense: '',
    customOffenseTitle: '',
    sanctionType: 'written_warning',
    occurrence: '1st',
    requiresExplanation: false,
    explanationDeadline: '{{ now()->addDays(2)->format('Y-m-d') }}',
    hrNote: 'Please see the HR Officer for clarification.',
    isOthers() {
        return this.selectedCategory === 'others';
    },
    isSternWarning() {
        return this.sanctionType === 'stern_warning' || this.sanctionType === 'notice_to_explain';
    },
    updateCategory(code, name, group) {
        this.selectedCategory = code;
        this.selectedCategoryName = name;
        this.classification = group;
        this.selectedOffense = '';
        this.checkRequirements();
    },
    onSanctionChange() {
        this.checkRequirements();
    },
    checkRequirements() {
        if (this.isSternWarning() || this.isOthers()) {
            this.requiresExplanation = true;
        }
    },
    getAvailableOffenses() {
        if (!this.selectedCategory) return [];
        for (const groupKey in this.catalog) {
            const group = this.catalog[groupKey];
            if (group.categories && group.categories[this.selectedCategory]) {
                return group.categories[this.selectedCategory].offenses || [];
            }
        }
        return [];
    }
}">

    <!-- Top Breadcrumb & Action Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.disciplinary.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-red-700 dark:hover:text-red-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Disciplinary Records</span>
        </a>
    </div>

    <!-- Main Creation Form Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 relative overflow-hidden">
        <div class="pb-5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 flex items-center justify-center font-black text-xs">COC</span>
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Issue Official Code of Conduct Notice</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Formal disciplinary action memorandum under the Commission Code of Conduct.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.disciplinary.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 pt-5">
            @csrf

            <!-- Hidden Inputs to bind dynamic Alpine state -->
            <input type="hidden" name="category_code" :value="selectedCategory">
            <input type="hidden" name="category_name" :value="selectedCategoryName">
            <input type="hidden" name="classification" :value="classification">

            <!-- Section 1: Employee & Incident Metadata -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Involved Employee <span class="text-red-600">*</span>
                    </label>
                    <select name="employee_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                        <option value="">Select Employee...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->position ?? 'Staff' }} &bull; {{ $emp->department?->name ?? 'General' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Date of Incident <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="incident_date" required value="{{ old('incident_date', now()->format('Y-m-d')) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Time of Incident (Optional)
                    </label>
                    <input type="text" name="incident_time" placeholder="e.g. 08:45 AM or During Lunch Shift" value="{{ old('incident_time') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Location of Incident (Optional)
                    </label>
                    <input type="text" name="incident_location" placeholder="e.g. Commission Office / Pantry / Remote Field" value="{{ old('incident_location') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <!-- Section 2: Categorized Violation Selection -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Violation Classification &amp; Specific Offense</span>
                    <span class="text-[10px] font-normal text-slate-400">(Referenced from Commission Code of Conduct)</span>
                </h3>

                <!-- Category Selector -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Select Category <span class="text-red-600">*</span>
                    </label>
                    <select required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500"
                            @change="
                                const opt = $event.target.selectedOptions[0];
                                updateCategory(opt.value, opt.dataset.name, opt.dataset.group);
                            ">
                        <option value="">-- Choose Offense Category --</option>
                        
                        <optgroup label="── MINOR OFFENSES ──">
                            @foreach($catalog['minor']['categories'] as $cKey => $cVal)
                                <option value="{{ $cKey }}" data-name="{{ $cVal['name'] }}" data-group="minor">
                                    [Minor] {{ $cVal['name'] }}
                                </option>
                            @endforeach
                        </optgroup>

                        <optgroup label="── MAJOR OFFENSES ──">
                            @foreach($catalog['major']['categories'] as $cKey => $cVal)
                                <option value="{{ $cKey }}" data-name="{{ $cVal['name'] }}" data-group="major">
                                    [Major] {{ $cVal['name'] }}
                                </option>
                            @endforeach
                        </optgroup>

                        <optgroup label="── SPECIAL CASES ──">
                            <option value="others" data-name="Others (Special Case)" data-group="special">
                                [Special Case] Others (Requires Notice of Explanation)
                            </option>
                        </optgroup>
                    </select>
                </div>

                <!-- Offense Dropdown (Dynamically populated from catalog) -->
                <div x-show="selectedCategory && !isOthers()" x-cloak>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Specific Offense Provision <span class="text-red-600">*</span>
                    </label>
                    <select name="offense_title" x-model="selectedOffense"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-medium dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Select specific offense item --</option>
                        <template x-for="item in getAvailableOffenses()" :key="item">
                            <option :value="item" x-text="item"></option>
                        </template>
                    </select>
                </div>

                <!-- Custom Offense Description if 'Others' is selected -->
                <div x-show="isOthers()" x-cloak class="p-4 bg-purple-50 dark:bg-purple-950/30 rounded-2xl border border-purple-200 dark:border-purple-800/60 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-purple-200 dark:bg-purple-900/60 text-purple-800 dark:text-purple-300 flex items-center justify-center font-bold text-xs">★</span>
                        <h4 class="text-xs font-bold text-purple-950 dark:text-purple-200">Special Case / Others Specification</h4>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-purple-900 dark:text-purple-300 mb-1">
                            Specify Offense / Matter Requiring Explanation <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="custom_offense_title" x-model="customOffenseTitle"
                               placeholder="e.g. Failure to comply with urgent departmental memorandum regarding audit submission"
                               class="w-full px-3.5 py-2 rounded-xl border border-purple-300 dark:border-purple-700 text-xs font-medium bg-white dark:bg-slate-800 dark:text-slate-100">
                    </div>
                    <p class="text-[11px] text-purple-700 dark:text-purple-300">
                        ✓ In special cases under <strong>Others</strong>, a formal <strong>Notice of Explanation (NTE)</strong> is automatically required with a 2-day submission window.
                    </p>
                </div>
            </div>

            <!-- Section 3: Sanctions, Occurrence & Notice to Explain Rule -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">
                    Sanction &amp; Notice of Explanation Requirement
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Occurrence Count <span class="text-red-600">*</span>
                        </label>
                        <select name="offense_occurrence" x-model="occurrence" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                            <option value="1st">1st Occurrence</option>
                            <option value="2nd">2nd Occurrence</option>
                            <option value="3rd">3rd Occurrence</option>
                            <option value="4th">4th Occurrence</option>
                            <option value="Habitual">Habitual Offense</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Sanction Type <span class="text-red-600">*</span>
                        </label>
                        <select name="sanction_type" x-model="sanctionType" @change="onSanctionChange()" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                            <option value="written_warning">Written Warning (WW)</option>
                            <option value="stern_warning">Stern Warning (SW) - Requires Notice of Explanation</option>
                            <option value="notice_to_explain">Notice to Explain (NTE)</option>
                            <option value="suspension">Suspension (SU)</option>
                            <option value="dismissal">Dismissal (DI)</option>
                            <option value="counseling">Verbal Counseling / Reprimand</option>
                        </select>
                    </div>
                </div>

                <!-- Notice to Explain Toggle & 2-Day Deadline Rule -->
                <div class="p-4 rounded-2xl border transition-all"
                     :class="requiresExplanation ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-300 dark:border-amber-800' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700'">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="requires_explanation" value="1" x-model="requiresExplanation"
                                class="mt-1 rounded text-red-700 focus:ring-red-500">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-slate-100">
                                Require Formal Notice of Explanation (NTE)
                            </span>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed">
                                Staff is required to personally proceed to the Human Resources (HR) Department to submit their written Notice to Explain within two (2) days. Failure to submit within 2 days will restrict them from timing in.
                            </p>
                        </div>
                    </label>

                    <div x-show="requiresExplanation" x-cloak class="mt-3 pt-3 border-t border-amber-200/80 dark:border-amber-800/60 grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 dark:text-amber-300 uppercase mb-1">
                                Explanation Deadline (2 Days Compliance) *
                            </label>
                            <input type="date" name="explanation_deadline" x-model="explanationDeadline" required
                                   class="w-full px-3 py-1.5 rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                        </div>
                        <div class="text-[11px] text-amber-800 dark:text-amber-300 italic pt-3 sm:pt-0">
                            ⚠️ Per Code of Conduct Section b.ii: Employee has two (2) days to submit their physical NTE to HR. If lapsed without HR attaching the scanned copy, Time-In will be blocked automatically.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Narrative Facts & Specifications -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Statement of Facts &amp; Narrative of Incident <span class="text-red-600">*</span>
                    </label>
                    <textarea name="description" rows="4" required minlength="5"
                              placeholder="Detail the exact factual circumstances, acts or omissions, and conduct observed..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">{{ old('description') }}</textarea>
                </div>

                <!-- HR Clarification Prompt -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Clarification Directive <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="hr_clarification_note" x-model="hrNote" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-red-700 dark:text-red-400 bg-slate-50 dark:bg-slate-800">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        This instruction is prominently displayed on the staff notice, portal view, and email notification.
                    </p>
                </div>

                <!-- HR Confidential Uploads Notice -->
                <div class="p-3 bg-slate-100 dark:bg-slate-800/70 rounded-xl border border-slate-300 dark:border-slate-700 flex items-start gap-2.5">
                    <span class="text-sm">🔒</span>
                    <p class="text-[11px] text-slate-700 dark:text-slate-300 leading-relaxed">
                        <strong>HR / Admin Confidentiality Notice:</strong> The Incident Report and Documentary Evidence uploaded below are strictly internal and visible <em>only</em> to HR/Admin. They will <strong>NOT</strong> be displayed in the employee portal and will <strong>NOT</strong> be attached to staff emails.
                    </p>
                </div>

                <!-- Separate File Uploads: Incident Report (Required) and Documentary Evidence (Optional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                            Incident Report <span class="text-red-600">* (Required)</span>
                        </label>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block mb-2">
                            Formal incident report file (PDF, DOCX, JPG, PNG &bull; max 10MB) &bull; 🔒 HR Only
                        </span>
                        <input type="file" name="incident_report" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-700 file:text-white hover:file:bg-red-800">
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                            Documentary Evidence <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block mb-2">
                            Supplemental logs, photos, CCTV captures, or vouchers &bull; 🔒 HR Only
                        </span>
                        <input type="file" name="documentary_evidence" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-300">
                    </div>
                </div>
            </div>

            <!-- Notification Dispatch Notice -->
            <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-200 dark:border-blue-900/60 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-[11px] text-blue-900 dark:text-blue-200 leading-relaxed">
                    <strong>Notice &amp; Delivery:</strong> The employee will receive an in-app portal notification and email with the notice details and directive: <em>"Please see the HR Officer for clarification."</em> If an NTE is required, the employee is directed to submit their written explanation directly to HR within 2 days to avoid time-in restriction.
                </p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.disciplinary.index') }}"
                   class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    Cancel
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold transition shadow-sm">
                    Issue Notice &amp; Dispatch Notification
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
