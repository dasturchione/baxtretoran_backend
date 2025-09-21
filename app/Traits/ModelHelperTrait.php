<?php

namespace App\Traits;


trait ModelHelperTrait
{

    public function folderPath($field = null)
    {
        return '/storage/' . self::$helpers['folderName'] . '/' . ($this->attributes['iid'] ?? $this->attributes['id']) . '/' . $field;
    }

    public function getImg($field, $size)
    {
        $img = $this->$field;

        if ($img == null) {
            return '/images/Default/default.svg';
        }

        return str_replace('.', '_' . $size . '.', $this->folderPath($field) . '/' . $img);
    }

    public function getImage($field, $src, $size = null)
    {
        if ($size) {
            return str_replace('.', '_' . $size . '.', $this->folderPath($field) . '/' . $src);
        } else {
            return $this->folderPath($field) . '/' . $src;
        }
    }

    public function generateImages(string $column = 'image_path'): array
    {
        $images = [];
        $sizes  = $this->imageSize($column);

        foreach ($sizes as $key => $size) {
            $images[$key] = asset(
                $this->getImage($column, $this->$column, $key)
            );
        }

        return $images;
    }

    public function getIsNewAttribute()
    {
        $newDate = new \DateTime('-2 days');
        return $this->created_at > $newDate;
    }

    // setters
    public function setCountryIdAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['country_id'] = (int)$value;
        }
    }
    public function setRegionIdAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['region_id'] = (int)$value;
        }
    }
    public function setBrandIdAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['brand_id'] = (int)$value;
        }
    }
    public function setOptionIdAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['option_id'] = (int)$value;
        }
    }

    // scopes
    public function scopeOnlyNew($data)
    {
        $newDate = new \DateTime('-2 days');

        return $data->whereDate('created_at', '>', $newDate);
    }
    public function scopeOrderBySort($data)
    {
        return $data->orderBy('sort', 'asc');
    }
    public function scopeOrderByDate($data)
    {
        return $data->orderBy('created_at', 'desc');
    }
    public function scopeActive($data)
    {
        return $data->where('is_active', true);
    }

    public function scopeFilter($q)
    {
        if (request('status')) {
            $q->where('status', request('status'));
        }

        if (request('iid')) {
            $q->where('iid', request('iid'));
        }

        if (request('in_stock')) {
            $q->where('quantity', '>', 0);
        }

        if (request('category')) {
            $category = request('category');
            $q->whereHas('categories', function ($query) use ($category) {
                $query->where('categories.id', $category)
                    ->orWhere('categories.parent_id', $category);
            });
        }

        if (request('brand')) {
            $q->whereIn('brand_id', request('brand'));
        }

        if (request('price_max')) {
            $min = str_replace(' ', '', request('price_min'));
            $max = str_replace(' ', '', request('price_max'));

            $q->whereBetween('price', [(int)$min, (int)$max]);
        }

        if (request('sort_by')) {
            $sort = explode('/', request('sort_by'));
            $q->orderBy($sort[0], $sort[1]);
        }

        if (request('delivery_type')) {
            $q->where('delivery_type', request('delivery_type'));
        }

        if (request('search')) {
            $search = request('search');
            $columns = $this->searchable;

            $terms = explode(' ', $search);

            $q->where(function ($q) use ($columns, $terms) {
                foreach ($terms as $term) {
                    $q->where(function ($q2) use ($columns, $term) {
                        foreach ($columns as $col) {
                            if (str_contains($col, '.')) {
                                [$relation, $field] = explode('.', $col);
                                $q2->orWhereHas($relation, function ($relQ) use ($field, $term) {
                                    $relQ->where($field, 'ILIKE', "%$term%");
                                });
                            } else {
                                if (is_numeric($term)) {
                                    $q2->orWhere($col, $term);
                                }
                                $q2->orWhere($col, 'ILIKE', "%$term%");
                            }
                        }
                    });
                }
            });
        }
    }

    // public function scopeSearch($query, $search)
    // {
    //     $columns = $this->searchable ?? [];

    //     if (!$search || empty($columns)) {
    //         return $query;
    //     }

    //     $terms = explode(' ', $search);

    //     return $query->where(function ($q) use ($columns, $terms) {
    //         foreach ($terms as $term) {
    //             $q->where(function ($q2) use ($columns, $term) {
    //                 foreach ($columns as $col) {
    //                     if (str_contains($col, '.')) {
    //                         // Relation qismi: category.name_uz
    //                         [$relation, $field] = explode('.', $col);
    //                         $q2->orWhereHas($relation, function ($relQ) use ($field, $term) {
    //                             $relQ->where($field, 'ILIKE', "%$term%");
    //                         });
    //                     } else {
    //                         if (is_numeric($term)) {
    //                             $q2->orWhere($col, $term);
    //                         }
    //                         $q2->orWhere($col, 'ILIKE', "%$term%");
    //                     }
    //                 }
    //             });
    //         }
    //     });
    // }
}
