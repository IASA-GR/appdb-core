# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [6.2.2] - 2019-07-19
### Changed
- Forbid virtual appliance working versions to be verified if the VM image location URL use a self-signed certificate
### Fixed
- Display proper IDs for OCCI and OpenStack endpoints

## [6.2.1] - 2019-06-20
### Added
- Added "authn" attribute to virtualization:provider and site:service XML elements in RESTful API
### Fixed
- Fixed regression bug in scientific classification API, which resulted in empty documents
- Fixed default country search scope for sites
- Fixed resource caching for cloud sites with native OpenStack endpoints

## [6.2.0] - 2019-06-14
### Changed
- Populate VM images from site endpoints exposing native APIs (e.g. openstack) along with OCCI enabled ones
- Updated acceptable netfilter rules according to RFC 1123
### Fixed
- Fixed organizations autocomplete list redirection bug
- Fixed bug related to missing organizations
- Fixed bug related to VA expiry dates

## [6.1.15] - 2019-04-10
### Changed
- Access to VO-wide image lists granted site administrators for sites with org.openstack.nova endpoints

## [6.1.14] - 2019-01-25
### Changed
- Added more warning messages and documentation links in continuous delivery UI to prevent user mistakes
### Fixed
- Properly handle site images that are not provided under a VO wide image list

## [6.1.13] - 2018-01-10
### Fixed
- Fixed invalid report of expired VM versions in site details page

## [6.1.12] - 2018-12-06
### Fixed
- Fixed issues regarding base64 encoding of unicode and object types
- Fixed paging in RESTful API VO member/contact, etc resources
- Fixed invalid edit button on non editable person profile tabs

## [6.1.11] - 2018-11-22
### Changed
- Improved internal server errors display
- Improved performance of openAIRE searching
### Fixed
- Properly handle unicode characters in user input when creating a new user account
- Display short name for all organizations retrieved from openAIRE

## [6.1.10] - 2018-09-11
### Added
- Import publication information for software and vappliances in various formats (biblatex, bib, copac, ebi, end, endx, isi, med, nbib, ris, wordbib)
### Changed
- Pass extended account information to authorized sub services (vmops dashboard) 
- Replace php file_get_contents with cURL
- Improved support for OpenAIRE project and organization metadata synchronization
### Fixed
- Invalid dojo versioning report due to custom build
- Fixed bug causing empty category and discipline collections when creating new software and vappliance entries


## [6.1.9] - 2018-08-30
### Fixed
- Properly display secant multiline details 


## [6.1.8] - 2018-08-29
### Added
- Added vomses information regarding geohazards.terradue.com, hydrology.terradue.com, vo.geoss.eu
- Funded by relation support for sw/va and projects
### Changed
- Revised UI of VO wide image list editor
- Display secant backend service outcome in security report UI
- Set software last updated date when performing software repository actions
- Update countries information
- Update vomses information
- Performance improvements when saving software and vappliance items
### Fixed
- Fixed bug rendering empty history list of edits for software and vappliance items
- Fixed bug causing predefined middlewares of software items to be saved as custom middlewares


## [6.1.7] - 2018-07-02
### Changed
- Improve performance by performing asynchronous calls to DB where possible
- Performance improvements when generating software, vappliance, person and permissions XML
### Fixed
- Avoid cache race conditions in filter items function
- Define xmlns:xsi namespace on elements that make use of xsi:nil to avoid XML errors inside the database
- Avoid possible null array references in REST API causing unhandled exceptions 
- Ensure all related DB entries are refreshed on software and vappliance updates (permissions etc)
- use view for application hitcount in ARO model
- Fix relation type literals in DB


## [6.1.6] - 2018-06-27
### Changed
- Allow only administrators to register new user profiles from UI (removed managers)
- People profile searching returns more relative results
### Fixed
- Fixed profile validation mechanism when a new user registers


## [6.1.5] - 2018-06-22
### Security
- Migrate jQuery to version 3.x
- Removed dead code
### Fixed
- Fix binary artifact types to recognize tar and gz formats in software repository 


## [6.1.4] - 2018-06-08
### Fixed
- Properly handle secant's failed checks due to internal failure


## [6.1.3] - 2018-05-24
### Changed
- Group sequential log entries and display count in continuous delivery view
Bug fixes and improvements
- Clean up automatic mail subscriptions that the user did not opt in (GDPR related)
- Disable notifications for outdated applications (GDPR related)
- Make profile contact information and VO membership available only to the same user account (GDPR related) 
### Security
- Differentiate handling of REST api calls from AppDBs client code and external calls in order to avoid external malicious javascript code to be executed on behalf of the logged in user 
### Removed
- Export button from people list (GDPR related)
- Broken links report as it lacked accuracy and code became obsolete
### Fixed
- Various bugs regarding the atom news feed
- Respond with tag information when inserting a new tag on sowftare and vappliance 
- Properly identify users access groups when accessing REST api using access token


## [6.1.2] - 2018-05-14
### Added
- Integration with virtual appliance continuous delivery sub service
### Removed
- Removed gender information from person profile (GDPR related)


## [6.1.1] - 2018-05-02
### Added
- PID related support to software and vapplince entries
- Support for diffs of software and vappliance change history in UI and REST api
### Changes
- Conform with EGI AAI entitlements format changes
- Retrieve site contact information from EGI AAI entitlements 
- Replace links to vmcatcher with CloudKeeper
### Fixed
- Display message when an entry was not found due to invalid url


## [6.1.0] - 2018-03-26
### Added
- Integration with secant service
- Added biomed and enmr.eu VOMS related files in public ui assets


## [6.0.1] - 2017-12-04
### Changed
- Use preset expiration dates for VA versions.
- Set default VA version expiration date to 1 year
- Sanitize existing VA version expiration dates (max 1 year)
- Only allow integrity check bypass for private VA versions

## [6.0.0] - 2017-11-24
### Changed
- Move cloud information retrieval mechanism to new information system (IS)
- Retrieve template disk info from top-bdii
- Notify VMOps Dashboard after syncing sites information from gocdb, in order to updaet its data
- Add service terms of use in login button
- Add link to VMOPs dashboard in cloud marketplace related views
