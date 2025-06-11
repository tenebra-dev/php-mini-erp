<?php
namespace dto\email;

class SendEmailDTO {
    public string $to;
    public string $name;
    public string $subject;
    public string $body;
    public string $altBody;

    public function __construct(array $data) {
        $this->to = $data['to'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->subject = $data['subject'] ?? '';
        $this->body = $data['body'] ?? '';
        $this->altBody = $data['altBody'] ?? '';
    }

    public function isValid(): bool {
        return !empty($this->to) && !empty($this->subject) && !empty($this->body);
    }
}