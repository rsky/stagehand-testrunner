<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

// {{{ Stagehand_TestRunner_Runner_PHPUnitRunner_JUnitXMLTest

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_PHPUnitRunner_JUnitXMLTest extends PHPUnit_Framework_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $backupGlobals = false;

    /**
     * @var Stagehand_TestRunner_Config
     */
    protected $config;

    protected $tmpDirectory;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */
 
    public function setUp()
    {
        $this->tmpDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
        $this->config = new Stagehand_TestRunner_Config();
        $this->config->logsResultsInJUnitXML = true;
        $this->config->junitXMLFile =
            $this->tmpDirectory . '/' . $this->getName(false) . '.xml';
    }

    public function tearDown()
    {
        $directoryScanner = new Stagehand_DirectoryScanner(array($this, 'removeJUnitXMLFile'));
        $directoryScanner->addExclude('^.*');
        $directoryScanner->addInclude('\.xml$');
        $directoryScanner->scan($this->tmpDirectory);
    }

    public function removeJUnitXMLFile($element)
    {
        unlink($element);
    }

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormat()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Stagehand_TestRunner_PHPUnitPassTest');
        $suite->addTestSuite('Stagehand_TestRunner_PHPUnitFailureTest');
        $suite->addTestSuite('Stagehand_TestRunner_PHPUnitErrorTest');
        ob_start();
        $runner = new Stagehand_TestRunner_Runner_PHPUnitRunner($this->config);
        $runner->run($suite);
        ob_end_clean();
        $this->assertFileExists($this->config->junitXMLFile);

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(5, $parentTestsuite->getAttribute('tests'));
        $this->assertTrue($parentTestsuite->hasAttribute('assertions'));
        $this->assertEquals(5, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(3, $parentTestsuite->childNodes->length);
        $this->assertTrue($parentTestsuite->hasAttribute('time'));

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitPassTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(3, $childTestsuite->getAttribute('tests'));
        $this->assertTrue($childTestsuite->hasAttribute('assertions'));
        $this->assertEquals(4, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(3, $childTestsuite->childNodes->length);
        $this->assertTrue($childTestsuite->hasAttribute('time'));

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithAnAssertion', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithAnAssertion');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithMultipleAssertions',
                            $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithMultipleAssertions');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(2, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));

        $testcase = $childTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('日本語を使用できる', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('日本語を使用できる');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));

        $childTestsuite = $parentTestsuite->childNodes->item(1);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitFailureTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitFailureTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertTrue($childTestsuite->hasAttribute('assertions'));
        $this->assertEquals(1, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);
        $this->assertTrue($childTestsuite->hasAttribute('time'));

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('isFailure', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitFailureTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('isFailure');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));
        $failure = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_ExpectationFailedException',
                            $failure->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitFailureTest::isFailure\s+This is an error message\./', $failure->nodeValue);

        $childTestsuite = $parentTestsuite->childNodes->item(2);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitErrorTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitErrorTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertTrue($childTestsuite->hasAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);
        $this->assertTrue($childTestsuite->hasAttribute('time'));

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('isError', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitErrorTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('isError');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(0, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));
        $error = $testcase->childNodes->item(0);
        $this->assertEquals('Stagehand_LegacyError_PHPError_Exception',
                            $error->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitErrorTest::isError\s+Stagehand_LegacyError_PHPError_Exception:/', $error->nodeValue);
    }

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormatIfNoTestsAreFound()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        ob_start();
        $runner = new Stagehand_TestRunner_Runner_PHPUnitRunner($this->config);
        $runner->run($suite);
        ob_end_clean();
        $this->assertFileExists($this->config->junitXMLFile);

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertFalse($parentTestsuite->hasChildNodes());
        $this->assertEquals(0, $parentTestsuite->getAttribute('tests'));
        $this->assertFalse($parentTestsuite->hasAttribute('assertions'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(0, $parentTestsuite->childNodes->length);
        $this->assertFalse($parentTestsuite->hasAttribute('time'));
    }

    /**
     * @test
     */
    public function treatsDataProvider()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Stagehand_TestRunner_PHPUnitDataProviderTest');
        ob_start();
        $runner = new Stagehand_TestRunner_Runner_PHPUnitRunner($this->config);
        $runner->run($suite);
        ob_end_clean();
        $this->assertFileExists($this->config->junitXMLFile);

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(4, $parentTestsuite->getAttribute('tests'));
        $this->assertTrue($parentTestsuite->hasAttribute('assertions'));
        $this->assertEquals(4, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $parentTestsuite->childNodes->length);
        $this->assertTrue($parentTestsuite->hasAttribute('time'));

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(4, $childTestsuite->getAttribute('tests'));
        $this->assertTrue($childTestsuite->hasAttribute('assertions'));
        $this->assertEquals(4, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);
        $this->assertTrue($childTestsuite->hasAttribute('time'));

        $grandChildTestsuite = $childTestsuite->childNodes->item(0);
        $this->assertTrue($grandChildTestsuite->hasChildNodes());
        $this->assertEquals('passWithDataProvider',
                            $grandChildTestsuite->getAttribute('name'));
        $this->assertTrue($grandChildTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $this->assertEquals($class->getFileName(), $grandChildTestsuite->getAttribute('file'));
        $this->assertEquals(4, $grandChildTestsuite->getAttribute('tests'));
        $this->assertTrue($grandChildTestsuite->hasAttribute('assertions'));
        $this->assertEquals(4, $grandChildTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $grandChildTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $grandChildTestsuite->getAttribute('errors'));
        $this->assertEquals(4, $grandChildTestsuite->childNodes->length);
        $this->assertTrue($grandChildTestsuite->hasAttribute('time'));

        $testcase = $grandChildTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #0', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));

        $testcase = $grandChildTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #1', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));

        $testcase = $grandChildTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #2', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));

        $testcase = $grandChildTestsuite->childNodes->item(3);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #3', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $this->assertTrue($testcase->hasAttribute('time'));
        $failure = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_ExpectationFailedException',
                            $failure->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitDataProviderTest::passWithDataProvider with data set #3/', $failure->nodeValue);
    }

    /**
     * @test
     */
    public function logsTestResultsInRealtimeIntoTheSpecifiedFileInTheJunitXmlFormat()
    {
        $this->config->logsResultsInJUnitXMLInRealtime = true;
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Stagehand_TestRunner_PHPUnitPassTest');
        $suite->addTestSuite('Stagehand_TestRunner_PHPUnitFailureTest');
        $suite->addTestSuite('Stagehand_TestRunner_PHPUnitErrorTest');
        ob_start();
        $runner = new Stagehand_TestRunner_Runner_PHPUnitRunner_JUnitXMLTest_MockPHPUnitRunner($this->config);
        $runner->run($suite);
        ob_end_clean();
        $this->assertFileExists($this->config->junitXMLFile);

        $streamContents = $this->readAttribute($runner, 'streamContents');
        $this->assertEquals(22, count($streamContents));
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<testsuites', $streamContents[0]);
        $this->assertEquals('><testsuite name="" tests="5"', $streamContents[1]);
        $this->assertEquals('><testsuite name="Stagehand_TestRunner_PHPUnitPassTest" tests="3" file="/home/iteman/GITREPOS/stagehand-testrunner/examples/Stagehand/TestRunner/PHPUnitPassTest.php"', $streamContents[2]);
        $this->assertEquals('><testcase name="passWithAnAssertion" class="Stagehand_TestRunner_PHPUnitPassTest" file="/home/iteman/GITREPOS/stagehand-testrunner/examples/Stagehand/TestRunner/PHPUnitPassTest.php" line="83"', $streamContents[3]);
        $this->assertEquals('/>', $streamContents[4]);
        $this->assertEquals('<testcase name="passWithMultipleAssertions" class="Stagehand_TestRunner_PHPUnitPassTest" file="/home/iteman/GITREPOS/stagehand-testrunner/examples/Stagehand/TestRunner/PHPUnitPassTest.php" line="91"', $streamContents[5]);
        $this->assertEquals('/>', $streamContents[6]);
        $this->assertEquals('<testcase name="日本語を使用できる" class="Stagehand_TestRunner_PHPUnitPassTest" file="/home/iteman/GITREPOS/stagehand-testrunner/examples/Stagehand/TestRunner/PHPUnitPassTest.php" line="100"', $streamContents[7]);
        $this->assertEquals('/>', $streamContents[8]);
        $this->assertEquals('</testsuite>', $streamContents[9]);
        $this->assertEquals('</testsuites>
', $streamContents[21]);

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLStream.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(5, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(3, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitPassTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(3, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(3, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithAnAssertion', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithAnAssertion');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithMultipleAssertions',
                            $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithMultipleAssertions');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $childTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('日本語を使用できる', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('日本語を使用できる');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $childTestsuite = $parentTestsuite->childNodes->item(1);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitFailureTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitFailureTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('isFailure', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitFailureTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('isFailure');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $failure = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_ExpectationFailedException',
                            $failure->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitFailureTest::isFailure\s+This is an error message\./', $failure->nodeValue);

        $childTestsuite = $parentTestsuite->childNodes->item(2);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitErrorTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitErrorTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('isError', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitErrorTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('isError');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $error = $testcase->childNodes->item(0);
        $this->assertEquals('Stagehand_LegacyError_PHPError_Exception',
                            $error->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitErrorTest::isError\s+Stagehand_LegacyError_PHPError_Exception:/', $error->nodeValue);
    }

    /**
     * @test
     */
    public function treatsDataProviderInRealtime()
    {
        $this->config->logsResultsInJUnitXMLInRealtime = true;
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Stagehand_TestRunner_PHPUnitDataProviderTest');
        ob_start();
        $runner = new Stagehand_TestRunner_Runner_PHPUnitRunner($this->config);
        $runner->run($suite);
        ob_end_clean();
        $this->assertFileExists($this->config->junitXMLFile);

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLStream.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(4, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $childTestsuite->getAttribute('name'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(4, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $grandChildTestsuite = $childTestsuite->childNodes->item(0);
        $this->assertTrue($grandChildTestsuite->hasChildNodes());
        $this->assertEquals('passWithDataProvider',
                            $grandChildTestsuite->getAttribute('name'));
        $class = new ReflectionClass('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $this->assertEquals($class->getFileName(), $grandChildTestsuite->getAttribute('file'));
        $this->assertEquals(4, $grandChildTestsuite->getAttribute('tests'));
        $this->assertEquals(4, $grandChildTestsuite->childNodes->length);

        $testcase = $grandChildTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #0', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $grandChildTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #1', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $grandChildTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #2', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $grandChildTestsuite->childNodes->item(3);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #3', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $failure = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_ExpectationFailedException',
                            $failure->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitDataProviderTest::passWithDataProvider with data set #3/', $failure->nodeValue);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: utf-8
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
