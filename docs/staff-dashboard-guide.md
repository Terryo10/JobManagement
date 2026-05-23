# Staff Dashboard Guide

> **URL:** `yoursite.com/staff`
> **Brand:** Household Media — Staff
> **Colour:** Blue

This guide walks you through every part of the Staff dashboard. It is designed for team members who work on jobs, claim tasks, submit expenses, and manage their day-to-day work.

---

## Logging In

1. Go to `yoursite.com/staff/login`.
2. Enter your **email** and **password**.
3. Click **Sign in**.

After you sign in you land on the Staff Dashboard home page.

---

## Top Bar

| Item | What It Does |
|---|---|
| **Bell icon** (top-right) | Shows your notifications. The system checks every 5 seconds. You get alerts when tasks are assigned to you, jobs are updated, requisitions are approved/rejected, and more. |
| **User menu** (top-right) | Click your name to see **Notification Preferences** (pick which alerts you get) and **Log Out**. |

---

## Sidebar Navigation

The sidebar can be collapsed by clicking the hamburger icon. Here is everything you will find:

### Announcements

- **Announcements** — See company-wide announcements posted by admins. Pinned ones always appear first. You can read them, leave comments, and reply to other comments. Use the rich text editor for formatting.

### My Work

| Menu Item | What It Does |
|---|---|
| **Tasks** | Your main task list. Claim tasks, update progress, and complete work. Full detail below. |
| **My Time Logs** | View and create time entries against your tasks. Each entry has a task, hours worked, date, and notes. |
| **Material Usage** | Log what materials you used on a job. Select the work order, material, and quantity. |
| **Leave Requests** | Submit leave/unavailability requests. Choose dates, reason (Annual Leave, Sick Leave, Field Deployment, Training, Other), and add notes. Your request goes to admin for approval. You will get a notification when it is approved or denied. |
| **Safety Checklists** | Fill out safety compliance records for your jobs. |

### Work Orders

| Menu Item | What It Does |
|---|---|
| **My Jobs** | Shows only work orders that **you have claimed**. This is your personal job list. You can view details, update status, and manage your claimed work. |
| **All Work Orders** | Shows all work orders in the system (read-only view). This lets you see what other jobs exist and what is available. |

### Finance

| Menu Item | What It Does |
|---|---|
| **Requisitions** | Submit requests for money. You fill out what you need the money for, the amount, link it to a work order, add attachments, and submit. You can track the status of your requests here. Full detail below. |
| **My Expenses** | Log expenses you have spent on a work order. Pick the work order, category (Labour, Transport, Materials, Equipment, Other), amount, date, and description. You can see if your expense was approved or rejected. |

### Resources

| Menu Item | What It Does |
|---|---|
| **Equipment** | View available equipment. You can see equipment details, status, and which jobs they are assigned to. |
| **Clients** | Look up client information like company name, contact person, email, and phone number. This is a read-only reference. |

---

## Dashboard Home Page Widgets

When you log in, you see these widgets on your dashboard:

### Announcements Widget
Shows the latest company announcement. Click to view the full announcements list.

### Overview Stats (Top Row)
Five stat cards giving you a quick picture of your workload:

| Card | What it Shows |
|---|---|
| **Available Tasks** | Number of tasks waiting to be claimed by anyone. Click to jump to the available tasks list. |
| **My Active Tasks** | Number of tasks you are currently working on (status = In Progress). Click to see just your tasks. |
| **Overdue Tasks** | Number of your tasks that have passed their deadline. If all clear, you see "All clear!" in green. If not, the card turns red. |
| **My Pending Requisitions** | Number of your money requests that are still waiting for approval. Click to go to your requisitions. |
| **Approved This Month** | How many of your requisitions were approved this month. |

### Calendar (Full Width)
A weekly calendar view showing:
- **Your claimed tasks** — Colour-coded by priority (green = low, blue = medium, amber = high, red = urgent). Shows the task title and linked work order ref number.
- **Your claimed work orders** — Colour-coded by status (grey = pending, indigo = in progress, amber = on hold).
- **Your leave/unavailability** — Shown as light red background blocks with a label like "Leave: Annual Leave".

You can switch between **Month**, **Week**, and **List** views using the buttons at the top. The week view also shows a "now" indicator line.

---

## Tasks — Detailed Guide

Go to **My Work → Tasks** in the sidebar. This is where you manage your day-to-day work.

### Task Table Columns
| Column | What it Shows |
|---|---|
| **Title** | The name of the task |
| **Job Card** | The work order ref number this task belongs to |
| **Claimed By** | Who has claimed this task (green badge if claimed, grey if unclaimed) |
| **Status** | Pending, In Progress, Completed, Blocked, or Cancelled |
| **Priority** | Low (grey), Normal (blue), High (orange), Urgent (red) |
| **Completion %** | How far along you are (0–100%) |
| **Deadline** | When the task is due. Shows in red if overdue. |

### Filters
Click the funnel icon to filter the list:
- **Status** — Pending, In Progress, Completed, Blocked, Cancelled
- **Priority** — Low, Normal, High, Urgent
- **Queue** — Available (unclaimed tasks), Mine (tasks you claimed)

### How to Claim a Task
1. Find a task that is not claimed (the "Claimed By" column says "Unclaimed").
2. Click on the task to open it.
3. Look for the **Claim** action button.
4. Click it. The task is now yours, and the status changes to In Progress.

### How to Update a Task
1. Open a task you have claimed.
2. Click **Edit**.
3. Update the **completion percentage** as you make progress.
4. Change the **status** when appropriate:
   - **In Progress** — You are working on it
   - **Blocked** — Something is stopping you
   - **Completed** — You are done (you cannot update it after this)
5. Save.

### How to Release a Task
If you can no longer work on a task:
1. Open the task.
2. Click the **Release** action.
3. The task goes back to the unclaimed queue for someone else to pick up.

### Relation Tabs (On View/Edit Pages)
Below the main task details, you will find:
- **Subtasks** — Break the task into smaller pieces.
- **Comments** — Leave notes and discuss with your team.
- **Time Logs** — Log your hours against this specific task.
- **Documents** — Upload files related to this task.

---

## Work Orders — For Staff

### My Jobs
Under **Work Orders → My Jobs**, you see only the work orders you have personally claimed. This is your focused list.

The form is the same tabbed format as the admin, with 6 tabs:
1. General Information
2. Design Job Card
3. Procurement
4. Production
5. Delivery & Installation
6. Assessment / Report

### All Work Orders
Under **Work Orders → All Work Orders**, you see every work order in the system. This is a read-only overview so you can see what is happening across the company.

### Claiming a Work Order
From the work order table or view page, click the **Claim Job** action. The job is now assigned to you, and the status changes to In Progress.

### Releasing a Work Order
If you need to step away from a job, click **Release Job**. It goes back to the unclaimed queue.

---

## Requisitions — Detailed Guide

### What is a Requisition?
A requisition is a formal request for money. You fill out what you need, how much, and why. It then goes through an approval process:

```
You submit → Pending Finance Approval → Finance Approved → Admin Final Approval → Approved
                                      ↘ Rejected (at any stage)
```

### How to Submit a Requisition
1. Go to **Finance → Requisitions**.
2. Click **New Requisition**.
3. Fill in:
   - **What do you need the money for?** — A short title.
   - **GL Account Code** and **GL Account Name** — The accounting code (ask finance if unsure).
   - **Amount Requested** — How much you need.
   - **Reference Number** — Auto-generated (REQ-2026-0001).
   - **Requested By** — Defaults to you.
   - **Link to Work Order** — Connect it to a job if it relates to one.
   - **Attachments** — Upload quotes, receipts, or other supporting documents.
   - **Additional Notes** — Explain anything that needs context.
4. Set the status to **Pending Finance Approval** and save.

### Tracking Your Requisitions
Back on the list page, you can see the status of all your submissions:
- **Draft** — You saved it but have not submitted yet.
- **Awaiting Finance** — The accountant is reviewing it.
- **Finance Approved** — Finance approved, waiting for admin.
- **Approved** — Fully approved. You can get the money.
- **Rejected** — It was denied. Check with admin or finance for the reason.

### Downloading the PDF
Click the **Download PDF** icon on any requisition to get a formatted document with all details and signatures.

---

## Expenses

### How to Submit an Expense
1. Go to **Finance → My Expenses**.
2. Click **New Expense**.
3. Fill in:
   - **Work Order** — Which job this expense is for.
   - **Submitted By** — Defaults to you.
   - **Category** — Labour, Transport, Materials, Equipment, or Other.
   - **Amount** — How much you spent.
   - **Expense Date** — When you spent it.
   - **Currency** — USD or ZWL.
   - **Description** — What the expense was for.
4. Save.

### Expense Status
After submitting, the admin will review your expense:
- **Pending Approval** (yellow badge) — Not yet reviewed.
- **Approved** (green badge) — Accepted.
- **Rejected** (red badge) — Denied. The rejection reason will be shown.

---

## Leave Requests

### How to Request Leave
1. Go to **My Work → Leave Requests**.
2. Click **Create**.
3. Fill in:
   - **Unavailable From** — Start date.
   - **Unavailable To** — End date.
   - **Reason** — Annual Leave, Sick Leave, Field Deployment, Training, or Other.
   - **Notes** — Any additional info.
4. Save.

### Tracking Leave Status
- **Pending** (yellow) — Admin has not reviewed yet.
- **Approved** (green) — Your leave is confirmed. It will appear on your calendar as a red background block.
- **Denied** (red) — Your request was not approved. Check the admin note for the reason.

---

## Change Password

Go to the sidebar and find **Change Password**. Enter your current password, type a new one, confirm it, and click Save.

---

## Notification Preferences

Click your name in the top-right corner and select **Notification Preferences**. Choose which events send you alerts and through which channels (Email, WhatsApp, SMS, Dashboard).

---

## Tips

- **Claim tasks quickly** — Tasks are first-come, first-served. If you see a task you can do, claim it before someone else does.
- **Keep your completion % updated** — Admin can see how far along you are. Update it as you make progress.
- **Check the calendar daily** — It shows all your deadlines, tasks, and leave in one place.
- **Red text on deadlines** means the deadline has passed — take action.
- **Submit expenses as you go** — Do not wait until the end of the job to submit expenses. It is easier to get approval while the job is fresh.
- **The bell icon** shows new notifications. Read them to stay up to date on approvals, assignments, and announcements.
