<?php

namespace Alquemedia_PostREST;

class TimeSince extends String_Object
{
	/**
	* 'since language' configurations
	*
	* @var array
	*/
	private $timeConfig = [
		[
			"display" => "Just now",
			"range" => "0|10"
		],
		[
			"display" => "A few seconds ago",
			"range" => "11|59"
		],
		[
			"display" => "A minute ago",
			"range" => "60|119"
		],
		[
			"display" => "Less than an hour ago",
			"range" => "120|3600"
		],
		[
			"display" => "More than an hour ago",
			"range" => "3601|86400"
		],
		[
			"display" => "One day ago",
			"range" => "86400|172800"
		],
		[
			"display" => "One week ago",
			"range" => "172801|604800"
		],
		[
			"display" => "Less than a month ago",
			"range" => "604800|2592000"
		],
		[
			"display" => "This year",
			"range" => "2592001|i"
		]
	];

	/**
	* TimeSince constructor
	*
	* @param string $createdDate
	*/
	public function __construct($createdDate)
	{
		$this->stringRepresentation = $this->getFormattedTime($createdDate);
	}

	/**
	* Convert the given time to 'since language' 
	*
	* @param string $createdDate
	* @return string
	*/
	private function getFormattedTime($createdDate)
	{
		// Get the difference with the current time in seconds
		$diff = strtotime(date(DATE_ATOM)) - strtotime(date($createdDate));

		foreach ($this->timeConfig as $config)
		{
			$range = @explode("|", $config["range"]);
			
			if ($diff >= $range[0] && (strcmp($range[1], "i") === 0 || $diff <= $range[1]))
			{
				return $config["display"];
			}
		}

		return "Unknow";
	}
}