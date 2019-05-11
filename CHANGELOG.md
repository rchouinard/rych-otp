# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

This file adheres to the format published by [http://keepachangelog.com/](http://keepachangelog.com/).

## [unreleased]

## Changed
 - Update minimum PHP version to >=7.1
 - Re-implement PR #9 - Allow tokens to drift away from real time (@pavarnos)
 - Deprecate validate() method in favor of verify()

## Removed
 - Dependency on Rych\Random - no longer needed in PHP 7+
 - Seed and Encoder classes - Shared secrets should be simply passed as raw strings. 

## [1.1.1] - 2015-06-29

### Changed
 - Improve project documentation
 - Update development dependencies
 - Update testing configurations

### Fixed
 - Use correct operator for hash comparisons [@Ennosuke]
 - Update correct variable when calculating TOTP counter offset [@pavarnos]


## [1.1.0] - 2014-02-23

### Library
 - Fix documentation error in README [@ceejayoz]
 - Move sources under `src/` and change to PSR-4 loading
 - Clean up and standardize source documentation
 - Remove specific implementation details from abstract class

### Tests
 - Move tests under `tests/` and into same namespace as main library
 - Add HHVM to Travis-CI build matrix
 - Break tests into smaller units and ensure better coverage


## [1.0.0] - 2013-11-12

- Initial release

[unreleased]: https://github.com/rchouinard/rych-otp/compare/v1.1.1...HEAD
[1.1.1]: https://github.com/rchouinard/rych-otp/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/rchouinard/rych-otp/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/rchouinard/rych-otp/compare/0b0751...v1.0.0
