<?php


use Bristolian\Exception\DataEncodingException;

function convertToValueSafe(mixed $value): mixed
{
    [$error, $info] = \convertToValue($value);

    if ($error !== null) {
        // @codeCoverageIgnoreStart
        throw new DataEncodingException("Could not convert object to flat - non-objects variables. ", $error);
        // @codeCoverageIgnoreEnd
    }

    return $info;
}


/**
 * @param $value
 *
 * @return array{string, null}|array{null, mixed}
 */
function convertToValue(mixed $value)
{
    if (is_scalar($value) === true) {
        return [
            null,
            $value
        ];
    }
    if ($value === null) {
        return [
            null,
            null
        ];
    }

    $callable = [$value, 'toArray'];
    if (is_object($value) === true && is_callable($callable)) {
        return [
            null,
            $callable()
        ];
    }
    if (is_object($value) === true) {
        if ($value instanceof \DateTimeInterface) {
            return [
                null,
                $value->format("Y-m-d\TH:i:sP")
            ];
        }
        if ($value instanceof \BackedEnum) {
            return [
                null,
                $value->value
            ];
        }
    }

    if (is_array($value) === true) {
        $values = [];
        foreach ($value as $key => $entry) {
            [$error, $value] = convertToValue($entry);

            if ($error !== null) {
                return [$error, null];
            }
            $values[$key] = $value;
        }

        return [
            null,
            $values
        ];
    }

    if (is_object($value) === true) {
        return [
            sprintf(
                "Unsupported type [%s] of class [%s] for toArray.",
                gettype($value),
                get_class($value)
            ),
            null
        ];
    }

    return [
        sprintf(
            "Unsupported type [%s] for toArray.",
            gettype($value)
        ),
        null
    ];
}

/**
 * Generate a cryptographically secure random token.
 *
 * Generates 32 random bytes and base64 encodes them, resulting in
 * approximately 44 characters (base64 encoding adds ~33% overhead).
 * Output is URL-safe (base64url: - and _ instead of + and /).
 *
 * @return string The generated token
 */
function generateSecureToken(): string
{
    $randomBytes = random_bytes(32);
    $token = rtrim(base64_encode($randomBytes), '=');
    $token = strtr($token, '+/', '-_');

    return $token;
}
