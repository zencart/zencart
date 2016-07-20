<?php
/**
 * Class FormValidationInterface
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\FormValidation;

/**
 * Interface FormValidationInterface
 * @package ZenCart\FormValidation
 */
interface FormValidationInterface
{

    public function __construct();
    public function validate($validationEntries);
    public function getErrors();
}
