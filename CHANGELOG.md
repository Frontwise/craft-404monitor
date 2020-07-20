Changelog
==================
## 1.4.0 - 2020-07-20
### Added
- Dashboard widget to monitor 404 web requests

## 1.3.0 - 2020-07-14
- Changed removal of requests: remove when there are no hits in the past X days
- Improved: performance when deleting lots of requests
- Improved: performance for multisites
- Fixed: requests showing up for each site

## 1.2.1 - 2019-04-24
- Check for soft-deleted elements before creating a new web 404. Remove hits when soft deleting web 404s.

## 1.2.0 - 2019-04-24
- Added referrer information to 404 hits

## 1.1.1 - 2019-04-24
- Use Craft 3.1 soft deletion of elements.
- Improved translations.

## 1.1.0 - 2018-10-19
- Updated scheme, improved performance for sites with a lot of 404s

## 1.0.6 - 2018-10-08
- Added Norwegian translation

## 1.0.5 - 2018-10-05
- Added german translation

## 1.0.4 - 2018-09-11
- Removed ANY_VALUE function which does not work in MariaDB

## 1.0.3 - 2018-09-11
- Fixed mysql ONLY_FULL_GROUP_BY (enabled by default on mysql > 5.7.5)

## 1.0.2 - 2018-09-05

### Improved
- Added screenshots

### Changed
- Set proprietary license
