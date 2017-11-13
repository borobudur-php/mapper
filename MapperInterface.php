<?php
/**
 * This file is part of the Borobudur package.
 *
 * (c) 2017 Borobudur <http://borobudur.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Borobudur\Component\Mapper;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
interface MapperInterface
{
    /**
     * Set the object to be map.
     *
     * @param mixed $data
     *
     * @return MapperInterface
     */
    public function map($data): MapperInterface;

    /**
     * Excludes fields from mapping.
     *
     * @param array $fields
     *
     * @return MapperInterface
     */
    public function excludes(array $fields): MapperInterface;

    /**
     * Only mapping from current fields.
     *
     * @param array $fields
     *
     * @return MapperInterface
     */
    public function only(array $fields): MapperInterface;

    /**
     * Fill the data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function fill($data);
}
