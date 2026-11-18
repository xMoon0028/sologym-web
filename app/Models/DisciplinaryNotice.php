<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryNotice extends Model
{
    protected $fillable = [
        'notice_no',
        'employee_id',
        'issuer_id',
        'category_code',
        'category_name',
        'classification',
        'offense_title',
        'offense_occurrence',
        'sanction_type',
        'sanction_label',
        'requires_explanation',
        'explanation_deadline',
        'incident_date',
        'incident_time',
        'incident_location',
        'description',
        'hr_clarification_note',
        'attachment_path',
        'incident_report_path',
        'documentary_evidence_path',
        'scanned_nte_path',
        'nte_received_at',
        'nte_processed_by',
        'status',
        'employee_explanation',
        'employee_attachment_path',
        'explanation_submitted_at',
        'viewed_at',
        'hr_resolution_notes',
        'final_sanction',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'explanation_deadline' => 'date',
        'requires_explanation' => 'boolean',
        'nte_received_at' => 'datetime',
        'explanation_submitted_at' => 'datetime',
        'viewed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issuer_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function nteProcessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nte_processed_by');
    }

    /**
     * Check if the physical NTE submission is still pending from the staff.
     */
    public function getIsExplanationPendingAttribute(): bool
    {
        return $this->requires_explanation
            && empty($this->scanned_nte_path)
            && empty($this->nte_received_at)
            && !in_array($this->status, ['resolved', 'closed']);
    }

    /**
     * Check if the 2-day NTE submission deadline has lapsed.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->is_explanation_pending
            && $this->explanation_deadline
            && $this->explanation_deadline->isPast()
            && !$this->explanation_deadline->isToday();
    }

    /**
     * Check if the employee's time-in is currently blocked due to overdue NTE.
     */
    public function getIsTimeInBlockedAttribute(): bool
    {
        return $this->is_overdue;
    }

    /**
     * Status badge styling.
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_time_in_blocked) {
            return [
                'label' => 'NTE Overdue (Time-In Blocked)',
                'class' => 'bg-red-100 text-red-900 border-red-300 dark:bg-red-950/70 dark:text-red-200 dark:border-red-800 font-extrabold',
            ];
        }

        if (!empty($this->scanned_nte_path) || !empty($this->nte_received_at)) {
            if (in_array($this->status, ['resolved', 'closed'])) {
                return [
                    'label' => 'Case Resolved',
                    'class' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                ];
            }
            return [
                'label' => 'NTE Attached & Processed',
                'class' => 'bg-blue-50 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
            ];
        }

        return match ($this->status) {
            'issued' => [
                'label' => $this->requires_explanation ? 'NTE Pending (Submit to HR)' : 'Notice Issued',
                'class' => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
            ],
            'explanation_submitted' => [
                'label' => 'NTE Submitted to HR',
                'class' => 'bg-blue-50 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
            ],
            'under_review' => [
                'label' => 'Under Investigation',
                'class' => 'bg-purple-50 text-purple-800 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800',
            ],
            'resolved' => [
                'label' => 'Case Resolved',
                'class' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
            ],
            'closed' => [
                'label' => 'Closed',
                'class' => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
            ],
            default => [
                'label' => ucfirst($this->status),
                'class' => 'bg-slate-100 text-slate-700 border-slate-300',
            ],
        };
    }

    /**
     * Sanction badge styling.
     */
    public function getSanctionBadgeClassAttribute(): string
    {
        return match ($this->sanction_type) {
            'written_warning' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300',
            'stern_warning' => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300',
            'suspension' => 'bg-orange-50 text-orange-800 border-orange-300 dark:bg-orange-950/60 dark:text-orange-300',
            'dismissal' => 'bg-rose-50 text-rose-800 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300',
            'notice_to_explain' => 'bg-purple-50 text-purple-800 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-200',
        };
    }

    /**
     * Short clean sanction label for badges in tables.
     */
    public function getShortSanctionLabelAttribute(): string
    {
        return match ($this->sanction_type) {
            'written_warning' => 'Written Warning (WW)',
            'stern_warning' => 'Stern Warning (SW)',
            'suspension' => 'Suspension (SU)',
            'dismissal' => 'Dismissal (DI)',
            'notice_to_explain' => 'Notice to Explain (NTE)',
            'counseling' => 'Verbal Counseling',
            default => $this->sanction_label ?: ucwords(str_replace('_', ' ', $this->sanction_type)),
        };
    }

    /**
     * Complete categorized offenses catalog according to the LASAC Code of Conduct.
     */
    public static function getCatalog(): array
    {
        return [
            'minor' => [
                'group' => 'Minor Offenses',
                'categories' => [
                    'housekeeping' => [
                        'name' => 'Housekeeping and Sanitation',
                        'offenses' => [
                            'Littering, smudging, smearing, no orderly stuffing of files, or otherwise violating conditions of cleanliness and sanitation; Non-compliance with rules in dining areas or pantry use',
                        ],
                    ],
                    'attendance' => [
                        'name' => 'Attendance',
                        'offenses' => [
                            'Unauthorized undertime',
                            'Violation of break time and lunch schedules',
                        ],
                    ],
                    'unauthorized_absences' => [
                        'name' => 'Unauthorized Absences',
                        'offenses' => [
                            'Absence without leave (AWOL) for three (3) consecutive days',
                            'Failure to notify the office for absence by reason of sickness on or before 7:30 AM on the day of absence',
                            'Unauthorized absences of over four (4) days in a month',
                        ],
                    ],
                    'punctuality' => [
                        'name' => 'Punctuality & Timekeeping',
                        'offenses' => [
                            'An employee who comes in late for work for five (5) times within a month',
                            'Failure to register in the Timekeeping System without accomplishing the Failure to Log Form (FLF)',
                            'Unauthorized alteration and removal of the Timekeeping System',
                            'Intentional destruction of the Timekeeping System',
                        ],
                    ],
                    'uniform' => [
                        'name' => 'Wearing of Uniform',
                        'offenses' => [
                            'Reporting for work not in prescribed uniform',
                        ],
                    ],
                    'id_card' => [
                        'name' => 'Offenses Involving Identification Card',
                        'offenses' => [
                            'Failure or refusal to wear the identification card (ID) upon entering and while inside office premises or in official activities',
                            'Deliberate destruction, alteration and concealment of one’s or that of another’s ID card',
                            'Unauthorized and improper use of the company ID card',
                        ],
                    ],
                ],
            ],
            'major' => [
                'group' => 'Major Offenses',
                'categories' => [
                    'work_behavior' => [
                        'name' => 'Work Behavior & Decorum',
                        'offenses' => [
                            'Refusal to abide by the order to undergo annual physical examinations or physical checkup within the prescribed period',
                            'Loitering/Loafing in the company premises during office hours',
                            'Leaving one’s post without permission',
                            'Refusal to abide by auditing, security, and safety regulations in the performance of official work or in the use of Commission facilities',
                            'Sleeping/napping while on duty',
                            'Pretending to be sick to avoid reporting for work',
                            'Using, operating, and other similar acts of unauthorized computer software in Commission’s computer, laptop or devices',
                        ],
                    ],
                    'prohibited_activities' => [
                        'name' => 'Prohibited Activities',
                        'offenses' => [
                            'Engaging in or practicing usury within the company premises',
                            'Collecting monetary contribution for any purpose without authority from management',
                            'Borrowing and soliciting from beneficiaries and benefactors for personal use',
                            'Selling any kind of goods or services during office hours or while on duty within company premises',
                            'Coming to work under the influence of liquor',
                            'Drinking alcoholic beverage within Commission premises unless otherwise permitted',
                            'Possession, use and engaging in trade of prohibited drugs (Comprehensive Dangerous Drugs Act)',
                            'Reading irrelevant books, magazines, novels, newspapers or printed materials not related to work while on duty',
                            'Opening, accessing or browsing social networking sites, unofficial e-mail or similar websites during duty',
                            'Downloading application games from the internet on computer issued',
                            'Playing computer games or similar activities using computer/devices while on duty',
                            'Installation of unauthorized computer software in company computer/laptop/devices',
                            'Holding unauthorized meetings within company premises',
                            'Failure to log external calls using company telephone/cellular phone',
                            'Deliberate slowdown or dragging out of work',
                            'Smoking and vaping within the premises',
                        ],
                    ],
                    'serious_misbehavior' => [
                        'name' => 'Serious Misbehavior',
                        'offenses' => [
                            'Falsification of records or furnishing false information with deliberate intent to defraud Commission of cash, materials, properties',
                            'Illegal possession of firearms, explosives, deadly weapons within Commission premises',
                            'Providing false statements about himself in company records to mislead, misrepresent or conceal incriminatory information',
                            'Engaging in scandalous relationship with another while validly married',
                            'Committing immoral acts such as seduction, rape, abduction and acts of lasciviousness',
                        ],
                    ],
                    'sabotage_security' => [
                        'name' => 'Sabotage, Espionage & Trade Security',
                        'offenses' => [
                            'Deliberate destruction of Commission property with intention of adversely disrupting operations',
                            'Hacking company systems and databases',
                            'Intentionally infecting company systems and databases with virus and other types of malware',
                            'Stealing Commission files, documents, records, equipment, devices considered highly confidential',
                            'Selling or distribution of highly confidential matters of the company',
                        ],
                    ],
                    'safety_measures' => [
                        'name' => 'Breach of Security and Safety Measures',
                        'offenses' => [
                            'Failure or refusal to cooperate with security protocol enforced by management or security systems',
                            'Unauthorized entry in restricted areas',
                            'Assisting others to enter restricted areas without authority',
                            'Reckless use of company vehicle resulting in damage',
                        ],
                    ],
                    'disorderly_conduct' => [
                        'name' => 'Disorderly Conduct',
                        'offenses' => [
                            'Promoting and taking part in any game of chance, lottery, gambling within company premises',
                            'Uttering profanities, disrespectful language or displaying obscene acts towards co-worker',
                            'Uttering profanities, disrespectful language or displaying obscene acts towards a superior or management',
                            'Disregarding lawful orders of superiors',
                            'Gross insubordination where there is substantial damage to Commission property or injury to person',
                            'Provoking a fight',
                            'Threatening bodily harm to any member of the Commission',
                            'Inflicting intentional bodily harm to a co-worker',
                            'Posting or writing unauthorized notices or removing and mutilating Commission notices',
                            'Discourtesy to visitors and staff (disrespectful, obscene/foul language, rude manner of dealing with guests/staff)',
                            'Engaging in a brawl, mauling, fisticuffs or physical fight within company premises while being the instigator',
                        ],
                    ],
                    'betrayal_trust' => [
                        'name' => 'Betrayal of Trust & Dishonesty',
                        'offenses' => [
                            'Inflicting damage to Commission property for failure or refusal to report in writing the loss under custody within 24 hours',
                            'Misappropriation of company supplies, material or equipment',
                            'Wastage of Commission supplies and materials',
                            'Refusing to provide information or testify in Commission investigation when having knowledge of acts under investigation',
                            'Use of company name, goodwill, products and intellectual property for personal gain/profit without authority',
                            'Engage in personal business or part-time work during office hours',
                            'Theft of Commission property with market value of not more than ₱5,000.00 other than sum of money',
                            'Theft of Commission property with market value of ₱5,000.00 to ₱30,000.00',
                            'Theft of Commission property with market value of more than ₱30,000.00',
                            'Taking of Commission collection, cash or other sum of money less than ₱5,000.00 without consent',
                            'Taking of Commission collection, cash or other sum of money more than ₱5,000.00 without consent',
                            'Unauthorized use of company vehicle',
                            'Falsification of Commission receipts, forms, and instruments without monetary personal gain',
                            'Falsification of company receipts, forms, and instruments for personal monetary gain where amount is not more than ₱5,000.00',
                            'Falsification of company receipts, forms, and instruments for personal monetary gain where amount is more than ₱5,000.00',
                            'Defrauding for personal gain or conspiring to defraud company of legitimate revenue, cash, materials, properties',
                            'Accepting money or gift in exchange of doing or not doing job or other favor affecting Commission transactions',
                            'False testimony given during Commission investigation',
                        ],
                    ],
                    'neglect_duty' => [
                        'name' => 'Neglect of Duty',
                        'offenses' => [
                            'Violation of standard operating procedure where there is no direct monetary damage to Commission',
                            'Neglect of duty resulting in loss, damage, fine or penalty to Commission amounting to not more than ₱5,000.00',
                            'Neglect of duty resulting in loss, damage, fine or penalty to Commission amounting to not more than ₱20,000.00',
                            'Neglect of duty resulting in loss, damage, fine or penalty to Commission amounting to not more than ₱50,000.00',
                            'Neglect of duty resulting in loss, damage, fine or penalty to company amounting to more than ₱50,000.00',
                            'Neglect of duty resulting in injury to a person',
                        ],
                    ],
                    'conflict_interest' => [
                        'name' => 'Conflict of Interest & Confidentiality',
                        'offenses' => [
                            'Failure to promptly disclose potential or actual personal, financial, or familial conflict of interest',
                            'Breach of confidentiality regarding Commission internal affairs, donor details, or beneficiary records',
                        ],
                    ],
                ],
            ],
            'special' => [
                'group' => 'Special Cases',
                'categories' => [
                    'others' => [
                        'name' => 'Others (Special Cases requiring Notice of Explanation)',
                        'offenses' => [
                            'Other operational violation or incident requiring formal Notice to Explain (NTE)',
                            'Specific management directive non-compliance',
                            'Inter-departmental incident requiring inquiry',
                        ],
                    ],
                ],
            ],
        ];
    }
}
