<?php

namespace Symfony\Cmf\Bundle\CoreBundle\Model;

/**
 * An interface for (potentially) translatable models.
 *
 * A metadata listener configures if the model really is translated or not.
 * The locale will be set to boolean false if translations are disabled.
 */
interface TranslatableInterface
{
    /**
     * @return string|boolean The locale of this model or false if
     *      translations are disabled in this project.
     */
    public function getLocale();

    /**
     * @param string|boolean $locale The local for this model, or false if
     *      translations are disabled in this project.
     */
    public function setLocale($locale);
}