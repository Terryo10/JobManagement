# Admin Dashboard Guide

> **URL:** `yoursite.com/admin`
> **Brand:** Household Media
> **Colour:** Amber / Gold

This guide walks you through every part of the Admin dashboard. Use it to find what you need quickly.

---

## Logging In

1. Go to `yoursite.com/admin/login`.
2. Enter your **email** and **password**.
3. Click **Sign in**.

After you sign in you land on the main Dashboard page.

---

## Top Bar

The top bar has three things you should know about:

| Item | What It Does |
|---|---|
| **Search bar** | Press `Ctrl+K` (or `Cmd+K` on Mac) to open global search. Type any reference number, client name, or task title and jump to it instantly. |
| **Bell icon** (top-right) | Opens your notification tray. The system checks for new notifications every 5 seconds. You will see alerts for overdue tasks, requisition approvals, invoice updates, and more. |
| **User menu** (top-right) | Click your name to see options: **Notification Preferences** (choose which alerts you get via email/WhatsApp/SMS) and **Log Out**. |

---

## Sidebar Navigation

The sidebar on the left is your main menu. You can collapse it by clicking the hamburger icon at the top. Below is every group and item you will find.

### Announcements

- **Announcements** — View, create, edit, and delete company-wide announcements. Announcements show up as cards in a grid layout. You can **pin** important ones so they always appear first. Each announcement supports rich text (bold, italic, lists, links, headings). Staff across all dashboards can see them and leave comments.

### Operations

| Menu Item | What It Does |
|---|---|
| **Work Orders** | The heart of the system. Each work order is a "job card" that tracks a project from start to finish. More detail below. |
| **Tasks** | Individual tasks linked to work orders. You can view, create, assign, reassign, and unassign them. |
| **Equipment** | Track equipment used on jobs. |
| **Billboards** | Manage billboard assets. |
| **Task Time Logs** | See how much time staff have logged against tasks. |
| **Task Comments** | View all comments left on tasks. |
| **Work Order Notes** | View notes attached to work orders. |
| **Work Order Materials** | See what materials were used on which jobs. |
| **Safety Compliance Records** | Health and safety checklists for jobs. |
| **Deadline Escalations** | View auto-generated escalation records when deadlines are missed. |
| **Personal Files** | Shared documents and uploads attached to the operations area. |

### CRM

| Menu Item | What It Does |
|---|---|
| **Clients** | Add and manage client companies. Each client record has a company name, contact person, email, phone, address, and notes. From a client's detail page you can see their **Leads**, **Work Orders**, **Invoices**, and **Documents** in tabs below. |
| **Leads** | Track potential customers. Each lead has a contact name, company, email, phone, source, status (New / In Progress / Converted / Lost), assigned staff member, follow-up date, and notes. |
| **Lead Communications** | Log calls, emails, and meetings with leads. |

### Warehouse

| Menu Item | What It Does |
|---|---|
| **Materials** | Add and manage materials. Each material has a name, unit, minimum stock level, and active toggle. |
| **Stock Levels** | View current stock quantities for each material. |
| **Suppliers** | Manage your list of suppliers. |

### Finance

| Menu Item | What It Does |
|---|---|
| **Invoices** | Create, edit, and manage invoices. Full detail below. |
| **Quotations** | Create quotations tied to clients and work orders. Can be converted into invoices with one click. |
| **Requisitions** | Staff members submit money requests here. In the Admin view you see all requests and can give **final approval** (with a digital signature) or **reject** them. |
| **Expenses** | Review expenses submitted by staff against work orders. You can **approve** or **reject** each one. Rejected ones require a reason. |
| **Rate Cards** | Set up service rates (type, category, rate, unit). These are used when filling out quotation and invoice line items. |

### Administration

| Menu Item | What It Does |
|---|---|
| **Admin Tasks** | Personal admin-only tasks (not tied to work orders). Set a category (general, finance, hr, procurement, marketing, compliance, it), priority, assignee, due date, and notes. You can mark them complete or reassign them. An overdue indicator shows automatically. |

### HR

| Menu Item | What It Does |
|---|---|
| **Users** | Add, edit, and deactivate staff accounts. Set their name, email, phone (with country code selector), department, roles, and password. You can also **Reset Password** for any user from the table. |
| **Departments** | Create departments (e.g., Media, Civil Works). |
| **Notification Rules** | Set up automatic alerts like deadline reminders (e.g., 3 days before), overdue nudges, budget threshold warnings, low stock alerts, and invoice overdue flags. Each rule has a type, threshold value, trigger days, target role, and active toggle. |
| **User Skills** | Track what skills each staff member has. |
| **Leave Requests** | Staff submit leave/unavailability requests. You can **Approve** (with an optional note) or **Deny** (with a reason). Types: Annual Leave, Sick Leave, Field Deployment, Training, Other. Approved leave shows on the calendar. |

### Reports

| Menu Item | What It Does |
|---|---|
| **AI Report** | An AI-powered report page. Pick a topic and the AI generates a summary of your data. You can then ask follow-up questions in a chat. |
| **AI Invoice Report** | An AI-powered deep dive into your invoicing data. |
| **Job Summary Report** | A filterable report on all work orders by status, category, and date range. |
| **Staff Performance Report** | See task completion rates and time logged per staff member. |
| **Report Logs** | History of all reports that were generated. |

### Messaging

| Menu Item | What It Does |
|---|---|
| **Compose Message** | Send messages to one or more staff members via **Email**, **WhatsApp**, or **SMS**. For WhatsApp you choose a pre-approved template and fill in the variables. For email you write a subject and body. You can also include a link button. A **Message History** button in the top-right shows all previously sent messages. |

### System

| Menu Item | What It Does |
|---|---|
| **Activity Logs** | See a full history of who did what in the system (creates, updates, deletes). |
| **Deletion Requests** | When staff request to delete a record, it comes here for your review. You see the record type, the record name, who requested it, and why. You can **Approve** (permanently deletes the record) or **Reject** (keeps the record). A red badge shows the count of pending requests. |

---

## Dashboard Home Page Widgets

When you first log in you see the Dashboard home page. It has several widgets that give you a quick overview of everything:

### AI Assistant (Top-Left)
A small widget that lets you pick a topic — Work Orders, Finance, Staff, CRM & Leads, Inventory, or Admin Tasks — and get an AI-generated summary. You can then chat with the AI to ask questions about that topic.

### Announcements (Top)
Shows the latest pinned or recent announcement. Links to the full announcements page.

### Executive Summary (Stats Row)
Four stat cards across the top:

| Card | What it Shows |
|---|---|
| **Awaiting Your Approval** | Number of requisitions that have been approved by finance and now need your final sign-off. Click it to jump to those requisitions. |
| **Urgent Attention** | Number of urgent or overdue work orders. Also shows how many tasks are overdue. |
| **Active Jobs** | Total active work orders (pending, in progress, on hold). Shows how many are currently in progress. Includes a mini chart. |
| **Outstanding Balance** | Total dollar value of all unpaid invoices. Click it to go to the invoices page. |

### Priority Tasks Table
Shows up to 10 tasks that are either overdue, urgent, or high priority. Columns: title, job card ref, assigned person, status, priority, deadline, and completion %. Click any row to open the task.

### Recent Leads
Shows the 5 most recent leads that are New or In Progress. Columns: contact name, company, status, follow-up date. Overdue follow-up dates show in red.

### Active Jobs Table
Shows the top 10 active work orders sorted by priority and deadline. Columns: ref #, title, client, claimed by, category, status, priority, deadline. Overdue deadlines show in red. Click any row to open the work order.

### Low Stock Alerts
Shows materials where the current stock is at or below the minimum level. If everything is fine, you see a green "All stock levels OK" message.

### Job Distribution Chart
A bar chart showing how many work orders exist in each division: Media, Civil Works, Energy, and Warehouse.

### Revenue Trend Chart
A line chart showing paid invoice totals for each of the last 6 months.

### Recent Financial Activity
A table of the 6 most recently updated invoices. Columns: invoice number, client, amount, status, last updated.

### Calendar (Full Width)
A full-page calendar showing:
- **Tasks** with deadlines (colour-coded by assigned staff member)
- **Work Orders** with deadlines (colour-coded by assigned staff member, shown with ref number)
- **Admin Tasks** with due dates (colour-coded by priority: red = urgent, orange = high, violet = normal)
- **Staff unavailability** (shown as light red background blocks)
- **Requisitions** (shown on the day they were created, orange if pending, green if approved)

You can switch between **Month**, **Week**, and **List** views using the buttons in the top-right of the calendar. Click any event to jump to that record.

---

## Work Orders — Detailed Guide

Work orders are the biggest resource. Here is how to use them:

### Viewing the List
Go to **Operations → Work Orders**. You see a table with columns: Ref #, Title, Client, Claimed By, Category, Status, Priority, Deadline.

**Filters** (click the funnel icon):
- Status: Pending, In Progress, On Hold, Completed, Cancelled
- Category: Media, Civil Works, Energy, Warehouse
- Priority: Low, Normal, High, Urgent
- Claimed: Claimed or Unclaimed
- Trashed: Show deleted records

### Creating a Work Order
Click the **New Work Order** button at the top. The form has 6 tabs:

1. **General Information** — Status, category, priority, client, department, and optionally link a lead.
2. **Design Job Card** — Job title, project description, date order received, and deadline.
3. **Procurement** — Logistics details, supplier info (name, contact, address), material specs, quantity, unit price, budget, actual cost, procurement process, approval process, timeline, budget alert threshold, and procurement deadline.
4. **Production** — Job number, sign type, quantity, size/material, job description, design file reference, text & graphics, colour scheme, finishing requirements, and production deadline.
5. **Delivery & Installation** — Delivery address, installation requirements, additional info, delivery deadline, job completion date, and start date.
6. **Assessment / Report** — Timeframe, challenges, client feedback, resolutions, and up to 3 signatures (name, signature, date & time).

### Table Actions (On Each Row)
| Action | What It Does |
|---|---|
| **View** | Opens a read-only view of the work order with all sections collapsed. Click a section heading to expand it. |
| **Edit** | Opens the full tabbed form for editing. |
| **PDF** | Downloads a PDF of the full job card. |
| **Reassign** | Change who the work order is claimed by. Pick a user from the dropdown. Status automatically changes to In Progress. |
| **Unassign** | Removes the current claim and puts the work order back into the queue. Requires confirmation. |
| **Send Message** | Opens a messaging dialog to send a notification about this work order. |

### Relation Tabs (On View/Edit Pages)
Below the main form you will see tabs for:
- **Tasks** — All tasks linked to this work order. You can create new tasks here.
- **Comments** — Discussion thread for team communication on this job.
- **Materials** — Materials used on this job (links to warehouse stock).
- **Documents** — Upload and view files attached to this work order.

---

## Invoices — Detailed Guide

### Invoice Status Flow
```
Draft → Pending Accountant → Pending Admin → Approved → Sent → Signed → Paid
                                                            ↘ Overdue
```
Cancelled can happen at any stage.

### Creating an Invoice
Click **New Invoice**. The form has 4 tabs:

1. **Details** — Invoice number (auto-generated like INV-2026-0001), client, link to work order, status, currency, and notes.
2. **Line Items** — Use the repeater to add line items. Each item has a description, quantity, unit, and unit price. The total updates automatically.
3. **Financials** — Subtotal (calculated), tax rate %, tax amount (calculated), and total (calculated). All update in real time as you edit line items.
4. **Dates & Payment** — Issue date, due date, paid at, payment method, and payment reference.

### Table Actions
| Action | What It Does |
|---|---|
| **View / Edit** | Open the invoice. |
| **Approve Invoice** | Only visible when status is "Pending Admin". Click to give final approval. |
| **Email Invoice** | Send the invoice to the client by email. Enter the recipient address (defaults to client email). The client also gets an in-app notification with a "Review & Sign" button. Status changes to Sent. |
| **Mark Paid** | Only visible when Sent, Signed, or Overdue. Marks the invoice as Paid and records the date. Client gets a "Payment Confirmed" notification. |
| **Download PDF** | Download a PDF version of the invoice. |
| **AI Notes** | The AI reads the invoice details and generates professional notes/descriptions. You can review them and click "Use These Notes" to save. |
| **AI Line Items** | Only visible when a work order is linked. The AI looks at the work order's tasks and expenses and suggests line items. Click to auto-add them to the invoice. |

### Relation Tabs
- **Invoice Items** — View all line items in a table format.
- **Documents** — Upload supporting documents.

---

## Requisitions — Detailed Guide

Requisitions (called Purchase Orders in the code) follow this flow:

```
Draft → Pending Finance Approval → Finance Approved → (Your) Final Approval → Approved
                                                   ↘ Rejected
```

### What You See
The table shows: Reference, Purpose, Work Order, GL Account, Requested By, Amount, Status, and Submitted date.

Status labels in the Admin view:
- **Draft** — Not yet submitted
- **Awaiting Finance** — Waiting for accountant review
- **Pending Your Approval** — Finance has approved, now needs your final sign-off
- **Approved** — Fully approved
- **Rejected** — Denied at any stage

### Your Actions
| Action | When Visible | What It Does |
|---|---|---|
| **Approve** | Status is "Finance Approved" | Opens a dialog where you draw your digital signature (or use your saved one). Click Approve to give final authorisation. The requester gets a notification. You can tick "Save this signature for future use". |
| **Reject** | Status is "Pending Finance" or "Finance Approved" | Rejects the requisition. The requester is notified. |
| **Download PDF** | Always | Downloads a formatted PDF of the requisition with all signatures. |

---

## Change Password

Go to the sidebar and find **Change Password** (in the pages section). Enter your current password, set a new one, confirm it, and click Save.

---

## Notification Preferences

Click your name in the top-right corner and select **Notification Preferences**. Here you can choose which events send you alerts and through which channel (Email, WhatsApp, SMS, Dashboard).

---

## Global Search

Press `Ctrl+K` (or `Cmd+K` on Mac) at any time to open the search bar. Type a reference number, name, or title and the system searches across all resources. Click a result to jump straight to it.

---

## Keyboard Shortcuts

| Shortcut | What It Does |
|---|---|
| `Ctrl+K` / `Cmd+K` | Open global search |
| Click sidebar hamburger icon | Collapse/expand sidebar |

---

## Tips

- **Red text on deadlines** means the deadline has passed.
- **Badge colours** follow a standard pattern: gray = pending, yellow/orange = in progress or warning, green = done or approved, red = overdue or urgent.
- **Clickable stat numbers** on the dashboard take you directly to the filtered list page.
- **Calendar events are clickable** — click any event to go to the related record.
- The **bell icon** badge disappears once you read your notifications.
