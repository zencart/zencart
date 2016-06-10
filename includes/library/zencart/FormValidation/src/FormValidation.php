<?php

namespace ZenCart\FormValidation;
use Valitron\Validator;

/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 28/04/16
 * Time: 17:25
 */
class FormValidation
{

    public function __construct()
    {
        $this->errors = array();
    }

    public function validate($validationEntries)
    {
        $nameValues = $this->buildNameValues($validationEntries);
        $valitron = new Validator($nameValues);
        $this->manageRequiredEntries($valitron, $validationEntries);
        $result = $valitron->validate();
        $this->errors = $this->rewriteFieldNames($valitron->errors());
        return $result;
    }

    private function buildNameValues($validationEntries) {
        $nameValues = array();
        foreach ($validationEntries as $validationEntry) {
            $nameValues[$validationEntry['name']] = $validationEntry['value'];
        }
        return $nameValues;
    }

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


    private function processValitronRules($valitron, $fieldName, $rules)
    {
        foreach ($rules as $rule) {
            $params0 = isset($rule['params'][0]) ? $rule['params'][0]: null;
            $params1 = isset($rule['params'][1]) ? $rule['params'][1]: null;
            $params2 = isset($rule['params'][2]) ? $rule['params'][2]: null;
            $valitron->rule($rule['type'], $fieldName, $params0, $params1, $params2);
        }
    }

    private function rewriteFieldNames($validationErrors)
    {
        $errors = array();
        foreach ($validationErrors as $validationErrorKey => $validationErrorDetail) {
            $errors['entry_field_' . $validationErrorKey] = $validationErrorDetail;
        }
        return $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
