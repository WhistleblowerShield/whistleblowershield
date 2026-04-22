
***

## WIN
**Add after `'whistleblower_note'` line:**
```php
'nationwide_example'      => 'WIN connects and strengthens civil society organisations that defend and support whistleblowers.',
```
**Add after `'sectors'` block:**
```php
'protected_classes'       => [ 'has-details' ],
'protected_class_details' => 'WIN is an org-to-org network; it does not serve individual whistleblowers directly. Member organizations collectively serve whistleblowers across all worker classes and sectors worldwide.',
```

***

## NELP
**Add after `'whistleblower_note'` line:**
```php
'nationwide_example'      => 'NELP is leading the fight for a good-jobs economy. Our victories over the last decade have impacted the lives of 100 million workers and their families.',
```
**Add after `'sectors'` block:**
```php
'protected_classes'       => [ 'corporate-staff', 'contractor-gig', 'agricultural-worker', 'has-details' ],
'protected_class_details' => 'Focused on low-wage, immigrant, contingent, and unemployed workers in private-sector and gig/temp arrangements; has-details reflects emphasis on economically vulnerable subsets rather than all corporate-staff broadly.',
```

***

## NELA
**Add after `'whistleblower_note'` line:**
```php
'nationwide_example'      => 'NELA is the largest professional organization in the United States whose members are lawyers who either exclusively or primarily represent workers in cases involving employment and traditional civil rights issues.',
```
**Add after `'sectors'` block:**
```php
'protected_classes'       => [ 'all-employees' ],
'protected_class_details' => 'Plaintiff-side attorneys in NELA\'s network represent all worker classifications in employment, wage theft, retaliation, discrimination, and civil rights matters nationwide.',
```
**Also flag:** `disclosure_targets` contains `judicial-federal` and `judicial-state` — non-standard slugs, valid slug is `court-filing`. Separate correction pass.

***

## LSC
**Add after `'whistleblower_note'` line:**
```php
'nationwide_example'      => 'LSC currently provides funding to 129 independent nonprofit legal aid programs in every state, the District of Columbia, and U.S. territories.',
```
**Add after `'sectors'` block:**
```php
'protected_classes'       => [ 'all-employees', 'has-details' ],
'protected_class_details' => 'Income-eligible individuals at or below 125% of Federal Poverty Guidelines (2026: $19,950/individual, $41,250/family of 4). Covers all employment classifications meeting income threshold — veterans, seniors, agricultural workers, gig/contract workers included.',
```

***

## NLADA
**Add after `'whistleblower_note'` line:**
```php
'nationwide_example'      => 'NLADA is America\'s oldest and largest nonprofit association devoted to excellence in the delivery of legal services to those who cannot afford counsel. For more than a century, we have connected and supported people across the country committed to justice for all.',
```
**Add after `'sectors'` block:**
```php
'protected_classes'       => [ 'all-employees', 'agricultural-worker', 'has-details' ],
'protected_class_details' => 'Low-income individuals across all worker classifications; dedicated sections for farmworkers and low-income Latinos; also serves seniors, persons with disabilities, and undocumented persons. Income constraint applies — services flow through member legal aid grantees, not NLADA directly.',
```

***

## NWC Referral
**Change `whistleblower_scope`:**
```php
'whistleblower_scope' => 2,  // was 3 — referral program, not full-service org
```
**Change `cost_models`:**
```php
'cost_models' => [ 'free' ],  // was fee-for-service — ARP is explicitly free to whistleblowers
```
**Add after `'whistleblower_note'` line:**
```php
'nationwide_example'      => 'This program is free of charge for all whistleblowers and has connected hundreds of whistleblowers with attorneys.',
```
**Add after `'sectors'` block:**
```php
'protected_classes'       => [ 'federal-employee', 'corporate-staff', 'contractor-gig' ],
'protected_class_details' => 'Government and private sector whistleblowers; cases reviewed under WPA (federal employees), Sarbanes-Oxley and Dodd-Frank (corporate), qui tam, and numerous federal and state laws. The referral itself is free; downstream attorney representation terms are set independently.',
```

***

## ABA
**Add after `'whistleblower_note'` line:**
```php
'nationwide_example'      => 'The ABA Free Legal Answers virtual advice legal clinic has responded to 400,000 civil legal questions to date, helping many low-income Americans access legal help.',
```
**Add after `'sectors'` block:**
```php
'protected_classes'       => [ 'all-employees' ],
'protected_class_details' => 'All individuals regardless of employment classification or income; connects to state bar lawyer referral services in all 50 states. Note: ABA Free Legal Answers (sister program) is income-restricted for civil matters — the Find Legal Help directory entry point is unrestricted.',
```

***

**Version bump suggestion for the file header:** `3.19.0` — gap-fill for remaining 7 orgs; one scope correction (NWC Referral 3→2); one cost_models correction (NWC Referral fee-for-service→free).