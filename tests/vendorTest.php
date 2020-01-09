<?php
/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\CloudFunctions\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for when this package is installed in a vendored directory
 *
 * @group gcf-framework
 * @runInSeparateProcess
 */
class vendorTest extends TestCase
{
    private static $tmpDir;

    public static function setUpBeforeClass()
    {
        mkdir($tmpDir = sys_get_temp_dir() . '/ff-php-test-' . rand());

        // Copy Fixtures
        file_put_contents($tmpDir . '/composer.json', sprintf(
            file_get_contents(__DIR__ . '/fixtures/composer.json'),
            dirname(__DIR__)
        ));
        passthru(sprintf('composer install --prefer-dist -d %s', $tmpDir));

        self::$tmpDir = $tmpDir;
    }

    public function testDefaultFunctionSource()
    {
        copy(__DIR__ . '/fixtures/index.php', self::$tmpDir . '/index.php');
        $cmd = sprintf(
            'FUNCTION_SOURCE=' .
            ' FUNCTION_SIGNATURE_TYPE=http' .
            ' FUNCTION_TARGET=helloDefault' .
            ' php %s/vendor/bin/router.php',
            self::$tmpDir
        );
        exec($cmd, $output);

        $this->assertEquals(1, count($output));
        $this->assertEquals('Hello Default!', $output[0]);
    }

    public function testRelativeFunctionSource()
    {
        copy(__DIR__ . '/fixtures/relative.php', self::$tmpDir . '/relative.php');
        putenv('FUNCTION_SOURCE=');
        $cmd = sprintf(
            'FUNCTION_SOURCE=relative.php' .
            ' FUNCTION_SIGNATURE_TYPE=http' .
            ' FUNCTION_TARGET=helloDefault' .
            ' php %s/vendor/bin/router.php',
            self::$tmpDir
        );
        exec($cmd, $output);

        $this->assertEquals(1, count($output));
        $this->assertEquals('Hello Relative!', $output[0]);
    }

    public function testAbsoluteFunctionSource()
    {
        copy(__DIR__ . '/fixtures/absolute.php', self::$tmpDir . '/absolute.php');
        putenv('FUNCTION_SOURCE=');
        $cmd = sprintf(
            'FUNCTION_SOURCE=/srv/absolute.php' .
            ' FUNCTION_SIGNATURE_TYPE=http' .
            ' FUNCTION_TARGET=helloDefault' .
            ' php %s/vendor/bin/router.php',
            self::$tmpDir
        );
        exec($cmd, $output);

        $this->assertEquals(1, count($output));
        $this->assertEquals('Hello Absolute!', $output[0]);
    }
}