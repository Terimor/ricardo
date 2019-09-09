<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class OdinModel extends Model
{
    /**
     *
     */
    public static function boot()
    {
        parent::boot();

		if (!empty(static::$save_history)) {

			self::updated(function($model) {
			$original = $model->getOriginal();
			// check if any field was changed
			if ($model->getChanges()) {
				$fields = [];
				// loop changed fields
				foreach ($model->getChanges() as $fieldName => $changedField) {
					$isArray = false;
					$old = [];
					$new = [];
					// check fields for ignore fields like updated_at
					if (in_array($fieldName, OdinHistory::$historyIgnoredFields)) {
						continue;
					} else {
						// if field is array check all array
						if (is_array($changedField)) {
							$isArray = true;

							// add json old and new objects
							$jsonFieldsOld = []; $jsonFieldsNew = [];
							$oldFields = []; $newFields = [];
							// old array
							foreach ($original[$fieldName] as $keyOld => $oField) {
								ksort($oField);
								$jsonFieldsOld[$keyOld] = json_encode($oField);
							}

							// new array
							foreach ($changedField as $keyNew => $nField) {
								ksort($nField);
								$jsonFieldsNew[$keyNew] = json_encode($nField);
							}

							// loop old array to check in new
							foreach ($jsonFieldsOld as $k => $jsonOld) {
								if (!in_array($jsonOld, $jsonFieldsNew)) {
									$oldFields[] = $original[$fieldName][$k];
								}
							}

							// loop new array to check in old
							foreach ($jsonFieldsNew as $k => $jsonNew) {
								if (!in_array($jsonNew, $jsonFieldsOld)) {
									$newFields[] = $changedField[$k];
								}
							}

							// add to changed fields
							$fields[] = [$fieldName, $oldFields, $newFields, $isArray];

						} else {
							// add to changed fields
							$fields[] = [$fieldName, $original[$fieldName], $changedField, $isArray];
						}
					}
				}

				// add to OdinHistory
				if ($fields) {
					$data = [
						'collection' => $model->collection,
						'document_id' => $model->_id,
						'fields' => $fields,
						'reason' => OdinHistory::REASON_ODIN_UPDATE
					];
					OdinHistory::saveHistoryData($data);
				}
			}
			});
		}
    }
}
