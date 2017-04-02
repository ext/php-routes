Authentication
==============

This example shows how to implement an authentication "filter" where any request
to `/private/...` will require an authenticated user.

It works by adding an extra `need_auth` option to the routes which the
dispatcher looks at before handling the request.
