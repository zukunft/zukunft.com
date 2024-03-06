
-- --------------------------------------------------------

--
-- table structure for public unprotected numeric values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard_prime
(
    phrase_id_1   smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_2   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_3   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_4   smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    numeric_value double       NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint   DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected numeric values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected numeric values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_standard
(
    group_id      char(112) NOT NULL COMMENT 'the 512-bit prime index to find the numeric value',
    numeric_value double    NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected numeric values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for numeric values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS `values`
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the numeric value',
    numeric_value double        NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for numeric values related to up to 16 phrases';

--
-- table structure for user specific changes of numeric values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user numeric value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the numeric value',
    numeric_value double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key numeric value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for user specific changes of numeric values related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested numeric values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a numeric value',
    numeric_value double        NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for the most often requested numeric values related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested numeric values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is with the user id  part of the prime key for a numeric value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a numeric value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a numeric value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a numeric value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the numeric value',
    numeric_value double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key numeric value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested numeric values related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for numeric values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_big
(
    group_id      char(255)     NOT NULL COMMENT 'the variable text index to find numeric value',
    numeric_value double        NOT NULL COMMENT 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for numeric values related to more than 16 phrases';

--
-- table structure to store the user specific changes of numeric values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_big
(
    group_id      char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the numeric value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the numeric value',
    numeric_value double    DEFAULT NULL COMMENT 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key numeric value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of numeric values related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard_prime
(
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    text_value  text         NOT NULL COMMENT 'the text value given by the user',
    source_id   bigint   DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected text values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected text values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_text_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the text value',
    text_value text          NOT NULL COMMENT 'the text value given by the user',
    source_id  bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected text values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for text values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the text value',
    text_value    text          NOT NULL COMMENT 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for text values related to up to 16 phrases';

--
-- table structure for user specific changes of text values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user text value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the text value',
    text_value    text      DEFAULT NULL COMMENT 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key text value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for user specific changes of text values related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_text_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a text value',
    text_value    text          NOT NULL COMMENT 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for the most often requested text values related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested text values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_text_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is with the user id  part of the prime key for a text value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a text value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a text value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a text value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the text value',
    text_value    text      DEFAULT NULL COMMENT 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key text value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested text values related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for text values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_text_big
(
    group_id      char(255)     NOT NULL COMMENT 'the variable text index to find text value',
    text_value    text          NOT NULL COMMENT 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for text values related to more than 16 phrases';

--
-- table structure to store the user specific changes of text values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_text_big
(
    group_id      char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the text value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the text value',
    text_value    text      DEFAULT NULL COMMENT 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key text value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of text values related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_time_standard_prime
(
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    time_value  timestamp    NOT NULL COMMENT 'the timestamp given by the user',
    source_id   bigint   DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected time values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected time values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_time_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time value',
    time_value timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    source_id  bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected time values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for time values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_time
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the time value',
    time_value    timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for time values related to up to 16 phrases';

--
-- table structure for user specific changes of time values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_time
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user time value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the time value',
    time_value    timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key time value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for user specific changes of time values related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested time values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_time_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a time value',
    time_value    timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for the most often requested time values related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested time values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_time_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is with the user id  part of the prime key for a time value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a time value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a time value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a time value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the time value',
    time_value    timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key time value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested time values related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for time values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_time_big
(
    group_id      char(255)     NOT NULL COMMENT 'the variable text index to find time value',
    time_value    timestamp     NOT NULL COMMENT 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for time values related to more than 16 phrases';

--
-- table structure to store the user specific changes of time values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_time_big
(
    group_id      char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the time value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the time value',
    time_value    timestamp DEFAULT NULL COMMENT 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key time value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of time values related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected geo values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_geo_standard_prime
(
    phrase_id_1 smallint     NOT NULL COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_2 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_3 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_4 smallint DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    geo_value   point        NOT NULL COMMENT 'the geolocation given by the user',
    source_id   bigint   DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected geo values related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected geo values that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS values_geo_standard
(
    group_id   char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the geo value',
    geo_value  point         NOT NULL COMMENT 'the geolocation given by the user',
    source_id  bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for public unprotected geo values that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for geo values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS values_geo
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the geo value',
    geo_value     point         NOT NULL COMMENT 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for geo values related to up to 16 phrases';

--
-- table structure for user specific changes of geo values related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_geo
(
    group_id      char(112)     NOT NULL COMMENT 'the 512-bit prime index to find the user geo value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the geo value',
    geo_value     point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key geo value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for user specific changes of geo values related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested geo values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS values_geo_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is part of the prime key for a geo value',
    geo_value     point         NOT NULL COMMENT 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for the most often requested geo values related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested geo values related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_values_geo_prime
(
    phrase_id_1   smallint      NOT NULL COMMENT 'phrase id that is with the user id  part of the prime key for a geo value',
    phrase_id_2   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a geo value',
    phrase_id_3   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a geo value',
    phrase_id_4   smallint  DEFAULT 0    COMMENT 'phrase id that is with the user id  part of the prime key for a geo value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the geo value',
    geo_value     point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key geo value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes for the most often requested geo values related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for geo values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS values_geo_big
(
    group_id      char(255)     NOT NULL COMMENT 'the variable text index to find geo value',
    geo_value     point         NOT NULL COMMENT 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'for geo values related to more than 16 phrases';

--
-- table structure to store the user specific changes of geo values related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_values_geo_big
(
    group_id      char(255)     NOT NULL COMMENT 'the text index for more than 16 phrases to find the geo value',
    user_id       bigint        NOT NULL COMMENT 'the changer of the geo value',
    geo_value     point     DEFAULT NULL COMMENT 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key geo value',
    last_update   timestamp DEFAULT NULL COMMENT 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT 'to store the user specific changes of geo values related to more than 16 phrases';
