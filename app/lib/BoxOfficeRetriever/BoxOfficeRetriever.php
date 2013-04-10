<?php namespace Draftr\BoxOfficeRetriever;

interface BoxOfficeRetriever
{
	public function supportsRegion($region);
	public function fetchBoxOfficeEarnings($externalId, $region);
}
