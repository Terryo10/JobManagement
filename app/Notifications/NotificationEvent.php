<?php

namespace App\Notifications;

/**
 * Normalized event DTO passed to NotificationRouter.
 * Must remain fully serializable (no Eloquent model instances).
 */
class NotificationEvent
{
    public function __construct(
        public readonly string  $type,
        public readonly string  $title,
        public readonly string  $body,
        public readonly string  $icon            = 'heroicon-o-bell',
        public readonly string  $color           = 'info',
        public readonly ?string $actionUrl       = null,
        public readonly ?string $actionText      = null,
        /** Direct user IDs to notify */
        public readonly array   $recipientUserIds  = [],
        /** Role slugs — all users with these roles are notified */
        public readonly array   $recipientRoles    = [],
        /** Class name of the source model, e.g. WorkOrder::class */
        public readonly ?string $subjectType     = null,
        /** Primary key of the source model */
        public readonly ?int    $subjectId       = null,
        /** low | normal | high | critical */
        public readonly string  $priority        = 'normal',
        /** Prevents duplicate delivery when the same event fires multiple times */
        public readonly ?string $idempotencyKey  = null,
        /** Channel-specific extras, e.g. ['whatsapp_template' => 'generic_alert'] */
        public readonly array   $extraData       = [],
    ) {}
}
