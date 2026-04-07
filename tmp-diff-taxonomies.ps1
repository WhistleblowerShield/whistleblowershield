$expected = Get-Content .\tmp-expected-taxonomy-terms.json -Raw | ConvertFrom-Json -AsHashtable
$live = Get-Content .\tmp-live-taxonomy-terms.json -Raw | ConvertFrom-Json -AsHashtable
$report = @()
$allTax = ($live.Keys + $expected.Keys | Sort-Object -Unique)
foreach ($tax in $allTax) {
  $exp = @()
  if ($expected.ContainsKey($tax)) { $exp = @($expected[$tax]) }
  $act = @()
  if ($live.ContainsKey($tax)) { $act = @($live[$tax]) }
  $extra = @($act | Where-Object { $_ -notin $exp } | Sort-Object -Unique)
  $missing = @($exp | Where-Object { $_ -notin $act } | Sort-Object -Unique)
  if ($extra.Count -gt 0 -or $missing.Count -gt 0) {
    $report += [PSCustomObject]@{
      taxonomy = $tax
      extra_count = $extra.Count
      missing_count = $missing.Count
      extra_in_db_not_in_registry = ($extra -join ', ')
      missing_in_db_but_in_registry = ($missing -join ', ')
    }
  }
}
if ($report.Count -eq 0) {
  'NO_DIFFS'
} else {
  $report | Sort-Object taxonomy | Format-Table -AutoSize | Out-String
}
