wordpress-puppetmaster
======================

Turns Wordpress into a data rendering API.

Using
-----

Make an HTTP `POST` to any Wordpress page with the post variable `resetblog`
set to `1` and the blog database will be reset.

If you additionally set thepost variable `newdata` to a JSON tree (of our
pre-specified format) containing blog data, then it will initialize the
database to have that data before continuing to render the page.

Example command-line use
------------------------

     curl -X POST --form \
          -Fresetblog='1' \
          -Fnewdata=@data-file-containing-blog.json \
          http://your-blog.com

