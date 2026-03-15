# Job Management System – Development Tasks Prompt for Claude Code

Use this prompt when handing off to Claude Code. It describes the existing Laravel/Filament system and the remaining tasks to implement. Completed behaviour is listed only for context; **completed tasks must not appear in the main task list** and should be visible only on a separate “Completed / Changelog” page or section.

---

## System context (brief)

- **Stack:** Laravel, Filament (Admin, Staff, Accountant, Client, Marketing panels), Spatie permissions, Filament Full Calendar.
- **Core concepts:** **Work orders** (DB: `work_orders`, UI: “Job Cards”) with reference `WO-YYYY-NNNN`; **Tasks** belong to work orders; staff **claim** or get **assigned** to tasks/jobs; managers and admins reassign and oversee.
- **Key paths:** `app/Models/` (Task, WorkOrder, User, Client, Expense, Equipment, ReportLog, StaffAvailability, etc.), `app/Observers/` (TaskObserver, WorkOrderObserver), `app/Filament/Admin/`, `app/Filament/Staff/`, `app/Services/ReportService.php`, `app/Notifications/DatabaseAlert.php`.

---

## Tasks to implement (only these in the main backlog)

### 1. Completed tasks visibility
- **Requirement:** Completed tasks must not appear in the main task list. They must be visible only on a **separate page** (e.g. “Completed Tasks” or “Task History”).
- **Implementation:** In Admin and Staff Task resources, exclude `status = 'completed'` from the default table query (and from “My Tasks” / “Available to Claim” flows). Add a dedicated Filament page or resource view “Completed Tasks” that shows only tasks with `status = 'completed'` (and optionally filters by work order, date range, user). Ensure widgets (e.g. overdue tasks) continue to exclude completed where appropriate.

### 2. Notify manager when staff drops or releases a task
- **Requirement:** When staff drops or releases a task, the manager must be notified.
- **Implementation:** In `Task::release()` (and any place that clears `claimed_by`/`assigned_to`), after updating the task, send a notification (e.g. `DatabaseAlert`) to managers (and optionally super_admins). Use the same pattern as in `TaskObserver` (notify `manager` role). Consider notifying the work order’s assigned department head if applicable. Add a clear message like “Task [title] released by [user]”.

### 3. Calendar feature
- **Requirement:** Calendar feature is required and must be kept/working.
- **Current state:** Admin and Staff panels already use `saade/filament-fullcalendar` (AdminCalendarWidget, StaffCalendarWidget). Ensure the calendar remains functional, events (tasks/work orders by deadline) and staff unavailability are shown, and any new task/work order behaviour (e.g. completed tasks hidden from main list) does not break calendar event sourcing.

### 4. Reassign task → reflect and notify staff
- **Requirement:** When a manager reassigns a task to another staff member, the assignment must reflect immediately and the new staff member must receive a notification.
- **Current state:** TaskObserver already notifies the new assignee on `assigned_to` change (“Task Assigned to You”). Ensure reassign actions (Admin “Reassign”) update `assigned_to` (and optionally `claimed_by`/`claimed_at`) and that the observer runs so the new assignee gets the notification. If there are edge cases (e.g. bulk reassign), add tests or manual checks.

### 5. Manager assigns task → admin notification wording
- **Requirement:** When a manager assigns a task (sets assignee directly, without staff “claiming”), admin notifications must say **“Assigned to [name]”** (or similar), not “Job claimed” / “Task claimed”.
- **Implementation:** In `TaskObserver` and `WorkOrderObserver`, distinguish:
  - **Claimed:** staff member claims an unassigned task/job → keep existing “Task Claimed” / “Job Claimed” message for admins.
  - **Assigned by manager/admin:** `assigned_to` / `claimed_by` set on create or by manager action without a “self-claim” → notify admins with “Task assigned to [user]” / “Job assigned to [user]” instead of “claimed”. Use the same notification channel (e.g. `DatabaseAlert`) and recipient set (e.g. super_admin).

### 6. Accounts can create expense
- **Requirement:** Accounts (accountant role) must be able to create expenses. Manager must be notified when an expense is created (optional from your list; can add if desired).
- **Current state:** Accountant already has `ExpenseResource` with `canCreate(): true`. If “accounts” means a broader set of users (e.g. staff), add an Expense resource or shared form for those roles and ensure `submitted_by` is set. If manager notification on new expense is required, add an `ExpenseObserver` and notify managers (e.g. `manager` role) on create.

### 7. Notify manager about staff availability
- **Requirement:** When staff submit or update their availability (e.g. leave, field deployment), the manager must be notified.
- **Implementation:** Create or use an observer for `StaffAvailability`. On `created` or `updated`, send a notification (e.g. `DatabaseAlert`) to users with `manager` (and optionally `super_admin`) role. Include summary: who is unavailable, from–to, reason. Staff must be able to create/edit their own availability (e.g. Staff panel resource for `StaffAvailability` with scope to own records); if that does not exist, add it.

### 8. Job summary clickable and PDF download
- **Requirement:** Job summary must be clickable (e.g. row opens work order or detail) and must have a “Download PDF” button.
- **Implementation:** In `app/Filament/Admin/Pages/Reports/JobSummaryReport.php`: (1) Add table row URL or action to open the work order (e.g. `->recordUrl(fn ($record) => WorkOrderResource::getUrl('view', ['record' => $record]))` or View action). (2) Add a header or table action “Download PDF” that generates a PDF of the current job summary (filtered results). Use the existing ReportService job summary data and a PDF library (e.g. Barryvdh DomPDF) to produce a simple report PDF; store or stream the file.

### 9. Fix TypeError on Staff Performance Report page
- **Requirement:** Resolve the TypeError at `/admin/staff-performance-report`.
- **Implementation:** The page uses `Task::query()->select('assigned_to')->selectRaw('MAX(id) as id')->...->groupBy('assigned_to')`. Rows can have `assigned_to` pointing to a deleted or missing user, so `$record->assignedTo` can be null. Make columns null-safe: e.g. `assignedTo?.name` in Blade or in the table use `->formatStateUsing` / default to `'—'` when the relation is null. Fix any similar issue for computed columns (e.g. efficiency, completion_rate) so that non-numeric states do not cause type errors.

### 10. Create Report Log – detect logged-in user
- **Requirement:** When creating a Report Log, automatically set the “generated by” (or equivalent) to the currently logged-in user to make creation simpler.
- **Implementation:** In `app/Filament/Admin/Resources/ReportLogResource/Pages/CreateReportLog.php`, in `mutateFormDataBeforeCreate()` (or form default), set `generated_by` to `auth()->id()`. In the form, make `generated_by` hidden or read-only and pre-filled with the current user so the creator does not have to select themselves.

### 11. New billboard under job cards
- **Requirement:** Creating a new billboard must fall under “Job Cards” (or the same area as work orders).
- **Implementation:** In the Admin panel, move `BillboardResource` into the same navigation group as Work Order (e.g. “Operations” or “Job Cards”). If the design is that a billboard is created from a job card, add a relation from work orders to billboards (e.g. `work_order_id` on `billboards` or a pivot) and a relation manager or action “Create Billboard” from the work order. Clarify with product: “fall under job cards” as in navigation grouping only, or as in “billboard belongs to a job card”.

### 12. Equipment not limited to admin only
- **Requirement:** Equipment should not be limited to admin only; other roles (e.g. staff, manager) should have appropriate access.
- **Implementation:** Add an `EquipmentResource` (or equivalent) to the Staff (and optionally Manager) panel with list/view and, if required, create/edit with appropriate policies. Restrict by role or permission (e.g. staff can list and view; manager can create/edit). Ensure `app/Models/Equipment.php` and migrations are unchanged unless a `created_by` or similar audit field is needed.

### 13. All users can create clients; show “Created by”
- **Requirement:** All (relevant) users can create new clients. Client must show “Created by [user]”.
- **Implementation:** Provide a Client resource or create form in Staff and other panels (currently only Admin and Marketing have client creation). When creating a client, set `created_by` to `auth()->id()` in the create page or an observer. In Admin (and any) Client list/detail view, add a “Created by” column/field showing `client.createdBy.name` (or “—” if null). Ensure `Client` model has `createdBy()` relationship and `created_by` is fillable.

### 14. Job card changes to match “work order” example
- **Requirement:** Update the job card to match the provided “work order” example (layout, fields, wording).
- **Implementation:** Obtain the exact work order example (mock-up or spec). Update `app/Filament/Admin/Resources/WorkOrderResource.php` (form schema, tabs, labels) and any view/infolist so that the job card matches the example. Use “Work Order” in the UI where the example uses it; keep or add fields (e.g. reference number, client, category, status, budget, dates, tasks summary) as in the example. If the example is in the repo, reference its path in this task.

### 15. Simplify Admin dashboard Operations section
- **Requirement:** The Operations section in the admin dashboard is “all over the place”; simplify it so that items that belong under “Job Cards” (work orders) are grouped there and the structure is clear.
- **Implementation:** In `app/Providers/Filament/AdminPanelProvider.php`, reorganise navigation: group Work Orders (Job Cards), Tasks, and any related resources (e.g. billboards if under job cards) under a single “Operations” or “Job Cards” group with a clear order. Consider sub-groups or labels (e.g. “Job Cards”, “Tasks”, “Related”) so managers see a simple hierarchy. Move or remove duplicate entries; ensure one clear place for each concept.

### 16. Reports: AI-generated, customisable, timeline, MD → PDF, editable by AI
- **Requirement:** Reports must be AI-generated and fully customisable. User can select which jobs (work orders) to include and select one or many jobs from a timeline of their choice. Generated report is in Markdown (MD), produced by an AI service (e.g. Gemini). Report can be converted to PDF, fully editable, and the user can send it back to the AI to make changes according to their instructions.
- **Implementation:**  
  - **AI service:** Introduce an AI report service (e.g. `app/Services/AiReportService.php` or `GeminiReportService`) that calls Gemini (or chosen provider) with selected work order IDs, date range, and optional user instructions; returns Markdown.  
  - **Report generation UI:** Add a report page (e.g. under Reports) where the user selects jobs (e.g. from a timeline or multi-select filtered by date range), optionally customises options (sections, metrics), and clicks “Generate report”. Call the AI service and show the result in an MD viewer/editor.  
  - **Format:** Store or display the report as Markdown. Provide “Download as PDF” (convert MD to PDF, e.g. via a library that renders MD then PDF).  
  - **Edit and re-run AI:** Allow the user to edit the MD content and/or send feedback (e.g. “add a section for expenses”, “shorten the summary”). Send the current MD + instructions back to the AI service and replace/update the report content.  
  - **Report logs:** Optionally create a `ReportLog` (or similar) entry for each AI-generated report with `generated_by` = current user, and link to the stored file or content.

---

## Completed / changelog (reference only – do not put these in main task list)

- Task and work order claiming and assignment (claim, release, reassign) with notifications for assignee and admins.
- Calendar widgets (Admin, Staff, etc.) with tasks, work orders, and staff unavailability.
- Job cards implemented as Work Orders (`work_orders`); reference number WO-YYYY-NNNN.
- Basic reports: Job Summary, Staff Performance, Revenue, Material usage; ReportLog and ScheduledReport.
- Expenses: Admin and Accountant can create; approval workflow in Accountant panel.
- Clients: Admin and Marketing can create; Marketing sets `created_by`.
- Equipment and Billboards in Admin; Staff availability in Admin (HR).
- Notifications: task assigned, task claimed, job claimed, status changes, budget alerts, deadline reminders (scheduled).

---

## Technical hints

- **Notifications:** Use `App\Notifications\DatabaseAlert` and `User::role('manager')->get()` (or notification rules) for manager/admin recipients; keep messages short and actionable.
- **Observers:** Prefer observers for “notify when X happens” (e.g. Task release, StaffAvailability create, Expense create) so logic stays in one place.
- **Staff task scope:** Staff Task resource uses `getEloquentQuery()` and filters “Available to Claim” vs “My Tasks”; ensure completed tasks are excluded from default list and only shown on the dedicated completed-tasks page.
- **Null safety:** In Filament tables, use `->default('—')`, `?->`, or `formatStateUsing` for relation columns that may be null (e.g. deleted users).
- **PDF:** Project already uses Barryvdh DomPDF for invoices; reuse or extend for job summary and AI report PDF export.
- **AI:** Store API keys in `.env` (e.g. `GEMINI_API_KEY`); use HTTP client or official SDK for Gemini; keep prompts and response handling in the AiReportService.

---

## Out of scope for this prompt

- CRM, leads, proposals, purchase orders, and other existing features not mentioned above remain as-is unless a task explicitly changes them.
- Front-end stack beyond Filament (e.g. separate SPA) is out of scope unless the report editor is a separate app.

---

End of prompt. Give this document to Claude Code and ask it to implement the tasks in order, or one at a time, and to move completed items to the “Completed / changelog” section and remove them from the main task list.
