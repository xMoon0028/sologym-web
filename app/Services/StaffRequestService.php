<?php

namespace App\Services;

use App\Models\CertificateTraining;
use App\Models\Employee;
use App\Models\StaffRequest;
use App\Models\StaffRequestApproval;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StaffRequestService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new Seminar / Training request with multi-stage approval routing.
     * Flow:
     *  - Regular staff (approval_role 'none' or 'hr_officer') -> Section Head -> Executive Office
     *  - Section Head ('section_head') -> Executive Office
     *  - Executive Office ('executive_office' or 'executive_director') -> Executive Director
     */
    public function createSeminarRequest(Employee $employee, array $data, ?UploadedFile $invitationFile = null): StaffRequest
    {
        return DB::transaction(function () use ($employee, $data, $invitationFile) {
            $invitationPath = null;
            if ($invitationFile) {
                $invitationPath = $invitationFile->store('seminar-invitations', 'public');
            }

            // Determine routing stage based on employee's approval role
            if ($employee->approval_role === 'section_head') {
                $initialStatus = 'pending_executive';
                $initialStage = 'executive_office';
                $stageDescription = 'Pending Executive Office Final Approval';
            } elseif (in_array($employee->approval_role, ['executive_office', 'executive_director'])) {
                $initialStatus = 'pending_executive_director';
                $initialStage = 'executive_director';
                $stageDescription = 'Pending Executive Director Final Approval';
            } else {
                $initialStatus = 'pending_section_head';
                $initialStage = 'section_head';
                $stageDescription = 'Pending Section Head Endorsement';
            }

            $request = StaffRequest::create([
                'employee_id' => $employee->id,
                'type' => 'seminar',
                'status' => $initialStatus,
                'current_stage' => $initialStage,
                'seminar_title' => $data['seminar_title'],
                'organizer' => $data['organizer'] ?? null,
                'venue' => $data['venue'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? $data['start_date'],
                'estimated_budget' => $data['estimated_budget'] ?? 0,
                'invitation_file_path' => $invitationPath,
                'justification' => $data['justification'] ?? null,
            ]);

            // 1. Record initial submission in audit trail
            StaffRequestApproval::create([
                'staff_request_id' => $request->id,
                'stage' => 'submission',
                'action' => 'submitted',
                'approver_id' => auth()->id(),
                'approver_name' => $employee->name,
                'approver_role' => 'Applicant',
                'notes' => 'Seminar Request filed: ' . $request->seminar_title . ($request->justification ? ' (Justification: ' . $request->justification . ')' : ''),
            ]);

            $notifiedIds = [];

            // 2. Email Notification to the Applicant
            $applicantUser = $employee->user ?: ($employee->email ? User::where('email', $employee->email)->first() : null);
            if ($applicantUser) {
                $notifiedIds[] = $applicantUser->id;
                $this->notificationService->notifyUser(
                    $applicantUser,
                    'request_submitted',
                    "Seminar Request Submitted: {$request->seminar_title}",
                    "Your request to attend \"{$request->seminar_title}\" from " .
                    $request->start_date->format('M d, Y') . " to " . $request->end_date->format('M d, Y') .
                    " has been submitted successfully.\n\nCurrent Status: {$stageDescription}. You will be notified via email at each stage of the approval trail.",
                    route('portal.requests.show', $request)
                );
            }

            // 3. Email Notification to designated Approvers for initial stage
            if ($initialStage === 'section_head') {
                $sectionHeads = $this->getSectionHeads($employee);
                foreach ($sectionHeads as $head) {
                    if (!in_array($head->id, $notifiedIds)) {
                        $notifiedIds[] = $head->id;
                        $this->notificationService->notifyUser(
                            $head,
                            'request_submitted',
                            "Action Required: Approve Seminar Request (#SR-{$request->id})",
                            "{$employee->name} ({$employee->department?->name}) requested approval to attend the seminar \"{$request->seminar_title}\" from " .
                            $request->start_date->format('M d, Y') . " to " . $request->end_date->format('M d, Y') .
                            ($request->estimated_budget > 0 ? " (Est. Budget: ₱" . number_format($request->estimated_budget, 2) . ")" : "") . ".\n\nPlease review and approve or disapprove this request.",
                            $this->resolveRequestUrl($head, $request)
                        );
                    }
                }
            } elseif ($initialStage === 'executive_office') {
                $execUsers = $this->getExecutiveApprovers();
                foreach ($execUsers as $execUser) {
                    if (!in_array($execUser->id, $notifiedIds)) {
                        $notifiedIds[] = $execUser->id;
                        $this->notificationService->notifyUser(
                            $execUser,
                            'request_submitted',
                            "Action Required: Section Head Seminar Request (#SR-{$request->id})",
                            "Section Head {$employee->name} ({$employee->department?->name}) requested approval to attend the seminar \"{$request->seminar_title}\" from " .
                            $request->start_date->format('M d, Y') . " to " . $request->end_date->format('M d, Y') . ".\n\nAwaiting Executive Office final approval.",
                            $this->resolveRequestUrl($execUser, $request)
                        );
                    }
                }
            } elseif ($initialStage === 'executive_director') {
                $dirUsers = $this->getExecutiveDirectors();
                foreach ($dirUsers as $dirUser) {
                    if (!in_array($dirUser->id, $notifiedIds)) {
                        $notifiedIds[] = $dirUser->id;
                        $this->notificationService->notifyUser(
                            $dirUser,
                            'request_submitted',
                            "Action Required: Executive Seminar Request (#SR-{$request->id})",
                            "Executive Member {$employee->name} requested approval to attend the seminar \"{$request->seminar_title}\".\n\nAwaiting Executive Director approval.",
                            $this->resolveRequestUrl($dirUser, $request)
                        );
                    }
                }
            }

            // 4. Email Notification to HR Officers / Admins for monitoring & records
            $hrUsers = $this->getHrOfficers();
            foreach ($hrUsers as $hrUser) {
                if (!in_array($hrUser->id, $notifiedIds)) {
                    $notifiedIds[] = $hrUser->id;
                    $this->notificationService->notifyUser(
                        $hrUser,
                        'request_submitted',
                        "HR Record: New Seminar Request (#SR-{$request->id})",
                        "{$employee->name} ({$employee->department?->name}) submitted a Seminar Request for \"{$request->seminar_title}\". Current Status: {$stageDescription}.",
                        route('admin.requests.show', $request)
                    );
                }
            }

            return $request;
        });
    }

    /**
     * Create a new Document request (e.g., COE, Service Record).
     */
    public function createDocumentRequest(Employee $employee, array $data): StaffRequest
    {
        return DB::transaction(function () use ($employee, $data) {
            $request = StaffRequest::create([
                'employee_id' => $employee->id,
                'type' => 'document',
                'status' => 'pending',
                'current_stage' => 'hr',
                'document_type' => $data['document_type'],
                'purpose' => $data['purpose'],
                'date_needed' => $data['date_needed'] ?? null,
            ]);

            // Record submission in audit trail
            StaffRequestApproval::create([
                'staff_request_id' => $request->id,
                'stage' => 'submission',
                'action' => 'submitted',
                'approver_id' => auth()->id(),
                'approver_name' => $employee->name,
                'approver_role' => 'Applicant',
                'notes' => 'Document Request submitted for: ' . $request->document_type . ' (Purpose: ' . $request->purpose . ')',
            ]);

            $dateNeededStr = $request->date_needed ? " (Date Needed: " . $request->date_needed->format('M d, Y') . ")" : "";

            // Notify HR Officers and Admins
            $this->notifyAdmins(
                'request_submitted',
                "New Document Request: {$request->document_type}",
                "{$employee->name} ({$employee->department?->name}) requested a \"{$request->document_type}\" for: \"{$request->purpose}\"{$dateNeededStr}.",
                route('admin.requests.show', $request)
            );

            return $request;
        });
    }

    /**
     * Process multi-stage approval or rejection for a Seminar Request.
     */
    public function processSeminarApproval(
        StaffRequest $request,
        User $approver,
        string $stage,
        string $action,
        ?string $notes = null
    ): StaffRequest {
        if (!$request->is_seminar) {
            throw ValidationException::withMessages(['type' => 'This request is not a seminar request.']);
        }

        return DB::transaction(function () use ($request, $approver, $stage, $action, $notes) {
            $approverEmp = $approver->employee;
            $isSuperAdmin = (!$approverEmp && $approver->email === 'admin@lasac.com') || $approver->hasRole('admin');

            // 1. Role Authorization Check
            if (!$isSuperAdmin) {
                if ($stage === 'section_head') {
                    if (!$approverEmp || $approverEmp->approval_role !== 'section_head') {
                        throw ValidationException::withMessages(['stage' => 'Only designated Section Heads can approve requests at this stage.']);
                    }
                    if ($approverEmp->department_id !== $request->employee->department_id) {
                        throw ValidationException::withMessages(['stage' => 'Section Heads can only approve requests from their own department.']);
                    }
                } elseif (in_array($stage, ['executive_office', 'executive'])) {
                    if (!$approverEmp || !in_array($approverEmp->approval_role, ['executive_office', 'executive_director'])) {
                        throw ValidationException::withMessages(['stage' => 'Only the Executive Office can grant final approval at this stage.']);
                    }
                } elseif ($stage === 'executive_director') {
                    if (!$approverEmp || $approverEmp->approval_role !== 'executive_director') {
                        throw ValidationException::withMessages(['stage' => 'Only the Executive Director can approve requests at this stage.']);
                    }
                }
            }

            // Determine approver role label
            $approverRole = match ($stage) {
                'section_head' => 'Section Head',
                'executive_office', 'executive' => 'Executive Office',
                'executive_director' => 'Executive Director',
                default => 'Approver',
            };
            if ($isSuperAdmin && !$approverEmp) {
                $approverRole .= ' (System Admin)';
            }

            $cleanNotes = !empty($notes) ? trim($notes) : null;
            $noteText = $cleanNotes ? "\n\nRemarks: \"{$cleanNotes}\"" : '';

            // 2. Rejection Flow
            if ($action === 'rejected') {
                $request->update([
                    'status' => 'rejected',
                    'current_stage' => null,
                    'rejection_reason' => $cleanNotes ?? 'Disapproved during review',
                    'reviewed_by' => $approver->id,
                    'reviewed_at' => now(),
                ]);

                StaffRequestApproval::create([
                    'staff_request_id' => $request->id,
                    'stage' => $stage,
                    'action' => 'rejected',
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'approver_role' => $approverRole,
                    'notes' => $cleanNotes ?? 'Disapproved during review.',
                ]);

                // Notify Applicant of Disapproval
                $applicantUser = $request->employee->user ?: ($request->employee->email ? User::where('email', $request->employee->email)->first() : null);
                if ($applicantUser) {
                    $this->notificationService->notifyUser(
                        $applicantUser,
                        'request_rejected',
                        "Seminar Request Disapproved ({$approverRole}): {$request->seminar_title}",
                        "Your seminar request to attend \"{$request->seminar_title}\" was disapproved at the {$approverRole} stage by {$approver->name}.{$noteText}",
                        route('portal.requests.show', $request)
                    );
                }

                // Notify HR of Disapproval for records
                $hrUsers = $this->getHrOfficers();
                foreach ($hrUsers as $hrUser) {
                    if (!$applicantUser || $hrUser->id !== $applicantUser->id) {
                        $this->notificationService->notifyUser(
                            $hrUser,
                            'request_rejected',
                            "HR Record: Seminar Disapproved (#SR-{$request->id})",
                            "{$request->employee->name}'s seminar request for \"{$request->seminar_title}\" was disapproved by {$approver->name} ({$approverRole}).{$noteText}",
                            route('admin.requests.show', $request)
                        );
                    }
                }

                return $request->fresh();
            }

            // 3. Approval Flow
            if ($stage === 'section_head') {
                // Section Head Approved -> Forward to Executive Office
                $request->update([
                    'status' => 'pending_executive',
                    'current_stage' => 'executive_office',
                ]);

                StaffRequestApproval::create([
                    'staff_request_id' => $request->id,
                    'stage' => 'section_head',
                    'action' => 'approved',
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'approver_role' => $approverRole,
                    'notes' => $cleanNotes ?? 'Endorsed and approved by Section Head.',
                ]);

                $notifiedIds = [$approver->id];

                // Notify Applicant
                $applicantUser = $request->employee->user ?: ($request->employee->email ? User::where('email', $request->employee->email)->first() : null);
                if ($applicantUser) {
                    $notifiedIds[] = $applicantUser->id;
                    $this->notificationService->notifyUser(
                        $applicantUser,
                        'request_approved',
                        "Section Head Approved: {$request->seminar_title}",
                        "Good news! Your Section Head ({$approver->name}) has approved and recommended your seminar request to attend \"{$request->seminar_title}\".{$noteText}\n\nYour application has been forwarded to the Executive Office for final approval.",
                        route('portal.requests.show', $request)
                    );
                }

                // Notify Executive Office Approvers
                $execUsers = $this->getExecutiveApprovers();
                foreach ($execUsers as $execUser) {
                    if (!in_array($execUser->id, $notifiedIds)) {
                        $notifiedIds[] = $execUser->id;
                        $this->notificationService->notifyUser(
                            $execUser,
                            'request_submitted',
                            "Action Required: Executive Approval for Seminar Request (#SR-{$request->id})",
                            "{$request->employee->name}'s Seminar Request for \"{$request->seminar_title}\" was approved by Section Head ({$approver->name}). Awaiting Executive Office final approval.",
                            $this->resolveRequestUrl($execUser, $request)
                        );
                    }
                }

                // Notify HR for monitoring
                $hrUsers = $this->getHrOfficers();
                foreach ($hrUsers as $hrUser) {
                    if (!in_array($hrUser->id, $notifiedIds)) {
                        $notifiedIds[] = $hrUser->id;
                        $this->notificationService->notifyUser(
                            $hrUser,
                            'request_approved',
                            "HR Record: Seminar Endorsed by Section Head (#SR-{$request->id})",
                            "{$request->employee->name}'s Seminar Request for \"{$request->seminar_title}\" was approved by Section Head ({$approver->name}) and is now pending Executive Office approval.",
                            route('admin.requests.show', $request)
                        );
                    }
                }
            } else {
                // Executive Office / Executive Director Final Approval
                $request->update([
                    'status' => 'approved',
                    'current_stage' => null,
                    'approved_at' => now(),
                    'reviewed_by' => $approver->id,
                    'reviewed_at' => now(),
                    'admin_notes' => $cleanNotes ?? $request->admin_notes,
                ]);

                StaffRequestApproval::create([
                    'staff_request_id' => $request->id,
                    'stage' => $stage,
                    'action' => 'approved',
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name,
                    'approver_role' => $approverRole,
                    'notes' => $cleanNotes ?? 'Final official approval granted by Executive Office.',
                ]);

                $notifiedIds = [$approver->id];

                // Notify Applicant of Final Approval + Mandatory Report Requirement
                $applicantUser = $request->employee->user ?: ($request->employee->email ? User::where('email', $request->employee->email)->first() : null);
                if ($applicantUser) {
                    $notifiedIds[] = $applicantUser->id;
                    $title = "🎓 Seminar Request Officially Approved: {$request->seminar_title}";
                    $message = "Congratulations! Your request to attend \"{$request->seminar_title}\" from " .
                        $request->start_date->format('M d, Y') . " to " . $request->end_date->format('M d, Y') .
                        " has received official FINAL APPROVAL from the Executive Office ({$approver->name}).{$noteText}\n\n" .
                        "📌 MANDATORY NEXT STEP: After completing this seminar, you are required to submit an official Seminar Report (Key Learnings, Action Plan, and Certificate) via your LASAC HRIS Portal.";

                    $this->notificationService->notifyUser($applicantUser, 'request_approved', $title, $message, route('portal.requests.show', $request));
                }

                // Notify Section Head of Final Approval
                $sectionHeads = $this->getSectionHeads($request->employee);
                foreach ($sectionHeads as $head) {
                    if (!in_array($head->id, $notifiedIds)) {
                        $notifiedIds[] = $head->id;
                        $this->notificationService->notifyUser(
                            $head,
                            'request_approved',
                            "Seminar Request Approved: {$request->employee->name}",
                            "{$request->employee->name}'s seminar attendance for \"{$request->seminar_title}\" has received final approval from the {$approverRole} ({$approver->name}).",
                            $this->resolveRequestUrl($head, $request)
                        );
                    }
                }

                // Notify HR Officers for record
                $hrUsers = $this->getHrOfficers();
                foreach ($hrUsers as $hrUser) {
                    if (!in_array($hrUser->id, $notifiedIds)) {
                        $notifiedIds[] = $hrUser->id;
                        $this->notificationService->notifyUser(
                            $hrUser,
                            'request_approved',
                            "HR Record: Seminar Fully Approved (#SR-{$request->id})",
                            "{$request->employee->name}'s Seminar Request for \"{$request->seminar_title}\" has received final official approval from {$approverRole} ({$approver->name}).",
                            route('admin.requests.show', $request)
                        );
                    }
                }
            }

            return $request->fresh();
        });
    }

    /**
     * Approve a Staff Request (Admin / Legacy wrapper).
     */
    public function approveRequest(StaffRequest $request, User $reviewer, ?string $adminNotes = null, ?UploadedFile $documentFile = null): StaffRequest
    {
        if ($request->is_seminar) {
            // Determine appropriate stage if processing from admin
            $stage = in_array($request->status, ['pending_executive', 'pending_executive_director']) ? 'executive_office' : 'section_head';
            return $this->processSeminarApproval($request, $reviewer, $stage, 'approved', $adminNotes);
        }

        // Document request approved & fulfilled by HR
        return DB::transaction(function () use ($request, $reviewer, $adminNotes, $documentFile) {
            $docPath = $request->document_file_path;
            if ($documentFile) {
                if ($docPath) {
                    Storage::disk('public')->delete($docPath);
                }
                $docPath = $documentFile->store('request-documents', 'public');
            }

            $request->update([
                'status' => 'completed',
                'current_stage' => null,
                'document_file_path' => $docPath,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'approved_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            StaffRequestApproval::create([
                'staff_request_id' => $request->id,
                'stage' => 'hr',
                'action' => 'approved',
                'approver_id' => $reviewer->id,
                'approver_name' => $reviewer->name,
                'approver_role' => 'HR Officer',
                'notes' => $adminNotes ?? 'Document issued and fulfilled.',
            ]);

            $employee = $request->employee;
            $title = "✅ Document Request Ready: {$request->document_type}";
            $message = "Your request for \"{$request->document_type}\" has been APPROVED and processed by HR." .
                ($docPath ? " An official digital copy has been attached to your portal request for download." : " Please coordinate with the HR office to receive your printed copy.") .
                (!empty($adminNotes) ? "\n\nHR Notes: \"{$adminNotes}\"" : "");

            $this->notifyUserOrEmployee($employee, 'request_approved', $title, $message, route('portal.requests.show', $request));

            return $request->fresh();
        });
    }

    /**
     * Reject a Staff Request.
     */
    public function rejectRequest(StaffRequest $request, User $reviewer, string $reason): StaffRequest
    {
        if ($request->is_seminar) {
            $stage = in_array($request->status, ['pending_executive', 'pending_executive_director']) ? 'executive_office' : 'section_head';
            return $this->processSeminarApproval($request, $reviewer, $stage, 'rejected', $reason);
        }

        return DB::transaction(function () use ($request, $reviewer, $reason) {
            $request->update([
                'status' => 'rejected',
                'current_stage' => null,
                'rejection_reason' => $reason,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            StaffRequestApproval::create([
                'staff_request_id' => $request->id,
                'stage' => 'hr',
                'action' => 'rejected',
                'approver_id' => $reviewer->id,
                'approver_name' => $reviewer->name,
                'approver_role' => 'HR Officer',
                'notes' => $reason,
            ]);

            $employee = $request->employee;
            $title = "❌ Request Disapproved: {$request->document_type}";
            $message = "Your request for {$request->document_type} was not approved by HR.\n\nReason: \"{$reason}\".";

            $this->notifyUserOrEmployee($employee, 'request_rejected', $title, $message, route('portal.requests.show', $request));

            return $request->fresh();
        });
    }

    /**
     * Submit Seminar Report (Mandatory Post-Seminar Step).
     */
    public function submitSeminarReport(StaffRequest $request, array $data, ?UploadedFile $certFile = null, ?UploadedFile $materialsFile = null): StaffRequest
    {
        if (!$request->is_seminar) {
            throw ValidationException::withMessages(['report' => 'Only seminar requests require a seminar report.']);
        }

        if (!in_array($request->status, ['approved', 'report_pending'])) {
            throw ValidationException::withMessages(['report' => 'This seminar request is not eligible for report submission.']);
        }

        return DB::transaction(function () use ($request, $data, $certFile, $materialsFile) {
            $certPath = $request->certificate_file_path;
            if ($certFile) {
                if ($certPath) {
                    Storage::disk('public')->delete($certPath);
                }
                $certPath = $certFile->store('seminar-certificates', 'public');

                CertificateTraining::create([
                    'employee_id' => $request->employee_id,
                    'type' => 'training',
                    'title' => $request->seminar_title,
                    'issuer' => $request->organizer ?: 'External Training',
                    'date_issued' => $request->end_date ?? now()->toDateString(),
                    'file_path' => $certPath,
                    'notes' => 'Auto-recorded from submitted Seminar Report for Request #' . $request->id,
                ]);
            }

            $matPath = $request->additional_materials_path;
            if ($materialsFile) {
                if ($matPath) {
                    Storage::disk('public')->delete($matPath);
                }
                $matPath = $materialsFile->store('seminar-materials', 'public');
            }

            $request->update([
                'status' => 'report_pending',
                'report_submitted_at' => now(),
                'report_summary' => $data['report_summary'],
                'report_action_plan' => $data['report_action_plan'] ?? null,
                'certificate_file_path' => $certPath,
                'additional_materials_path' => $matPath,
                'report_status' => 'pending_review',
            ]);

            // Record in audit trail
            StaffRequestApproval::create([
                'staff_request_id' => $request->id,
                'stage' => 'report_submission',
                'action' => 'submitted',
                'approver_id' => auth()->id(),
                'approver_name' => $request->employee->name,
                'approver_role' => 'Participant',
                'notes' => 'Official Seminar Report & Certificate submitted for HR review.',
            ]);

            // Notify HR that the seminar report was submitted
            $employee = $request->employee;
            $this->notifyAdmins(
                'seminar_report_submitted',
                "📋 Seminar Report Submitted: {$request->seminar_title}",
                "{$employee->name} has submitted the post-training Seminar Report for \"{$request->seminar_title}\". Please review the summary, action plan, and attached credentials.",
                route('admin.requests.show', $request)
            );

            return $request->fresh();
        });
    }

    /**
     * Acknowledge and finalize a submitted Seminar Report.
     */
    public function acknowledgeSeminarReport(StaffRequest $request, User $reviewer, ?string $notes = null): StaffRequest
    {
        return DB::transaction(function () use ($request, $reviewer, $notes) {
            $request->update([
                'status' => 'completed',
                'report_status' => 'acknowledged',
                'report_acknowledged_by' => $reviewer->id,
                'report_acknowledged_at' => now(),
                'admin_notes' => $notes ?: $request->admin_notes,
            ]);

            StaffRequestApproval::create([
                'staff_request_id' => $request->id,
                'stage' => 'hr_report',
                'action' => 'approved',
                'approver_id' => $reviewer->id,
                'approver_name' => $reviewer->name,
                'approver_role' => 'HR Officer',
                'notes' => $notes ?? 'Seminar report acknowledged and training credentials officially logged.',
            ]);

            $employee = $request->employee;
            $title = "🎓 Seminar Report Acknowledged & Completed: {$request->seminar_title}";
            $message = "Your Seminar Report and training credentials for \"{$request->seminar_title}\" have been reviewed and acknowledged by HR. The training is officially recorded in your employee credentials." .
                (!empty($notes) ? "\n\nHR Remarks: \"{$notes}\"" : "");

            $this->notifyUserOrEmployee($employee, 'seminar_report_completed', $title, $message, route('portal.requests.show', $request));

            return $request->fresh();
        });
    }

    /**
     * Get all HR Officers & System Super-Admins.
     */
    public function getHrOfficers(): \Illuminate\Database\Eloquent\Collection
    {
        $byRelation = User::whereHas('employee', fn($q) => $q->where('approval_role', 'hr_officer'))->get();
        $bySuperAdmin = User::role('admin')->whereDoesntHave('employee')->get();
        $byEmail = User::whereIn('email', Employee::where('approval_role', 'hr_officer')->whereNotNull('email')->pluck('email'))->get();

        return $byRelation->merge($bySuperAdmin)->merge($byEmail)->unique('id');
    }

    /**
     * Get all Executive Office approvers & System Super-Admins.
     */
    public function getExecutiveApprovers(): \Illuminate\Database\Eloquent\Collection
    {
        $byRelation = User::whereHas('employee', fn($q) => $q->whereIn('approval_role', ['executive_office', 'executive_director']))->get();
        $bySuperAdmin = User::role('admin')->whereDoesntHave('employee')->get();
        $byEmail = User::whereIn('email', Employee::whereIn('approval_role', ['executive_office', 'executive_director'])->whereNotNull('email')->pluck('email'))->get();

        return $byRelation->merge($bySuperAdmin)->merge($byEmail)->unique('id');
    }

    /**
     * Get all Executive Director approvers & System Super-Admins.
     */
    public function getExecutiveDirectors(): \Illuminate\Database\Eloquent\Collection
    {
        $byRelation = User::whereHas('employee', fn($q) => $q->where('approval_role', 'executive_director'))->get();
        $bySuperAdmin = User::role('admin')->whereDoesntHave('employee')->get();
        $byEmail = User::whereIn('email', Employee::where('approval_role', 'executive_director')->whereNotNull('email')->pluck('email'))->get();

        return $byRelation->merge($bySuperAdmin)->merge($byEmail)->unique('id');
    }

    /**
     * Get Section Heads for an employee's department.
     */
    public function getSectionHeads(Employee $employee): \Illuminate\Database\Eloquent\Collection
    {
        $byRelation = User::whereHas('employee', fn($q) => $q->where('department_id', $employee->department_id)->where('approval_role', 'section_head'))->get();
        $byEmail = User::whereIn('email', Employee::where('department_id', $employee->department_id)->where('approval_role', 'section_head')->whereNotNull('email')->pluck('email'))->get();

        return $byRelation->merge($byEmail)->unique('id');
    }

    /**
     * Resolve the appropriate target URL for an approver.
     */
    public function resolveRequestUrl(User $user, StaffRequest $request): string
    {
        if ($user->hasRole('admin')) {
            return route('admin.requests.show', $request);
        }
        return route('portal.requests.show', $request);
    }

    /**
     * Notify all HR Officers and Super Admins.
     */
    protected function notifyAdmins(string $type, string $title, string $message, string $link): void
    {
        $recipients = $this->getHrOfficers();
        foreach ($recipients as $admin) {
            $this->notificationService->notifyUser($admin, $type, $title, $message, $link);
        }
    }

    /**
     * Safely notify employee via linked user account or direct email fallback.
     */
    protected function notifyUserOrEmployee(Employee $employee, string $type, string $title, string $message, string $link): void
    {
        if ($employee->user) {
            $this->notificationService->notifyUser($employee->user, $type, $title, $message, $link);
        } elseif (!empty($employee->email)) {
            $tempUser = new User([
                'id' => 0,
                'name' => $employee->name,
                'email' => $employee->email,
            ]);
            $this->notificationService->notifyUser($tempUser, $type, $title, $message, $link);
        }
    }
}
