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
        $owner = $this->owner;
	    $key = get_class($owner);
        
        if (!isset(self::$_listData[$key])) {
            if (is_object($condition))
                $condition = $condition->toArray();
            
            $criteria = new CDbCriteria($condition);
            $ownerCriteria = $owner->getDbCriteria(false);
            if ($ownerCriteria)
                $criteria->mergeWith($ownerCriteria);
            
            self::$_listData[$key] = $this->_findListData($criteria);
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
    protected function _findListData($criteria)
    {
        if ($this->orderByLabel) {
            if ($criteria->order)
                $criteria->order .= ', ' . $this->labelAttribute;
            else
                $criteria->order = $this->labelAttribute;
        }
        
        if ( $this->useModels )
            $items = $this->_findListDataUsingModels($criteria);
        else
            $items = $this->_findListDataUsingQuery($criteria);
        
        return $this->arrayListData($items);
    }
    
    protected function _findListDataUsingModels($criteria)
    {
        return $this->owner->findAll($criteria);
    }
    
    /**
     * 
     * @param CDbCriteria $criteria
     * @return array
     */
    protected function _findListDataUsingQuery($criteria)
    {
        $command = $this->getOwner()->getDbConnection()->createCommand();
        /* @var $command CDbCommand */
        
        $command
            ->select(array($this->idAttribute, $this->labelAttribute))
            ->from($this->owner->tableName() . ' ' . $criteria->alias);
        
        $command->where  = $criteria->condition;
        $command->order  = $criteria->order;
        $command->params = $criteria->params;
        
        return $command->queryAll();
    }
    
}
