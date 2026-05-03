<?php

namespace Database\Seeders\Workflows;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use App\Models\Workflows\WorkflowDefinition;
use App\Models\Workflows\WorkflowStep;
use Illuminate\Database\Seeder;

class DefaultWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->first();
        $principalRole = Role::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'principal')->first();
        $accountantRole = Role::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'accountant')->first();

        $definitions = [
            [
                'name' => 'Admission Approval Workflow',
                'code' => 'ADMISSION-APPROVAL',
                'module' => 'admissions',
                'description' => 'Reviews and approves student admission submissions.',
                'trigger_type' => 'event',
                'trigger_event' => 'student.admission.submitted',
                'steps' => [
                    [
                        'step_name' => 'Principal Admission Review',
                        'step_type' => 'approval',
                        'sequence' => 1,
                        'assigned_role_id' => $principalRole?->id,
                        'config' => [
                            'message' => 'Please review the submitted admission application.',
                        ],
                    ],
                    [
                        'step_name' => 'Admission Status Update',
                        'step_type' => 'action',
                        'sequence' => 2,
                        'config' => [
                            'action' => [
                                'type' => 'status_update',
                                'status_field' => 'application_status',
                                'status_value' => 'approved',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Leave Approval Workflow',
                'code' => 'LEAVE-APPROVAL',
                'module' => 'hr',
                'description' => 'Approves submitted staff leave applications.',
                'trigger_type' => 'event',
                'trigger_event' => 'leave.application.submitted',
                'steps' => [
                    [
                        'step_name' => 'Leave Review',
                        'step_type' => 'approval',
                        'sequence' => 1,
                        'assigned_role_id' => $principalRole?->id,
                        'config' => [
                            'message' => 'Please review the submitted leave request.',
                        ],
                    ],
                    [
                        'step_name' => 'Leave Notification',
                        'step_type' => 'notification',
                        'sequence' => 2,
                        'config' => [
                            'channel' => 'in_app',
                            'recipient_type' => 'user',
                            'message' => 'Leave workflow has been approved.',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Fee Concession Approval Workflow',
                'code' => 'FEE-CONCESSION-APPROVAL',
                'module' => 'fees',
                'description' => 'Reviews fee concession requests before final approval.',
                'trigger_type' => 'manual',
                'trigger_event' => null,
                'steps' => [
                    [
                        'step_name' => 'Accounts Review',
                        'step_type' => 'approval',
                        'sequence' => 1,
                        'assigned_role_id' => $accountantRole?->id,
                        'config' => [
                            'message' => 'Validate concession eligibility and supporting records.',
                        ],
                    ],
                    [
                        'step_name' => 'Principal Approval',
                        'step_type' => 'approval',
                        'sequence' => 2,
                        'assigned_role_id' => $principalRole?->id,
                        'config' => [
                            'message' => 'Provide final approval for the concession request.',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($definitions as $definitionData) {
            $definition = WorkflowDefinition::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => $definitionData['code'],
                ],
                [
                    'name' => $definitionData['name'],
                    'module' => $definitionData['module'],
                    'description' => $definitionData['description'],
                    'trigger_type' => $definitionData['trigger_type'],
                    'trigger_event' => $definitionData['trigger_event'],
                    'status' => 'active',
                    'created_by' => $admin?->id,
                ]
            );

            foreach ($definitionData['steps'] as $stepData) {
                WorkflowStep::withoutGlobalScopes()->updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'workflow_definition_id' => $definition->id,
                        'sequence' => $stepData['sequence'],
                    ],
                    [
                        'step_name' => $stepData['step_name'],
                        'step_type' => $stepData['step_type'],
                        'config' => $stepData['config'] ?? null,
                        'assigned_role_id' => $stepData['assigned_role_id'] ?? null,
                        'assigned_user_id' => $stepData['assigned_user_id'] ?? null,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
