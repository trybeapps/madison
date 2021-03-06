<?php

namespace App\Notifications;

use App\Models\Annotation;
use App\Notifications\Messages\MailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentFlagged extends Notification implements ShouldQueue
{
    use Queueable;

    public $flag;
    public $parentType;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Annotation $flag)
    {
        $this->flag = $flag;
        $this->actionUrl = $flag->annotatable->getLink();

        if ($this->flag->annotatable->isNote()) {
            $this->parentType = trans('messages.notifications.comment_type_note');
        } else {
            $this->parentType = trans('messages.notifications.comment_type_comment');
        }

        $this->subjectText = trans(static::baseMessageLocation().'.subject', [
            'comment_type' => $this->parentType,
            'document' => $this->flag->rootAnnotatable->title,
        ]);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage($this, $notifiable))
                    ->subject($this->subjectText)
                    ->action(trans('messages.notifications.see_comment', ['comment_type' => $this->parentType]), $this->actionUrl)
                    ;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'line' => $this->toLine(),
            'name' => static::getName(),
            'flag_id' => $this->flag->id,
            'comment_type' => $this->parentType,
        ];
    }

    public static function getName()
    {
        return 'madison.comment.flagged';
    }

    public static function getType()
    {
        return static::TYPE_SPONSOR;
    }

    public function getInstigator()
    {
        return $this->flag->user;
    }
}
