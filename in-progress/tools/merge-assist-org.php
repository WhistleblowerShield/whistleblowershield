<?php
/**
 * Merges normalized batch files, dropping records already seeded in the matrix.
 * Output: normalized/US-all-new-normalized.json
 *
 * Run: php merge-assist-org.php
 */

declare(strict_types=1);

const NORMALIZED_DIR = __DIR__ . '/normalized/';
const OUT_FILE       = NORMALIZED_DIR . 'US-all-new-normalized.json';

// URLs already seeded in matrix-assist-orgs.php — compare after rtrim('/').
const MATRIX_URLS = [
    'https://whistleblower.org',
    'https://www.whistleblowers.org',
    'https://whistlebloweraid.org',
    'https://www.pogo.org',
    'https://www.taf.org',
    'https://www.whistleblowersofamerica.org',
    'https://whistleblowingnetwork.org/Home',
    'https://www.nelp.org',
    'https://www.nela.org',
    'https://www.lsc.gov',
    'https://www.nlada.org',
    'https://www.americanbar.org/groups/legal_services/',
    'https://peer.org',
    'https://thesignalsnetwork.org',
];

$matrix_norm = array_map(fn(string $u) => rtrim($u, '/'), MATRIX_URLS);

$files = glob(NORMALIZED_DIR . 'US-[0-9]*-normalized.json');
sort($files);

$seen    = [];  // normalized URLs already added to output (dedup within batches)
$records = [];
$skipped = [];

foreach ($files as $path) {
    $batch = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    foreach ($batch['records'] ?? [] as $record) {
        $url  = rtrim((string)($record['official_homepage_url'] ?? ''), '/');
        $name = $record['official_name'] ?? $url;

        if (in_array($url, $matrix_norm, true)) {
            $skipped[] = "MATRIX  {$name}";
            continue;
        }
        if (in_array($url, $seen, true)) {
            $skipped[] = "DUP     {$name}";
            continue;
        }

        $seen[]    = $url;
        $records[] = $record;
    }
}

$out = [
    'meta' => [
        'merged_by'      => 'merge-assist-org.php',
        'merged_date'    => date('Y-m-d'),
        'schema_version' => 'starting-point-refactor',
        'record_count'   => count($records),
        'note'           => 'New records only — matrix-seeded orgs excluded.',
    ],
    'records' => $records,
];

file_put_contents(OUT_FILE, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");

echo "WROTE: " . OUT_FILE . "  (" . count($records) . " new records)\n";
if (!empty($skipped)) {
    echo "Skipped:\n";
    foreach ($skipped as $line) echo "  {$line}\n";
}
