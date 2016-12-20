## 1.2.8 (2016-12-20)

## Features

- `php-routes` binary learned `-v` for verbose output (non-verbose
  mode now hides regex output.)
- `php-routes` binary no longer writes trailing whitespace.
- `php-routes` binary output simplified regex (unnecessary details
  hidden from user.)

### Changes

- Routes are now stored in `Route` state class instead of positional
  array.
- Minor refactoring

## 1.2.7 (2016-10-25)

### Fixes

- Additional unittests
- Internal changes to `parseTo`

## 1.2.6 (2016-10-23)

### Breaking changes

- Implemented PSR-1 and PSR-2 coding standards: all methods previously
  using snake_case is now using camelCase.
