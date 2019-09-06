<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\OdinModel;

class OdinModel extends Model
{
    /**
     *
     */
    public static function boot()
    {
        parent::boot();

	if (isset(static::$save_history) && static::$save_history) {
	    
	    self::updated(function($model) {		
		$original = $model->getOriginal();		
		// check if any field was changed
		if ($model->getChanges()) {
		    $fields = []; $isArray = false;
		    // loop changed fields
		    foreach ($model->getChanges() as $fieldName => $changedField) {
			$old = [];
			$new = [];
			// check fields for ignore fields like updated_at
			if (in_array($fieldName, OdinHistory::$ignoreFields)) {
			    continue;
			} else {
			    // if field is array check all array
			    if (is_array($changedField)) {
				$isArray = true;
				// check array
				foreach ($changedField as $key => $cField) {
				    // check if new, add to new array
				    if(!isset($original[$fieldName][$key])) {
					$new[] = $cField;
				    } else {
					// use json for check equal array
					$jsonOld = json_encode($original[$fieldName][$key]);
					$jsonNew = json_encode($cField);
					// add to array if different json
					if($jsonOld != $jsonNew) {
					    $old[] = $original[$fieldName][$key];
					    $new[] = $cField;
					}
				    }
				}
				// add to changed fields
				$fields[] = [$fieldName, $old, $new];

			    } else {
				// add to changed fields
				$fields[] = [$fieldName, $original[$fieldName], $changedField];
			    }
			}
		    }

		    // add to OdinHistory
		    if ($fields) {
			$data = [
			    'collection' => $model->collection,
			    'document_id' => $model->_id,
			    'fields' => $fields,
			    'is_array_changed' => $isArray,
			    'reason' => OdinHistory::REASON_ODIN_UPDATE
			];
			OdinHistory::saveHistoryData($data);
		    }
		}	
	    });
	}
    }
}
