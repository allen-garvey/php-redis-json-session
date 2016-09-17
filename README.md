# PHP Redis JSON Session

Save your PHP sessions in Redis formatted as JSON, so that you can share your session data with non-PHP applications.


## Dependencies

* PHP 7.0
* Redis
* [PHP Redis extension](https://github.com/phpredis/phpredis)
* php.ini value `session.serialize_handler` must be `php` (this is the default)
* PHPUnit 5.5.4 (for testing only)

## Getting Started

* See `example.php` for an example of how to setup the handler
* php.ini value `session.gc_maxlifetime` is used to set session key expiration dates in Redis

## Running Tests

Run the unit tests by running `phpunit --bootstrap lib/redis_json_session_handler.php tests` while in the project directory if phpunit is installed on your path, otherwise through `php /path/to/phpunit.phar --bootstrap lib/redis_json_session_handler.php tests`.

## Caveats

If your session data is binary encoded, see [http://us.php.net/session_decode](http://us.php.net/session_decode) for example code on how to unserialize binary encoded session data, and adapt the `unserializeSessionData` function appropriately (Note you are on your own for how to serialize the JSON session data to binary).

## License

PHP Redis JSON Session is released under the MIT License. See license.txt for more details.