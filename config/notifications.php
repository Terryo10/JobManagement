<?php

/**
 * Per-event-type notification defaults.
 *
 * These are used when a notification_preferences row does not yet exist for a user.
 * Keys match the NotificationEvent::$type values used throughout observers/commands.
 *
 * Channel defaults:
 *   database  — always on (in-app bell)
 *   mail      — on for most events
 *   sms       — only for high-urgency, time-sensitive events
 *   whatsapp  — reserved for client-facing / critical alerts (Phase 3)
 */
return [

    // -------------------------------------------------------------------------
    // Work Orders
    // -------------------------------------------------------------------------
    'work_order.created' => [
        'label'             => 'New Job Card Created',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'work_order.assigned_to_department' => [
        'label'             => 'Job Assigned to Department',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'work_order.claimed' => [
        'label'             => 'Job Claimed by Staff',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'work_order.assigned' => [
        'label'             => 'Job Assigned to Staff',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'work_order.released' => [
        'label'             => 'Job Released',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'work_order.status_changed' => [
        'label'             => 'Job Status Changed',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'work_order.deadline_approaching' => [
        'label'             => 'Job Deadline Approaching',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'work_order.budget_alert' => [
        'label'             => 'Job Budget Alert',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],

    // -------------------------------------------------------------------------
    // Tasks
    // -------------------------------------------------------------------------
    'task.assigned' => [
        'label'             => 'Task Assigned to You',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'task.claimed' => [
        'label'             => 'Task Claimed',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'task.claimed_assigned' => [
        'label'             => 'Task Assigned (via Claim)',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'task.released' => [
        'label'             => 'Task Released',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'task.status_changed' => [
        'label'             => 'Task Status Changed',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'task.all_completed' => [
        'label'             => 'All Tasks on Job Completed',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'task.deadline_approaching' => [
        'label'             => 'Task Deadline Approaching',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'task.overdue' => [
        'label'             => 'Task Overdue',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],

    // -------------------------------------------------------------------------
    // Approvals — Payment Requisitions
    // -------------------------------------------------------------------------
    'requisition.submitted' => [
        'label'            => 'Requisition Submitted for Finance Approval',
        'channel_database' => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'requisition.finance_approved' => [
        'label'            => 'Requisition Finance-Approved – Awaiting Admin',
        'channel_database' => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'requisition.approved' => [
        'label'            => 'Requisition Fully Approved',
        'channel_database' => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'requisition.rejected' => [
        'label'            => 'Requisition Rejected',
        'channel_database' => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],

    // -------------------------------------------------------------------------
    // Approvals — Expenses
    // -------------------------------------------------------------------------
    'expense.submitted' => [
        'label'             => 'Expense Submitted for Approval',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'expense.approved' => [
        'label'             => 'Expense Approved',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'expense.rejected' => [
        'label'             => 'Expense Rejected',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],

    // -------------------------------------------------------------------------
    // Approvals — Leave
    // -------------------------------------------------------------------------
    'leave.submitted' => [
        'label'             => 'Leave Request Submitted',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'leave.updated' => [
        'label'             => 'Leave Request Updated',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'leave.approved' => [
        'label'             => 'Leave Request Approved',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],
    'leave.denied' => [
        'label'             => 'Leave Request Denied',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],

    // -------------------------------------------------------------------------
    // Invoices
    // -------------------------------------------------------------------------
    'invoice.overdue' => [
        'label'             => 'Invoice Overdue',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],

    // -------------------------------------------------------------------------
    // Admin Tasks
    // -------------------------------------------------------------------------
    'admin_task.assigned' => [
        'label'            => 'Admin Task Assigned to You',
        'channel_database' => true,
        'channel_mail'     => false,
        'channel_sms'      => false,
        'channel_whatsapp' => false,
    ],
    'admin_task.status_changed' => [
        'label'            => 'Admin Task Status Changed',
        'channel_database' => true,
        'channel_mail'     => false,
        'channel_sms'      => false,
        'channel_whatsapp' => false,
    ],
    'admin_task.completed' => [
        'label'            => 'Admin Task Completed',
        'channel_database' => true,
        'channel_mail'     => false,
        'channel_sms'      => false,
        'channel_whatsapp' => false,
    ],
    'admin_task.urgent' => [
        'label'            => 'Admin Task Escalated to Urgent',
        'channel_database' => true,
        'channel_mail'     => false,
        'channel_sms'      => false,
        'channel_whatsapp' => false,
    ],
    'admin_task.overdue' => [
        'label'            => 'Admin Task Overdue',
        'channel_database' => true,
        'channel_mail'     => false,
        'channel_sms'      => false,
        'channel_whatsapp' => false,
    ],

    // -------------------------------------------------------------------------
    // Stock / Procurement
    // -------------------------------------------------------------------------
    'stock.low' => [
        'label'             => 'Low Stock Alert',
        'channel_database'  => true,
        'channel_mail'      => true,
        'channel_sms'       => false,
        'channel_whatsapp'  => true,
    ],

];
