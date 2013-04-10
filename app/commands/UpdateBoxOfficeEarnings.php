<?php

use Draftr\BoxOfficeRetriever\BoxOfficeMojoRetriever;
use Draftr\Constants\Regions;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateBoxOfficeEarnings extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:UpdateBOE';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run an update to pull the current BOEs from all default movies.';

	/**
	 * The datetime at the start of command
	 *
	 * @var string
	 */
	protected $timestamp;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$timestamp = new DateTime();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
	
		$movies = Movie::whereIn('id', Config::get("draft.league_defaults.movies"))->get();

		foreach ($movies as $key => $movie) {
			$this->updateMovie($movie["id"], $movie["boxmojo_id"]);
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			
		);
	}

	/**
	*  Fires off the method to find the value
	*  Passes the value to DB updater
	*	@return bool
	*
	*/
	private function updateMovie($movieId, $mojoId) {

		sleep(rand(1,8)); //hopefully this will help keep us on the dl

		$BOE = $this->liberateBOE($mojoId, Regions::DOMESTIC);
		return $this->updateTable($movieId, $BOE);
	}

	/**
	*  Updates the BOE table with new value
	*	
	*  @return bool
	*/
	private function updateTable($movieId, $BOE) {
		$this->info('Update '.$movieId.' with BOE: '.$BOE);

		return true;
	}

	/**
	*  Returns the latest BOE for the movie in the specified region
	*	
	*  @return int
	*/
	private function liberateBOE($mojoId, $region) {
		$retriever = BoxOfficeMojoRetriever::getInstance();
		return $retriever->fetchBoxOfficeEarnings($mojoId, $region);
	}
}

function silentError() { }
