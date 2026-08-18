<?php

namespace App\Mail;

use App\Models\MailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplatedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected function __construct(
        public string $renderedSubject,
        public string $renderedBody,
    ) {}

    /**
     * TemplatedMail::make('welcome', $user, ['points' => 10])->send() a la vue "Mail::to($user)->send(...)"
     * Usage: Mail::to($user)->send(TemplatedMail::make('welcome', $user));
     */
    public static function make(string $templateKey, User $user, array $extra = []): self
    {
        $template = MailTemplate::where('key', $templateKey)->firstOrFail();

        $values = array_merge([
            'user' => ['name' => $user->name, 'email' => $user->email],
        ], $extra);

        $rendered = $template->render($values);

        return new self($rendered['subject'], $rendered['body_html']);
    }

    public function build(): self
    {
        return $this->subject($this->renderedSubject)
            ->html($this->renderedBody);
    }
}
