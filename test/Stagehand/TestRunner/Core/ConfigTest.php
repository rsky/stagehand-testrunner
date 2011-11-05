<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2010-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.15.0
 */

namespace Stagehand\TestRunner\Core;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.15.0
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/247
     */
    public function decodesUrlEncodedTestingMethods()
    {
        $testingClass = 'フーTest';
        $testingMethod = 'バー';
        $config = new Config();
        $config->addTestingMethod(urlencode($testingClass . '::' . $testingMethod));
        $this->assertTrue($config->isTestingMethod($testingClass, $testingMethod));
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/247
     */
    public function decodesUrlEncodedTestingClasses()
    {
        $testingClass = 'フーTest';
        $config = new Config();
        $config->addTestingClass(urlencode($testingClass));
        $this->assertTrue($config->isTestingClass($testingClass));
    }

    /**
     * @test
     * @since Method available since Release 2.20.0
     */
    public function getsTheCurrentDirectoryAsTheTestDirectoryIfNoDirectoriesOrFilesAreSpecified()
    {
        $currentDirectory = '/path/to/currentDir';

        $config = \Phake::partialMock('\Stagehand\TestRunner\Core\Config');
        \Phake::when($config)->getWorkingDirectoryAtStartup()->thenReturn($currentDirectory);

        $testingResources = $config->getTestingResources();
        $this->assertEquals(1, count($testingResources));
        $this->assertEquals($currentDirectory, $testingResources[0]);
    }
}

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