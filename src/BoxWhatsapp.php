<?php

namespace BoxWhatsapp;

class BoxWhatsapp
{
    private ?string $apiKey = null;
    private ?string $baseUrl = null; // ex: https://your-unipile-host/api
    private ?string $accountId = null;
    private ?string $defaultDest = null; // ex: +243000000000à

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey ?: getenv('WHATSAPP_API_KEY') ?: null;
        $this->baseUrl = $baseUrl ? rtrim($baseUrl, '/') : (getenv('WHATSAPP_BASE_URL') ? rtrim(getenv('WHATSAPP_BASE_URL'), '/') : null);
    }

    // Configuration
    public function setKey(string $key): self
    {
        $this->apiKey = $key;
        return $this;
    }

    public function setDns(string $dns): self
    {
        $this->baseUrl = rtrim($dns, '/');
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

    // Envois
    public function sendMessage(string $message, ?string $dest = null): array
    {
        $this->assertConfigured();
        $accountId = $this->getAccountId();
        $number = $dest ?: $this->defaultDest;
        if (!$number) {
            return $this->errorResult('Aucun destinataire fourni (setDest ou paramètre manquant)');
        }

        $whatsappId = $this->toWhatsappId($number);
        $postData = [
            'account_id'    => $accountId,
            'attendees_ids' => $whatsappId,
            'text'          => $message,
        ];

        return $this->postMultipart('/chats', $postData);
    }

    public function sendMessageGroup(string $message, array $dests): array
    {
        $results = [];
        foreach ($dests as $dest) {
            $results[$dest] = $this->sendMessage($message, $dest);
        }
        return $results;
    }

    // Optionnel: envoi à un groupe via JID (ex: 12345-67890@g.us)
    public function sendMessageToGroup(string $groupJid, string $message): array
    {
        $this->assertConfigured();
        $accountId = $this->getAccountId();
        $postData = [
            'account_id'    => $accountId,
            'attendees_ids' => $groupJid,
            'text'          => $message,
        ];
        return $this->postMultipart('/chats', $postData);
    }

    // Découverte du compte
    public function getAccountId(): string
    {
        if ($this->accountId) {
            return $this->accountId;
        }

        $res = $this->getJson('/accounts');
        if (!$res['success']) {
            throw new \RuntimeException('Impossible de récupérer account_id: ' . ($res['error'] ?? 'erreur inconnue'));
        }

        $data = $res['response'];
        $accounts = $data['items'] ?? $data['data'] ?? $data ?? [];
        if (!is_array($accounts) || empty($accounts)) {
            throw new \RuntimeException('Aucun compte Unipile trouvé');
        }
        $account = $accounts[0];
        $accountId = $account['id'] ?? $account['account_id'] ?? null;
        if (!$accountId) {
            throw new \RuntimeException('account_id introuvable dans la réponse');
        }
        $this->accountId = $accountId;
        return $this->accountId;
    }

    // Helpers HTTP
    private function getJson(string $endpoint): array
    {
        $this->assertConfigured();
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-KEY: ' . $this->apiKey,
            'accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'success'   => false,
                'error'     => "Erreur HTTP $httpCode: " . ($curlError ?: 'Erreur de requête'),
                'http_code' => $httpCode,
                'details'   => $response,
            ];
        }

        return [
            'success'  => true,
            'response' => json_decode($response, true),
        ];
    }

    private function postMultipart(string $endpoint, array $postData): array
    {
        $this->assertConfigured();
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); // multipart/form-data
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-KEY: ' . $this->apiKey,
            'accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            return [
                'success'     => true,
                'message_id'  => $data['id'] ?? $data['message_id'] ?? ($data['data']['id'] ?? null),
                'response'    => $data,
                'http_code'   => $httpCode,
            ];
        }

        $errorData = json_decode($response, true);
        $errorMsg = $errorData['error'] ?? $errorData['message'] ?? $curlError ?? "Erreur HTTP $httpCode";
        return [
            'success'   => false,
            'error'     => $errorMsg,
            'http_code' => $httpCode,
            'details'   => $response,
        ];
    }

    private function toWhatsappId(string $number): string
    {
        $num = ltrim($number, '+');
        return $num . '@s.whatsapp.net';
    }

    private function assertConfigured(): void
    {
        if (!$this->apiKey) {
            throw new \InvalidArgumentException('API key manquante. Utilisez setKey("...") ou définissez WHATSAPP_API_KEY.');
        }
        if (!$this->baseUrl) {
            throw new \InvalidArgumentException('Base URL/DNS manquant. Utilisez setDns("...") ou définissez WHATSAPP_BASE_URL.');
        }
    }

    private function errorResult(string $message): array
    {
        return [
            'success' => false,
            'error'   => $message,
        ];
    }
}