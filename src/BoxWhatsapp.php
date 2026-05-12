<?php

declare(strict_types=1);

namespace BoxWhatsapp;

/**
 * BoxWhatsapp - Lightweight WhatsApp messenger via Unipile API
 *
 * @package BoxWhatsapp
 * @author Exauce Stan Malka
 * @license MIT
 */
class BoxWhatsapp
{
    private ?string $apiKey = null;
    private ?string $baseUrl = null;
    private ?string $accountId = null;
    private ?string $defaultDest = null;

    private const CONNECTION_TIMEOUT = 10;
    private const REQUEST_TIMEOUT = 15;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey ?: (getenv('WHATSAPP_API_KEY') ?: null);
        $this->baseUrl = $baseUrl ? $this->normalizeBaseUrl($baseUrl) : $this->getBaseUrlFromEnv();
    }

    private function normalizeBaseUrl(string $url): string
    {
        return rtrim($url, '/');
    }

    private function getBaseUrlFromEnv(): ?string
    {
        $envUrl = getenv('WHATSAPP_BASE_URL');
        return $envUrl ? $this->normalizeBaseUrl($envUrl) : null;
    }

    public function setKey(string $key): self
    {
        $this->apiKey = $key;
        return $this;
    }

    public function setDns(string $dns): self
    {
        $this->baseUrl = $this->normalizeBaseUrl($dns);
        return $this;
    }

    public function setAccountId(string $accountId): self
    {
        $this->accountId = $accountId;
        return $this;
    }

    public function setDest(string $phoneNumber): self
    {
        $this->defaultDest = $phoneNumber;
        return $this;
    }

    /**
     * Send a text message to a single recipient.
     *
     * @param string $message Message content
     * @param string|null $dest Recipient phone number (international format)
     * @return array Response with success flag, message_id, response data and http_code
     */
    public function sendMessage(string $message, ?string $dest = null): array
    {
        $this->assertConfigured();
        $accountId = $this->getAccountId();
        $recipient = $dest ?: $this->defaultDest;

        if (!$recipient) {
            return $this->errorResult('No recipient provided. Use setDest() or pass $dest parameter.');
        }

        $whatsappId = $this->toWhatsappId($recipient);
        $payload = [
            'account_id'    => $accountId,
            'attendees_ids' => [$whatsappId],
            'text'          => $message,
        ];

        return $this->postMultipart('/api/v1/chats', $payload);
    }

    /**
     * Send the same message to multiple recipients.
     *
     * @param string $message Message content
     * @param array $dests Array of phone numbers
     * @return array Associative array with phone numbers as keys and send results as values
     */
    public function sendMessageGroup(string $message, array $dests): array
    {
        $results = [];
        foreach ($dests as $dest) {
            $results[$dest] = $this->sendMessage($message, $dest);
        }
        return $results;
    }

    /**
     * Send a message to a WhatsApp group using its JID.
     *
     * @param string $groupJid Group identifier (e.g., 12345-67890@g.us)
     * @param string $message Message content
     * @return array Response with success flag, message_id, response data and http_code
     */
    public function sendMessageToGroup(string $groupJid, string $message): array
    {
        $this->assertConfigured();
        $accountId = $this->getAccountId();
        $payload = [
            'account_id'    => $accountId,
            'attendees_ids' => [$groupJid],
            'text'          => $message,
        ];
        return $this->postMultipart('/api/v1/chats', $payload);
    }

    /**
     * Retrieve or auto-discover the account ID.
     *
     * @throws \RuntimeException If auto-discovery fails and no account ID is set
     */
    public function getAccountId(): string
    {
        if ($this->accountId !== null) {
            return $this->accountId;
        }

        $response = $this->getJson('/api/v1/me');
        if (!$response['success']) {
            throw new \RuntimeException(
                'Unable to auto-discover account_id. Please set it manually using setAccountId(). Error: ' .
                ($response['error'] ?? 'unknown error')
            );
        }

        $data = $response['response'];
        $accountId = $data['id'] ?? $data['account_id'] ?? null;

        if (!$accountId) {
            throw new \RuntimeException('account_id not found in /me response.');
        }

        $this->accountId = $accountId;
        return $this->accountId;
    }

    private function getJson(string $endpoint): array
    {
        $this->assertConfigured();
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-KEY: ' . $this->apiKey,
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::REQUEST_TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTION_TIMEOUT);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($curlError !== '') {
            return [
                'success' => false,
                'error' => 'cURL error: ' . $curlError,
                'http_code' => $httpCode ?: 0,
                'details' => null,
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => "HTTP {$httpCode} error",
                'http_code' => $httpCode,
                'details' => $response,
            ];
        }

        $decoded = json_decode($response, true);
        return [
            'success' => true,
            'response' => $decoded,
        ];
    }

    private function postMultipart(string $endpoint, array $postData): array
    {
        $this->assertConfigured();
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-KEY: ' . $this->apiKey,
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::REQUEST_TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTION_TIMEOUT);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($curlError !== '') {
            return [
                'success' => false,
                'error' => 'cURL error: ' . $curlError,
                'http_code' => 0,
                'details' => null,
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'message_id' => $data['id'] ?? $data['message_id'] ?? ($data['data']['id'] ?? null),
                'response' => $data,
                'http_code' => $httpCode,
            ];
        }

        $errorData = json_decode($response, true);
        $errorMsg = $errorData['error'] ?? $errorData['message'] ?? $curlError ?? "HTTP {$httpCode} error";

        return [
            'success' => false,
            'error' => $errorMsg,
            'http_code' => $httpCode,
            'details' => $response,
        ];
    }

    private function toWhatsappId(string $number): string
    {
        $cleanNumber = ltrim($number, '+');
        return $cleanNumber . '@s.whatsapp.net';
    }

    private function assertConfigured(): void
    {
        if ($this->apiKey === null || $this->apiKey === '') {
            throw new \InvalidArgumentException(
                'Missing API key. Use setKey() or define WHATSAPP_API_KEY environment variable.'
            );
        }
        if ($this->baseUrl === null || $this->baseUrl === '') {
            throw new \InvalidArgumentException(
                'Missing Base URL. Use setDns() or define WHATSAPP_BASE_URL environment variable.'
            );
        }
    }

    private function errorResult(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
        ];
    }
}