<?php
if (!isset($crawlToken) || $crawlToken != 418941) {
    if (!isset($_GET['78wc58v'])) {
        die('Error');
    }
}

ini_set('display_errors', 'on');
$dir = realpath(dirname(__FILE__));
include $dir . '/../inc/config.php';

function shutdown()
{
    global $dir;
    $error = error_get_last();
    if ($error !== null && $error['type'] === E_ERROR) {
        file_put_contents($dir . '/crawlStatus.txt', '0');
        get_headers(HOST . '/crawler/runCrawl.php');
    }
}

set_time_limit(0);
register_shutdown_function('shutdown');

include $dir . '/PHPCrawl/libs/PHPCrawler.class.php';
include $dir . '/simple_html_dom.php';

function addURL($t, $u, $d)
{
    global $dbh;
    if ($t != '' && filter_var($u, FILTER_VALIDATE_URL)) {
        $check = $dbh->prepare('SELECT `id` FROM `search` WHERE `url`=?');
        $check->execute(array($u));
        $t = preg_replace("/\s+/", ' ', $t);
        $t = substr($t, 0, 1) == ' ' ? substr_replace($t, '', 0, 1) : $t;
        $t = substr($t, -1) == ' ' ? substr_replace($t, '', -1, 1) : $t;
        $t = html_entity_decode($t, ENT_QUOTES);
        $d = html_entity_decode($d, ENT_QUOTES);
        echo $u . "<br/>\n";
        ob_flush();
        flush();
        if ($check->rowCount() == 0) {
            $sql = $dbh->prepare('INSERT INTO `search` (`title`, `url`, `description`) VALUES (?, ?, ?)');
            $sql->execute(array(
                $t,
                $u,
                $d,
            ));
        } else {
            $sql = $dbh->prepare('UPDATE `search` SET `description` = ?, `title` = ? WHERE `url`=?');
            $sql->execute(array(
                $d,
                $t,
                $u,
            ));
        }
    }
}

class WSCrawler extends PHPCrawler
{
    public function handleDocumentInfo(PHPCrawlerDocumentInfo $p)
    {
        $u = $p->url;
        $c = $p->http_status_code;
        $s = $p->source;
        if ($c == 200 && $s != '') {
            $html = str_get_html($s);
            if (is_object($html)) {
                $d  = '';
                $do = $html->find('meta[name=description]', 0);
                if ($do) {
                    $d = $do->content;
                }
                $t = $html->find('title', 0);
                if ($t) {
                    $t = $t->innertext;
                    addURL($t, $u, $d);
                }
                $html->clear();
                unset($html);
            }
        }
    }
}
function crawl($u)
{
    $C = new WSCrawler();
    $C->setURL($u);
    $C->addContentTypeReceiveRule('#text/html#');
    $C->addURLFilterRule('#(jpg|gif|png|pdf|jpeg|svg|css|js)$# i');
    if (!isset($GLOBALS['bgFull'])) {
        $C->setTrafficLimit(2000 * 1024);
    }
    $C->obeyRobotsTxt(true);
    $C->obeyNoFollowTags(true);
    $C->setUserAgentString('DingoBot (http://search.subinsb.com/about/bot.php)');
    $C->setFollowMode(0);
    $C->go();
}

if (!isset($url4Array)) {
    // Get the last indexed URLs (If there isn't, use default URL's) & start Crawling
    $last = $dbh->prepare('SELECT `url` FROM search');
    $last->execute();

    $count = $last->rowCount();

    if ($count < 1) {
        crawl('http://subinsb.com'); // The Default URL #1
    } else {
        $urls  = $last->fetchAll();
        $index = rand(0, $count - 1);
        crawl($urls[$index]['url']);
    }
} elseif (is_array($url4Array)) {
    foreach ($url4Array as $url) {
        crawl($url);
    }
}
