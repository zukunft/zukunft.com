# `use` and `include_once` ordering

Detail for the file-layout rule in `CLAUDE.md`.

Every PHP source file that uses classes from other namespaces follows this
three-block structure.

**Block 1 — path-constant `use` statements** (before any `include_once`):
Import only the path-constant classes needed to build the `include_once` paths.
Order: `cfg` paths → `web` paths → `shared` paths → test paths → other paths.

```php
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
```

**Block 2 — `include_once` statements**:
List all file includes, using the path constants from Block 1.

```php
include_once paths::API_OBJECT . 'api_message.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'sandbox_link.php';
```

**Block 3 — class `use` statements** (after all `include_once`):
Import all class names used in this file. Order: `cfg`/`api` → `web` → `shared`.
Within each group, sort alphabetically by fully-qualified class name.

```php
// cfg / api group (alphabetic within)
use Zukunft\ZukunftCom\main\php\api\api_message;
// web group (alphabetic within)
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
// shared group (alphabetic within)
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
```
