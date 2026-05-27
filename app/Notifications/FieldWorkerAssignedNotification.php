<?php

namespace App\Notifications;

use App\Models\FieldWorker;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FieldWorkerAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Task $task,
        public readonly string $instructions,
        public readonly ?string $customDeadline = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $deadlineStr = $this->customDeadline 
            ? \Carbon\Carbon::parse($this->customDeadline)->format('d M Y H:i') 
            : ($this->task->deadline ? $this->task->deadline->format('d M Y') : 'Not set');

        return (new MailMessage)
            ->subject("New Task Assignment: {$this->task->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been assigned to a new task for Household Media.")
            ->line("**Task Details:**")
            ->line("- Task: {$this->task->title}")
            ->line("- Deadline: {$deadlineStr}")
            ->line(" ")
            ->line("**Your Specific Instructions:**")
            ->line("-----------------------------------------")
            ->line($this->instructions)
            ->line("-----------------------------------------")
            ->line(" ")
            ->line("Report to your supervisor upon completion or if you have any questions.")
            ->salutation("Thank you,\nHousehold Media Operations");
    }
}
