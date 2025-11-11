<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewRewardPenaltyNotification extends Notification
{
    use Queueable;

    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function via($notifiable)
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {
        $type = $this->item->type ?? 'reward/penalty';
        $line = $type === 'reward' ? 'You have received a reward' : 'A penalty has been issued';
        return (new MailMessage)
                    ->subject('Notification: ' . ucfirst($type))
                    ->line($line)
                    ->line('Amount: ' . ($this->item->amount ?? '-'))
                    ->line('Reason: ' . ($this->item->reason ?? '-'))
                    ->action('View record', url(route('hr.employees.profile', $this->item->employee_id)));
    }

    public function toArray($notifiable)
    {
        return [
            'reward_penalty_id' => $this->item->id ?? null,
            'type' => $this->item->type ?? null,
            'amount' => $this->item->amount ?? null,
            'reason' => $this->item->reason ?? null,
        ];
    }
}
