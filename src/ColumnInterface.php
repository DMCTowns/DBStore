<?php

namespace Reapit\Database;

interface ColumnInterface{

	/**
	 * Sets column name
	 * @param string $name
	 */
	public function setName($name);

	/**
	 * Returns column name
	 * @return string
	 */
	public function getName();

	/**
	 * Sets column type
	 * @param string $type
	 */
	public function setType($type);

	/**
	 * Returns column type
	 * @return string
	 */
	public function getType();

	/**
	 * Sets column length
	 * @param integer length
	 */
	public function setLength($length);

	/**
	 * Gets column length
	 * @return mixed
	 */
	public function getLength();

	/**
	 * Sets column default
	 * @param mixed $default
	 */
	public function setDefault($default);

	/**
	 * Gets column default
	 * @return mixed
	 */
	public function getDefault();

	/**
	 * Sets column default
	 * @param string $default
	 */
	public function setIndexType($indexType);

	/**
	 * Gets column default
	 * @return string
	 */
	public function getIndexType();

	/**
	 * Sets/Gets null allowed
	 * @param boolean $boolean
	 * @return boolean
	 */
	public function allowNull($boolean=null);

	/**
	 * Sets/Gets whether column auto increments
	 * @param boolean $boolean
	 * @return boolean
	 */
	public function autoIncrements($boolean=null);

	/**
	 * Sets column attributes
	 * @param string attributes
	 */
	public function setAttributes($attributes);

	/**
	 * Gets column attributes
	 * @return string
	 */
	public function getAttributes();

	/**
	 * Sets column extra settings
	 * @param string $extra
	 */
	public function setExtra($extra);

	/**
	 * Gets column extra settings
	 * @return string
	 */
	public function getExtra();

	/**
	 * Sets column data from array
	 * @param array $data
	 */
	public function setData(array $data);

	/**
	 * Gets column SQL - Abstract
	 * @param array $options
	 * @return string
	 */
	public function getSQL(array $options);

	/**
	 * Returns whether column is a boolean field
	 * @return boolean
	 */
	public function isBoolean();

	/**
	 * Returns whether column is numeric
	 * @return boolean
	 */
	public function isNumeric();

	/**
	 * Returns whether column is date/time
	 * @return boolean
	 */
	public function isDate();

	/**
	 * Returns whether column is date/time
	 * @return boolean
	 */
	public function isDateTime();

	/**
	 * Returns whether column is date/time
	 * @return boolean
	 */
	public function isTime();

}
?>