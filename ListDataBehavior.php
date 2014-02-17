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
	 * @param CDbCriteria|array $criteria
     * @param string $labelAttribute label attribute name.
     * Defaults to {@link $labelAttribute}
	 * @return array id => name
	 */
	public function getListData($condition = array(), $labelAttribute = null)
	{
        if ($labelAttribute === null)
            $labelAttribute = $this->labelAttribute;
        
        $owner = $this->getOwner();
        /* @var $owner CActiveRecord */
        
        if (is_object($condition))
            $criteria = clone $condition;
        else
            $criteria = new CDbCriteria($condition);
        
        $ownerCriteria = $owner->getDbCriteria(false);
        if ($ownerCriteria)
            $criteria->mergeWith($ownerCriteria);

        $orderByLabel = 
            $this->orderByLabel &&
            isset($owner->getMetaData()->columns[$labelAttribute]);
        if ($orderByLabel) {
            $order = $owner->getTableAlias() . '.' . $labelAttribute;
            if ($criteria->order)
                $criteria->order .= ', ' . $order;
            else
                $criteria->order = $order;
        }

        $items = $this->_findItems($criteria);
        $listData = $this->arrayListData($items, $labelAttribute);
        
	    return $listData;
	}
    
	/**
	 * @param CActiveRecord[] $items
	 * @return array id => name
	 */
	public function arrayListData($items, $labelAttribute = null)
	{
        if ($labelAttribute === null)
            $labelAttribute = $this->labelAttribute;
        
        return CHtml::listData(
            $items,
            $this->idAttribute,
            $labelAttribute
        );
	}
    
    /**
     * 
     * @param CDbCriteria $criteria
     * @return array
     */
    protected function _findItems($criteria)
    {
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
        return $this->owner->resetScope()->findAll($criteria);
    }
    
    /**
     * 
     * @param CDbCriteria $criteria
     * @return array
     */
    protected function _findItemsUsingQuery($criteria)
    {
        $owner = $this->getOwner();
	    /* @var $owner CActiveRecord */
        
        $command = $owner->getDbConnection()->createCommand();
        /* @var $command CDbCommand */
        
        $command
            ->select(array($this->idAttribute, $this->labelAttribute))
            ->from($owner->tableName() . ' ' . $owner->getTableAlias());
        
        $command->where  = $criteria->condition;
        $command->order  = $criteria->order;
        $command->params = $criteria->params;
        
        return $command->queryAll();
    }
    
}
