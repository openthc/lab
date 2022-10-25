<?php
/**
 * Class to generate sequences
 */

namespace OpenTHC\Lab;

class Sequence
{
	const BASE_OPTION_KEY = 'lab-sample-seq-format';

	const SEQUENCE_SYMBOL_LIST = array(
		'018NY6XC00SEQH9174ZH0DV5DQ' => array(
			'id' => '018NY6XC00SEQH9174ZH0DV5DQ',
			'name' => 'Global Sequence',
			'symbol' => 'g',
			'reset' => '',
		),
		'018NY6XC00SEQCYWPQKDX1A37D' => array(
			'id' => '018NY6XC00SEQCYWPQKDX1A37D',
			'name' => 'Yearly Sequence',
			'symbol' => 'y',
			'reset' => 'year',
		),
		'018NY6XC00SEQWYYHWTB9DA4H1' => array(
			'id' => '018NY6XC00SEQWYYHWTB9DA4H1',
			'name' => 'Quarterly Sequence',
			'symbol' => 'q',
			'reset' => 'quarter',
		),
		'018NY6XC00SEQY40MRNEYXG414' => array(
			'id' => '018NY6XC00SEQY40MRNEYXG414',
			'name' => 'Monthly Sequence',
			'symbol' => 'm',
			'reset' => 'month',
		),
		'018NY6XC00SEQSQ2Q3HGEWKVPJ' => array(
			'id' => '018NY6XC00SEQSQ2Q3HGEWKVPJ',
			'name' => 'Daily Sequence',
			'symbol' => 'd',
			'reset' => 'day',
		),

		'018NY6XC00SEQ91X94PEQE6FE6' => array(
			'id' => '018NY6XC00SEQ91X94PEQE6FE6',
			'name' => 'two character type',
			'symbol' => 'TYPE',
			'example' => 'LS|LR',
		),
		'018NY6XC00SEQ2AJG2NWAATGDW' => array(
			'id' => '018NY6XC00SEQ2AJG2NWAATGDW',
			'name' => 'four digit year',
			'symbol' => 'YYYY',
			'example' => '"2022" [2000-9999]',
			'datetime_format' => 'Y',
		),
		'018NY6XC00SEQQDZ29D40GD851' => array(
			'id' => '018NY6XC00SEQQDZ29D40GD851',
			'name' => 'two digit year',
			'symbol' => 'YY',
			'example' => '"22" [20-99]',
			'datetime_format' => 'y',
		),
		'018NY6XC00SEQPGV8ZFACQN842' => array(
			'id' => '018NY6XC00SEQPGV8ZFACQN842',
			'name' => 'two digit month',
			'symbol' => 'MM',
			'example' => '"10" [01-12]',
			'datetime_format' => 'm',
		),
		'018NY6XC00SEQZZHEDF3CS1YEY' => array(
			'id' => '018NY6XC00SEQZZHEDF3CS1YEY',
			'name' => 'single character month',
			'symbol' => 'MA',
			'example' => '"J" [A-L]',
			'datetime_format' => 'F',
		),
		'018NY6XC00SEQEMJDC0Q4BXXE8' => array(
			'id' => '018NY6XC00SEQEMJDC0Q4BXXE8',
			'name' => 'two digit day of month',
			'symbol' => 'DD',
			'example' => '"24" [00-31]',
			'datetime_format' => 'd',
		),
		'018NY6XC00SEQD0SR832AFBEZM' => array(
			'id' => '018NY6XC00SEQD0SR832AFBEZM',
			'name' => 'three digit day of year',
			'symbol' => 'DDD',
			'example' => '"297" [000-366]',
			'datetime_format' => 'z',
		),
		'018NY6XC00SEQW1K0KJ7J2H9HT' => array(
			'id' => '018NY6XC00SEQW1K0KJ7J2H9HT',
			'name' => 'two digit hour',
			'symbol' => 'HH',
			'example' => '"21" [00-23]',
			'datetime_format' => 'H',
		),
		'018NY6XC00SEQA564ZV11D9FXB' => array(
			'id' => '018NY6XC00SEQA564ZV11D9FXB',
			'name' => 'two digit minute',
			'symbol' => 'II',
			'example' => '"00" [00-59]',
			'datetime_format' => 'i',
		),
		'018NY6XC00SEQ4YX5JGN5RZ8ZS' => array(
			'id' => '018NY6XC00SEQ4YX5JGN5RZ8ZS',
			'name' => 'two digit seconds',
			'symbol' => 'SS',
			'example' => '"01" [00-59]',
			'datetime_format' => 's',
		),


	);

	protected $dbc;
	protected $format;
	protected $sequence_namespace;

	private $_timezone;

	function __construct($namespace, $dbc) {
		$this->sequence_namespace = strtolower($namespace);
		$this->dbc = $dbc;
	}

	/**
	 * Get the next value of the sequence
	 */
	function next($format = null) {
		$this->format = $format;
		return $this->_next($this->_sequence_next_value());
	}

	/**
	 * Get the next sequence value, do not increment
	 */
	function peek($format = null) {
		$this->format = $format;
		return $this->_next($this->_sequence_last_value());
	}

	function _next($fun_sequence_get_value) {

		if (empty($this->format)) {
			$this->format = $this->dbc->fetchOne('SELECT val FROM base_option WHERE key = :k', [
				':k' => self::BASE_OPTION_KEY,
			]);
			$this->format = json_decode($this->format);
		}

		// Look for things that looks like sequence symbols separated by non-words
		preg_match_all('/{(\w+)}[\W]?/', $this->format, $matches);
		$symbols_found = $matches[1];

		$symbols = array();

		$tz = new \DateTimeZone($this->_timezone);
		$d0 = new \DateTime('now', $tz);
		foreach ($symbols_found as $sym) {
			$Symbol = $this->_findSymbol($sym);

			switch ($Symbol['id']) {
				case '018NY6XC00SEQH9174ZH0DV5DQ':
				case '018NY6XC00SEQCYWPQKDX1A37D':
				case '018NY6XC00SEQWYYHWTB9DA4H1':
				case '018NY6XC00SEQY40MRNEYXG414':
				case '018NY6XC00SEQSQ2Q3HGEWKVPJ':
					// @todo Company awareness
					$s = sprintf('seq_%s_%s', $this->sequence_namespace, strtolower($Symbol['symbol']));
					$s = strtolower($s);

					// $Symbol['value'] = $this->_sequence_last_value($s);
					$Symbol['value'] = $fun_sequence_get_value($s);
					break;

				case '018NY6XC00SEQ91X94PEQE6FE6':
					$Symbol['value'] = 'LR';
					// $Symbol['value'] = 'LS'; // @todo
					break;

				case '018NY6XC00SEQ2AJG2NWAATGDW':
				case '018NY6XC00SEQQDZ29D40GD851':
				case '018NY6XC00SEQPGV8ZFACQN842':
				case '018NY6XC00SEQZZHEDF3CS1YEY':
				case '018NY6XC00SEQEMJDC0Q4BXXE8':
				case '018NY6XC00SEQD0SR832AFBEZM':
				case '018NY6XC00SEQW1K0KJ7J2H9HT':
				case '018NY6XC00SEQA564ZV11D9FXB':
				case '018NY6XC00SEQ4YX5JGN5RZ8ZS':
					$Symbol['value'] = $d0->format($Symbol['datetime_format']);
					break;

				// default:
				// 	throw new Exception("Unknown Symbol [LSE-196]");
			}

			$symbols[ $sym ] = $Symbol;
		}

		$ret = $this->format;
		foreach ($symbols as $s) {
			switch ($s['id']) {
				case '018NY6XC00SEQH9174ZH0DV5DQ':
					$symbol = 'SEQ';
					break;
				case '018NY6XC00SEQCYWPQKDX1A37D':
				case '018NY6XC00SEQWYYHWTB9DA4H1':
				case '018NY6XC00SEQY40MRNEYXG414':
				case '018NY6XC00SEQSQ2Q3HGEWKVPJ':
					$symbol = sprintf("SEQ_%s", strtoupper($s['symbol']));
					break;
				default:
					$symbol = $s['symbol'];
			}
			$symbol = sprintf("{%s}", $symbol);
			$ret = str_replace($symbol, $s['value'], $ret);
		}
		return $ret;

	}

	function setTimeZone($tz) {
		$this->_timezone = $tz;
	}

	/**
	 * Find a symbol object given a value
	 */
	function _findSymbol($symbol) {
		foreach (self::SEQUENCE_SYMBOL_LIST as $id => $seq) {
			if (strtolower($symbol) === strtolower($seq['symbol'])) {
				return $seq;
			}

			$datetime_seq = sprintf('SEQ_%s', $seq['symbol']);
			if (strtolower($symbol) === strtolower($datetime_seq)) {
				return $seq;
			}

			if (strtolower($symbol) === strtolower('SEQ')) {
				return $seq;
			}
		}
	}

	/**
	 * Peek at the next value in the sequence
	 */
	function _sequence_last_value() {
		$dbc = $this->dbc;
		return function($seq) use ($dbc) {
			return $dbc->fetchOne(sprintf('SELECT (last_value + 1) FROM "%s"', $seq));
		};
	}
	/**
	 * Pop the next value from the sequence
	 */
	function _sequence_next_value($seq) {
		$dbc = $this->dbc;
		return function ($seq) use ($dbc) {
			return $this->dbc->fetchOne(sprintf("SELECT nextval('%s')", $s));
		};
	}

}
