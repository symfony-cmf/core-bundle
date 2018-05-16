<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Form\Data;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
interface Converter
{
    /**
     * Converts a given data transfer object into a document.
     *
     * @param object $dto
     * @param object $document
     *
     * @return object
     */
    public function toDocument($dto, $document);


    /**
     * Converts a given document in its data transfer object.
     *
     * @param object $document
     * @param object $dto
     *
     * @return object
     */
    public function toDataTransferObject($document, $dto);
}
