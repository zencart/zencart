<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcUnitTestCase;

/**
 * Unit Tests for password hashing rules
 */
class PasswordHashTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php';
        require_once DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php';

        $pass = zcPassword::getInstance(PHP_VERSION);
    }

    public function testPasswordHashFunctionsExist(): void
    {
        $this->assertTrue(class_exists('zcPassword', false));
        $this->assertTrue(function_exists('password_hash'));
        $this->assertTrue(function_exists('password_verify'));
        $this->assertTrue(function_exists('password_needs_rehash'));
    }

    public function testPasswordHashResult(): void
    {
        $result = password_hash('testpass1', PASSWORD_DEFAULT);
        $tmp = explode(':', $result);
        $this->assertSame('$', $result[0]);
    }

    public function testPasswordVerify(): void
    {
        $this->assertTrue(password_verify('testpass1', '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC'));
    }

    public function testPasswordNeedsRehash(): void
    {
        $hash = 'd95e8fa7f20a009372eb3477473fcd34:1c';
        $this->assertTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
        $hash = 'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751';
        $this->assertTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
        $hash = '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC';
        if (version_compare(PHP_VERSION, '8.3.999', '<')) {
            $this->assertNotTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
        } else {
            // PHP 8.4 hashing "cost" default changed, so must test differently
            $this->assertTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
            $hash = '$2y$12$nF06GV6Oi6CQ39vtfdkII.jwqxpLnRbsCNpXpQQ0kLuU.rV5Tnl8G';
            $this->assertNotTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
        }
    }

    public function testDetectPasswordType(): void
    {
        $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('d95e8fa7f20a009372eb3477473fcd34:1c');
        $this->assertSame('oldMd5', $result);
        $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
        $this->assertSame('compatSha256', $result);
        $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
        $this->assertSame('unknown', $result);
    }

    public function testPasswordValidate(): void
    {
        $result = zcPassword::getInstance(PHP_VERSION)->validatePassword('password', 'd95e8fa7f20a009372eb3477473fcd34:1c');
        $this->assertTrue($result);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePassword(
            'testpass1',
            'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751'
        );
        $this->assertTrue($result);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePassword(
            'testpass1',
            '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC'
        );
        $this->assertTrue($result);
    }

    public function testValidatePasswordOldMd5(): void
    {
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5('password', 'd95e8fa7f20a009372eb3477473fcd34:1c');
        $this->assertTrue($result);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5(
            'testpass1',
            'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751'
        );
        $this->assertFalse($result);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5(
            'testpass1',
            '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC'
        );
        $this->assertFalse($result);
    }

    public function testValidatePasswordCompatSha256(): void
    {
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256(
            'password',
            'd95e8fa7f20a009372eb3477473fcd34:1c'
        );
        $this->assertFalse($result);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256(
            'testpass1',
            'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751'
        );
        $this->assertTrue($result);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256(
            'testpass1',
            '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC'
        );
        $this->assertFalse($result);
    }

    /**
     * Test password entropy / duplication risks
     */
    public function testPasswordGeneration(): void
    {
        $passwordList = [];
        $loopCount = 10000;
        if (defined('BIG_LOOPS_BYPASS')) {
            $loopCount = 100;
        }
        for ($i = 0; $i < $loopCount; $i++) {
            $password = zen_create_PADSS_password();
            if (isset($passwordList [$password])) {
                $this->fail('Duplicate Password ');
            }
            $passwordList [$password] = $password;
        }
        // @TODO - add an assertion here, instead of just watching for a failure.
    }


    public function testExtensions(): void
    {
        $this->assertTrue(function_exists('crypt'));
        $this->assertTrue(defined("CRYPT_BLOWFISH"));
    }

    public function testBlowfishCrypt(): void
    {
        $tests = [
            [
                '$2a$05$CCCCCCCCCCCCCCCCCCCCC.E5YPO9kmyuRGyh0XouQYb4YMJKvyOeW',
                'U*U',
            ],
            [
                '$2a$05$CCCCCCCCCCCCCCCCCCCCC.VGOzA784oUp/Z0DY336zx7pLYAy0lwK',
                'U*U*',
            ],
            [
                '$2a$05$XXXXXXXXXXXXXXXXXXXXXOAcXxm9kjPGEMsLznoKqmqw7tc8WCx4a',
                'U*U*U',
            ],
            [
                '$2a$05$abcdefghijklmnopqrstuu5s2v8.iXieOjg/.AySBTTZIIVFJeBui',
                '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789chars after 72 are ignored',
            ],
            [
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.CE5elHaaO4EbggVDjb8P19RukzXSM3e',
                "\xa3",
            ],
            [
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.CE5elHaaO4EbggVDjb8P19RukzXSM3e',
                "\xff\xff\xa3",
            ],
            [
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.CE5elHaaO4EbggVDjb8P19RukzXSM3e',
                "\xff\xff\xa3",
            ],
            [
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.nqd1wy.pTMdcvrRWxyiGL2eMz.2a85.',
                "\xff\xff\xa3",
            ],
            [
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.Sa7shbm4.OzKpvFnX1pQLmQW96oUlCq',
                "\xa3",
            ],
            [
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.Sa7shbm4.OzKpvFnX1pQLmQW96oUlCq',
                "\xa3",
            ],
            [
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.o./n25XVfn6oAPaUvHe.Csk4zRfsYPi',
                "1\xa3345",
            ],
            [
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.o./n25XVfn6oAPaUvHe.Csk4zRfsYPi',
                "\xff\xa3345",
            ],
            [
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.o./n25XVfn6oAPaUvHe.Csk4zRfsYPi',
                "\xff\xa334\xff\xff\xff\xa3345",
            ],
            [
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.o./n25XVfn6oAPaUvHe.Csk4zRfsYPi',
                "\xff\xa334\xff\xff\xff\xa3345",
            ],
            [
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.ZC1JEJ8Z4gPfpe1JOr/oyPXTWl9EFd.',
                "\xff\xa334\xff\xff\xff\xa3345",
            ],
            [
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.nRht2l/HRhr6zmCp9vYUvvsqynflf9e',
                "\xff\xa3345",
            ],
            [
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.nRht2l/HRhr6zmCp9vYUvvsqynflf9e',
                "\xff\xa3345",
            ],
            [
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.6IflQkJytoRVc1yuaNtHfiuq.FRlSIS',
                "\xa3ab",
            ],
            [
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.6IflQkJytoRVc1yuaNtHfiuq.FRlSIS',
                "\xa3ab",
            ],
            [
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.6IflQkJytoRVc1yuaNtHfiuq.FRlSIS',
                "\xa3ab",
            ],
            [
                '$2x$05$6bNw2HLQYeqHYyBfLMsv/OiwqTymGIGzFsA4hOTWebfehXHNprcAS',
                "\xd1\x91",
            ],
            [
                '$2x$05$6bNw2HLQYeqHYyBfLMsv/O9LIGgn8OMzuDoHfof8AQimSGfcSWxnS',
                "\xd0\xc1\xd2\xcf\xcc\xd8",
            ],
            [
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.swQOIzjOiJ9GHEPuhEkvqrUyvWhEMx6',
                "\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaachars after 72 are ignored as usual",
            ],
            [
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.R9xrDjiycxMbQE2bp.vgqlYpW5wx2yy',
                "\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55",
            ],
            [
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.9tQZzcJfm3uj2NvJ/n5xkhpqLrMpWCe',
                "\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff",
            ],
            [
                '$2a$05$CCCCCCCCCCCCCCCCCCCCC.7uG0VCzI2bS7j6ymqJi9CdcdxiRTWNy',
                '',
            ],
        ];
        foreach ($tests as $test) {
            $this->assertEquals(crypt($test[1], $test[0]), $test[0]);
        }
    }

    public function testPasswordHashing(): void
    {
        $hashLength = strlen(password_hash('foo', PASSWORD_BCRYPT));
        $passwordHash1 = password_hash('rasmuslerdorf', PASSWORD_BCRYPT, ['cost' => 7]);
        $passwordHash2 = password_hash('test', PASSWORD_BCRYPT);
        $this->assertSame(60, $hashLength);
        $this->assertTrue(password_verify('rasmuslerdorf', $passwordHash1));
        $this->assertTrue(password_verify('test', $passwordHash2));
    }
}
