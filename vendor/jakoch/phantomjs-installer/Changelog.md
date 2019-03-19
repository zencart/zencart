# ChangeLog

## [Unreleased]

- "It was a bright day in April, and the clocks were striking thirteen." - 1984

## [2.1.1] - 2015-01-25

- added v2.1.1 to the PhantomJS versions array to
- Automatic download retrying with version lowering, if download fails with 404
- class `PhantomInstaller\PhantomBinary` is created automatically during installation,
  to access the binary and its folder more easily
- added support Composer patch version tag with a patch level, like "2.1.1-p02"
- added usage examples (inside `/test`), each with a different `composer.json` file
- add support for vendor-dir as installation folder for the extracted "phantomjs"

## [2.0.0] - 2014-08-09

## [1.9.8] - 2014-07-10

## [1.9.7] - 2014-06-24

- Initial Release
- grab version number from explicit commit references, issue #8

[Unreleased]: https://github.com/jakoch/phantomjs-installer/compare/2.1.1...HEAD
[2.1.1]: https://github.com/jakoch/phantomjs-installer/compare/2.0.0...2.1.1
[2.0.0]: https://github.com/jakoch/phantomjs-installer/compare/1.9.8...2.0.0
[1.9.8]: https://github.com/jakoch/phantomjs-installer/compare/1.9.7...1.9.8
[1.9.7]: https://github.com/jakoch/phantomjs-installer/releases/tag/1.9.7
