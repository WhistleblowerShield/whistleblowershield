<?php
/**
 * matrix-assist-orgs.php — Seeds nationwide and federal-scope whistleblower support organizations.
 *
 * @package    WhistleblowerShield
 * @since      3.0.0
 * @version    3.18.1
 *
 * VERSION
 * -------
 * 3.18.1  Factual conflict resolution after live-site verification:
 *         - NWC mailing_address corrected to 1800 M Street NW #33888, DC 20033
 *           (whistleblowers.org contact page confirmed; old 1140 Connecticut Ave was stale)
 *         - PEER mailing_address: expanded 'PEER' abbreviation to full org name
 *         - GAP zip +4 (20006-2802): kept — USPS extended zip, not incorrect
 *         - WbAid secure_contact_url /signal/: kept — specific page confirmed live
 *         - POGO has_secure_channel: kept 0 — no live public secure channel confirmed
 *         - TAF has_attorneys: kept 1 — staff page confirms 3 attorneys on staff
 *         - NWC/PEER contact_url from JSON was blank — kept existing matrix values
 *
 * 3.18.0  Gap-fill from Grok research batches (US-0-Assist-org-Grok-4-matrix-updates*.json).
 *         Filled ws_aorg_contact_url where empty: NWC, POGO.
 *         (GAP, WbAid, TAF had no Grok contact_url to fill from.)
 *         Inserted nationwide_example after whistleblower_note for all 8 orgs.
 *         Inserted protected_classes and protected_class_details after sectors
 *         for all 8 researched orgs. No existing values overwritten.
 *
 * 3.17.1  Data corrections from deep research pass:
 *         - GAP: corrected mailing address to 1612 K St NW Suite 808;
 *           updated intake_url to /how-to-request-assistance/
 *         - Whistleblower Aid: corrected mailing address to
 *           1250 Connecticut Ave NW Suite 700 (Charity Navigator confirmed)
 *         - TAF: re-branded from Taxpayers Against Fraud Education Fund
 *           to The Anti-Fraud Coalition; slug, internal_id, description,
 *           mailing address, and intake_url updated; gate bumped to 1.1.0
 *         - The Signals Network: has_secure_channel set to 1 (ProtonMail
 *           confirmed); secure fields populated; protect@ email added to
 *           emails repeater; ProtonMail note removed from description;
 *           intake_url added
 *         - WIN: removed invalid public-general slug from disclosure_targets
 *         - POGO: whistleblower_scope corrected from 3 to 1 (investigative
 *           watchdog, not direct help org)
 * @author     Whistleblower Shield
 * @link       https://whistleblowershield.org
 * @copyright  Copyright (c) Whistleblower Shield
 * 
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════════════════════
// Assist-Org Data
// ════════════════════════════════════════════════════════════════════════════

global $_ws_assist_org_matrix;
$_ws_assist_org_matrix = [PLACEHOLDER];
