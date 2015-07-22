<?php
/**
 *
 */
/**
 * Class CompatibilityTestCase
 */
trait CompatibilityTestCase
{
    public function assertTextPresent($text, \PHPUnit_Extensions_Selenium2TestCase_Element $element = NULL)
    {
        $element = $element ?: $this->byCssSelector('body');
        $el_text = str_replace("\n", " ", $element->text());
        return strpos($el_text, $text) !== false;
    }

    public static function SpinAssertTest($msg, $test, $args=array(), $timeout=10)
    {
        $num_tries = 0;
        $result = false;
        while ($num_tries < $timeout && !$result) {
            try {
                $result = call_user_func_array($test, $args);
            } catch (\Exception $e) {
                $result = false;
            }

            if (!$result)
                sleep(1);

            $num_tries++;
        }

        $msg .= " (Failed after $num_tries tries)";

        return array($result, $msg);
    }

    public function byCss($selector)
    {
        return parent::byCssSelector($selector);
    }
    public function spinAssert($msg, $test, $args=array(), $timeout=10)
    {
        list($result, $msg) = self::SpinAssertTest($msg, $test, $args, $timeout);
        $this->assertTrue($result, $msg);
    }

    public function spinWait($msg, $test, $args=array(), $timeout=10)
    {
        list($result, $msg) = self::SpinAssertTest($msg, $test, $args, $timeout);
        if (!$result)
            throw new \Exception($msg);
    }
}
