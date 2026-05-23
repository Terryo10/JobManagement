# Finance Dashboard Guide (Accountant)

> **URL:** `yoursite.com/accountant`
> **Brand:** Household Media — Finance
> **Colour:** Green

This guide walks you through every part of the Finance dashboard. It is designed for the accounting team who manage invoices, review requisitions, handle quotations, and keep track of the company's money.

---

## Logging In

1. Go to `yoursite.com/accountant/login`.
2. Enter your **email** and **password**.
3. Click **Sign in**.

After you sign in you land on the Finance Dashboard home page.

---

## Top Bar

| Item | What It Does |
|---|---|
| **Bell icon** (top-right) | Shows your notifications. The system checks every 5 seconds. You get alerts when new requisitions arrive, invoices change status, and deadlines approach. |
| **User menu** (top-right) | Click your name to see **Notification Preferences** and **Log Out**. |

---

## Sidebar Navigation

### Announcements

- **Announcements** — See company-wide announcements. You can read and comment on them, same as all other dashboards.

### Finance

| Menu Item | What It Does |
|---|---|
| **Invoices** | Create, review, and manage all invoices. This is your main working area. Full detail below. |
| **Quotations** | Create and manage quotations. Send them to clients, and convert accepted ones into invoices. Full detail below. |
| **Requisitions** | Review money requests from staff. As finance, you are the first approval step. Full detail below. |
| **Rate Cards** | Set up and manage service rates. Each rate card has a service type, category, rate, and unit. These show up as quick-fill options when creating quotation and invoice line items. |
| **Tasks** | View tasks across the system. Useful for seeing what work is happening and linking it to invoicing. |
| **Work Orders** | View all work orders. Useful for understanding the scope of a job when reviewing its financials. |
| **Personal Files** | Upload and manage documents for the finance area. |

### Reports

| Menu Item | What It Does |
|---|---|
| **AI Invoice Report** | An AI-powered report that analyses your invoice data. You pick filters and the AI generates insights about revenue, payment patterns, and outstanding balances. You can ask follow-up questions in a chat. |

---

## Dashboard Home Page Widgets

When you log in, you see these widgets:

### Announcements Widget
Shows the latest company announcement. Click to view all.

### Financial Overview (Stats — Top Two Rows)
Six stat cards in two rows:

**Row 1 — Action Items:**

| Card | What it Shows |
|---|---|
| **Awaiting Your Approval** | Number of requisitions that need your finance review. Click to jump to those records. If zero, it shows "All clear!" in green. |
| **Finance Approved** | Number of requisitions you already approved and are now waiting for the admin's final sign-off. |
| **Overdue Invoices** | Total dollar value of invoices that are overdue. If there are any, the card turns red. Click to see them. |

**Row 2 — Financial Summary:**

| Card | What it Shows |
|---|---|
| **Total Invoiced** | Life-to-date total of all active invoices (sent, signed, paid, approved, overdue). |
| **Outstanding Balance** | Total dollar value of invoices that are still unpaid. |
| **Paid This Month** | Dollar value of invoices paid in the current month. |

### Finance Calendar
A calendar showing key financial events and deadlines. Switch between Month, Week, and List views.

### Recent Invoice Activity (Table)
A table of the 8 most recently updated invoices. Columns:
- **Invoice** — Click to go directly to that invoice.
- **Client** — Company name.
- **Amount** — Invoice total in USD.
- **Status** — Badge with colour coding (grey = draft, yellow = pending, green = approved/signed/paid, blue = sent, red = overdue).
- **Due** — Due date. Shows in red if overdue and not yet paid.
- **Last Updated** — How long ago the invoice was changed.

---

## Invoices — Detailed Guide

Go to **Finance → Invoices** in the sidebar.

### Invoice Status Flow
```
Draft → Pending Accountant → Pending Admin → Approved → Sent → Signed → Paid
                                                             ↘ Overdue
```

As the accountant, your main job is to:
1. **Create** or review invoices in Draft status.
2. **Move** them to "Pending Accountant" and then "Pending Admin" when they are ready.
3. After admin approves, **send** the invoice to the client.

### Creating an Invoice
1. Click **New Invoice**.
2. Fill in the 4 tabs:
   - **Details** — Invoice number (auto-generated), client, linked work order, status, currency, notes.
   - **Line Items** — Add line items with description, quantity, unit, and unit price. Totals calculate automatically.
   - **Financials** — See subtotal, set tax rate %, and the system calculates tax amount and total.
   - **Dates & Payment** — Issue date, due date, paid at, payment method, payment reference.

### Table Actions

| Action | When Visible | What It Does |
|---|---|---|
| **View / Edit** | Always | Open the invoice details or edit form. |
| **Approve (Finance)** | Status = "Pending Accountant" | You approve the invoice from the finance side. It moves to "Pending Admin". |
| **Email Invoice** | Status = Approved, Sent, Signed, or Overdue | Send the invoice to the client by email. You enter the email address (defaults to the client's email). The client also gets a dashboard notification with a "Review & Sign" link. |
| **Mark Paid** | Status = Sent, Signed, or Overdue | Mark the invoice as paid. Records the payment date automatically. The client gets a "Payment Confirmed" notification. |
| **Download PDF** | Always | Download a formatted PDF of the invoice. |
| **AI Notes** | Always | The AI reads the invoice and generates professional notes. Review them and click "Use These Notes" to save. |
| **AI Line Items** | When linked to a work order | The AI looks at the work order's tasks and expenses and suggests line items. Click to add them automatically. |

### Relation Tabs
- **Invoice Items** — See all line items in a table.
- **Documents** — Upload supporting documents.

---

## Quotations — Detailed Guide

Go to **Finance → Quotations** in the sidebar.

### Quotation Status Flow
```
Draft → Sent → Accepted → Converted to Invoice
             ↘ Rejected
             ↘ Expired
```

### Creating a Quotation
1. Click **New Quotation**.
2. Fill in the 3 tabs:
   - **Details** — Quotation number (auto-generated like QUO-2026-0001), client, linked work order, status, currency, valid until date, and notes.
   - **Line Items** — Add line items. You can pick a **Rate Card** from the dropdown to auto-fill the description, unit, and unit price. Otherwise, type them manually. Totals calculate automatically.
   - **Financials** — Subtotal, tax rate, tax amount, and total (all calculated).

### Table Actions

| Action | When Visible | What It Does |
|---|---|---|
| **View / Edit** | Always | Open the quotation. |
| **Download PDF** | Always | Download a formatted PDF of the quotation. |
| **Convert to Invoice** | Status = Sent or Accepted | Creates a new invoice with all the line items copied over. The quotation status changes to "Converted". |

---

## Requisitions — Detailed Guide

Go to **Finance → Requisitions** in the sidebar.

### Requisition Approval Flow
```
Staff submits → Pending Finance Approval → (You review) → Finance Approved → Admin Final Approval → Approved
                                         ↘ Rejected
```

As the finance team, you are the **first gate**. When a staff member submits a requisition, it arrives here with status "Pending Finance Approval".

### What You See in the Table
| Column | What it Shows |
|---|---|
| **Reference** | Reference number (REQ-2026-0001) |
| **Purpose** | What the money is for |
| **Work Order** | Linked job (if any) |
| **GL Account** | Accounting code and name |
| **Requested By** | Who submitted it |
| **Amount** | Dollar amount requested |
| **Status** | Badge showing current state |
| **Submitted** | When it was submitted |

### Your Actions

| Action | When Visible | What It Does |
|---|---|---|
| **Approve** | Status = "Pending Finance Approval" | Opens a dialog where you draw your digital signature. Once you sign, the requisition moves to "Finance Approved" and goes to admin for final approval. You can tick "Save this signature for future use" to speed things up next time. The requester is notified. |
| **Reject** | Status = "Pending Finance Approval" | Denies the requisition. The requester is notified. |
| **Download PDF** | Always | Download a formatted PDF with all details and signatures. |
| **View / Edit** | Always | Open the full requisition details. |

### Reviewing a Requisition
When you see a requisition in "Pending Finance Approval":
1. Check the **Purpose** — Does it make sense?
2. Check the **Amount** — Is it reasonable?
3. Check the **GL Account** — Is it coded to the right account?
4. Check **Attachments** — Are there supporting documents (quotes, receipts)?
5. If OK, click **Approve** and draw your signature.
6. If not OK, click **Reject**.

---

## Rate Cards

Go to **Finance → Rate Cards** in the sidebar.

Rate cards are your pricing templates. Each one has:
- **Service Type** — What kind of service (e.g., "Billboard Printing")
- **Category** — Which division it falls under (e.g., "Media")
- **Rate** — The price per unit
- **Unit** — What unit the rate is in (e.g., "per sqm", "per hour", "each")
- **Active** — Toggle to enable/disable

When someone creates a quotation or invoice, they can pick a rate card from a dropdown and the description, unit, and price fill in automatically. This keeps pricing consistent.

---

## Change Password

Go to the sidebar and find **Change Password**. Enter your current password, type a new one, confirm it, and click Save.

---

## Notification Preferences

Click your name in the top-right corner and select **Notification Preferences**. Choose which events you want alerts for and how you want to receive them (Email, WhatsApp, SMS, Dashboard).

---

## Tips

- **Check "Awaiting Your Approval" daily** — Requisitions from staff should not sit too long. Try to review them within 24 hours.
- **Overdue invoices in red** — These need follow-up. Either contact the client or mark the invoice as overdue.
- **Use Rate Cards** — They save time and keep pricing consistent. Set them up once and use them on every quotation and invoice.
- **AI tools save time** — Use "AI Notes" to auto-generate professional invoice descriptions and "AI Line Items" to auto-populate invoices from work order data.
- **Convert quotations to invoices** — When a client accepts a quotation, use the "Convert to Invoice" button instead of re-typing everything.
- **The Recent Invoice Activity widget** on the dashboard is a quick way to see what has changed without going into the full invoices list.
