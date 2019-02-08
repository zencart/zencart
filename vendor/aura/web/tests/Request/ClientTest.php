<?php
namespace Aura\Web\Request;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected function newClient(
        $server = array(),
        $mobile_agents = array(),
        $crawler_agents = array(),
        $proxies = array()
    ) {
        return new Client($server, $mobile_agents, $crawler_agents, $proxies);
    }

    public function testGetAuthDigest()
    {
        $server['PHP_AUTH_DIGEST'] = 'foo';
        $client = $this->newClient($server);
        $actual = $client->getAuthDigest();
        $expect = 'foo';
        $this->assertSame($expect, $actual);
    }

    public function testGetAuthPw()
    {
        $server['PHP_AUTH_PW'] = 'foo';
        $client = $this->newClient($server);
        $actual = $client->getAuthPw();
        $expect = 'foo';
        $this->assertSame($expect, $actual);
    }

    public function testGetAuthUser()
    {
        $server['PHP_AUTH_USER'] = 'foo';
        $client = $this->newClient($server);
        $actual = $client->getAuthUser();
        $expect = 'foo';
        $this->assertSame($expect, $actual);
    }

    public function testGetAuthType()
    {
        $server['AUTH_TYPE'] = 'foo';
        $client = $this->newClient($server);
        $actual = $client->getAuthType();
        $expect = 'foo';
        $this->assertSame($expect, $actual);
    }

    public function testGetForwardedFor()
    {
        // this is the last proxy in the chain
        $server['REMOTE_ADDR'] = '127.0.0.4';

        // this is the forwarding chain
        $server['HTTP_X_FORWARDED_FOR'] = '127.0.0.1, 127.0.0.2, 127.0.0.3';

        // these are the trusted proxies
        $proxies = array(
            '127.0.0.2',
            '127.0.0.3',
            '127.0.0.4',
        );

        // create client and test
        $client = $this->newClient($server, array(), array(), $proxies);
        $expect = array('127.0.0.1', '127.0.0.2', '127.0.0.3');
        $actual = $client->getForwardedFor();
        $this->assertSame($expect, $actual);
        $this->assertSame('127.0.0.1', $client->getIp());
    }

    public function testGetIp()
    {
        $expect = '127.0.0.1';
        $server['REMOTE_ADDR'] = $expect;
        $client = $this->newClient($server);
        $actual = $client->getIp();
        $this->assertSame($expect, $actual);
    }

    public function testGetIp_useRemoteWhenForwarded()
    {
        // coming directly from a proxy
        $server['REMOTE_ADDR'] = '127.0.0.1';

        $proxies = array(
            '127.0.0.1',
            '127.0.0.2'
        );

        $client = $this->newClient($server, array(), array(), $proxies);
        $this->assertSame('127.0.0.1', $client->getIp());
    }

    public function testGetReferer()
    {
        $expect = 'http://example.com';
        $server['HTTP_REFERER'] = $expect;
        $client = $this->newClient($server);
        $actual = $client->getReferer();
        $this->assertSame($expect, $actual);
    }

    public function testGetUserAgent()
    {
        $expect = 'Foo/1.0';
        $server['HTTP_USER_AGENT'] = $expect;
        $client = $this->newClient($server);
        $actual = $client->getUserAgent();
        $this->assertSame($expect, $actual);
    }

    public function testConstructorAgents()
    {
        $mobile_agents = array('foo');
        $crawler_agents = array('bar');

        $server['HTTP_USER_AGENT'] = 'foo';
        $client = $this->newClient($server, $mobile_agents, $crawler_agents);
        $this->assertTrue($client->isMobile());

        $server['HTTP_USER_AGENT'] = 'bar';
        $client = $this->newClient($server, $mobile_agents, $crawler_agents);
        $this->assertTrue($client->isCrawler());
    }

    public function testIsMobile()
    {
        $agents = array(
            array('Android', 'Mozilla/5.0 (Linux; U; Android 2.1; en-us; Nexus One Build/ERD62) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17 –Nexus'),
            array('BlackBerry', 'BlackBerry8330/4.3.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/105'),
            array('iPhone', 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16'),
            array('iPad', 'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; es-es) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405'),
            array('Blazer', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows 98; PalmSource/Palm-D062; Blazer/4.5) 16;320x320'),
            array('Brew', 'Mozilla/5.0 (compatible; Teleca Q7; Brew 3.1.5; U; en) 240X400 LGE VX9700'),
            array('IEMobile', 'LG-CT810/V10x IEMobile/7.11 Profile/MIDP-2.0 Configuration/CLDC-1.1 Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 7.11)'),
            array('iPod', 'Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A100a Safari/419.3 '),
            array('KDDI', 'KDDI-KC31 UP.Browser/6.2.0.5 (GUI) MMP/2.0'),
            array('Kindle', 'Mozilla/4.0 (compatible; Linux 2.6.22) NetFront/3.4 Kindle/2.0 (screen 600x800)'),
            array('Maemo', 'Mozilla/4.0 (compatible; MSIE 6.0; ; Linux armv5tejl; U) Opera 8.02 [en_US] Maemo browser 0.4.31 N770/SU-18'),
            array('MOT-' ,'MOT-L6/0A.52.45R MIB/2.2.1 Profile/MIDP-2.0 Configuration/CLDC-1.1'),
            array('Nokia', 'Mozilla/4.0 (compatible; MSIE 5.0; Series80/2.0 Nokia9300/05.22 Profile/MIDP-2.0 Configuration/CLDC-1.1)'),
            array('SymbianOS', 'Mozilla/5.0 (SymbianOS/9.1; U; en-us) AppleWebKit/413 (KHTML, like Gecko) Safari/413 es61i'),
            array('UP.Browser', 'OPWV-SDK UP.Browser/7.0.2.3.119 (GUI) MMP/2.0 Push/PO'),
            array('UP.Link', 'HTC-ST7377/1.59.502.3 (67150) Opera/9.50 (Windows NT 5.1; U; en) UP.Link/6.3.1.17.0'),
            array('Opera Mobi', 'Opera/9.80 (S60; SymbOS; Opera Mobi/499; U; en-GB) Presto/2.4.18 Version/10.00'),
            array('Opera Mini', 'Opera/9.60 (J2ME/MIDP; Opera Mini/4.2.13918/488; U; en) Presto/2.2.0'),
            array('webOS', 'Mozilla/5.0 (webOS/1.0; U; en-US) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pre/1.0'),
            array('Playstation', 'Mozilla/5.0 (PLAYSTATION 3; 1.00)'),
            array('Windows CE', 'Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; Sprint:PPC-6700; PPC; 240x320)'),
            array('Polaris', 'LG-LX600 Polaris/6.0 MMP/2.0 Profile/MIDP-2.1 Configuration/CLDC-1.1'),
            array('SEMC', 'SonyEricssonK608i/R2L/SN356841000828910 Browser/SEMC-Browser/4.2 Profile/MIDP-2.0 Configuration/CLDC-1.1'),
            array('NetFront', 'Mozilla/4.0 (compatible;MSIE 6.0;Windows95;PalmSource) Netfront/3.0;8;320x320'),
            array('Fennec', 'Mozilla/5.0 (X11; U; Linux armv61; en-US; rv:1.9.1b2pre) Gecko/20081015 Fennec/1.0a1'),
        );

        foreach ($agents as $agent) {
            $server['HTTP_USER_AGENT'] = $agent[1];
            $client = $this->newClient($server);
            $this->assertTrue($client->isMobile());
        }

        // test an unknown agent
        $server['HTTP_USER_AGENT'] = 'NoSuchAgent/1.0';
        $client = $this->newClient($server);
        $this->assertFalse($client->isMobile());

        // try to get it again, for code coverage
        $this->assertFalse($client->isMobile());

    }

    public function testIsCrawler()
    {
        $agents = array(
            array('Google', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'),
            array('Google', 'Mozilla/5.0 (compatible) Feedfetcher-Google; (+http://www.google.com/feedfetcher.html)'),
            array('Ask', 'Mozilla/5.0 (compatible; Ask Jeeves/Teoma; +http://about.ask.com/en/docs/about/webmasters.shtml)'),
            array('Baidu', 'Baiduspider+(+http://www.baidu.com/search/spider.htm)'),
            array('Yahoo', 'Mozilla/5.0 (compatible; Yahoo! Slurp/3.0; http://help.yahoo.com/help/us/ysearch/slurp)'),
            array('Nutch', 'GeoHasher/Nutch-1.0 (GeoHasher Web Search Engine; geohasher.gotdns.org;'),
            array('Y!J', 'Y!J-BRI/0.0.1 crawler ( http://help.yahoo.co.jp/help/jp/search/indexing/indexing-15.html)'),
            array('Danger hiptop', 'Mozilla/5.0 (Danger hiptop 3.3; U; AvantGo 3.2)'),
            array('MSR-ISRCCrawler', 'MSR-ISRCCrawler'),
            array('Y!OASIS', 'Y!OASIS/TEST no-ad Mozilla/4.08 [en] (X11; I; FreeBSD 2.2.8-STABLE i386)'),
            array('gsa-crawler', 'gsa-crawler (Enterprise; GID-01422; me@company.com)'),
            array('librabot' ,'librabot/1.0 (+http://search.msn.com/msnbot.htm)'),
            array('llssbot', 'llssbot/1.0(+http://labs.live.com;llssbot@microsoft.com)'),
            array('bingbot', 'Mozilla/5.0 (compatible; bingbot/2.0 +http://www.bing.com/bingbot.htm)'),
            array('MSMOBOT', 'msmobot/1.1 (+http://search.msn.com/msnbot.htm)'),
            array('MSNBot', 'msnbot-207-46-194-100.search.msn.com'),
            array('MSRBOT', 'MSRBOT (http://research.microsoft.com/research/sv/msrbot/)'),
            array('slurp', 'Slurp/2.0-condor_hourly (slurp@inktomi.com; http://www.inktomi.com/slurp.html)'),
            array('Scooter', 'Scooter/2.0 G.R.A.B. X2.0'),
            array('Yandex', 'Yandex/1.01.001 (compatible; Win16; I)'),
            array('Fast', 'FAST-WebCrawler/3.8 (atw-crawler at fast dot no; http://fast.no/support/crawler.asp)'),
            array('heritrix', 'Mozilla/5.0 (compatible; heritrix/1.12.1 +http://www.page-store.com) [email:paul@page-store.com]'),
            array('ia_archiver', 'ia_archiver/8.8 (Windows XP 7.2; en-US;)'),
            array('InternetArchive', 'internetarchive/0.8-dev (Nutch; http://lucene.apache.org/nutch/bot.html; nutch-agent@lucene.apache.org)'),
            array('archive.org_bot', 'Mozilla/5.0 (compatible; archive.org_bot/1.13.1x +http://crawler.archive.org)'),
            array('WordPress', 'wordpress/2.1.3'),
            array('Mp3Bot', 'Mozilla/5.0 (compatible; Mp3Bot/0.4; +http://mp3realm.org/mp3bot/)'),
            array('mp3Spider', 'mp3spider cn-search-devel'),
            array('Wget', 'Wget/1.12 (linux-gnu)'),
        );

        foreach ($agents as $agent) {
            $server['HTTP_USER_AGENT'] = $agent[1];
            $client = $this->newClient($server);
            $this->assertTrue($client->isCrawler());
        }

        // test an unknown agent
        $server['HTTP_USER_AGENT'] = 'NoSuchAgent/1.0';
        $client = $this->newClient($server);
        $this->assertFalse($client->isCrawler());

        // try to get it again, for code coverage
        $this->assertFalse($client->isCrawler());
    }
}
