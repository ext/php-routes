## 1.5.0 (2021-07-09)

* PHP 8.0 compatibility

## 1.4.2

## 1.4.1 (2017-04-13)

### Features

* Added `Router::loadRoutes` for loading routes from file later. Constructor
  still takes filename as before.

## 1.4.0 (2017-04-09)

### Changes

* Non-existing path functions now trigger `E_USER_WARNING` instead of
  `E_USER_ERROR` as execution can continue even without it.

### Features

* Added global `$patch` and `$addRoute`, the latter for defining fully custom
  routes.
* Added `RouterMatch::$options` to supply user-defined options.
* Added documentation.
* Added `Router::$default_format` and `Router::$variable_formats` for setting
  application-wide format settings.

## 1.3.0 (2017-03-23)

### Changes

- `Router::method` is renamed to `Router::addRoute`. API breaking but should not
  be called directly by users. The motivation is to make it easier to use
  PHPUnit mocks.

## 1.2.8 (2016-12-20)

### Features

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
