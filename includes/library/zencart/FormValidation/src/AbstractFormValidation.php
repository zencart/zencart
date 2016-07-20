<?php
/**
 * Class FormValidation
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

namespace ZenCart\FormValidation;
use Valitron\Validator;

/**
 * Class FormValidation
 * @package ZenCart\FormValidation]
 */
Abstract class AbstractFormValidation implements FormValidationInterface
{

    /**
     * FormValidation constructor.
     */
    public function __construct()
    {
        $this->errors = array();
    }

    /**
     * @param $validationEntries
     * @return bool
     */
    public function validate($validationEntries)
    {
        $nameValues = $this->buildNameValues($validationEntries);
        $valitron = new Validator($nameValues);
        $this->manageRequiredEntries($valitron, $validationEntries);
        $result = $valitron->validate();
        $this->errors = $this->rewriteFieldNames($valitron->errors());
        return $result;
    }

    /**
     * @param $validationEntries
     * @return array
     */
    private function buildNameValues($validationEntries) {
        $nameValues = array();
        foreach ($validationEntries as $validationEntry) {
            $nameValues[$validationEntry['name']] = $validationEntry['value'];
        }
        return $nameValues;
    }

    /**
     * @param $valitron
     * @param $validationEntries
     */
    private function manageRequiredEntries($valitron, $validationEntries)
    {
        foreach ($validationEntries as $validationEntry) {
            if ($validationEntry['validations']['required']) {
                $valitron->rule('required', $validationEntry['name']);
            }
            if (count($validationEntry['validations']['rules']) > 0) {
                $this->processValitronRules($valitron, $validationEntry['name'], $validationEntry['validations']['rules']);
            }
        }
    }

    /**
     * @param $valitron
     * @param $fieldName
     * @param $rules
     */
    private function processValitronRules($valitron, $fieldName, $rules)
    {
        foreach ($rules as $rule) {
            if (isset($rule['dotNotation'])) {
                $fieldName .= $rule['dotNotation'];
            }
            $params0 = isset($rule['params'][0]) ? $rule['params'][0]: null;
            $params1 = isset($rule['params'][1]) ? $rule['params'][1]: null;
            $params2 = isset($rule['params'][2]) ? $rule['params'][2]: null;
            $valitron->rule($rule['type'], $fieldName, $params0, $params1, $params2);
        }
    }

    /**
     * @param $validationErrors
     * @return array
     */
    private function rewriteFieldNames($validationErrors)
    {
        $errors = array();
        foreach ($validationErrors as $validationErrorKey => $validationErrorDetail) {
            $errors['entry_field_' . $validationErrorKey] = $validationErrorDetail;
        }
        return $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
