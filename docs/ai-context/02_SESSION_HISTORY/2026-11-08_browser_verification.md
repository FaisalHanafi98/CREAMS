# CREAMS recovery — 8 November 2026

The owner resumed current-release repairs, delegated reversible business
defaults to the central decision register and authorised GitHub publication
of the Fixers branch.

## Verified locally

- A clean source snapshot migrated on the dedicated MySQL 8.4.3 fixture
  and passed 413 PHPUnit tests with 756 assertions.
- The HTTP fixture signed in Admin, Supervisor, Teacher and AJK. Each role
  reached its dashboard and received the expected nonzero API count.
- Chromium passed 17 cases. The cases covered four role login and logout
  journeys, centre and category pages, attendance marking and denial paths,
  and attendance reports with CSV and PDF downloads.
- The route ledger assigned 16 direct entries to those cases. Another 285
  direct entries still need browser checks. The route list also contained
  294 demo and 6 deferred-role registrations.
- The MySQL CA configuration probe passed on PHP 8.3.30 and 8.5.7. It used
  a synthetic CA value and checked configuration loading only.

## Publication preparation

The first clean snapshot could not migrate because a legacy attendance
foreign key used incompatible types. Two existing local migration
prerequisites passed the fresh-schema check and were included. The next
clean run reproduced a dashboard API error, which the API repair and
stricter regression resolved.

The publication history keeps the foundation-seeder adjustment and
excludes an unpublished change to the legacy real-data seeder. The
publication candidate was tested on synthetic records. A populated
upgrade, production behaviour and the untested browser entries remain
open.
