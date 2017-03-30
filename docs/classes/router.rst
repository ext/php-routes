Router
======

.. php:class:: Router

  .. php:attr:: $default_format

       Default regexp for matching variables.
      
       :default: ``'[A-Za-z0-9\-_\.]+'``

  .. php:attr:: $variable_formats

       Regexps for matching specific variables.
      
       .. code-block:: php
      
           $variable_formats = [
             'foo' => '\d+',
           ];
      
       will only match digits when adding a route ``/:foo/``
      
       :default: ``[]``

  .. php:function::  formatRoutes($verbose = false)


       Describe available routes in human-readable form.
      
       :returns: String with description.

  .. php:function::  printRoutes($verbose = false)


       Print available routes in human-readable form.

  .. php:function::  match($url, $method = false)


       Match a request against routes.
      
       :param string $url: Request URL.
       :param string|false $method: Request method or false to read from ``$_SERVER['REQUEST_METHOD']``.
       :returns: Matching route or false.
       :rtype: :doc:`routermatch`

  .. php:function::  clear()


       Removes all routes.
       Mostly useful for tests.

  .. php:function::  addRoute($pattern, $method, $options)


       Add a new route.
      
       Basic format is ``/path/to/match`` which literally matches the url.
       Variables can be assigned using ``/foo/:bar`` where the second part is a
       variable called bar. By default variables accept almost anything but the
       regular expression can be tuned using the option ``bar_format`` (see below).
       Patterns may also contain regular expressions.
      
       :param string $pattern: Route pattern to match, see format description above.
       :param string $method: Which HTTP method to match.
       :param array $options:
           Array of options:
             - ``to`` Target controller/action. [controller][#action]
             - ``as`` Name of this route.
             - ``var_format`` Variable format. ``var`` should be replaced with the
               actual variable name, like 'bar_format'. The value
               is the RE for matching, e.g. ``[0-9]+`` for a required
               number.
       :returns: Generated path function.

  .. php:function::  context()


       Create a new context for adding routes directly to root scope.

