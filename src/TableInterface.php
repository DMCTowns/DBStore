<?php

namespace DMCTowns\DBStore;

interface TableInterface{

	/**
	 * Returns table name
	 * @return string
	 */
	public function getName();

	/**
	 * Sets the primary index field used in the table
	 * @param string $primaryIndex
	 */
	public function setPrimaryIndexField($index);

	/**
	 * Returns primary index field used in the table
	 * @return string
	 */
	public function getPrimaryIndexField();

	/**
	 * Runs query on table's connection - Abstract
	 * @param string $query
	 */
	public function query($query);

	/**
	 * Creates new table based on supplied column data and options - Abstract
	 * @param array $column_data
	 * @param array $options
	 */
	public function create(array $column_data, array $options=null);

	/**
	 * Adds column to table
	 * @param ColumnInterface $column_data
	 * @param array $options
	 */
	public function addColumn($column, array $options=null);

	/**
	 * Alters table structure based on supplied column data and options - Abstract
	 * @param array $column_data
	 * @param array $options
	 */
	public function alter(array $column_data, array $options=null);

	/**
	 * Returns database object
	 * @return Database
	 */
	public function getDatabase();

	/**
	 * Returns array of record objects - Abstract
	 * @param string $query
	 * @return array
	 */
	public function getRecords($query);

	/**
	 * Gets a new record based on this table
	 * @param array $fieldData
	 */
	public function getNewRecord($fieldData=null);

	/**
	 * Returns array of column objects - Abstract
	 * @param boolean $reload
	 * @return array
	 */
	public function getColumns($reload=false);

	/**
	 * Returns column object
	 * @param string $name
	 * @return ColumnInterface
	 */
	public function getColumn($name);

	/**
	 * Gets new column
	 * @return \DMCTowns\DBStore\ColumnInterface
	 */
	public function getNewColumn();

}

?>