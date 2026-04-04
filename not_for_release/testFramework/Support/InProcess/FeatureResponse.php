<?php

namespace Tests\Support\InProcess;

use PHPUnit\Framework\Assert;

class FeatureResponse
{
    public function __construct(
        public readonly int $statusCode = 200,
        public readonly string $content = '',
        public readonly array $headers = [],
        public readonly array $cookies = [],
    ) {
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $headerName => $value) {
            if (strcasecmp($headerName, $name) === 0) {
                return $value;
            }
        }

        return null;
    }

    public function cookie(string $name): ?string
    {
        return $this->cookies[$name] ?? null;
    }

    public function hiddenFieldValue(string $name): ?string
    {
        $quotedName = preg_quote($name, '/');
        if (preg_match('/<input[^>]*type="hidden"[^>]*name="' . $quotedName . '"[^>]*value="([^"]*)"/i', $this->content, $matches) === 1) {
            return html_entity_decode($matches[1], ENT_QUOTES);
        }

        if (preg_match('/<input[^>]*name="' . $quotedName . '"[^>]*type="hidden"[^>]*value="([^"]*)"/i', $this->content, $matches) === 1) {
            return html_entity_decode($matches[1], ENT_QUOTES);
        }

        return null;
    }

    public function antiSpamFieldName(): ?string
    {
        if (preg_match('/<input[^>]*type="[^"]*"[^>]*id="CAAS"[^>]*name="([^"]+)"/i', $this->content, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/<input[^>]*name="([^"]+)"[^>]*id="CAAS"/i', $this->content, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public function checkedInputValue(string $name): ?string
    {
        $quotedName = preg_quote($name, '/');

        if (preg_match('/<input[^>]*name="' . $quotedName . '"[^>]*value="([^"]*)"[^>]*checked/i', $this->content, $matches) === 1) {
            return html_entity_decode($matches[1], ENT_QUOTES);
        }

        if (preg_match('/<input[^>]*checked[^>]*name="' . $quotedName . '"[^>]*value="([^"]*)"/i', $this->content, $matches) === 1) {
            return html_entity_decode($matches[1], ENT_QUOTES);
        }

        return $this->hiddenFieldValue($name);
    }

    public function formAction(string $formId): ?string
    {
        $form = $this->formMarkup($formId);
        if ($form === null) {
            return null;
        }

        $attributes = $this->extractTagAttributes($this->openingFormTag($form));
        if (isset($attributes['action'])) {
            return html_entity_decode((string) $attributes['action'], ENT_QUOTES);
        }

        return null;
    }

    public function formDefaults(string $formId): array
    {
        $form = $this->formMarkup($formId);
        if ($form === null) {
            return [];
        }

        $defaults = [];

        if (preg_match_all('/<input\b[^>]*>/i', $form, $matches) > 0) {
            foreach ($matches[0] as $inputTag) {
                $attributes = $this->extractTagAttributes($inputTag);
                $name = $attributes['name'] ?? null;
                if ($name === null || isset($attributes['disabled'])) {
                    continue;
                }

                $type = strtolower($attributes['type'] ?? 'text');
                if (in_array($type, ['submit', 'button', 'image', 'file', 'reset'], true)) {
                    continue;
                }

                if (in_array($type, ['checkbox', 'radio'], true) && !isset($attributes['checked'])) {
                    continue;
                }

                $defaults[$name] = html_entity_decode($attributes['value'] ?? '', ENT_QUOTES);
            }
        }

        if (preg_match_all('/<textarea\b([^>]*)>(.*?)<\/textarea>/is', $form, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $textareaMatch) {
                $attributes = $this->extractTagAttributes('<textarea ' . $textareaMatch[1] . '>');
                $name = $attributes['name'] ?? null;
                if ($name === null || isset($attributes['disabled'])) {
                    continue;
                }

                $defaults[$name] = html_entity_decode(trim($textareaMatch[2]), ENT_QUOTES);
            }
        }

        if (preg_match_all('/<select\b([^>]*)>(.*?)<\/select>/is', $form, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $selectMatch) {
                $attributes = $this->extractTagAttributes('<select ' . $selectMatch[1] . '>');
                $name = $attributes['name'] ?? null;
                if ($name === null || isset($attributes['disabled'])) {
                    continue;
                }

                if (preg_match('/<option\b[^>]*value="([^"]*)"[^>]*selected/is', $selectMatch[2], $optionMatch) === 1) {
                    $defaults[$name] = html_entity_decode($optionMatch[1], ENT_QUOTES);
                    continue;
                }

                if (preg_match('/<option\b[^>]*value="([^"]*)"/is', $selectMatch[2], $optionMatch) === 1) {
                    $defaults[$name] = html_entity_decode($optionMatch[1], ENT_QUOTES);
                }
            }
        }

        return $defaults;
    }

    private function formMarkup(string $formId): ?string
    {
        if (preg_match_all('/(<form\b[^>]*>.*?<\/form>)/is', $this->content, $matches) > 0) {
            foreach ($matches[1] as $form) {
                $attributes = $this->extractTagAttributes($this->openingFormTag($form));
                if (($attributes['id'] ?? null) === $formId || ($attributes['name'] ?? null) === $formId) {
                    return $form;
                }
            }
        }

        return null;
    }

    private function openingFormTag(string $form): string
    {
        if (preg_match('/<form\b[^>]*>/i', $form, $matches) === 1) {
            return $matches[0];
        }

        return $form;
    }

    private function extractTagAttributes(string $tag): array
    {
        $attributes = [];

        if (preg_match_all('/([a-zA-Z_:][a-zA-Z0-9:._-]*)(?:\s*=\s*"([^"]*)")?/i', $tag, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $name = strtolower($match[1]);
                $attributes[$name] = $match[2] ?? true;
            }
        }

        return $attributes;
    }

    public function securityToken(): ?string
    {
        if (preg_match("/securityToken:\\s*'([^']+)'/", $this->content, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/name="securityToken" value="([^"]+)"/', $this->content, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public function assertOk(): self
    {
        Assert::assertSame(200, $this->statusCode, 'Expected response status code 200.');

        return $this;
    }

    public function assertStatus(int $statusCode): self
    {
        Assert::assertSame($statusCode, $this->statusCode, sprintf('Expected response status code %d.', $statusCode));

        return $this;
    }

    public function assertSee(string $text): self
    {
        Assert::assertStringContainsString($text, $this->content);

        return $this;
    }

    public function assertHeader(string $name, ?string $value = null): self
    {
        $header = $this->header($name);
        Assert::assertNotNull($header, sprintf('Expected header [%s] to be present.', $name));

        if ($value !== null) {
            Assert::assertSame($value, $header, sprintf('Expected header [%s] to equal [%s].', $name, $value));
        }

        return $this;
    }

    public function isRedirect(): bool
    {
        return in_array($this->statusCode, [301, 302, 303, 307, 308], true) && $this->redirectLocation() !== null;
    }

    public function redirectLocation(): ?string
    {
        return $this->header('Location');
    }

    public function assertRedirect(?string $locationContains = null): self
    {
        Assert::assertContains(
            $this->statusCode,
            [301, 302, 303, 307, 308],
            sprintf('Expected redirect status code, got [%d].', $this->statusCode)
        );

        $location = $this->redirectLocation();
        Assert::assertNotNull($location, 'Expected redirect response to contain a Location header.');

        if ($locationContains !== null) {
            Assert::assertStringContainsString($locationContains, $location);
        }

        return $this;
    }
}
