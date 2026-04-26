<?php

namespace CalendarActivities\Scrapers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use CalendarActivities\Contracts\HolidayProvider;
use CalendarActivities\Data\Holiday;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Scrapes the Official Gazette of the Republic of the Philippines:
 *   https://www.officialgazette.gov.ph/nationwide-holidays/{year}/
 */
class OfficialGazetteScraper implements HolidayProvider
{
    private const BASE_URL = 'https://www.officialgazette.gov.ph/nationwide-holidays/';

    private Client $http;

    public function __construct(?Client $http = null)
    {
        $this->http = $http ?? new Client([
            'timeout'         => 15,
            'connect_timeout' => 10,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (compatible; CalendarActivitiesBot/1.0)',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
            ],
            'verify' => true,
        ]);
    }

    /** @return Collection<int, Holiday> */
    public function get(int $year): Collection
    {
        return $this->parse($this->fetchPage($year), $year);
    }

    private function fetchPage(int $year): string
    {
        try {
            $response = $this->http->get(self::BASE_URL . $year . '/');
            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException("Official Gazette returned HTTP {$response->getStatusCode()}");
            }
            return (string) $response->getBody();
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Failed to fetch holidays for {$year}: {$e->getMessage()}", 0, $e);
        }
    }

    private function parse(string $html, int $year): Collection
    {
        $crawler     = new Crawler($html);
        $holidays    = new Collection();
        $currentType = 'Public';

        $content = $crawler->filter('.entry-content, article.post, .post-content, main article')->first();
        if (! $content->count()) {
            $content = $crawler->filter('body');
        }

        $content->children()->each(function (Crawler $node) use (&$currentType, &$holidays, $year) {
            $tag  = $node->nodeName();
            $text = trim($node->text());

            if (in_array($tag, ['h1','h2','h3','h4','h5','strong','p'], true)) {
                $lower = strtolower($text);
                if (str_contains($lower, 'regular holiday'))                                              { $currentType = 'Public';   }
                elseif (str_contains($lower, 'special non-working') || str_contains($lower, 'special (non-working)')) { $currentType = 'Optional'; }
                elseif (str_contains($lower, 'special working day') || str_contains($lower, 'special (working)'))     { $currentType = 'WorkDay';  }
                return;
            }

            if (in_array($tag, ['ul','ol'], true)) {
                $node->filter('li')->each(function (Crawler $li) use (&$holidays, $currentType, $year) {
                    if ($h = $this->parseListItem($li->text(), $year, $currentType)) {
                        $holidays->push($h);
                    }
                });
                return;
            }

            if ($tag === 'table') {
                $node->filter('tr')->each(function (Crawler $tr) use (&$holidays, &$currentType, $year) {
                    $cells = $tr->filter('td, th');
                    if ($cells->count() < 2) return;
                    $first  = trim($cells->eq(0)->text());
                    $second = trim($cells->eq(1)->text());
                    $lower  = strtolower($first . ' ' . $second);
                    if (str_contains($lower, 'regular holiday'))       { $currentType = 'Public';   return; }
                    if (str_contains($lower, 'special non-working'))   { $currentType = 'Optional'; return; }
                    if ($h = $this->parseTableRow($first, $second, $year, $currentType)) {
                        $holidays->push($h);
                    }
                });
            }
        });

        return $holidays->values();
    }

    private function parseListItem(string $raw, int $year, string $type): ?Holiday
    {
        $parts = preg_split('/\s*[–—-]\s*/', trim($raw), 2);
        if (count($parts) < 2) return null;
        $date = $this->parseDate($parts[0], $year);
        if (! $date) return null;
        return $this->makeHoliday($date, $this->cleanName($parts[1]), $type);
    }

    private function parseTableRow(string $dateCell, string $nameCell, int $year, string $type): ?Holiday
    {
        $date = $this->parseDate($dateCell, $year);
        $name = $this->cleanName($nameCell);
        if (! $date || empty($name)) return null;
        return $this->makeHoliday($date, $name, $type);
    }

    private function makeHoliday(string $date, string $name, string $type): Holiday
    {
        return new Holiday(
            date:        $date,
            localName:   $name,
            name:        $name,
            countryCode: 'PH',
            fixed:       $this->isFixed($name),
            global:      true,
            types:       [$type],
        );
    }

    private function parseDate(string $raw, int $year): ?string
    {
        $cleaned   = preg_replace('/\([^)]+\)/', '', $raw);
        $timestamp = strtotime(trim($cleaned) . " {$year}");
        if ($timestamp === false || (int) date('Y', $timestamp) !== $year) return null;
        return date('Y-m-d', $timestamp);
    }

    private function cleanName(string $raw): string
    {
        return trim(preg_replace(['/\*+/', '/\s+/'], ['', ' '], $raw), " \t\n\r\0\x0B-–—");
    }

    private function isFixed(string $name): bool
    {
        $movable = ['maundy','good friday','black saturday','easter','eid','fitr','adha','heroes day'];
        $lower   = strtolower($name);
        foreach ($movable as $kw) {
            if (str_contains($lower, $kw)) return false;
        }
        return true;
    }
}
