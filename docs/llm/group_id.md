# The compact group id

A value or a result is addressed by the list of its phrases, so the key of a
value is the id of its **group** (see `cfg/group/group_id.php`, `cfg/group/id.php`
and `shared/group_id_url.php`). The key is built from the phrase ids, so a group
needs no own database row unless a user gives it a name.

## Three key types, three tables

| phrases                                   | key                    | table e.g.     | column         |
|-------------------------------------------|------------------------|----------------|----------------|
| up to 4 prime phrases (id < 32'768)       | 64-bit integer         | `values_prime` | `bigint`       |
| up to 16 phrases and up to 112 key chars  | compact text key       | `values`       | `char(112)`    |
| more than 16 phrases or a longer key      | the same compact key   | `values_big`   | `text`         |

Postgres returns a `char(112)` key padded with spaces to 112 chars, so
`sql_db::pg_unpadded` removes the trailing spaces of every `char` field when a
row is read; a key never contains a space, and mysql removes the padding itself.

`group_id::table_type()` selects the table from the key alone: an integer is
prime, a text key is big if `group_id::is_big()` counts more than 16 phrases or
more than 112 chars, otherwise it is the standard table. The standard and the
big table use the same key format; only the size decides.

## How the compact text key is built

Each phrase id is written with **only its significant alpha_num chars** and is
**ended by one sign char**. There are no fixed slots, no `.` padding and no
empty entries, so a key is as short as its phrase ids.

### The 64 alpha_num digits (base 64, see `id::int2char`)

| value  | char        |
|--------|-------------|
| 0      | `.`         |
| 1      | `/`         |
| 2..11  | `0` .. `9`  |
| 12..37 | `A` .. `Z`  |
| 38..63 | `a` .. `z`  |

The most significant digit comes first; zero is the single char `.`. A 32-bit
phrase id needs at most 6 chars (64^6 = 2^36). Upper and lower case are
different digits, e.g. 1148 is `Fw` and 1122 is `FW`, so every compare and
search must be case-sensitive.

### The sign chars (see `group_id_url`)

| char | meaning                                   |
|------|-------------------------------------------|
| `+`  | word (a positive phrase id)               |
| `_`  | word, the form of `+` in a url            |
| `-`  | triple (a negative phrase id)             |
| `=`  | formula (result keys only)                |
| `<`  | word only used for the source of a result |
| `(`  | triple only used for the source           |
| `>`  | word only used for the result             |
| `)`  | triple only used for the result           |

None of the sign chars is a digit, so the key is split unambiguously at each
sign char. Both `+` and `_` are read as a word; the database key always uses
`+`.

### Order

The phrase ids of a group key are sorted by id (`phrase_list::sort_by_id`), so
the same phrases always give the same key. A result key (`result_id`) starts
with the formula, then the phrases used for source and result, then the
source-only and the result-only phrases.

## Samples

| ids                         | key                    |
|-----------------------------|------------------------|
| word 1                      | `/+`                   |
| word 12                     | `A+`                   |
| word 64                     | `/.+`                  |
| triple -2                   | `0-`                   |
| word 3'082'113              | `9kS/+`                |
| triples -5, -1, words 1, 2, 17 | `3-/-/+0+F+`        |
| words 135, 158, 201, 210, 329  | `05+0S+17+1G+37+` (`groups::CH_2019_MIO`) |
| formula 21 and phrases      | `J=8jId-I1A-...`       |
| words 1, 2 (prime)          | the 64-bit integer, no text key |

The url form of `3-/-/+0+F+` is `3-/-/_0_F_` (`group_id_url::to_url`), because
a url reads a `+` as a space; `group_id_url::from_url` reverses it.

## Searching a phrase within a key

Without fixed slots `0U+` is also the end of `10U+`, so a `LIKE '%0U+%'` would
find the wrong groups. A phrase is searched with the regular expression of
`group_id_url::phrase_pattern`, which requires the start of the key or a sign
char before the phrase, e.g. `(^|[-=()+<>_])0U\+`. `sql_creator::key_match`
writes it as `group_id ~ $1` for postgres and as `REGEXP_LIKE(group_id, ?, 'c')`
for mysql, both case-sensitive (`sql_par_type::LIKE_KEY`).

## Rules

- Never pad a key or compare a key by position; the key has no fixed length.
- Never compare a text key with a number: php reports e.g. `'/x-' <= 0` as true
  (see `db_object_multi::is_id_set`).
- Build a key only with `group_id::get_id` / `result_id::get_id` and read it only
  with `group_id::get_array`, so that the format lives in one place.
