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

## never remove an include that looks unused

There is no autoloader, so a class exists only because some file has included it,
and the order in which the files are included decides whether a class is already
defined when another class body needs it. An `include_once` that this file does
not seem to need can therefore be the one that defines a parent class before a
child extends it, in this file or in a file included later.

Until the code is stable and the includes are changed to the proper dynamic
loading, **leave an include that looks unused in place**. Removing it may work in
the one page that was tested and fatal in another, because the surviving order
depends on which entry point was called first — the failure then shows up as
`Class "..." not found` in a completely different file (see the commented-out
includes listed in `docs/llm/pending.md`).

## include everything a file needs, always in block 2

Loading a php file costs nothing worth optimising on the machines this runs on,
so every class a file uses is included in block 2 — never lazily inside the
function that happens to need it, because a conditional include hides the
dependency from the reader and from the include check of `coding_rule_tests`.

The order within block 2 can matter. A file that is loaded early may include a
class whose own include chain leads back to a class that is not defined yet, e.g.
`web/component/component.php` includes `view_list.php`, which leads back to
`component_exe.php`, which loads `system_form.php`, which extends `component` —
still undefined while `component.php` is being parsed. In that case include the
file that loads the package in the working order first (here `component_exe.php`,
which loads `component.php` and only then `system_form.php`), then the file
itself, and say in a comment why the extra include is there.
