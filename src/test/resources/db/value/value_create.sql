
-- --------------------------------------------------------

--
-- table structure for public unprotected numeric values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard_prime
(
    phrase_id_1   smallint         NOT NULL,
    phrase_id_2   smallint         DEFAULT 0,
    phrase_id_3   smallint         DEFAULT 0,
    phrase_id_4   smallint         DEFAULT 0,
    numeric_value double precision NOT NULL,
    source_id     bigint           DEFAULT NULL
);

COMMENT ON TABLE values_standard_prime                IS 'for public unprotected numeric values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_standard_prime.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_standard_prime.source_id     IS 'the source of the value as given by the user';

--
-- table structure for public unprotected numeric values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard
(
    group_id      char(112)        PRIMARY KEY,
    numeric_value double precision NOT NULL,
    source_id     bigint           DEFAULT NULL
);

COMMENT ON TABLE values_standard                IS 'for public unprotected numeric values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_standard.group_id      IS 'the 512-bit prime index to find the numeric value';
COMMENT ON COLUMN values_standard.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_standard.source_id     IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for numeric values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values
(
    group_id        char(112)        PRIMARY KEY,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values                IS 'for numeric values related to up to 16 phrases';
COMMENT ON COLUMN values.group_id      IS 'the 512-bit prime index to find the numeric value';
COMMENT ON COLUMN values.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of numeric values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values
(
    group_id      char(112)        NOT NULL,
    user_id       bigint           NOT NULL,
    numeric_value double precision DEFAULT NULL,
    source_id     bigint           DEFAULT NULL,
    last_update   timestamp        DEFAULT NULL,
    excluded      smallint         DEFAULT NULL,
    share_type_id smallint         DEFAULT NULL,
    protect_id    smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values                IS 'for user specific changes of numeric values related to up to 16 phrases';
COMMENT ON COLUMN user_values.group_id      IS 'the 512-bit prime index to find the user numeric value';
COMMENT ON COLUMN user_values.user_id       IS 'the changer of the numeric value';
COMMENT ON COLUMN user_values.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key numeric value';
COMMENT ON COLUMN user_values.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested numeric values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_prime
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT 0,
    phrase_id_3     smallint         DEFAULT 0,
    phrase_id_4     smallint         DEFAULT 0,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values_prime                IS 'for the most often requested numeric values related up to four prime phrase';
COMMENT ON COLUMN values_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a numeric value';
COMMENT ON COLUMN values_prime.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested numeric values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_prime
(
    phrase_id_1     smallint         NOT NULL,
    phrase_id_2     smallint         DEFAULT 0,
    phrase_id_3     smallint         DEFAULT 0,
    phrase_id_4     smallint         DEFAULT 0,
    user_id         bigint           NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values_prime                IS 'to store the user specific changes for the most often requested numeric values related up to four prime phrase';
COMMENT ON COLUMN user_values_prime.phrase_id_1   IS 'phrase id that is with the user id part of the prime key for a numeric value';
COMMENT ON COLUMN user_values_prime.phrase_id_2   IS 'phrase id that is with the user id part of the prime key for a numeric value';
COMMENT ON COLUMN user_values_prime.phrase_id_3   IS 'phrase id that is with the user id part of the prime key for a numeric value';
COMMENT ON COLUMN user_values_prime.phrase_id_4   IS 'phrase id that is with the user id part of the prime key for a numeric value';
COMMENT ON COLUMN user_values_prime.user_id       IS 'the changer of the numeric value';
COMMENT ON COLUMN user_values_prime.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values_prime.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key numeric value';
COMMENT ON COLUMN user_values_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for numeric values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_big
(
    group_id        text             PRIMARY KEY,
    numeric_value   double precision NOT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    user_id         bigint           DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE values_big                IS 'for numeric values related to more than 16 phrases';
COMMENT ON COLUMN values_big.group_id      IS 'the variable text index to find numeric value';
COMMENT ON COLUMN values_big.numeric_value IS 'the numeric value given by the user';
COMMENT ON COLUMN values_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of numeric values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_big
(
    group_id        text             NOT NULL,
    user_id         bigint           NOT NULL,
    numeric_value   double precision DEFAULT NULL,
    source_id       bigint           DEFAULT NULL,
    last_update     timestamp        DEFAULT NULL,
    excluded        smallint         DEFAULT NULL,
    share_type_id   smallint         DEFAULT NULL,
    protect_id      smallint         DEFAULT NULL
);

COMMENT ON TABLE user_values_big                IS 'to store the user specific changes of numeric values related to more than 16 phrases';
COMMENT ON COLUMN user_values_big.group_id      IS 'the text index for more than 16 phrases to find the numeric value';
COMMENT ON COLUMN user_values_big.user_id       IS 'the changer of the numeric value';
COMMENT ON COLUMN user_values_big.numeric_value IS 'the user specific numeric value change';
COMMENT ON COLUMN user_values_big.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key numeric value';
COMMENT ON COLUMN user_values_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard_prime
(
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    phrase_id_4 smallint  DEFAULT 0,
    text_value  text      NOT NULL,
    source_id   bigint    DEFAULT NULL
);

COMMENT ON TABLE values_text_standard_prime             IS 'for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_text_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_standard_prime.text_value IS 'the text value given by the user';
COMMENT ON COLUMN values_text_standard_prime.source_id  IS 'the source of the value as given by the user';

--
-- table structure for public unprotected text values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard
(
    group_id   char(112) PRIMARY KEY,
    text_value text      NOT NULL,
    source_id  bigint    DEFAULT NULL
);

COMMENT ON TABLE values_text_standard             IS 'for public unprotected text values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_text_standard.group_id   IS 'the 512-bit prime index to find the text value';
COMMENT ON COLUMN values_text_standard.text_value IS 'the text value given by the user';
COMMENT ON COLUMN values_text_standard.source_id  IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for text values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text
(
    group_id      char(112) PRIMARY KEY,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text                IS 'for text values related to up to 16 phrases';
COMMENT ON COLUMN values_text.group_id      IS 'the 512-bit prime index to find the text value';
COMMENT ON COLUMN values_text.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of text values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text
(
    group_id      char(112) NOT NULL,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text                IS 'for user specific changes of text values related to up to 16 phrases';
COMMENT ON COLUMN user_values_text.group_id      IS 'the 512-bit prime index to find the user text value';
COMMENT ON COLUMN user_values_text.user_id       IS 'the changer of the text value';
COMMENT ON COLUMN user_values_text.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key text value';
COMMENT ON COLUMN user_values_text.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_text_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text_prime                IS 'for the most often requested text values related up to four prime phrase';
COMMENT ON COLUMN values_text_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a text value';
COMMENT ON COLUMN values_text_prime.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_text_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text_prime                IS 'to store the user specific changes for the most often requested text values related up to four prime phrase';
COMMENT ON COLUMN user_values_text_prime.phrase_id_1   IS 'phrase id that is with the user id part of the prime key for a text value';
COMMENT ON COLUMN user_values_text_prime.phrase_id_2   IS 'phrase id that is with the user id part of the prime key for a text value';
COMMENT ON COLUMN user_values_text_prime.phrase_id_3   IS 'phrase id that is with the user id part of the prime key for a text value';
COMMENT ON COLUMN user_values_text_prime.phrase_id_4   IS 'phrase id that is with the user id part of the prime key for a text value';
COMMENT ON COLUMN user_values_text_prime.user_id       IS 'the changer of the text value';
COMMENT ON COLUMN user_values_text_prime.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text_prime.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key text value';
COMMENT ON COLUMN user_values_text_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for text values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text_big
(
    group_id      text      PRIMARY KEY,
    text_value    text      NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_text_big                IS 'for text values related to more than 16 phrases';
COMMENT ON COLUMN values_text_big.group_id      IS 'the variable text index to find text value';
COMMENT ON COLUMN values_text_big.text_value    IS 'the text value given by the user';
COMMENT ON COLUMN values_text_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_text_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_text_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_text_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_text_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_text_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of text values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text_big
(
    group_id      text      NOT NULL,
    user_id       bigint    NOT NULL,
    text_value    text      DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_text_big                IS 'to store the user specific changes of text values related to more than 16 phrases';
COMMENT ON COLUMN user_values_text_big.group_id      IS 'the text index for more than 16 phrases to find the text value';
COMMENT ON COLUMN user_values_text_big.user_id       IS 'the changer of the text value';
COMMENT ON COLUMN user_values_text_big.text_value    IS 'the user specific text value change';
COMMENT ON COLUMN user_values_text_big.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key text value';
COMMENT ON COLUMN user_values_text_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_text_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_text_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_text_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_time_standard_prime
(
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    phrase_id_4 smallint  DEFAULT 0,
    time_value  timestamp NOT NULL,
    source_id   bigint    DEFAULT NULL
);

COMMENT ON TABLE values_time_standard_prime             IS 'for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_time_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_standard_prime.time_value IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time_standard_prime.source_id  IS 'the source of the value as given by the user';

--
-- table structure for public unprotected time values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_time_standard
(
    group_id   char(112) PRIMARY KEY,
    time_value timestamp NOT NULL,
    source_id  bigint    DEFAULT NULL
);

COMMENT ON TABLE values_time_standard             IS 'for public unprotected time values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_time_standard.group_id   IS 'the 512-bit prime index to find the time value';
COMMENT ON COLUMN values_time_standard.time_value IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time_standard.source_id  IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for time values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_time
(
    group_id      char(112) PRIMARY KEY,
    time_value    timestamp NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time                IS 'for time values related to up to 16 phrases';
COMMENT ON COLUMN values_time.group_id      IS 'the 512-bit prime index to find the time value';
COMMENT ON COLUMN values_time.time_value    IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_time.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of time values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_time
(
    group_id      char(112) NOT NULL,
    user_id       bigint    NOT NULL,
    time_value    timestamp DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time                IS 'for user specific changes of time values related to up to 16 phrases';
COMMENT ON COLUMN user_values_time.group_id      IS 'the 512-bit prime index to find the user time value';
COMMENT ON COLUMN user_values_time.user_id       IS 'the changer of the time value';
COMMENT ON COLUMN user_values_time.time_value    IS 'the user specific timestamp change';
COMMENT ON COLUMN user_values_time.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key time value';
COMMENT ON COLUMN user_values_time.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_time.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested time values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_time_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    time_value    timestamp NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time_prime                IS 'for the most often requested time values related up to four prime phrase';
COMMENT ON COLUMN values_time_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a time value';
COMMENT ON COLUMN values_time_prime.time_value    IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_time_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested time values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_time_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    user_id       bigint    NOT NULL,
    time_value    timestamp DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time_prime                IS 'to store the user specific changes for the most often requested time values related up to four prime phrase';
COMMENT ON COLUMN user_values_time_prime.phrase_id_1   IS 'phrase id that is with the user id part of the prime key for a time value';
COMMENT ON COLUMN user_values_time_prime.phrase_id_2   IS 'phrase id that is with the user id part of the prime key for a time value';
COMMENT ON COLUMN user_values_time_prime.phrase_id_3   IS 'phrase id that is with the user id part of the prime key for a time value';
COMMENT ON COLUMN user_values_time_prime.phrase_id_4   IS 'phrase id that is with the user id part of the prime key for a time value';
COMMENT ON COLUMN user_values_time_prime.user_id       IS 'the changer of the time value';
COMMENT ON COLUMN user_values_time_prime.time_value    IS 'the user specific timestamp change';
COMMENT ON COLUMN user_values_time_prime.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key time value';
COMMENT ON COLUMN user_values_time_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_time_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for time values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_time_big
(
    group_id      text      PRIMARY KEY,
    time_value    timestamp NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time_big                IS 'for time values related to more than 16 phrases';
COMMENT ON COLUMN values_time_big.group_id      IS 'the variable text index to find time value';
COMMENT ON COLUMN values_time_big.time_value    IS 'the timestamp given by the user';
COMMENT ON COLUMN values_time_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_time_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of time values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_time_big
(
    group_id      text      NOT NULL,
    user_id       bigint    NOT NULL,
    time_value    timestamp DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time_big                IS 'to store the user specific changes of time values related to more than 16 phrases';
COMMENT ON COLUMN user_values_time_big.group_id      IS 'the text index for more than 16 phrases to find the time value';
COMMENT ON COLUMN user_values_time_big.user_id       IS 'the changer of the time value';
COMMENT ON COLUMN user_values_time_big.time_value    IS 'the user specific timestamp change';
COMMENT ON COLUMN user_values_time_big.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key time value';
COMMENT ON COLUMN user_values_time_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_time_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for public unprotected geo values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_geo_standard_prime
(
    phrase_id_1 smallint  NOT NULL,
    phrase_id_2 smallint  DEFAULT 0,
    phrase_id_3 smallint  DEFAULT 0,
    phrase_id_4 smallint  DEFAULT 0,
    geo_value   point     NOT NULL,
    source_id   bigint    DEFAULT NULL
);

COMMENT ON TABLE values_geo_standard_prime             IS 'for public unprotected geo values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_geo_standard_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_standard_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_standard_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_standard_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_standard_prime.geo_value  IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo_standard_prime.source_id  IS 'the source of the value as given by the user';

--
-- table structure for public unprotected geo values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_geo_standard
(
    group_id   char(112) PRIMARY KEY,
    geo_value  point     NOT NULL,
    source_id  bigint    DEFAULT NULL
);

COMMENT ON TABLE values_geo_standard             IS 'for public unprotected geo values that have never changed the owner, does not have a description and are rarely updated';
COMMENT ON COLUMN values_geo_standard.group_id   IS 'the 512-bit prime index to find the geo value';
COMMENT ON COLUMN values_geo_standard.geo_value  IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo_standard.source_id  IS 'the source of the value as given by the user';

-- --------------------------------------------------------

--
-- table structure for geo values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_geo
(
    group_id      char(112) PRIMARY KEY,
    geo_value     point     NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_geo                IS 'for geo values related to up to 16 phrases';
COMMENT ON COLUMN values_geo.group_id      IS 'the 512-bit prime index to find the geo value';
COMMENT ON COLUMN values_geo.geo_value     IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_geo.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_geo.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_geo.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_geo.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_geo.protect_id    IS 'to protect against unwanted changes';

--
-- table structure for user specific changes of geo values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_geo
(
    group_id      char(112) NOT NULL,
    user_id       bigint    NOT NULL,
    geo_value     point     DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_geo                IS 'for user specific changes of geo values related to up to 16 phrases';
COMMENT ON COLUMN user_values_geo.group_id      IS 'the 512-bit prime index to find the user geo value';
COMMENT ON COLUMN user_values_geo.user_id       IS 'the changer of the geo value';
COMMENT ON COLUMN user_values_geo.geo_value     IS 'the user specific geolocation change';
COMMENT ON COLUMN user_values_geo.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key geo value';
COMMENT ON COLUMN user_values_geo.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_geo.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_geo.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_geo.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the most often requested geo values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_geo_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    geo_value     point     NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_geo_prime                IS 'for the most often requested geo values related up to four prime phrase';
COMMENT ON COLUMN values_geo_prime.phrase_id_1   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_prime.phrase_id_2   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_prime.phrase_id_3   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_prime.phrase_id_4   IS 'phrase id that is part of the prime key for a geo value';
COMMENT ON COLUMN values_geo_prime.geo_value     IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo_prime.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_geo_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_geo_prime.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_geo_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_geo_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_geo_prime.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes for the most often requested geo values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_geo_prime
(
    phrase_id_1   smallint  NOT NULL,
    phrase_id_2   smallint  DEFAULT 0,
    phrase_id_3   smallint  DEFAULT 0,
    phrase_id_4   smallint  DEFAULT 0,
    user_id       bigint    NOT NULL,
    geo_value     point     DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_geo_prime                IS 'to store the user specific changes for the most often requested geo values related up to four prime phrase';
COMMENT ON COLUMN user_values_geo_prime.phrase_id_1   IS 'phrase id that is with the user id part of the prime key for a geo value';
COMMENT ON COLUMN user_values_geo_prime.phrase_id_2   IS 'phrase id that is with the user id part of the prime key for a geo value';
COMMENT ON COLUMN user_values_geo_prime.phrase_id_3   IS 'phrase id that is with the user id part of the prime key for a geo value';
COMMENT ON COLUMN user_values_geo_prime.phrase_id_4   IS 'phrase id that is with the user id part of the prime key for a geo value';
COMMENT ON COLUMN user_values_geo_prime.user_id       IS 'the changer of the geo value';
COMMENT ON COLUMN user_values_geo_prime.geo_value     IS 'the user specific geolocation change';
COMMENT ON COLUMN user_values_geo_prime.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key geo value';
COMMENT ON COLUMN user_values_geo_prime.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_geo_prime.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_geo_prime.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_geo_prime.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for geo values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_geo_big
(
    group_id      text      PRIMARY KEY,
    geo_value     point     NOT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    user_id       bigint    DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE values_geo_big                IS 'for geo values related to more than 16 phrases';
COMMENT ON COLUMN values_geo_big.group_id      IS 'the variable text index to find geo value';
COMMENT ON COLUMN values_geo_big.geo_value     IS 'the geolocation given by the user';
COMMENT ON COLUMN values_geo_big.source_id     IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_geo_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_geo_big.user_id       IS 'the owner / creator of the value';
COMMENT ON COLUMN values_geo_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_geo_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN values_geo_big.protect_id    IS 'to protect against unwanted changes';

--
-- table structure to store the user specific changes of geo values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_geo_big
(
    group_id      text      NOT NULL,
    user_id       bigint    NOT NULL,
    geo_value     point     DEFAULT NULL,
    source_id     bigint    DEFAULT NULL,
    last_update   timestamp DEFAULT NULL,
    excluded      smallint  DEFAULT NULL,
    share_type_id smallint  DEFAULT NULL,
    protect_id    smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_geo_big                IS 'to store the user specific changes of geo values related to more than 16 phrases';
COMMENT ON COLUMN user_values_geo_big.group_id      IS 'the text index for more than 16 phrases to find the geo value';
COMMENT ON COLUMN user_values_geo_big.user_id       IS 'the changer of the geo value';
COMMENT ON COLUMN user_values_geo_big.geo_value     IS 'the user specific geolocation change';
COMMENT ON COLUMN user_values_geo_big.source_id     IS 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key geo value';
COMMENT ON COLUMN user_values_geo_big.last_update   IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_geo_big.excluded      IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_geo_big.share_type_id IS 'to restrict the access';
COMMENT ON COLUMN user_values_geo_big.protect_id    IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS values_time_series
(
    group_id             char(112) PRIMARY KEY,
    value_time_series_id bigint        NOT NULL,
    source_id            bigint    DEFAULT NULL,
    last_update          timestamp DEFAULT NULL,
    user_id              bigint    DEFAULT NULL,
    excluded             smallint  DEFAULT NULL,
    share_type_id        smallint  DEFAULT NULL,
    protect_id           smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time_series                       IS 'for the common parameters for a list of numbers that differ only by the timestamp';
COMMENT ON COLUMN values_time_series.group_id             IS 'the 512-bit prime index to find the time_series value';
COMMENT ON COLUMN values_time_series.value_time_series_id IS 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
COMMENT ON COLUMN values_time_series.source_id            IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time_series.last_update          IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time_series.user_id              IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time_series.excluded             IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time_series.share_type_id        IS 'to restrict the access';
COMMENT ON COLUMN values_time_series.protect_id           IS 'to protect against unwanted changes';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_values_time_series
(
    group_id             char(112)     NOT NULL,
    user_id              bigint        NOT NULL,
    value_time_series_id bigint        NOT NULL,
    source_id            bigint    DEFAULT NULL,
    last_update          timestamp DEFAULT NULL,
    excluded             smallint  DEFAULT NULL,
    share_type_id        smallint  DEFAULT NULL,
    protect_id           smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time_series                       IS 'for the common parameters for a list of numbers that differ only by the timestamp';
COMMENT ON COLUMN user_values_time_series.group_id             IS 'the 512-bit prime index to find the user time_series value';
COMMENT ON COLUMN user_values_time_series.user_id              IS 'the changer of the time_series value';
COMMENT ON COLUMN user_values_time_series.value_time_series_id IS 'the 64 bit integer which is unique for the standard and the user series';
COMMENT ON COLUMN user_values_time_series.source_id            IS 'one user can add different values from different sources, that have the same group, but a different value,so the source should be included in the unique key time_series value';
COMMENT ON COLUMN user_values_time_series.last_update          IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time_series.excluded             IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time_series.share_type_id        IS 'to restrict the access';
COMMENT ON COLUMN user_values_time_series.protect_id           IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS values_time_series_prime
(
    phrase_id_1          smallint  NOT NULL,
    phrase_id_2          smallint  DEFAULT 0,
    phrase_id_3          smallint  DEFAULT 0,
    phrase_id_4          smallint  DEFAULT 0,
    value_time_series_id bigint        NOT NULL,
    source_id            bigint    DEFAULT NULL,
    last_update          timestamp DEFAULT NULL,
    user_id              bigint    DEFAULT NULL,
    excluded             smallint  DEFAULT NULL,
    share_type_id        smallint  DEFAULT NULL,
    protect_id           smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time_series_prime                       IS 'for the common parameters for a list of numbers that differ only by the timestamp';
COMMENT ON COLUMN values_time_series_prime.phrase_id_1          IS 'phrase id that is part of the prime key for a time_series value';
COMMENT ON COLUMN values_time_series_prime.phrase_id_2          IS 'phrase id that is part of the prime key for a time_series value';
COMMENT ON COLUMN values_time_series_prime.phrase_id_3          IS 'phrase id that is part of the prime key for a time_series value';
COMMENT ON COLUMN values_time_series_prime.phrase_id_4          IS 'phrase id that is part of the prime key for a time_series value';
COMMENT ON COLUMN values_time_series_prime.value_time_series_id IS 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
COMMENT ON COLUMN values_time_series_prime.source_id            IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time_series_prime.last_update          IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time_series_prime.user_id              IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time_series_prime.excluded             IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time_series_prime.share_type_id        IS 'to restrict the access';
COMMENT ON COLUMN values_time_series_prime.protect_id           IS 'to protect against unwanted changes';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_values_time_series_prime
(
    phrase_id_1          smallint      NOT NULL,
    phrase_id_2          smallint  DEFAULT 0,
    phrase_id_3          smallint  DEFAULT 0,
    phrase_id_4          smallint  DEFAULT 0,
    user_id              bigint        NOT NULL,
    value_time_series_id bigint        NOT NULL,
    source_id            bigint    DEFAULT NULL,
    last_update          timestamp DEFAULT NULL,
    excluded             smallint  DEFAULT NULL,
    share_type_id        smallint  DEFAULT NULL,
    protect_id           smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time_series_prime                       IS 'for the common parameters for a list of numbers that differ only by the timestamp';
COMMENT ON COLUMN user_values_time_series_prime.phrase_id_1          IS 'phrase id that is with the user id part of the prime key for a time_series value';
COMMENT ON COLUMN user_values_time_series_prime.phrase_id_2          IS 'phrase id that is with the user id part of the prime key for a time_series value';
COMMENT ON COLUMN user_values_time_series_prime.phrase_id_3          IS 'phrase id that is with the user id part of the prime key for a time_series value';
COMMENT ON COLUMN user_values_time_series_prime.phrase_id_4          IS 'phrase id that is with the user id part of the prime key for a time_series value';
COMMENT ON COLUMN user_values_time_series_prime.user_id              IS 'the changer of the time_series value';
COMMENT ON COLUMN user_values_time_series_prime.value_time_series_id IS 'the 64 bit integer which is unique for the standard and the user series';
COMMENT ON COLUMN user_values_time_series_prime.source_id            IS 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key time_series value';
COMMENT ON COLUMN user_values_time_series_prime.last_update          IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time_series_prime.excluded             IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time_series_prime.share_type_id        IS 'to restrict the access';
COMMENT ON COLUMN user_values_time_series_prime.protect_id           IS 'to protect against unwanted changes';

-- --------------------------------------------------------

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS values_time_series_big
(
    group_id      text PRIMARY KEY,
    value_time_series_id bigint        NOT NULL,
    source_id            bigint    DEFAULT NULL,
    last_update          timestamp DEFAULT NULL,
    user_id              bigint    DEFAULT NULL,
    excluded             smallint  DEFAULT NULL,
    share_type_id        smallint  DEFAULT NULL,
    protect_id           smallint  DEFAULT NULL
);

COMMENT ON TABLE values_time_series_big                       IS 'for the common parameters for a list of numbers that differ only by the timestamp';
COMMENT ON COLUMN values_time_series_big.group_id             IS 'the variable text index to find time_series value';
COMMENT ON COLUMN values_time_series_big.value_time_series_id IS 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
COMMENT ON COLUMN values_time_series_big.source_id            IS 'the source of the value as given by the user';
COMMENT ON COLUMN values_time_series_big.last_update          IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN values_time_series_big.user_id              IS 'the owner / creator of the value';
COMMENT ON COLUMN values_time_series_big.excluded             IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN values_time_series_big.share_type_id        IS 'to restrict the access';
COMMENT ON COLUMN values_time_series_big.protect_id           IS 'to protect against unwanted changes';

--
-- table structure for the common parameters for a list of numbers that differ only by the timestamp
--

CREATE TABLE IF NOT EXISTS user_values_time_series_big
(
    group_id             text          NOT NULL,
    user_id              bigint        NOT NULL,
    value_time_series_id bigint        NOT NULL,
    source_id            bigint    DEFAULT NULL,
    last_update          timestamp DEFAULT NULL,
    excluded             smallint  DEFAULT NULL,
    share_type_id        smallint  DEFAULT NULL,
    protect_id           smallint  DEFAULT NULL
);

COMMENT ON TABLE user_values_time_series_big                       IS 'for the common parameters for a list of numbers that differ only by the timestamp';
COMMENT ON COLUMN user_values_time_series_big.group_id             IS 'the text index for more than 16 phrases to find the time_series value';
COMMENT ON COLUMN user_values_time_series_big.user_id              IS 'the changer of the time_series value';
COMMENT ON COLUMN user_values_time_series_big.value_time_series_id IS 'the 64 bit integer which is unique for the standard and the user series';
COMMENT ON COLUMN user_values_time_series_big.source_id            IS 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key time_series value';
COMMENT ON COLUMN user_values_time_series_big.last_update          IS 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation';
COMMENT ON COLUMN user_values_time_series_big.excluded             IS 'true if a user, but not all, have removed it';
COMMENT ON COLUMN user_values_time_series_big.share_type_id        IS 'to restrict the access';
COMMENT ON COLUMN user_values_time_series_big.protect_id           IS 'to protect against unwanted changes';
