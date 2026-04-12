# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - QR Generator Section Missing from Events Tab
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the QR generator section is absent
  - **Scoped PBT Approach**: Scope the property to the concrete failing case: render `events-list.blade.php` and assert the QR generator section is present
  - Open `resources/views/admin/partials/events-list.blade.php` and verify it contains a QR code generator form (select event, series prefix input, quantity input, generate button)
  - Verify the file contains a reference to `qrcode.js` CDN script
  - Verify the file contains logic to produce QR codes in format `PHY-{SERIE}-{NUMERO}` with zero-padded numbers
  - Run test on UNFIXED code (current state of the file)
  - **EXPECTED OUTCOME**: Test FAILS (confirms the QR generator section is missing — the bug exists)
  - Document counterexamples found: e.g., "events-list.blade.php has no QR generator form, no qrcode.js CDN, no PHY- format logic"
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Events Table, Search Filter, and Pagination Remain Intact
  - **IMPORTANT**: Follow observation-first methodology
  - Observe: current `events-list.blade.php` renders a search form with `events_search` input and submit button
  - Observe: current file renders a `<table>` with columns Titre, Lieu, Date, Billets, Tarifs
  - Observe: current file renders `@forelse($eventsList as $event)` loop with event rows
  - Observe: current file renders pagination block `$eventsList->hasPages()`
  - Write property-based test: for any rendered output of the file, it MUST contain the search form, the events table with all 5 columns, the `@forelse` loop, and the pagination block
  - Verify test passes on UNFIXED code (the table/filter/pagination are present today)
  - **EXPECTED OUTCOME**: Tests PASS (confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2_

- [x] 3. Restore QR code generator section in events-list.blade.php

  - [x] 3.1 Add qrcode.js CDN script reference to the view
    - Add `<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>` at the bottom of the file (or in a `@push('scripts')` block if the layout supports stacks)
    - _Bug_Condition: isBugCondition(view) where view does not contain qrcode.js CDN reference_
    - _Expected_Behavior: view contains qrcode.js CDN script so client-side QR generation works_
    - _Requirements: 2.2_

  - [x] 3.2 Add QR code generator form section above or below the events table
    - Add a clearly delimited section (e.g., card/panel) titled "Générateur de QR Codes Physiques"
    - Include a `<select>` populated from `$events` (value = event id or slug, label = event title)
    - Include a text input for series prefix (placeholder: `PHY-GSA2026-`, default empty)
    - Include a number input for quantity (min=1, max=200)
    - Include a "Générer" button that triggers the JS generation function
    - Include a print button that calls `window.print()`
    - Include a `<div id="qr-output">` grid container where generated QR codes will be rendered
    - _Bug_Condition: isBugCondition(view) where QR generator form is absent_
    - _Expected_Behavior: expectedBehavior — form is present with event select, prefix input, quantity input, generate button, print button, and output grid_
    - _Preservation: existing events table div, search form, and pagination block are untouched_
    - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.2_

  - [x] 3.3 Add client-side JavaScript to generate QR codes
    - Write a `generateQRCodes()` function that reads selected event, prefix, and quantity from the form
    - Validate quantity is between 1 and 200; show an alert if invalid
    - Clear the `#qr-output` container before each generation
    - Loop from 1 to quantity: build code string `PHY-{PREFIX}{NNN}` where NNN is zero-padded to 3 digits (e.g., `String(i).padStart(3, '0')`)
    - For each code, create a wrapper div, a label `<p>`, and a QR div; call `new QRCode(div, { text: code, width: 128, height: 128 })`
    - Append each wrapper to `#qr-output`
    - _Bug_Condition: isBugCondition(output) where QR codes are not generated or format is wrong_
    - _Expected_Behavior: expectedBehavior — codes follow `PHY-{PREFIX}-{NNN}` with zero-padded 3-digit numbers_
    - _Requirements: 2.2_

  - [x] 3.4 Add print-specific CSS
    - Add a `<style>` block (or `@push('styles')`) with `@media print` rules
    - Hide everything except `#qr-output` during print: `body > *:not(#print-area) { display: none }` or equivalent scoped selector
    - Ensure QR code grid is visible and laid out cleanly for print
    - _Requirements: 2.3_

  - [x] 3.5 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - QR Generator Section Present in Events Tab
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior (QR section present, qrcode.js CDN, PHY- format)
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms QR generator section is restored)
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 3.6 Verify preservation tests still pass
    - **Property 2: Preservation** - Events Table, Search Filter, and Pagination Remain Intact
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions — table, filter, pagination untouched)
    - Confirm all tests still pass after fix (no regressions)

- [x] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
