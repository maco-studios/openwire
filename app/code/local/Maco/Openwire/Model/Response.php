<?php

declare(strict_types=1);

/**
 * Copyright (c) 2025 MACO
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/maco-studios/openwire
 */

/**
 * Normalized server response builder for OpenWire
 *
 * This class builds standardized JSON responses with html, state, and effects.
 */
class Maco_Openwire_Model_Response
{
    private string $html = '';
    private array $state = [];
    private array $effects = [];
    private bool $success = true;
    private ?string $error = null;

    /**
     * Set the HTML content for the response
     */
    public function setHtml(string $html): self
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Set the component state for the response
     */
    public function setState(array $state): self
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Add an effect to the response
     */
    public function addEffect(string $type, array $data = []): self
    {
        $this->effects[] = [
            'type' => $type,
            'data' => $data
        ];
        return $this;
    }

    /**
     * Add multiple effects to the response
     */
    public function addEffects(array $effects): self
    {
        foreach ($effects as $effect) {
            if (isset($effect['type'])) {
                $this->addEffect($effect['type'], $effect['data'] ?? []);
            }
        }
        return $this;
    }

    /**
     * Mark response as error
     */
    public function setError(string $error): self
    {
        $this->success = false;
        $this->error = $error;
        return $this;
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use addEffects() instead
     */
    public function setEffects(?array $effects): self
    {
        $this->effects = $effects ?? [];
        return $this;
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use setError() instead
     */
    public function setErrors(?array $errors): self
    {
        if (!empty($errors)) {
            $this->setError(implode(', ', $errors));
        }
        return $this;
    }

    /**
     * Add a notification effect
     */
    public function addNotification(string $message, string $type = 'info'): self
    {
        return $this->addEffect('notify', [
            'message' => $message,
            'type' => $type
        ]);
    }

    /**
     * Add a redirect effect
     */
    public function addRedirect(string $url, int $delay = 0): self
    {
        return $this->addEffect('redirect', [
            'url' => $url,
            'delay' => $delay
        ]);
    }

    /**
     * Add a registered effect (for component lifecycle)
     */
    public function addRegistered(string $componentId, ?string $serverId = null): self
    {
        $data = ['id' => $componentId];
        if ($serverId) {
            $data['server_id'] = $serverId;
        }

        return $this->addEffect('registered', $data);
    }

    /**
     * Add a destroyed effect (for component cleanup)
     */
    public function addDestroyed(string $componentId): self
    {
        return $this->addEffect('destroyed', [
            'id' => $componentId
        ]);
    }

    /**
     * Build and return the response array
     */
    public function toArray(): array
    {
        $response = [
            'success' => $this->success,
            'html' => $this->html,
            'state' => $this->state,
            'effects' => $this->effects
        ];

        if (!$this->success && $this->error) {
            $response['error'] = $this->error;
        }

        return $response;
    }

    /**
     * Build and return the response as JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Create an error response
     */
    public static function error(string $message, int $code = 400): self
    {
        $response = new self();
        return $response->setError($message);
    }

    /**
     * Create a success response
     */
    public static function success(string $html = '', array $state = [], array $effects = []): self
    {
        $response = new self();
        $response->setHtml($html);
        $response->setState($state);
        $response->addEffects($effects);

        return $response;
    }

    /**
     * Check if response has effects of a specific type
     */
    public function hasEffect(string $type): bool
    {
        foreach ($this->effects as $effect) {
            if (($effect['type'] ?? null) === $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all effects of a specific type
     */
    public function getEffects(string $type): array
    {
        $filtered = [];
        foreach ($this->effects as $effect) {
            if (($effect['type'] ?? null) === $type) {
                $filtered[] = $effect;
            }
        }
        return $filtered;
    }
}
