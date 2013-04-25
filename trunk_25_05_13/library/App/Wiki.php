<?php

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
/**
 * Parse structured wiki text and render into arbitrary formats such as XHTML.
 *
 * PHP versions 4 and 5
 *
 * @category   Text
 * @package    App_Wiki
 * @author     Paul M. Jones <pmjones@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    CVS: $Id: Wiki.php 248433 2007-12-17 16:03:48Z justinpatrin $
 * @link       http://pear.php.net/package/App_Text_Wiki
 */
/**
 * The baseline abstract parser class.
 */
//require_once 'App\Text\Wiki\Parse.php';
//
///**
// * The baseline abstract render class.
// */
//require_once 'App\Text\Wiki\Render.php';

/**
 * Parse structured wiki text and render into arbitrary formats such as XHTML.
 *
 * This is the "master" class for handling the management and convenience
 * functions to transform Wiki-formatted text.
 *
 * @category   Text
 * @package    App_Text_Wiki
 * @author     Paul M. Jones <pmjones@php.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version    Release: 1.2.1
 * @link       http://pear.php.net/package/App_Text_Wiki
 */
class App_Wiki {

	var $url = "http://en.wikipedia.org/w/api.php?action=parse&prop=text|headitems|images&format=json&redirects=true&page=";

	public function wiki_search($keyword, $amount = 1) {
		$keywords = array();
		try {
			$wikipedia = new Zend_Rest_Client('http://en.wikipedia.org/w/api.php');
			$wikipedia->action('query');
			$wikipedia->list('search');
			$wikipedia->srwhat('text');
			$wikipedia->format('xml');
			$wikipedia->srsearch($keyword);

			$result = $wikipedia->get();
		} catch (Exception $e) {
			die('ERROR: ' . $e->getMessage());
		}
		$result = $this->xml2array($result);
		$i = 0;
		foreach ($result as $key => $res) {
			if ($i == 0) {
				$resArray = $res;
			}
			$i++;
		}
		if (isset($resArray['query']['search']['p']) && is_array($resArray['query']['search']['p']) && !empty($resArray['query']['search']['p'])) {
			foreach ($resArray['query']['search']['p'] as $key => $pageAttributes) {
				if ($key < $amount) {
					$keywords[$key]['text'] = $pageAttributes['@attributes']['title'];
					$keywords[$key]['snippet'] = $pageAttributes['@attributes']['snippet'];
				} else {
					break;
				}
			}
		}
		$result = $this->process($keywords);
		return json_encode($result);
	}

	public function parse($keywords) {
		$url = $this->url;
		$bar = "";
		$mh = curl_multi_init();
		$ch = array();
		$replys = false;
		foreach ($keywords as $num => $keyword) {
			$ch[$keyword['text']] = curl_init();
			curl_setopt($ch[$keyword['text']], CURLOPT_USERAGENT, 'User-Agent: Splyst (http://splyst.com/)');
			curl_setopt($ch[$keyword['text']], CURLOPT_URL, $url . urlencode($keyword['text']));
			curl_setopt($ch[$keyword['text']], CURLOPT_RETURNTRANSFER, TRUE);
			curl_multi_add_handle($mh, $ch[$keyword['text']]);
		}
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) != -1) {
				do {
					$mrc = curl_multi_exec($mh, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		foreach ($keywords as $num => $keyword) {
			$replys[$keyword['text']] = curl_multi_getcontent($ch[$keyword['text']]);
			curl_multi_remove_handle($mh, $ch[$keyword['text']]);
		}
		curl_multi_close($mh);
		return $replys;
	}

	public function process($keywords) {
		$response = $this->parse($keywords);
		if ($response) {
                    $i=0;
			foreach ($response as $original => $page) {
				if (isset(json_decode($page)->parse)) {
					$page = json_decode($page)->parse;
					unset($page->redirects);
					$page->original = $original;
					$page->date = strtotime('now');
                                        $page->inckey=$i++;
					$page->text = $page->text->{'*'};
					$dom = new App_Simple();
					$dom->load($page->text);
					$images = $dom->find('img');
					$page->headitems = $page->headitems[0];
					$search = explode(' ', $page->title);
					$page->text = preg_replace("|href=['\"][^\#http](.*?)['\"]|is", " target='blank' href='http://en.wikipedia.org/$1'", $page->text);
					foreach ($images as $image) {
						if (preg_match('/' . $search[0] . '/', $image->src)) {
							$page->image = $image->src;
						}
					}
					if (isset($page->image) && isset($search[1])) {
						foreach ($images as $image) {
							if (preg_match('/' . $search[0] . '/', $image->src)) {
								$page->image = $image->src;
							}
						}
					}

					if (!isset($page->image) && isset($images[3])) {
						$page->image = $images[3]->src;
					}
					if (!isset($page->image) && isset($images[2])) {
						$page->image = $images[2]->src;
					}
					if (!isset($page->image) && isset($images[1])) {
						$page->image = $images[1]->src;
					}
					if (!isset($page->image) && isset($images[0])) {
						$page->image = $images[0]->src;
					}
					$dom->clear();
					unset($dom);
					unset($page->images);

					$pages[] = $page;
				}
			}
			foreach ($keywords as $key => $keyword) {
				$pages[$key]->snippet = $keyword['snippet'];
			}
			return $pages;
		}
	}

	public function xml2array($xmlObject, $out = array()) {
		foreach ((array) $xmlObject as $index => $node)
			$out[$index] = ( is_object($node) || is_array($node) ) ? $this->xml2array($node) : $node;

		return $out;
	}

}

?>
