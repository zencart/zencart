<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

/**
 * Unit Tests for password hashing rules
 */
class testPasswordHash extends zcTestCase
{
    public function setUp()
    {
        parent::setUp();
        require DIR_FS_CATALOG . 'includes/functions/functions_general.php';
        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php');
        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php');

        $pass = zcPassword::getInstance(PHP_VERSION);
    }

    public function testPasswordHashFunctionsExist()
    {
        $this->assertTrue(class_exists('zcPassword', false));
        $this->assertTrue(function_exists('password_hash'));
        $this->assertTrue(function_exists('password_verify'));
        $this->assertTrue(function_exists('password_needs_rehash'));
    }

    public function testPasswordHashResult()
    {
        $result = password_hash('testpass1', PASSWORD_DEFAULT);
        $tmp = explode(':', $result);
        $this->assertTrue($result [0] == '$');
    }

    public function testPasswordVerify()
    {
        $this->assertTrue(password_verify('testpass1', '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC'));
    }

    public function testPasswordNeedsRehash()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $hash = 'd95e8fa7f20a009372eb3477473fcd34:1c';
            $this->assertTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
            $hash = 'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751';
            $this->assertTrue(password_needs_rehash($hash, PASSWORD_DEFAULT));
            $hash = '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC';
            $this->assertTrue(!password_needs_rehash($hash, PASSWORD_DEFAULT));
        }
    }

    public function testDetectPasswordType()
    {
        $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('d95e8fa7f20a009372eb3477473fcd34:1c');
        $this->assertTrue($result == 'oldMd5');
        $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
        $this->assertTrue($result == 'compatSha256');
        $result = zcPassword::getInstance(PHP_VERSION)->detectPasswordType('$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
        $this->assertTrue($result == 'unknown');
    }

    public function testPasswordValidate()
    {
        $result = zcPassword::getInstance(PHP_VERSION)->validatePassword('password', 'd95e8fa7f20a009372eb3477473fcd34:1c');
        $this->assertTrue($result == true);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePassword('testpass1',
            'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
        $this->assertTrue($result == true);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePassword('testpass1',
            '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
        $this->assertTrue($result == true);
    }

    public function testValidatePasswordOldMd5()
    {
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5('password', 'd95e8fa7f20a009372eb3477473fcd34:1c');
        $this->assertTrue($result == true);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5('testpass1',
            'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
        $this->assertTrue($result == false);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordOldMd5('testpass1',
            '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
        $this->assertTrue($result == false);
    }

    public function testValidatePasswordCompatSha256()
    {
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256('password',
            'd95e8fa7f20a009372eb3477473fcd34:1c');
        $this->assertTrue($result == false);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256('testpass1',
            'c7d6976483032e03d48c1255cc9714838915e58007952f9f5f9c2af6f81f20d7:4972adcbae0c13a8bf77560479341f0beb2fb200ff21c16fc1ade1d467208751');
        $this->assertTrue($result == true);
        $result = zcPassword::getInstance(PHP_VERSION)->validatePasswordCompatSha256('testpass1',
            '$2y$10$XP.PqzC8/M.NbVIRVVael.WU8YxBss.qBUIzXtoIuWPbFHYxjGySC');
        $this->assertTrue($result == false);
    }

    /**
     * Test password entropy / duplication risks
     */
    public function testPasswordGeneration()
    {
        $passwordList = array();
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
    }


    public function testExtensions()
    {
        $this->assertTrue(function_exists('crypt'));
        $this->assertTrue(defined("CRYPT_BLOWFISH"));
    }

    public function testBlowfishCrypt()
    {
        $tests = array(
            array(
                '$2a$05$CCCCCCCCCCCCCCCCCCCCC.E5YPO9kmyuRGyh0XouQYb4YMJKvyOeW',
                'U*U'
            ),
            array(
                '$2a$05$CCCCCCCCCCCCCCCCCCCCC.VGOzA784oUp/Z0DY336zx7pLYAy0lwK',
                'U*U*'
            ),
            array(
                '$2a$05$XXXXXXXXXXXXXXXXXXXXXOAcXxm9kjPGEMsLznoKqmqw7tc8WCx4a',
                'U*U*U'
            ),
            array(
                '$2a$05$abcdefghijklmnopqrstuu5s2v8.iXieOjg/.AySBTTZIIVFJeBui',
                '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789chars after 72 are ignored'
            ),
            array(
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.CE5elHaaO4EbggVDjb8P19RukzXSM3e',
                "\xa3"
            ),
            array(
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.CE5elHaaO4EbggVDjb8P19RukzXSM3e',
                "\xff\xff\xa3"
            ),
            array(
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.CE5elHaaO4EbggVDjb8P19RukzXSM3e',
                "\xff\xff\xa3"
            ),
            array(
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.nqd1wy.pTMdcvrRWxyiGL2eMz.2a85.',
                "\xff\xff\xa3"
            ),
            array(
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.Sa7shbm4.OzKpvFnX1pQLmQW96oUlCq',
                "\xa3"
            ),
            array(
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.Sa7shbm4.OzKpvFnX1pQLmQW96oUlCq',
                "\xa3"
            ),
            array(
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.o./n25XVfn6oAPaUvHe.Csk4zRfsYPi',
                "1\xa3345"
            ),
            array(
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.o./n25XVfn6oAPaUvHe.Csk4zRfsYPi',
                "\xff\xa3345"
            ),
            array(
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.o./n25XVfn6oAPaUvHe.Csk4zRfsYPi',
                "\xff\xa334\xff\xff\xff\xa3345"
            ),
            array(
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.o./n25XVfn6oAPaUvHe.Csk4zRfsYPi',
                "\xff\xa334\xff\xff\xff\xa3345"
            ),
            array(
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.ZC1JEJ8Z4gPfpe1JOr/oyPXTWl9EFd.',
                "\xff\xa334\xff\xff\xff\xa3345"
            ),
            array(
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.nRht2l/HRhr6zmCp9vYUvvsqynflf9e',
                "\xff\xa3345"
            ),
            array(
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.nRht2l/HRhr6zmCp9vYUvvsqynflf9e',
                "\xff\xa3345"
            ),
            array(
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.6IflQkJytoRVc1yuaNtHfiuq.FRlSIS',
                "\xa3ab"
            ),
            array(
                '$2x$05$/OK.fbVrR/bpIqNJ5ianF.6IflQkJytoRVc1yuaNtHfiuq.FRlSIS',
                "\xa3ab"
            ),
            array(
                '$2y$05$/OK.fbVrR/bpIqNJ5ianF.6IflQkJytoRVc1yuaNtHfiuq.FRlSIS',
                "\xa3ab"
            ),
            array(
                '$2x$05$6bNw2HLQYeqHYyBfLMsv/OiwqTymGIGzFsA4hOTWebfehXHNprcAS',
                "\xd1\x91"
            ),
            array(
                '$2x$05$6bNw2HLQYeqHYyBfLMsv/O9LIGgn8OMzuDoHfof8AQimSGfcSWxnS',
                "\xd0\xc1\xd2\xcf\xcc\xd8"
            ),
            array(
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.swQOIzjOiJ9GHEPuhEkvqrUyvWhEMx6',
                "\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaa\xaachars after 72 are ignored as usual"
            ),
            array(
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.R9xrDjiycxMbQE2bp.vgqlYpW5wx2yy',
                "\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55\xaa\x55"
            ),
            array(
                '$2a$05$/OK.fbVrR/bpIqNJ5ianF.9tQZzcJfm3uj2NvJ/n5xkhpqLrMpWCe',
                "\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff\x55\xaa\xff"
            ),
            array(
                '$2a$05$CCCCCCCCCCCCCCCCCCCCC.7uG0VCzI2bS7j6ymqJi9CdcdxiRTWNy',
                ''
            )
        );
        foreach ($tests as $test) {
            $this->assertTrue(crypt($test [1], $test [0]) == $test [0]);
        }
    }

    public function testPasswordHashing()
    {
        $hashLength = strlen(password_hash("foo", PASSWORD_BCRYPT));
        $passwordHash1 = password_hash("rasmuslerdorf", PASSWORD_BCRYPT, array(
            "cost" => 7,
            "salt" => "usesomesillystringforsalt"
        ));
        $passwordHash2 = password_hash("test", PASSWORD_BCRYPT, array(
            "salt" => "123456789012345678901" . chr(0)
        ));
        $this->assertTrue($hashLength == 60);
        $this->assertTrue($passwordHash1 === '$2y$07$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I90dH6hi');
        $this->assertTrue($passwordHash2 === '$2y$10$MTIzNDU2Nzg5MDEyMzQ1Nej0NmcAWSLR.oP7XOR9HD/vjUuOj100y');
    }


}
