
-- --------------------------------------------------------

--
-- table structure for public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard_prime
(
    group_id      bigint     NOT NULL COMMENT = 'the 64-bit prime index to find the numeric result',
    numeric_value double     NOT NULL COMMENT = 'the numeric value given by the user',
    source_id     bigint DEFAULT NULL COMMENT = 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected numeric results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_standard
(
    group_id      char(112) NOT NULL COMMENT = 'the 512-bit prime index to find the numeric result',
    numeric_value double    NOT NULL COMMENT = 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected numeric results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results
(
    group_id      char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the numeric result',
    numeric_value double        NOT NULL COMMENT = 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for numeric results related to up to 16 phrases';

--
-- table structure for user specific changes of numeric results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results
(
    group_id      char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the user numeric result',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the numeric result',
    numeric_value double    DEFAULT NULL COMMENT = 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for user specific changes of numeric results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_prime
(
    group_id      bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the numeric result',
    numeric_value double        NOT NULL COMMENT = 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for the most often requested numeric results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested numeric results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_prime
(
    group_id      bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the user values',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the numeric result',
    numeric_value double    DEFAULT NULL COMMENT = 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'to store the user specific changes for the most often requested numeric results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_big
(
    group_id      text          NOT NULL COMMENT = 'the variable text index to find numeric result',
    numeric_value double        NOT NULL COMMENT = 'the numeric value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for numeric results related to more than 16 phrases';

--
-- table structure to store the user specific changes of numeric results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_big
(
    group_id      text          NOT NULL COMMENT = 'the text index to find the user values related to more than 16 phrases',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the numeric result',
    numeric_value double    DEFAULT NULL COMMENT = 'the user specific numeric value change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'to store the user specific changes of numeric results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard_prime
(
    group_id   bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the text result',
    text_value text          NOT NULL COMMENT = 'the text value given by the user',
    source_id  bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected text results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected text results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_text_standard
(
    group_id   char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the text result',
    text_value text          NOT NULL COMMENT = 'the text value given by the user',
    source_id  bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected text results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text
(
    group_id      char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the text result',
    text_value    text          NOT NULL COMMENT = 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for text results related to up to 16 phrases';

--
-- table structure for user specific changes of text results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text
(
    group_id      char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the user text result',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the text result',
    text_value    text      DEFAULT NULL COMMENT = 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for user specific changes of text results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_text_prime
(
    group_id      bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the text result',
    text_value    text          NOT NULL COMMENT = 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for the most often requested text results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested text results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_text_prime
(
    group_id      bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the user values',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the text result',
    text_value    text      DEFAULT NULL COMMENT = 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'to store the user specific changes for the most often requested text results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_text_big
(
    group_id      text          NOT NULL COMMENT = 'the variable text index to find text result',
    text_value    text          NOT NULL COMMENT = 'the text value given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for text results related to more than 16 phrases';

--
-- table structure to store the user specific changes of text results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_text_big
(
    group_id      text          NOT NULL COMMENT = 'the text index to find the user values related to more than 16 phrases',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the text result',
    text_value    text      DEFAULT NULL COMMENT = 'the user specific text value change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'to store the user specific changes of text results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard_prime
(
    group_id   bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the time result',
    time_value timestamp     NOT NULL COMMENT = 'the timestamp given by the user',
    source_id  bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected time results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected time results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_time_standard
(
    group_id   char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the time result',
    time_value timestamp     NOT NULL COMMENT = 'the timestamp given by the user',
    source_id  bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected time results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time
(
    group_id      char(112) NOT NULL COMMENT = 'the 512-bit prime index to find the time result',
    time_value    timestamp NOT NULL COMMENT = 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for time results related to up to 16 phrases';

--
-- table structure for user specific changes of time results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time
(
    group_id      char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the user time result',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the time result',
    time_value    timestamp DEFAULT NULL COMMENT = 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for user specific changes of time results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_time_prime
(
    group_id      bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the time result',
    time_value    timestamp     NOT NULL COMMENT = 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for the most often requested time results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested time results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_time_prime
(
    group_id      bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the user values',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the time result',
    time_value    timestamp DEFAULT NULL COMMENT = 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'to store the user specific changes for the most often requested time results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_time_big
(
    group_id      text          NOT NULL COMMENT = 'the variable text index to find time result',
    time_value    timestamp     NOT NULL COMMENT = 'the timestamp given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for time results related to more than 16 phrases';

--
-- table structure to store the user specific changes of time results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_time_big
(
    group_id      text          NOT NULL COMMENT = 'the text index to find the user values related to more than 16 phrases',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the time result',
    time_value    timestamp DEFAULT NULL COMMENT = 'the user specific timestamp change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'to store the user specific changes of time results related to more than 16 phrases';

-- --------------------------------------------------------

--
-- table structure for public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard_prime
(
    group_id   bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the geo result',
    geo_value  point         NOT NULL COMMENT = 'the geolocation given by the user',
    source_id  bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected geo results related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';

--
-- table structure for public unprotected geo results that have never changed the owner, does not have a description and are rarely updated
--

CREATE TABLE IF NOT EXISTS results_geo_standard
(
    group_id   char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the geo result',
    geo_value  point         NOT NULL COMMENT = 'the geolocation given by the user',
    source_id  bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for public unprotected geo results that have never changed the owner, does not have a description and are rarely updated';

-- --------------------------------------------------------

--
-- table structure for geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo
(
    group_id      char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the geo result',
    geo_value     point         NOT NULL COMMENT = 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for geo results related to up to 16 phrases';

--
-- table structure for user specific changes of geo results related to up to 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo
(
    group_id      char(112)     NOT NULL COMMENT = 'the 512-bit prime index to find the user geo result',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the geo result',
    geo_value     point     DEFAULT NULL COMMENT = 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for user specific changes of geo results related to up to 16 phrases';

-- --------------------------------------------------------

--
-- table structure for the most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS results_geo_prime
(
    group_id      bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the geo result',
    geo_value     point         NOT NULL COMMENT = 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for the most often requested geo results related up to four prime phrase';

--
-- table structure to store the user specific changes for the most often requested geo results related up to four prime phrase
--

CREATE TABLE IF NOT EXISTS user_results_geo_prime
(
    group_id      bigint        NOT NULL COMMENT = 'the 64-bit prime index to find the user values',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the geo result',
    geo_value     point     DEFAULT NULL COMMENT = 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'to store the user specific changes for the most often requested geo results related up to four prime phrase';

-- --------------------------------------------------------

--
-- table structure for geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS results_geo_big
(
    group_id      text          NOT NULL COMMENT = 'the variable text index to find geo result',
    geo_value     point         NOT NULL COMMENT = 'the geolocation given by the user',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    user_id       bigint    DEFAULT NULL COMMENT = 'the owner / creator of the value',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'for geo results related to more than 16 phrases';

--
-- table structure to store the user specific changes of geo results related to more than 16 phrases
--

CREATE TABLE IF NOT EXISTS user_results_geo_big
(
    group_id      text          NOT NULL COMMENT = 'the text index to find the user values related to more than 16 phrases',
    user_id       bigint        NOT NULL COMMENT = 'the changer of the geo result',
    geo_value     point     DEFAULT NULL COMMENT = 'the user specific geolocation change',
    source_id     bigint    DEFAULT NULL COMMENT = 'the source of the value as given by the user',
    last_update   timestamp DEFAULT NULL COMMENT = 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation',
    excluded      smallint  DEFAULT NULL COMMENT = 'true if a user, but not all, have removed it',
    share_type_id smallint  DEFAULT NULL COMMENT = 'to restrict the access',
    protect_id    smallint  DEFAULT NULL COMMENT = 'to protect against unwanted changes'
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'to store the user specific changes of geo results related to more than 16 phrases';
