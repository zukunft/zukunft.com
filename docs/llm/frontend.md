# Frontend (`web/`) conventions

Detail for the "Frontend" rules in `CLAUDE.md`.

## Public properties + PHP 8.4 property hooks

In classes under `src/main/php/web/` (the HTML frontend layer), object
properties are declared `public`. Frontend objects are thin view-models
populated from the backend api json and consumed by renderers; trivial private
fields with one-line `get_x()`/`set_x()` only add boilerplate. Direct property
access (`$wrd->plural`) is the intended style.

When a property genuinely needs non-trivial set/get behaviour (validation, lazy
computation, normalisation), express it with **PHP 8.4 property hooks declared
inline on the property**, not separate methods. The hook keeps the custom
behaviour at the declaration, and callers still use `$obj->prop` /
`$obj->prop = …` — no second API to keep in sync.

- **Right** — public property with inline hooks for the non-standard part:
```php
public ?string $plural = null {
    get => $this->plural;
    set => $this->plural = trim($value);
}
```
- **Right** — plain public property when no custom logic is needed:
```php
public ?float $weight = null;
```
- **Wrong** — `private` field with hand-written accessors that add nothing:
```php
private ?string $plural = null;
public function get_plural(): ?string { return $this->plural; }
public function set_plural(?string $v): void { $this->plural = $v; }
```

Backend (`cfg/`) classes are **not** covered: they keep `private` fields and
explicit accessors because they enforce user-sandbox, log, and DB-write
invariants on every change. Apply the public-property + inline-hook rule only to
`web/`.

## Frontend / UI functions end with `_ui`

Any function that builds, returns, or operates on a **frontend (UI) object** ends
with the `_ui` suffix, so a reader can tell at the call site whether they get a
backend (`cfg/`) or frontend (`web/`) object without checking the return type.
This matters most in test factories where a backend and frontend variant of the
same fixture sit side by side.

- **Right**: `test_words::word_swiss_franc()` returns the backend `word`;
  `test_words::swiss_franc_ui()` returns the frontend `word_ui`
- **Wrong**: `word_swiss_franc_dsp()` for a frontend factory — `_dsp` is reserved
  for the display *class* suffix (`word_dsp`), not "returns a UI object"; use
  `_ui`

When a backend and frontend factory of the same fixture both exist, pair them as
`<name>()` (backend) and `<name>_ui()` (frontend) — `word_chf()` / `chf_ui()`,
`word_swiss_franc()` / `swiss_franc_ui()`. Older `*_dsp` helpers (`word_dsp()`,
`word_chf_dsp()`, `word_zh_dsp()`) predate this rule and should be renamed to the
`_ui` ending when next touched.
