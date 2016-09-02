# The Page Class

Clone this project, run `composer install`, then `vendor/bin/phpunit`.

The central class of this project is `Page`, which is a very basic, very crappy PHP CMS. Never use this code or part of it in a production application!

This code base can be used to practice your "effectively working with legacy code" skill.

If you need some ideas to get started:

- Move `define`d configuration values into a dedicated `Configuration` object.
- Apply the Dependency Inversion Principle on external dependencies, like the templating engine `Smarty` and the `mysql_*` functions.
- Instead of directly sending output and headers, use output buffering (using the `ob_*` functions) and collect headers before sending them to the HTTP client.
- Use meaningful objects instead of "anonymous" arrays.
- Move the `sfTimer` stuff to a decorator.
- Reduce the amount of public functions by moving "static" functionality to some other object.
- Enhance the level encapsulation of this object by making attributes private and making sure that a `Page` object will always behave consistently from object client's point of view.

Have fun!