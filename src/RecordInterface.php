<?php

namespace Reapit\Database;

interface RecordInterface{

	/**
	 * Sets table
	 * @param TableInterface
	 */
	public function setTable(TableInterface $table);

	/**
	 * Returns record's table object
	 * @return TableInterface
	 */
	public function getTable();

	/**
	 * Gets column object for supplied field
	 * @param string $field
	 * @return ColumnInterface
	 */
	public function getFieldColumn($field);

	/**
	 * Sets field data from array
	 * @param array $fieldData
	 */
	public function setValues(array $fieldData);

	/**
	 * Sets field value
	 * @param string $field
	 * @param mixed $data
	 */
	public function setValue($field,$data);

	/**
	 * Returns field value
	 * @param string $field
	 */
	public function getValue($field);

	/**
	 * Sets date field value from either string, DateTime object or timestamp
	 * @param string $field
	 * @param mixed $datetime
	 */
	public function setDateTimeValue($field,$date);

	/**
	 * Sets date field value from either string, DateTime object or timestamp
	 * @param string $field
	 * @return \DateTime
	 */
	public function getDateTimeValue($field);

	/**
	 * Sets ID - argument is array of field => $value
	 * @param array $idPairs
	 */
	public function setKeys($idPairs);

	/**
	 *  Gets ID pair(s)
	 * @return array
	 */
	public function getKeys();

	/**
	 * Returns a string of ID value
	 * @return string
	 */
	public function getID();

	/**
	 *  Sets ID value
	 * @param string $id
	 * @return boolean
	 */
	public function setID($id);

	/**
	 * Loads data from database - returns true on success
	 * @param array $options
	 * @return boolean
	 */
	public function load(array $options=null);

	/**
	 * Saves data to database
	 * @param array $fieldData
	 * @return boolean
	 */
	public function save(array $fieldData=null);

	/**
	 * Deletes record from database
	 */
	public function delete();

	/**
	 * Returns true if reccord data is synched to database
	 * @param boolean $bool
	 * @return boolean
	 */
	public function isSynched($bool=null);

	/**
	 * Clears data from record
	 */
	public function clear();

	/**
	 * Returns record data in JSON format
	 * @return string
	 */
	public function getJSON();

	/**
	 * Returns record data as SimpleXMLElement
	 * @return string
	 */
	public function getXML();

}

?>