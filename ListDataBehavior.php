<?php

/**
 * Behavior that adds getListData method.
 * @property-read array $listData
 */
class ListDataBehavior extends CActiveRecordBehavior
{
    public $idAttribute = 'id';
    public $labelAttribute = 'name';
    
    /**
     * True if you want results to be ordered by label.
     * @var boolean
     */
    public $orderByLabel = true;
    /**
     * @var boolean
     */
    public $useModels = false;


    /**
     * @var array list data cache
     */
    private static $_listData = array();
    
	/**
	 * @param CDbCriteria|array $criteria
	 * @return array id => name
	 */
	public function getListData($condition = array())
	{
        $owner = $this->getOwner();
	    $key = get_class($owner);
        
        if (!isset(self::$_listData[$key])) {
            if (is_object($condition))
                $condition = $condition->toArray();
            
            $criteria = new CDbCriteria($condition);
            $ownerCriteria = $owner->getDbCriteria(false);
            if ($ownerCriteria)
                $criteria->mergeWith($ownerCriteria);
            
            $items = $this->_findItems($criteria);
            self::$_listData[$key] = $this->arrayListData($items);
	    }
	    
	    return self::$_listData[$key];
	}
    
	/**
	 * @param CActiveRecord[] $items
	 * @return array id => name
	 */
	public function arrayListData($items)
	{
        return CHtml::listData(
            $items,
            $this->idAttribute,
            $this->labelAttribute
        );
	}
    
    /**
     * 
     * @param CDbCriteria $criteria
     * @return array
     */
    protected function _findItems($criteria)
    {
        if ($this->orderByLabel) {
            if ($criteria->order)
                $criteria->order .= ', ' . $this->labelAttribute;
            else
                $criteria->order = $this->labelAttribute;
        }
        
        if ($this->useModels)
            $items = $this->_findItemsUsingModels($criteria);
        else
            $items = $this->_findItemsUsingQuery($criteria);
        
        return $items;
    }
    
    /**
     * 
     * @param CDbCriteria $criteria
     * @return CActiveRecord[]
     */
    protected function _findItemsUsingModels($criteria)
    {
        return $this->owner->findAll($criteria);
    }
    
    /**
     * 
     * @param CDbCriteria $criteria
     * @return array
     */
    protected function _findItemsUsingQuery($criteria)
    {
        $owner = $this->getOwner();
	    
        $command = $owner->getDbConnection()->createCommand();
        /* @var $command CDbCommand */
        
        $command
            ->select(array($this->idAttribute, $this->labelAttribute))
            ->from($owner->tableName() . ' ' . $criteria->alias);
        
        $command->where  = $criteria->condition;
        $command->order  = $criteria->order;
        $command->params = $criteria->params;
        
        return $command->queryAll();
    }
    
}
