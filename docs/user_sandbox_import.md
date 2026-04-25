User sandbox
------------

# import

## sample formula "increase"

Assume many users use the JSON import for the formula "increase" (if a cell is empty, the value is "null" or the field
is
missing in the import file)

|                 | User 1            | User 2     | User 3            | User 4   | User 5            | User 6   |
|-----------------|-------------------|------------|-------------------|----------|-------------------|----------|
| **name**        | increase          | increase   | increase          | increase | increase          | increase |
| **description** | by default yearly | in percent | by default yearly |          | "" (empty string) |          |
| **code_id**     | increase          | increase   |                   |          |                   | percent  |
| **message**     | OK                | OK         | OK                | OK       | OK                | reject   |

The result should be these rows in the database

|                 | Standard          | User 2     | User 5            | 
|-----------------|-------------------|------------|-------------------|
| **id**          | 1                 | 1          | 1                 |
| **owner**       | User 1            |            |                   |
| **name**        | increase          |            |                   |
| **description** | by default yearly | in percent | "" (empty string) |
| **code_id**     | increase          |            |                   | 

Based on the database, the output to the users will be:

|                 | User 1            | User 2     | User 3            | User 4            | User 5            | User 6            |
|-----------------|-------------------|------------|-------------------|-------------------|-------------------|-------------------|
| **name**        | increase          | increase   | increase          | increase          | increase          | increase          |
| **description** | by default yearly | in percent | by default yearly | by default yearly | "" (empty string) | by default yearly |
| **code_id**     | increase          | increase   | increase          | increase          | increase          | increase          |

The output for the user is the "best known" value, e.g. for the field "description". For some fields like "exclude" the default value is "null" which means "do not overwrite". 

### code functions

To detect if the import object could be merged with a database row, the functions "get_similar" and "is_similar" are used. To detect, if a reject message should be created, the function "is_same" is used.
