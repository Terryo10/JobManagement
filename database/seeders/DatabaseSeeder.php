<?php

namespace Database\Seeders;

use App\Models\BusinessReport;
use App\Models\Client;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\MarketResearch;
use App\Models\Material;
use App\Models\NetworkingEvent;
use App\Models\NotificationRule;
use App\Models\Proposal;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RateCard;
use App\Models\StockLevel;
use App\Models\Supplier;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ────────────────────────────────────────
        $roles = ['super_admin', 'manager', 'dept_head', 'staff', 'accountant', 'client', 'marketing'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ── Departments ──────────────────────────────────
        $departments = [
            ['name' => 'Design Studio', 'code' => 'DSN'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Procurement', 'code' => 'PRC'],
            ['name' => 'Events & Projects', 'code' => 'EVT'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Workshop', 'code' => 'WRK'],
            ['name' => 'Field Operations', 'code' => 'FLD'],
        ];
        $deptModels = [];
        foreach ($departments as $dept) {
            $deptModels[$dept['name']] = Department::firstOrCreate(
                ['code' => $dept['code']],
                ['name' => $dept['name']]
            );
        }

        // ── Users ────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@householdmedia.co.zw'],
            ['name' => 'System Admin', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $admin->assignRole('super_admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@householdmedia.co.zw'],
            ['name' => 'Operations Manager', 'password' => Hash::make('password'), 'is_active' => true, 'department_id' => $deptModels['Events & Projects']->id]
        );
        $manager->assignRole('manager');

        $accountant = User::firstOrCreate(
            ['email' => 'accountant@householdmedia.co.zw'],
            ['name' => 'Finance Officer', 'password' => Hash::make('password'), 'is_active' => true, 'department_id' => $deptModels['Finance']->id]
        );
        $accountant->assignRole('accountant');

        $deptHead = User::firstOrCreate(
            ['email' => 'depthead@householdmedia.co.zw'],
            ['name' => 'Design Lead', 'password' => Hash::make('password'), 'is_active' => true, 'department_id' => $deptModels['Design Studio']->id]
        );
        $deptHead->assignRole('dept_head');

        $staff1 = User::firstOrCreate(
            ['email' => 'staff@householdmedia.co.zw'],
            ['name' => 'John Moyo', 'password' => Hash::make('password'), 'is_active' => true, 'department_id' => $deptModels['Field Operations']->id]
        );
        $staff1->assignRole('staff');

        $staff2 = User::firstOrCreate(
            ['email' => 'staff2@householdmedia.co.zw'],
            ['name' => 'Tendai Ncube', 'password' => Hash::make('password'), 'is_active' => true, 'department_id' => $deptModels['Workshop']->id]
        );
        $staff2->assignRole('staff');

        $staff3 = User::firstOrCreate(
            ['email' => 'staff3@householdmedia.co.zw'],
            ['name' => 'Rumbidzai Chuma', 'password' => Hash::make('password'), 'is_active' => true, 'department_id' => $deptModels['Marketing']->id]
        );
        $staff3->assignRole('staff');

        $clientUser = User::firstOrCreate(
            ['email' => 'client@example.com'],
            ['name' => 'Demo Client', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $clientUser->assignRole('client');

        $marketingUser = User::firstOrCreate(
            ['email' => 'marketing@householdmedia.co.zw'],
            ['name' => 'Sarah BD', 'password' => Hash::make('password'), 'is_active' => true, 'department_id' => $deptModels['Marketing']->id]
        );
        $marketingUser->assignRole('marketing');

        // ── Clients ──────────────────────────────────────
        $client1 = Client::firstOrCreate(
            ['company_name' => 'Coca-Cola Zimbabwe'],
            ['contact_person' => 'Sarah Mutasa', 'email' => 'sarah@cocacola.co.zw', 'phone' => '+263 77 123 4567', 'city' => 'Harare', 'is_active' => true, 'created_by' => $admin->id]
        );

        $client2 = Client::firstOrCreate(
            ['company_name' => 'Old Mutual Zimbabwe'],
            ['contact_person' => 'James Banda', 'email' => 'james@oldmutual.co.zw', 'phone' => '+263 77 234 5678', 'city' => 'Harare', 'is_active' => true, 'created_by' => $admin->id]
        );

        $client3 = Client::firstOrCreate(
            ['company_name' => 'Econet Wireless'],
            ['contact_person' => 'Tatenda Mhaka', 'email' => 'tatenda@econet.co.zw', 'phone' => '+263 77 345 6789', 'city' => 'Harare', 'is_active' => true, 'created_by' => $admin->id]
        );

        $client4 = Client::firstOrCreate(
            ['company_name' => 'Delta Beverages'],
            ['contact_person' => 'Farai Chirwa', 'email' => 'farai@delta.co.zw', 'phone' => '+263 77 456 7890', 'city' => 'Bulawayo', 'is_active' => true, 'created_by' => $admin->id]
        );

        // Client record for the demo client user (matches client@example.com login)
        $clientDemo = Client::firstOrCreate(
            ['company_name' => 'Demo Client Company'],
            ['contact_person' => 'Demo Client', 'email' => 'client@example.com', 'phone' => '+263 77 000 0000', 'city' => 'Harare', 'is_active' => true, 'created_by' => $admin->id]
        );

        $client5 = Client::firstOrCreate(
            ['company_name' => 'TelOne'],
            ['contact_person' => 'Memory Dube', 'email' => 'memory@telone.co.zw', 'phone' => '+263 77 567 8901', 'city' => 'Harare', 'is_active' => true, 'created_by' => $admin->id]
        );

        // ── Leads ────────────────────────────────────────
        Lead::firstOrCreate(
            ['contact_email' => 'info@zimtrade.co.zw'],
            ['client_id' => null, 'contact_name' => 'Peter Zimba', 'contact_phone' => '+263 78 111 2233', 'company_name' => 'ZimTrade', 'source' => 'referral', 'status' => 'new', 'assigned_to' => $manager->id, 'follow_up_date' => now()->addDays(2), 'created_by' => $admin->id]
        );

        Lead::firstOrCreate(
            ['contact_email' => 'promo@natfoods.co.zw'],
            ['client_id' => null, 'contact_name' => 'Grace Chipo', 'contact_phone' => '+263 78 222 3344', 'company_name' => 'National Foods', 'source' => 'website', 'status' => 'in_progress', 'assigned_to' => $staff3->id, 'follow_up_date' => now()->subDay(), 'created_by' => $manager->id]
        );

        Lead::firstOrCreate(
            ['contact_email' => 'events@rgs.co.zw'],
            ['client_id' => $client3->id, 'contact_name' => 'Lloyd Shumba', 'contact_phone' => '+263 78 333 4455', 'company_name' => 'Econet Wireless', 'source' => 'cold_call', 'status' => 'new', 'assigned_to' => $manager->id, 'follow_up_date' => now()->addDays(5), 'created_by' => $admin->id]
        );

        // ── Work Orders ──────────────────────────────────
        $wo1 = WorkOrder::firstOrCreate(
            ['reference_number' => 'HMC-2026-0001'],
            ['client_id' => $client1->id, 'title' => 'Main Street Billboard Installation', 'description' => 'Install 6x3m billboard at Main Street & 2nd Avenue intersection for Coca-Cola summer campaign.', 'category' => 'media', 'status' => 'in_progress', 'priority' => 'high', 'budget' => 8500.00, 'actual_cost' => 3200.00, 'assigned_department_id' => $deptModels['Field Operations']->id, 'start_date' => now()->subDays(5), 'deadline' => now()->addDays(10), 'created_by' => $admin->id]
        );

        $wo2 = WorkOrder::firstOrCreate(
            ['reference_number' => 'HMC-2026-0002'],
            ['client_id' => $client2->id, 'title' => 'Corporate Office Interior Branding', 'description' => 'Full interior branding package for Old Mutual new Harare office. Includes wall wraps, directional signage, and reception area design.', 'category' => 'media', 'status' => 'pending', 'priority' => 'normal', 'budget' => 15000.00, 'assigned_department_id' => $deptModels['Design Studio']->id, 'start_date' => now()->addDays(3), 'deadline' => now()->addDays(30), 'created_by' => $manager->id]
        );

        $wo3 = WorkOrder::firstOrCreate(
            ['reference_number' => 'HMC-2026-0003'],
            ['client_id' => $client3->id, 'title' => 'Solar Panel Installation — Econet Tower', 'description' => 'Install 50kW solar panel system on Econet communications tower in Masvingo.', 'category' => 'energy', 'status' => 'in_progress', 'priority' => 'urgent', 'budget' => 45000.00, 'actual_cost' => 28000.00, 'assigned_department_id' => $deptModels['Field Operations']->id, 'start_date' => now()->subDays(14), 'deadline' => now()->subDays(2), 'created_by' => $admin->id]
        );

        $wo4 = WorkOrder::firstOrCreate(
            ['reference_number' => 'HMC-2026-0004'],
            ['client_id' => $client4->id, 'title' => 'Warehouse Shelving & Signage', 'description' => 'Design and install warehouse inventory management signage and shelving labels for Delta Beverages distribution centre.', 'category' => 'warehouse', 'status' => 'in_progress', 'priority' => 'normal', 'budget' => 6000.00, 'actual_cost' => 1800.00, 'assigned_department_id' => $deptModels['Workshop']->id, 'start_date' => now()->subDays(3), 'deadline' => now()->addDays(14), 'created_by' => $manager->id]
        );

        $wo5 = WorkOrder::firstOrCreate(
            ['reference_number' => 'HMC-2026-0005'],
            ['client_id' => $client5->id, 'title' => 'Roadside Retaining Wall Construction', 'description' => 'Build 200m retaining wall along access road to TelOne data centre in Msasa.', 'category' => 'civil_works', 'status' => 'on_hold', 'priority' => 'high', 'budget' => 120000.00, 'assigned_department_id' => $deptModels['Field Operations']->id, 'start_date' => now()->subDays(7), 'deadline' => now()->addDays(60), 'created_by' => $admin->id]
        );

        $wo6 = WorkOrder::firstOrCreate(
            ['reference_number' => 'HMC-2026-0006'],
            ['client_id' => $client1->id, 'title' => 'Digital Campaign Design — Summer 2026', 'description' => 'Design digital campaign assets for Coca-Cola summer promotion including social media, web banners, and email templates.', 'category' => 'media', 'status' => 'pending', 'priority' => 'normal', 'budget' => 5000.00, 'assigned_department_id' => $deptModels['Marketing']->id, 'start_date' => now()->addDays(1), 'deadline' => now()->addDays(21), 'created_by' => $manager->id]
        );

        // ── Tasks ────────────────────────────────────────
        Task::firstOrCreate(
            ['work_order_id' => $wo1->id, 'title' => 'Site survey & measurements'],
            ['description' => 'Conduct site survey at Main St intersection, take measurements for billboard foundation.', 'assigned_to' => $staff1->id, 'department_id' => $deptModels['Field Operations']->id, 'status' => 'completed', 'priority' => 'high', 'estimated_hours' => 4, 'actual_hours' => 3.5, 'completion_percentage' => 100, 'start_date' => now()->subDays(5), 'deadline' => now()->subDays(4), 'completed_at' => now()->subDays(4), 'created_by' => $admin->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo1->id, 'title' => 'Foundation & structure fabrication'],
            ['description' => 'Fabricate steel structure for 6x3m billboard in workshop.', 'assigned_to' => $staff2->id, 'department_id' => $deptModels['Workshop']->id, 'status' => 'in_progress', 'priority' => 'high', 'estimated_hours' => 16, 'actual_hours' => 10, 'completion_percentage' => 65, 'start_date' => now()->subDays(3), 'deadline' => now()->addDays(2), 'created_by' => $admin->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo1->id, 'title' => 'Billboard graphic design & print'],
            ['description' => 'Design and print Coca-Cola summer campaign billboard graphics.', 'assigned_to' => $deptHead->id, 'department_id' => $deptModels['Design Studio']->id, 'status' => 'in_progress', 'priority' => 'normal', 'estimated_hours' => 8, 'actual_hours' => 4, 'completion_percentage' => 50, 'start_date' => now()->subDays(2), 'deadline' => now()->addDays(5), 'created_by' => $admin->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo1->id, 'title' => 'On-site installation'],
            ['description' => 'Transport and install billboard at site.', 'assigned_to' => $staff1->id, 'department_id' => $deptModels['Field Operations']->id, 'status' => 'pending', 'priority' => 'high', 'estimated_hours' => 8, 'completion_percentage' => 0, 'start_date' => now()->addDays(5), 'deadline' => now()->addDays(8), 'created_by' => $admin->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo3->id, 'title' => 'Electrical assessment & planning'],
            ['description' => 'Assess tower electrical capacity and plan solar panel wiring.', 'assigned_to' => $staff1->id, 'department_id' => $deptModels['Field Operations']->id, 'status' => 'completed', 'priority' => 'urgent', 'estimated_hours' => 6, 'actual_hours' => 8, 'completion_percentage' => 100, 'start_date' => now()->subDays(14), 'deadline' => now()->subDays(12), 'completed_at' => now()->subDays(11), 'created_by' => $admin->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo3->id, 'title' => 'Solar panel mounting & wiring'],
            ['description' => 'Mount panels on tower structure and complete electrical wiring.', 'assigned_to' => $staff1->id, 'department_id' => $deptModels['Field Operations']->id, 'status' => 'in_progress', 'priority' => 'urgent', 'estimated_hours' => 40, 'actual_hours' => 32, 'completion_percentage' => 80, 'start_date' => now()->subDays(10), 'deadline' => now()->subDays(2), 'created_by' => $admin->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo3->id, 'title' => 'System testing & commissioning'],
            ['description' => 'Test solar system output and commission with Econet technical team.', 'assigned_to' => $staff2->id, 'department_id' => $deptModels['Field Operations']->id, 'status' => 'pending', 'priority' => 'urgent', 'estimated_hours' => 8, 'completion_percentage' => 0, 'deadline' => now()->addDays(3), 'created_by' => $admin->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo4->id, 'title' => 'Signage design for warehouse zones'],
            ['description' => 'Design zone identification signage and aisle labels.', 'assigned_to' => $deptHead->id, 'department_id' => $deptModels['Design Studio']->id, 'status' => 'in_progress', 'priority' => 'normal', 'estimated_hours' => 6, 'actual_hours' => 2, 'completion_percentage' => 30, 'start_date' => now()->subDays(2), 'deadline' => now()->addDays(7), 'created_by' => $manager->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo2->id, 'title' => 'Client brief & concept development'],
            ['description' => 'Meet with Old Mutual team to finalise interior branding requirements and develop initial concepts.', 'assigned_to' => $deptHead->id, 'department_id' => $deptModels['Design Studio']->id, 'status' => 'pending', 'priority' => 'normal', 'estimated_hours' => 8, 'completion_percentage' => 0, 'start_date' => now()->addDays(3), 'deadline' => now()->addDays(10), 'created_by' => $manager->id]
        );

        Task::firstOrCreate(
            ['work_order_id' => $wo6->id, 'title' => 'Creative brief & storyboard'],
            ['description' => 'Develop creative brief and storyboard for Coca-Cola digital campaign.', 'assigned_to' => $staff3->id, 'department_id' => $deptModels['Marketing']->id, 'status' => 'pending', 'priority' => 'normal', 'estimated_hours' => 6, 'completion_percentage' => 0, 'start_date' => now()->addDays(1), 'deadline' => now()->addDays(7), 'created_by' => $manager->id]
        );

        // ── Suppliers ────────────────────────────────────
        $supplier1 = Supplier::firstOrCreate(
            ['name' => 'ZimPrint Supplies'],
            ['contact_person' => 'Edwin Takura', 'email' => 'sales@zimprint.co.zw', 'phone' => '+263 77 888 9900', 'address' => '14 Mutare Rd, Harare', 'is_active' => true]
        );

        Supplier::firstOrCreate(
            ['name' => 'SteelWorks Zimbabwe'],
            ['contact_person' => 'Brian Ndlovu', 'email' => 'orders@steelworks.co.zw', 'phone' => '+263 77 999 0011', 'address' => '45 Industrial Rd, Bulawayo', 'is_active' => true]
        );

        // ── Materials ────────────────────────────────────
        $mat1 = Material::firstOrCreate(
            ['name' => 'PVC Banner (per sq metre)'],
            ['sku' => 'MAT-PVC-001', 'unit' => 'sqm', 'unit_cost' => 12.50, 'minimum_stock_level' => 50, 'category' => 'printing', 'is_active' => true]
        );

        $mat2 = Material::firstOrCreate(
            ['name' => 'Steel Tubing 50mm'],
            ['sku' => 'MAT-STL-001', 'unit' => 'metres', 'unit_cost' => 8.00, 'minimum_stock_level' => 100, 'category' => 'fabrication', 'is_active' => true]
        );

        $mat3 = Material::firstOrCreate(
            ['name' => 'Solar Panel 350W'],
            ['sku' => 'MAT-SOL-001', 'unit' => 'units', 'unit_cost' => 280.00, 'minimum_stock_level' => 10, 'category' => 'energy', 'is_active' => true]
        );

        // ── Stock Levels ─────────────────────────────────
        StockLevel::firstOrCreate(
            ['material_id' => $mat1->id],
            ['current_quantity' => 120, 'last_updated' => now(), 'last_updated_by' => $admin->id]
        );

        StockLevel::firstOrCreate(
            ['material_id' => $mat2->id],
            ['current_quantity' => 45, 'last_updated' => now(), 'last_updated_by' => $admin->id]
        );

        StockLevel::firstOrCreate(
            ['material_id' => $mat3->id],
            ['current_quantity' => 8, 'last_updated' => now(), 'last_updated_by' => $admin->id]
        );

        // ── Rate Cards ──────────────────────────────────
        $rateCards = [
            RateCard::firstOrCreate(['service_type' => 'Billboard Installation'], [
                'category' => 'media', 'unit' => 'sqm', 'rate' => 45.00, 'currency' => 'USD',
                'effective_from' => now()->startOfYear(), 'is_active' => true, 'created_by' => $admin->id,
            ]),
            RateCard::firstOrCreate(['service_type' => 'Graphic Design'], [
                'category' => 'design', 'unit' => 'hour', 'rate' => 35.00, 'currency' => 'USD',
                'effective_from' => now()->startOfYear(), 'is_active' => true, 'created_by' => $admin->id,
            ]),
            RateCard::firstOrCreate(['service_type' => 'Solar Panel Installation'], [
                'category' => 'energy', 'unit' => 'unit', 'rate' => 150.00, 'currency' => 'USD',
                'effective_from' => now()->startOfYear(), 'is_active' => true, 'created_by' => $admin->id,
            ]),
            RateCard::firstOrCreate(['service_type' => 'Steel Fabrication'], [
                'category' => 'civil_works', 'unit' => 'metre', 'rate' => 25.00, 'currency' => 'USD',
                'effective_from' => now()->startOfYear(), 'is_active' => true, 'created_by' => $admin->id,
            ]),
            RateCard::firstOrCreate(['service_type' => 'General Labour'], [
                'category' => 'labour', 'unit' => 'hour', 'rate' => 12.00, 'currency' => 'USD',
                'effective_from' => now()->startOfYear(), 'is_active' => true, 'created_by' => $admin->id,
            ]),
        ];

        // ── Invoices ────────────────────────────────────
        $inv1 = Invoice::firstOrCreate(['invoice_number' => 'INV-2026-0001'], [
            'client_id' => $client1->id, 'work_order_id' => $wo1->id,
            'status' => 'sent', 'subtotal' => 4500.00, 'tax_rate' => 15,
            'tax_amount' => 675.00, 'total' => 5175.00, 'currency' => 'USD',
            'issued_at' => now()->subDays(10), 'due_at' => now()->addDays(20),
            'created_by' => $admin->id,
        ]);

        // Add line items for invoice
        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv1->id, 'description' => 'Billboard Installation — Main Street'],
            ['quantity' => 50, 'unit' => 'sqm', 'unit_price' => 45.00, 'total' => 2250.00, 'rate_card_id' => $rateCards[0]->id]
        );
        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv1->id, 'description' => 'Design & Artwork'],
            ['quantity' => 40, 'unit' => 'hour', 'unit_price' => 35.00, 'total' => 1400.00, 'rate_card_id' => $rateCards[1]->id]
        );
        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv1->id, 'description' => 'General Labour'],
            ['quantity' => 70.83, 'unit' => 'hour', 'unit_price' => 12.00, 'total' => 850.00, 'rate_card_id' => $rateCards[4]->id]
        );

        $inv2 = Invoice::firstOrCreate(['invoice_number' => 'INV-2026-0002'], [
            'client_id' => $client3->id, 'work_order_id' => $wo3->id,
            'status' => 'paid', 'subtotal' => 3600.00, 'tax_rate' => 15,
            'tax_amount' => 540.00, 'total' => 4140.00, 'currency' => 'USD',
            'issued_at' => now()->subDays(30), 'due_at' => now()->subDays(5),
            'paid_at' => now()->subDays(3), 'payment_method' => 'EFT',
            'created_by' => $admin->id,
        ]);

        InvoiceItem::firstOrCreate(
            ['invoice_id' => $inv2->id, 'description' => 'Solar Panel Installation — Econet Tower'],
            ['quantity' => 24, 'unit' => 'unit', 'unit_price' => 150.00, 'total' => 3600.00, 'rate_card_id' => $rateCards[2]->id]
        );

        // ── Purchase Orders ─────────────────────────────
        $po1 = PurchaseOrder::firstOrCreate(['reference_number' => 'PO-2026-0001'], [
            'title' => 'Printing Materials for Summer Campaign',
            'supplier_id' => $supplier1->id, 'status' => 'ordered',
            'ordered_by' => $manager->id, 'approved_by' => $admin->id,
            'total_amount' => 1525.00, 'expected_delivery' => now()->addDays(7),
        ]);

        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po1->id, 'material_id' => $mat1->id],
            ['quantity' => 100, 'unit_price' => 12.50, 'total_price' => 1250.00]
        );
        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po1->id, 'material_id' => $mat2->id],
            ['quantity' => 50, 'unit_price' => 5.50, 'total_price' => 275.00]
        );

        // ── Notification Rules ──────────────────────────
        $notifRules = [
            ['rule_key' => 'deadline_3d', 'rule_type' => 'deadline_reminder', 'label' => 'Deadline — 3 Days Warning',
             'value' => '3', 'trigger_days' => 3, 'is_active' => true],
            ['rule_key' => 'deadline_1d', 'rule_type' => 'deadline_reminder', 'label' => 'Deadline — Tomorrow',
             'value' => '1', 'trigger_days' => 1, 'is_active' => true],
            ['rule_key' => 'task_overdue_daily', 'rule_type' => 'task_overdue', 'label' => 'Overdue Task Nudge',
             'value' => '1', 'is_active' => true, 'applies_to_role' => 'manager'],
            ['rule_key' => 'budget_80pct', 'rule_type' => 'budget_threshold', 'label' => 'Budget at 80%',
             'value' => '80', 'is_active' => true, 'applies_to_role' => 'manager'],
            ['rule_key' => 'stock_low', 'rule_type' => 'stock_low', 'label' => 'Low Stock Alert',
             'value' => '1', 'is_active' => true, 'applies_to_role' => 'manager'],
            ['rule_key' => 'invoice_overdue', 'rule_type' => 'invoice_overdue', 'label' => 'Invoice Overdue Auto-Mark',
             'value' => '1', 'is_active' => true],
        ];
        foreach ($notifRules as $rule) {
            NotificationRule::firstOrCreate(['rule_key' => $rule['rule_key']], $rule);
        }

        // ── Marketing Sample Data ───────────────────────
        Proposal::firstOrCreate(
            ['title' => 'Econet Solar Rollout 2026'],
            ['client_id' => $client3->id, 'prepared_by' => $marketingUser->id, 'type' => 'proposal', 'status' => 'submitted', 'value' => 150000, 'currency' => 'USD', 'submitted_at' => now()->subDays(2), 'valid_until' => now()->addDays(28), 'content' => '<p>Proposal for full solar rollout...</p>']
        );
        Proposal::firstOrCreate(
            ['title' => 'Old Mutual Branding Refresh'],
            ['client_id' => $client2->id, 'prepared_by' => $marketingUser->id, 'type' => 'pitch', 'status' => 'draft', 'value' => 25000, 'currency' => 'USD']
        );

        MarketResearch::firstOrCreate(
            ['title' => 'Digital Billboard Adoption Q1 2026'],
            ['category' => 'trend', 'summary' => 'Rising demand for DOOH in Harare CBD.', 'source' => 'ZimAds Report', 'findings' => '<ul><li>30% increase in DOOH</li></ul>', 'researched_by' => $marketingUser->id, 'research_date' => now()->subDays(10)]
        );

        NetworkingEvent::firstOrCreate(
            ['name' => 'ZimTrade Exporters Conference'],
            ['type' => 'conference', 'location' => 'HICC, Harare', 'start_date' => now()->addDays(15), 'end_date' => now()->addDays(16), 'description' => 'Annual exporters conference.', 'created_by' => $marketingUser->id]
        );

        BusinessReport::firstOrCreate(
            ['title' => 'Q1 Growth Strategy'],
            ['type' => 'growth_strategy', 'client_id' => null, 'content' => '<p>Focus on energy sector...</p>', 'prepared_by' => $marketingUser->id, 'status' => 'draft']
        );

        // ── Output ───────────────────────────────────────
        $this->command->info('✅ Seed data created successfully.');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['super_admin', 'admin@householdmedia.co.zw', 'password'],
                ['manager', 'manager@householdmedia.co.zw', 'password'],
                ['dept_head', 'depthead@householdmedia.co.zw', 'password'],
                ['staff', 'staff@householdmedia.co.zw', 'password'],
                ['staff', 'staff2@householdmedia.co.zw', 'password'],
                ['staff', 'staff3@householdmedia.co.zw', 'password'],
                ['accountant', 'accountant@householdmedia.co.zw', 'password'],
                ['marketing', 'marketing@householdmedia.co.zw', 'password'],
                ['client', 'client@example.com', 'password'],
            ]
        );
    }
}
