<?php

/**
 * This file is part of the ramsey/uuid-doctrine library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <http://benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Uuid\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function is_object;
use function is_string;
use function method_exists;

/**
 * Field type mapping for the Doctrine Database Abstraction Layer (DBAL).
 *
 * UUID fields will be stored as a string in the database and converted back to
 * the Uuid value object when querying.
 */
class UuidType extends GuidType
{
    public const NAME = 'uuid';

    /**
     * {@inheritdoc}
     *
     * @param string|UuidInterface|null $value
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?UuidInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof UuidInterface) {
            return $value;
        }

        try {
            $uuid = Uuid::fromString($value);
        } catch (InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, static::NAME);
        }

        return $uuid;
    }

    /**
     * {@inheritdoc}
     *
     * @param UuidInterface|string|null $value
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (
            $value instanceof UuidInterface
            || (
                (is_string($value)
                || (is_object($value) && method_exists($value, '__toString')))
                && Uuid::isValid((string) $value)
            )
        ) {
            return (string) $value;
        }

        throw ConversionException::conversionFailed($value, static::NAME);
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return [self::NAME];
    }
}
