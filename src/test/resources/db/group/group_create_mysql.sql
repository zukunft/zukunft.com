-- --------------------------------------------------------
--
-- table structure to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS `groups` (
    group_id    char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the group',
    user_id     bigint    DEFAULT NULL COMMENT 'the owner / creator of the group',
    group_name  text      DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text      DEFAULT NULL COMMENT 'the user specific description for mouse over helps',
    PRIMARY KEY (group_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';

--
-- table structure to save user specific changes to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups (
    group_id    char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user group',
    user_id     bigint        NOT NULL COMMENT 'the changer of the group',
    group_name  text      DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text      DEFAULT NULL COMMENT 'the user specific description for mouse over helps',
    PRIMARY KEY (group_id, user_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';

--
-- table structure to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS groups_prime (
    group_id    bigint     NOT NULL COMMENT 'the 64-bit prime index to find the group',
    user_id     bigint DEFAULT NULL COMMENT 'the owner / creator of the group',
    group_name  text   DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text   DEFAULT NULL COMMENT 'the user specific description for mouse over helps',
    PRIMARY KEY (group_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';

--
-- table structure to save user specific changes to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups_prime (
    group_id    bigint     NOT NULL COMMENT 'the 64-bit prime index to find the user group',
    user_id     bigint     NOT NULL COMMENT 'the changer of the group',
    group_name  text   DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text   DEFAULT NULL COMMENT 'the user specific description for mouse over helps',
    PRIMARY KEY (group_id, user_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';

--
-- table structure to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS groups_big (
    group_id    char(255)     NOT NULL COMMENT 'the variable text index to find group',
    user_id     bigint    DEFAULT NULL COMMENT 'the owner / creator of the group',
    group_name  text      DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text      DEFAULT NULL COMMENT 'the user specific description for mouse over helps',
    PRIMARY KEY (group_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';

--
-- table structure to save user specific changes to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order
--

CREATE TABLE IF NOT EXISTS user_groups_big (
    group_id    char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the group',
    user_id     bigint        NOT NULL COMMENT 'the changer of the group',
    group_name  text      DEFAULT NULL COMMENT 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)',
    description text      DEFAULT NULL COMMENT 'the user specific description for mouse over helps',
    PRIMARY KEY (group_id, user_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';
