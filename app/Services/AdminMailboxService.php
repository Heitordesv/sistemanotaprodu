<?php

namespace App\Services;

use RuntimeException;

class AdminMailboxService
{
    private array $config;

    public function __construct()
    {
        $this->config = (array) config('mail.imap', []);
    }

    public function inbox(int $limit = 60, ?string $search = null): array
    {
        $imap = $this->connect();

        try {
            $uids = imap_sort($imap, SORTARRIVAL, true, SE_UID);

            if (!is_array($uids)) {
                return [];
            }

            $search = $search !== null ? mb_strtolower(trim($search)) : null;
            $messages = [];
            $uids = array_slice($uids, 0, max($limit * 5, 300));

            foreach ($uids as $uid) {
                $overviewList = imap_fetch_overview($imap, (string) $uid, FT_UID);
                $overview = is_array($overviewList) ? ($overviewList[0] ?? null) : null;

                if (!$overview) {
                    continue;
                }

                $from = $this->parseAddress((string) ($overview->from ?? ''));
                $subject = $this->decodeHeader((string) ($overview->subject ?? '(Sem assunto)'));

                if ($search !== null && $search !== '') {
                    $haystack = mb_strtolower(implode(' ', [
                        $subject,
                        $from['name'],
                        $from['email'],
                    ]));

                    if (!str_contains($haystack, $search)) {
                        continue;
                    }
                }

                $timestamp = isset($overview->udate)
                    ? (int) $overview->udate
                    : (strtotime((string) ($overview->date ?? '')) ?: null);

                $messages[] = [
                    'uid' => (int) $uid,
                    'subject' => $subject !== '' ? $subject : '(Sem assunto)',
                    'from_name' => $from['name'] ?: $from['email'],
                    'from_email' => $from['email'],
                    'date' => $timestamp,
                    'seen' => !empty($overview->seen),
                    'answered' => !empty($overview->answered),
                    'flagged' => !empty($overview->flagged),
                    'size' => (int) ($overview->size ?? 0),
                ];

                if (count($messages) >= $limit) {
                    break;
                }
            }

            return $messages;
        } finally {
            imap_close($imap);
        }
    }

    public function message(int $uid): array
    {
        if ($uid <= 0) {
            throw new RuntimeException('Mensagem de e-mail inválida.');
        }

        $imap = $this->connect();

        try {
            $messageNumber = imap_msgno($imap, $uid);

            if (!$messageNumber) {
                throw new RuntimeException('Este e-mail não foi encontrado na caixa de entrada.');
            }

            $overviewList = imap_fetch_overview($imap, (string) $uid, FT_UID);
            $overview = is_array($overviewList) ? ($overviewList[0] ?? null) : null;
            $header = imap_headerinfo($imap, $messageNumber);
            $structure = imap_fetchstructure($imap, $uid, FT_UID);

            if (!$structure) {
                throw new RuntimeException('Não foi possível interpretar o conteúdo deste e-mail.');
            }

            $bodies = $this->extractBodies($imap, $uid, $structure);
            $body = trim($bodies['plain']);

            if ($body === '' && trim($bodies['html']) !== '') {
                $body = $this->htmlToText($bodies['html']);
            }

            if ($body === '') {
                $raw = imap_body($imap, $uid, FT_UID | FT_PEEK);
                $body = $this->decodeTransfer((string) $raw, (int) ($structure->encoding ?? 0));
                $body = $this->toUtf8($body, $this->charsetFromPart($structure));
            }

            $from = $this->addressFromHeader($header && isset($header->from) ? $header->from : null);
            $to = $this->formatAddressList($header && isset($header->to) ? $header->to : null);
            $cc = $this->formatAddressList($header && isset($header->cc) ? $header->cc : null);
            $timestamp = isset($overview->udate)
                ? (int) $overview->udate
                : (strtotime((string) ($overview->date ?? '')) ?: null);

            return [
                'uid' => $uid,
                'subject' => $this->decodeHeader((string) ($overview->subject ?? '(Sem assunto)')) ?: '(Sem assunto)',
                'from_name' => $from['name'] ?: $from['email'],
                'from_email' => $from['email'],
                'to' => $to,
                'cc' => $cc,
                'date' => $timestamp,
                'body' => mb_substr(trim($body), 0, 350000),
                'seen' => !empty($overview->seen),
                'attachments' => $this->collectAttachments($structure),
            ];
        } finally {
            imap_close($imap);
        }
    }

    private function connect()
    {
        if (!function_exists('imap_open')) {
            throw new RuntimeException(
                'A extensão PHP IMAP não está habilitada no servidor. Habilite a extensão imap para visualizar a caixa de entrada.'
            );
        }

        $username = trim((string) ($this->config['username'] ?? ''));
        $password = (string) ($this->config['password'] ?? '');
        $host = $this->resolveHost();
        $port = (int) ($this->config['port'] ?? 993);
        $folder = trim((string) ($this->config['folder'] ?? 'INBOX')) ?: 'INBOX';
        $encryption = mb_strtolower(trim((string) ($this->config['encryption'] ?? 'ssl')));
        $validateCertificate = filter_var(
            $this->config['validate_cert'] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        $validateCertificate = $validateCertificate ?? true;

        if ($username === '' || $password === '') {
            throw new RuntimeException(
                'Usuário ou senha do e-mail não estão configurados. A caixa de entrada reutiliza MAIL_USERNAME e MAIL_PASSWORD do envio.'
            );
        }

        if ($host === '') {
            throw new RuntimeException('Servidor IMAP não configurado. Defina MAIL_IMAP_HOST no ambiente.');
        }

        $flags = '/imap';

        if ($encryption === 'ssl') {
            $flags .= '/ssl';
        } elseif ($encryption === 'tls') {
            $flags .= '/tls';
        } elseif ($encryption === 'none' || $encryption === 'notls') {
            $flags .= '/notls';
        }

        if (!$validateCertificate) {
            $flags .= '/novalidate-cert';
        }

        $host = str_replace(['{', '}'], '', $host);
        $folder = str_replace(['{', '}'], '', $folder);
        $mailbox = sprintf('{%s:%d%s}%s', $host, $port, $flags, $folder);

        $imap = @imap_open($mailbox, $username, $password, OP_READONLY, 1);

        if ($imap === false) {
            $detail = trim((string) imap_last_error());
            $message = 'Não foi possível conectar à caixa de entrada pelo IMAP.';

            if ($detail !== '') {
                $message .= ' ' . $detail;
            }

            throw new RuntimeException($message);
        }

        return $imap;
    }

    private function resolveHost(): string
    {
        $host = trim((string) ($this->config['host'] ?? ''));

        if ($host !== '') {
            return $host;
        }

        $smtpHost = trim((string) config('mail.mailers.smtp.host', ''));

        if (str_starts_with($smtpHost, 'smtp.')) {
            return 'imap.' . substr($smtpHost, 5);
        }

        return $smtpHost;
    }

    private function parseAddress(string $value): array
    {
        if ($value === '') {
            return ['name' => '', 'email' => ''];
        }

        $parsed = imap_rfc822_parse_adrlist($value, '');
        $address = is_array($parsed) ? ($parsed[0] ?? null) : null;

        if (!$address) {
            return ['name' => $this->decodeHeader($value), 'email' => ''];
        }

        $email = '';
        if (!empty($address->mailbox) && !empty($address->host) && $address->host !== '.SYNTAX-ERROR.') {
            $email = $address->mailbox . '@' . $address->host;
        }

        return [
            'name' => $this->decodeHeader((string) ($address->personal ?? '')),
            'email' => $email,
        ];
    }

    private function addressFromHeader($addresses): array
    {
        if (!is_array($addresses) || empty($addresses)) {
            return ['name' => '', 'email' => ''];
        }

        $address = $addresses[0];
        $email = '';

        if (!empty($address->mailbox) && !empty($address->host)) {
            $email = $address->mailbox . '@' . $address->host;
        }

        return [
            'name' => $this->decodeHeader((string) ($address->personal ?? '')),
            'email' => $email,
        ];
    }

    private function formatAddressList($addresses): string
    {
        if (!is_array($addresses)) {
            return '';
        }

        $formatted = [];

        foreach ($addresses as $address) {
            $email = '';
            if (!empty($address->mailbox) && !empty($address->host)) {
                $email = $address->mailbox . '@' . $address->host;
            }

            $name = $this->decodeHeader((string) ($address->personal ?? ''));

            if ($name !== '' && $email !== '') {
                $formatted[] = $name . ' <' . $email . '>';
            } elseif ($email !== '') {
                $formatted[] = $email;
            } elseif ($name !== '') {
                $formatted[] = $name;
            }
        }

        return implode(', ', $formatted);
    }

    private function decodeHeader(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $parts = @imap_mime_header_decode($value);

        if (!is_array($parts)) {
            return trim($value);
        }

        $decoded = '';

        foreach ($parts as $part) {
            $text = (string) ($part->text ?? '');
            $charset = (string) ($part->charset ?? 'default');
            $decoded .= $this->toUtf8($text, $charset);
        }

        return trim($decoded);
    }

    private function extractBodies($imap, int $uid, $structure, string $section = ''): array
    {
        $result = ['plain' => '', 'html' => ''];

        if (!$structure) {
            return $result;
        }

        if (!empty($structure->parts) && is_array($structure->parts)) {
            foreach ($structure->parts as $index => $part) {
                $partSection = $section === ''
                    ? (string) ($index + 1)
                    : $section . '.' . ($index + 1);

                $child = $this->extractBodies($imap, $uid, $part, $partSection);

                if ($child['plain'] !== '') {
                    $result['plain'] .= ($result['plain'] !== '' ? "\n\n" : '') . $child['plain'];
                }

                if ($child['html'] !== '') {
                    $result['html'] .= ($result['html'] !== '' ? "\n" : '') . $child['html'];
                }
            }

            return $result;
        }

        if ($this->partHasFilename($structure)) {
            return $result;
        }

        $type = (int) ($structure->type ?? 0);
        $subtype = mb_strtoupper((string) ($structure->subtype ?? 'PLAIN'));

        if ($type !== 0 || !in_array($subtype, ['PLAIN', 'HTML'], true)) {
            return $result;
        }

        $raw = $section === ''
            ? imap_body($imap, $uid, FT_UID | FT_PEEK)
            : imap_fetchbody($imap, $uid, $section, FT_UID | FT_PEEK);

        $decoded = $this->decodeTransfer((string) $raw, (int) ($structure->encoding ?? 0));
        $decoded = $this->toUtf8($decoded, $this->charsetFromPart($structure));

        if ($subtype === 'HTML') {
            $result['html'] = $decoded;
        } else {
            $result['plain'] = $decoded;
        }

        return $result;
    }

    private function decodeTransfer(string $value, int $encoding): string
    {
        if ($encoding === 3) {
            $decoded = base64_decode($value, true);
            return $decoded !== false ? $decoded : $value;
        }

        if ($encoding === 4) {
            return quoted_printable_decode($value);
        }

        return $value;
    }

    private function charsetFromPart($part): string
    {
        foreach (['parameters', 'dparameters'] as $property) {
            if (empty($part->{$property}) || !is_array($part->{$property})) {
                continue;
            }

            foreach ($part->{$property} as $parameter) {
                if (mb_strtoupper((string) ($parameter->attribute ?? '')) === 'CHARSET') {
                    return (string) ($parameter->value ?? 'UTF-8');
                }
            }
        }

        return 'UTF-8';
    }

    private function toUtf8(string $value, string $charset): string
    {
        $charset = trim($charset);

        if ($value === '' || $charset === '' || in_array(mb_strtoupper($charset), ['DEFAULT', 'UTF-8', 'US-ASCII'], true)) {
            return $value;
        }

        if (function_exists('mb_convert_encoding')) {
            try {
                return mb_convert_encoding($value, 'UTF-8', $charset);
            } catch (\Throwable $e) {
                // Mantém o conteúdo original quando o charset informado é inválido.
            }
        }

        if (function_exists('iconv')) {
            $converted = @iconv($charset, 'UTF-8//IGNORE', $value);
            if ($converted !== false) {
                return $converted;
            }
        }

        return $value;
    }

    private function htmlToText(string $html): string
    {
        $html = preg_replace('/<(br\s*\/?|\/p|\/div|\/li|\/tr|\/h[1-6])\s*>/i', "\n", $html) ?? $html;
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\r\n?|\n/", "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function collectAttachments($structure, string $section = ''): array
    {
        if (!$structure) {
            return [];
        }

        $attachments = [];

        if (!empty($structure->parts) && is_array($structure->parts)) {
            foreach ($structure->parts as $index => $part) {
                $partSection = $section === ''
                    ? (string) ($index + 1)
                    : $section . '.' . ($index + 1);

                $attachments = array_merge(
                    $attachments,
                    $this->collectAttachments($part, $partSection)
                );
            }

            return $attachments;
        }

        $filename = $this->filenameFromPart($structure);

        if ($filename !== '') {
            $attachments[] = [
                'name' => $this->decodeHeader($filename),
                'size' => (int) ($structure->bytes ?? 0),
                'section' => $section,
            ];
        }

        return $attachments;
    }

    private function partHasFilename($part): bool
    {
        return $this->filenameFromPart($part) !== '';
    }

    private function filenameFromPart($part): string
    {
        foreach (['dparameters', 'parameters'] as $property) {
            if (empty($part->{$property}) || !is_array($part->{$property})) {
                continue;
            }

            foreach ($part->{$property} as $parameter) {
                $attribute = mb_strtoupper((string) ($parameter->attribute ?? ''));
                if (in_array($attribute, ['FILENAME', 'NAME'], true)) {
                    return (string) ($parameter->value ?? '');
                }
            }
        }

        return '';
    }
}