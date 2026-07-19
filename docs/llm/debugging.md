# Debug levels

How to see what a URL request actually does. The debug level is set per request
with the `debug` URL parameter (`url_var::DEBUG`) and controls how much of the
internal work is echoed into the response, e.g.

```
http://localhost/http/view.php?m=1&debug=7
```

The constants live in `src/main/php/shared/url_var.php`.

## Where the level comes from and why it is gated

`http/const.php` reads the requested level from the URL but keeps the global
`$debug` at `0` until the environment is known, then honours the request **only
in the dev environment**:

```php
$debug_requested = $_GET['debug'] ?? 0;
$debug = 0;
// ... init.php loads .env ...
if (getenv(ENVIRONMENT) == ENV_DEV) {
    $debug = $debug_requested;
}
```

In prod and test `$debug` stays `0` no matter what the URL asks for, so the debug
output — which exposes SQL, table and column names and the internal call graph —
is never leaked to a visitor. Treat `?debug=…` as a **dev-only** tool.

## How a level decides what is shown

`log_debug($msg, $level)` (in `cfg/log_text/text_log_functions.php`) echoes the
message plus the cumulative execution time once the global `$debug` reaches the
given minimum `$level` (`$debug >= $level`). Called **without** a level a message
is treated as a deep call-graph trace and is shown only *above the last named
level* — from `&debug=11` upward (`DEBUG_LEVEL_MAX_FIXED + 1`) — so the plain
`log_debug($msg)` calls that pepper the code stay out of the way of the named
levels below.

Raising the level therefore makes the log progressively more verbose: each named
level below marks the point at which that category of activity starts to be
shown, and a higher level includes everything the lower levels already show. Pick
the lowest level that reveals the activity you are investigating so the output
stays readable.

A message for a **named** level is emitted by passing that level as the second
argument of `log_debug`, which shows it from `&debug=<level>` upward. For example
every database write in `sql_db::insert` / `update` / `delete`:

```php
// show every db write from '&debug=6' upward
log_debug($description . ': ' . $qp->sql, url_var::DEBUG_LEVEL_DB_WRITE);
```

`log_debug($msg, $level)` shows the message once the global `$debug` reaches
`$level` (`$debug >= $level`); called with no level (`log_debug($msg)`) it shows
only from `&debug=11` upward (`DEBUG_LEVEL_MAX_FIXED + 1`), i.e. in the depth
range. To become visible at a specific depth, pass that depth level directly
(e.g. `16` for a low-level SQL echo).

## The levels

| Const | Value | Shows |
|-------|-------|-------|
| `DEBUG` | `'debug'` | the URL parameter name that carries the level |
| `DEBUG_EXE_TIME_REPORT` | `-1` | not a verbosity level: appends the execution-time report to the frontend page (`http/view.php` checks `$debug == -1`) |
| `DEBUG_LEVEL_EXTERNAL_CALLS` | `1` | calls to external systems, e.g. Wikipedia |
| `DEBUG_LEVEL_POD_READS` | `2` | data read calls from other zukunft.com pods |
| `DEBUG_LEVEL_POD_PUSH` | `3` | push messages to other zukunft.com pods |
| `DEBUG_LEVEL_SERVICE_CALLS` | `4` | calls to internal services, e.g. an R-project server |
| `DEBUG_LEVEL_API_CALL` | `5` | api calls of the frontend to the backend (`rest_call::api_call`) |
| `DEBUG_LEVEL_DB_WRITE` | `6` | write actions to the database |
| `DEBUG_LEVEL_DB_READ` | `7` | read actions from the database |
| `DEBUG_LEVEL_COMPLEX_FUNCTION` | `8` | potentially long-running function calls, e.g. a suspected endless loop |
| `DEBUG_LEVEL_MAIN_STEP` | `9` | the main processing steps such as start and end |
| `DEBUG_LEVEL_MAX_FIXED` | `10` | the last predefined level and the start of the *depth* debug levels: from `10` upward each higher number drills one more call deeper |

The ordering runs from the rarest, most significant activity (external calls at
`1`) to the most frequent, most voluminous (database reads at `7`), then the
long-runner tracing at `8` and the main steps at `9`; from `10` upward the depth
levels drill one call deeper for each additional number, for tracing a specific
deep call chain.

## Typical use

- `?debug=1` — is the request reaching out to an external system at all?
- `?debug=5` — see every api call the frontend makes to the backend, e.g. to find
  out which backend calls a slow page rendering triggers.
- `?debug=7` — see every SQL read (and everything above it) a request triggers,
  e.g. to check how many database reads a cached vs. an uncached page needs.
- `?debug=8` — hunt a suspected endless loop or a slow function.
- `?debug=-1` — just the execution-time report, without the message flood.