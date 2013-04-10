<?php namespace Draftr\BoxOfficeRetriever;

use Draftr\Constants\Regions;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BoxOfficeMojoRetriever implements BoxOfficeRetriever
{
	private static $instance = NULL;
	private static $baseURL = "http://boxofficemojo.com/movies/?id=";
	private static $regionsSupported = array(Regions::DOMESTIC => '',
						 Regions::WORLDWIDE => '');

	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new BoxOfficeMojoRetriever();
		}
		return self::$instance;
	}

	public function supportsRegion($region)
	{
		return isset(self::$regionsSupported[$region]);
	}

	private function parseDomesticEarnings($htmlString)
	{
		$matches;
		$found = preg_match('/Domestic Total as of [\w\.]+\s*\d+,?\s*\d*:<\/font>\s*<b>\$([0-9,]*)/', $htmlString, $matches);
		$BOE = $found ? intval(str_replace(',', '', $matches[1])) : 0;
		return $BOE;
	}	

	private function parseWorldwideEarnings($htmlString)
	{
		$matches;
		$found = preg_match('/Worldwide:<\/b><\/td>[\s]*<td width=\"35\%\" align=\"right\">\W<b>\$([0-9,]*)/', $htmlString, $matches);
		$BOE = $found ? intval(str_replace(',', '', $matches[1])) : 0;
		return $BOE;
	}

	public function fetchBoxOfficeEarnings($externalId, $region)
	{
		if (!$this->supportsRegion($region)) {
			throw new \RuntimeException('Unsupported region');
		}

		$doc = new \DOMDocument();

		//Wrap the loading function with a silent error wrapper as the site is not properly formed
		set_error_handler('silentError');
		try {
			$doc->loadHTMLFile( self::$baseURL.$externalId.'.htm' );
		} catch(ErrorException $e) {}
		restore_error_handler();

		/*
		*  Yes, nothing like parsing html with regex :(  unfortunately the mark up is too f'd for the parser.
		*/
		$htmlString = $doc->saveHTML();

		switch ($region) {
			case Regions::DOMESTIC:
				$BOE = $this->parseDomesticEarnings($htmlString);
				break;
			case Regions::WORLDWIDE:
				$BOE = $this->parseWorldwideEarnings($htmlString);
				break;
		}
		return $BOE;
	
	}

	// Private constructor so that retriever is singleton
	private function __construct()
	{
	}
}
