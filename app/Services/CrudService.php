<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.08.2020
 * Time: 14:15
 */

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class CrudService
{

    private $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function changeStatus($status = null)
    {
        if (isset($status)) {
            return 1;
        }
        return 0;
    }

    public function dateFormat($date)
    {
        if ($date) {
            return Date::createFromFormat('d.m.Y', $date);
        } else {
            return now()->format('Y-m-d');
        }
    }

    public function applyImages($model, $files, $noChangedGallery = [])
    {
        $new_data = [];
        if (count($files) > 0) {
            foreach ($files as $key => $file) {
                if (is_array($file)) {
                    foreach ($file as $f) {
                        $noChangedGallery[] = $this->fileService->uploadImage($f, $model, $key, true);
                    }
                    $new_data[$key] = json_encode($noChangedGallery);
                } else {
                    $new_data[$key] = $this->fileService->uploadImage($file, $model, $key, false);
                }
            }
        }
        return $new_data;
    }

    public function linkedCategories($categories, $model)
    {
        if (!empty($categories)) {
            $model->categories()->sync($categories);
        } else {
            $model->categories()->detach();
        }
    }

    public function CREATE_OR_UPDATE($model, $validated, $files, $id)
    {
        DB::beginTransaction();
        try {
            $dataReq = $validated;

            if (!empty($dataReq['password'])) {
                $dataReq['password'] = bcrypt($dataReq['password']);
            } else {
                unset($dataReq['password']);
            }



            $instance  = $id ? $model::findOrFail($id) : new $model;

            // special processing
            $noChangedGallery = $dataReq['gallery'] ?? [];
            $dataReq['gallery'] = json_encode($noChangedGallery);

            if ($id) {
                $instance->update($dataReq);
            } else {
                $instance  = $instance->create($dataReq);
            }


            if (!empty($validated['role']) && method_exists($instance, 'syncRoles')) {
                $instance->syncRoles([$validated['role']]);
            }

            // applyImages
            $uploadedImages = $this->applyImages($instance, $files, $noChangedGallery);
            if (!empty($uploadedImages)) {
                $instance->update($uploadedImages);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \DomainException($exception->getMessage());
        }

        return $instance;
    }
}
