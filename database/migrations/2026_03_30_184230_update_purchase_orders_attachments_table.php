<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Add the new JSON column
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('attachment');
        });

        // 2. Migrate existing data (wrap existing singular attachment in a JSON array)
        DB::table('purchase_orders')
            ->whereNotNull('attachment')
            ->where('attachment', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    DB::table('purchase_orders')
                        ->where('id', $order->id)
                        ->update([
                            'attachments' => json_encode([$order->attachment])
                        ]);
                }
            });

        // 3. Drop old column
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }

    public function down(): void
    {
        // 1. Restore old column
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('attachments');
        });

        // 2. Migrate data back (take first item from array)
        DB::table('purchase_orders')
            ->whereNotNull('attachments')
            ->orderBy('id')
            ->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    $attachments = json_decode($order->attachments, true);
                    $firstAttachment = is_array($attachments) && count($attachments) > 0 ? $attachments[0] : null;

                    if ($firstAttachment) {
                        DB::table('purchase_orders')
                            ->where('id', $order->id)
                            ->update([
                                'attachment' => $firstAttachment
                            ]);
                    }
                }
            });

        // 3. Drop JSON column
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
