-- --------------------------------------------------------

--
-- table structure to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups
(
    group_id    char(112)     NOT NULL,
    user_id     bigint        NOT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE user_groups IS 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';
COMMENT ON COLUMN user_groups.group_id IS 'the 512-bit prime index to find the user ';
COMMENT ON COLUMN user_groups.user_id IS 'the changer of the ';
COMMENT ON COLUMN user_groups.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN user_groups.description IS 'the user specific description for mouse over helps';

--
-- table structure to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups_prime
(
    group_id    bigint        NOT NULL,
    user_id     bigint        NOT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE user_groups_prime IS 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';
COMMENT ON COLUMN user_groups_prime.group_id IS 'the 64-bit prime index to find the user ';
COMMENT ON COLUMN user_groups_prime.user_id IS 'the changer of the ';
COMMENT ON COLUMN user_groups_prime.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN user_groups_prime.description IS 'the user specific description for mouse over helps';

--
-- table structure to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups_big
(
    group_id    text          NOT NULL,
    user_id     bigint        NOT NULL,
    group_name  text      DEFAULT NULL,
    description text      DEFAULT NULL
);

COMMENT ON TABLE user_groups_big IS 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';
COMMENT ON COLUMN user_groups_big.group_id IS 'the text index for more than 16 phrases to find the ';
COMMENT ON COLUMN user_groups_big.user_id IS 'the changer of the ';
COMMENT ON COLUMN user_groups_big.group_name IS 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
COMMENT ON COLUMN user_groups_big.description IS 'the user specific description for mouse over helps';

