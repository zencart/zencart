<?php
/**
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

/**
 * Class behatExtensionsTrait
 */
trait behatExtensionsTrait
{
    /**
     * @BeforeScenario
     */
    public function setBaseUrl($scope)
    {
        $environment = $scope->getEnvironment();
        foreach ($environment->getContexts() as $context) {
            if ($context instanceof \Behat\MinkExtension\Context\RawMinkContext) {
                $context->setMinkParameter('base_url', $this->baseUrl);
            }
        }
    }

    /**
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep($event)
    {
        if (!isset($this->configParams['take_screenshot_on_failed_step']) || $this->configParams['take_screenshot_on_failed_step'] === false) {
            return;
        }
        if ($event->getTestResult()->getResultCode() !== \Behat\Testwork\Tester\Result\TestResult::FAILED) {
            return;
        }

        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof Behat\Mink\Driver\Selenium2Driver) {
            return;
        }
        $stepText = $event->getStep()->getText();
        $fileName = 'testFailed_' . preg_replace('#[^a-zA-Z0-9\._-]#', '', $stepText) . '.png';
        $filePath = $this->configParams['screenshot_path'];
        $this->saveScreenshot($fileName, $filePath);
        print "Screenshot for '{$stepText}' placed in " . $filePath . DIRECTORY_SEPARATOR . $fileName . "\n";

    }

    /**
     * @AfterStep
     */
    public function dumpResponseAfterFailedStep($event)
    {
        if (!isset($this->configParams['dump_response_on_failed_step']) || $this->configParams['dump_response_on_failed_step'] === false) {
            return;
        }
        if ($event->getTestResult()->getResultCode() !== \Behat\Testwork\Tester\Result\TestResult::FAILED) {
            return;
        }

        $this->iShouldOutputPage();

        if (isset($this->configParams['die_after_dump_repsonse']) && $this->configParams['die_after_dump_repsonse'] === true) {
            die();
        }

    }


    /**
     * @Then I take a screenshot :arg1
     */
    public function iTakeAScreenshot($arg1)
    {
        $fileName = $arg1 . '.png';
        $filePath = $this->configParams['screenshot_path'];
        $this->saveScreenshot($fileName, $filePath);
    }

    /**
     * Click on the element with the provided xpath query
     *
     * @When /^I click on the element with xpath "([^"]*)"$/
     */
    public function iClickOnTheElementWithXPath($xpath)
    {
        $session = $this->getSession(); // get the mink session
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        ); // runs the actual query and returns the element

        // errors must not pass silently
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }

        // ok, let's click on it
        $element->click();
        sleep(1);

    }

    /**
     * @When I fill in :arg1 with <param>:arg2
     */
    public function iFillInWithParam($arg1, $arg2)
    {
        $this->fillField($arg1, $this->configParams[$arg2]);
    }

    /**
     * @Then I wait :arg1
     */
    public function iWait($arg1)
    {
        sleep($arg1);
    }

    /**
     * @Then I fill in css element :arg1 with :arg2
     */
    public function iFillInCssElementWith($arg1, $arg2)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find('css', $arg1);
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS Selector: "%s"', $arg1));
        }
        $element->setValue($arg2);
    }

    /**
     * @Then I should output page
     */
    public function iShouldOutputPage()
    {
        $html = $this->getSession()->getPage()->getHtml();
        var_dump($html);
    }

    /**
     * @Then I visit :arg1
     */
    public function iVisit($arg1)
    {
        $this->visit($arg1);
    }

    /**
     * @Then I submit the form :arg1
     */
    public function iSubmitTheForm($arg1)
    {
        $page = $this->getSession()->getPage();
        $formNode = $page->find('named', array('id_or_name', $arg1));
        if (null === $formNode) {
            throw new \InvalidArgumentException(sprintf('Could not find form with id|name: "%s"', $arg1));
        }
        $formNode->submit();
    }

    /**
     * @Then I click on the element with css :arg1
     */
    public function iClickOnTheElementWithCss($arg1)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $arg1);
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not find css element: "%s"', $arg1));
        }
        $element->click();
    }

    /**
     * This function prevents Behat form failing a tests if the HTML is not loaded yet.
     * Behat with Selenium often executes tests faster thant Selenium is able to retreive
     * the HTML causing false negatives.
     *
     * Use this for all test cases that depend on a presence of some elements on the
     * website.
     *
     * Pass an anonymous function containing your normal test as an argument.
     * The function needs to return a boolean.
     *
     * @see http://docs.behat.org/cookbook/using_spin_functions.html
     *
     * @param \Closure $closure
     * @param int $tries
     *
     * @return bool
     *
     * @throws \Exception|UnsupportedTestException
     * @throws \Exception
     */
    public function spin($closure, $tries = 30)
    {
        for ($i = 0; $i < $tries; $i++) {
            try {
                if ($result = $closure($this)) {
                    if (!is_bool($result)) {
                        throw new UnsupportedTestException(
                            'The spinned callback needs to return true on success or throw an Exception'
                        );
                    }

                    return true;
                }
            } catch (UnsupportedTestException $e) {
                // If the test is unsupported, we quit
                throw $e;
            } catch (\Exception $e) {
                // do nothing to continue the loop
            }

            usleep(300000);
        }

        $backtrace = debug_backtrace();
        throw new \Exception(
            "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()\n" .
            "With the following arguments: " . print_r($backtrace[1]['args'], true)
        );
    }


    /**
     * @Given /^I wait to see "(?P<text>(?:[^"]|\\")*)"$/
     *
     * @param string $text
     *
     * @return bool
     */
    public function iWaitToSee($text)
    {
        return $this->assertPageContainsTextSpin($text);
    }

    /**
     * Overrides MinkContext method by adding a spin
     *
     * {@inheritdoc}
     */
    public function assertPageContainsTextSpin($text)
    {
        $this->waitUntilPageLoaded();

        return $this->spin(
            function ($context) use ($text) {
                $context->assertSession()->pageTextContains($context->fixStepArgument($text));

                return true;
            }
        );
    }

    /**
     * This methods makes Selenium2 wait until the body element is present.
     * This supposes that the html is loaded (even if it's not 100% reliable).
     *
     * @return bool
     */
    protected function waitUntilPageLoaded()
    {
        $this->spin(
            function ($context) {
                $context->assertSession()->elementExists('xpath', '//body');

                return true;
            }
        );
    }

    /**
     * @Given /^I press button "(?P<text>(?:[^"]|\\")*)"$/
     * @param $arg1
     */
    public function iPressButton($arg1)
    {
        $this->iClickOnTheElementWithCss("#" . $arg1);
    }

    /**
     * @Then I press button by name :arg1
     */
    public function iPressButtonByName($arg1)
    {

        $this->iClickOnTheElementWithCss("input[name=$arg1]");
    }

}

