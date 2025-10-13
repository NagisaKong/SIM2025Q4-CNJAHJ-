<?php

namespace App\Core;

class Response
{
    public function __construct(private int $status = 200, private array $headers = [], private string $content = '')
    {
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }
        echo $this->content;
    }
}
