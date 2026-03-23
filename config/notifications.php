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
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'work_order.assigned_to_department' => [
        'label'             => 'Job Assigned to Department',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'work_order.claimed' => [
        'label'             => 'Job Claimed by Staff',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'work_order.assigned' => [
        'label'             => 'Job Assigned to Staff',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'work_order.released' => [
        'label'             => 'Job Released',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'work_order.status_changed' => [
        'label'             => 'Job Status Changed',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'work_order.deadline_approaching' => [
        'label'             => 'Job Deadline Approaching',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'work_order.budget_alert' => [
        'label'             => 'Job Budget Alert',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],

    // -------------------------------------------------------------------------
    // Tasks
    // -------------------------------------------------------------------------
    'task.assigned' => [
        'label'             => 'Task Assigned to You',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'task.claimed' => [
        'label'             => 'Task Claimed',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'task.claimed_assigned' => [
        'label'             => 'Task Assigned (via Claim)',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'task.released' => [
        'label'             => 'Task Released',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'task.status_changed' => [
        'label'             => 'Task Status Changed',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'task.all_completed' => [
        'label'             => 'All Tasks on Job Completed',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'task.deadline_approaching' => [
        'label'             => 'Task Deadline Approaching',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'task.overdue' => [
        'label'             => 'Task Overdue',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],

    // -------------------------------------------------------------------------
    // Approvals — Payment Requisitions
    // -------------------------------------------------------------------------
    'requisition.submitted' => [
        'label'            => 'Requisition Submitted for Finance Approval',
        'channel_database' => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'requisition.finance_approved' => [
        'label'            => 'Requisition Finance-Approved – Awaiting Admin',
        'channel_database' => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'requisition.approved' => [
        'label'            => 'Requisition Fully Approved',
        'channel_database' => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'requisition.rejected' => [
        'label'            => 'Requisition Rejected',
        'channel_database' => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],

    // -------------------------------------------------------------------------
    // Approvals — Expenses
    // -------------------------------------------------------------------------
    'expense.submitted' => [
        'label'             => 'Expense Submitted for Approval',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'expense.approved' => [
        'label'             => 'Expense Approved',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'expense.rejected' => [
        'label'             => 'Expense Rejected',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],

    // -------------------------------------------------------------------------
    // Approvals — Leave
    // -------------------------------------------------------------------------
    'leave.submitted' => [
        'label'             => 'Leave Request Submitted',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'leave.updated' => [
        'label'             => 'Leave Request Updated',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'leave.approved' => [
        'label'             => 'Leave Request Approved',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],
    'leave.denied' => [
        'label'             => 'Leave Request Denied',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],

    // -------------------------------------------------------------------------
    // Invoices
    // -------------------------------------------------------------------------
    'invoice.overdue' => [
        'label'             => 'Invoice Overdue',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],

    // -------------------------------------------------------------------------
    // Stock / Procurement
    // -------------------------------------------------------------------------
    'stock.low' => [
        'label'             => 'Low Stock Alert',
        'channel_database'  => true,
        'channel_mail'      => false,
        'channel_sms'       => false,
        'channel_whatsapp'  => false,
    ],

];
