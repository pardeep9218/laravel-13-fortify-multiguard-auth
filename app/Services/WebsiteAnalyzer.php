<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteAnalyzer
{
    private const MAX_RESPONSE_BYTES = 2 * 1024 * 1024;

    /**
     * Fetch and extract information from a website's homepage.
     *
     * This does not crawl every page yet.
     */
    public function analyze(string $url): array
    {
        $target = $this->validateAndResolveUrl($url);

        $host = $target['host'];
        $port = $target['port'];
        $ip = $target['ip'];
        $scheme = $target['scheme'];

        $temporaryFile = tempnam(sys_get_temp_dir(), 'website-analysis-');

        if ($temporaryFile === false) {
            throw new RuntimeException('Unable to create a temporary file.');
        }

        try {
            $client = new Client([
                'connect_timeout' => 5,
                'timeout' => 15,
                'allow_redirects' => false,
                'http_errors' => false,
                'verify' => true,
            ]);

            $resolveEntry = sprintf(
                '%s:%d:%s',
                $host,
                $port,
                str_contains($ip, ':') ? '[' . $ip . ']' : $ip
            );

            $response = $client->request('GET', $url, [
                'sink' => $temporaryFile,

                'headers' => [
                    'User-Agent' => 'AIChatbotBuilder/1.0',
                    'Accept' => 'text/html,application/xhtml+xml',
                ],

                // Pin the request to the IP that was validated.
                // TLS certificate verification remains enabled.
                'curl' => [
                    CURLOPT_RESOLVE => [$resolveEntry],
                    CURLOPT_NOPROGRESS => false,
                ],

                // Reject oversized responses before downloading the body.
                'on_headers' => function ($response) {
                    $contentLength = $response->getHeaderLine(
                        'Content-Length'
                    );

                    if (
                        $contentLength !== ''
                        && (
                            ! ctype_digit($contentLength)
                            || (int) $contentLength > self::MAX_RESPONSE_BYTES
                        )
                    ) {
                        throw new RuntimeException(
                            'The website response exceeds the allowed size.'
                        );
                    }

                    $contentType = strtolower(
                        $response->getHeaderLine('Content-Type')
                    );

                    if (
                        $contentType !== ''
                        && ! str_contains($contentType, 'text/html')
                        && ! str_contains($contentType, 'application/xhtml+xml')
                    ) {
                        throw new RuntimeException(
                            'The URL did not return an HTML page.'
                        );
                    }
                },

                // Also limit chunked responses without Content-Length.
                'progress' => function (
                    $downloadTotal,
                    $downloadedBytes
                ) {
                    if ($downloadedBytes > self::MAX_RESPONSE_BYTES) {
                        throw new RuntimeException(
                            'The website response exceeds the allowed size.'
                        );
                    }
                },
            ]);

            if ($response->getStatusCode() >= 300) {
                throw new RuntimeException(
                    'The website returned HTTP status '
                    . $response->getStatusCode()
                    . '. Redirects are not followed automatically.'
                );
            }

            $html = file_get_contents($temporaryFile);

            if ($html === false || trim($html) === '') {
                throw new RuntimeException(
                    'The website returned an empty response.'
                );
            }

            return $this->extractPageData($html, $url);
        } catch (GuzzleException $exception) {
            throw new RuntimeException(
                'Unable to fetch the website. Check that the URL is accessible.',
                previous: $exception
            );
        } finally {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    /**
     * Validate the URL and resolve its hostname to a public IP address.
     */
    private function validateAndResolveUrl(string $url): array
    {
        if (
            filter_var($url, FILTER_VALIDATE_URL) === false
            || strlen($url) > 2048
        ) {
            throw new RuntimeException('Please provide a valid website URL.');
        }

        $parts = parse_url($url);

        if ($parts === false) {
            throw new RuntimeException('Invalid website URL.');
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower(rtrim($parts['host'] ?? '', '.'));

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException('Only HTTP and HTTPS URLs are allowed.');
        }

        if (
            $host === ''
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            throw new RuntimeException('The website URL is not allowed.');
        }

        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        // Avoid arbitrary ports and services.
        if (
            ($scheme === 'https' && $port !== 443)
            || ($scheme === 'http' && $port !== 80)
        ) {
            throw new RuntimeException(
                'Only standard HTTP and HTTPS ports are allowed.'
            );
        }

        $ipAddresses = [];

        // Handle IP literals separately from hostnames.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ipAddresses[] = $host;
        } else {
            // Reject unusual host formats and single-label internal hosts.
            if (
                ! str_contains($host, '.')
                || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            ) {
                throw new RuntimeException('The website hostname is not allowed.');
            }

            $records = dns_get_record($host, DNS_A | DNS_AAAA);

            if ($records === false || $records === []) {
                throw new RuntimeException(
                    'The website hostname could not be resolved.'
                );
            }

            foreach ($records as $record) {
                if (! empty($record['ip'])) {
                    $ipAddresses[] = $record['ip'];
                }

                if (! empty($record['ipv6'])) {
                    $ipAddresses[] = $record['ipv6'];
                }
            }
        }

        $ipAddresses = array_values(array_unique($ipAddresses));

        if ($ipAddresses === []) {
            throw new RuntimeException(
                'The website hostname has no usable IP address.'
            );
        }

        // Reject the entire hostname if any answer is not public.
        foreach ($ipAddresses as $ip) {
            if (
                filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                ) === false
            ) {
                throw new RuntimeException(
                    'The website resolves to a private or reserved IP address.'
                );
            }
        }

        return [
            'host' => $host,
            'port' => $port,
            'scheme' => $scheme,
            'ip' => $ipAddresses[0],
        ];
    }

    /**
     * Extract useful information from the HTML document.
     */
    private function extractPageData(string $html, string $url): array
    {
        $previousSetting = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument();

            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8">' . $html,
                LIBXML_NONET | LIBXML_COMPACT
            );

            if (! $loaded) {
                throw new RuntimeException(
                    'Unable to parse the website HTML.'
                );
            }

            $xpath = new DOMXPath($document);

            $title = $this->firstText(
                $xpath,
                '//title'
            );

            $description = $this->firstText(
                $xpath,
                '//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="description"]/@content'
            );

            $headings = [];

            foreach ($xpath->query('//h1 | //h2 | //h3') as $heading) {
                $text = $this->cleanText($heading->textContent);

                if ($text !== '') {
                    $headings[] = [
                        'level' => strtolower($heading->nodeName),
                        'text' => $text,
                    ];
                }

                if (count($headings) >= 100) {
                    break;
                }
            }

            // Remove elements that don't contribute useful page text.
            foreach (
                $xpath->query(
                    '//script | //style | //noscript | //svg | //iframe | //template'
                ) as $node
            ) {
                $node->parentNode?->removeChild($node);
            }

            $bodyText = $this->cleanText(
                $xpath->query('//body')->item(0)?->textContent ?? ''
            );

            // Keep the extracted content bounded for later AI processing.
            $bodyText = Str::limit($bodyText, 30000, '');

            $links = [];
            $baseHost = strtolower(
                parse_url($url, PHP_URL_HOST) ?? ''
            );

            foreach ($xpath->query('//a[@href]') as $anchor) {
                if (! $anchor instanceof DOMElement) {
                    continue;
                }

                $href = trim($anchor->getAttribute('href'));

                if (
                    $href === ''
                    || str_starts_with($href, '#')
                    || preg_match('/^(javascript|mailto|tel|data):/i', $href)
                ) {
                    continue;
                }

                $absoluteUrl = $this->makeAbsoluteUrl($href, $url);

                if ($absoluteUrl === null) {
                    continue;
                }

                $linkHost = strtolower(
                    parse_url($absoluteUrl, PHP_URL_HOST) ?? ''
                );

                if ($linkHost !== $baseHost) {
                    continue;
                }

                $links[$absoluteUrl] = [
                    'url' => $absoluteUrl,
                    'text' => $this->cleanText($anchor->textContent),
                ];

                if (count($links) >= 50) {
                    break;
                }
            }

            return [
                'title' => $title,
                'description' => $description,
                'headings' => $headings,
                'text' => $bodyText,
                'internal_links' => array_values($links),
                'source_url' => $url,
                'analyzed_at' => now()->toIso8601String(),
            ];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousSetting);
        }
    }

    private function firstText(DOMXPath $xpath, string $query): ?string
    {
        $node = $xpath->query($query)?->item(0);

        if ($node === null) {
            return null;
        }

        $text = $this->cleanText(
            $node instanceof DOMElement
                ? ($node->getAttribute('content') ?: $node->textContent)
                : $node->nodeValue
        );

        return $text !== '' ? $text : null;
    }

    private function cleanText(string $text): string
    {
        return trim(
            preg_replace('/\s+/u', ' ', $text) ?? ''
        );
    }

    /**
     * Resolve a link without fetching it.
     */
    private function makeAbsoluteUrl(string $href, string $baseUrl): ?string
    {
        if (preg_match('/[\x00-\x20]/', $href)) {
            return null;
        }

        if (str_starts_with($href, '//')) {
            $href = (parse_url($baseUrl, PHP_URL_SCHEME) ?? 'https')
                . ':'
                . $href;
        } elseif (! preg_match('~^https?://~i', $href)) {
            $base = parse_url($baseUrl);

            if (! is_array($base) || empty($base['host'])) {
                return null;
            }

            $origin = ($base['scheme'] ?? 'https')
                . '://'
                . $base['host'];

            if (isset($base['port'])) {
                $origin .= ':' . $base['port'];
            }

            if (str_starts_with($href, '/')) {
                $href = $origin . $href;
            } else {
                $basePath = $base['path'] ?? '/';
                $directory = rtrim(
                    substr($basePath, 0, strrpos($basePath, '/') + 1),
                    '/'
                );

                $href = $origin . $directory . '/' . $href;
            }
        }

        $parts = parse_url($href);

        if (
            $parts === false
            || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])
        ) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return strtolower($parts['scheme'])
            . '://'
            . strtolower($parts['host'])
            . $path
            . $query;
    }
}